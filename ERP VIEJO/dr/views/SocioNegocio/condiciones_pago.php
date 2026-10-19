<?php require_once __DIR__ . '/../Estructura/header.php'; ?>

<!-- Estilos exclusivos del módulo Condiciones de Pago -->
<style>
    <?php include __DIR__ . '/styles/condiciones_pago.css'; ?>
</style>

<div class="condiciones-container">
    <!-- Header de la Pantalla -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-calendar2-week-fill"></i>
            </div>
            <div>
                <h2 class="page-title">Condiciones de Pago</h2>
                <p class="page-subtitle">Configuración de plazos comerciales, crédito y días de gracia</p>
            </div>
        </div>

        <button type="button" class="btn-primary-action" onclick="limpiarForm()">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Nueva Condición</span>
        </button>
    </div>

    <!-- Mensajes de Sistema -->
    <?php echo Helper::getMensaje(); ?>

    <!-- Layout Dividido: Formulario y Tabla -->
    <div class="split-layout-grid">
        <!-- FORMULARIO: Crear / Editar Condición -->
        <div class="form-card-box">
            <div class="form-card-header">
                <h4 class="form-card-title" id="titulo_form">
                    <i class="bi bi-clock-history"></i>
                    <span>Datos de la Condición</span>
                </h4>
            </div>
            <div class="form-card-body">
                <form action="index.php?action=guardar_condicion_pago" method="POST" id="formCondicion">
                    <input type="hidden" name="codigo_original" id="codigo_original" value="">

                    <!-- Campo visible solo al editar para referencia del código SAP -->
                    <div class="field-group d-none" id="divGroupNum">
                        <label class="field-label">Código</label>
                        <div class="field-input-wrap">
                            <i class="bi bi-hash field-icon"></i>
                            <input type="text" id="GroupNumVisual" class="field-input text-muted fw-bold" readonly>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Nombre de la Condición<span class="required">*</span></label>
                        <div class="field-input-wrap">
                            <i class="bi bi-card-text field-icon"></i>
                            <input type="text" name="PymntGroup" id="PymntGroup" class="field-input" maxlength="100" required placeholder="Ej: Contado, Crédito 30 Días">
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Días Adicionales / Gracia</label>
                        <div class="field-input-wrap">
                            <i class="bi bi-calendar-plus field-icon"></i>
                            <input type="number" name="ExtraDays" id="ExtraDays" class="field-input" min="0" value="0" placeholder="0">
                        </div>
                        <div class="field-hint">Días adicionales aplicados al vencimiento estándar de la factura.</div>
                    </div>

                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" class="btn-primary-action w-100 justify-content-center" id="btnGuardar">
                            <i class="bi bi-save"></i>
                            <span>Guardar Condición</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarForm()" title="Limpiar formulario">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLA: Condiciones Registradas con Filtro en Vivo -->
        <div class="modern-table-card">
            <div class="modern-table-header">
                <h3 class="modern-table-title">
                    <i class="bi bi-list-check text-primary"></i>
                    <span>Condiciones Registradas (<?php echo count($condiciones); ?>)</span>
                </h3>
                <div style="width: 240px;">
                    <input type="text" id="filtroCondiciones" class="field-input" style="height: 36px; padding-left: 12px;" placeholder="Buscar condición..." oninput="filtrarCondiciones()">
                </div>
            </div>

            <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                <table class="custom-table" id="tablaCondiciones">
                    <thead>
                        <tr>
                            <th style="width: 80px; text-align: center;">Código</th>
                            <th>Descripción Comercial</th>
                            <th style="width: 120px; text-align: center;">Días Extra</th>
                            <th style="width: 80px; text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyCondiciones">
                        <?php if(empty($condiciones)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No hay condiciones de pago registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($condiciones as $c): ?>
                                <tr class="fila-condicion" data-nombre="<?php echo strtolower(htmlspecialchars($c['PymntGroup'] ?? '')); ?>" data-codigo="<?php echo htmlspecialchars($c['GroupNum'] ?? ''); ?>">
                                    <td class="fw-bold text-center text-primary" style="font-family: monospace;">
                                        #<?php echo htmlspecialchars($c['GroupNum']); ?>
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <?php echo htmlspecialchars($c['PymntGroup']); ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-2 py-1 fw-bold">
                                            <?php echo htmlspecialchars($c['ExtraDays'] ?? '0'); ?> días
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" 
                                                class="btn-table-action" 
                                                title="Editar condición"
                                                onclick='editarCondicion(<?php echo htmlspecialchars(json_encode($c)); ?>)'>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
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

<script>
    // Edición de condición
    function editarCondicion(data) {
        document.getElementById('codigo_original').value = data.GroupNum;
        document.getElementById('PymntGroup').value = data.PymntGroup;
        document.getElementById('ExtraDays').value = data.ExtraDays || 0;
        
        // Mostrar código de referencia visual
        document.getElementById('GroupNumVisual').value = data.GroupNum;
        document.getElementById('divGroupNum').classList.remove('d-none');

        document.getElementById('titulo_form').innerHTML = '<i class="bi bi-pencil-square"></i> <span>Modificar Condición #' + data.GroupNum + '</span>';
        document.getElementById('btnGuardar').innerHTML = '<i class="bi bi-arrow-repeat"></i> <span>Actualizar Condición</span>';
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Limpiar formulario para nueva condición
    function limpiarForm() {
        document.getElementById('formCondicion').reset();
        document.getElementById('codigo_original').value = '';
        document.getElementById('ExtraDays').value = 0;
        
        document.getElementById('divGroupNum').classList.add('d-none');
        document.getElementById('titulo_form').innerHTML = '<i class="bi bi-clock-history"></i> <span>Datos de la Condición</span>';
        document.getElementById('btnGuardar').innerHTML = '<i class="bi bi-save"></i> <span>Guardar Condición</span>';
    }

    // Filtro en vivo de la tabla
    function filtrarCondiciones() {
        const query = document.getElementById('filtroCondiciones').value.toLowerCase().trim();
        const filas = document.querySelectorAll('.fila-condicion');
        filas.forEach(fila => {
            const nombre = fila.getAttribute('data-nombre');
            const codigo = fila.getAttribute('data-codigo');
            if (!query || nombre.includes(query) || codigo.includes(query)) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>