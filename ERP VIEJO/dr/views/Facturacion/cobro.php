<?php 
 $totalPagar = $datosCobro['total'] ?? 0;
 $docEntry = $datosCobro['docEntry'] ?? 0;
 $esCreditoPendiente = isset($datosCobro['pendiente']) && $datosCobro['pendiente'] === true;
 $titulo = $esCreditoPendiente ? "Cobrar Factura de Crédito" : "Cobro de Factura al Contado";
 
 // Datos ARS opcionales para display visual
 $arsInfo = $datosCobro['ars'] ?? null;
 $arsActivo = !empty($arsInfo) && !empty($arsInfo['NumAprobacion']);
 $totalVisual = ($arsActivo && ($arsInfo['MontoResponsable'] ?? 0) > 0) 
                ? floatval($arsInfo['MontoResponsable']) 
                : floatval($totalPagar);
?>

<?php require_once __DIR__ . '/../Estructura/header.php'; ?>
<link rel="stylesheet" href="views/Facturacion/styles/cobro.css">

<div class="cobro-container">
    <div class="cobro-card">
        <div class="cobro-card-header">
            <div class="cobro-header-icon">
                <i class="bi bi-cash-coin"></i>
            </div>
            <h3 class="cobro-title"><?php echo htmlspecialchars($titulo); ?></h3>
            <p class="cobro-subtitle">Liquidación de valores en caja operativa</p>
        </div>

        <div class="cobro-card-body">
            <div id="alertContainer">
                <?php echo Helper::getMensaje(); ?>
            </div>

            <input type="hidden" id="docEntryPago" value="<?php echo $docEntry; ?>">
            <input type="hidden" id="esCreditoPendiente" value="<?php echo $esCreditoPendiente ? '1' : '0'; ?>">
            
            <?php if ($arsActivo): ?>
            <!-- Banner de Cobertura ARS -->
            <div class="cobro-ars-banner">
                <div class="cobro-ars-header">
                    <i class="bi bi-shield-check"></i>
                    <span>Cobertura de Seguro Médico Aplicada</span>
                    <span class="cobro-ars-badge"><?php echo htmlspecialchars($arsInfo['NombreARS'] ?: $arsInfo['CardCode_ARS']); ?></span>
                </div>
                <div class="cobro-ars-grid">
                    <div class="cobro-ars-item">
                        <span class="cobro-ars-label">N° Autorización</span>
                        <span class="cobro-ars-value font-monospace fw-bold"><?php echo htmlspecialchars($arsInfo['NumAprobacion']); ?></span>
                    </div>
                    <div class="cobro-ars-item">
                        <span class="cobro-ars-label">Total Factura</span>
                        <span class="cobro-ars-value font-monospace text-muted">RD$ <?php echo number_format($totalPagar, 2); ?></span>
                    </div>
                    <div class="cobro-ars-item">
                        <span class="cobro-ars-label">Cubre ARS (<?php echo number_format($arsInfo['PorcentajeCober'], 1); ?>%)</span>
                        <span class="cobro-ars-value font-monospace text-success">RD$ <?php echo number_format($arsInfo['MontoAprobado'], 2); ?></span>
                    </div>
                    <div class="cobro-ars-item cobro-ars-item-copago">
                        <span class="cobro-ars-label">Paga Paciente</span>
                        <span class="cobro-ars-value font-monospace fw-bold text-primary">RD$ <?php echo number_format($arsInfo['MontoResponsable'], 2); ?></span>
                    </div>
                    <?php if (!empty($arsInfo['TipoServicio'])): ?>
                    <div class="cobro-ars-item">
                        <span class="cobro-ars-label">Servicio</span>
                        <span class="cobro-ars-value"><?php echo htmlspecialchars($arsInfo['TipoServicio']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($arsInfo['NumAfiliado'])): ?>
                    <div class="cobro-ars-item">
                        <span class="cobro-ars-label">N° Afiliado</span>
                        <span class="cobro-ars-value font-monospace"><?php echo htmlspecialchars($arsInfo['NumAfiliado']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Banner Total (muestra copago si hay ARS, sino el total completo) -->
            <div class="cobro-total-banner <?php echo $arsActivo ? 'cobro-total-banner-ars' : ''; ?>">
                <?php if ($arsActivo): ?>
                    <div class="cobro-total-label" style="font-size:0.7rem; color:#166534;">
                        <i class="bi bi-person-fill me-1"></i>MONTO A COBRAR AL PACIENTE (Copago)
                    </div>
                    <div class="cobro-total-value" id="lblTotalPagar" style="color:#0369a1;">
                        RD$ <?php echo number_format($arsInfo['MontoResponsable'], 2); ?>
                    </div>
                    <div style="font-size:0.72rem; color:#64748b; margin-top:3px;">
                        Total factura: RD$ <?php echo number_format($totalPagar, 2); ?> 
                        &nbsp;·&nbsp; ARS cubre: RD$ <?php echo number_format($arsInfo['MontoAprobado'], 2); ?>
                    </div>
                <?php else: ?>
                    <div class="cobro-total-label">Monto Total a Liquidar</div>
                    <div class="cobro-total-value" id="lblTotalPagar">RD$ <?php echo number_format($totalPagar, 2); ?></div>
                <?php endif; ?>
            </div>

            <!-- Accesos rápidos de pago en grid organizado -->
            <div class="quick-pay-bar">
                <button type="button" class="btn-quick-pay" onclick="setMontoExacto()">
                    <i class="bi bi-cash-stack text-success"></i> Exacto (Efectivo)
                </button>
                <button type="button" class="btn-quick-pay" onclick="setTodoTarjeta()">
                    <i class="bi bi-credit-card text-primary"></i> Todo Tarjeta
                </button>
                <button type="button" class="btn-quick-pay" onclick="setTodoTransferencia()">
                    <i class="bi bi-bank2 text-info"></i> Todo Transferencia
                </button>
                <button type="button" class="btn-quick-pay" onclick="setSiguienteDenominacion(1000)">
                    RD$ 1,000
                </button>
                <button type="button" class="btn-quick-pay" onclick="setSiguienteDenominacion(2000)">
                    RD$ 2,000
                </button>
            </div>

            <form id="formCobro" onsubmit="event.preventDefault(); procesarCobro();">
                
                <!-- 1. Método: Efectivo -->
                <div class="payment-method-card payment-method-efectivo">
                    <div class="payment-card-header-row">
                        <div class="payment-method-title">
                            <i class="bi bi-cash-stack text-success"></i>
                            <span>Efectivo Recibido</span>
                        </div>
                        <small class="text-muted font-monospace fw-bold">RD$</small>
                    </div>
                    <input type="number" step="0.01" min="0" id="txtMontoEfectivo" 
                           class="form-control payment-input" value="0.00" oninput="calcularVuelto()" autofocus>
                </div>

                <!-- 2. Método: Tarjetas / POS (Soporta múltiples tarjetas y logos SVG integrados) -->
                <div class="payment-method-card payment-method-tarjeta">
                    <div class="payment-card-header-row">
                        <div class="payment-method-title">
                            <i class="bi bi-credit-card-2-back text-primary"></i>
                            <span>Tarjeta(s) / POS</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="subtotal-label">Subtotal:</span>
                            <span class="payment-subtotal-badge font-monospace" id="lblSubtotalTarjetas">RD$ 0.00</span>
                        </div>
                    </div>

                    <!-- Contenedor dinámico de tarjetas -->
                    <div id="contenedorTarjetas" class="tarjetas-container">
                        <!-- Tarjeta 1 (Principal por defecto) -->
                        <div class="tarjeta-row" id="tarjetaRow_1" data-index="1">
                            <div class="tarjeta-row-header">
                                <span class="tarjeta-index-label" id="lblTarjetaIndex_1">Tarjeta 1</span>
                            </div>
                            <div class="tarjeta-row-grid">
                                <div class="tarjeta-field">
                                    <label class="tarjeta-input-label">Monto (RD$)</label>
                                    <input type="number" step="0.01" min="0" 
                                           class="form-control payment-input input-tarjeta-monto" 
                                           id="txtMontoTarjeta_1" value="0.00" oninput="calcularVuelto()">
                                </div>
                                <div class="tarjeta-field">
                                    <label class="tarjeta-input-label">N° Tarjeta / Ref. POS <small class="text-muted fw-normal">(opcional)</small></label>
                                    <div class="tarjeta-input-ref-wrapper">
                                        <input type="text" class="form-control tarjeta-input-ref input-tarjeta-ref" 
                                               id="txtNumTarjeta_1" placeholder="Ej: 4532... ó Ref: 9812" 
                                               maxlength="24" oninput="detectarTarjetaInput(1)">
                                        <div class="card-brand-badge" id="cardBrandBadge_1">
                                            <span class="brand-badge-generic" id="brandBadgeIcon_1">
                                                <i class="bi bi-credit-card-2-back"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón para añadir otra tarjeta -->
                    <div class="add-tarjeta-action">
                        <button type="button" class="btn-link-action" onclick="agregarTarjeta()">
                            <i class="bi bi-plus-circle-fill"></i>
                            <span>Añadir otra tarjeta</span>
                        </button>
                    </div>
                </div>

                <!-- Botón para desplegar Transferencia Bancaria si está oculta -->
                <div id="toggleTransferWrapper" class="toggle-transfer-wrapper">
                    <button type="button" class="btn-link-action" id="btnToggleTransfer" onclick="toggleTransferencia(true)">
                        <i class="bi bi-plus-circle-fill"></i>
                        <span>Añadir Transferencia Bancaria</span>
                    </button>
                </div>

                <!-- 3. Método: Transferencia Bancaria (Plegable para no saturar la pantalla) -->
                <div class="payment-method-card payment-method-transfer" id="seccionTransferencia" style="display: none;">
                    <div class="payment-card-header-row">
                        <div class="payment-method-title">
                            <i class="bi bi-bank2 text-info"></i>
                            <span>Transferencia Bancaria</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="subtotal-label">Subtotal:</span>
                            <span class="payment-subtotal-badge font-monospace" id="lblSubtotalTransferencia">RD$ 0.00</span>
                            <button type="button" class="btn-remove-transfer" onclick="toggleTransferencia(false)" title="Quitar transferencia">
                                <i class="bi bi-x-circle me-1"></i> Quitar
                            </button>
                        </div>
                    </div>

                    <div class="transfer-row-grid">
                        <div class="transfer-field-monto">
                            <label class="tarjeta-input-label">Monto Transferido (RD$)</label>
                            <input type="number" step="0.01" min="0" id="txtMontoTransferencia" 
                                   class="form-control payment-input" value="0.00" oninput="validarTransferencia(); calcularVuelto();">
                        </div>
                        <div class="transfer-field-banco">
                            <label class="tarjeta-input-label">Banco Emisor / Destino</label>
                            <select id="selBancoTransferencia" class="form-select transfer-select" onchange="validarTransferencia(); calcularVuelto();">
                                <option value="">-- Seleccionar Banco --</option>
                                <option value="Popular">Banco Popular Dominicano</option>
                                <option value="Banreservas">Banreservas</option>
                                <option value="BHD">Banco BHD</option>
                                <option value="Santa Cruz">Banco Santa Cruz</option>
                                <option value="Scotiabank">Scotiabank</option>
                                <option value="Promerica">Banco Promerica</option>
                                <option value="Banesco">Banesco</option>
                                <option value="Asociacion">Asoc. Popular / Cibao</option>
                                <option value="Otro">Otro Banco / ACH</option>
                            </select>
                        </div>
                        <div class="transfer-field-ref">
                            <label class="tarjeta-input-label d-flex justify-content-between align-items-center">
                                <span>N° Referencia / Comprobante</span>
                                <span class="badge-req-ref" id="reqStarRef" style="display:none;">Requerido</span>
                            </label>
                            <input type="text" id="txtRefTransferencia" class="form-control transfer-input" 
                                   placeholder="Ej: 984521034 ó Aprobación ACH" maxlength="30" 
                                   oninput="validarTransferencia(); calcularVuelto();">
                        </div>
                    </div>
                    <div id="transferenciaWarning" class="transfer-warning-box" style="display: none;">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                        <span>Debe ingresar el N° de Referencia o Comprobante para validar el pago por transferencia.</span>
                    </div>
                </div>
                
                <!-- Caja de Vuelto / Diferencia -->
                <div id="alertVuelto" class="vuelto-box danger" role="alert">
                    <span id="lblVueltoTexto"><i class="bi bi-exclamation-triangle-fill me-1"></i> Falta por pagar:</span>
                    <span class="font-monospace fs-5" id="lblVueltoMonto">RD$ <?php echo number_format($totalVisual, 2); ?></span>
                </div>

                <!-- Desglose de Medios Aplicados -->
                <div id="resumenDesgloseMedios" class="resumen-medios-bar" style="display: none;">
                    <span id="lblResumenMediosTexto"></span>
                </div>
                
                <!-- Acciones -->
                <div class="cobro-actions">
                    <button type="button" class="btn-confirm-pay" id="btnConfirmarCobro" onclick="procesarCobro()" disabled>
                        <i class="bi bi-check2-circle fs-5"></i> Registrar Cobro &amp; Finalizar
                    </button>
                    <a href="index.php?page=facturacion" class="btn btn-light btn-sm text-secondary rounded-3 py-2 border">
                        <i class="bi bi-arrow-left me-1"></i> Cancelar y Volver a Facturación
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="cobro-toast-container">
    <div id="cobroToast" class="toast align-items-center text-bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="cobroToastBody">
                Mensaje de cobro
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
// totalPagar = valor REAL de la factura (lo que se registra en POSOINV)
let totalPagar = <?php echo floatval($totalPagar); ?>;
// totalVisual = lo que el cajero ve y cobra al paciente (copago si hay ARS, sino total completo)
let totalVisual = <?php echo floatval($totalVisual); ?>;

// Contador secuencial para tarjetas dinámicas
let tarjetaSecuencia = 1;

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

function showCobroToast(mensaje, tipo = 'dark') {
    const toastElem = document.getElementById('cobroToast');
    const toastBody = document.getElementById('cobroToastBody');
    toastElem.className = `toast align-items-center text-bg-${tipo} border-0 shadow-lg`;
    toastBody.innerText = mensaje;
    const toast = new bootstrap.Toast(toastElem, { delay: 4000 });
    toast.show();
}

/**
 * Control de visibilidad de Transferencia Bancaria
 */
function toggleTransferencia(mostrar) {
    let seccion = $('#seccionTransferencia');
    let toggleBtn = $('#toggleTransferWrapper');
    if (mostrar) {
        toggleBtn.slideUp(150);
        seccion.slideDown(200);
        $('#txtMontoTransferencia').focus();
    } else {
        $('#txtMontoTransferencia').val('0.00');
        $('#txtRefTransferencia').val('');
        $('#selBancoTransferencia').val('');
        validarTransferencia();
        calcularVuelto();
        seccion.slideUp(200);
        toggleBtn.slideDown(150);
    }
}

/**
 * Logos oficiales en SVG Vectorial (sin dependencias externas ni APIs)
 */
const CARD_LOGOS = {
    visa: `<svg class="svg-card-logo" viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="48" height="32" rx="4" fill="#0E4595"/>
        <path d="M19.4 22L21.8 7.2H25.6L23.2 22H19.4ZM33.9 7.6C33.1 7.3 32 7 30.6 7C27 7 24.5 8.9 24.5 11.6C24.5 13.6 26.3 14.7 27.7 15.4C29.1 16.1 29.6 16.5 29.6 17.2C29.6 18.2 28.4 18.7 27.2 18.7C25.5 18.7 24.5 18.4 23.4 17.9L22.9 17.7L22.3 21.3C23.3 21.8 25.1 22.2 26.9 22.2C30.8 22.2 33.3 20.3 33.3 17.4C33.3 15.1 31.9 13.9 29.9 13C28.7 12.4 27.9 12 27.9 11.3C27.9 10.6 28.7 10 30.2 10C31.5 10 32.5 10.3 33.2 10.6L33.6 10.8L33.9 7.6ZM42.5 7.2H39.5C38.6 7.2 37.8 7.5 37.4 8.5L31.9 22H35.9C35.9 22 36.6 20.1 36.8 19.5H41.5C41.6 20 42 22 42 22H45.5L42.5 7.2ZM37.8 16.7C38.2 15.6 39.8 11.4 39.8 11.4C39.8 11.4 40.2 10.3 40.4 9.8L40.7 11.3C40.7 11.3 41.3 14.8 41.7 16.7H37.8ZM16.3 7.2L12.7 17.2L12.3 15.2C11.6 12.8 9.5 10.1 7 8.7L10.3 22H14.3L20.3 7.2H16.3Z" fill="white"/>
        <path d="M9.8 7.2H4.2L4 7.5C8.4 8.6 12.2 11.7 13.4 15.2L12.3 8.5C12.1 7.5 11.1 7.2 9.8 7.2Z" fill="#F7B600"/>
    </svg>`,
    mastercard: `<svg class="svg-card-logo" viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="48" height="32" rx="4" fill="#222222"/>
        <circle cx="18" cy="16" r="9" fill="#EB001B"/>
        <circle cx="30" cy="16" r="9" fill="#F79E1B"/>
        <path d="M24 8.9C26.1 10.7 27.5 13.2 27.5 16C27.5 18.8 26.1 21.3 24 23.1C21.9 21.3 20.5 18.8 20.5 16C20.5 13.2 21.9 10.7 24 8.9Z" fill="#FF5F00"/>
    </svg>`,
    amex: `<svg class="svg-card-logo" viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="48" height="32" rx="4" fill="#007BC1"/>
        <path d="M8 12.5H12.5L14 16L12.5 19.5H8V12.5ZM10 14V18H11.5L12.8 16L11.5 14H10ZM15.5 12.5H18L19.5 16L21 12.5H23.5V19.5H21.5V15L20 18.5H19L17.5 15V19.5H15.5V12.5ZM25 12.5H30V14.2H27V15.2H29.5V16.8H27V17.8H30V19.5H25V12.5ZM31.5 12.5H33.8L35.5 15L37.2 12.5H39.5L36.8 16L39.5 19.5H37.2L35.5 17L33.8 19.5H31.5L34.2 16L31.5 12.5Z" fill="white"/>
    </svg>`,
    discover: `<svg class="svg-card-logo" viewBox="0 0 48 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect width="48" height="32" rx="4" fill="#FFFFFF" stroke="#E2E8F0"/>
        <circle cx="34" cy="16" r="6" fill="#F47216"/>
        <path d="M6 13H11C13.2 13 14.5 14.3 14.5 16C14.5 17.7 13.2 19 11 19H6V13ZM8.5 17.2H10.5C11.5 17.2 12.2 16.7 12.2 16C12.2 15.3 11.5 14.8 10.5 14.8H8.5V17.2ZM16 13H18.5V19H16V13ZM20 17.8C20.5 18.5 21.5 19.1 22.8 19.1C24.5 19.1 25.4 18.1 25.4 16.9C25.4 14.8 22.8 14.8 22.8 14.1C22.8 13.8 23.1 13.6 23.6 13.6C24.2 13.6 24.8 13.8 25.2 14.2L25.8 13.2C25.2 12.6 24.3 12.3 23.4 12.3C21.8 12.3 21 13.3 21 14.5C21 16.6 23.6 16.5 23.6 17.3C23.6 17.7 23.2 17.9 22.7 17.9C21.8 17.9 21 17.4 20.6 16.9L20 17.8Z" fill="#1E293B"/>
    </svg>`,
    generica: `<span class="brand-badge-generic"><i class="bi bi-credit-card-2-back"></i></span>`
};

/**
 * Detección instantánea de franquicia de tarjeta por sus dígitos
 */
function detectarFranquicia(numero) {
    if (!numero) return { nombre: 'Tarjeta', key: 'generica' };
    let clean = numero.replace(/\D/g, '');
    if (/^4/.test(clean)) {
        return { nombre: 'Visa', key: 'visa' };
    } else if (/^(5[1-5]|222[1-9]|22[3-9]\d|2[3-6]\d{2}|27[01]\d|2720)/.test(clean)) {
        return { nombre: 'Mastercard', key: 'mastercard' };
    } else if (/^3[47]/.test(clean)) {
        return { nombre: 'American Express', key: 'amex' };
    } else if (/^6(?:011|5)/.test(clean)) {
        return { nombre: 'Discover', key: 'discover' };
    }
    return { nombre: 'Tarjeta', key: 'generica' };
}

function detectarTarjetaInput(index) {
    let inputVal = ($('#txtNumTarjeta_' + index).val() || '').trim();
    let franquicia = detectarFranquicia(inputVal);

    let badgeContainer = $('#cardBrandBadge_' + index);
    if (CARD_LOGOS[franquicia.key]) {
        badgeContainer.html(CARD_LOGOS[franquicia.key]);
    }
    badgeContainer.attr('title', franquicia.nombre);
}

/**
 * Añade dinámicamente otra tarjeta para dividir el cobro
 */
function agregarTarjeta() {
    tarjetaSecuencia++;
    let idx = tarjetaSecuencia;
    let totalCardsActuales = $('.tarjeta-row').length + 1;

    let html = `
        <div class="tarjeta-row tarjeta-row-extra" id="tarjetaRow_${idx}" data-index="${idx}">
            <div class="tarjeta-row-header">
                <span class="tarjeta-index-label" id="lblTarjetaIndex_${idx}">Tarjeta ${totalCardsActuales}</span>
                <button type="button" class="btn-remove-tarjeta" onclick="eliminarTarjeta(${idx})">
                    <i class="bi bi-trash3 me-1"></i> Quitar tarjeta
                </button>
            </div>
            <div class="tarjeta-row-grid">
                <div class="tarjeta-field">
                    <label class="tarjeta-input-label">Monto (RD$)</label>
                    <input type="number" step="0.01" min="0" 
                           class="form-control payment-input input-tarjeta-monto" 
                           id="txtMontoTarjeta_${idx}" value="0.00" oninput="calcularVuelto()">
                </div>
                <div class="tarjeta-field">
                    <label class="tarjeta-input-label">N° Tarjeta / Ref. POS <small class="text-muted fw-normal">(opcional)</small></label>
                    <div class="tarjeta-input-ref-wrapper">
                        <input type="text" class="form-control tarjeta-input-ref input-tarjeta-ref" 
                               id="txtNumTarjeta_${idx}" placeholder="Ej: 4532... ó Ref: 9812" 
                               maxlength="24" oninput="detectarTarjetaInput(${idx})">
                        <div class="card-brand-badge" id="cardBrandBadge_${idx}">
                            <span class="brand-badge-generic" id="brandBadgeIcon_${idx}">
                                <i class="bi bi-credit-card-2-back"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('#contenedorTarjetas').append(html);
    $('#txtMontoTarjeta_' + idx).focus();
    renumerarTarjetas();
    calcularVuelto();
}

/**
 * Elimina una fila de tarjeta extra
 */
function eliminarTarjeta(idx) {
    let elem = $('#tarjetaRow_' + idx);
    elem.css({ transition: 'opacity 0.2s, transform 0.2s', opacity: 0, transform: 'translateY(-6px)' });
    setTimeout(function() {
        elem.remove();
        renumerarTarjetas();
        calcularVuelto();
    }, 200);
}

function renumerarTarjetas() {
    $('.tarjeta-row').each(function(i) {
        let num = i + 1;
        $(this).find('.tarjeta-index-label').text('Tarjeta ' + num);
    });
}

/**
 * Validar campos de transferencia bancaria
 */
function validarTransferencia() {
    let monto = parseFloat($('#txtMontoTransferencia').val()) || 0;
    let ref = ($('#txtRefTransferencia').val() || '').trim();
    let warning = $('#transferenciaWarning');
    let star = $('#reqStarRef');
    let inputRef = $('#txtRefTransferencia');

    if (monto > 0) {
        star.show();
        if (ref === '') {
            warning.slideDown(150);
            inputRef.addClass('is-warning-field');
            return false;
        } else {
            warning.slideUp(150);
            inputRef.removeClass('is-warning-field');
            return true;
        }
    } else {
        star.hide();
        warning.slideUp(150);
        inputRef.removeClass('is-warning-field');
        return true;
    }
}

/**
 * Accesos rápidos de cobro
 */
function setMontoExacto() {
    $('#txtMontoEfectivo').val(totalVisual.toFixed(2));
    $('.input-tarjeta-monto').val('0.00');
    $('#txtMontoTransferencia').val('0.00');
    validarTransferencia();
    calcularVuelto();
}

function setTodoTarjeta() {
    $('#txtMontoEfectivo').val('0.00');
    $('.tarjeta-row-extra').remove();
    renumerarTarjetas();
    $('#txtMontoTarjeta_1').val(totalVisual.toFixed(2));
    $('#txtMontoTransferencia').val('0.00');
    validarTransferencia();
    calcularVuelto();
}

function setTodoTransferencia() {
    toggleTransferencia(true);
    $('#txtMontoEfectivo').val('0.00');
    $('.input-tarjeta-monto').val('0.00');
    $('#txtMontoTransferencia').val(totalVisual.toFixed(2));
    $('#txtRefTransferencia').focus();
    validarTransferencia();
    calcularVuelto();
}

function setSiguienteDenominacion(denom) {
    let monto = Math.ceil(totalVisual / denom) * denom;
    if (monto < totalVisual) monto += denom;
    $('#txtMontoEfectivo').val(monto.toFixed(2));
    $('.input-tarjeta-monto').val('0.00');
    $('#txtMontoTransferencia').val('0.00');
    validarTransferencia();
    calcularVuelto();
}

/**
 * Cálculo dinámico de vuelto, faltante y validaciones
 */
function calcularVuelto() {
    let efectivo = parseFloat($('#txtMontoEfectivo').val()) || 0;
    
    // Suma de todas las tarjetas activas
    let totalTarjetas = 0;
    let cantTarjetasConMonto = 0;
    $('.tarjeta-row').each(function() {
        let idx = $(this).data('index');
        let m = parseFloat($('#txtMontoTarjeta_' + idx).val()) || 0;
        if (m > 0) cantTarjetasConMonto++;
        totalTarjetas += m;
    });

    let montoTransferencia = parseFloat($('#txtMontoTransferencia').val()) || 0;
    let totalPagado = efectivo + totalTarjetas + montoTransferencia;
    let vuelto = totalPagado - totalVisual;

    // Actualizar subtotales en las cabeceras de cada método
    $('#lblSubtotalTarjetas').text('RD$ ' + totalTarjetas.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
    $('#lblSubtotalTransferencia').text('RD$ ' + montoTransferencia.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

    // Validar transferencia
    let transferValida = validarTransferencia();

    let alertDiv = $('#alertVuelto');
    let lblTexto = $('#lblVueltoTexto');
    let lblMonto = $('#lblVueltoMonto');
    let btnConfirmar = $('#btnConfirmarCobro');

    if (totalPagado < totalVisual - 0.001) {
        alertDiv.removeClass('success').addClass('danger');
        lblTexto.html('<i class="bi bi-exclamation-triangle-fill me-1"></i> Falta por pagar:');
        lblMonto.text('RD$ ' + Math.abs(vuelto).toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        btnConfirmar.prop('disabled', true);
    } else {
        if (!transferValida) {
            alertDiv.removeClass('success').addClass('danger');
            lblTexto.html('<i class="bi bi-exclamation-circle-fill me-1"></i> Requiere Referencia:');
            lblMonto.text('Falta Ref. de Transferencia');
            btnConfirmar.prop('disabled', true);
        } else {
            alertDiv.removeClass('danger').addClass('success');
            if (Math.abs(vuelto) < 0.001) {
                lblTexto.html('<i class="bi bi-check-circle-fill me-1 text-primary"></i> Pago Exacto / Completo:');
                lblMonto.text('RD$ 0.00');
            } else {
                lblTexto.html('<i class="bi bi-check-circle-fill me-1 text-primary"></i> Cambio / Vuelto:');
                lblMonto.text('RD$ ' + vuelto.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            }
            btnConfirmar.prop('disabled', false);
        }
    }

    // Desglose de medios si hay pagos mixtos
    let partes = [];
    if (efectivo > 0) partes.push(`Efectivo: <strong>RD$ ${efectivo.toFixed(2)}</strong>`);
    if (totalTarjetas > 0) partes.push(`Tarjetas (${cantTarjetasConMonto}): <strong>RD$ ${totalTarjetas.toFixed(2)}</strong>`);
    if (montoTransferencia > 0) partes.push(`Transferencia: <strong>RD$ ${montoTransferencia.toFixed(2)}</strong>`);

    let resumenDiv = $('#resumenDesgloseMedios');
    if (partes.length > 1) {
        $('#lblResumenMediosTexto').html('<i class="bi bi-info-circle me-1 text-primary"></i> ' + partes.join(' &nbsp;·&nbsp; '));
        resumenDiv.slideDown(150);
    } else {
        resumenDiv.slideUp(150);
    }
}

/**
 * Enviar cobro al backend
 */
function procesarCobro() {
    let efectivo = parseFloat($('#txtMontoEfectivo').val()) || 0;
    let docEntry = $('#docEntryPago').val();
    let esPendiente = $('#esCreditoPendiente').val() === '1';

    // Recoger lista de tarjetas activas
    let listaTarjetas = [];
    $('.tarjeta-row').each(function() {
        let idx = $(this).data('index');
        let m = parseFloat($('#txtMontoTarjeta_' + idx).val()) || 0;
        let r = ($('#txtNumTarjeta_' + idx).val() || '').trim();
        let badgeElem = $('#cardBrandBadge_' + idx);
        let t = badgeElem.attr('title') || 'Tarjeta';
        if (m > 0) {
            listaTarjetas.push({
                monto: m,
                referencia: r,
                tipo: t
            });
        }
    });

    // Recoger transferencia bancaria
    let montoTransf = parseFloat($('#txtMontoTransferencia').val()) || 0;
    let bancoTransf = ($('#selBancoTransferencia').val() || '').trim();
    let refTransf = ($('#txtRefTransferencia').val() || '').trim();

    if (montoTransf > 0 && refTransf === '') {
        showCobroToast("Debe ingresar el número de referencia para la transferencia.", "warning");
        $('#txtRefTransferencia').focus();
        return;
    }

    let btn = $('#btnConfirmarCobro');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Registrando Pago...');

    let payload = {
        docEntry: docEntry,
        efectivo: efectivo,
        tarjetas: listaTarjetas,
        tarjeta: listaTarjetas.length > 0 ? listaTarjetas[0].monto : 0, // retrocompatible
        transferencia: {
            monto: montoTransf,
            banco: bancoTransf,
            referencia: refTransf
        },
        esPendiente: esPendiente
    };

    $.ajax({
        url: 'index.php?action=guardar_cobro',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        dataType: 'json',
        success: function(res) {
            showCobroToast(res.msg || "Cobro registrado correctamente.", "success");
            setTimeout(function() {
                window.location.href = 'index.php?page=facturacion';
            }, 1200);
        },
        error: function(err) {
            btn.prop('disabled', false).html('<i class="bi bi-check2-circle fs-5"></i> Registrar Cobro &amp; Finalizar');
            let msg = "Error al procesar el cobro.";
            if(err.responseText) {
                try {
                    let parsed = JSON.parse(err.responseText);
                    msg = parsed.msg || msg;
                } catch(e) {}
            }
            showCobroToast(msg, "danger");
        }
    });
}

// Inicializar estado al cargar
calcularVuelto();
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>