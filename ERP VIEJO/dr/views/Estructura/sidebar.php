<?php
if (!defined('APP_RUNNING')) {
    http_response_code(403);
    die('Acceso denegado directamente');
}

$currentPage = $_GET['page'] ?? 'dashboard';

// Definir qué grupos existen y sus items
$menuGroups = [
    'facturacion' => [
        'id' => 'menuFacturacion',
        'label' => 'Facturación & Caja',
        'pages' => ['apertura_caja', 'facturacion', 'cobro', 'retiro_caja', 'cierre_caja', 'consulta_cierre', 'consulta_retiro', 'consulta_aprobaciones', 'nota_credito_ncf', 'consulta_notaNCF', 'consulta_facturas'],
        'sections' => [
            [
                'subtitle' => 'Operaciones de Caja',
                'items' => [
                    ['page' => 'apertura_caja', 'label' => 'Apertura de Caja', 'icon' => 'bi bi-unlock-fill'],
                    ['page' => 'facturacion', 'label' => 'Facturación de Venta', 'icon' => 'bi bi-receipt'],
                    ['page' => 'nota_credito_ncf', 'label' => 'Nota Crédito NCF', 'icon' => 'bi bi-file-earmark-text-fill'],
                    ['page' => 'retiro_caja', 'label' => 'Retiro de Caja', 'icon' => 'bi bi-wallet2'],
                    ['page' => 'cierre_caja', 'label' => 'Cierre de Caja', 'icon' => 'bi bi-lock-fill'],
                ]
            ],
            [
                'subtitle' => 'Consultas & Historial',
                'items' => [
                    ['page' => 'consulta_cierre', 'label' => 'Consulta de Cierres', 'icon' => 'bi bi-journal-check'],
                    ['page' => 'consulta_retiro', 'label' => 'Consulta de Retiros', 'icon' => 'bi bi-journal-minus'],
                    ['page' => 'consulta_aprobaciones', 'label' => 'Consulta de Aprobaciones', 'icon' => 'bi bi-shield-check'],
                    ['page' => 'consulta_notaNCF', 'label' => 'Consulta de Notas NCF', 'icon' => 'bi bi-file-earmark-text-fill'],
                    ['page' => 'consulta_facturas', 'label' => 'Consulta de Facturas', 'icon' => 'bi bi-receipt']
                ]
            ]
        ]
    ],
    'socios' => [
        'id' => 'menuSocios',
        'label' => 'Socios de Negocio',
        'pages' => ['socios', 'grupos_cliente_prov', 'condiciones_pago'],
        'sections' => [
            [
                'items' => [
                    ['page' => 'socios', 'label' => 'Clientes / Suplidores', 'icon' => 'bi bi-people-fill'],
                    ['page' => 'grupos_cliente_prov', 'label' => 'Grupos de Socios', 'icon' => 'bi bi-person-lines-fill'],
                    ['page' => 'condiciones_pago', 'label' => 'Condiciones de Pago', 'icon' => 'bi bi-calendar2-week-fill'],
                ]
            ]
        ]
    ],
    'inventario' => [
        'id' => 'menuInventario',
        'label' => 'Inventario & Artículos',
        'pages' => ['entrada_mercancia', 'articulos', 'asignacion_precios', 'grupos_articulo', 'listas_precio', 'almacenes', 'tipos_impuesto'],
        'sections' => [
            [
                'subtitle' => 'Operaciones & Catálogo',
                'items' => [
                    ['page' => 'entrada_mercancia', 'label' => 'Entrada de Mercancías', 'icon' => 'bi bi-box-arrow-in-down'],
                    ['page' => 'articulos', 'label' => 'Catálogo de Artículos', 'icon' => 'bi bi-box-seam-fill'],
                    ['page' => 'asignacion_precios', 'label' => 'Asignación de Precios & Costos', 'icon' => 'bi bi-tags-fill'],
                ]
            ],
            [
                'subtitle' => 'Mantenimientos & Catálogos',
                'items' => [
                    ['page' => 'grupos_articulo', 'label' => 'Grupos de Artículos', 'icon' => 'bi bi-folder2-open'],
                    ['page' => 'listas_precio', 'label' => 'Listas de Precios', 'icon' => 'bi bi-currency-dollar'],
                    ['page' => 'almacenes', 'label' => 'Almacenes', 'icon' => 'bi bi-house-door-fill'],
                    ['page' => 'tipos_impuesto', 'label' => 'Tipos de Impuesto', 'icon' => 'bi bi-percent'],
                ]
            ]
        ]
    ],
    'registros' => [
        'id' => 'menuRegistros',
        'label' => 'Administración & Sistema',
        'pages' => ['usuarios', 'ncf'],
        'sections' => [
            [
                'items' => [
                    ['page' => 'usuarios', 'label' => 'Gestión de Usuarios', 'icon' => 'bi bi-person-badge-fill'],
                    ['page' => 'ncf', 'label' => 'Comprobantes Fiscales (NCF)', 'icon' => 'bi bi-file-earmark-text-fill'],
                ]
            ]
        ]
    ]
];
?>

