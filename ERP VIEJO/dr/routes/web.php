<?php
if (!defined('APP_RUNNING')) { exit('Acceso denegado'); }

// =========================================================================
// REGISTRO CENTRAL DE RUTAS, TÍTULOS Y CONTROLADORES
// =========================================================================

$appRoutes = [
    'login' => [
        'title'      => 'Iniciar Sesión',
        'slug'       => 'login',
        'aliases'    => ['acceso', 'auth'],
        'auth'       => false,
        'type'       => 'view',
        'file'       => __DIR__ . '/../views/Estructura/login.php',
    ],
    'dashboard' => [
        'title'      => 'Panel Principal',
        'slug'       => 'dashboard',
        'aliases'    => ['inicio', 'home', 'panel'],
        'auth'       => true,
        'controller' => 'DashboardController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/DashboardController.php',
    ],
    'usuarios' => [
        'title'      => 'Gestión de Usuarios',
        'slug'       => 'usuarios',
        'aliases'    => ['usuarios-sistema', 'perfiles'],
        'auth'       => true,
        'controller' => 'UsuarioController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/UsuarioController.php',
    ],
    'grupos_articulo' => [
        'title'      => 'Grupos de Artículos',
        'slug'       => 'grupos_articulo',
        'aliases'    => ['grupos-articulos', 'grupos-articulo'],
        'auth'       => true,
        'controller' => 'GrupoArticuloController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/GrupoArticuloController.php',
    ],
    'listas_precio' => [
        'title'      => 'Listas de Precios',
        'slug'       => 'listas_precio',
        'aliases'    => ['listas-precio', 'listas-precios', 'tarifarios'],
        'auth'       => true,
        'controller' => 'ListaPrecioController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/ListaPrecioController.php',
    ],
    'almacenes' => [
        'title'      => 'Almacenes de Inventario',
        'slug'       => 'almacenes',
        'aliases'    => ['depositos', 'bodegas'],
        'auth'       => true,
        'controller' => 'AlmacenController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/AlmacenController.php',
    ],
    'tipos_impuesto' => [
        'title'      => 'Tipos de Impuesto',
        'slug'       => 'tipos_impuesto',
        'aliases'    => ['tipos-impuesto', 'itbis', 'impuestos'],
        'auth'       => true,
        'controller' => 'TipoImpuestoController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/TipoImpuestoController.php',
    ],
    'ncf' => [
        'title'      => 'Comprobantes Fiscales (NCF)',
        'slug'       => 'ncf',
        'aliases'    => ['comprobantes-fiscales', 'secuencias-ncf'],
        'auth'       => true,
        'controller' => 'NcfController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/NcfController.php',
    ],
    'articulos' => [
        'title'      => 'Catálogo de Artículos',
        'slug'       => 'articulos',
        'aliases'    => ['productos', 'inventario', 'catalogo'],
        'auth'       => true,
        'controller' => 'ArticuloController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/ArticuloController.php',
    ],
    'entrada_mercancia' => [
        'title'      => 'Entrada de Mercancías',
        'slug'       => 'entrada_mercancia',
        'aliases'    => ['entrada-mercancia', 'compras-inventario'],
        'auth'       => true,
        'controller' => 'EntradaMercanciaController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/EntradaMercanciaController.php',
    ],
    'asignacion_precios' => [
        'title'      => 'Asignación de Precios y Costos',
        'slug'       => 'asignacion_precios',
        'aliases'    => ['asignacion-precios', 'precios-costos'],
        'auth'       => true,
        'controller' => 'AsignacionPreciosController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/AsignacionPreciosController.php',
    ],
    'condiciones_pago' => [
        'title'      => 'Condiciones de Pago',
        'slug'       => 'condiciones_pago',
        'aliases'    => ['condiciones-pago', 'terminos-pago'],
        'auth'       => true,
        'controller' => 'CondicionesPagoController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/CondicionesPagoController.php',
    ],
    'grupos_cliente_prov' => [
        'title'      => 'Grupos de Socios',
        'slug'       => 'grupos_cliente_prov',
        'aliases'    => ['grupos-socios', 'grupos-clientes'],
        'auth'       => true,
        'controller' => 'GrupoClienteProvController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/GrupoClienteProvController.php',
    ],
    'apertura_caja' => [
        'title'      => 'Apertura de Turno de Caja',
        'slug'       => 'apertura_caja',
        'aliases'    => ['apertura-caja', 'abrir-caja'],
        'auth'       => true,
        'controller' => 'CajaController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/CajaController.php',
    ],
    'cierre_caja' => [
        'title'      => 'Cierre de Turno de Caja',
        'slug'       => 'cierre_caja',
        'aliases'    => ['cierre-caja', 'cerrar-caja'],
        'auth'       => true,
        'controller' => 'CajaController',
        'method'     => 'cierre',
        'file'       => __DIR__ . '/../controllers/CajaController.php',
    ],
    'retiro_caja' => [
        'title'      => 'Retiro de Efectivo de Caja',
        'slug'       => 'retiro_caja',
        'aliases'    => ['retiro-caja', 'retiros'],
        'auth'       => true,
        'controller' => 'CajaController',
        'method'     => 'retiro',
        'file'       => __DIR__ . '/../controllers/CajaController.php',
    ],
    'consulta_cierre' => [
        'title'      => 'Consulta de Cierres de Caja',
        'slug'       => 'consulta_cierre',
        'aliases'    => ['consulta-cierre', 'historial-cierres'],
        'auth'       => true,
        'controller' => 'CajaController',
        'method'     => 'consultaCierre',
        'file'       => __DIR__ . '/../controllers/CajaController.php',
    ],
    'consulta_retiro' => [
        'title'      => 'Consulta de Retiros de Caja',
        'slug'       => 'consulta_retiro',
        'aliases'    => ['consulta-retiro', 'historial-retiros'],
        'auth'       => true,
        'controller' => 'CajaController',
        'method'     => 'consultaRetiro',
        'file'       => __DIR__ . '/../controllers/CajaController.php',
    ],
    'socios' => [
        'title'      => 'Socios de Negocio',
        'slug'       => 'socios',
        'aliases'    => ['clientes', 'suplidores', 'aseguradoras'],
        'auth'       => true,
        'controller' => 'SocioController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/SocioController.php',
    ],
    'facturacion' => [
        'title'      => 'Facturación de Venta',
        'slug'       => 'facturacion',
        'aliases'    => ['pos', 'factura', 'ventas'],
        'auth'       => true,
        'controller' => 'FacturaController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/FacturaController.php',
    ],
    'cobro' => [
        'title'      => 'Cobro de Factura',
        'slug'       => 'cobro',
        'aliases'    => ['procesar-cobro'],
        'auth'       => true,
        'controller' => 'FacturaController',
        'method'     => 'mostrarPantallaCobro',
        'file'       => __DIR__ . '/../controllers/FacturaController.php',
    ],
    'aprobaciones_ars' => [
        'title'      => 'Registro de Aprobaciones ARS',
        'slug'       => 'aprobaciones_ars',
        'aliases'    => ['aprobaciones-ars', 'reclamaciones'],
        'auth'       => true,
        'controller' => 'ARSCodController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/ARSCodController.php',
    ],
    'consulta_aprobaciones' => [
        'title'      => 'Consulta de Aprobaciones ARS',
        'slug'       => 'consulta_aprobaciones',
        'aliases'    => ['consulta-aprobaciones', 'historial-aprobaciones', 'gestion-ars'],
        'auth'       => true,
        'controller' => 'ARSCodController',
        'method'     => 'consulta',
        'file'       => __DIR__ . '/../controllers/ARSCodController.php',
    ],
    'consulta_notaNCF' => [
        'title'      => 'Consulta de Notas de Crédito NCF',
        'slug'       => 'consulta_notaNCF',
        'aliases'    => ['consulta-notancf', 'consulta_notancf', 'historial-notas-credito'],
        'auth'       => true,
        'controller' => 'NotaNCFController',
        'method'     => 'consulta',
        'file'       => __DIR__ . '/../controllers/NotaNCFController.php',
    ],
    'nota_credito_ncf' => [
        'title'      => 'Emisión de Nota de Crédito NCF',
        'slug'       => 'nota_credito_ncf',
        'aliases'    => ['nota-credito-ncf', 'nota_credito', 'nota-credito'],
        'auth'       => true,
        'controller' => 'NotaNCFController',
        'method'     => 'index',
        'file'       => __DIR__ . '/../controllers/NotaNCFController.php',
    ],
    'consulta_facturas' => [
        'title'      => 'Historial y Consulta de Facturas',
        'slug'       => 'consulta_facturas',
        'aliases'    => ['consulta-facturas', 'historial-facturas', 'ventas-historial'],
        'auth'       => true,
        'controller' => 'FacturaController',
        'method'     => 'consulta',
        'file'       => __DIR__ . '/../controllers/FacturaController.php',
    ],
];

