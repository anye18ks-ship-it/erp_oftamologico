<?php require_once __DIR__ . '/header.php'; ?>
<link rel="stylesheet" href="views/Estructura/styles/dashboard.css">

<!-- Barra de Progreso de Actividad en Recarga -->
<div class="dash-loading-bar" id="dashLoadingBar"></div>

<div class="dash-container">

    <!-- =======================================================================
         1. ENCABEZADO DEL DASHBOARD Y ACCIONES RÁPIDAS
         ======================================================================= -->
    <div class="dash-header-wrap">
        <div class="dash-header-title-box">
            <h2 class="dash-title">Panel de Control General</h2>
            <p class="dash-subtitle">
                <span>Resumen ejecutivo y operaciones clínicas</span>
                <span class="dash-date-pill"><i class="bi bi-calendar3 me-1"></i><?php echo date('d/m/Y'); ?></span>
                <span class="dash-cache-pill" id="badgeCacheInfo" title="Optimización de consultas activa (Caché 10 min)">
                    <i class="bi bi-lightning-charge-fill"></i>
                    <?php echo $desdeCache ? ($minutosCache == 0 ? 'Actualizado hace ' . $segundosCache . 's' : 'Actualizado hace ' . $minutosCache . ' min') : 'Actualizado ahora'; ?>
                </span>
            </p>
        </div>

        <div class="dash-header-actions">
            <!-- Botón de Refrescar al Instante -->
            <button type="button" class="btn-dash-refresh" id="btnRefresh" onclick="refrescarDashboard()" title="Actualizar consultas y limpiar caché">
                <i class="bi bi-arrow-clockwise" id="iconRefresh"></i>
                <span id="lblRefresh">Refrescar</span>
            </button>

            <a href="index.php?page=facturacion" class="btn-dash-primary">
                <i class="bi bi-receipt fs-5"></i>
                <span>Nueva Factura</span>
            </a>
            <a href="index.php?page=retiro_caja" class="btn-dash-secondary">
                <i class="bi bi-wallet2 fs-5"></i>
                <span>Registrar Retiro</span>
            </a>
            <a href="index.php?page=consulta_aprobaciones" class="btn-dash-emerald">
                <i class="bi bi-shield-check fs-5"></i>
                <span>Reclamaciones ARS</span>
            </a>
        </div>
    </div>

    <!-- =======================================================================
         2. TARJETAS KPI (4 MÉTRICAS CLAVE DEL DÍA Y MES)
         ======================================================================= -->
    <div class="dash-kpis-grid">
        
        <!-- 1. Ventas del Día -->
        <div class="kpi-card kpi-ventas-hoy">
            <div class="kpi-info-content">
                <span class="kpi-label">Ventas del Día</span>
                <span class="kpi-value">RD$ <?php echo number_format($ventasHoy, 2); ?></span>
                <div class="kpi-meta-badge <?php echo ($porcentajeVar >= 0) ? 'meta-up' : 'meta-down'; ?>">
                    <i class="bi <?php echo ($porcentajeVar >= 0) ? 'bi-arrow-up-right' : 'bi-arrow-down-right'; ?>"></i>
                    <span><?php echo ($porcentajeVar >= 0 ? '+' : '') . number_format($porcentajeVar, 1); ?>% vs día anterior (<?php echo $facturasHoy; ?> facturas)</span>
                </div>
            </div>
            <div class="kpi-icon-box">
                <i class="bi bi-currency-dollar"></i>
            </div>
        </div>

        <!-- 2. Caja Actual (Efectivo en Gaveta) -->
        <div class="kpi-card kpi-caja-actual">
            <div class="kpi-info-content">
                <span class="kpi-label">Efectivo en Caja</span>
                <span class="kpi-value">RD$ <?php echo number_format($saldoEfectivoCaja, 2); ?></span>
                <div class="kpi-meta-badge meta-highlight">
                    <i class="bi bi-shield-check"></i>
                    <span><?php echo htmlspecialchars($cajaAlias); ?> &bull; <?php echo htmlspecialchars($numTurnoZ); ?></span>
                </div>
            </div>
            <div class="kpi-icon-box">
                <i class="bi bi-cash-stack"></i>
            </div>
        </div>

        <!-- 3. Cobertura Seguros ARS del Mes -->
        <div class="kpi-card kpi-ars-seguros">
            <div class="kpi-info-content">
                <span class="kpi-label">Seguros ARS (Mes Actual)</span>
                <span class="kpi-value">RD$ <?php echo number_format($montoCoberturaARS, 2); ?></span>
                <div class="kpi-meta-badge meta-neutral">
                    <i class="bi bi-file-earmark-medical"></i>
                    <span><?php echo $totalAprobacionesARS; ?> autorizaciones procesadas</span>
                </div>
            </div>
            <div class="kpi-icon-box">
                <i class="bi bi-hospital"></i>
            </div>
        </div>

        <!-- 4. Directorio de Pacientes -->
        <div class="kpi-card kpi-pacientes-total">
            <div class="kpi-info-content">
                <span class="kpi-label">Pacientes Registrados</span>
                <span class="kpi-value"><?php echo number_format($totalPacientes); ?> Pacientes</span>
                <div class="kpi-meta-badge meta-up">
                    <i class="bi bi-people-fill"></i>
                    <span><?php echo $pacientesConSeguro; ?> con ARS &bull; <?php echo $pacientesPrivados; ?> privados</span>
                </div>
            </div>
            <div class="kpi-icon-box">
                <i class="bi bi-person-heart"></i>
            </div>
        </div>

    </div>

    <!-- =======================================================================
         3. SECCIÓN DE GRÁFICO DE VENTAS & TOP 5 ARTÍCULOS/SERVICIOS
         ======================================================================= -->
    <div class="dash-analytics-grid">
        
        <!-- Gráfico de Tendencia de Ventas (Últimos 7 Días) -->
        <div class="dash-card">
            <div class="dash-card-header">
                <div>
                    <h3 class="dash-card-title">
                        <i class="bi bi-graph-up text-primary"></i>
                        <span>Tendencia de Facturación (Últimos 7 Días)</span>
                    </h3>
                    <p class="dash-card-subtitle">Consultas oftalmológicas, estudios, monturas y lentes</p>
                </div>
                <span class="dash-pill-tag">Mes: RD$ <?php echo number_format($ventasMes, 2); ?></span>
            </div>
            <div class="dash-chart-container">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>

        <!-- Top 5 Artículos / Servicios Más Vendidos -->
        <div class="dash-card">
            <div class="dash-card-header">
                <div>
                    <h3 class="dash-card-title">
                        <i class="bi bi-trophy-fill text-warning"></i>
                        <span>Top 5 Servicios & Artículos</span>
                    </h3>
                    <p class="dash-card-subtitle">Mayor volumen de facturación</p>
                </div>
                <a href="index.php?page=articulos" class="dash-pill-tag text-decoration-none">Ver catálogo</a>
            </div>

            <div class="top-products-list">
                <?php if (empty($topArticulos)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-box-seam fs-1 opacity-50"></i>
                        <p class="mt-2 mb-0 fw-semibold">No hay artículos facturados aún.</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $rank = 1;
                    foreach ($topArticulos as $top): 
                        $rankClass = 'rank-' . $rank;
                    ?>
                        <div class="top-product-item">
                            <div class="top-product-left">
                                <div class="top-rank-badge <?php echo $rankClass; ?>"><?php echo $rank; ?></div>
                                <div class="top-product-meta">
                                    <span class="top-product-name"><?php echo htmlspecialchars($top['Dscription']); ?></span>
                                    <span class="top-product-sub"><?php echo htmlspecialchars($top['GrupoNombre']); ?> &bull; <?php echo number_format((float)$top['total_cant']); ?> unidades</span>
                                </div>
                            </div>
                            <div class="top-product-right">
                                <span class="top-product-price">RD$ <?php echo number_format((float)$top['total_monto'], 2); ?></span>
                                <span class="top-product-units"><?php echo htmlspecialchars($top['ItemCode']); ?></span>
                            </div>
                        </div>
                    <?php 
                        $rank++;
                    endforeach; 
                    ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- =======================================================================
         4. RESUMEN DE CAJA & DISPONIBILIDAD DE NCF CON COLORES DE NCF.PHP
         ======================================================================= -->
    <div class="dash-secondary-grid">
        
        <!-- Estado de Caja y Turno -->
        <div class="status-mini-card">
            <div class="status-mini-header">
                <span class="status-mini-title">
                    <i class="bi bi-safe2 text-primary"></i>
                    <span>Estado del Turno de Caja</span>
                </span>
                <?php if ($cajaActual): ?>
                    <span class="status-indicator-pill pill-active">
                        <i class="bi bi-check-circle-fill"></i> Abierta (<?php echo htmlspecialchars($cajaActual['cajero'] ?? 'Cajero'); ?>)
                    </span>
                <?php else: ?>
                    <span class="status-indicator-pill pill-inactive">
                        <i class="bi bi-lock-fill"></i> Caja Cerrada
                    </span>
                <?php endif; ?>
            </div>

            <div class="status-breakdown-row">
                <div class="breakdown-col">
                    <span class="breakdown-label">Fondo Inicial</span>
                    <span class="breakdown-val">RD$ <?php echo number_format($fondoInicial, 2); ?></span>
                </div>
                <div class="breakdown-col">
                    <span class="breakdown-label">Cobros Efectivo</span>
                    <span class="breakdown-val text-success">+ RD$ <?php echo number_format($efectivoCobrado, 2); ?></span>
                </div>
                <div class="breakdown-col">
                    <span class="breakdown-label">Retiros Turno</span>
                    <span class="breakdown-val text-danger">- RD$ <?php echo number_format($retirosCaja, 2); ?></span>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-1">
                <?php if ($cajaActual): ?>
                    <a href="index.php?page=cierre_caja" class="btn-dash-secondary w-100 justify-content-center">
                        <i class="bi bi-calculator"></i>
                        <span>Realizar Arqueo & Cerrar Turno</span>
                    </a>
                <?php else: ?>
                    <a href="index.php?page=apertura_caja" class="btn-dash-primary w-100 justify-content-center">
                        <i class="bi bi-unlock-fill"></i>
                        <span>Aperturar Nuevo Turno de Caja</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Disponibilidad de Comprobantes Fiscales NCF con Colores correspondientes a ncf.php -->
        <div class="status-mini-card">
            <div class="status-mini-header">
                <span class="status-mini-title">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    <span>Comprobantes Fiscales (NCF)</span>
                </span>
                <span class="dash-pill-tag"><?php echo number_format($totalNCFDisponibles); ?> Disponibles</span>
            </div>

            <div class="ncf-tags-grid">
                <?php if (empty($seriesNCFResumen)): ?>
                    <div class="text-muted small py-2">No hay secuencias NCF activas cargadas en el sistema.</div>
                <?php else: ?>
                    <?php foreach ($seriesNCFResumen as $ncf): 
                        $disp = (int)$ncf['disponibles'];
                        $alerta = (int)($ncf['alerta'] ?? 100);

                        // Regla de color idéntica a ncf.php
                        if ($disp <= 0) {
                            $claseStock = 'stock-danger';
                            $textoStock = 'Agotado (0)';
                        } elseif ($disp <= $alerta) {
                            $claseStock = 'stock-warning';
                            $textoStock = number_format($disp) . ' restantes';
                        } else {
                            $claseStock = 'stock-ok';
                            $textoStock = number_format($disp) . ' disp.';
                        }
                    ?>
                        <div class="ncf-tag-item">
                            <div class="ncf-tag-left">
                                <span class="ncf-tag-serie"><?php echo htmlspecialchars($ncf['serie']); ?></span>
                                <span class="ncf-tag-desc"><?php echo htmlspecialchars($ncf['descripcion']); ?></span>
                            </div>
                            <span class="stock-badge <?php echo $claseStock; ?>">
                                <?php echo $textoStock; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-1">
                <a href="index.php?page=ncf" class="btn-dash-secondary w-100 justify-content-center">
                    <i class="bi bi-gear-wide-connected"></i>
                    <span>Administrar Rangos & Series NCF</span>
                </a>
            </div>
        </div>

    </div>

    <!-- =======================================================================
         5. HISTORIAL RECIENTE DE FACTURAS EMITIDAS & ALERTAS DE INVENTARIO
         ======================================================================= -->
    <div class="dash-bottom-grid">
        
        <!-- Facturas Emitidas Recientemente -->
        <div class="dash-card">
            <div class="dash-card-header">
                <div>
                    <h3 class="dash-card-title">
                        <i class="bi bi-receipt text-primary"></i>
                        <span>Últimas Facturas Emitidas</span>
                    </h3>
                    <p class="dash-card-subtitle">Registro de pacientes atendidos y comprobantes generados</p>
                </div>
                <a href="index.php?page=facturacion" class="dash-pill-tag text-decoration-none">
                    <i class="bi bi-plus-circle me-1"></i>Facturar
                </a>
            </div>

            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Paciente / Socio</th>
                            <th>NCF Emitido</th>
                            <th>Cobertura / Seguro</th>
                            <th class="text-end">Total (RD$)</th>
                            <th>Cajero</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ultimasFacturas)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-1 opacity-50"></i>
                                    No hay facturas registradas aún.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ultimasFacturas as $fac): 
                                $tieneARS = !empty($fac['NombreARS']) || !empty($fac['NumAprobacion']);
                            ?>
                                <tr>
                                    <td class="fw-semibold text-muted">
                                        <?php echo !empty($fac['DocDate']) ? date('d/m/Y', strtotime($fac['DocDate'])) : date('d/m/Y'); ?>
                                    </td>
                                    <td class="fw-bold text-dark">
                                        <?php echo htmlspecialchars($fac['CardName']); ?>
                                        <small class="d-block text-muted font-monospace" style="font-size: 0.72rem;"><?php echo htmlspecialchars($fac['CardCode']); ?></small>
                                    </td>
                                    <td>
                                        <span class="code-ref"><?php echo htmlspecialchars($fac['NCF'] ?? '-'); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($tieneARS): ?>
                                            <span class="badge-paciente-ars">
                                                <i class="bi bi-hospital"></i> <?php echo htmlspecialchars(substr($fac['NombreARS'] ?? 'ARS Aprobada', 0, 18)); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-paciente-privado">
                                                <i class="bi bi-person"></i> Privado
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-bold text-dark font-monospace">
                                        RD$ <?php echo number_format((float)$fac['DocTotal'], 2); ?>
                                    </td>
                                    <td>
                                        <span class="text-muted small"><i class="bi bi-person-badge me-1"></i><?php echo htmlspecialchars($fac['Cajero']); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Alertas de Inventario Crítico / Stock Mínimo -->
        <div class="dash-card">
            <div class="dash-card-header">
                <div>
                    <h3 class="dash-card-title">
                        <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                        <span>Alertas de Inventario</span>
                    </h3>
                    <p class="dash-card-subtitle">Productos con stock bajo o agotado</p>
                </div>
                <a href="index.php?page=articulos" class="dash-pill-tag text-decoration-none">Ver stock</a>
            </div>

            <div class="stock-alert-list">
                <?php if (empty($alertasStock)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check2-circle fs-2 text-success opacity-75"></i>
                        <p class="mt-2 mb-0 fw-semibold text-dark">Inventario en orden</p>
                        <small class="text-muted">No hay artículos críticos con stock bajo.</small>
                    </div>
                <?php else: ?>
                    <?php foreach ($alertasStock as $art): 
                        $stockVal = (float)$art['OnHand'];
                        $esAgotado = ($stockVal <= 0);
                    ?>
                        <div class="stock-alert-item <?php echo $esAgotado ? '' : 'alert-warning'; ?>">
                            <div class="stock-alert-left">
                                <div class="stock-alert-icon">
                                    <i class="bi <?php echo $esAgotado ? 'bi-x-circle-fill' : 'bi-exclamation-circle-fill'; ?>"></i>
                                </div>
                                <div class="stock-alert-meta">
                                    <span class="stock-alert-name"><?php echo htmlspecialchars($art['ItemName']); ?></span>
                                    <span class="stock-alert-cat"><?php echo htmlspecialchars($art['GrupoNombre']); ?> &bull; <code><?php echo htmlspecialchars($art['ItemCode']); ?></code></span>
                                </div>
                            </div>
                            <span class="stock-alert-badge">
                                <?php echo $esAgotado ? 'Agotado (0)' : number_format($stockVal, 0) . ' unid.'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<!-- =======================================================================
     6. SCRIPT DE INICIALIZACIÓN DEL GRÁFICO Y REFRESCAR AL INSTANTE
     ======================================================================= -->
<script>
// Función para Refrescar al Instante con Activity Loading
function refrescarDashboard() {
    const loadingBar = document.getElementById('dashLoadingBar');
    const refreshIcon = document.getElementById('iconRefresh');
    const btnRefresh = document.getElementById('btnRefresh');
    const lblRefresh = document.getElementById('lblRefresh');

    if (loadingBar) loadingBar.classList.add('active');
    if (refreshIcon) refreshIcon.classList.add('spinning');
    if (btnRefresh) btnRefresh.disabled = true;
    if (lblRefresh) lblRefresh.textContent = 'Actualizando...';

    // Redirigir con parámetro refresh=1 para forzar recálculo
    window.location.href = 'index.php?page=dashboard&refresh=1';
}

document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesTrendChart');
    if (!ctx) return;

    const diasLabels = <?php echo json_encode($chartDiasLabels); ?>;
    const ventasData = <?php echo json_encode($chartVentasData); ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: diasLabels,
            datasets: [{
                label: 'Ventas Diarias (RD$)',
                data: ventasData,
                borderColor: '#0284c7',
                backgroundColor: 'rgba(2, 132, 199, 0.08)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#0284c7',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return ' Total Facturado: RD$ ' + Number(context.parsed.y).toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11, weight: '600' }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f5f9'
                    },
                    ticks: {
                        color: '#64748b',
                        font: { size: 11 },
                        callback: function(value) {
                            return 'RD$ ' + Number(value).toLocaleString('es-DO', { maximumFractionDigits: 0 });
                        }
                    }
                }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>