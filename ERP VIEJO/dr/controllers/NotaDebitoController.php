<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

/**
 * NotaDebitoController
 * 
 * Controlador dedicado para la gestión y emisión de Notas de Débito (POSODIN / POSDIN1).
 * Comprobantes Fiscales Oficiales de Débito:
 *  - E33: Comprobante Electrónico de Nota de Débito (e-CF DGII)
 *  - B03: Comprobante Tradicional de Nota de Débito
 * 
 * Mantiene separación de responsabilidades y comunicación bidireccional limpia con:
 *  - FacturaController (Facturas de Venta POSOINV modificadas)
 *  - NotaNCFController (Notas de Crédito POSORIN reversadas)
 */
class NotaDebitoController {

    /**
     * AJAX / API: Guardar y Emitir Nota de Débito (E33 / B03) sobre una Factura de Venta (POSOINV)
     * La factura original NO se anula, se marca como modificada y se genera la ND enlazada.
     */
    public function guardar() {
        $this->guardarNotaDebito();
    }

    public function guardarNotaDebito() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        $pdo = null;
        try {
            $database = new Database();
            $pdo = $database->getConnection();

            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }
            if (!$input) {
                throw new Exception('Datos de entrada inválidos para la Nota de Débito.');
            }

            $h = $input['header'] ?? [];
            $facDocEntry = (int)($h['C_DocOrigenNum'] ?? $input['fac_doc_entry'] ?? $input['doc_entry'] ?? 0);
            if ($facDocEntry <= 0) {
                throw new Exception('Debe especificar la Factura de Venta a la que se aplicará la Nota de Débito.');
            }

            $motivo = trim($h['Comments'] ?? $input['motivo'] ?? '');
            if (empty($motivo)) {
                throw new Exception('Debe ingresar el motivo o justificación de la Nota de Débito.');
            }

            $docDate = trim($h['DocDate'] ?? $input['doc_date'] ?? date('Y-m-d'));

            // Iniciar Transacción Atómica
            $pdo->beginTransaction();

            // 1. Obtener y bloquear la Factura de Venta en POSOINV
            $sqlFac = "SELECT * FROM POSOINV WITH (UPDLOCK, ROWLOCK) WHERE C_DocEntry = ?";
            $stmtFac = $pdo->prepare($sqlFac);
            $stmtFac->execute([$facDocEntry]);
            $fac = $stmtFac->fetch(PDO::FETCH_ASSOC);

            if (!$fac) {
                throw new Exception("La Factura ID #$facDocEntry no existe en la base de datos.");
            }

            if ($fac['CANCELED'] === 'Y') {
                throw new Exception("La Factura {$fac['NumAtCard']} ({$fac['U_FACNUM']}) está ANULADA y no puede recibir Notas de Débito.");
            }

            if (empty($fac['U_FACNUM'])) {
                throw new Exception("La Factura seleccionada no tiene un Comprobante Fiscal (NCF) emitido.");
            }

            // 2. Determinar serie de Nota de Débito (E33 para e-CF o B03 para tradicional)
            $serieFac = strtoupper(substr(trim($fac['U_FacSerie'] ?? $fac['U_FACNUM'] ?? 'E31'), 0, 3));
            $serieND = (stripos($serieFac, 'B') === 0) ? 'B03' : 'E33';

            $stmtLong = $pdo->prepare("SELECT longitud FROM serie_ncf WHERE UPPER(serie) = ?");
            $stmtLong->execute([$serieND]);
            $longitudSerie = (int)$stmtLong->fetchColumn();
            if ($longitudSerie <= 0) {
                $longitudSerie = (stripos($serieND, 'B') === 0) ? 8 : 10;
            }

            // Actualizar atómicamente carga_ncf para reservar el número
            $sqlNCF = "UPDATE carga_ncf
                       SET contador = contador + 1
                       OUTPUT Inserted.id_carga, Inserted.serie, Inserted.contador, Inserted.vencimiento
                       WHERE UPPER(serie) = ? AND contador <= final
                         AND id_carga = (SELECT MIN(id_carga) FROM carga_ncf WHERE UPPER(serie) = ? AND contador <= final)";
            $stmtNCF = $pdo->prepare($sqlNCF);
            $stmtNCF->execute([$serieND, $serieND]);
            $ncfRow = $stmtNCF->fetch(PDO::FETCH_ASSOC);

