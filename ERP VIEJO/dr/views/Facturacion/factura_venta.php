<?php 
 $condiciones = $condiciones ?? [];
 $seriesNCF = $seriesNCF ?? [];
?>

<?php require_once __DIR__ . '/../Estructura/header.php'; ?>
<link rel="stylesheet" href="views/Facturacion/styles/factura_venta.css?v=<?php echo time(); ?>">

<div class="container-fluid facturacion-container">

    <!-- Header de Página -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <h2 class="page-title">Punto de Venta & Facturación</h2>
                <p class="page-subtitle">Emisión de facturas a pacientes, clientes y aseguradoras (NCF / Contado / Crédito)</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-bold font-monospace">
                <i class="bi bi-safe me-1"></i> Caja #<?php echo intval($_SESSION['caja_id'] ?? 1); ?> • Turno Z-<?php echo str_pad($_SESSION['caja_apertura_id'] ?? 0, 7, '0', STR_PAD_LEFT); ?>
            </span>
        </div>
    </div>

    <div id="alertContainer">
        <?php echo Helper::getMensaje(); ?>
    </div>

    <!-- SECCIÓN 1: ENCABEZADO DE LA FACTURA -->
    <div class="fac-header-card">
        <div class="fac-header-card-top">
            <span class="fw-bold text-dark small"><i class="bi bi-person-lines-fill text-primary me-1"></i> Datos del Paciente / Cliente & Configuración Fiscal</span>
            <span class="badge bg-primary-subtle text-primary font-monospace">Comprobante Fiscal</span>
        </div>

        <div class="fac-card-body">
            <div class="row g-3">
                
                <!-- Cliente / Paciente -->
                <div class="col-lg-4 col-md-6">
                    <label class="form-label-pos text-primary"><i class="bi bi-person-badge"></i> Cliente / Paciente <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="txtCardName" class="form-control input-pos input-pos-readonly fw-semibold" placeholder="Seleccione paciente..." readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="abrirModalCliente()" id="btnBuscarCliente">
                            <i class="bi bi-search me-1"></i> Buscar
                        </button>
                    </div>
                    <input type="hidden" id="txtCardCode">
                    <input type="hidden" id="txtRNC">
                    <input type="hidden" id="txtListNum">
                </div>

                <!-- RNC / Cédula -->
                <div class="col-lg-2 col-md-3">
                    <label class="form-label-pos"><i class="bi bi-card-heading"></i> RNC / Cédula</label>
                    <input type="text" id="txtRNCVisible" class="form-control input-pos input-pos-readonly font-monospace" placeholder="Sin RNC" readonly>
                </div>

                <!-- N° Aprobación Seguro / ARS (MODAL INTERACTIVO) -->
                <div class="col-lg-3 col-md-3">
                    <label class="form-label-pos text-success">
                        <i class="bi bi-shield-check"></i> N° Aprobación ARS / Seguro
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-success-subtle text-success border-success-subtle"><i class="bi bi-hospital"></i></span>
                        <input type="text" id="txtNumAprobacion" name="U_NumAprobacion" class="form-control input-pos input-pos-readonly input-num-aprobacion-trigger font-monospace fw-bold" 
                               placeholder="Click para autorizar seguro..." title="Haga clic para registrar o consultar autorización de la ARS" readonly onclick="abrirModalARS()">
                        <button class="btn btn-outline-success" type="button" onclick="abrirModalARS()" title="Abrir ventana de Aprobación ARS">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                    </div>
                </div>

                <!-- Fecha Factura -->
                <div class="col-lg-3 col-md-4">
                    <label class="form-label-pos"><i class="bi bi-calendar3"></i> Fecha de Emisión</label>
                    <input type="date" id="txtDocDate" class="form-control input-pos" value="<?php echo date('Y-m-d'); ?>">
                </div>

                <!-- Condición de Pago (Bloqueado / Amarrado al Paciente) -->
                <div class="col-lg-4 col-md-4">
                    <label class="form-label-pos"><i class="bi bi-credit-card-2-front"></i> Términos de Pago</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock-fill"></i></span>
                        <input type="text" id="txtCondicionVisible" class="form-control input-pos input-pos-readonly fw-bold font-monospace"  placeholder="Seleccione paciente..." readonly>
                    </div>
                    <input type="hidden" id="cboCondicion" value="1" data-dias="0">
                </div>

                <!-- Tipo Comprobante (NCF) (Bloqueado / Amarrado al Paciente) -->
                <div class="col-lg-5 col-md-4">
                    <label class="form-label-pos"><i class="bi bi-receipt-cutoff"></i> Tipo de Comprobante NCF </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock-fill"></i></span>
                        <input type="text" id="txtTipoNCFVisible" class="form-control input-pos input-pos-readonly fw-bold font-monospace" placeholder="Seleccione paciente para amarrar NCF..." readonly>
                    </div>
                    <input type="hidden" id="cboTipoNCF" value="">
                </div>

                <!-- % Descuento Global -->
                <div class="col-lg-3 col-md-4">
                    <label class="form-label-pos"><i class="bi bi-percent"></i>Descuento Global</label>
                    <div class="input-group">
                        <input type="number" id="txtDescGlobal" class="form-control input-pos text-center font-monospace" value="0" min="0" max="100" oninput="renderTabla()">
                        <span class="input-group-text bg-light">%</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: DETALLE DE ARTÍCULOS / SERVICIOS -->
    <div class="fac-detail-card">
        <div class="fac-detail-card-header">
            <span class="fw-bold"><i class="bi bi-cart4 me-2"></i> Artículos, Consultas & Procedimientos</span>
            <button type="button" class="btn btn-light btn-sm fw-bold rounded-3 text-primary" onclick="abrirModalArticulo()">
                <i class="bi bi-plus-circle-fill me-1"></i> Agregar Artículos
            </button>
        </div>

        <div class="fac-table-wrapper">
            <table class="table-pos" id="tablaDetalleFactura">
                <thead>
                    <tr>
                        <th style="width: 10%;">Código</th>
                        <th style="width: 32%;">Descripción</th>
                        <th style="width: 8%;" class="text-center">Cant.</th>
                        <th style="width: 12%;" class="text-end">Precio (RD$)</th>
                        <th style="width: 8%;" class="text-center">% Desc</th>
                        <th style="width: 8%;" class="text-center">ITBIS %</th>
                        <th style="width: 8%;" class="text-center">Almacén</th>
                        <th style="width: 10%;" class="text-end">Total Línea</th>
                        <th style="width: 4%;" class="text-center"></th>
                    </tr>
                </thead>
                <tbody id="tbodyDetalle">
                    <tr id="rowEmptyPlaceholder">
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            <span class="fw-semibold">No se han añadido artículos a la factura</span>
                            <div class="small mt-1">Haga clic en <strong>Agregar Artículos</strong> para buscar en el catálogo</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer y Totales -->
        <div class="fac-footer-section">
            <div class="row align-items-center g-3">
                <div class="col-lg-7">
                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <i class="bi bi-info-circle text-primary"></i>
                        <span>Los artículos con descripción amarilla permiten personalización de texto para el reporte.</span>
                    </div>
                </div>
                
                <div class="col-lg-5">
                    <div class="totals-summary-box">
                        <div class="total-row">
                            <span class="total-row-label">Subtotal Neto:</span>
                            <span class="total-row-val" id="lblSubtotal">RD$ 0.00</span>
                        </div>
                        <div class="total-row">
                            <span class="total-row-label">Descuento Total:</span>
                            <span class="total-row-val text-danger" id="lblDescuento">RD$ 0.00</span>
                        </div>
                        <div class="total-row">
                            <span class="total-row-label">ITBIS Impuesto:</span>
                            <span class="total-row-val" id="lblImpuesto">RD$ 0.00</span>
                        </div>
                        <div class="total-row total-general-row">
                            <span class="total-general-label">TOTAL GENERAL:</span>
                            <span class="total-general-val" id="lblTotal">RD$ 0.00</span>
                        </div>
                        <!-- Copago ARS: solo visible cuando se aplica aprobación de seguro -->
                        <div class="total-row" id="rowCopago" style="display:none; border-top: 2px dashed #86efac; margin-top: 6px; padding-top: 8px;">
                            <span class="total-row-label" id="lblCopagoTitulo" style="color:#059669; font-weight:700;">
                                <i class="bi bi-shield-check me-1"></i>Copago Paciente (ARS):
                            </span>
                            <span class="total-row-val fw-bold" id="lblCopagoARS" style="color:#059669; font-size:1.05rem;">RD$ 0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-2">
                <button type="button" class="btn-process-invoice" id="btnProcesarFactura" onclick="iniciarProcesoFactura()">
                    <i class="bi bi-check2-circle fs-5"></i> PROCESAR FACTURA
                </button>
            </div>
        </div>
    </div>

