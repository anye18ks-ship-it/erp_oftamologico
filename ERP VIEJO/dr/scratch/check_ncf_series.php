<?php
define('APP_RUNNING', true);
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->getConnection();

$stmt = $db->query("SELECT TOP 1 * FROM TIPOS_ANULACION");
$r = $stmt->fetch(PDO::FETCH_ASSOC);
print_r(array_keys($r));
print_r($r);
