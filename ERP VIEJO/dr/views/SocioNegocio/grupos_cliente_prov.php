<?php require_once __DIR__ . '/../Estructura/header.php'; ?>

<!-- Estilos exclusivos del módulo Grupos de Socios -->
<style>
    <?php include __DIR__ . '/styles/grupos_cliente.css'; ?>
</style>

<div class="grupos-container">
    <!-- Header de la Pantalla -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-person-lines-fill"></i>
            </div>
            <div>
                <h2 class="page-title">Grupos de Socios</h2>
                <p class="page-subtitle">Categorías para clasificación comercial de clientes, pacientes y suplidores</p>
            </div>
        </div>

        <button type="button" class="btn-primary-action" onclick="limpiarForm()">
            <i class="bi bi-plus-circle-fill"></i>
            <span>Nuevo Grupo</span>
        </button>
    </div>

    <!-- Mensajes de Sistema -->
    <?php echo Helper::getMensaje(); ?>

    <!-- Layout Dividido: Formulario y Tabla -->
    <div class="split-layout-grid">
        <!-- FORMULARIO: Crear / Editar Grupo -->
        <div class="form-card-box">
            <div class="form-card-header">
                <h4 class="form-card-title" id="titulo_form">
                    <i class="bi bi-people"></i>
                    <span>Datos del Grupo</span>
                </h4>
            </div>
            <div class="form-card-body">
                <form action="index.php?action=guardar_grupo_cp" method="POST" id="formGrupo">
                    <input type="hidden" name="codigo_original" id="codigo_original" value="">

                    <!-- Campo visible solo al editar para referencia del código SAP -->
                    <div class="field-group d-none" id="divGroupCode">
                        <label class="field-label">Código</label>
                        <div class="field-input-wrap">
                            <i class="bi bi-hash field-icon"></i>
                            <input type="text" id="GroupCodeVisual" class="field-input text-muted fw-bold" readonly>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Nombre del Grupo<span class="required">*</span></label>
                        <div class="field-input-wrap">
                            <i class="bi bi-tag field-icon"></i>
                            <input type="text" name="GroupName" id="GroupName" class="field-input" maxlength="20" required placeholder="Ej: VIP, Asegurados, Mayoristas">
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Tipo de Grupo <span class="required">*</span></label>
                        <div class="field-input-wrap">
                            <i class="bi bi-diagram-3 field-icon"></i>
                            <select name="GroupType" id="GroupType" class="field-select" required>
                                <option value="">-- Seleccionar Clasificación --</option>
                                <option value="C">C — Clientes / Pacientes</option>
                                <option value="S">S — Suplidores / Proveedores</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-2">
                        <button type="submit" class="btn-primary-action w-100 justify-content-center" id="btnGuardar">
                            <i class="bi bi-save"></i>
                            <span>Guardar Grupo</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="limpiarForm()" title="Limpiar formulario">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLA: Grupos Registrados con Filtro en Vivo -->
        <div class="modern-table-card">
            <div class="modern-table-header">
                <h3 class="modern-table-title">
                    <i class="bi bi-list-check text-primary"></i>
                    <span>Grupos Registrados (<?php echo count($grupos); ?>)</span>
                </h3>
                <div style="width: 240px;">
                    <input type="text" id="filtroGrupos" class="field-input" style="height: 36px; padding-left: 12px;" placeholder="Buscar grupo..." oninput="filtrarGrupos()">
                </div>
            </div>

            <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                <table class="custom-table" id="tablaGrupos">
                    <thead>
                        <tr>
                            <th style="width: 80px; text-align: center;">Código</th>
                            <th>Nombre del Grupo</th>
                            <th style="width: 140px; text-align: center;">Tipo</th>
                            <th style="width: 80px; text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyGrupos">
                        <?php if(empty($grupos)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No hay grupos registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($grupos as $g): ?>
                                <tr class="fila-grupo" data-nombre="<?php echo strtolower(htmlspecialchars($g['GroupName'] ?? '')); ?>" data-codigo="<?php echo htmlspecialchars($g['GroupCode'] ?? ''); ?>">
                                    <td class="fw-bold text-center text-primary" style="font-family: monospace;">
                                        #<?php echo htmlspecialchars($g['GroupCode']); ?>
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <?php echo htmlspecialchars($g['GroupName']); ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if($g['GroupType'] == 'C'): ?>
                                            <span class="badge-group-c">Cliente</span>
                                        <?php else: ?>
                                            <span class="badge-group-s">Suplidor</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <button type="button" 
                                                class="btn-table-action" 
                                                title="Editar grupo"
                                                onclick='editarGrupo(<?php echo htmlspecialchars(json_encode($g)); ?>)'>
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
    // Edición de grupo
    function editarGrupo(data) {
        document.getElementById('codigo_original').value = data.GroupCode;
        document.getElementById('GroupName').value = data.GroupName;
        document.getElementById('GroupType').value = data.GroupType;
        
        // Mostrar código de referencia visual
        document.getElementById('GroupCodeVisual').value = data.GroupCode;
        document.getElementById('divGroupCode').classList.remove('d-none');

        document.getElementById('titulo_form').innerHTML = '<i class="bi bi-pencil-square"></i> <span>Modificar Grupo #' + data.GroupCode + '</span>';
        document.getElementById('btnGuardar').innerHTML = '<i class="bi bi-arrow-repeat"></i> <span>Actualizar Grupo</span>';
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Limpiar formulario para nuevo grupo
    function limpiarForm() {
        document.getElementById('formGrupo').reset();
        document.getElementById('codigo_original').value = '';
        
        document.getElementById('divGroupCode').classList.add('d-none');
        document.getElementById('titulo_form').innerHTML = '<i class="bi bi-people"></i> <span>Datos del Grupo</span>';
        document.getElementById('btnGuardar').innerHTML = '<i class="bi bi-save"></i> <span>Guardar Grupo</span>';
    }

    // Filtro en vivo de la tabla
    function filtrarGrupos() {
        const query = document.getElementById('filtroGrupos').value.toLowerCase().trim();
        const filas = document.querySelectorAll('.fila-grupo');
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