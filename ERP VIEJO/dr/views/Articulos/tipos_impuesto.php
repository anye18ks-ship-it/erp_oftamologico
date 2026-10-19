<?php require_once __DIR__ . '/../Estructura/header.php'; ?>

<!-- Estilos exclusivos del módulo Tipos de Impuesto -->
<style>
    <?php include __DIR__ . '/styles/tipos_impuesto.css'; ?>
</style>

<div class="impuestos-container">
    <!-- Header de la Pantalla -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-percent"></i>
            </div>
            <div>
                <h2 class="page-title">Tipos de Impuestos</h2>
                <p class="page-subtitle">Configuración fiscal de tasas impositivas, ITBIS y exenciones</p>
            </div>
        </div>

        <button type="button" class="btn-primary-action" onclick="limpiarForm()">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Nuevo Impuesto</span>
        </button>
    </div>

    <!-- Mensajes de Sistema -->
    <?php echo Helper::getMensaje(); ?>

    <!-- Layout Dividido: Formulario y Tabla -->
    <div class="split-layout-grid">
        <!-- FORMULARIO: Crear / Editar Impuesto -->
        <div class="form-card-box">
            <div class="form-card-header">
                <h4 class="form-card-title" id="titulo_form">
                    <i class="bi bi-receipt"></i>
                    <span>Datos del Impuesto</span>
                </h4>
            </div>
            <div class="form-card-body">
                <form action="index.php?action=guardar_impuesto" method="POST" id="formImpuesto" onsubmit="return validarFormImpuesto()">
                    <input type="hidden" name="codigo_original" id="codigo_original" value="">

                    <div class="field-group">
                        <label class="field-label">Código del Impuesto<span class="required">*</span></label>
                        <div class="field-input-wrap">
                            <i class="bi bi-hash field-icon"></i>
                            <input type="text" name="Code" id="Code" class="field-input text-uppercase fw-bold" 
                                   maxlength="8" required placeholder="Ej: ITBIS18, EXENTO">
                        </div>
                        <div class="field-hint" id="hintCodigo">Máximo 8 caracteres. Clave fiscal única.</div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Descripción Fiscal<span class="required">*</span></label>
                        <div class="field-input-wrap">
                            <i class="bi bi-card-text field-icon"></i>
                            <input type="text" name="Name" id="Name" class="field-input fw-semibold" 
                                   maxlength="100" required placeholder="Ej: ITBIS 18% General, Bienes Exentos">
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Tasa Porcentual</label>
                        <div class="field-input-wrap">
                            <i class="bi bi-percent field-icon"></i>
                            <input type="number" step="0.000001" min="0" max="100" name="Rate" id="Rate" class="field-input fw-bold" 
                                   placeholder="Ej: 18.00">
                        </div>
                        <div class="field-hint">Porcentaje aplicable al cálculo del impuesto (Ej: 18 para 18%).</div>
                    </div>

                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" class="btn-primary-action w-100 justify-content-center" id="btnGuardar">
                            <i class="bi bi-save"></i>
                            <span>Guardar Impuesto</span>
                        </button>
                        <button type="button" class="btn-outline-secondary" onclick="limpiarForm()" title="Limpiar formulario">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLA: Impuestos Registrados con Filtro en Vivo -->
        <div class="modern-table-card">
            <div class="modern-table-header">
                <h3 class="modern-table-title">
                    <i class="bi bi-list-check text-primary"></i>
                    <span>Impuestos Registrados (<?php echo count($impuestos); ?>)</span>
                </h3>
                <div style="width: 240px;">
                    <input type="text" id="filtroImpuestos" class="field-input" style="height: 36px; padding-left: 12px;" placeholder="Buscar impuesto..." oninput="filtrarImpuestos()">
                </div>
            </div>

            <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                <table class="custom-table" id="tablaImpuestos">
                    <thead>
                        <tr>
                            <th style="width: 120px; text-align: center;">Código</th>
                            <th>Descripción Fiscal</th>
                            <th style="width: 120px; text-align: right;">Tasa (%)</th>
                            <th style="width: 80px; text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyImpuestos">
                        <?php if(empty($impuestos)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No hay tipos de impuesto registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($impuestos as $i): ?>
                                <tr class="fila-impuesto" 
                                    data-nombre="<?php echo strtolower(htmlspecialchars($i['Name'] ?? '')); ?>" 
                                    data-codigo="<?php echo strtolower(htmlspecialchars($i['Code'] ?? '')); ?>">
                                    <td class="fw-bold text-center text-primary" style="font-family: monospace;">
                                        <?php echo htmlspecialchars($i['Code']); ?>
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <?php echo htmlspecialchars($i['Name']); ?>
                                    </td>
                                    <td style="text-align: right;">
                                        <span class="rate-badge">
                                            <?php echo number_format((float)($i['Rate'] ?? 0), 2); ?>%
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" 
                                                class="btn-table-action" 
                                                title="Editar impuesto"
                                                onclick='editarImpuesto(<?php echo htmlspecialchars(json_encode($i)); ?>)'>
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
    // Auto-dismiss alertas
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(el) {
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(() => el.remove(), 600);
        });
    }, 5000);

    function editarImpuesto(data) {
        document.getElementById('codigo_original').value = data.Code;
        document.getElementById('Code').value = data.Code;
        document.getElementById('Name').value = data.Name;
        document.getElementById('Rate').value = data.Rate || '';
        
        // Bloquear código al editar
        const inputCod = document.getElementById('Code');
        inputCod.setAttribute('readonly', true);
        document.getElementById('hintCodigo').textContent = 'El código del impuesto no puede modificarse en modo edición.';

        document.getElementById('titulo_form').innerHTML = '<i class="bi bi-pencil me-2"></i><span>Modificar Impuesto: ' + data.Code + '</span>';
        document.getElementById('btnGuardar').innerHTML = '<i class="bi bi-check2-circle"></i> <span>Actualizar Impuesto</span>';
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function limpiarForm() {
        document.getElementById('formImpuesto').reset();
        document.getElementById('codigo_original').value = '';
        
        const inputCod = document.getElementById('Code');
        inputCod.removeAttribute('readonly');
        document.getElementById('hintCodigo').textContent = 'Máximo 8 caracteres. Clave fiscal única.';

        document.getElementById('titulo_form').innerHTML = '<i class="bi bi-receipt me-2"></i><span>Datos del Impuesto</span>';
        document.getElementById('btnGuardar').innerHTML = '<i class="bi bi-save"></i> <span>Guardar Impuesto</span>';
    }

    function filtrarImpuestos() {
        const query = document.getElementById('filtroImpuestos').value.toLowerCase().trim();
        const filas = document.querySelectorAll('.fila-impuesto');
        
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

    function validarFormImpuesto() {
        const cod = document.getElementById('Code').value.trim();
        const nom = document.getElementById('Name').value.trim();

        if (!cod) {
            alert('Por favor ingrese el código del impuesto.');
            document.getElementById('Code').focus();
            return false;
        }

        if (!nom) {
            alert('Por favor ingrese la descripción del impuesto.');
            document.getElementById('Name').focus();
            return false;
        }

        return true;
    }
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>