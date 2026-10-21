<?php require_once __DIR__ . '/../Estructura/header.php'; ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="views/Facturacion/styles/consulta_facturas.css">

<div class="container-fluid consulta-facturas-container">
    
    <!-- ============================================================
         1. HEADER DE PÁGINA
         ============================================================ -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <h2 class="page-title">Historial y Consulta de Facturas</h2>
                <p class="page-subtitle">Auditoría central de ventas, desglose de pacientes asegurados y privados, y trazabilidad de comprobantes fiscales</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?page=facturacion" class="btn-header-action btn-header-success">
                <i class="bi bi-plus-circle"></i> Nueva Factura
            </a>
            <a href="index.php?page=consulta_aprobaciones" class="btn-header-action btn-header-primary">
                <i class="bi bi-shield-check"></i> Aprobaciones ARS
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
         3. TABLA PRINCIPAL DE HISTORIAL & FILTROS
         ============================================================ -->
    <div class="consulta-card">
        
        <!-- Barra de Filtros Multifuncional -->
        <div class="consulta-filter-bar">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-list-check text-primary fs-5"></i>
                <span class="fw-bold text-dark">Historial General de Facturación</span>
            </div>

            <div class="filter-controls-group">
                <!-- Rango Fecha Desde -->
                <div class="filter-date-group">
                    <span class="filter-date-label"><i class="bi bi-calendar-event me-1"></i>Desde:</span>
                    <input type="date" id="filtroDesde" class="filter-date-input" onchange="filtrarFacturas()">
                </div>

                <!-- Rango Fecha Hasta -->
                <div class="filter-date-group">
                    <span class="filter-date-label"><i class="bi bi-calendar-event me-1"></i>Hasta:</span>
                    <input type="date" id="filtroHasta" class="filter-date-input" onchange="filtrarFacturas()">
                </div>

                <!-- Tipo Paciente -->
                <div class="filter-select-group">
                    <span class="filter-select-label"><i class="bi bi-person me-1"></i>Tipo:</span>
                    <select id="filtroTipo" class="filter-select-input" onchange="filtrarFacturas()">
                        <option value="">Todos</option>
                        <option value="ARS">Asegurados (ARS)</option>
                        <option value="PRIVADO">Privados</option>
                    </select>
                </div>

                <!-- Condición de Pago -->
                <div class="filter-select-group">
                    <span class="filter-select-label"><i class="bi bi-credit-card me-1"></i>Pago:</span>
                    <select id="filtroCondicion" class="filter-select-input" onchange="filtrarFacturas()">
                        <option value="">Todas</option>
                        <option value="CONTADO">Contado</option>
                        <option value="CREDITO">Crédito</option>
                    </select>
                </div>

                <!-- Estado -->
                <div class="filter-select-group">
                    <span class="filter-select-label"><i class="bi bi-toggle-on me-1"></i>Estado:</span>
                    <select id="filtroEstado" class="filter-select-input" onchange="filtrarFacturas()">
                        <option value="">Todos</option>
                        <option value="VIGENTE">Vigente</option>
                        <option value="AFECTADA">NC Parcial</option>
                        <option value="MODIFICADA">Modificada (ND)</option>
                        <option value="ANULADA">Anulada</option>
                    </select>
                </div>

                <!-- Serie NCF -->
                <div class="filter-select-group">
                    <span class="filter-select-label"><i class="bi bi-file-earmark-text me-1"></i>NCF:</span>
                    <select id="filtroSerie" class="filter-select-input" onchange="filtrarFacturas()">
                        <option value="">Todas</option>
                        <?php 
                        $nombresAmigablesNCF = [
                            'E31' => 'E31',
                            'E32' => 'E32',
                            'E44' => 'E44',
                            'E45' => 'E45',
                            'E46' => 'E46',
                            'B01' => 'B01',
                            'B02' => 'B02',
                            'B14' => 'B14',
                            'B15' => 'B15',
                            'B16' => 'B16'
                        ];
                        $seriesExcluidas = ['E33', 'B03', 'E34', 'B04', 'E41', 'B11', 'E42', 'B12', 'E43', 'B13', 'E47', 'B17'];

                        foreach ($seriesNCF as $sn): 
                            $codSerie = strtoupper(trim($sn['serie'] ?? ''));
                            if (empty($codSerie)) continue;
                            // Excluir notas de débito, crédito y comprobantes de compras/gastos que no son facturas
                            if (in_array($codSerie, $seriesExcluidas) || str_ends_with($codSerie, '33') || str_ends_with($codSerie, '34') || str_ends_with($codSerie, '03') || str_ends_with($codSerie, '04')) {
                                continue;
                            }
                            $textoOpcion = $nombresAmigablesNCF[$codSerie] ?? ($codSerie . (!empty($sn['descripcion']) ? ' - ' . ucwords(strtolower($sn['descripcion'])) : ''));
                        ?>
                            <option value="<?php echo htmlspecialchars($codSerie); ?>"><?php echo htmlspecialchars($textoOpcion); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="button" onclick="limpiarFiltros()" class="btn-filter-clear">
                    <i class="bi bi-x-circle"></i> Limpiar
                </button>
            </div>
        </div>

        <div class="consulta-card-body">
            <table id="tablaFacturas" class="table table-hover align-middle table-sm" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 10%;"># Factura</th>
                        <th style="width: 10%;">Fecha</th>
                        <th style="width: 14%;">NCF / Comprobante</th>
                        <th style="width: 22%;">Paciente / Cliente</th>
                        <th style="width: 13%;">Tipo / ARS</th>
                        <th style="width: 9%;">Condición</th>
                        <th style="width: 12%;" class="text-end" title="Total neto facturado al paciente / Copago">Total Facturado</th>
                        <th style="width: 10%;" class="text-center">Estado</th>
                        <th style="width: 10%;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($facturas as $f): 
                        $docEntry = (int)$f['C_DocEntry'];
                        $fechaFormat = !empty($f['DocDate']) ? date('d/m/Y', strtotime($f['DocDate'])) : '-';
                        $fechaIso = !empty($f['DocDate']) ? substr($f['DocDate'], 0, 10) : '';
                        $ncf = !empty($f['U_FACNUM']) ? trim($f['U_FACNUM']) : trim($f['NumAtCard'] ?? '');
                        $serie = strtoupper(trim($f['U_FacSerie'] ?? substr($ncf, 0, 3)));
                        
                        $montoNC = (float)($f['MontoNotaCredito'] ?? 0);
                        $docTotalVal = (float)($f['DocTotal'] ?? 0);
                        $esAnuladaTotal = ($f['CANCELED'] === 'Y' || (!empty($f['IdNotaCredito']) && $montoNC >= $docTotalVal - 0.01));
                        $esAfectadaNC = (!$esAnuladaTotal && !empty($f['IdNotaCredito']) && $montoNC > 0);
                        $esAnulada = $esAnuladaTotal;
                        $tieneND = (!empty($f['IdNotaDebito']) || !empty($f['NCF_NotaDebito']));
                        $tieneARS = (!empty($f['NumAprobacion']) || !empty($f['CardCode_ARS']) || !empty($f['SubGrupoARS']));
                        $esCredito = ((int)($f['ExtraDays'] ?? 0) > 1);
                        
                        $estadoFiltro = $esAnuladaTotal ? 'ANULADA' : ($esAfectadaNC ? 'AFECTADA' : ($tieneND ? 'MODIFICADA' : 'VIGENTE'));

                        // Clase de badge de serie fiscal
                        $badgeSerieClass = 'badge-serie-other';
                        if (stripos($serie, 'E31') !== false) $badgeSerieClass = 'badge-serie-e31';
                        elseif (stripos($serie, 'E32') !== false) $badgeSerieClass = 'badge-serie-e32';
                        elseif (stripos($serie, 'E44') !== false) $badgeSerieClass = 'badge-serie-e44';
                        elseif (stripos($serie, 'E45') !== false) $badgeSerieClass = 'badge-serie-e45';
                        elseif (stripos($serie, 'B01') !== false) $badgeSerieClass = 'badge-serie-b01';
                        elseif (stripos($serie, 'B02') !== false) $badgeSerieClass = 'badge-serie-b02';

                    ?>
                    <tr data-fecha="<?php echo $fechaIso; ?>"
                        data-tipo="<?php echo $tieneARS ? 'ARS' : 'PRIVADO'; ?>"
                        data-condicion="<?php echo $esCredito ? 'CREDITO' : 'CONTADO'; ?>"
                        data-estado="<?php echo $estadoFiltro; ?>"
                        data-serie="<?php echo $serie; ?>">
                        
                        <!-- 1. # Factura -->
                        <td>
                            <span class="font-monospace fw-bold text-dark">
                                <?php echo htmlspecialchars($f['NumAtCard'] ?? ('F-' . $docEntry)); ?>
                            </span>
                        </td>

                        <!-- 2. Fecha -->
                        <td class="font-monospace text-secondary">
                            <?php echo $fechaFormat; ?>
                        </td>

                        <!-- 3. NCF -->
                        <td>
                            <div class="ncf-cell-wrap">
                                <span class="ncf-code-text"><?php echo htmlspecialchars($ncf); ?></span>
                                <?php if (!empty($serie)): ?>
                                    <span class="badge-ncf-serie <?php echo $badgeSerieClass; ?>">
                                        <?php echo htmlspecialchars($serie); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <!-- 4. Paciente -->
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark"><?php echo htmlspecialchars($f['CardName']); ?></span>
                                <div class="d-flex gap-2 text-muted" style="font-size: 0.74rem;">
                                    <?php if (!empty($f['PacienteNIT'])): ?>
                                        <span><i class="bi bi-card-heading me-1"></i><?php echo htmlspecialchars($f['PacienteNIT']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($f['NumExpediente'])): ?>
                                        <span><i class="bi bi-folder2 me-1"></i>Exp: <?php echo htmlspecialchars($f['NumExpediente']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>

                        <!-- 5. Tipo / ARS -->
                        <td>
                            <?php if ($tieneARS): ?>
                                <span class="badge-paciente-tipo badge-paciente-ars" title="<?php echo htmlspecialchars($f['NombreARS'] ?? 'Asegurado'); ?>">
                                    <i class="bi bi-shield-check"></i>
                                    <span class="text-truncate" style="max-width: 120px;">
                                        <?php echo htmlspecialchars($f['NombreARS'] ?? 'ARS'); ?>
                                    </span>
                                </span>
                            <?php else: ?>
                                <span class="badge-paciente-tipo badge-paciente-privado">
                                    <i class="bi bi-person"></i> Privado
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- 6. Condición -->
                        <td>
                            <?php if ($esCredito): ?>
                                <span class="badge-condicion badge-credito">
                                    <i class="bi bi-calendar-event me-1"></i>Crédito
                                </span>
                            <?php else: ?>
                                <span class="badge-condicion badge-contado">
                                    <i class="bi bi-check2-circle me-1"></i>Contado
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- 7. Total Factura / Copago -->
                        <td class="text-end">
                            <?php 
                            $copagoVal = (float)($f['CopagoPaciente'] ?? 0);
                            ?>
                            <span class="font-monospace fw-bold <?php echo $esAnuladaTotal ? 'text-decoration-line-through text-muted' : 'text-dark'; ?>">
                                RD$ <?php echo number_format($docTotalVal, 2); ?>
                            </span>
                            <?php if ($esAfectadaNC): ?>
                                <small class="d-block font-monospace fw-semibold" style="font-size: 0.70rem; color: #b45309;" title="Afectada con Nota de Crédito parcial">
                                    <i class="bi bi-arrow-return-left me-1"></i>NC: -RD$ <?php echo number_format($montoNC, 2); ?>
                                </small>
                            <?php endif; ?>
                            <?php if ($tieneARS): ?>
                                <?php if ((float)($f['CoberturaARS'] ?? 0) > 0): ?>
                                    <small class="d-block font-monospace fw-semibold" style="font-size: 0.70rem; color: #7e22ce;" title="Monto cubierto por ARS">
                                        <i class="bi bi-shield-check me-1"></i>ARS: RD$ <?php echo number_format((float)$f['CoberturaARS'], 2); ?>
                                    </small>
                                <?php elseif ($copagoVal > 0): ?>
                                    <small class="d-block text-muted font-monospace" style="font-size: 0.70rem;">
                                        <?php echo (abs($copagoVal - $docTotalVal) > 0.01) ? ('Copago: RD$ ' . number_format($copagoVal, 2)) : 'Copago ARS'; ?>
                                    </small>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>

                        <!-- 8. Estado -->
                        <td class="text-center">
                            <?php if ($esAnuladaTotal): ?>
                                <span class="badge-estado-fac badge-fac-anulada" title="Anulada totalmente con Nota de Crédito <?php echo htmlspecialchars($f['NumNotaCredito'] ?? ''); ?>">
                                    <i class="bi bi-x-circle-fill"></i> Anulada
                                </span>
                            <?php elseif ($esAfectadaNC): ?>
                                <span class="badge-estado-fac badge-fac-modificada" style="background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a;" title="Afectada con Nota de Crédito parcial <?php echo htmlspecialchars($f['NumNotaCredito'] ?? ''); ?> (RD$ <?php echo number_format($montoNC, 2); ?>)">
                                    <i class="bi bi-arrow-return-left"></i> NC Parcial
                                </span>
                            <?php elseif ($tieneND): ?>
                                <span class="badge-estado-fac badge-fac-modificada" title="Modificada con Nota de Débito <?php echo htmlspecialchars($f['NCF_NotaDebito'] ?? ''); ?> (RD$ <?php echo number_format((float)($f['MontoNotaDebito'] ?? 0), 2); ?>)">
                                    <i class="bi bi-file-earmark-plus-fill"></i> Modificada (ND)
                                </span>
                            <?php else: ?>
                                <span class="badge-estado-fac badge-fac-vigente">
                                    <i class="bi bi-check-circle-fill"></i> Vigente
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- 9. Acciones -->
                        <td class="text-center">
                            <?php
                            $ecfRecordId = !empty($f['EcfIdRecord']) ? (int)$f['EcfIdRecord'] : 0;
                            $printUrl = "xmlceyes/factura_supermercado.php?id=" . urlencode($ecfRecordId);
                            $ndNcfOrigen = !empty($f['U_FACNUM']) ? trim($f['U_FACNUM']) : trim($f['NumAtCard'] ?? '');
                            $cardNameEsc = htmlspecialchars($f['CardName'] ?? '', ENT_QUOTES);
                            $pacNitEsc = htmlspecialchars($f['PacienteNIT'] ?? ($f['U_FacNit'] ?? ''), ENT_QUOTES);
                            $numAtCardEsc = htmlspecialchars($f['NumAtCard'] ?? ('F-' . $docEntry), ENT_QUOTES);
                            $totalMontoNum = (float)($f['DocTotal'] ?? 0);
                            ?>
                            <div class="d-inline-flex align-items-center gap-1">
                                <button type="button" 
                                        class="btn-action-icon btn-action-view" 
                                        onclick="verDetalleFactura(<?php echo $docEntry; ?>)"
                                        title="Ver detalle completo de factura">
                                    <i class="bi bi-eye"></i>
                                </button>
                                
                                <?php if (!$esAnulada): ?>
                                    <button type="button" 
                                            class="btn-action-icon btn-action-nd" 
                                            onclick="abrirModalEmitirND(<?php echo $docEntry; ?>, '<?php echo $numAtCardEsc; ?>', '<?php echo $ndNcfOrigen; ?>', '<?php echo $cardNameEsc; ?>', '<?php echo $pacNitEsc; ?>', <?php echo $totalMontoNum; ?>, '<?php echo $serie; ?>')"
                                            title="Emitir Nota de Débito (ND) para esta Factura">
                                        <i class="bi bi-file-earmark-plus"></i>
                                    </button>
                                <?php endif; ?>

                                <?php if ($ecfRecordId > 0): ?>
                                    <a href="<?php echo $printUrl; ?>" 
                                       target="_blank" 
                                       class="btn-action-icon btn-action-print" 
                                       title="Imprimir Factura Sellada / e-CF DGII">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                <?php else: ?>
                                    <button type="button" 
                                            class="btn-action-icon btn-action-print" 
                                            style="opacity: 0.4; cursor: not-allowed;" 
                                            title="Factura pendiente de sellado e-CF" 
                                            disabled>
                                        <i class="bi bi-printer"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================
     4. MODAL DE DETALLE COMPLETO DE FACTURA
     ============================================================ -->
<div class="modal fade" id="modalDetalleFactura" tabindex="-1" aria-labelledby="modalDetalleFacturaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.18);">
            
            <div class="modal-header modal-factura-header">
                <div>
                    <h5 class="modal-factura-title" id="modalDetalleFacturaLabel">
                        <i class="bi bi-receipt-cutoff text-info"></i> Detalle de Factura <span id="modalFac_Numero" class="font-monospace text-warning ms-1">#0000</span>
                    </h5>
                    <span id="modalFac_SubHeader" class="text-white-50" style="font-size: 0.8rem;">Comprobante Fiscal e Información de Emisión</span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Spinner de Carga -->
                <div id="modalLoadingSpinner" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="text-muted mt-2 fw-semibold">Consultando información detallada...</p>
                </div>

                <!-- Contenido Dinámico de Factura -->
                <div id="modalFacturaContent" style="display: none;">
                    
                    <!-- Banner de Alerta si la Factura fue Anulada con Nota de Crédito -->
                    <div id="modalBannerNC" class="modal-banner-nc" style="display: none;">
                        <i class="bi bi-exclamation-triangle-fill fs-4 flex-shrink-0"></i>
                        <div>
                            <span class="fw-bold">Factura Anulada / Afectada por Nota de Crédito:</span>
                            <span id="modalNC_TextoInfo" class="ms-1 font-monospace">NC #...</span>
                        </div>
                    </div>

                    <!-- Banner de Información si la Factura fue Modificada por Nota de Débito -->
                    <div id="modalBannerND" class="modal-banner-nd" style="display: none;">
                        <div class="d-flex align-items-center gap-2 flex-grow-1">
                            <i class="bi bi-file-earmark-plus-fill fs-4 flex-shrink-0 text-purple"></i>
                            <div>
                                <span class="fw-bold text-purple">Factura Modificada por Nota de Débito:</span>
                                <span id="modalND_TextoInfo" class="ms-1 font-monospace text-dark">ND #...</span>
                            </div>
                        </div>
                        <div>
                            <a href="#" id="modalND_BtnPrintBanner" target="_blank" class="btn btn-sm text-white fw-bold px-3 py-1 d-inline-flex align-items-center gap-1 shadow-sm" style="background: #4338ca; border-radius: 8px; font-size: 0.82rem;">
                                <i class="bi bi-printer-fill"></i> Imprimir ND (PDF)
                            </a>
                        </div>
                    </div>

                    <!-- Banner de Información de ARS si aplica -->
                    <div id="modalBannerARS" class="modal-banner-ars" style="display: none;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-fill-check fs-4 text-purple"></i>
                            <div>
                                <span class="fw-bold text-purple" id="modalARS_Nombre">ARS UNIVERSAL</span>
                                <div class="text-muted" style="font-size: 0.78rem;">
                                    <span>Aut: <strong id="modalARS_Aut" class="font-monospace text-dark">000000</strong></span> | 
                                    <span>Afiliado: <strong id="modalARS_Afiliado" class="font-monospace text-dark">000-00000-0</strong></span> | 
                                    <span>Servicio: <strong id="modalARS_Servicio" class="text-dark">Consulta</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cuadrícula de Información General -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <div class="modal-info-card">
                                <span class="modal-info-label">Paciente / Cliente</span>
                                <div class="modal-info-val text-truncate" id="modalFac_Cliente">-</div>
                                <small class="text-muted font-monospace" id="modalFac_Nit">-</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="modal-info-card">
                                <span class="modal-info-label">Comprobante Fiscal (NCF)</span>
                                <div class="modal-info-val font-monospace text-primary" id="modalFac_NCF">-</div>
                                <small class="text-muted" id="modalFac_SerieVence">-</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="modal-info-card">
                                <span class="modal-info-label">Fecha y Condición</span>
                                <div class="modal-info-val" id="modalFac_Fecha">-</div>
                                <small class="text-muted fw-bold" id="modalFac_Condicion">-</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="modal-info-card">
                                <span class="modal-info-label">Cajero / Usuario</span>
                                <div class="modal-info-val" id="modalFac_Usuario">-</div>
                                <small class="text-muted" id="modalFac_Estado">-</small>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Artículos / Servicios (POSINV1) -->
                    <div class="table-responsive mb-4" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                        <table class="table table-hover align-middle mb-0 modal-table-lines">
                            <thead>
                                <tr>
                                    <th style="width: 4%;">#</th>
                                    <th style="width: 10%;">Código</th>
                                    <th style="width: 27%;">Descripción del Artículo / Servicio</th>
                                    <th style="width: 6%;" class="text-center">Cant.</th>
                                    <th style="width: 11%;" class="text-end" title="Precio de lista oficial sin deducción de seguro">Precio Lista</th>
                                    <th style="width: 13%;" class="text-center" title="Monto cubierto por la Aseguradora">Cobertura ARS</th>
                                    <th style="width: 11%;" class="text-end" title="Precio unitario a pagar por el paciente">Precio Copago</th>
                                    <th style="width: 6%;" class="text-center">ITBIS</th>
                                    <th style="width: 12%;" class="text-end" title="Total neto facturado al paciente">Total Facturado</th>
                                </tr>
                            </thead>
                            <tbody id="modalFac_LineasBody">
                                <!-- Filas dinámicas -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Totales y Resumen -->
                    <div class="row justify-content-end">
                        <div class="col-md-5">
                            <div class="modal-totals-card">
                                <div class="modal-total-row">
                                    <span class="text-muted fw-bold">Subtotal de Lista:</span>
                                    <span id="modalFac_Subtotal" class="font-monospace fw-bold text-dark">RD$ 0.00</span>
                                </div>
                                <div class="modal-total-row text-success" id="rowModalCoberturaARS" style="display: none;">
                                    <span class="fw-bold"><i class="bi bi-shield-check me-1"></i>Cobertura Seguro (ARS):</span>
                                    <span id="modalFac_CoberturaARS" class="font-monospace fw-bold text-success">- RD$ 0.00</span>
                                </div>
                                <div class="modal-total-row" id="rowModalDescuento" style="display: none;">
                                    <span class="text-muted fw-bold">Descuento Comercial:</span>
                                    <span id="modalFac_Descuento" class="font-monospace fw-bold text-danger">RD$ 0.00</span>
                                </div>
                                <div class="modal-total-row">
                                    <span class="text-muted fw-bold">Total ITBIS:</span>
                                    <span id="modalFac_ITBIS" class="font-monospace fw-bold text-dark">RD$ 0.00</span>
                                </div>
                                <div class="modal-total-grand">
                                    <span id="modalFac_LblTotalGeneral">TOTAL FACTURADO:</span>
                                    <span id="modalFac_TotalGeneral" class="font-monospace text-primary">RD$ 0.00</span>
                                </div>
                                
                                <!-- Desglose Responsable Si aplica ARS -->
                                <div id="modalFac_DesgloseARS" style="display: none;" class="mt-2 pt-2 border-top">
                                    <div class="modal-total-row text-purple">
                                        <span class="fw-bold">Total Cubierto por ARS:</span>
                                        <span id="modalFac_TotalARS" class="font-monospace fw-bold">RD$ 0.00</span>
                                    </div>
                                    <div class="modal-total-row text-success">
                                        <span class="fw-bold">Copago Total Paciente:</span>
                                        <span id="modalFac_TotalCopago" class="font-monospace fw-bold">RD$ 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer bg-light d-flex justify-content-between">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="#" id="modalFac_BtnPrint" target="_blank" class="btn btn-success px-4 fw-bold">
                        <i class="bi bi-printer me-1"></i> Imprimir Factura
                    </a>
                    <a href="#" id="modalFac_BtnPrintND" target="_blank" class="btn text-white px-4 fw-bold" style="background: #4338ca; border: none; display: none;">
                        <i class="bi bi-file-earmark-arrow-down-fill me-1"></i> Imprimir Nota de Débito (<span id="modalFac_LblND_NCF">ND</span>)
                    </a>
                </div>
                <button type="button" class="btn btn-outline-secondary px-4 fw-bold" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ============================================================
     5. MODAL PARA EMITIR NOTA DE DÉBITO (ND - E33 / B03)
     ============================================================ -->
<div class="modal fade" id="modalEmitirND" tabindex="-1" aria-labelledby="modalEmitirNDLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            
            <!-- Encabezado corporativo oscuro coherente con el módulo -->
            <div class="modal-header modal-factura-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #fff; box-shadow: 0 4px 10px rgba(67, 56, 202, 0.3);">
                        <i class="bi bi-file-earmark-plus-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-factura-title text-white mb-0" id="modalEmitirNDLabel">
                            Emitir Nota de Débito (ND)
                        </h5>
                        <span class="text-white-50" style="font-size: 0.8rem;">Modificación y ajuste de cargo fiscal sobre factura activa (e-CF E33 / B03)</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-white">
                <form id="formEmitirNotaDebito" onsubmit="event.preventDefault(); procesarEmisionND();">
                    
                    <!-- Ficha Factura Origen Afectada -->
                    <div class="p-3 mb-3 rounded-3 border" style="background: #f8fafc; border-color: #cbd5e1 !important;">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-6">
                                <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.74rem; letter-spacing: 0.5px;">Factura Afectada / Comprobante:</span>
                                <div class="text-dark font-monospace fw-bold fs-6 mt-1" id="modalND_LblFactura">-</div>
                                <div class="mt-1">
                                    <span class="badge font-monospace fw-bold px-2 py-1" style="background: #e0e7fe; color: #0369a1; border: 1px solid #7dd3fc; font-size: 0.82rem;" id="modalND_LblNCF">-</span>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <span class="text-secondary text-uppercase fw-bold" style="font-size: 0.74rem; letter-spacing: 0.5px;">Paciente / Cliente Receptor:</span>
                                <div class="text-dark fw-bold fs-6 mt-1 text-truncate" id="modalND_LblCliente">-</div>
                                <div class="mt-1 d-flex gap-2 justify-content-md-end flex-wrap">
                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.78rem;" id="modalND_LblNit">-</span>
                                    <span class="badge font-monospace fw-bold" style="background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; font-size: 0.78rem;" id="modalND_LblTotalOrig">RD$ 0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="modalND_DocEntry">

                    <div class="row g-3">
                        <!-- Serie ND (E33 / B03) -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark mb-1">
                                <i class="bi bi-shield-lock-fill text-primary me-1"></i> Serie NCF (ND):
                            </label>
                            <div class="d-flex align-items-center bg-light border rounded px-2" style="border-color: #cbd5e1 !important; height: 38px;">
                                <i class="bi bi-lock-fill text-secondary me-2"></i>
                                <input type="text" id="modalND_Serie" class="form-control-plaintext font-monospace fw-bold text-dark py-0" readonly value="E33" style="font-size: 0.95rem;">
                                <span class="badge font-monospace fw-bold" style="background: #e0e7ff; color: #4338ca; font-size: 0.72rem;" id="modalND_SerieDesc">e-CF Débito</span>
                            </div>
                        </div>

                        <!-- Tipo de Ajuste DGII -->
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-dark mb-1">
                                <i class="bi bi-tag-fill text-primary me-1"></i> Concepto / Tipo de Ajuste (DGII):
                            </label>
                            <select id="modalND_TipoAjuste" class="form-select" style="border-color: #cbd5e1; height: 38px; font-size: 0.88rem; font-weight: 600;">
                                <option value="01">01 - Gastos por Intereses / Mora</option>
                                <option value="02">02 - Gastos Administrativos / Recargos</option>
                                <option value="03" selected>03 - Corrección de Valor / Servicios Adicionales</option>
                                <option value="04">04 - Otros Ajustes de Débito</option>
                            </select>
                        </div>

                        <!-- Monto Subtotal Débito -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">
                                <i class="bi bi-cash-stack text-success me-1"></i> Monto Débito (Subtotal): <span class="text-danger">*</span>
                            </label>
                            <div class="input-group" style="height: 38px;">
                                <span class="input-group-text font-monospace fw-bold bg-light text-dark" style="border-color: #cbd5e1;">RD$</span>
                                <input type="number" step="0.01" min="0.01" id="modalND_SubtotalInput" class="form-control font-monospace fw-bold text-dark" placeholder="0.00" oninput="calcularTotalesModalND()" required style="border-color: #cbd5e1; font-size: 1rem;">
                            </div>
                        </div>

                        <!-- Aplicar ITBIS -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">
                                <i class="bi bi-percent text-warning me-1"></i> Impuesto ITBIS:
                            </label>
                            <select id="modalND_ItbisSelect" class="form-select" onchange="calcularTotalesModalND()" style="border-color: #cbd5e1; height: 38px; font-size: 0.88rem; font-weight: 600;">
                                <option value="0.18">18% (Gravado con ITBIS)</option>
                                <option value="0.00">0% (Exento / Sin ITBIS)</option>
                            </select>
                        </div>

                        <!-- Resumen y Totales Reactivos -->
                        <div class="col-12">
                            <div class="p-3 rounded-3 border d-flex justify-content-between align-items-center flex-wrap gap-2" style="background: #f8fafc; border-color: #cbd5e1 !important;">
                                <div class="d-flex flex-column gap-1" style="font-size: 0.86rem;">
                                    <div>
                                        <span class="text-dark fw-bold">Subtotal Débito:</span>
                                        <span id="modalND_LblSubtotalCalc" class="font-monospace fw-bold text-dark ms-1">RD$ 0.00</span>
                                    </div>
                                    <div>
                                        <span class="text-dark fw-bold">ITBIS Débito:</span>
                                        <span id="modalND_LblItbisCalc" class="font-monospace fw-bold text-dark ms-1">RD$ 0.00</span>
                                    </div>
                                </div>
                                <div class="text-end bg-white p-2 px-3 rounded-2 border shadow-sm" style="border-color: #4338ca !important;">
                                    <span class="d-block fw-bold" style="font-size: 0.74rem; color: #4338ca; text-transform: uppercase; letter-spacing: 0.5px;">TOTAL NOTA DE DÉBITO</span>
                                    <span id="modalND_LblTotalCalc" class="font-monospace fw-bold fs-5" style="color: #312e81;">RD$ 0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Motivo / Justificación -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark mb-1">
                                <i class="bi bi-pencil-square text-secondary me-1"></i> Motivo / Justificación Fiscal: <span class="text-danger">*</span>
                            </label>
                            <textarea id="modalND_Motivo" class="form-control" rows="2" placeholder="Ingrese el motivo por el cual se emite esta Nota de Débito..." required style="border-color: #cbd5e1; font-size: 0.88rem;"></textarea>
                            
                            <!-- Nota Aclaratoria DGII -->
                            <div class="p-2 px-3 mt-2 rounded-2 d-flex align-items-center gap-2" style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; font-size: 0.78rem;">
                                <i class="bi bi-info-circle-fill flex-shrink-0 fs-6"></i>
                                <span>Este comprobante electrónico generará un <strong>NCF de Nota de Débito (E33 / B03)</strong> registrado ante la DGII, ajustando el cargo sin anular la factura original.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer del Modal con Acciones -->
                    <div class="modal-footer bg-light px-0 pb-0 pt-3 mt-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary px-4 fw-bold" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i> Cancelar
                        </button>
                        <button type="submit" id="modalND_BtnSubmit" class="btn btn-primary px-4 fw-bold" style="background: #4338ca; border-color: #4338ca;">
                            <i class="bi bi-check2-circle me-1"></i> Emitir Nota de Débito
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
let tablaFacturas;

$(document).ready(function() {
    tablaFacturas = $('#tablaFacturas').DataTable({ 
        language: {
            processing: "Procesando...",
            search: "Buscar en tabla:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros en total)",
            loadingRecords: "Cargando...",
            zeroRecords: "No se encontraron registros coincidentes",
            emptyTable: "No hay facturas disponibles en la tabla",
            paginate: {
                first: "Primero",
                previous: "Anterior",
                next: "Siguiente",
                last: "Último"
            }
        },
        pageLength: 25, 
        order: [[ 0, 'desc' ]],                    
        columnDefs: [ { orderable: false, targets: 8 } ],
        initComplete: function() {
            $('div.dataTables_length select').addClass('form-select form-select-sm');
        }
    });
});

/**
 * Filtrar tabla de facturas por múltiples criterios
 */
function filtrarFacturas() {
    let desde = $('#filtroDesde').val();
    let hasta = $('#filtroHasta').val();
    let tipo = $('#filtroTipo').val();
    let condicion = $('#filtroCondicion').val();
    let estado = $('#filtroEstado').val();
    let serie = $('#filtroSerie').val();

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaFacturas') return true;

        let row = tablaFacturas.row(dataIndex).node();
        let rowFecha = $(row).data('fecha');
        let rowTipo = $(row).data('tipo');
        let rowCondicion = $(row).data('condicion');
        let rowEstado = $(row).data('estado');
        let rowSerie = $(row).data('serie');

        // Filtrar por Fecha
        if (desde && rowFecha) {
            if (rowFecha < desde) return false;
        }
        if (hasta && rowFecha) {
            if (rowFecha > hasta + ' 23:59:59') return false;
        }

        // Filtrar por Tipo Paciente
        if (tipo && String(rowTipo) !== String(tipo)) {
            return false;
        }

        // Filtrar por Condición de Pago
        if (condicion && String(rowCondicion) !== String(condicion)) {
            return false;
        }

        // Filtrar por Estado
        if (estado && String(rowEstado) !== String(estado)) {
            return false;
        }

        // Filtrar por Serie NCF
        if (serie && String(rowSerie).toUpperCase() !== String(serie).toUpperCase()) {
            return false;
        }

        return true;
    });

    tablaFacturas.draw();
    $.fn.dataTable.ext.search.pop();
}