</div>

<!-- ========================================== -->
<!-- MODAL: BUSCAR CLIENTE / PACIENTE           -->
<!-- ========================================== -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-pacientes-custom modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-pos-header cliente">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-white bg-opacity-25 rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-people-fill fs-5 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white">Buscar Cliente / Paciente</h5>
                        <div class="small text-white text-opacity-75" style="font-size: 0.78rem;">Priorización automática de pacientes en espera, en consulta o con cita hoy</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="modal-search-bar mb-3">
                    <i class="bi bi-search"></i>
                    <input type="text" id="txtSearchCliente" placeholder="Buscar por Nombre, Cédula/RNC, Código o N° de Expediente..." oninput="filtrarClientesModal()">
                </div>
                <div style="max-height: 48vh; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;">
                    <table class="table-modal-clientes">
                        <thead class="sticky-top">
                            <tr>
                                <th style="width: 14%;">Código / Exp.</th>
                                <th style="width: 27%;">Paciente / Razón Social</th>
                                <th style="width: 15%;">RNC / Cédula</th>
                                <th style="width: 24%;">Estado / Consulta Hoy</th>
                                <th style="width: 10%;">Tipo</th>
                                <th style="width: 10%;" class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyClientes">
                            <tr><td colspan="6" class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm me-2 text-primary"></span> Consultando pacientes y agenda del día...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-pagination-bar" id="paginacionClientes">
                    <span id="lblInfoPaginaCliente">Mostrando registros</span>
                    <div class="d-flex gap-1">
                        <button type="button" class="modal-page-btn" id="btnPrevCliente" onclick="cambiarPaginaCliente(-1)">&laquo; Anterior</button>
                        <button type="button" class="modal-page-btn" id="btnNextCliente" onclick="cambiarPaginaCliente(1)">Siguiente &raquo;</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: BUSCAR ARTÍCULOS                    -->
<!-- ========================================== -->
<div class="modal fade" id="modalArticulo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-articulos-custom modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-pos-header articulo">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-white bg-opacity-25 rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-box-seam-fill fs-5 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white">Catálogo de Artículos & Servicios</h5>
                        <div class="small text-white text-opacity-75" style="font-size: 0.78rem;">Búsqueda y selección de consultas médicas, procedimientos, lentes y artículos</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="modal-filter-toolbar mb-3">
                    <div class="modal-search-bar modal-search-articulo flex-grow-1 mb-0">
                        <i class="bi bi-search"></i>
                        <input type="text" id="txtSearchArticulo" placeholder="Buscar por código, descripción, código de barras o grupo..." oninput="filtrarArticulosModal()">
                    </div>
                    <select id="filtroGrupoArticuloModal" class="filter-select-modal" onchange="filtrarArticulosModal()">
                        <option value="">Todos los Grupos</option>
                        <?php if (!empty($gruposArticulos)): ?>
                            <?php foreach ($gruposArticulos as $grp): ?>
                                <option value="<?php echo htmlspecialchars($grp['ItmsGrpCod']); ?>"><?php echo htmlspecialchars($grp['ItmsGrpNam']); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <select id="filtroTipoArticuloModal" class="filter-select-modal" onchange="filtrarArticulosModal()">
                        <option value="todos">Todos los Tipos</option>
                        <option value="favorito">Solo Favoritos ⭐</option>
                        <option value="editable">Solo Editables 📝</option>
                        <option value="inventario">Solo Inventario</option>
                        <option value="servicio">Solo Servicios</option>
                    </select>
                    <select id="filtroStockArticuloModal" class="filter-select-modal" onchange="filtrarArticulosModal()">
                        <option value="todos">Todo Stock</option>
                        <option value="con_stock">Con Existencia (&gt; 0)</option>
                        <option value="sin_stock">Sin Existencia (0)</option>
                    </select>
                    <button type="button" class="btn-refresh-catalogo" id="btnRefreshArticulos" onclick="limpiarYRefrescarArticulosModal(true)" title="Sincronizar artículos nuevos y refrescar catálogo">
                        <i class="bi bi-arrow-clockwise fs-6" id="iconRefreshArticulos"></i>
                        <span>Refrescar</span>
                    </button>
                </div>
                <div style="max-height: 48vh; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff;">
                    <table class="table-modal-articulos">
                        <thead class="sticky-top">
                            <tr>
                                <th style="width: 14%;">Código</th>
                                <th style="width: 33%;">Descripción / Artículo</th>
                                <th style="width: 15%;" class="text-end">Precio Base</th>
                                <th style="width: 11%;" class="text-center">ITBIS %</th>
                                <th style="width: 11%;" class="text-center">Almacén</th>
                                <th style="width: 8%;" class="text-center">Editable</th>
                                <th style="width: 10%;" class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyArticulos">
                            <tr><td colspan="7" class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm me-2 text-success"></span> Cargando catálogo...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-pagination-bar" id="paginacionArticulos">
                    <span id="lblInfoPaginaArticulo">Mostrando registros</span>
                    <div class="d-flex gap-1">
                        <button type="button" class="modal-page-btn" id="btnPrevArticulo" onclick="cambiarPaginaArticulo(-1)">&laquo; Anterior</button>
                        <button type="button" class="modal-page-btn" id="btnNextArticulo" onclick="cambiarPaginaArticulo(1)">Siguiente &raquo;</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- El modal ARS se incluye DESPUÉS de jQuery (ver abajo) -->

