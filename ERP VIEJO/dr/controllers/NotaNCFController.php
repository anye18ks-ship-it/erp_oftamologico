<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class NotaNCFController {

    /**
     * Pantalla principal de Nota de Crédito NCF
     */
    public function index() {
        $database = new Database();
        $pdo = $database->getConnection();

        // Tipos de NC para el combo (Devolución / Descuento)
        $stmtTipos = $pdo->query("SELECT Code as code, Code, Name FROM TIPOS_ANULACION ORDER BY Name ASC");
        $tiposNC = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);

        // Series NCF activas (para referencia)
        $seriesNCF = [];
        try {
            $stmtSeries = $pdo->query("SELECT serie, descripcion FROM serie_ncf WHERE activo = 'Y' ORDER BY serie");
            if ($stmtSeries) {
                $seriesNCF = $stmtSeries->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $e) {
            $seriesNCF = [];
        }

        // Verificar si hay caja aperturada activa para devolución física
        $caja_id = $_SESSION['caja_id'] ?? 0;
        $id_diario_caja = $_SESSION['caja_apertura_id'] ?? 0;
        $cajaActiva = null;
        try {
            $sqlCaja = "SELECT TOP 1 id_diario_caja, CONVERT(varchar, fecha_apertura, 103) as fecha 
                        FROM diario_caja 
                        WHERE (id_diario_caja = ? OR id_caja = ? OR ? = 0) AND fecha_cierre IS NULL 
                        ORDER BY id_diario_caja DESC";
            $stmtCaja = $pdo->prepare($sqlCaja);
            $stmtCaja->execute([$id_diario_caja, $caja_id, $caja_id]);
            $cajaActiva = $stmtCaja->fetch(PDO::FETCH_ASSOC);
            if ($cajaActiva && empty($_SESSION['caja_apertura_id'])) {
                $_SESSION['caja_apertura_id'] = (int)$cajaActiva['id_diario_caja'];
            }
        } catch (Throwable $e) {
            $cajaActiva = null;
        }

        include __DIR__ . '/../views/Facturacion/nota_credito_ncf.php';
    }

    /**
     * AJAX: Buscar factura por número (NumAtCard o DocNum)
     * Retorna cabecera + líneas + saldo disponible para NC
     */
    public function buscarFactura() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $database = new Database();
            $pdo = $database->getConnection();

            $num = trim($_GET['num'] ?? '');
            if (empty($num)) {
                throw new Exception('Debe indicar el número de factura.');
            }

            // Buscar en POSOINV por NumAtCard (NCF), U_FACNUM, DocNum, C_DocEntry o DocEntry
            $sql = "SELECT TOP 1
                        f.C_DocEntry, f.DocEntry, f.DocNum, f.NumAtCard,
                        f.CardCode, f.CardName, f.Address,
                        f.DocDate, f.DocDueDate,
                        FORMAT(f.DocDate, 'dd/MM/yyyy') AS DocDateFmt,
                        f.DocTotal, f.VatSum, f.DiscSum,
                        f.Comments, f.U_FACNUM, f.U_FacSerie,
                        f.U_FacNit, f.U_FacNom,
                        f.CANCELED, f.DocStatus
                    FROM POSOINV f
                    WHERE f.NumAtCard = ? 
                       OR f.U_FACNUM = ?
                       OR CAST(f.DocNum AS NVARCHAR) = ?
                       OR CAST(f.C_DocEntry AS NVARCHAR) = ?
                       OR CAST(f.DocEntry AS NVARCHAR) = ?
                    ORDER BY f.C_DocEntry DESC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$num, $num, $num, $num, $num]);
            $factura = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$factura) {
                throw new Exception("No se encontró ninguna factura con el número: $num");
            }

            // Verificar que la factura no esté cancelada
            if ($factura['CANCELED'] === 'Y') {
                throw new Exception("La factura $num está CANCELADA y no admite Notas de Crédito.");
            }

            $docEntry = (int)$factura['C_DocEntry'];

            // Líneas de la factura
            $sqlLines = "SELECT
                            l.LineNum, l.ItemCode, l.Dscription,
                            l.Quantity, l.Price, l.PriceBefDi,
                            l.DiscPrcnt, l.LineTotal,
                            l.VatPrcnt, l.VatSum,
                            l.WhsCode, l.TaxCode,
                            l.OpenQty
                         FROM POSINV1 l
                         WHERE l.C_DocEntry = ?
                         ORDER BY l.LineNum ASC";
            $stmtL = $pdo->prepare($sqlLines);
            $stmtL->execute([$docEntry]);
            $lineas = $stmtL->fetchAll(PDO::FETCH_ASSOC);

            // Calcular total ya aplicado en NCs previas (no anuladas)
            $sqlNC = "SELECT ISNULL(SUM(nc.DocTotal), 0) AS TotalNC
                      FROM POSORIN nc
                      WHERE nc.C_DocOrigenNum = ? AND ISNULL(nc.CANCELED, 'N') <> 'Y'";
            $stmtNC = $pdo->prepare($sqlNC);
            $stmtNC->execute([$docEntry]);
            $totalNCPrevio = (float)$stmtNC->fetchColumn();

            $docTotal = (float)$factura['DocTotal'];
            $saldoDisponible = $docTotal - $totalNCPrevio;

            if ($saldoDisponible <= 0) {
                throw new Exception("La factura $num no tiene saldo disponible para Nota de Crédito. El monto ya fue totalmente cubierto por NC(s) anteriores.");
            }

            // Autocorrección de registros previos con C_DocEntry = 0
            try {
                $pdo->exec("UPDATE nl SET nl.C_DocEntry = nc.C_DocEntry FROM POSRIN1 nl INNER JOIN POSORIN nc ON nc.DocEntry = nl.DocEntry WHERE ISNULL(nl.C_DocEntry, 0) = 0");
            } catch (Throwable $e) {}

            // Calcular cantidad ya devuelta por línea en NCs previas
            $sqlQtyNC = "SELECT nl.BaseLine, SUM(nl.Quantity) AS QtyCreditada
                         FROM POSRIN1 nl
                         INNER JOIN POSORIN nc ON (nc.C_DocEntry = nl.C_DocEntry OR (ISNULL(nl.C_DocEntry,0) = 0 AND nc.DocEntry = nl.DocEntry))
                         WHERE (nl.BaseEntry = ? OR nc.C_DocOrigenNum = ?) AND ISNULL(nc.CANCELED, 'N') <> 'Y'
                         GROUP BY nl.BaseLine";
            $stmtQtyNC = $pdo->prepare($sqlQtyNC);
            $stmtQtyNC->execute([$docEntry, $docEntry]);
            $qtyPorLinea = [];
            while ($row = $stmtQtyNC->fetch(PDO::FETCH_ASSOC)) {
                $qtyPorLinea[(int)$row['BaseLine']] = (float)$row['QtyCreditada'];
            }

            // Enriquecer líneas con cantidad disponible
            foreach ($lineas as &$l) {
                $lineNum    = (int)$l['LineNum'];
                $qtyCred    = $qtyPorLinea[$lineNum] ?? 0.0;
                $qtyOrig    = (float)$l['Quantity'];
                $qtyDisp    = $qtyOrig - $qtyCred;
                $l['QtyDisponible'] = max(0, $qtyDisp);
                $l['QtyCreditada']  = $qtyCred;
                // Formatear números
                $l['Price']       = (float)$l['Price'];
                $l['PriceBefDi']  = (float)$l['PriceBefDi'];
                $l['DiscPrcnt']   = (float)$l['DiscPrcnt'];
                $l['VatPrcnt']    = (float)$l['VatPrcnt'];
                $l['LineTotal']   = (float)$l['LineTotal'];
                $l['Quantity']    = $qtyOrig;
            }
            unset($l);

            ob_end_clean();
            echo json_encode([
                'success'         => true,
                'factura'         => $factura,
                'lineas'          => $lineas,
                'saldoDisponible' => round($saldoDisponible, 2),
                'totalNCPrevio'   => round($totalNCPrevio, 2),
                'docTotal'        => round($docTotal, 2),
            ]);

        } catch (Exception $e) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (Throwable $e) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX: Buscar Nota de Crédito por número (NCF E34, DocNum o C_DocEntry)
     * Para reversión / anulación con Nota de Débito (E33 / B03)
     * Delegado a NotaDebitoController para separación de responsabilidades.
     */
    public function buscarNotaCredito() {
        require_once __DIR__ . '/NotaDebitoController.php';
        (new NotaDebitoController())->buscarNotaCredito();
    }

    /**
     * AJAX: Buscar artículo del catálogo para agregar a la NC
     */
    public function buscarArticulo() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            $term = trim($_GET['q'] ?? '');
            $lista = intval($_GET['lista'] ?? 1);
            if ($lista <= 0) $lista = 1;
            
            $sql = "SELECT DISTINCT ItemCode, ItemName, PriceBefDi, TaxCode, VatPrcnt, DfltWH, QryGroup1 
                    FROM v_sale_item 
                    WHERE PriceList = ? 
                    AND (ItemName LIKE ? OR ItemCode LIKE ?)
                    AND SellItem = 'Y'
                    ORDER BY ItemName ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$lista, "%$term%", "%$term%"]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ob_end_clean();
            echo json_encode($result);
        } catch (Throwable $e) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Guardar Nota de Crédito
     */
    public function guardar() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        $pdo = null;
        try {
            $database = new Database();
            $pdo = $database->getConnection();

            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                throw new Exception('Datos de entrada inválidos.');
            }

            // ---------- Validaciones de cabecera ----------
            $h = $input['header'] ?? [];
            $lineas = $input['lines'] ?? [];

            $cDocOrigenNum = (int)($h['C_DocOrigenNum'] ?? 0);
            if ($cDocOrigenNum <= 0) {
                throw new Exception('Debe buscar y seleccionar una factura origen.');
            }
            if (empty($lineas)) {
                throw new Exception('Debe incluir al menos un artículo en la Nota de Crédito.');
            }

            // ---------- Obtener factura origen ----------
            $sqlFac = "SELECT C_DocEntry, DocEntry, DocNum, NumAtCard, CardCode, CardName,
                               Address, DocTotal, PaidToDate, VatSum, DiscSum,
                               U_FACNUM, U_FacSerie, U_FacNit, U_FacNom, U_TNegocio,
                               CANCELED, DocStatus
                        FROM POSOINV WHERE C_DocEntry = ?";
            $stmtFac = $pdo->prepare($sqlFac);
            $stmtFac->execute([$cDocOrigenNum]);
            $factura = $stmtFac->fetch(PDO::FETCH_ASSOC);

            if (!$factura) {
                throw new Exception('La factura origen no existe.');
            }
            if ($factura['CANCELED'] === 'Y') {
                throw new Exception('La factura origen está cancelada.');
            }

            // Serie fijada o determinada automáticamente según el NCF de la factura
            $serie = strtoupper(trim($h['U_FacSerie'] ?? ''));
            if (empty($serie)) {
                $ncfOrigDetect = $factura['U_FACNUM'] ?: ($factura['NumAtCard'] ?? '');
                $serie = (stripos($ncfOrigDetect, 'E') === 0) ? 'E34' : 'B04';
            }

            $tipoNC = trim($h['U_TipoNC'] ?? '');
            if (empty($tipoNC)) {
                throw new Exception('El tipo de Nota de Crédito es obligatorio.');
            }

            $motivo = trim($h['Comments'] ?? '');
            if (empty($motivo)) {
                throw new Exception('El motivo de la Nota de Crédito es obligatorio.');
            }

            // ---------- Validar saldo disponible ----------
            $sqlSaldo = "SELECT ISNULL(SUM(DocTotal), 0) FROM POSORIN WHERE C_DocOrigenNum = ? AND ISNULL(CANCELED,'N') <> 'Y'";
            $stmtSaldo = $pdo->prepare($sqlSaldo);
            $stmtSaldo->execute([$cDocOrigenNum]);
            $totalNCPrevio = (float)$stmtSaldo->fetchColumn();

            $docTotal = (float)$factura['DocTotal'];
            $saldoDisponible = $docTotal - $totalNCPrevio;

            // ---------- Calcular totales de las líneas ----------
            $subtotalNeto = 0.0;
            $totalVat     = 0.0;
            $lineasPrep   = [];

            foreach ($lineas as $idx => $l) {
                $qty       = (float)str_replace(',', '', (string)($l['Quantity'] ?? 0));
                $price     = (float)str_replace(',', '', (string)($l['Price'] ?? 0));
                $discPrcnt = (float)str_replace(',', '', (string)($l['DiscPrcnt'] ?? 0));
                $vatPrcnt  = (float)str_replace(',', '', (string)($l['VatPrcnt'] ?? 0));
                $lineNum   = (int)($l['LineNum'] ?? $idx);
                $baseEntry = (int)($l['BaseEntry'] ?? $cDocOrigenNum);

                if ($qty <= 0) {
                    throw new Exception("La cantidad de la línea '{$l['Dscription']}' debe ser mayor a cero.");
                }

                // Validar que no exceda cantidad disponible de la factura
                if (isset($l['QtyDisponible'])) {
                    $qtyDisp = (float)str_replace(',', '', (string)$l['QtyDisponible']);
                    if ($qty > $qtyDisp + 0.001) {
                        throw new Exception("La cantidad ({$qty}) para '{$l['Dscription']}' excede la cantidad disponible ({$qtyDisp}).");
                    }
                }

                $descMonto  = ($qty * $price) * ($discPrcnt / 100);
                $lineNeto   = ($qty * $price) - $descMonto;
                $lineVat    = $lineNeto * ($vatPrcnt / 100);
                $lineTotal  = $lineNeto + $lineVat;

                $subtotalNeto += $lineNeto;
                $totalVat     += $lineVat;

                $lineasPrep[] = [
                    'idx'        => $idx,
                    'lineNum'    => $lineNum,
                    'baseEntry'  => $baseEntry,
                    'baseLine'   => (int)($l['BaseLine'] ?? $lineNum),
                    'itemCode'   => trim($l['ItemCode'] ?? ''),
                    'dscription' => substr(trim($l['Dscription'] ?? ''), 0, 100),
                    'whsCode'    => trim($l['WhsCode'] ?? '01'),
                    'taxCode'    => trim($l['TaxCode'] ?? ''),
                    'qty'        => $qty,
                    'price'      => $price,
                    'priceBefDi' => (float)str_replace(',', '', (string)($l['PriceBefDi'] ?? $l['Price'] ?? 0)),
                    'discPrcnt'  => $discPrcnt,
                    'lineNeto'   => round($lineNeto, 6),
                    'lineVat'    => round($lineVat, 6),
                    'vatPrcnt'   => $vatPrcnt,
                    'lineTotal'  => round($lineTotal, 6),
                ];
            }

            $ncTotal = $subtotalNeto + $totalVat;
            $ncTotal = round($ncTotal, 2);

            // Validar que el total de la NC no exceda el saldo disponible
            if ($ncTotal > $saldoDisponible + 0.01) {
                throw new Exception(
                    sprintf(
                        'El monto de la Nota de Crédito (RD$ %s) excede el saldo disponible de la factura (RD$ %s).',
                        number_format($ncTotal, 2),
                        number_format($saldoDisponible, 2)
                    )
                );
            }

            // Iniciar Transacción
            $pdo->beginTransaction();

            // ---------- Obtener longitud de la serie en serie_ncf ----------
            $stmtLong = $pdo->prepare("SELECT longitud FROM serie_ncf WHERE UPPER(serie) = ?");
            $stmtLong->execute([$serie]);
            $longitudSerie = (int)$stmtLong->fetchColumn();
            if ($longitudSerie <= 0) {
                $longitudSerie = (stripos($serie, 'E') === 0) ? 10 : 8;
            }

            // ---------- Obtener y reservar NCF atómicamente ----------
            $sqlNCF = "UPDATE carga_ncf
                       SET contador = contador + 1
                       OUTPUT Inserted.id_carga, Inserted.serie, Inserted.contador, Inserted.vencimiento
                       WHERE UPPER(serie) = ? AND contador < final
                         AND id_carga = (SELECT MIN(id_carga) FROM carga_ncf WHERE UPPER(serie) = ? AND contador < final)";
            $stmtNCF = $pdo->prepare($sqlNCF);
            $stmtNCF->execute([$serie, $serie]);
            $ncfRow = $stmtNCF->fetch(PDO::FETCH_ASSOC);

            if (!$ncfRow) {
                throw new Exception("No hay comprobantes fiscales disponibles para la serie '$serie'. Verifique o cargue un nuevo rango en el módulo NCF.");
            }

            // Verificar vencimiento NCF (comparación estricta de fecha)
            if (!empty($ncfRow['vencimiento'])) {
                $vencTs = strtotime($ncfRow['vencimiento']);
                if ($vencTs && date('Y-m-d', $vencTs) < date('Y-m-d')) {
                    throw new Exception("Los comprobantes de la serie '$serie' están vencidos (vencimiento: " . date('d/m/Y', $vencTs) . "). Cargue una nueva tanda en el módulo NCF.");
                }
            }

            // Formatear NCF en MAYÚSCULAS con la longitud correspondiente
            $serieMayus = strtoupper(trim($ncfRow['serie'] ?: $serie));
            $numNCF = $serieMayus . str_pad($ncfRow['contador'], $longitudSerie, '0', STR_PAD_LEFT);

            // ---------- Obtener DocEntry SAP (siguiente en POSORIN) ----------
            $sqlNextDoc = "SELECT ISNULL(MAX(DocEntry), 0) + 1 FROM POSORIN";
            $nextDocEntry = (int)$pdo->query($sqlNextDoc)->fetchColumn();

            // ---------- Generar Secuencia NumAtCard (N0000001 - 8 caracteres) ----------
            $serieDocNC = 'N';
            $sqlSecNC = "MERGE secuencia WITH (SERIALIZABLE) AS T 
                         USING (VALUES (?)) AS U (tipo) ON U.tipo = T.tipo 
                         WHEN MATCHED THEN UPDATE SET T.secuencia = secuencia + 1 
                         WHEN NOT MATCHED THEN INSERT (secuencia, tipo) VALUES (1, U.tipo) 
                         OUTPUT inserted.secuencia;";
            $stmtSecNC = $pdo->prepare($sqlSecNC);
            $stmtSecNC->execute([$serieDocNC]);
            $secDocNC = $stmtSecNC->fetchColumn();
            if (!$secDocNC) throw new Exception("No se generó la secuencia interna para la Nota de Crédito.");
            $numAtCardNC = substr($serieDocNC . str_pad($secDocNC, 7, '0', STR_PAD_LEFT), 0, 20);

            // ---------- Insertar cabecera POSORIN ----------
            $cardCode   = substr($factura['CardCode'], 0, 15);
            $cardName   = substr($factura['CardName'] ?? '', 0, 100);
            $address    = substr($factura['Address'] ?? '', 0, 254);
            $docDate    = trim($h['DocDate'] ?? date('Y-m-d'));
            $tNegocio   = $factura['U_TNegocio'] ?? Helper::determinarTipoNegocio($factura['U_FacNit'] ?? '');
            $facNit     = substr(Helper::limpiarNit($factura['U_FacNit'] ?? ''), 0, 20);
            $facNom     = substr($factura['U_FacNom'] ?? '', 0, 100);
            $terminal   = substr($_SERVER['COMPUTERNAME'] ?? 'WEB', 0, 20);
            $usuario    = substr($_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'ADMIN', 0, 20);
            $ncfOrigen  = strtoupper(substr($factura['U_FACNUM'] ?? $factura['NumAtCard'] ?? '', 0, 19));

            $sqlHeader = "INSERT INTO POSORIN (
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
                            'N', ?, NULL, 'I', 'N', 'N', 'N',
                            'O', 'R', 'N', '14',
                            ?, ?, ?, ?, ?,
                            ?, 0, ?, 0, 0,
                            'DOP', 1, ?, ?,
                            ?, ?, 0,
                            'I', 1, 1,
                            ?, ?, ?,
                            ?, ?, ?, ?,
                            '', ?, ?, ?,
                            'N', ?,
                            ?, GETDATE(), 'O',
                            'INVOICE', ?, ?,
                            ?
                          )";

            $stmtH = $pdo->prepare($sqlHeader);
            $stmtH->execute([
                $nextDocEntry,                          // DocEntry
                $docDate,                               // DocDate
                $docDate,                               // DocDueDate
                $cardCode,                              // CardCode
                $cardName,                              // CardName
                $address,                               // Address
                $numAtCardNC,                           // NumAtCard (Secuencia interna N0000001)
                round($totalVat, 6),                    // VatSum
                round($ncTotal, 6),                     // DocTotal
                round($ncTotal, 6),                     // DocTotalFC
                $motivo,                                // Comments
                'Nota de Crédito - ' . $numNCF,        // JrnlMemo
                $facNit,                                // U_FacNit
                $facNom,                                // U_FacNom
                $docDate,                               // U_FacFecha
                $serieMayus,                            // U_FacSerie
                $tipoNC,                                // U_TipoNC
                $numNCF,                                // U_FACNUM (NCF DGII)
                $ncfOrigen,                             // U_NCO (NCF factura original)
                $usuario,                               // U_user
                $terminal,                              // U_terminal
                $tNegocio,                              // U_TNegocio
                (int)$secDocNC,                         // U_SCIADocNum
                round($subtotalNeto, 6),               // C_DocNet
                $cDocOrigenNum,                         // C_DocOrigenNum
                $factura['NumAtCard'] ?? '',            // C_FromNumAtCard
                $cDocOrigenNum,                         // C_FromC_DocEntry
            ]);

            // Obtener C_DocEntry insertado
            $ncDocEntry = (int)$pdo->query('SELECT SCOPE_IDENTITY()')->fetchColumn();
            if (!$ncDocEntry) {
                $ncDocEntry = (int)$pdo->lastInsertId();
            }
            if ($ncDocEntry <= 0) {
                $ncDocEntry = (int)$pdo->query("SELECT ISNULL(MAX(C_DocEntry), 0) FROM POSORIN")->fetchColumn();
            }


            // ---------- Insertar líneas POSRIN1 ----------
            $sqlLine = "INSERT INTO POSRIN1 (
                            C_DocEntry, DocEntry, LineNum, LineStatus,
                            ItemCode, Dscription, Quantity, OpenQty, OpenCreQty,
                            Price, PriceBefDi, DiscPrcnt, LineTotal,
                            VatPrcnt, VatSum, VatGroup,
                            WhsCode, TaxCode, ObjType,
                            BaseType, BaseEntry, BaseLine,
                            BaseRef, DocDate
                        ) VALUES (
                            ?, ?, ?, 'O',
                            ?, ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            ?, ?, '',
                            ?, ?, '14',
                            13, ?, ?,
                            ?, ?
                        )";
            $stmtLine = $pdo->prepare($sqlLine);

            $baseRef = substr($factura['NumAtCard'] ?? '', 0, 16);
            foreach ($lineasPrep as $lp) {
                $stmtLine->execute([
                    $ncDocEntry,          // C_DocEntry
                    $nextDocEntry,        // DocEntry
                    $lp['idx'],           // LineNum
                    $lp['itemCode'],      // ItemCode
                    $lp['dscription'],    // Dscription
                    $lp['qty'],           // Quantity
                    $lp['qty'],           // OpenQty
                    $lp['qty'],           // OpenCreQty
                    $lp['price'],         // Price (Copago efectivamente acreditado)
                    $lp['priceBefDi'],    // PriceBefDi (Precio de lista de origen)
                    $lp['discPrcnt'],     // DiscPrcnt
                    $lp['lineNeto'],      // LineTotal (neto sin vat)
                    $lp['vatPrcnt'],      // VatPrcnt
                    $lp['lineVat'],       // VatSum
                    $lp['whsCode'],       // WhsCode
                    $lp['taxCode'],       // TaxCode
                    $lp['baseEntry'],     // BaseEntry (C_DocEntry de POSOINV)
                    $lp['baseLine'],      // BaseLine
                    $baseRef,             // BaseRef
                    $docDate,             // DocDate
                ]);
            }

            // ---------- Restituir Stock si es Devolución o Afecta Inventario Físico ----------
            $stmtTipoInfo = $pdo->prepare("SELECT Code, Name FROM TIPOS_ANULACION WHERE Code = ?");
            $stmtTipoInfo->execute([$tipoNC]);
            $tipoInfo = $stmtTipoInfo->fetch(PDO::FETCH_ASSOC);
            $nombreTipo = strtoupper($tipoInfo['Name'] ?? '');

            // Identificar tipos de anulación que implican devolución o reingreso físico de mercancía
            $esDevolucionFisica = in_array($tipoNC, ['06', '07', '14', '16', '20', '21', '23', '25', '38']) 
                || (strpos($nombreTipo, 'DEVOLUCION') !== false)
                || (strpos($nombreTipo, 'CAMBIO') !== false)
                || (strpos($nombreTipo, 'PRODUCTO') !== false)
                || (strpos($nombreTipo, 'MERCANCIA') !== false)
                || (strpos($nombreTipo, 'ARTICULO') !== false);

            $afectaInventario = isset($h['afectaInventario']) 
                ? (filter_var($h['afectaInventario'], FILTER_VALIDATE_BOOLEAN) || $h['afectaInventario'] === 'Y' || $h['afectaInventario'] === '1') 
                : $esDevolucionFisica;

            if ($afectaInventario) {
                $stmtCheckInvNC = $pdo->prepare("SELECT InvntItem, DfltWH FROM posOITM WHERE ItemCode = ?");
                $stmtUpdOITM_NC = $pdo->prepare("UPDATE posOITM SET OnHand = ISNULL(OnHand, 0) + ? WHERE ItemCode = ? AND InvntItem = 'Y'");
                $stmtCheckOITW_NC = $pdo->prepare("SELECT COUNT(*) FROM posOITW WHERE ItemCode = ? AND WhsCode = ?");
                $stmtUpdOITW_NC = $pdo->prepare("UPDATE posOITW SET OnHand = ISNULL(OnHand, 0) + ? WHERE ItemCode = ? AND WhsCode = ?");
                $stmtInsOITW_NC = $pdo->prepare("INSERT INTO posOITW (ItemCode, WhsCode, OnHand) VALUES (?, ?, ?)");

                $stmtFirstWhsNC = $pdo->query("SELECT TOP 1 WhsCode FROM posOWHS ORDER BY WhsCode ASC");
                $fallbackWhsNC = $stmtFirstWhsNC ? $stmtFirstWhsNC->fetchColumn() : '01';

                foreach ($lineasPrep as $lp) {
                    $itemCode = $lp['itemCode'];
                    $qty = floatval($lp['qty'] ?? 0);
                    $whsCode = $lp['whsCode'];

                    if (!empty($itemCode) && $qty > 0) {
                        $stmtCheckInvNC->execute([$itemCode]);
                        $itemInfo = $stmtCheckInvNC->fetch(PDO::FETCH_ASSOC);
                        if ($itemInfo && ($itemInfo['InvntItem'] ?? '') === 'Y') {
                            $stmtUpdOITM_NC->execute([$qty, $itemCode]);
                            $targetWhs = !empty($whsCode) ? $whsCode : (!empty($itemInfo['DfltWH']) ? $itemInfo['DfltWH'] : $fallbackWhsNC);
                            if (!empty($targetWhs)) {
                                $stmtCheckOITW_NC->execute([$itemCode, $targetWhs]);
                                if ($stmtCheckOITW_NC->fetchColumn() > 0) {
                                    $stmtUpdOITW_NC->execute([$qty, $itemCode, $targetWhs]);
                                } else {
                                    $stmtInsOITW_NC->execute([$itemCode, $targetWhs, $qty]);
                                }
                            }
                        }
                    }
                }
            }

            // ---------- Devolución / Egreso de Efectivo en Caja (Evaluación de parámetro) ----------
            $devolverEfectivo = isset($h['devolver_efectivo']) 
                ? (filter_var($h['devolver_efectivo'], FILTER_VALIDATE_BOOLEAN) || $h['devolver_efectivo'] === 'Y' || $h['devolver_efectivo'] === '1' || $h['devolver_efectivo'] === 1) 
                : false;

            // ---------- Actualizar Factura Origen en POSOINV ----------
            // Si el saldo restante queda en 0 (<= 0.01), la factura queda saldada/cerrada (C_DocStatus = 'C')
            $nuevoSaldo = $saldoDisponible - $ncTotal;
            $esAnulacionTotal = ($nuevoSaldo <= 0.01);
            $nuevoStatus = $esAnulacionTotal ? 'C' : 'O';
            $nuevoCanceled = $esAnulacionTotal ? 'Y' : 'N';

            // Tratamiento contable y financiero de PaidToDate (Monto efectivamente cobrado):
            $facDocTotal = (float)($factura['DocTotal'] ?? 0);
            $facPaidToDate = (float)($factura['PaidToDate'] ?? 0);
            $esFacturaPagada = ($facPaidToDate >= $facDocTotal - 0.01 && $facDocTotal > 0);

            if ($esFacturaPagada) {
                if ($devolverEfectivo) {
                    $nuevoPaidToDate = max(0.0, $facPaidToDate - $ncTotal);
                } else {
                    $nuevoPaidToDate = $facDocTotal;
                }
            } else {
                $nuevoPaidToDate = min($facDocTotal, $facPaidToDate + $ncTotal);
            }

            $sqlUpdFac = "UPDATE POSOINV
                          SET PaidToDate  = ?,
                              C_DocStatus = ?,
                              CANCELED    = CASE WHEN ? = 'Y' THEN 'Y' ELSE CANCELED END,
                              C_Updated   = GETDATE(),
                              U_user      = ?
                          WHERE C_DocEntry = ?";
            $stmtUpd = $pdo->prepare($sqlUpdFac);
            $stmtUpd->execute([
                round($nuevoPaidToDate, 6),
                $nuevoStatus,
                $nuevoCanceled,
                $usuario,
                $cDocOrigenNum
            ]);

            // ---------- Actualizar AprobacionARS y Registrar Trazabilidad ARS (Si aplica) ----------
            try {
                $stmtUpdARS = $pdo->prepare("UPDATE AprobacionARS SET Status = 'R', Observaciones = ISNULL(Observaciones, '') + ' [NC Emitida: ' + ? + ']' WHERE DocEntry_FacturaARS = ?");
                $stmtUpdARS->execute([$numNCF, $cDocOrigenNum]);

                $stmtInsTrace = $pdo->prepare("INSERT INTO trazabilidad_reclamacion_ars (
                                                DocEntry_Factura, NumAtCard, NCF_Factura, CardCode_ARS, CardName_ARS,
                                                MontoFactura, Estado, FechaEstado, Motivo, DocEntry_NC, NCF_NC, Usuario, Observaciones
                                              ) VALUES (?, ?, ?, ?, ?, ?, 'NOTA_CREDITO', GETDATE(), ?, ?, ?, ?, ?)");
                $stmtInsTrace->execute([
                    $cDocOrigenNum,
                    $factura['NumAtCard'] ?? '',
                    $factura['U_FACNUM'] ?? '',
                    $cardCode,
                    $cardName,
                    $factura['DocTotal'],
                    $motivo,
                    $ncDocEntry,
                    $numNCF,
                    $usuario,
                    'Nota de Crédito emitida exitosamente (' . $numNCF . ')'
                ]);
            } catch (Throwable $eARS) {
                // Registro silencioso si la factura no pertenece a reclamos ARS
            }

            // ---------- Devolución / Egreso de Efectivo en Caja (Si fue solicitado) ----------
            $devolverEfectivo = isset($h['devolver_efectivo']) 
                ? (filter_var($h['devolver_efectivo'], FILTER_VALIDATE_BOOLEAN) || $h['devolver_efectivo'] === 'Y' || $h['devolver_efectivo'] === '1' || $h['devolver_efectivo'] === 1) 
                : false;

            if ($devolverEfectivo && $ncTotal > 0) {
                $caja_id = $_SESSION['caja_id'] ?? 0;
                $id_diario_caja = $_SESSION['caja_apertura_id'] ?? 0;
                if ($id_diario_caja <= 0) {
                    $stmtBox = $pdo->prepare("SELECT TOP 1 id_diario_caja FROM diario_caja WHERE (id_caja = ? OR ? = 0) AND fecha_cierre IS NULL ORDER BY id_diario_caja DESC");
                    $stmtBox->execute([$caja_id, $caja_id]);
                    $id_diario_caja = (int)$stmtBox->fetchColumn();
                }
                if ($id_diario_caja <= 0) {
                    $stmtBoxAny = $pdo->query("SELECT TOP 1 id_diario_caja FROM diario_caja WHERE fecha_cierre IS NULL ORDER BY id_diario_caja DESC");
                    $id_diario_caja = (int)$stmtBoxAny->fetchColumn();
                }

                if ($id_diario_caja > 0) {
                    $montoNegativo = -abs($ncTotal);
                    
                    // 1. Insertar en retiro_caja
                    $sqlRetiro = "INSERT INTO retiro_caja (id_diario_caja, id_concepto_retiro, id_medio_retiro, monto, referencia, quien_entrega, quien_recibe, comentario) 
                                  OUTPUT INSERTED.id_retiro_caja 
                                  VALUES (?, 1, 1, ?, ?, ?, ?, ?)";
                    $stmtRetiro = $pdo->prepare($sqlRetiro);
                    $stmtRetiro->execute([
                        $id_diario_caja,
                        $montoNegativo,
                        substr($numNCF, 0, 50),
                        substr($usuario, 0, 100),
                        substr($cardName, 0, 100),
                        'Devolución NC: ' . $numNCF . ' (Fact. ' . ($factura['NumAtCard'] ?? '') . ')'
                    ]);
                    $idRetiro = $stmtRetiro->fetchColumn();

                    // 2. Insertar en diario_caja_det (id_tipo_movimiento = 7 - Retiro/Egreso)
                    $sqlDet = "INSERT INTO diario_caja_det (id_diario_caja, id_tipo_movimiento, id_documento, monto_factura, monto_caja, comentario, U_SCIASerie, U_SCIADocNum) 
                               VALUES (?, 7, ?, 0, ?, ?, ?, ?)";
                    $pdo->prepare($sqlDet)->execute([
                        $id_diario_caja,
                        $idRetiro,
                        $montoNegativo,
                        'Devolución NC: ' . $numNCF,
                        substr($factura['U_SCIASerie'] ?? '', 0, 20),
                        $factura['U_SCIADocNum'] ?? 0
                    ]);
                }
            }

            // ---------- Registrar en ecf_xml_invoice para DGII ----------
            if (!empty($numNCF)) {
                require_once __DIR__ . '/../config/EcfHelper.php';
                EcfHelper::registrarEcfXml(
                    $pdo,
                    $ncDocEntry,
                    $numAtCardNC,
                    $serieMayus,
                    $numNCF,
                    $ncfRow['vencimiento'] ?? null,
                    $facNit,
                    0,
                    $docDate,
                    'CREDIT_NOTE'
                );
            }

            // Confirmar Transacción
            $pdo->commit();

            ob_end_clean();
            echo json_encode([
                'success' => true,
                'msg'     => 'Nota de Crédito emitida exitosamente.',
                'NCF'     => $numNCF,
                'docEntry'=> $ncDocEntry,
            ]);

        } catch (Exception $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
        } catch (Throwable $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'msg' => 'Error interno: ' . $e->getMessage()]);
        }
    }


    /**
     * AJAX: Guardar Nota de Crédito en Lote para Facturas de Reclamaciones ARS
     * Emite una NC (E34) para cada factura del lote, actualiza POSOINV, AprobacionARS y registra trazabilidad
     */
    public function guardarLoteARS() {
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
                throw new Exception('Datos de entrada inválidos.');
            }

            $cardCodeARS = trim($input['CardCode_ARS'] ?? '');
            $tipoNC      = trim($input['U_TipoNC'] ?? '01');
            $motivo      = trim($input['Comments'] ?? 'Nota de Crédito por Rechazo de Reclamación ARS');
            $docDate     = trim($input['DocDate'] ?? date('Y-m-d'));
            $serie       = trim($input['U_FacSerie'] ?? 'E34');
            $usuario     = substr($_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'ADMIN', 0, 20);
            $terminal    = substr($_SERVER['COMPUTERNAME'] ?? 'WEB', 0, 20);

            // Lista de facturas recibidas (puede ser array de IDs o array de objetos { C_DocEntry, lines })
            $facturasInput = $input['facturas'] ?? $input['idsFacturas'] ?? [];
            if (empty($facturasInput) || !is_array($facturasInput)) {
                throw new Exception('Debe seleccionar al menos una factura rechazada para aplicar la Nota de Crédito.');
            }

            // Normalizar lista de facturas
            $listaFacturas = [];
            foreach ($facturasInput as $item) {
                if (is_numeric($item)) {
                    $listaFacturas[] = ['C_DocEntry' => (int)$item, 'lines' => []];
                } elseif (is_array($item) && !empty($item['C_DocEntry'])) {
                    $listaFacturas[] = [
                        'C_DocEntry' => (int)$item['C_DocEntry'],
                        'lines'      => $item['lines'] ?? []
                    ];
                }
            }

            if (empty($listaFacturas)) {
                throw new Exception('No se especificaron facturas válidas.');
            }

            // Validar que todas las facturas pertenezcan a la misma ARS y no estén canceladas
            $stmtCheckFac = $pdo->prepare("SELECT C_DocEntry, DocEntry, DocNum, NumAtCard, CardCode, CardName, Address, DocTotal, VatSum, DiscSum, U_FACNUM, U_FacSerie, U_FacNit, U_FacNom, U_TNegocio, CANCELED, DocStatus FROM POSOINV WHERE C_DocEntry = ?");
            $facturasValidadas = [];
            $primerCardCode = $cardCodeARS;

            foreach ($listaFacturas as $fItem) {
                $cDocEntry = $fItem['C_DocEntry'];
                $stmtCheckFac->execute([$cDocEntry]);
                $fac = $stmtCheckFac->fetch(PDO::FETCH_ASSOC);

                if (!$fac) {
                    throw new Exception("La factura con ID #{$cDocEntry} no fue encontrada en la base de datos.");
                }
                if ($fac['CANCELED'] === 'Y') {
                    throw new Exception("La factura {$fac['NumAtCard']} (NCF: {$fac['U_FACNUM']}) ya se encuentra CANCELADA.");
                }

                // Si no se pasó CardCode inicial, tomar el de la primera factura
                if (empty($primerCardCode)) {
                    $primerCardCode = $fac['CardCode'];
                } elseif ($primerCardCode !== $fac['CardCode']) {
                    throw new Exception("Incompatibilidad de Aseguradora: La factura {$fac['NumAtCard']} pertenece a '{$fac['CardName']}' ({$fac['CardCode']}) y no puede procesarse junto con las facturas de '{$primerCardCode}'. Todas las facturas de una Nota de Crédito en lote deben pertenecer a la misma ARS.");
                }

                $facturasValidadas[] = [
                    'datos' => $fac,
                    'lines' => $fItem['lines']
                ];
            }

            // Longitud de serie en serie_ncf
            $stmtLong = $pdo->prepare("SELECT longitud FROM serie_ncf WHERE UPPER(serie) = ?");
            $stmtLong->execute([$serie]);
            $longitudSerie = (int)$stmtLong->fetchColumn();
            if ($longitudSerie <= 0) $longitudSerie = 10;

            // Iniciar Transacción
            $pdo->beginTransaction();

            $notasCreditoGeneradas = [];

            // Preparar statements reutilizables
            $sqlNCF = "UPDATE carga_ncf
                       SET contador = contador + 1
                       OUTPUT Inserted.id_carga, Inserted.serie, Inserted.contador, Inserted.vencimiento
                       WHERE UPPER(serie) = ? AND contador < final
                         AND id_carga = (SELECT MIN(id_carga) FROM carga_ncf WHERE UPPER(serie) = ? AND contador < final)";
            $stmtNCF = $pdo->prepare($sqlNCF);

            $sqlSecNC = "MERGE secuencia WITH (SERIALIZABLE) AS T 
                         USING (VALUES (?)) AS U (tipo) ON U.tipo = T.tipo 
                         WHEN MATCHED THEN UPDATE SET T.secuencia = secuencia + 1 
                         WHEN NOT MATCHED THEN INSERT (secuencia, tipo) VALUES (1, U.tipo) 
                         OUTPUT inserted.secuencia;";
            $stmtSecNC = $pdo->prepare($sqlSecNC);

            $sqlHeader = "INSERT INTO POSORIN (
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
                            'N', ?, NULL, 'I', 'N', 'N', 'N',
                            'O', 'R', 'N', '14',
                            ?, ?, ?, ?, ?,
                            ?, 0, ?, 0, 0,
                            'DOP', 1, ?, ?,
                            ?, ?, 0,
                            'I', 1, 1,
                            ?, ?, ?,
                            ?, ?, ?, ?,
                            '', ?, ?, ?,
                            'N', ?,
                            ?, GETDATE(), 'O',
                            'INVOICE', ?, ?,
                            ?
                          )";
            $stmtHeader = $pdo->prepare($sqlHeader);

            $sqlLine = "INSERT INTO POSRIN1 (
                            C_DocEntry, DocEntry, LineNum, LineStatus,
                            ItemCode, Dscription, Quantity, OpenQty, OpenCreQty,
                            Price, PriceBefDi, DiscPrcnt, LineTotal,
                            VatPrcnt, VatSum, VatGroup,
                            WhsCode, TaxCode, ObjType,
                            BaseType, BaseEntry, BaseLine,
                            BaseRef, DocDate
                        ) VALUES (
                            ?, ?, ?, 'O',
                            ?, ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            ?, ?, '',
                            ?, ?, '14',
                            13, ?, ?,
                            ?, ?
                        )";
            $stmtLine = $pdo->prepare($sqlLine);

            $stmtGetLines = $pdo->prepare("SELECT LineNum, ItemCode, Dscription, Quantity, Price, PriceBefDi, DiscPrcnt, LineTotal, VatPrcnt, VatSum, WhsCode, TaxCode FROM POSINV1 WHERE C_DocEntry = ? ORDER BY LineNum ASC");

            $stmtUpdPOSOINV = $pdo->prepare("UPDATE POSOINV SET PaidToDate = DocTotal, C_DocStatus = 'C', CANCELED = 'Y', C_Updated = GETDATE(), U_user = ? WHERE C_DocEntry = ?");

            $stmtUpdARS = $pdo->prepare("UPDATE AprobacionARS SET Status = 'R', Observaciones = ISNULL(Observaciones, '') + ' [NC Emitida: ' + ? + ']' WHERE DocEntry_FacturaARS = ?");

            $stmtInsTrace = $pdo->prepare("INSERT INTO trazabilidad_reclamacion_ars (
                                            DocEntry_Factura, NumAtCard, NCF_Factura, CardCode_ARS, CardName_ARS,
                                            MontoFactura, Estado, FechaEstado, Motivo, DocEntry_NC, NCF_NC, Usuario, Observaciones
                                          ) VALUES (?, ?, ?, ?, ?, ?, 'NOTA_CREDITO', GETDATE(), ?, ?, ?, ?, ?)");

            foreach ($facturasValidadas as $fv) {
                $fac = $fv['datos'];
                $cDocEntry = (int)$fac['C_DocEntry'];

                // Determinar serie para esta factura (E34 para electrónicas, B04 para tradicionales)
                $ncfFac = trim($fac['U_FACNUM'] ?: $fac['NumAtCard']);
                $serieFac = (stripos($ncfFac, 'E') === 0) ? 'E34' : 'B04';

                // Obtener longitud de la serie en serie_ncf
                $stmtLong = $pdo->prepare("SELECT longitud FROM serie_ncf WHERE UPPER(serie) = ?");
                $stmtLong->execute([$serieFac]);
                $longitudSerie = (int)$stmtLong->fetchColumn();
                if ($longitudSerie <= 0) $longitudSerie = (stripos($serieFac, 'E') === 0) ? 10 : 8;

                // 1. Obtener y reservar NCF
                $stmtNCF->execute([$serieFac, $serieFac]);
                $ncfRow = $stmtNCF->fetch(PDO::FETCH_ASSOC);
                if (!$ncfRow) {
                    throw new Exception("No hay comprobantes fiscales disponibles para la serie '$serieFac'. Verifique o cargue un nuevo rango en el módulo NCF.");
                }
                $serieMayusFac = strtoupper(trim($ncfRow['serie'] ?: $serieFac));
                $numNCF = $serieMayusFac . str_pad($ncfRow['contador'], $longitudSerie, '0', STR_PAD_LEFT);

                // 2. Obtener siguiente DocEntry para POSORIN
                $sqlNextDoc = "SELECT ISNULL(MAX(DocEntry), 0) + 1 FROM POSORIN";
                $nextDocEntry = (int)$pdo->query($sqlNextDoc)->fetchColumn();

                // 3. Obtener líneas de la factura
                $lineasFac = $fv['lines'];
                if (empty($lineasFac)) {
                    $stmtGetLines->execute([$cDocEntry]);
                    $lineasFac = $stmtGetLines->fetchAll(PDO::FETCH_ASSOC);
                }

                $subtotalNeto = 0.0;
                $totalVat = 0.0;
                $lineasPrep = [];

                foreach ($lineasFac as $idx => $l) {
                    $qty       = (float)str_replace(',', '', (string)($l['Quantity'] ?? 1));
                    $price     = (float)str_replace(',', '', (string)($l['Price'] ?? 0));
                    $discPrcnt = (float)str_replace(',', '', (string)($l['DiscPrcnt'] ?? 0));
                    $vatPrcnt  = (float)str_replace(',', '', (string)($l['VatPrcnt'] ?? 0));
                    $lineNum   = (int)($l['LineNum'] ?? $idx);

                    $descMonto  = ($qty * $price) * ($discPrcnt / 100);
                    $lineNeto   = ($qty * $price) - $descMonto;
                    $lineVat    = $lineNeto * ($vatPrcnt / 100);
                    $lineTotal  = $lineNeto + $lineVat;

                    $subtotalNeto += $lineNeto;
                    $totalVat     += $lineVat;

                    $lineasPrep[] = [
                        'lineNum'    => $lineNum,
                        'itemCode'   => trim($l['ItemCode'] ?? ''),
                        'dscription' => substr(trim($l['Dscription'] ?? ''), 0, 100),
                        'whsCode'    => trim($l['WhsCode'] ?? '01'),
                        'taxCode'    => trim($l['TaxCode'] ?? ''),
                        'qty'        => $qty,
                        'price'      => $price,
                        'discPrcnt'  => $discPrcnt,
                        'lineNeto'   => round($lineNeto, 6),
                        'lineVat'    => round($lineVat, 6),
                        'vatPrcnt'   => $vatPrcnt,
                        'lineTotal'  => round($lineTotal, 6),
                        'baseLine'   => (int)($l['BaseLine'] ?? $lineNum),
                    ];
                }

                $ncTotal = round($subtotalNeto + $totalVat, 2);
                if ($ncTotal <= 0) {
                    $ncTotal = (float)$fac['DocTotal'];
                }

                // 4. Insertar Cabecera POSORIN
                $stmtSecNC->execute(['N']);
                $secDocNC = $stmtSecNC->fetchColumn();
                $numAtCardNC = substr('N' . str_pad($secDocNC, 7, '0', STR_PAD_LEFT), 0, 20); // N0000001 (8 caracteres)

                $cardCode   = substr($fac['CardCode'], 0, 15);
                $cardName   = substr($fac['CardName'] ?? '', 0, 100);
                $address    = substr($fac['Address'] ?? '', 0, 254);
                $tNegocioFac= $fac['U_TNegocio'] ?? Helper::determinarTipoNegocio($fac['U_FacNit'] ?? '');
                $facNit     = substr(Helper::limpiarNit($fac['U_FacNit'] ?? ''), 0, 20);
                $facNom     = substr($fac['U_FacNom'] ?? '', 0, 100);
                $ncfOrigen  = strtoupper(substr($fac['U_FACNUM'] ?? $fac['NumAtCard'] ?? '', 0, 19));

                $stmtHeader->execute([
                    $nextDocEntry,                          // DocEntry
                    $docDate,                               // DocDate
                    $docDate,                               // DocDueDate
                    $cardCode,                              // CardCode
                    $cardName,                              // CardName
                    $address,                               // Address
                    $numAtCardNC,                           // NumAtCard (Secuencia interna N0000001)
                    round($totalVat, 6),                    // VatSum
                    round($ncTotal, 6),                     // DocTotal
                    round($ncTotal, 6),                     // DocTotalFC
                    $motivo,                                // Comments
                    'Nota de Crédito ARS - ' . $numNCF,     // JrnlMemo
                    $facNit,                                // U_FacNit
                    $facNom,                                // U_FacNom
                    $docDate,                               // U_FacFecha
                    $serieMayusFac,                         // U_FacSerie (E34 o B04)
                    $tipoNC,                                // U_TipoNC
                    $numNCF,                                // U_FACNUM
                    $ncfOrigen,                             // U_NCO
                    $usuario,                               // U_user
                    $terminal,                              // U_terminal
                    $tNegocioFac,                           // U_TNegocio
                    (int)$secDocNC,                         // U_SCIADocNum
                    round($subtotalNeto, 6),                // C_DocNet
                    $cDocEntry,                             // C_DocOrigenNum
                    $fac['NumAtCard'] ?? '',                // C_FromNumAtCard
                    $cDocEntry                              // C_FromC_DocEntry
                ]);

                $ncDocEntry = (int)$pdo->query('SELECT SCOPE_IDENTITY()')->fetchColumn();
                if (!$ncDocEntry) {
                    $ncDocEntry = (int)$pdo->lastInsertId();
                }
                if ($ncDocEntry <= 0) {
                    $ncDocEntry = (int)$pdo->query("SELECT ISNULL(MAX(C_DocEntry), 0) FROM POSORIN")->fetchColumn();
                }

                // 5. Insertar Líneas POSRIN1
                $baseRef = substr($fac['NumAtCard'] ?? '', 0, 16);
                foreach ($lineasPrep as $lp) {
                    $stmtLine->execute([
                        $ncDocEntry,          // C_DocEntry
                        $nextDocEntry,        // DocEntry
                        $lp['lineNum'],       // LineNum
                        $lp['itemCode'],      // ItemCode
                        $lp['dscription'],    // Dscription
                        $lp['qty'],           // Quantity
                        $lp['qty'],           // OpenQty
                        $lp['qty'],           // OpenCreQty
                        $lp['price'],         // Price
                        $lp['price'],         // PriceBefDi
                        $lp['discPrcnt'],     // DiscPrcnt
                        $lp['lineNeto'],      // LineTotal
                        $lp['vatPrcnt'],      // VatPrcnt
                        $lp['lineVat'],       // VatSum
                        $lp['whsCode'],       // WhsCode
                        $lp['taxCode'],       // TaxCode
                        $cDocEntry,           // BaseEntry (POSOINV)
                        $lp['baseLine'],      // BaseLine
                        $baseRef,             // BaseRef
                        $docDate              // DocDate
                    ]);
                }

                // 6. Actualizar Factura POSOINV
                $stmtUpdPOSOINV->execute([$usuario, $cDocEntry]);

                // 7. Actualizar AprobacionARS
                $stmtUpdARS->execute([$numNCF, $cDocEntry]);

                // 8. Registrar Trazabilidad
                $stmtInsTrace->execute([
                    $cDocEntry,
                    $fac['NumAtCard'],
                    $fac['U_FACNUM'],
                    $cardCode,
                    $cardName,
                    $fac['DocTotal'],
                    $motivo,
                    $ncDocEntry,
                    $numNCF,
                    $usuario,
                    'Nota de Crédito en Lote ARS por motivo de rechazo'
                ]);

                // 9. Registrar en ecf_xml_invoice para transmisión DGII
                if (!empty($numNCF)) {
                    require_once __DIR__ . '/../config/EcfHelper.php';
                    EcfHelper::registrarEcfXml(
                        $pdo,
                        $ncDocEntry,
                        $numAtCardNC,
                        $serieMayusFac,
                        $numNCF,
                        $ncfRow['vencimiento'] ?? null,
                        $facNit,
                        0,
                        $docDate,
                        'CREDIT_NOTE'
                    );
                }

                $notasCreditoGeneradas[] = [
                    'C_DocEntry_Fac' => $cDocEntry,
                    'NumAtCard_Fac'  => $fac['NumAtCard'],
                    'NCF_Fac'        => $fac['U_FACNUM'],
                    'DocEntry_NC'    => $ncDocEntry,
                    'NCF_NC'         => $numNCF,
                    'Monto'          => $ncTotal
                ];
            }

            // Confirmar Transacción
            $pdo->commit();

            ob_end_clean();
            echo json_encode([
                'success' => true,
                'msg'     => "Se emitieron exitosamente " . count($notasCreditoGeneradas) . " Nota(s) de Crédito electrónica(s).",
                'count'   => count($notasCreditoGeneradas),
                'notas'   => $notasCreditoGeneradas
            ]);
            exit();

        } catch (Exception $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
            exit();
        } catch (Throwable $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'msg' => 'Error interno: ' . $e->getMessage()]);
            exit();
        }
    }

    /**
     * AJAX: Guardar Nota de Débito (E33 / B03) aplicada a una Factura de Venta (POSOINV)
     * Delegado a NotaDebitoController para separación de responsabilidades.
     */
    public function guardarNotaDebito() {
        require_once __DIR__ . '/NotaDebitoController.php';
        (new NotaDebitoController())->guardarNotaDebito();
    }

    /**
     * Pantalla de consulta e historial de Notas de Crédito y Débito emitidas
     */
    public function consulta() {
        $database = new Database();
        $pdo = $database->getConnection();



        // 2. Tipos de Anulación para filtros
        $tiposNC = [];
        try {
            $stmtTipos = $pdo->query("SELECT Code as code, Code, Name FROM TIPOS_ANULACION ORDER BY Name ASC");
            if ($stmtTipos) {
                $tiposNC = $stmtTipos->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $e) {
            $tiposNC = [];
        }

        // 3. Listado de Notas de Crédito
        $sqlNC = "SELECT 
                    nc.C_DocEntry,
                    nc.DocEntry,
                    nc.DocNum,
                    nc.DocDate,
                    FORMAT(nc.DocDate, 'dd/MM/yyyy') AS DocDateFmt,
                    FORMAT(nc.C_Inserted, 'dd/MM/yyyy hh:mm tt') AS C_InsertedFmt,
                    nc.CardCode,
                    nc.CardName,
                    ISNULL(nc.U_FacNit, '') AS U_FacNit,
                    nc.U_FACNUM AS NumNCF,
                    ISNULL(nc.U_FacSerie, 'E34') AS SerieNC,
                    ISNULL(nc.U_NCO, '') AS NumFacturaOrigen,
                    ISNULL(nc.C_FromNumAtCard, '') AS C_FromNumAtCard,
                    nc.C_DocOrigenNum,
                    nc.U_TipoNC,
                    ISNULL(ta.Name, 'No especificado') AS TipoNC_Nombre,
                    ISNULL(nc.C_DocNet, 0) AS SubtotalNeto,
                    ISNULL(nc.VatSum, 0) AS ITBIS,
                    ISNULL(nc.DocTotal, 0) AS TotalNC,
                    ISNULL(nc.Comments, '') AS Motivo,
                    ISNULL(u.nombre, ISNULL(nc.U_user, 'SYS')) AS Usuario,
                    ISNULL(nc.U_terminal, '') AS Terminal,
                    nc.C_DocStatus,
                    ISNULL(nc.CANCELED, 'N') AS CANCELED
                  FROM POSORIN nc
                  LEFT JOIN TIPOS_ANULACION ta ON nc.U_TipoNC = ta.Code
                  LEFT JOIN usuario u ON (nc.U_user = u.usuario OR nc.U_user = CAST(u.id_usuario AS VARCHAR(20)) OR nc.U_user = u.nombre)
                  ORDER BY nc.C_DocEntry DESC";
        $stmtList = $pdo->query($sqlNC);
        $notasCredito = $stmtList ? $stmtList->fetchAll(PDO::FETCH_ASSOC) : [];

        include __DIR__ . '/../views/Facturacion/consulta_notaNCF.php';
    }

    /**
     * AJAX: Obtener detalle completo de una Nota de Crédito (Cabecera y Líneas)
     */
    public function obtenerDetalleNC() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $database = new Database();
            $pdo = $database->getConnection();

            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("ID de Nota de Crédito no proporcionado.");
            }

            // 1. Cabecera de la NC + datos de factura original + datos de ND si fue anulada
            $sqlH = "SELECT 
                        nc.C_DocEntry,
                        nc.DocEntry,
                        nc.DocNum,
                        nc.DocDate,
                        FORMAT(nc.DocDate, 'dd/MM/yyyy') AS DocDateFmt,
                        FORMAT(nc.C_Inserted, 'dd/MM/yyyy hh:mm tt') AS C_InsertedFmt,
                        nc.CardCode,
                        nc.CardName,
                        nc.Address,
                        ISNULL(nc.U_FacNit, '') AS U_FacNit,
                        nc.U_FACNUM AS NumNCF,
                        ISNULL(nc.U_FacSerie, 'E34') AS SerieNC,
                        ISNULL(nc.U_NCO, '') AS NumFacturaOrigen,
                        ISNULL(nc.C_FromNumAtCard, '') AS C_FromNumAtCard,
                        nc.C_DocOrigenNum,
                        nc.U_TipoNC,
                        ISNULL(ta.Name, 'Devolución / Corrección') AS TipoNC_Nombre,
                        ISNULL(nc.C_DocNet, 0) AS SubtotalNeto,
                        ISNULL(nc.VatSum, 0) AS ITBIS,
                        ISNULL(nc.DocTotal, 0) AS TotalNC,
                        ISNULL(nc.Comments, '') AS Motivo,
                        nc.U_user,
                        ISNULL(u.nombre, ISNULL(nc.U_user, 'SYS')) AS Usuario,
                        ISNULL(nc.U_terminal, '') AS Terminal,
                        ISNULL(nc.CANCELED, 'N') AS CANCELED,
                        -- Datos de la factura origen
                        inv.DocTotal AS FacturaDocTotal,
                        inv.NumAtCard AS FacturaNCF,
                        FORMAT(inv.DocDate, 'dd/MM/yyyy') AS FacturaDocDateFmt,
                        -- Datos de la ND anulatoria
                        nd.U_FACNUM AS ND_NumNCF,
                        FORMAT(nd.DocDate, 'dd/MM/yyyy') AS ND_DocDateFmt,
                        -- Datos de Aprobación ARS si la factura origen tuvo cobertura
                        ars_info.NombreARS,
                        ars_info.NumAprobacion,
                        ars_info.MontoARS,
                        ars_info.TotalCopago
                     FROM POSORIN nc
                     LEFT JOIN TIPOS_ANULACION ta ON nc.U_TipoNC = ta.Code
                     LEFT JOIN POSOINV inv ON nc.C_DocOrigenNum = inv.C_DocEntry
                     LEFT JOIN POSODIN nd ON nd.C_DocOrigenNum = nc.C_DocEntry
                     LEFT JOIN usuario u ON (nc.U_user = u.usuario OR nc.U_user = CAST(u.id_usuario AS VARCHAR(20)) OR nc.U_user = u.nombre)
                     OUTER APPLY (
                         SELECT 
                             MAX(COALESCE(ars_soc.CardName, a_inner.CardCode_ARS)) AS NombreARS,
                             STRING_AGG(a_inner.NumAprobacion, ', ') AS NumAprobacion,
                             SUM(ISNULL(a_inner.MontoAprobado, 0)) AS MontoARS,
                             SUM(ISNULL(a_inner.MontoResponsable, 0)) AS TotalCopago
                         FROM AprobacionARS a_inner WITH (NOLOCK)
                         LEFT JOIN posOCRD ars_soc WITH (NOLOCK) ON a_inner.CardCode_ARS = ars_soc.CardCode
                         WHERE a_inner.DocEntry_Factura = inv.C_DocEntry AND a_inner.Status <> 'R'
                         GROUP BY a_inner.DocEntry_Factura
                     ) ars_info
                     WHERE nc.C_DocEntry = ?";
            
            $stmtH = $pdo->prepare($sqlH);
            $stmtH->execute([$id]);
            $cabecera = $stmtH->fetch(PDO::FETCH_ASSOC);

            if (!$cabecera) {
                throw new Exception("No se encontró la Nota de Crédito solicitada.");
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

            // 2. Líneas de la NC (POSRIN1)
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
                     FROM POSRIN1 l
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

        } catch (Exception $e) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
        } catch (Throwable $e) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'msg' => 'Error interno: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX: Obtener detalle completo de una Nota de Débito (Cabecera y Líneas)
     * Delegado a NotaDebitoController para separación de responsabilidades.
     */
    public function obtenerDetalleND() {
        require_once __DIR__ . '/NotaDebitoController.php';
        (new NotaDebitoController())->obtenerDetalleND();
    }

    /**
     * Generar Reporte / Impresión PDF: Comprobante de Nota de Crédito o Relación General
     */
    public function informeNCF() {
        $id = (int)($_GET['id'] ?? $_GET['docEntry'] ?? 0);
        $copia = (int)($_GET['copia'] ?? 0);
        $tipo = $_GET['tipo'] ?? '';

        require_once __DIR__ . '/../reports/InformeNotaCredito.php';
        $reporte = new InformeNotaCredito();

        if ($id > 0) {
            // Imprimir Comprobante Fiscal Oficial de Nota de Crédito individual
            $reporte->generarComprobante($id, $copia);
        } else if ($tipo === 'relacion' || isset($_GET['mes'])) {
            // Generar Relación General de Notas de Crédito por período
            $mes = (int)($_GET['mes'] ?? date('n'));
            $anio = (int)($_GET['anio'] ?? date('Y'));
            $reporte->generarRelacionGeneral($mes, $anio);
        } else {
            die("Parámetros insuficientes para generar el informe de Nota de Crédito.");
        }
    }

    /**
     * Generar Reporte / Impresión PDF: Comprobante de Nota de Débito (E33 / B03)
     * Delegado a NotaDebitoController para separación de responsabilidades.
     */
    public function informeND() {
        require_once __DIR__ . '/NotaDebitoController.php';
        (new NotaDebitoController())->informeND();
    }

}
?>