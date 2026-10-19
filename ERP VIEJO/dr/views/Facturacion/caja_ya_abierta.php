<?php require_once __DIR__ . '/../Estructura/header.php'; ?>
<link rel="stylesheet" href="views/Facturacion/styles/caja_ya_abierta.css">

<div class="caja-status-wrapper">
    <div class="caja-status-card">
        <div class="caja-status-header">
            <div class="caja-status-icon">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h3 class="caja-status-title">Turno de Caja Activo</h3>
            <p class="caja-subtitle">Esta terminal ya cuenta con un turno aperturado en curso</p>
        </div>

        <div class="caja-status-body">
            <div id="alertContainer">
                <?php echo Helper::getMensaje(); ?>
            </div>

            <div class="shift-info-box">
                <div class="shift-row">
                    <span class="shift-label"><i class="bi bi-receipt-cutoff"></i> Turno Apertura:</span>
                    <span class="shift-value font-monospace text-primary fs-6">Z-<?php echo htmlspecialchars($data['num_z']); ?></span>
                </div>
                <div class="shift-row">
                    <span class="shift-label"><i class="bi bi-calendar-check"></i> Fecha / Hora:</span>
                    <span class="shift-value"><?php echo htmlspecialchars($data['fecha'] . ' • ' . $data['hora']); ?></span>
                </div>
                <div class="shift-row">
                    <span class="shift-label"><i class="bi bi-display"></i> Terminal:</span>
                    <span class="shift-value font-monospace">Caja #<?php echo intval($_SESSION['caja_id'] ?? 0); ?></span>
                </div>
                <div class="shift-row">
                    <span class="shift-label"><i class="bi bi-activity"></i> Estado Operativo:</span>
                    <span class="badge-active-shift">EN OPERACIÓN</span>
                </div>
            </div>

            <div class="shift-actions">
                <div class="shift-actions-top">
                    <a href="index.php?page=dashboard" class="btn-shift btn-shift-dashboard">
                        <i class="bi bi-house"></i> Inicio
                    </a>
                    <a href="index.php?page=facturacion" class="btn-shift btn-shift-pos">
                        <i class="bi bi-receipt"></i> Ir a Facturación
                    </a>
                </div>
                <a href="index.php?page=cierre_caja" class="btn-shift btn-shift-close">
                    <i class="bi bi-lock-fill"></i> Proceder al Cierre de Caja (Corte Z)
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-dismiss de mensajes de alerta tras 5 segundos
setTimeout(function() {
    const alerts = document.querySelectorAll('#alertContainer .alert');
    alerts.forEach(function(el) {
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-8px)';
        setTimeout(() => el.remove(), 600);
    });
}, 5000);
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>