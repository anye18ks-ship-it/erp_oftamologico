<?php
// Cargamos las cajas activas para el combo del login
require_once __DIR__ . '/../../config/Database.php';

$db  = new Database();
$pdo = $db->getConnection();

$cajas = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT id_caja, alias FROM caja WHERE activo = 'Y' ORDER BY alias");
    if ($stmt) {
        $cajas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Clínica Oftalmológica Dra. Nellie Grace Olivo</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="public/favicon.ico">
    <link rel="apple-touch-icon" href="public/favicon.ico">
    
    <!-- Script de URL Limpia -->
    <script>
    (function() {
        try {
            if (window.history && window.history.replaceState) {
                const urlParams = new URLSearchParams(window.location.search);
                const pageParam = urlParams.get('page');
                if (pageParam === 'login') {
                    urlParams.delete('page');
                    const remaining = urlParams.toString();
                    const pathParts = window.location.pathname.split('/');
                    const lastSeg = pathParts.pop();
                    const basePath = pathParts.join('/') + (lastSeg.toLowerCase() === 'index.php' ? '' : '/' + lastSeg);
                    const cleanUrl = basePath.replace(/\/+$/, '') + '/login' + (remaining ? '?' + remaining : '');
                    window.history.replaceState({ page: 'login' }, document.title, cleanUrl);
                }
            }
        } catch(e) {}
    })();
    </script>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Iconos Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
 
    <!-- Css login -->
    <style>
        <?php include __DIR__ . '/styles/login.css'; ?>
    </style>

</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">
            <!-- Header con Branding Oftalmológico -->
            <div class="login-header">
                <div class="clinic-icon-badge">
                    <i class="bi bi-eye-fill"></i>
                </div>
                <h2 class="clinic-name">DRA. NELLIE GRACE OLIVO MARTE</h2>
                <span class="clinic-specialty">Clínica Oftalmológica Especializada</span>
            </div>

            <!-- Formulario de Acceso -->
            <div class="login-body">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert-custom">
                        <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                        <span>Usuario o contraseña incorrectos.</span>
                    </div>
                <?php endif; ?>

                <form action="index.php?action=login" method="POST" autocomplete="off">
                    <!-- Campo Usuario -->
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario del Sistema</label>
                        <div class="input-group-custom">
                            <i class="bi bi-person-fill input-icon-left"></i>
                            <input type="text" 
                                   class="form-control-custom" 
                                   id="usuario" 
                                   name="usuario"
                                   placeholder="Ingresa tu usuario" 
                                   required 
                                   autofocus>
                        </div>
                    </div>

                    <!-- Campo Contraseña -->
                    <div class="mb-3">
                        <label for="clave" class="form-label">Contraseña</label>
                        <div class="input-group-custom">
                            <i class="bi bi-lock-fill input-icon-left"></i>
                            <input type="password" 
                                   class="form-control-custom pe-5" 
                                   id="clave" 
                                   name="clave"
                                   placeholder="••••••••" 
                                   required>
                            <button type="button" class="toggle-password-btn" id="togglePassword" aria-label="Ver clave">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Combo de Selección de Caja -->
                    <div class="mb-4">
                        <label for="caja_id" class="form-label">Punto de Venta / Caja</label>
                        <div class="input-group-custom">
                            <i class="bi bi-cash-coin input-icon-left"></i>
                            <select class="form-select-custom" id="caja_id" name="caja_id" required>
                                <option value="">-- Selecciona una Caja --</option>
                                <?php foreach ($cajas as $c): ?>
                                    <option value="<?php echo htmlspecialchars($c['id_caja']); ?>">
                                        <?php echo htmlspecialchars($c['alias']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Botón Ingresar -->
                    <button type="submit" class="btn-login">
                        <i class="bi bi-box-arrow-in-right"></i>
                        <span>Ingresar al Sistema</span>
                    </button>
                </form>

                <div class="login-footer">
                    <span>&copy; <?php echo date("Y"); ?> Dra. Nellie Olivo. Todos los derechos reservados.</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para Toggle de Visibilidad de Contraseña -->
    <script>
        const toggleBtn = document.getElementById('togglePassword');
        const passInput = document.getElementById('clave');
        const passIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passInput && passIcon) {
            toggleBtn.addEventListener('click', function () {
                const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passInput.setAttribute('type', type);
                passIcon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
            });
        }
    </script>
</body>
</html>