<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class EntradaMercanciaController {

    public function index() {
        $database = new Database();
        $pdo = $database->getConnection();

        // 1. Proveedores y Suplidores dinámicos desde posOCRD y posOCRG
        $sqlProveedores = "SELECT c.CardCode, c.CardName, c.U_NIT, c.Phone1, ISNULL(g.GroupName, '') AS GroupName
                           FROM posOCRD c
                           LEFT JOIN posOCRG g ON c.GroupCode = g.GroupCode
                           WHERE (g.GroupType = 'S' OR c.CardType = 'P')
                             AND ISNULL(c.QryGroup1, 'N') <> 'Y'
                             AND ISNULL(g.GroupName, '') NOT LIKE '%ASEGURADORA%'
                             AND ISNULL(g.GroupName, '') NOT LIKE '%SEGURO%'
                           ORDER BY c.CardName ASC";
        $stmtProv = $pdo->prepare($sqlProveedores);
        $stmtProv->execute();
        $proveedores = $stmtProv->fetchAll(PDO::FETCH_ASSOC);

        // 2. Almacenes disponibles (posOWHS)
        $sqlAlmacenes = "SELECT WhsCode, WhsName FROM posOWHS ORDER BY WhsName ASC";
        $stmtAlm = $pdo->prepare($sqlAlmacenes);
        $stmtAlm->execute();
        $almacenes = $stmtAlm->fetchAll(PDO::FETCH_ASSOC);

        // 3. Historial de Entradas Recientes (POSOIGN)
        $sqlEntradas = "SELECT TOP 50 
                            o.C_DocEntry, o.DocEntry, 
                            ISNULL(o.DocNum, o.DocEntry) AS DocNum, 
                            ISNULL(FORMAT(o.DocDate, 'dd/MM/yyyy'), '') AS FechaFmt,
                            o.DocDate, 
                            ISNULL(o.CardCode, '') AS CardCode, 
                            ISNULL(o.CardName, '') AS CardName, 
                            ISNULL(o.NumAtCard, '') AS NumAtCard, 
                            ISNULL(o.DocTotal, 0) AS DocTotal, 
                            ISNULL(o.Comments, '') AS Comments, 
                            ISNULL(o.U_user, '') AS U_user,
                            (SELECT COUNT(*) FROM POSIGN1 l WHERE l.C_DocEntry = o.C_DocEntry) AS TotalLineas
                        FROM POSOIGN o 
                        ORDER BY o.C_DocEntry DESC";
        $stmtEntradas = $pdo->prepare($sqlEntradas);
        $stmtEntradas->execute();
        $entradasRecientes = $stmtEntradas->fetchAll(PDO::FETCH_ASSOC);

        // 4. Grupos de artículos (posOITB)
        $stmtGrupos = $pdo->query("SELECT ItmsGrpCod, ItmsGrpNam FROM posOITB ORDER BY ItmsGrpNam ASC");
        $gruposArticulos = $stmtGrupos ? $stmtGrupos->fetchAll(PDO::FETCH_ASSOC) : [];

        include __DIR__ . '/../views/Articulos/entrada_mercancia.php';
    }

    // AJAX: Buscar Artículos aptos para compra / recepción (PrchseItem = 'Y')
    public function buscarArticulo() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            $term = trim($_GET['q'] ?? '');

            $sql = "SELECT 
                        a.ItemCode, a.ItemName, a.CodeBars, a.AvgPrice, a.OnHand, 
                        a.DfltWH, a.TaxCodeAr, a.ItmsGrpCod, g.ItmsGrpNam,
                        a.PrchseItem, a.SellItem, a.InvntItem, a.QryGroup1, a.QryGroup2
                    FROM posOITM a
                    LEFT JOIN posOITB g ON a.ItmsGrpCod = g.ItmsGrpCod
                    WHERE a.PrchseItem = 'Y'
                    AND (a.ItemName LIKE ? OR a.ItemCode LIKE ? OR a.CodeBars LIKE ? OR g.ItmsGrpNam LIKE ?)
                    ORDER BY a.ItemName ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(["%$term%", "%$term%", "%$term%", "%$term%"]);
            $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            ob_end_clean();
            echo json_encode($articulos);
        } catch (Throwable $e) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['error' => true, 'message' => $e->getMessage()]);
        }
    }

    // AJAX: Guardar Entrada de Mercancía Maestro-Detalle y Actualizar Inventario
    public function guardar() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');

        $database = new Database();
        $pdo = $database->getConnection();
        $pdo->beginTransaction();

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) {
                throw new Exception("Datos de entrada inválidos.");
            }

            $header = $input['header'] ?? [];
            $lines = $input['lines'] ?? [];

            if (empty($lines)) {
                throw new Exception("Debe agregar al menos un artículo para registrar la entrada.");
            }

            $usuario = substr($_SESSION['usuario'] ?? ($_SESSION['nombre'] ?? 'ADMIN'), 0, 20);
            $terminal = substr($_SERVER['COMPUTERNAME'] ?? 'WEB', 0, 20);

            $cardCode = substr(trim($header['CardCode'] ?? ''), 0, 15);
            $cardName = substr(trim($header['CardName'] ?? 'ENTRADA DE MERCANCIA'), 0, 100);
            $numAtCard = substr(trim($header['NumAtCard'] ?? ''), 0, 100); // N° Factura / Conduce del proveedor
            $docDate = !empty($header['DocDate']) ? trim($header['DocDate']) : date('Y-m-d');
            $comments = substr(trim($header['Comments'] ?? 'Entrada de mercancía / recepción de stock'), 0, 254);
            $whsGeneral = substr(trim($header['WhsCode'] ?? ''), 0, 20);

            // Almacén fallback
            if (empty($whsGeneral)) {
                $stmtFirstW = $pdo->query("SELECT TOP 1 WhsCode FROM posOWHS ORDER BY WhsCode ASC");
                $whsGeneral = $stmtFirstW ? $stmtFirstW->fetchColumn() : '01';
            }

            // 1. Calcular total del documento
            $docTotal = 0;
            foreach ($lines as $line) {
                $cant = floatval($line['Quantity'] ?? 0);
                $costo = floatval($line['Price'] ?? 0);
                if ($cant <= 0) {
                    throw new Exception("La cantidad de cada artículo debe ser mayor que 0.");
                }
                $docTotal += ($cant * $costo);
            }

            // 2. Correlativo DocEntry y DocNum en POSOIGN
            $nextDocEntry = (int)$pdo->query("SELECT ISNULL(MAX(DocEntry), 0) + 1 FROM POSOIGN")->fetchColumn();
            $nextDocNum   = (int)$pdo->query("SELECT ISNULL(MAX(DocNum), 0) + 1 FROM POSOIGN")->fetchColumn();

            // 3. Insertar Cabecera POSOIGN
            $sqlHeader = "INSERT INTO POSOIGN (
                            C_Posted, DocEntry, DocNum, DocType, CANCELED, Handwrtten, Printed,
                            DocStatus, InvntSttus, Transfered, ObjType,
                            DocDate, DocDueDate, CardCode, CardName, Address,
                            NumAtCard, VatPercent, VatSum, DiscSum, DiscPrcnt,
                            DocCur, DocRate, DocTotal, DocTotalFC,
                            Comments, JrnlMemo, GrossBase,
                            DataSource, UserSign, UserSign2,
                            U_user, U_terminal
                          ) VALUES (
                            'Y', ?, ?, 'I', 'N', 'N', 'N',
                            'C', 'O', 'N', '59',
                            ?, ?, ?, ?, '',
                            ?, 0, 0, 0, 0,
                            'DOP', 1, ?, ?,
                            ?, 'Entrada de Mercancía', 0,
                            'O', 1, 1,
                            ?, ?
                          )";

            $stmtH = $pdo->prepare($sqlHeader);
            $stmtH->execute([
                $nextDocEntry, $nextDocNum,
                $docDate, $docDate, $cardCode, $cardName,
                $numAtCard, $docTotal, $docTotal,
                $comments,
                $usuario, $terminal
            ]);

            $cDocEntry = (int)$pdo->lastInsertId();
            if (!$cDocEntry) {
                $cDocEntry = (int)$pdo->query("SELECT ISNULL(MAX(C_DocEntry), 0) FROM POSOIGN")->fetchColumn();
            }

            // 4. Preparar Sentencias para Detalle e Inventario
            $sqlLine = "INSERT INTO POSIGN1 (
                            C_DocEntry, DocEntry, LineNum, LineStatus,
                            ItemCode, Dscription, Quantity, OpenQty,
                            Price, Currency, Rate, DiscPrcnt, LineTotal,
                            WhsCode, ObjType, DocDate
                        ) VALUES (
                            ?, ?, ?, 'C',
                            ?, ?, ?, ?,
                            ?, 'DOP', 1, 0, ?,
                            ?, '59', ?
                        )";
            $stmtL = $pdo->prepare($sqlLine);

            $stmtItemActual = $pdo->prepare("SELECT OnHand, AvgPrice, InvntItem, DfltWH FROM posOITM WHERE ItemCode = ?");
            $stmtUpdOITM    = $pdo->prepare("UPDATE posOITM SET OnHand = ISNULL(OnHand, 0) + ?, AvgPrice = ? WHERE ItemCode = ?");
            $stmtCheckOITW  = $pdo->prepare("SELECT COUNT(*) FROM posOITW WHERE ItemCode = ? AND WhsCode = ?");
            $stmtUpdOITW    = $pdo->prepare("UPDATE posOITW SET OnHand = ISNULL(OnHand, 0) + ? WHERE ItemCode = ? AND WhsCode = ?");
            $stmtInsOITW    = $pdo->prepare("INSERT INTO posOITW (ItemCode, WhsCode, OnHand) VALUES (?, ?, ?)");

            // 5. Procesar cada línea
            foreach ($lines as $i => $line) {
                $itemCode = substr(trim($line['ItemCode'] ?? ''), 0, 50);
                $dscription = substr(trim($line['ItemName'] ?? ''), 0, 100);
                $cant = floatval($line['Quantity'] ?? 0);
                $costoUnitario = floatval($line['Price'] ?? 0);
                $lineTotal = $cant * $costoUnitario;
                $whsLine = !empty($line['WhsCode']) ? substr(trim($line['WhsCode']), 0, 20) : $whsGeneral;

                $stmtL->execute([
                    $cDocEntry, $nextDocEntry, intval($i),
                    $itemCode, $dscription, $cant, $cant,
                    $costoUnitario, $lineTotal,
                    $whsLine, $docDate
                ]);

                // Actualizar Maestro de Artículos (posOITM)
                $stmtItemActual->execute([$itemCode]);
                $itemActual = $stmtItemActual->fetch(PDO::FETCH_ASSOC);

                if ($itemActual) {
                    $stockAnterior = floatval($itemActual['OnHand'] ?? 0);
                    $costoAnterior = floatval($itemActual['AvgPrice'] ?? 0);

                    // Cálculo del Costo Promedio Ponderado (Weighted Average Cost)
                    $nuevoCosto = $costoUnitario;
                    if ($stockAnterior > 0 && ($stockAnterior + $cant) > 0) {
                        $nuevoCosto = (($stockAnterior * $costoAnterior) + ($cant * $costoUnitario)) / ($stockAnterior + $cant);
                    } elseif ($stockAnterior <= 0) {
                        $nuevoCosto = $costoUnitario;
                    }
                    $nuevoCosto = round($nuevoCosto, 4);

                    // Actualizar posOITM
                    $stmtUpdOITM->execute([$cant, $nuevoCosto, $itemCode]);

                    // Actualizar Stock en Almacén posOITW
                    $stmtCheckOITW->execute([$itemCode, $whsLine]);
                    if ($stmtCheckOITW->fetchColumn() > 0) {
                        $stmtUpdOITW->execute([$cant, $itemCode, $whsLine]);
                    } else {
                        $stmtInsOITW->execute([$itemCode, $whsLine, $cant]);
                    }
                }
            }

            $pdo->commit();
            ob_end_clean();
            echo json_encode([
                'success' => true,
                'msg' => "Entrada de mercancía registrada exitosamente (#{$nextDocNum}).",
                'docNum' => $nextDocNum,
                'docEntry' => $cDocEntry
            ]);

        } catch (Exception $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            ob_end_clean();
            http_response_code(400);
            echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
        } catch (Throwable $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'msg' => 'Error interno: ' . $e->getMessage()]);
        }
    }

    // AJAX: Consultar Detalle de Entrada para Visualización
    public function obtenerDetalle() {
        ob_start();
        header('Content-Type: application/json; charset=utf-8');
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            $id = intval($_GET['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception("ID de entrada no proporcionado.");
            }

            $sqlH = "SELECT o.C_DocEntry, o.DocNum, FORMAT(o.DocDate, 'dd/MM/yyyy') AS FechaFmt,
                            o.CardCode, o.CardName, o.NumAtCard, o.DocTotal, o.Comments, o.U_user,
                            prov.U_NIT, prov.Phone1
                     FROM POSOIGN o
                     LEFT JOIN posOCRD prov ON o.CardCode = prov.CardCode
                     WHERE o.C_DocEntry = ?";
            $stmtH = $pdo->prepare($sqlH);
            $stmtH->execute([$id]);
            $cabecera = $stmtH->fetch(PDO::FETCH_ASSOC);

            if (!$cabecera) {
                throw new Exception("No se encontró el documento de entrada.");
            }

            $sqlL = "SELECT l.LineNum, l.ItemCode, l.Dscription, l.Quantity, l.Price, l.LineTotal, l.WhsCode,
                            w.WhsName
                     FROM POSIGN1 l
                     LEFT JOIN posOWHS w ON l.WhsCode = w.WhsCode
                     WHERE l.C_DocEntry = ?
                     ORDER BY l.LineNum ASC";
            $stmtL = $pdo->prepare($sqlL);
            $stmtL->execute([$id]);
            $lineas = $stmtL->fetchAll(PDO::FETCH_ASSOC);

            ob_end_clean();
            echo json_encode(['success' => true, 'header' => $cabecera, 'lines' => $lineas]);
        } catch (Throwable $e) {
            ob_end_clean();
            http_response_code(500);
            echo json_encode(['success' => false, 'msg' => $e->getMessage()]);
        }
    }
}
