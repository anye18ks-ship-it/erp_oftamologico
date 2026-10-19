<?php 
// ==========================================================================
// CONTROLADOR Y DATOS DEL ARTÍCULO SELECCIONADO
// ==========================================================================
$art = $articuloSeleccionado ?? null;
$val = [
    'ItemCode' => $art['ItemCode'] ?? '',
    'ItemName' => $art['ItemName'] ?? '',
    'FrgnName' => $art['FrgnName'] ?? '',
    'ItmsGrpCod' => $art['ItmsGrpCod'] ?? '',
    'CodeBars' => $art['CodeBars'] ?? '',
    'TaxCodeAr' => $art['TaxCodeAr'] ?? '',
    'DfltWH' => $art['DfltWH'] ?? '',
    'AvgPrice' => number_format((float)($art['AvgPrice'] ?? 0), 2),
    'OnHand' => number_format((float)($art['OnHand'] ?? 0), 2)
];

$esEdicion = ($art !== null);
$readonlyCode = $esEdicion ? 'readonly' : '';

// Función para extraer prefijo dinámico del grupo de artículos
function obtenerPrefijoGrupoArt(?string $nombreGrupo): string {
    if (empty($nombreGrupo)) return 'SV';
    $norm = preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($nombreGrupo, ENT_QUOTES, 'UTF-8'));
    $letras = strtoupper(preg_replace('/[^A-Z]/i', '', $norm));
    if (empty($letras)) return 'SV';

    // Si el grupo es o comienza por "SERVICIO", asignar prefijo "SV"
    if (strpos($letras, 'SERVICIO') === 0 || $letras === 'SRV') {
        return 'SV';
    }
    if (strlen($letras) >= 2) {
        return substr($letras, 0, min(3, strlen($letras)));
    }
    return 'ART';
}

// Mapa de grupos [ItmsGrpCod => ItmsGrpNam]
$mapaGrupos = [];
foreach (($todosGrupos ?? []) as $g) {
    $mapaGrupos[(string)$g['ItmsGrpCod']] = (string)$g['ItmsGrpNam'];
}

// Grupo por defecto para nuevo artículo: primer grupo existente en posOITB
$primerGrupoKey = !empty($mapaGrupos) ? (string)array_key_first($mapaGrupos) : '';
$grupoSeleccionadoInicial = ($val['ItmsGrpCod'] !== '' && $val['ItmsGrpCod'] !== null) ? (string)$val['ItmsGrpCod'] : ($defaultGrp ?? $primerGrupoKey);
$nombreGrupoInicial = $mapaGrupos[$grupoSeleccionadoInicial] ?? '';
$prefijoInicial = obtenerPrefijoGrupoArt($nombreGrupoInicial);

// Cálculo de secuencia automática para nuevo artículo en base al prefijo del grupo
$maxCodeNum = 0;
$regexPrefijo = '/^' . preg_quote($prefijoInicial, '/') . '(\d+)$/i';
foreach (($articulos ?? []) as $aItem) {
    $cCode = trim($aItem['ItemCode'] ?? '');
    if (preg_match($regexPrefijo, $cCode, $matches)) {
        $nCode = (int)$matches[1];
        if ($nCode > $maxCodeNum) $maxCodeNum = $nCode;
    }
}
$siguienteItemCode = $prefijoInicial . str_pad($maxCodeNum + 1, 4, '0', STR_PAD_LEFT);

// Si es nuevo artículo, precargar el código secuencial sugerido y el grupo inicial
if (!$esEdicion && empty($val['ItemCode'])) {
    $val['ItemCode'] = $siguienteItemCode;
    if ($val['ItmsGrpCod'] === '' || $val['ItmsGrpCod'] === null) {
        $val['ItmsGrpCod'] = $grupoSeleccionadoInicial;
    }
}

// Estado inicial de naturaleza del ítem: en creación se predetermina Servicio Clínico (oftalmología)
$esServicioInicial = $esEdicion ? (($art['InvntItem'] ?? 'N') === 'N') : true;

// Almacenes permitidos: en creación están todos deshabilitados excepto el principal
$almacenesMarcados = $esEdicion ? ($almacenesItem ?? []) : [];
if (empty($almacenesMarcados) && !empty($val['DfltWH'])) {
    $almacenesMarcados = [$val['DfltWH']];
}

// Helper para verificar checkboxes 'Y' / 'N'
function chkY($val, $field) { return ($val === 'Y') ? 'checked' : ''; }

// Total de artículos para la pestaña y paginación
$totalArticulos = count($articulos ?? []);

// Mapa de Propiedades Principales SAP (QryGroup)
$propiedadesPrincipalesArt = [
    1 => [
        'label' => 'Descripción Editable',
        'desc' => 'Permite modificar la descripción o concepto directamente al momento de facturar',
        'icon' => 'bi-pencil-square',
        'destacada' => true
    ],
    2 => [
        'label' => 'Artículo Favorito / Catálogo Rápido',
        'desc' => 'Destaca el artículo en accesos directos y panel de venta rápida',
        'icon' => 'bi-star-fill',
        'destacada' => true
    ]
];
?>

<?php require_once __DIR__ . '/../Estructura/header.php'; ?>

<!-- Estilos exclusivos del módulo Artículos -->
<style>
    <?php include __DIR__ . '/styles/articulos.css'; ?>
</style>