<!-- Toast Container -->
<div class="pos-toast-container">
    <div id="posToast" class="toast align-items-center text-bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="posToastBody">
                Mensaje de facturación
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- ========================================== -->
<!-- MODAL: APROBACIÓN DE SEGURO ARS            -->
<!-- ========================================== -->
<?php 
if (!defined('EMBEDDED_ARS_MODAL')) {
    define('EMBEDDED_ARS_MODAL', true);
}
include __DIR__ . '/num_aprobacion_ars.php'; 
?>
<script>
// Diccionario de nombres de Comprobantes Fiscales
const mapaSeriesNCF = <?php 
    $ncfMap = [];
    foreach ($seriesNCF as $sn) {
        $ncfMap[$sn['serie']] = $sn['serie'] . ' - ' . $sn['descripcion'];
    }
    $ncfMap['E31'] = $ncfMap['E31'] ?? 'E31 - COMPROBANTE CREDITO FISCAL ELECTRONICO';
    $ncfMap['E32'] = $ncfMap['E32'] ?? 'E32 - COMPROBANTE CONSUMIDOR FINAL ELECTRONICO';
    $ncfMap['E44'] = $ncfMap['E44'] ?? 'E44 - REGIMENES ESPECIALES ELECTRONICO';
    $ncfMap['E45'] = $ncfMap['E45'] ?? 'E45 - GUBERNAMENTAL ELECTRONICO';
    $ncfMap['B01'] = $ncfMap['B01'] ?? 'B01 - FACTURA CREDITO FISCAL';
    $ncfMap['B02'] = $ncfMap['B02'] ?? 'B02 - FACTURA CONSUMIDOR FINAL';
    $ncfMap['B14'] = $ncfMap['B14'] ?? 'B14 - REGIMENES ESPECIALES';
    $ncfMap['B15'] = $ncfMap['B15'] ?? 'B15 - GUBERNAMENTAL';
    echo json_encode($ncfMap, JSON_UNESCAPED_UNICODE);
?>;

// Diccionario de Condiciones de Pago
const mapaCondicionesPago = <?php 
    $condMap = [];
    foreach ($condiciones as $c) {
        $condMap[$c['GroupNum']] = [
            'nombre' => $c['PymntGroup'],
            'dias' => intval($c['ExtraDays'])
        ];
    }
    $condMap['1'] = $condMap['1'] ?? ['nombre' => 'CONTADO', 'dias' => 0];
    echo json_encode($condMap, JSON_UNESCAPED_UNICODE);
?>;

function aplicarCondicionPago(groupNum) {
    let gNum = (groupNum !== null && groupNum !== undefined && groupNum !== '') ? String(groupNum) : '1';
    let info = mapaCondicionesPago[gNum] || { nombre: 'CONTADO', dias: 0 };
    $('#cboCondicion').val(gNum).attr('data-dias', info.dias);
    $('#txtCondicionVisible').val(info.nombre + (info.dias > 0 ? ` (${info.dias} días)` : ''));
}

let detalleFactura = [];
let listaClientesGlobal = [];
let listaClientesFiltrada = [];
let paginaClienteActual = 1;
const porPaginaCliente = 12;

let listaArticulosGlobal = [];
let listaArticulosFiltrada = [];
let paginaArticuloActual = 1;
const porPaginaArticulo = 12;

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

function showPosToast(mensaje, tipo = 'dark') {
    const toastElem = document.getElementById('posToast');
    const toastBody = document.getElementById('posToastBody');
    toastElem.className = `toast align-items-center text-bg-${tipo} border-0 shadow-lg`;
    toastBody.innerText = mensaje;
    const toast = new bootstrap.Toast(toastElem, { delay: 4000 });
    toast.show();
}

// ==========================================
// LÓGICA DE CLIENTES CON CARGA AUTOMÁTICA Y PAGINACIÓN
// ==========================================
function abrirModalCliente() {
    $('#txtSearchCliente').val('');
    new bootstrap.Modal(document.getElementById('modalCliente')).show();
    cargarClientesAJAX('');
    setTimeout(() => $('#txtSearchCliente').focus(), 400);
}

function cargarClientesAJAX(term) {
    $('#tbodyClientes').html('<tr><td colspan="5" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Consultando lista de pacientes...</td></tr>');
    
    $.ajax({
        url: 'index.php?action=buscar_cliente_factura',
        type: 'GET',
        data: { q: term },
        dataType: 'json',
        success: function(data) {
            listaClientesGlobal = data || [];
            listaClientesFiltrada = [...listaClientesGlobal];
            paginaClienteActual = 1;
            renderPaginacionClientes();
        },
        error: function(xhr, status, error) {
            let msg = 'Error de comunicación al consultar clientes.';
            if (xhr.responseText) {
                try { let j = JSON.parse(xhr.responseText); msg = j.message || msg; }
                catch(e) { msg = 'Error del servidor: ' + xhr.responseText.substring(0, 200); }
            }
            $('#tbodyClientes').html('<tr><td colspan="5" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-1"></i>' + msg + '</td></tr>');
        }
    });
}

function filtrarClientesModal() {
    let q = $('#txtSearchCliente').val().trim().toLowerCase();
    if (!q) {
        listaClientesFiltrada = [...listaClientesGlobal];
    } else {
        listaClientesFiltrada = listaClientesGlobal.filter(c => 
            (c.CardName && c.CardName.toLowerCase().includes(q)) ||
            (c.CardCode && c.CardCode.toLowerCase().includes(q)) ||
            (c.U_NIT && c.U_NIT.toLowerCase().includes(q)) ||
            (c.U_expediente && c.U_expediente.toString().includes(q)) ||
            (c.NombreDoctor && c.NombreDoctor.toLowerCase().includes(q)) ||
            (c.NombreDoctorCita && c.NombreDoctorCita.toLowerCase().includes(q))
        );
    }
    paginaClienteActual = 1;
    renderPaginacionClientes();
}

