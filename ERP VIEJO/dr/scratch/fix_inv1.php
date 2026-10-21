<?php
define('APP_RUNNING', true);
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->getConnection();
$db->exec("UPDATE POSOINV SET PaidToDate = 0, C_DocStatus = 'C', CANCELED = 'Y' WHERE C_DocEntry = 1");
echo "Invoice 1 corrected.\n";