/**
 * Función para resolver el nombre de página a partir de una clave, slug o alias
 */
function resolverRuta($solicitud, $rutas) {
    if (empty($solicitud)) {
        return isset($_SESSION['logueado']) ? 'dashboard' : 'login';
    }
    
    $claveLimpia = trim(strtolower($solicitud));
    
    // 1. Coincidencia directa por clave
    foreach ($rutas as $key => $info) {
        if (strtolower($key) === $claveLimpia) {
            return $key;
        }
    }
    
    // 2. Coincidencia por slug o aliases
    foreach ($rutas as $key => $info) {
        if (isset($info['slug']) && strtolower($info['slug']) === $claveLimpia) {
            return $key;
        }
        if (!empty($info['aliases'])) {
            foreach ($info['aliases'] as $alias) {
                if (strtolower($alias) === $claveLimpia) {
                    return $key;
                }
            }
        }
    }
    
    return $solicitud;
}

// Resolver la ruta activa
$page = resolverRuta($page, $appRoutes);

// Validar existencia de la ruta
if (!isset($appRoutes[$page])) {
    if (isset($_SESSION['logueado'])) {
        header("Location: index.php?page=dashboard");
    } else {
        header("Location: index.php?page=login");
    }
    exit();
}

$rutaActual = $appRoutes[$page];
$pageTitle = $rutaActual['title'];

// ==========================================
// DESPACHO DE LA VISTA O CONTROLADOR
// ==========================================

if (isset($rutaActual['type']) && $rutaActual['type'] === 'view') {
    include $rutaActual['file'];
} elseif (isset($rutaActual['controller'])) {
    require_once $rutaActual['file'];
    $controllerClass = $rutaActual['controller'];
    $metodo = $rutaActual['method'] ?? 'index';
    $instancia = new $controllerClass();
    $instancia->$metodo();
}
