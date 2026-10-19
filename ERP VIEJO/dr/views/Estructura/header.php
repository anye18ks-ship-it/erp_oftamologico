<?php
if (!defined('APP_RUNNING')) {
    http_response_code(403);
    die('Acceso denegado directamente');
}

// Evitar que se acceda al header directamente sin sesión activa
if (!isset($_SESSION['logueado'])) {
    header("Location: index.php?page=login");
    exit();
}

$userName = $_SESSION['nombre'] ?? 'Usuario';
$userRole = $_SESSION['rol'] ?? 'Administrador';
$firstLetter = mb_strtoupper(mb_substr(trim($userName), 0, 1, 'UTF-8'), 'UTF-8');

global $pageTitle, $page, $appRoutes;
$activeTitle = !empty($pageTitle) 
    ? $pageTitle 
    : ($appRoutes[$page]['title'] ?? 'Sistema de Gestión Oftalmológica');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($activeTitle); ?> | Clínica Oftalmológica Dra. Nellie Grace Olivo</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="public/favicon.ico">
    <link rel="apple-touch-icon" href="public/favicon.ico">
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Custom Application CSS -->
    <link rel="stylesheet" href="public/css/app.css">

    <!-- Script de URL Limpia en la barra de navegación del navegador -->
    <script>
    (function() {
        try {
            if (window.history && window.history.replaceState) {
                const urlParams = new URLSearchParams(window.location.search);
                const pageParam = urlParams.get('page');
                if (pageParam) {
                    urlParams.delete('page');
                    const remainingQuery = urlParams.toString();
                    const pathParts = window.location.pathname.split('/');
                    const lastSegment = pathParts.pop();
                    const basePath = pathParts.join('/') + (lastSegment.toLowerCase() === 'index.php' ? '' : '/' + lastSegment);
                    const cleanSlug = encodeURIComponent(pageParam);
                    const cleanUrl = basePath.replace(/\/+$/, '') + '/' + cleanSlug + (remainingQuery ? '?' + remainingQuery : '') + window.location.hash;
                    window.history.replaceState({ page: pageParam }, document.title, cleanUrl);
                }
            }
        } catch(err) {}
    })();
    </script>
</head>
<body>

    <!-- BARRA SUPERIOR (HEADER FIJO POR ENCIMA DEL SIDEBAR) -->
    <header class="app-header">
        <a href="index.php?page=dashboard" class="header-brand">
            <div class="brand-icon">
                <i class="bi bi-eye-fill"></i>
            </div>
            <div class="brand-info">
                <h1 class="brand-title">DRA. NELLIE GRACE OLIVO MARTE</h1>
                <span class="brand-subtitle">Clínica Oftalmológica Especializada</span>
            </div>
        </a>

        <!-- Acciones del Usuario & Dropdown -->
        <div class="header-actions">
            <?php 
            $currentUser = strtolower(trim($_SESSION['usuario'] ?? ''));
            $currentName = strtolower(trim($_SESSION['nombre'] ?? ''));
            if ($currentUser === 'ereyes' || stripos($currentName, 'eduardo reyes') !== false || stripos($currentUser, 'ereyes') !== false): 
            ?>
                <a href="xmlceyes/index.php" target="_blank" class="btn-audit-ecf" title="Auditoría e-CF & Facturación Electrónica DGII (XML/Firmas)">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span class="d-none d-md-inline">Portal e-CF DGII</span>
                    <span class="badge-audit-pulse"></span>
                </a>
            <?php endif; ?>

            <div class="user-profile-wrap" id="userProfileWrap">
                <div class="user-profile-btn" id="userProfileBtn">
                    <div class="user-avatar">
                        <?php echo htmlspecialchars($firstLetter); ?>
                    </div>
                    <div class="user-details d-none d-sm-flex">
                        <span class="user-name"><?php echo htmlspecialchars($userName); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($userRole); ?></span>
                    </div>
                    <span class="user-chevron">
                        <i class="bi bi-chevron-down"></i>
                    </span>
                </div>

                <!-- Dropdown Menú de Usuario -->
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <div class="dropdown-user-header">
                        <span class="full-name"><?php echo htmlspecialchars($userName); ?></span>
                        <span class="account-type"><?php echo htmlspecialchars($userRole); ?></span>
                    </div>
                    <a href="index.php?action=logout" class="dropdown-item-link">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- SIDEBAR SEPARADO EN SU PROPIO ARCHIVO -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- CONTENIDO DINÁMICO PRINCIPAL -->
    <main class="main-content">