<?php require_once __DIR__ . '/../Estructura/header.php'; ?>
<link rel="stylesheet" href="views/Facturacion/styles/cierre_caja.css">

<script>
    function formatCurrency(value) {
        return new Intl.NumberFormat('es-DO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(value);
    }
</script>

<div class="container-fluid cierre-container">

    <!-- Header del Módulo -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-lock-fill"></i>
            </div>
            <div>
                <h2 class="page-title">Cierre de Caja & Arqueo (Corte Z)</h2>
                <p class="page-subtitle">Cuadre de valores monetarios, arqueo ciego y liquidación del turno operativo</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?page=consulta_cierre" class="btn-header-action btn-header-history">
                <i class="bi bi-journal-text"></i> Historial de Cierres
            </a>
            <a href="index.php?page=dashboard" class="btn-header-action btn-header-home">
                <i class="bi bi-house"></i> Inicio
            </a>
        </div>
    </div>

    <div id="alertContainer">
        <?php echo Helper::getMensaje(); ?>
    </div>

    <form action="index.php?action=cerrar_caja" method="POST" id="formCierre">
        <input type="hidden" name="id_diario_caja" id="id_diario_caja" value="<?php echo $data['apertura']['id_diario_caja'] ?? 0; ?>">

        <!-- Tarjetas de Resumen de Turno -->
        <div class="shift-summary-grid">
            
            <!-- Datos de Apertura -->
            <div class="summary-card">
                <div class="summary-card-header apertura">
                    <span><i class="bi bi-box-arrow-in-right me-1"></i> Datos de Apertura</span>
                    <span class="badge bg-success font-monospace">Z-<?php echo htmlspecialchars($data['apertura']['num_z'] ?? '0000000'); ?></span>
                </div>
                <div class="summary-card-body">
                    <div class="summary-row">
                        <span class="summary-row-label">Terminal / Caja:</span>
                        <span class="summary-row-value text-primary"><?php echo htmlspecialchars($data['apertura']['alias'] ?? 'Sin asignar'); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-row-label">Fecha / Hora Apertura:</span>
                        <span class="summary-row-value"><?php echo ($data['apertura']['fecha_apertura'] ?? '') . ' • ' . ($data['apertura']['hora_apertura'] ?? ''); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-row-label">Cajero Apertura:</span>
                        <span class="summary-row-value"><?php echo htmlspecialchars($data['apertura']['usuario'] ?? ''); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-row-label">Fondo Inicial:</span>
                        <span class="summary-row-value text-success fs-6 font-monospace">RD$ <?php echo number_format($data['totales']['deposito'] ?? 0, 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Datos de Cierre -->
            <div class="summary-card">
                <div class="summary-card-header cierre">
                    <span><i class="bi bi-shield-check me-1"></i> Datos de Liquidación</span>
                    <span class="badge bg-warning text-dark">EN PROCESO DE CIERRE</span>
                </div>
                <div class="summary-card-body">
                    <div class="summary-row">
                        <span class="summary-row-label">Fecha / Hora Cierre:</span>
                        <span class="summary-row-value"><?php echo ($data['fecha_cierre'] ?? date('d/m/Y')) . ' • ' . ($data['hora_cierre'] ?? date('g:i A')); ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-row-label">Cajero de Cierre:</span>
                        <span class="summary-row-value"><?php echo htmlspecialchars($_SESSION['nombre'] ?? '') . ' (' . htmlspecialchars($_SESSION['usuario'] ?? '') . ')'; ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-row-label">Retiros Registrados:</span>
                        <span class="summary-row-value text-danger fs-6 font-monospace">
                            RD$ <?php echo number_format($data['totales']['retiro'] ?? 0, 2); ?>
                            <small class="text-muted fw-normal">(<?php echo intval($data['totales']['cant_retiro'] ?? 0); ?>)</small>
                        </span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-row-label">Estado de Cierre:</span>
                        <span class="badge bg-secondary-subtle text-secondary">Pendiente de Aceptación</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Fila Principal: Denominaciones vs Registros y Ventas -->
        <div class="row g-3 mb-3">
            
            <!-- Columna Izquierda: Denominaciones Monetarias con Imágenes -->
            <div class="col-lg-7">
                <div class="denominaciones-panel h-100">
                    <div class="denominaciones-header">
                        <span><i class="bi bi-cash-stack text-success me-1"></i> Desglose de Denominaciones Físicas</span>
                        <span class="badge bg-primary-subtle text-primary font-monospace" id="lblBadgeTotalEfec">Total: RD$ 0.00</span>
                    </div>
                    <div class="denominaciones-body">
                        <?php
                        $denominaciones = [
                            ['val' => 2000, 'tipo' => 'billete', 'img' => 'public/img/2000.jpg'],
                            ['val' => 1000, 'tipo' => 'billete', 'img' => 'public/img/1000.jpg'],
                            ['val' => 500,  'tipo' => 'billete', 'img' => 'public/img/500.jpg'],
                            ['val' => 200,  'tipo' => 'billete', 'img' => 'public/img/200.jpg'],
                            ['val' => 100,  'tipo' => 'billete', 'img' => 'public/img/100.jpg'],
                            ['val' => 50,   'tipo' => 'billete', 'img' => 'public/img/50.jpg'],
                            ['val' => 25,   'tipo' => 'moneda',  'img' => 'public/img/25.png'],
                            ['val' => 10,   'tipo' => 'moneda',  'img' => 'public/img/10.png'],
                            ['val' => 5,    'tipo' => 'moneda',  'img' => 'public/img/5.png'],
                            ['val' => 1,    'tipo' => 'moneda',  'img' => 'public/img/1.png']
                        ];
                        ?>
                        
                        <div class="denominaciones-grid">
                            <?php foreach ($denominaciones as $d): ?>
                            <div class="denom-card">
                                <div class="denom-img-wrapper">
                                    <img src="<?php echo $d['img']; ?>" alt="RD$ <?php echo $d['val']; ?>" class="denom-img" loading="lazy">
                                </div>
                                <div class="denom-info-row">
                                    <span class="denom-badge">RD$ <?php echo number_format($d['val']); ?></span>
                                    <span class="denom-subtotal" id="sub_<?php echo $d['val']; ?>">RD$ 0.00</span>
                                </div>
                                <div class="denom-input-group">
                                    <button type="button" class="denom-btn-step" onclick="modificarCantidadDenom(<?php echo $d['val']; ?>, -1)">-</button>
                                    <input type="number" min="0" name="mon_<?php echo $d['val']; ?>" id="mon_<?php echo $d['val']; ?>" 
                                           class="denom-input denominacion" data-val="<?php echo $d['val']; ?>" placeholder="0">
                                    <button type="button" class="denom-btn-step" onclick="modificarCantidadDenom(<?php echo $d['val']; ?>, 1)">+</button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <span class="fw-bold text-muted small"><i class="bi bi-info-circle me-1"></i>Suma de billetes y monedas físicas en gaveta</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-muted small">Efectivo Contado:</span>
                                <input type="text" id="txtCierreEfectivo" 
                                       class="form-control form-control-sm text-end fw-bold text-primary font-monospace bg-light border-primary" 
                                       style="width: 140px; font-size: 1rem;" value="<?php echo number_format($data['teorico']['efectivo_neto'] ?? 0, 2, '.', ''); ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Registro Resumido y Arqueo Teórico -->
            <div class="col-lg-5">
                <div class="row g-3">
                    
                    <!-- Tarjeta 1: Registro Declarado por el Cajero -->
                    <div class="col-12">
                        <div class="arqueo-side-card">
                            <div class="arqueo-card-header bg-light">
                                <span class="text-dark fw-bold"><i class="bi bi-pencil-square text-primary me-1"></i> Declaración de Valores</span>
                                <span class="badge bg-primary-subtle text-primary">Cierre Físico</span>
                            </div>
                            <div class="arqueo-card-body">
                                <table class="table-arqueo">
                                    <tr>
                                        <td>
                                            <div class="table-arqueo-label"><i class="bi bi-cash"></i> Efectivo en Caja:</div>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="monto_efectivo" id="resEfectivo" 
                                                   class="form-control input-arqueo font-monospace" 
                                                   value="<?php echo number_format($data['teorico']['efectivo_neto'] ?? 0, 2, '.', ''); ?>" 
                                                   oninput="calculoTotalMonto()">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="table-arqueo-label"><i class="bi bi-credit-card text-secondary"></i> Tarjeta / POS:</div>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="monto_tarjeta" id="txtTarjeta" 
                                                   class="form-control input-arqueo font-monospace" 
                                                   value="<?php echo number_format($data['teorico']['ventas_tarjeta'] ?? 0, 2, '.', ''); ?>" 
                                                   oninput="calculoTotalMonto()">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="table-arqueo-label"><i class="bi bi-bank text-secondary"></i> Transferencias:</div>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="monto_transferencia" id="txtTransferencia" 
                                                   class="form-control input-arqueo font-monospace" 
                                                   value="<?php echo number_format($data['teorico']['ventas_transferencia'] ?? 0, 2, '.', ''); ?>" 
                                                   oninput="calculoTotalMonto()">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="table-arqueo-label"><i class="bi bi-card-text text-secondary"></i> Cheques:</div>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="monto_cheque" id="txtCheque" 
                                                   class="form-control input-arqueo font-monospace" 
                                                   value="<?php echo number_format($data['teorico']['cheque_neto'] ?? 0, 2, '.', ''); ?>" 
                                                   oninput="calculoTotalMonto()">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="table-arqueo-label"><i class="bi bi-clock-history text-secondary"></i> Crédito:</div>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="monto_credito" id="txtCredito" 
                                                   class="form-control input-arqueo font-monospace" value="0.00" oninput="calculoTotalMonto()">
                                        </td>
                                    </tr>
                                    <tr class="arqueo-total-row">
                                        <td>
                                            <div class="table-arqueo-label fw-bold text-dark"><i class="bi bi-calculator text-secondary"></i> Total Declarado:</div>
                                        </td>
                                        <td>
                                            <input type="text" id="txtTotalMonto" class="form-control input-arqueo arqueo-total-input font-monospace" value="0.00" readonly>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta 2: Resumen del Sistema (Arqueo Teórico) -->
                    <div class="col-12">
                        <div class="arqueo-side-card">
                            <div class="arqueo-card-header bg-dark text-white">
                                <span><i class="bi bi-calculator me-1"></i> Resumen del Sistema (Arqueo)</span>
                            </div>
                            <div class="arqueo-card-body">
                                <table class="table-arqueo">
                                    <tr>
                                        <td>
                                            <div class="table-arqueo-label">Ventas Efectivo (+ Fondo):</div>
                                        </td>
                                        <td>
                                            <input type="text" id="vEfectivo" class="form-control input-arqueo input-arqueo-readonly font-monospace" 
                                                   value="<?php echo number_format($data['teorico']['efectivo_con_fondo'] ?? 0, 2); ?>" readonly>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="table-arqueo-label">Ventas Tarjeta:</div>
                                        </td>
                                        <td>
                                            <input type="text" id="vTarjeta" class="form-control input-arqueo input-arqueo-readonly font-monospace" 
                                                   value="<?php echo number_format($data['teorico']['ventas_tarjeta'] ?? 0, 2); ?>" readonly>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="table-arqueo-label">Ventas Transferencia:</div>
                                        </td>
                                        <td>
                                            <input type="text" id="vTransferencia" class="form-control input-arqueo input-arqueo-readonly font-monospace" 
                                                   value="<?php echo number_format($data['teorico']['ventas_transferencia'] ?? 0, 2); ?>" readonly>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="table-arqueo-label">Ventas Cheque:</div>
                                        </td>
                                        <td>
                                            <input type="text" id="vCheque" class="form-control input-arqueo input-arqueo-readonly font-monospace" 
                                                   value="<?php echo number_format($data['teorico']['ventas_cheque'] ?? 0, 2); ?>" readonly>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="table-arqueo-label text-danger">Retiros Efectivo (-):</div>
                                        </td>
                                        <td>
                                            <input type="text" id="vRetirosEfec" class="form-control input-arqueo input-arqueo-readonly font-monospace text-danger" 
                                                   value="<?php echo number_format($data['teorico']['monto_retiro_efec'] ?? 0, 2); ?>" readonly>
                                        </td>
                                    </tr>
                                    <tr id="rowRetirosCheque" style="<?php echo (($data['teorico']['monto_retiro_cheq'] ?? 0) > 0) ? '' : 'display: none;'; ?>">
                                        <td>
                                            <div class="table-arqueo-label text-danger">Retiros Cheques (-):</div>
                                        </td>
                                        <td>
                                            <input type="text" id="vRetirosCheq" class="form-control input-arqueo input-arqueo-readonly font-monospace text-danger" 
                                                   value="<?php echo number_format($data['teorico']['monto_retiro_cheq'] ?? 0, 2); ?>" readonly>
                                        </td>
                                    </tr>
                                    <tr class="arqueo-total-row">
                                        <td>
                                            <div class="table-arqueo-label fw-bold text-dark">Total Teórico en Sistema:</div>
                                        </td>
                                        <td>
                                            <input type="text" id="vTotal" class="form-control input-arqueo input-arqueo-readonly font-monospace fw-bold" 
                                                   value="<?php echo number_format($data['teorico']['total_teorico'] ?? 0, 2); ?>" readonly>
                                        </td>
                                    </tr>
                                </table>

                                <div class="mt-3 pt-2 border-top">
                                    <button type="button" class="btn-imprimir-arqueo" id="btnImprimirResumen" onclick="imprimirReporteCompleto()">
                                        <i class="bi bi-printer text-primary"></i> Imprimir Arqueo Fiscal (TCPDF)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- Comentario y Botón Final -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-chat-square-text me-1"></i> Comentario / Observación de Cierre</label>
                    <textarea name="comentario" id="txtComentarioCierre" class="form-control input-custom" rows="2" placeholder="Notas sobre sobrantes, faltantes o incidencias del turno (opcional)"></textarea>
                </div>
                <div class="col-lg-4 d-flex gap-2 justify-content-end align-items-end">
                    <a href="index.php?page=dashboard" class="btn-action-cancel">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </a>
                    <button type="button" class="btn-action-submit-close" id="btnSolicitarCierre" onclick="confirmarCierreModal()">
                        <i class="bi bi-lock-fill me-1"></i> Liquidar & Cerrar Caja
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

<!-- Modal de Confirmación Moderno -->
<div class="modal fade" id="modalConfirmCierre" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-body p-4 text-center">
                <div class="d-inline-flex align-items-center justify-content-center bg-warning-subtle text-warning rounded-circle mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-lock-fill fs-2"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Confirmar Cierre de Turno</h5>
                <p class="text-muted small mb-3">Una vez cerrada la caja, no se podrán realizar más operaciones en este turno.</p>
                
                <div class="bg-light p-3 rounded-3 border mb-3 text-start">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Total Efectivo Declarado:</span>
                        <strong class="text-primary font-monospace" id="lblConfirmEfectivo">RD$ 0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Total Declarado General:</span>
                        <strong class="text-dark font-monospace fs-6" id="lblConfirmTotal">RD$ 0.00</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Turno N°:</span>
                        <span class="small font-monospace">Z-<?php echo htmlspecialchars($data['apertura']['num_z'] ?? '0000000'); ?></span>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light flex-fill py-2 fw-semibold" data-bs-dismiss="modal">Revisar</button>
                    <button type="button" class="btn btn-primary flex-fill py-2 fw-bold" id="btnEjecutarCierre" onclick="ejecutarEnvioCierre()">
                        <i class="bi bi-check2-circle me-1"></i> Sí, Cerrar Caja
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="cierre-toast-container">
    <div id="cierreToast" class="toast align-items-center text-bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="cierreToastBody">
                Notificación del sistema
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
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

function showToast(mensaje, tipo = 'dark') {
    const toastElem = document.getElementById('cierreToast');
    const toastBody = document.getElementById('cierreToastBody');
    toastElem.className = `toast align-items-center text-bg-${tipo} border-0 shadow-lg`;
    toastBody.innerText = mensaje;
    const toast = new bootstrap.Toast(toastElem, { delay: 3500 });
    toast.show();
}

function modificarCantidadDenom(val, delta) {
    const input = document.getElementById('mon_' + val);
    let cant = parseInt(input.value) || 0;
    cant = Math.max(0, cant + delta);
    input.value = cant > 0 ? cant : '';
    calcularDenominaciones();
}

function calcularDenominaciones() {
    let totalEfec = 0;
    document.querySelectorAll('.denominacion').forEach(function(inp) {
        let cant = parseInt(inp.value) || 0;
        let val = parseInt(inp.getAttribute('data-val')) || 0;
        let sub = cant * val;
        totalEfec += sub;
        let subElem = document.getElementById('sub_' + val);
        if (subElem) {
            subElem.innerText = 'RD$ ' + sub.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    });

    let txtEfec = document.getElementById('txtCierreEfectivo');
    if (txtEfec) txtEfec.value = totalEfec.toFixed(2);

    let resEfec = document.getElementById('resEfectivo');
    if (resEfec) resEfec.value = totalEfec.toFixed(2);

    let badgeEfec = document.getElementById('lblBadgeTotalEfec');
    if (badgeEfec) badgeEfec.innerText = 'Total: RD$ ' + totalEfec.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    calculoTotalMonto();
}

document.querySelectorAll('.denominacion').forEach(function(input) {
    input.addEventListener('input', calcularDenominaciones);
});

function calculoTotalMonto() {
    let elemEfec = document.getElementById('txtCierreEfectivo') || document.getElementById('resEfectivo');
    let efec = parseFloat(elemEfec ? elemEfec.value : 0) || 0;
    
    let elemTarj = document.getElementById('txtTarjeta');
    let tarj = parseFloat(elemTarj ? elemTarj.value : 0) || 0;
    
    let elemTrans = document.getElementById('txtTransferencia');
    let trans = parseFloat(elemTrans ? elemTrans.value : 0) || 0;
    
    let elemCheq = document.getElementById('txtCheque');
    let cheq = parseFloat(elemCheq ? elemCheq.value : 0) || 0;
    
    let elemCred = document.getElementById('txtCredito');
    let cred = parseFloat(elemCred ? elemCred.value : 0) || 0;
    
    let total = efec + tarj + trans + cheq + cred;
    let elemTotal = document.getElementById('txtTotalMonto');
    if (elemTotal) elemTotal.value = total.toFixed(2);
}

// Arqueo Automático (sincronización en segundo plano si se requiere)
function sincronizarArqueoAutomatico() {
    let idDiario = document.getElementById('id_diario_caja').value;
    if (!idDiario || idDiario == "0") return;

    fetch('index.php?action=visualizar_arqueo&id=' + idDiario)
        .then(res => res.json())
        .then(data => {
            if (data.error) return;

            document.getElementById('vEfectivo').value = formatCurrency(parseFloat(data.monto_efectivo || 0).toFixed(2));
            document.getElementById('vTarjeta').value = formatCurrency(parseFloat(data.monto_tarjeta || 0).toFixed(2));
            document.getElementById('vTransferencia').value = formatCurrency(parseFloat(data.monto_transferencia || 0).toFixed(2));
            document.getElementById('vCheque').value = formatCurrency(parseFloat(data.monto_cheque || 0).toFixed(2));

            let retEfec = parseFloat(data.monto_retiro_efectivo !== undefined ? data.monto_retiro_efectivo : data.monto_retiro || 0);
            document.getElementById('vRetirosEfec').value = formatCurrency(retEfec.toFixed(2));

            let retCheq = parseFloat(data.monto_retiro_cheque || 0);
            let rowRetCheq = document.getElementById('rowRetirosCheque');
            if (rowRetCheq) {
                if (retCheq > 0) {
                    rowRetCheq.style.display = '';
                    document.getElementById('vRetirosCheq').value = formatCurrency(retCheq.toFixed(2));
                } else {
                    rowRetCheq.style.display = 'none';
                }
            }

            document.getElementById('vTotal').value = formatCurrency(parseFloat(data.monto_total || 0).toFixed(2));

            // Sincronizar monto_transferencia si el cajero no lo ha digitado o está en 0
            let txtTransf = document.getElementById('txtTransferencia');
            if (txtTransf && parseFloat(txtTransf.value || 0) === 0 && parseFloat(data.monto_transferencia || 0) > 0) {
                txtTransf.value = parseFloat(data.monto_transferencia).toFixed(2);
                calculoTotalMonto();
            }
        })
        .catch(err => console.warn('Arqueo automático cargado directamente'));
}

function imprimirReporteCompleto() {
    let id = document.getElementById('id_diario_caja').value;
    if (!id || id == "0") {
        showToast("No se encontró ID de caja para imprimir.", "warning");
        return;
    }
    window.open('index.php?action=reporte_cierre_caja&id=' + id, '_blank');
}

// Modal de Confirmación
function confirmarCierreModal() {
    let elemEfec = document.getElementById('txtCierreEfectivo') || document.getElementById('resEfectivo');
    let efec = parseFloat(elemEfec ? elemEfec.value : 0) || 0;
    
    let elemTotal = document.getElementById('txtTotalMonto');
    let total = parseFloat(elemTotal ? elemTotal.value : 0) || 0;

    let lblEfec = document.getElementById('lblConfirmEfectivo');
    if (lblEfec) lblEfec.innerText = 'RD$ ' + efec.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    let lblTot = document.getElementById('lblConfirmTotal');
    if (lblTot) lblTot.innerText = 'RD$ ' + total.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    let modalEl = document.getElementById('modalConfirmCierre');
    if (modalEl) new bootstrap.Modal(modalEl).show();
}

function ejecutarEnvioCierre() {
    const btn = document.getElementById('btnEjecutarCierre');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Cerrando...';
    document.getElementById('formCierre').submit();
}

// Inicializar cálculos
calculoTotalMonto();
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>