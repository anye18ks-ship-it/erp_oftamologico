<?php
error_reporting(0);
ini_set('display_errors', '0');

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helper.php';

 $action = $_GET['action'] ?? $_POST['action'] ?? '';

// Manejador de errores
set_error_handler(function($errno, $errstr) {
    jsonResponse(false, "Error PHP: $errstr", null, 500);
});

set_exception_handler(function($exception) {
    jsonResponse(false, "Excepción: " . $exception->getMessage(), null, 500);
});

try {
    switch ($action) {
        
        // ==========================================
        // OBTENER DATOS INICIALES
        // ==========================================
        case 'getInitData':
            $tipos = getSelectAll("SELECT [Code], [Name] FROM [SBO_AGROFEM].[@DOCUMENTOS] WITH (NOLOCK) WHERE Code in ('31','32','44','45') ORDER BY [Code]", getSAPDB());
            $almacenes = getSelectAll("SELECT sap_whscode, sap_whscode FROM " . DB_MAIN . ".dbo.usuario_almacen WITH (NOLOCK) WHERE id_usuario = 0" . USER_ID . " AND sap_whscode != 'TODOS' ORDER BY 1", getDB());
            jsonResponse(true, 'OK', [
                'tiposComprobante' => $tipos,
                'almacenes' => $almacenes,
                'fechaFactura' => date('Y-m-d'),
                'fechaEntrega' => date('Y-m-d')
            ]);
            break;
        
        // ==========================================
        // BUSCAR CLIENTE (BD SAP)
        // ==========================================
        case 'buscarCliente':
            $codigo = $_GET['codigo'] ?? '';
            if (empty($codigo)) {
                jsonResponse(false, 'Código vacío', null, 400);
            }
            
            $connSAP = getSAPDB();
            $codigo = cleanSQL($codigo);
            
            // Búsqueda exacta
            $sql = "SELECT TOP 1 
                    CardCode, CardName, CardFName, U_NIT, U_Comprob, ListNum, 
                    Discount, AddID, ExtraDays, PymntGroup, validFor
                FROM SBO_AGROFEM.dbo.OCRD WITH (NOLOCK)
                WHERE CardType = 'C' AND validFor = 'Y'
                  AND Currency in ('RD$', '##')
                  AND '{$codigo}' in (CardCode, U_CodAnt, AddID)";
            
            $result = getSelectFirstRow($sql, $connSAP);
            
            // Si no encuentra, buscar con LIKE
            if (empty($result)) {
                $sql = "SELECT TOP 1 
                        CardCode, CardName, CardFName, U_NIT, U_Comprob, ListNum, 
                        Discount, AddID, ExtraDays, PymntGroup, validFor
                    FROM SBO_AGROFEM.dbo.OCRD WITH (NOLOCK)
                    WHERE CardType = 'C' AND validFor = 'Y'
                      AND Currency in ('RD$', '##')
                      AND (
                          CardCode LIKE '%{$codigo}%' 
                          OR CardName LIKE '%{$codigo}%'
                          OR U_NIT LIKE '%{$codigo}%'
                      )
                    ORDER BY CardName";
                $result = getSelectFirstRow($sql, $connSAP);
            }
            
            if (empty($result)) {
                jsonResponse(false, 'Cliente no encontrado', null, 404);
            }
            
            // Obtener nombre de lista de precio (BD SAP)
            $listaSql = "SELECT ListName FROM SBO_AGROFEM.dbo.OPLN WHERE ListNum = " . (int)($result['ListNum'] ?? 1);
            $listaRow = getSelectFirstRow($listaSql, $connSAP);
            $result['ListName'] = $listaRow['ListName'] ?? '';
            
            jsonResponse(true, 'OK', $result);
            break;
        
        // ==========================================
        // BUSCAR LISTA DE CLIENTES PARA MODAL (BD SAP)
        // ==========================================
        case 'buscarClientesLista':
            $filtro = $_GET['filtro'] ?? '';
            $mostrarTodos = ($_GET['mostrarTodos'] ?? 'N') === 'Y';
            
            $sql = "SELECT TOP 100 
                    T0.CardCode as CODIGO, 
                    ISNULL(T0.U_NIT, '') as RNC, 
                    T0.CardName as NOMBRE, 
                    T0.CardFName as NOMBRE_COMERCIAL,
                    T0.U_Comprob as TIPO_COMPROB,
                    T0.ExtraDays as DIAS_CREDITO
                FROM SBO_AGROFEM.dbo.OCRD T0 WITH (NOLOCK)
                WHERE T0.CardType = 'C' AND T0.validFor = 'Y'
                  AND T0.Currency in ('RD$', '##')";
            
            if (!empty($filtro) && !$mostrarTodos) {
                $sql .= " AND (
                            T0.CardCode LIKE '%" . cleanSQL($filtro) . "%' 
                            OR T0.CardName LIKE '%" . cleanSQL($filtro) . "%'
                            OR T0.U_NIT LIKE '%" . cleanSQL($filtro) . "%'
                         )";
            }
            
            $sql .= " ORDER BY T0.CardName";
            
            $clientes = getSelectAll($sql, getSAPDB());
            jsonResponse(true, 'OK', $clientes);
            break;
        
        // ==========================================
        // BUSCAR ARTÍCULO (BD SAP)
        // ==========================================
        case 'buscarArticulo':
            $codigo = $_GET['codigo'] ?? '';
            $listaPrecio = $_GET['listaPrecio'] ?? 1;
            $almacen = $_GET['almacen'] ?? ALMACEN;
            
            if (empty($codigo)) {
                jsonResponse(false, 'Código vacío', null, 400);
            }
            
            $connSAP = getSAPDB();
            $codigo = cleanSQL($codigo);
            
            // Búsqueda exacta
            $sql = "SELECT TOP 1 
                    T0.ItemCode as [Código],
                    T0.ItemName as [Descripción],
                    T0.SalUnitmsr as [Unidad de medida],
                    T1.Price as [Precio de venta],
                    T0.TaxCodeAR as [Indicador de impuesto],
                    T2.Rate as [Impuesto %],
                    '{$almacen}' as [Almacén],
                    T0.InvntItem,
                    T4.OnHand
                FROM SBO_AGROFEM.dbo.OITM T0 WITH (NOLOCK)
                INNER JOIN SBO_AGROFEM.dbo.ITM1 T1 WITH (NOLOCK) ON T1.ItemCode = T0.ItemCode AND T1.PriceList = {$listaPrecio}
                INNER JOIN SBO_AGROFEM.dbo.OITW T4 WITH (NOLOCK) ON T0.ItemCode = T4.ItemCode AND T4.WhsCode = '{$almacen}' AND T4.Locked = 'N'
                LEFT JOIN SBO_AGROFEM.dbo.OSTC T2 WITH (NOLOCK) ON T2.Code = T0.TaxCodeAR
                WHERE T0.SellItem = 'Y' AND T0.validFor = 'Y'
                  AND '{$codigo}' in (T0.ItemCode, T0.U_CodAnt)";
            
            $result = getSelectFirstRow($sql, $connSAP);
            
            // Si no encuentra, buscar con LIKE
            if (empty($result)) {
                $sql = "SELECT TOP 1 
                        T0.ItemCode as [Código],
                        T0.ItemName as [Descripción],
                        T0.SalUnitmsr as [Unidad de medida],
                        T1.Price as [Precio de venta],
                        T0.TaxCodeAR as [Indicador de impuesto],
                        T2.Rate as [Impuesto %],
                        '{$almacen}' as [Almacén],
                        T0.InvntItem,
                        T4.OnHand
                    FROM SBO_AGROFEM.dbo.OITM T0 WITH (NOLOCK)
                    INNER JOIN SBO_AGROFEM.dbo.ITM1 T1 WITH (NOLOCK) ON T1.ItemCode = T0.ItemCode AND T1.PriceList = {$listaPrecio}
                    INNER JOIN SBO_AGROFEM.dbo.OITW T4 WITH (NOLOCK) ON T0.ItemCode = T4.ItemCode AND T4.WhsCode = '{$almacen}' AND T4.Locked = 'N'
                    LEFT JOIN SBO_AGROFEM.dbo.OSTC T2 WITH (NOLOCK) ON T2.Code = T0.TaxCodeAR
                    WHERE T0.SellItem = 'Y' AND T0.validFor = 'Y'
                      AND (T0.ItemCode LIKE '%{$codigo}%' OR T0.ItemName LIKE '%{$codigo}%')
                    ORDER BY T0.ItemName";
                $result = getSelectFirstRow($sql, $connSAP);
            }
            
            if (empty($result)) {
                jsonResponse(false, 'Artículo no encontrado', null, 404);
            }
            
            // Obtener existencia (BD CMS usando función)
            if (!empty($result) && !empty($result['Código'])) {
                $sqlExist = "SELECT " . DB_MAIN . ".dbo.get_onhand_item('{$almacen}', '" . $result['Código'] . "') as OnHand";
                $existResult = getSelectFirstRow($sqlExist, getDB());
                $result['OnHand'] = $existResult['OnHand'] ?? 0;
            }
            
            jsonResponse(true, 'OK', $result);
            break;
        
        // ==========================================
        // BUSCAR LISTA DE ARTÍCULOS PARA MODAL (BD SAP)
        // ==========================================
        case 'buscarArticulosLista':
            $filtro = $_GET['filtro'] ?? '';
            $listaPrecio = $_GET['listaPrecio'] ?? 1;
            $almacen = $_GET['almacen'] ?? ALMACEN;
            $cardCode = $_GET['cardCode'] ?? '';
            
            $sql = "SELECT TOP 100 
                    T0.ItemCode as CODIGO,
                    T0.ItemName as DESCRIPCION,
                    T0.SalUnitmsr as UNIDAD,
                    T4.OnHand as EXISTENCIA,
                    T1.Price as PRECIO_SIN_IMP,
                    T2.Rate as PORC_IMPUESTO
                FROM SBO_AGROFEM.dbo.OITM T0 WITH (NOLOCK)
                INNER JOIN SBO_AGROFEM.dbo.ITM1 T1 WITH (NOLOCK) ON T1.ItemCode = T0.ItemCode AND T1.PriceList = {$listaPrecio}
                INNER JOIN SBO_AGROFEM.dbo.OITW T4 WITH (NOLOCK) ON T0.ItemCode = T4.ItemCode AND T4.WhsCode = '{$almacen}' AND T4.Locked = 'N'
                LEFT JOIN SBO_AGROFEM.dbo.OSTC T2 WITH (NOLOCK) ON T2.Code = T0.TaxCodeAR
                WHERE T0.SellItem = 'Y' AND T0.validFor = 'Y'";
            
            if (!empty($filtro)) {
                $sql .= " AND (T0.ItemCode LIKE '%" . cleanSQL($filtro) . "%' OR T0.ItemName LIKE '%" . cleanSQL($filtro) . "%')";
            }
            
            $sql .= " ORDER BY T0.ItemName";
            
            $articulos = getSelectAll($sql, getSAPDB());
            jsonResponse(true, 'OK', $articulos);
            break;
        
        // ==========================================
        // VALIDAR NCF (BD CMS)
        // ==========================================
        case 'validarNCF':
            $tipoComprobante = $_GET['tipoComprobante'] ?? '';
            $serieComprob = $_GET['serieComprob'] ?? '';
            
            $sql = "SELECT 
                        CASE WHEN (T1.final - T1.contador) - 1 <= T0.alerta THEN 'Y' ELSE 'N' END as alertar,
                        (T1.final - T1.contador) - 1 as restantes,
                        T0.longitud,
                        T0.serie,
                        FORMAT(T1.vencimiento, 'yyyy/MM/dd') as vencimiento
                    FROM " . DB_MAIN . ".[dbo].[serie_ncf] T0,
                         " . DB_MAIN . ".[dbo].[carga_ncf] T1
                    WHERE T0.serie like '%" . cleanSQL($tipoComprobante) . "%'
                    AND T0.serie = T1.serie
                    AND T1.inicial < T1.final
                    AND T1.contador < T1.final
                    AND T1.id_carga IN (
                        SELECT MIN(id_carga) 
                        FROM " . DB_MAIN . ".[dbo].[carga_ncf]
                        WHERE inicial < final AND contador < final AND serie like '%" . cleanSQL($tipoComprobante) . "%'
                    )";
            
            $result = getSelectFirstRow($sql, getDB());
            
            if (empty($result) || $result['alertar'] === null) {
                jsonResponse(true, 'OK', ['alertar' => 'Y', 'restantes' => 0, 'longitud' => 0, 'serie' => '', 'vencimiento' => '']);
            }
            
            jsonResponse(true, 'OK', $result);
            break;
        
        // ==========================================
        // INFO FINANCIERA (BD SAP)
        // ==========================================
        case 'getInfoFinanciera':
            $cardCode = $_GET['cardCode'] ?? '';
            
            $sql = "SELECT TOP 1 CreditLine, 0 as Balance, ExtraDays 
                    FROM SBO_AGROFEM.dbo.OCRD WHERE CardCode = '{$cardCode}'";
            
            $result = getSelectFirstRow($sql, getSAPDB());
            jsonResponse(true, 'OK', $result);
            break;
        
        // ==========================================
        // CREAR FACTURA (usa AMBAS bases de datos)
        // ==========================================
        case 'crearFactura':
            $datos = [
                'serie' => $_POST['serie'] ?? SERIE,
                'tipoComprobante' => $_POST['tipoComprobante'] ?? '31',
                'fechaDoc' => $_POST['fechaDoc'] ?? date('Y-m-d'),
                'fechaEntrega' => $_POST['fechaEntrega'] ?? date('Y-m-d'),
                'cardCode' => $_POST['cardCode'] ?? '',
                'cardName' => $_POST['cardName'] ?? '',
                'rnc' => $_POST['rnc'] ?? '',
                'facNom' => $_POST['facNom'] ?? '',
                'direccion' => $_POST['direccion'] ?? '',
                'almacen' => $_POST['almacen'] ?? ALMACEN,
                'condicion' => $_POST['condicion'] ?? 1,
                'comentarios' => $_POST['comentarios'] ?? '',
                'ordenCompra' => $_POST['ordenCompra'] ?? '',
                'empleado' => $_POST['empleado'] ?? USER_NAME,
                'listaNum' => $_POST['listaNum'] ?? 1,
                'listaName' => $_POST['listaName'] ?? '',
                'descuentoCliente' => $_POST['descuentoCliente'] ?? 0,
                'docSource' => $_POST['docSource'] ?? '',
                'ordenPedido' => $_POST['ordenPedido'] ?? '',
                'cajaAperturaId' => $_POST['cajaAperturaId'] ?? CAJA_APERTURA_ID,
                'lineas' => json_decode($_POST['lineas'] ?? '[]', true)
            ];
            
            if (empty($datos['cardCode'])) {
                jsonResponse(false, 'Código de cliente requerido', null, 400);
            }
            if (empty($datos['lineas'])) {
                jsonResponse(false, 'Debe incluir al menos un artículo', null, 400);
            }
            
            // Instanciar la clase Facturacion
            $facturacion = new Facturacion();
            $resultado = $facturacion->crearFactura($datos);
            
            if ($resultado['success']) {
                jsonResponse(true, $resultado['mensaje'], $resultado);
            } else {
                jsonResponse(false, $resultado['mensaje'], null, 500);
            }
            break;
        
        default:
            jsonResponse(false, 'Acción no válida: ' . $action, null, 400);
    }
} catch (Exception $e) {
    jsonResponse(false, "Error: " . $e->getMessage(), null, 500);
}
?>