function renderPaginacionClientes() {
    let tbody = $('#tbodyClientes');
    if (listaClientesFiltrada.length === 0) {
        tbody.html('<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron clientes o pacientes</td></tr>');
        $('#lblInfoPaginaCliente').text('0 registros');
        $('#btnPrevCliente').prop('disabled', true);
        $('#btnNextCliente').prop('disabled', true);
        return;
    }

    let total = listaClientesFiltrada.length;
    let totalPaginas = Math.ceil(total / porPaginaCliente);
    let inicio = (paginaClienteActual - 1) * porPaginaCliente;
    let fin = Math.min(inicio + porPaginaCliente, total);
    let slice = listaClientesFiltrada.slice(inicio, fin);

    let html = '';
    slice.forEach((c, idx) => {
        // Badge de Tipo / Clasificación dinámico desde la BD (posOCRG)
        let nombreGrupo = (c.GroupName && c.GroupName.trim() !== '') ? c.GroupName.trim() : (c.CardType === 'P' ? 'PROVEEDORES' : (c.CardType === 'S' ? 'ASEGURADORAS' : 'CLIENTES'));
        let iconGrupo = 'bi-person';
        let claseBadge = 'badge-tipo-cliente';
        let grpUpper = nombreGrupo.toUpperCase();

        if (c.QryGroup1 === 'Y' || grpUpper.includes('ASEGURADORA') || grpUpper.includes('SEGURO') || c.CardType === 'S') {
            iconGrupo = 'bi-hospital';
            claseBadge = 'badge-tipo-aseguradora';
        } else if (c.GroupType === 'S' || grpUpper.includes('PROVEEDOR') || grpUpper.includes('SUPLIDOR') || c.CardType === 'P') {
            iconGrupo = 'bi-truck';
            claseBadge = 'badge-tipo-suplidor';
        } else if (c.U_expediente && parseInt(c.U_expediente) > 0) {
            iconGrupo = 'bi-heart-pulse';
            claseBadge = 'badge-tipo-paciente';
        }

        let tipoTexto = `<span class="badge-tipo-entidad ${claseBadge}"><i class="bi ${iconGrupo} me-1"></i>${$('<div>').text(nombreGrupo).html()}</span>`;

        // Badge de Código y Expediente
        let numExp = (c.U_expediente && parseInt(c.U_expediente) > 0) ? parseInt(c.U_expediente) : null;
        let expBadge = numExp ? `<span class="badge-exp-pill font-monospace" title="N° Expediente: ${numExp}"><i class="bi bi-folder2 me-1"></i>#${numExp}</span>` : '';

        // Badge de Consulta / Cita Hoy (Priorización Visual)
        let estadoConsultaHtml = '';
        let filaClase = '';
        let prioridad = parseInt(c.NivelPrioridad) || 5;

        if (parseInt(c.FacturadoHoy) === 1) {
            estadoConsultaHtml = '<div><span class="badge-estado-prioridad badge-facturado"><i class="bi bi-check-circle-fill me-1"></i> Facturado Hoy</span></div>';
        } else if (prioridad === 1) {
            filaClase = 'row-prioridad-1';
            let horaLlegada = c.lisp_HoraLlegada ? `<span class="ms-1 text-dark fw-semibold" style="font-size: 0.78rem;"><i class="bi bi-clock text-amber"></i> ${c.lisp_HoraLlegada}</span>` : '';
            let doc = c.NombreDoctor ? `<div class="text-secondary fw-semibold" style="font-size: 0.75rem; margin-top: 2px;"><i class="bi bi-person-badge text-muted me-1"></i>${c.NombreDoctor}</div>` : '';
            estadoConsultaHtml = `<div><span class="badge-estado-prioridad badge-por-cobrar"><i class="bi bi-cash-stack"></i> Por Cobrar</span>${horaLlegada}${doc}</div>`;
        } else if (prioridad === 2) {
            filaClase = 'row-prioridad-2';
            let horaLlegada = c.lisp_HoraLlegada ? `<span class="ms-1 text-dark fw-semibold" style="font-size: 0.78rem;"><i class="bi bi-clock text-warning"></i> ${c.lisp_HoraLlegada}</span>` : '';
            let doc = c.NombreDoctor ? `<div class="text-secondary fw-semibold" style="font-size: 0.75rem; margin-top: 2px;"><i class="bi bi-person-badge text-muted me-1"></i>${c.NombreDoctor}</div>` : '';
            estadoConsultaHtml = `<div><span class="badge-estado-prioridad badge-en-espera"><i class="bi bi-clock-history"></i> En Espera</span>${horaLlegada}${doc}</div>`;
        } else if (prioridad === 3) {
            filaClase = 'row-prioridad-3';
            let doc = c.NombreDoctor ? `<div class="text-primary fw-semibold" style="font-size: 0.75rem; margin-top: 2px;"><i class="bi bi-person-badge text-primary me-1"></i>${c.NombreDoctor}</div>` : '';
            estadoConsultaHtml = `<div><span class="badge-estado-prioridad badge-en-consulta"><i class="bi bi-person-check-fill"></i> En Consulta</span>${doc}</div>`;
        } else if (prioridad === 4) {
            filaClase = 'row-prioridad-4';
            let doc = c.NombreDoctorCita ? `<div class="text-success fw-semibold" style="font-size: 0.75rem; margin-top: 2px;"><i class="bi bi-person-badge text-success me-1"></i>${c.NombreDoctorCita}</div>` : '';
            estadoConsultaHtml = `<div><span class="badge-estado-prioridad badge-cita-hoy"><i class="bi bi-calendar-check"></i> Cita Hoy ${c.cita_hora || ''}</span>${doc}</div>`;
        } else {
            estadoConsultaHtml = '<span class="badge-estado-prioridad badge-general"><i class="bi bi-dash-circle"></i> General</span>';
        }

        let rncTexto = c.U_NIT ? `<span class="font-monospace text-dark fw-semibold" style="font-size: 0.86rem;">${c.U_NIT}</span>` : '<span class="text-muted opacity-50 fst-italic" style="font-size: 0.82rem;">Sin RNC</span>';
        let subGrupoBadge = c.NombreARS ? `<span class="badge-subgrupo-ars"><i class="bi bi-shield-check me-1"></i>${c.NombreARS}</span>` : '';

        html += `<tr class="${filaClase}">
            <td>
                <div class="d-flex flex-column align-items-start" style="gap: 2px;">
                    <span class="font-monospace fw-bold text-dark" style="font-size: 0.88rem; line-height: 1.2;">${c.CardCode}</span>
                    ${expBadge}
                </div>
            </td>
            <td>
                <div class="d-flex flex-column align-items-start" style="gap: 2px;">
                    <span class="fw-bold text-dark" style="font-size: 0.90rem; line-height: 1.25;">${c.CardName}</span>
                    ${subGrupoBadge}
                </div>
            </td>
            <td>${rncTexto}</td>
            <td>${estadoConsultaHtml}</td>
            <td>${tipoTexto}</td>
            <td class="text-center">
                <button type="button" class="btn-select-cliente" onclick="seleccionarClientePorIndex(${inicio + idx})">
                    <i class="bi bi-check2"></i> Elegir
                </button>
            </td>
        </tr>`;
    });

    tbody.html(html);
    $('#lblInfoPaginaCliente').text(`Mostrando ${inicio + 1}-${fin} de ${total} registros (Pág. ${paginaClienteActual}/${totalPaginas})`);
    $('#btnPrevCliente').prop('disabled', paginaClienteActual <= 1);
    $('#btnNextCliente').prop('disabled', paginaClienteActual >= totalPaginas);
}

