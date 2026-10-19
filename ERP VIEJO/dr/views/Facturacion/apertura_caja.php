<?php require_once __DIR__ . '/../Estructura/header.php'; ?>
<link rel="stylesheet" href="views/Facturacion/styles/apertura_caja.css">

<div class="caja-wrapper">
    <div class="caja-card">
        <div class="caja-card-header">
            <div class="caja-header-icon">
                <i class="bi bi-safe2"></i>
            </div>
            <h3 class="caja-title">Apertura de Turno de Caja</h3>
            <p class="caja-subtitle">Inicie la jornada registrando el fondo inicial de efectivo</p>
        </div>

        <div class="caja-card-body">
            <div id="alertContainer">
                <?php echo Helper::getMensaje(); ?>
            </div>

            <?php if ($cajaAlias === 'Sin asignar'): ?>
                <div class="alert alert-warning border-0 d-flex align-items-center gap-3 p-3 rounded-3 mb-4">
                    <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
                    <div>
                        <strong class="d-block text-dark">Terminal No Asignada</strong>
                        <small class="text-muted">Este usuario o equipo no tiene una caja asignada en el sistema. Contacte al administrador.</small>
                    </div>
                </div>
            <?php endif; ?>

            <form action="index.php?action=abrir_caja" method="POST" id="formApertura">
                
                <!-- Info Grid -->
                <div class="caja-info-grid">
                    <div class="caja-info-item caja-info-terminal">
                        <div class="caja-info-label"><i class="bi bi-display me-1"></i>Terminal / Caja Asignada</div>
                        <div class="caja-info-value">
                            <span><?php echo htmlspecialchars($cajaAlias); ?></span>
                            <span class="badge bg-primary text-white font-monospace">Caja #<?php echo intval($_SESSION['caja_id'] ?? 0); ?></span>
                        </div>
                    </div>

                    <div class="caja-info-item">
                        <div class="caja-info-label"><i class="bi bi-calendar3 me-1"></i>Fecha</div>
                        <div class="caja-info-value"><?php echo date('d/m/Y', strtotime($fechaActual)); ?></div>
                    </div>

                    <div class="caja-info-item">
                        <div class="caja-info-label"><i class="bi bi-clock me-1"></i>Hora</div>
                        <div class="caja-info-value"><?php echo $horaActual; ?></div>
                    </div>
                </div>

                <!-- Fondo Inicial -->
                <div class="monto-section">
                    <label for="txtMonto" class="monto-label">
                        <i class="bi bi-cash-stack text-primary me-1"></i> Fondo Inicial en Caja (Efectivo)
                    </label>
                    <div class="monto-input-wrapper">
                        <span class="monto-currency-badge">RD$</span>
                        <input type="number" step="0.01" min="0" name="monto" id="txtMonto" 
                               class="monto-input" placeholder="0.00" required autofocus>
                    </div>
                    <div class="monto-helper">
                        <i class="bi bi-info-circle text-muted"></i>
                        <span>Indique el balance base en caja chica para dar cambio</span>
                    </div>
                </div>

                <!-- Botones -->
                <div class="caja-actions">
                    <a href="index.php?page=dashboard" class="btn-caja-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="button" class="btn-caja-primary" id="btnSolicitarApertura" onclick="confirmarApertura()">
                        <i class="bi bi-check2-circle"></i> Abrir Caja
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmación Moderno -->
<div class="modal fade" id="modalConfirmApertura" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-body p-4 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-safe2 fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Confirmar Apertura de Caja</h5>
                <p class="text-muted small mb-3">Se iniciará el turno de caja con el siguiente balance inicial:</p>
                
                <div class="bg-light p-3 rounded-3 border mb-4">
                    <div class="text-muted small fw-semibold">MONTO INICIAL</div>
                    <div class="fs-3 fw-bold text-primary" id="lblMontoConfirm">RD$ 0.00</div>
                    <div class="small text-secondary mt-1">Terminal: <strong><?php echo htmlspecialchars($cajaAlias); ?></strong></div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light flex-fill py-2 fw-semibold" data-bs-dismiss="modal">Corregir</button>
                    <button type="button" class="btn btn-primary flex-fill py-2 fw-bold" onclick="ejecutarEnvioApertura()">
                        <i class="bi bi-check2-circle me-1"></i> Sí, Abrir Caja
                    </button>
                </div>
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

function confirmarApertura() {
    const inputMonto = document.getElementById('txtMonto');
    const valor = parseFloat(inputMonto.value);

    if (isNaN(valor) || valor < 0 || inputMonto.value.trim() === '') {
        inputMonto.classList.add('is-invalid');
        inputMonto.focus();
        return;
    }

    inputMonto.classList.remove('is-invalid');
    document.getElementById('lblMontoConfirm').innerText = 'RD$ ' + valor.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    const modal = new bootstrap.Modal(document.getElementById('modalConfirmApertura'));
    modal.show();
}

function ejecutarEnvioApertura() {
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Aperturando...';
    document.getElementById('formApertura').submit();
}

document.getElementById('txtMonto').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        confirmarApertura();
    }
});
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>