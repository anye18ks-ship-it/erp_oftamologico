<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class FacturaController {

    public function index() {
        $database = new Database();
        $pdo = $database->getConnection();

        // Limpiar cualquier sesión de cobro pendiente cancelada o abandonada
        unset($_SESSION['datos_cobro_pendiente']);

        // ==========================================
        // VALIDACIÓN DE CAJA APERTURADA (REGRA DE NEGOCIO)
        // ==========================================
        $caja_id = $_SESSION['caja_id'] ?? 0;
        $sqlCheckCaja = "SELECT TOP 1 id_diario_caja FROM diario_caja WHERE id_caja = ? AND fecha_cierre IS NULL";
        $stmtCheckCaja = $pdo->prepare($sqlCheckCaja);
        $stmtCheckCaja->execute([$caja_id]);
        $cajaAbierta = $stmtCheckCaja->fetchColumn();

        if (!$cajaAbierta) {
            unset($_SESSION['caja_apertura_id']);
            Helper::setMensaje('warning', 'Debe aperturar una caja antes de poder facturar.');
            header("Location: index.php?page=apertura_caja");
            exit();
        } else {
            // Restauramos el id en la sesión por si se perdió
            $_SESSION['caja_apertura_id'] = $cajaAbierta;
        }

        // Combos usando tus tablas reales
        $stmtCond = $pdo->query("SELECT GroupNum, PymntGroup, ExtraDays FROM posOCTG ORDER BY PymntGroup");
        $condiciones = $stmtCond->fetchAll(PDO::FETCH_ASSOC);

        $stmtNCF = $pdo->query("SELECT serie, descripcion FROM serie_ncf WHERE activo = 'Y' ORDER BY serie");
        $seriesNCF = $stmtNCF->fetchAll(PDO::FETCH_ASSOC);

        $stmtGrupos = $pdo->query("SELECT ItmsGrpCod, ItmsGrpNam FROM posOITB ORDER BY ItmsGrpNam ASC");
        $gruposArticulos = $stmtGrupos ? $stmtGrupos->fetchAll(PDO::FETCH_ASSOC) : [];

        include __DIR__ . '/../views/Facturacion/factura_venta.php';
    }

    // AJAX: Buscar Cliente con Priorización de Pacientes en Consulta / Citas Hoy
    public function buscarCliente() {
        ob_start(); // capturar warnings PHP para que no corrompan el JSON
        header('Content-Type: application/json; charset=utf-8');
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            $term = trim($_GET['q'] ?? '');
            
            $sql = "
                WITH FacturadosHoy AS (
                    SELECT DISTINCT inv.CardCode
                    FROM POSOINV inv WITH (NOLOCK)
                    WHERE CAST(inv.DocDate AS DATE) = CAST(GETDATE() AS DATE)
                      AND ISNULL(inv.CANCELED, 'N') <> 'Y'
                      AND (inv.DocStatus = 'C' OR inv.ExtraDays > 1 OR ISNULL(inv.PaidToDate, 0) > 0)
                    UNION
                    SELECT DISTINCT a.CardCode
                    FROM AprobacionARS a WITH (NOLOCK)
                    WHERE CAST(a.CreateDateD AS DATE) = CAST(GETDATE() AS DATE)
                      AND a.Status <> 'X'
                      AND a.CardCode IS NOT NULL
                      AND (a.DocEntry_Factura IS NOT NULL OR a.NumFactura = 'COBERTURA 100%')
                ),
                ListaHoy AS (
                    SELECT 
                        lp.lisp_NumeroExpediente,
                        lp.lisp_Fecha,
                        lp.lisp_Estatus,
                        lp.lisp_StsPag,
                        lp.lisp_HoraLlegada,
                        lp.lisp_HoraIniConsulta,
                        lp.lisp_NomProceso,
                        lp.lisp_IdDoctor,
                        d.doct_Nombre AS NombreDoctor,
                        ROW_NUMBER() OVER(PARTITION BY lp.lisp_NumeroExpediente ORDER BY lp.lisp_Fecha DESC, lp.NumLista DESC) as rn
                    FROM Expediente.dbo.s_ListaPaciente lp WITH (NOLOCK)
                    LEFT JOIN Expediente.dbo.s_Doctor d WITH (NOLOCK) ON lp.lisp_IdDoctor = d.doct_IdDoctor
                    WHERE CAST(lp.lisp_Fecha AS DATE) = CAST(GETDATE() AS DATE)
                ),
                CitasHoy AS (
                    SELECT 
                        sc.cita_NumeroExpediente,
                        sc.cita_Fecha,
                        sc.cita_hora,
                        sc.cita_IdDoctor,
                        d.doct_Nombre AS NombreDoctorCita,
                        sc.cita_Comentario,
                        ROW_NUMBER() OVER(PARTITION BY sc.cita_NumeroExpediente ORDER BY sc.cita_Fecha DESC) as rn
                    FROM Expediente.dbo.s_Cita sc WITH (NOLOCK)
                    LEFT JOIN Expediente.dbo.s_Doctor d WITH (NOLOCK) ON sc.cita_IdDoctor = d.doct_IdDoctor
                    WHERE CAST(sc.cita_Fecha AS DATE) = CAST(GETDATE() AS DATE)
                )
                SELECT TOP 150
                    c.CardCode, 
                    c.CardName, 
                    c.U_NIT, 
                    c.U_Comprob, 
                    c.GroupNum, 
                    c.ListNum, 
                    c.CardType,
                    c.GroupCode, 
                    ISNULL(g.GroupName, '') AS GroupName,
                    g.GroupType,
                    c.QryGroup1,
                    c.U_expediente, 
                    c.U_CadenaID, 
                    c.U_SubGrupo,
                    ars.CardName AS NombreARS,
                    -- Información de Consulta y Cita (Se anula si ya fue facturado hoy para que no figure en consulta)
                    CASE WHEN fh.CardCode IS NOT NULL THEN NULL ELSE lh.lisp_Estatus END AS lisp_Estatus,
                    CASE WHEN fh.CardCode IS NOT NULL THEN 'P' ELSE lh.lisp_StsPag END AS lisp_StsPag,
                    lh.lisp_HoraLlegada,
                    lh.lisp_HoraIniConsulta,
                    lh.lisp_NomProceso,
                    lh.NombreDoctor,
                    ch.cita_hora,
                    ch.NombreDoctorCita,
                    CASE WHEN fh.CardCode IS NOT NULL THEN 1 ELSE 0 END AS FacturadoHoy,
                    -- Nivel de Prioridad (1: Pendiente cobro hoy, 2: Sala espera hoy, 3: En consulta hoy, 4: Cita hoy, 5: Pacientes/Clientes de BD, 6: Aseguradoras, 7: Proveedores)
                    CASE 
                        WHEN fh.CardCode IS NULL AND lh.lisp_NumeroExpediente IS NOT NULL AND lh.lisp_StsPag = 'E' THEN 1
                        WHEN fh.CardCode IS NULL AND lh.lisp_NumeroExpediente IS NOT NULL AND lh.lisp_Estatus = 'E' THEN 2
                        WHEN fh.CardCode IS NULL AND lh.lisp_NumeroExpediente IS NOT NULL AND lh.lisp_Estatus = 'C' THEN 3
                        WHEN fh.CardCode IS NULL AND ch.cita_NumeroExpediente IS NOT NULL THEN 4
                        WHEN ISNULL(c.QryGroup1, 'N') <> 'Y' AND (ISNULL(g.GroupType, c.CardType) = 'C' OR c.CardType = 'C') THEN 5
                        WHEN c.CardType = 'S' OR c.QryGroup1 = 'Y' OR c.GroupCode = 4 OR ISNULL(g.GroupName, '') LIKE '%ASEGURADORA%' THEN 6
                        ELSE 7
                    END AS NivelPrioridad
                FROM posOCRD c WITH (NOLOCK)
                LEFT JOIN posOCRG g WITH (NOLOCK) ON c.GroupCode = g.GroupCode
                LEFT JOIN posOCRD ars WITH (NOLOCK) ON c.U_SubGrupo = ars.CardCode
                LEFT JOIN FacturadosHoy fh ON c.CardCode = fh.CardCode
                LEFT JOIN ListaHoy lh ON (TRY_CAST(c.U_expediente AS INT) > 0 AND TRY_CAST(c.U_expediente AS INT) = lh.lisp_NumeroExpediente AND lh.rn = 1)
                LEFT JOIN CitasHoy ch ON (TRY_CAST(c.U_expediente AS INT) > 0 AND TRY_CAST(c.U_expediente AS INT) = ch.cita_NumeroExpediente AND ch.rn = 1)
                WHERE (c.CardName LIKE ? OR c.U_NIT LIKE ? OR c.CardCode LIKE ? OR c.U_expediente LIKE ?) 
                ORDER BY 
                    NivelPrioridad ASC,
                    lh.lisp_HoraLlegada DESC,
                    ch.cita_hora ASC,
                    c.CardName ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%$term%", "%$term%", "%$term%", "%$term%"]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ob_end_clean();
            echo json_encode($result);
        } catch (Throwable $e) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => $e->getMessage()]);
        }
    }

    // AJAX: Buscar Artículo
    public function buscarArticulo() {
        ob_start(); // capturar warnings PHP para que no corrompan el JSON
        header('Content-Type: application/json; charset=utf-8');
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            $term = trim($_GET['q'] ?? '');
            $lista = intval($_GET['lista'] ?? 1);
            if ($lista <= 0) $lista = 1;

            $refresh = !empty($_GET['refresh']);
            if ($refresh) {
                // Sincronizar artículos de posOITM que no tengan registro en positm1
                $sqlSync = "INSERT INTO positm1 (ItemCode, PriceList, Price)
                            SELECT a.ItemCode, opl.ListNum, 0.00
                            FROM posOITM a
                            CROSS JOIN posOPLN opl
                            LEFT JOIN positm1 p ON a.ItemCode = p.ItemCode AND opl.ListNum = p.PriceList
                            WHERE p.ItemCode IS NULL";
                $pdo->exec($sqlSync);

                // Asegurar registro en posOITW para el almacén predeterminado o primer almacén
                $sqlSyncWhs = "INSERT INTO posOITW (ItemCode, WhsCode, OnHand)
                               SELECT a.ItemCode, ISNULL(a.DfltWH, (SELECT TOP 1 WhsCode FROM posOWHS ORDER BY WhsCode ASC)), 0.00
                               FROM posOITM a
                               LEFT JOIN posOITW w ON a.ItemCode = w.ItemCode
                               WHERE w.ItemCode IS NULL";
                $pdo->exec($sqlSyncWhs);
            }
            
            $sql = "SELECT DISTINCT ItemCode, ItemName, PriceBefDi, TaxCode, VatPrcnt, DfltWH, 
                            QryGroup1, QryGroup2, ItmsGrpCod, ItmsGrpNam, CodeBars, InvntItem, SellItem, OnHand
                    FROM v_sale_item 
                    WHERE PriceList = ? 
                    AND (ItemName LIKE ? OR ItemCode LIKE ? OR CodeBars LIKE ? OR ItmsGrpNam LIKE ?)
                    AND SellItem = 'Y'
                    ORDER BY ItemName ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$lista, "%$term%", "%$term%", "%$term%", "%$term%"]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ob_end_clean();
            echo json_encode($result);
        } catch (Throwable $e) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => $e->getMessage()]);
        }
    }

    // Mostrar pantalla de cobro separada
    public function mostrarPantallaCobro() {
        $datosCobro = $_SESSION['datos_cobro_pendiente'] ?? null;
        
        if (isset($_GET['docEntry'])) {
            $database = new Database();
            $pdo = $database->getConnection();
            $docEntry = intval($_GET['docEntry']);
            
            $sql = "SELECT C_DocEntry, DocTotal, CardName FROM POSOINV WHERE C_DocEntry = ? AND DocStatus = 'O'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$docEntry]);
            $fac = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($fac) {
                $datosCobro = [
                    'total' => $fac['DocTotal'],
                    'docEntry' => $fac['C_DocEntry'],
                    'pendiente' => true 
                ];
            }
        }

        if (!$datosCobro) {
            header("Location: index.php?page=facturacion");
            exit();
        }

        // NO destruir datos_cobro_pendiente aquí: se necesita al enviar guardarCobro
        include __DIR__ . '/../views/Facturacion/cobro.php';
    }

    // Guardar el cobro y el pago en caja (Soporta Efectivo, Múltiples Tarjetas y Transferencia Bancaria)
    // Para ventas al contado, aquí se realiza la creación atómica de Factura (POSOINV), Detalle (POSINV1),
    // Aprobaciones ARS (AprobacionARS), ecf_xml_invoice y diario_caja_det sin contaminar tablas ni secuencias si se cancela.
    public function guardarCobro() {
        $database = new Database();
        $pdo = $database->getConnection();
        $pdo->beginTransaction();

        try {
            $datos = json_decode(file_get_contents('php://input'), true);
            $docEntry = intval($datos['docEntry'] ?? 0);
            $efectivo = floatval($datos['efectivo'] ?? 0);
            $esPendiente = $datos['esPendiente'] ?? false;

            // 1. Procesar múltiples tarjetas (retrocompatible con tarjeta individual)
            $tarjetas = [];
            if (!empty($datos['tarjetas']) && is_array($datos['tarjetas'])) {
                foreach ($datos['tarjetas'] as $t) {
                    $montoT = floatval($t['monto'] ?? 0);
                    if ($montoT > 0) {
                        $tarjetas[] = [
                            'monto' => $montoT,
                            'tipo' => substr(trim($t['tipo'] ?? 'Tarjeta'), 0, 30),
                            'referencia' => substr(trim($t['referencia'] ?? ''), 0, 30)
                        ];
                    }
                }
            } elseif (!empty($datos['tarjeta'])) {
                $montoT = floatval($datos['tarjeta']);
                if ($montoT > 0) {
                    $tarjetas[] = [
                        'monto' => $montoT,
                        'tipo' => 'Tarjeta',
                        'referencia' => ''
                    ];
                }
            }

            // 2. Procesar transferencia bancaria
            $transferencia = $datos['transferencia'] ?? null;
            $montoTransferencia = floatval($transferencia['monto'] ?? ($datos['monto_transferencia'] ?? 0));
            $refTransferencia = trim($transferencia['referencia'] ?? ($datos['ref_transferencia'] ?? ''));
            $bancoTransferencia = trim($transferencia['banco'] ?? ($datos['banco_transferencia'] ?? ''));

            if ($montoTransferencia > 0 && empty($refTransferencia)) {
                throw new Exception("Debe ingresar el número de referencia o comprobante para la transferencia bancaria.");
            }

            $totalTarjetas = 0;
            foreach ($tarjetas as $tj) {
                $totalTarjetas += $tj['monto'];
            }
            $totalPagado = $efectivo + $totalTarjetas + $montoTransferencia;

            // Consultar ID de caja abierta activa
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
            if ($id_diario_caja <= 0) {
                $id_diario_caja = 1;
            }

            $pendingPayload = $_SESSION['datos_cobro_pendiente']['factura_payload'] ?? null;

            // =========================================================================
            // CASO 1: NUEVA FACTURA AL CONTADO / COPAGO (Emisión atómica en el cobro)
            // =========================================================================
            if (!$esPendiente && !empty($pendingPayload)) {
                $hdr = $pendingPayload['header'];
                $lineasProcesadas = $pendingPayload['lineasProcesadas'];
                $articulos100 = $pendingPayload['articulos100'] ?? [];
                $articulosCopagoARS = $pendingPayload['articulosCopagoARS'] ?? [];
                $hayAprobacionesPorArticulo = !empty($pendingPayload['hayAprobacionesPorArticulo']);
                $aprobacionARS = $pendingPayload['aprobacionARS'] ?? null;
                $numAprobacion = $pendingPayload['numAprobacion'] ?? '';
                $tieneCopagoARS = !empty($pendingPayload['tieneCopagoARS']);
                $copagoObjetivo = floatval($pendingPayload['copagoObjetivo'] ?? 0);
                $arsMontoAprobado = floatval($pendingPayload['arsMontoAprobado'] ?? 0);
                $arsPorcentajeCober = floatval($pendingPayload['arsPorcentajeCober'] ?? 0);

                $cardCode = $hdr['CardCode'];
                $cardName = $hdr['CardName'];
                $facNit = $hdr['U_FacNit'];
                $facNom = $hdr['U_FacNom'];
                $facSerie = $hdr['U_FacSerie'];
                $extraDays = intval($hdr['ExtraDays']);
                $docDate = $hdr['DocDate'];
                $tNegocio = $hdr['U_TNegocio'];
                $terminal = $hdr['U_terminal'];
                $usuario = $hdr['U_user'];
                $docTotalFiscal = floatval($hdr['DocTotalFiscal']);
                $discPrcntFiscal = floatval($hdr['DiscPrcntFiscal']);
                $discSumFiscal = floatval($hdr['DiscSumFiscal']);
                $totalImpuestoFiscal = floatval($hdr['TotalImpuestoFiscal']);

                $totalCobrar = $docTotalFiscal;

                // Validación de monto total cubierto
                if ($totalPagado < $totalCobrar - 0.001) {
                    throw new Exception("El monto total ingresado (RD$ " . number_format($totalPagado, 2) . ") es menor al total a pagar (RD$ " . number_format($totalCobrar, 2) . ").");
                }

                // 1. SECUENCIA 'F'
                $serieDoc = 'F';
                $sqlSec = "MERGE secuencia WITH (SERIALIZABLE) AS T 
                           USING (VALUES (?)) AS U (tipo) ON U.tipo = T.tipo 
                           WHEN MATCHED THEN UPDATE SET T.secuencia = secuencia + 1 
                           WHEN NOT MATCHED THEN INSERT (secuencia, tipo) VALUES (1, U.tipo) 
                           OUTPUT inserted.secuencia;";
                $stmtSec = $pdo->prepare($sqlSec);
                $stmtSec->execute([$serieDoc]);
                $secDoc = $stmtSec->fetchColumn();
                if (!$secDoc) throw new Exception("No se generó la secuencia para " . $serieDoc);
                $NumAtCard = substr($serieDoc . str_pad($secDoc, 7, '0', STR_PAD_LEFT), 0, 20);

                // 2. NCF
                $cNCF = '';
                $vNCFVence = null;
                if (!empty($facSerie)) {
                    $sqlNCF = "UPDATE carga_ncf 
                               SET contador = contador + 1 
                               OUTPUT Inserted.serie, Inserted.contador, Inserted.vencimiento 
                               WHERE UPPER(serie) = ? 
                               AND contador < final 
                               AND id_carga = (SELECT MIN(id_carga) FROM carga_ncf WHERE UPPER(serie) = ? AND contador < final)";
                    $stmtNCF = $pdo->prepare($sqlNCF);
                    $stmtNCF->execute([$facSerie, $facSerie]);
                    $ncfRow = $stmtNCF->fetch(PDO::FETCH_ASSOC);

                    if (!$ncfRow) {
                        throw new Exception("No quedan NCF disponibles para la serie " . $facSerie);
                    }

                    $stmtLen = $pdo->prepare("SELECT longitud FROM serie_ncf WHERE UPPER(serie) = ?");
                    $stmtLen->execute([$facSerie]);
                    $lenPad = (int)$stmtLen->fetchColumn();
                    if ($lenPad <= 0) $lenPad = (stripos($facSerie, 'B') === 0) ? 8 : 10;

                    $serieMayus = strtoupper(trim($ncfRow['serie'] ?: $facSerie));
                    $cNCF = substr($serieMayus . str_pad($ncfRow['contador'], $lenPad, '0', STR_PAD_LEFT), 0, 20);
                    $vNCFVence = $ncfRow['vencimiento'];
                    if (empty($vNCFVence) || $vNCFVence == '0000-00-00') $vNCFVence = null;
                }

                // 3. INSERTAR HEADER (POSOINV) - DocStatus = 'C' (Cobrada)
                $whsCodeHeader = !empty($lineasProcesadas) ? substr(trim($lineasProcesadas[0]['WhsCode'] ?? ''), 0, 20) : '';
                $sqlH = "INSERT INTO POSOINV (
                            DocDate, CardCode, CardName, U_FacNit, U_FacNom, NumAtCard, 
                            U_SCIASerie, U_SCIADocNum, U_FacSerie, U_NCFVence, U_FACNUM, 
                            DocTotal, DiscPrcnt, DiscSum, VatSum, ExtraDays, Filler, 
                            U_user, U_terminal, U_TNegocio, DocStatus, PaidToDate, ObjType
                         ) VALUES (
                            CAST(? AS DATE), ?, ?, ?, ?, ?, 
                            ?, ?, ?, ?, ?, 
                            ?, ?, ?, ?, ?, ?, 
                            ?, ?, ?, 'C', ?, 13
                         )";
                $stmtH = $pdo->prepare($sqlH);
                $stmtH->execute([
                    $docDate, $cardCode, $cardName, $facNit, $facNom, $NumAtCard, 
                    $serieDoc, (int)$secDoc, $facSerie, $vNCFVence, $cNCF, 
                    $docTotalFiscal, $discPrcntFiscal, $discSumFiscal, $totalImpuestoFiscal, 
                    $extraDays, $whsCodeHeader, substr(trim($usuario), 0, 15), substr(trim($terminal), 0, 20), 
                    $tNegocio, $docTotalFiscal
                ]);
                $docEntry = (int)$pdo->query('SELECT SCOPE_IDENTITY()')->fetchColumn();
                if (!$docEntry) $docEntry = (int)$pdo->lastInsertId();
                if (!$docEntry) throw new Exception("Error al obtener DocEntry de cabecera POSOINV.");

                // 4. INSERTAR DETALLE (POSINV1) Y REBAJAR INVENTARIO
                $sqlD = "INSERT INTO POSINV1 (C_DocEntry, LineNum, ItemCode, Dscription, Quantity, PriceBefDi, Price, DiscPrcnt, LineTotal, VatPrcnt, VatSum, TaxCode, WhsCode)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtD = $pdo->prepare($sqlD);

                $stmtCheckInv  = $pdo->prepare("SELECT InvntItem, DfltWH FROM posOITM WHERE ItemCode = ?");
                $stmtUpdOITM   = $pdo->prepare("UPDATE posOITM SET OnHand = ISNULL(OnHand, 0) - ? WHERE ItemCode = ? AND InvntItem = 'Y'");
                $stmtCheckOITW = $pdo->prepare("SELECT COUNT(*) FROM posOITW WHERE ItemCode = ? AND WhsCode = ?");
                $stmtUpdOITW   = $pdo->prepare("UPDATE posOITW SET OnHand = ISNULL(OnHand, 0) - ? WHERE ItemCode = ? AND WhsCode = ?");
                $stmtInsOITW   = $pdo->prepare("INSERT INTO posOITW (ItemCode, WhsCode, OnHand) VALUES (?, ?, ?)");
                $stmtFirstWhs  = $pdo->query("SELECT TOP 1 WhsCode FROM posOWHS ORDER BY WhsCode ASC");
                $fallbackWhs   = $stmtFirstWhs ? $stmtFirstWhs->fetchColumn() : '01';

                foreach ($lineasProcesadas as $i => $lProc) {
                    $itemCode = $lProc['ItemCode'];
                    $cant = $lProc['Quantity'];
                    $precioCatalogo = $lProc['PriceBefDi'];
                    $precioReal = $lProc['Price'];
                    $descP = $lProc['DiscPrcnt'];
                    $lineTotal = $lProc['LineTotal'];
                    $impP = $lProc['VatPrcnt'];
                    $impuesto = $lProc['VatSum'];
                    $whsCodeLine = $lProc['WhsCode'];

                    $stmtD->execute([
                        intval($docEntry), intval($i), $itemCode, 
                        $lProc['Dscription'], $cant, $precioCatalogo, $precioReal, 
                        $descP, $lineTotal, $impP, $impuesto, $lProc['TaxCode'], 
                        $whsCodeLine
                    ]);

                    // REBAJA DE INVENTARIO: Solo para artículos con control de inventario (InvntItem = 'Y')
                    if (!empty($itemCode) && $cant > 0) {
                        $stmtCheckInv->execute([$itemCode]);
                        $itemInfo = $stmtCheckInv->fetch(PDO::FETCH_ASSOC);

                        if ($itemInfo && ($itemInfo['InvntItem'] ?? '') === 'Y') {
                            // 1. Rebajar existencia en el maestro posOITM
                            $stmtUpdOITM->execute([$cant, $itemCode]);
                            // 2. Determinar almacén destino (línea -> almacén predeterminado -> primer almacén activo)
                            $targetWhs = !empty($whsCodeLine) ? $whsCodeLine : (!empty($itemInfo['DfltWH']) ? $itemInfo['DfltWH'] : $fallbackWhs);

                            if (!empty($targetWhs)) {
                                $stmtCheckOITW->execute([$itemCode, $targetWhs]);
                                if ($stmtCheckOITW->fetchColumn() > 0) {
                                    // 3. Rebajar existencia en el almacén posOITW
                                    $stmtUpdOITW->execute([$cant, $itemCode, $targetWhs]);
                                } else {
                                    $stmtInsOITW->execute([$itemCode, $targetWhs, -$cant]);
                                }
                            }
                        }
                    }
                }

                // 5. INSERTAR APROBACIONES ARS
                $sqlARS = "INSERT INTO AprobacionARS (
                                TipoSocio, CardCode, CardName, NumExpediente, NumAfiliado,
                                CardCode_ARS, NumAprobacion, FechaAprobacionT, FechaAprobacionD,
                                FechaVencimientoT, FechaVencimientoD, MontoAprobado, PorcentajeCober,
                                MontoResponsable, DocEntry_Factura, NumFactura, FechaFacturaT,
                                FechaFacturaD, TipoServicio, Observaciones, Status, U_user,
                                CreateDateT, CreateDateD
                           ) VALUES (
                                ?, ?, ?, ?, ?,
                                ?, ?, ?, ?,
                                ?, ?, ?, ?,
                                ?, ?, ?, ?,
                                ?, ?, ?, 'P', ?,
                                GETDATE(), CAST(GETDATE() AS DATE)
                           )";
                $stmtARS = $pdo->prepare($sqlARS);

                // Consultar datos maestros del paciente para garantizar NumExpediente y afiliación
                $stmtPacInfo = $pdo->prepare("SELECT U_expediente, U_CadenaID, U_SubGrupo FROM posOCRD WHERE CardCode = ?");
                $stmtPacInfo->execute([$cardCode]);
                $pacInfoDb = $stmtPacInfo->fetch(PDO::FETCH_ASSOC);
                $pacExpFallback = ($pacInfoDb && !empty($pacInfoDb['U_expediente'])) ? intval($pacInfoDb['U_expediente']) : null;
                $pacAfilFallback = ($pacInfoDb && !empty($pacInfoDb['U_CadenaID'])) ? trim($pacInfoDb['U_CadenaID']) : '';
                $pacArsFallback = ($pacInfoDb && !empty($pacInfoDb['U_SubGrupo'])) ? trim($pacInfoDb['U_SubGrupo']) : '';

                if ($hayAprobacionesPorArticulo) {
                    // Si hubo algún artículo con 100% en esta venta mixta
                    if (!empty($articulos100)) {
                        foreach ($articulos100 as $item100) {
                            $ap = $item100['ap'];
                            $arsTipoSocio = substr(trim($ap['TipoSocio'] ?? 'P'), 0, 15);
                            $arsCardCode = substr(trim($ap['CardCode'] ?? $cardCode), 0, 100);
                            $arsCardName = substr(trim($ap['CardName'] ?? $cardName), 0, 100);
                            $arsNumExp = !empty($ap['NumExpediente']) ? intval($ap['NumExpediente']) : $pacExpFallback;
                            $arsNumAfiliado = !empty($ap['NumAfiliado']) ? substr(trim($ap['NumAfiliado']), 0, 50) : substr($pacAfilFallback, 0, 50);
                            $arsCardCodeARS = !empty($ap['CardCode_ARS']) ? substr(trim($ap['CardCode_ARS']), 0, 100) : substr($pacArsFallback, 0, 100);
                            $numAp = substr(trim($ap['NumAprobacion'] ?? ''), 0, 50);

                            $arsFechaAprobT = !empty($ap['FechaAprobacionT']) ? $ap['FechaAprobacionT'] : date('Y-m-d H:i:s');
                            $arsFechaAprobD = !empty($ap['FechaAprobacionD']) ? $ap['FechaAprobacionD'] : substr($arsFechaAprobT, 0, 10);
                            $arsFechaVencT = !empty($ap['FechaVencimientoT']) ? $ap['FechaVencimientoT'] : null;
                            $arsFechaVencD = !empty($ap['FechaVencimientoD']) ? $ap['FechaVencimientoD'] : ($arsFechaVencT ? substr($arsFechaVencT, 0, 10) : null);

                            $rawTipoServ = trim($ap['TipoServicio'] ?? $item100['Dscription']);
                            $arsTipoServicio = strtoupper(str_replace(['á','é','í','ó','ú','Á','É','Í','Ó','Ú'], ['a','e','i','o','u','A','E','I','O','U'], $rawTipoServ));
                            $arsTipoServicio = substr($arsTipoServicio, 0, 50);
                            $arsObservaciones = substr(trim($ap['Observaciones'] ?? ('Atención médica 100% cubierta por ARS: ' . $item100['Dscription'])), 0, 254);

                            $stmtARS->execute([
                                $arsTipoSocio, $arsCardCode, $arsCardName, $arsNumExp, $arsNumAfiliado,
                                $arsCardCodeARS, $numAp, $arsFechaAprobT, $arsFechaAprobD,
                                $arsFechaVencT, $arsFechaVencD, $item100['MontoAprobado'], 100.00,
                                0.00, NULL, 'COBERTURA 100%', date('Y-m-d H:i:s'),
                                date('Y-m-d'), $arsTipoServicio, $arsObservaciones, substr(trim($usuario), 0, 20)
                            ]);
                        }
                    }

                    // Aprobaciones con copago ancladas a la factura emitida
                    foreach ($articulosCopagoARS as $itemCopago) {
                        $ap = $itemCopago['ap'];
                        $arsTipoSocio = substr(trim($ap['TipoSocio'] ?? 'P'), 0, 15);
                        $arsCardCode = substr(trim($ap['CardCode'] ?? $cardCode), 0, 100);
                        $arsCardName = substr(trim($ap['CardName'] ?? $cardName), 0, 100);
                        $arsNumExp = !empty($ap['NumExpediente']) ? intval($ap['NumExpediente']) : $pacExpFallback;
                        $arsNumAfiliado = !empty($ap['NumAfiliado']) ? substr(trim($ap['NumAfiliado']), 0, 50) : substr($pacAfilFallback, 0, 50);
                        $arsCardCodeARS = !empty($ap['CardCode_ARS']) ? substr(trim($ap['CardCode_ARS']), 0, 100) : substr($pacArsFallback, 0, 100);
                        $numAp = substr(trim($ap['NumAprobacion'] ?? ''), 0, 50);

                        $arsFechaAprobT = !empty($ap['FechaAprobacionT']) ? $ap['FechaAprobacionT'] : date('Y-m-d H:i:s');
                        $arsFechaAprobD = !empty($ap['FechaAprobacionD']) ? $ap['FechaAprobacionD'] : substr($arsFechaAprobT, 0, 10);
                        $arsFechaVencT = !empty($ap['FechaVencimientoT']) ? $ap['FechaVencimientoT'] : null;
                        $arsFechaVencD = !empty($ap['FechaVencimientoD']) ? $ap['FechaVencimientoD'] : ($arsFechaVencT ? substr($arsFechaVencT, 0, 10) : null);

                        $rawTipoServ = trim($ap['TipoServicio'] ?? $itemCopago['Dscription']);
                        $arsTipoServicio = strtoupper(str_replace(['á','é','í','ó','ú','Á','É','Í','Ó','Ú'], ['a','e','i','o','u','A','E','I','O','U'], $rawTipoServ));
                        $arsTipoServicio = substr($arsTipoServicio, 0, 50);
                        $arsObservaciones = substr(trim($ap['Observaciones'] ?? ('Copago paciente por: ' . $itemCopago['Dscription'])), 0, 254);

                        $stmtARS->execute([
                            $arsTipoSocio, $arsCardCode, $arsCardName, $arsNumExp, $arsNumAfiliado,
                            $arsCardCodeARS, $numAp, $arsFechaAprobT, $arsFechaAprobD,
                            $arsFechaVencT, $arsFechaVencD, $itemCopago['MontoAprobado'], $itemCopago['PorcentajeCober'],
                            $itemCopago['MontoResponsable'], intval($docEntry), $NumAtCard, date('Y-m-d H:i:s'),
                            $docDate, $arsTipoServicio, $arsObservaciones, substr(trim($usuario), 0, 20)
                        ]);
                    }
                } elseif (!empty($numAprobacion)) {
                    $arsTipoSocio = substr(trim($aprobacionARS['TipoSocio'] ?? 'P'), 0, 15);
                    $arsCardCode = substr(trim($aprobacionARS['CardCode'] ?? $cardCode), 0, 100);
                    $arsCardName = substr(trim($aprobacionARS['CardName'] ?? $cardName), 0, 100);
                    $arsNumExp = !empty($aprobacionARS['NumExpediente']) ? intval($aprobacionARS['NumExpediente']) : null;
                    $arsNumAfiliado = substr(trim($aprobacionARS['NumAfiliado'] ?? ''), 0, 50);
                    $arsCardCodeARS = substr(trim($aprobacionARS['CardCode_ARS'] ?? ''), 0, 100);

                    $arsFechaAprobT = !empty($aprobacionARS['FechaAprobacionT']) ? $aprobacionARS['FechaAprobacionT'] : date('Y-m-d H:i:s');
                    $arsFechaAprobD = !empty($aprobacionARS['FechaAprobacionD']) ? $aprobacionARS['FechaAprobacionD'] : substr($arsFechaAprobT, 0, 10);
                    $arsFechaVencT = !empty($aprobacionARS['FechaVencimientoT']) ? $aprobacionARS['FechaVencimientoT'] : null;
                    $arsFechaVencD = !empty($aprobacionARS['FechaVencimientoD']) ? $aprobacionARS['FechaVencimientoD'] : ($arsFechaVencT ? substr($arsFechaVencT, 0, 10) : null);

                    $montoAprobFinal = $tieneCopagoARS ? $arsMontoAprobado : floatval($aprobacionARS['MontoAprobado'] ?? 0);
                    $porcentajeFinal = $tieneCopagoARS ? $arsPorcentajeCober : floatval($aprobacionARS['PorcentajeCober'] ?? 0);
                    $montoRespFinal = $tieneCopagoARS ? $copagoObjetivo : floatval($aprobacionARS['MontoResponsable'] ?? 0);

                    $rawTipoServ = trim($aprobacionARS['TipoServicio'] ?? 'CONSULTA MEDICA');
                    $arsTipoServicio = strtoupper(str_replace(['á','é','í','ó','ú','Á','É','Í','Ó','Ú'], ['a','e','i','o','u','A','E','I','O','U'], $rawTipoServ));
                    $arsTipoServicio = substr($arsTipoServicio, 0, 50);
                    $arsObservaciones = substr(trim($aprobacionARS['Observaciones'] ?? ''), 0, 254);

                    $stmtARS->execute([
                        $arsTipoSocio, $arsCardCode, $arsCardName, $arsNumExp, $arsNumAfiliado,
                        $arsCardCodeARS, $numAprobacion, $arsFechaAprobT, $arsFechaAprobD,
                        $arsFechaVencT, $arsFechaVencD, $montoAprobFinal, $porcentajeFinal,
                        $montoRespFinal, intval($docEntry), $NumAtCard, date('Y-m-d H:i:s'),
                        $docDate, $arsTipoServicio, $arsObservaciones, substr(trim($usuario), 0, 20)
                    ]);
                }

                // 6. REGISTRAR EN ecf_xml_invoice
                if (!empty($cNCF)) {
                    require_once __DIR__ . '/../config/EcfHelper.php';
                    EcfHelper::registrarEcfXml(
                        $pdo,
                        $docEntry,
                        $NumAtCard,
                        $facSerie,
                        $cNCF,
                        $vNCFVence,
                        $facNit,
                        $extraDays,
                        $docDate,
                        'INVOICE'
                    );
                }

                $sciaSerie = $serieDoc;
                $sciaDocNum = (int)$secDoc;

            } else {
                // =========================================================================
                // CASO 2: PAGO DE FACTURA DE CRÉDITO PRE-EXISTENTE
                // =========================================================================
                // Consultar Factura y Aprobación ARS si existe
                $sqlFac = "
                    SELECT 
                        f.DocTotal, 
                        f.U_SCIASerie, 
                        f.U_SCIADocNum,
                        f.PaidToDate,
                        a.MontoResponsable
                    FROM POSOINV f
                    LEFT JOIN AprobacionARS a ON f.C_DocEntry = a.DocEntry_Factura AND a.Status <> 'X'
                    WHERE f.C_DocEntry = ?
                ";
                $stmtFac = $pdo->prepare($sqlFac);
                $stmtFac->execute([$docEntry]);
                $fac = $stmtFac->fetch(PDO::FETCH_ASSOC);

                if (!$fac) throw new Exception("Factura no encontrada o ya procesada.");

                $totalFactura = floatval($fac['DocTotal']);
                // Si hay un copago de ARS, ese es el monto esperado a cobrar, sino el total de la factura
                $totalCobrar = isset($fac['MontoResponsable']) ? floatval($fac['MontoResponsable']) : $totalFactura;

                // Validación usando $totalCobrar en lugar de DocTotal general
                if ($totalPagado < $totalCobrar - 0.001) {
                    throw new Exception("El monto total ingresado (RD$ " . number_format($totalPagado, 2) . ") es menor al total a pagar (RD$ " . number_format($totalCobrar, 2) . ").");
                }

                $sciaSerie = substr($fac['U_SCIASerie'] ?? '', 0, 20);
                $sciaDocNum = $fac['U_SCIADocNum'] ?? 0;

                // Actualizar monto pagado y cerrar factura si se completa el pago
                // La factura se ha pagado en su totalidad (validado arriba), así que la cerramos.
                $pdo->prepare("UPDATE POSOINV 
                               SET PaidToDate = ISNULL(PaidToDate, 0) + ?, 
                                   DocStatus = CASE WHEN (ISNULL(PaidToDate, 0) + ?) >= DocTotal THEN 'C' ELSE 'O' END 
                               WHERE C_DocEntry = ?")
                    ->execute([$totalPagado, $totalPagado, $docEntry]);
            }

            // =========================================================================
            // REGISTRO DE MOVIMIENTOS EN DIARIO_CAJA_DET (COMÚN A AMBOS CASOS)
            // =========================================================================
            $sqlInsDet = "INSERT INTO diario_caja_det (id_diario_caja, id_tipo_movimiento, id_documento, monto_factura, monto_caja, comentario, U_SCIASerie, U_SCIADocNum, referencia, trans_ref, trans_banc)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtInsDet = $pdo->prepare($sqlInsDet);

            // 1. Efectivo (id_tipo_movimiento = 1)
            if ($efectivo > 0) {
                $stmtInsDet->execute([
                    $id_diario_caja,
                    1,
                    $docEntry,
                    $totalCobrar,
                    $efectivo,
                    'Cobro Factura Efectivo',
                    $sciaSerie,
                    $sciaDocNum,
                    '', '', ''
                ]);
            }

            // 2. Tarjetas (id_tipo_movimiento = 2)
            $tarjetaIdx = 1;
            foreach ($tarjetas as $tj) {
                $montoTj = floatval($tj['monto']);
                $tipoTj = $tj['tipo'] ?: 'Tarjeta';
                $refTj = $tj['referencia'] ?: '';
                $comentarioTj = 'Cobro Factura ' . $tipoTj . ($refTj ? ' (Ref: ' . $refTj . ')' : '');
                if (count($tarjetas) > 1) {
                    $comentarioTj = 'Cobro T' . $tarjetaIdx . ' ' . $tipoTj . ($refTj ? ' (Ref: ' . $refTj . ')' : '');
                }
                $comentarioTj = substr($comentarioTj, 0, 300);
                $refTjCortada = substr($refTj, 0, 10);

                $stmtInsDet->execute([
                    $id_diario_caja,
                    2,
                    $docEntry,
                    $totalCobrar,
                    $montoTj,
                    $comentarioTj,
                    $sciaSerie,
                    $sciaDocNum,
                    $refTjCortada,
                    $refTjCortada,
                    ''
                ]);
                $tarjetaIdx++;
            }

            // 3. Transferencia (id_tipo_movimiento = 4)
            if ($montoTransferencia > 0) {
                $bancoStr = $bancoTransferencia ? "Banco: $bancoTransferencia - " : "";
                $comentarioTransf = substr("Cobro Transferencia - {$bancoStr}Ref: $refTransferencia", 0, 300);
                $refTransfCortada = substr($refTransferencia, 0, 10);
                $bancoCortado = substr($bancoTransferencia, 0, 10);

                $stmtInsDet->execute([
                    $id_diario_caja,
                    4,
                    $docEntry,
                    $totalCobrar,
                    $montoTransferencia,
                    $comentarioTransf,
                    $sciaSerie,
                    $sciaDocNum,
                    $refTransfCortada,
                    $refTransfCortada,
                    $bancoCortado
                ]);
            }

            // 4. Exonerado / Cobertura Especial (id_tipo_movimiento = 5)
            if ($efectivo <= 0 && $totalTarjetas <= 0 && $montoTransferencia <= 0) {
                $stmtInsDet->execute([
                    $id_diario_caja,
                    5,
                    $docEntry,
                    $totalCobrar,
                    0,
                    'Cobro Cobertura Total ARS',
                    $sciaSerie,
                    $sciaDocNum,
                    '', '', ''
                ]);
            }

            // Limpiar la sesión de cobro pendiente ya completada
            unset($_SESSION['datos_cobro_pendiente']);

            $pdo->commit();
            echo json_encode([
                'status' => 'success', 
                'msg' => 'Pago y Factura ' . ($NumAtCard ?? ('#' . $docEntry)) . ' registrados correctamente.',
                'docEntry' => $docEntry,
                'NCF' => $cNCF ?? ''
            ]);

        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
        }
    }
    
    // Guardar Factura Maestro-Detalle (CON DEBUG POR PASOS)
    public function guardarVenta() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') exit('Acceso denegado');
        
        $database = new Database();
        $pdo = $database->getConnection();
        $pdo->beginTransaction();

        try {
            // Verificar caja abierta antes de guardar
            if (empty($_SESSION['caja_apertura_id'])) {
                throw new Exception("No hay caja aperturada. No se puede facturar.");
            }

            $datos = json_decode(file_get_contents('php://input'), true);
            $header = $datos['header'] ?? [];
            $lines = $datos['lines'] ?? [];
            
            $usuario = $_SESSION['nombre'] ?? 'ADMIN'; 
            $terminal = 'WEB'; 

            // Limpieza y formateo de datos
            $cardCode = substr(trim($header['CardCode'] ?? ''), 0, 20);
            $cardName = substr(trim($header['CardName'] ?? ''), 0, 100);
            $facNitRaw = trim($header['U_FacNit'] ?? '');
            $tNegocio = Helper::determinarTipoNegocio($facNitRaw, $header['U_TNegocio'] ?? null);
            $facNit = substr(Helper::limpiarNit($facNitRaw), 0, 20);
            $facNom = substr(trim($header['U_FacNom'] ?? ''), 0, 100);
            $facSerie = strtoupper(substr(trim($header['U_FacSerie'] ?? ''), 0, 10));
            $extraDays = intval($header['ExtraDays'] ?? 0);
            $discPrcnt = floatval($header['DiscPrcnt'] ?? 0);
            $sciaSerie = 'F';

            // CÁLCULO DE TOTALES PREVIO
            $totalNeto = 0; $totalImpuesto = 0; $descLinea = 0;
            foreach ($lines as $line) {
                $cant = floatval($line['Quantity'] ?? 0);
                $precio = floatval($line['PriceBefDi'] ?? 0);
                $descP = floatval($line['DiscPrcnt'] ?? 0);
                $impP = floatval($line['VatPrcnt'] ?? 0);

                $descuento = ($cant * $precio) * ($descP / 100);
                $lineTotal = ($cant * $precio) - $descuento;
                $impuesto = $lineTotal * ($impP / 100);
                
                $descLinea += $descuento;
                $totalNeto += $lineTotal;
                $totalImpuesto += $impuesto;
            }
            $descGlobal = $totalNeto * ($discPrcnt / 100);
            $docTotal = $totalNeto - $descGlobal + $totalImpuesto;

            // =========================================================================
            // DETECCIÓN Y PROCESAMIENTO DE APROBACIONES ARS
            // =========================================================================
            $aprobacionARS = $datos['aprobacion_ars'] ?? null;
            $numAprobacion = trim($header['U_NumAprobacion'] ?? ($aprobacionARS['NumAprobacion'] ?? ''));

            // 1. Verificar si existen aprobaciones individuales por artículo
            $hayAprobacionesPorArticulo = false;
            foreach ($lines as $l) {
                if (!empty($l['aprobacion_ars']) && !empty($l['aprobacion_ars']['NumAprobacion'])) {
                    $hayAprobacionesPorArticulo = true;
                    break;
                }
            }

            // =========================================================================
            // CASO A: APROBACIONES REGISTRADAS POR ARTÍCULO / PROCEDIMIENTO INDIVIDUAL
            // =========================================================================
            if ($hayAprobacionesPorArticulo) {
                $articulos100 = [];
                $articulosFacturables = [];
                $articulosCopagoARS = [];

                foreach ($lines as $i => $line) {
                    $itemCode = substr(trim($line['ItemCode'] ?? ''), 0, 20);
                    $cant = floatval($line['Quantity'] ?? 1);
                    if ($cant <= 0) $cant = 1.0;
                    $precioCatalogo = floatval($line['PriceBefDi'] ?? 0);
                    if ($precioCatalogo <= 0) $precioCatalogo = floatval($line['Price'] ?? 0);
                    $descPOrig = floatval($line['DiscPrcnt'] ?? 0);
                    $impP = floatval($line['VatPrcnt'] ?? 0);
                    $whsCodeLine = substr(trim($line['WhsCode'] ?? ''), 0, 20);
                    $itemName = substr(trim($line['ItemName'] ?? ($line['Dscription'] ?? '')), 0, 100);
                    $taxCode = substr(trim($line['TaxCode'] ?? 'EXE'), 0, 20);

                    $brutoLinea = $cant * $precioCatalogo;
                    $descLineaOrig = $brutoLinea * ($descPOrig / 100);
                    $netoLineaOrig = $brutoLinea - $descLineaOrig;
                    $impLineaOrig = $netoLineaOrig * ($impP / 100);
                    $totalLineaBruto = $netoLineaOrig + $impLineaOrig;

                    $ap = $line['aprobacion_ars'] ?? null;
                    if (!empty($ap) && !empty($ap['NumAprobacion'])) {
                        $pctCob = floatval($ap['PorcentajeCober'] ?? 0);
                        $mResp = floatval($ap['MontoResponsable'] ?? 0);
                        $mAprob = floatval($ap['MontoAprobado'] ?? 0);
                        $es100 = ($pctCob >= 99.99 || ($mResp <= 0.001 && $mAprob > 0));

                        if ($es100) {
                            // Cobertura 100%: Solo se registra en AprobacionARS. NO ensucia POSINV1 con copago 0
                            $articulos100[] = [
                                'line' => $line,
                                'ap' => $ap,
                                'ItemCode' => $itemCode,
                                'Dscription' => $itemName,
                                'Quantity' => $cant,
                                'WhsCode' => $whsCodeLine,
                                'MontoAprobado' => ($mAprob > 0) ? $mAprob : $totalLineaBruto
                            ];
                        } else {
                            // Cobertura parcial: El paciente paga el copago $mResp directamente en la línea.
                            // La cobertura ARS no es un descuento comercial al paciente en POSINV1;
                            // es el valor neto/copago a facturar para evitar doble descuento en Nota de Crédito.
                            $lineTotalAjustado = round($mResp, 2);
                            $precioEfectivo = ($cant > 0) ? round($lineTotalAjustado / $cant, 4) : $lineTotalAjustado;
                            $impuestoAjustado = round($lineTotalAjustado * ($impP / 100), 2);

                            $lineasFacturables[] = [
                                'ItemCode' => $itemCode,
                                'Dscription' => $itemName,
                                'Quantity' => $cant,
                                'PriceBefDi' => $precioCatalogo,
                                'Price' => $precioEfectivo,
                                'DiscPrcnt' => 0.00,
                                'LineTotal' => $lineTotalAjustado,
                                'VatPrcnt' => $impP,
                                'VatSum' => $impuestoAjustado,
                                'TaxCode' => $taxCode,
                                'WhsCode' => $whsCodeLine
                            ];

                            $articulosCopagoARS[] = [
                                'line' => $line,
                                'ap' => $ap,
                                'ItemCode' => $itemCode,
                                'Dscription' => $itemName,
                                'MontoAprobado' => ($mAprob > 0) ? $mAprob : max(0, $totalLineaBruto - $mResp),
                                'PorcentajeCober' => $pctCob,
                                'MontoResponsable' => $lineTotalAjustado
                            ];
                        }
                    } else {
                        // Artículo privado (sin seguro)
                        $impuestoPrivado = round($netoLineaOrig * ($impP / 100), 2);
                        $lineasFacturables[] = [
                            'ItemCode' => $itemCode,
                            'Dscription' => $itemName,
                            'Quantity' => $cant,
                            'PriceBefDi' => $precioCatalogo,
                            'Price' => floatval($line['Price'] ?? $precioCatalogo),
                            'DiscPrcnt' => $descPOrig,
                            'LineTotal' => round($netoLineaOrig, 2),
                            'VatPrcnt' => $impP,
                            'VatSum' => $impuestoPrivado,
                            'TaxCode' => $taxCode,
                            'WhsCode' => $whsCodeLine
                        ];
                    }
                }

                // SUB-CASO A.1: TODOS LOS ARTÍCULOS ESTÁN AL 100% CUBIERTOS POR ARS
                if (empty($lineasFacturables)) {
                    $stmtCheckInv  = $pdo->prepare("SELECT InvntItem, DfltWH FROM posOITM WHERE ItemCode = ?");
                    $stmtUpdOITM   = $pdo->prepare("UPDATE posOITM SET OnHand = ISNULL(OnHand, 0) - ? WHERE ItemCode = ? AND InvntItem = 'Y'");
                    $stmtCheckOITW = $pdo->prepare("SELECT COUNT(*) FROM posOITW WHERE ItemCode = ? AND WhsCode = ?");
                    $stmtUpdOITW   = $pdo->prepare("UPDATE posOITW SET OnHand = ISNULL(OnHand, 0) - ? WHERE ItemCode = ? AND WhsCode = ?");
                    $stmtInsOITW   = $pdo->prepare("INSERT INTO posOITW (ItemCode, WhsCode, OnHand) VALUES (?, ?, ?)");
                    $stmtFirstWhs  = $pdo->query("SELECT TOP 1 WhsCode FROM posOWHS ORDER BY WhsCode ASC");
                    $fallbackWhs   = $stmtFirstWhs ? $stmtFirstWhs->fetchColumn() : '01';

                    $sqlARS100 = "INSERT INTO AprobacionARS (
                                    TipoSocio, CardCode, CardName, NumExpediente, NumAfiliado,
                                    CardCode_ARS, NumAprobacion, FechaAprobacionT, FechaAprobacionD,
                                    FechaVencimientoT, FechaVencimientoD, MontoAprobado, PorcentajeCober,
                                    MontoResponsable, DocEntry_Factura, NumFactura, FechaFacturaT,
                                    FechaFacturaD, TipoServicio, Observaciones, Status, U_user,
                                    CreateDateT, CreateDateD
                               ) VALUES (
                                    ?, ?, ?, ?, ?,
                                    ?, ?, ?, ?,
                                    ?, ?, ?, 100.00,
                                    0.00, NULL, 'COBERTURA 100%', GETDATE(),
                                    CAST(GETDATE() AS DATE), ?, ?, 'P', ?,
                                    GETDATE(), CAST(GETDATE() AS DATE)
                               )";
                    $stmtARS100 = $pdo->prepare($sqlARS100);

                    foreach ($articulos100 as $item100) {
                        $ap = $item100['ap'];
                        $itemCode = $item100['ItemCode'];
                        $cant = $item100['Quantity'];
                        $whsCodeLine = $item100['WhsCode'];

                        if (!empty($itemCode) && $cant > 0) {
                            $stmtCheckInv->execute([$itemCode]);
                            $itemInfo = $stmtCheckInv->fetch(PDO::FETCH_ASSOC);
                            if ($itemInfo && ($itemInfo['InvntItem'] ?? '') === 'Y') {
                                $stmtUpdOITM->execute([$cant, $itemCode]);
                                $targetWhs = !empty($whsCodeLine) ? $whsCodeLine : (!empty($itemInfo['DfltWH']) ? $itemInfo['DfltWH'] : $fallbackWhs);
                                if (!empty($targetWhs)) {
                                    $stmtCheckOITW->execute([$itemCode, $targetWhs]);
                                    if ($stmtCheckOITW->fetchColumn() > 0) {
                                        $stmtUpdOITW->execute([$cant, $itemCode, $targetWhs]);
                                    } else {
                                        $stmtInsOITW->execute([$itemCode, $targetWhs, -$cant]);
                                    }
                                }
                            }
                        }

                        $arsTipoSocio = substr(trim($ap['TipoSocio'] ?? 'P'), 0, 15);
                        $arsCardCode = substr(trim($ap['CardCode'] ?? $cardCode), 0, 100);
                        $arsCardName = substr(trim($ap['CardName'] ?? $cardName), 0, 100);
                        $arsNumExp = !empty($ap['NumExpediente']) ? intval($ap['NumExpediente']) : null;
                        $arsNumAfiliado = substr(trim($ap['NumAfiliado'] ?? ''), 0, 50);
                        $arsCardCodeARS = substr(trim($ap['CardCode_ARS'] ?? ''), 0, 100);
                        $numAp = substr(trim($ap['NumAprobacion'] ?? ''), 0, 50);

                        $arsFechaAprobT = !empty($ap['FechaAprobacionT']) ? $ap['FechaAprobacionT'] : date('Y-m-d H:i:s');
                        $arsFechaAprobD = !empty($ap['FechaAprobacionD']) ? $ap['FechaAprobacionD'] : substr($arsFechaAprobT, 0, 10);
                        $arsFechaVencT = !empty($ap['FechaVencimientoT']) ? $ap['FechaVencimientoT'] : null;
                        $arsFechaVencD = !empty($ap['FechaVencimientoD']) ? $ap['FechaVencimientoD'] : ($arsFechaVencT ? substr($arsFechaVencT, 0, 10) : null);

                        $rawTipoServ = trim($ap['TipoServicio'] ?? $item100['Dscription']);
                        $arsTipoServicio = strtoupper(str_replace(['á','é','í','ó','ú','Á','É','Í','Ó','Ú'], ['a','e','i','o','u','A','E','I','O','U'], $rawTipoServ));
                        $arsTipoServicio = substr($arsTipoServicio, 0, 50);
                        $arsObservaciones = substr(trim($ap['Observaciones'] ?? ('Atención médica 100% cubierta por ARS: ' . $item100['Dscription'])), 0, 254);

                        $stmtARS100->execute([
                            $arsTipoSocio, $arsCardCode, $arsCardName, $arsNumExp, $arsNumAfiliado,
                            $arsCardCodeARS, $numAp, $arsFechaAprobT, $arsFechaAprobD,
                            $arsFechaVencT, $arsFechaVencD, $item100['MontoAprobado'],
                            $arsTipoServicio, $arsObservaciones, substr(trim($usuario), 0, 20)
                        ]);
                    }

                    $pdo->commit();

                    echo json_encode([
                        'status' => 'success',
                        'esCoberturaTotal' => true,
                        'msg' => 'Aprobaciones ARS registradas exitosamente con Cobertura 100% (' . count($articulos100) . ' servicio(s)).',
                        'totalAprobaciones' => count($articulos100)
                    ]);
                    return;
                }

                // SUB-CASO A.2: FACTURACIÓN CON ELEMENTOS COPAGO / PRIVADOS
                // Los artículos con 100% se conservan en $articulos100 para guardarse al confirmar el cobro
                // 1. Líneas facturables fiscales
                $lineasProcesadas = $lineasFacturables;
                $docTotalFiscal = 0;
                $totalImpuestoFiscal = 0;
                foreach ($lineasProcesadas as $lp) {
                    $docTotalFiscal += $lp['LineTotal'] + $lp['VatSum'];
                    $totalImpuestoFiscal += $lp['VatSum'];
                }
                $discSumFiscal = 0;
                $discPrcntFiscal = 0;
                $tieneCopagoARS = (!empty($articulosCopagoARS));

            } else {
                // =========================================================================
                // CASO B: APROBACIÓN GENERAL PARA TODA LA FACTURA O FACTURA PRIVADA
                // =========================================================================
                $montoAprobadoARS = floatval($aprobacionARS['MontoAprobado'] ?? 0);
                $porcentajeCoberARS = floatval($aprobacionARS['PorcentajeCober'] ?? 0);
                $montoResponsableARS = floatval($aprobacionARS['MontoResponsable'] ?? 0);

                $esCoberturaTotal = (
                    !empty($aprobacionARS) && 
                    !empty($numAprobacion) && 
                    ($porcentajeCoberARS >= 99.99 || $montoResponsableARS <= 0.001) &&
                    ($montoAprobadoARS > 0 || $docTotal > 0)
                );

                if ($esCoberturaTotal) {
                    $arsTipoSocio = substr(trim($aprobacionARS['TipoSocio'] ?? 'P'), 0, 15);
                    $arsCardCode = substr(trim($aprobacionARS['CardCode'] ?? $cardCode), 0, 100);
                    $arsCardName = substr(trim($aprobacionARS['CardName'] ?? $cardName), 0, 100);
                    $arsNumExp = !empty($aprobacionARS['NumExpediente']) ? intval($aprobacionARS['NumExpediente']) : $pacExpFallback;
                    $arsNumAfiliado = !empty($aprobacionARS['NumAfiliado']) ? substr(trim($aprobacionARS['NumAfiliado']), 0, 50) : substr($pacAfilFallback, 0, 50);
                    $arsCardCodeARS = !empty($aprobacionARS['CardCode_ARS']) ? substr(trim($aprobacionARS['CardCode_ARS']), 0, 100) : substr($pacArsFallback, 0, 100);

                    $arsFechaAprobT = !empty($aprobacionARS['FechaAprobacionT']) ? $aprobacionARS['FechaAprobacionT'] : date('Y-m-d H:i:s');
                    $arsFechaAprobD = !empty($aprobacionARS['FechaAprobacionD']) ? $aprobacionARS['FechaAprobacionD'] : substr($arsFechaAprobT, 0, 10);
                    $arsFechaVencT = !empty($aprobacionARS['FechaVencimientoT']) ? $aprobacionARS['FechaVencimientoT'] : null;
                    $arsFechaVencD = !empty($aprobacionARS['FechaVencimientoD']) ? $aprobacionARS['FechaVencimientoD'] : ($arsFechaVencT ? substr($arsFechaVencT, 0, 10) : null);

                    $montoRealAprobado = ($montoAprobadoARS > 0) ? $montoAprobadoARS : $docTotal;

                    $tipoServicioInput = trim($aprobacionARS['TipoServicio'] ?? '');
                    if (empty($tipoServicioInput) && !empty($lines)) {
                        $nombresItems = array_map(function($l) { return trim($l['ItemName'] ?? $l['ItemCode'] ?? ''); }, $lines);
                        $tipoServicioInput = implode(', ', array_filter($nombresItems));
                    }
                    $rawTipoServ = !empty($tipoServicioInput) ? $tipoServicioInput : 'CONSULTA MEDICA';
                    $arsTipoServicio = strtoupper(str_replace(['á','é','í','ó','ú','Á','É','Í','Ó','Ú'], ['a','e','i','o','u','A','E','I','O','U'], $rawTipoServ));
                    $arsTipoServicio = substr($arsTipoServicio, 0, 50);
                    $arsObservaciones = substr(trim($aprobacionARS['Observaciones'] ?? 'Atención médica autorizada bajo Cobertura 100% ARS'), 0, 254);

                    $sqlARS = "INSERT INTO AprobacionARS (
                                    TipoSocio, CardCode, CardName, NumExpediente, NumAfiliado,
                                    CardCode_ARS, NumAprobacion, FechaAprobacionT, FechaAprobacionD,
                                    FechaVencimientoT, FechaVencimientoD, MontoAprobado, PorcentajeCober,
                                    MontoResponsable, DocEntry_Factura, NumFactura, FechaFacturaT,
                                    FechaFacturaD, TipoServicio, Observaciones, Status, U_user,
                                    CreateDateT, CreateDateD
                               ) VALUES (
                                    ?, ?, ?, ?, ?,
                                    ?, ?, ?, ?,
                                    ?, ?, ?, 100.00,
                                    0.00, NULL, 'COBERTURA 100%', GETDATE(),
                                    CAST(GETDATE() AS DATE), ?, ?, 'P', ?,
                                    GETDATE(), CAST(GETDATE() AS DATE)
                               )";
                    $stmtARS = $pdo->prepare($sqlARS);
                    $stmtARS->execute([
                        $arsTipoSocio, $arsCardCode, $arsCardName, $arsNumExp, $arsNumAfiliado,
                        $arsCardCodeARS, $numAprobacion, $arsFechaAprobT, $arsFechaAprobD,
                        $arsFechaVencT, $arsFechaVencD, $montoRealAprobado,
                        $arsTipoServicio, $arsObservaciones, substr(trim($usuario), 0, 20)
                    ]);

                    $idARS = (int)$pdo->query('SELECT SCOPE_IDENTITY()')->fetchColumn();
                    if (!$idARS) $idARS = (int)$pdo->lastInsertId();

                    $pdo->commit();

                    echo json_encode([
                        'status' => 'success',
                        'esCoberturaTotal' => true,
                        'msg' => 'Aprobación ARS registrada exitosamente con Cobertura 100%.',
                        'numAprobacion' => $numAprobacion,
                        'idARS' => $idARS,
                        'montoAprobado' => $montoRealAprobado
                    ]);
                    return;
                }

                // Copago para aprobación general
                $tieneCopagoARS = false;
                $copagoObjetivo = 0.0;
                $coberturaARS = 0.0;
                $arsMontoAprobado = floatval($aprobacionARS['MontoAprobado'] ?? 0);
                $arsPorcentajeCober = floatval($aprobacionARS['PorcentajeCober'] ?? 0);
                $arsMontoResponsable = floatval($aprobacionARS['MontoResponsable'] ?? 0);

                if (!empty($numAprobacion) && !empty($aprobacionARS) && !$esCoberturaTotal) {
                    if ($arsMontoResponsable == 0 && $arsMontoAprobado > 0) {
                        $arsMontoResponsable = max(0, $docTotal - $arsMontoAprobado);
                    }
                    if ($arsMontoAprobado == 0 && $arsMontoResponsable > 0) {
                        $arsMontoAprobado = max(0, $docTotal - $arsMontoResponsable);
                    }

                    if ($arsMontoResponsable > 0 && $docTotal > 0 && $arsMontoResponsable < $docTotal) {
                        $tieneCopagoARS = true;
                        $copagoObjetivo = round($arsMontoResponsable, 2);
                        $coberturaARS = round($docTotal - $copagoObjetivo, 2);
                        if ($arsPorcentajeCober <= 0 && $docTotal > 0) {
                            $arsPorcentajeCober = round(($coberturaARS / $docTotal) * 100, 2);
                        }
                    }
                }

                $lineasProcesadas = [];
                $factorCopago = ($tieneCopagoARS && $docTotal > 0) ? ($copagoObjetivo / $docTotal) : 1.0;
                $acumuladoLineTotal = 0;
                $acumuladoVat = 0;
                $numLineas = count($lines);

                foreach ($lines as $i => $line) {
                    $itemCode = substr(trim($line['ItemCode'] ?? ''), 0, 20);
                    $cant = floatval($line['Quantity'] ?? 0);
                    if ($cant <= 0) $cant = 1.0;
                    $precioCatalogo = floatval($line['PriceBefDi'] ?? 0);
                    if ($precioCatalogo <= 0) $precioCatalogo = floatval($line['Price'] ?? 0);
                    $descPOrig = floatval($line['DiscPrcnt'] ?? 0);
                    $impP = floatval($line['VatPrcnt'] ?? 0);
                    $whsCodeLine = substr(trim($line['WhsCode'] ?? ''), 0, 20);
                    $itemName = substr(trim($line['ItemName'] ?? ($line['Dscription'] ?? '')), 0, 100);
                    $taxCode = substr(trim($line['TaxCode'] ?? 'EXE'), 0, 20);

                    $brutoLinea = $cant * $precioCatalogo;
                    $descLineaOrig = $brutoLinea * ($descPOrig / 100);
                    $netoLineaOrig = $brutoLinea - $descLineaOrig;

                    if ($tieneCopagoARS && $docTotal > 0 && $copagoObjetivo > 0) {
                        if ($i === $numLineas - 1) {
                            $lineTotalAjustado = round($copagoObjetivo - $acumuladoLineTotal, 2);
                        } else {
                            $lineTotalAjustado = round($netoLineaOrig * $factorCopago, 2);
                            $acumuladoLineTotal += $lineTotalAjustado;
                        }

                        $precioEfectivo = ($cant > 0) ? round($lineTotalAjustado / $cant, 4) : $lineTotalAjustado;
                        $impuestoAjustado = round($lineTotalAjustado * ($impP / 100), 2);
                        $acumuladoVat += $impuestoAjustado;

                        $lineasProcesadas[] = [
                            'ItemCode' => $itemCode,
                            'Dscription' => $itemName,
                            'Quantity' => $cant,
                            'PriceBefDi' => $precioCatalogo,
                            'Price' => $precioEfectivo,
                            'DiscPrcnt' => 0.00,
                            'LineTotal' => $lineTotalAjustado,
                            'VatPrcnt' => $impP,
                            'VatSum' => $impuestoAjustado,
                            'TaxCode' => $taxCode,
                            'WhsCode' => $whsCodeLine
                        ];
                    } else {
                        $impuesto = round($netoLineaOrig * ($impP / 100), 2);
                        $lineasProcesadas[] = [
                            'ItemCode' => $itemCode,
                            'Dscription' => $itemName,
                            'Quantity' => $cant,
                            'PriceBefDi' => $precioCatalogo,
                            'Price' => floatval($line['Price'] ?? $precioCatalogo),
                            'DiscPrcnt' => $descPOrig,
                            'LineTotal' => round($netoLineaOrig, 2),
                            'VatPrcnt' => $impP,
                            'VatSum' => $impuesto,
                            'TaxCode' => $taxCode,
                            'WhsCode' => $whsCodeLine
                        ];
                    }
                }

                $docTotalFiscal = $tieneCopagoARS ? $copagoObjetivo : $docTotal;
                $discSumFiscal = $descGlobal;
                $discPrcntFiscal = $discPrcnt;
                $totalImpuestoFiscal = $tieneCopagoARS ? $acumuladoVat : $totalImpuesto;
            }

            // =========================================================================
            // VALIDACIÓN DE NCF Y PREPARACIÓN DE INFORMACIÓN DE COBRO
            // =========================================================================
            if (empty($facSerie)) {
                throw new Exception("Debe seleccionar un cliente con comprobante fiscal (NCF) válido asignado.");
            }
            if (in_array($facSerie, ['E33', 'E34', 'B04', 'B03'])) {
                throw new Exception("La serie {$facSerie} es un comprobante de Nota de Débito/Crédito y no puede emitirse como Factura de Venta.");
            }

            // Validar que existan NCF disponibles para la serie antes de proceder
            $stmtCheckNCF = $pdo->prepare("SELECT COUNT(*) FROM carga_ncf WHERE UPPER(serie) = ? AND contador < final");
            $stmtCheckNCF->execute([$facSerie]);
            if ($stmtCheckNCF->fetchColumn() <= 0) {
                throw new Exception("No quedan NCF disponibles para la serie " . $facSerie);
            }

            $whsCodeHeader = !empty($lines) ? substr(trim($lines[0]['WhsCode'] ?? ''), 0, 20) : '';
            $docDateInput = trim($header['DocDate'] ?? '');
            $docDate = !empty($docDateInput) ? $docDateInput : date('Y-m-d');

            // Armar información ARS para la pantalla de cobro
            $arsInfoCobro = null;
            if ($hayAprobacionesPorArticulo && !empty($articulosCopagoARS)) {
                $numsAprob = array_map(function($a) { return $a['ap']['NumAprobacion'] ?? ''; }, $articulosCopagoARS);
                $montoAprobTotal = 0;
                foreach ($articulosCopagoARS as $a) {
                    $montoAprobTotal += floatval($a['MontoAprobado'] ?? 0);
                }

                $cardCodeARS = '';
                $nombreARS = '';
                $numAfiliado = '';
                foreach ($articulosCopagoARS as $a) {
                    if (!empty($a['ap']['CardCode_ARS'])) {
                        $cardCodeARS = trim($a['ap']['CardCode_ARS']);
                    }
                    if (!empty($a['ap']['NumAfiliado'])) {
                        $numAfiliado = trim($a['ap']['NumAfiliado']);
                    }
                    if (!empty($a['ap']['NombreARS'])) {
                        $nombreARS = trim($a['ap']['NombreARS']);
                    }
                }

                if (empty($cardCodeARS) || empty($nombreARS) || empty($numAfiliado)) {
                    $stmtPacInfo = $pdo->prepare("SELECT U_SubGrupo, U_CadenaID FROM posOCRD WHERE CardCode = ?");
                    $stmtPacInfo->execute([$cardCode]);
                    $pacInfo = $stmtPacInfo->fetch(PDO::FETCH_ASSOC);
                    if ($pacInfo) {
                        if (empty($cardCodeARS)) $cardCodeARS = trim($pacInfo['U_SubGrupo'] ?? '');
                        if (empty($numAfiliado)) $numAfiliado = trim($pacInfo['U_CadenaID'] ?? '');
                    }
                }

                if (!empty($cardCodeARS) && empty($nombreARS)) {
                    $stmtN = $pdo->prepare("SELECT CardName FROM posOCRD WHERE CardCode = ?");
                    $stmtN->execute([$cardCodeARS]);
                    $nombreARS = trim($stmtN->fetchColumn() ?: $cardCodeARS);
                }
                if (empty($nombreARS)) $nombreARS = (!empty($cardCodeARS) ? $cardCodeARS : 'Seguro ARS');

                $totalBrutoServicios = $montoAprobTotal + $docTotalFiscal;
                $pctCoberPromedio = ($totalBrutoServicios > 0) ? round(($montoAprobTotal / $totalBrutoServicios) * 100, 1) : 0;

                $arsInfoCobro = [
                    'NumAprobacion' => implode(', ', array_filter(array_unique($numsAprob))),
                    'MontoAprobado' => $montoAprobTotal,
                    'PorcentajeCober' => $pctCoberPromedio,
                    'MontoResponsable' => $docTotalFiscal,
                    'TotalBruto' => $totalBrutoServicios,
                    'CardCode_ARS' => $cardCodeARS,
                    'NombreARS' => $nombreARS,
                    'NumAfiliado' => $numAfiliado,
                    'TipoServicio' => (count($articulosCopagoARS) + (!empty($articulos100) ? count($articulos100) : 0)) . ' servicio(s) autorizados',
                    'esPorArticulos' => true
                ];
            } elseif (!empty($numAprobacion) && !empty($aprobacionARS)) {
                $montoAprobadoVal = $tieneCopagoARS ? $arsMontoAprobado : floatval($aprobacionARS['MontoAprobado'] ?? 0);
                $montoRespVal = $tieneCopagoARS ? $copagoObjetivo : floatval($aprobacionARS['MontoResponsable'] ?? 0);
                $totalBrutoVal = $montoAprobadoVal + $montoRespVal;
                $pctCoberVal = $tieneCopagoARS ? $arsPorcentajeCober : floatval($aprobacionARS['PorcentajeCober'] ?? 0);
                if ($pctCoberVal <= 0 && $totalBrutoVal > 0) {
                    $pctCoberVal = round(($montoAprobadoVal / $totalBrutoVal) * 100, 1);
                }

                $cardCodeARS = trim($aprobacionARS['CardCode_ARS'] ?? '');
                $nombreARS = trim($aprobacionARS['NombreARS'] ?? '');
                if (empty($nombreARS) && !empty($cardCodeARS)) {
                    $stmtN = $pdo->prepare("SELECT CardName FROM posOCRD WHERE CardCode = ?");
                    $stmtN->execute([$cardCodeARS]);
                    $nombreARS = trim($stmtN->fetchColumn() ?: $cardCodeARS);
                }
                if (empty($nombreARS)) $nombreARS = (!empty($cardCodeARS) ? $cardCodeARS : 'Seguro ARS');

                $arsInfoCobro = [
                    'NumAprobacion' => $numAprobacion,
                    'MontoAprobado' => $montoAprobadoVal,
                    'PorcentajeCober' => $pctCoberVal,
                    'MontoResponsable' => $montoRespVal,
                    'TotalBruto' => $totalBrutoVal,
                    'CardCode_ARS' => $cardCodeARS,
                    'NombreARS' => $nombreARS,
                    'TipoServicio' => trim($aprobacionARS['TipoServicio'] ?? 'CONSULTA MEDICA'),
                    'NumAfiliado' => trim($aprobacionARS['NumAfiliado'] ?? ''),
                    'esPorArticulos' => false
                ];
            }

            // =========================================================================
            // CASO 1: VENTA AL CONTADO / COPAGO ($extraDays <= 1)
            // =========================================================================
            // Redirigir a cobro.php SIN tocar la base de datos (ni secuencia, ni NCF, ni POSOINV, ni AprobacionARS).
            // La inserción atómica se realiza en guardarCobro() al confirmar el pago en caja.
            if ($extraDays <= 1) {
                $_SESSION['datos_cobro_pendiente'] = [
                    'total' => $docTotalFiscal,
                    'docEntry' => 0,
                    'pendiente' => false,
                    'ars' => $arsInfoCobro,
                    'factura_payload' => [
                        'header' => [
                            'CardCode' => $cardCode,
                            'CardName' => $cardName,
                            'U_FacNit' => $facNit,
                            'U_FacNom' => $facNom,
                            'U_FacSerie' => $facSerie,
                            'ExtraDays' => $extraDays,
                            'DocDate' => $docDate,
                            'U_TNegocio' => $tNegocio,
                            'U_terminal' => $terminal,
                            'U_user' => $usuario,
                            'DocTotalFiscal' => $docTotalFiscal,
                            'DiscPrcntFiscal' => $discPrcntFiscal,
                            'DiscSumFiscal' => $discSumFiscal,
                            'TotalImpuestoFiscal' => $totalImpuestoFiscal
                        ],
                        'lineasProcesadas' => $lineasProcesadas,
                        'articulos100' => $articulos100 ?? [],
                        'articulosCopagoARS' => $articulosCopagoARS ?? [],
                        'hayAprobacionesPorArticulo' => $hayAprobacionesPorArticulo,
                        'aprobacionARS' => $aprobacionARS,
                        'numAprobacion' => $numAprobacion,
                        'tieneCopagoARS' => $tieneCopagoARS,
                        'copagoObjetivo' => $copagoObjetivo ?? 0,
                        'coberturaARS' => $coberturaARS ?? 0,
                        'arsMontoAprobado' => $arsMontoAprobado ?? 0,
                        'arsPorcentajeCober' => $arsPorcentajeCober ?? 0,
                        'docTotal' => $docTotal,
                        'totalNeto' => $totalNeto,
                        'totalImpuesto' => $totalImpuesto
                    ]
                ];

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                echo json_encode([
                    'status' => 'success',
                    'redirect' => 'index.php?page=cobro'
                ]);
                return;
            }

            // =========================================================================
            // CASO 2: VENTA A CRÉDITO ($extraDays > 1) -> No pasa por cobro inmediato
            // =========================================================================
            $paso = '';

            // PASO 1: SECUENCIA
            $paso = "1-Secuencia";
            $serieDoc = 'F';
            $sqlSec = "MERGE secuencia WITH (SERIALIZABLE) AS T 
                       USING (VALUES (?)) AS U (tipo) ON U.tipo = T.tipo 
                       WHEN MATCHED THEN UPDATE SET T.secuencia = secuencia + 1 
                       WHEN NOT MATCHED THEN INSERT (secuencia, tipo) VALUES (1, U.tipo) 
                       OUTPUT inserted.secuencia;";
            $stmtSec = $pdo->prepare($sqlSec);
            $stmtSec->execute([$serieDoc]);
            $secDoc = $stmtSec->fetchColumn();
            if (!$secDoc) throw new Exception("No se generó la secuencia para " . $serieDoc);
            $NumAtCard = substr($serieDoc . str_pad($secDoc, 7, '0', STR_PAD_LEFT), 0, 20);

            // PASO 2: GENERAR NCF
            $paso = "2-NCF";
            $cNCF = '';
            $vNCFVence = null;

            if (!empty($facSerie)) {
                $sqlNCF = "UPDATE carga_ncf 
                           SET contador = contador + 1 
                           OUTPUT Inserted.serie, Inserted.contador, Inserted.vencimiento 
                           WHERE UPPER(serie) = ? 
                           AND contador < final 
                           AND id_carga = (SELECT MIN(id_carga) FROM carga_ncf WHERE UPPER(serie) = ? AND contador < final)";
                
                $stmtNCF = $pdo->prepare($sqlNCF);
                $stmtNCF->execute([$facSerie, $facSerie]);
                $ncfRow = $stmtNCF->fetch(PDO::FETCH_ASSOC);

                if (!$ncfRow) {
                    throw new Exception("No quedan NCF disponibles para la serie " . $facSerie);
                }

                if ($ncfRow) {
                    $stmtLen = $pdo->prepare("SELECT longitud FROM serie_ncf WHERE UPPER(serie) = ?");
                    $stmtLen->execute([$facSerie]);
                    $lenPad = (int)$stmtLen->fetchColumn();
                    if ($lenPad <= 0) $lenPad = (stripos($facSerie, 'B') === 0) ? 8 : 10;

                    $serieMayus = strtoupper(trim($ncfRow['serie'] ?: $facSerie));
                    $cNCF = substr($serieMayus . str_pad($ncfRow['contador'], $lenPad, '0', STR_PAD_LEFT), 0, 20);
                    $vNCFVence = $ncfRow['vencimiento'];
                    if (empty($vNCFVence) || $vNCFVence == '0000-00-00') $vNCFVence = null;
                }
            }

            // PASO 4: INSERTAR HEADER (POSOINV) - DocStatus = 'O' (Abierta / A Crédito)
            $paso = "4-InsertHeader";
            $sqlH = "INSERT INTO POSOINV (DocDate, CardCode, CardName, U_FacNit, U_FacNom, NumAtCard, U_SCIASerie, U_SCIADocNum, U_FacSerie, U_NCFVence, U_FACNUM, DocTotal, DiscPrcnt, DiscSum, VatSum, ExtraDays, Filler, U_user, U_terminal, U_TNegocio, DocStatus, ObjType)
                     VALUES (CAST(? AS DATE), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'O', 13)";
            $stmtH = $pdo->prepare($sqlH);
            $stmtH->execute([
                $docDate, $cardCode, $cardName, $facNit, $facNom, $NumAtCard, $serieDoc, (int)$secDoc,
                $facSerie, $vNCFVence, $cNCF, $docTotalFiscal, $discPrcntFiscal, $discSumFiscal,
                $totalImpuestoFiscal, $extraDays, $whsCodeHeader, substr(trim($usuario), 0, 15), substr(trim($terminal), 0, 20),
                $tNegocio
            ]);
            $docEntry = (int)$pdo->query('SELECT SCOPE_IDENTITY()')->fetchColumn();
            if (!$docEntry) {
                $docEntry = (int)$pdo->lastInsertId();
            }
            if (!$docEntry) throw new Exception("El DocEntry del Header se generó como vacío.");

            // PASO 5: INSERTAR DETALLE (POSINV1) Y REBAJAR INVENTARIO (posOITM & posOITW)
            $paso = "5-InsertDetail";
            $sqlD = "INSERT INTO POSINV1 (C_DocEntry, LineNum, ItemCode, Dscription, Quantity, PriceBefDi, Price, DiscPrcnt, LineTotal, VatPrcnt, VatSum, TaxCode, WhsCode)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtD = $pdo->prepare($sqlD);

            $stmtCheckInv  = $pdo->prepare("SELECT InvntItem, DfltWH FROM posOITM WHERE ItemCode = ?");
            $stmtUpdOITM   = $pdo->prepare("UPDATE posOITM SET OnHand = ISNULL(OnHand, 0) - ? WHERE ItemCode = ? AND InvntItem = 'Y'");
            $stmtCheckOITW = $pdo->prepare("SELECT COUNT(*) FROM posOITW WHERE ItemCode = ? AND WhsCode = ?");
            $stmtUpdOITW   = $pdo->prepare("UPDATE posOITW SET OnHand = ISNULL(OnHand, 0) - ? WHERE ItemCode = ? AND WhsCode = ?");
            $stmtInsOITW   = $pdo->prepare("INSERT INTO posOITW (ItemCode, WhsCode, OnHand) VALUES (?, ?, ?)");
            // Almacén fallback en caso de no venir especificado
            $stmtFirstWhs  = $pdo->query("SELECT TOP 1 WhsCode FROM posOWHS ORDER BY WhsCode ASC");
            $fallbackWhs   = $stmtFirstWhs ? $stmtFirstWhs->fetchColumn() : '01';

            foreach ($lineasProcesadas as $i => $lProc) {
                $itemCode = $lProc['ItemCode'];
                $cant = $lProc['Quantity'];
                $precioCatalogo = $lProc['PriceBefDi'];
                $precioReal = $lProc['Price'];
                $descP = $lProc['DiscPrcnt'];
                $lineTotal = $lProc['LineTotal'];
                $impP = $lProc['VatPrcnt'];
                $impuesto = $lProc['VatSum'];
                $whsCodeLine = $lProc['WhsCode'];

                $stmtD->execute([
                    intval($docEntry), intval($i), $itemCode, 
                    $lProc['Dscription'], $cant, $precioCatalogo, $precioReal, 
                    $descP, $lineTotal, $impP, $impuesto, $lProc['TaxCode'], 
                    $whsCodeLine
                ]);

                // REBAJA DE INVENTARIO: Solo para artículos con control de inventario (InvntItem = 'Y')
                if (!empty($itemCode) && $cant > 0) {
                    $stmtCheckInv->execute([$itemCode]);
                    $itemInfo = $stmtCheckInv->fetch(PDO::FETCH_ASSOC);

                    if ($itemInfo && ($itemInfo['InvntItem'] ?? '') === 'Y') {
                        // 1. Rebajar existencia en el maestro posOITM
                        $stmtUpdOITM->execute([$cant, $itemCode]);
                        // 2. Determinar almacén destino (línea -> almacén predeterminado -> primer almacén activo)
                        $targetWhs = !empty($whsCodeLine) ? $whsCodeLine : (!empty($itemInfo['DfltWH']) ? $itemInfo['DfltWH'] : $fallbackWhs);

                        if (!empty($targetWhs)) {
                            $stmtCheckOITW->execute([$itemCode, $targetWhs]);
                            if ($stmtCheckOITW->fetchColumn() > 0) {
                                // 3. Rebajar existencia en el almacén posOITW
                                $stmtUpdOITW->execute([$cant, $itemCode, $targetWhs]);
                            } else {
                                $stmtInsOITW->execute([$itemCode, $targetWhs, -$cant]);
                            }
                        }
                    }
                }
            }

            // PASO 6: REGISTRAR / ENLAZAR APROBACIÓN ARS
            $paso = "6-AprobacionARS";
            $sqlARS = "INSERT INTO AprobacionARS (
                            TipoSocio, CardCode, CardName, NumExpediente, NumAfiliado,
                            CardCode_ARS, NumAprobacion, FechaAprobacionT, FechaAprobacionD,
                            FechaVencimientoT, FechaVencimientoD, MontoAprobado, PorcentajeCober,
                            MontoResponsable, DocEntry_Factura, NumFactura, FechaFacturaT,
                            FechaFacturaD, TipoServicio, Observaciones, Status, U_user,
                            CreateDateT, CreateDateD
                       ) VALUES (
                            ?, ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            ?, ?, ?, 'P', ?,
                            GETDATE(), CAST(GETDATE() AS DATE)
                       )";
            $stmtARS = $pdo->prepare($sqlARS);

            // Consultar datos maestros del paciente para garantizar NumExpediente y afiliación
            $stmtPacInfo = $pdo->prepare("SELECT U_expediente, U_CadenaID, U_SubGrupo FROM posOCRD WHERE CardCode = ?");
            $stmtPacInfo->execute([$cardCode]);
            $pacInfoDb = $stmtPacInfo->fetch(PDO::FETCH_ASSOC);
            $pacExpFallback = ($pacInfoDb && !empty($pacInfoDb['U_expediente'])) ? intval($pacInfoDb['U_expediente']) : null;
            $pacAfilFallback = ($pacInfoDb && !empty($pacInfoDb['U_CadenaID'])) ? trim($pacInfoDb['U_CadenaID']) : '';
            $pacArsFallback = ($pacInfoDb && !empty($pacInfoDb['U_SubGrupo'])) ? trim($pacInfoDb['U_SubGrupo']) : '';

            if ($hayAprobacionesPorArticulo) {
                if (!empty($articulos100)) {
                    foreach ($articulos100 as $item100) {
                        $ap = $item100['ap'];
                        $arsTipoSocio = substr(trim($ap['TipoSocio'] ?? 'P'), 0, 15);
                        $arsCardCode = substr(trim($ap['CardCode'] ?? $cardCode), 0, 100);
                        $arsCardName = substr(trim($ap['CardName'] ?? $cardName), 0, 100);
                        $arsNumExp = !empty($ap['NumExpediente']) ? intval($ap['NumExpediente']) : $pacExpFallback;
                        $arsNumAfiliado = !empty($ap['NumAfiliado']) ? substr(trim($ap['NumAfiliado']), 0, 50) : substr($pacAfilFallback, 0, 50);
                        $arsCardCodeARS = !empty($ap['CardCode_ARS']) ? substr(trim($ap['CardCode_ARS']), 0, 100) : substr($pacArsFallback, 0, 100);
                        $numAp = substr(trim($ap['NumAprobacion'] ?? ''), 0, 50);

                        $arsFechaAprobT = !empty($ap['FechaAprobacionT']) ? $ap['FechaAprobacionT'] : date('Y-m-d H:i:s');
                        $arsFechaAprobD = !empty($ap['FechaAprobacionD']) ? $ap['FechaAprobacionD'] : substr($arsFechaAprobT, 0, 10);
                        $arsFechaVencT = !empty($ap['FechaVencimientoT']) ? $ap['FechaVencimientoT'] : null;
                        $arsFechaVencD = !empty($ap['FechaVencimientoD']) ? $ap['FechaVencimientoD'] : ($arsFechaVencT ? substr($arsFechaVencT, 0, 10) : null);

                        $rawTipoServ = trim($ap['TipoServicio'] ?? $item100['Dscription']);
                        $arsTipoServicio = strtoupper(str_replace(['á','é','í','ó','ú','Á','É','Í','Ó','Ú'], ['a','e','i','o','u','A','E','I','O','U'], $rawTipoServ));
                        $arsTipoServicio = substr($arsTipoServicio, 0, 50);
                        $arsObservaciones = substr(trim($ap['Observaciones'] ?? ('Atención médica 100% cubierta por ARS: ' . $item100['Dscription'])), 0, 254);

                        $stmtARS->execute([
                            $arsTipoSocio, $arsCardCode, $arsCardName, $arsNumExp, $arsNumAfiliado,
                            $arsCardCodeARS, $numAp, $arsFechaAprobT, $arsFechaAprobD,
                            $arsFechaVencT, $arsFechaVencD, $item100['MontoAprobado'], 100.00,
                            0.00, NULL, 'COBERTURA 100%', date('Y-m-d H:i:s'),
                            date('Y-m-d'), $arsTipoServicio, $arsObservaciones, substr(trim($usuario), 0, 20)
                        ]);
                    }
                }

                foreach ($articulosCopagoARS as $itemCopago) {
                    $ap = $itemCopago['ap'];
                    $arsTipoSocio = substr(trim($ap['TipoSocio'] ?? 'P'), 0, 15);
                    $arsCardCode = substr(trim($ap['CardCode'] ?? $cardCode), 0, 100);
                    $arsCardName = substr(trim($ap['CardName'] ?? $cardName), 0, 100);
                    $arsNumExp = !empty($ap['NumExpediente']) ? intval($ap['NumExpediente']) : $pacExpFallback;
                    $arsNumAfiliado = !empty($ap['NumAfiliado']) ? substr(trim($ap['NumAfiliado']), 0, 50) : substr($pacAfilFallback, 0, 50);
                    $arsCardCodeARS = !empty($ap['CardCode_ARS']) ? substr(trim($ap['CardCode_ARS']), 0, 100) : substr($pacArsFallback, 0, 100);
                    $numAp = substr(trim($ap['NumAprobacion'] ?? ''), 0, 50);

                    $arsFechaAprobT = !empty($ap['FechaAprobacionT']) ? $ap['FechaAprobacionT'] : date('Y-m-d H:i:s');
                    $arsFechaAprobD = !empty($ap['FechaAprobacionD']) ? $ap['FechaAprobacionD'] : substr($arsFechaAprobT, 0, 10);
                    $arsFechaVencT = !empty($ap['FechaVencimientoT']) ? $ap['FechaVencimientoT'] : null;
                    $arsFechaVencD = !empty($ap['FechaVencimientoD']) ? $ap['FechaVencimientoD'] : ($arsFechaVencT ? substr($arsFechaVencT, 0, 10) : null);

                    $rawTipoServ = trim($ap['TipoServicio'] ?? $itemCopago['Dscription']);
                    $arsTipoServicio = strtoupper(str_replace(['á','é','í','ó','ú','Á','É','Í','Ó','Ú'], ['a','e','i','o','u','A','E','I','O','U'], $rawTipoServ));
                    $arsTipoServicio = substr($arsTipoServicio, 0, 50);
                    $arsObservaciones = substr(trim($ap['Observaciones'] ?? ('Copago paciente por: ' . $itemCopago['Dscription'])), 0, 254);

                    $stmtARS->execute([
                        $arsTipoSocio, $arsCardCode, $arsCardName, $arsNumExp, $arsNumAfiliado,
                        $arsCardCodeARS, $numAp, $arsFechaAprobT, $arsFechaAprobD,
                        $arsFechaVencT, $arsFechaVencD, $itemCopago['MontoAprobado'], $itemCopago['PorcentajeCober'],
                        $itemCopago['MontoResponsable'], intval($docEntry), $NumAtCard, date('Y-m-d H:i:s'),
                        $docDate, $arsTipoServicio, $arsObservaciones, substr(trim($usuario), 0, 20)
                    ]);
                }
            } elseif (!empty($numAprobacion)) {
                $arsTipoSocio = substr(trim($aprobacionARS['TipoSocio'] ?? 'P'), 0, 15);
                $arsCardCode = substr(trim($aprobacionARS['CardCode'] ?? $cardCode), 0, 100);
                $arsCardName = substr(trim($aprobacionARS['CardName'] ?? $cardName), 0, 100);
                $arsNumExp = !empty($aprobacionARS['NumExpediente']) ? intval($aprobacionARS['NumExpediente']) : $pacExpFallback;
                $arsNumAfiliado = !empty($aprobacionARS['NumAfiliado']) ? substr(trim($aprobacionARS['NumAfiliado']), 0, 50) : substr($pacAfilFallback, 0, 50);
                $arsCardCodeARS = !empty($aprobacionARS['CardCode_ARS']) ? substr(trim($aprobacionARS['CardCode_ARS']), 0, 100) : substr($pacArsFallback, 0, 100);

                $arsFechaAprobT = !empty($aprobacionARS['FechaAprobacionT']) ? $aprobacionARS['FechaAprobacionT'] : date('Y-m-d H:i:s');
                $arsFechaAprobD = !empty($aprobacionARS['FechaAprobacionD']) ? $aprobacionARS['FechaAprobacionD'] : substr($arsFechaAprobT, 0, 10);
                $arsFechaVencT = !empty($aprobacionARS['FechaVencimientoT']) ? $aprobacionARS['FechaVencimientoT'] : null;
                $arsFechaVencD = !empty($aprobacionARS['FechaVencimientoD']) ? $aprobacionARS['FechaVencimientoD'] : ($arsFechaVencT ? substr($arsFechaVencT, 0, 10) : null);

                $montoAprobFinal = $tieneCopagoARS ? $arsMontoAprobado : floatval($aprobacionARS['MontoAprobado'] ?? 0);
                $porcentajeFinal = $tieneCopagoARS ? $arsPorcentajeCober : floatval($aprobacionARS['PorcentajeCober'] ?? 0);
                $montoRespFinal = $tieneCopagoARS ? $copagoObjetivo : floatval($aprobacionARS['MontoResponsable'] ?? 0);

                $rawTipoServ = trim($aprobacionARS['TipoServicio'] ?? 'CONSULTA MEDICA');
                $arsTipoServicio = strtoupper(str_replace(['á','é','í','ó','ú','Á','É','Í','Ó','Ú'], ['a','e','i','o','u','A','E','I','O','U'], $rawTipoServ));
                $arsTipoServicio = substr($arsTipoServicio, 0, 50);
                $arsObservaciones = substr(trim($aprobacionARS['Observaciones'] ?? ''), 0, 254);

                $stmtARS->execute([
                    $arsTipoSocio, $arsCardCode, $arsCardName, $arsNumExp, $arsNumAfiliado,
                    $arsCardCodeARS, $numAprobacion, $arsFechaAprobT, $arsFechaAprobD,
                    $arsFechaVencT, $arsFechaVencD, $montoAprobFinal, $porcentajeFinal,
                    $montoRespFinal, intval($docEntry), $NumAtCard, date('Y-m-d H:i:s'),
                    $docDate, $arsTipoServicio, $arsObservaciones, substr(trim($usuario), 0, 20)
                ]);
            }

            // PASO 7: REGISTRAR EN ecf_xml_invoice PARA DGII
            $paso = "7-EcfXml";
            if (!empty($cNCF)) {
                require_once __DIR__ . '/../config/EcfHelper.php';
                EcfHelper::registrarEcfXml(
                    $pdo,
                    $docEntry,
                    $NumAtCard,
                    $facSerie,
                    $cNCF,
                    $vNCFVence,
                    $facNit,
                    $extraDays,
                    $docDate,
                    'INVOICE'
                );
            }

            $pdo->commit();
            
            echo json_encode([
                'status' => 'success', 
                'msg' => 'Factura a Crédito creada: ' . $NumAtCard, 
                'NCF' => $cNCF, 
                'DocEntry' => $docEntry,
                'redirect' => ''
            ]);

        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(400);
            // Devolvemos el PASO exacto donde falló para depuración
            echo json_encode(['status' => 'error', 'msg' => "Error en paso $paso: " . $e->getMessage()]);
        }
    }

    /**
     * Consulta Histórica General de Facturas de Venta
     */
    public function consulta() {
        $database = new Database();
        $pdo = $database->getConnection();

        // 1. Obtener listado histórico de facturas con información de Paciente, ARS y Notas de Crédito
        $sqlFacturas = "
            SELECT 
                h.C_DocEntry,
                h.DocNum,
                h.NumAtCard,
                h.DocDate,
                h.DocDueDate,
                h.CardCode,
                h.CardName,
                ISNULL(h.U_FACNUM, h.NumAtCard) AS U_FACNUM,
                h.U_FacNit,
                h.U_FacSerie,
                h.U_NCFVence,
                h.U_FacNom,
                h.ExtraDays,
                ISNULL(h.DocTotal, 0) AS DocTotal,
                ISNULL(h.VatSum, 0) AS VatSum,
                ISNULL(h.DiscSum, 0) AS DiscSum,
                h.Comments,
                ISNULL(h.U_user, 'Cajero') AS U_user,
                ISNULL(h.CANCELED, 'N') AS CANCELED,
                h.DocStatus,
                -- Info Paciente
                pac.U_NIT AS PacienteNIT,
                pac.U_expediente AS NumExpediente,
                pac.U_SubGrupo AS SubGrupoARS,
                -- Info ARS (si aplica, agregada para soportar múltiples aprobaciones por factura)
                a.IdARS,
                a.NumAprobacion,
                a.CardCode_ARS,
                COALESCE(ars.CardName, a.CardCode_ARS, pac_ars.CardName) AS NombreARS,
                ISNULL(a.CoberturaARS, 0) AS CoberturaARS,
                ISNULL(a.CopagoPaciente, 0) AS CopagoPaciente,
                a.TipoServicio,
                a.StatusARS,
                a.CantidadAprobaciones,
                -- Info Nota de Crédito (si fue afectada/anulada)
                nc.C_DocEntry AS IdNotaCredito,
                nc.NumAtCard AS NumNotaCredito,
                nc.U_FACNUM AS NCF_NotaCredito,
                nc.DocTotal AS MontoNotaCredito,
                -- Info Nota de Débito (si fue modificada)
                nd.C_DocEntry AS IdNotaDebito,
                nd.NumAtCard AS NumNotaDebito,
                nd.U_FACNUM AS NCF_NotaDebito,
                nd.DocTotal AS MontoNotaDebito,
                -- Info e-CF / Sellador DGII
                ecf.id_record AS EcfIdRecord,
                ecf.xml_ecf_status_name AS EcfStatusName,
                ecf.xml_security_code AS EcfSecurityCode
            FROM POSOINV h WITH (NOLOCK)
            LEFT JOIN posOCRD pac WITH (NOLOCK) ON h.CardCode = pac.CardCode
            OUTER APPLY (
                SELECT 
                    COUNT(a_sub.IdARS) AS CantidadAprobaciones,
                    MIN(a_sub.IdARS) AS IdARS,
                    STRING_AGG(a_sub.NumAprobacion, ', ') AS NumAprobacion,
                    MAX(a_sub.CardCode_ARS) AS CardCode_ARS,
                    SUM(a_sub.MontoAprobado) AS CoberturaARS,
                    SUM(a_sub.MontoResponsable) AS CopagoPaciente,
                    STRING_AGG(a_sub.TipoServicio, ' | ') AS TipoServicio,
                    MIN(a_sub.Status) AS StatusARS
                FROM AprobacionARS a_sub WITH (NOLOCK)
                WHERE a_sub.DocEntry_Factura = h.C_DocEntry AND a_sub.Status <> 'R'
            ) a
            LEFT JOIN posOCRD ars WITH (NOLOCK) ON ars.CardCode = a.CardCode_ARS
            LEFT JOIN posOCRD pac_ars WITH (NOLOCK) ON pac_ars.CardCode = pac.U_SubGrupo
            LEFT JOIN POSORIN nc WITH (NOLOCK) ON h.C_DocEntry = nc.C_DocOrigenNum AND ISNULL(nc.CANCELED, 'N') <> 'Y'
            OUTER APPLY (
                SELECT TOP 1 
                    nd_inner.C_DocEntry,
                    nd_inner.NumAtCard,
                    nd_inner.U_FACNUM,
                    nd_inner.DocTotal
                FROM POSODIN nd_inner WITH (NOLOCK)
                WHERE nd_inner.C_DocOrigenNum = h.C_DocEntry AND ISNULL(nd_inner.CANCELED, 'N') <> 'Y'
                ORDER BY nd_inner.C_DocEntry DESC
            ) nd
            OUTER APPLY (
                SELECT TOP 1 
                    ecf_inner.id_record,
                    ecf_inner.xml_ecf_status_name,
                    ecf_inner.xml_security_code
                FROM ecf_xml_invoice ecf_inner WITH (NOLOCK)
                WHERE ecf_inner.doc_id = h.C_DocEntry 
                   OR (ecf_inner.ecf = h.U_FACNUM AND h.U_FACNUM IS NOT NULL AND h.U_FACNUM <> '')
                ORDER BY ecf_inner.id_record DESC
            ) ecf
            ORDER BY h.C_DocEntry DESC
        ";
        $stmtFac = $pdo->query($sqlFacturas);
        $facturas = $stmtFac ? $stmtFac->fetchAll(PDO::FETCH_ASSOC) : [];



        // 3. Obtener series NCF disponibles correspondientes exclusivamente a Facturas de Venta
        $sqlSeries = "
            SELECT DISTINCT serie, descripcion
            FROM (
                SELECT UPPER(serie) AS serie, descripcion
                FROM serie_ncf
                WHERE activo = 'Y'
                  AND UPPER(serie) NOT IN ('E33', 'B03', 'E34', 'B04', 'E41', 'B11', 'E42', 'B12', 'E43', 'B13', 'E47', 'B17')
                  AND UPPER(serie) NOT LIKE '%33'
                  AND UPPER(serie) NOT LIKE '%34'
                  AND UPPER(serie) NOT LIKE '%03'
                  AND UPPER(serie) NOT LIKE '%04'
                  AND UPPER(descripcion) NOT LIKE '%NOTA DE CR%'
                  AND UPPER(descripcion) NOT LIKE '%NOTA DE D%'
                UNION
                SELECT DISTINCT UPPER(U_FacSerie) AS serie,
                       CASE 
                           WHEN UPPER(U_FacSerie) = 'E31' THEN 'COMPROBANTE CREDITO FISCAL ELECTRONICO'
                           WHEN UPPER(U_FacSerie) = 'E32' THEN 'COMPROBANTE CONSUMIDOR FINAL ELECTRONICO'
                           WHEN UPPER(U_FacSerie) = 'E44' THEN 'REGIMENES ESPECIALES ELECTRONICO'
                           WHEN UPPER(U_FacSerie) = 'E45' THEN 'GUBERNAMENTAL ELECTRONICO'
                           WHEN UPPER(U_FacSerie) = 'B01' THEN 'FACTURA CREDITO FISCAL'
                           WHEN UPPER(U_FacSerie) = 'B02' THEN 'FACTURA CONSUMIDOR FINAL'
                           ELSE 'FACTURA FISCAL'
                       END AS descripcion
                FROM POSOINV
                WHERE U_FacSerie IS NOT NULL AND LTRIM(RTRIM(U_FacSerie)) <> ''
                  AND UPPER(U_FacSerie) NOT IN ('E33', 'B03', 'E34', 'B04')
                  AND UPPER(U_FacSerie) NOT LIKE '%33'
                  AND UPPER(U_FacSerie) NOT LIKE '%34'
            ) t
            ORDER BY serie ASC
        ";
        $stmtSeries = $pdo->query($sqlSeries);
        $seriesNCF = $stmtSeries ? $stmtSeries->fetchAll(PDO::FETCH_ASSOC) : [];


        include __DIR__ . '/../views/Facturacion/consulta_facturas.php';
    }

    /**
     * AJAX: Obtener el detalle completo de una Factura de Venta
     */
    public function obtenerDetalleFactura() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $database = new Database();
            $pdo = $database->getConnection();

            $docEntry = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($docEntry <= 0) {
                throw new Exception("ID de factura inválido.");
            }

            // 1. Cabecera de la Factura
            $sqlH = "
                SELECT 
                    h.C_DocEntry,
                    h.DocNum,
                    h.NumAtCard,
                    h.DocDate,
                    h.DocDueDate,
                    h.CardCode,
                    h.CardName,
                    ISNULL(h.U_FACNUM, h.NumAtCard) AS U_FACNUM,
                    h.U_FacNit,
                    h.U_FacSerie,
                    h.U_NCFVence,
                    h.U_FacNom,
                    h.ExtraDays,
                    ISNULL(h.DocTotal, 0) AS DocTotal,
                    ISNULL(h.VatSum, 0) AS VatSum,
                    ISNULL(h.DiscPrcnt, 0) AS DiscPrcnt,
                    ISNULL(h.DiscSum, 0) AS DiscSum,
                    h.Comments,
                    ISNULL(h.U_user, 'Cajero') AS U_user,
                    h.U_terminal,
                    h.U_TNegocio,
                    ISNULL(h.CANCELED, 'N') AS CANCELED,
                    h.DocStatus,
                    -- Paciente
                    pac.U_NIT AS PacienteNIT,
                    pac.U_expediente AS NumExpediente,
                    pac.Phone1,
                    pac.Address,
                    pac.U_SubGrupo AS SubGrupoARS,
                    -- Condición de Pago
                    octg.PymntGroup AS CondicionPagoNombre,
                    -- Info e-CF / Sellador DGII
                    ecf.id_record AS EcfIdRecord,
                    ecf.xml_ecf_status_name AS EcfStatusName,
                    ecf.xml_security_code AS EcfSecurityCode
                FROM POSOINV h WITH (NOLOCK)
                LEFT JOIN posOCRD pac WITH (NOLOCK) ON h.CardCode = pac.CardCode
                LEFT JOIN posOCTG octg WITH (NOLOCK) ON octg.ExtraDays = h.ExtraDays
                OUTER APPLY (
                    SELECT TOP 1 
                        ecf_inner.id_record,
                        ecf_inner.xml_ecf_status_name,
                        ecf_inner.xml_security_code
                    FROM ecf_xml_invoice ecf_inner WITH (NOLOCK)
                    WHERE ecf_inner.doc_id = h.C_DocEntry 
                       OR (ecf_inner.ecf = h.U_FACNUM AND h.U_FACNUM IS NOT NULL AND h.U_FACNUM <> '')
                    ORDER BY ecf_inner.id_record DESC
                ) ecf
                WHERE h.C_DocEntry = ?
            ";
            $stmtH = $pdo->prepare($sqlH);
            $stmtH->execute([$docEntry]);
            $cabecera = $stmtH->fetch(PDO::FETCH_ASSOC);

            if (!$cabecera) {
                throw new Exception("Factura #$docEntry no encontrada.");
            }

            // 2. Líneas de la Factura (POSINV1)
            $sqlD = "
                SELECT 
                    d.LineNum,
                    d.ItemCode,
                    d.Dscription,
                    ISNULL(d.Quantity, 1) AS Quantity,
                    ISNULL(d.PriceBefDi, d.Price) AS PriceBefDi,
                    ISNULL(d.Price, 0) AS Price,
                    ISNULL(d.DiscPrcnt, 0) AS DiscPrcnt,
                    ISNULL(d.LineTotal, 0) AS LineTotal,
                    ISNULL(d.VatPrcnt, 0) AS VatPrcnt,
                    ISNULL(d.VatSum, 0) AS VatSum,
                    d.TaxCode,
                    d.WhsCode,
                    ISNULL(g.ItmsGrpNam, 'Servicio / Artículo') AS GrupoArticulo
                FROM POSINV1 d WITH (NOLOCK)
                LEFT JOIN posOITM i WITH (NOLOCK) ON d.ItemCode = i.ItemCode
                LEFT JOIN posOITB g WITH (NOLOCK) ON i.ItmsGrpCod = g.ItmsGrpCod
                WHERE d.C_DocEntry = ?
                ORDER BY d.LineNum ASC
            ";
            $stmtD = $pdo->prepare($sqlD);
            $stmtD->execute([$docEntry]);
            $lineas = $stmtD->fetchAll(PDO::FETCH_ASSOC);

            // 3. Información ARS si existe
            $sqlARS = "
                SELECT 
                    a.IdARS,
                    a.NumAprobacion,
                    a.CardCode_ARS,
                    COALESCE(ars.CardName, a.CardCode_ARS) AS NombreARS,
                    a.NumAfiliado,
                    a.NumExpediente,
                    a.FechaAprobacionD,
                    a.MontoAprobado,
                    a.PorcentajeCober,
                    a.MontoResponsable,
                    a.TipoServicio,
                    a.Observaciones,
                    a.Status,
                    a.NumFacturaARS
                FROM AprobacionARS a WITH (NOLOCK)
                LEFT JOIN posOCRD ars WITH (NOLOCK) ON a.CardCode_ARS = ars.CardCode
                WHERE a.DocEntry_Factura = ? AND a.Status <> 'R'
            ";
            $stmtARS = $pdo->prepare($sqlARS);
            $stmtARS->execute([$docEntry]);
            $aprobacionesARS = $stmtARS->fetchAll(PDO::FETCH_ASSOC);
            $aprobacionARS = !empty($aprobacionesARS) ? $aprobacionesARS[0] : null;

            // 4. Información de Nota de Crédito si existe
            $sqlNC = "
                SELECT 
                    nc.C_DocEntry,
                    nc.NumAtCard,
                    nc.U_FACNUM,
                    nc.DocDate,
                    nc.DocTotal,
                    nc.U_TipoNC,
                    nc.Comments,
                    ISNULL(nc.CANCELED, 'N') AS CANCELED
                FROM POSORIN nc WITH (NOLOCK)
                WHERE nc.C_DocOrigenNum = ? AND ISNULL(nc.CANCELED, 'N') <> 'Y'
            ";
            $stmtNC = $pdo->prepare($sqlNC);
            $stmtNC->execute([$docEntry]);
            $notaCredito = $stmtNC->fetch(PDO::FETCH_ASSOC);

            // 5. Información de Nota de Débito si existe (Modificación) - Integración con NotaDebitoController
            require_once __DIR__ . '/NotaDebitoController.php';
            $notaDebito = NotaDebitoController::obtenerNDPorFactura($pdo, $docEntry);

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'cabecera'        => $cabecera,
                    'lineas'          => $lineas,
                    'aprobacionARS'   => $aprobacionARS ?: null,
                    'aprobacionesARS' => $aprobacionesARS ?: [],
                    'notaCredito'     => $notaCredito ?: null,
                    'notaDebito'      => $notaDebito ?: null
                ]
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
        }
    }
}
?>