/**
 * Limpiar todos los filtros
 */
function limpiarFiltros() {
    $('#filtroDesde').val('');
    $('#filtroHasta').val('');
    $('#filtroTipo').val('');
    $('#filtroCondicion').val('');
    $('#filtroEstado').val('');
    $('#filtroSerie').val('');
    tablaFacturas.search('').columns().search('').draw();
}

/**
 * Abrir Modal para Emitir Nota de Débito
 */
function abrirModalEmitirND(docEntry, numAtCard, ncf, cardName, nit, total, serie) {
    $('#modalND_DocEntry').val(docEntry);
    $('#modalND_LblFactura').text(numAtCard || ('Factura #' + docEntry));
    $('#modalND_LblNCF').text(ncf ? ('NCF: ' + ncf) : 'Sin NCF asignado');
    $('#modalND_LblCliente').text(cardName || 'Consumidor Final');
    $('#modalND_LblNit').text(nit ? ('RNC/Céd: ' + nit) : 'Sin Documento');
    $('#modalND_LblTotalOrig').text('Monto Factura: RD$ ' + parseFloat(total || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    
    // Serie sugerida (E33 si la factura es electrónica, B03 si es tradicional)
    let serieND = (String(serie).toUpperCase().startsWith('B') || String(ncf).toUpperCase().startsWith('B')) ? 'B03' : 'E33';
    $('#modalND_Serie').val(serieND);
    $('#modalND_SerieDesc').text(serieND === 'E33' ? 'e-CF Débito' : 'NCF Tradicional');

    $('#modalND_SubtotalInput').val('');
    $('#modalND_Motivo').val('');
    $('#modalND_ItbisSelect').val('0.18');
    calcularTotalesModalND();

    new bootstrap.Modal(document.getElementById('modalEmitirND')).show();
}

/**
 * Calcular Totales reactivos en Modal de ND
 */
function calcularTotalesModalND() {
    let subtotal = parseFloat($('#modalND_SubtotalInput').val()) || 0;
    let itbisRate = parseFloat($('#modalND_ItbisSelect').val()) || 0;
    let itbis = subtotal * itbisRate;
    let total = subtotal + itbis;

    $('#modalND_LblSubtotalCalc').text('RD$ ' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    $('#modalND_LblItbisCalc').text('RD$ ' + itbis.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    $('#modalND_LblTotalCalc').text('RD$ ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
}

/**
 * Procesar Emisión de Nota de Débito vía AJAX
 */
function procesarEmisionND() {
    let docEntry = parseInt($('#modalND_DocEntry').val()) || 0;
    if (docEntry <= 0) {
        alert('Factura no especificada.');
        return;
    }

    let subtotal = parseFloat($('#modalND_SubtotalInput').val()) || 0;
    if (subtotal <= 0) {
        alert('Ingrese un monto válido para el débito.');
        $('#modalND_SubtotalInput').focus();
        return;
    }

    let motivo = $('#modalND_Motivo').val().trim();
    if (!motivo) {
        alert('Debe ingresar el motivo o justificación de la Nota de Débito.');
        $('#modalND_Motivo').focus();
        return;
    }

    let itbisRate = parseFloat($('#modalND_ItbisSelect').val()) || 0;
    let itbis = subtotal * itbisRate;
    let total = subtotal + itbis;
    let tipoND = $('#modalND_TipoAjuste').val();

    let btn = $('#modalND_BtnSubmit');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Emitiendo...');

    let payload = {
        fac_doc_entry: docEntry,
        monto_debito: total,
        itbis_debito: itbis,
        tipo_nd: tipoND,
        motivo: motivo,
        doc_date: new Date().toISOString().substring(0, 10)
    };

    $.ajax({
        url: 'index.php?action=guardar_nota_debito',
        type: 'POST',
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(payload),
        success: function(res) {
            btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Emitir Nota de Débito');
            if (res.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalEmitirND')).hide();
                let confirmPrint = confirm(res.message + '\n\n¿Desea imprimir el comprobante oficial de Nota de Débito ahora?');
                if (confirmPrint && res.docEntry) {
                    window.open(`index.php?action=informeND&id=${res.docEntry}`, '_blank');
                }
                setTimeout(() => location.reload(), 800);
            } else {
                alert(res.message || 'Error al emitir la Nota de Débito.');
            }
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> Emitir Nota de Débito');
            let msg = 'Error en el servidor al emitir la Nota de Débito.';
            try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e){}
            alert(msg);
        }
    });
}

/**
 * Consultar y Abrir Modal de Detalle de Factura vía AJAX
 */
function verDetalleFactura(docEntry) {
    let modal = new bootstrap.Modal(document.getElementById('modalDetalleFactura'));
    $('#modalLoadingSpinner').show();
    $('#modalFacturaContent').hide();
    modal.show();

    $.ajax({
        url: `index.php?action=obtener_detalle_factura&id=${docEntry}`,
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success' && res.data) {
                let h = res.data.cabecera;
                let lines = res.data.lineas || [];
                let ars = res.data.aprobacionARS;
                let nc = res.data.notaCredito;
                let nd = res.data.notaDebito;

                // 1. Cabecera Modal
                $('#modalFac_Numero').text(h.NumAtCard || ('F-' + h.C_DocEntry));
                $('#modalFac_Cliente').text(h.CardName || '-');
                $('#modalFac_Nit').text(h.PacienteNIT ? ('Céd/RNC: ' + h.PacienteNIT) : (h.U_FacNit ? ('RNC: ' + h.U_FacNit) : 'Sin Documento'));
                $('#modalFac_NCF').text(h.U_FACNUM || h.NumAtCard);
                $('#modalFac_SerieVence').text(h.U_FacSerie ? ('Serie ' + h.U_FacSerie + (h.U_NCFVence ? (' | Vence: ' + h.U_NCFVence) : '')) : 'Comprobante Estándar');
                
                let fDate = h.DocDate ? h.DocDate.substring(0, 10) : '-';
                $('#modalFac_Fecha').text(fDate);
                
                let esCredito = parseInt(h.ExtraDays || 0) > 1;
                $('#modalFac_Condicion').html(esCredito ? '<span class="badge-condicion badge-credito">Crédito (' + h.ExtraDays + ' días)</span>' : '<span class="badge-condicion badge-contado">Contado</span>');
                
                let docTotal = parseFloat(h.DocTotal || 0);
                let ncTotal = nc ? parseFloat(nc.DocTotal || 0) : 0;
                let esTotalmenteAnulada = (h.CANCELED === 'Y' || (nc != null && ncTotal >= docTotal - 0.01));
                let esAfectadaParcial = (!esTotalmenteAnulada && nc != null && ncTotal > 0);
                let tieneND = (nd != null);

                if (esTotalmenteAnulada) {
                    $('#modalFac_Estado').html('<span class="badge-estado-fac badge-fac-anulada">ANULADA</span>');
                } else if (esAfectadaParcial) {
                    $('#modalFac_Estado').html('<span class="badge-estado-fac badge-fac-modificada" style="background-color:#fef3c7; color:#92400e; border:1px solid #fde68a;">NC PARCIAL</span>');
                } else if (tieneND) {
                    $('#modalFac_Estado').html('<span class="badge-estado-fac badge-fac-modificada">MODIFICADA (ND)</span>');
                } else {
                    $('#modalFac_Estado').html('<span class="badge-estado-fac badge-fac-vigente">VIGENTE</span>');
                }

                // 2. Banner NC
                if (nc) {
                    $('#modalBannerNC').show();
                    $('#modalNC_TextoInfo').text(`${nc.NumAtCard || 'NC'} (NCF: ${nc.U_FACNUM || '-'}) por RD$ ${parseFloat(nc.DocTotal || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}`);
                } else {
                    $('#modalBannerNC').hide();
                }

                // 2.B Banner ND (Modificación) y Botones de Impresión
                if (nd) {
                    $('#modalBannerND').show();
                    $('#modalND_TextoInfo').text(`${nd.NumAtCard || 'ND'} (NCF: ${nd.U_FACNUM || '-'}) por RD$ ${parseFloat(nd.DocTotal || 0).toLocaleString('en-US', {minimumFractionDigits: 2})} - Motivo: ${nd.Comments || 'Ajuste fiscal'}`);
                    
                    let printNdUrl = `index.php?action=informeND&id=${nd.C_DocEntry}`;
                    $('#modalND_BtnPrintBanner').attr('href', printNdUrl).show();
                    $('#modalFac_BtnPrintND').attr('href', printNdUrl).show();
                    $('#modalFac_LblND_NCF').text(nd.U_FACNUM || nd.NumAtCard || 'ND');
                } else {
                    $('#modalBannerND').hide();
                    $('#modalND_BtnPrintBanner').hide();
                    $('#modalFac_BtnPrintND').hide();
                }

                // 3. Banner ARS
                let aprobaciones = res.data.aprobacionesARS || (ars ? [ars] : []);
                let covTotal = 0;
                let copagoTotal = 0;

                if (aprobaciones.length > 0) {
                    $('#modalBannerARS').show();
                    let primerArs = aprobaciones[0];
                    $('#modalARS_Nombre').text(primerArs.NombreARS || primerArs.CardCode_ARS || 'Aseguradora');
                    $('#modalARS_Afiliado').text(primerArs.NumAfiliado || '-');

                    if (aprobaciones.length > 1) {
                        let numsAut = aprobaciones.map(a => a.NumAprobacion).filter(Boolean).join(', ');
                        let tiposServ = aprobaciones.map(a => a.TipoServicio).filter(Boolean).join(' | ');
                        $('#modalARS_Aut').text(numsAut || 'S/N');
                        $('#modalARS_Servicio').text(tiposServ || 'Por Artículos');
                    } else {
                        $('#modalARS_Aut').text(primerArs.NumAprobacion || 'S/N');
                        $('#modalARS_Servicio').text(primerArs.TipoServicio || 'CONSULTA MEDICA');
                    }

                    aprobaciones.forEach(a => {
                        covTotal += parseFloat(a.MontoAprobado || 0);
                        copagoTotal += parseFloat(a.MontoResponsable || 0);
                    });

                    $('#modalARS_CoberturaBadge').text('Cobertura ARS: RD$ ' + covTotal.toLocaleString('en-US', {minimumFractionDigits: 2}));
                    $('#modalFac_DesgloseARS').show();
                    $('#modalFac_TotalARS').text('RD$ ' + covTotal.toLocaleString('en-US', {minimumFractionDigits: 2}));
                    $('#modalFac_TotalCopago').text('RD$ ' + copagoTotal.toLocaleString('en-US', {minimumFractionDigits: 2}));
                } else {
                    $('#modalBannerARS').hide();
                    $('#modalFac_DesgloseARS').hide();
                }

                // 4. Líneas de Factura (Desglose de Precio Real, Cobertura ARS y Copago)
                let tbody = $('#modalFac_LineasBody');
                tbody.empty();

                let subtotalRealBruto = 0;
                let totalCobARS = 0;
                let descTotal = 0;

                if (lines.length > 0) {
                    lines.forEach((l, idx) => {
                        let cant = parseFloat(l.Quantity || 1);
                        let precioReal = parseFloat(l.PriceBefDi || l.Price || 0);
                        let precioCopago = parseFloat(l.Price || 0);
                        let descP = parseFloat(l.DiscPrcnt || 0);
                        let impP = parseFloat(l.VatPrcnt || 0);
                        let totalLin = parseFloat(l.LineTotal || 0);

                        let brutLinReal = cant * precioReal;
                        let descLin = (cant * precioCopago) * (descP / 100);

                        subtotalRealBruto += brutLinReal;
                        descTotal += descLin;

                        // Cobertura ARS de esta línea específica
                        let coberturaHtml = '<span class="text-muted font-monospace">-</span>';
                        if (precioReal > (precioCopago + 0.001)) {
                            let diffUnit = precioReal - precioCopago;
                            let cobLinea = diffUnit * cant;
                            totalCobARS += cobLinea;
                            let pctCob = Math.round((diffUnit / precioReal) * 100);
                            coberturaHtml = `<span class="badge" style="background: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe; font-size: 0.78rem; font-weight: 700; font-family: monospace;" title="Diferencia cubierta por seguro: RD$ ${cobLinea.toFixed(2)}">RD$ ${cobLinea.toLocaleString('en-US', {minimumFractionDigits: 2})} (${pctCob}%)</span>`;
                        }

                        tbody.append(`
                            <tr>
                                <td class="text-muted font-monospace text-center">${idx + 1}</td>
                                <td class="font-monospace fw-bold text-dark">${l.ItemCode || '-'}</td>
                                <td>
                                    <div class="fw-semibold text-dark">${l.Dscription || '-'}</div>
                                    <small class="text-muted">${l.GrupoArticulo || ''}</small>
                                </td>
                                <td class="text-center font-monospace fw-semibold">${cant}</td>
                                <td class="text-end font-monospace fw-bold text-secondary">RD$ ${precioReal.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                <td class="text-center">${coberturaHtml}</td>
                                <td class="text-end font-monospace fw-bold text-primary">RD$ ${precioCopago.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                <td class="text-center font-monospace">${impP > 0 ? impP + '%' : 'Exento'}</td>
                                <td class="text-end font-monospace fw-bold text-dark">RD$ ${totalLin.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                            </tr>
                        `);
                    });
                } else {
                    tbody.append(`
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No se encontraron líneas registradas para esta factura.</td>
                        </tr>
                    `);
                }

                // 5. Totales
                let itbisTotal = parseFloat(h.VatSum || 0);
                docTotal = parseFloat(h.DocTotal || 0);
                let cobFinalMostrar = (totalCobARS > 0) ? totalCobARS : covTotal;

                $('#modalFac_Subtotal').text('RD$ ' + subtotalRealBruto.toLocaleString('en-US', {minimumFractionDigits: 2}));

                if (cobFinalMostrar > 0.001) {
                    $('#rowModalCoberturaARS').show();
                    $('#modalFac_CoberturaARS').text('- RD$ ' + cobFinalMostrar.toLocaleString('en-US', {minimumFractionDigits: 2}));
                } else {
                    $('#rowModalCoberturaARS').hide();
                }

                if (descTotal > 0.001) {
                    $('#rowModalDescuento').show();
                    $('#modalFac_Descuento').text('- RD$ ' + descTotal.toLocaleString('en-US', {minimumFractionDigits: 2}));
                } else {
                    $('#rowModalDescuento').hide();
                }

                $('#modalFac_ITBIS').text('RD$ ' + itbisTotal.toLocaleString('en-US', {minimumFractionDigits: 2}));
                $('#modalFac_TotalGeneral').text('RD$ ' + docTotal.toLocaleString('en-US', {minimumFractionDigits: 2}));
                $('#modalFac_LblTotalGeneral').text('TOTAL FACTURADO (COPAGO):');

                // Si hay desglose de ARS en el footer
                if (cobFinalMostrar > 0.001) {
                    $('#modalFac_DesgloseARS').show();
                    $('#modalFac_TotalARS').text('RD$ ' + cobFinalMostrar.toLocaleString('en-US', {minimumFractionDigits: 2}));
                    $('#modalFac_TotalCopago').text('RD$ ' + docTotal.toLocaleString('en-US', {minimumFractionDigits: 2}));
                }

                // 6. Configurar enlace de impresión de Factura Sellada
                let ecfId = h.EcfIdRecord ? parseInt(h.EcfIdRecord) : 0;
                if (ecfId > 0) {
                    $('#modalFac_BtnPrint').attr('href', `xmlceyes/factura_supermercado.php?id=${ecfId}`).removeClass('disabled').show();
                } else {
                    $('#modalFac_BtnPrint').attr('href', '#').addClass('disabled').hide();
                }

                $('#modalLoadingSpinner').hide();
                $('#modalFacturaContent').fadeIn(200);

            } else {
                alert(res.msg || 'No fue posible cargar el detalle de la factura.');
                modal.hide();
            }
        },
        error: function(xhr) {
            alert('Error en la comunicación con el servidor al cargar la factura.');
            modal.hide();
        }
    });
}
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>