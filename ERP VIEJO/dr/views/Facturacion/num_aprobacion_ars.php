<?php
// El modal se embebe en factura_venta.php. Si se accede directo por ruta, se muestra con header/footer.
$esModoStandalone = defined('EMBEDDED_ARS_MODAL') ? false : (!isset($page) || $page !== 'aprobaciones_ars' ? false : true);
if ($esModoStandalone) {
    require_once __DIR__ . '/../Estructura/header.php';
}
?>
<link rel="stylesheet" href="views/Facturacion/styles/num_aprobacion_ars.css">

<!-- ============================================================
     MODAL OVERLAY: AUTORIZACIÓN Y APROBACIÓN DE SEGURO ARS
     ============================================================ -->
<div class="modal-ars-overlay" id="modalAprobacionARS" tabindex="-1" role="dialog" aria-modal="true">
    <div class="modal-ars-dialog" role="document">
        
        <!-- Header -->
        <div class="modal-ars-header">
            <div class="modal-ars-title-group">
                <div class="modal-ars-header-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <h4 class="modal-ars-title">Aprobación de Cobertura ARS / Seguro</h4>
                    <p class="modal-ars-subtitle">Registro y validación de autorización médica de la aseguradora</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php if (($page ?? '') !== 'consulta_aprobaciones'): ?>
                <a href="index.php?page=consulta_aprobaciones" class="btn btn-sm btn-outline-light d-none d-md-inline-flex align-items-center gap-1 rounded-3" target="_blank" title="Ver Historial y Generar Reportes de Reclamaciones">
                    <i class="bi bi-file-earmark-pdf"></i> Reportes ARS
                </a>
                <?php endif; ?>
                <button type="button" class="btn-close-ars" onclick="cerrarModalARS()" title="Cerrar ventana">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="modal-ars-body">
            
            <!-- TARJETA INFORMATIVA DEL PACIENTE Y ASEGURADORA (AUTOMÁTICOS) -->
            <div class="ars-patient-card">
                <div class="ars-info-item">
                    <span class="ars-info-label"><i class="bi bi-person-fill text-success"></i> Paciente / Socio</span>
                    <span class="ars-info-value" id="lblArsCardName">Seleccione paciente...</span>
                    <small class="text-muted font-monospace" id="lblArsCardCode">-</small>
                </div>
                
                <div class="ars-info-item">
                    <span class="ars-info-label"><i class="bi bi-folder2-open text-success"></i> N° Expediente</span>
                    <span class="ars-info-value font-monospace" id="lblArsNumExpediente">-</span>
                </div>

                <div class="ars-info-item">
                    <span class="ars-info-label"><i class="bi bi-credit-card-2-front-fill text-success"></i> N° Afiliado / Carnet</span>
                    <span class="ars-info-value font-monospace text-primary" id="lblArsNumAfiliado">-</span>
                </div>

                <div class="ars-info-item">
                    <span class="ars-info-label"><i class="bi bi-hospital-fill text-success"></i> Aseguradora (ARS)</span>
                    <span class="ars-info-value" id="lblArsNombreARS">
                        <span class="badge-ars" id="lblArsBadgeARS">Sin Seguro Asignado</span>
                    </span>
                    <small class="text-muted font-monospace" id="lblArsCardCodeARS"></small>
                </div>
            </div>

            <!-- FORMULARIO DE CAPTURA MANUAL DE AUTORIZACIÓN -->
            <form id="formAprobacionARS" onsubmit="event.preventDefault(); aplicarAprobacionARS();">
                
                <!-- Campos ocultos del paciente/socio y estado de edición -->
                <input type="hidden" id="ars_IdARS" name="IdARS" value="">
                <input type="hidden" id="ars_Status" name="Status" value="P">
                <input type="hidden" id="ars_TotalFacturaBase" value="0">
                <input type="hidden" id="ars_TipoSocio" name="TipoSocio">
                <input type="hidden" id="ars_CardCode" name="CardCode">
                <input type="hidden" id="ars_CardName" name="CardName">
                <input type="hidden" id="ars_NumExpediente" name="NumExpediente">
                <input type="hidden" id="ars_NumAfiliado" name="NumAfiliado">
                <input type="hidden" id="ars_CardCode_ARS" name="CardCode_ARS">


                <!-- SECCIÓN 1: DATOS DE LA APROBACIÓN -->
                <div class="ars-form-section-title">
                    <i class="bi bi-check2-circle"></i> Datos de la Autorización Emitida
                </div>

                <div class="row g-3 mb-3">
                    <!-- Número de Aprobación -->
                    <div class="col-md-6">
                        <label class="ars-label" for="ars_NumAprobacion">
                            <i class="bi bi-hash"></i> N° de Aprobación ARS <span class="req">*</span>
                        </label>
                        <input type="text" id="ars_NumAprobacion" name="NumAprobacion" class="ars-input font-monospace" 
                               placeholder="Ej: AUT-2026-98432" required autocomplete="off">
                    </div>

                    <!-- Tipo de Servicio Cubierto -->
                    <div class="col-md-6">
                        <label class="ars-label" for="ars_TipoServicio">
                            <i class="bi bi-clipboard2-pulse"></i> Tipo de Servicio Cubierto
                        </label>
                        <select id="ars_TipoServicio" name="TipoServicio" class="ars-input">
                            <option value="CONSULTA MEDICA">Consulta Medica Especializada</option>
                            <option value="PROCEDIMIENTO / CIRUGIA">Procedimiento Quirurgico / Cirugia</option>
                            <option value="DIAGNOSTICO / ESTUDIOS">Diagnostico / Estudios Especiales</option>
                            <option value="MATERIALES MEDICOS">Materiales Medicos / Insumos</option>
                            <option value="OTRO">Otro Servicio Clinico</option>
                        </select>
                    </div>

                    <!-- Fecha/Hora de Aprobación -->
                    <div class="col-md-6">
                        <label class="ars-label" for="ars_FechaAprobacionT">
                            <i class="bi bi-calendar-check"></i> Fecha / Hora de Aprobación
                        </label>
                        <input type="datetime-local" id="ars_FechaAprobacionT" name="FechaAprobacionT" class="ars-input">
                    </div>

                    <!-- Fecha/Hora de Vencimiento -->
                    <div class="col-md-6">
                        <label class="ars-label" for="ars_FechaVencimientoT">
                            <i class="bi bi-calendar-x"></i> Fecha / Hora de Vencimiento
                        </label>
                        <input type="datetime-local" id="ars_FechaVencimientoT" name="FechaVencimientoT" class="ars-input">
                    </div>
                </div>

                <!-- SECCIÓN 2: COBERTURA Y MONTOS FINANCIEROS -->
                <div class="ars-form-section-title d-flex justify-content-between align-items-center">
                    <div><i class="bi bi-cash-coin"></i> Cobertura & Copago del Paciente</div>
                    <span id="badgeArsModoEdicion" class="badge bg-secondary-subtle text-secondary border font-monospace d-none" style="font-size: 0.72rem; text-transform: none;">
                        <i class="bi bi-lock-fill me-1"></i>Montos Bloqueados en Edición
                    </span>
                </div>

                <div class="row g-3 mb-3">
                    <!-- % Cobertura -->
                    <div class="col-md-4">
                        <label class="ars-label" for="ars_PorcentajeCober">
                            <i class="bi bi-percent"></i>Cobertura ARS
                            <i class="bi bi-lock-fill text-muted ms-auto ars-lock-icon d-none" title="Bloqueado"></i>
                        </label>
                        <div class="input-group">
                            <input type="number" id="ars_PorcentajeCober" name="PorcentajeCober" class="ars-input text-center font-monospace" 
                                   min="0" max="100" step="0.01" placeholder="80.00" oninput="calcularMontosARS('porcentaje')">
                        </div>
                        <div class="d-flex gap-1 mt-1" id="ars_botones_porcentaje">
                            <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 font-monospace fw-bold" style="font-size: 0.70rem;" onclick="$('#ars_PorcentajeCober').val(100); calcularMontosARS('porcentaje');" title="El seguro cubre el 100% (Sin copago)">
                                <i class="bi bi-shield-fill-check me-1"></i>100% (Total)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 font-monospace" style="font-size: 0.70rem;" onclick="$('#ars_PorcentajeCober').val(80); calcularMontosARS('porcentaje');">80%</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 font-monospace" style="font-size: 0.70rem;" onclick="$('#ars_PorcentajeCober').val(70); calcularMontosARS('porcentaje');">70%</button>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.72rem;" id="lblArsAyudaCobertura">En base al total de la factura</small>
                    </div>

                    <!-- Monto Aprobado (Cubre Seguro) -->
                    <div class="col-md-4">
                        <label class="ars-label" for="ars_MontoAprobado">
                            <i class="bi bi-shield-check"></i> Monto Cubierto ARS (RD$)
                            <i class="bi bi-lock-fill text-muted ms-auto ars-lock-icon d-none" title="Bloqueado"></i>
                        </label>
                        <input type="number" id="ars_MontoAprobado" name="MontoAprobado" class="ars-input text-end font-monospace" 
                               min="0" step="0.01" placeholder="0.00" oninput="calcularMontosARS('monto')">
                        <small class="text-muted" style="font-size: 0.72rem;">Monto autorizado por la aseguradora</small>
                    </div>

                    <!-- Monto Responsable / Diferencia Paciente -->
                    <div class="col-md-4">
                        <label class="ars-label" for="ars_MontoResponsable">
                            <i class="bi bi-person-fill-check"></i> Copago Paciente (RD$)
                            <i class="bi bi-lock-fill text-muted ms-auto ars-lock-icon d-none" title="Bloqueado"></i>
                        </label>
                        <input type="number" id="ars_MontoResponsable" name="MontoResponsable" class="ars-input text-end font-monospace" 
                               min="0" step="0.01" placeholder="0.00" oninput="calcularMontosARS('responsable')">
                        <small class="text-muted" style="font-size: 0.72rem;">Diferencia después de la cobertura</small>
                    </div>
                </div>

                <!-- BANNER EN VIVO DE CÁLCULO -->
                <div class="ars-cobertura-banner">
                    <div class="ars-calc-col">
                        <div class="ars-calc-label">Total Factura</div>
                        <div class="ars-calc-value total" id="lblArsTotalFactura">RD$ 0.00</div>
                    </div>
                    <div class="ars-calc-col">
                        <div class="ars-calc-label"><i class="bi bi-shield-fill-check text-success"></i> Cobertura ARS</div>
                        <div class="ars-calc-value text-success" id="lblArsMontoCobertura">RD$ 0.00 (0%)</div>
                    </div>
                    <div class="ars-calc-col">
                        <div class="ars-calc-label"><i class="bi bi-person-fill text-primary"></i> Diferencia / Paciente</div>
                        <div class="ars-calc-value paciente" id="lblArsMontoDiferencia">RD$ 0.00</div>
                    </div>
                </div>

                <!-- SECCIÓN 3: OBSERVACIONES -->
                <div class="mb-2">
                    <label class="ars-label" for="ars_Observaciones">
                        <i class="bi bi-chat-left-text"></i> Observaciones Adicionales
                    </label>
                    <textarea id="ars_Observaciones" name="Observaciones" class="ars-textarea" 
                              placeholder="Notas sobre exclusiones, deducibles, requisitos específicos del seguro..."></textarea>
                </div>

            </form>

        </div>

        <!-- Footer / Acciones -->
        <div class="modal-ars-footer">
            <div>
                <button type="button" class="btn-ars-clear" onclick="limpiarAprobacionARS()" title="Quitar autorización">
                    <i class="bi bi-trash3"></i> Quitar Aprobación
                </button>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn-ars-cancel" onclick="cerrarModalARS()">
                    Cancelar
                </button>
                <button type="button" class="btn-ars-apply" onclick="aplicarAprobacionARS()">
                    <i class="bi bi-check2-circle fs-5"></i> Aplicar y Guardar
                </button>
            </div>
        </div>

    </div>
