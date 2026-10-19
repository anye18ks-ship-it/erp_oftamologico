<?php require_once __DIR__ . '/../Estructura/header.php'; ?>

<!-- Estilos exclusivos del módulo Almacenes -->
<style>
    <?php include __DIR__ . '/styles/almacenes.css'; ?>
</style>

<div class="almacenes-container">
    <!-- Header de la Pantalla -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-buildings-fill"></i>
            </div>
            <div>
                <h2 class="page-title">Almacenes y Depósitos</h2>
                <p class="page-subtitle">Gestión de centros de almacenamiento, sucursales y ubicaciones físicas</p>
            </div>
        </div>

        <button type="button" class="btn-primary-action" onclick="limpiarForm()">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Nuevo Almacén</span>
        </button>
    </div>

    <!-- Mensajes de Sistema -->
    <?php echo Helper::getMensaje(); ?>

    <!-- Layout Dividido: Formulario y Tabla -->
    <div class="split-layout-grid">
        <!-- FORMULARIO: Crear / Editar Almacén -->
        <div class="form-card-box">
            <div class="form-card-header">
                <h4 class="form-card-title" id="titulo_form">
                    <i class="bi bi-building"></i>
                    <span>Datos del Almacén</span>
                </h4>
            </div>
            <div class="form-card-body">
                <form action="index.php?action=guardar_almacen" method="POST" id="formAlmacen" onsubmit="return validarFormAlmacen()">
                    <!-- Campo oculto para saber si estamos actualizando -->
                    <input type="hidden" name="codigo_original" id="codigo_original" value="">

                    <div class="field-group">
                        <label class="field-label">Código de Almacén<span class="required">*</span></label>
                        <div class="field-input-wrap">
                            <i class="bi bi-hash field-icon"></i>
                            <input type="text" name="WhsCode" id="WhsCode" class="field-input text-uppercase fw-bold" 
                                   maxlength="8" required placeholder="Ej: 01, ALM01, PTP">
                        </div>
                        <div class="field-hint" id="hintCodigo">Máximo 8 caracteres alfanuméricos. Clave única del almacén.</div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Nombre / Ubicación<span class="required">*</span></label>
                        <div class="field-input-wrap">
                            <i class="bi bi-geo-alt field-icon"></i>
                            <input type="text" name="WhsName" id="WhsName" class="field-input fw-semibold" 
                                   maxlength="100" required placeholder="Ej: Almacén Principal, Farmacia Central">
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" class="btn-primary-action w-100 justify-content-center" id="btnGuardar">
                            <i class="bi bi-save"></i>
                            <span>Guardar Almacén</span>
                        </button>
                        <button type="button" class="btn-outline-secondary" onclick="limpiarForm()" title="Limpiar formulario">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLA: Almacenes Registrados con Filtro en Vivo -->
        <div class="modern-table-card">
            <div class="modern-table-header">
                <h3 class="modern-table-title">
                    <i class="bi bi-list-check text-primary"></i>
                    <span>Almacenes Registrados (<?php echo count($almacenes); ?>)</span>
                </h3>
                <div style="width: 240px;">
                    <input type="text" id="filtroAlmacenes" class="field-input" style="height: 36px; padding-left: 12px;" placeholder="Buscar almacén..." oninput="filtrarAlmacenes()">
                </div>
            </div>

            <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                <table class="custom-table" id="tablaAlmacenes">
                    <thead>
                        <tr>
                            <th style="width: 120px; text-align: center;">Código</th>
                            <th>Nombre / Descripción del Almacén</th>
                            <th style="width: 80px; text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyAlmacenes">
                        <?php if(empty($almacenes)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No hay almacenes registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($almacenes as $a): ?>
                                <tr class="fila-almacen" 
                                    data-nombre="<?php echo strtolower(htmlspecialchars($a['WhsName'] ?? '')); ?>" 
                                    data-codigo="<?php echo strtolower(htmlspecialchars($a['WhsCode'] ?? '')); ?>">
                                    <td class="fw-bold text-center text-primary" style="font-family: monospace;">
                                        <?php echo htmlspecialchars($a['WhsCode']); ?>
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-box-seam text-muted"></i>
                                            <span><?php echo htmlspecialchars($a['WhsName']); ?></span>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" 
                                                class="btn-table-action" 
                                                title="Editar almacén"
                                                onclick='editarAlmacen(<?php echo htmlspecialchars(json_encode($a)); ?>)'>
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

    function editarAlmacen(data) {
        document.getElementById('codigo_original').value = data.WhsCode;
        document.getElementById('WhsCode').value = data.WhsCode;
        document.getElementById('WhsName').value = data.WhsName;
        
        // Bloquear código al editar
        const inputCod = document.getElementById('WhsCode');
        inputCod.setAttribute('readonly', true);
        document.getElementById('hintCodigo').textContent = 'El código del almacén no puede modificarse en modo edición.';

        document.getElementById('titulo_form').innerHTML = '<i class="bi bi-pencil me-2"></i><span>Modificar Almacén: ' + data.WhsCode + '</span>';
        document.getElementById('btnGuardar').innerHTML = '<i class="bi bi-check2-circle"></i> <span>Actualizar Almacén</span>';
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function limpiarForm() {
        document.getElementById('formAlmacen').reset();
        document.getElementById('codigo_original').value = '';
        
        const inputCod = document.getElementById('WhsCode');
        inputCod.removeAttribute('readonly');
        document.getElementById('hintCodigo').textContent = 'Máximo 8 caracteres alfanuméricos. Clave única del almacén.';

        document.getElementById('titulo_form').innerHTML = '<i class="bi bi-building me-2"></i><span>Datos del Almacén</span>';
        document.getElementById('btnGuardar').innerHTML = '<i class="bi bi-save"></i> <span>Guardar Almacén</span>';
    }

    function filtrarAlmacenes() {
        const query = document.getElementById('filtroAlmacenes').value.toLowerCase().trim();
        const filas = document.querySelectorAll('.fila-almacen');
        
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

    function validarFormAlmacen() {
        const cod = document.getElementById('WhsCode').value.trim();
        const nom = document.getElementById('WhsName').value.trim();

        if (!cod) {
            alert('Por favor ingrese el código del almacén.');
            document.getElementById('WhsCode').focus();
            return false;
        }

        if (!nom) {
            alert('Por favor ingrese el nombre del almacén.');
            document.getElementById('WhsName').focus();
            return false;
        }

        return true;
    }
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>