<!-- SIDEBAR MODULAR -->
<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-inner">
        <!-- Navegación Principal -->
        <nav class="sidebar-nav">
            <!-- Inicio / Dashboard -->
            <div class="sidebar-group mb-1">
                <div data-url="index.php?page=dashboard" 
                   role="link"
                   tabindex="0"
                   class="sidebar-item <?php echo ($currentPage === 'dashboard') ? 'active' : ''; ?>">
                    <span class="sidebar-item-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                    <span class="sidebar-item-label">Panel Principal</span>
                </div>
            </div>

            <div class="sidebar-divider"></div>

            <!-- Grupos Reorganizados para ERP Oftalmológico -->
            <?php foreach ($menuGroups as $groupKey => $group): 
                $isGroupActive = in_array($currentPage, $group['pages']);
            ?>
                <div class="sidebar-group">
                    <!-- Cabecera de Categoría (Sin ícono de categoría para ligereza visual) -->
                    <div class="sidebar-group-header <?php echo $isGroupActive ? 'group-active' : ''; ?>" 
                         data-target="#<?php echo $group['id']; ?>"
                         role="button"
                         tabindex="0"
                         aria-expanded="<?php echo $isGroupActive ? 'true' : 'false'; ?>">
                        <span class="sidebar-group-title"><?php echo htmlspecialchars($group['label']); ?></span>
                        <span class="sidebar-group-chevron <?php echo $isGroupActive ? 'chevron-open' : ''; ?>">
                            <i class="bi bi-chevron-down"></i>
                        </span>
                    </div>

                    <!-- Contenedor desplegable con animación suave -->
                    <div class="sidebar-group-body <?php echo $isGroupActive ? 'body-open' : ''; ?>" id="<?php echo $group['id']; ?>">
                        <?php foreach ($group['sections'] as $section): ?>
                            <?php if (!empty($section['subtitle'])): ?>
                                <div class="sidebar-section-subtitle">
                                    <?php echo htmlspecialchars($section['subtitle']); ?>
                                </div>
                            <?php endif; ?>

                            <?php foreach ($section['items'] as $item): 
                                $isItemActive = ($currentPage === $item['page']);
                            ?>
                                <div data-url="index.php?page=<?php echo $item['page']; ?>" 
                                   role="link"
                                   tabindex="0"
                                   class="sidebar-subitem <?php echo $isItemActive ? 'active' : ''; ?>">
                                    <span class="sidebar-subitem-icon"><i class="<?php echo $item['icon']; ?>"></i></span>
                                    <span class="sidebar-subitem-label"><?php echo htmlspecialchars($item['label']); ?></span>
                                    <?php if ($isItemActive): ?>
                                        <span class="active-indicator"></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </nav>

        <!-- Pie de Sidebar -->
        <div class="sidebar-footer">
            <div class="sidebar-system-badge">
                <i class="bi bi-shield-check text-success me-1"></i>
                <span>ERP Oftalmología</span>
            </div>
        </div>
    </div>
</aside>

<!-- Script interactivo suave para el Sidebar y navegación limpia sin URL tooltip nativo -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Manejo de acordeón en grupos
    const groupHeaders = document.querySelectorAll('.sidebar-group-header');

    groupHeaders.forEach(header => {
        header.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-target');
            const targetBody = document.querySelector(targetId);
            const chevron = this.querySelector('.sidebar-group-chevron');
            
            if (!targetBody) return;

            const isOpen = targetBody.classList.contains('body-open');

            if (isOpen) {
                targetBody.classList.remove('body-open');
                chevron.classList.remove('chevron-open');
                this.classList.remove('group-active');
                this.setAttribute('aria-expanded', 'false');
            } else {
                targetBody.classList.add('body-open');
                chevron.classList.add('chevron-open');
                this.classList.add('group-active');
                this.setAttribute('aria-expanded', 'true');
            }
        });

        // Soporte accesible para tecla Enter / Space en headers
        header.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // 2. Navegación limpia sin mostrar URL en barra de estado nativa
    const navLinks = document.querySelectorAll('[data-url]');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const url = this.getAttribute('data-url');
            if (url) {
                window.location.href = url;
            }
        });

        link.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const url = this.getAttribute('data-url');
                if (url) {
                    window.location.href = url;
                }
            }
        });
    });
});
</script>
