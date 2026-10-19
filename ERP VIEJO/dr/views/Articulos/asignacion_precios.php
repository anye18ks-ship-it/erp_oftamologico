<?php
$listas = $listas ?? [];
$grupos = $grupos ?? [];
$articulos = $articulos ?? [];
$listaSeleccionada = $listaSeleccionada ?? 1;
$nombreListaSeleccionada = $nombreListaSeleccionada ?? 'Lista de Precios';
$filtroGrupo = $filtroGrupo ?? null;

$totalArticulos = $totalArticulos ?? count($articulos);
$conPrecio = $conPrecio ?? 0;
$sinPrecio = $sinPrecio ?? 0;
$margenPromedio = $margenPromedio ?? 0;
?>

<?php require_once __DIR__ . '/../Estructura/header.php'; ?>

<!-- Estilos exclusivos de Asignación de Precios -->
<style>
    <?php include __DIR__ . '/styles/asignacion_precios.css'; ?>
</style>

<div class="precios-container">
    <!-- Header del Módulo -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-tags-fill"></i>
            </div>
            <div>
                <h2 class="page-title">Asignación de Precios & Costos</h2>
                <p class="page-subtitle">Matriz de asignación de precios de venta</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn-save-batch" id="btnGuardarLoteSuperior" onclick="guardarLoteModificados()" disabled>
                <i class="bi bi-save2-fill"></i>
                <span id="textoBotonLote">Sin Cambios Pendientes</span>
            </button>
        </div>
    </div>

    <!-- Mensajes de Sistema -->
    <div id="alertContainer">
        <?php echo Helper::getMensaje(); ?>
    </div>

    <!-- KPIs de la Lista de Precios Activa -->
    <div class="precios-kpi-grid">
        <div class="precios-kpi-card kpi-total">
            <div>
                <div class="kpi-number"><?php echo $totalArticulos; ?></div>
                <div class="kpi-name">Total Artículos</div>
            </div>
            <div class="kpi-icon-wrap">
                <i class="bi bi-boxes"></i>
            </div>
        </div>

        <div class="precios-kpi-card kpi-configured">
            <div>
                <div class="kpi-number"><?php echo $conPrecio; ?></div>
                <div class="kpi-name">Con Precio (&gt; RD$ 0)</div>
            </div>
            <div class="kpi-icon-wrap">
                <i class="bi bi-check2-circle"></i>
            </div>
        </div>

        <div class="precios-kpi-card kpi-pending">
            <div>
                <div class="kpi-number"><?php echo $sinPrecio; ?></div>
                <div class="kpi-name">Sin Precio (RD$ 0)</div>
            </div>
            <div class="kpi-icon-wrap">
                <i class="bi bi-exclamation-circle"></i>
            </div>
        </div>

        <div class="precios-kpi-card kpi-margin">
            <div>
                <div class="kpi-number"><?php echo $margenPromedio; ?>%</div>
                <div class="kpi-name">Margen Promedio Estimado</div>
            </div>
            <div class="kpi-icon-wrap">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros y Selección de Lista de Precios -->
    <div class="filter-bar-precios">
        <div class="filter-left-group">
            <!-- Selector de Lista de Precios -->
            <div class="d-flex align-items-center gap-2">
                <label for="cboListaPrecio" class="fw-bold text-dark small" style="white-space:nowrap;">
                    <i class="bi bi-currency-dollar text-primary"></i> Lista Activa:
                </label>
                <select id="cboListaPrecio" class="filter-select-precios" onchange="cambiarListaPrecio(this.value)">
                    <?php foreach ($listas as $l): ?>
                        <option value="<?php echo $l['ListNum']; ?>" <?php echo ((int)$l['ListNum'] === $listaSeleccionada) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($l['ListNum'] . ' - ' . $l['ListName']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Selector de Grupo de Artículos -->
            <div class="d-flex align-items-center gap-2">
                <label for="cboGrupoArt" class="fw-bold text-dark small" style="white-space:nowrap;">
                    <i class="bi bi-folder2 text-secondary"></i> Grupo:
                </label>
                <select id="cboGrupoArt" class="filter-select-precios" onchange="cambiarFiltroGrupo(this.value)">
                    <option value="">-- Todos los Grupos --</option>
                    <?php foreach ($grupos as $g): ?>
                        <option value="<?php echo $g['ItmsGrpCod']; ?>" <?php echo ($filtroGrupo !== null && (int)$g['ItmsGrpCod'] === (int)$filtroGrupo) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($g['ItmsGrpNam']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Buscador en tiempo real -->
            <div class="search-box-precios">
                <i class="bi bi-search"></i>
                <input type="text" id="filtroTextoPrecios" placeholder="Filtrar por código o descripción en tiempo real..." oninput="filtrarMatrizEnVivo()">
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-secondary border px-3 py-2" id="contadorModificados">0 cambios sin guardar</span>
        </div>
    </div>

    <!-- Tabla Matriz de Precios y Costos -->
    <div class="table-card-precios">
        <div class="table-responsive-precios">
            <table class="table-matrix" id="tablaMatrizPrecios">
                <thead>
                    <tr>
                        <th style="width: 12%;">Código</th>
                        <th style="width: 30%;">Descripción del Artículo</th>
                        <th style="width: 14%;">Grupo</th>
                        <th style="width: 8%; text-align: right;">Stock</th>
                        <th style="width: 14%; text-align: right;">Costo Promedio</th>
                        <th style="width: 14%; text-align: right;">Precio Venta</th>
                        <th style="width: 8%; text-align: center;">Margen %</th>
                        <th style="width: 8%; text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody id="tbodyMatrizPrecios">
                    <?php if (empty($articulos)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">No se encontraron artículos para esta lista de precios.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($articulos as $idx => $a): 
                            $costo = (float)$a['AvgPrice'];
                            $precio = (float)$a['Price'];
                            $stock = (float)$a['OnHand'];
                            $margen = ($precio > 0 && $costo > 0) ? round((($precio - $costo) / $precio) * 100, 1) : 0;

                            $claseMargen = 'badge-margen-zero';
                            if ($precio > 0) {
                                if ($margen >= 30) $claseMargen = 'badge-margen-high';
                                elseif ($margen > 0) $claseMargen = 'badge-margen-mid';
                                else $claseMargen = 'badge-margen-low';
                            }
                        ?>
                            <tr class="fila-matriz" 
                                id="fila_<?php echo htmlspecialchars($a['ItemCode']); ?>"
                                data-codigo="<?php echo strtolower(htmlspecialchars($a['ItemCode'])); ?>"
                                data-nombre="<?php echo strtolower(htmlspecialchars($a['ItemName'])); ?>"
                                data-grupo="<?php echo htmlspecialchars($a['ItmsGrpCod'] ?? ''); ?>">
                                
                                <td class="font-monospace fw-bold text-dark">
                                    <?php echo htmlspecialchars($a['ItemCode']); ?>
                                </td>

                                <td class="fw-semibold text-dark">
                                    <?php echo htmlspecialchars($a['ItemName']); ?>
                                </td>

                                <td>
                                    <span class="badge bg-light text-secondary border small">
                                        <?php echo htmlspecialchars($a['ItmsGrpNam'] ?: 'General'); ?>
                                    </span>
                                </td>

                                <td style="text-align: right; font-family: monospace; font-weight: 600;">
                                    <?php echo number_format($stock, 2); ?>
                                </td>

                                <td style="text-align: right;">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <span class="small text-muted">RD$</span>
                                        <input type="number" step="0.01" min="0" 
                                               class="editable-price-input input-costo" 
                                               id="costo_<?php echo htmlspecialchars($a['ItemCode']); ?>"
                                               value="<?php echo number_format($costo, 2, '.', ''); ?>"
                                               data-original="<?php echo number_format($costo, 2, '.', ''); ?>"
                                               oninput="marcarModificado('<?php echo htmlspecialchars($a['ItemCode']); ?>')">
                                    </div>
                                </td>

                                <td style="text-align: right;">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <span class="small text-muted">RD$</span>
                                        <input type="number" step="0.01" min="0" 
                                               class="editable-price-input input-precio" 
                                               id="precio_<?php echo htmlspecialchars($a['ItemCode']); ?>"
                                               value="<?php echo number_format($precio, 2, '.', ''); ?>"
                                               data-original="<?php echo number_format($precio, 2, '.', ''); ?>"
                                               oninput="marcarModificado('<?php echo htmlspecialchars($a['ItemCode']); ?>')">
                                    </div>
                                </td>

                                <td style="text-align: center;">
                                    <span class="badge-margen <?php echo $claseMargen; ?>" id="badge_margen_<?php echo htmlspecialchars($a['ItemCode']); ?>">
                                        <?php echo $margen; ?>%
                                    </span>
                                </td>

                                <td style="text-align: center;">
                                    <button type="button" class="btn-row-save" 
                                            id="btn_save_<?php echo htmlspecialchars($a['ItemCode']); ?>"
                                            onclick="guardarFilaIndividual('<?php echo htmlspecialchars($a['ItemCode']); ?>')"
                                            title="Guardar precio y costo de este artículo">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación Puro CSS / JS -->
        <div class="pure-pagination-card">
            <div class="d-flex align-items-center gap-2">
                <span>Mostrar</span>
                <select id="itemsPorPaginaPrecios" class="pure-page-btn" onchange="cambiarLimitePaginacionPrecios()">
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="all">Todos</option>
                </select>
                <span>por página</span>
            </div>

            <div id="infoPaginacionPrecios">Mostrando 1 a 25 de <?php echo $totalArticulos; ?> artículos</div>

            <div class="d-flex gap-1" id="navPaginacionPrecios">
                <!-- Se genera dinámicamente con JS -->
            </div>
        </div>
    </div>
</div>

<script>
const listaActualId = <?php echo $listaSeleccionada; ?>;
let itemsModificados = new Set();
let filasFiltradas = [];
let paginaActual = 1;
let porPagina = 25;

document.addEventListener('DOMContentLoaded', () => {
    inicializarPaginacion();
});

function cambiarListaPrecio(listNum) {
    if (itemsModificados.size > 0) {
        if (!confirm("Tiene cambios sin guardar en esta lista. ¿Desea descartarlos y cambiar de lista?")) {
            document.getElementById('cboListaPrecio').value = listaActualId;
            return;
        }
    }
    const grupo = document.getElementById('cboGrupoArt').value;
    window.location.href = `index.php?page=asignacion_precios&lista=${listNum}&grupo=${encodeURIComponent(grupo)}`;
}

function cambiarFiltroGrupo(grupoId) {
    if (itemsModificados.size > 0) {
        if (!confirm("Tiene cambios sin guardar. ¿Desea continuar?")) {
            return;
        }
    }
    window.location.href = `index.php?page=asignacion_precios&lista=${listaActualId}&grupo=${encodeURIComponent(grupoId)}`;
}

function marcarModificado(itemCode) {
    const inputCosto = document.getElementById('costo_' + itemCode);
    const inputPrecio = document.getElementById('precio_' + itemCode);
    const fila = document.getElementById('fila_' + itemCode);
    const badgeMargen = document.getElementById('badge_margen_' + itemCode);

    const origCosto = parseFloat(inputCosto.getAttribute('data-original') || 0);
    const origPrecio = parseFloat(inputPrecio.getAttribute('data-original') || 0);

    const currCosto = parseFloat(inputCosto.value || 0);
    const currPrecio = parseFloat(inputPrecio.value || 0);

    // Calcular margen en tiempo real
    let nuevoMargen = 0;
    if (currPrecio > 0 && currCosto > 0) {
        nuevoMargen = Math.round(((currPrecio - currCosto) / currPrecio) * 100 * 10) / 10;
    }

    badgeMargen.textContent = nuevoMargen + '%';
    badgeMargen.className = 'badge-margen ' + (
        currPrecio <= 0 ? 'badge-margen-zero' :
        (nuevoMargen >= 30 ? 'badge-margen-high' : (nuevoMargen > 0 ? 'badge-margen-mid' : 'badge-margen-low'))
    );

    const huboCambio = (Math.abs(currCosto - origCosto) > 0.001) || (Math.abs(currPrecio - origPrecio) > 0.001);

    if (huboCambio) {
        itemsModificados.add(itemCode);
        fila.classList.add('row-modified');
    } else {
        itemsModificados.delete(itemCode);
        fila.classList.remove('row-modified');
    }

    actualizarBotonLote();
}

function actualizarBotonLote() {
    const btnLote = document.getElementById('btnGuardarLoteSuperior');
    const txtLote = document.getElementById('textoBotonLote');
    const badgeCount = document.getElementById('contadorModificados');

    const total = itemsModificados.size;
    badgeCount.textContent = `${total} cambios sin guardar`;

    if (total > 0) {
        btnLote.disabled = false;
        txtLote.textContent = `Guardar Cambios (${total})`;
        badgeCount.className = 'badge bg-warning text-dark border px-3 py-2 fw-bold';
    } else {
        btnLote.disabled = true;
        txtLote.textContent = 'Sin Cambios Pendientes';
        badgeCount.className = 'badge bg-light text-secondary border px-3 py-2';
    }
}

function guardarFilaIndividual(itemCode) {
    const inputCosto = document.getElementById('costo_' + itemCode);
    const inputPrecio = document.getElementById('precio_' + itemCode);
    const btnSave = document.getElementById('btn_save_' + itemCode);

    const price = parseFloat(inputPrecio.value || 0);
    const avgPrice = parseFloat(inputCosto.value || 0);

    btnSave.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch('index.php?action=guardar_precio_individual', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            ItemCode: itemCode,
            PriceList: listaActualId,
            Price: price,
            AvgPrice: avgPrice
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            inputCosto.setAttribute('data-original', avgPrice.toFixed(2));
            inputPrecio.setAttribute('data-original', price.toFixed(2));
            itemsModificados.delete(itemCode);
            document.getElementById('fila_' + itemCode).classList.remove('row-modified');
            actualizarBotonLote();

            btnSave.classList.add('saved');
            btnSave.innerHTML = '<i class="bi bi-check-all"></i>';
            setTimeout(() => {
                btnSave.classList.remove('saved');
                btnSave.innerHTML = '<i class="bi bi-check2"></i>';
            }, 1800);
        } else {
            alert("Error al guardar: " + data.msg);
            btnSave.innerHTML = '<i class="bi bi-x-circle text-danger"></i>';
        }
    })
    .catch(err => {
        alert("Error de red: " + err.message);
        btnSave.innerHTML = '<i class="bi bi-check2"></i>';
    });
}

function guardarLoteModificados() {
    if (itemsModificados.size === 0) return;

    const payloadItems = [];
    itemsModificados.forEach(code => {
        const inputCosto = document.getElementById('costo_' + code);
        const inputPrecio = document.getElementById('precio_' + code);
        if (inputCosto && inputPrecio) {
            payloadItems.push({
                ItemCode: code,
                AvgPrice: parseFloat(inputCosto.value || 0),
                Price: parseFloat(inputPrecio.value || 0)
            });
        }
    });

    const btnLote = document.getElementById('btnGuardarLoteSuperior');
    btnLote.disabled = true;
    btnLote.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Guardando Lote...';

    fetch('index.php?action=guardar_lote_precios', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            PriceList: listaActualId,
            items: payloadItems
        })
    })
    .then(res => res.json())
    .then(data => {
        btnLote.innerHTML = '<i class="bi bi-save2-fill"></i> <span id="textoBotonLote">Sin Cambios</span>';
        if (data.success) {
            alert(data.msg);
            window.location.reload();
        } else {
            alert("Error al guardar lote: " + data.msg);
            btnLote.disabled = false;
        }
    })
    .catch(err => {
        alert("Error de conexión: " + err.message);
        btnLote.disabled = false;
    });
}

