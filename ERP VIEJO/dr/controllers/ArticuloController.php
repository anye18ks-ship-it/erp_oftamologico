<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class ArticuloController {

    public function index() {
        $database = new Database();
        $pdo = $database->getConnection();

        // Consulta de artículos para el directorio con información relacionada
        $sql = "SELECT a.ItemCode, a.ItemName, a.FrgnName, a.ItmsGrpCod, g.ItmsGrpNam, 
                       a.CodeBars, a.TaxCodeAr, t.Name AS TaxName, t.Rate AS TaxRate, 
                       a.DfltWH, w.WhsName AS DfltWHName,
                       a.PrchseItem, a.SellItem, a.InvntItem, a.OnHand, a.AvgPrice,
                       a.QryGroup1, a.QryGroup2, a.QryGroup3, a.QryGroup4, a.QryGroup5, 
                       a.QryGroup6, a.QryGroup7, a.QryGroup8, a.QryGroup9, a.QryGroup10,
                       a.QryGroup54, a.QryGroup55, a.QryGroup56, a.QryGroup57, a.QryGroup58, 
                       a.QryGroup59, a.QryGroup60, a.QryGroup61, a.QryGroup62, a.QryGroup63, a.QryGroup64
                FROM posOITM a
                LEFT JOIN posOITB g ON a.ItmsGrpCod = g.ItmsGrpCod
                LEFT JOIN posOSTC t ON a.TaxCodeAr = t.Code
                LEFT JOIN posOWHS w ON a.DfltWH = w.WhsCode
                ORDER BY a.ItemCode ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lógica de Selección de Artículo para Edición
        $articuloSeleccionado = null;
        $preciosItem = [];
        $almacenesItem = [];
        $todosAlmacenes = [];

        $itemSeleccionado = isset($_GET['item']) ? trim($_GET['item']) : null;

        if (!empty($itemSeleccionado)) {
            // Obtener datos del artículo para el formulario
            $sqlItem = "SELECT * FROM posOITM WHERE ItemCode = ?";
            $stmtItem = $pdo->prepare($sqlItem);
            $stmtItem->execute([$itemSeleccionado]);
            $articuloSeleccionado = $stmtItem->fetch(PDO::FETCH_ASSOC);

            if ($articuloSeleccionado) {
                // Obtener las listas de precios de positm1 para ese artículo
                $sqlPrecios = "SELECT p1.PriceList, p1.Price, opl.ListName 
                               FROM positm1 p1 
                               JOIN posOPLN opl ON p1.PriceList = opl.ListNum 
                               WHERE p1.ItemCode = ? 
                               ORDER BY opl.ListName";
                $stmtPrecios = $pdo->prepare($sqlPrecios);
                $stmtPrecios->execute([$itemSeleccionado]);
                $preciosItem = $stmtPrecios->fetchAll(PDO::FETCH_ASSOC);

                // Obtener los almacenes permitidos para este artículo
                $sqlAlmItem = "SELECT WhsCode FROM posOITW WHERE ItemCode = ?";
                $stmtAlm = $pdo->prepare($sqlAlmItem);
                $stmtAlm->execute([$itemSeleccionado]);
                $almacenesItem = $stmtAlm->fetchAll(PDO::FETCH_COLUMN, 0);
            }
        }

        // Valores predeterminados para la creación
        $defaultGrp = $articuloSeleccionado['ItmsGrpCod'] ?? null;
        if ($defaultGrp === null) {
            $stmtFirstGrp = $pdo->query("SELECT TOP 1 ItmsGrpCod FROM posOITB ORDER BY ItmsGrpCod ASC");
            $defaultGrp = $stmtFirstGrp ? $stmtFirstGrp->fetchColumn() : null;
        }

        $defaultTax = $articuloSeleccionado['TaxCodeAr'] ?? null;
        if ($defaultTax === null) {
            $stmtTax = $pdo->query("SELECT TOP 1 Code FROM posOSTC WHERE Code = 'EXE'");
            $defaultTax = $stmtTax ? $stmtTax->fetchColumn() : null;
            if (!$defaultTax) {
                $stmtTax2 = $pdo->query("SELECT TOP 1 Code FROM posOSTC ORDER BY Code ASC");
                $defaultTax = $stmtTax2 ? $stmtTax2->fetchColumn() : 'EXE';
            }
        }

        // Definición de consultas para los combos
        $sqlGrupos = "SELECT ItmsGrpCod, ItmsGrpNam FROM posOITB ORDER BY ItmsGrpNam";
        $sqlImpuestos = "SELECT Code, Code + ' - ' + Name AS NombreImpuesto FROM posOSTC ORDER BY Name";
        $sqlAlmacenes = "SELECT WhsCode, WhsCode + ' - ' + WhsName AS NombreAlmacen FROM posOWHS ORDER BY WhsName"; 
        $sqlListasPrecios = "SELECT ListNum, ListName FROM posOPLN ORDER BY ListName";

        // Combos principales con selección automática del valor actual o por defecto
        $comboGrupos = Helper::llenarCombo($pdo, $sqlGrupos, 'ItmsGrpCod', 'ItmsGrpNam', 'ItmsGrpCod', $defaultGrp);
        $comboImpuestos = Helper::llenarCombo($pdo, $sqlImpuestos, 'Code', 'NombreImpuesto', 'TaxCodeAr', $defaultTax);
        $comboAlmacenes = Helper::llenarCombo($pdo, $sqlAlmacenes, 'WhsCode', 'NombreAlmacen', 'DfltWH', $articuloSeleccionado['DfltWH'] ?? null);
        $comboFiltroGrupo = Helper::llenarCombo($pdo, $sqlGrupos, 'ItmsGrpCod', 'ItmsGrpNam', 'filtroGrupo');
        $comboListasPrecios = Helper::llenarCombo($pdo, $sqlListasPrecios, 'ListNum', 'ListName', 'PriceList');

        // Obtener TODOS los almacenes disponibles para mostrar en las tarjetas
        $sqlTodosAlm = "SELECT WhsCode, WhsName FROM posOWHS ORDER BY WhsName";
        $stmtTodosAlm = $pdo->prepare($sqlTodosAlm);
        $stmtTodosAlm->execute();
        $todosAlmacenes = $stmtTodosAlm->fetchAll(PDO::FETCH_ASSOC);

        // Obtener catálogo completo de grupos de artículos para mapeo dinámico
        $stmtTodosGrupos = $pdo->prepare($sqlGrupos);
        $stmtTodosGrupos->execute();
        $todosGrupos = $stmtTodosGrupos->fetchAll(PDO::FETCH_ASSOC);
		
        // Pasamos las variables a la vista
        include __DIR__ . '/../views/Articulos/articulos.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();

            $codigo_original = isset($_POST['codigo_original']) ? trim($_POST['codigo_original']) : '';
            $checkToYN = function($field) { return (isset($_POST[$field]) && $_POST[$field] !== 'N') ? 'Y' : 'N'; };
            
            $ItemCode = strtoupper(trim($_POST['ItemCode'] ?? ''));
            $ItemName = trim($_POST['ItemName'] ?? '');
            $FrgnName = trim($_POST['FrgnName'] ?? '');
            
            $ItmsGrpCod = (isset($_POST['ItmsGrpCod']) && $_POST['ItmsGrpCod'] !== '') ? (int)$_POST['ItmsGrpCod'] : null;
            $CodeBars = trim($_POST['CodeBars'] ?? ''); 
            $TaxCodeAr = (isset($_POST['TaxCodeAr']) && $_POST['TaxCodeAr'] !== '') ? trim($_POST['TaxCodeAr']) : null; 
            $DfltWH = (isset($_POST['DfltWH']) && $_POST['DfltWH'] !== '') ? trim($_POST['DfltWH']) : null;
            
            $rawAvgPrice = str_replace(',', '', $_POST['AvgPrice'] ?? '0');
            $AvgPrice = is_numeric($rawAvgPrice) ? (float)$rawAvgPrice : 0.00;
            
            $almacenesSeleccionados = isset($_POST['almacenes']) && is_array($_POST['almacenes']) ? array_values(array_filter(array_map('trim', $_POST['almacenes']))) : [];

            // Flags de compra, venta e inventario
            $PrchseItem = $checkToYN('PrchseItem');
            $SellItem   = $checkToYN('SellItem');
            $InvntItem  = $checkToYN('InvntItem');

            // Propiedades SAP (QryGroup)
            // QryGroup1 = Editable en Facturación
            // QryGroup2 = Artículo Favorito / Destacado
            $Q1  = $checkToYN('QryGroup1');
            $Q2  = $checkToYN('QryGroup2');
            $Q3  = $checkToYN('QryGroup3');
            $Q4  = $checkToYN('QryGroup4');
            $Q5  = $checkToYN('QryGroup5');
            $Q6  = $checkToYN('QryGroup6');
            $Q7  = $checkToYN('QryGroup7');
            $Q8  = $checkToYN('QryGroup8');
            $Q9  = $checkToYN('QryGroup9');
            $Q10 = $checkToYN('QryGroup10');

            $Q54 = $checkToYN('QryGroup54');
            $Q55 = $checkToYN('QryGroup55');
            $Q56 = $checkToYN('QryGroup56');
            $Q57 = $checkToYN('QryGroup57');
            $Q58 = $checkToYN('QryGroup58');
            $Q59 = $checkToYN('QryGroup59');
            $Q60 = $checkToYN('QryGroup60');
            $Q61 = $checkToYN('QryGroup61');
            $Q62 = $checkToYN('QryGroup62');
            $Q63 = $checkToYN('QryGroup63');
            $Q64 = $checkToYN('QryGroup64');

            if (empty($ItemCode) || empty($ItemName)) {
                Helper::setMensaje('danger', 'El código y la descripción del artículo son obligatorios.');
                header("Location: index.php?page=articulos" . (!empty($codigo_original) ? '&item=' . urlencode($codigo_original) : ''));
                exit();
            }

            try {
                // Asignar valores por defecto dinámicos si no fueron seleccionados
                if (empty($TaxCodeAr)) {
                    $stmtTax = $pdo->query("SELECT TOP 1 Code FROM posOSTC WHERE Code = 'EXE'");
                    $defaultTax = $stmtTax ? $stmtTax->fetchColumn() : null;
                    if (!$defaultTax) {
                        $stmtTax2 = $pdo->query("SELECT TOP 1 Code FROM posOSTC ORDER BY Code ASC");
                        $defaultTax = $stmtTax2 ? $stmtTax2->fetchColumn() : 'EXE';
                    }
                    $TaxCodeAr = $defaultTax ?: 'EXE';
                }

                // Sincronizar almacén predeterminado con los almacenes seleccionados (solo si es inventariable)
                if ($InvntItem === 'N') {
                    // SERVICIO CLÍNICO / PROCEDIMIENTO: No maneja existencias ni almacenes en posOITW
                    $DfltWH = null;
                    $almacenesSeleccionados = [];
                    $CodeBars = ''; // En servicios clínicos no se maneja código de barras
                } else {
                    if (empty($DfltWH) && !empty($almacenesSeleccionados)) {
                        $DfltWH = $almacenesSeleccionados[0];
                    } elseif (empty($DfltWH)) {
                        $stmtWhs = $pdo->query("SELECT TOP 1 WhsCode FROM posOWHS ORDER BY WhsCode ASC");
                        $DfltWH = $stmtWhs ? $stmtWhs->fetchColumn() : null;
                    }

                    // Garantizar que el almacén predeterminado esté dentro de la lista de almacenes permitidos
                    if (!empty($DfltWH) && !in_array($DfltWH, $almacenesSeleccionados)) {
                        $almacenesSeleccionados[] = $DfltWH;
                    }
                }

                if (empty($codigo_original)) {
                    // INSERTAR NUEVO ARTÍCULO (AvgPrice y OnHand inician en 0 hasta sus entradas de mercancía)
                    $sql = "INSERT INTO posOITM (
                                ItemCode, ItemName, FrgnName, ItmsGrpCod, CodeBars, TaxCodeAr, DfltWH, VATLiable, PrchseItem, SellItem, InvntItem, OnHand, AvgPrice,
                                QryGroup1, QryGroup2, QryGroup3, QryGroup4, QryGroup5, QryGroup6, QryGroup7, QryGroup8, QryGroup9, QryGroup10,
                                QryGroup11, QryGroup12, QryGroup13, QryGroup14, QryGroup15, QryGroup16, QryGroup17, QryGroup18, QryGroup19, QryGroup20,
                                QryGroup21, QryGroup22, QryGroup23, QryGroup24, QryGroup25, QryGroup26, QryGroup27, QryGroup28, QryGroup29, QryGroup30,
                                QryGroup31, QryGroup32, QryGroup33, QryGroup34, QryGroup35, QryGroup36, QryGroup37, QryGroup38, QryGroup39, QryGroup40,
                                QryGroup41, QryGroup42, QryGroup43, QryGroup44, QryGroup45, QryGroup46, QryGroup47, QryGroup48, QryGroup49, QryGroup50,
                                QryGroup51, QryGroup52, QryGroup53,
                                QryGroup54, QryGroup55, QryGroup56, QryGroup57, QryGroup58, QryGroup59, QryGroup60, QryGroup61, QryGroup62, QryGroup63, QryGroup64
                            ) VALUES (
                                ?, ?, ?, ?, ?, ?, ?, 'N', ?, ?, ?, 0, 0,
                                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                                'N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N',
                                'N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N',
                                'N','N','N',
                                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                            )";
                    $params = [
                        $ItemCode, $ItemName, $FrgnName, $ItmsGrpCod, $CodeBars, $TaxCodeAr, $DfltWH, $PrchseItem, $SellItem, $InvntItem,
                        $Q1, $Q2, $Q3, $Q4, $Q5, $Q6, $Q7, $Q8, $Q9, $Q10,
                        $Q54, $Q55, $Q56, $Q57, $Q58, $Q59, $Q60, $Q61, $Q62, $Q63, $Q64
                    ];
                } else {
                    // ACTUALIZAR ARTÍCULO EXISTENTE (Preservando AvgPrice y OnHand que provienen de transacciones)
                    $sql = "UPDATE posOITM SET 
                                ItemName=?, FrgnName=?, ItmsGrpCod=?, CodeBars=?, TaxCodeAr=?, DfltWH=?, PrchseItem=?, SellItem=?, InvntItem=?,
                                QryGroup1=?, QryGroup2=?, QryGroup3=?, QryGroup4=?, QryGroup5=?, QryGroup6=?, QryGroup7=?, QryGroup8=?, QryGroup9=?, QryGroup10=?,
                                QryGroup54=?, QryGroup55=?, QryGroup56=?, QryGroup57=?, QryGroup58=?, QryGroup59=?, QryGroup60=?, QryGroup61=?, QryGroup62=?, QryGroup63=?, QryGroup64=?
                            WHERE ItemCode=?";
                    $params = [
                        $ItemName, $FrgnName, $ItmsGrpCod, $CodeBars, $TaxCodeAr, $DfltWH, $PrchseItem, $SellItem, $InvntItem,
                        $Q1, $Q2, $Q3, $Q4, $Q5, $Q6, $Q7, $Q8, $Q9, $Q10,
                        $Q54, $Q55, $Q56, $Q57, $Q58, $Q59, $Q60, $Q61, $Q62, $Q63, $Q64,
                        $codigo_original
                    ];
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $targetCode = empty($codigo_original) ? $ItemCode : $codigo_original;

                // =========================================================================
                // SINCRONIZACIÓN AUTOMÁTICA EN positm1 Y posOITW
                // =========================================================================
                // 1. Listas de Precios (positm1)
                $stmtPL = $pdo->query("SELECT ListNum FROM posOPLN ORDER BY ListNum ASC");
                $listas = $stmtPL ? $stmtPL->fetchAll(PDO::FETCH_COLUMN) : [];
                if (empty($listas)) {
                    $listas = [1];
                }

                $stmtCheckP1 = $pdo->prepare("SELECT COUNT(*) FROM positm1 WHERE ItemCode = ? AND PriceList = ?");
                $stmtInsP1 = $pdo->prepare("INSERT INTO positm1 (ItemCode, PriceList, Price) VALUES (?, ?, 0.00)");

                foreach ($listas as $listNum) {
                    $stmtCheckP1->execute([$targetCode, $listNum]);
                    if ($stmtCheckP1->fetchColumn() == 0) {
                        $stmtInsP1->execute([$targetCode, $listNum]);
                    }
                }

                // 2. Almacenes Permitidos (posOITW) - Solo para artículos con control de inventario físico
                if ($InvntItem === 'Y') {
                    $stmtCheckW = $pdo->prepare("SELECT COUNT(*) FROM posOITW WHERE ItemCode = ? AND WhsCode = ?");
                    $stmtInsW = $pdo->prepare("INSERT INTO posOITW (ItemCode, WhsCode, OnHand) VALUES (?, ?, 0.00)");

                    foreach ($almacenesSeleccionados as $whsCode) {
                        $stmtCheckW->execute([$targetCode, $whsCode]);
                        if ($stmtCheckW->fetchColumn() == 0) {
                            $stmtInsW->execute([$targetCode, $whsCode]);
                        }
                    }

                    // Si es edición y se desmarcaron almacenes, desasociar solo aquellos que NO tengan stock físico
                    if (!empty($codigo_original) && !empty($almacenesSeleccionados)) {
                        $inPlaceholders = implode(',', array_fill(0, count($almacenesSeleccionados), '?'));
                        $sqlDelW = "DELETE FROM posOITW 
                                    WHERE ItemCode = ? 
                                      AND WhsCode NOT IN ($inPlaceholders) 
                                      AND (OnHand IS NULL OR OnHand = 0)";
                        $paramsDel = array_merge([$targetCode], array_values($almacenesSeleccionados));
                        $stmtDelW = $pdo->prepare($sqlDelW);
                        $stmtDelW->execute($paramsDel);
                    }
                } else {
                    // SERVICIO CLÍNICO INTANGIBLE (posOITM.InvntItem = 'N', posOITM.DfltWH = NULL)
                    // Para compatibilidad total con la vista v_sale_item (requerida por facturación) sin alterar la BD,
                    // garantizamos un único registro en posOITW con OnHand = 0.00 en el almacén principal.
                    $stmtFirstW = $pdo->query("SELECT TOP 1 WhsCode FROM posOWHS ORDER BY WhsCode ASC");
                    $primaryWhs = $stmtFirstW ? $stmtFirstW->fetchColumn() : '01';

                    $stmtCheckSrvW = $pdo->prepare("SELECT COUNT(*) FROM posOITW WHERE ItemCode = ? AND WhsCode = ?");
                    $stmtCheckSrvW->execute([$targetCode, $primaryWhs]);
                    if ($stmtCheckSrvW->fetchColumn() == 0) {
                        $stmtInsSrvW = $pdo->prepare("INSERT INTO posOITW (ItemCode, WhsCode, OnHand) VALUES (?, ?, 0.00)");
                        $stmtInsSrvW->execute([$targetCode, $primaryWhs]);
                    }

                    // Limpiar almacenes excedentes sin stock si el artículo fue transformado de físico a servicio
                    $sqlDelExtra = "DELETE FROM posOITW WHERE ItemCode = ? AND WhsCode != ? AND (OnHand IS NULL OR OnHand = 0)";
                    $stmtDelExtra = $pdo->prepare($sqlDelExtra);
                    $stmtDelExtra->execute([$targetCode, $primaryWhs]);
                }

                $tipoDesc = ($InvntItem === 'N') ? "Servicio Clínico" : "Artículo";
                $msg = empty($codigo_original) 
                    ? ($InvntItem === 'N' ? "Servicio Clínico {$targetCode} creado exitosamente." : "Artículo {$targetCode} creado y sincronizado en almacenes.") 
                    : ($InvntItem === 'N' ? "Servicio Clínico {$targetCode} actualizado exitosamente." : "Artículo {$targetCode} actualizado correctamente.");
                Helper::setMensaje('success', $msg);

            } catch (PDOException $e) {
                if ($e->getCode() == 2627 || strpos($e->getMessage(), 'PRIMARY KEY') !== false) { 
                    Helper::setMensaje('warning', "El código de artículo '{$ItemCode}' ya existe en el sistema."); 
                } else { 
                    Helper::setMensaje('danger', 'Error de base de datos: ' . $e->getMessage()); 
                }
            }

            header("Location: index.php?page=articulos");
            exit();
        }
    }

    // MÉTODO PARA AGREGAR / EDITAR PRECIOS EN POSITM1
    public function guardarPrecio() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();

            $itemCode = trim($_POST['itemCode'] ?? '');
            $rawPrice = str_replace(',', '', $_POST['price'] ?? '0');
            $price = is_numeric($rawPrice) ? (float)$rawPrice : 0.00;
            $listaOriginal = trim($_POST['lista_original'] ?? '');
            $priceList = trim($_POST['priceList'] ?? '');

            $listaFinal = !empty($listaOriginal) ? $listaOriginal : $priceList;

            try {
                if (empty($itemCode) || empty($listaFinal)) {
                    Helper::setMensaje('danger', 'Error: Se debe especificar el artículo y la lista de precios.');
                } else {
                    $sqlCheck = "SELECT COUNT(*) FROM positm1 WHERE ItemCode = ? AND PriceList = ?";
                    $stmtCheck = $pdo->prepare($sqlCheck);
                    $stmtCheck->execute([$itemCode, $listaFinal]);
                    $existe = $stmtCheck->fetchColumn();

                    if ($existe > 0) {
                        $sql = "UPDATE positm1 SET Price = ? WHERE ItemCode = ? AND PriceList = ?";
                        $pdo->prepare($sql)->execute([$price, $itemCode, $listaFinal]);
                        Helper::setMensaje('success', 'Precio actualizado correctamente.');
                    } else {
                        $sql = "INSERT INTO positm1 (ItemCode, PriceList, Price) VALUES (?, ?, ?)";
                        $pdo->prepare($sql)->execute([$itemCode, $listaFinal, $price]);
                        Helper::setMensaje('success', 'Nuevo precio registrado.');
                    }
                }
            } catch (PDOException $e) {
                Helper::setMensaje('danger', 'Error SQL al guardar precio: ' . $e->getMessage());
            }
            
            header("Location: index.php?page=articulos&item=" . urlencode($itemCode));
            exit();
        }
    }
	
    // MÉTODO PARA GUARDAR ALMACENES PERMITIDOS EN POSOITW
    public function guardarAlmacenesItem() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();

            $itemCode = trim($_POST['itemCode'] ?? '');
            $almacenes = $_POST['almacenes'] ?? [];

            if (empty($itemCode)) {
                Helper::setMensaje('danger', 'Error: No se especificó el artículo.');
                header("Location: index.php?page=articulos");
                exit();
            }

            try {
                // 1. Borramos los almacenes actuales de este artículo
                $pdo->prepare("DELETE FROM posOITW WHERE ItemCode = ?")->execute([$itemCode]);

                // 2. Insertamos los nuevos seleccionados
                if (!empty($almacenes) && is_array($almacenes)) {
                    $stmt = $pdo->prepare("INSERT INTO posOITW (ItemCode, WhsCode) VALUES (?, ?)");
                    foreach ($almacenes as $whsCode) {
                        $whsCodeClean = trim($whsCode);
                        if (!empty($whsCodeClean)) {
                            $stmt->execute([$itemCode, $whsCodeClean]);
                        }
                    }
                }
                Helper::setMensaje('success', 'Almacenes permitidos actualizados correctamente.');
            } catch (PDOException $e) {
                Helper::setMensaje('danger', 'Error al guardar almacenes: ' . $e->getMessage());
            }
            
            header("Location: index.php?page=articulos&item=" . urlencode($itemCode));
            exit();
        }
    }
}
?>