<?php 
require_once __DIR__ . '/../Estructura/header.php'; 

// Si no hay serie seleccionada en URL, tomar la primera de la lista por defecto si existe
if (!$serieSeleccionada && !empty($series)) {
    $serieSeleccionada = $series[0]['serie'];
    // Obtener sus cargas
    $database = new Database();
    $pdo = $database->getConnection();
    $stmtCargas = $pdo->prepare("SELECT * FROM carga_ncf WHERE serie = :serie ORDER BY id_carga DESC");
    $stmtCargas->execute([':serie' => $serieSeleccionada]);
    $cargas = $stmtCargas->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener datos de la serie activa
$infoSerieActiva = null;
foreach ($series as $s) {
    if ($s['serie'] === $serieSeleccionada) {
        $infoSerieActiva = $s;
        break;
    }
}

// Calcular disponibilidad total de la serie seleccionada
$totalDisponibles = 0;
$totalEmitidos = 0;
foreach ($cargas as $c) {
    $disp = ($c['final'] ?? 0) - ($c['contador'] ?? 0);
    if ($disp > 0) $totalDisponibles += $disp;
    $totalEmitidos += (($c['contador'] ?? 0) - ($c['inicial'] ?? 0));
}
?>

<!-- Estilos exclusivos del módulo NCF -->
<style>
    <?php include __DIR__ . '/styles/ncf.css'; ?>
</style>

<div class="ncf-container">
    <!-- Header de la Pantalla -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-file-earmark-ruled-fill"></i>
            </div>
            <div>
                <h2 class="page-title">Comprobantes Fiscales (NCF)</h2>
                <p class="page-subtitle">Control de secuencias oficiales DGII, rangos autorizados y disponibilidad en tiempo real</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <?php if($serieSeleccionada): ?>
            <?php endif; ?>
            <button type="button" class="btn-primary-action" onclick="activarTabNCF('tab-catalogo'); limpiarFormSerie();">
                <i class="bi bi-tag-fill"></i>
                <span>Nueva Serie</span>
            </button>
        </div>
    </div>

    <!-- Mensajes de Sistema -->
    <?php echo Helper::getMensaje(); ?>

    <!-- Selector Rápido de Serie Activa -->
    <div class="series-nav-bar">
        <div class="series-nav-header">
            <span class="series-nav-title">
                <i class="bi bi-tags text-primary"></i>
                <span>Seleccionar Tipo de Comprobante</span>
            </span>
            <span class="text-muted" style="font-size: 0.8rem;">
                <?php echo count($series); ?> Series Registradas
            </span>
        </div>
        <div class="series-pills-list">
            <?php foreach ($series as $s): 
                $isSelected = ($serieSeleccionada === $s['serie']);
            ?>
                <a href="index.php?page=ncf&serie=<?php echo urlencode($s['serie']); ?>" 
                   class="series-pill-item <?php echo $isSelected ? 'active' : ''; ?>">
                    <span class="series-code"><?php echo htmlspecialchars($s['serie']); ?></span>
                    <span><?php echo htmlspecialchars($s['descripcion']); ?></span>
                    <?php if(($s['activo'] ?? '') !== 'Y'): ?>
                        <small class="badge bg-danger ms-1" style="font-size: 0.65rem;">Inactiva</small>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Pestañas de Vista -->
    <div class="module-tabs">
        <button type="button" class="module-tab-btn active" id="btn-tab-rangos" onclick="activarTabNCF('tab-rangos')">
            <i class="bi bi-stack"></i>
            <span>Historial de Rangos & Stock</span>
        </button>
        <button type="button" class="module-tab-btn" id="btn-tab-catalogo" onclick="activarTabNCF('tab-catalogo')">
            <i class="bi bi-gear-wide-connected"></i>
            <span>Catálogo de Series & Configuración</span>
        </button>
    </div>

    <!-- PESTAÑA 1: CONTROL DE RANGOS DE LA SERIE SELECCIONADA -->
    <div class="tab-content-pane pane-active" id="tab-rangos">
        <?php if($infoSerieActiva): ?>
            <!-- Hero Card Informativo -->
            <div class="serie-hero-card">
                <div class="serie-hero-info">
                    <span class="serie-tag-badge">
                        <i class="bi bi-check2-circle"></i> Serie Activa
                    </span>
                    <h3 class="serie-hero-title"><?php echo htmlspecialchars($infoSerieActiva['serie'] ?? ''); ?> — <?php echo htmlspecialchars($infoSerieActiva['descripcion'] ?? ''); ?></h3>
                    <p class="serie-hero-desc">
                        Longitud: <strong><?php echo $infoSerieActiva['longitud'] ?? 10; ?> dígitos</strong> | 
                        Umbral de Alerta: <strong><?php echo $infoSerieActiva['alerta'] ?? 100; ?> comprobantes</strong>
                    </p>
                </div>

                <div class="serie-hero-stats">
                    <div class="stat-box">
                        <div class="stat-val"><?php echo number_format($totalDisponibles); ?></div>
                        <div class="stat-label">Comprobantes Disponibles</div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Rangos Autorizados -->
            <div class="modern-table-card">
                <div class="modern-table-header">
                    <h3 class="modern-table-title">
                        <i class="bi bi-journal-text text-primary"></i>
                        <span>Autorizaciones Registradas para <?php echo htmlspecialchars($infoSerieActiva['serie'] ?? ''); ?></span>
                    </h3>
                    <button type="button" class="btn-success-action" data-bs-toggle="modal" data-bs-target="#modalNuevoRango">
                        <i class="bi bi-plus-lg"></i>
                        <span>Autorizar Rango</span>
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th style="width: 70px;">ID</th>
                                <th>Fecha Carga</th>
                                <th>Vencimiento DGII</th>
                                <th style="text-align: right;">Inicial</th>
                                <th style="text-align: right;">Último Emitido</th>
                                <th style="text-align: right;">Final</th>
                                <th style="text-align: center;">Disponibles</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($cargas)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox" style="font-size: 2.5rem; opacity: 0.4;"></i>
                                        <h5 class="mt-2 fw-bold text-dark">No hay rangos autorizados para esta serie</h5>
                                        <p style="font-size: 0.85rem;">Haz clic en "Autorizar Rango" para cargar una nueva secuencia autorizada por la DGII.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($cargas as $c): 
                                    $disponibles = $c['final'] - $c['contador'];
                                    $totalRango = ($c['final'] - $c['inicial']) ?: 1;
                                    $porcentajeUsado = min(100, max(0, round((($c['contador'] - $c['inicial']) / $totalRango) * 100)));
                                    
                                    $claseStock = 'stock-ok';
                                    if ($disponibles <= 0) $claseStock = 'stock-danger';
                                    elseif ($disponibles <= ($infoSerieActiva['alerta'] ?? 100)) $claseStock = 'stock-warning';
                                ?>
                                    <tr class="fila-carga">
                                        <td class="text-muted fw-bold">#<?php echo $c['id_carga']; ?></td>
                                        <td>
                                            <i class="bi bi-calendar-event text-muted me-1"></i>
                                            <?php echo substr($c['fecha_carga'], 0, 10); ?>
                                        </td>
                                        <td>
                                            <?php if($c['vencimiento']): ?>
                                                <span class="badge bg-light text-dark border">
                                                    <i class="bi bi-clock-history me-1"></i><?php echo substr($c['vencimiento'], 0, 10); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Sin límite</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right; font-family: monospace; font-weight: 600;">
                                            <?php echo number_format($c['inicial']); ?>
                                        </td>
                                        <td style="text-align: right; font-family: monospace; font-weight: 800; color: #0284c7;">
                                            <?php echo number_format($c['contador']); ?>
                                        </td>
                                        <td style="text-align: right; font-family: monospace; font-weight: 600;">
                                            <?php echo number_format($c['final']); ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <div class="d-flex flex-column align-items-center">
                                                <span class="stock-badge <?php echo $claseStock; ?>">
                                                    <?php echo number_format($disponibles); ?> restantes
                                                </span>
                                                <div class="ncf-progress-wrap" title="<?php echo $porcentajeUsado; ?>% utilizado">
                                                    <div class="ncf-progress-bar" style="width: <?php echo $porcentajeUsado; ?>%;"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-1 text-muted" style="font-size: 0.84rem;">
                                                <i class="bi bi-person"></i>
                                                <span><?php echo htmlspecialchars($c['usuario'] ?? 'Sistema'); ?></span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINACIÓN PURO CSS PARA RANGOS -->
                <?php if(!empty($cargas)): ?>
                <div class="pure-pagination-card">
                    <div class="pure-length-selector">
                        <span>Mostrar</span>
                        <select id="cargasPorPagina" class="pure-select-input" onchange="cambiarLimitePaginacionCargas()">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">Todos</option>
                        </select>
                        <span>rangos por página</span>
                    </div>

                    <div class="pure-pagination-info" id="purePaginacionInfoCargas">
                        Mostrando 1 a <?php echo min(25, count($cargas)); ?> de <?php echo count($cargas); ?> rangos
                    </div>

                    <div class="pure-pagination-nav" id="purePaginacionNavCargas">
                        <!-- Los botones de página se generan dinámicamente -->
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-tags text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                <h4 class="mt-3 fw-bold">No hay series registradas</h4>
                <p class="text-muted">Crea tu primer tipo de comprobante en la pestaña de configuración.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- PESTAÑA 2: CATÁLOGO DE SERIES Y CONFIGURACIÓN -->
    <div class="tab-content-pane" id="tab-catalogo">
        <div class="catalog-layout-grid">
            <!-- Formulario de Serie -->
            <div class="form-card-box">
                <div class="form-card-header">
                    <h4 class="form-card-title" id="titulo_form_serie">
                        <i class="bi bi-tag-fill"></i>
                        <span>Tipo de Comprobante</span>
                    </h4>
                </div>
                <div class="form-card-body">
                    <form action="index.php?action=guardar_serie" method="POST" id="formSerie">
                        <input type="hidden" name="serie_original" id="serie_original" value="">

                        <div class="field-group">
                            <label class="field-label">Código Serie Electrónica (Ej: E31, E32, E34, E44, E45)</label>
                            <input type="text" name="serie" id="serie" class="field-input text-uppercase fw-bold" maxlength="3" placeholder="E31" required>
                        </div>

                        <div class="field-group">
                            <label class="field-label">Descripción Oficial DGII (e-CF)</label>
                            <input type="text" name="descripcion" id="descripcion" class="field-input" placeholder="Ej: Factura de Crédito Fiscal Electrónica" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="field-label">Longitud</label>
                                <input type="number" name="longitud" id="longitud" class="field-input" value="10" required>
                            </div>
                            <div class="col-6">
                                <label class="field-label">Alerta Stock</label>
                                <input type="number" name="alerta" id="alerta" class="field-input" value="100" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="d-flex align-items-center gap-2" style="cursor: pointer;">
                                <input type="checkbox" name="activo" id="activo" checked style="width: 17px; height: 17px; accent-color: #0284c7;">
                                <span style="font-size: 0.88rem; font-weight: 600; color: #334155;">Serie activa para facturar</span>
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn-primary-action w-100 justify-content-center" id="btnGuardarSerie">
                                <i class="bi bi-save"></i>
                                <span>Guardar Serie</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarFormSerie()" title="Limpiar formulario">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Listado de Series Existentes -->
            <div class="modern-table-card">
                <div class="modern-table-header">
                    <h3 class="modern-table-title">
                        <i class="bi bi-list-check text-primary"></i>
                        <span>Series en el Sistema</span>
                    </h3>
                </div>
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Longitud</th>
                                <th>Alerta</th>
                                <th>Estado</th>
                                <th style="text-align: center;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($series as $s): ?>
                                <tr class="fila-serie">
                                    <td class="fw-bold" style="font-family: monospace; font-size: 0.95rem; color: #0284c7;">
                                        <?php echo htmlspecialchars($s['serie']); ?>
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <?php echo htmlspecialchars($s['descripcion']); ?>
                                    </td>
                                    <td><?php echo $s['longitud']; ?> dígitos</td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?php echo $s['alerta']; ?> uds</span>
                                    </td>
                                    <td>
                                        <?php if(($s['activo'] ?? '') === 'Y'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Activa</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" 
                                                class="btn-table-action" 
                                                title="Editar serie"
                                                onclick='editarSerie(<?php echo htmlspecialchars(json_encode($s)); ?>)'>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINACIÓN PURO CSS PARA SERIES -->
                <?php if(!empty($series)): ?>
                <div class="pure-pagination-card">
                    <div class="pure-length-selector">
                        <span>Mostrar</span>
                        <select id="seriesPorPagina" class="pure-select-input" onchange="cambiarLimitePaginacionSeries()">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="all">Todos</option>
                        </select>
                        <span>series por página</span>
                    </div>

                    <div class="pure-pagination-info" id="purePaginacionInfoSeries">
                        Mostrando 1 a <?php echo min(25, count($series)); ?> de <?php echo count($series); ?> series
                    </div>

                    <div class="pure-pagination-nav" id="purePaginacionNavSeries">
                        <!-- Los botones de página se generan dinámicamente -->
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- MODAL MODERNO PARA AUTORIZAR NUEVO RANGO -->
<?php if($serieSeleccionada): ?>
<div class="modal fade" id="modalNuevoRango" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 18px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
            <form action="index.php?action=guardar_rango" method="POST">
                <div class="modal-header" style="background: linear-gradient(135deg, #0a2540 0%, #103b66 100%); color: #ffffff; padding: 18px 24px;">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-warning"></i>
                        <span>Autorizar Secuencia: <?php echo htmlspecialchars($serieSeleccionada); ?></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="padding: 24px;">
                    <input type="hidden" name="serie" value="<?php echo htmlspecialchars($serieSeleccionada); ?>">

                    <div class="mb-3">
                        <label class="field-label">Fecha de Vencimiento de la Autorización DGII</label>
                        <input type="date" name="vencimiento" class="field-input" required>
                        <small class="text-muted" style="font-size: 0.76rem;">Fecha límite emitida en la resolución de la DGII.</small>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="field-label">Secuencia Inicial</label>
                            <input type="number" name="inicial" class="field-input" placeholder="Ej: 1" required min="1">
                        </div>
                        <div class="col-6">
                            <label class="field-label">Secuencia Final</label>
                            <input type="number" name="final" class="field-input" placeholder="Ej: 500" required min="1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 14px 24px;">
                    <button type="button" class="btn btn-light border fw-semibold px-3" data-bs-dismiss="modal" style="border-radius: 10px;">Cancelar</button>
                    <button type="submit" class="btn-success-action">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Guardar Autorización</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    // ==========================================
    // ESTADO DE PAGINACIÓN PURO CSS (NCF)
    // ==========================================
    let paginaActualCargas = 1;
    let registrosPorPaginaCargas = 25;

    let paginaActualSeries = 1;
    let registrosPorPaginaSeries = 25;

    document.addEventListener('DOMContentLoaded', function() {
        renderizarPaginacionCargas();
        renderizarPaginacionSeries();
    });

    function activarTabNCF(tabId) {
        document.querySelectorAll('.module-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content-pane').forEach(p => p.classList.remove('pane-active'));

        if (tabId === 'tab-rangos') {
            document.getElementById('btn-tab-rangos').classList.add('active');
            document.getElementById('tab-rangos').classList.add('pane-active');
            renderizarPaginacionCargas();
        } else {
            document.getElementById('btn-tab-catalogo').classList.add('active');
            document.getElementById('tab-catalogo').classList.add('pane-active');
            renderizarPaginacionSeries();
        }
    }

    function editarSerie(data) {
        activarTabNCF('tab-catalogo');
        document.getElementById('serie_original').value = data.serie;
        document.getElementById('serie').value = data.serie;
        document.getElementById('descripcion').value = data.descripcion;
        document.getElementById('longitud').value = data.longitud;
        document.getElementById('alerta').value = data.alerta;
        document.getElementById('activo').checked = (data.activo === 'Y');
        
        document.getElementById('serie').setAttribute('readonly', true);
        document.getElementById('titulo_form_serie').innerHTML = '<i class="bi bi-pencil-square"></i> <span>Modificar Serie: ' + data.serie + '</span>';
        document.getElementById('btnGuardarSerie').innerHTML = '<i class="bi bi-arrow-repeat"></i> <span>Actualizar Serie</span>';
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function limpiarFormSerie() {
        document.getElementById('formSerie').reset();
        document.getElementById('serie_original').value = '';
        document.getElementById('serie').removeAttribute('readonly');
        document.getElementById('titulo_form_serie').innerHTML = '<i class="bi bi-tag-fill"></i> <span>Tipo de Comprobante</span>';
        document.getElementById('btnGuardarSerie').innerHTML = '<i class="bi bi-save"></i> <span>Guardar Serie</span>';
        document.getElementById('activo').checked = true;
    }

    // ==========================================
    // PAGINACIÓN PARA TABLA DE RANGOS
    // ==========================================
    function cambiarLimitePaginacionCargas() {
        const val = document.getElementById('cargasPorPagina').value;
        registrosPorPaginaCargas = (val === 'all') ? -1 : parseInt(val, 10);
        paginaActualCargas = 1;
        renderizarPaginacionCargas();
    }

    function irAPaginaCargas(numPagina) {
        paginaActualCargas = numPagina;
        renderizarPaginacionCargas();
    }

    function renderizarPaginacionCargas() {
        const filas = Array.from(document.querySelectorAll('.fila-carga'));
        const total = filas.length;
        const info = document.getElementById('purePaginacionInfoCargas');
        const nav = document.getElementById('purePaginacionNavCargas');

        if (total === 0) {
            if (info) info.textContent = 'Mostrando 0 rangos';
            if (nav) nav.innerHTML = '';
            return;
        }

        let totalPaginas = (registrosPorPaginaCargas === -1) ? 1 : Math.ceil(total / registrosPorPaginaCargas);
        if (paginaActualCargas > totalPaginas) paginaActualCargas = totalPaginas;
        if (paginaActualCargas < 1) paginaActualCargas = 1;

        let inicio = (registrosPorPaginaCargas === -1) ? 0 : (paginaActualCargas - 1) * registrosPorPaginaCargas;
        let fin = (registrosPorPaginaCargas === -1) ? total : Math.min(inicio + registrosPorPaginaCargas, total);

        filas.forEach((fila, index) => {
            fila.style.display = (index >= inicio && index < fin) ? '' : 'none';
        });

        if (info) info.textContent = `Mostrando ${inicio + 1} a ${fin} de ${total} rangos`;

        let htmlNav = '';
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaCargas(1)" ${paginaActualCargas === 1 ? 'disabled' : ''} title="Primera página"><i class="bi bi-chevron-double-left"></i></button>`;
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaCargas(${paginaActualCargas - 1})" ${paginaActualCargas === 1 ? 'disabled' : ''} title="Página anterior"><i class="bi bi-chevron-left"></i></button>`;

        let maxBotones = 5;
        let inicioPag = Math.max(1, paginaActualCargas - Math.floor(maxBotones / 2));
        let finPag = Math.min(totalPaginas, inicioPag + maxBotones - 1);
        if (finPag - inicioPag + 1 < maxBotones) {
            inicioPag = Math.max(1, finPag - maxBotones + 1);
        }

        for (let i = inicioPag; i <= finPag; i++) {
            const esActiva = (i === paginaActualCargas) ? 'active' : '';
            htmlNav += `<button type="button" class="pure-page-btn ${esActiva}" onclick="irAPaginaCargas(${i})">${i}</button>`;
        }

        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaCargas(${paginaActualCargas + 1})" ${paginaActualCargas === totalPaginas ? 'disabled' : ''} title="Página siguiente"><i class="bi bi-chevron-right"></i></button>`;
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaCargas(${totalPaginas})" ${paginaActualCargas === totalPaginas ? 'disabled' : ''} title="Última página"><i class="bi bi-chevron-double-right"></i></button>`;

        if (nav) nav.innerHTML = htmlNav;
    }

    // ==========================================
    // PAGINACIÓN PARA TABLA DE SERIES
    // ==========================================
    function cambiarLimitePaginacionSeries() {
        const val = document.getElementById('seriesPorPagina').value;
        registrosPorPaginaSeries = (val === 'all') ? -1 : parseInt(val, 10);
        paginaActualSeries = 1;
        renderizarPaginacionSeries();
    }

    function irAPaginaSeries(numPagina) {
        paginaActualSeries = numPagina;
        renderizarPaginacionSeries();
    }

    function renderizarPaginacionSeries() {
        const filas = Array.from(document.querySelectorAll('.fila-serie'));
        const total = filas.length;
        const info = document.getElementById('purePaginacionInfoSeries');
        const nav = document.getElementById('purePaginacionNavSeries');

        if (total === 0) {
            if (info) info.textContent = 'Mostrando 0 series';
            if (nav) nav.innerHTML = '';
            return;
        }

        let totalPaginas = (registrosPorPaginaSeries === -1) ? 1 : Math.ceil(total / registrosPorPaginaSeries);
        if (paginaActualSeries > totalPaginas) paginaActualSeries = totalPaginas;
        if (paginaActualSeries < 1) paginaActualSeries = 1;

        let inicio = (registrosPorPaginaSeries === -1) ? 0 : (paginaActualSeries - 1) * registrosPorPaginaSeries;
        let fin = (registrosPorPaginaSeries === -1) ? total : Math.min(inicio + registrosPorPaginaSeries, total);

        filas.forEach((fila, index) => {
            fila.style.display = (index >= inicio && index < fin) ? '' : 'none';
        });

        if (info) info.textContent = `Mostrando ${inicio + 1} a ${fin} de ${total} series`;

        let htmlNav = '';
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaSeries(1)" ${paginaActualSeries === 1 ? 'disabled' : ''} title="Primera página"><i class="bi bi-chevron-double-left"></i></button>`;
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaSeries(${paginaActualSeries - 1})" ${paginaActualSeries === 1 ? 'disabled' : ''} title="Página anterior"><i class="bi bi-chevron-left"></i></button>`;

        let maxBotones = 5;
        let inicioPag = Math.max(1, paginaActualSeries - Math.floor(maxBotones / 2));
        let finPag = Math.min(totalPaginas, inicioPag + maxBotones - 1);
        if (finPag - inicioPag + 1 < maxBotones) {
            inicioPag = Math.max(1, finPag - maxBotones + 1);
        }

        for (let i = inicioPag; i <= finPag; i++) {
            const esActiva = (i === paginaActualSeries) ? 'active' : '';
            htmlNav += `<button type="button" class="pure-page-btn ${esActiva}" onclick="irAPaginaSeries(${i})">${i}</button>`;
        }

        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaSeries(${paginaActualSeries + 1})" ${paginaActualSeries === totalPaginas ? 'disabled' : ''} title="Página siguiente"><i class="bi bi-chevron-right"></i></button>`;
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaSeries(${totalPaginas})" ${paginaActualSeries === totalPaginas ? 'disabled' : ''} title="Última página"><i class="bi bi-chevron-double-right"></i></button>`;

        if (nav) nav.innerHTML = htmlNav;
    }
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>