// ==========================================
// FILTRADO Y PAGINACIÓN EN TIEMPO REAL
// ==========================================
function inicializarPaginacion() {
    filasFiltradas = Array.from(document.querySelectorAll('.fila-matriz'));
    paginaActual = 1;
    renderizarPaginacion();
}

function filtrarMatrizEnVivo() {
    const q = document.getElementById('filtroTextoPrecios').value.toLowerCase().trim();
    const todas = Array.from(document.querySelectorAll('.fila-matriz'));

    filasFiltradas = todas.filter(fila => {
        const codigo = fila.getAttribute('data-codigo') || '';
        const nombre = fila.getAttribute('data-nombre') || '';
        return !q || codigo.includes(q) || nombre.includes(q);
    });

    todas.forEach(f => f.style.display = 'none');
    paginaActual = 1;
    renderizarPaginacion();
}

function cambiarLimitePaginacionPrecios() {
    const val = document.getElementById('itemsPorPaginaPrecios').value;
    porPagina = val === 'all' ? -1 : parseInt(val, 10);
    paginaActual = 1;
    renderizarPaginacion();
}

function irAPaginaPrecios(num) {
    paginaActual = num;
    renderizarPaginacion();
}

function renderizarPaginacion() {
    const total = filasFiltradas.length;
    const info = document.getElementById('infoPaginacionPrecios');
    const nav = document.getElementById('navPaginacionPrecios');

    if (total === 0) {
        info.textContent = 'Mostrando 0 registros';
        nav.innerHTML = '';
        return;
    }

    let totalPaginas = (porPagina === -1) ? 1 : Math.ceil(total / porPagina);
    if (paginaActual > totalPaginas) paginaActual = totalPaginas;
    if (paginaActual < 1) paginaActual = 1;

    let inicio = (porPagina === -1) ? 0 : (paginaActual - 1) * porPagina;
    let fin = (porPagina === -1) ? total : Math.min(inicio + porPagina, total);

    document.querySelectorAll('.fila-matriz').forEach(f => f.style.display = 'none');
    for (let i = inicio; i < fin; i++) {
        if (filasFiltradas[i]) filasFiltradas[i].style.display = '';
    }

    info.textContent = `Mostrando ${inicio + 1} a ${fin} de ${total} artículos`;

    let html = '';
    html += `<button type="button" class="pure-page-btn" onclick="irAPaginaPrecios(1)" ${paginaActual === 1 ? 'disabled' : ''}><i class="bi bi-chevron-double-left"></i></button>`;
    html += `<button type="button" class="pure-page-btn" onclick="irAPaginaPrecios(${paginaActual - 1})" ${paginaActual === 1 ? 'disabled' : ''}><i class="bi bi-chevron-left"></i></button>`;

    let inicioB = Math.max(1, paginaActual - 2);
    let finB = Math.min(totalPaginas, inicioB + 4);
    if (finB - inicioB < 4) inicioB = Math.max(1, finB - 4);

    for (let i = inicioB; i <= finB; i++) {
        html += `<button type="button" class="pure-page-btn ${i === paginaActual ? 'active' : ''}" onclick="irAPaginaPrecios(${i})">${i}</button>`;
    }

    html += `<button type="button" class="pure-page-btn" onclick="irAPaginaPrecios(${paginaActual + 1})" ${paginaActual === totalPaginas ? 'disabled' : ''}><i class="bi bi-chevron-right"></i></button>`;
    html += `<button type="button" class="pure-page-btn" onclick="irAPaginaPrecios(${totalPaginas})" ${paginaActual === totalPaginas ? 'disabled' : ''}><i class="bi bi-chevron-double-right"></i></button>`;

    nav.innerHTML = html;
}
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>