function cambiarPaginaCliente(delta) {
    let totalPaginas = Math.ceil(listaClientesFiltrada.length / porPaginaCliente);
    paginaClienteActual += delta;
    if (paginaClienteActual < 1) paginaClienteActual = 1;
    if (paginaClienteActual > totalPaginas) paginaClienteActual = totalPaginas;
    renderPaginacionClientes();
}

// Asignación inteligente y amarrada de Comprobante Fiscal Electrónico (e-CF) al cliente/paciente
function aplicarComprobanteCliente(comprob, rnc, groupCode = null, qryGroup1 = 'N') {
    let compToSet = '';
    
    if (comprob && comprob.trim() !== '') {
        compToSet = comprob.trim().toUpperCase();
        // Mapear series tradicionales a electrónicas
        let mapNCF = { 'B01': 'E31', 'B02': 'E32', 'B14': 'E44', 'B15': 'E45' };
        if (mapNCF[compToSet]) {
            compToSet = mapNCF[compToSet];
        }
        // Protección: En facturas de venta regular no se permiten series de Notas de Débito/Crédito (E33, E34, B04)
        if (compToSet === 'E33' || compToSet === 'E34' || compToSet === 'B04' || compToSet === 'B03') {
            compToSet = (groupCode == 4 || qryGroup1 === 'Y' || (rnc && rnc.trim() !== '')) ? 'E31' : 'E32';
        }
    } else {
        // Sin comprobante explícito:
        // Si es aseguradora (GroupCode 4 o QryGroup1 Y) o tiene RNC/Cédula -> E31, de lo contrario -> E32 (Consumidor Final)
        if (groupCode == 4 || qryGroup1 === 'Y' || (rnc && rnc.trim() !== '')) {
            compToSet = 'E31';
        } else {
            compToSet = 'E32';
        }
    }

    $('#cboTipoNCF').val(compToSet);
    let desc = mapaSeriesNCF[compToSet] || (compToSet + ' - COMPROBANTE FISCAL');
    $('#txtTipoNCFVisible').val(desc);
}

function seleccionarClientePorIndex(idx) {
    let c = listaClientesFiltrada[idx];
    if (!c) return;
    
    $('#txtCardCode').val(c.CardCode);
    $('#txtCardName').val(c.CardName);
    $('#txtRNC').val(c.U_NIT || '');
    $('#txtRNCVisible').val(c.U_NIT || 'Sin RNC');
    $('#txtListNum').val(c.ListNum || 1);
    
    aplicarComprobanteCliente(c.U_Comprob, c.U_NIT, c.GroupCode, c.QryGroup1);
    aplicarCondicionPago(c.GroupNum);

    // Guardar datos completos del paciente/socio para el modal de Aprobación ARS
    window.pacienteSeleccionadoARS = {
        CardCode: c.CardCode,
        CardName: c.CardName,
        CardType: c.CardType || 'P',
        GroupCode: c.GroupCode || null,
        QryGroup1: c.QryGroup1 || 'N',
        U_NIT: c.U_NIT || '',
        U_expediente: c.U_expediente || null,
        U_CadenaID: c.U_CadenaID || '',
        U_SubGrupo: c.U_SubGrupo || '',
        NombreARS: c.NombreARS || ''
    };

    // Si cambia de paciente, limpiar datos de aprobación anterior
    if (typeof payloadAprobacionARS !== 'undefined' && payloadAprobacionARS && payloadAprobacionARS.CardCode !== c.CardCode) {
        if (typeof limpiarAprobacionARS === 'function') { limpiarAprobacionARS(); }
    }

    // Bloquear o habilitar campo ARS según seguro del paciente
    actualizarEstadoCampoARS(window.pacienteSeleccionadoARS);

    $('#txtCardName').removeClass('is-invalid');
    bootstrap.Modal.getInstance(document.getElementById('modalCliente')).hide();
    showPosToast(`Cliente seleccionado: ${c.CardName} (NCF: ${$('#cboTipoNCF').val()})`, "success");
}

function seleccionarCliente(code, name, rnc, comprob, groupnum, listnum, exp = null, afiliado = '', subgrupo = '', nombreArs = '', cardType = 'P', groupCode = null, qryGroup1 = 'N') {
    $('#txtCardCode').val(code);
    $('#txtCardName').val(name);
    $('#txtRNC').val(rnc);
    $('#txtRNCVisible').val(rnc || 'Sin RNC');
    $('#txtListNum').val(listnum || 1);
    
    aplicarComprobanteCliente(comprob, rnc, groupCode, qryGroup1);
    aplicarCondicionPago(groupnum);

    window.pacienteSeleccionadoARS = {
        CardCode: code, CardName: name, CardType: cardType,
        GroupCode: groupCode, QryGroup1: qryGroup1,
        U_NIT: rnc || '', U_expediente: exp,
        U_CadenaID: afiliado, U_SubGrupo: subgrupo, NombreARS: nombreArs
    };

    if (typeof payloadAprobacionARS !== 'undefined' && payloadAprobacionARS && payloadAprobacionARS.CardCode !== code) {
        if (typeof limpiarAprobacionARS === 'function') { limpiarAprobacionARS(); }
    }

    actualizarEstadoCampoARS(window.pacienteSeleccionadoARS);

    $('#txtCardName').removeClass('is-invalid');
    bootstrap.Modal.getInstance(document.getElementById('modalCliente')).hide();
    showPosToast(`Cliente seleccionado: ${name} (NCF: ${$('#cboTipoNCF').val()})`, "success");
}

