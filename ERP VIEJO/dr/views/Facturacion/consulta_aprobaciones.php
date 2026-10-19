<?php require_once __DIR__ . '/../Estructura/header.php'; ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="views/Facturacion/styles/consulta_aprobaciones.css">


<div class="container-fluid consulta-container">
    
    <!-- Header de Página -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-shield-check"></i>
            </div>
            <div>
                <h2 class="page-title">Aprobaciones ARS & Facturación Especial</h2>
                <p class="page-subtitle">Gestión de autorizaciones médicas, emisión de facturas por lote a aseguradoras y relaciones de reclamaciones</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?page=facturacion" class="btn-header-action btn-header-success">
                <i class="bi bi-receipt"></i> Nueva Factura Paciente
            </a>
            <a href="index.php?page=dashboard" class="btn-header-action btn-header-home">
                <i class="bi bi-house"></i> Inicio
            </a>
        </div>
    </div>

    <div id="alertContainer">
        <?php echo Helper::getMensaje(); ?>
    </div>



    <!-- ============================================================
         PESTAÑAS DE NAVEGACIÓN MODULAR
         ============================================================ -->
    <div class="module-tabs-ars">
        <button type="button" class="module-tab-ars-btn active" id="btn-tab-pendientes" onclick="cambiarTabARS('tab-pendientes')">
            <i class="bi bi-clock-history text-primary"></i>
            <span>Pendientes de Facturar a ARS</span>
            <span class="tab-badge-ars tab-badge-pending"><?php echo $totalPendientes ?? 0; ?></span>
        </button>

        <button type="button" class="module-tab-ars-btn" id="btn-tab-facturadas" onclick="cambiarTabARS('tab-facturadas')">
            <i class="bi bi-patch-check-fill text-warning"></i>
            <span>Historial Facturadas a ARS</span>
            <span class="tab-badge-ars tab-badge-billed"><?php echo $totalFacturadas ?? 0; ?></span>
        </button>

        <button type="button" class="module-tab-ars-btn" id="btn-tab-cobro" onclick="cambiarTabARS('tab-cobro-ars')">
            <i class="bi bi-shield-shaded text-success"></i>
            <span>Pendientes por Cobrar a la ARS</span>
            <span class="tab-badge-ars tab-badge-cobro"><?php echo $totalPendientesCobroARS ?? 0; ?></span>
        </button>

        <button type="button" class="module-tab-ars-btn" id="btn-tab-reportes" onclick="cambiarTabARS('tab-reportes')">
            <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
            <span>Relación General de Reclamaciones (PDF)</span>
        </button>
    </div>

    <!-- ============================================================
         PESTAÑA 1: APROBACIONES PENDIENTES DE FACTURAR (PRIORIZADA)
         ============================================================ -->
    <div class="tab-pane-ars active" id="tab-pendientes">
        
        <!-- Barra de Filtros para Pendientes -->
        <div class="consulta-filter-bar mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-hourglass-top text-warning fs-5"></i>
                <span class="fw-bold text-dark">Autorizaciones Médicas Pendientes de Facturar a Aseguradora</span>
            </div>

            <div class="filter-controls-group">
                <div class="filter-date-group">
                    <span class="filter-date-label"><i class="bi bi-hospital me-1"></i>ARS:</span>
                    <select id="filtroARSPendiente" class="filter-date-input" onchange="filtrarPendientes()" style="min-width: 170px;">
                        <option value="TODAS">-- Todas las ARS --</option>
                        <?php if (!empty($listaARS)): ?>
                            <?php foreach ($listaARS as $ars): ?>
                                <option value="<?php echo htmlspecialchars($ars['CardCode']); ?>">
                                    <?php echo htmlspecialchars($ars['CardName']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="filter-date-group">
                    <span class="filter-date-label"><i class="bi bi-calendar-event me-1"></i>Desde:</span>
                    <input type="date" id="filtroDesdePendiente" class="filter-date-input" onchange="filtrarPendientes()">
                </div>
                <div class="filter-date-group">
                    <span class="filter-date-label"><i class="bi bi-calendar-event me-1"></i>Hasta:</span>
                    <input type="date" id="filtroHastaPendiente" class="filter-date-input" onchange="filtrarPendientes()">
                </div>
                <button type="button" onclick="limpiarFiltrosPendientes()" class="btn-filter-clear">
                    <i class="bi bi-x-circle text-secondary"></i> Limpiar
                </button>
            </div>
        </div>

        <!-- Barra de Acción de Facturación de Lote -->
        <div class="batch-action-bar">
            <div class="batch-stats-wrap">
                <div class="batch-stat-item">
                    <i class="bi bi-check2-square fs-5"></i>
                    <span>Seleccionadas:</span>
                    <span class="batch-stat-val" id="lblCantSeleccionadas">0</span>
                </div>
                <div class="batch-stat-item">
                    <i class="bi bi-cash-stack fs-5 text-success"></i>
                    <span>Total a Facturar a ARS:</span>
                    <span class="batch-stat-val text-success" id="lblMontoSeleccionado">RD$ 0.00</span>
                </div>
                <div class="batch-stat-item text-muted" id="lblARSSeleccionadaWrap" style="display: none;">
                    <span>Aseguradora:</span>
                    <strong class="text-dark" id="lblARSSeleccionadaNombre">-</strong>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-3" onclick="seleccionarTodasPendientesVisibles()">
                    <i class="bi bi-check-all"></i> Seleccionar Visibles
                </button>
                <button type="button" class="btn-batch-facturar" id="btnAbrirModalFacturarLote" onclick="abrirModalFacturarLote()" disabled>
                    <i class="bi bi-receipt-cutoff"></i>
                    <span>Facturar Lote a Aseguradora</span>
                </button>
            </div>
        </div>

        <!-- Tabla de Aprobaciones Pendientes -->
        <div class="consulta-card">
            <div class="consulta-card-body">
                <div class="table-responsive">
                    <table id="tablaPendientes" class="table table-hover align-middle table-sm" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 4%; text-align: center;">
                                    <input type="checkbox" id="chkSelectAllPendientes" class="check-aprobacion" onchange="toggleSelectAllPendientes(this)">
                                </th>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 13%;">N° Autorización</th>
                                <th style="width: 9%;">Fecha</th>
                                <th style="width: 17%;">Paciente / Afiliado</th>
                                <th style="width: 14%;">Aseguradora (ARS)</th>
                                <th style="width: 12%;">Servicio</th>
                                <th style="width: 10%;" class="text-end">Monto ARS</th>
                                <th style="width: 9%;" class="text-end">Copago</th>
                                <th style="width: 9%;" class="text-center">Fac. Paciente</th>
                                <th style="width: 7%;" class="text-center">Estado</th>
                                <th style="width: 7%;" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($aprobacionesPendientes)): ?>
                                <?php foreach ($aprobacionesPendientes as $a): 
                                    $jsonData = htmlspecialchars(json_encode($a), ENT_QUOTES, 'UTF-8');
                                    $arsCode = !empty($a['CodigoARSReal']) ? $a['CodigoARSReal'] : $a['CardCode_ARS'];
                                ?>
                                <tr class="fila-pendiente" 
                                    data-id="<?php echo $a['IdARS']; ?>"
                                    data-arscode="<?php echo htmlspecialchars($arsCode); ?>"
                                    data-arsname="<?php echo htmlspecialchars($a['NombreARS']); ?>"
                                    data-monto="<?php echo (float)$a['MontoAprobado']; ?>"
                                    data-servicio="<?php echo htmlspecialchars($a['TipoServicio'] ?? 'CONSULTA MEDICA'); ?>"
                                    data-fecha="<?php echo $a['FechaAprobacionD'] ?? ''; ?>">
                                    
                                    <td class="text-center">
                                        <input type="checkbox" 
                                               class="check-aprobacion check-fila-pendiente" 
                                               value="<?php echo $a['IdARS']; ?>"
                                               data-id="<?php echo $a['IdARS']; ?>"
                                               data-arscode="<?php echo htmlspecialchars($arsCode); ?>"
                                               data-arsname="<?php echo htmlspecialchars($a['NombreARS']); ?>"
                                               data-monto="<?php echo (float)$a['MontoAprobado']; ?>"
                                               data-servicio="<?php echo htmlspecialchars($a['TipoServicio'] ?? 'CONSULTA MEDICA'); ?>"
                                               onchange="actualizarSeleccionPendientes()">
                                    </td>
                                    <td class="font-monospace text-muted"><?php echo $a['IdARS']; ?></td>
                                    <td>
                                        <span class="badge-aprobacion font-monospace">
                                            <i class="bi bi-shield-lock-fill me-1"></i><?php echo htmlspecialchars($a['NumAprobacion'] ?? '-'); ?>
                                        </span>
                                    </td>
                                    <td class="font-monospace text-secondary"><?php echo $a['FechaAprobacionFormato'] ?? $a['FechaAprobacionD'] ?? '-'; ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($a['CardName'] ?? 'Sin nombre'); ?></div>
                                        <div class="text-muted" style="font-size: 0.74rem;">
                                            Carnet: <span class="font-monospace text-primary"><?php echo htmlspecialchars($a['NumAfiliado'] ?? 'N/D'); ?></span>
                                            <?php if (!empty($a['NumExpediente'])): ?>
                                                | Exp: <span class="font-monospace"><?php echo htmlspecialchars($a['NumExpediente']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-ars-pill">
                                            <?php echo htmlspecialchars($a['NombreARS'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.76rem;">
                                            <?php echo htmlspecialchars($a['TipoServicio'] ?? 'CONSULTA MEDICA'); ?>
                                        </span>
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-success">
                                        RD$ <?php echo number_format($a['MontoAprobado'] ?? 0, 2); ?>
                                    </td>
                                    <td class="text-end font-monospace text-dark">
                                        RD$ <?php echo number_format($a['MontoResponsable'] ?? 0, 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($a['NumFactura'])): ?>
                                            <?php if (strtoupper(trim($a['NumFactura'])) === 'COBERTURA 100%'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success font-monospace fw-semibold" style="font-size: 0.72rem;">
                                                    <i class="bi bi-shield-fill-check me-1"></i>Cobertura 100%
                                                </span>
                                            <?php else: ?>
                                                <span class="badge-factura font-monospace">
                                                    <?php echo htmlspecialchars($a['NumFactura']); ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 0.78rem;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-status badge-status-pendiente">
                                            Pendiente
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <button type="button" 
                                                    class="btn-action-view" 
                                                    onclick="abrirModalDetalle(<?php echo $jsonData; ?>)" 
                                                    title="Ver Detalle">
                                                <i class="bi bi-eye-fill"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn-action-edit" 
                                                    onclick="abrirModalEditarAprobacionARS(<?php echo $jsonData; ?>)" 
                                                    title="Editar Aprobación">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         PESTAÑA 2: HISTORIAL DE FACTURAS ARS & APROBACIONES FACTURADAS
         ============================================================ -->
    <div class="tab-pane-ars" id="tab-facturadas">
        
        <!-- Barra de Filtros para Facturadas -->
        <div class="consulta-filter-bar mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-check2-all text-success fs-5"></i>
                <span class="fw-bold text-dark">Historial de Aprobaciones Facturadas a Aseguradoras</span>
            </div>

            <div class="filter-controls-group">
                <div class="filter-date-group">
                    <span class="filter-date-label"><i class="bi bi-hospital me-1"></i>ARS:</span>
                    <select id="filtroARSFacturada" class="filter-date-input" onchange="filtrarFacturadas()" style="min-width: 170px;">
                        <option value="TODAS">-- Todas las ARS --</option>
                        <?php if (!empty($listaARS)): ?>
                            <?php foreach ($listaARS as $ars): ?>
                                <option value="<?php echo htmlspecialchars($ars['CardCode']); ?>">
                                    <?php echo htmlspecialchars($ars['CardName']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="filter-date-group">
                    <span class="filter-date-label"><i class="bi bi-calendar-event me-1"></i>Desde:</span>
                    <input type="date" id="filtroDesdeFacturada" class="filter-date-input" onchange="filtrarFacturadas()">
                </div>
                <div class="filter-date-group">
                    <span class="filter-date-label"><i class="bi bi-calendar-event me-1"></i>Hasta:</span>
                    <input type="date" id="filtroHastaFacturada" class="filter-date-input" onchange="filtrarFacturadas()">
                </div>
                <button type="button" onclick="limpiarFiltrosFacturadas()" class="btn-filter-clear">
                    <i class="bi bi-x-circle text-secondary"></i> Limpiar
                </button>
            </div>
        </div>

        <div class="consulta-card">
            <div class="consulta-card-body">
                <div class="table-responsive">
                    <table id="tablaFacturadas" class="table table-hover align-middle table-sm" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 13%;">N° Autorización</th>
                                <th style="width: 9%;">Fecha Aprob.</th>
                                <th style="width: 17%;">Paciente / Afiliado</th>
                                <th style="width: 14%;">Aseguradora (ARS)</th>
                                <th style="width: 11%;">Servicio</th>
                                <th style="width: 10%;" class="text-end">Monto ARS</th>
                                <th style="width: 10%;" class="text-center">Fact. Paciente</th>
                                <th style="width: 13%;" class="text-center">Factura / NCF ARS</th>
                                <th style="width: 7%;" class="text-center">Estado</th>
                                <th style="width: 11%;" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($aprobacionesFacturadas)): 
                                // Contar ocurrencias de cada lote para identificar multi-registros
                                $conteoPorLote = [];
                                foreach ($aprobacionesFacturadas as $item) {
                                    $lKey = !empty($item['DocEntry_FacturaARS']) ? 'DOC_' . $item['DocEntry_FacturaARS'] : (!empty($item['NumFacturaARS']) ? 'NCF_' . $item['NumFacturaARS'] : '');
                                    if ($lKey !== '') {
                                        $conteoPorLote[$lKey] = ($conteoPorLote[$lKey] ?? 0) + 1;
                                    }
                                }
                                $totalRowsFac = count($aprobacionesFacturadas);
                            ?>
                                <?php foreach ($aprobacionesFacturadas as $idx => $a): 
                                    $jsonData = htmlspecialchars(json_encode($a), ENT_QUOTES, 'UTF-8');
                                    $docEntryARS = $a['DocEntry_FacturaARS'] ?? null;
                                    $numFacturaARS = $a['NumFacturaARS'] ?? null;

                                    $loteIdAttr = !empty($docEntryARS) ? 'DOC_' . $docEntryARS : (!empty($numFacturaARS) ? 'NCF_' . $numFacturaARS : '');
                                    $esMultiLote = (!empty($loteIdAttr) && ($conteoPorLote[$loteIdAttr] ?? 0) > 1);

                                    $prevItem = ($idx > 0) ? $aprobacionesFacturadas[$idx - 1] : null;
                                    $nextItem = ($idx < $totalRowsFac - 1) ? $aprobacionesFacturadas[$idx + 1] : null;

                                    $prevLoteKey = $prevItem ? (!empty($prevItem['DocEntry_FacturaARS']) ? 'DOC_' . $prevItem['DocEntry_FacturaARS'] : (!empty($prevItem['NumFacturaARS']) ? 'NCF_' . $prevItem['NumFacturaARS'] : '')) : '';
                                    $nextLoteKey = $nextItem ? (!empty($nextItem['DocEntry_FacturaARS']) ? 'DOC_' . $nextItem['DocEntry_FacturaARS'] : (!empty($nextItem['NumFacturaARS']) ? 'NCF_' . $nextItem['NumFacturaARS'] : '')) : '';

                                    $clasesLote = '';
                                    if ($esMultiLote && ($prevLoteKey === $loteIdAttr || $nextLoteKey === $loteIdAttr)) {
                                        $clasesLote .= ' fila-en-lote';
                                        if ($prevLoteKey !== $loteIdAttr && $nextLoteKey === $loteIdAttr) {
                                            $clasesLote .= ' lote-inicio';
                                        } elseif ($prevLoteKey === $loteIdAttr && $nextLoteKey !== $loteIdAttr) {
                                            $clasesLote .= ' lote-fin';
                                        } else {
                                            $clasesLote .= ' lote-medio';
                                        }
                                    }
                                    if ($nextItem !== null && $loteIdAttr !== '' && $loteIdAttr !== $nextLoteKey) {
                                        $clasesLote .= ' lote-separador-fin';
                                    }
                                    $loteSortKey = !empty($docEntryARS) ? sprintf('%010d', $docEntryARS) : (!empty($numFacturaARS) ? $numFacturaARS : '0000000000');
                                    $rowOrderKey = $loteSortKey . '_' . sprintf('%010d', $a['IdARS']);
                                ?>
                                <tr class="fila-facturada<?php echo $clasesLote; ?>"
                                    data-arscode="<?php echo htmlspecialchars($a['CodigoARSReal'] ?? $a['CardCode_ARS']); ?>"
                                    data-lote="<?php echo htmlspecialchars($loteIdAttr); ?>"
                                    data-fecha="<?php echo $a['FechaAprobacionD'] ?? ''; ?>">
                                    
                                    <td class="font-monospace text-muted" data-order="<?php echo $rowOrderKey; ?>"><?php echo $a['IdARS']; ?></td>
                                    <td>
                                        <span class="badge-aprobacion font-monospace">
                                            <i class="bi bi-shield-lock-fill me-1"></i><?php echo htmlspecialchars($a['NumAprobacion'] ?? '-'); ?>
                                        </span>
                                    </td>
                                    <td class="font-monospace text-secondary"><?php echo $a['FechaAprobacionFormato'] ?? $a['FechaAprobacionD'] ?? '-'; ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($a['CardName'] ?? 'Sin nombre'); ?></div>
                                        <div class="text-muted" style="font-size: 0.74rem;">
                                            Carnet: <span class="font-monospace text-primary"><?php echo htmlspecialchars($a['NumAfiliado'] ?? 'N/D'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-ars-pill">
                                            <?php echo htmlspecialchars($a['NombreARS'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.76rem;">
                                            <?php echo htmlspecialchars($a['TipoServicio'] ?? 'CONSULTA MEDICA'); ?>
                                        </span>
                                    </td>
                                    <td class="text-end font-monospace fw-bold text-success">
                                        RD$ <?php echo number_format($a['MontoAprobado'] ?? 0, 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($a['NumFactura'])): ?>
                                            <?php if (strtoupper(trim($a['NumFactura'])) === 'COBERTURA 100%'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success font-monospace fw-semibold" style="font-size: 0.72rem;">
                                                    <i class="bi bi-shield-fill-check me-1"></i>Cobertura 100%
                                                </span>
                                            <?php else: ?>
                                                <span class="badge-factura font-monospace">
                                                    <?php echo htmlspecialchars($a['NumFactura']); ?>
                                                </span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center" data-order="<?php echo $rowOrderKey; ?>">
                                        <?php if (!empty($a['NumFacturaARS'])): ?>
                                            <span class="badge bg-primary-subtle text-primary border font-monospace fw-bold" style="font-size: 0.78rem;">
                                                <i class="bi bi-receipt me-1"></i><?php echo htmlspecialchars($a['NumFacturaARS']); ?>
                                            </span>
                                        <?php elseif (!empty($docEntryARS)): ?>
                                            <span class="badge bg-primary-subtle text-primary border font-monospace">
                                                DocEntry: <?php echo $docEntryARS; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border">Facturado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge-status badge-status-facturado">
                                            Facturado
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <?php if (!empty($docEntryARS)): ?>
                                                <a href="index.php?action=reporte_fact_aprobacion&docEntry=<?php echo $docEntryARS; ?>" 
                                                   target="_blank" 
                                                   class="btn-action-factura-ars" 
                                                   title="Imprimir Factura Especial ARS">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                </a>
                                                <a href="index.php?action=reporte_aprobacion_ars&docEntry=<?php echo $docEntryARS; ?>" 
                                                   target="_blank" 
                                                   class="btn-action-relacion" 
                                                   title="Imprimir Relación de Reclamaciones de este Lote">
                                                    <i class="bi bi-list-columns-reverse"></i>
                                                </a>
                                            <?php else: ?>
                                                <button type="button" class="btn-action-view" onclick="abrirModalDetalle(<?php echo $jsonData; ?>)" title="Ver Detalle">
                                                    <i class="bi bi-eye-fill"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         PESTAÑA 3: GESTIÓN DE COBRO ARS & TRAZABILIDAD (SUBVISTA MODULAR)
         ============================================================ -->
    <?php require_once __DIR__ . '/partials/tab_gestion_cobro_ars.php'; ?>

    <!-- ============================================================
         PESTAÑA 4: GENERADOR DE RELACIÓN GENERAL DE RECLAMACIONES (PDF)
         ============================================================ -->
    <div class="tab-pane-ars" id="tab-reportes">
        <div class="report-generator-card mb-4">
            <div class="report-generator-header">
                <div class="d-flex align-items-center gap-3">
                    <div class="generator-icon-badge">
                        <i class="bi bi-file-earmark-pdf-fill"></i>
                    </div>
                    <div>
                        <h3 class="generator-title">Generador de Relación General de Reclamaciones (PDF)</h3>
                        <p class="generator-subtitle">Emita el reporte mensual agrupado por ARS para la liquidación de servicios médicos</p>
                    </div>
                </div>
                <div class="quick-period-pills">
                    <button type="button" class="btn-pill-period" onclick="seleccionarPeriodoActual()">
                        <i class="bi bi-calendar-check"></i> Mes Actual
                    </button>
                    <button type="button" class="btn-pill-period" onclick="seleccionarPeriodoAnterior()">
                        <i class="bi bi-calendar-minus"></i> Mes Anterior
                    </button>
                </div>
            </div>

            <div class="report-generator-body">
                <form id="formGenerarReporte" onsubmit="event.preventDefault(); generarReportePDF();" class="row g-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-report-label" for="reporte_mes">
                            <i class="bi bi-calendar3 me-1 text-primary"></i> Mes de Reclamación:
                        </label>
                        <select id="reporte_mes" name="mes" class="form-select form-report-select">
                            <?php 
                            $nombresMeses = [
                                1 => '01 - Enero', 2 => '02 - Febrero', 3 => '03 - Marzo', 4 => '04 - Abril',
                                5 => '05 - Mayo', 6 => '06 - Junio', 7 => '07 - Julio', 8 => '08 - Agosto',
                                9 => '09 - Septiembre', 10 => '10 - Octubre', 11 => '11 - Noviembre', 12 => '12 - Diciembre'
                            ];
                            foreach ($nombresMeses as $numMes => $nombreMes): 
                                $selected = ($numMes === (int)date('n')) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $numMes; ?>" <?php echo $selected; ?>>
                                    <?php echo $nombreMes; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label class="form-report-label" for="reporte_anio">
                            <i class="bi bi-calendar2-range me-1 text-primary"></i> Año:
                        </label>
                        <select id="reporte_anio" name="anio" class="form-select form-report-select">
                            <?php 
                            $anioBase = (int)date('Y');
                            for ($y = $anioBase + 1; $y >= $anioBase - 3; $y--): 
                                $selected = ($y === $anioBase) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $y; ?>" <?php echo $selected; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-8">
                        <label class="form-report-label" for="reporte_ars">
                            <i class="bi bi-hospital me-1 text-primary"></i> Aseguradora (ARS):
                        </label>
                        <select id="reporte_ars" name="ars" class="form-select form-report-select">
                            <option value="TODAS">-- (TODAS LAS ASEGURADORAS) --</option>
                            <?php if (!empty($listaARS)): ?>
                                <?php foreach ($listaARS as $ars): ?>
                                    <option value="<?php echo htmlspecialchars($ars['CardCode']); ?>">
                                        <?php echo htmlspecialchars($ars['CardName']); ?> (<?php echo htmlspecialchars($ars['CardCode']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="col-lg-3 col-md-4">
                        <button type="button" onclick="generarReportePDF()" class="btn-generate-report-pdf w-100">
                            <i class="bi bi-printer-fill me-1"></i> Generar Relación PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<!-- ============================================================
     MODAL DE FACTURACIÓN DE LOTE A ASEGURADORA (ARS)
     ============================================================ -->
<div class="modal fade" id="modalFacturarLoteARS" tabindex="-1" aria-labelledby="modalFacturarLoteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            
            <div class="modal-header bg-success text-white p-3 rounded-top-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-receipt-cutoff fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalFacturarLoteLabel">Emitir Factura a Aseguradora (Lote de Reclamaciones)</h5>
                        <small class="text-white-50">Generación de factura electrónica (e-CF E31) agrupada por servicios</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                
                <!-- Resumen de Aseguradora y Totales -->
                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="row align-items-center g-2">
                        <div class="col-md-7">
                            <span class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700;">Aseguradora Destino:</span>
                            <h5 class="text-success fw-bold mb-0" id="modal_ars_nombre">-</h5>
                            <span class="text-muted font-monospace" style="font-size: 0.8rem;" id="modal_ars_codigo">-</span>
                        </div>
                        <div class="col-md-5 text-md-end">
                            <span class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700;">Monto Total a Reclamar:</span>
                            <h4 class="text-dark font-monospace fw-bold mb-0" id="modal_total_monto">RD$ 0.00</h4>
                            <span class="badge bg-success" id="modal_total_cant">0 autorizaciones</span>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Agrupación de Servicios -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2">
                        <i class="bi bi-list-task text-primary me-1"></i> Desglose Agrupado por Tipo de Servicio (Factura Final)
                    </h6>
                    <div class="table-responsive border rounded-3 overflow-hidden">
                        <table class="table table-sm align-middle mb-0 modal-services-table" id="tablaModalServicios">
                            <thead>
                                <tr>
                                    <th style="width: 20%; text-align: center;">Cantidad</th>
                                    <th style="width: 50%;">Descripción del Servicio</th>
                                    <th style="width: 30%; text-align: right;">Total (RD$)</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyModalServicios">
                                <!-- Filas generadas dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Parámetros Fiscales de la Factura -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 0.82rem;">Tipo de Comprobante NCF:</label>
                        <div class="modal-locked-input">
                            <span class="badge-locked-tag">E31</span>
                            <span class="modal-locked-text">Crédito Fiscal Electrónico</span>
                            <input type="hidden" id="modal_ncf_serie" value="E31">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 0.82rem;">Fecha de Emisión:</label>
                        <input type="date" id="modal_doc_date" class="form-control form-control-sm" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="font-size: 0.82rem;">Condición de Crédito:</label>
                        <select id="modal_extra_days" class="form-select form-select-sm">
                            <option value="30" selected>30 Días</option>
                            <option value="15">15 Días</option>
                            <option value="60">60 Días</option>
                            <option value="1">Contado</option>
                        </select>
                    </div>
                </div>

                <!-- Alert de éxito / progreso post emisión -->
                <div id="modalSuccessResult" class="mt-3 p-3 bg-success-subtle border border-success rounded-3" style="display: none;">
                    <div class="d-flex align-items-center gap-2 text-success fw-bold mb-2">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <span>¡Factura de Aseguradora generada con éxito!</span>
                    </div>
                    <div class="mb-3" style="font-size: 0.88rem;">
                        <strong>NCF Asignado:</strong> <span class="font-monospace fw-bold text-dark" id="res_ncf">-</span> | 
                        <strong>Documento:</strong> <span class="font-monospace" id="res_docnum">-</span>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="#" id="lnkImprimirFacturaARS" target="_blank" class="btn btn-sm btn-success fw-bold px-3 py-2 rounded-3">
                            <i class="bi bi-printer-fill me-1"></i> 1. Imprimir Factura ARS (PDF)
                        </a>
                        <a href="#" id="lnkImprimirRelacionARS" target="_blank" class="btn btn-sm btn-primary fw-bold px-3 py-2 rounded-3">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> 2. Imprimir Relación de Reclamaciones (PDF)
                        </a>
                    </div>
                </div>

            </div>

            <div class="modal-footer p-3 bg-light rounded-bottom-4">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal" id="btnCerrarModalFacturar">
                    Cancelar
                </button>
                <button type="button" class="btn btn-success px-4 fw-bold" id="btnConfirmarFacturarLote" onclick="confirmarFacturacionLoteARS()">
                    <i class="bi bi-check2-circle me-1"></i> Confirmar y Emitir Factura
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ============================================================
     MODAL DE FICHA DE DETALLE CLÍNICO DE APROBACIÓN
     ============================================================ -->
<div class="modal fade" id="modalDetalleAprobacion" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content modal-detail-content">
            
            <div class="modal-header modal-detail-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="modal-header-icon-badge">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="modalDetalleLabel">Ficha Clínica de Aprobación ARS</h5>
                        <p class="modal-subtitle mb-0">Detalle completo de la autorización médica y cobertura financiera</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-white">
                
                <!-- Banner Superior: Número de Aprobación y Estado -->
                <div class="detail-banner mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span class="info-label-muted d-block">Número de Autorización ARS</span>
                        <h3 class="font-monospace text-primary fw-bold mb-0" id="det_NumAprobacion">-</h3>
                    </div>
                    <div class="text-end">
                        <span class="badge" id="det_BadgeStatus" style="font-size: 0.85rem; padding: 7px 14px; border-radius: 8px;">-</span>
                    </div>
                </div>

                <!-- Grilla de Tarjetas de Información Estructurada -->
                <div class="row g-3 mb-3">
                    <!-- Tarjeta 1: Datos del Paciente -->
                    <div class="col-md-6">
                        <div class="modal-nc-card-info">
                            <div class="info-label-muted">
                                <i class="bi bi-person-fill text-primary"></i> Datos del Paciente
                            </div>
                            <div id="det_CardName" class="info-value-bold text-dark text-truncate mb-2" style="font-size: 0.96rem;">-</div>
                            
                            <div class="detail-info-grid">
                                <div class="detail-info-row">
                                    <span class="detail-info-label">Cédula / RNC:</span>
                                    <span class="detail-info-val font-monospace" id="det_Cedula">-</span>
                                </div>
                                <div class="detail-info-row">
                                    <span class="detail-info-label">Código Paciente:</span>
                                    <span class="detail-info-val font-monospace text-secondary" id="det_CardCode">-</span>
                                </div>
                                <div class="detail-info-row">
                                    <span class="detail-info-label">N° Expediente:</span>
                                    <span class="detail-info-val font-monospace fw-bold text-dark" id="det_NumExpediente">-</span>
                                </div>
                                <div class="detail-info-row">
                                    <span class="detail-info-label">N° Carnet / Afiliado:</span>
                                    <span class="detail-info-val font-monospace fw-bold text-primary" id="det_NumAfiliado">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta 2: Aseguradora (ARS) & Cobertura -->
                    <div class="col-md-6">
                        <div class="modal-nc-card-info">
                            <div class="info-label-muted">
                                <i class="bi bi-hospital-fill text-success"></i> Aseguradora (ARS) & Servicio
                            </div>
                            <div id="det_NombreARS" class="info-value-bold text-success text-truncate mb-2" style="font-size: 0.96rem;">-</div>

                            <div class="detail-info-grid">
                                <div class="detail-info-row">
                                    <span class="detail-info-label">Código ARS:</span>
                                    <span class="detail-info-val font-monospace text-secondary" id="det_CardCodeARS">-</span>
                                </div>
                                <div class="detail-info-row">
                                    <span class="detail-info-label">Servicio Cubierto:</span>
                                    <span class="detail-info-val fw-semibold text-dark" id="det_TipoServicio">-</span>
                                </div>
                                <div class="detail-info-row">
                                    <span class="detail-info-label">Factura Paciente:</span>
                                    <span class="detail-info-val" id="det_NumFacturaWrap">-</span>
                                </div>
                                <div class="detail-info-row">
                                    <span class="detail-info-label">Factura Lote ARS:</span>
                                    <span class="detail-info-val" id="det_NumFacturaARSWrap">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 3: Cobertura Financiera & Copago -->
                <div class="modal-nc-card-info mb-3 p-3">
                    <div class="info-label-muted mb-2">
                        <i class="bi bi-cash-coin text-warning"></i> Cobertura Financiera & Copago
                    </div>
                    <div class="row text-center g-2">
                        <div class="col-4">
                            <div class="fin-box h-100">
                                <span class="fin-label">% Cobertura</span>
                                <h5 class="fin-value text-primary font-monospace" id="det_PorcentajeCober">0.00%</h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="fin-box h-100">
                                <span class="fin-label">Monto Cubierto ARS</span>
                                <h5 class="fin-value text-success font-monospace" id="det_MontoAprobado">RD$ 0.00</h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="fin-box h-100">
                                <span class="fin-label">Copago Paciente</span>
                                <h5 class="fin-value text-dark font-monospace" id="det_MontoResponsable">RD$ 0.00</h5>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 4: Fechas & Auditoría -->
                <div class="modal-nc-card-info p-3">
                    <div class="info-label-muted mb-2">
                        <i class="bi bi-clock-history text-secondary"></i> Fechas & Auditoría del Registro
                    </div>
                    <div class="row g-2" style="font-size: 0.82rem;">
                        <div class="col-md-4">
                            <span class="text-muted d-block" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600;">Fecha de Aprobación:</span>
                            <div class="font-monospace fw-bold text-dark mt-1" id="det_FechaAprobacion">-</div>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted d-block" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600;">Fecha de Vencimiento:</span>
                            <div class="font-monospace fw-semibold text-dark mt-1" id="det_FechaVencimiento">-</div>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted d-block" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600;">Usuario Registrador:</span>
                            <div class="fw-bold text-primary mt-1" id="det_Usuario">-</div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer modal-detail-footer d-flex justify-content-between align-items-center">
                <span class="text-muted small font-monospace" id="det_AuditID">ID Aprobación: -</span>
                <button type="button" class="btn btn-secondary px-4 py-1 rounded-3" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Librerías JS DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<!-- Modal Compartido de Edición / Aprobación ARS -->
<?php include __DIR__ . '/num_aprobacion_ars.php'; ?>

<script>
    var tablaPendientesDT;
    var tablaFacturadasDT;
    var itemsSeleccionadosPendientes = [];

    // Auto-dismiss de mensajes de alerta tras 5 segundos
    setTimeout(function() {
        const alerts = document.querySelectorAll('#alertContainer .alert');
        alerts.forEach(function(el) {
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(() => el.remove(), 600);
        });
    }, 5000);

    var tablaCobroARSDT;
    var itemsSeleccionadosCobro = [];

    $(document).ready(function() {
        // Inicializar DataTable de Pendientes
        tablaPendientesDT = $('#tablaPendientes').DataTable({
            language: {
                processing: "Procesando...",
                search: "Buscar en pendientes:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ pendientes",
                infoEmpty: "0 pendientes",
                infoFiltered: "(filtrado de _MAX_ totales)",
                zeroRecords: '<div class="empty-data-state"><div class="empty-state-icon empty-neutral"><i class="bi bi-search"></i></div><h6 class="empty-state-title">Sin resultados coincidentes</h6><p class="empty-state-text">No se encontraron autorizaciones médicas con los filtros de búsqueda aplicados.</p></div>',
                emptyTable: '<div class="empty-data-state"><div class="empty-state-icon empty-pending"><i class="bi bi-shield-check"></i></div><h6 class="empty-state-title">No hay autorizaciones pendientes</h6><p class="empty-state-text">Todas las autorizaciones registradas han sido facturadas o no hay datos cargados en el sistema.</p></div>',
                paginate: { first: "Primero", previous: "Anterior", next: "Siguiente", last: "Último" }
            },
            pageLength: 25,
            order: [[ 1, 'desc' ]],
            columnDefs: [ { orderable: false, targets: [0, 11] } ]
        });

        // Inicializar DataTable de Facturadas
        tablaFacturadasDT = $('#tablaFacturadas').DataTable({
            language: {
                processing: "Procesando...",
                search: "Buscar en facturadas:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ facturadas",
                infoEmpty: "0 facturadas",
                infoFiltered: "(filtrado de _MAX_ totales)",
                zeroRecords: '<div class="empty-data-state"><div class="empty-state-icon empty-neutral"><i class="bi bi-search"></i></div><h6 class="empty-state-title">Sin resultados coincidentes</h6><p class="empty-state-text">No se encontraron facturas o comprobantes con los filtros de búsqueda aplicados.</p></div>',
                emptyTable: '<div class="empty-data-state"><div class="empty-state-icon empty-billed"><i class="bi bi-receipt"></i></div><h6 class="empty-state-title">No hay autorizaciones facturadas</h6><p class="empty-state-text">Aún no se han emitido facturas de lotes a aseguradoras (ARS).</p></div>',
                paginate: { first: "Primero", previous: "Anterior", next: "Siguiente", last: "Último" }
            },
            pageLength: 25,
            order: [], // Mantener el orden agrupado por lote del backend para que los lotes no se separen
            columnDefs: [ { orderable: false, targets: 10 } ],
            drawCallback: function() {
                actualizarConectoresLoteFacturadas();
            }
        });

        // Inicializar DataTable de Gestión de Cobro ARS
        tablaCobroARSDT = $('#tablaCobroARS').DataTable({
            language: {
                processing: "Procesando...",
                search: "Buscar en gestión de cobro:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ facturas ARS",
                infoEmpty: "0 facturas",
                infoFiltered: "(filtrado de _MAX_ totales)",
                zeroRecords: '<div class="empty-data-state"><div class="empty-state-icon empty-neutral"><i class="bi bi-search"></i></div><h6 class="empty-state-title">Sin resultados coincidentes</h6><p class="empty-state-text">No se encontraron facturas de aseguradoras con los criterios seleccionados.</p></div>',
                emptyTable: '<div class="empty-data-state"><div class="empty-state-icon empty-billed"><i class="bi bi-shield-shaded"></i></div><h6 class="empty-state-title">No hay facturas registradas para cobro</h6><p class="empty-state-text">Las facturas emitidas por lotes a aseguradoras aparecerán en este módulo.</p></div>',
                paginate: { first: "Primero", previous: "Anterior", next: "Siguiente", last: "Último" }
            },
            pageLength: 25,
            order: [], // Mantener el orden de prioridad del backend (Pendientes y por fecha arriba)
            columnDefs: [ { orderable: false, targets: [0, 9] } ]
        });
    });

    // ==========================================
    // CAMBIO DE PESTAÑAS
    // ==========================================
    function cambiarTabARS(tabId) {
        $('.module-tab-ars-btn').removeClass('active');
        $('.tab-pane-ars').removeClass('active');

        if (tabId === 'tab-pendientes') {
            $('#btn-tab-pendientes').addClass('active');
            $('#tab-pendientes').addClass('active');
            if (tablaPendientesDT) tablaPendientesDT.columns.adjust().draw();
        } else if (tabId === 'tab-facturadas') {
            $('#btn-tab-facturadas').addClass('active');
            $('#tab-facturadas').addClass('active');
            if (tablaFacturadasDT) tablaFacturadasDT.columns.adjust().draw();
        } else if (tabId === 'tab-cobro-ars') {
            $('#btn-tab-cobro').addClass('active');
            $('#tab-cobro-ars').addClass('active');
            if (tablaCobroARSDT) tablaCobroARSDT.columns.adjust().draw();
        } else if (tabId === 'tab-reportes') {
            $('#btn-tab-reportes').addClass('active');
            $('#tab-reportes').addClass('active');
        }
    }

    // ==========================================
    // FILTROS EN TIEMPO REAL - PENDIENTES
    // ==========================================
    function filtrarPendientes() {
        var ars = $('#filtroARSPendiente').val();
        var desde = $('#filtroDesdePendiente').val();
        var hasta = $('#filtroHastaPendiente').val();

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'tablaPendientes') return true;

            var node = tablaPendientesDT.row(dataIndex).node();
            var rowARS = $(node).attr('data-arscode') || '';
            var rowFecha = $(node).attr('data-fecha') || '';

            if (ars !== 'TODAS' && rowARS !== ars) {
                return false;
            }

            if (desde && rowFecha && rowFecha < desde) {
                return false;
            }

            if (hasta && rowFecha && rowFecha > hasta) {
                return false;
            }

            return true;
        });

        tablaPendientesDT.draw();
        $.fn.dataTable.ext.search.pop();
        actualizarSeleccionPendientes();
    }

    function limpiarFiltrosPendientes() {
        $('#filtroARSPendiente').val('TODAS');
        $('#filtroDesdePendiente').val('');
        $('#filtroHastaPendiente').val('');
        filtrarPendientes();
    }

    // ==========================================
    // FILTROS EN TIEMPO REAL - FACTURADAS
    // ==========================================
    function filtrarFacturadas() {
        var ars = $('#filtroARSFacturada').val();
        var desde = $('#filtroDesdeFacturada').val();
        var hasta = $('#filtroHastaFacturada').val();

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'tablaFacturadas') return true;

            var node = tablaFacturadasDT.row(dataIndex).node();
            var rowARS = $(node).attr('data-arscode') || '';
            var rowFecha = $(node).attr('data-fecha') || '';

            if (ars !== 'TODAS' && rowARS !== ars) {
                return false;
            }

            if (desde && rowFecha && rowFecha < desde) {
                return false;
            }

            if (hasta && rowFecha && rowFecha > hasta) {
                return false;
            }

            return true;
        });

        tablaFacturadasDT.draw();
        $.fn.dataTable.ext.search.pop();
    }

    function limpiarFiltrosFacturadas() {
        $('#filtroARSFacturada').val('TODAS');
        $('#filtroDesdeFacturada').val('');
        $('#filtroHastaFacturada').val('');
        filtrarFacturadas();
    }

    // ==========================================
    // CONECTORES VISUALES DE LOTE EN FACTURADAS
    // ==========================================
    function actualizarConectoresLoteFacturadas() {
        var $filas = $('#tablaFacturadas tbody tr.fila-facturada');
        if (!$filas.length) return;

        // Limpiar clases de lote previas en el DOM
        $filas.removeClass('fila-en-lote lote-inicio lote-medio lote-fin lote-separador-fin');

        var total = $filas.length;
        $filas.each(function(idx) {
            var $tr = $(this);
            var lote = ($tr.attr('data-lote') || '').trim();
            if (!lote) return;

            var prevLote = (idx > 0) ? ($($filas[idx - 1]).attr('data-lote') || '').trim() : '';
            var nextLote = (idx < total - 1) ? ($($filas[idx + 1]).attr('data-lote') || '').trim() : '';

            // Si está adyacente a otra fila del mismo lote (pertenece a un grupo en la vista actual)
            if (prevLote === lote || nextLote === lote) {
                $tr.addClass('fila-en-lote');
                if (prevLote !== lote && nextLote === lote) {
                    $tr.addClass('lote-inicio');
                } else if (prevLote === lote && nextLote !== lote) {
                    $tr.addClass('lote-fin');
                } else if (prevLote === lote && nextLote === lote) {
                    $tr.addClass('lote-medio');
                }
            }

            // Separador sólido al final de un lote si la siguiente fila es de otro lote
            if (idx < total - 1 && lote !== nextLote) {
                $tr.addClass('lote-separador-fin');
            }
        });
    }

    // ==========================================

    // SELECCIÓN DE APROBACIONES PENDIENTES
    // ==========================================
    function toggleSelectAllPendientes(masterChk) {
        var isChecked = $(masterChk).is(':checked');
        $('#tablaPendientes tbody tr:visible .check-fila-pendiente').prop('checked', isChecked);
        actualizarSeleccionPendientes();
    }

    function seleccionarTodasPendientesVisibles() {
        $('#tablaPendientes tbody tr:visible .check-fila-pendiente').prop('checked', true);
        $('#chkSelectAllPendientes').prop('checked', true);
        actualizarSeleccionPendientes();
    }

    function actualizarSeleccionPendientes() {
        itemsSeleccionadosPendientes = [];
        var totalMonto = 0;
        var aseguradorasDiferentes = new Set();
        var primerNombreARS = '';

        $('.check-fila-pendiente:checked').each(function() {
            var id = $(this).val();
            var arsCode = $(this).attr('data-arscode');
            var arsName = $(this).attr('data-arsname');
            var monto = parseFloat($(this).attr('data-monto') || 0);
            var servicio = $(this).attr('data-servicio') || 'CONSULTA';

            itemsSeleccionadosPendientes.push({
                id: id,
                arsCode: arsCode,
                arsName: arsName,
                monto: monto,
                servicio: servicio
            });

            totalMonto += monto;
            aseguradorasDiferentes.add(arsCode);
            if (!primerNombreARS) primerNombreARS = arsName;
        });

        var cant = itemsSeleccionadosPendientes.length;
        $('#lblCantSeleccionadas').text(cant);
        $('#lblMontoSeleccionado').text('RD$ ' + totalMonto.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        if (cant > 0) {
            $('#lblARSSeleccionadaWrap').show();
            if (aseguradorasDiferentes.size > 1) {
                $('#lblARSSeleccionadaNombre').html('<span class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Múltiples ARS (' + aseguradorasDiferentes.size + ')</span>');
                $('#btnAbrirModalFacturarLote').prop('disabled', true).attr('title', 'Seleccione aprobaciones de una sola ARS a la vez');
            } else {
                $('#lblARSSeleccionadaNombre').text(primerNombreARS);
                $('#btnAbrirModalFacturarLote').prop('disabled', false).removeAttr('title');
            }
        } else {
            $('#lblARSSeleccionadaWrap').hide();
            $('#btnAbrirModalFacturarLote').prop('disabled', true);
        }
    }

    // ==========================================
    // MODAL DE FACTURACIÓN DE LOTE ARS
    // ==========================================
    function abrirModalFacturarLote() {
        if (itemsSeleccionadosPendientes.length === 0) {
            alert('Debe seleccionar al menos una aprobación pendiente.');
            return;
        }

        var primerItem = itemsSeleccionadosPendientes[0];
        $('#modal_ars_nombre').text(primerItem.arsName);
        $('#modal_ars_codigo').text('Código: ' + primerItem.arsCode);

        // Agrupar servicios
        var serviciosMap = {};
        var granTotal = 0;

        itemsSeleccionadosPendientes.forEach(function(item) {
            var rawS = (item.servicio || 'CONSULTA MEDICA').toUpperCase()
                .replace(/Á/g, 'A').replace(/É/g, 'E').replace(/Í/g, 'I').replace(/Ó/g, 'O').replace(/Ú/g, 'U');
            var serv = rawS.trim();
            if (!serviciosMap[serv]) {
                serviciosMap[serv] = { cantidad: 0, total: 0 };
            }
            serviciosMap[serv].cantidad += 1;
            serviciosMap[serv].total += item.monto;
            granTotal += item.monto;
        });

        var tbodyHtml = '';
        for (var s in serviciosMap) {
            tbodyHtml += '<tr>' +
                '<td class="text-center fw-bold font-monospace">' + serviciosMap[s].cantidad + '</td>' +
                '<td class="fw-semibold">' + s + '</td>' +
                '<td class="text-end font-monospace fw-bold text-success">RD$ ' + serviciosMap[s].total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
            '</tr>';
        }

        $('#tbodyModalServicios').html(tbodyHtml);
        $('#modal_total_monto').text('RD$ ' + granTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#modal_total_cant').text(itemsSeleccionadosPendientes.length + ' autorizaciones');

        $('#modalSuccessResult').hide();
        $('#btnConfirmarFacturarLote').show().prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Confirmar y Emitir Factura');
        $('#btnCerrarModalFacturar').text('Cancelar');

        new bootstrap.Modal(document.getElementById('modalFacturarLoteARS')).show();
    }

    function confirmarFacturacionLoteARS() {
        if (itemsSeleccionadosPendientes.length === 0) return;

        var primerItem = itemsSeleccionadosPendientes[0];
        var ids = itemsSeleccionadosPendientes.map(function(x) { return parseInt(x.id); });
        var serieNCF = $('#modal_ncf_serie').val();
        var docDate = $('#modal_doc_date').val();
        var extraDays = $('#modal_extra_days').val();

        var $btn = $('#btnConfirmarFacturarLote');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Emitiendo Factura ARS...');

        $.ajax({
            url: 'index.php?action=facturar_lote_ars',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                CardCode_ARS: primerItem.arsCode,
                ids: ids,
                U_FacSerie: serieNCF,
                DocDate: docDate,
                ExtraDays: extraDays
            }),
            dataType: 'json',
            success: function(resp) {
                if (resp.status === 'success') {
                    $btn.hide();
                    $('#btnCerrarModalFacturar').text('Cerrar Ventana');

                    $('#res_ncf').text(resp.ncf || 'Emitido');
                    $('#res_docnum').text(resp.numAtCard || ('DocEntry: ' + resp.docEntry));
                    
                    $('#lnkImprimirFacturaARS').attr('href', 'index.php?action=reporte_fact_aprobacion&docEntry=' + resp.docEntry);
                    $('#lnkImprimirRelacionARS').attr('href', 'index.php?action=reporte_aprobacion_ars&docEntry=' + resp.docEntry);

                    $('#modalSuccessResult').slideDown();

                    // Al cerrar modal, recargar la pantalla para reflejar cambios
                    $('#modalFacturarLoteARS').on('hidden.bs.modal', function() {
                        location.reload();
                    });
                } else {
                    alert('Error: ' + (resp.msg || 'No se pudo generar la factura.'));
                    $btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Confirmar y Emitir Factura');
                }
            },
            error: function(xhr) {
                var msg = 'Error de comunicación con el servidor.';
                try {
                    var j = JSON.parse(xhr.responseText);
                    if (j.msg) msg = j.msg;
                } catch(e) {}
                alert('Error al facturar lote: ' + msg);
                $btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Confirmar y Emitir Factura');
            }
        });
    }

    // ==========================================
    // GENERADOR PDF RELACIÓN GENERAL
    // ==========================================
    function generarReportePDF() {
        var mes = document.getElementById('reporte_mes').value;
        var anio = document.getElementById('reporte_anio').value;
        var ars = document.getElementById('reporte_ars').value;

        var url = 'index.php?action=reporte_aprobacion_ars&mes=' + encodeURIComponent(mes) + '&anio=' + encodeURIComponent(anio) + '&ars=' + encodeURIComponent(ars);
        window.open(url, '_blank');
    }

    function seleccionarPeriodoActual() {
        var d = new Date();
        document.getElementById('reporte_mes').value = d.getMonth() + 1;
        document.getElementById('reporte_anio').value = d.getFullYear();
    }

    function seleccionarPeriodoAnterior() {
        var d = new Date();
        d.setMonth(d.getMonth() - 1);
        document.getElementById('reporte_mes').value = d.getMonth() + 1;
        document.getElementById('reporte_anio').value = d.getFullYear();
    }

    // ==========================================
    // MODAL DE DETALLE INDIVIDUAL
    // ==========================================
    function abrirModalDetalle(data) {
        if (!data) return;

        document.getElementById('det_NumAprobacion').innerText = data.NumAprobacion || '-';
        document.getElementById('det_CardName').innerText = data.CardName || '-';
        
        // Cédula formateada (de posOCRD.U_NIT)
        var cedula = (data.CedulaPaciente || '').trim();
        if (cedula && cedula.replace(/\D/g, '').length === 11) {
            var num = cedula.replace(/\D/g, '');
            cedula = num.substring(0, 3) + '-' + num.substring(3, 10) + '-' + num.substring(10);
        }
        document.getElementById('det_Cedula').innerText = cedula || 'No registrada';
        document.getElementById('det_CardCode').innerText = data.CardCode || '-';
        document.getElementById('det_NumExpediente').innerText = data.NumExpediente || '-';
        document.getElementById('det_NumAfiliado').innerText = data.NumAfiliado ? data.NumAfiliado : 'Sin carnet registrado';

        document.getElementById('det_NombreARS').innerText = data.NombreARS || 'Sin aseguradora';
        document.getElementById('det_CardCodeARS').innerText = data.CodigoARSReal || data.CardCode_ARS || '-';
        document.getElementById('det_TipoServicio').innerText = data.TipoServicio || 'CONSULTA MEDICA';

        // Factura Paciente / Vinculada
        var numFac = (data.NumFactura || '').trim();
        var wrapFac = document.getElementById('det_NumFacturaWrap');
        if (numFac.toUpperCase() === 'COBERTURA 100%') {
            wrapFac.innerHTML = '<span class="badge bg-success-subtle text-success border border-success font-monospace px-2 py-1"><i class="bi bi-shield-check me-1"></i>Cobertura Total (100%)</span>';
        } else if (numFac) {
            wrapFac.innerHTML = '<span class="badge bg-light text-dark border font-monospace px-2 py-1">' + numFac + '</span>';
        } else {
            wrapFac.innerHTML = '<span class="text-muted font-monospace small">Sin factura</span>';
        }

        // Factura Lote ARS
        var numFacARS = (data.NumFacturaARS || '').trim();
        var wrapFacARS = document.getElementById('det_NumFacturaARSWrap');
        if (numFacARS) {
            wrapFacARS.innerHTML = '<span class="badge bg-primary-subtle text-primary border font-monospace px-2 py-1"><i class="bi bi-receipt me-1"></i>' + numFacARS + '</span>';
        } else if (data.DocEntry_FacturaARS) {
            wrapFacARS.innerHTML = '<span class="badge bg-primary-subtle text-primary border font-monospace px-2 py-1">DocEntry #' + data.DocEntry_FacturaARS + '</span>';
        } else {
            wrapFacARS.innerHTML = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning font-monospace px-2 py-1">Pendiente por Facturar</span>';
        }

        var pct = parseFloat(data.PorcentajeCober || 0).toFixed(2);
        var montoAprob = parseFloat(data.MontoAprobado || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        var montoResp = parseFloat(data.MontoResponsable || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        document.getElementById('det_PorcentajeCober').innerText = pct + '%';
        document.getElementById('det_MontoAprobado').innerText = 'RD$ ' + montoAprob;
        document.getElementById('det_MontoResponsable').innerText = 'RD$ ' + montoResp;

        document.getElementById('det_FechaAprobacion').innerText = data.FechaAprobacionFormato || data.FechaAprobacionD || '-';
        document.getElementById('det_FechaVencimiento').innerText = data.FechaVencimientoFormato || data.FechaVencimientoD || 'No especificada';
        document.getElementById('det_Usuario').innerText = data.U_user || 'ADMIN';
        document.getElementById('det_AuditID').innerText = 'ID Aprobación: #' + (data.IdARS || '-');

        var badge = document.getElementById('det_BadgeStatus');
        var st = (data.Status || 'P').toUpperCase();
        if (st === 'F') {
            badge.className = 'badge bg-success';
            badge.innerText = 'Facturado a ARS';
        } else if (st === 'P') {
            badge.className = 'badge bg-warning text-dark';
            badge.innerText = 'Pendiente por Facturar';
        } else if (st === 'X') {
            badge.className = 'badge bg-danger';
            badge.innerText = 'Anulado';
        }

        new bootstrap.Modal(document.getElementById('modalDetalleAprobacion')).show();
    }

    // ==========================================
    // GESTIÓN DE COBRO ARS: FILTROS EN TIEMPO REAL
    // ==========================================
    function filtrarCobroARS() {
        var ars = $('#filtroARSCobro').val();
        var estado = $('#filtroEstadoCobro').val();
        var desde = $('#filtroDesdeCobro').val();
        var hasta = $('#filtroHastaCobro').val();

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (settings.nTable.id !== 'tablaCobroARS') return true;

            var node = tablaCobroARSDT.row(dataIndex).node();
            var rowARS = $(node).attr('data-arscode') || '';
            var rowEstado = $(node).attr('data-estado') || '';
            var rowFecha = $(node).attr('data-fecha') || '';

            if (ars !== 'TODAS' && rowARS !== ars) {
                return false;
            }

            if (estado !== 'TODOS') {
                if (estado === 'PENDIENTE' && rowEstado !== 'PENDIENTE') return false;
                if (estado === 'COBRADA' && rowEstado !== 'COBRADA') return false;
                if (estado === 'RECHAZADA' && rowEstado !== 'RECHAZADA') return false;
                if (estado === 'NOTA_CREDITO' && rowEstado !== 'NOTA_CREDITO') return false;
                if (estado === 'CANCELADA' && rowEstado !== 'CANCELADA') return false;
            }

            if (desde && rowFecha && rowFecha < desde) {
                return false;
            }

            if (hasta && rowFecha && rowFecha > hasta) {
                return false;
            }

            return true;
        });

        tablaCobroARSDT.draw();
        $.fn.dataTable.ext.search.pop();
        actualizarSeleccionCobro();
    }

    function limpiarFiltrosCobroARS() {
        $('#filtroARSCobro').val('TODAS');
        $('#filtroEstadoCobro').val('TODOS');
        $('#filtroDesdeCobro').val('');
        $('#filtroHastaCobro').val('');
        filtrarCobroARS();
    }

    // ==========================================
    // SELECCIÓN EN LOTE DE FACTURAS COBRO ARS
    // ==========================================
    function toggleSelectAllCobro(masterChk) {
        var isChecked = $(masterChk).is(':checked');
        $('#tablaCobroARS tbody tr:visible .check-fila-cobro').prop('checked', isChecked);
        actualizarSeleccionCobro();
    }

    function actualizarSeleccionCobro() {
        itemsSeleccionadosCobro = [];
        var totalMonto = 0;
        var aseguradorasDiferentes = new Set();
        var primerNombreARS = '';

        $('#tablaCobroARS tbody .check-fila-cobro:checked').each(function() {
            var docEntry = $(this).val();
            var numAtCard = $(this).attr('data-numatcard') || '';
            var ncf = $(this).attr('data-ncf') || '';
            var arsCode = $(this).attr('data-arscode') || '';
            var arsName = $(this).attr('data-arsname') || '';
            var monto = parseFloat($(this).attr('data-monto') || 0);
            var estado = $(this).attr('data-estado') || 'PENDIENTE';

            itemsSeleccionadosCobro.push({
                docEntry: docEntry,
                numAtCard: numAtCard,
                ncf: ncf,
                arsCode: arsCode,
                arsName: arsName,
                monto: monto,
                estado: estado
            });

            totalMonto += monto;
            aseguradorasDiferentes.add(arsCode);
            if (!primerNombreARS) primerNombreARS = arsName;
        });

        var cant = itemsSeleccionadosCobro.length;
        $('#lblCantCobroSeleccionadas').text(cant);
        $('#lblMontoCobroSeleccionado').text('RD$ ' + totalMonto.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

        if (cant > 0) {
            $('#lblARSCobroSeleccionadaWrap').show();
            if (aseguradorasDiferentes.size > 1) {
                $('#lblARSCobroSeleccionadaNombre').html('<span class="text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Múltiples ARS (' + aseguradorasDiferentes.size + ')</span>');
            } else {
                $('#lblARSCobroSeleccionadaNombre').text(primerNombreARS);
            }

            $('#btnMarcarCobradaARS').prop('disabled', false);
            $('#btnMarcarRechazadaARS').prop('disabled', false);

            // Botón de Nota de Crédito en Lote: SOLO activo si TODAS las facturas seleccionadas son RECHAZADAS
            var cantRechazadas = itemsSeleccionadosCobro.filter(function(x) { return x.estado === 'RECHAZADA'; }).length;
            if (cantRechazadas === cant && cant > 0) {
                $('#btnAplicarNCLoteARS').prop('disabled', false).attr('title', 'Emitir Nota de Crédito para ' + cant + ' factura(s) rechazada(s)');
            } else {
                $('#btnAplicarNCLoteARS').prop('disabled', true).attr('title', 'Solo se puede emitir Nota de Crédito a facturas que hayan sido marcadas como RECHAZADAS');
            }
        } else {
            $('#lblARSCobroSeleccionadaWrap').hide();
            $('#btnMarcarCobradaARS').prop('disabled', true);
            $('#btnMarcarRechazadaARS').prop('disabled', true);
            $('#btnAplicarNCLoteARS').prop('disabled', true).attr('title', 'Seleccione facturas rechazadas para emitir Nota de Crédito');
        }
    }

    // ==========================================
    // MODAL DE CONFIRMACIÓN DE COBRO
    // ==========================================
    function abrirModalConfirmarCobro() {
        if (itemsSeleccionadosCobro.length === 0) {
            alert('Debe seleccionar al menos una factura para marcar como cobrada.');
            return;
        }

        var totalMonto = 0;
        var nombresARS = new Set();
        itemsSeleccionadosCobro.forEach(function(x) {
            totalMonto += x.monto;
            nombresARS.add(x.arsName);
        });

        $('#cobro_modal_ars_nombre').text(Array.from(nombresARS).join(', '));
        $('#cobro_modal_cant').text(itemsSeleccionadosCobro.length + ' factura(s)');
        $('#cobro_modal_monto').text('RD$ ' + totalMonto.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#cobro_modal_referencia').val('');
        $('#cobro_modal_fecha').val(new Date().toISOString().substring(0, 10));
        $('#cobro_modal_observaciones').val('');

        $('#btnConfirmarCobroFinal').prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Confirmar Cobro ARS');
        new bootstrap.Modal(document.getElementById('modalConfirmarCobroARS')).show();
    }

    function ejecutarMarcarCobroARS() {
        if (itemsSeleccionadosCobro.length === 0) return;

        var ids = itemsSeleccionadosCobro.map(function(x) { return parseInt(x.docEntry); });
        var ref = $('#cobro_modal_referencia').val().trim();
        var fecha = $('#cobro_modal_fecha').val();
        var obs = $('#cobro_modal_observaciones').val().trim();

        var $btn = $('#btnConfirmarCobroFinal');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Guardando Cobro...');

        $.ajax({
            url: 'index.php?action=marcar_cobro_ars',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                idsFacturas: ids,
                referenciaPago: ref,
                fechaPago: fecha,
                observaciones: obs
            }),
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    alert(resp.msg || 'Facturas marcadas como cobradas exitosamente.');
                    location.reload();
                } else {
                    alert('Error: ' + (resp.msg || 'No se pudo procesar la acción.'));
                    $btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Confirmar Cobro ARS');
                }
            },
            error: function(xhr) {
                var msg = 'Error de comunicación con el servidor.';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e){}
                alert('Error al registrar cobro: ' + msg);
                $btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Confirmar Cobro ARS');
            }
        });
    }

    // ==========================================
    // MODAL DE CONFIRMACIÓN DE RECHAZO
    // ==========================================
    function abrirModalConfirmarRechazo() {
        if (itemsSeleccionadosCobro.length === 0) {
            alert('Debe seleccionar al menos una factura para marcar como rechazada.');
            return;
        }

        var totalMonto = 0;
        var nombresARS = new Set();
        itemsSeleccionadosCobro.forEach(function(x) {
            totalMonto += x.monto;
            nombresARS.add(x.arsName);
        });

        $('#rechazo_modal_ars_nombre').text(Array.from(nombresARS).join(', '));
        $('#rechazo_modal_cant').text(itemsSeleccionadosCobro.length + ' factura(s)');
        $('#rechazo_modal_monto').text('RD$ ' + totalMonto.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('#rechazo_modal_motivo_select').val('');
        $('#rechazo_modal_motivo').val('');
        $('#rechazo_modal_observaciones').val('');

        $('#btnConfirmarRechazoFinal').prop('disabled', false).html('<i class="bi bi-x-octagon me-1"></i> Confirmar Rechazo ARS');
        new bootstrap.Modal(document.getElementById('modalConfirmarRechazoARS')).show();
    }

    function cambiarMotivoRechazoRapido(val) {
        if (val && val !== 'OTRO') {
            $('#rechazo_modal_motivo').val(val);
        } else if (val === 'OTRO') {
            $('#rechazo_modal_motivo').val('').focus();
        }
    }

    function ejecutarMarcarRechazoARS() {
        if (itemsSeleccionadosCobro.length === 0) return;

        var motivo = $('#rechazo_modal_motivo').val().trim();
        if (!motivo) {
            alert('Debe indicar el motivo del rechazo o glosa de la Aseguradora.');
            $('#rechazo_modal_motivo').focus();
            return;
        }

        var ids = itemsSeleccionadosCobro.map(function(x) { return parseInt(x.docEntry); });
        var obs = $('#rechazo_modal_observaciones').val().trim();
        var irANC = $('#chkIrANotaCreditoInmediata').is(':checked');

        var $btn = $('#btnConfirmarRechazoFinal');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Guardando Rechazo...');

        $.ajax({
            url: 'index.php?action=marcar_rechazo_ars',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                idsFacturas: ids,
                motivo: motivo,
                observaciones: obs
            }),
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    if (irANC) {
                        // Proceder directamente a la Nota de Crédito en lote
                        window.location.href = 'index.php?page=nota_credito_ncf&modo=lote_ars&facturas=' + ids.join(',');
                    } else {
                        alert(resp.msg || 'Facturas marcadas como rechazadas.');
                        location.reload();
                    }
                } else {
                    alert('Error: ' + (resp.msg || 'No se pudo procesar el rechazo.'));
                    $btn.prop('disabled', false).html('<i class="bi bi-x-octagon me-1"></i> Confirmar Rechazo ARS');
                }
            },
            error: function(xhr) {
                var msg = 'Error de comunicación con el servidor.';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e){}
                alert('Error al registrar rechazo: ' + msg);
                $btn.prop('disabled', false).html('<i class="bi bi-x-octagon me-1"></i> Confirmar Rechazo ARS');
            }
        });
    }

    // ==========================================
    // NOTA DE CRÉDITO EN LOTE & VALIDACIÓN DE MISMA ARS
    // ==========================================
    // NOTA DE CRÉDITO EN LOTE & AGRUPACIÓN FISCAL (ARS + TIPO NCF)
    // ==========================================
    function prepararNotaCreditoLoteARS() {
        if (itemsSeleccionadosCobro.length === 0) {
            alert('Debe seleccionar al menos una factura rechazada para aplicar la Nota de Crédito.');
            return;
        }

        // Validar que TODAS las facturas seleccionadas estén en estado RECHAZADA
        var noRechazadas = itemsSeleccionadosCobro.filter(function(x) { return x.estado !== 'RECHAZADA'; });
        if (noRechazadas.length > 0) {
            alert('La emisión de Nota de Crédito solo está permitida para facturas con estado RECHAZADA / GLOSADA.\n\nSe han detectado ' + noRechazadas.length + ' factura(s) que aún no han sido marcadas como rechazadas. Por favor selecciónelas únicamente después de registrar el rechazo correspondiente.');
            return;
        }

        // Agrupar por ARS y por Tipo de Comprobante Fiscal (DGII: E31 -> E34 vs B01 -> B04)
        var gruposValidos = {};
        itemsSeleccionadosCobro.forEach(function(item) {
            var ncfVal = (item.ncf || item.numAtCard || '').trim().toUpperCase();
            var esElectronico = (ncfVal.startsWith('E') || ncfVal.startsWith('e'));
            var tipoNCF = esElectronico ? 'E34' : 'B04';
            var tipoNCFNombre = esElectronico ? 'Electrónica (e-CF E31 → NC E34)' : 'Tradicional / Impresa (B01 → NC B04)';
            var groupKey = item.arsCode + '||' + tipoNCF;

            if (!gruposValidos[groupKey]) {
                gruposValidos[groupKey] = {
                    arsCode: item.arsCode,
                    arsName: item.arsName,
                    tipoNCF: tipoNCF,
                    tipoNCFNombre: tipoNCFNombre,
                    esElectronico: esElectronico,
                    items: []
                };
            }
            gruposValidos[groupKey].items.push(item);
        });

        var llavesGrupos = Object.keys(gruposValidos);

        // Si hay más de 1 grupo (diferente ARS o mezcla de E31 con B01), mostrar modal de agrupación
        if (llavesGrupos.length > 1) {
            var htmlLista = '';
            llavesGrupos.forEach(function(key, idx) {
                var g = gruposValidos[key];
                var totalMontoGrupo = g.items.reduce(function(acc, it) { return acc + (parseFloat(it.monto) || 0); }, 0);
                var idsGrupo = g.items.map(function(it) { return it.docEntry; });
                var ncfList = g.items.map(function(it) { return it.ncf || it.numAtCard; }).join(', ');

                htmlLista += '<div class="p-3 bg-white rounded-3 border shadow-sm mb-2">' +
                    '<div class="d-flex justify-content-between align-items-start gap-2 mb-2">' +
                        '<div>' +
                            '<div class="fw-bold text-dark fs-6">' +
                                '<i class="bi bi-shield-check text-primary me-1"></i>' + g.arsName +
                                ' <span class="text-muted small">(' + g.arsCode + ')</span>' +
                            '</div>' +
                            '<div class="mt-1">' +
                                '<span class="badge ' + (g.esElectronico ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle') + ' me-2">' +
                                    '<i class="bi ' + (g.esElectronico ? 'bi-lightning-charge-fill' : 'bi-receipt') + ' me-1"></i>' + g.tipoNCFNombre +
                                '</span>' +
                                '<span class="badge bg-light text-dark border">' + g.items.length + ' factura(s)</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="text-end">' +
                            '<span class="text-muted small text-uppercase d-block" style="font-size:0.7rem;">Monto Grupo:</span>' +
                            '<strong class="text-danger font-monospace fs-6">RD$ ' + totalMontoGrupo.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</strong>' +
                        '</div>' +
                    '</div>' +
                    '<div class="text-muted small font-monospace mb-3" style="font-size: 0.74rem;">' +
                        'Facturas: ' + ncfList +
                    '</div>' +
                    '<div class="text-end">' +
                        '<button type="button" class="btn btn-sm btn-primary fw-bold rounded-2 px-3 py-1" onclick="procesarGrupoEspecificoNC([\'' + idsGrupo.join("','") + '\'])">' +
                            '<i class="bi bi-arrow-right-circle me-1"></i> Procesar este grupo (' + g.items.length + ' fact.)' +
                        '</button>' +
                    '</div>' +
                '</div>';
            });

            $('#listaIncompatibilidadARS').html(htmlLista);
            new bootstrap.Modal(document.getElementById('modalAlertaIncompatibilidadNC')).show();
            return;
        }

        // Grupo único compatible: proceder directamente
        var ids = itemsSeleccionadosCobro.map(function(x) { return x.docEntry; });
        window.location.href = 'index.php?page=nota_credito_ncf&modo=lote_ars&facturas=' + ids.join(',');
    }

    function procesarGrupoEspecificoNC(idsArray) {
        if (!idsArray || idsArray.length === 0) return;
        var modalEl = document.getElementById('modalAlertaIncompatibilidadNC');
        var modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();

        window.location.href = 'index.php?page=nota_credito_ncf&modo=lote_ars&facturas=' + idsArray.join(',');
    }

    function aplicarNCIndividual(docEntry, arsCode, arsName, ncf, monto) {
        window.location.href = 'index.php?page=nota_credito_ncf&modo=lote_ars&facturas=' + docEntry;
    }

    // ==========================================
    // CONSULTA DE TRAZABILIDAD & LÍNEA DE TIEMPO
    // ==========================================
    function consultarTrazabilidadFactura(docEntry) {
        if (!docEntry) return;

        $('#traz_ncf').text('Consultando...');
        $('#traz_numatcard').text('');
        $('#traz_ars_nombre').text('Cargando datos...');
        $('#traz_ars_code').text('');
        $('#traz_monto').text('RD$ 0.00');
        $('#traz_badge_estado').text('-').attr('class', 'badge bg-secondary');
        $('#timelineEventosARS').html('<div class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Cargando línea de tiempo...</div>');
        $('#tbodyTrazAprobaciones').html('<tr><td colspan="5" class="text-center text-muted py-3">Consultando autorizaciones...</td></tr>');
        $('#traz_cant_aprobaciones').text('0');

        new bootstrap.Modal(document.getElementById('modalTrazabilidadARS')).show();

        $.ajax({
            url: 'index.php?action=consultar_trazabilidad_ars&docEntry=' + docEntry,
            type: 'GET',
            dataType: 'json',
            success: function(resp) {
                if (resp.success) {
                    var f = resp.factura;
                    $('#traz_ncf').text(f.U_FACNUM || f.NumAtCard || ('DocEntry: ' + f.C_DocEntry));
                    $('#traz_numatcard').text('Interno: ' + f.NumAtCard);
                    $('#traz_ars_nombre').text(f.CardName);
                    $('#traz_ars_code').text('Código ARS: ' + f.CardCode);
                    $('#traz_monto').text('RD$ ' + parseFloat(f.DocTotal || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                    var st = (f.CANCELED === 'Y') ? 'CANCELADA' : (resp.eventos.length > 0 ? resp.eventos[resp.eventos.length - 1].Estado : 'PENDIENTE');
                    var badgeClass = 'bg-info';
                    if (st === 'COBRADA') badgeClass = 'bg-success';
                    else if (st === 'RECHAZADA') badgeClass = 'bg-danger';
                    else if (st === 'NOTA_CREDITO') badgeClass = 'bg-purple text-white';
                    else if (st === 'CANCELADA') badgeClass = 'bg-danger text-white';
                    $('#traz_badge_estado').text(st).attr('class', 'badge ' + badgeClass);

                    // Construir Timeline
                    var timelineHtml = '';

                    // Evento 0: Creación / Emisión Factura Lote ARS
                    timelineHtml += '<div class="timeline-item-ars">' +
                        '<div class="timeline-dot-ars"></div>' +
                        '<div class="timeline-header-ars">' +
                            '<span class="timeline-title-ars"><i class="bi bi-file-earmark-plus text-primary me-1"></i> Factura de Lote ARS Emitida</span>' +
                            '<span class="timeline-date-ars">' + (f.FechaDoc || '-') + '</span>' +
                        '</div>' +
                        '<div class="timeline-body-ars">Factura emitida por <strong>RD$ ' + parseFloat(f.DocTotal).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</strong> (NCF: ' + (f.U_FACNUM || f.NumAtCard) + ').</div>' +
                    '</div>';

                    // Eventos de trazabilidad_reclamacion_ars
                    if (resp.eventos && resp.eventos.length > 0) {
                        resp.eventos.forEach(function(ev) {
                            var dotClass = 'timeline-dot-ars';
                            var titleIcon = 'bi-clock-history';
                            var titleText = ev.Estado;

                            if (ev.Estado === 'COBRADA') {
                                dotClass = 'timeline-dot-ars timeline-dot-green';
                                titleIcon = 'bi-check2-circle text-success';
                                titleText = 'Factura Cobrada / Liquidada por ARS';
                            } else if (ev.Estado === 'RECHAZADA') {
                                dotClass = 'timeline-dot-ars timeline-dot-red';
                                titleIcon = 'bi-x-octagon text-danger';
                                titleText = 'Reclamación Rechazada / Glosada por ARS';
                            } else if (ev.Estado === 'NOTA_CREDITO') {
                                dotClass = 'timeline-dot-ars timeline-dot-purple';
                                titleIcon = 'bi-file-earmark-minus text-purple';
                                titleText = 'Nota de Crédito Electrónica Emitida';
                            }

                            timelineHtml += '<div class="timeline-item-ars">' +
                                '<div class="' + dotClass + '"></div>' +
                                '<div class="timeline-header-ars">' +
                                    '<span class="timeline-title-ars"><i class="bi ' + titleIcon + ' me-1"></i> ' + titleText + '</span>' +
                                    '<span class="timeline-date-ars">' + (ev.FechaEstadoFmt || '-') + '</span>' +
                                '</div>' +
                                '<div class="timeline-body-ars">' +
                                    (ev.Motivo ? '<div class="fw-semibold">Motivo: ' + ev.Motivo + '</div>' : '') +
                                    (ev.ReferenciaPago ? '<div class="text-success"><i class="bi bi-receipt me-1"></i> Ref. Pago: ' + ev.ReferenciaPago + '</div>' : '') +
                                    (ev.NCF_NC ? '<div class="text-purple fw-bold font-monospace"><i class="bi bi-file-earmark-minus me-1"></i> NCF NC: ' + ev.NCF_NC + '</div>' : '') +
                                    (ev.Observaciones ? '<div class="text-muted small mt-1">' + ev.Observaciones + '</div>' : '') +
                                    '<div class="timeline-meta-ars"><i class="bi bi-person me-1"></i> Registrado por: <strong>' + (ev.Usuario || 'SYS') + '</strong></div>' +
                                '</div>' +
                            '</div>';
                        });
                    }

                    $('#timelineEventosARS').html(timelineHtml);

                    // Aprobaciones
                    var aps = resp.aprobaciones || [];
                    $('#traz_cant_aprobaciones').text(aps.length + ' paciente(s)');
                    if (aps.length > 0) {
                        var tbodyAps = '';
                        aps.forEach(function(a) {
                            tbodyAps += '<tr>' +
                                '<td class="font-monospace fw-bold">' + (a.NumAprobacion || '-') + '</td>' +
                                '<td>' + (a.CardName || '-') + '</td>' +
                                '<td class="font-monospace">' + (a.NumAfiliado || '-') + '</td>' +
                                '<td><span class="badge bg-light text-dark border">' + (a.TipoServicio || 'CONSULTA MEDICA') + '</span></td>' +
                                '<td class="text-end font-monospace fw-bold text-success">RD$ ' + parseFloat(a.MontoAprobado || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                            '</tr>';
                        });
                        $('#tbodyTrazAprobaciones').html(tbodyAps);
                    } else {
                        $('#tbodyTrazAprobaciones').html('<tr><td colspan="5" class="text-center text-muted py-3">No hay autorizaciones detalladas vinculadas directamente a este registro.</td></tr>');
                    }
                } else {
                    $('#timelineEventosARS').html('<div class="alert alert-danger mb-0">' + (resp.msg || 'No se pudo obtener la trazabilidad.') + '</div>');
                }
            },
            error: function(xhr) {
                var msg = 'Error al consultar el servidor.';
                try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e){}
                $('#timelineEventosARS').html('<div class="alert alert-danger mb-0">' + msg + '</div>');
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>