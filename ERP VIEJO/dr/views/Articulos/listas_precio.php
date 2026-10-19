<?php require_once __DIR__ . '/../Estructura/header.php'; ?>

<!-- Estilos exclusivos del módulo Listas de Precios -->
<style>
    <?php include __DIR__ . '/styles/listas_precio.css'; ?>
</style>

<div class="listas-container">
    <!-- Header de la Pantalla -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-tags-fill"></i>
            </div>
            <div>
                <h2 class="page-title">Listas de Precios</h2>
                <p class="page-subtitle">Configuración de tarifas comerciales, listas base y factores de cálculo</p>
            </div>
        </div>

        <button type="button" class="btn-primary-action" onclick="limpiarForm()">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Nueva Lista</span>
        </button>
    </div>

    <!-- Mensajes de Sistema -->
    <?php echo Helper::getMensaje(); ?>

    <!-- Layout Dividido: Formulario y Tabla -->
    <div class="split-layout-grid">
        <!-- FORMULARIO: Crear / Editar Lista de Precios -->
        <div class="form-card-box">
            <div class="form-card-header">
                <h4 class="form-card-title" id="titulo_form">
                    <i class="bi bi-tag"></i>
                    <span>Datos de la Lista</span>
                </h4>
            </div>
            <div class="form-card-body">
                <form action="index.php?action=guardar_lista" method="POST" id="formLista" onsubmit="return validarFormLista()">
                    <input type="hidden" name="ListNum" id="ListNum" value="">

                    <!-- Campo visible solo al editar para referencia del código SAP -->
                    <div class="field-group d-none" id="divListNumVisual">
                        <label class="field-label">Código SAP (ListNum)</label>
                        <div class="field-input-wrap">
                            <i class="bi bi-hash field-icon"></i>
                            <input type="text" id="ListNumVisual" class="field-input text-muted fw-bold" readonly>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Nombre de la Lista<span class="required">*</span></label>
                        <div class="field-input-wrap">
                            <i class="bi bi-card-text field-icon"></i>
                            <input type="text" name="ListName" id="ListName" class="field-input" maxlength="32" required placeholder="Ej: General, Mayorista, Seguro">
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Lista Base</label>
                        <div class="field-input-wrap">
                            <i class="bi bi-diagram-3 field-icon"></i>
                            <input type="number" name="BASE_NUM" id="BASE_NUM" class="field-input" min="1" placeholder="N° de lista base (opcional)">
                        </div>
                        <div class="field-hint">Código de la lista de precio que sirve como referencia base.</div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Factor Multiplicador</label>
                        <div class="field-input-wrap">
                            <i class="bi bi-percent field-icon"></i>
                            <input type="number" step="0.000001" name="Factor" id="Factor" class="field-input" placeholder="Ej: 1.000000">
                        </div>
                        <div class="field-hint">Multiplicador aplicado sobre el precio de la lista base.</div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Configuración Operativa</label>
                        <label class="switch-item" for="IsGrossPrc">
                            <input type="checkbox" name="IsGrossPrc" id="IsGrossPrc">
                            <span class="switch-label-text">Precio Incluye Impuesto (Precio Bruto)</span>
                        </label>

                        <label class="switch-item" for="ValidFor">
                            <input type="checkbox" name="ValidFor" id="ValidFor" checked>
                            <span class="switch-label-text">Lista Vigente / Activa</span>
                        </label>
                    </div>

                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" class="btn-primary-action w-100 justify-content-center" id="btnGuardar">
                            <i class="bi bi-save"></i>
                            <span>Guardar Lista</span>
                        </button>
                        <button type="button" class="btn-outline-secondary" onclick="limpiarForm()" title="Limpiar formulario">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLA: Listas Registradas con Filtro en Vivo -->
        <div class="modern-table-card">
            <div class="modern-table-header">
                <h3 class="modern-table-title">
                    <i class="bi bi-list-check text-primary"></i>
                    <span>Listas Registradas (<?php echo count($listas); ?>)</span>
                </h3>
                <div style="width: 240px;">
                    <input type="text" id="filtroListas" class="field-input" style="height: 36px; padding-left: 12px;" placeholder="Buscar lista..." oninput="filtrarListas()">
                </div>
            </div>

            <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                <table class="custom-table" id="tablaListas">
                    <thead>
                        <tr>
                            <th style="width: 80px; text-align: center;">Código</th>
                            <th>Nombre de Tarifa</th>
                            <th style="width: 100px; text-align: center;">Base</th>
                            <th style="width: 100px; text-align: right;">Factor</th>
                            <th style="width: 100px; text-align: center;">Bruto</th>
                            <th style="width: 100px; text-align: center;">Estado</th>
                            <th style="width: 80px; text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyListas">
                        <?php if(empty($listas)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No hay listas de precios registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($listas as $l): 
                                $esVigente = (($l['ValidFor'] ?? 'Y') === 'Y');
                                $esBruto = (($l['IsGrossPrc'] ?? 'N') === 'Y');
                            ?>
                                <tr class="fila-lista" 
                                    data-nombre="<?php echo strtolower(htmlspecialchars($l['ListName'] ?? '')); ?>" 
                                    data-codigo="<?php echo htmlspecialchars($l['ListNum'] ?? ''); ?>">
                                    <td class="fw-bold text-center text-primary" style="font-family: monospace;">
                                        #<?php echo htmlspecialchars($l['ListNum']); ?>
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <?php echo htmlspecialchars($l['ListName']); ?>
                                    </td>
                                    <td class="text-center text-muted font-monospace">
                                        <?php echo !empty($l['BASE_NUM']) ? '#' . htmlspecialchars($l['BASE_NUM']) : '—'; ?>
                                    </td>
                                    <td style="text-align: right; font-family: monospace; font-weight: 600;">
                                        <?php echo isset($l['Factor']) ? number_format((float)$l['Factor'], 2) : '1.00'; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if($esBruto): ?>
                                            <span class="badge bg-light text-dark border px-2 py-1 fw-semibold">Bruto</span>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 0.8rem;">Neto</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if($esVigente): ?>
                                            <span class="status-pill status-active"><span class="status-dot"></span> Activa</span>
                                        <?php else: ?>
                                            <span class="status-pill status-inactive"><span class="status-dot"></span> Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" 
                                                class="btn-table-action" 
                                                title="Editar lista"
                                                onclick='editarLista(<?php echo htmlspecialchars(json_encode($l)); ?>)'>
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

    function editarLista(data) {
        document.getElementById('ListNum').value = data.ListNum;
        document.getElementById('ListNumVisual').value = '#' + data.ListNum;
        document.getElementById('divListNumVisual').classList.remove('d-none');
        document.getElementById('ListName').value = data.ListName;
        document.getElementById('BASE_NUM').value = data.BASE_NUM || '';
        document.getElementById('Factor').value = data.Factor || '';
        
        document.getElementById('IsGrossPrc').checked = (data.IsGrossPrc === 'Y');
        document.getElementById('ValidFor').checked = (data.ValidFor === 'Y');

        document.getElementById('titulo_form').innerHTML = '<i class="bi bi-pencil me-2"></i><span>Modificar Lista #' + data.ListNum + '</span>';
        document.getElementById('btnGuardar').innerHTML = '<i class="bi bi-check2-circle"></i> <span>Actualizar Lista</span>';
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function limpiarForm() {
        document.getElementById('formLista').reset();
        document.getElementById('ListNum').value = '';
        document.getElementById('divListNumVisual').classList.add('d-none');
        document.getElementById('ValidFor').checked = true;
        document.getElementById('titulo_form').innerHTML = '<i class="bi bi-tag me-2"></i><span>Datos de la Lista</span>';
        document.getElementById('btnGuardar').innerHTML = '<i class="bi bi-save"></i> <span>Guardar Lista</span>';
    }

    function filtrarListas() {
        const query = document.getElementById('filtroListas').value.toLowerCase().trim();
        const filas = document.querySelectorAll('.fila-lista');
        
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

    function validarFormLista() {
        const nombre = document.getElementById('ListName').value.trim();
        if (!nombre) {
            alert('Por favor ingrese el nombre de la lista de precios.');
            document.getElementById('ListName').focus();
            return false;
        }
        return true;
    }
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>