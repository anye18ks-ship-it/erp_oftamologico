<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class ARSCodController {

    /**
     * Vista principal / Historial de Aprobaciones ARS
     */
    public function index() {
        $database = new Database();
        $pdo = $database->getConnection();

        // Obtener historial reciente de aprobaciones
        $sql = "SELECT TOP 100 
                    a.IdARS, a.TipoSocio, a.CardCode, a.CardName, a.NumExpediente, a.NumAfiliado,
                    a.CardCode_ARS, a.NumAprobacion, a.FechaAprobacionT, a.FechaAprobacionD,
                    a.FechaVencimientoT, a.FechaVencimientoD, a.MontoAprobado, a.PorcentajeCober,
                    a.MontoResponsable, a.DocEntry_Factura, a.NumFactura, a.FechaFacturaT,
                    a.FechaFacturaD, a.TipoServicio, a.Observaciones, a.Status, a.U_user,
                    a.CreateDateT, a.CreateDateD,
                    COALESCE(ars.CardName, NULLIF(a.CardCode_ARS, ''), 'SIN ASEGURADORA') AS NombreARS
                FROM AprobacionARS a
                LEFT JOIN posOCRD pac ON a.CardCode = pac.CardCode
                LEFT JOIN posOCRD ars ON ars.QryGroup1 = 'Y' AND (
                    ars.CardCode = a.CardCode_ARS 
                    OR (NULLIF(a.CardCode_ARS, '') IS NULL AND ars.CardCode = pac.U_SubGrupo)
                )
                ORDER BY a.IdARS DESC";
        $stmt = $pdo->query($sql);
        $aprobaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/Facturacion/num_aprobacion_ars.php';

    }



    /**
     * Vista de Consulta de Aprobaciones ARS y Generador de Reportes
     */
    public function consulta() {
        $database = new Database();
        $pdo = $database->getConnection();

        // 1. Obtener lista de Aseguradoras activas (QryGroup1 = 'Y' o GroupCode = 4)
        $sqlAseguradoras = "SELECT CardCode, CardName, U_SubGrupo, U_NIT 
                            FROM posOCRD 
                            WHERE QryGroup1 = 'Y' OR GroupCode = 4 
                            ORDER BY CardName ASC";
        $stmtARS = $pdo->query($sqlAseguradoras);
        $listaARS = $stmtARS->fetchAll(PDO::FETCH_ASSOC);

        // 2. Obtener historial completo de aprobaciones
        $sql = "SELECT 
                    a.IdARS, a.TipoSocio, a.CardCode, a.CardName, a.NumExpediente, a.NumAfiliado,
                    a.CardCode_ARS, a.NumAprobacion, 
                    a.FechaAprobacionD,
                    a.FechaAprobacionT,
                    FORMAT(a.FechaAprobacionD, 'dd/MM/yyyy') as FechaAprobacionFormato,
                    a.FechaVencimientoD,
                    FORMAT(a.FechaVencimientoD, 'dd/MM/yyyy') as FechaVencimientoFormato,
                    ISNULL(a.MontoAprobado, 0) as MontoAprobado,
                    ISNULL(a.PorcentajeCober, 0) as PorcentajeCober,
                    ISNULL(a.MontoResponsable, 0) as MontoResponsable,
                    a.DocEntry_Factura, a.NumFactura, 
                    FORMAT(a.FechaFacturaD, 'dd/MM/yyyy') as FechaFacturaFormato,
                    a.DocEntry_FacturaARS, a.NumFacturaARS,
                    FORMAT(a.FechaFacturaARS, 'dd/MM/yyyy') as FechaFacturaARSFormato,
                    a.TipoServicio, a.Observaciones, a.Status, a.U_user,
                    FORMAT(a.CreateDateT, 'dd/MM/yyyy hh:mm tt') as CreateDateFormato,
                    COALESCE(ars.CardName, NULLIF(a.CardCode_ARS, ''), 'SIN ASEGURADORA') as NombreARS,
                    COALESCE(ars.CardCode, a.CardCode_ARS, '') as CodigoARSReal,
                    ISNULL(pac.U_NIT, '') as CedulaPaciente,
                    ISNULL(pac.Phone1, '') as TelefonoPaciente
                FROM AprobacionARS a
                LEFT JOIN posOCRD pac ON a.CardCode = pac.CardCode
                LEFT JOIN posOCRD ars ON (ars.QryGroup1 = 'Y' OR ars.GroupCode = 4) AND (
                    ars.CardCode = a.CardCode_ARS 
                    OR (NULLIF(a.CardCode_ARS, '') IS NULL AND ars.CardCode = pac.U_SubGrupo)
                )
                ORDER BY a.IdARS DESC";
        $stmt = $pdo->query($sql);
        $aprobaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Separación de Pendientes y Facturadas
        $aprobacionesPendientes = [];
        $aprobacionesFacturadas = [];

        $totalAprobaciones = count($aprobaciones);
        $totalPendientes = 0;
        $montoTotalPendiente = 0;
        $totalFacturadas = 0;
        $montoTotalFacturado = 0;
        $totalMontoResponsable = 0;

        foreach ($aprobaciones as $ap) {
            $st = strtoupper($ap['Status'] ?? 'P');
            $montoAp = (float)($ap['MontoAprobado'] ?? 0);
            $totalMontoResponsable += (float)($ap['MontoResponsable'] ?? 0);

            if ($st === 'P') {
                $aprobacionesPendientes[] = $ap;
                $totalPendientes++;
                $montoTotalPendiente += $montoAp;
            } elseif ($st === 'F') {
                $aprobacionesFacturadas[] = $ap;
                $totalFacturadas++;
                $montoTotalFacturado += $montoAp;
            }
        }

        // Ordenar facturadas agrupadas por lote de factura ARS para que las que pertenecen al mismo lote estén siempre juntas
        usort($aprobacionesFacturadas, function($a, $b) {
            $fecA = $a['FechaFacturaARS'] ?? '';
            $fecB = $b['FechaFacturaARS'] ?? '';
            if ($fecA !== $fecB && !empty($fecA) && !empty($fecB)) {
                return strcmp($fecB, $fecA);
            }
            $docA = (int)($a['DocEntry_FacturaARS'] ?? 0);
            $docB = (int)($b['DocEntry_FacturaARS'] ?? 0);
            if ($docA !== $docB) {
                return $docB <=> $docA;
            }
            $ncfA = trim($a['NumFacturaARS'] ?? '');
            $ncfB = trim($b['NumFacturaARS'] ?? '');
            if ($docA === 0 && $docB === 0 && $ncfA !== $ncfB) {
                return strcmp($ncfB, $ncfA);
            }
            return ((int)($b['IdARS'] ?? 0)) <=> ((int)($a['IdARS'] ?? 0));
        });

        // 4. Series NCF disponibles para facturar a Aseguradoras (E31 por defecto electrónico)
        $sqlSeriesNCF = "SELECT DISTINCT c.serie, s.descripcion, s.tipo_ncf 
                         FROM carga_ncf c
                         INNER JOIN serie_ncf s ON c.serie = s.serie
                         WHERE c.contador < c.final AND c.serie = 'E31'
                         ORDER BY c.serie ASC";
        $stmtSeries = $pdo->query($sqlSeriesNCF);
        $seriesNCFDisponibles = $stmtSeries ? $stmtSeries->fetchAll(PDO::FETCH_ASSOC) : [];

        // 5. Facturas de Lotes ARS y Gestión de Cobro (con Trazabilidad y Priorización por Estado)
        $sqlFacturasCobro = "
            SELECT 
                f.C_DocEntry, 
                f.DocEntry, 
                f.DocNum, 
                f.DocDate,
                FORMAT(f.DocDate, 'dd/MM/yyyy') as FechaDocFormato,
                f.CardCode, 
                f.CardName, 
                ISNULL(f.NumAtCard, '') as NumAtCard, 
                ISNULL(f.U_FACNUM, '') as U_FACNUM, 
                ISNULL(f.U_FacSerie, 'E31') as U_FacSerie,
                ISNULL(f.DocTotal, 0) as DocTotal,
                ISNULL(f.CANCELED, 'N') as CANCELED,
                ISNULL(f.DocStatus, 'O') as DocStatus,
                ISNULL(f.U_user, 'ADMIN') as U_user,
                CASE 
                    WHEN f.CANCELED = 'Y' THEN 'CANCELADA'
                    WHEN t.Estado IS NOT NULL THEN t.Estado
                    ELSE 'PENDIENTE'
                END as EstadoCobro,
                t.FechaEstado as FechaEstadoCobro,
                FORMAT(t.FechaEstado, 'dd/MM/yyyy hh:mm tt') as FechaEstadoFormato,
                t.Motivo as MotivoRechazo,
                t.ReferenciaPago,
                t.DocEntry_NC,
                t.NCF_NC,
                t.Usuario as UsuarioAccion,
                (SELECT COUNT(*) FROM AprobacionARS ap WHERE ap.DocEntry_FacturaARS = f.C_DocEntry) as CantidadAprobaciones,
                (SELECT ISNULL(SUM(MontoAprobado), 0) FROM AprobacionARS ap WHERE ap.DocEntry_FacturaARS = f.C_DocEntry) as MontoReclamaciones
            FROM POSOINV f
            OUTER APPLY (
                SELECT TOP 1 *
                FROM trazabilidad_reclamacion_ars tr
                WHERE tr.DocEntry_Factura = f.C_DocEntry
                ORDER BY tr.id_trazabilidad DESC
            ) t
            WHERE (f.Filler = 'LOTE ARS' OR f.CardCode IN (SELECT CardCode FROM posOCRD WHERE QryGroup1 = 'Y' OR GroupCode = 4))
            ORDER BY 
                CASE 
                    WHEN (t.Estado IS NULL OR t.Estado = 'PENDIENTE') AND ISNULL(f.CANCELED, 'N') <> 'Y' THEN 0 
                    WHEN t.Estado = 'RECHAZADA' AND ISNULL(f.CANCELED, 'N') <> 'Y' THEN 1
                    ELSE 2 
                END ASC,
                f.CardName ASC,
                f.DocDate DESC,
                f.C_DocEntry DESC";
        $stmtFacturasCobro = $pdo->query($sqlFacturasCobro);
        $facturasCobroARS = $stmtFacturasCobro ? $stmtFacturasCobro->fetchAll(PDO::FETCH_ASSOC) : [];

        $totalPendientesCobroARS = 0;
        $montoPendientesCobroARS = 0;
        $totalCobradasARS = 0;
        $montoCobradasARS = 0;
        $totalRechazadasARS = 0;
        $montoRechazadasARS = 0;

        foreach ($facturasCobroARS as $fc) {
            $est = strtoupper($fc['EstadoCobro'] ?? 'PENDIENTE');
            $tot = (float)($fc['DocTotal'] ?? 0);
            if ($fc['CANCELED'] !== 'Y') {
                if ($est === 'PENDIENTE') {
                    $totalPendientesCobroARS++;
                    $montoPendientesCobroARS += $tot;
                } elseif ($est === 'COBRADA') {
                    $totalCobradasARS++;
                    $montoCobradasARS += $tot;
                } elseif ($est === 'RECHAZADA') {
                    $totalRechazadasARS++;
                    $montoRechazadasARS += $tot;
                }
            }
        }

        include __DIR__ . '/../views/Facturacion/consulta_aprobaciones.php';
    }
    
    /**
     * Generar Reporte PDF: Relación General de Reclamaciones Médicas
     */
    public function reporteAprobacion() {
        $docEntry = isset($_GET['docEntry']) ? (int)$_GET['docEntry'] : null;
        $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('n');
        $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y');
        $ars = isset($_GET['ars']) ? trim($_GET['ars']) : 'TODAS';

        require_once __DIR__ . '/../reports/ReporteAprobacion.php';
        $reporte = new ReporteAprobacion($mes, $anio, $ars, $docEntry);
        $reporte->generar('I');
    }

    /**
     * Generar Reporte PDF: Factura Especial de Aseguradora (Estilo Factoring.pdf)
     */
    public function reporteFactAprobacion() {
        $docEntry = isset($_GET['docEntry']) ? (int)$_GET['docEntry'] : null;
        if (!$docEntry) {
            die("Debe indicar el DocEntry de la factura.");
        }

        require_once __DIR__ . '/../reports/ReporteFactAprobacion.php';
        $reporte = new ReporteFactAprobacion();
        $reporte->generarFacturaARS($docEntry, 'I');
    }

    /**
     * AJAX: Facturar Lote de Aprobaciones a Aseguradora (Crear Factura ARS y agrupar por servicio)
     */
    public function facturarLoteARS() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        $database = new Database();
        $pdo = $database->getConnection();
        $pdo->beginTransaction();

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) $input = $_POST;

            $cardCodeARS = trim($input['CardCode_ARS'] ?? '');
            $idsAprobaciones = $input['ids'] ?? [];
            $facSerie = trim($input['U_FacSerie'] ?? 'E31');
            $docDateInput = trim($input['DocDate'] ?? '');
            $docDate = !empty($docDateInput) ? $docDateInput : date('Y-m-d');
            $extraDays = intval($input['ExtraDays'] ?? 30);
            $usuario = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'ADMIN';

            if (empty($cardCodeARS)) {
                throw new Exception("Debe seleccionar una Aseguradora válida.");
            }

            if (empty($idsAprobaciones) || !is_array($idsAprobaciones)) {
                throw new Exception("Debe seleccionar al menos una aprobación pendiente para facturar.");
            }

            // 1. Obtener datos de la Aseguradora en posOCRD
            $stmtARS = $pdo->prepare("SELECT c.CardCode, c.CardName, c.U_NIT, c.Address, c.Phone1, c.U_Comprob, octg.ExtraDays 
                                      FROM posOCRD c 
                                      LEFT JOIN posOCTG octg ON c.GroupNum = octg.GroupNum 
                                      WHERE c.CardCode = ?");
            $stmtARS->execute([$cardCodeARS]);
            $arsData = $stmtARS->fetch(PDO::FETCH_ASSOC);

            if (!$arsData) {
                throw new Exception("Aseguradora no encontrada con el código: " . htmlspecialchars($cardCodeARS));
            }

            $cardNameARS = $arsData['CardName'];
            $rncARS = $arsData['U_NIT'] ?? '';
            $addressARS = $arsData['Address'] ?? '';
            
            // Serie electrónica obligatoria para facturación de aseguradoras (e-CF DGII)
            $facSerie = 'E31';

            if (empty($input['ExtraDays']) && isset($arsData['ExtraDays'])) {
                $extraDays = intval($arsData['ExtraDays']);
            }

            // 2. Consultar las aprobaciones seleccionadas (solo las que estén PENDIENTES 'P')
            $placeholders = implode(',', array_fill(0, count($idsAprobaciones), '?'));
            $sqlAps = "SELECT IdARS, NumAprobacion, CardName, NumAfiliado, TipoServicio, MontoAprobado, NumFactura
                       FROM AprobacionARS
                       WHERE IdARS IN ($placeholders) AND Status = 'P'";
            $stmtAps = $pdo->prepare($sqlAps);
            $stmtAps->execute($idsAprobaciones);
            $aprobacionesSel = $stmtAps->fetchAll(PDO::FETCH_ASSOC);

            if (empty($aprobacionesSel)) {
                throw new Exception("Ninguna de las aprobaciones seleccionadas está en estado PENDIENTE.");
            }

            // 3. Agrupar por Tipo de Servicio y calcular totales
            $serviciosAgrupados = [];
            $totalFactura = 0;
            $totalCantidad = 0;

            foreach ($aprobacionesSel as $ap) {
                $rawServ = !empty(trim($ap['TipoServicio'])) ? trim($ap['TipoServicio']) : 'CONSULTA MEDICA';
                $tipoServ = strtoupper(str_replace(['á','é','í','ó','ú','Á','É','Í','Ó','Ú'], ['a','e','i','o','u','A','E','I','O','U'], $rawServ));
                $monto = floatval($ap['MontoAprobado'] ?? 0);

                if (!isset($serviciosAgrupados[$tipoServ])) {
                    $serviciosAgrupados[$tipoServ] = [
                        'TipoServicio' => $tipoServ,
                        'Cantidad' => 0,
                        'Total' => 0.0
                    ];
                }

                $serviciosAgrupados[$tipoServ]['Cantidad'] += 1;
                $serviciosAgrupados[$tipoServ]['Total'] += $monto;
                $totalFactura += $monto;
                $totalCantidad += 1;
            }

            // 4. Generar Secuencia NumAtCard (F0000001 - 8 caracteres)
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

            // 5. Consumir NCF de la serie seleccionada (B01 o E31)
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
                if ($lenPad <= 0) $lenPad = (stripos($facSerie, 'E') === 0) ? 10 : 8;

                $serieMayus = strtoupper(trim($ncfRow['serie'] ?: $facSerie));
                $cNCF = substr($serieMayus . str_pad($ncfRow['contador'], $lenPad, '0', STR_PAD_LEFT), 0, 20);
                $vNCFVence = $ncfRow['vencimiento'];
                if (empty($vNCFVence) || $vNCFVence == '0000-00-00') $vNCFVence = null;
            }

            // 6. Insertar Cabecera en POSOINV
            $sqlH = "INSERT INTO POSOINV (
                        DocDate, CardCode, CardName, U_FacNit, U_FacNom, NumAtCard, 
                        U_SCIASerie, U_SCIADocNum, U_FacSerie, U_NCFVence, U_FACNUM, DocTotal, 
                        DiscPrcnt, DiscSum, VatSum, ExtraDays, Filler, U_user, 
                        U_terminal, DocStatus, ObjType
                    ) VALUES (
                        CAST(? AS DATE), ?, ?, ?, ?, ?, 
                        'F', ?, ?, ?, ?, ?, 
                        0, 0, 0, ?, 'LOTE ARS', ?, 
                        'WEB', 'O', 13
                    )";
            $stmtH = $pdo->prepare($sqlH);
            $stmtH->execute([
                $docDate, $cardCodeARS, $cardNameARS, $rncARS, $cardNameARS, $NumAtCard,
                (int)$secDoc, $facSerie, $vNCFVence, $cNCF, $totalFactura,
                $extraDays, substr(trim($usuario), 0, 15)
            ]);

            $docEntry = (int)$pdo->query('SELECT SCOPE_IDENTITY()')->fetchColumn();
            if (!$docEntry) $docEntry = (int)$pdo->lastInsertId();
            if (!$docEntry) throw new Exception("Error al obtener DocEntry de la factura ARS.");

            // 7. Insertar Detalle en POSINV1 (Grouped by service)
            $sqlD = "INSERT INTO POSINV1 (
                        C_DocEntry, LineNum, ItemCode, Dscription, Quantity, 
                        PriceBefDi, Price, DiscPrcnt, LineTotal, VatPrcnt, VatSum, TaxCode, WhsCode
                    ) VALUES (
                        ?, ?, ?, ?, ?, 
                        ?, ?, 0, ?, 0, 0, 'EXE', '01'
                    )";
            $stmtD = $pdo->prepare($sqlD);

            $lineNum = 0;
            foreach ($serviciosAgrupados as $s) {
                $cant = floatval($s['Cantidad']);
                $totalLinea = floatval($s['Total']);
                $precioUnit = ($cant > 0) ? ($totalLinea / $cant) : $totalLinea;
                $descrip = $s['TipoServicio'];

                $stmtD->execute([
                    $docEntry,
                    $lineNum,
                    'SERV-ARS',
                    $descrip,
                    $cant,
                    $precioUnit,
                    $precioUnit,
                    $totalLinea
                ]);
                $lineNum++;
            }

            // 8. Actualizar AprobacionARS: pasar a 'F', registrar DocEntry_FacturaARS y NCF
            $idsActualizar = array_column($aprobacionesSel, 'IdARS');
            $placeholdersUpd = implode(',', array_fill(0, count($idsActualizar), '?'));
            $sqlUpdARS = "UPDATE AprobacionARS 
                          SET Status = 'F', 
                              DocEntry_FacturaARS = ?, 
                              NumFacturaARS = ?, 
                              FechaFacturaARS = GETDATE()
                          WHERE IdARS IN ($placeholdersUpd)";
            $paramsUpd = array_merge([$docEntry, $cNCF], $idsActualizar);
            $stmtUpd = $pdo->prepare($sqlUpdARS);
            $stmtUpd->execute($paramsUpd);

            // 9. Registrar Trazabilidad Inicial de la Factura de Reclamación ARS
            $sqlTrace = "INSERT INTO trazabilidad_reclamacion_ars (
                            DocEntry_Factura, NumAtCard, NCF_Factura, CardCode_ARS, CardName_ARS, 
                            MontoFactura, Estado, FechaEstado, Usuario, Observaciones
                        ) VALUES (?, ?, ?, ?, ?, ?, 'PENDIENTE', GETDATE(), ?, ?)";
            $stmtTrace = $pdo->prepare($sqlTrace);
            $stmtTrace->execute([
                $docEntry,
                $NumAtCard,
                $cNCF,
                $cardCodeARS,
                $cardNameARS,
                $totalFactura,
                substr(trim($usuario), 0, 50),
                "Emisión de Factura Lote ARS ({$totalCantidad} reclamaciones)"
            ]);

            // 10. Registrar en ecf_xml_invoice para transmisión DGII
            if (!empty($cNCF)) {
                require_once __DIR__ . '/../config/EcfHelper.php';
                EcfHelper::registrarEcfXml(
                    $pdo,
                    $docEntry,
                    $NumAtCard,
                    $facSerie,
                    $cNCF,
                    $vNCFVence,
                    $rncARS,
                    $extraDays,
                    $docDate,
                    'INVOICE'
                );
            }

            $pdo->commit();
            ob_end_clean();

            echo json_encode([
                'status' => 'success',
                'msg' => 'Factura de Aseguradora generada exitosamente.',
                'docEntry' => $docEntry,
                'ncf' => $cNCF,
                'numAtCard' => $NumAtCard,
                'cardName' => $cardNameARS,
                'total' => $totalFactura,
                'cantidad' => $totalCantidad,
                'servicios' => $serviciosAgrupados
            ]);

        } catch (Throwable $e) {
            $pdo->rollBack();
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Guardar o Registrar Aprobación Independiente
     */
    public function guardar() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        $database = new Database();
        $pdo = $database->getConnection();

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                $input = $_POST;
            }

            $numAprobacion = trim($input['NumAprobacion'] ?? '');
            if (empty($numAprobacion)) {
                throw new Exception("El número de aprobación es obligatorio.");
            }

            $tipoSocio = substr(trim($input['TipoSocio'] ?? 'P'), 0, 15);
            $cardCode = substr(trim($input['CardCode'] ?? ''), 0, 100);
            $cardName = substr(trim($input['CardName'] ?? ''), 0, 100);
            $numExpediente = !empty($input['NumExpediente']) ? intval($input['NumExpediente']) : null;
            $numAfiliado = substr(trim($input['NumAfiliado'] ?? ''), 0, 50);
            $cardCode_ARS = substr(trim($input['CardCode_ARS'] ?? ''), 0, 100);

            $fechaAprobT = !empty($input['FechaAprobacionT']) ? $input['FechaAprobacionT'] : date('Y-m-d H:i:s');
            $fechaAprobD = !empty($input['FechaAprobacionD']) ? $input['FechaAprobacionD'] : substr($fechaAprobT, 0, 10);

            $fechaVencT = !empty($input['FechaVencimientoT']) ? $input['FechaVencimientoT'] : null;
            $fechaVencD = !empty($input['FechaVencimientoD']) ? $input['FechaVencimientoD'] : ($fechaVencT ? substr($fechaVencT, 0, 10) : null);

            $montoAprobado = floatval($input['MontoAprobado'] ?? 0);
            $porcentajeCober = floatval($input['PorcentajeCober'] ?? 0);
            $montoResponsable = floatval($input['MontoResponsable'] ?? 0);

            $rawTipoServ = trim($input['TipoServicio'] ?? 'CONSULTA MEDICA');
            $tipoServicio = strtoupper(str_replace(['á','é','í','ó','ú','Á','É','Í','Ó','Ú'], ['a','e','i','o','u','A','E','I','O','U'], $rawTipoServ));
            $tipoServicio = substr($tipoServicio, 0, 50);
            $observaciones = substr(trim($input['Observaciones'] ?? ''), 0, 254);
            $status = substr(trim($input['Status'] ?? 'P'), 0, 1);
            $usuario = substr(trim($_SESSION['usuario'] ?? $_SESSION['nombre'] ?? 'ADMIN'), 0, 20);

            $idARS = !empty($input['IdARS']) ? intval($input['IdARS']) : 0;

            if ($idARS > 0) {
                // Actualización: Proteger montos financieros de cobertura fijados en la venta/factura
                $stmtOrig = $pdo->prepare("SELECT MontoAprobado, PorcentajeCober, MontoResponsable FROM AprobacionARS WHERE IdARS = ?");
                $stmtOrig->execute([$idARS]);
                $orig = $stmtOrig->fetch(PDO::FETCH_ASSOC);
                if ($orig) {
                    $montoAprobado = floatval($orig['MontoAprobado']);
                    $porcentajeCober = floatval($orig['PorcentajeCober']);
                    $montoResponsable = floatval($orig['MontoResponsable']);
                }

                $sql = "UPDATE AprobacionARS SET 
                            TipoSocio = ?, CardCode = ?, CardName = ?, NumExpediente = ?, NumAfiliado = ?,
                            CardCode_ARS = ?, NumAprobacion = ?, FechaAprobacionT = ?, FechaAprobacionD = ?,
                            FechaVencimientoT = ?, FechaVencimientoD = ?, MontoAprobado = ?, PorcentajeCober = ?,
                            MontoResponsable = ?, TipoServicio = ?, Observaciones = ?, Status = ?
                        WHERE IdARS = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $tipoSocio, $cardCode, $cardName, $numExpediente, $numAfiliado,
                    $cardCode_ARS, $numAprobacion, $fechaAprobT, $fechaAprobD,
                    $fechaVencT, $fechaVencD, $montoAprobado, $porcentajeCober,
                    $montoResponsable, $tipoServicio, $observaciones, $status,
                    $idARS
                ]);
            } else {
                // Inserción
                $sql = "INSERT INTO AprobacionARS (
                            TipoSocio, CardCode, CardName, NumExpediente, NumAfiliado,
                            CardCode_ARS, NumAprobacion, FechaAprobacionT, FechaAprobacionD,
                            FechaVencimientoT, FechaVencimientoD, MontoAprobado, PorcentajeCober,
                            MontoResponsable, TipoServicio, Observaciones, Status, U_user,
                            CreateDateT, CreateDateD
                        ) VALUES (
                            ?, ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            ?, ?, ?, ?, ?,
                            GETDATE(), CAST(GETDATE() AS DATE)
                        )";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $tipoSocio, $cardCode, $cardName, $numExpediente, $numAfiliado,
                    $cardCode_ARS, $numAprobacion, $fechaAprobT, $fechaAprobD,
                    $fechaVencT, $fechaVencD, $montoAprobado, $porcentajeCober,
                    $montoResponsable, $tipoServicio, $observaciones, $status, $usuario
                ]);
                $idARS = $pdo->lastInsertId();
            }

            ob_end_clean();
            echo json_encode([
                'status' => 'success',
                'msg' => 'Aprobación ARS registrada exitosamente.',
                'IdARS' => $idARS,
                'NumAprobacion' => $numAprobacion
            ]);

        } catch (Throwable $e) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
        }
    }

    /**
     * AJAX: Buscar Aprobaciones por Paciente o Número
     */
    public function buscar() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $database = new Database();
            $pdo = $database->getConnection();
            $term = trim($_GET['q'] ?? '');
            $cardCode = trim($_GET['cardCode'] ?? '');

            $params = [];
            $where = ["1=1"];

            if (!empty($cardCode)) {
                $where[] = "a.CardCode = ?";
                $params[] = $cardCode;
            }

            if (!empty($term)) {
                $where[] = "(a.NumAprobacion LIKE ? OR a.CardName LIKE ? OR a.NumAfiliado LIKE ?)";
                $params[] = "%$term%";
                $params[] = "%$term%";
                $params[] = "%$term%";
            }

            $sql = "SELECT TOP 50 
                        a.IdARS, a.TipoSocio, a.CardCode, a.CardName, a.NumExpediente, a.NumAfiliado,
                        a.CardCode_ARS, a.NumAprobacion, a.FechaAprobacionT, a.FechaAprobacionD,
                        a.FechaVencimientoT, a.FechaVencimientoD, a.MontoAprobado, a.PorcentajeCober,
                        a.MontoResponsable, a.DocEntry_Factura, a.NumFactura, a.FechaFacturaT,
                        a.FechaFacturaD, a.TipoServicio, a.Observaciones, a.Status, a.U_user,
                        COALESCE(ars.CardName, NULLIF(a.CardCode_ARS, ''), 'SIN ASEGURADORA') AS NombreARS
                    FROM AprobacionARS a
                    LEFT JOIN posOCRD pac ON a.CardCode = pac.CardCode
                    LEFT JOIN posOCRD ars ON ars.QryGroup1 = 'Y' AND (
                        ars.CardCode = a.CardCode_ARS 
                        OR (NULLIF(a.CardCode_ARS, '') IS NULL AND ars.CardCode = pac.U_SubGrupo)
                    )
                    WHERE " . implode(" AND ", $where) . "
                    ORDER BY a.IdARS DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
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
     * AJAX: Marcar Facturas como COBRADAS por la ARS
     */
    public function marcarCobroARS() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        $database = new Database();
        $pdo = $database->getConnection();
        $pdo->beginTransaction();

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) $input = $_POST;

            $idsFacturas = $input['idsFacturas'] ?? [];
            $referenciaPago = trim($input['referenciaPago'] ?? '');
            $fechaCobroInput = trim($input['fechaCobro'] ?? '');
            $fechaCobro = !empty($fechaCobroInput) ? $fechaCobroInput : date('Y-m-d H:i:s');
            $observaciones = trim($input['observaciones'] ?? '');
            $usuario = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'ADMIN';

            if (empty($idsFacturas) || !is_array($idsFacturas)) {
                throw new Exception("Debe seleccionar al menos una factura para marcar como cobrada.");
            }

            $count = 0;
            $stmtGetFac = $pdo->prepare("SELECT C_DocEntry, NumAtCard, U_FACNUM, CardCode, CardName, DocTotal, CANCELED FROM POSOINV WHERE C_DocEntry = ?");
            $stmtInsTrace = $pdo->prepare("INSERT INTO trazabilidad_reclamacion_ars (
                                                DocEntry_Factura, NumAtCard, NCF_Factura, CardCode_ARS, CardName_ARS, 
                                                MontoFactura, Estado, FechaEstado, ReferenciaPago, Usuario, Observaciones
                                            ) VALUES (?, ?, ?, ?, ?, ?, 'COBRADA', ?, ?, ?, ?)");
            $stmtUpdFac = $pdo->prepare("UPDATE POSOINV SET DocStatus = 'C' WHERE C_DocEntry = ?");

            foreach ($idsFacturas as $docEntry) {
                $docEntry = (int)$docEntry;
                $stmtGetFac->execute([$docEntry]);
                $fac = $stmtGetFac->fetch(PDO::FETCH_ASSOC);
                if (!$fac) continue;
                if ($fac['CANCELED'] === 'Y') {
                    throw new Exception("La factura {$fac['NumAtCard']} está cancelada y no puede marcarse como cobrada.");
                }

                $stmtInsTrace->execute([
                    $docEntry,
                    $fac['NumAtCard'],
                    $fac['U_FACNUM'],
                    $fac['CardCode'],
                    $fac['CardName'],
                    $fac['DocTotal'],
                    $fechaCobro,
                    $referenciaPago,
                    $usuario,
                    !empty($observaciones) ? $observaciones : 'Cobro liquidado por la ARS'
                ]);

                $stmtUpdFac->execute([$docEntry]);
                $count++;
            }

            $pdo->commit();
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'msg' => "Se han marcado {$count} factura(s) como COBRADAS correctamente.",
                'count' => $count
            ]);
            exit();

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
            exit();
        }
    }

    /**
     * AJAX: Marcar Facturas como RECHAZADAS por la ARS
     */
    public function marcarRechazoARS() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        $database = new Database();
        $pdo = $database->getConnection();
        $pdo->beginTransaction();

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) $input = $_POST;

            $idsFacturas = $input['idsFacturas'] ?? [];
            $motivo = trim($input['motivo'] ?? '');
            $observaciones = trim($input['observaciones'] ?? '');
            $usuario = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'ADMIN';

            if (empty($idsFacturas) || !is_array($idsFacturas)) {
                throw new Exception("Debe seleccionar al menos una factura para marcar como rechazada.");
            }
            if (empty($motivo)) {
                throw new Exception("Debe indicar el motivo del rechazo o glosa de la ARS.");
            }

            $count = 0;
            $facturasRechazadas = [];
            $stmtGetFac = $pdo->prepare("SELECT C_DocEntry, NumAtCard, U_FACNUM, CardCode, CardName, DocTotal, CANCELED FROM POSOINV WHERE C_DocEntry = ?");
            $stmtInsTrace = $pdo->prepare("INSERT INTO trazabilidad_reclamacion_ars (
                                                DocEntry_Factura, NumAtCard, NCF_Factura, CardCode_ARS, CardName_ARS, 
                                                MontoFactura, Estado, FechaEstado, Motivo, Usuario, Observaciones
                                            ) VALUES (?, ?, ?, ?, ?, ?, 'RECHAZADA', GETDATE(), ?, ?, ?)");

            foreach ($idsFacturas as $docEntry) {
                $docEntry = (int)$docEntry;
                $stmtGetFac->execute([$docEntry]);
                $fac = $stmtGetFac->fetch(PDO::FETCH_ASSOC);
                if (!$fac) continue;

                $stmtInsTrace->execute([
                    $docEntry,
                    $fac['NumAtCard'],
                    $fac['U_FACNUM'],
                    $fac['CardCode'],
                    $fac['CardName'],
                    $fac['DocTotal'],
                    $motivo,
                    $usuario,
                    !empty($observaciones) ? $observaciones : 'Rechazo / Glosa médica reportada por ARS'
                ]);

                $facturasRechazadas[] = [
                    'C_DocEntry' => $docEntry,
                    'NumAtCard' => $fac['NumAtCard'],
                    'U_FACNUM' => $fac['U_FACNUM'],
                    'CardCode' => $fac['CardCode'],
                    'CardName' => $fac['CardName'],
                    'DocTotal' => (float)$fac['DocTotal']
                ];
                $count++;
            }

            $pdo->commit();
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'msg' => "Se han marcado {$count} factura(s) como RECHAZADAS. Puede proceder a emitir la Nota de Crédito correspondiente.",
                'count' => $count,
                'facturas' => $facturasRechazadas
            ]);
            exit();

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
            exit();
        }
    }

    /**
     * AJAX: Consultar Trazabilidad y Línea de Tiempo de una Factura
     */
    public function consultarTrazabilidad() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        try {
            $database = new Database();
            $pdo = $database->getConnection();
            $docEntry = isset($_GET['docEntry']) ? (int)$_GET['docEntry'] : 0;

            if ($docEntry <= 0) {
                throw new Exception("DocEntry de factura no válido.");
            }

            $sqlFac = "SELECT C_DocEntry, NumAtCard, U_FACNUM, CardCode, CardName, DocTotal, FORMAT(DocDate, 'dd/MM/yyyy') as FechaDoc, CANCELED, DocStatus
                       FROM POSOINV WHERE C_DocEntry = ?";
            $stmtF = $pdo->prepare($sqlFac);
            $stmtF->execute([$docEntry]);
            $factura = $stmtF->fetch(PDO::FETCH_ASSOC);

            if (!$factura) {
                throw new Exception("Factura no encontrada.");
            }

            $sqlTrace = "SELECT 
                            id_trazabilidad, DocEntry_Factura, NumAtCard, NCF_Factura,
                            CardCode_ARS, CardName_ARS, MontoFactura, Estado,
                            FORMAT(FechaEstado, 'dd/MM/yyyy hh:mm tt') as FechaEstadoFmt,
                            Motivo, ReferenciaPago, DocEntry_NC, NCF_NC, Usuario, Observaciones
                         FROM trazabilidad_reclamacion_ars
                         WHERE DocEntry_Factura = ?
                         ORDER BY id_trazabilidad ASC";
            $stmtT = $pdo->prepare($sqlTrace);
            $stmtT->execute([$docEntry]);
            $eventos = $stmtT->fetchAll(PDO::FETCH_ASSOC);

            // Aprobaciones vinculadas a este lote
            $sqlAps = "SELECT IdARS, NumAprobacion, CardName, NumAfiliado, TipoServicio, MontoAprobado, Status
                       FROM AprobacionARS
                       WHERE DocEntry_FacturaARS = ?
                       ORDER BY IdARS ASC";
            $stmtA = $pdo->prepare($sqlAps);
            $stmtA->execute([$docEntry]);
            $aprobaciones = $stmtA->fetchAll(PDO::FETCH_ASSOC);

            ob_end_clean();
            echo json_encode([
                'success' => true,
                'factura' => $factura,
                'eventos' => $eventos,
                'aprobaciones' => $aprobaciones
            ]);
            exit();

        } catch (Throwable $e) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
            exit();
        }
    }
}

?>