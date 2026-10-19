<?php
if (!defined('APP_RUNNING')) { exit('Acceso denegado'); }

// ==========================================
// PROCESAMIENTO DE ACCIONES (POST / AJAX)
// ==========================================

switch ($action) {
    case 'login':
        require_once __DIR__ . '/../controllers/AuthController.php';
        (new AuthController())->login();
        exit();

    case 'logout':
        require_once __DIR__ . '/../controllers/AuthController.php';
        (new AuthController())->logout();
        exit();

    case 'guardar_usuario':
        require_once __DIR__ . '/../controllers/UsuarioController.php';
        (new UsuarioController())->guardar();
        exit();

    case 'guardar_grupo':
        require_once __DIR__ . '/../controllers/GrupoArticuloController.php';
        (new GrupoArticuloController())->guardar();
        exit();

    case 'guardar_lista':
        require_once __DIR__ . '/../controllers/ListaPrecioController.php';
        (new ListaPrecioController())->guardar();
        exit();

    case 'guardar_almacen':
        require_once __DIR__ . '/../controllers/AlmacenController.php';
        (new AlmacenController())->guardar();
        exit();

    case 'guardar_impuesto':
        require_once __DIR__ . '/../controllers/TipoImpuestoController.php';
        (new TipoImpuestoController())->guardar();
        exit();

    case 'guardar_serie':
        require_once __DIR__ . '/../controllers/NcfController.php';
        (new NcfController())->guardarSerie();
        exit();

    case 'guardar_rango':
        require_once __DIR__ . '/../controllers/NcfController.php';
        (new NcfController())->guardarRango();
        exit();

    case 'guardar_articulo':
        require_once __DIR__ . '/../controllers/ArticuloController.php';
        (new ArticuloController())->guardar();
        exit();

    case 'guardar_precio_articulo':
        require_once __DIR__ . '/../controllers/ArticuloController.php';
        (new ArticuloController())->guardarPrecio();
        exit();

    case 'guardar_almacenes_item':
        require_once __DIR__ . '/../controllers/ArticuloController.php';
        (new ArticuloController())->guardarAlmacenesItem();
        exit();

    case 'guardar_condicion_pago':
        require_once __DIR__ . '/../controllers/CondicionesPagoController.php';
        (new CondicionesPagoController())->guardar();
        exit();

    case 'guardar_grupo_cp':
        require_once __DIR__ . '/../controllers/GrupoClienteProvController.php';
        (new GrupoClienteProvController())->guardar();
        exit();

    case 'abrir_caja':
        require_once __DIR__ . '/../controllers/CajaController.php';
        (new CajaController())->abrir();
        exit();

    case 'cerrar_caja':
        require_once __DIR__ . '/../controllers/CajaController.php';
        (new CajaController())->ejecutarCierre();
        exit();

    case 'visualizar_arqueo':
        require_once __DIR__ . '/../controllers/CajaController.php';
        (new CajaController())->visualizarArqueo();
        exit();

    case 'guardar_retiro':
        require_once __DIR__ . '/../controllers/CajaController.php';
        (new CajaController())->guardarRetiro();
        exit();

    case 'reporte_cierre_caja':
        require_once __DIR__ . '/../controllers/CajaController.php';
        (new CajaController())->reporteCierre();
        exit();

    case 'consulta_cierre':
        require_once __DIR__ . '/../controllers/CajaController.php';
        (new CajaController())->consultaCierre();
        exit();

    case 'reporte_retiro_caja':
        require_once __DIR__ . '/../controllers/CajaController.php';
        (new CajaController())->reporteRetiro();
        exit();

    case 'guardar_socio':
        require_once __DIR__ . '/../controllers/SocioController.php';
        (new SocioController())->guardar();
        exit();

    case 'buscar_expediente':
        require_once __DIR__ . '/../controllers/SocioController.php';
        (new SocioController())->buscarExpediente();
        exit();

    case 'generar_codigo_socio':
        require_once __DIR__ . '/../controllers/SocioController.php';
        (new SocioController())->generarCodigo();
        exit();

    case 'buscar_articulo_factura':
        require_once __DIR__ . '/../controllers/FacturaController.php';
        (new FacturaController())->buscarArticulo();
        exit();

    case 'buscar_cliente_factura':
        require_once __DIR__ . '/../controllers/FacturaController.php';
        (new FacturaController())->buscarCliente();
        exit();

    case 'guardar_factura_venta':
        require_once __DIR__ . '/../controllers/FacturaController.php';
        (new FacturaController())->guardarVenta();
        exit();

    case 'guardar_cobro':
        require_once __DIR__ . '/../controllers/FacturaController.php';
        (new FacturaController())->guardarCobro();
        exit();

    case 'obtener_detalle_factura':
        require_once __DIR__ . '/../controllers/FacturaController.php';
        (new FacturaController())->obtenerDetalleFactura();
        exit();

    case 'guardar_aprobacion_ars':
        require_once __DIR__ . '/../controllers/ARSCodController.php';
        (new ARSCodController())->guardar();
        exit();

    case 'buscar_aprobacion_ars':
        require_once __DIR__ . '/../controllers/ARSCodController.php';
        (new ARSCodController())->buscar();
        exit();

    case 'reporte_aprobacion_ars':
        require_once __DIR__ . '/../controllers/ARSCodController.php';
        (new ARSCodController())->reporteAprobacion();
        exit();

    case 'facturar_lote_ars':
        require_once __DIR__ . '/../controllers/ARSCodController.php';
        (new ARSCodController())->facturarLoteARS();
        exit();

    case 'reporte_fact_aprobacion':
        require_once __DIR__ . '/../controllers/ARSCodController.php';
        (new ARSCodController())->reporteFactAprobacion();
        exit();

    case 'marcar_cobro_ars':
        require_once __DIR__ . '/../controllers/ARSCodController.php';
        (new ARSCodController())->marcarCobroARS();
        exit();

    case 'marcar_rechazo_ars':
        require_once __DIR__ . '/../controllers/ARSCodController.php';
        (new ARSCodController())->marcarRechazoARS();
        exit();

    case 'consultar_trazabilidad_ars':
        require_once __DIR__ . '/../controllers/ARSCodController.php';
        (new ARSCodController())->consultarTrazabilidad();
        exit();

    case 'buscar_factura_nc':
        require_once __DIR__ . '/../controllers/NotaNCFController.php';
        (new NotaNCFController())->buscarFactura();
        exit();

    case 'buscar_articulo_nc':
        require_once __DIR__ . '/../controllers/NotaNCFController.php';
        (new NotaNCFController())->buscarArticulo();
        exit();

    case 'guardar_nota_credito':
        require_once __DIR__ . '/../controllers/NotaNCFController.php';
        (new NotaNCFController())->guardar();
        exit();

    case 'guardar_nota_credito_lote_ars':
        require_once __DIR__ . '/../controllers/NotaNCFController.php';
        (new NotaNCFController())->guardarLoteARS();
        exit();

    case 'obtener_detalle_nc':
        require_once __DIR__ . '/../controllers/NotaNCFController.php';
        (new NotaNCFController())->obtenerDetalleNC();
        exit();

    case 'reporte_nota_credito':
    case 'informeNCF':
        require_once __DIR__ . '/../controllers/NotaNCFController.php';
        (new NotaNCFController())->informeNCF();
        exit();

    // ==========================================
    // NOTAS DE DÉBITO (E33 / B03)
    // ==========================================
    case 'buscar_factura_nd':
        require_once __DIR__ . '/../controllers/NotaDebitoController.php';
        (new NotaDebitoController())->buscarFactura();
        exit();

    case 'buscar_nota_credito_nd':
        require_once __DIR__ . '/../controllers/NotaDebitoController.php';
        (new NotaDebitoController())->buscarNotaCredito();
        exit();

    case 'guardar_nota_debito':
        require_once __DIR__ . '/../controllers/NotaDebitoController.php';
        (new NotaDebitoController())->guardarNotaDebito();
        exit();

    case 'obtener_detalle_nd':
        require_once __DIR__ . '/../controllers/NotaDebitoController.php';
        (new NotaDebitoController())->obtenerDetalleND();
        exit();

    case 'reporte_nota_debito':
    case 'informeND':
        require_once __DIR__ . '/../controllers/NotaDebitoController.php';
        (new NotaDebitoController())->informeND();
        exit();

    // ==========================================
    // ENTRADA DE MERCANCÍAS & RECEPCIÓN
    // ==========================================
    case 'buscar_articulo_entrada':
        require_once __DIR__ . '/../controllers/EntradaMercanciaController.php';
        (new EntradaMercanciaController())->buscarArticulo();
        exit();

    case 'guardar_entrada_mercancia':
        require_once __DIR__ . '/../controllers/EntradaMercanciaController.php';
        (new EntradaMercanciaController())->guardar();
        exit();

    case 'obtener_detalle_entrada':
        require_once __DIR__ . '/../controllers/EntradaMercanciaController.php';
        (new EntradaMercanciaController())->obtenerDetalle();
        exit();

    // ==========================================
    // ASIGNACIÓN DE PRECIOS & COSTOS
    // ==========================================
    case 'guardar_precio_individual':
        require_once __DIR__ . '/../controllers/AsignacionPreciosController.php';
        (new AsignacionPreciosController())->guardarPrecioIndividual();
        exit();

    case 'guardar_lote_precios':
        require_once __DIR__ . '/../controllers/AsignacionPreciosController.php';
        (new AsignacionPreciosController())->guardarLote();
        exit();
}
?>
