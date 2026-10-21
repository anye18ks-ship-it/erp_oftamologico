<?php
define('APP_RUNNING', true);
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->getConnection();

echo "--- diario_caja_det ---\n";
$stmt = $db->query("SELECT * FROM diario_caja_det");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    print_r($r);
}