            if (!$ncfRow) {
                throw new Exception("No hay comprobantes fiscales disponibles para la serie '$serieND' (Nota de Débito). Verifique la carga NCF.");
            }

            // Validar vencimiento NCF
            if (!empty($ncfRow['vencimiento'])) {
                $vencTs = strtotime($ncfRow['vencimiento']);
                if ($vencTs && date('Y-m-d', $vencTs) < date('Y-m-d')) {
                    throw new Exception("Los comprobantes de la serie '$serieND' están vencidos (vencimiento: " . date('d/m/Y', $vencTs) . "). Cargue una nueva tanda en NCF.");
                }
            }

            $serieMayusND = strtoupper(trim($ncfRow['serie'] ?: $serieND));
            $numND_NCF = $serieMayusND . str_pad($ncfRow['contador'], $longitudSerie, '0', STR_PAD_LEFT);

            // 3. Calcular Totales y Líneas
            $inputLines = $input['lines'] ?? [];
            $lines = [];
            $totalND = 0.0;
            $itbisSum = 0.0;
            $subtotalNeto = 0.0;

            if (!empty($inputLines)) {
                foreach ($inputLines as $idx => $l) {
                    $cant = (float)($l['Quantity'] ?? 1);
                    $precio = (float)($l['Price'] ?? 0);
                    $discP = (float)($l['DiscPrcnt'] ?? 0);
                    $vatP = (float)($l['VatPrcnt'] ?? 0);
                    $lineTotal = ($cant * $precio) * (1 - ($discP / 100));
                    $lineVat = $lineTotal * ($vatP / 100);

                    $subtotalNeto += $lineTotal;
                    $itbisSum += $lineVat;

                    $lines[] = [
                        'LineNum'    => $idx,
                        'ItemCode'   => substr(trim($l['ItemCode'] ?? 'ND-AJUSTE'), 0, 20),
                        'Dscription' => substr(trim($l['Dscription'] ?? $motivo), 0, 100),
                        'Quantity'   => $cant,
                        'Price'      => $precio,
                        'DiscPrcnt'  => $discP,
                        'LineTotal'  => $lineTotal,
                        'VatPrcnt'   => $vatP,
                        'VatSum'     => $lineVat,
                        'WhsCode'    => substr(trim($l['WhsCode'] ?? '01'), 0, 8),
                        'TaxCode'    => substr(trim($l['TaxCode'] ?? ($vatP > 0 ? 'ITBIS18' : 'EXE')), 0, 8)
                    ];
                }
                $totalND = $subtotalNeto + $itbisSum;
            } else {
                $totalND = (float)($input['monto_debito'] ?? $h['DocTotal'] ?? 0);
                $itbisSum = (float)($input['itbis_debito'] ?? $h['VatSum'] ?? 0);
                $subtotalNeto = $totalND - $itbisSum;

                if ($totalND <= 0) {
                    throw new Exception("El monto de la Nota de Débito debe ser mayor a RD$ 0.00.");
                }

                $lines[] = [
                    'LineNum'    => 0,
                    'ItemCode'   => 'ND-AJUSTE',
                    'Dscription' => substr('Nota de Débito: ' . $motivo, 0, 100),
                    'Quantity'   => 1,
                    'Price'      => $subtotalNeto,
                    'DiscPrcnt'  => 0,
                    'LineTotal'  => $subtotalNeto,
                    'VatPrcnt'   => ($itbisSum > 0 && $subtotalNeto > 0) ? round(($itbisSum / $subtotalNeto) * 100, 2) : 0,
                    'VatSum'     => $itbisSum,
                    'WhsCode'    => '01',
                    'TaxCode'    => ($itbisSum > 0 ? 'ITBIS18' : 'EXE')
                ];
            }

            // 4. Generar Secuencia NumAtCard (D0000001 - 8 caracteres)
            $serieDocND = 'D';
            $sqlSecND = "MERGE secuencia WITH (SERIALIZABLE) AS T 
                         USING (VALUES (?)) AS U (tipo) ON U.tipo = T.tipo 
                         WHEN MATCHED THEN UPDATE SET T.secuencia = secuencia + 1 
                         WHEN NOT MATCHED THEN INSERT (secuencia, tipo) VALUES (1, U.tipo) 
                         OUTPUT inserted.secuencia;";
            $stmtSecND = $pdo->prepare($sqlSecND);
            $stmtSecND->execute([$serieDocND]);
            $secDocND = $stmtSecND->fetchColumn();
            if (!$secDocND) throw new Exception("No se generó la secuencia interna para la Nota de Débito.");
            $numAtCardND = substr($serieDocND . str_pad($secDocND, 7, '0', STR_PAD_LEFT), 0, 20);