<div class="articulos-container">
    <!-- Header de la Pantalla -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div>
                <h2 class="page-title">Catálogo de Artículos</h2>
                <p class="page-subtitle">Gestión integral de productos, servicios, inventario y listas de precios</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn-primary-action" onclick="activarTabArticulo('tab-form'); limpiarFormArticulo();">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Nuevo Artículo</span>
            </button>
        </div>
    </div>

    <!-- Mensajes de Sistema -->
    <?php echo Helper::getMensaje(); ?>



    <!-- Pestañas de Navegación -->
    <div class="module-tabs">
        <button type="button" class="module-tab-btn <?php echo !$esEdicion ? 'active' : ''; ?>" id="btn-tab-list" onclick="activarTabArticulo('tab-list')">
            <i class="bi bi-list-columns-reverse"></i>
            <span>Directorio de Artículos</span>
            <span class="tab-badge"><?php echo $totalArticulos; ?></span>
        </button>
        <button type="button" class="module-tab-btn <?php echo $esEdicion ? 'active' : ''; ?>" id="btn-tab-form" onclick="activarTabArticulo('tab-form')">
            <i class="bi bi-box-seam"></i>
            <span id="tab-form-title"><?php echo $esEdicion ? 'Editando: ' . htmlspecialchars(substr($val['ItemCode'] . ' - ' . $val['ItemName'], 0, 24)) : 'Ficha del Artículo'; ?></span>
        </button>
    </div>

    <!-- =======================================================================
         PESTAÑA 1: DIRECTORIO DE ARTÍCULOS CON FILTROS Y PAGINACIÓN PURO CSS
         ======================================================================= -->
    <div class="tab-content-pane <?php echo !$esEdicion ? 'pane-active' : ''; ?>" id="tab-list">
        <!-- Barra de Filtros -->
        <div class="table-filter-bar">
            <div class="filter-group-left">
                <div class="search-input-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="filtroTextoArt" placeholder="Buscar por código, descripción, código de barras o grupo..." oninput="filtrarArticulos()">
                </div>
                <div style="min-width: 180px;">
                    <?php echo $comboFiltroGrupo; ?>
                </div>
                <select id="filtroTipoArt" class="filter-select" onchange="filtrarArticulos()">
                    <option value="todos">Todos los Tipos</option>
                    <option value="servicio">Solo Servicios Clínicos 🩺</option>
                    <option value="inventario">Solo Inventariables 📦</option>
                    <option value="venta">Solo Venta</option>
                    <option value="compra">Solo Compra</option>
                    <option value="favorito">Solo Favoritos ⭐</option>
                    <option value="editable">Solo Editables 📝</option>
                </select>
                <select id="filtroStockArt" class="filter-select" onchange="filtrarArticulos()">
                    <option value="todos">Todo Stock</option>
                    <option value="con_stock">Con Existencia (&gt; 0)</option>
                    <option value="sin_stock">Sin Existencia (0)</option>
                </select>
            </div>
            <div class="filter-group-right">
                <button type="button" class="btn-secondary-action" onclick="limpiarFiltrosArticulos()">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Restablecer</span>
                </button>
            </div>
        </div>

        <!-- Tabla de Artículos -->
        <div class="modern-table-card">
            <div class="modern-table-header">
                <h3 class="modern-table-title">
                    <i class="bi bi-grid-3x3-gap-fill text-primary"></i>
                    <span>Artículos Registrados</span>
                </h3>
                <span class="text-muted" style="font-size: 0.82rem;" id="contador-articulos-resumen">
                    Mostrando <?php echo $totalArticulos; ?> artículos
                </span>
            </div>

            <div class="table-responsive" style="max-height: 620px; overflow-y: auto;">
                <table class="custom-table" id="tablaArticulos">
                    <thead>
                        <tr>
                            <th>Artículo</th>
                            <th>Grupo / Impuesto</th>
                            <th style="text-align: center;">Clasificación</th>
                            <th style="text-align: center;">Propiedades</th>
                            <th style="text-align: right;">Stock Actual</th>
                            <th style="text-align: right;">Costo Prom.</th>
                            <th style="text-align: center; width: 70px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyArticulos">
                        <?php foreach ($articulos as $a): 
                            $esVenta = ($a['SellItem'] ?? '') === 'Y';
                            $esCompra = ($a['PrchseItem'] ?? '') === 'Y';
                            $esInv = ($a['InvntItem'] ?? '') === 'Y';
                            $esFav = ($a['QryGroup2'] ?? '') === 'Y';
                            $esEdit = ($a['QryGroup1'] ?? '') === 'Y';
                            $stock = (float)($a['OnHand'] ?? 0);
                            $costo = (float)($a['AvgPrice'] ?? 0);
                            $inicial = mb_strtoupper(mb_substr(trim($a['ItemName'] ?? 'A'), 0, 1, 'UTF-8'), 'UTF-8');
                        ?>
                            <tr class="fila-articulo <?php echo ($esEdicion && $a['ItemCode'] == $val['ItemCode']) ? 'row-selected' : ''; ?>" 
                                data-nombre="<?php echo strtolower(htmlspecialchars($a['ItemName'] ?? '')); ?>"
                                data-codigo="<?php echo strtolower(htmlspecialchars($a['ItemCode'] ?? '')); ?>"
                                data-frgn="<?php echo strtolower(htmlspecialchars($a['FrgnName'] ?? '')); ?>"
                                data-codebars="<?php echo strtolower(htmlspecialchars($a['CodeBars'] ?? '')); ?>"
                                data-grupo="<?php echo htmlspecialchars($a['ItmsGrpCod'] ?? ''); ?>"
                                data-venta="<?php echo $esVenta ? '1' : '0'; ?>"
                                data-compra="<?php echo $esCompra ? '1' : '0'; ?>"
                                data-inventario="<?php echo $esInv ? '1' : '0'; ?>"
                                data-favorito="<?php echo $esFav ? '1' : '0'; ?>"
                                data-editable="<?php echo $esEdit ? '1' : '0'; ?>"
                                data-stock="<?php echo ($stock > 0) ? 'con_stock' : 'sin_stock'; ?>">
                                
                                <td>
                                    <div class="articulo-cell-wrap">
                                        <div class="articulo-table-avatar">
                                            <?php echo $inicial; ?>
                                        </div>
                                        <div class="articulo-cell-meta">
                                            <div class="articulo-name"><?php echo htmlspecialchars($a['ItemName']); ?></div>
                                            <div class="articulo-code">
                                                <span><?php echo htmlspecialchars($a['ItemCode']); ?></span>
                                                <?php if(!empty($a['CodeBars'])): ?>
                                                    <span class="barcode-badge"><i class="bi bi-upc me-1"></i><?php echo htmlspecialchars($a['CodeBars']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div>
                                            <span class="badge-tag badge-grupo">
                                                <i class="bi bi-folder2 me-1"></i><?php echo htmlspecialchars($a['ItmsGrpNam'] ?? ('Grupo ' . $a['ItmsGrpCod'])); ?>
                                            </span>
                                        </div>
                                        <?php if(!empty($a['TaxCodeAr'])): ?>
                                            <div>
                                                <span class="badge-tag badge-impuesto">
                                                    <i class="bi bi-percent me-1"></i><?php echo htmlspecialchars($a['TaxCodeAr']); ?> (<?php echo number_format((float)($a['TaxRate'] ?? 0), 0); ?>%)
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div class="d-flex justify-content-center gap-1 flex-wrap">
                                        <?php if(!$esInv): ?>
                                            <span class="badge-tag badge-servicio-tag" title="Servicio Clínico / Intangible"><i class="bi bi-heart-pulse me-1"></i>Servicio</span>
                                        <?php endif; ?>
                                        <?php if($esVenta): ?><span class="badge-tag badge-venta" title="Para Venta">Venta</span><?php endif; ?>
                                        <?php if($esCompra): ?><span class="badge-tag badge-compra" title="Para Compra">Compra</span><?php endif; ?>
                                        <?php if($esInv): ?><span class="badge-tag badge-inventario" title="Control Inventario">Inv</span><?php endif; ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <div class="d-flex justify-content-center gap-1 flex-wrap">
                                        <?php if($esFav): ?>
                                            <span class="prop-badge-fav" title="Artículo Favorito"><i class="bi bi-star-fill text-warning me-1"></i>Fav</span>
                                        <?php endif; ?>
                                        <?php if($esEdit): ?>
                                            <span class="prop-badge-edit" title="Editable en Facturación"><i class="bi bi-pencil me-1"></i>Edit</span>
                                        <?php endif; ?>
                                        <?php if(!$esFav && !$esEdit): ?>
                                            <span class="text-muted" style="font-size: 0.75rem;">—</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <?php if(!$esInv): ?>
                                        <span class="badge-tag badge-servicio-tag" style="font-size: 0.72rem; padding: 2px 7px;" title="Servicio clínico sin control de almacén">No aplica</span>
                                    <?php else: ?>
                                        <span class="stock-pill <?php echo ($stock > 0) ? 'stock-in' : 'stock-zero'; ?>">
                                            <?php echo number_format($stock, 2); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right; font-family: monospace; font-weight: 600; color: #475569;">
                                    RD$ <?php echo number_format($costo, 2); ?>
                                </td>
                                <td style="text-align: center;">
                                    <a href="index.php?page=articulos&item=<?php echo urlencode($a['ItemCode']); ?>" 
                                       class="btn-table-action" 
                                       title="Editar ficha del artículo">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            <div id="sin-articulos" style="display: none; padding: 40px; text-align: center; color: #64748b;">
                <i class="bi bi-search" style="font-size: 2.5rem; opacity: 0.4;"></i>
                <h5 class="mt-2 fw-bold text-dark">No se encontraron artículos</h5>
                <p style="font-size: 0.88rem;">Intenta con otro término de búsqueda o ajusta los filtros seleccionados.</p>
            </div>

            <!-- PAGINACIÓN PURO CSS (SIN BOOTSTRAP) -->
            <div class="pure-pagination-card">
                <div class="pure-length-selector">
                    <span>Mostrar</span>
                    <select id="articulosPorPagina" class="pure-select-input" onchange="cambiarLimitePaginacion()">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="all">Todos</option>
                    </select>
                    <span>artículos por página</span>
                </div>

                <div class="pure-pagination-info" id="purePaginacionInfo">
                    Mostrando 1 a 25 de <?php echo $totalArticulos; ?> artículos
                </div>

                <div class="pure-pagination-nav" id="purePaginacionNav">
                    <!-- Los botones de página se generan dinámicamente con puro CSS / JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- =======================================================================
         PESTAÑA 2: FORMULARIO MODULAR DE ARTÍCULOS
         ======================================================================= -->
    <div class="tab-content-pane <?php echo $esEdicion ? 'pane-active' : ''; ?>" id="tab-form">
        
        <!-- BANNER DE EDICIÓN ACTIVA -->
        <?php if($esEdicion): ?>
            <div class="edit-status-banner">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-primary fs-5"></i>
                    <div>
                        <strong class="text-dark">Modo Edición:</strong>
                        <span class="text-primary fw-bold"><?php echo htmlspecialchars($val['ItemName']); ?> (<?php echo htmlspecialchars($val['ItemCode']); ?>)</span>
                    </div>
                </div>
                <a href="index.php?page=articulos" class="btn-cancel-edit">
                    <i class="bi bi-x-circle"></i>
                    <span>Cancelar Edición</span>
                </a>
            </div>
        <?php endif; ?>

        <form action="index.php?action=guardar_articulo" method="POST" id="formArticulo" onsubmit="return validarFormArticulo()">
            <input type="hidden" name="codigo_original" id="codigo_original" value="<?php echo $esEdicion ? htmlspecialchars($val['ItemCode']) : ''; ?>">

            <div class="form-sections-wrap">
                <!-- SECCIÓN 1: IDENTIFICACIÓN PRINCIPAL Y CLASIFICACIÓN ERP -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <h4 class="form-section-title">
                            <i class="bi bi-card-heading text-primary"></i>
                            <span>1. Identificación & Clasificación General</span>
                        </h4>
                        <span class="text-muted" style="font-size: 0.8rem;">Datos maestros del producto / servicio</span>
                    </div>
                    <div class="form-section-body">
                        <!-- Selector Visual de Naturaleza: Servicio Clínico vs Artículo Físico (Compacto y Elegante) -->
                        <div class="tipo-item-compact-wrap">
                            <div class="tipo-segmented-control">
                                <button type="button" class="tipo-seg-btn <?php echo $esServicioInicial ? 'active-servicio' : ''; ?>" id="cardTipoServicio" onclick="cambiarTipoItemUi('S')">
                                    <span class="seg-icon srv-icon"><i class="bi bi-heart-pulse-fill"></i></span>
                                    <div class="seg-text">
                                        <span class="seg-title">Servicio Clínico / Procedimiento</span>
                                        <span class="seg-sub">Consultas, cirugías, láser y estudios</span>
                                    </div>
                                    <span class="seg-badge srv-badge"><i class="bi bi-check-circle-fill me-1"></i>Recomendado Clínica</span>
                                </button>

                                <button type="button" class="tipo-seg-btn <?php echo !$esServicioInicial ? 'active-inventario' : ''; ?>" id="cardTipoInventario" onclick="cambiarTipoItemUi('I')">
                                    <span class="seg-icon inv-icon"><i class="bi bi-box-seam-fill"></i></span>
                                    <div class="seg-text">
                                        <span class="seg-title">Artículo Físico de Inventario</span>
                                        <span class="seg-sub">Lentes, monturas, fármacos y material</span>
                                    </div>
                                    <span class="seg-badge inv-badge"><i class="bi bi-check-circle-fill me-1"></i>Control de Stock</span>
                                </button>
                            </div>
                        </div>

                        <!-- Sub-bloque A: Identificación Básica y Categoría -->
                        <div class="form-grid-3">
                            <div class="field-group">
                                <label class="field-label">Código de Artículo <span class="required">*</span></label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-upc-scan field-icon"></i>
                                    <input type="text" name="ItemCode" id="ItemCode" class="field-input text-uppercase fw-bold" value="<?php echo htmlspecialchars($val['ItemCode']); ?>" required <?php echo $readonlyCode; ?> placeholder="Ej: ART0001, SERV01">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Código de Barras</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-upc field-icon <?php echo $esServicioInicial ? 'text-muted' : ''; ?>" id="iconCodeBars"></i>
                                    <input type="text" name="CodeBars" id="CodeBars" 
                                           class="field-input <?php echo $esServicioInicial ? 'field-disabled' : ''; ?>" 
                                           value="<?php echo $esServicioInicial ? '' : htmlspecialchars($val['CodeBars']); ?>" 
                                           placeholder="<?php echo $esServicioInicial ? 'No aplica para servicios clínicos' : 'Escanee o digite...'; ?>" 
                                           <?php echo $esServicioInicial ? 'disabled' : ''; ?>>
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Grupo de Artículos</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-folder2 field-icon"></i>
                                    <?php echo str_replace("<select name='ItmsGrpCod'", "<select name='ItmsGrpCod' onchange='actualizarSecuenciaPorGrupo()'", $comboGrupos); ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-sub-divider"></div>

                        <!-- Sub-bloque B: Denominación y Descripción -->
                        <div class="form-grid-2">
                            <div class="field-group">
                                <label class="field-label">Descripción Principal <span class="required">*</span></label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-card-text field-icon"></i>
                                    <input type="text" name="ItemName" id="ItemName" class="field-input fw-semibold" value="<?php echo htmlspecialchars($val['ItemName']); ?>" required placeholder="Nombre descriptivo del artículo">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Descripción Secundaria / Extranjera</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-translate field-icon"></i>
                                    <input type="text" name="FrgnName" id="FrgnName" class="field-input" value="<?php echo htmlspecialchars($val['FrgnName']); ?>" placeholder="Nombre alternativo o en otro idioma">
                                </div>
                            </div>
                        </div>

                        <div class="form-sub-divider"></div>

                        <!-- Sub-bloque C: Parámetros Fiscales y Almacén Predeterminado -->
                        <div class="form-grid-2">
                            <div class="field-group">
                                <label class="field-label">Tipo de Impuesto Asociado <span class="required">*</span></label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-percent field-icon"></i>
                                    <?php echo $comboImpuestos; ?>
                                </div>
                            </div>

                            <div class="field-group" id="bloqueAlmacenPredet" style="<?php echo $esServicioInicial ? 'display: none;' : ''; ?>">
                                <label class="field-label">Almacén Predeterminado (Principal) <span class="required">*</span></label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-building field-icon"></i>
                                    <?php echo $comboAlmacenes; ?>
                                </div>
                            </div>

                            <div class="field-group" id="infoServicioAlm" style="<?php echo $esServicioInicial ? '' : 'display: none;'; ?>">
                                <label class="field-label">Almacén Físico Asignado</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-slash-circle field-icon text-muted"></i>
                                    <input type="text" class="field-input" value="No requerido (Servicio Clínico Intangible)" readonly disabled style="background-color: #f8fafc; color: #64748b; font-weight: 500; cursor: not-allowed;">
                                </div>
                            </div>
                        </div>

                        <div class="form-sub-divider" id="dividerAlmacenes" style="<?php echo $esServicioInicial ? 'display: none;' : ''; ?>"></div>

                        <!-- Sub-bloque D: Almacenes Permitidos para Inventario (Plegable / Escalable / Bajo Ruido) -->
                        <div class="field-group" id="bloqueAlmacenesGrid" style="<?php echo $esServicioInicial ? 'display: none;' : ''; ?>">
                            <div class="almacenes-collapse-card">
                                <div class="almacenes-collapse-header" onclick="toggleAcordeonAlmacenes()">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="alm-collapse-icon">
                                            <i class="bi bi-building-gear text-primary"></i>
                                        </div>
                                        <div>
                                            <span class="alm-collapse-title">Almacenes Permitidos para Inventario</span>
                                            <span class="alm-collapse-desc">Habilitar o restringir almacenes secundarios autorizados para este artículo</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge-almacenes-resumen badge-resumen-single" id="resumenAlmacenesContador">
                                            <?php echo count($almacenesMarcados); ?> almacenes habilitados
                                        </span>
                                        <button type="button" class="btn-toggle-almacenes" id="btnToggleAlmacenes">
                                            <span id="textoToggleAlmacenes">Personalizar</span>
                                            <i class="bi bi-chevron-down" id="chevronAlmacenes"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Panel Desplegable (Plegado por defecto) -->
                                <div class="almacenes-collapse-body" id="cuerpoAcordeonAlmacenes" style="display: none;">
                                    <div class="almacenes-toolbar">
                                        <div class="almacenes-search-wrap">
                                            <i class="bi bi-search"></i>
                                            <input type="text" id="filtroBuscarAlmacen" class="almacenes-search-input" placeholder="Buscar almacén por nombre o código..." oninput="filtrarAlmacenesLista()">
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn-whs-action" onclick="conservarSoloPrincipalAlmacen()" title="Conservar únicamente el almacén predeterminado">
                                                <i class="bi bi-star-fill text-warning"></i> Solo Principal
                                            </button>
                                            <button type="button" class="btn-whs-action" onclick="marcarTodosAlmacenes(true)" title="Habilitar todos los almacenes autorizados">
                                                <i class="bi bi-check-all text-primary"></i> Habilitar Todos
                                            </button>
                                        </div>
                                    </div>

                                    <div class="almacenes-lista-scroll" id="contenedorAlmacenesLista">
                                        <?php foreach ($todosAlmacenes as $alm): 
                                            $whsCodeEsc = htmlspecialchars($alm['WhsCode']);
                                            $marcado = in_array($alm['WhsCode'], $almacenesMarcados);
                                            $esPredet = ($val['DfltWH'] === $alm['WhsCode']);
                                        ?>
                                            <label class="alm-fila-item <?php echo $marcado ? 'is-checked' : ''; ?> <?php echo $esPredet ? 'is-default' : ''; ?>" 
                                                   for="alm_<?php echo $whsCodeEsc; ?>" 
                                                   id="card_alm_<?php echo $whsCodeEsc; ?>"
                                                   data-nombre="<?php echo htmlspecialchars($alm['WhsName']); ?>"
                                                   data-codigo="<?php echo $whsCodeEsc; ?>">
                                                <div class="alm-fila-left">
                                                    <input type="checkbox" name="almacenes[]" value="<?php echo $whsCodeEsc; ?>" id="alm_<?php echo $whsCodeEsc; ?>" <?php echo $marcado ? 'checked' : ''; ?> onchange="actualizarCheckAlmacen(this)">
                                                    <div class="alm-check-custom">
                                                        <i class="bi bi-check-lg"></i>
                                                    </div>
                                                    <div class="alm-avatar-icon">
                                                        <i class="bi bi-building"></i>
                                                    </div>
                                                    <div class="alm-info-box">
                                                        <span class="alm-nombre" title="<?php echo htmlspecialchars($alm['WhsName']); ?>"><?php echo htmlspecialchars($alm['WhsName']); ?></span>
                                                        <span class="alm-codigo-tag">Cód: <?php echo $whsCodeEsc; ?></span>
                                                    </div>
                                                </div>
                                                <div class="alm-fila-right">
                                                    <span class="alm-badge-default" id="badge_predet_<?php echo $whsCodeEsc; ?>" style="<?php echo $esPredet ? '' : 'display: none;'; ?>">
                                                        <i class="bi bi-star-fill text-warning"></i> Almacén Principal
                                                    </span>
                                                    <span class="alm-status-indicator" id="status_alm_<?php echo $whsCodeEsc; ?>">
                                                        <?php if($marcado): ?>
                                                            <span class="alm-status-pill pill-active"><i class="bi bi-check-circle-fill me-1"></i>Habilitado</span>
                                                        <?php else: ?>
                                                            <span class="alm-status-pill pill-inactive"><i class="bi bi-dash-circle me-1"></i>Inactivo</span>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-sub-divider"></div>

                        <!-- Sub-bloque E: Switches de Configuración ERP -->
                        <div class="field-group">
                            <label class="field-label">Tipo de Manejo Operativo</label>
                            <div class="erp-switches-box">
                                <label class="erp-switch-item" for="SellItem" title="Permite facturar el artículo. Si se desmarca, no aparecerá en el catálogo de ventas.">
                                    <input type="checkbox" name="SellItem" id="SellItem" <?php echo chkY($art['SellItem'] ?? 'Y', 'SellItem'); ?>>
                                    <span class="erp-switch-label"><i class="bi bi-cart-check text-primary me-1"></i> Artículo de Venta</span>
                                </label>

                                <label class="erp-switch-item" for="PrchseItem" title="Permite comprar y recibir el artículo a través del módulo de Entrada de Mercancías.">
                                    <input type="checkbox" name="PrchseItem" id="PrchseItem" <?php echo chkY($art['PrchseItem'] ?? ($esEdicion ? 'N' : ($esServicioInicial ? 'N' : 'Y')), 'PrchseItem'); ?>>
                                    <span class="erp-switch-label"><i class="bi bi-bag-plus text-warning me-1"></i> Artículo de Compra</span>
                                </label>

                                <label class="erp-switch-item" for="InvntItem" title="Mantiene control de stock físico. Rebaja en facturación y suma en entradas. Desmarcar para servicios, consultas y procedimientos médicos.">
                                    <input type="checkbox" name="InvntItem" id="InvntItem" <?php echo chkY($art['InvntItem'] ?? ($esEdicion ? 'N' : ($esServicioInicial ? 'N' : 'Y')), 'InvntItem'); ?> onchange="cambiarTipoItemUi(this.checked ? 'I' : 'S')">
                                    <span class="erp-switch-label"><i class="bi bi-check2-square text-success me-1"></i> Artículo de Inventario</span>
                                </label>
                            </div>
                            <small class="text-muted mt-1" style="font-size: 0.78rem;">
                                <i class="bi bi-info-circle me-1 text-primary"></i> 
                                Para servicios médicos, consultas o procedimientos que no llevan control de stock físico, desmarque <strong>Artículo de Inventario</strong>.
                            </small>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: PROPIEDADES SAP (QryGroup) -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <h4 class="form-section-title">
                            <i class="bi bi-toggles text-primary"></i>
                            <span>2. Propiedades & Clasificación Especial</span>
                        </h4>
                        <span class="text-muted" style="font-size: 0.8rem;">Reglas de edición y catálogo</span>
                    </div>
                    <div class="form-section-body">
                        <!-- Propiedades Principales con Tarjetas Interactivas -->
                        <div class="props-main-grid">
                            <?php foreach ($propiedadesPrincipalesArt as $numProp => $p): 
                                $fieldName = 'QryGroup' . $numProp;
                                $isChecked = (($art[$fieldName] ?? '') === 'Y');
                                $esDestacada = $p['destacada'] ? 'prop-featured' : '';
                            ?>
                                <label class="prop-card-item <?php echo $esDestacada; ?>" for="<?php echo $fieldName; ?>">
                                    <div class="prop-info-wrap">
                                        <div class="prop-title">
                                            <i class="bi <?php echo $p['icon']; ?> me-1 text-primary"></i>
                                            <?php echo htmlspecialchars($p['label']); ?>
                                        </div>
                                        <div class="prop-desc"><?php echo htmlspecialchars($p['desc']); ?></div>
                                    </div>
                                    <input type="checkbox" name="<?php echo $fieldName; ?>" id="<?php echo $fieldName; ?>" class="prop-checkbox" <?php echo $isChecked ? 'checked' : ''; ?>>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Botón para desplegar propiedades extendidas adicionales (QryGroup3 - 10 y 54 - 64) -->
                        <button type="button" class="props-extended-toggle" onclick="togglePropsExtendidasArt()">
                            <i class="bi bi-chevron-down" id="propsChevronArt"></i>
                            <span>Ver / Configurar Propiedades Adicionales (3 a 10 y 54 a 64)</span>
                        </button>

                        <div class="props-extended-grid" id="propsExtendedGridArt">
                            <?php for($i=3; $i<=10; $i++): 
                                $fName = 'QryGroup' . $i;
                            ?>
                                <label class="prop-mini-item" for="<?php echo $fName; ?>">
                                    <input type="checkbox" name="<?php echo $fName; ?>" id="<?php echo $fName; ?>" <?php echo chkY($art[$fName] ?? '', $fName); ?>>
                                    <span>Propiedad <?php echo $i; ?></span>
                                </label>
                            <?php endfor; ?>

                            <?php for($i=54; $i<=64; $i++): 
                                $fName = 'QryGroup' . $i;
                            ?>
                                <label class="prop-mini-item" for="<?php echo $fName; ?>">
                                    <input type="checkbox" name="<?php echo $fName; ?>" id="<?php echo $fName; ?>" <?php echo chkY($art[$fName] ?? '', $fName); ?>>
                                    <span>Propiedad <?php echo $i; ?></span>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción del Formulario -->
                <div class="d-flex align-items-center justify-content-between p-3 bg-white border rounded-3 shadow-sm">
                    <button type="button" class="btn-secondary-action" onclick="activarTabArticulo('tab-list')">
                        <i class="bi bi-arrow-left"></i>
                        <span>Volver al Directorio</span>
                    </button>
                    <button type="submit" class="btn-primary-action" id="btnGuardarArticulo">
                        <i class="bi bi-check2-circle"></i>
                        <span><?php echo $esEdicion ? 'Actualizar Artículo' : 'Guardar Artículo'; ?></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script>
    // =========================================================================
    // ESTADO DE PAGINACIÓN Y FILTROS (PURO JS & CSS)
    // =========================================================================
    let paginaActual = 1;
    let registrosPorPagina = 25;
    let filasFiltradas = [];

    // Auto-dismiss de alertas
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(el) {
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(() => el.remove(), 600);
        });
    }, 5000);

    // Inicializar al cargar el DOM
    document.addEventListener('DOMContentLoaded', function() {
        // Enlazar el combo de grupo si existe (filtro de directorio)
        const comboFiltroGrupo = document.getElementById('filtroGrupo');
        if (comboFiltroGrupo) {
            comboFiltroGrupo.classList.add('filter-select');
            comboFiltroGrupo.addEventListener('change', filtrarArticulos);
        }

        // Sincronizar almacén predeterminado con los checkboxes y badges
        const selectDflt = document.getElementById('DfltWH');
        if (selectDflt) {
            selectDflt.addEventListener('change', sincronizarAlmacenPredeterminado);
        }

        // Enlazar cambio de grupo de artículos en el formulario para regenerar secuencia dinámica (3 primeras letras)
        const selectGrupoForm = document.getElementById('ItmsGrpCod');
        if (selectGrupoForm) {
            selectGrupoForm.addEventListener('change', actualizarSecuenciaPorGrupo);
            selectGrupoForm.addEventListener('input', actualizarSecuenciaPorGrupo);
        }
        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'ItmsGrpCod') {
                actualizarSecuenciaPorGrupo();
            }
        });

        // Sincronizar estado inicial de tarjetas y badges de almacenes
        sincronizarBadgesPredeterminado();
        actualizarContadorResumenAlmacenes();

        filtrarArticulos();
    });

    // =========================================================================
    // NAVEGACIÓN POR PESTAÑAS
    // =========================================================================
    function activarTabArticulo(tabId) {
        document.querySelectorAll('.module-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content-pane').forEach(p => p.classList.remove('pane-active'));

        if (tabId === 'tab-list') {
            document.getElementById('btn-tab-list').classList.add('active');
            document.getElementById('tab-list').classList.add('pane-active');
        } else {
            document.getElementById('btn-tab-form').classList.add('active');
            document.getElementById('tab-form').classList.add('pane-active');
        }
    }

    // =========================================================================
    // LIMPIAR FORMULARIO PARA NUEVO ARTÍCULO
    // =========================================================================
    function limpiarFormArticulo() {
        document.getElementById('formArticulo').reset();
        document.getElementById('codigo_original').value = '';
        const itemCodeInput = document.getElementById('ItemCode');
        itemCodeInput.removeAttribute('readonly');
        
        const banner = document.querySelector('.edit-status-banner');
        if (banner) banner.style.display = 'none';

        // Preseleccionar el primer grupo disponible
        const selectGrp = document.getElementById('ItmsGrpCod');
        if (selectGrp && '<?php echo $grupoSeleccionadoInicial; ?>') {
            selectGrp.value = '<?php echo $grupoSeleccionadoInicial; ?>';
        }

        // Impuesto exento por defecto
        const selectTax = document.getElementById('TaxCodeAr');
        if (selectTax && '<?php echo $defaultTax ?? "EXE"; ?>') {
            selectTax.value = '<?php echo $defaultTax ?? "EXE"; ?>';
        }

        // Regenerar código secuencial dinámico según el grupo
        actualizarSecuenciaPorGrupo();

        document.getElementById('SellItem').checked = true;

        // Predeterminar Servicio Clínico para nuevos registros
        cambiarTipoItemUi('S');

        document.getElementById('btnGuardarArticulo').innerHTML = '<i class="bi bi-check2-circle"></i> <span>Guardar Artículo</span>';
    }

    // =========================================================================
    // TOGGLE PROPIEDADES EXTENDIDAS
    // =========================================================================
    function togglePropsExtendidasArt() {
        const grid = document.getElementById('propsExtendedGridArt');
        const chevron = document.getElementById('propsChevronArt');
        const isOpen = grid.classList.contains('open');

        if (isOpen) {
            grid.classList.remove('open');
            chevron.className = 'bi bi-chevron-down';
        } else {
            grid.classList.add('open');
            chevron.className = 'bi bi-chevron-up';
        }
    }

    // =========================================================================
    // FILTRADO EN TIEMPO REAL
    // =========================================================================
    function filtrarArticulos() {
        const texto = document.getElementById('filtroTextoArt').value.toLowerCase().trim();
        const tipo = document.getElementById('filtroTipoArt').value;
        const stock = document.getElementById('filtroStockArt').value;
        const selectGrupo = document.getElementById('filtroGrupo');
        const grupo = selectGrupo ? selectGrupo.value : '';

        const todasFilas = Array.from(document.querySelectorAll('.fila-articulo'));
        filasFiltradas = [];

        todasFilas.forEach(fila => {
            const nombre = fila.getAttribute('data-nombre') || '';
            const codigo = fila.getAttribute('data-codigo') || '';
            const frgn = fila.getAttribute('data-frgn') || '';
            const codebars = fila.getAttribute('data-codebars') || '';
            const fGrupo = fila.getAttribute('data-grupo') || '';
            const fVenta = fila.getAttribute('data-venta') === '1';
            const fCompra = fila.getAttribute('data-compra') === '1';
            const fInv = fila.getAttribute('data-inventario') === '1';
            const fFav = fila.getAttribute('data-favorito') === '1';
            const fEdit = fila.getAttribute('data-editable') === '1';
            const fStock = fila.getAttribute('data-stock') || '';

            const coincideTexto = !texto || nombre.includes(texto) || codigo.includes(texto) || frgn.includes(texto) || codebars.includes(texto);
            const coincideGrupo = !grupo || (fGrupo === grupo);
            const coincideStock = (stock === 'todos') || (fStock === stock);

            let coincideTipo = true;
            if (tipo === 'servicio') coincideTipo = !fInv;
            else if (tipo === 'inventario') coincideTipo = fInv;
            else if (tipo === 'venta') coincideTipo = fVenta;
            else if (tipo === 'compra') coincideTipo = fCompra;
            else if (tipo === 'favorito') coincideTipo = fFav;
            else if (tipo === 'editable') coincideTipo = fEdit;

            if (coincideTexto && coincideGrupo && coincideTipo && coincideStock) {
                filasFiltradas.push(fila);
            } else {
                fila.style.display = 'none';
            }
        });

        // Ocultar o mostrar mensaje si no hay registros
        const divSinArticulos = document.getElementById('sin-articulos');
        if (filasFiltradas.length === 0) {
            divSinArticulos.style.display = 'block';
        } else {
            divSinArticulos.style.display = 'none';
        }

        // Reiniciar a la primera página tras filtrar
        paginaActual = 1;
        renderizarPaginacion();
    }

    function limpiarFiltrosArticulos() {
        document.getElementById('filtroTextoArt').value = '';
        document.getElementById('filtroTipoArt').value = 'todos';
        document.getElementById('filtroStockArt').value = 'todos';
        const selectGrupo = document.getElementById('filtroGrupo');
        if (selectGrupo) selectGrupo.value = '';
        filtrarArticulos();
    }

    // =========================================================================
    // MOTOR DE PAGINACIÓN PURO CSS
    // =========================================================================
    function cambiarLimitePaginacion() {
        const val = document.getElementById('articulosPorPagina').value;
        registrosPorPagina = (val === 'all') ? -1 : parseInt(val, 10);
        paginaActual = 1;
        renderizarPaginacion();
    }

    function irAPagina(numPagina) {
        paginaActual = numPagina;
        renderizarPaginacion();
    }

    function renderizarPaginacion() {
        const total = filasFiltradas.length;
        const info = document.getElementById('purePaginacionInfo');
        const resumen = document.getElementById('contador-articulos-resumen');
        const nav = document.getElementById('purePaginacionNav');

        if (total === 0) {
            info.textContent = 'Mostrando 0 registros';
            resumen.textContent = 'Mostrando 0 artículos';
            nav.innerHTML = '';
            return;
        }

        let totalPaginas = (registrosPorPagina === -1) ? 1 : Math.ceil(total / registrosPorPagina);
        if (paginaActual > totalPaginas) paginaActual = totalPaginas;
        if (paginaActual < 1) paginaActual = 1;

        let inicio = (registrosPorPagina === -1) ? 0 : (paginaActual - 1) * registrosPorPagina;
        let fin = (registrosPorPagina === -1) ? total : Math.min(inicio + registrosPorPagina, total);

        // Mostrar únicamente las filas del rango
        filasFiltradas.forEach((fila, index) => {
            if (index >= inicio && index < fin) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });

        // Actualizar textos informativos
        info.textContent = `Mostrando ${inicio + 1} a ${fin} de ${total} artículos`;
        resumen.textContent = `Mostrando ${total} artículos`;

        // Generar botones de navegación con puro CSS
        let htmlNav = '';

        // Botón Primero y Anterior
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPagina(1)" ${paginaActual === 1 ? 'disabled' : ''} title="Primera página"><i class="bi bi-chevron-double-left"></i></button>`;
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPagina(${paginaActual - 1})" ${paginaActual === 1 ? 'disabled' : ''} title="Página anterior"><i class="bi bi-chevron-left"></i></button>`;

        // Rango de páginas numeradas
        let maxBotones = 5;
        let inicioPag = Math.max(1, paginaActual - Math.floor(maxBotones / 2));
        let finPag = Math.min(totalPaginas, inicioPag + maxBotones - 1);
        if (finPag - inicioPag + 1 < maxBotones) {
            inicioPag = Math.max(1, finPag - maxBotones + 1);
        }

        for (let i = inicioPag; i <= finPag; i++) {
            const esActiva = (i === paginaActual) ? 'active' : '';
            htmlNav += `<button type="button" class="pure-page-btn ${esActiva}" onclick="irAPagina(${i})">${i}</button>`;
        }

        // Botón Siguiente y Último
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPagina(${paginaActual + 1})" ${paginaActual === totalPaginas ? 'disabled' : ''} title="Página siguiente"><i class="bi bi-chevron-right"></i></button>`;
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPagina(${totalPaginas})" ${paginaActual === totalPaginas ? 'disabled' : ''} title="Última página"><i class="bi bi-chevron-double-right"></i></button>`;

        nav.innerHTML = htmlNav;
    }

    // =========================================================================
    // DATOS DE SECUENCIAS Y GRUPOS (PHP -> JS)
    // =========================================================================
    const mapaGrupos = <?php echo json_encode($mapaGrupos, JSON_UNESCAPED_UNICODE); ?>;
    const codigosArticulosExistentes = <?php echo json_encode(array_values(array_filter(array_map(function($a) { return trim($a['ItemCode'] ?? ''); }, $articulos ?? []))), JSON_UNESCAPED_UNICODE); ?>;
    const esModoEdicion = <?php echo $esEdicion ? 'true' : 'false'; ?>;

    // Extraer las primeras 2-3 letras del nombre del grupo (o 'SV' para Servicios / fallback 'ART')
    function extraerPrefijoGrupo(nombre) {
        if (!nombre || nombre.includes('--')) return 'SV';
        // Normalizar acentos y eliminar caracteres no alfabéticos
        const norm = nombre.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        const letras = norm.replace(/[^a-zA-Z]/g, "").toUpperCase();
        if (!letras) return 'SV';

        // Si el grupo es o comienza por "SERVICIO", asignar prefijo "SV"
        if (letras.startsWith('SERVICIO') || letras === 'SRV') {
            return 'SV';
        }
        if (letras.length >= 2) {
            return letras.substring(0, Math.min(3, letras.length));
        }
        return 'ART';
    }

    // Calcular el siguiente correlativo para el prefijo dado
    function calcularSiguienteSecuencia(prefijo) {
        prefijo = (prefijo || 'SV').toUpperCase();
        const regex = new RegExp('^' + prefijo + '(\\d+)$', 'i');
        let maxNum = 0;
        if (Array.isArray(codigosArticulosExistentes)) {
            codigosArticulosExistentes.forEach(codigo => {
                const match = String(codigo).trim().match(regex);
                if (match) {
                    const num = parseInt(match[1], 10);
                    if (!isNaN(num) && num > maxNum) {
                        maxNum = num;
                    }
                }
            });
        }
        const sigNum = maxNum + 1;
        return prefijo + String(sigNum).padStart(4, '0');
    }

    // Actualizar dinámicamente el código de artículo según el grupo seleccionado
    function actualizarSecuenciaPorGrupo() {
        const inputCode = document.getElementById('ItemCode');
        const codigoOriginal = document.getElementById('codigo_original');
        
        // Si el campo de código es de solo lectura o existe un código original previo guardado (edición), no sobreescribir
        if (inputCode && inputCode.readOnly) {
            return;
        }
        if (codigoOriginal && codigoOriginal.value.trim() !== '') {
            return;
        }
        if (!inputCode) return;

        const selectGrp = document.getElementById('ItmsGrpCod');
        let nombreGrupo = '';
        if (selectGrp) {
            const cod = String(selectGrp.value || '').trim();
            if (cod && mapaGrupos && mapaGrupos[cod]) {
                nombreGrupo = mapaGrupos[cod];
            } else if (cod && selectGrp.selectedIndex >= 0 && selectGrp.options[selectGrp.selectedIndex]) {
                nombreGrupo = selectGrp.options[selectGrp.selectedIndex].text;
            }
        }

        const prefijo = extraerPrefijoGrupo(nombreGrupo);
        const nuevoCodigo = calcularSiguienteSecuencia(prefijo);
        inputCode.value = nuevoCodigo;

        // Feedback visual con micro-animación
        inputCode.classList.remove('input-pulse-highlight');
        void inputCode.offsetWidth;
        inputCode.classList.add('input-pulse-highlight');
    }

    // =========================================================================
    // CONMUTADOR DE TIPO DE ÍTEM: SERVICIO CLÍNICO VS ARTÍCULO FÍSICO
    // =========================================================================
    function cambiarTipoItemUi(tipo) {
        const esServicio = (tipo === 'S');
        const chkInvnt = document.getElementById('InvntItem');
        const cardServicio = document.getElementById('cardTipoServicio');
        const cardInventario = document.getElementById('cardTipoInventario');
        const bloqueAlmPredet = document.getElementById('bloqueAlmacenPredet');
        const infoServicioAlm = document.getElementById('infoServicioAlm');
        const dividerAlmacenes = document.getElementById('dividerAlmacenes');
        const bloqueAlmacenesGrid = document.getElementById('bloqueAlmacenesGrid');
        const chkPrchse = document.getElementById('PrchseItem');
        const inputCodeBars = document.getElementById('CodeBars');
        const iconCodeBars = document.getElementById('iconCodeBars');

        if (chkInvnt) {
            chkInvnt.checked = !esServicio;
        }

        if (esServicio) {
            if (cardServicio) cardServicio.classList.add('active-servicio');
            if (cardInventario) cardInventario.classList.remove('active-inventario');
            if (bloqueAlmPredet) bloqueAlmPredet.style.display = 'none';
            if (infoServicioAlm) infoServicioAlm.style.display = '';
            if (dividerAlmacenes) dividerAlmacenes.style.display = 'none';
            if (bloqueAlmacenesGrid) bloqueAlmacenesGrid.style.display = 'none';
            // Para servicios en modo nuevo, compra suele ser N
            if (!esModoEdicion && chkPrchse) {
                chkPrchse.checked = false;
            }
            // Bloquear código de barras para servicios
            if (inputCodeBars) {
                inputCodeBars.disabled = true;
                inputCodeBars.value = '';
                inputCodeBars.placeholder = 'No aplica para servicios clínicos';
                inputCodeBars.classList.add('field-disabled');
            }
            if (iconCodeBars) {
                iconCodeBars.classList.add('text-muted');
            }
        } else {
            if (cardServicio) cardServicio.classList.remove('active-servicio');
            if (cardInventario) cardInventario.classList.add('active-inventario');
            if (bloqueAlmPredet) bloqueAlmPredet.style.display = '';
            if (infoServicioAlm) infoServicioAlm.style.display = 'none';
            if (dividerAlmacenes) dividerAlmacenes.style.display = '';
            if (bloqueAlmacenesGrid) bloqueAlmacenesGrid.style.display = '';
            if (!esModoEdicion && chkPrchse) {
                chkPrchse.checked = true;
            }
            // Habilitar código de barras para artículos físicos
            if (inputCodeBars) {
                inputCodeBars.disabled = false;
                inputCodeBars.placeholder = 'Escanee o digite...';
                inputCodeBars.classList.remove('field-disabled');
            }
            if (iconCodeBars) {
                iconCodeBars.classList.remove('text-muted');
            }

            // Los almacenes por defecto deben estar deshabilitados; solo el principal seleccionado se habilita
            const selectDflt = document.getElementById('DfltWH');
            const valPredet = selectDflt ? selectDflt.value : '';
            const marcados = document.querySelectorAll('input[name="almacenes[]"]:checked');
            if (marcados.length === 0 && valPredet) {
                conservarSoloPrincipalAlmacen();
            } else {
                sincronizarAlmacenPredeterminado();
            }
        }
    }

    // =========================================================================
    // GESTIÓN Y SINCRONIZACIÓN VISUAL DE ALMACENES
    // =========================================================================
    function actualizarCheckAlmacen(chkElement) {
        if (!chkElement) return;
        const card = document.getElementById('card_' + chkElement.id);
        const statusSpan = document.getElementById('status_' + chkElement.id);
        const selectDflt = document.getElementById('DfltWH');
        
        if (chkElement.checked) {
            if (card) card.classList.add('is-checked');
            if (statusSpan) {
                statusSpan.innerHTML = '<span class="alm-status-pill pill-active"><i class="bi bi-check-circle-fill me-1"></i>Habilitado</span>';
            }
        } else {
            // Prevenir desmarcar el almacén predeterminado actual
            if (selectDflt && selectDflt.value === chkElement.value) {
                chkElement.checked = true;
                if (card) card.classList.add('is-checked');
                if (statusSpan) {
                    statusSpan.innerHTML = '<span class="alm-status-pill pill-active"><i class="bi bi-check-circle-fill me-1"></i>Habilitado</span>';
                }
                alert('El almacén predeterminado (principal) debe permanecer habilitado.');
                return;
            }

            if (card) {
                card.classList.remove('is-checked');
                card.classList.remove('is-default');
            }
            if (statusSpan) {
                statusSpan.innerHTML = '<span class="alm-status-pill pill-inactive"><i class="bi bi-dash-circle me-1"></i>Inactivo</span>';
            }
        }
        
        sincronizarBadgesPredeterminado();
        actualizarContadorResumenAlmacenes();
    }

    function sincronizarAlmacenPredeterminado() {
        const selectDflt = document.getElementById('DfltWH');
        if (!selectDflt) return;
        const val = selectDflt.value;
        if (val) {
            const chk = document.getElementById('alm_' + val);
            if (chk && !chk.checked) {
                chk.checked = true;
                const card = document.getElementById('card_' + chk.id);
                const statusSpan = document.getElementById('status_' + chk.id);
                if (card) card.classList.add('is-checked');
                if (statusSpan) {
                    statusSpan.innerHTML = '<span class="alm-status-pill pill-active"><i class="bi bi-check-circle-fill me-1"></i>Habilitado</span>';
                }
            }
        }
        sincronizarBadgesPredeterminado();
        actualizarContadorResumenAlmacenes();
    }

    function sincronizarBadgesPredeterminado() {
        const selectDflt = document.getElementById('DfltWH');
        if (!selectDflt) return;
        const valPredet = selectDflt.value;

        document.querySelectorAll('.alm-fila-item').forEach(card => {
            const chk = card.querySelector('input[type="checkbox"]');
            const badge = card.querySelector('.alm-badge-default');
            if (chk && chk.value === valPredet && chk.checked) {
                card.classList.add('is-default');
                if (badge) badge.style.display = 'inline-flex';
            } else {
                card.classList.remove('is-default');
                if (badge) badge.style.display = 'none';
            }
        });
    }

    // Conservar únicamente el almacén predeterminado habilitado
    function conservarSoloPrincipalAlmacen() {
        const selectDflt = document.getElementById('DfltWH');
        const valPredet = selectDflt ? selectDflt.value : '';

        if (!valPredet) {
            alert('Por favor seleccione primero un Almacén Predeterminado (Principal) en el campo superior.');
            if (selectDflt) selectDflt.focus();
            return;
        }

        document.querySelectorAll('input[name="almacenes[]"]').forEach(chk => {
            chk.checked = (chk.value === valPredet);
            const card = document.getElementById('card_' + chk.id);
            const statusSpan = document.getElementById('status_' + chk.id);
            if (chk.checked) {
                if (card) card.classList.add('is-checked');
                if (statusSpan) {
                    statusSpan.innerHTML = '<span class="alm-status-pill pill-active"><i class="bi bi-check-circle-fill me-1"></i>Habilitado</span>';
                }
            } else {
                if (card) {
                    card.classList.remove('is-checked');
                    card.classList.remove('is-default');
                }
                if (statusSpan) {
                    statusSpan.innerHTML = '<span class="alm-status-pill pill-inactive"><i class="bi bi-dash-circle me-1"></i>Inactivo</span>';
                }
            }
        });

        sincronizarBadgesPredeterminado();
        actualizarContadorResumenAlmacenes();
    }

    function marcarTodosAlmacenes(marcar) {
        if (!marcar) {
            conservarSoloPrincipalAlmacen();
            return;
        }

        document.querySelectorAll('input[name="almacenes[]"]').forEach(chk => {
            chk.checked = true;
            const card = document.getElementById('card_' + chk.id);
            const statusSpan = document.getElementById('status_' + chk.id);
            if (card) card.classList.add('is-checked');
            if (statusSpan) {
                statusSpan.innerHTML = '<span class="alm-status-pill pill-active"><i class="bi bi-check-circle-fill me-1"></i>Habilitado</span>';
            }
        });

        sincronizarBadgesPredeterminado();
        actualizarContadorResumenAlmacenes();
    }

    // Filtro en tiempo real para la lista de almacenes
    function filtrarAlmacenesLista() {
        const input = document.getElementById('filtroBuscarAlmacen');
        const q = input ? input.value.toLowerCase().trim() : '';
        document.querySelectorAll('.alm-fila-item').forEach(fila => {
            const nombre = (fila.getAttribute('data-nombre') || '').toLowerCase();
            const codigo = (fila.getAttribute('data-codigo') || '').toLowerCase();
            if (!q || nombre.includes(q) || codigo.includes(q)) {
                fila.style.display = 'flex';
            } else {
                fila.style.display = 'none';
            }
        });
    }

    // Plegar / Desplegar Acordeón de Almacenes
    function toggleAcordeonAlmacenes() {
        const cuerpo = document.getElementById('cuerpoAcordeonAlmacenes');
        const chevron = document.getElementById('chevronAlmacenes');
        const texto = document.getElementById('textoToggleAlmacenes');
        if (!cuerpo) return;

        const estaAbierto = (cuerpo.style.display !== 'none');
        if (estaAbierto) {
            cuerpo.style.display = 'none';
            if (chevron) chevron.className = 'bi bi-chevron-down';
            if (texto) texto.textContent = 'Personalizar';
        } else {
            cuerpo.style.display = 'block';
            if (chevron) chevron.className = 'bi bi-chevron-up';
            if (texto) texto.textContent = 'Plegar';
        }
    }

    // Actualizar badge resumen de almacenes
    function actualizarContadorResumenAlmacenes() {
        const marcados = document.querySelectorAll('input[name="almacenes[]"]:checked').length;
        const total = document.querySelectorAll('input[name="almacenes[]"]').length;
        const badge = document.getElementById('resumenAlmacenesContador');
        if (!badge) return;

        if (marcados === total && total > 0) {
            badge.textContent = `Todos habilitados (${marcados})`;
            badge.className = 'badge-almacenes-resumen badge-resumen-all';
        } else if (marcados === 1) {
            badge.textContent = `Solo almacén principal (1)`;
            badge.className = 'badge-almacenes-resumen badge-resumen-single';
        } else if (marcados === 0) {
            badge.textContent = `Ningún almacén seleccionado`;
            badge.className = 'badge-almacenes-resumen badge-resumen-single';
        } else {
            badge.textContent = `${marcados} de ${total} habilitados`;
            badge.className = 'badge-almacenes-resumen badge-resumen-partial';
        }
    }

    // =========================================================================
    // VALIDACIÓN PREVIA AL ENVÍO
    // =========================================================================
    function validarFormArticulo() {
        const itemCode = document.getElementById('ItemCode').value.trim();
        const itemName = document.getElementById('ItemName').value.trim();
        const taxCodeSelect = document.getElementById('TaxCodeAr');
        const whsSelect = document.getElementById('DfltWH');
        const chkInvnt = document.getElementById('InvntItem');
        const esInventario = chkInvnt ? chkInvnt.checked : false;

        if (!itemCode) {
            alert('Por favor ingrese el Código del Artículo (ItemCode).');
            document.getElementById('ItemCode').focus();
            return false;
        }

        if (!itemName) {
            alert('Por favor ingrese la Descripción del Artículo (ItemName).');
            document.getElementById('ItemName').focus();
            return false;
        }

        if (taxCodeSelect && !taxCodeSelect.value.trim()) {
            alert('Por favor seleccione el Tipo de Impuesto Asociado.');
            taxCodeSelect.focus();
            return false;
        }

        // Validación de almacenes exclusiva para artículos tangibles con inventario
        if (esInventario) {
            const almacenesMarcados = document.querySelectorAll('input[name="almacenes[]"]:checked');
            if (almacenesMarcados.length === 0) {
                alert('Por favor seleccione al menos un Almacén Permitido para el artículo de inventario.');
                return false;
            }

            if (whsSelect && !whsSelect.value.trim()) {
                whsSelect.value = almacenesMarcados[0].value;
            }
        }

        return true;
    }
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>