// Habilita o bloquea el campo ARS según si el paciente tiene seguro
function actualizarEstadoCampoARS(paciente) {
    let tieneSeguro = !!(paciente && (paciente.U_SubGrupo || paciente.U_CadenaID));
    let inputARS = document.getElementById('txtNumAprobacion');
    let btnARS = inputARS ? inputARS.nextElementSibling : null;

    if (tieneSeguro) {
        if (inputARS) {
            inputARS.disabled = false;
            inputARS.placeholder = 'Click para autorizar seguro...';
            inputARS.classList.remove('input-ars-disabled');
            inputARS.classList.add('input-num-aprobacion-trigger');
            inputARS.onclick = function() { abrirModalARS(); };
        }
        if (btnARS) {
            btnARS.disabled = false;
            btnARS.className = 'btn btn-outline-success';
            btnARS.title = 'Abrir ventana de Aprobación ARS';
        }
    } else {
        // Paciente privado sin seguro: bloquear campo
        if (inputARS) {
            inputARS.disabled = true;
            inputARS.value = '';
            inputARS.placeholder = 'No aplica (Paciente Privado)';
            inputARS.classList.remove('input-num-aprobacion-trigger');
            inputARS.classList.add('input-ars-disabled');
            inputARS.onclick = null;
        }
        if (btnARS) {
            btnARS.disabled = true;
            btnARS.className = 'btn btn-outline-secondary';
            btnARS.title = 'Paciente sin seguro asignado';
        }
        // Limpiar copago si lo había
        $('#rowCopago').hide();
        if (typeof payloadAprobacionARS !== 'undefined') payloadAprobacionARS = null;
        $('#txtNumAprobacion').val('');
        $('#badgeArsActivo').remove();
    }
}

// ==========================================
// LÓGICA DE ARTÍCULOS CON CARGA AUTOMÁTICA Y PAGINACIÓN
// ==========================================
function abrirModalArticulo() {
    if(!$('#txtCardCode').val()) { 
        $('#txtCardName').addClass('is-invalid');
        showPosToast("Seleccione un cliente o paciente primero.", "warning");
        abrirModalCliente();
        return; 
    }
    $('#txtSearchArticulo').val('');
    $('#filtroGrupoArticuloModal').val('');
    $('#filtroTipoArticuloModal').val('todos');
    $('#filtroStockArticuloModal').val('todos');
    new bootstrap.Modal(document.getElementById('modalArticulo')).show();
    cargarArticulosAJAX('');
    setTimeout(() => $('#txtSearchArticulo').focus(), 400);
}

function limpiarYRefrescarArticulosModal(forzarRefresh = false) {
    $('#txtSearchArticulo').val('');
    $('#filtroGrupoArticuloModal').val('');
    $('#filtroTipoArticuloModal').val('todos');
    $('#filtroStockArticuloModal').val('todos');
    cargarArticulosAJAX('', forzarRefresh);
}

function cargarArticulosAJAX(term, forzarRefresh = false) {
    let lista = $('#txtListNum').val() || 1;
    let icon = $('#iconRefreshArticulos');
    if (forzarRefresh) {
        icon.addClass('spin-animation');
    }
    let loadingText = forzarRefresh ? 'Sincronizando artículos y actualizando catálogo...' : 'Consultando catálogo de artículos...';
    $('#tbodyArticulos').html(`<tr><td colspan="7" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> ${loadingText}</td></tr>`);
    
    $.ajax({
        url: 'index.php?action=buscar_articulo_factura',
        type: 'GET',
        data: { q: term, lista: lista, refresh: forzarRefresh ? 1 : 0 },
        dataType: 'json',
        success: function(data) {
            icon.removeClass('spin-animation');
            listaArticulosGlobal = data || [];
            filtrarArticulosModal();
            if (forzarRefresh) {
                showPosToast("Catálogo sincronizado y actualizado correctamente.", "success");
            }
        },
        error: function(xhr, status, error) {
            icon.removeClass('spin-animation');
            let msg = 'Error al consultar artículos.';
            if (xhr.responseText) {
                try { let j = JSON.parse(xhr.responseText); msg = j.message || msg; }
                catch(e) { msg = 'Error del servidor: ' + xhr.responseText.substring(0, 300); }
            }
            $('#tbodyArticulos').html('<tr><td colspan="7" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-1"></i>' + msg + '</td></tr>');
        }
    });
}

function filtrarArticulosModal() {
    let q = ($('#txtSearchArticulo').val() || '').trim().toLowerCase();
    let grupo = $('#filtroGrupoArticuloModal').val();
    let tipo = $('#filtroTipoArticuloModal').val();
    let stock = $('#filtroStockArticuloModal').val();

    listaArticulosFiltrada = listaArticulosGlobal.filter(a => {
        let nombre = (a.ItemName || '').toLowerCase();
        let codigo = (a.ItemCode || '').toLowerCase();
        let barras = (a.CodeBars || '').toLowerCase();
        let grupoNam = (a.ItmsGrpNam || '').toLowerCase();

        let coincideTexto = !q || nombre.includes(q) || codigo.includes(q) || barras.includes(q) || grupoNam.includes(q);
        let coincideGrupo = !grupo || (String(a.ItmsGrpCod) === String(grupo));

        let coincideTipo = true;
        if (tipo === 'favorito') coincideTipo = (a.QryGroup2 === 'Y');
        else if (tipo === 'editable') coincideTipo = (a.QryGroup1 === 'Y');
        else if (tipo === 'inventario') coincideTipo = (a.InvntItem === 'Y');
        else if (tipo === 'servicio') coincideTipo = (a.InvntItem === 'N');

        let onHand = parseFloat(a.OnHand || 0);
        let coincideStock = true;
        if (stock === 'con_stock') coincideStock = (onHand > 0);
        else if (stock === 'sin_stock') coincideStock = (onHand <= 0);

        return coincideTexto && coincideGrupo && coincideTipo && coincideStock;
    });

    paginaArticuloActual = 1;
    renderPaginacionArticulos();
}