            $sqlNextDoc = "SELECT ISNULL(MAX(DocEntry), 0) + 1 FROM POSODIN";
            $nextDocEntry = (int)$pdo->query($sqlNextDoc)->fetchColumn();

            // 5. Insertar Cabecera en POSODIN
            $cardCode   = substr($fac['CardCode'], 0, 15);
            $cardName   = substr($fac['CardName'] ?? '', 0, 100);
            $address    = substr($fac['Address'] ?? '', 0, 254);
            $tNegocioND = $fac['U_TNegocio'] ?? Helper::determinarTipoNegocio($fac['U_FacNit'] ?? '');
            $facNit     = substr(Helper::limpiarNit($fac['U_FacNit'] ?? ''), 0, 20);
            $facNom     = substr($fac['U_FacNom'] ?? $fac['CardName'] ?? '', 0, 100);
            $terminal   = substr($_SERVER['COMPUTERNAME'] ?? 'WEB', 0, 20);
            $usuario    = substr($_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'ADMIN', 0, 20);
            $ncfFacOrig = strtoupper(substr($fac['U_FACNUM'] ?? $fac['NumAtCard'] ?? '', 0, 19));
            $facNumAtCard = trim($fac['NumAtCard'] ?? ('F' . str_pad($facDocEntry, 7, '0', STR_PAD_LEFT)));
            $tipoNC_code = substr(trim($input['tipo_nd'] ?? $h['U_TipoNC'] ?? '01'), 0, 30);

