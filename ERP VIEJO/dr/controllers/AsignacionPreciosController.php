<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class AsignacionPreciosController {

    public function index() {
        $database = new Database();
        $pdo = $database->getConnection();

        // 1. Obtener todas las listas de precios disponibles (posOPLN)
        $sqlListas = "SELECT ListNum, ListName FROM posOPLN ORDER BY ListNum ASC";
        $stmtL = $pdo->prepare($sqlListas);
        $stmtL->execute();
        $listas = $stmtL->fetchAll(PDO::FETCH_ASSOC);

        // Lista activa seleccionada (fallback al primer ListNum existente)
        $firstListNum = !empty($listas) ? (int)$listas[0]['ListNum'] : 1;
        $listaSeleccionada = isset($_GET['lista']) && is_numeric($_GET['lista']) ? (int)$_GET['lista'] : $firstListNum;

        // Nombre de la lista seleccionada
        $nombreListaSeleccionada = 'Lista de Precios';
        foreach ($listas as $l) {
            if ((int)$l['ListNum'] === $listaSeleccionada) {
                $nombreListaSeleccionada = $l['ListName'];
                break;
            }
        }

        // 2. Obtener grupos de artículos para filtro
        $sqlGrupos = "SELECT ItmsGrpCod, ItmsGrpNam FROM posOITB ORDER BY ItmsGrpNam ASC";
        $stmtG = $pdo->prepare($sqlGrupos);
        $stmtG->execute();
        $grupos = $stmtG->fetchAll(PDO::FETCH_ASSOC);

        // 3. Filtro por grupo si se seleccionó
        $filtroGrupo = isset($_GET['grupo']) && $_GET['grupo'] !== '' ? (int)$_GET['grupo'] : null;

        // 4. Auto-sincronizar artículos que no tengan registro en positm1 para esta lista
        $sqlAutoSync = "INSERT INTO positm1 (ItemCode, PriceList, Price)
                        SELECT a.ItemCode, ?, 0.00
                        FROM posOITM a
                        LEFT JOIN positm1 p ON a.ItemCode = p.ItemCode AND p.PriceList = ?
                        WHERE p.ItemCode IS NULL";
        $pdo->prepare($sqlAutoSync)->execute([$listaSeleccionada, $listaSeleccionada]);

        // 5. Consulta de artículos con su precio en la lista seleccionada y costo AvgPrice
        $sqlArticulos = "SELECT 
                            a.ItemCode, a.ItemName, a.CodeBars, a.ItmsGrpCod, g.ItmsGrpNam,
                            a.AvgPrice, a.OnHand, a.SellItem, a.PrchseItem, a.InvntItem,
                            ISNULL(p.Price, 0.00) AS Price
                         FROM posOITM a
                         LEFT JOIN posOITB g ON a.ItmsGrpCod = g.ItmsGrpCod
                         LEFT JOIN positm1 p ON a.ItemCode = p.ItemCode AND p.PriceList = ?
                         WHERE (? IS NULL OR a.ItmsGrpCod = ?)
                         ORDER BY a.ItemCode ASC";
        $stmtArt = $pdo->prepare($sqlArticulos);
        $stmtArt->execute([$listaSeleccionada, $filtroGrupo, $filtroGrupo]);
        $articulos = $stmtArt->fetchAll(PDO::FETCH_ASSOC);

        // 6. Métricas y KPIs de la Lista
        $totalArticulos = count($articulos);
        $conPrecio = 0;
        $sinPrecio = 0;
        $sumaMargen = 0;
        $conMargenCount = 0;

        foreach ($articulos as $a) {
            $precio = (float)$a['Price'];
            $costo = (float)$a['AvgPrice'];

            if ($precio > 0) {
                $conPrecio++;
                if ($costo > 0) {
                    $margen = (($precio - $costo) / $precio) * 100;
                    $sumaMargen += $margen;
                    $conMargenCount++;
                }
            } else {
                $sinPrecio++;
            }
        }

        $margenPromedio = $conMargenCount > 0 ? round($sumaMargen / $conMargenCount, 1) : 0;

        include __DIR__ . '/../views/Articulos/asignacion_precios.php';
    }

    // AJAX: Guardar precio de venta y costo individual de un artículo
    public function guardarPrecioIndividual() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        $database = new Database();
        $pdo = $database->getConnection();

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                throw new Exception("Datos de entrada inválidos.");
            }

            $itemCode = trim($input['ItemCode'] ?? '');
            $priceList = intval($input['PriceList'] ?? 1);
            $rawPrice = str_replace(',', '', (string)($input['Price'] ?? 0));
            $rawCost = str_replace(',', '', (string)($input['AvgPrice'] ?? 0));

            $price = is_numeric($rawPrice) ? (float)$rawPrice : 0.00;
            $avgPrice = is_numeric($rawCost) ? (float)$rawCost : 0.00;

            if (empty($itemCode)) {
                throw new Exception("Código de artículo no especificado.");
            }

            // 1. Guardar o actualizar Precio en positm1
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM positm1 WHERE ItemCode = ? AND PriceList = ?");
            $stmtCheck->execute([$itemCode, $priceList]);
            
            if ($stmtCheck->fetchColumn() > 0) {
                $stmtP = $pdo->prepare("UPDATE positm1 SET Price = ? WHERE ItemCode = ? AND PriceList = ?");
                $stmtP->execute([$price, $itemCode, $priceList]);
            } else {
                $stmtP = $pdo->prepare("INSERT INTO positm1 (ItemCode, PriceList, Price) VALUES (?, ?, ?)");
                $stmtP->execute([$itemCode, $priceList, $price]);
            }

            // 2. Guardar o actualizar Costo Promedio (AvgPrice) en posOITM
            $stmtC = $pdo->prepare("UPDATE posOITM SET AvgPrice = ? WHERE ItemCode = ?");
            $stmtC->execute([$avgPrice, $itemCode]);

            // Calcular margen nuevo
            $margen = 0;
            if ($price > 0 && $avgPrice > 0) {
                $margen = round((($price - $avgPrice) / $price) * 100, 1);
            }

            ob_end_clean();
            echo json_encode([
                'success' => true,
                'msg' => "Precio y costo actualizados para {$itemCode}.",
                'itemCode' => $itemCode,
                'price' => $price,
                'avgPrice' => $avgPrice,
                'margen' => $margen
            ]);

        } catch (Throwable $e) {
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    // AJAX: Guardar Lote masivo de cambios
    public function guardarLote() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        $database = new Database();
        $pdo = $database->getConnection();
        $pdo->beginTransaction();

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $items = $input['items'] ?? [];
            $priceList = intval($input['PriceList'] ?? 1);

            if (empty($items)) {
                throw new Exception("No hay elementos para actualizar.");
            }

            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM positm1 WHERE ItemCode = ? AND PriceList = ?");
            $stmtUpdP = $pdo->prepare("UPDATE positm1 SET Price = ? WHERE ItemCode = ? AND PriceList = ?");
            $stmtInsP = $pdo->prepare("INSERT INTO positm1 (ItemCode, PriceList, Price) VALUES (?, ?, ?)");
            $stmtUpdC = $pdo->prepare("UPDATE posOITM SET AvgPrice = ? WHERE ItemCode = ?");

            $actualizados = 0;
            foreach ($items as $item) {
                $itemCode = trim($item['ItemCode'] ?? '');
                $rawP = str_replace(',', '', (string)($item['Price'] ?? 0));
                $rawC = str_replace(',', '', (string)($item['AvgPrice'] ?? 0));
                $price = is_numeric($rawP) ? (float)$rawP : 0.00;
                $avgPrice = is_numeric($rawC) ? (float)$rawC : 0.00;

                if (!empty($itemCode)) {
                    $stmtCheck->execute([$itemCode, $priceList]);
                    if ($stmtCheck->fetchColumn() > 0) {
                        $stmtUpdP->execute([$price, $itemCode, $priceList]);
                    } else {
                        $stmtInsP->execute([$itemCode, $priceList, $price]);
                    }

                    $stmtUpdC->execute([$avgPrice, $itemCode]);
                    $actualizados++;
                }
            }

            $pdo->commit();
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'msg' => "Se actualizaron {$actualizados} artículos correctamente.",
                'total' => $actualizados
            ]);

        } catch (Throwable $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
        }
    }
}
