<!-- ============================================================
     PESTAÑA 3: PENDIENTES POR COBRAR A LA ARS & TRAZABILIDAD
     Archivo modular: views/Facturacion/partials/tab_gestion_cobro_ars.php
     ============================================================ -->
<div class="tab-pane-ars" id="tab-cobro-ars">
    
    <!-- Barra de Filtros para Gestión de Cobro ARS -->
    <div class="consulta-filter-bar mb-3">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-shaded text-success fs-5"></i>
            <span class="fw-bold text-dark">Facturas Pendientes por Cobrar a Aseguradoras (ARS)</span>
        </div>

        <div class="filter-controls-group">
            <div class="filter-date-group">
                <span class="filter-date-label"><i class="bi bi-hospital me-1"></i>ARS:</span>
                <select id="filtroARSCobro" class="filter-date-input" onchange="filtrarCobroARS()" style="min-width: 170px;">
                    <option value="TODAS">-- Todas las ARS --</option>
                    <?php if (!empty($listaARS)): ?>
                        <?php foreach ($listaARS as $ars): ?>
                            <option value="<?php echo htmlspecialchars($ars['CardCode']); ?>">
                                <?php echo htmlspecialchars($ars['CardName']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="filter-date-group">
                <span class="filter-date-label"><i class="bi bi-funnel me-1"></i>Estado:</span>
                <select id="filtroEstadoCobro" class="filter-date-input" onchange="filtrarCobroARS()" style="min-width: 140px;">
                    <option value="TODOS">-- Todos los Estados --</option>
                    <option value="PENDIENTE">PENDIENTES DE COBRO</option>
                    <option value="COBRADA">COBRADAS / PAGADAS</option>
                    <option value="RECHAZADA">RECHAZADAS / GLOSADAS</option>
                    <option value="NOTA_CREDITO">CON NOTA DE CRÉDITO</option>
                    <option value="CANCELADA">CANCELADAS</option>
                </select>
            </div>

            <div class="filter-date-group">
                <span class="filter-date-label"><i class="bi bi-calendar-event me-1"></i>Desde:</span>
                <input type="date" id="filtroDesdeCobro" class="filter-date-input" onchange="filtrarCobroARS()">
            </div>
            <div class="filter-date-group">
                <span class="filter-date-label"><i class="bi bi-calendar-event me-1"></i>Hasta:</span>
                <input type="date" id="filtroHastaCobro" class="filter-date-input" onchange="filtrarCobroARS()">
            </div>
            <button type="button" onclick="limpiarFiltrosCobroARS()" class="btn-filter-clear">
                <i class="bi bi-x-circle text-secondary"></i> Limpiar
            </button>
        </div>
    </div>

    <!-- Barra de Acciones en Lote para Gestión de Cobro -->
    <div class="batch-action-bar batch-action-bar-cobro mb-3">
        <div class="batch-stats-wrap">
            <div class="batch-stat-item">
                <i class="bi bi-check2-square fs-5 text-success"></i>
                <span>Seleccionadas:</span>
                <span class="batch-stat-val text-success" id="lblCantCobroSeleccionadas">0</span>
            </div>
            <div class="batch-stat-item">
                <i class="bi bi-cash-stack fs-5 text-success"></i>
                <span>Monto Seleccionado:</span>
                <span class="batch-stat-val text-success font-monospace" id="lblMontoCobroSeleccionado">RD$ 0.00</span>
            </div>
            <div class="batch-stat-item" id="lblARSCobroSeleccionadaWrap" style="display: none;">
                <i class="bi bi-hospital fs-5 text-primary"></i>
                <span>ARS:</span>
                <strong class="text-dark" id="lblARSCobroSeleccionadaNombre">-</strong>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Botón 1: Marcar como Cobradas -->
            <button type="button" class="btn-batch-cobrada" id="btnMarcarCobradaARS" onclick="abrirModalConfirmarCobro()" disabled>
                <i class="bi bi-check-circle-fill"></i> Marcar como Cobradas
            </button>

            <!-- Botón 2: Marcar como Rechazadas -->
            <button type="button" class="btn-batch-rechazada" id="btnMarcarRechazadaARS" onclick="abrirModalConfirmarRechazo()" disabled>
                <i class="bi bi-x-octagon-fill"></i> Marcar como Rechazadas
            </button>

            <!-- Botón 3: Aplicar Nota de Crédito en Lote (Solo para facturas rechazadas) -->
            <button type="button" class="btn-batch-nc" id="btnAplicarNCLoteARS" onclick="prepararNotaCreditoLoteARS()" disabled title="Seleccione facturas rechazadas para emitir Nota de Crédito">
                <i class="bi bi-file-earmark-minus-fill"></i> Aplicar Nota de Crédito
            </button>
        </div>
    </div>

    <!-- Tabla de Facturas ARS y Cobros (Estilo exacto a Pendientes y Facturadas) -->
    <div class="consulta-card">
        <div class="consulta-card-body">
            <div class="table-responsive">
                <table id="tablaCobroARS" class="table table-hover align-middle table-sm" style="width:100%">
                    <thead>
                        <tr>
                            <th style="width: 4%; text-align: center;">
                                <input type="checkbox" id="chkSelectAllCobro" class="check-aprobacion" onchange="toggleSelectAllCobro(this)" title="Seleccionar todas las visibles">
                            </th>
                            <th style="width: 5%;">ID</th>
                            <th style="width: 14%;">FACTURA / NCF ARS</th>
                            <th style="width: 9%;">FECHA</th>
                            <th style="width: 17%;">ASEGURADORA (ARS)</th>
                            <th style="width: 10%; text-align: center;">AUTORIZ.</th>
                            <th style="width: 11%;" class="text-end">MONTO ARS</th>
                            <th style="width: 8%;" class="text-center">ESTADO</th>
                            <th style="width: 12%;">ÚLTIMA ACCIÓN</th>
                            <th style="width: 10%;" class="text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($facturasCobroARS)): ?>
                            <?php foreach ($facturasCobroARS as $fc): 
                                $docEntryFac = (int)$fc['C_DocEntry'];
                                $estadoCobro = strtoupper($fc['EstadoCobro'] ?? 'PENDIENTE');
                                $isCanceled = ($fc['CANCELED'] === 'Y');
                                
                                // Determinar si es seleccionable (solo facturas pendientes o rechazadas sin NC emitida)
                                $esSeleccionable = (!$isCanceled && empty($fc['NCF_NC']) && ($estadoCobro === 'PENDIENTE' || $estadoCobro === 'RECHAZADA'));
                            ?>
                                <tr data-arscode="<?php echo htmlspecialchars($fc['CardCode']); ?>"
                                    data-arsname="<?php echo htmlspecialchars($fc['CardName']); ?>"
                                    data-fecha="<?php echo htmlspecialchars(substr($fc['DocDate'], 0, 10)); ?>"
                                    data-estado="<?php echo htmlspecialchars($isCanceled ? 'CANCELADA' : $estadoCobro); ?>"
                                    class="<?php echo $isCanceled ? 'table-row-canceled' : ($estadoCobro === 'COBRADA' ? 'table-row-cobrada' : ($estadoCobro === 'RECHAZADA' ? 'table-row-rechazada' : '')); ?>">
                                    
                                    <!-- 1. Checkbox de Selección -->
                                    <td class="text-center">
                                        <?php if ($esSeleccionable): ?>
                                            <input type="checkbox" class="check-aprobacion check-fila-cobro"
                                                   value="<?php echo $docEntryFac; ?>"
                                                   data-docentry="<?php echo $docEntryFac; ?>"
                                                   data-numatcard="<?php echo htmlspecialchars($fc['NumAtCard']); ?>"
                                                   data-ncf="<?php echo htmlspecialchars($fc['U_FACNUM']); ?>"
                                                   data-arscode="<?php echo htmlspecialchars($fc['CardCode']); ?>"
                                                   data-arsname="<?php echo htmlspecialchars($fc['CardName']); ?>"
                                                   data-monto="<?php echo (float)$fc['DocTotal']; ?>"
                                                   data-estado="<?php echo $estadoCobro; ?>"
                                                   onchange="actualizarSeleccionCobro()">
                                        <?php else: ?>
                                            <i class="bi bi-lock-fill text-muted" title="<?php echo $isCanceled ? 'Factura Cancelada' : 'Factura procesada (' . $estadoCobro . ')'; ?>" style="font-size: 0.85rem;"></i>
                                        <?php endif; ?>
                                    </td>

                                    <!-- 2. ID -->
                                    <td class="font-monospace text-muted"><?php echo $docEntryFac; ?></td>

                                    <!-- 3. NCF / Factura -->
                                    <td>
                                        <?php if (!empty($fc['U_FACNUM'])): ?>
                                            <span class="badge bg-primary-subtle text-primary border font-monospace fw-bold <?php echo $isCanceled ? 'text-decoration-line-through text-danger' : ''; ?>" style="font-size: 0.78rem;">
                                                <i class="bi bi-receipt me-1"></i><?php echo htmlspecialchars($fc['U_FACNUM']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-factura font-monospace"><?php echo htmlspecialchars($fc['NumAtCard'] ?: '#' . $fc['DocNum']); ?></span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- 4. Fecha -->
                                    <td class="font-monospace text-secondary"><?php echo htmlspecialchars($fc['FechaDocFormato'] ?? date('d/m/Y', strtotime($fc['DocDate']))); ?></td>

                                    <!-- 5. Aseguradora -->
                                    <td>
                                        <span class="badge-ars-pill">
                                            <?php echo htmlspecialchars($fc['CardName']); ?>
                                        </span>
                                    </td>

                                    <!-- 6. Autorizaciones Incluidas -->
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.76rem;">
                                            <?php echo (int)($fc['CantidadAprobaciones'] ?? 0); ?> autoriz.
                                        </span>
                                    </td>

                                    <!-- 7. Total Factura -->
                                    <td class="text-end font-monospace fw-bold <?php echo $isCanceled ? 'text-decoration-line-through text-danger' : 'text-success'; ?>">
                                        RD$ <?php echo number_format((float)$fc['DocTotal'], 2); ?>
                                    </td>

                                    <!-- 8. Estado de Cobro -->
                                    <td class="text-center">
                                        <?php if ($isCanceled): ?>
                                            <span class="badge-status badge-status-anulado">Cancelada</span>
                                        <?php elseif ($estadoCobro === 'COBRADA'): ?>
                                            <span class="badge-status badge-status-facturado">Cobrada</span>
                                        <?php elseif ($estadoCobro === 'RECHAZADA'): ?>
                                            <span class="badge-status badge-status-anulado">Rechazada</span>
                                        <?php elseif ($estadoCobro === 'NOTA_CREDITO'): ?>
                                            <span class="badge-status badge-status-nc">Nota Crédito</span>
                                        <?php else: ?>
                                            <span class="badge-status badge-status-pendiente">Pendiente</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- 9. Última Acción / Trazabilidad -->
                                    <td>
                                        <?php if (!empty($fc['FechaEstadoFormato'])): ?>
                                            <div class="font-monospace text-secondary" style="font-size: 0.76rem;"><?php echo htmlspecialchars($fc['FechaEstadoFormato']); ?></div>
                                            <div class="text-muted" style="font-size: 0.72rem;">Por: <strong><?php echo htmlspecialchars($fc['UsuarioAccion'] ?? 'SYS'); ?></strong></div>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 0.78rem;">-</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- 10. Acciones (Iconos compactos con tooltips) -->
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1 align-items-center">
                                            <!-- Ver Trazabilidad -->
                                            <button type="button" class="btn-action-trazabilidad"
                                                    onclick="consultarTrazabilidadFactura(<?php echo $docEntryFac; ?>)"
                                                    title="Ver Historial y Trazabilidad">
                                                <i class="bi bi-clock-history"></i>
                                            </button>

                                            <!-- Ver Factura ARS -->
                                            <a href="index.php?action=reporte_fact_aprobacion&docEntry=<?php echo $docEntryFac; ?>" target="_blank"
                                               class="btn-action-factura-ars" title="Imprimir Factura Especial ARS">
                                                <i class="bi bi-file-earmark-pdf"></i>
                                            </a>

                                            <!-- Relación de Pacientes -->
                                            <a href="index.php?action=reporte_aprobacion_ars&docEntry=<?php echo $docEntryFac; ?>" target="_blank"
                                               class="btn-action-relacion" title="Imprimir Relación de Reclamaciones (PDF)">
                                                <i class="bi bi-list-columns-reverse"></i>
                                            </a>

                                            <!-- Botón de Nota de Crédito: SOLO VISIBLE SI LA FACTURA ES RECHAZADA -->
                                            <?php if ($estadoCobro === 'RECHAZADA' && empty($fc['NCF_NC']) && !$isCanceled): ?>
                                                <button type="button" class="btn-action-nc"
                                                        onclick="aplicarNCIndividual(<?php echo $docEntryFac; ?>, '<?php echo htmlspecialchars($fc['CardCode']); ?>', '<?php echo htmlspecialchars($fc['CardName']); ?>', '<?php echo htmlspecialchars($fc['U_FACNUM'] ?: $fc['NumAtCard']); ?>', <?php echo (float)$fc['DocTotal']; ?>)"
                                                        title="Emitir Nota de Crédito por Glosa">
                                                    <i class="bi bi-file-earmark-minus-fill"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL 1: CONFIRMAR COBRO ARS (AMPLIO, ESPACIOSO Y RESPONSIVO)
     ============================================================ -->
<div class="modal fade" id="modalConfirmarCobroARS" tabindex="-1" aria-labelledby="modalCobroLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 bg-white bg-opacity-25 text-white">
                        <i class="bi bi-cash-coin fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalCobroLabel">Confirmar Cobro de Facturas ARS</h5>
                        <small class="text-white-50">Registro de pago liquidado por la Aseguradora</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="alert alert-success d-flex align-items-center gap-2 mb-4 py-2">
                    <i class="bi bi-info-circle-fill fs-5"></i>
                    <div class="small">
                        ¿Está seguro que desea marcar las facturas seleccionadas como <strong>COBRADAS / PAGADAS</strong> por la Aseguradora?
                    </div>
                </div>

                <!-- Resumen de facturas seleccionadas en grid espacioso -->
                <div class="p-3 bg-light rounded-3 border mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-muted small fw-bold text-uppercase d-block" style="font-size: 0.72rem;">Aseguradora:</span>
                            <strong class="text-dark fs-6" id="cobro_modal_ars_nombre">-</strong>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small fw-bold text-uppercase d-block" style="font-size: 0.72rem;">Facturas:</span>
                            <span class="badge bg-success fs-6 px-3 py-1" id="cobro_modal_cant">0 facturas</span>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <span class="text-muted small fw-bold text-uppercase d-block" style="font-size: 0.72rem;">Total Liquidado:</span>
                            <strong class="text-success font-monospace fs-5" id="cobro_modal_monto">RD$ 0.00</strong>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="bi bi-receipt-cutoff text-success me-1"></i> Referencia de Pago / Depósito / Cheque:
                        </label>
                        <input type="text" id="cobro_modal_referencia" class="form-control" placeholder="Ej: Transf. Banreservas #89234, Cheque #004523, Depósito ARS..." style="font-size: 0.88rem;">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                            <i class="bi bi-calendar-event text-success me-1"></i> Fecha de Cobro / Depósito:
                        </label>
                        <input type="date" id="cobro_modal_fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" style="font-size: 0.88rem;">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                        <i class="bi bi-chat-left-text text-success me-1"></i> Observaciones / Notas de Liquidación:
                    </label>
                    <textarea id="cobro_modal_observaciones" class="form-control" rows="3" placeholder="Detalle adicional sobre la liquidación o referencia contable..." style="font-size: 0.88rem;"></textarea>
                </div>
            </div>

            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-outline-secondary fw-bold rounded-3 px-4 py-2" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-success fw-bold rounded-3 px-4 py-2" id="btnConfirmarCobroFinal" onclick="ejecutarMarcarCobroARS()">
                    <i class="bi bi-check2-circle me-1"></i> Confirmar Cobro ARS
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL 2: CONFIRMAR RECHAZO ARS (AMPLIO, ESPACIOSO Y RESPONSIVO)
     ============================================================ -->
<div class="modal fade" id="modalConfirmarRechazoARS" tabindex="-1" aria-labelledby="modalRechazoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 bg-white bg-opacity-25 text-white">
                        <i class="bi bi-x-octagon-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalRechazoLabel">Marcar Facturas como RECHAZADAS por ARS</h5>
                        <small class="text-white-50">Registro formal de glosa médica o rechazo de liquidación</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-4 py-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div class="small">
                        ¿Está seguro que desea marcar las facturas como <strong>RECHAZADAS / GLOSADAS</strong>? Posteriormente podrá emitir la Nota de Crédito correspondiente.
                    </div>
                </div>

                <!-- Resumen de facturas a rechazar -->
                <div class="p-3 bg-light rounded-3 border mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-muted small fw-bold text-uppercase d-block" style="font-size: 0.72rem;">Aseguradora:</span>
                            <strong class="text-dark fs-6" id="rechazo_modal_ars_nombre">-</strong>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small fw-bold text-uppercase d-block" style="font-size: 0.72rem;">Facturas:</span>
                            <span class="badge bg-danger fs-6 px-3 py-1" id="rechazo_modal_cant">0 facturas</span>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <span class="text-muted small fw-bold text-uppercase d-block" style="font-size: 0.72rem;">Total Rechazado:</span>
                            <strong class="text-danger font-monospace fs-5" id="rechazo_modal_monto">RD$ 0.00</strong>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                        <i class="bi bi-tag-fill text-danger me-1"></i> Motivo de Glosa / Rechazo Médico <span class="text-danger">*</span>:
                    </label>
                    <select id="rechazo_modal_motivo" class="form-select" style="font-size: 0.88rem;">
                        <option value="">-- Seleccione el motivo estándar de rechazo / glosa --</option>
                        <option value="Glosa médica - Servicio no autorizado por ARS">Glosa médica - Servicio no autorizado por ARS</option>
                        <option value="Documentación clínica / Expediente incompleto">Documentación clínica / Expediente incompleto</option>
                        <option value="Afiliado inactivo o sin cobertura al momento de la atención">Afiliado inactivo o sin cobertura al momento de la atención</option>
                        <option value="Tarifa no convenida / Diferencia en contrato">Tarifa no convenida / Diferencia en contrato</option>
                        <option value="Doble reclamación / Servicio ya facturado anteriormente">Doble reclamación / Servicio ya facturado anteriormente</option>
                        <option value="Falta de pre-autorización o código de autorización inválido">Falta de pre-autorización o código de autorización inválido</option>
                        <option value="Servicio no cubierto en el plan del afiliado">Servicio no cubierto en el plan del afiliado</option>
                        <option value="Error en datos del paciente / Número de carnet incorrecto">Error en datos del paciente / Número de carnet incorrecto</option>
                        <option value="Rechazo administrativo general de la aseguradora">Rechazo administrativo general de la aseguradora</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-dark mb-1" style="font-size: 0.85rem;">
                        <i class="bi bi-chat-left-text-fill text-danger me-1"></i> Observaciones Adicionales / Instrucciones:
                    </label>
                    <textarea id="rechazo_modal_observaciones" class="form-control" rows="3" placeholder="Comentarios de auditoría médica o instrucciones de anulación..." style="font-size: 0.88rem;"></textarea>
                </div>

                <div class="p-3 bg-light rounded-3 border">
                    <div class="form-check d-flex align-items-center gap-2 mb-0">
                        <input class="form-check-input mt-0" type="checkbox" id="chkIrANotaCreditoInmediata" checked style="width: 19px; height: 19px; cursor: pointer;">
                        <label class="form-check-label fw-bold text-dark" for="chkIrANotaCreditoInmediata" style="font-size: 0.88rem; cursor: pointer;">
                            <i class="bi bi-arrow-right-circle-fill text-primary me-1"></i> Proceder a emitir Nota de Crédito inmediatamente tras guardar el rechazo
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-outline-secondary fw-bold rounded-3 px-4 py-2" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger fw-bold rounded-3 px-4 py-2" id="btnConfirmarRechazoFinal" onclick="ejecutarMarcarRechazoARS()">
                    <i class="bi bi-x-octagon-fill me-1"></i> Confirmar Rechazo ARS
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL 3: ALERTA DE AGRUPACIÓN / INCOMPATIBILIDAD FISCAL (NC EN LOTE)
     ============================================================ -->
<div class="modal fade" id="modalAlertaIncompatibilidadNC" tabindex="-1" aria-labelledby="modalIncompLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-warning text-dark px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 bg-dark bg-opacity-10 text-dark">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalIncompLabel">Incompatibilidad de Facturas para Nota de Crédito</h5>
                        <small class="text-dark-50">Solo se pueden agrupar facturas de la misma ARS y del mismo tipo de comprobante (DGII)</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <p class="text-dark mb-3">
                    Por normativa fiscal (DGII) y control contable, una <strong>Nota de Crédito</strong> solo puede emitirse a una <strong>misma Aseguradora</strong> y para un <strong>mismo tipo de comprobante</strong> (los e-CF <strong>E31</strong> requieren NC <strong>E34</strong>, mientras que las facturas tradicionales <strong>B01</strong> requieren NC <strong>B04</strong>).
                </p>

                <div class="p-3 bg-light rounded-3 border mb-3">
                    <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-collection-fill text-primary me-1"></i> Grupos de Facturas Detectados:</h6>
                    <div class="d-flex flex-column gap-2" id="listaIncompatibilidadARS">
                        <!-- Generado dinámicamente -->
                    </div>
                </div>

                <div class="alert alert-info py-2 small mb-0">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    Haga clic en el botón <strong>"Procesar este grupo"</strong> de la opción que desee emitir ahora. Las demás facturas podrán ser procesadas posteriormente por separado.
                </div>
            </div>

            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-outline-secondary fw-bold rounded-3 px-4 py-2" data-bs-dismiss="modal">
                    Cerrar y Revisar Selección
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL 4: TRAZABILIDAD & LÍNEA DE TIEMPO COMPLETA DE FACTURA
     ============================================================ -->
<div class="modal fade" id="modalTrazabilidadARS" tabindex="-1" aria-labelledby="modalTrazLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 bg-primary rounded-3 text-white">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalTrazLabel">Línea de Tiempo y Trazabilidad de Factura ARS</h5>
                        <small class="text-white-50">Historial completo del ciclo de reclamación médica</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Ficha Resumen de la Factura -->
                <div class="p-3 bg-light rounded-3 border mb-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <span class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700;">NCF Factura:</span>
                            <h5 class="text-primary font-monospace fw-bold mb-0" id="traz_ncf">-</h5>
                            <small class="text-muted font-monospace" id="traz_numatcard">-</small>
                        </div>
                        <div class="col-md-5">
                            <span class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700;">Aseguradora:</span>
                            <div class="fw-bold text-dark" id="traz_ars_nombre">-</div>
                            <small class="text-muted font-monospace" id="traz_ars_code">-</small>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <span class="text-muted text-uppercase" style="font-size: 0.72rem; font-weight: 700;">Monto Facturado:</span>
                            <h5 class="text-dark font-monospace fw-bold mb-0" id="traz_monto">RD$ 0.00</h5>
                            <span class="badge" id="traz_badge_estado">-</span>
                        </div>
                    </div>
                </div>

                <!-- Línea de Tiempo de Eventos -->
                <h6 class="fw-bold text-dark mb-3">
                    <i class="bi bi-diagram-3-fill text-primary me-1"></i> Ciclo de Vida & Eventos Registrados
                </h6>

                <div class="timeline-ars-wrapper mb-4">
                    <div class="timeline-ars" id="timelineEventosARS">
                        <!-- Generado dinámicamente -->
                    </div>
                </div>

                <!-- Desglose de Aprobaciones / Pacientes del Lote -->
                <div class="border rounded-3 overflow-hidden">
                    <div class="bg-light p-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark small"><i class="bi bi-people-fill text-primary me-1"></i> Pacientes / Autorizaciones Incluidas en este Lote</span>
                        <span class="badge bg-secondary" id="traz_cant_aprobaciones">0</span>
                    </div>
                    <div class="table-responsive" style="max-height: 180px; overflow-y: auto;">
                        <table class="table table-sm align-middle mb-0" style="font-size: 0.8rem;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>N° Autorización</th>
                                    <th>Paciente</th>
                                    <th>N° Afiliado</th>
                                    <th>Servicio</th>
                                    <th class="text-end">Monto Reclamado</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyTrazAprobaciones">
                                <!-- Filas de autorizaciones -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light px-4 py-3">
                <button type="button" class="btn btn-secondary fw-bold rounded-3 px-4 py-2" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