            $sqlHeader = "INSERT INTO POSODIN (
                            C_Posted, DocEntry, DocNum, DocType, CANCELED, Handwrtten, Printed,
                            DocStatus, InvntSttus, Transfered, ObjType,
                            DocDate, DocDueDate, CardCode, CardName, Address,
                            NumAtCard, VatPercent, VatSum, DiscSum, DiscPrcnt,
                            DocCur, DocRate, DocTotal, DocTotalFC,
                            Comments, JrnlMemo, GrossBase,
                            DataSource, UserSign, UserSign2,
                            U_FacNit, U_FacNom, U_FacFecha,
                            U_FacSerie, U_TipoNC, U_FACNUM, U_NCO,
                            U_CGastos, U_user, U_terminal, U_TNegocio,
                            U_SCIASerie, U_SCIADocNum,
                            C_DocNet, C_Inserted, C_DocStatus,
                            C_DocOrigen, C_DocOrigenNum, C_FromNumAtCard,
                            C_FromC_DocEntry
                          ) VALUES (
                            'E', ?, ?, 'I', 'N', 'N', 'N',
                            'C', 'R', 'N', '13',
                            ?, ?, ?, ?, ?,
                            ?, ?, ?, 0, 0,
                            'DOP', 1, ?, ?,
                            ?, ?, 0,
                            'I', 1, 1,
                            ?, ?, ?,
                            ?, ?, ?, ?,
                            '01', ?, ?, ?,
                            'D', ?,
                            ?, GETDATE(), 'C',
                            'INVOICE', ?, ?,
                            ?
                          )";

            $stmtH = $pdo->prepare($sqlHeader);
            $stmtH->execute([
                $nextDocEntry,                          // DocEntry
                $nextDocEntry,                          // DocNum
                $docDate,                               // DocDate
                $docDate,                               // DocDueDate
                $cardCode,                              // CardCode
                $cardName,                              // CardName
                $address,                               // Address
                $numAtCardND,                           // NumAtCard (Secuencia interna D0000001)
                ($subtotalNeto > 0) ? round(($itbisSum / $subtotalNeto) * 100, 2) : 0, // VatPercent
                $itbisSum,                              // VatSum
                $totalND,                               // DocTotal
                $totalND,                               // DocTotalFC
                $motivo,                                // Comments
                'Nota de Débito ' . $numND_NCF,         // JrnlMemo
                $facNit,                                // U_FacNit
                $facNom,                                // U_FacNom
                $fac['DocDate'],                        // U_FacFecha
                $serieND,                               // U_FacSerie
                $tipoNC_code,                           // U_TipoNC
                $numND_NCF,                             // U_FACNUM (NCF ND)
                $ncfFacOrig,                            // U_NCO (NCF de la Factura modificada)
                $usuario,                               // U_user
                $terminal,                              // U_terminal
                $tNegocioND,                            // U_TNegocio
                (int)$secDocND,                         // U_SCIADocNum
                $subtotalNeto,                          // C_DocNet
                $facDocEntry,                           // C_DocOrigenNum (C_DocEntry de POSOINV)
                $facNumAtCard,                          // C_FromNumAtCard (Número de Factura de Origen)
                $facDocEntry                            // C_FromC_DocEntry
            ]);

            $ndDocEntry = (int)$pdo->query('SELECT SCOPE_IDENTITY()')->fetchColumn();
            if (!$ndDocEntry) {
                $ndDocEntry = (int)$pdo->lastInsertId();
            }
            if ($ndDocEntry <= 0) {
                $ndDocEntry = (int)$pdo->query("SELECT ISNULL(MAX(C_DocEntry), 0) FROM POSODIN")->fetchColumn();
            }
            if ($ndDocEntry <= 0) {
                throw new Exception("Error al obtener el ID generado para la Nota de Débito en POSODIN.");
            }

            // 6. Insertar Detalle en POSDIN1
            $sqlLineInsert = "INSERT INTO POSDIN1 (
                                C_DocEntry, DocEntry, LineNum, LineStatus,
                                ItemCode, Dscription, Quantity, Price,
                                DiscPrcnt, LineTotal, VatPrcnt, VatSum,
                                WhsCode, TaxCode, BaseEntry, BaseType, BaseLine
                              ) VALUES (
                                ?, ?, ?, 'C',
                                ?, ?, ?, ?,
                                ?, ?, ?, ?,
                                ?, ?, ?, 13, ?
                              )";
            $stmtLineIns = $pdo->prepare($sqlLineInsert);

            foreach ($lines as $l) {
                $stmtLineIns->execute([
                    $ndDocEntry,
                    $nextDocEntry,
                    (int)$l['LineNum'],
                    $l['ItemCode'],
                    $l['Dscription'],
                    (float)$l['Quantity'],
                    (float)$l['Price'],
                    (float)($l['DiscPrcnt'] ?? 0),
                    (float)$l['LineTotal'],
                    (float)($l['VatPrcnt'] ?? 0),
                    (float)($l['VatSum'] ?? 0),
                    $l['WhsCode'],
                    $l['TaxCode'],
                    $facDocEntry,
                    (int)($l['LineNum'])
                ]);
            }

            // 7. Actualizar POSOINV: La factura NO se cancela, se anexa la trazabilidad de modificación
            $commentUpdate = trim(($fac['Comments'] ?? '') . " [Modificada por ND {$numND_NCF}]");
            $sqlModFac = "UPDATE POSOINV 
                          SET Comments = ?,
                              C_Updated = GETDATE()
                          WHERE C_DocEntry = ?";
            $pdo->prepare($sqlModFac)->execute([$commentUpdate, $facDocEntry]);

            // 8. Registrar en ecf_xml_invoice para transmisión DGII
            require_once __DIR__ . '/../config/EcfHelper.php';
            EcfHelper::registrarEcfXml(
                $pdo,
                $ndDocEntry,
                $numAtCardND,
                $serieND,
                $numND_NCF,
                $ncfRow['vencimiento'] ?? null,
                $facNit,
                0,
                $docDate,
                'DEBIT_NOTE'
            );

            // Confirmar Transacción
            $pdo->commit();

            ob_end_clean();
            echo json_encode([
                'success'         => true,
                'message'         => "Nota de Débito {$numND_NCF} emitida exitosamente para la Factura {$facNumAtCard}.",
                'docEntry'        => $ndDocEntry,
                'numNCF'          => $numND_NCF,
                'facturaAfectada' => $facNumAtCard
            ]);
            exit();

        } catch (Exception $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        } catch (Throwable $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
            exit();
        }
    }

    /**
     * AJAX: Obtener detalle completo de una Nota de Débito (Cabecera y Líneas)
     */
    public function obtenerDetalle() {
        $this->obtenerDetalleND();
    }

    public function obtenerDetalleND() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $database = new Database();
            $pdo = $database->getConnection();

            $id = (int)($_GET['id'] ?? $_GET['docEntry'] ?? 0);
            if ($id <= 0) {
                throw new Exception("ID de Nota de Débito no proporcionado.");
            }

            // 1. Cabecera de la ND + datos de factura original
            $sqlH = "SELECT 
                        nd.C_DocEntry,
                        nd.DocEntry,
                        nd.DocNum,
                        nd.DocDate,
                        FORMAT(nd.DocDate, 'dd/MM/yyyy') AS DocDateFmt,
                        FORMAT(nd.C_Inserted, 'dd/MM/yyyy hh:mm tt') AS C_InsertedFmt,
                        nd.CardCode,
                        nd.CardName,
                        nd.Address,
                        ISNULL(nd.U_FacNit, '') AS U_FacNit,
                        nd.U_FACNUM AS NumNCF,
                        ISNULL(nd.U_FacSerie, 'E33') AS SerieND,
                        ISNULL(nd.U_NCO, '') AS NumNCOrigen,
                        ISNULL(nd.C_FromNumAtCard, '') AS C_FromNumAtCard,
                        nd.C_DocOrigenNum,
                        ISNULL(nd.C_DocNet, 0) AS SubtotalNeto,
                        ISNULL(nd.VatSum, 0) AS ITBIS,
                        ISNULL(nd.DocTotal, 0) AS TotalND,
                        ISNULL(nd.Comments, '') AS Motivo,
                        nd.U_user,
                        ISNULL(u.nombre, ISNULL(nd.U_user, 'SYS')) AS Usuario,
                        ISNULL(nd.U_terminal, '') AS Terminal,
                        ISNULL(nd.CANCELED, 'N') AS CANCELED,
                        -- Datos de la factura origen modificada
                        ISNULL(inv.U_FACNUM, inv.NumAtCard) AS FacturaNCF,
                        inv.NumAtCard AS FacturaNumAtCard,
                        inv.DocTotal AS FacturaDocTotal,
                        FORMAT(inv.DocDate, 'dd/MM/yyyy') AS FacturaDocDateFmt
                     FROM POSODIN nd
                     LEFT JOIN POSOINV inv ON nd.C_DocOrigenNum = inv.C_DocEntry
                     LEFT JOIN usuario u ON (nd.U_user = u.usuario OR nd.U_user = CAST(u.id_usuario AS VARCHAR(20)) OR nd.U_user = u.nombre)
                     WHERE nd.C_DocEntry = ?";
            
            $stmtH = $pdo->prepare($sqlH);
            $stmtH->execute([$id]);
            $cabecera = $stmtH->fetch(PDO::FETCH_ASSOC);

            if (!$cabecera) {
                throw new Exception("No se encontró la Nota de Débito solicitada.");
            }

            $userDb = trim($cabecera['Usuario'] ?? '');
            if (empty($userDb) || strtoupper($userDb) === 'SYS') {
                if (!empty($_SESSION['nombre'])) {
                    $cabecera['Usuario'] = $_SESSION['nombre'];
                } elseif (!empty($_SESSION['usuario'])) {
                    $cabecera['Usuario'] = $_SESSION['usuario'];
                } else {
                    $cabecera['Usuario'] = 'SYS';
                }
            }

            // 2. Líneas de la ND (POSDIN1)
            $sqlL = "SELECT 
                        l.LineNum,
                        l.ItemCode,
                        l.Dscription,
                        l.Quantity,
                        l.Price,
                        l.PriceBefDi,
                        l.DiscPrcnt,
                        l.LineTotal,
                        l.VatPrcnt,
                        l.VatSum,
                        l.WhsCode
                     FROM POSDIN1 l
                     WHERE l.C_DocEntry = ?
                     ORDER BY l.LineNum ASC";
            $stmtL = $pdo->prepare($sqlL);
            $stmtL->execute([$id]);
            $lineas = $stmtL->fetchAll(PDO::FETCH_ASSOC);

            ob_end_clean();
            echo json_encode([
                'success'  => true,
                'cabecera' => $cabecera,
                'lineas'   => $lineas
            ]);
            exit();

        } catch (Exception $e) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
            exit();
        } catch (Throwable $e) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'msg' => 'Error interno: ' . $e->getMessage()]);
            exit();
        }
    }

    /**
     * AJAX: Buscar Factura de Venta por número o NCF para prepararle una Nota de Débito
     */
    public function buscarFactura() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $database = new Database();
            $pdo = $database->getConnection();

            $num = trim($_GET['num'] ?? '');
            if (empty($num)) {
                throw new Exception('Debe indicar el número de Factura o NCF.');
            }

            $sql = "SELECT TOP 1
                        h.C_DocEntry, h.DocEntry, h.DocNum, h.NumAtCard,
                        h.CardCode, h.CardName, h.Address,
                        h.DocDate, FORMAT(h.DocDate, 'dd/MM/yyyy') AS DocDateFmt,
                        h.DocTotal, h.VatSum, h.DiscSum, h.C_DocNet,
                        h.Comments, h.U_FACNUM, h.U_FacSerie,
                        h.U_FacNit, h.U_FacNom,
                        h.CANCELED, h.DocStatus
                    FROM POSOINV h
                    WHERE h.U_FACNUM = ?
                       OR h.NumAtCard = ?
                       OR CAST(h.DocNum AS NVARCHAR) = ?
                       OR CAST(h.C_DocEntry AS NVARCHAR) = ?
                    ORDER BY h.C_DocEntry DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$num, $num, $num, $num]);
            $factura = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$factura) {
                throw new Exception("No se encontró la Factura de Venta '$num'.");
            }

            if ($factura['CANCELED'] === 'Y') {
                throw new Exception("La Factura {$factura['NumAtCard']} está CANCELADA y no admite Nota de Débito.");
            }

            ob_end_clean();
            echo json_encode([
                'success' => true,
                'factura' => $factura
            ]);
            exit();

        } catch (Throwable $e) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        }
    }

    /**
     * AJAX: Buscar Nota de Crédito por número (NCF E34, DocNum o C_DocEntry)
     * Para reversión / anulación con Nota de Débito (E33) si aplica
     */
    public function buscarNotaCredito() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $database = new Database();
            $pdo = $database->getConnection();

            $num = trim($_GET['num'] ?? '');
            if (empty($num)) {
                throw new Exception('Debe indicar el número de Nota de Crédito o NCF E34.');
            }

            $sql = "SELECT TOP 1
                        nc.C_DocEntry, nc.DocEntry, nc.DocNum, nc.NumAtCard,
                        nc.CardCode, nc.CardName, nc.Address,
                        nc.DocDate, nc.DocDueDate,
                        FORMAT(nc.DocDate, 'dd/MM/yyyy') AS DocDateFmt,
                        FORMAT(nc.C_Inserted, 'dd/MM/yyyy hh:mm tt') AS C_InsertedFmt,
                        nc.DocTotal, nc.VatSum, nc.DiscSum, nc.C_DocNet,
                        nc.Comments, nc.U_FACNUM, nc.U_FacSerie,
                        nc.U_FacNit, nc.U_FacNom,
                        nc.CANCELED, nc.DocStatus, nc.C_DocStatus,
                        nc.C_DocOrigenNum, nc.U_NCO, nc.C_FromNumAtCard,
                        nc.U_TipoNC,
                        ISNULL(ta.Name, 'Devolución / Corrección') AS TipoNC_Nombre,
                        inv.NumAtCard AS FacturaNCF,
                        inv.DocTotal AS FacturaDocTotal,
                        FORMAT(inv.DocDate, 'dd/MM/yyyy') AS FacturaDocDateFmt
                    FROM POSORIN nc
                    LEFT JOIN TIPOS_ANULACION ta ON nc.U_TipoNC = ta.code
                    LEFT JOIN POSOINV inv ON nc.C_DocOrigenNum = inv.C_DocEntry
                    WHERE nc.U_FACNUM = ? 
                       OR nc.NumAtCard = ?
                       OR CAST(nc.DocNum AS NVARCHAR) = ?
                       OR CAST(nc.C_DocEntry AS NVARCHAR) = ?
                       OR CAST(nc.DocEntry AS NVARCHAR) = ?
                    ORDER BY nc.C_DocEntry DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$num, $num, $num, $num, $num]);
            $notaCredito = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$notaCredito) {
                throw new Exception("No se encontró ninguna Nota de Crédito con el número o NCF: $num");
            }

            // Verificar si ya fue reversada con una ND
            if ($notaCredito['CANCELED'] === 'Y') {
                $ndRow = self::obtenerNDPorOrigenNC($pdo, (int)$notaCredito['C_DocEntry']);
                $ndInfo = $ndRow ? " (Reversada mediante ND {$ndRow['U_FACNUM']} el {$ndRow['DocDateFmt']})" : "";
                throw new Exception("La Nota de Crédito $num ya está CANCELADA / ANULADA{$ndInfo} y no admite otra Nota de Débito.");
            }

            $ncDocEntry = (int)$notaCredito['C_DocEntry'];

            // Líneas de la Nota de Crédito (POSRIN1)
            $sqlLines = "SELECT
                            l.LineNum, l.ItemCode, l.Dscription,
                            l.Quantity, l.Price, l.PriceBefDi,
                            l.DiscPrcnt, l.LineTotal,
                            l.VatPrcnt, l.VatSum,
                            l.WhsCode, l.TaxCode,
                            l.OpenQty
                         FROM POSRIN1 l
                         WHERE l.C_DocEntry = ?
                         ORDER BY l.LineNum ASC";
            $stmtL = $pdo->prepare($sqlLines);
            $stmtL->execute([$ncDocEntry]);
            $lineas = $stmtL->fetchAll(PDO::FETCH_ASSOC);

            ob_end_clean();
            echo json_encode([
                'success'     => true,
                'notaCredito' => $notaCredito,
                'lineas'      => $lineas
            ]);
            exit();

        } catch (Throwable $e) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit();
        }
    }

    /**
     * Generar Reporte / Impresión PDF: Comprobante de Nota de Débito (E33 / B03)
     */
    public function informeND() {
        $id = (int)($_GET['id'] ?? $_GET['docEntry'] ?? 0);
        $copia = (int)($_GET['copia'] ?? 0);

        require_once __DIR__ . '/../reports/InformeNotaCredito.php';
        $reporte = new InformeNotaCredito();

        if ($id > 0) {
            $reporte->generarComprobanteND($id, $copia);
        } else {
            die("Parámetros insuficientes para generar el comprobante de Nota de Débito.");
        }
    }

    // =========================================================================
    // MÉTODOS DE INTEGRACIÓN / SERVICIO PARA OTROS CONTROLADORES
    // =========================================================================

    /**
     * Obtener datos de la Nota de Débito vinculada a una Factura de Venta
     * Utilizado por FacturaController para enriquecer la consulta y detalle de facturas.
     *
     * @param PDO $pdo
     * @param int $facDocEntry C_DocEntry de POSOINV
     * @return array|null
     */
    public static function obtenerNDPorFactura(PDO $pdo, int $facDocEntry): ?array {
        if ($facDocEntry <= 0) return null;

        $sql = "SELECT TOP 1
                    nd.C_DocEntry,
                    nd.DocEntry,
                    nd.DocNum,
                    nd.NumAtCard,
                    nd.U_FACNUM,
                    nd.U_FacSerie,
                    nd.DocDate,
                    FORMAT(nd.DocDate, 'dd/MM/yyyy') AS DocDateFmt,
                    nd.DocTotal,
                    nd.VatSum,
                    nd.C_DocNet,
                    nd.Comments,
                    ISNULL(nd.CANCELED, 'N') AS CANCELED
                FROM POSODIN nd WITH (NOLOCK)
                WHERE nd.C_DocOrigenNum = ? AND ISNULL(nd.CANCELED, 'N') <> 'Y'
                ORDER BY nd.C_DocEntry DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$facDocEntry]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Obtener datos de la Nota de Débito vinculada a una Nota de Crédito (Reversión)
     * Utilizado por NotaNCFController para verificar estado de anulación.
     *
     * @param PDO $pdo
     * @param int $ncDocEntry C_DocEntry de POSORIN
     * @return array|null
     */
    public static function obtenerNDPorOrigenNC(PDO $pdo, int $ncDocEntry): ?array {
        if ($ncDocEntry <= 0) return null;

        $sql = "SELECT TOP 1 
                    nd.C_DocEntry,
                    nd.NumAtCard,
                    nd.U_FACNUM, 
                    nd.DocDate,
                    FORMAT(nd.DocDate, 'dd/MM/yyyy') AS DocDateFmt, 
                    nd.Comments,
                    nd.DocTotal
                FROM POSODIN nd WITH (NOLOCK)
                WHERE nd.C_DocOrigenNum = ? 
                ORDER BY nd.C_DocEntry DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ncDocEntry]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
?>
