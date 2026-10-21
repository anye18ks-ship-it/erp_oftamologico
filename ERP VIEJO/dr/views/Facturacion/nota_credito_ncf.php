<?php 
    $tiposNC   = $tiposNC   ?? [];
    $seriesNCF = $seriesNCF ?? [];
?>

<?php require_once __DIR__ . '/../Estructura/header.php'; ?>
<link rel="stylesheet" href="views/Facturacion/styles/nota_credito.css">

<!-- ============================= HELPER FORMAT ============================= -->
<script>
    function formatCurrency(value) {
        return new Intl.NumberFormat('es-DO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(value);
    }
</script>

<div class="container-fluid nc-container">

    <!-- ============================= PAGE HEADER ============================= -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon" id="pageHeaderIcon">
                <i class="bi bi-file-earmark-minus-fill" id="headerIconClass"></i>
            </div>
            <div>
                <h2 class="page-title" id="pageTitleText">Notas de Crédito NCF</h2>
                <p class="page-subtitle" id="pageSubtitleText">Emisión de notas de crédito sobre facturas existentes</p>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="index.php?page=consulta_notaNCF" class="btn-header-action btn-header-history">
                <i class="bi bi-clock-history"></i> Historial y Consulta
            </a>
            <a href="index.php?page=dashboard" class="btn-header-action btn-header-home">
                <i class="bi bi-house"></i> Inicio
            </a>
        </div>
    </div>

    <div id="alertContainer">
        <?php echo Helper::getMensaje(); ?>
    </div>

    <!-- ============================= BUSCAR FACTURA / NOTA DE CRÉDITO ============================= -->
    <div class="nc-search-card" id="searchCard">
        <div class="nc-search-card-top">
            <i class="bi bi-search fs-5"></i>
            <span class="fw-bold" id="lblSearchTitle">Buscar Factura Origen</span>
        </div>
        <div class="nc-search-body">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5 col-md-6">
                    <label class="form-label-nc" id="lblSearchInput">
                        <i class="bi bi-receipt-cutoff text-danger" id="iconSearchInput"></i>
                        Número de Factura / NCF
                        <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-danger-subtle text-danger border-danger-subtle" id="inputGroupIconWrap">
                            <i class="bi bi-upc-scan"></i>
                        </span>
                        <input type="text" id="txtNumFactura" class="form-control input-nc font-monospace fw-bold"
                               placeholder="Ej: E310000000001, E32... ó número de factura"
                               style="text-transform:uppercase;"
                               onkeydown="if(event.key==='Enter') buscarDocumentoOrigen()">
                        <button class="btn btn-danger fw-bold" type="button" id="btnBuscarFactura" onclick="buscarDocumentoOrigen()">
                            <i class="bi bi-search me-1"></i> Buscar
                        </button>
                    </div>
                    <div class="form-text text-muted mt-1" id="lblSearchHelp">Puede buscar por NCFo por número interno de factura.</div>
                </div>
                <div class="col-lg-7 col-md-6">
                    <div id="statusBusqueda" class="d-none"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================= 1. FICHA: DATOS DEL DOCUMENTO ORIGEN (READONLY) ============================= -->
    <div class="nc-origin-card" id="cardInfoFactura">
        <div class="nc-origin-card-top">
            <span class="fw-bold text-dark small" id="lblCardOriginTitle">
                <i class="bi bi-file-earmark-text-fill text-danger me-1"></i>
                Datos de la Factura Origen
            </span>
        </div>
        
        <!-- Ficha de visualización -->
        <div class="nc-origin-grid">
            
            <!-- Cliente -->
            <div class="nc-origin-item">
                <span class="nc-origin-label" id="lblOriginClientTitle">
                    <i class="bi bi-person-fill text-danger"></i> Cliente / Paciente
                </span>
                <span class="nc-origin-value" id="lblFacCardName">-</span>
                <small class="text-muted font-monospace" id="lblFacCardCode">-</small>
            </div>

            <!-- RNC / Cédula -->
            <div class="nc-origin-item">
                <span class="nc-origin-label" id="lblOriginNitTitle">
                    <i class="bi bi-card-heading text-danger"></i> RNC / Cédula
                </span>
                <span class="nc-origin-value font-monospace" id="lblFacNit">-</span>
            </div>

            <!-- NCF / Documento -->
            <div class="nc-origin-item highlight-ncf">
                <span class="nc-origin-label" id="lblOriginNCFTitle">
                    <i class="bi bi-receipt text-danger"></i> NCF Factura
                </span>
                <span class="nc-origin-value font-monospace text-danger" id="lblFacNCF">-</span>
            </div>

            <!-- Fecha Documento -->
            <div class="nc-origin-item">
                <span class="nc-origin-label" id="lblOriginDateTitle">
                    <i class="bi bi-calendar3 text-danger"></i> Fecha Documento
                </span>
                <span class="nc-origin-value font-monospace" id="lblFacFecha">-</span>
            </div>

            <!-- Total Documento -->
            <div class="nc-origin-item">
                <span class="nc-origin-label" id="lblOriginTotalTitle">
                    <i class="bi bi-currency-dollar text-danger"></i> Total Factura
                </span>
                <span class="nc-origin-value font-monospace text-dark" id="lblFacTotal">RD$ 0.00</span>
            </div>

            <!-- Saldo Disponible / Monto a Reversar -->
            <div class="nc-origin-item highlight-saldo">
                <span class="nc-origin-label" id="lblOriginSaldoTitle">
                    <i class="bi bi-wallet2 text-success"></i> Saldo Disponible
                </span>
                <span class="nc-origin-value font-monospace text-success" id="lblFacSaldoDisp">RD$ 0.00</span>
            </div>

        </div>

        <!-- Campos ocultos de control interno -->
        <input type="hidden" id="txtFacCardCode">
        <input type="hidden" id="txtFacCardName">
        <input type="hidden" id="txtFacCDocEntry">
        <input type="hidden" id="txtFacDocEntry">
        <input type="hidden" id="txtFacNumAtCard">
    </div>

    <!-- ============================= 1.B FICHA: LOTE DE FACTURAS ARS (MÚLTIPLES FACTURAS) ============================= -->
    <div class="nc-origin-card d-none" id="cardInfoLoteARS" style="border-left: 5px solid #8b5cf6;">
        <div class="nc-origin-card-top d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark small">
                <i class="bi bi-collection-fill text-purple me-1"></i>
                Lote de Facturas ARS Seleccionadas para Nota de Crédito
            </span>
            <span class="badge bg-purple text-white px-2 py-1" id="badgeTotalFacturasLote">0 Facturas</span>
        </div>
        
        <div class="p-3 bg-light rounded-3 border mb-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-6">
                    <span class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700;">Aseguradora (ARS):</span>
                    <h5 class="text-dark fw-bold mb-0" id="lblLoteARSName">-</h5>
                    <small class="text-muted font-monospace" id="lblLoteARSCode">-</small>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700;">Monto Total Reclamaciones a Anular:</span>
                    <h4 class="text-danger font-monospace fw-bold mb-0" id="lblLoteTotalMonto">RD$ 0.00</h4>
                    <small class="text-muted">Se emitirá una Nota de Crédito individual (E34) por cada factura en cola</small>
                </div>
            </div>
        </div>

        <div class="table-responsive border rounded-3 overflow-hidden">
            <table class="table table-sm align-middle mb-0" style="font-size: 0.82rem;">
                <thead class="table-light">
                    <tr>
                        <th>N° Factura / NCF</th>
                        <th>Fecha</th>
                        <th>Estado / Glosa</th>
                        <th class="text-end">Monto Factura</th>
                        <th class="text-end">Saldo Disponible</th>
                    </tr>
                </thead>
                <tbody id="tbodyFacturasLoteARS">
                    <!-- Filas generadas dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- ============================= 2. SECCIÓN: DATOS DE LA NOTA DE CRÉDITO / DÉBITO ============================= -->
    <div class="nc-form-card" id="cardFormNC">
        <div class="nc-form-card-top">
            <i class="bi bi-pencil-square fs-5"></i>
            <span class="fw-bold small" id="lblFormDocTitle">Datos de la Nota de Crédito</span>
        </div>
 
        <div class="nc-form-body">
            <div class="row g-3">

                <!-- Serie NCF: Dinámico según tipo de comprobante o locked E33 para ND -->
                <div class="col-lg-3 col-md-6">
                    <label class="form-label-nc" id="lblFormSerieTitle">
                        <i class="bi bi-receipt-cutoff text-danger"></i> Serie NCF (NC)
                    </label>
                    <div class="badge-locked-serie" id="badgeSerieNCWrap">
                        <span><i class="bi bi-shield-check text-primary me-2"></i><strong id="lblSerieNombre">E34</strong> - <span id="lblSerieDesc">NOTA DE CRÉDITO ELECTRÓNICA</span></span>
                     </div>
                    <input type="hidden" id="cboSerie" value="E34">
                </div>

                <!-- Tipo NC (Oculto o bloqueado en modo ND) -->
                <div class="col-lg-3 col-md-6" id="wrapTipoNC">
                    <label class="form-label-nc" for="cboTipoNC">
                        <i class="bi bi-tag text-danger"></i> Tipo de Nota de Crédito <span class="text-danger">*</span>
                    </label>
                    <select id="cboTipoNC" class="form-select input-nc">
                        <?php foreach ($tiposNC as $t): 
                            $cVal = htmlspecialchars($t['code'] ?? $t['Code'] ?? '');
                        ?>
                            <option value="<?= $cVal ?>"><?= $cVal ? ($cVal . ' - ') : '' ?><?= htmlspecialchars($t['Name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Fecha de emisión -->
                <div class="col-lg-2 col-md-4">
                    <label class="form-label-nc" for="txtDocDateNC" id="lblFormDateTitle">
                        <i class="bi bi-calendar-event text-danger"></i> Fecha Emisión
                    </label>
                    <input type="date" id="txtDocDateNC" class="form-control input-nc" value="<?php echo date('Y-m-d'); ?>">
                </div>

                <!-- Motivo -->
                <div class="col-lg-4 col-md-8" id="wrapMotivo">
                    <label class="form-label-nc" for="txtMotivo">
                        <i class="bi bi-chat-text text-danger"></i> Motivo / Justificación <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="txtMotivo" class="form-control input-nc"
                           placeholder="Ej: Devolución de medicamentos, Corrección de factura...">
                </div>

                <!-- Switch de Devolución de Efectivo en Caja Activa (Solo en modo NC) -->
                <div class="col-12 mt-3" id="wrapDevolverEfectivo">
                    <div class="cash-refund-box d-flex align-items-center justify-content-between flex-wrap gap-3" id="boxDevolverEfectivo">
                        <div class="d-flex align-items-center gap-3">
                            <div class="nc-refund-icon-wrapper" id="refundIconWrapper">
                                <i class="bi bi-cash-coin fs-4" id="refundIcon"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <strong class="text-dark" id="refundTitle">Devolución de Efectivo al Paciente</strong>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" id="refundBadge">Condicionado</span>
                                </div>
                                <small class="text-muted d-block" id="refundHelp">
                                    Determinado automáticamente según el tipo de nota de crédito seleccionado.
                                </small>
                            </div>
                        </div>
                        <div class="form-check form-switch fs-5 m-0 d-flex align-items-center">
                            <input class="form-check-input" type="checkbox" role="switch" id="chkDevolverEfectivo" <?php echo !empty($cajaActiva) ? '' : 'disabled'; ?>>
                            <label class="form-check-label fs-6 ms-2" for="chkDevolverEfectivo" id="lblDevolverEfectivo">
                                <?php echo !empty($cajaActiva) ? 'Sí, egresar de caja activa' : 'Sin caja activa (Solo contable)'; ?>
                            </label>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ============================= 3. DETALLE ARTÍCULOS ============================= -->
    <div class="nc-detail-card" id="cardDetalle">
        <div class="nc-detail-card-header">
            <span class="fw-bold" id="lblDetailCardTitle"><i class="bi bi-list-ul me-2"></i>Artículos a Incluir en la Nota de Crédito</span>
            <button type="button" class="btn btn-light btn-sm fw-bold rounded-3 text-danger" id="btnAgregarArticuloExtra" onclick="abrirModalArticuloNC()">
                <i class="bi bi-plus-circle-fill me-1"></i> Agregar Artículo Adicional
            </button>
        </div>

        <div class="nc-table-wrapper">
            <table class="table-nc" id="tablaDetalleNC">
                <thead>
                    <tr>
                        <th style="width:10%;">Código</th>
                        <th style="width:30%;">Descripción</th>
                        <th style="width:9%;" class="text-center" id="thCantOrig">Cant. Orig.</th>
                        <th style="width:10%;" class="text-center" id="thCantDoc">Cant. NC</th>
                        <th style="width:11%;" class="text-end" title="Precio unitario facturado al paciente a acreditar">Precio Facturado</th>
                        <th style="width:8%;" class="text-center">% Desc</th>
                        <th style="width:7%;" class="text-center">ITBIS %</th>
                        <th style="width:12%;" class="text-end">Total Línea</th>
                        <th style="width:4%;" class="text-center"></th>
                    </tr>
                </thead>
                <tbody id="tbodyDetalleNC">
                    <tr id="rowEmptyNC">
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-file-earmark-minus fs-1 d-block mb-2 text-danger opacity-40"></i>
                            <span class="fw-semibold">Busque un documento origen para cargar sus artículos</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer y Totales -->
        <div class="nc-footer-section">
            <div class="row align-items-center g-3">
                <div class="col-lg-7">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex align-items-center gap-2 text-muted small" id="lblFooterAdvice">
                            <i class="bi bi-info-circle text-danger"></i>
                            <span>Modifique las cantidades o elimine artículos que no apliquen. El documento <strong>no puede superar el saldo disponible</strong>.</span>
                        </div>
                        
                        <!-- Alerta cuando excede saldo -->
                        <div id="avisoExcede" class="d-none" style="background:#fee2e2;border:1.5px solid #fca5a5;border-radius:8px;padding:8px 12px;">
                            <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>
                            <span class="text-danger fw-bold" id="txtMsgExcede">El monto excede el límite permitido.</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="totals-summary-box-nc">
                        <div class="total-row-nc">
                            <span class="total-row-nc-label">Subtotal Neto:</span>
                            <span class="total-row-nc-val" id="lblNcSubtotal">RD$ 0.00</span>
                        </div>
                        <div class="total-row-nc">
                            <span class="total-row-nc-label">Descuento:</span>
                            <span class="total-row-nc-val text-danger" id="lblNcDescuento">RD$ 0.00</span>
                        </div>
                        <div class="total-row-nc">
                            <span class="total-row-nc-label">ITBIS:</span>
                            <span class="total-row-nc-val" id="lblNcImpuesto">RD$ 0.00</span>
                        </div>
                        <div class="total-row-nc total-general-row-nc">
                            <span class="total-general-label-nc" id="lblTotalDocTitle">TOTAL NC:</span>
                            <span class="total-general-val-nc" id="lblNcTotal">RD$ 0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button type="button" class="btn-process-nc" id="btnProcesarNC" onclick="procesarNC()">
                    <i class="bi bi-check2-circle fs-5"></i> EMITIR NOTA DE CRÉDITO
                </button>
            </div>
        </div>
    </div>

</div>


<!-- ============================= MODAL: BUSCAR ARTÍCULO (NC) ============================= -->
<div class="modal fade" id="modalArticuloNC" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
            <div class="modal-nc-header">
                <h5 class="modal-title fw-bold mb-0">
                    <i class="bi bi-box-seam-fill me-2"></i> Catálogo de Artículos &amp; Servicios
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="modal-search-bar">
                    <i class="bi bi-search"></i>
                    <input type="text" id="txtSearchArticuloNC" placeholder="Filtrar por Código o Nombre del Artículo..." oninput="filtrarArticulosNC()">
                </div>
                <div style="max-height:48vh;overflow-y:auto;border:1px solid #e2e8f0;border-radius:10px;">
                    <table class="table table-hover align-middle mb-0 table-sm">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th class="text-end">Precio</th>
                                <th class="text-center">ITBIS %</th>
                                <th class="text-center">Almacén</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyArticulosNC">
                            <tr><td colspan="6" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-pagination-bar" id="paginacionArticulosNC">
                    <span id="lblInfoPaginaArticuloNC">Mostrando registros</span>
                    <div class="d-flex gap-1">
                        <button type="button" class="modal-page-btn" id="btnPrevArticuloNC" onclick="cambiarPaginaArticuloNC(-1)">&laquo; Anterior</button>
                        <button type="button" class="modal-page-btn" id="btnNextArticuloNC" onclick="cambiarPaginaArticuloNC(1)">Siguiente &raquo;</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL PROGRESO COLA DE NOTAS DE CRÉDITO LOTE ARS
     ============================================================ -->
<div class="modal fade" id="modalProgresoLoteNC" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="spinner-border text-danger mb-3" style="width: 3.5rem; height: 3.5rem;" role="status">
                    <span class="visually-hidden">Procesando...</span>
                </div>
                <h5 class="fw-bold text-dark mb-1" id="progreso_lote_titulo">Emitiendo Notas de Crédito en Cola</h5>
                <p class="text-muted small mb-3" id="progreso_lote_detalle">Procesando factura 1 de N...</p>

                <div class="progress mb-2" style="height: 12px; border-radius: 10px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" id="progreso_lote_bar" role="progressbar" style="width: 0%;"></div>
                </div>
                <small class="text-muted font-monospace" id="progreso_lote_porcentaje">0%</small>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL ÉXITO FINAL LOTE DE NOTAS DE CRÉDITO ARS
     ============================================================ -->
<div class="modal fade" id="modalExitoLoteNC" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-success text-white p-3 rounded-top-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Notas de Crédito Emitidas Exitosamente</h5>
                        <small class="text-white-50">Lote de comprobantes electrónicos (E34) procesado en cola</small>
                    </div>
                </div>
            </div>

            <div class="modal-body p-4">
                <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-shield-check fs-4 text-success"></i>
                    <div>
                        Se han emitido satisfactoriamente las Notas de Crédito para todas las facturas del lote seleccionado.
                    </div>
                </div>

                <div class="table-responsive border rounded-3 overflow-hidden mb-3">
                    <table class="table table-sm align-middle mb-0" style="font-size: 0.84rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Factura Origen</th>
                                <th>NCF Nota de Crédito</th>
                                <th class="text-end">Monto NC</th>
                                <th class="text-center">Comprobante</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyResultadoLoteNC">
                            <!-- Filas generadas dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer bg-light p-3 rounded-bottom-4 justify-content-between">
                <a href="index.php?page=consulta_aprobaciones" class="btn btn-outline-secondary fw-bold rounded-3 px-3">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Consulta de Aprobaciones
                </a>
                <button type="button" class="btn btn-success fw-bold rounded-3 px-4" onclick="location.href='index.php?page=consulta_notaNCF'">
                    <i class="bi bi-clock-history me-1"></i> Ir al Historial de Notas de Crédito
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="nc-toast-container">
    <div id="ncToast" class="toast align-items-center text-bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="ncToastBody">Mensaje</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
// =======================================================================
// ESTADO GLOBAL
// =======================================================================
const tieneCajaActiva = <?php echo !empty($cajaActiva) ? 'true' : 'false'; ?>;
let detalleNC       = [];   // líneas del documento a emitir
let facturaOrigen   = null; // cabecera de la factura cargada
let saldoDisponible = 0;   // saldo máximo permitido

let modoLoteARS     = false;
let facturasLoteARS = []; // array de facturas { factura, lineas, saldoDisponible }

let listaArtNCGlobal   = [];
let listaArtNCFiltrada = [];
let paginaArtNC = 1;
const porPaginaArtNC = 12;

function limpiarFormularioCompleto() {
    facturaOrigen = null;
    saldoDisponible = 0;
    detalleNC = [];
    $('#cardInfoFactura').removeClass('visible');
    $('#cardFormNC').removeClass('visible');
    $('#cardDetalle').removeClass('visible');
    $('#cardInfoLoteARS').addClass('d-none').removeClass('visible');
    $('#txtNumFactura').val('');
    $('#statusBusqueda').addClass('d-none').html('');
    $('#txtMotivo').val('');
    renderTablaNC();
}

// =======================================================================
// CONDICIONAMIENTO REACTIVO DE DEVOLUCIÓN DE EFECTIVO EN CAJA (NC)
// =======================================================================
function evaluarTipoNC() {
    const tipoVal = ($('#cboTipoNC').val() || '').trim();
    const tipoText = ($('#cboTipoNC option:selected').text() || '').toUpperCase();

    const codigosDevolucion = ['06', '07', '13', '14', '16', '20', '21', '22', '23', '25', '38'];
    const keywords = ['DEVOLUCION', 'DEVOLVER', 'CAMBIO DE PRODUCTO', 'NO DECEADO', 'CANCELACION', 'NO HAY EXISTENCIA', 'NO DESPACHADO', 'DEFECTO', 'DEJAR EL ARTICULO', 'REEMBOLSO'];

    let esDevolucion = codigosDevolucion.includes(tipoVal);
    if (!esDevolucion) {
        esDevolucion = keywords.some(k => tipoText.includes(k));
    }

    const $box   = $('#boxDevolverEfectivo');
    const $chk   = $('#chkDevolverEfectivo');
    const $lbl   = $('#lblDevolverEfectivo');
    const $icon  = $('#refundIcon');
    const $title = $('#refundTitle');
    const $badge = $('#refundBadge');
    const $help  = $('#refundHelp');

    $box.removeClass('active-refund inactive-refund warning-refund');

    if (esDevolucion) {
        if (tieneCajaActiva) {
            $box.addClass('active-refund');
            $chk.prop('disabled', false).prop('checked', true);
            $lbl.html('<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Sí, egresar de caja activa</span>');
            $icon.attr('class', 'bi bi-cash-coin text-success fs-3');
            $title.text('Devolución de Efectivo al Paciente (Caja Activa)');
            $badge.attr('class', 'badge bg-success-subtle text-success border border-success-subtle').text('Devolución de Efectivo');
            $help.text('Este motivo involucra devolución física de artículo/reembolso al paciente. Al emitirse la NC, se registrará el egreso en la caja chica operativa.');
        } else {
            $box.addClass('warning-refund');
            $chk.prop('disabled', true).prop('checked', false);
            $lbl.html('<span class="text-warning-emphasis fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>Sin caja activa</span>');
            $icon.attr('class', 'bi bi-exclamation-diamond text-warning fs-3');
            $title.text('Devolución física sin turno de caja');
            $badge.attr('class', 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle').text('Sin Caja Abierta');
            $help.text('El motivo es por devolución de artículo, pero no hay un turno de caja abierto por este usuario. Se registrará únicamente a nivel contable.');
        }
    } else {
        $box.addClass('inactive-refund');
        $chk.prop('disabled', true).prop('checked', false);
        $lbl.html('<span class="text-secondary fw-semibold"><i class="bi bi-slash-circle me-1"></i>No aplica egreso de dinero</span>');
        $icon.attr('class', 'bi bi-file-earmark-diff text-muted fs-3');
        $title.text('Ajuste Administrativo / Contable');
        $badge.attr('class', 'badge bg-secondary-subtle text-secondary border border-secondary-subtle').text('Ajuste Contable');
        $help.text('Este motivo es para corrección de datos o ajuste fiscal. No genera egreso ni descuenta dinero físico de la caja.');
    }
}

$(document).ready(function() {
    evaluarTipoNC();

    $('#cboTipoNC').on('change', function() {
        evaluarTipoNC();
    });

    $('#chkDevolverEfectivo').on('change', function() {
        if ($(this).is(':checked')) {
            $('#lblDevolverEfectivo').html('<span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Sí, egresar de caja activa</span>');
        } else {
            $('#lblDevolverEfectivo').html('<span class="text-secondary fw-semibold"><i class="bi bi-x-circle me-1"></i>No egresar dinero (Solo nota de crédito)</span>');
        }
    });

    // Detectar si venimos con parámetros de lote ARS en la URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('modo') === 'lote_ars' && urlParams.get('facturas')) {
        modoLoteARS = true;
        iniciarCargaLoteARS(urlParams.get('facturas'));
    }
});

// Auto-dismiss de alertas
setTimeout(function() {
    document.querySelectorAll('#alertContainer .alert').forEach(el => {
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-8px)';
        setTimeout(() => el.remove(), 600);
    });
}, 5000);

function showNcToast(msg, tipo = 'dark') {
    const el = document.getElementById('ncToast');
    const body = document.getElementById('ncToastBody');
    el.className = `toast align-items-center text-bg-${tipo} border-0 shadow-lg`;
    body.innerText = msg;
    new bootstrap.Toast(el, { delay: 4500 }).show();
}

// =======================================================================
// BUSCADOR DE FACTURA ORIGEN
// =======================================================================
function buscarDocumentoOrigen() {
    buscarFactura();
}

function buscarFactura() {
    const num = $('#txtNumFactura').val().trim().toUpperCase();
    if (!num) {
        showNcToast('Ingrese el número de factura o NCF.', 'warning');
        $('#txtNumFactura').focus();
        return;
    }

    const btn = $('#btnBuscarFactura');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Buscando...');
    $('#statusBusqueda').html('').removeClass('d-none').addClass('d-none');

    $.ajax({
        url: 'index.php?action=buscar_factura_nc',
        type: 'GET',
        data: { num: num },
        dataType: 'json',
        success: function(res) {
            btn.prop('disabled', false).html('<i class="bi bi-search me-1"></i> Buscar');
            if (!res.success) {
                showNcToast(res.message || 'No se encontró la factura.', 'danger');
                $('#statusBusqueda').removeClass('d-none')
                    .html(`<div class="alert alert-danger py-2 mb-0"><i class="bi bi-exclamation-triangle me-1"></i>${res.message}</div>`);
                return;
            }
            cargarFacturaEnPantalla(res);
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="bi bi-search me-1"></i> Buscar');
            let msg = 'Error de comunicación.';
            try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e){}
            showNcToast(msg, 'danger');
        }
    });
}

// Helper para determinar y ajustar visualmente la serie de NC según la factura
function ajustarSerieNC(ncf) {
    const ncfStr = (ncf || '').trim().toUpperCase();
    const esElectronica = ncfStr.startsWith('E');
    const serie = esElectronica ? 'E34' : 'B04';
    const desc = esElectronica ? 'NOTA DE CRÉDITO ELECTRÓNICA' : 'NOTA DE CRÉDITO CON VALOR FISCAL';
    $('#cboSerie').val(serie);
    $('#lblSerieNombre').text(serie);
    $('#lblSerieDesc').text(desc);
}

function cargarFacturaEnPantalla(res) {
    facturaOrigen   = res.factura;
    saldoDisponible = parseFloat(res.saldoDisponible) || 0;

    // Ajustar serie NC (E34 / B04)
    ajustarSerieNC(facturaOrigen.U_FACNUM || facturaOrigen.NumAtCard);

    // Ficha de Factura Origen (Readonly)
    $('#lblFacCardName').text(facturaOrigen.CardName || '-');
    $('#lblFacCardCode').text(facturaOrigen.CardCode ? '(' + facturaOrigen.CardCode + ')' : '');
    $('#lblFacNit').text(facturaOrigen.U_FacNit || 'Sin RNC / Cédula');
    $('#lblFacNCF').text(facturaOrigen.U_FACNUM || facturaOrigen.NumAtCard || (facturaOrigen.DocNum ? 'DocNum: ' + facturaOrigen.DocNum : 'ID: ' + facturaOrigen.C_DocEntry));
    // No se muestra DocNum para evitar ruido (#null en facturas locales POS)
    $('#lblFacFecha').text(facturaOrigen.DocDateFmt || '-');
    $('#lblFacTotal').text('RD$ ' + parseFloat(facturaOrigen.DocTotal).toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    $('#lblFacSaldoDisp').text('RD$ ' + saldoDisponible.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

    // Hidden inputs para control interno
    $('#txtFacCardCode').val(facturaOrigen.CardCode);
    $('#txtFacCardName').val(facturaOrigen.CardName);
    $('#txtFacCDocEntry').val(facturaOrigen.C_DocEntry);
    $('#txtFacDocEntry').val(facturaOrigen.DocEntry);
    $('#txtFacNumAtCard').val(facturaOrigen.NumAtCard);

    // Mostrar tarjetas
    $('#cardInfoFactura').addClass('visible');
    $('#cardFormNC').addClass('visible');
    $('#cardDetalle').addClass('visible');

    // Cargar líneas de la factura como líneas iniciales de la NC
    detalleNC = res.lineas
        .filter(l => parseFloat(l.QtyDisponible) > 0)
        .map(l => ({
            LineNum:        parseInt(l.LineNum),
            BaseLine:       parseInt(l.LineNum),
            BaseEntry:      parseInt(facturaOrigen.C_DocEntry),
            ItemCode:       l.ItemCode,
            Dscription:     l.Dscription,
            Quantity:       parseFloat(l.QtyDisponible),
            QtyOrig:        parseFloat(l.Quantity),
            QtyDisponible:  parseFloat(l.QtyDisponible),
            Price:          parseFloat(l.Price),
            PriceBefDi:     parseFloat(l.PriceBefDi || l.Price),
            DiscPrcnt:      parseFloat(l.DiscPrcnt) || 0,
            VatPrcnt:       parseFloat(l.VatPrcnt) || 0,
            TaxCode:        l.TaxCode || '',
            WhsCode:        l.WhsCode || '01',
            esDeFactura:    true,
         }));

    renderTablaNC();
    showNcToast('Factura cargada. Ajuste las cantidades según corresponda.', 'success');
    $([document.documentElement, document.body]).animate({ scrollTop: $('#cardInfoFactura').offset().top - 80 }, 500);
}

// =======================================================================
// RENDERIZADO TABLA NC
// =======================================================================
function renderTablaNC() {
    const tbody = $('#tbodyDetalleNC');

    if (detalleNC.length === 0) {
        tbody.html(`<tr id="rowEmptyNC">
            <td colspan="9" class="text-center py-5 text-muted">
                <i class="bi bi-file-earmark-minus fs-1 d-block mb-2 text-danger opacity-40"></i>
                <span class="fw-semibold">No hay artículos en el documento</span>
            </td>
        </tr>`);
        actualizarTotalesNC();
        return;
    }

    let html = '';
    detalleNC.forEach((l, i) => {
        const discMonto = (l.Quantity * l.Price) * (l.DiscPrcnt / 100);
        const lineNeto  = (l.Quantity * l.Price) - discMonto;
        const lineTotal = lineNeto + lineNeto * (l.VatPrcnt / 100);

        const qtyMax   = l.esDeFactura ? l.QtyDisponible : 9999;
        const esEntero = l.QtyOrig % 1 === 0;
        const inputStep = esEntero ? '1' : '0.01';
        const inputMin  = esEntero ? '1' : '0.01';
        const inputVal  = esEntero ? l.Quantity.toFixed(0) : l.Quantity.toFixed(2);
        const qtyLabel  = l.esDeFactura
            ? `<small class="text-muted d-block" style="font-size:0.7rem;">Disp: ${parseFloat(l.QtyDisponible).toFixed(esEntero ? 0 : 2)}</small>`
            : '';

        const btnDeleteHtml = `<button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 rounded-2" title="Eliminar" onclick="eliminarFilaNC(${i})"><i class="bi bi-trash"></i></button>`;

        html += `<tr id="rowNC_${i}">
            <td class="font-monospace fw-bold">${l.ItemCode}</td>
            <td class="fw-semibold text-dark">${l.Dscription}</td>
            <td class="text-center font-monospace">${l.esDeFactura ? l.QtyOrig.toFixed(2) : '—'}</td>
            <td class="text-center">
                <input type="number" min="${inputMin}" step="${inputStep}" max="${qtyMax}"
                       id="inputQtyNC_${i}"
                       class="input-table-qty-nc font-monospace"
                       value="${inputVal}"
                       oninput="actualizarCantidadNC(${i}, this)"
                       onblur="validarCantidadNC(${i}, this)">
                ${qtyLabel}
            </td>
            <td class="text-end font-monospace">
                <div class="fw-bold text-dark">RD$ ${formatCurrency(l.Price)}</div>
                ${(l.PriceBefDi && l.PriceBefDi > l.Price + 0.01) ? `<div class="text-muted" style="font-size:0.70rem;" title="Precio sin cobertura">Catálogo: RD$ ${formatCurrency(l.PriceBefDi)}</div>` : ''}
            </td>
            <td class="text-center">
                <input type="number" min="0" max="100" step="1"
                       id="inputDiscNC_${i}"
                       class="input-table-qty-nc font-monospace"
                       style="width:60px;"
                       value="${l.DiscPrcnt.toFixed(0)}"
                       oninput="actualizarDescuentoNC(${i}, this)"
                       onblur="validarDescuentoNC(${i}, this)">
            </td>
            <td class="text-center font-monospace">${l.VatPrcnt.toFixed(2)}%</td>
            <td class="fw-bold text-end font-monospace" id="lblLineTotalNC_${i}">RD$ ${formatCurrency(lineTotal)}</td>
            <td class="text-center">
                ${btnDeleteHtml}
            </td>
        </tr>`;
    });

    tbody.html(html);
    actualizarTotalesNC();
}

function actualizarTotalesNC() {
    let subtotal = 0, descTotal = 0, vatTotal = 0;

    detalleNC.forEach(l => {
        const discMonto = (l.Quantity * l.Price) * (l.DiscPrcnt / 100);
        const lineNeto  = (l.Quantity * l.Price) - discMonto;
        const lineVat   = lineNeto * (l.VatPrcnt / 100);
        subtotal += lineNeto;
        descTotal += discMonto;
        vatTotal  += lineVat;
    });

    const totalNC = subtotal + vatTotal;
    const fmt = n => 'RD$ ' + n.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    $('#lblNcSubtotal').text(fmt(subtotal));
    $('#lblNcDescuento').text(fmt(descTotal));
    $('#lblNcImpuesto').text(fmt(vatTotal));
    $('#lblNcTotal').text(fmt(totalNC));

    const saldo = saldoDisponible;
    if (saldo > 0) {
        if (totalNC > saldo + 0.01) {
            $('#avisoExcede').removeClass('d-none');
            $('#txtMsgExcede').text(
                `El total de la NC (${fmt(totalNC)}) excede el saldo disponible (${fmt(saldo)}).`
            );
            $('#lblNcTotal').addClass('text-danger');
        } else {
            $('#avisoExcede').addClass('d-none');
            $('#lblNcTotal').removeClass('text-danger');
        }
    } else {
        $('#avisoExcede').addClass('d-none');
    }
}

function actualizarCantidadNC(idx, inputEl) {
    let val = parseFloat(inputEl.value) || 0;
    const l = detalleNC[idx];

    if (val < 0) val = 0;
    detalleNC[idx].Quantity = val;

    const discMonto = (val * l.Price) * (l.DiscPrcnt / 100);
    const lineNeto  = (val * l.Price) - discMonto;
    const lineTotal = lineNeto + lineNeto * (l.VatPrcnt / 100);
    $(`#lblLineTotalNC_${idx}`).text('RD$ ' + formatCurrency(lineTotal));

    actualizarTotalesNC();
}

function validarCantidadNC(idx, inputEl) {
    let qty = parseFloat(inputEl.value) || 0;
    const l = detalleNC[idx];
    const esEntero = l.QtyOrig % 1 === 0;

    if (qty <= 0) {
        qty = esEntero ? 1 : 0.01;
    }

    if (esEntero) {
        qty = Math.round(qty);
        if (qty < 1) qty = 1;
    }

    if (l.esDeFactura && qty > l.QtyDisponible + 0.001) {
        qty = l.QtyDisponible;
        showNcToast(`La cantidad máxima disponible para este artículo es ${l.QtyDisponible.toFixed(esEntero ? 0 : 2)}.`, 'warning');
    }

    detalleNC[idx].Quantity = qty;
    inputEl.value = esEntero ? qty.toFixed(0) : qty.toFixed(2);

    const discMonto = (qty * l.Price) * (l.DiscPrcnt / 100);
    const lineNeto  = (qty * l.Price) - discMonto;
    const lineTotal = lineNeto + lineNeto * (l.VatPrcnt / 100);
    $(`#lblLineTotalNC_${idx}`).text('RD$ ' + formatCurrency(lineTotal));

    actualizarTotalesNC();
}

function actualizarDescuentoNC(idx, inputEl) {
    let disc = parseFloat(inputEl.value) || 0;
    if (disc < 0) disc = 0;
    if (disc > 100) disc = 100;
    detalleNC[idx].DiscPrcnt = disc;

    const l = detalleNC[idx];
    const discMonto = (l.Quantity * l.Price) * (disc / 100);
    const lineNeto  = (l.Quantity * l.Price) - discMonto;
    const lineTotal = lineNeto + lineNeto * (l.VatPrcnt / 100);
    $(`#lblLineTotalNC_${idx}`).text('RD$ ' + formatCurrency(lineTotal));

    actualizarTotalesNC();
}

function validarDescuentoNC(idx, inputEl) {
    let disc = parseFloat(inputEl.value) || 0;
    if (disc < 0) disc = 0;
    if (disc > 100) disc = 100;
    detalleNC[idx].DiscPrcnt = disc;
    inputEl.value = disc.toFixed(0);
    actualizarTotalesNC();
}

function eliminarFilaNC(idx) {
    detalleNC.splice(idx, 1);
    renderTablaNC();
}

// =======================================================================
// MODAL ARTÍCULO NC (artículos extra no de la factura)
// =======================================================================
function abrirModalArticuloNC() {
    if (!facturaOrigen) {
        showNcToast('Primero busque y cargue una factura origen.', 'warning');
        return;
    }
    $('#txtSearchArticuloNC').val('');
    new bootstrap.Modal(document.getElementById('modalArticuloNC')).show();
    cargarArticulosNC('');
    setTimeout(() => $('#txtSearchArticuloNC').focus(), 400);
}

function cargarArticulosNC(term) {
    $('#tbodyArticulosNC').html('<tr><td colspan="6" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Consultando catálogo...</td></tr>');
    $.ajax({
        url: 'index.php?action=buscar_articulo_nc',
        type: 'GET',
        data: { q: term },
        dataType: 'json',
        success: function(data) {
            listaArtNCGlobal   = data || [];
            listaArtNCFiltrada = [...listaArtNCGlobal];
            paginaArtNC = 1;
            renderPaginacionArtNC();
        },
        error: function() {
            $('#tbodyArticulosNC').html('<tr><td colspan="6" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Error al cargar catálogo.</td></tr>');
        }
    });
}

function filtrarArticulosNC() {
    const q = $('#txtSearchArticuloNC').val().trim().toLowerCase();
    listaArtNCFiltrada = q
        ? listaArtNCGlobal.filter(a => (a.ItemName && a.ItemName.toLowerCase().includes(q)) || (a.ItemCode && a.ItemCode.toLowerCase().includes(q)))
        : [...listaArtNCGlobal];
    paginaArtNC = 1;
    renderPaginacionArtNC();
}

function renderPaginacionArtNC() {
    const tbody = $('#tbodyArticulosNC');
    if (listaArtNCFiltrada.length === 0) {
        tbody.html('<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron artículos.</td></tr>');
        $('#lblInfoPaginaArticuloNC').text('0 registros');
        $('#btnPrevArticuloNC, #btnNextArticuloNC').prop('disabled', true);
        return;
    }
    const total = listaArtNCFiltrada.length;
    const totalPags = Math.ceil(total / porPaginaArtNC);
    const inicio = (paginaArtNC - 1) * porPaginaArtNC;
    const fin = Math.min(inicio + porPaginaArtNC, total);
    const slice = listaArtNCFiltrada.slice(inicio, fin);

    let html = '';
    slice.forEach((a) => {
        html += `<tr>
            <td class="font-monospace fw-bold">${a.ItemCode}</td>
            <td class="fw-bold">${a.ItemName}</td>
            <td class="text-end font-monospace">RD$ ${parseFloat(a.PriceBefDi).toFixed(2)}</td>
            <td class="text-center font-monospace">${parseFloat(a.VatPrcnt).toFixed(2)}%</td>
            <td class="text-center"><span class="badge bg-light text-dark border">${a.DfltWH || '01'}</span></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger py-1 px-3 rounded-3"
                    onclick="agregarArticuloExtraNC('${a.ItemCode}', \`${a.ItemName.replace(/`/g,'\\`')}\`, ${a.PriceBefDi}, ${a.VatPrcnt}, '${a.TaxCode || ''}', '${a.DfltWH || '01'}')">
                    <i class="bi bi-plus-lg"></i> Agregar
                </button>
            </td>
        </tr>`;
    });

    tbody.html(html);
    $('#lblInfoPaginaArticuloNC').text(`Mostrando ${inicio + 1}-${fin} de ${total} (Pág. ${paginaArtNC}/${totalPags})`);
    $('#btnPrevArticuloNC').prop('disabled', paginaArtNC <= 1);
    $('#btnNextArticuloNC').prop('disabled', paginaArtNC >= totalPags);
}

function cambiarPaginaArticuloNC(delta) {
    const totalPags = Math.ceil(listaArtNCFiltrada.length / porPaginaArtNC);
    paginaArtNC = Math.max(1, Math.min(totalPags, paginaArtNC + delta));
    renderPaginacionArtNC();
}

function agregarArticuloExtraNC(code, name, price, vatPrcnt, taxCode, whsCode) {
    detalleNC.push({
        LineNum:       detalleNC.length,
        BaseLine:      detalleNC.length,
        BaseEntry:     parseInt($('#txtFacCDocEntry').val()) || 0,
        ItemCode:      code,
        Dscription:    name,
        Quantity:      1,
        QtyOrig:       0,
        QtyDisponible: 9999,
        Price:         parseFloat(price) || 0,
        PriceBefDi:    parseFloat(price) || 0,
        DiscPrcnt:     0,
        VatPrcnt:      parseFloat(vatPrcnt) || 0,
        TaxCode:       taxCode || '',
        WhsCode:       whsCode || '01',
        esDeFactura:   false,
    });
    renderTablaNC();
    showNcToast(`Artículo añadido: ${code}`, 'success');
    bootstrap.Modal.getInstance(document.getElementById('modalArticuloNC')).hide();
}

// =======================================================================
// CARGA DE LOTE DE FACTURAS ARS (MÚLTIPLES FACTURAS)
// =======================================================================
async function iniciarCargaLoteARS(facturasParam) {
    const ids = facturasParam.split(',').map(s => s.trim()).filter(s => s !== '');
    if (ids.length === 0) return;

    $('.nc-search-card').addClass('d-none');
    $('#cardInfoLoteARS').removeClass('d-none').addClass('visible');
    $('#lblLoteARSName').text('Consultando lote de facturas...');
    $('#tbodyFacturasLoteARS').html('<tr><td colspan="5" class="text-center py-3 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Cargando facturas del lote...</td></tr>');

    facturasLoteARS = [];
    detalleNC = [];
    let granTotalLote = 0;
    let cardCodeARS = '';
    let cardNameARS = '';
    let serieTipoLote = '';
    let facturasIncompatibles = [];

    for (let id of ids) {
        try {
            const res = await $.ajax({
                url: 'index.php?action=buscar_factura_nc',
                type: 'GET',
                data: { num: id },
                dataType: 'json'
            });

            if (res.success && res.factura) {
                const fac = res.factura;
                const facNCF = (fac.U_FACNUM || fac.NumAtCard || '').trim().toUpperCase();
                const esElec = facNCF.startsWith('E');
                const serieFac = esElec ? 'E34' : 'b04';

                if (!cardCodeARS) {
                    cardCodeARS = fac.CardCode;
                    cardNameARS = fac.CardName;
                    serieTipoLote = serieFac;
                } else if (cardCodeARS !== fac.CardCode || serieTipoLote !== serieFac) {
                    facturasIncompatibles.push(fac);
                    continue;
                }

                facturasLoteARS.push(res);
                granTotalLote += parseFloat(fac.DocTotal || 0);

                const lineasFac = res.lineas
                    .filter(l => parseFloat(l.QtyDisponible) > 0)
                    .map(l => ({
                        LineNum:        parseInt(l.LineNum),
                        BaseLine:       parseInt(l.LineNum),
                        BaseEntry:      parseInt(fac.C_DocEntry),
                        BaseDocNum:     fac.U_FACNUM || fac.NumAtCard || (fac.DocNum ? '#' + fac.DocNum : 'ID: ' + fac.C_DocEntry),
                        ItemCode:       l.ItemCode,
                        Dscription:     l.Dscription,
                        Quantity:       parseFloat(l.QtyDisponible),
                        QtyOrig:        parseFloat(l.Quantity),
                        QtyDisponible:  parseFloat(l.QtyDisponible),
                        Price:          parseFloat(l.Price),
                        PriceBefDi:     parseFloat(l.PriceBefDi || l.Price),
                        DiscPrcnt:      parseFloat(l.DiscPrcnt) || 0,
                        VatPrcnt:       parseFloat(l.VatPrcnt) || 0,
                        TaxCode:        l.TaxCode || '',
                        WhsCode:        l.WhsCode || '01',
                        esDeFactura:    true,
                    }));
                detalleNC = detalleNC.concat(lineasFac);
            }
        } catch (e) {
            console.error('Error al cargar factura ID:', id, e);
        }
    }

    if (facturasLoteARS.length === 0) {
        alert('No se pudo cargar ninguna de las facturas solicitadas.');
        location.href = 'index.php?page=consulta_aprobaciones';
        return;
    }

    ajustarSerieNC(facturasLoteARS[0].factura.U_FACNUM || facturasLoteARS[0].factura.NumAtCard);

    if (facturasIncompatibles.length > 0) {
        const nombresIncomp = facturasIncompatibles.map(f => `${f.U_FACNUM || f.NumAtCard || (f.DocNum ? '#' + f.DocNum : 'ID: ' + f.C_DocEntry)} (${f.CardName})`).join('\n- ');
        alert(`Aviso de Agrupación Fiscal / Incompatibilidad:\n\nLas siguientes facturas pertenecen a otra ARS o a un tipo fiscal diferente y deben procesarse en una Nota de Crédito separada:\n- ${nombresIncomp}\n\nSe continuará con el grupo de: ${cardNameARS} (${serieTipoLote.toUpperCase()}).`);
    }

    facturaOrigen = facturasLoteARS[0].factura;
    saldoDisponible = granTotalLote;

    $('#lblLoteARSName').text(cardNameARS);
    $('#lblLoteARSCode').text('Código ARS: ' + cardCodeARS);
    $('#lblLoteTotalMonto').text('RD$ ' + granTotalLote.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    $('#badgeTotalFacturasLote').text(facturasLoteARS.length + ' Facturas en Lote (' + (serieTipoLote === 'E34' ? 'E34' : 'B04') + ')');

    let tbodyFacHtml = '';
    facturasLoteARS.forEach(item => {
        const f = item.factura;
        const ncfMostrar = f.U_FACNUM || f.NumAtCard || (f.DocNum ? '#' + f.DocNum : 'ID: ' + f.C_DocEntry);
        tbodyFacHtml += `<tr>
            <td class="font-monospace fw-bold text-danger">${ncfMostrar}</td>
            <td>${f.DocDateFmt || '-'}</td>
            <td><span class="badge bg-warning-subtle text-warning-emphasis border">RECHAZADA</span></td>
            <td class="text-end font-monospace">RD$ ${parseFloat(f.DocTotal).toFixed(2)}</td>
            <td class="text-end font-monospace text-success fw-bold">RD$ ${parseFloat(item.saldoDisponible).toFixed(2)}</td>
        </tr>`;
    });
    $('#tbodyFacturasLoteARS').html(tbodyFacHtml);

    $('#txtMotivo').val('Nota de Crédito por Rechazo de Reclamación ARS');
    $('#cboTipoNC').val('01');
    evaluarTipoNC();

    $('#cardFormNC').addClass('visible');
    $('#cardDetalle').addClass('visible');

    renderTablaNC();
    showNcToast(`Se cargaron ${facturasLoteARS.length} factura(s) del lote ARS para Nota de Crédito.`, 'success');
}

// =======================================================================
// PROCESAR NOTA DE CRÉDITO (NC)
// =======================================================================
function procesarNC() {
    if (modoLoteARS) {
        procesarColaLoteNC();
        return;
    }

    if (!facturaOrigen) {
        showNcToast('Debe buscar una factura origen primero.', 'warning');
        return;
    }
    if (detalleNC.length === 0) {
        showNcToast('No hay artículos en la nota de crédito.', 'warning');
        return;
    }
    const motivo = $('#txtMotivo').val().trim();
    if (!motivo) {
        showNcToast('El motivo de la Nota de Crédito es obligatorio.', 'warning');
        $('#txtMotivo').focus();
        return;
    }
    const serie = $('#cboSerie').val();
    if (!serie) {
        showNcToast('Debe seleccionar la serie NCF.', 'warning');
        return;
    }

    let totalNC = 0;
    detalleNC.forEach(l => {
        const discMonto = (l.Quantity * l.Price) * (l.DiscPrcnt / 100);
        const lineNeto  = (l.Quantity * l.Price) - discMonto;
        totalNC += lineNeto + lineNeto * (l.VatPrcnt / 100);
    });

    const saldo = saldoDisponible;
    if (totalNC > saldo + 0.01) {
        showNcToast('El monto de la NC excede el saldo disponible de la factura. Ajuste las cantidades.', 'danger');
        return;
    }

    if (!confirm(`¿Confirma emitir la Nota de Crédito por RD$ ${totalNC.toFixed(2)} sobre la factura ${facturaOrigen.NumAtCard || facturaOrigen.U_FACNUM || (facturaOrigen.DocNum ? '#' + facturaOrigen.DocNum : 'ID: ' + facturaOrigen.C_DocEntry)}?`)) {
        return;
    }

    const btn = $('#btnProcesarNC');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Procesando...');

    const payload = {
        header: {
            C_DocOrigenNum:    parseInt($('#txtFacCDocEntry').val()),
            DocDate:           $('#txtDocDateNC').val(),
            U_FacSerie:        serie,
            U_TipoNC:          $('#cboTipoNC').val(),
            Comments:          motivo,
            devolver_efectivo: $('#chkDevolverEfectivo').is(':checked'),
        },
        lines: detalleNC.map((l, i) => ({
            LineNum:       i,
            BaseLine:      parseInt(l.BaseLine !== undefined ? l.BaseLine : i),
            BaseEntry:     parseInt(l.BaseEntry || $('#txtFacCDocEntry').val()),
            ItemCode:      String(l.ItemCode || ''),
            Dscription:    String(l.Dscription || ''),
            Quantity:      parseFloat(l.Quantity) || 0,
            QtyDisponible: parseFloat(l.QtyDisponible || 0),
            Price:         parseFloat(l.Price) || 0,
            PriceBefDi:    parseFloat(l.PriceBefDi || l.Price) || 0,
            DiscPrcnt:     parseFloat(l.DiscPrcnt) || 0,
            VatPrcnt:      parseFloat(l.VatPrcnt) || 0,
            TaxCode:       String(l.TaxCode || ''),
            WhsCode:       String(l.WhsCode || '01')
        })),
    };

    $.ajax({
        url: 'index.php?action=guardar_nota_credito',
        type: 'POST',
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(payload),
        success: function(res) {
            if (res.success) {
                showNcToast(res.msg + (res.NCF ? ' | NCF: ' + res.NCF : ''), 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-5"></i> EMITIR NOTA DE CRÉDITO');
                showNcToast(res.msg || 'Error al guardar la NC.', 'danger');
            }
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-5"></i> EMITIR NOTA DE CRÉDITO');
            let msg = 'Error al procesar la Nota de Crédito.';
            try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e){}
            showNcToast(msg, 'danger');
        }
    });
}

// =======================================================================
// PROCESAR NOTA DE CRÉDITO (NC)
// =======================================================================
function procesarNC() {
    if (modoLoteARS) {
        procesarColaLoteNC();
        return;
    }

    if (!facturaOrigen) {
        showNcToast('Debe buscar una factura origen primero.', 'warning');
        return;
    }
    if (detalleNC.length === 0) {
        showNcToast('No hay artículos en la nota de crédito.', 'warning');
        return;
    }
    const motivo = $('#txtMotivo').val().trim();
    if (!motivo) {
        showNcToast('El motivo de la Nota de Crédito es obligatorio.', 'warning');
        $('#txtMotivo').focus();
        return;
    }
    const serie = $('#cboSerie').val();
    if (!serie) {
        showNcToast('Debe seleccionar la serie NCF.', 'warning');
        return;
    }

    let totalNC = 0;
    detalleNC.forEach(l => {
        const discMonto = (l.Quantity * l.Price) * (l.DiscPrcnt / 100);
        const lineNeto  = (l.Quantity * l.Price) - discMonto;
        totalNC += lineNeto + lineNeto * (l.VatPrcnt / 100);
    });

    const saldo = saldoDisponible;
    if (totalNC > saldo + 0.01) {
        showNcToast('El monto de la NC excede el saldo disponible de la factura. Ajuste las cantidades.', 'danger');
        return;
    }

    if (!confirm(`¿Confirma emitir la Nota de Crédito por RD$ ${totalNC.toFixed(2)} sobre la factura ${facturaOrigen.NumAtCard || facturaOrigen.U_FACNUM || (facturaOrigen.DocNum ? '#' + facturaOrigen.DocNum : 'ID: ' + facturaOrigen.C_DocEntry)}?`)) {
        return;
    }

    const btn = $('#btnProcesarNC');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Procesando...');

    const payload = {
        header: {
            C_DocOrigenNum:    parseInt($('#txtFacCDocEntry').val()),
            DocDate:           $('#txtDocDateNC').val(),
            U_FacSerie:        serie,
            U_TipoNC:          $('#cboTipoNC').val(),
            Comments:          motivo,
            devolver_efectivo: $('#chkDevolverEfectivo').is(':checked'),
        },
        lines: detalleNC.map((l, i) => ({
            LineNum:       i,
            BaseLine:      parseInt(l.BaseLine !== undefined ? l.BaseLine : i),
            BaseEntry:     parseInt(l.BaseEntry || $('#txtFacCDocEntry').val()),
            ItemCode:      String(l.ItemCode || ''),
            Dscription:    String(l.Dscription || ''),
            Quantity:      parseFloat(l.Quantity) || 0,
            QtyDisponible: parseFloat(l.QtyDisponible || 0),
            Price:         parseFloat(l.Price) || 0,
            DiscPrcnt:     parseFloat(l.DiscPrcnt) || 0,
            VatPrcnt:      parseFloat(l.VatPrcnt) || 0,
            TaxCode:       String(l.TaxCode || ''),
            WhsCode:       String(l.WhsCode || '01')
        })),
    };

    $.ajax({
        url: 'index.php?action=guardar_nota_credito',
        type: 'POST',
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(payload),
        success: function(res) {
            if (res.success) {
                showNcToast(res.msg + (res.NCF ? ' | NCF: ' + res.NCF : ''), 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-5"></i> EMITIR NOTA DE CRÉDITO');
                showNcToast(res.msg || 'Error al guardar la NC.', 'danger');
            }
        },
        error: function(xhr) {
            btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-5"></i> EMITIR NOTA DE CRÉDITO');
            let msg = 'Error al procesar la Nota de Crédito.';
            try { msg = JSON.parse(xhr.responseText).msg || msg; } catch(e){}
            showNcToast(msg, 'danger');
        }
    });
}

// =======================================================================
// PROCESAMIENTO EN COLA DE LOTE DE NOTAS DE CRÉDITO ARS
// =======================================================================
async function procesarColaLoteNC() {
    if (facturasLoteARS.length === 0) {
        showNcToast('No hay facturas en el lote.', 'warning');
        return;
    }

    const motivo = $('#txtMotivo').val().trim();
    if (!motivo) {
        showNcToast('El motivo de la Nota de Crédito es obligatorio.', 'warning');
        $('#txtMotivo').focus();
        return;
    }

    const serie = $('#cboSerie').val() || 'E34';
    const tipoNC = $('#cboTipoNC').val() || '01';
    const docDate = $('#txtDocDateNC').val() || new Date().toISOString().substring(0, 10);
    const devolverEfectivo = $('#chkDevolverEfectivo').is(':checked');

    if (!confirm(`¿Confirma emitir en cola las Notas de Crédito para ${facturasLoteARS.length} factura(s) del lote ARS?`)) {
        return;
    }

    const modalProgreso = new bootstrap.Modal(document.getElementById('modalProgresoLoteNC'));
    modalProgreso.show();

    const totalFacturas = facturasLoteARS.length;
    const resultados = [];

    for (let i = 0; i < totalFacturas; i++) {
        const itemFac = facturasLoteARS[i];
        const fac = itemFac.factura;
        const cDocEntry = parseInt(fac.C_DocEntry);
        const numFactura = fac.NumAtCard || (fac.DocNum ? '#' + fac.DocNum : 'ID: ' + fac.C_DocEntry);

        const pct = Math.round(((i) / totalFacturas) * 100);
        $('#progreso_lote_bar').css('width', pct + '%');
        $('#progreso_lote_porcentaje').text(pct + '%');
        $('#progreso_lote_detalle').text(`Procesando factura ${i + 1} de ${totalFacturas}: ${numFactura}...`);

        const lineasEstaFac = detalleNC
            .filter(l => l.BaseEntry === cDocEntry)
            .map((l, idx) => ({
                LineNum:       idx,
                BaseLine:      parseInt(l.BaseLine !== undefined ? l.BaseLine : idx),
                BaseEntry:     cDocEntry,
                ItemCode:      String(l.ItemCode || ''),
                Dscription:    String(l.Dscription || ''),
                Quantity:      parseFloat(l.Quantity) || 0,
                QtyDisponible: parseFloat(l.QtyDisponible || 0),
                Price:         parseFloat(l.Price) || 0,
                DiscPrcnt:     parseFloat(l.DiscPrcnt) || 0,
                VatPrcnt:      parseFloat(l.VatPrcnt) || 0,
                TaxCode:       String(l.TaxCode || ''),
                WhsCode:       String(l.WhsCode || '01')
            }));

        const payload = {
            header: {
                C_DocOrigenNum:    cDocEntry,
                DocDate:           docDate,
                U_FacSerie:        serie,
                U_TipoNC:          tipoNC,
                Comments:          motivo,
                devolver_efectivo: devolverEfectivo
            },
            lines: lineasEstaFac
        };

        try {
            const resp = await $.ajax({
                url: 'index.php?action=guardar_nota_credito',
                type: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify(payload)
            });

            if (resp.success) {
                resultados.push({
                    factura: numFactura,
                    ncf: resp.NCF || '-',
                    docEntry: resp.docEntry || 0,
                    monto: parseFloat(fac.DocTotal || 0),
                    status: 'OK'
                });
            } else {
                resultados.push({
                    factura: numFactura,
                    ncf: 'Error',
                    docEntry: 0,
                    monto: parseFloat(fac.DocTotal || 0),
                    status: 'ERROR: ' + (resp.msg || 'Fallo al guardar')
                });
            }
        } catch (xhr) {
            let errorMsg = 'Error en servidor';
            try { errorMsg = JSON.parse(xhr.responseText).msg || errorMsg; } catch(e){}
            resultados.push({
                factura: numFactura,
                ncf: 'Error',
                docEntry: 0,
                monto: parseFloat(fac.DocTotal || 0),
                status: 'ERROR: ' + errorMsg
            });
        }
    }

    $('#progreso_lote_bar').css('width', '100%');
    $('#progreso_lote_porcentaje').text('100%');

    setTimeout(() => {
        modalProgreso.hide();

        let tbodyResultHtml = '';
        resultados.forEach(r => {
            tbodyResultHtml += `<tr>
                <td class="font-monospace fw-bold">${r.factura}</td>
                <td class="font-monospace fw-bold ${r.status === 'OK' ? 'text-danger' : 'text-muted'}">${r.ncf}</td>
                <td class="text-end font-monospace">RD$ ${r.monto.toFixed(2)}</td>
                <td class="text-center">
                    ${r.status === 'OK' && r.docEntry > 0 ? 
                        `<a href="index.php?action=reporte_nota_credito&id=${r.docEntry}" target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-2"><i class="bi bi-printer-fill me-1"></i> Imprimir NC</a>` 
                        : `<span class="badge bg-danger">${r.status}</span>`}
                </td>
            </tr>`;
        });
        $('#tbodyResultadoLoteNC').html(tbodyResultHtml);

        new bootstrap.Modal(document.getElementById('modalExitoLoteNC')).show();
    }, 600);
}
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>