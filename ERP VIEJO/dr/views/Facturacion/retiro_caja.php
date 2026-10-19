<?php require_once __DIR__ . '/../Estructura/header.php'; ?>
<link rel="stylesheet" href="views/Facturacion/styles/retiro_caja.css">

<div class="container-fluid retiro-container">
    
    <!-- Header del Módulo -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <h2 class="page-title">Retiro de Efectivo / Caja</h2>
                <p class="page-subtitle">Registre salidas de dinero y transferencias desde la terminal activa</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?page=consulta_retiro" class="btn-header-action btn-header-history">
                <i class="bi bi-clock-history"></i> Historial de Retiros
            </a>
            <a href="index.php?page=dashboard" class="btn-header-action btn-header-home">
                <i class="bi bi-house"></i> Inicio
            </a>
        </div>
    </div>

    <div id="alertContainer">
        <?php echo Helper::getMensaje(); ?>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="retiro-main-card">
                <div class="retiro-card-header">
                    <div class="retiro-card-header-title">
                        <i class="bi bi-dash-circle-fill text-danger"></i> Formulario de Retiro de Caja
                    </div>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 fw-bold font-monospace">
                        Turno Z-<?php echo str_pad($_SESSION['caja_apertura_id'] ?? 0, 7, '0', STR_PAD_LEFT); ?>
                    </span>
                </div>

                <div class="retiro-card-body">
                    <!-- Banner Informativo -->
                    <div class="retiro-info-banner">
                        <div class="info-banner-item">
                            <span class="info-banner-label"><i class="bi bi-calendar3"></i> Fecha Operación</span>
                            <span class="info-banner-val"><?php echo $fechaActual; ?></span>
                        </div>
                        <div class="info-banner-item">
                            <span class="info-banner-label"><i class="bi bi-clock"></i> Hora Operación</span>
                            <span class="info-banner-val"><?php echo $horaActual; ?></span>
                        </div>
                        <div class="info-banner-item">
                            <span class="info-banner-label"><i class="bi bi-display"></i> Terminal / Caja</span>
                            <span class="info-banner-val text-primary"><?php echo htmlspecialchars($alias ?? 'Sin asignar'); ?></span>
                        </div>
                        <div class="info-banner-item">
                            <span class="info-banner-label"><i class="bi bi-person-badge"></i> Quien Entrega</span>
                            <span class="info-banner-val"><?php echo htmlspecialchars($_SESSION['nombre'] ?? ''); ?></span>
                        </div>
                    </div>

                    <form action="index.php?action=guardar_retiro" method="POST" id="formRetiro">
                        
                        <!-- FILA 1: Medio, Concepto, Referencia -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label-custom"><i class="bi bi-credit-card-2-front"></i> Medio de Retiro</label>
                                <select name="id_medio_retiro" id="cboMedioRetiro" class="form-select input-custom" required>
                                    <?php foreach ($medios as $m): ?>
                                        <option value="<?php echo $m['id_medio_retiro']; ?>"><?php echo htmlspecialchars($m['descripcion']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom"><i class="bi bi-tag"></i> Concepto</label>
                                <select name="id_concepto_retiro" id="cboConcepto" class="form-select input-custom" required>
                                    <?php foreach ($conceptos as $c): ?>
                                        <option value="<?php echo $c['id_concepto_retiro']; ?>"><?php echo htmlspecialchars($c['descripcion']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label-custom"><i class="bi bi-hash"></i> Referencia / N° Recibo</label>
                                <input type="text" name="referencia" id="txtReferencia" class="form-control input-custom" placeholder="Opcional">
                            </div>
                        </div>

                        <!-- FILA 2: Quien Recibe, Monto a Retirar -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="bi bi-person-check"></i> Persona que Recibe <span class="text-danger">*</span></label>
                                <input type="text" name="quien_recibe" id="txtQuienRecibe" class="form-control input-custom" placeholder="Nombre completo de quien recibe el dinero" required>
                                <div class="invalid-feedback">Indique el nombre de la persona que recibe el retiro.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-custom"><i class="bi bi-cash-stack"></i> Monto a Retirar (RD$) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold text-danger">RD$</span>
                                    <input type="number" step="0.01" min="0.01" name="monto" id="txtMonto" 
                                           class="form-control input-custom monto-retiro-input" placeholder="0.00" required>
                                </div>
                                <div class="invalid-feedback">El monto a retirar debe ser mayor a 0.00.</div>
                            </div>
                        </div>

                        <!-- PANEL DENOMINACIONES (Dinámico con Imágenes) -->
                        <div class="denominaciones-panel-retiro d-none" id="grpDenominaciones">
                            <div class="denominaciones-panel-header">
                                <span><i class="bi bi-cash-coin text-success me-1"></i> Desglose de Billetes y Monedas <small class="text-muted fw-normal ms-1">(Opcional: Desglose o digite el monto directo)</small></span>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="limpiarDesgloseManual()" style="font-size: 0.76rem;" title="Limpiar desglose para digitar monto manual">
                                        <i class="bi bi-eraser me-1"></i>Limpiar
                                    </button>
                                    <span class="badge bg-primary-subtle text-primary font-monospace" id="lblDenomTotalRetiro">Total: RD$ 0.00</span>
                                </div>
                            </div>
                            
                            <?php 
                            $denominacionesRetiro = [
                                ['val' => 2000, 'img' => 'public/img/2000.jpg'],
                                ['val' => 1000, 'img' => 'public/img/1000.jpg'],
                                ['val' => 500,  'img' => 'public/img/500.jpg'],
                                ['val' => 200,  'img' => 'public/img/200.jpg'],
                                ['val' => 100,  'img' => 'public/img/100.jpg'],
                                ['val' => 50,   'img' => 'public/img/50.jpg'],
                                ['val' => 25,   'img' => 'public/img/25.png'],
                                ['val' => 10,   'img' => 'public/img/10.png'],
                                ['val' => 5,    'img' => 'public/img/5.png'],
                                ['val' => 1,    'img' => 'public/img/1.png']
                            ];
                            ?>

                            <div class="denominaciones-grid-retiro">
                                <?php foreach ($denominacionesRetiro as $d): ?>
                                <div class="denom-retiro-card">
                                    <div class="denom-retiro-img-wrap">
                                        <img src="<?php echo $d['img']; ?>" alt="RD$ <?php echo $d['val']; ?>" class="denom-retiro-img" loading="lazy">
                                    </div>
                                    <div class="denom-retiro-info">
                                        <span class="denom-badge-sm">RD$ <?php echo number_format($d['val']); ?></span>
                                        <span class="denom-subtotal-sm" id="sub_ret_<?php echo $d['val']; ?>">RD$ 0.00</span>
                                    </div>
                                    <div class="denom-retiro-input-group">
                                        <button type="button" class="denom-step-btn" onclick="ajustarDenomRetiro(<?php echo $d['val']; ?>, -1)">-</button>
                                        <input type="number" min="0" name="mon_<?php echo $d['val']; ?>" id="mon_ret_<?php echo $d['val']; ?>" 
                                               class="denom-input-sm denominacion-ret" data-val="<?php echo $d['val']; ?>" placeholder="0">
                                        <button type="button" class="denom-step-btn" onclick="ajustarDenomRetiro(<?php echo $d['val']; ?>, 1)">+</button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- COMENTARIO -->
                        <div class="mb-3">
                            <label class="form-label-custom"><i class="bi bi-chat-left-text"></i> Motivo / Justificación del Retiro <span class="text-danger">*</span></label>
                            <textarea name="comentario" id="txtComentario" class="form-control input-custom" rows="2" 
                                      placeholder="Indique la justificación del retiro de efectivo" required></textarea>
                            <div class="invalid-feedback">Debe escribir una justificación para el retiro.</div>
                        </div>

                        <!-- BOTONES -->
                        <div class="retiro-footer-actions">
                            <a href="index.php?page=dashboard" class="btn-action-cancel">
                                <i class="bi bi-x-circle me-1"></i> Cancelar
                            </a>
                            <button type="button" class="btn-action-submit-danger" id="btnValidarRetiro" onclick="confirmarRetiroModal()">
                                <i class="bi bi-check2-circle me-1"></i> Procesar Retiro
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación Moderno -->
<div class="modal fade" id="modalConfirmRetiro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-body p-4 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-wallet2 fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Confirmar Retiro de Caja</h5>
                <p class="text-muted small mb-3">Se registrará una salida de dinero en esta terminal:</p>
                
                <div class="bg-light p-3 rounded-3 border mb-3 text-start">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Monto Total a Retirar:</span>
                        <strong class="text-danger fs-5 font-monospace" id="lblMontoConfirmRetiro">RD$ 0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Quien Entrega:</span>
                        <span class="small fw-semibold"><?php echo htmlspecialchars($_SESSION['nombre'] ?? ''); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Persona que Recibe:</span>
                        <span class="small fw-semibold" id="lblRecibeConfirm"></span>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light flex-fill py-2 fw-semibold" data-bs-dismiss="modal">Revisar</button>
                    <button type="button" class="btn btn-danger flex-fill py-2 fw-bold" id="btnEjecutarRetiro" onclick="ejecutarEnvioRetiro()">
                        <i class="bi bi-check2-circle me-1"></i> Sí, Realizar Retiro
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

function evaluarDenominaciones() {
    let medio = document.getElementById('cboMedioRetiro').value;
    let panel = document.getElementById('grpDenominaciones');
    let txtMonto = document.getElementById('txtMonto');
    let txtRef = document.getElementById('txtReferencia');

    // Medio 1 = Efectivo (activo opcionalmente para cualquier concepto: Depósito, Proveedor, Vale, Otro)
    // Medio 2 = Cheque (desactivado desglose de billetes, solicita N° cheque en referencia)
    if (medio == 1) {
        panel.classList.remove('d-none');
        if (txtRef) txtRef.placeholder = 'Opcional';
        
        let totalDenom = 0;
        document.querySelectorAll('.denominacion-ret').forEach(inp => {
            let cant = parseInt(inp.value) || 0;
            let val = parseInt(inp.getAttribute('data-val')) || 0;
            totalDenom += (cant * val);
        });

        if (totalDenom > 0) {
            txtMonto.value = totalDenom.toFixed(2);
            txtMonto.readOnly = true;
            txtMonto.classList.add('input-custom-readonly');
        } else {
            txtMonto.readOnly = false;
            txtMonto.classList.remove('input-custom-readonly');
        }
    } else {
        // Medio Cheque u otros
        panel.classList.add('d-none');
        limpiarDenominacionesRetiro();
        if (txtRef) txtRef.placeholder = 'Ej: Cheque #00124';
        txtMonto.readOnly = false;
        txtMonto.classList.remove('input-custom-readonly');
    }
}

function limpiarDenominacionesRetiro() {
    document.querySelectorAll('.denominacion-ret').forEach(inp => {
        inp.value = '';
        let val = inp.getAttribute('data-val');
        let subElem = document.getElementById('sub_ret_' + val);
        if (subElem) subElem.innerText = 'RD$ 0.00';
    });
    document.getElementById('lblDenomTotalRetiro').innerText = 'Total: RD$ 0.00';
}

function limpiarDesgloseManual() {
    limpiarDenominacionesRetiro();
    let txtMonto = document.getElementById('txtMonto');
    txtMonto.value = '';
    txtMonto.readOnly = false;
    txtMonto.classList.remove('input-custom-readonly');
    txtMonto.focus();
}

document.getElementById('cboConcepto').addEventListener('change', evaluarDenominaciones);
document.getElementById('cboMedioRetiro').addEventListener('change', evaluarDenominaciones);

function ajustarDenomRetiro(val, delta) {
    const input = document.getElementById('mon_ret_' + val);
    let cant = parseInt(input.value) || 0;
    cant = Math.max(0, cant + delta);
    input.value = cant > 0 ? cant : '';
    calcularDenominacionesRetiro();
}

function calcularDenominacionesRetiro() {
    let total = 0;
    document.querySelectorAll('.denominacion-ret').forEach(inp => {
        let cant = parseInt(inp.value) || 0;
        let val = parseInt(inp.getAttribute('data-val')) || 0;
        let sub = cant * val;
        total += sub;
        let subElem = document.getElementById('sub_ret_' + val);
        if (subElem) {
            subElem.innerText = 'RD$ ' + sub.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    });

    let txtMonto = document.getElementById('txtMonto');
    if (total > 0) {
        txtMonto.value = total.toFixed(2);
        txtMonto.readOnly = true;
        txtMonto.classList.add('input-custom-readonly');
    } else {
        txtMonto.readOnly = false;
        txtMonto.classList.remove('input-custom-readonly');
    }

    document.getElementById('lblDenomTotalRetiro').innerText = 'Total: RD$ ' + total.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

document.querySelectorAll('.denominacion-ret').forEach(input => {
    input.addEventListener('input', calcularDenominacionesRetiro);
});

// Validación y Modal de Confirmación
function confirmarRetiroModal() {
    const txtMonto = document.getElementById('txtMonto');
    const txtRecibe = document.getElementById('txtQuienRecibe');
    const txtComentario = document.getElementById('txtComentario');
    
    let valid = true;

    if (!txtRecibe.value.trim()) {
        txtRecibe.classList.add('is-invalid');
        txtRecibe.focus();
        valid = false;
    } else {
        txtRecibe.classList.remove('is-invalid');
    }

    let monto = parseFloat(txtMonto.value) || 0;
    if (monto <= 0) {
        txtMonto.classList.add('is-invalid');
        if (valid) txtMonto.focus();
        valid = false;
    } else {
        txtMonto.classList.remove('is-invalid');
    }

    if (!txtComentario.value.trim()) {
        txtComentario.classList.add('is-invalid');
        if (valid) txtComentario.focus();
        valid = false;
    } else {
        txtComentario.classList.remove('is-invalid');
    }

    if (!valid) return;

    document.getElementById('lblMontoConfirmRetiro').innerText = 'RD$ ' + monto.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('lblRecibeConfirm').innerText = txtRecibe.value.trim();

    new bootstrap.Modal(document.getElementById('modalConfirmRetiro')).show();
}

function ejecutarEnvioRetiro() {
    const btn = document.getElementById('btnEjecutarRetiro');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Procesando...';
    document.getElementById('formRetiro').submit();
}

// Inicializar estado
evaluarDenominaciones();
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>