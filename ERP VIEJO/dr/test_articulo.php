<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
define('APP_RUNNING', true);
require_once __DIR__ . '/config/Database.php';

echo "<pre>";
echo "=== DIAGNOSTICO buscarArticulo ===\n\n";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    echo "Conexion OK\n\n";
    
    echo "--- v_sale_item ---\n";
    try {
        $check = $pdo->query("SELECT TOP 1 * FROM v_sale_item");
        $row = $check->fetch(PDO::FETCH_ASSOC);
        if ($row) { echo "Vista existe. Columnas: " . implode(', ', array_keys($row)) . "\n\n"; }
        else { echo "Vista existe pero vacia\n\n"; }
    } catch (Exception $e) {
        echo "ERROR v_sale_item: " . $e->getMessage() . "\n\n";
    }

    echo "--- Query exacta (PriceList=1) ---\n";
    try {
        $sql = "SELECT TOP 5 DISTINCT ItemCode, ItemName, PriceBefDi, TaxCode, VatPrcnt, DfltWH, QryGroup1 FROM v_sale_item WHERE PriceList = 1 AND (ItemName LIKE '%' OR ItemCode LIKE '%') AND SellItem = 'Y' ORDER BY ItemName ASC";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Registros: " . count($rows) . "\n";
        if (count($rows) > 0) { echo "Primero: " . json_encode($rows[0]) . "\n"; }
    } catch (Exception $e) {
        echo "ERROR query: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "ERROR conexion: " . $e->getMessage() . "\n";
}
echo "</pre>";
?>
