<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
date_default_timezone_set('America/Santo_Domingo');

define('APP_RUNNING', true);

// 1. Detección Inteligente de Ruta (Soporte para URL limpia y parámetros estándar)
$page   = $_GET['page']   ?? '';
$action = $_GET['action'] ?? '';

if (empty($page) && empty($action)) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $parsedPath = parse_url($requestUri, PHP_URL_PATH);
    
    // Normalizar subcarpeta base si la aplicación no está en la raíz
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir  = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    
    $cleanPath = $parsedPath;
    if (!empty($scriptDir) && strpos($cleanPath, $scriptDir) === 0) {
        $cleanPath = substr($cleanPath, strlen($scriptDir));
    }
    
    $cleanSlug = trim(str_ireplace('index.php', '', $cleanPath), '/');
    if (!empty($cleanSlug)) {
        $page = $cleanSlug;
    }
}

if (empty($page)) {
    $page = isset($_SESSION['logueado']) ? 'dashboard' : 'login';
}

// 2. Carga y procesamiento de acciones (POST / AJAX)
if (!empty($action)) {
    require_once __DIR__ . '/routes/actions.php';
}

// 3. Control de autenticación para páginas protegidas
if ($page !== 'login' && !isset($_SESSION['logueado'])) {
    header("Location: index.php?page=login");
    exit();
}

// 4. Carga y despacho de la vista/controlador
require_once __DIR__ . '/routes/web.php';
?>