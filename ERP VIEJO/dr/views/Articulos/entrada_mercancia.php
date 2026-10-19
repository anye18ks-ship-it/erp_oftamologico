<?php
$proveedores = $proveedores ?? [];
$almacenes = $almacenes ?? [];
$entradasRecientes = $entradasRecientes ?? [];
?>

<?php require_once __DIR__ . '/../Estructura/header.php'; ?>

<!-- Estilos exclusivos de Entrada de Mercancías -->
<style>
    <?php include __DIR__ . '/styles/entrada_mercancia.css'; ?>
</style>

<div class="entrada-container">
    <!-- Header de la Pantalla -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-box-arrow-in-down"></i>
            </div>
            <div>
                <h2 class="page-title">Entrada de Mercancías & Recepción de Stock</h2>
                <p class="page-subtitle">Recepción de compras, aumento de inventario y recálculo de costo promedio ponderado</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn-primary-action" onclick="activarTabEntrada('tab-registro'); limpiarFormEntrada();">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Nueva Entrada</span>
            </button>
        </div>
    </div>

    <!-- Alertas y Mensajes -->
    <div id="alertContainer">
        <?php echo Helper::getMensaje(); ?>
    </div>

    <!-- Pestañas de Navegación -->
    <div class="module-tabs">
        <button type="button" class="module-tab-btn active" id="btn-tab-registro" onclick="activarTabEntrada('tab-registro')">
            <i class="bi bi-pencil-square"></i>
            <span>Registro de Entrada</span>
        </button>
        <button type="button" class="module-tab-btn" id="btn-tab-historial" onclick="activarTabEntrada('tab-historial')">
            <i class="bi bi-clock-history"></i>
            <span>Historial de Entradas</span>
            <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1"><?php echo count($entradasRecientes); ?></span>
        </button>
    </div>

    <!-- =========================================================================
         PESTAÑA 1: REGISTRO DE ENTRADA DE MERCANCÍA
         ========================================================================= -->
    <div class="tab-content-pane pane-active" id="tab-registro">
        <form id="formEntradaMercancia" onsubmit="event.preventDefault(); guardarEntradaMercancia();">
            
            <!-- DATOS GENERALES DE LA ENTRADA -->
            <div class="modern-card">
                <div class="card-header-pos">
                    <span class="card-title-pos"><i class="bi bi-file-earmark-text text-primary"></i> 1. Datos del Documento & Proveedor</span>
                </div>
                <div class="card-body-pos">
                    <div class="form-grid-4">
                        <!-- Proveedor / Suplidor -->
                        <div class="field-group">
                            <label class="field-label">Proveedor / Suplidor <span class="required">*</span></label>
                            <div class="field-input-wrap">
                                <i class="bi bi-truck field-icon"></i>
                                <select id="entCardCode" class="field-select" required onchange="actualizarNombreProveedor()">
                                    <option value="">-- Seleccione Proveedor --</option>
                                    <?php foreach ($proveedores as $prov): ?>
                                        <option value="<?php echo htmlspecialchars($prov['CardCode']); ?>" data-name="<?php echo htmlspecialchars($prov['CardName']); ?>">
                                            <?php echo htmlspecialchars($prov['CardName'] . ' (' . $prov['CardCode'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <input type="hidden" id="entCardName" value="">
                        </div>

                        <!-- Número de Factura / Conduce del Proveedor -->
                        <div class="field-group">
                            <label class="field-label">N° Factura / Conduce Suplidor <span class="required">*</span></label>
                            <div class="field-input-wrap">
                                <i class="bi bi-receipt field-icon"></i>
                                <input type="text" id="entNumAtCard" class="field-input font-monospace text-uppercase" placeholder="Ej: FAC-9821, E31..." required>
                            </div>
                        </div>

                        <!-- Fecha de Recepción -->
                        <div class="field-group">
                            <label class="field-label">Fecha de Recepción <span class="required">*</span></label>
                            <div class="field-input-wrap">
                                <i class="bi bi-calendar3 field-icon"></i>
                                <input type="date" id="entDocDate" class="field-input" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <!-- Almacén de Destino Predeterminado -->
                        <div class="field-group">
                            <label class="field-label">Almacén Destino Predeterminado <span class="required">*</span></label>
                            <div class="field-input-wrap">
                                <i class="bi bi-building field-icon"></i>
                                <select id="entWhsCode" class="field-select" required>
                                    <?php foreach ($almacenes as $alm): ?>
                                        <option value="<?php echo htmlspecialchars($alm['WhsCode']); ?>">
                                            <?php echo htmlspecialchars($alm['WhsCode'] . ' - ' . $alm['WhsName']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field-group mt-3">
                        <label class="field-label">Comentarios / Observaciones</label>
                        <div class="field-input-wrap">
                            <i class="bi bi-chat-left-text field-icon"></i>
                            <input type="text" id="entComments" class="field-input" placeholder="Detalles de la entrega, lote, o notas adicionales...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- DETALLE DE ARTÍCULOS RECIBIDOS -->
            <div class="modern-card">
                <div class="card-header-pos">
                    <span class="card-title-pos"><i class="bi bi-boxes text-primary"></i> 2. Artículos a Ingresar al Inventario</span>
                    <button type="button" class="btn-primary-action py-1 px-3" onclick="abrirModalArticulosEntrada()">
                        <i class="bi bi-plus-circle"></i>
                        <span>Agregar Artículos</span>
                    </button>
                </div>
                <div class="card-body-pos p-0">
                    <div class="table-pos-wrap">
                        <table class="table-pos-clean" id="tablaLineasEntrada">
                            <thead>
                                <tr>
                                    <th style="width: 12%;">Código</th>
                                    <th style="width: 32%;">Descripción del Artículo</th>
                                    <th style="width: 14%;">Almacén</th>
                                    <th style="width: 12%; text-align: right;">Cantidad</th>
                                    <th style="width: 14%; text-align: right;">Costo Unit. (RD$)</th>
                                    <th style="width: 12%; text-align: right;">Subtotal (RD$)</th>
                                    <th style="width: 4%; text-align: center;"></th>
                                </tr>
                            </thead>
                            <tbody id="tbodyLineasEntrada">
                                <tr id="rowEmptyEntrada">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary opacity-40"></i>
                                        <span class="fw-semibold">No se han añadido artículos para recepción</span>
                                        <div class="small mt-1">Haga clic en <strong>Agregar Artículos</strong> para buscar en el catálogo de compra</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Resumen de Totales y Botón de Procesar -->
                    <div class="summary-receipt-box">
                        <div>
                            <span class="summary-total-label">TOTAL ENTRADA:</span>
                            <span class="summary-total-value ms-2" id="lblTotalEntrada">RD$ 0.00</span>
                        </div>
                        <button type="submit" class="btn-primary-action px-4 py-2" id="btnProcesarEntrada">
                            <i class="bi bi-check2-circle fs-5"></i>
                            <span>Registrar Entrada & Actualizar Stock</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- =========================================================================
         PESTAÑA 2: HISTORIAL DE ENTRADAS RECIENTES (POSOIGN)
         ========================================================================= -->
    <div class="tab-content-pane" id="tab-historial">
        <div class="modern-card">
            <div class="card-header-pos">
                <span class="card-title-pos"><i class="bi bi-journal-text text-primary"></i> Entradas de Mercancías Registradas</span>
                <span class="text-muted small">Últimos registros</span>
            </div>
            <div class="card-body-pos p-0">
                <div class="table-pos-wrap">
                    <table class="table-pos-clean">
                        <thead>
                            <tr>
                                <th>N° Entrada</th>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Doc. / Factura</th>
                                <th style="text-align: center;">Líneas</th>
                                <th style="text-align: right;">Total General</th>
                                <th>Usuario</th>
                                <th style="text-align: center; width: 80px;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($entradasRecientes)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No se registran entradas de mercancía previas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($entradasRecientes as $ent): 
                                    $docNumVal = (string)($ent['DocNum'] ?? $ent['DocEntry'] ?? $ent['C_DocEntry'] ?? '');
                                    $fechaVal  = (string)($ent['FechaFmt'] ?? '');
                                    $cardVal   = (string)($ent['CardName'] ?: ($ent['CardCode'] ?: '—'));
                                    $numAtCard = (string)($ent['NumAtCard'] ?: '—');
                                    $userVal   = (string)($ent['U_user'] ?? '');
                                ?>
                                    <tr>
                                        <td>
                                            <span class="badge-doc">#<?php echo htmlspecialchars($docNumVal); ?></span>
                                        </td>
                                        <td class="font-monospace"><?php echo htmlspecialchars($fechaVal); ?></td>
                                        <td class="fw-semibold text-dark"><?php echo htmlspecialchars($cardVal); ?></td>
                                        <td class="font-monospace text-primary"><?php echo htmlspecialchars($numAtCard); ?></td>
                                        <td style="text-align: center;">
                                            <span class="badge bg-light text-dark border"><?php echo intval($ent['TotalLineas'] ?? 0); ?> arts</span>
                                        </td>
                                        <td style="text-align: right; font-weight: 700; font-family: monospace; color: #0284c7;">
                                            RD$ <?php echo number_format((float)($ent['DocTotal'] ?? 0), 2); ?>
                                        </td>
                                        <td class="text-muted small"><?php echo htmlspecialchars($userVal); ?></td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-2" 
                                                    title="Ver detalle del documento" 
                                                    onclick="verDetalleEntrada(<?php echo intval($ent['C_DocEntry']); ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
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
</div>

<!-- ========================================== -->
<!-- MODAL: AGREGAR ARTÍCULOS A LA ENTRADA     -->
<!-- ========================================== -->
<div class="modal fade" id="modalArticulosEntrada" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header-pos bg-primary text-white py-3 px-4">
                <h5 class="modal-title fw-bold mb-0 text-white"><i class="bi bi-box-seam me-2"></i> Artículos para Recepción de Mercancía</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="modal-filter-toolbar-entrada mb-3">
                    <div class="search-input-modal flex-grow-1">
                        <i class="bi bi-search"></i>
                        <input type="text" id="txtBuscarArticuloEntrada" placeholder="Buscar por código, descripción, código de barras o grupo..." oninput="filtrarArticulosEntradaEnMemoria()">
                    </div>
                    <select id="filtroGrupoEntradaModal" class="filter-select-entrada" onchange="filtrarArticulosEntradaEnMemoria()">
                        <option value="">Todos los Grupos</option>
                        <?php if (!empty($gruposArticulos)): ?>
                            <?php foreach ($gruposArticulos as $grp): ?>
                                <option value="<?php echo htmlspecialchars($grp['ItmsGrpCod']); ?>"><?php echo htmlspecialchars($grp['ItmsGrpNam']); ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <select id="filtroTipoEntradaModal" class="filter-select-entrada" onchange="filtrarArticulosEntradaEnMemoria()">
                        <option value="todos">Todos los Tipos</option>
                        <option value="compra">Solo Compra</option>
                        <option value="inventario">Solo Inventario</option>
                        <option value="favorito">Solo Favoritos ⭐</option>
                        <option value="editable">Solo Editables 📝</option>
                    </select>
                    <select id="filtroStockEntradaModal" class="filter-select-entrada" onchange="filtrarArticulosEntradaEnMemoria()">
                        <option value="todos">Todo Stock</option>
                        <option value="con_stock">Con Existencia (&gt; 0)</option>
                        <option value="sin_stock">Sin Existencia (0)</option>
                    </select>
                    <button type="button" class="btn-refresh-entrada" id="btnRefreshEntradaArticulos" onclick="buscarArticulosEntradaAJAX('', true)" title="Refrescar catálogo">
                        <i class="bi bi-arrow-clockwise fs-6" id="iconRefreshEntrada"></i>
                        <span>Refrescar</span>
                    </button>
                </div>

                <div style="max-height: 48vh; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 10px;">
                    <table class="table-pos-clean">
                        <thead class="sticky-top bg-light">
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Grupo</th>
                                <th style="text-align: right;">Stock Actual</th>
                                <th style="text-align: right;">Costo Actual</th>
                                <th style="text-align: center;">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyArticulosModalEntrada">
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Cargando artículos...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2 px-1 text-muted small" id="infoArticulosEntrada">
                    <span id="lblInfoArticulosEntrada">Mostrando artículos</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL: CONSULTA DE DETALLE DE ENTRADA     -->
<!-- ========================================== -->
<div class="modal fade" id="modalDetalleEntrada" tabindex="-1" aria-labelledby="tituloModalDetalle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header modal-entrada-header">
                <div>
                    <h5 class="modal-entrada-title" id="tituloModalDetalle">
                        <i class="bi bi-box-arrow-in-down text-info"></i> Detalle de Entrada <span id="modalEnt_Numero" class="font-monospace text-warning ms-1">#0000</span>
                    </h5>
                    <span class="text-white-50" style="font-size: 0.8rem;">Recepción de Compras, Aumento de Stock y Recálculo de Costo</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold" style="font-size: 0.8rem;">
                        <i class="bi bi-check-circle-fill me-1"></i> Contabilizado en Stock
                    </span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
            </div>
            
            <div class="modal-body p-4" id="cuerpoModalDetalle">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="text-muted mt-2 fw-semibold">Consultando información detallada...</p>
                </div>
            </div>

            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-outline-secondary px-4 fw-bold" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>


<script>
let lineasEntrada = [];
let almacenesDisponibles = <?php echo json_encode($almacenes); ?>;

function activarTabEntrada(tabId) {
    document.querySelectorAll('.module-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content-pane').forEach(p => p.classList.remove('pane-active'));

    const btn = document.getElementById('btn-' + tabId);
    const pane = document.getElementById(tabId);
    if (btn) btn.classList.add('active');
    if (pane) pane.classList.add('pane-active');
}

function actualizarNombreProveedor() {
    const sel = document.getElementById('entCardCode');
    const opt = sel.options[sel.selectedIndex];
    document.getElementById('entCardName').value = opt ? (opt.getAttribute('data-name') || '') : '';
}

let articulosEntradaGlobal = [];
let articulosEntradaFiltrados = [];

function abrirModalArticulosEntrada() {
    new bootstrap.Modal(document.getElementById('modalArticulosEntrada')).show();
    document.getElementById('txtBuscarArticuloEntrada').value = '';
    const selGrupo = document.getElementById('filtroGrupoEntradaModal');
    if (selGrupo) selGrupo.value = '';
    const selTipo = document.getElementById('filtroTipoEntradaModal');
    if (selTipo) selTipo.value = 'todos';
    const selStock = document.getElementById('filtroStockEntradaModal');
    if (selStock) selStock.value = 'todos';
    buscarArticulosEntradaAJAX('');
    setTimeout(() => {
        const inp = document.getElementById('txtBuscarArticuloEntrada');
        if (inp) inp.focus();
    }, 400);
}

function buscarArticulosEntradaAJAX(q = '', forzarAnim = false) {
    const tbody = document.getElementById('tbodyArticulosModalEntrada');
    const icon = document.getElementById('iconRefreshEntrada');
    if (forzarAnim && icon) icon.classList.add('spin-animation');

    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2 text-primary"></span> Cargando catálogo de artículos...</td></tr>';

    fetch(`index.php?action=buscar_articulo_entrada&q=${encodeURIComponent(q)}`)
        .then(res => res.json())
        .then(data => {
            if (forzarAnim && icon) icon.classList.remove('spin-animation');
            articulosEntradaGlobal = data || [];
            filtrarArticulosEntradaEnMemoria();
        })
        .catch(err => {
            if (forzarAnim && icon) icon.classList.remove('spin-animation');
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger"><i class="bi bi-exclamation-circle me-1"></i> Error al cargar artículos: ${err.message}</td></tr>`;
        });
}

function filtrarArticulosEntradaEnMemoria() {
    const q = (document.getElementById('txtBuscarArticuloEntrada').value || '').trim().toLowerCase();
    const selGrupo = document.getElementById('filtroGrupoEntradaModal');
    const grupo = selGrupo ? selGrupo.value : '';
    const selTipo = document.getElementById('filtroTipoEntradaModal');
    const tipo = selTipo ? selTipo.value : 'todos';
    const selStock = document.getElementById('filtroStockEntradaModal');
    const stock = selStock ? selStock.value : 'todos';

    articulosEntradaFiltrados = articulosEntradaGlobal.filter(item => {
        const nombre = (item.ItemName || '').toLowerCase();
        const codigo = (item.ItemCode || '').toLowerCase();
        const barras = (item.CodeBars || '').toLowerCase();
        const grpNam = (item.ItmsGrpNam || '').toLowerCase();

        const coincideTexto = !q || nombre.includes(q) || codigo.includes(q) || barras.includes(q) || grpNam.includes(q);
        const coincideGrupo = !grupo || (String(item.ItmsGrpCod) === String(grupo));

        let coincideTipo = true;
        if (tipo === 'compra') coincideTipo = (item.PrchseItem === 'Y');
        else if (tipo === 'inventario') coincideTipo = (item.InvntItem === 'Y');
        else if (tipo === 'favorito') coincideTipo = (item.QryGroup2 === 'Y');
        else if (tipo === 'editable') coincideTipo = (item.QryGroup1 === 'Y');

        const onHand = parseFloat(item.OnHand || 0);
        let coincideStock = true;
        if (stock === 'con_stock') coincideStock = (onHand > 0);
        else if (stock === 'sin_stock') coincideStock = (onHand <= 0);

        return coincideTexto && coincideGrupo && coincideTipo && coincideStock;
    });

    renderizarArticulosEntradaModal();
}

function renderizarArticulosEntradaModal() {
    const tbody = document.getElementById('tbodyArticulosModalEntrada');
    const lblInfo = document.getElementById('lblInfoArticulosEntrada');

    if (!articulosEntradaFiltrados || articulosEntradaFiltrados.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No se encontraron artículos con ese criterio.</td></tr>';
        if (lblInfo) lblInfo.textContent = 'Mostrando 0 artículos';
        return;
    }

    let html = '';
    articulosEntradaFiltrados.forEach(item => {
        const stock = parseFloat(item.OnHand || 0).toFixed(2);
        const costo = parseFloat(item.AvgPrice || 0).toFixed(2);
        const safeName = (item.ItemName || '').replace(/"/g, '&quot;').replace(/'/g, "\\'");
        const favStar = item.QryGroup2 === 'Y' ? '<span title="Favorito" class="text-warning me-1">⭐</span>' : '';
        const barrasBadge = item.CodeBars ? `<span class="badge bg-light text-muted border ms-1 font-monospace" style="font-size:0.72rem;"><i class="bi bi-upc me-1"></i>${item.CodeBars}</span>` : '';

        html += `
            <tr>
                <td class="font-monospace fw-bold">${favStar}${item.ItemCode}</td>
                <td class="fw-semibold text-dark">
                    <div class="d-flex align-items-center flex-wrap gap-1">
                        <span>${item.ItemName}</span>
                        ${barrasBadge}
                    </div>
                </td>
                <td><span class="badge bg-light text-secondary border">${item.ItmsGrpNam || 'General'}</span></td>
                <td style="text-align: right; font-family: monospace;">${stock}</td>
                <td style="text-align: right; font-family: monospace; color: #0284c7; font-weight: 700;">RD$ ${costo}</td>
                <td style="text-align: center;">
                    <button type="button" class="btn btn-sm btn-primary py-1 px-3 rounded-2 fw-semibold" 
                            onclick="agregarArticuloALista('${item.ItemCode}', '${safeName}', ${item.AvgPrice || 0}, '${item.DfltWH || ''}')">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
    if (lblInfo) lblInfo.textContent = `Mostrando ${articulosEntradaFiltrados.length} artículo(s) disponible(s)`;
}

function agregarArticuloALista(itemCode, itemName, avgPrice, dfltWh) {
    // Si ya existe en la lista, aumentamos la cantidad
    const existente = lineasEntrada.find(l => l.ItemCode === itemCode);
    const whsDefault = dfltWh || document.getElementById('entWhsCode').value || '01';

    if (existente) {
        existente.Quantity += 1;
    } else {
        lineasEntrada.push({
            ItemCode: itemCode,
            ItemName: itemName,
            Quantity: 1,
            Price: parseFloat(avgPrice) || 0,
            WhsCode: whsDefault
        });
    }

    renderizarLineasEntrada();
    // Feedback sonoro o visual ligero
    const toastElem = document.getElementById('alertContainer');
    if (toastElem) {
        toastElem.innerHTML = `<div class="alert alert-success alert-dismissible fade show py-2 px-3 small" role="alert"><i class="bi bi-check-circle me-1"></i> Artículo <strong>${itemCode}</strong> agregado.<button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button></div>`;
    }
}

function eliminarLineaEntrada(index) {
    lineasEntrada.splice(index, 1);
    renderizarLineasEntrada();
}

function actualizarCantidadLinea(index, val) {
    const qty = parseFloat(val);
    lineasEntrada[index].Quantity = isNaN(qty) || qty <= 0 ? 1 : qty;
    renderizarLineasEntrada();
}

function actualizarCostoLinea(index, val) {
    const cost = parseFloat(val);
    lineasEntrada[index].Price = isNaN(cost) || cost < 0 ? 0 : cost;
    renderizarLineasEntrada();
}

function actualizarAlmacenLinea(index, val) {
    lineasEntrada[index].WhsCode = val;
}

function renderizarLineasEntrada() {
    const tbody = document.getElementById('tbodyLineasEntrada');
    const emptyRow = document.getElementById('rowEmptyEntrada');

    if (lineasEntrada.length === 0) {
        tbody.innerHTML = `
            <tr id="rowEmptyEntrada">
                <td colspan="7" class="text-center py-5 text-muted">
                    <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary opacity-40"></i>
                    <span class="fw-semibold">No se han añadido artículos para recepción</span>
                    <div class="small mt-1">Haga clic en <strong>Agregar Artículos</strong> para buscar en el catálogo de compra</div>
                </td>
            </tr>
        `;
        document.getElementById('lblTotalEntrada').textContent = 'RD$ 0.00';
        return;
    }

    let html = '';
    let totalGeneral = 0;

    lineasEntrada.forEach((line, idx) => {
        const subtotal = line.Quantity * line.Price;
        totalGeneral += subtotal;

        // Opciones de almacenes
        let whsOptions = '';
        almacenesDisponibles.forEach(alm => {
            const sel = (alm.WhsCode === line.WhsCode) ? 'selected' : '';
            whsOptions += `<option value="${alm.WhsCode}" ${sel}>${alm.WhsCode} - ${alm.WhsName}</option>`;
        });

        html += `
            <tr>
                <td class="font-monospace fw-bold">${line.ItemCode}</td>
                <td class="fw-semibold text-dark">${line.ItemName}</td>
                <td>
                    <select class="form-select form-select-sm" onchange="actualizarAlmacenLinea(${idx}, this.value)">
                        ${whsOptions}
                    </select>
                </td>
                <td style="text-align: right;">
                    <input type="number" step="any" min="0.01" class="input-cell" value="${line.Quantity}" onchange="actualizarCantidadLinea(${idx}, this.value)">
                </td>
                <td style="text-align: right;">
                    <input type="number" step="0.01" min="0" class="input-cell" value="${line.Price.toFixed(2)}" onchange="actualizarCostoLinea(${idx}, this.value)">
                </td>
                <td style="text-align: right; font-weight: 700; font-family: monospace; color: #0284c7;">
                    RD$ ${subtotal.toFixed(2)}
                </td>
                <td style="text-align: center;">
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-2" onclick="eliminarLineaEntrada(${idx})" title="Eliminar línea">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    document.getElementById('lblTotalEntrada').textContent = 'RD$ ' + totalGeneral.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function guardarEntradaMercancia() {
    if (lineasEntrada.length === 0) {
        alert("Debe agregar al menos un artículo para registrar la entrada de mercancía.");
        return;
    }

    const cardCode = document.getElementById('entCardCode').value.trim();
    const cardName = document.getElementById('entCardName').value.trim();
    const numAtCard = document.getElementById('entNumAtCard').value.trim();
    const docDate = document.getElementById('entDocDate').value;
    const whsCode = document.getElementById('entWhsCode').value;
    const comments = document.getElementById('entComments').value.trim();

    if (!cardCode) {
        alert("Por favor seleccione un Proveedor / Suplidor.");
        document.getElementById('entCardCode').focus();
        return;
    }

    if (!numAtCard) {
        alert("Por favor ingrese el número de factura o conduce del suplidor.");
        document.getElementById('entNumAtCard').focus();
        return;
    }

    const payload = {
        header: {
            CardCode: cardCode,
            CardName: cardName,
            NumAtCard: numAtCard,
            DocDate: docDate,
            WhsCode: whsCode,
            Comments: comments
        },
        lines: lineasEntrada
    };

    const btn = document.getElementById('btnProcesarEntrada');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando Entrada...';

    fetch('index.php?action=guardar_entrada_mercancia', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle fs-5"></i><span>Registrar Entrada & Actualizar Stock</span>';

        if (data.success) {
            alert(data.msg);
            window.location.href = 'index.php?page=entrada_mercancia';
        } else {
            alert("Error al registrar entrada: " + (data.msg || "Desconocido"));
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle fs-5"></i><span>Registrar Entrada & Actualizar Stock</span>';
        alert("Error de comunicación: " + err.message);
    });
}

function limpiarFormEntrada() {
    document.getElementById('entCardCode').value = '';
    document.getElementById('entCardName').value = '';
    document.getElementById('entNumAtCard').value = '';
    document.getElementById('entComments').value = '';
    lineasEntrada = [];
    renderizarLineasEntrada();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function verDetalleEntrada(id) {
    const modalElem = document.getElementById('modalDetalleEntrada');
    const cuerpo = document.getElementById('cuerpoModalDetalle');
    const numSpan = document.getElementById('modalEnt_Numero');

    if (numSpan) numSpan.innerText = '#' + id;
    cuerpo.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="text-muted mt-2 fw-semibold">Consultando información detallada...</p>
        </div>
    `;

    const bsModal = bootstrap.Modal.getOrCreateInstance(modalElem);
    bsModal.show();

    fetch(`index.php?action=obtener_detalle_entrada&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                cuerpo.innerHTML = `
                    <div class="alert alert-danger d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div>${escapeHtml(data.msg || 'No se pudo cargar el detalle de la entrada.')}</div>
                    </div>
                `;
                return;
            }

            const h = data.header;
            if (numSpan) numSpan.innerText = '#' + (h.DocNum || id);

            // Observaciones
            let htmlComments = '';
            if (h.Comments && h.Comments.trim() !== '') {
                htmlComments = `
                    <div class="modal-comments-box">
                        <i class="bi bi-chat-left-quote fs-5 flex-shrink-0"></i>
                        <div>
                            <strong>Observaciones / Comentarios:</strong>
                            <div class="mt-1">${escapeHtml(h.Comments)}</div>
                        </div>
                    </div>
                `;
            }

            // Filas de artículos
            let totalUnidades = 0;
            let htmlLineas = '';
            (data.lines || []).forEach((l, idx) => {
                const cant = parseFloat(l.Quantity || 0);
                const precio = parseFloat(l.Price || 0);
                const total = parseFloat(l.LineTotal || 0);
                totalUnidades += cant;

                const whsDisplay = l.WhsName ? `${escapeHtml(l.WhsName)} <small class="text-muted font-monospace">(${escapeHtml(l.WhsCode)})</small>` : escapeHtml(l.WhsCode || '01');

                htmlLineas += `
                    <tr>
                        <td class="text-center text-muted fw-bold">${idx + 1}</td>
                        <td>
                            <span class="font-monospace fw-bold text-dark">${escapeHtml(l.ItemCode)}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">${escapeHtml(l.Dscription)}</div>
                        </td>
                        <td>
                            <span class="modal-whs-badge">
                                <i class="bi bi-building"></i> ${whsDisplay}
                            </span>
                        </td>
                        <td class="text-end font-monospace fw-bold text-dark">
                            ${cant.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                        </td>
                        <td class="text-end font-monospace text-secondary">
                            RD$ ${precio.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                        </td>
                        <td class="text-end font-monospace fw-bold" style="color: #0284c7;">
                            RD$ ${total.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                        </td>
                    </tr>
                `;
            });

            const totalGeneral = parseFloat(h.DocTotal || 0);

            cuerpo.innerHTML = `
                <!-- 1. Cuadrícula de Información General (4 Cards) -->
                <div class="row g-3 mb-4">
                    <!-- Proveedor -->
                    <div class="col-md-3">
                        <div class="modal-info-card">
                            <span class="modal-info-label">
                                <i class="bi bi-truck"></i> Proveedor / Suplidor
                            </span>
                            <div class="modal-info-val text-truncate" title="${escapeHtml(h.CardName || '')}">
                                ${escapeHtml(h.CardName || h.CardCode)}
                            </div>
                            <div class="d-flex gap-2 text-muted mt-1" style="font-size: 0.76rem;">
                                ${h.U_NIT ? `<span><i class="bi bi-card-heading me-1"></i>${escapeHtml(h.U_NIT)}</span>` : ''}
                                ${h.Phone1 ? `<span><i class="bi bi-telephone me-1"></i>${escapeHtml(h.Phone1)}</span>` : ''}
                            </div>
                        </div>
                    </div>

                    <!-- Factura / Conduce -->
                    <div class="col-md-3">
                        <div class="modal-info-card">
                            <span class="modal-info-label">
                                <i class="bi bi-receipt"></i> Factura / Conduce
                            </span>
                            <div class="modal-info-val font-monospace text-primary">
                                ${escapeHtml(h.NumAtCard || 'N/A')}
                            </div>
                            <div class="text-muted mt-1" style="font-size: 0.76rem;">
                                <i class="bi bi-upc-scan me-1"></i> DocEntry ERP: <span class="font-monospace fw-bold">${h.C_DocEntry}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Fecha de Recepción -->
                    <div class="col-md-3">
                        <div class="modal-info-card">
                            <span class="modal-info-label">
                                <i class="bi bi-calendar3"></i> Fecha de Recepción
                            </span>
                            <div class="modal-info-val font-monospace">
                                ${escapeHtml(h.FechaFmt)}
                            </div>
                            <div class="text-muted mt-1" style="font-size: 0.76rem;">
                                <i class="bi bi-clock-history me-1"></i> Entrada de Mercancía
                            </div>
                        </div>
                    </div>

                    <!-- Usuario Responsable -->
                    <div class="col-md-3">
                        <div class="modal-info-card">
                            <span class="modal-info-label">
                                <i class="bi bi-person-badge"></i> Usuario / Responsable
                            </span>
                            <div class="modal-info-val text-truncate">
                                ${escapeHtml(h.U_user || 'Sistema')}
                            </div>
                            <div class="text-success mt-1 fw-semibold" style="font-size: 0.76rem;">
                                <i class="bi bi-check2-all me-1"></i> Registrado en inventario
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Observaciones / Comentarios si existen -->
                ${htmlComments}

                <!-- 3. Tabla de Artículos Recibidos -->
                <div class="table-responsive mb-4" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                    <table class="table align-middle mb-0 modal-table-lines">
                        <thead>
                            <tr>
                                <th style="width: 3%;" class="text-center">#</th>
                                <th style="width: 10%;">Código</th>
                                <th style="width: 35%;">Descripción del Artículo</th>
                                <th style="width: 15%;">Almacén Destino</th>
                                <th style="width: 10%;" class="text-end">Cant. Recibida</th>
                                <th style="width: 12%;" class="text-end">Costo Unit.</th>
                                <th style="width: 15%;" class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${htmlLineas}
                        </tbody>
                    </table>
                </div>

                <!-- 4. Resumen y Totales -->
                <div class="row align-items-center justify-content-between g-3">
                    <div class="col-md-6">
                        <div class="d-flex gap-3">
                            <div class="modal-stat-pill">
                                <i class="bi bi-list-ol"></i>
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase">Renglones</div>
                                    <div class="fw-bold font-monospace text-dark">${data.lines ? data.lines.length : 0} ítems</div>
                                </div>
                            </div>
                            <div class="modal-stat-pill">
                                <i class="bi bi-boxes"></i>
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase">Unidades Totales</div>
                                    <div class="fw-bold font-monospace text-dark">${totalUnidades.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="modal-totals-card">
                            <div class="modal-total-row">
                                <span class="fw-semibold">Total Renglones Recibidos:</span>
                                <span class="font-monospace fw-bold text-dark">${data.lines ? data.lines.length : 0}</span>
                            </div>
                            <div class="modal-total-row">
                                <span class="fw-semibold">Total Unidades Físicas:</span>
                                <span class="font-monospace fw-bold text-dark">${totalUnidades.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                            </div>
                            <div class="modal-total-grand">
                                <span>TOTAL ENTRADA:</span>
                                <span class="font-monospace text-success">RD$ ${totalGeneral.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(err => {
            cuerpo.innerHTML = `
                <div class="alert alert-danger d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>Error de comunicación: ${escapeHtml(err.message)}</div>
                </div>
            `;
        });
}
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>