</div>

<script>
// Objeto de estado de la aprobación ARS activa
let payloadAprobacionARS = null;

// Formatear fecha y hora para datetime-local (YYYY-MM-DDTHH:mm)
function obtenerFechaHoraActualLocal(diasAdicionales = 0) {
    let now = new Date();
    if (diasAdicionales > 0) {
        now.setDate(now.getDate() + diasAdicionales);
    }
    let year = now.getFullYear();
    let month = String(now.getMonth() + 1).padStart(2, '0');
    let day = String(now.getDate()).padStart(2, '0');
    let hours = String(now.getHours()).padStart(2, '0');
    let minutes = String(now.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// Convertir fecha de BD (YYYY-MM-DD o YYYY-MM-DD HH:mm:ss) a formato datetime-local
function formatearParaDateTimeLocal(fechaStr) {
    if (!fechaStr) return '';
    let limpia = String(fechaStr).trim().replace(' ', 'T');
    if (limpia.length >= 16) {
        return limpia.substring(0, 16);
    }
    if (limpia.length === 10) {
        return limpia + 'T12:00';
    }
    return limpia;
}

// ============================================================
// ABRIR MODAL EN MODO EDICIÓN DIRECTA EN BD (consulta_aprobaciones)
// ============================================================
function abrirModalEditarAprobacionARS(data) {
    if (!data) return;

    // Configurar ID y estado para modo actualización en BD
    $('#ars_IdARS').val(data.IdARS);
    $('#ars_Status').val(data.Status || 'P');

    // Ajustar encabezados y botones para modo edición
    $('.modal-ars-title').html('<i class="bi bi-pencil-square me-2 text-warning"></i>Editar Aprobación ARS #' + data.IdARS);
    $('.modal-ars-subtitle').text('Modifique los datos de autorización antes de facturar el lote a la aseguradora');
    $('.btn-ars-clear').hide();
    $('.btn-ars-apply').html('<i class="bi bi-check2-circle fs-5"></i> Guardar Cambios');

    // Datos del socio / paciente (valores fijos)
    $('#ars_TipoSocio').val(data.TipoSocio || 'P');
    $('#ars_CardCode').val(data.CardCode || '');
    $('#ars_CardName').val(data.CardName || '');
    $('#ars_NumExpediente').val(data.NumExpediente || '');
    $('#ars_NumAfiliado').val(data.NumAfiliado || '');

    let codArs = data.CodigoARSReal || data.CardCode_ARS || '';
    $('#ars_CardCode_ARS').val(codArs);

    // Tarjeta visual informativa del paciente (solo lectura)
    $('#lblArsCardName').text(data.CardName || 'Sin Nombre');
    $('#lblArsCardCode').text(`Código: ${data.CardCode || '-'}`);
    $('#lblArsNumExpediente').text(data.NumExpediente ? `EXP-${data.NumExpediente}` : 'Sin Expediente');
    $('#lblArsNumAfiliado').text(data.NumAfiliado || 'Sin N° Afiliado');
    
    let nombreArs = data.NombreARS || codArs || 'Sin Seguro Asignado';
    $('#lblArsNombreARS').html(`<span class="badge-ars"><i class="bi bi-hospital me-1"></i>${nombreArs}</span>`);
    $('#lblArsCardCodeARS').text(codArs ? `ARS Cod: ${codArs}` : '');

    // Formulario de autorización
    $('#ars_NumAprobacion').val(data.NumAprobacion || '');

    // Fechas
    let fechaAprob = data.FechaAprobacionT || data.FechaAprobacionD || '';
    $('#ars_FechaAprobacionT').val(formatearParaDateTimeLocal(fechaAprob) || obtenerFechaHoraActualLocal(0));

    let fechaVenc = data.FechaVencimientoT || data.FechaVencimientoD || '';
    $('#ars_FechaVencimientoT').val(formatearParaDateTimeLocal(fechaVenc) || obtenerFechaHoraActualLocal(30));

    // Servicio
    let serv = (data.TipoServicio || 'CONSULTA MEDICA').toUpperCase()
        .replace(/Á/g, 'A').replace(/É/g, 'E').replace(/Í/g, 'I').replace(/Ó/g, 'O').replace(/Ú/g, 'U');
    $('#ars_TipoServicio').val(serv);
    if (!$('#ars_TipoServicio').val()) {
        $('#ars_TipoServicio option').each(function() {
            if (serv.indexOf($(this).val()) !== -1 || $(this).val().indexOf(serv) !== -1) {
                $('#ars_TipoServicio').val($(this).val());
                return false;
            }
        });
    }

    // Montos
    let mAprob = parseFloat(data.MontoAprobado) || 0;
    let mResp = parseFloat(data.MontoResponsable) || 0;
    let pct = parseFloat(data.PorcentajeCober) || 0;
    let totalBase = mAprob + mResp;

    $('#ars_TotalFacturaBase').val(totalBase);
    $('#ars_PorcentajeCober').val(pct > 0 ? pct.toFixed(2) : '');
    $('#ars_MontoAprobado').val(mAprob.toFixed(2));
    $('#ars_MontoResponsable').val(mResp.toFixed(2));

    // BLOQUEAR INPUTS FINANCIEROS EN MODO EDICIÓN
    $('#ars_PorcentajeCober, #ars_MontoAprobado, #ars_MontoResponsable')
        .prop('readonly', true)
        .addClass('ars-input-readonly');
    $('#ars_botones_porcentaje').hide();
    $('.ars-lock-icon').removeClass('d-none');
    $('#badgeArsModoEdicion').removeClass('d-none');
    $('#lblArsAyudaCobertura').text('Fijado por la factura emitida (no modificable)');

    $('#ars_Observaciones').val(data.Observaciones || '');

    // Recalcular banner visual en vivo
    calcularMontosARS('init');

    // Mostrar modal con overlay
    $('#modalAprobacionARS').addClass('active');
    setTimeout(() => $('#ars_NumAprobacion').focus(), 250);
}

// ============================================================
// ABRIR MODAL EN MODO NUEVA FACTURA (factura_venta)
// ============================================================
function abrirModalARS() {
    $('#ars_IdARS').val('');
    $('#ars_Status').val('P');
    $('.modal-ars-title').text('Aprobación de Cobertura ARS / Seguro');
    $('.modal-ars-subtitle').text('Registro y validación de autorización médica de la aseguradora');
    $('.btn-ars-clear').show();
    $('.btn-ars-apply').html('<i class="bi bi-check2-circle fs-5"></i> Aplicar y Guardar');

    let cardCode = $('#txtCardCode').val();
    let cardName = $('#txtCardName').val();

    if (!cardCode) {
        if (typeof showPosToast === 'function') {
            showPosToast("Debe seleccionar primero un paciente para registrar la aprobación de seguro.", "warning");
        } else {
            alert("Debe seleccionar primero un paciente.");
        }
        if (typeof abrirModalCliente === 'function') {
            abrirModalCliente();
        }
        return;
    }

    if (typeof detalleFactura !== 'undefined' && detalleFactura.length === 0) {
        if (typeof showPosToast === 'function') {
            showPosToast("Debe agregar al menos un artículo o servicio antes de aplicar la aprobación de seguro.", "warning");
        } else {
            alert("Debe agregar al menos un artículo o servicio.");
        }
        if (typeof abrirModalArticulo === 'function') {
            abrirModalArticulo();
        }
        return;
    }

    // Cargar datos del paciente en el modal
    let paciente = window.pacienteSeleccionadoARS || {};
    
    $('#ars_TipoSocio').val(paciente.CardType || 'P');
    $('#ars_CardCode').val(cardCode);
    $('#ars_CardName').val(cardName);
    $('#ars_NumExpediente').val(paciente.U_expediente || '');
    $('#ars_NumAfiliado').val(paciente.U_CadenaID || '');
    $('#ars_CardCode_ARS').val(paciente.U_SubGrupo || '');

    // Etiquetas informativas visuales
    $('#lblArsCardName').text(cardName);
    $('#lblArsCardCode').text(`Código: ${cardCode}`);
    $('#lblArsNumExpediente').text(paciente.U_expediente ? `EXP-${paciente.U_expediente}` : 'Sin Expediente');
    $('#lblArsNumAfiliado').text(paciente.U_CadenaID || 'Sin N° Afiliado');
    
    if (paciente.NombreARS || paciente.U_SubGrupo) {
        let nombreArs = paciente.NombreARS || paciente.U_SubGrupo;
        $('#lblArsNombreARS').html(`<span class="badge-ars"><i class="bi bi-hospital me-1"></i>${nombreArs}</span>`);
        $('#lblArsCardCodeARS').text(`ARS Cod: ${paciente.U_SubGrupo || ''}`);
    } else {
        $('#lblArsNombreARS').html('<span class="badge-ars" style="background:#fee2e2;color:#991b1b;border-color:#fca5a5;">Sin Seguro Asignado</span>');
        $('#lblArsCardCodeARS').text('');
    }

    // Inicializar fechas por defecto si están vacías
    if (!$('#ars_FechaAprobacionT').val()) {
        $('#ars_FechaAprobacionT').val(obtenerFechaHoraActualLocal(0));
    }
    if (!$('#ars_FechaVencimientoT').val()) {
        $('#ars_FechaVencimientoT').val(obtenerFechaHoraActualLocal(30)); // 30 días de vencimiento
    }

    // Restaurar si ya existía un payload previo
    if (payloadAprobacionARS) {
        $('#ars_NumAprobacion').val(payloadAprobacionARS.NumAprobacion || '');
        if (payloadAprobacionARS.FechaAprobacionT) $('#ars_FechaAprobacionT').val(payloadAprobacionARS.FechaAprobacionT);
        if (payloadAprobacionARS.FechaVencimientoT) $('#ars_FechaVencimientoT').val(payloadAprobacionARS.FechaVencimientoT);
        let tipoServVal = (payloadAprobacionARS.TipoServicio || 'CONSULTA MEDICA').toUpperCase()
            .replace(/Á/g, 'A').replace(/É/g, 'E').replace(/Í/g, 'I').replace(/Ó/g, 'O').replace(/Ú/g, 'U');
        $('#ars_TipoServicio').val(tipoServVal);
        if (!$('#ars_TipoServicio').val()) {
            $('#ars_TipoServicio').val('CONSULTA MEDICA');
        }
        $('#ars_PorcentajeCober').val(payloadAprobacionARS.PorcentajeCober || '');
        $('#ars_MontoAprobado').val(payloadAprobacionARS.MontoAprobado || '');
        $('#ars_MontoResponsable').val(payloadAprobacionARS.MontoResponsable || '');
        $('#ars_Observaciones').val(payloadAprobacionARS.Observaciones || '');
    }

    // DESBLOQUEAR INPUTS FINANCIEROS EN MODO NUEVA FACTURA
    $('#ars_PorcentajeCober, #ars_MontoAprobado, #ars_MontoResponsable')
        .prop('readonly', false)
        .removeClass('ars-input-readonly');
    $('#ars_botones_porcentaje').show();
    $('.ars-lock-icon').addClass('d-none');
    $('#badgeArsModoEdicion').addClass('d-none');
    $('#lblArsAyudaCobertura').text('En base al total de la factura');

    // Recalcular montos en base a la factura actual
    calcularMontosARS('init');

    // Mostrar modal con overlay
    $('#modalAprobacionARS').addClass('active');
    setTimeout(() => $('#ars_NumAprobacion').focus(), 250);
}

// Cerrar el Modal de Aprobación ARS
function cerrarModalARS() {
    $('#modalAprobacionARS').removeClass('active');
    $('#ars_IdARS').val('');
    $('#ars_PorcentajeCober, #ars_MontoAprobado, #ars_MontoResponsable')
        .prop('readonly', false)
        .removeClass('ars-input-readonly');
    $('#ars_botones_porcentaje').show();
    $('.ars-lock-icon').addClass('d-none');
    $('#badgeArsModoEdicion').addClass('d-none');
    $('#lblArsAyudaCobertura').text('En base al total de la factura');
}

// Obtener el total neto actual de la factura desde la interfaz o base guardada
function obtenerTotalFacturaActual() {
    if ($('#lblTotal').length > 0) {
        let totalTexto = $('#lblTotal').text() || '0';
        let totalLimpio = totalTexto.replace(/[^\d.-]/g, '');
        let total = parseFloat(totalLimpio) || 0;
        if (total > 0) return total;
    }
    let totalBase = parseFloat($('#ars_TotalFacturaBase').val()) || 0;
    if (totalBase > 0) return totalBase;

    let mAp = parseFloat($('#ars_MontoAprobado').val()) || 0;
    let mResp = parseFloat($('#ars_MontoResponsable').val()) || 0;
    return (mAp + mResp);
}

// Cálculo interactivo de Cobertura y Responsabilidad
function calcularMontosARS(origen) {
    let totalFactura = obtenerTotalFacturaActual();
    $('#lblArsTotalFactura').text('RD$ ' + totalFactura.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

    let pct = parseFloat($('#ars_PorcentajeCober').val()) || 0;
    let montoAprob = parseFloat($('#ars_MontoAprobado').val()) || 0;
    let montoResp = parseFloat($('#ars_MontoResponsable').val()) || 0;

    if (origen === 'porcentaje') {
        if (pct > 100) { pct = 100; $('#ars_PorcentajeCober').val(100); }
        if (pct < 0) { pct = 0; $('#ars_PorcentajeCober').val(0); }
        
        montoAprob = totalFactura * (pct / 100);
        montoResp = Math.max(0, totalFactura - montoAprob);
        
        $('#ars_MontoAprobado').val(montoAprob.toFixed(2));
        $('#ars_MontoResponsable').val(montoResp.toFixed(2));
    } else if (origen === 'monto') {
        if (totalFactura > 0) {
            pct = Math.min(100, (montoAprob / totalFactura) * 100);
            $('#ars_PorcentajeCober').val(pct.toFixed(2));
        }
        montoResp = Math.max(0, totalFactura - montoAprob);
        $('#ars_MontoResponsable').val(montoResp.toFixed(2));
    } else if (origen === 'responsable') {
        montoAprob = Math.max(0, totalFactura - montoResp);
        if (totalFactura > 0) {
            pct = Math.min(100, (montoAprob / totalFactura) * 100);
            $('#ars_PorcentajeCober').val(pct.toFixed(2));
        }
        $('#ars_MontoAprobado').val(montoAprob.toFixed(2));
    } else if (origen === 'init') {
        if (totalFactura > 0 && pct === 0 && montoAprob > 0) {
            pct = (montoAprob / totalFactura) * 100;
            $('#ars_PorcentajeCober').val(pct.toFixed(2));
        } else if (pct > 0 && montoAprob === 0 && totalFactura > 0) {
            montoAprob = totalFactura * (pct / 100);
            montoResp = Math.max(0, totalFactura - montoAprob);
            $('#ars_MontoAprobado').val(montoAprob.toFixed(2));
            $('#ars_MontoResponsable').val(montoResp.toFixed(2));
        }
    }

    // Actualizar banner visual
    $('#lblArsMontoCobertura').text(`RD$ ${montoAprob.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} (${pct.toFixed(1)}%)`);
    $('#lblArsMontoDiferencia').text('RD$ ' + montoResp.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
}

// Aplicar y Guardar (en BD si es edición o en memoria de factura)
function aplicarAprobacionARS() {
    let numAprob = $('#ars_NumAprobacion').val().trim();
    if (!numAprob) {
        $('#ars_NumAprobacion').focus();
        if (typeof showPosToast === 'function') {
            showPosToast("Ingrese el número de aprobación emitido por la ARS.", "warning");
        } else {
            alert("Ingrese el número de aprobación emitido por la ARS.");
        }
        return;
    }

    let fAprobT = $('#ars_FechaAprobacionT').val();
    let fVencT = $('#ars_FechaVencimientoT').val();
    let fAprobD = fAprobT ? fAprobT.substring(0, 10) : null;
    let fVencD = fVencT ? fVencT.substring(0, 10) : null;

    let montoAprobado = parseFloat($('#ars_MontoAprobado').val()) || 0;
    let porcentajeCober = parseFloat($('#ars_PorcentajeCober').val()) || 0;
    let montoResponsable = parseFloat($('#ars_MontoResponsable').val()) || 0;
    let numAfiliadoVal = $('#ars_NumAfiliado').val() || '';

    let idARS = parseInt($('#ars_IdARS').val()) || 0;

    // ── MODO EDICIÓN EN BASE DE DATOS (consulta_aprobaciones) ──
    if (idARS > 0) {
        let datosAprobacion = {
            IdARS: idARS,
            TipoSocio: $('#ars_TipoSocio').val() || 'P',
            CardCode: $('#ars_CardCode').val(),
            CardName: $('#ars_CardName').val(),
            NumExpediente: $('#ars_NumExpediente').val() ? parseInt($('#ars_NumExpediente').val()) : null,
            NumAfiliado: numAfiliadoVal,
            CardCode_ARS: $('#ars_CardCode_ARS').val(),
            NumAprobacion: numAprob,
            FechaAprobacionT: fAprobT ? fAprobT.replace('T', ' ') + ':00' : null,
            FechaAprobacionD: fAprobD,
            FechaVencimientoT: fVencT ? fVencT.replace('T', ' ') + ':00' : null,
            FechaVencimientoD: fVencD,
            MontoAprobado: montoAprobado,
            PorcentajeCober: porcentajeCober,
            MontoResponsable: montoResponsable,
            TipoServicio: $('#ars_TipoServicio').val(),
            Observaciones: $('#ars_Observaciones').val().trim(),
            Status: $('#ars_Status').val() || 'P'
        };

        let $btnGuardar = $('.btn-ars-apply');
        let txtOriginal = $btnGuardar.html();
        $btnGuardar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Guardando...');

        $.ajax({
            url: 'index.php?action=guardar_aprobacion_ars',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(datosAprobacion),
            dataType: 'json',
            success: function(resp) {
                $btnGuardar.prop('disabled', false).html(txtOriginal);
                if (resp && resp.status === 'success') {
                    cerrarModalARS();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Aprobación Actualizada!',
                            text: 'La autorización ' + numAprob + ' se actualizó correctamente en la base de datos.',
                            timer: 1600,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        location.reload();
                    }
                } else {
                    let errorMsg = (resp && resp.msg) ? resp.msg : 'Error al actualizar la aprobación.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', errorMsg, 'error');
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                }
            },
            error: function(xhr) {
                $btnGuardar.prop('disabled', false).html(txtOriginal);
                let errorMsg = 'Error en el servidor al actualizar la aprobación.';
                try {
                    let json = JSON.parse(xhr.responseText);
                    if (json && json.msg) errorMsg = json.msg;
                } catch(e) {}
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', errorMsg, 'error');
                } else {
                    alert('Error: ' + errorMsg);
                }
            }
        });
        return;
    }

    // ── MODO NUEVA FACTURA EN MEMORIA (factura_venta) ──
    payloadAprobacionARS = {
        TipoSocio: $('#ars_TipoSocio').val(),
        CardCode: $('#ars_CardCode').val(),
        CardName: $('#ars_CardName').val(),
        NumExpediente: $('#ars_NumExpediente').val() ? parseInt($('#ars_NumExpediente').val()) : null,
        NumAfiliado: numAfiliadoVal,
        CardCode_ARS: $('#ars_CardCode_ARS').val(),
        NumAprobacion: numAprob,
        FechaAprobacionT: fAprobT ? fAprobT.replace('T', ' ') + ':00' : null,
        FechaAprobacionD: fAprobD,
        FechaVencimientoT: fVencT ? fVencT.replace('T', ' ') + ':00' : null,
        FechaVencimientoD: fVencD,
        MontoAprobado: montoAprobado,
        PorcentajeCober: porcentajeCober,
        MontoResponsable: montoResponsable,
        TipoServicio: $('#ars_TipoServicio').val(),
        Observaciones: $('#ars_Observaciones').val().trim(),
        Status: 'P'
    };

    // Actualizar campo visible en la factura
    $('#txtNumAprobacion').val(numAprob);
    $('#txtNumAprobacion').addClass('input-num-aprobacion-trigger');

    // Badge visual "Autorizado"
    if ($('#badgeArsActivo').length === 0) {
        $('#txtNumAprobacion').parent().append('<span id="badgeArsActivo" class="badge-ars-activo" title="Aprobación ARS Activa"><i class="bi bi-shield-check"></i> Autorizado</span>');
    }

    // ── Mostrar fila de Copago en el resumen de totales ──
    if (montoResponsable > 0) {
        let copago = montoResponsable.toLocaleString('es-DO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        $('#lblCopagoTitulo').html('<i class="bi bi-shield-check me-1"></i>Copago Paciente (ARS):');
        $('#lblCopagoARS').text('RD$ ' + copago);
        $('#rowCopago').show();
        if (typeof actualizarBotonProcesar === 'function') {
            actualizarBotonProcesar(false, copago);
        }
    } else if (montoAprobado > 0) {
        // Cobertura 100% (Seguro cubre todo)
        $('#lblCopagoTitulo').html('<i class="bi bi-shield-fill-check me-1 text-success"></i>Cobertura ARS (100%):');
        $('#lblCopagoARS').html('<span class="badge bg-success font-monospace px-2 py-1"><i class="bi bi-check2-all me-1"></i>Totalmente Cubierto (RD$ 0.00)</span>');
        $('#rowCopago').show();
        if (typeof actualizarBotonProcesar === 'function') {
            actualizarBotonProcesar(true, '0.00');
        }
    } else {
        $('#rowCopago').hide();
        if (typeof actualizarBotonProcesar === 'function') {
            actualizarBotonProcesar(false, null);
        }
    }

    cerrarModalARS();
    if (typeof showPosToast === 'function') {
        if (montoResponsable === 0 && montoAprobado > 0) {
            showPosToast(`Aprobación ARS ${numAprob} aplicada con Cobertura 100%. Paciente no paga copago.`, "success");
        } else {
            showPosToast(`Aprobación ARS ${numAprob} aplicada. Copago paciente: RD$ ${montoResponsable.toLocaleString('es-DO', {minimumFractionDigits:2})}`, "success");
        }
    }
}

// Limpiar la Aprobación
function limpiarAprobacionARS() {
    payloadAprobacionARS = null;
    $('#ars_NumAprobacion').val('');
    $('#ars_PorcentajeCober').val('');
    $('#ars_MontoAprobado').val('');
    $('#ars_MontoResponsable').val('');
    $('#ars_Observaciones').val('');

    $('#txtNumAprobacion').val('');
    $('#txtNumAprobacion').removeClass('input-num-aprobacion-trigger');
    $('#badgeArsActivo').remove();

    // Ocultar fila de copago y restablecer botón
    $('#rowCopago').hide();
    $('#lblCopagoARS').text('RD$ 0.00');
    if (typeof actualizarBotonProcesar === 'function') {
        actualizarBotonProcesar(false, null);
    }

    cerrarModalARS();
    if (typeof showPosToast === 'function') {
        showPosToast("Aprobación de seguro removida.", "info");
    }
}

// Event Listeners seguros para cierre
document.addEventListener('DOMContentLoaded', function() {
    if (window.jQuery) {
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $('#modalAprobacionARS').hasClass('active')) {
                cerrarModalARS();
            }
        });

        $(document).on('click', '#modalAprobacionARS', function(e) {
            if ($(e.target).hasClass('modal-ars-overlay')) {
                cerrarModalARS();
            }
        });
    }
});
</script>

<?php 
if ($esModoStandalone) {
    require_once __DIR__ . '/../Estructura/footer.php'; 
}
?>