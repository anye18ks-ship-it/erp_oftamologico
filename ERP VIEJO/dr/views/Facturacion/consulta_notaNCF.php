<?php require_once __DIR__ . '/../Estructura/header.php'; ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="views/Facturacion/styles/consulta_notaNCF.css">



<div class="container-fluid consulta-nc-container">
    
    <!-- ============================================================
         1. HEADER DE PÁGINA
         ============================================================ -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-file-earmark-x-fill"></i>
            </div>
            <div>
                <h2 class="page-title">Historial y Consulta de Notas de Crédito NCF</h2>
                <p class="page-subtitle">Auditoría fiscal, trazabilidad de comprobantes emitidos y reimpresión de notas de crédito</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?page=nota_credito_ncf" class="btn-header-action btn-header-primary">
                <i class="bi bi-plus-circle"></i> Nueva Nota de Crédito
            </a>
            <a href="index.php?page=facturacion" class="btn-header-action btn-header-success">
                <i class="bi bi-receipt"></i> Facturación
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
         3. GENERADOR DE RELACIÓN MENSUAL (PDF)
         ============================================================ -->
    <div class="report-generator-card mb-4">
        <div class="report-generator-header">
            <div class="d-flex align-items-center gap-3">
                <div class="generator-icon-badge">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                </div>
                <div>
                    <h3 class="generator-title">Generador de Relación General de Notas de Crédito (PDF)</h3>
                    <p class="generator-subtitle">Emita el informe mensual de auditoría contable para declaraciones fiscales</p>
                </div>
            </div>
        </div>

        <div class="report-generator-body">
            <form id="formGenerarReporteNC" onsubmit="event.preventDefault(); generarReporteMensualPDF();" class="row g-3 align-items-end">
                <!-- Selector de Mes -->
                <div class="col-lg-4 col-md-5">
                    <label class="form-report-label" for="reporte_nc_mes">
                        <i class="bi bi-calendar3 me-1 text-danger"></i> Mes de Emisión:
                    </label>
                    <select id="reporte_nc_mes" name="mes" class="form-select form-report-select">
                        <?php 
                        $nombresMeses = [
                            1 => '01 - Enero', 2 => '02 - Febrero', 3 => '03 - Marzo', 4 => '04 - Abril',
                            5 => '05 - Mayo', 6 => '06 - Junio', 7 => '07 - Julio', 8 => '08 - Agosto',
                            9 => '09 - Septiembre', 10 => '10 - Octubre', 11 => '11 - Noviembre', 12 => '12 - Diciembre'
                        ];
                        $mesActual = (int)date('n');
                        foreach ($nombresMeses as $numMes => $nombreMes): 
                            $selected = ($numMes === $mesActual) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $numMes; ?>" <?php echo $selected; ?>>
                                <?php echo $nombreMes; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Selector de Año -->
                <div class="col-lg-3 col-md-4">
                    <label class="form-report-label" for="reporte_nc_anio">
                        <i class="bi bi-calendar2-range me-1 text-danger"></i> Año Fiscal:
                    </label>
                    <select id="reporte_nc_anio" name="anio" class="form-select form-report-select">
                        <?php 
                        $anioBase = (int)date('Y');
                        for ($y = $anioBase + 1; $y >= $anioBase - 3; $y--): 
                            $selected = ($y === $anioBase) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $y; ?>" <?php echo $selected; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Botón de Generación -->
                <div class="col-lg-3 col-md-3">
                    <button type="button" onclick="generarReporteMensualPDF()" class="btn-generate-report-pdf w-100">
                        <i class="bi bi-printer-fill me-1"></i> Imprimir Relación PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================
         4. REGISTROS Y COMPROBANTES DE NOTAS DE CRÉDITO
         ============================================================ -->
    <div class="consulta-card">
        
        <!-- Barra de Filtros Avanzados -->
        <div class="consulta-filter-bar">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-funnel-fill text-danger fs-5"></i>
                <span class="fw-bold text-dark">Registros y Comprobantes de Notas de Crédito</span>
            </div>

            <div class="filter-controls-group">
                <div class="filter-group-item">
                    <span class="filter-group-label"><i class="bi bi-calendar-event me-1"></i>Desde:</span>
                    <input type="date" id="filtroDesde" class="filter-input">
                </div>
                <div class="filter-group-item">
                    <span class="filter-group-label"><i class="bi bi-calendar-event me-1"></i>Hasta:</span>
                    <input type="date" id="filtroHasta" class="filter-input">
                </div>

                <div class="filter-group-item">
                    <span class="filter-group-label"><i class="bi bi-tag me-1"></i>Motivo:</span>
                    <select id="filtroMotivo" class="filter-input bg-transparent" style="max-width: 180px;">
                        <option value="">-- Todos los Motivos --</option>
                        <?php foreach ($tiposNC as $t): ?>
                            <option value="<?php echo htmlspecialchars($t['code']); ?>">
                                <?php echo htmlspecialchars($t['code'] . ' - ' . $t['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="button" onclick="filtrarNC()" class="btn-filter-apply">
                    <i class="bi bi-search"></i> Filtrar
                </button>
                <button type="button" onclick="limpiarFiltrosNC()" class="btn-filter-clear">
                    <i class="bi bi-x-circle text-secondary"></i> Limpiar
                </button>
            </div>
        </div>

        <!-- Cuerpo de la Tabla -->
        <div class="consulta-card-body">
            <div class="table-responsive">
                <table id="tablaNotasCredito" class="table table-hover align-middle table-sm" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 4%;">ID</th>
                            <th style="width: 8%;">Fecha</th>
                            <th style="width: 13%;">NCF Nota Crédito</th>
                            <th style="width: 11%;">Factura Afectada</th>
                            <th style="width: 18%;">Cliente / Paciente</th>
                            <th style="width: 12%;">Motivo / Tipo</th>
                            <th style="width: 9%;" class="text-end">Subtotal</th>
                            <th style="width: 7%;" class="text-end">ITBIS</th>
                            <th style="width: 8%;" class="text-end">Total NC</th>
                            <th style="width: 10%;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($notasCredito)): ?>
                            <?php foreach ($notasCredito as $nc): ?>
                            <tr data-fecha="<?php echo htmlspecialchars($nc['DocDate'] ?? ''); ?>" data-motivo="<?php echo htmlspecialchars($nc['U_TipoNC'] ?? ''); ?>">
                                <td class="font-monospace text-muted"><?php echo $nc['C_DocEntry']; ?></td>
                                <td class="font-monospace text-secondary">
                                    <?php echo $nc['DocDateFmt']; ?>
                                    <div class="text-muted" style="font-size: 0.72rem;"><?php echo explode(' ', $nc['C_InsertedFmt'])[1] ?? ''; ?></div>
                                </td>
                                <td>
                                    <?php if ($nc['CANCELED'] === 'Y'): ?>
                                        <div class="ncf-cell-wrap">
                                            <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                                <span class="badge-ncf-nc is-canceled" title="Nota de Crédito Anulada">
                                                    <span class="text-decoration-line-through"><?php echo htmlspecialchars($nc['NumNCF'] ?: 'PENDIENTE'); ?></span>
                                                </span>
                                                <span class="badge-tag-anulada" title="Estado: Anulada">ANULADA</span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge-ncf-nc">
                                            <?php echo htmlspecialchars($nc['NumNCF'] ?: 'PENDIENTE'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-factura-orig">
                                        <?php echo htmlspecialchars($nc['NumFacturaOrigen'] ?: ($nc['C_FromNumAtCard'] ?: 'ID ' . $nc['C_DocOrigenNum'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($nc['CardName']); ?></div>
                                    <div class="text-muted font-monospace" style="font-size: 0.74rem;">
                                        RNC: <?php echo htmlspecialchars($nc['U_FacNit'] ?: 'No registrado'); ?> | Cód: <?php echo htmlspecialchars($nc['CardCode']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-tipo-anulacion" title="<?php echo htmlspecialchars($nc['Motivo']); ?>">
                                        <?php echo htmlspecialchars($nc['TipoNC_Nombre']); ?>
                                    </span>
                                </td>
                                <td class="text-end font-monospace text-secondary">
                                    RD$ <?php echo number_format($nc['SubtotalNeto'] ?? 0, 2); ?>
                                </td>
                                <td class="text-end font-monospace text-secondary">
                                    RD$ <?php echo number_format($nc['ITBIS'] ?? 0, 2); ?>
                                </td>
                                <td class="text-end font-monospace monto-total-nc <?php echo ($nc['CANCELED'] === 'Y') ? 'text-decoration-line-through text-muted' : ''; ?>">
                                    RD$ <?php echo number_format($nc['TotalNC'] ?? 0, 2); ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1 align-items-center flex-nowrap justify-content-center">
                                        <button type="button" class="btn-action-icon btn-action-view" onclick="verDetalleNC(<?php echo $nc['C_DocEntry']; ?>)" title="Ver Detalle Completo">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="index.php?action=informeNCF&id=<?php echo $nc['C_DocEntry']; ?>" target="_blank" class="btn-action-icon btn-action-print" title="Imprimir Comprobante Oficial">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <a href="index.php?action=informeNCF&id=<?php echo $nc['C_DocEntry']; ?>&copia=1" target="_blank" class="btn-action-icon btn-action-copy" title="Imprimir Copia">
                                            <i class="bi bi-files"></i>
                                        </a>
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
     5. MODAL DETALLE DE NOTA DE CRÉDITO
     ============================================================ -->
<div class="modal fade" id="modalDetalleNC" tabindex="-1" aria-labelledby="modalDetalleNCLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header modal-header-nc">
                <h5 class="modal-title modal-nc-title" id="modalDetalleNCLabel">
                    <i class="bi bi-file-earmark-ruled-fill text-danger"></i> Detalle de Nota de Crédito: <span id="modalNC_NCF" class="text-warning font-monospace">--</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background: #ffffff;">
                
                <!-- Spinner de carga -->
                <div id="modalLoadingSpinner" class="text-center py-5">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted fw-semibold">Consultando detalle de comprobante...</p>
                </div>

                <!-- Contenido Dinámico -->
                <div id="modalNCContent" style="display: none;">
                    
                    <!-- Fila de Tarjetas Informativas -->
                    <div class="row g-3 mb-3">
                        <!-- Cliente -->
                        <div class="col-lg-4 col-md-6">
                            <div class="modal-nc-card-info">
                                <div class="info-label-muted"><i class="bi bi-person-fill me-1"></i> Receptor / Cliente</div>
                                <div id="modalNC_CardName" class="info-value-bold">--</div>
                                <div class="text-muted font-monospace mt-1" style="font-size: 0.78rem;">
                                    RNC/Cédula: <span id="modalNC_RNC" class="fw-bold text-dark">--</span><br>
                                    Código: <span id="modalNC_CardCode" class="fw-bold text-dark">--</span>
                                </div>
                            </div>
                        </div>

                        <!-- Factura Origen -->
                        <div class="col-lg-4 col-md-6">
                            <div class="modal-nc-card-info">
                                <div class="info-label-muted"><i class="bi bi-receipt me-1"></i> Factura Modificada (Origen)</div>
                                <div id="modalNC_FacturaNCF" class="info-value-bold text-primary font-monospace">--</div>
                                <div class="text-muted mt-1" style="font-size: 0.78rem;">
                                    Fecha Factura: <span id="modalNC_FacturaFecha" class="fw-bold text-dark">--</span><br>
                                    Total Factura: <span id="modalNC_FacturaTotal" class="fw-bold text-dark font-monospace">--</span>
                                </div>
                            </div>
                        </div>

                        <!-- Motivo y Auditoría -->
                        <div class="col-lg-4 col-md-12">
                            <div class="modal-nc-card-info">
                                <div class="info-label-muted"><i class="bi bi-info-circle-fill me-1"></i> Clasificación y Auditoría</div>
                                <div id="modalNC_TipoNombre" class="info-value-bold text-danger">--</div>
                                <div class="text-muted mt-1" style="font-size: 0.78rem;">
                                    Usuario: <span id="modalNC_Usuario" class="fw-bold text-dark">--</span> | Term: <span id="modalNC_Terminal" class="fw-bold text-dark">--</span><br>
                                    Registro: <span id="modalNC_FechaRegistro" class="fw-bold text-dark">--</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Observación / Motivo -->
                    <div class="alert alert-light border p-2 mb-3" style="font-size: 0.82rem;">
                        <strong><i class="bi bi-chat-left-text me-1 text-secondary"></i> Motivo / Justificación:</strong> 
                        <span id="modalNC_Comentario" class="fst-italic text-secondary">Sin comentario adicional</span>
                    </div>

                    <!-- Tabla de Artículos Afectados -->
                    <div class="table-responsive mb-3 border rounded-3 overflow-hidden">
                        <table class="table table-sm table-striped align-middle modal-table-detail mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 12%;">Código</th>
                                    <th style="width: 38%;">Descripción del Artículo / Servicio</th>
                                    <th style="width: 12%;" class="text-center">Cant. Acreditada</th>
                                    <th style="width: 12%;" class="text-end">Precio Unit.</th>
                                    <th style="width: 8%;" class="text-center">Desc.%</th>
                                    <th style="width: 8%;" class="text-end">ITBIS</th>
                                    <th style="width: 12%;" class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="modalNC_TbodyLineas">
                                <!-- Filas dinámicas -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Resumen de Totales -->
                    <div class="row justify-content-end">
                        <div class="col-md-5">
                            <div class="modal-nc-totals-box">
                                <div class="d-flex justify-content-between mb-1" style="font-size: 0.82rem;">
                                    <span class="text-muted fw-bold">Subtotal Neto:</span>
                                    <span id="modalNC_Subtotal" class="font-monospace fw-bold text-dark">RD$ 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2" style="font-size: 0.82rem;">
                                    <span class="text-muted fw-bold">Total ITBIS (18%):</span>
                                    <span id="modalNC_ITBIS" class="font-monospace fw-bold text-dark">RD$ 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between pt-2 border-top">
                                    <span class="fw-bold text-dark fs-8">TOTAL NOTA CRÉDITO:</span>
                                    <span id="modalNC_TotalGeneral" class="font-monospace fw-bold fs-5 text-danger">RD$ 0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a id="modalNC_BtnImprimir" href="#" target="_blank" class="btn btn-danger">
                    <i class="bi bi-printer me-1"></i> Imprimir Comprobante Oficial
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
let tablaNC;

$(document).ready(function() {
    tablaNC = $('#tablaNotasCredito').DataTable({ 
        language: {
            processing: "Procesando...",
            search: "Buscar en tabla:",
            lengthMenu: "Mostrar _MENU_ registros",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros en total)",
            loadingRecords: "Cargando...",
            zeroRecords: "No se encontraron registros coincidentes",
            emptyTable: "No hay datos disponibles en la tabla",
            paginate: {
                first: "Primero",
                previous: "Anterior",
                next: "Siguiente",
                last: "Último"
            }
        },
        pageLength: 25, 
        order: [[ 0, 'desc' ]],                    
        columnDefs: [ { orderable: false, targets: 9 } ],
        initComplete: function() {
            $('div.dataTables_length select').addClass('form-select form-select-sm');
        }
    });
});

/**
 * Filtrar tabla por fecha y motivo de anulación
 */
function filtrarNC() {
    let desde = $('#filtroDesde').val();
    let hasta = $('#filtroHasta').val();
    let motivo = $('#filtroMotivo').val();

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'tablaNotasCredito') return true;

        let row = tablaNC.row(dataIndex).node();
        let rowFecha = $(row).data('fecha');
        let rowMotivo = $(row).data('motivo');

        // Filtrar por Fecha
        if (desde && rowFecha) {
            if (rowFecha < desde) return false;
        }
        if (hasta && rowFecha) {
            if (rowFecha > hasta + ' 23:59:59') return false;
        }

        // Filtrar por Motivo
        if (motivo && String(rowMotivo) !== String(motivo)) {
            return false;
        }

        return true;
    });

    tablaNC.draw();
    $.fn.dataTable.ext.search.pop();
}

/**
 * Limpiar filtros NC
 */
function limpiarFiltrosNC() {
    $('#filtroDesde').val('');
    $('#filtroHasta').val('');
    $('#filtroMotivo').val('');
    tablaNC.search('').columns().search('').draw();
}

/**
 * Generar Relación General Mensual PDF
 */
function generarReporteMensualPDF() {
    let mes = $('#reporte_nc_mes').val();
    let anio = $('#reporte_nc_anio').val();
    let url = `index.php?action=informeNCF&tipo=relacion&mes=${mes}&anio=${anio}`;
    window.open(url, '_blank');
}

/**
 * Consultar y Abrir Modal de Detalle de Nota de Crédito
 */
function verDetalleNC(id) {
    let modal = new bootstrap.Modal(document.getElementById('modalDetalleNC'));
    $('#modalLoadingSpinner').show();
    $('#modalNCContent').hide();
    modal.show();

    $.ajax({
        url: `index.php?action=obtener_detalle_nc&id=${id}`,
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            $('#modalLoadingSpinner').hide();
            if (res.success && res.cabecera) {
                let c = res.cabecera;
                let lineas = res.lineas || [];

                // Llenar Cabecera
                $('#modalNC_NCF').text(c.NumNCF || `ID #${c.C_DocEntry}`);
                $('#modalNC_CardName').text(c.CardName || 'CONSUMIDOR FINAL');
                $('#modalNC_RNC').text(c.U_FacNit || 'No registrado');
                $('#modalNC_CardCode').text(c.CardCode || '--');

                $('#modalNC_FacturaNCF').text(c.NumFacturaOrigen || c.FacturaNCF || `DocEntry #${c.C_DocOrigenNum}`);
                $('#modalNC_FacturaFecha').text(c.FacturaDocDateFmt || '--');
                let factTotal = parseFloat(c.FacturaDocTotal) || 0;
                $('#modalNC_FacturaTotal').text('RD$ ' + factTotal.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                $('#modalNC_TipoNombre').text((c.U_TipoNC ? c.U_TipoNC + ' - ' : '') + c.TipoNC_Nombre);
                $('#modalNC_Usuario').text(c.Usuario || 'SYS');
                $('#modalNC_Terminal').text(c.Terminal || 'POS-01');
                $('#modalNC_FechaRegistro').text(c.C_InsertedFmt || c.DocDateFmt);
                $('#modalNC_Comentario').text(c.Motivo || 'Emisión de Nota de Crédito');

                // Llenar Líneas
                let htmlLineas = '';
                if (lineas.length === 0) {
                    htmlLineas = '<tr><td colspan="7" class="text-center text-muted py-3">No hay renglones registrados para este comprobante</td></tr>';
                } else {
                    lineas.forEach(l => {
                        let qty = parseFloat(l.Quantity) || 0;
                        let price = parseFloat(l.PriceBefDi) || parseFloat(l.Price) || 0;
                        let disc = parseFloat(l.DiscPrcnt) || 0;
                        let vat = parseFloat(l.VatSum) || 0;
                        let tot = parseFloat(l.LineTotal) || 0;

                        htmlLineas += `<tr>
                            <td class="font-monospace fw-bold text-secondary">${l.ItemCode}</td>
                            <td>${l.Dscription}</td>
                            <td class="text-center font-monospace">${qty.toFixed(2)}</td>
                            <td class="text-end font-monospace">RD$ ${price.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td class="text-center font-monospace">${disc.toFixed(0)}%</td>
                            <td class="text-end font-monospace">RD$ ${vat.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            <td class="text-end font-monospace fw-bold text-dark">RD$ ${tot.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        </tr>`;
                    });
                }
                $('#modalNC_TbodyLineas').html(htmlLineas);

                // Llenar Totales
                let subtotal = parseFloat(c.SubtotalNeto) || 0;
                let itbis = parseFloat(c.ITBIS) || 0;
                let total = parseFloat(c.TotalNC) || 0;

                $('#modalNC_Subtotal').text('RD$ ' + subtotal.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#modalNC_ITBIS').text('RD$ ' + itbis.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#modalNC_TotalGeneral').text('RD$ ' + total.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                // Enlace de impresión
                $('#modalNC_BtnImprimir').attr('href', `index.php?action=informeNCF&id=${c.C_DocEntry}`);

                $('#modalNCContent').fadeIn(150);
            } else {
                alert('No se pudo cargar el detalle: ' + (res.msg || 'Error desconocido'));
                modal.hide();
            }
        },
        error: function(err) {
            $('#modalLoadingSpinner').hide();
            alert('Error al conectar con el servidor.');
            modal.hide();
        }
    });
}
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>