function renderPaginacionArticulos() {
    let tbody = $('#tbodyArticulos');
    if (listaArticulosFiltrada.length === 0) {
        tbody.html('<tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron artículos con ese criterio</td></tr>');
        $('#lblInfoPaginaArticulo').text('0 registros');
        $('#btnPrevArticulo').prop('disabled', true);
        $('#btnNextArticulo').prop('disabled', true);
        return;
    }

    let total = listaArticulosFiltrada.length;
    let totalPaginas = Math.ceil(total / porPaginaArticulo);
    let inicio = (paginaArticuloActual - 1) * porPaginaArticulo;
    let fin = Math.min(inicio + porPaginaArticulo, total);
    let slice = listaArticulosFiltrada.slice(inicio, fin);

    let html = '';
    slice.forEach(a => {
        let vat = parseFloat(a.VatPrcnt) || 0;
        let itbisBadge = vat > 0 
            ? `<span class="badge-itbis-tag itbis-gravado">${vat.toFixed(0)}% ITBIS</span>` 
            : `<span class="badge-itbis-tag itbis-exento">0% Exento</span>`;
            
        let editableBadge = a.QryGroup1 === 'Y' 
            ? `<span class="badge-editable-tag editable-si"><i class="bi bi-pencil-square"></i> Sí</span>` 
            : `<span class="badge-editable-tag editable-no"><i class="bi bi-dash"></i> No</span>`;
            
        let whsBadge = `<span class="badge-almacen-tag"><i class="bi bi-shop me-1 text-muted"></i>${a.DfltWH || '01'}</span>`;
        let precioFmt = parseFloat(a.PriceBefDi || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        let favIcon = a.QryGroup2 === 'Y' ? '<span title="Favorito" class="text-warning me-1">⭐</span>' : '';
        let grupoBadge = a.ItmsGrpNam ? `<span class="badge bg-light text-secondary border ms-1" style="font-size: 0.72rem; font-weight: 600;">${a.ItmsGrpNam}</span>` : '';
        let codebarsBadge = a.CodeBars ? `<span class="badge bg-light text-muted border ms-1 font-monospace" style="font-size: 0.70rem;"><i class="bi bi-upc me-1"></i>${a.CodeBars}</span>` : '';

        html += `<tr>
            <td>
                <div class="font-monospace fw-bold text-dark" style="font-size: 0.88rem;">${favIcon}${a.ItemCode}</div>
            </td>
            <td>
                <div class="d-flex align-items-center flex-wrap gap-1">
                    <span class="fw-bold text-dark" style="font-size: 0.92rem; line-height: 1.25;">${a.ItemName}</span>
                    ${grupoBadge}
                    ${codebarsBadge}
                </div>
            </td>
            <td class="text-end">
                <span class="font-monospace fw-bold text-dark" style="font-size: 0.92rem;">RD$ ${precioFmt}</span>
            </td>
            <td class="text-center">${itbisBadge}</td>
            <td class="text-center">${whsBadge}</td>
            <td class="text-center">${editableBadge}</td>
            <td class="text-center">
                <button type="button" class="btn-add-articulo" onclick="agregarItemAGrid('${a.ItemCode}', \`${a.ItemName.replace(/`/g, '\\`')}\`, ${a.PriceBefDi}, ${a.VatPrcnt}, '${a.TaxCode}', '${a.DfltWH || '01'}', '${a.QryGroup1 || 'N'}')">
                    <i class="bi bi-plus-lg"></i> Agregar
                </button>
            </td>
        </tr>`;
    });

    tbody.html(html);
    $('#lblInfoPaginaArticulo').text(`Mostrando ${inicio + 1}-${fin} de ${total} artículos (Pág. ${paginaArticuloActual}/${totalPaginas})`);
    $('#btnPrevArticulo').prop('disabled', paginaArticuloActual <= 1);
    $('#btnNextArticulo').prop('disabled', paginaArticuloActual >= totalPaginas);
}

function cambiarPaginaArticulo(delta) {
    let totalPaginas = Math.ceil(listaArticulosFiltrada.length / porPaginaArticulo);
    paginaArticuloActual += delta;
    if (paginaArticuloActual < 1) paginaArticuloActual = 1;
    if (paginaArticuloActual > totalPaginas) paginaArticuloActual = totalPaginas;
    renderPaginacionArticulos();
}

function agregarItemAGrid(code, name, price, vatPrcnt, taxCode, whsCode, editableDesc) {
    let linea = {
        ItemCode: code,
        ItemName: name,
        EditableDesc: editableDesc,
        Quantity: 1,
        PriceBefDi: parseFloat(price) || 0,
        DiscPrcnt: 0,
        VatPrcnt: parseFloat(vatPrcnt) || 0,
        TaxCode: taxCode,
        WhsCode: whsCode
    };
    detalleFactura.push(linea);
    renderTabla();
    showPosToast(`Artículo añadido: ${code}`, "success");
}

