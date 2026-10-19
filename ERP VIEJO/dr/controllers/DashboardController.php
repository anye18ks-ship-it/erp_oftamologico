<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class DashboardController {

    const CACHE_TTL_SECONDS = 600; // 10 minutos de caché para optimización de rendimiento

    public function index() {
        $forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] == '1';
        $now = time();

        // ── Comprobar Caché en Sesión (Válida por 10 minutos) ──
        if (!$forceRefresh && isset($_SESSION['dashboard_cache']) && ($now - $_SESSION['dashboard_cache']['time'] < self::CACHE_TTL_SECONDS)) {
            $data = $_SESSION['dashboard_cache']['data'];
            $tiempoCache = $now - $_SESSION['dashboard_cache']['time'];
            $minutosCache = floor($tiempoCache / 60);
            $segundosCache = $tiempoCache % 60;
            $desdeCache = true;
        } else {
            // ── Consultas Optimizadas con WITH (NOLOCK) ──
            $data = $this->obtenerMetricasDashboard();
            $_SESSION['dashboard_cache'] = [
                'time' => $now,
                'data' => $data
            ];
            $minutosCache = 0;
            $segundosCache = 0;
            $desdeCache = false;
        }

        // Extraer variables para la vista
        extract($data);

        include __DIR__ . '/../views/Estructura/dashboard.php';
    }

    /**
     * Ejecuta todas las consultas optimizadas con NOLOCK
     */
    private function obtenerMetricasDashboard() {
        $database = new Database();
        $pdo = $database->getConnection();

        $hoy = date('Y-m-d');
        $mesActual = (int)date('m');
        $anioActual = (int)date('Y');

        $resultado = [
            'ventasHoy'            => 0.0,
            'facturasHoy'          => 0,
            'ventasAyer'           => 0.0,
            'porcentajeVar'        => 0.0,
            'ventasMes'            => 0.0,
            'facturasMes'          => 0,
            'cajaActual'           => null,
            'cajaAlias'            => 'Caja General',
            'fondoInicial'         => 0.0,
            'efectivoCobrado'      => 0.0,
            'retirosCaja'          => 0.0,
            'saldoEfectivoCaja'    => 0.0,
            'numTurnoZ'            => 'Sin Turno',
            'montoCoberturaARS'    => 0.0,
            'copagoPacientesARS'   => 0.0,
            'totalAprobacionesARS' => 0,
            'totalPacientes'       => 0,
            'pacientesConSeguro'   => 0,
            'pacientesPrivados'    => 0,
            'totalNCFDisponibles'  => 0,
            'seriesNCFResumen'     => [],
            'chartDiasLabels'      => [],
            'chartVentasData'      => [],
            'topArticulos'         => [],
            'ultimasFacturas'      => [],
            'alertasStock'         => []
        ];

        try {
            // 1. Ventas del Día y de Ayer
            $stmtVentasHoy = $pdo->prepare("
                SELECT 
                    ISNULL(SUM(DocTotal), 0) AS total_hoy,
                    COUNT(*) AS facturas_hoy
                FROM POSOINV WITH (NOLOCK)
                WHERE CAST(DocDate AS DATE) = CAST(? AS DATE) 
                  AND ISNULL(CANCELED, 'N') != 'Y'
            ");
            $stmtVentasHoy->execute([$hoy]);
            $resVentasHoy = $stmtVentasHoy->fetch(PDO::FETCH_ASSOC);
            $resultado['ventasHoy'] = (float)($resVentasHoy['total_hoy'] ?? 0);
            $resultado['facturasHoy'] = (int)($resVentasHoy['facturas_hoy'] ?? 0);

            $stmtVentasAyer = $pdo->prepare("
                SELECT ISNULL(SUM(DocTotal), 0) AS total_ayer
                FROM POSOINV WITH (NOLOCK)
                WHERE CAST(DocDate AS DATE) = CAST(DATEADD(DAY, -1, ?) AS DATE) 
                  AND ISNULL(CANCELED, 'N') != 'Y'
            ");
            $stmtVentasAyer->execute([$hoy]);
            $resultado['ventasAyer'] = (float)$stmtVentasAyer->fetchColumn();

            if ($resultado['ventasAyer'] > 0) {
                $resultado['porcentajeVar'] = (($resultado['ventasHoy'] - $resultado['ventasAyer']) / $resultado['ventasAyer']) * 100;
            } elseif ($resultado['ventasHoy'] > 0) {
                $resultado['porcentajeVar'] = 100.0;
            }

            // 2. Ventas del Mes
            $stmtVentasMes = $pdo->prepare("
                SELECT 
                    ISNULL(SUM(DocTotal), 0) AS total_mes,
                    COUNT(*) AS facturas_mes
                FROM POSOINV WITH (NOLOCK)
                WHERE MONTH(DocDate) = ? 
                  AND YEAR(DocDate) = ? 
                  AND ISNULL(CANCELED, 'N') != 'Y'
            ");
            $stmtVentasMes->execute([$mesActual, $anioActual]);
            $resVentasMes = $stmtVentasMes->fetch(PDO::FETCH_ASSOC);
            $resultado['ventasMes'] = (float)($resVentasMes['total_mes'] ?? 0);
            $resultado['facturasMes'] = (int)($resVentasMes['facturas_mes'] ?? 0);

            // 3. Caja Actual y Turno
            $stmtCaja = $pdo->prepare("
                SELECT TOP 1 
                    d.id_diario_caja, 
                    d.id_caja, 
                    d.fecha_apertura, 
                    d.hora_apertura, 
                    d.monto_apertura,
                    ISNULL(c.alias, 'Caja Principal') AS alias,
                    ISNULL(u.nombre, 'Usuario') AS cajero
                FROM diario_caja d WITH (NOLOCK)
                LEFT JOIN caja c WITH (NOLOCK) ON d.id_caja = c.id_caja
                LEFT JOIN usuario u WITH (NOLOCK) ON d.usuario_apertura = u.id_usuario
                WHERE d.fecha_cierre IS NULL
                ORDER BY d.id_diario_caja DESC
            ");
            $stmtCaja->execute();
            $cajaActual = $stmtCaja->fetch(PDO::FETCH_ASSOC);

            if ($cajaActual) {
                $resultado['cajaActual'] = $cajaActual;
                $idDiario = (int)$cajaActual['id_diario_caja'];
                $resultado['cajaAlias'] = $cajaActual['alias'];
                $resultado['fondoInicial'] = (float)($cajaActual['monto_apertura'] ?? 0);
                $resultado['numTurnoZ'] = 'Z-' . str_pad($idDiario, 7, '0', STR_PAD_LEFT);

                $stmtEfec = $pdo->prepare("
                    SELECT ISNULL(SUM(monto_caja), 0) 
                    FROM diario_caja_det WITH (NOLOCK)
                    WHERE id_diario_caja = ? AND id_tipo_movimiento IN (1, 5, 6)
                ");
                $stmtEfec->execute([$idDiario]);
                $resultado['efectivoCobrado'] = (float)$stmtEfec->fetchColumn();

                $stmtRet = $pdo->prepare("
                    SELECT ISNULL(SUM(monto), 0) 
                    FROM retiro_caja WITH (NOLOCK)
                    WHERE id_diario_caja = ? AND ISNULL(anulado, 'N') = 'N'
                ");
                $stmtRet->execute([$idDiario]);
                $resultado['retirosCaja'] = (float)$stmtRet->fetchColumn();

                $resultado['saldoEfectivoCaja'] = ($resultado['fondoInicial'] + $resultado['efectivoCobrado']) - $resultado['retirosCaja'];
            }

            // 4. ARS Seguros del Mes
            $stmtARS = $pdo->prepare("
                SELECT 
                    ISNULL(SUM(MontoAprobado), 0) AS total_cobertura,
                    ISNULL(SUM(MontoResponsable), 0) AS total_copago,
                    COUNT(*) AS total_autorizaciones
                FROM AprobacionARS WITH (NOLOCK)
                WHERE MONTH(COALESCE(FechaAprobacionD, CreateDateD, GETDATE())) = ? 
                  AND YEAR(COALESCE(FechaAprobacionD, CreateDateD, GETDATE())) = ?
            ");
            $stmtARS->execute([$mesActual, $anioActual]);
            $resARS = $stmtARS->fetch(PDO::FETCH_ASSOC);
            $resultado['montoCoberturaARS'] = (float)($resARS['total_cobertura'] ?? 0);
            $resultado['copagoPacientesARS'] = (float)($resARS['total_copago'] ?? 0);
            $resultado['totalAprobacionesARS'] = (int)($resARS['total_autorizaciones'] ?? 0);

            // 5. Directorio de Pacientes
            $stmtPac = $pdo->query("
                SELECT 
                    COUNT(*) AS total_pacientes,
                    SUM(CASE WHEN NULLIF(U_SubGrupo, '') IS NOT NULL THEN 1 ELSE 0 END) AS con_seguro,
                    SUM(CASE WHEN NULLIF(U_SubGrupo, '') IS NULL THEN 1 ELSE 0 END) AS privados
                FROM posOCRD WITH (NOLOCK)
                WHERE CardType IN ('P', 'C')
            ");
            $resPac = $stmtPac->fetch(PDO::FETCH_ASSOC);
            $resultado['totalPacientes'] = (int)($resPac['total_pacientes'] ?? 0);
            $resultado['pacientesConSeguro'] = (int)($resPac['con_seguro'] ?? 0);
            $resultado['pacientesPrivados'] = (int)($resPac['privados'] ?? 0);

            // 6. Comprobantes Fiscales NCF con Alerta de Disponibilidad (Igual a ncf.php)
            $stmtNCF = $pdo->query("
                SELECT 
                    c.serie,
                    s.descripcion,
                    ISNULL(s.alerta, 100) AS alerta,
                    SUM(c.final - c.contador) AS disponibles,
                    SUM(c.final - c.inicial) AS total_rango
                FROM carga_ncf c WITH (NOLOCK)
                INNER JOIN serie_ncf s WITH (NOLOCK) ON c.serie = s.serie
                WHERE c.contador < c.final
                GROUP BY c.serie, s.descripcion, s.alerta
                ORDER BY c.serie ASC
            ");
            $resultado['seriesNCFResumen'] = $stmtNCF->fetchAll(PDO::FETCH_ASSOC);
            foreach ($resultado['seriesNCFResumen'] as $sn) {
                $resultado['totalNCFDisponibles'] += (int)$sn['disponibles'];
            }

            // 7. Gráfico de Ventas (Últimos 7 Días)
            $diasSemanales = [];
            $nombresDias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

            for ($i = 6; $i >= 0; $i--) {
                $fechaLoop = date('Y-m-d', strtotime("-$i days"));
                $numDiaSemana = (int)date('w', strtotime($fechaLoop));
                $etiqueta = $nombresDias[$numDiaSemana] . ' ' . date('d/m', strtotime($fechaLoop));
                $diasSemanales[$fechaLoop] = [
                    'label' => $etiqueta,
                    'total' => 0.0
                ];
            }

            $stmtHist = $pdo->prepare("
                SELECT 
                    CAST(DocDate AS DATE) AS fecha,
                    ISNULL(SUM(DocTotal), 0) AS total_dia
                FROM POSOINV WITH (NOLOCK)
                WHERE CAST(DocDate AS DATE) >= CAST(DATEADD(DAY, -6, ?) AS DATE)
                  AND ISNULL(CANCELED, 'N') != 'Y'
                GROUP BY CAST(DocDate AS DATE)
                ORDER BY fecha ASC
            ");
            $stmtHist->execute([$hoy]);
            $filasHist = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

            foreach ($filasHist as $fh) {
                $fStr = substr($fh['fecha'], 0, 10);
                if (isset($diasSemanales[$fStr])) {
                    $diasSemanales[$fStr]['total'] = (float)$fh['total_dia'];
                }
            }

            foreach ($diasSemanales as $dInfo) {
                $resultado['chartDiasLabels'][] = $dInfo['label'];
                $resultado['chartVentasData'][] = $dInfo['total'];
            }

            // 8. Top 5 Artículos y Servicios
            $stmtTop = $pdo->query("
                SELECT TOP 5
                    d.ItemCode,
                    d.Dscription,
                    SUM(d.Quantity) AS total_cant,
                    SUM(d.LineTotal) AS total_monto,
                    ISNULL(g.ItmsGrpNam, 'Servicio / Artículo') AS GrupoNombre
                FROM POSINV1 d WITH (NOLOCK)
                INNER JOIN POSOINV h WITH (NOLOCK) ON d.C_DocEntry = h.C_DocEntry
                LEFT JOIN posOITM i WITH (NOLOCK) ON d.ItemCode = i.ItemCode
                LEFT JOIN posOITB g WITH (NOLOCK) ON i.ItmsGrpCod = g.ItmsGrpCod
                WHERE ISNULL(h.CANCELED, 'N') != 'Y'
                GROUP BY d.ItemCode, d.Dscription, g.ItmsGrpNam
                ORDER BY total_monto DESC
            ");
            $resultado['topArticulos'] = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

            // 9. Historial Reciente de Facturas Emitidas (Reemplazo Útil y Real)
            $stmtFacRecientes = $pdo->query("
                SELECT TOP 8
                    h.C_DocEntry,
                    h.DocDate,
                    h.CardCode,
                    h.CardName,
                    ISNULL(h.U_FACNUM, h.NumAtCard) AS NCF,
                    h.DocTotal,
                    ISNULL(h.U_user, 'Cajero') AS Cajero,
                    h.DocStatus,
                    a.NumAprobacion,
                    a.MontoAprobado AS CoberturaARS,
                    a.MontoResponsable AS CopagoPaciente,
                    COALESCE(ars.CardName, NULLIF(h.U_FacNom, '')) AS NombreARS
                FROM POSOINV h WITH (NOLOCK)
                LEFT JOIN AprobacionARS a WITH (NOLOCK) ON h.C_DocEntry = a.DocEntry_Factura
                LEFT JOIN posOCRD pac WITH (NOLOCK) ON h.CardCode = pac.CardCode
                LEFT JOIN posOCRD ars WITH (NOLOCK) ON (ars.CardCode = a.CardCode_ARS OR ars.CardCode = pac.U_SubGrupo)
                ORDER BY h.C_DocEntry DESC
            ");
            $resultado['ultimasFacturas'] = $stmtFacRecientes->fetchAll(PDO::FETCH_ASSOC);

            // 10. Alertas de Inventario / Stock Mínimo para Óptica y Farmacia
            $stmtStock = $pdo->query("
                SELECT TOP 8
                    i.ItemCode,
                    i.ItemName,
                    ISNULL(i.OnHand, 0) AS OnHand,
                    ISNULL(g.ItmsGrpNam, 'Catálogo') AS GrupoNombre
                FROM posOITM i WITH (NOLOCK)
                LEFT JOIN posOITB g WITH (NOLOCK) ON i.ItmsGrpCod = g.ItmsGrpCod
                WHERE i.InvntItem = 'Y' 
                  AND ISNULL(i.OnHand, 0) <= 5
                ORDER BY i.OnHand ASC, i.ItemName ASC
            ");
            $resultado['alertasStock'] = $stmtStock->fetchAll(PDO::FETCH_ASSOC);

        } catch (Throwable $e) {
            error_log("Error al obtener métricas de dashboard: " . $e->getMessage());
        }

        return $resultado;
    }
}
?>