// ==========================================
// RENDERIZADO Y CÁLCULOS DINÁMICOS
// ==========================================
function renderTabla() {
    let tbody = $('#tbodyDetalle');
    if(detalleFactura.length === 0) {
        tbody.html(`<tr id="rowEmptyPlaceholder">
            <td colspan="9" class="text-center py-5 text-muted">
                <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                <span class="fw-semibold">No se han añadido artículos a la factura</span>
                <div class="small mt-1">Haga clic en <strong>Agregar Artículos</strong> para buscar en el catálogo</div>
            </td>
        </tr>`);
        $('#lblSubtotal').text('RD$ 0.00');
        $('#lblDescuento').text('RD$ 0.00');
        $('#lblImpuesto').text('RD$ 0.00');
        $('#lblTotal').text('RD$ 0.00');
        return;
    }

    let html = '';
    let subtotal = 0; 
    let descTotal = 0; 
    let impTotal = 0;

    detalleFactura.forEach((l, i) => {
        let descLineaMonto = (l.Quantity * l.PriceBefDi) * (l.DiscPrcnt / 100);
        let lineNeto = (l.Quantity * l.PriceBefDi) - descLineaMonto;
        let impLinea = lineNeto * (l.VatPrcnt / 100);
        let totalLinea = lineNeto + impLinea;

        subtotal += lineNeto; 
        descTotal += descLineaMonto; 
        impTotal += impLinea;

        let inputDesc = '';
        if(l.EditableDesc === 'Y') {
            inputDesc = `<input type="text" class="input-table-desc desc-editable-highlight w-100" value="${l.ItemName}" oninput="detalleFactura[${i}].ItemName=this.value">`;
        } else {
            inputDesc = `<span class="fw-semibold text-dark">${l.ItemName}</span>`;
        }

        html += `<tr>
            <td class="font-monospace fw-bold">${l.ItemCode}</td>
            <td>${inputDesc}</td>
            <td class="text-center"><input type="number" min="1" step="1" class="input-table-qty text-center font-monospace" style="width: 65px;" value="${l.Quantity}" onchange="actualizarCantidad(${i}, this.value)"></td>
            <td class="text-end font-monospace">RD$ ${l.PriceBefDi.toFixed(2)}</td>
            <td class="text-center"><input type="number" min="0" max="100" step="1" class="input-table-disc text-center font-monospace" style="width: 60px;" value="${l.DiscPrcnt}" onchange="actualizarDescuento(${i}, this.value)"></td>
            <td class="text-center font-monospace">${l.VatPrcnt.toFixed(2)}%</td>
            <td class="text-center"><span class="badge bg-light text-dark border">${l.WhsCode}</span></td>
            <td class="fw-bold text-end font-monospace">RD$ ${totalLinea.toFixed(2)}</td>
            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 rounded-2" title="Eliminar fila" onclick="eliminarFila(${i})"><i class="bi bi-trash"></i></button></td>
        </tr>`;
    });
    
    tbody.html(html);

    let descGlobalPct = parseFloat($('#txtDescGlobal').val()) || 0;
    let descGlobal = subtotal * (descGlobalPct / 100);
    let totalGeneral = subtotal - descGlobal + impTotal;

    $('#lblSubtotal').text('RD$ ' + subtotal.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    $('#lblDescuento').text('RD$ ' + (descTotal + descGlobal).toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    $('#lblImpuesto').text('RD$ ' + impTotal.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    $('#lblTotal').text('RD$ ' + totalGeneral.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

    // Recalcular banner del modal ARS si está abierto o si hay payload activo
    if (typeof calcularMontosARS === 'function') {
        calcularMontosARS('init');
    }
}

function actualizarCantidad(idx, val) {
    let qty = parseFloat(val) || 1;
    if(qty < 1) qty = 1;
    detalleFactura[idx].Quantity = qty;
    renderTabla();
}

function actualizarDescuento(idx, val) {
    let disc = parseFloat(val) || 0;
    if(disc < 0) disc = 0;
    if(disc > 100) disc = 100;
    detalleFactura[idx].DiscPrcnt = disc;
    renderTabla();
}

function eliminarFila(idx) {
    detalleFactura.splice(idx, 1);
    renderTabla();
}

// ==========================================
// ADAPTAR BOTÓN DE PROCESAR SEGÚN COBERTURA ARS
// ==========================================
function actualizarBotonProcesar(esCoberturaTotal, copago) {
    let btn = $('#btnProcesarFactura');
    if (!btn.length) return;
    
    if (esCoberturaTotal) {
        btn.html('<i class="bi bi-shield-fill-check fs-5 me-2"></i> REGISTRAR ATENCIÓN ARS (COBERTURA 100%)');
        btn.addClass('btn-cobertura-total');
    } else if (copago && parseFloat(copago) > 0) {
        btn.html('<i class="bi bi-check2-circle fs-5 me-2"></i> PROCESAR FACTURA (COPAGO: RD$ ' + copago + ')');
        btn.removeClass('btn-cobertura-total');
    } else {
        btn.html('<i class="bi bi-check2-circle fs-5 me-2"></i> PROCESAR FACTURA');
        btn.removeClass('btn-cobertura-total');
    }
}

// ==========================================
// INICIAR PROCESO DE FACTURACIÓN
// ==========================================
function iniciarProcesoFactura() {
    let cardCode = $('#txtCardCode').val();
    if (!cardCode) { 
        $('#txtCardName').addClass('is-invalid');
        showPosToast("Debe seleccionar un cliente o paciente para facturar.", "warning");
        abrirModalCliente();
        return; 
    }

    let facSerie = $('#cboTipoNCF').val();
    let esCob100 = (
        typeof payloadAprobacionARS !== 'undefined' && 
        payloadAprobacionARS && 
        (parseFloat(payloadAprobacionARS.PorcentajeCober) >= 99.99 || parseFloat(payloadAprobacionARS.MontoResponsable) <= 0.001) && 
        parseFloat(payloadAprobacionARS.MontoAprobado) > 0
    );

    if (!facSerie && !esCob100) {
        showPosToast("Debe seleccionar un paciente o cliente para amarrar el comprobante fiscal NCF.", "warning");
        abrirModalCliente();
        return;
    }

    if (detalleFactura.length === 0) { 
        showPosToast("Debe agregar al menos un artículo o servicio al detalle.", "warning");
        abrirModalArticulo();
        return; 
    }

    let extraDays = parseInt($('#cboCondicion').attr('data-dias')) || 0;
    let numAprobacion = $('#txtNumAprobacion').val().trim();
    
    let payload = {
        header: {
            CardCode: cardCode, 
            CardName: $('#txtCardName').val(),
            U_FacNit: $('#txtRNC').val(), 
            U_FacNom: $('#txtCardName').val(),
            U_FacSerie: $('#cboTipoNCF').val() || 'B02', 
            ExtraDays: parseInt(extraDays),
            DiscPrcnt: parseFloat($('#txtDescGlobal').val()) || 0, 
            U_SCIASerie: 'F',
            DocDate: $('#txtDocDate').val(),
            U_NumAprobacion: numAprobacion // Número de aprobación (referencia rápida)
        },
        lines: detalleFactura,
        // Payload completo de aprobación ARS (null si no aplica seguro)
        aprobacion_ars: (typeof payloadAprobacionARS !== 'undefined' && payloadAprobacionARS) ? payloadAprobacionARS : null,
        pagos: {}
    };

    let btn = $('#btnProcesarFactura');
    btn.prop('disabled', true).html(esCob100
        ? '<span class="spinner-border spinner-border-sm me-2"></span> Registrando Autorización 100% ARS...'
        : '<span class="spinner-border spinner-border-sm me-2"></span> Generando Factura Fiscal...'
    );

    $.ajax({
        url: 'index.php?action=guardar_factura_venta',
        type: 'POST', 
        contentType: 'application/json',
        dataType: 'json',
        data: JSON.stringify(payload),
        success: function(res) {
            if (res.esCoberturaTotal) {
                showPosToast(res.msg + (res.numAprobacion ? ' | Autorización ARS: ' + res.numAprobacion : ''), "success");
                btn.prop('disabled', false).html('<i class="bi bi-shield-fill-check fs-5 me-2"></i> REGISTRAR ATENCIÓN ARS (COBERTURA 100%)');
                setTimeout(() => location.reload(), 1800);
            } else if (res.redirect) { 
                window.location.href = res.redirect; 
            } else { 
                showPosToast(res.msg + (res.NCF ? ' | NCF: ' + res.NCF : ''), "success");
                setTimeout(() => location.reload(), 1500);
            }
        },
        error: function(err) {
            if (esCob100) {
                btn.prop('disabled', false).html('<i class="bi bi-shield-fill-check fs-5 me-2"></i> REGISTRAR ATENCIÓN ARS (COBERTURA 100%)');
            } else {
                btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-5"></i> PROCESAR FACTURA');
            }
            let errorMsg = "Error al procesar la factura.";
            if (err.responseText) {
                try {
                    let parsed = JSON.parse(err.responseText);
                    errorMsg = parsed.msg || errorMsg;
                } catch(e) {
                    errorMsg = "Respuesta del servidor no válida.";
                }
            }
            showPosToast(errorMsg, "danger");
        }
    });
}
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>