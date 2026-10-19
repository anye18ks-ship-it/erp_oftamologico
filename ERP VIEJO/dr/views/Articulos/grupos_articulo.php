<?php require_once __DIR__ . '/../Estructura/header.php'; ?>

<!-- Estilos exclusivos del módulo Grupos de Artículos -->
<style>
    <?php include __DIR__ . '/styles/grupos_articulo.css'; ?>
</style>

<div class="grupos-container">
    <!-- Header de la Pantalla -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-folder-fill"></i>
            </div>
            <div>
                <h2 class="page-title">Grupos de Artículos</h2>
                <p class="page-subtitle">Determinación de cuentas contables, inventario, costos e ingresos</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn-primary-action" onclick="activarTabGrupo('tab-form'); limpiarFormGrupo();">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Nuevo Grupo</span>
            </button>
        </div>
    </div>

    <!-- Mensajes de Sistema -->
    <?php echo Helper::getMensaje(); ?>

    <!-- Pestañas de Navegación -->
    <div class="module-tabs">
        <button type="button" class="module-tab-btn active" id="btn-tab-list" onclick="activarTabGrupo('tab-list')">
            <i class="bi bi-list-columns-reverse"></i>
            <span>Directorio de Grupos</span>
            <span class="tab-badge"><?php echo count($grupos); ?></span>
        </button>
        <button type="button" class="module-tab-btn" id="btn-tab-form" onclick="activarTabGrupo('tab-form')">
            <i class="bi bi-folder-plus"></i>
            <span id="tab-form-title">Ficha del Grupo</span>
        </button>
    </div>

    <!-- =======================================================================
         PESTAÑA 1: DIRECTORIO DE GRUPOS CON FILTRO EN VIVO
         ======================================================================= -->
    <div class="tab-content-pane pane-active" id="tab-list">
        <div class="table-filter-bar">
            <div class="search-input-wrap">
                <i class="bi bi-search"></i>
                <input type="text" id="filtroTextoGrupos" placeholder="Buscar por código o nombre del grupo..." oninput="filtrarGrupos()">
            </div>
            <button type="button" class="btn-secondary-action" onclick="limpiarFiltrosGrupos()">
                <i class="bi bi-arrow-counterclockwise"></i>
                <span>Restablecer</span>
            </button>
        </div>

        <div class="modern-table-card">
            <div class="modern-table-header">
                <h3 class="modern-table-title">
                    <i class="bi bi-folder2-open text-primary"></i>
                    <span>Grupos Configurados (<?php echo count($grupos); ?>)</span>
                </h3>
            </div>

            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="custom-table" id="tablaGrupos">
                    <thead>
                        <tr>
                            <th style="width: 80px; text-align: center;">Código</th>
                            <th>Nombre del Grupo</th>
                            <th>Cuenta Inv. Balance</th>
                            <th>Cuenta Ingresos</th>
                            <th>Cuenta Gastos</th>
                            <th>Costo de Ventas</th>
                            <th style="width: 80px; text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyGrupos">
                        <?php if(empty($grupos)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No hay grupos de artículos registrados.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($grupos as $g): ?>
                                <tr class="fila-grupo" 
                                    data-nombre="<?php echo strtolower(htmlspecialchars($g['ItmsGrpNam'] ?? '')); ?>" 
                                    data-codigo="<?php echo htmlspecialchars($g['ItmsGrpCod'] ?? ''); ?>">
                                    <td class="fw-bold text-center text-primary" style="font-family: monospace;">
                                        #<?php echo htmlspecialchars($g['ItmsGrpCod']); ?>
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-folder text-primary"></i>
                                            <span><?php echo htmlspecialchars($g['ItmsGrpNam']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if(!empty($g['BalInvntAc'])): ?>
                                            <span class="account-code-pill"><?php echo htmlspecialchars($g['BalInvntAc']); ?></span>
                                        <?php else: ?><span class="text-muted" style="font-size: 0.8rem;">—</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($g['RevenuesAc'])): ?>
                                            <span class="account-code-pill"><?php echo htmlspecialchars($g['RevenuesAc']); ?></span>
                                        <?php else: ?><span class="text-muted" style="font-size: 0.8rem;">—</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($g['ExpensesAc'])): ?>
                                            <span class="account-code-pill"><?php echo htmlspecialchars($g['ExpensesAc']); ?></span>
                                        <?php else: ?><span class="text-muted" style="font-size: 0.8rem;">—</span><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($g['SaleCostAc'])): ?>
                                            <span class="account-code-pill"><?php echo htmlspecialchars($g['SaleCostAc']); ?></span>
                                        <?php else: ?><span class="text-muted" style="font-size: 0.8rem;">—</span><?php endif; ?>
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

    <!-- =======================================================================
         PESTAÑA 2: FORMULARIO MODULAR DEL GRUPO (4 SECCIONES ESTRUCTURADAS)
         ======================================================================= -->
    <div class="tab-content-pane" id="tab-form">
        <form action="index.php?action=guardar_grupo" method="POST" id="formGrupo" onsubmit="return validarFormGrupo()">
            <input type="hidden" name="ItmsGrpCod" id="ItmsGrpCod" value="">

            <div class="form-sections-wrap">
                <!-- SECCIÓN 1: IDENTIFICACIÓN PRINCIPAL -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <h4 class="form-section-title">
                            <i class="bi bi-folder-check text-primary"></i>
                            <span>1. Datos Principales del Grupo</span>
                        </h4>
                        <span class="text-muted" style="font-size: 0.78rem;">Nombre y ciclo</span>
                    </div>
                    <div class="form-section-body">
                        <div class="form-grid-2">
                            <div class="field-group mb-0">
                                <label class="field-label">Nombre del Grupo<span class="required">*</span></label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-tag field-icon"></i>
                                    <input type="text" name="ItmsGrpNam" id="ItmsGrpNam" class="field-input fw-semibold" required placeholder="Ej: Medicamentos, Monturas, Cristales">
                                </div>
                            </div>

                            <div class="field-group mb-0">
                                <label class="field-label">Código de Ciclo</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-arrow-repeat field-icon"></i>
                                    <input type="number" name="CycleCode" id="CycleCode" class="field-input" placeholder="Opcional">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: CUENTAS DE INVENTARIO Y COSTOS -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <h4 class="form-section-title">
                            <i class="bi bi-boxes text-primary"></i>
                            <span>2. Cuentas Contables de Inventario & Costos</span>
                        </h4>
                        <span class="text-muted" style="font-size: 0.78rem;">Balance, variaciones y costo de venta</span>
                    </div>
                    <div class="form-section-body">
                        <div class="form-grid-3">
                            <div class="field-group">
                                <label class="field-label">Cuenta Inventario Balance</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-cash-coin field-icon"></i>
                                    <input type="text" name="BalInvntAc" id="BalInvntAc" class="field-input font-monospace" maxlength="15" placeholder="Cuenta contable">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Cuenta Costo de Ventas</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-calculator field-icon"></i>
                                    <input type="text" name="SaleCostAc" id="SaleCostAc" class="field-input font-monospace" maxlength="15" placeholder="Cuenta contable">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Cuenta de Transferencia</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-arrow-left-right field-icon"></i>
                                    <input type="text" name="TransferAc" id="TransferAc" class="field-input font-monospace" maxlength="15" placeholder="Cuenta contable">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Cuenta de Variación</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-graph-up-arrow field-icon"></i>
                                    <input type="text" name="VarianceAc" id="VarianceAc" class="field-input font-monospace" maxlength="15" placeholder="Cuenta contable">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Cuenta de Disminución</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-dash-circle field-icon"></i>
                                    <input type="text" name="DecreasAc" id="DecreasAc" class="field-input font-monospace" maxlength="15" placeholder="Cuenta contable">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Cuenta de Incremento</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-plus-circle field-icon"></i>
                                    <input type="text" name="IncreasAc" id="IncreasAc" class="field-input font-monospace" maxlength="15" placeholder="Cuenta contable">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: CUENTAS DE INGRESOS Y GASTOS -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <h4 class="form-section-title">
                            <i class="bi bi-cash-stack text-success"></i>
                            <span>3. Cuentas Contables de Ingresos & Gastos</span>
                        </h4>
                        <span class="text-muted" style="font-size: 0.78rem;">Ventas, devoluciones y gastos operativos</span>
                    </div>
                    <div class="form-section-body">
                        <div class="form-grid-4">
                            <div class="field-group">
                                <label class="field-label">Cuenta de Ingresos</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-wallet2 field-icon"></i>
                                    <input type="text" name="RevenuesAc" id="RevenuesAc" class="field-input font-monospace" maxlength="15" placeholder="Cuenta contable">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Cuenta de Devoluciones</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-arrow-counterclockwise field-icon"></i>
                                    <input type="text" name="ReturnAc" id="ReturnAc" class="field-input font-monospace" maxlength="15" placeholder="Cuenta contable">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Cuenta de Gastos</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-receipt-cutoff field-icon"></i>
                                    <input type="text" name="ExpensesAc" id="ExpensesAc" class="field-input font-monospace" maxlength="15" placeholder="Cuenta contable">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Cuenta Ingreso Exento</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-shield-check field-icon"></i>
                                    <input type="text" name="ExmptIncom" id="ExmptIncom" class="field-input font-monospace" maxlength="15" placeholder="Cuenta contable">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 4: CUENTAS INTERNACIONALES (EU / FR) -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <h4 class="form-section-title">
                            <i class="bi bi-globe text-primary"></i>
                            <span>4. Cuentas de Operaciones Internacionales (EU / Extranjero)</span>
                        </h4>
                        <span class="text-muted" style="font-size: 0.78rem;">Ingresos y gastos transfronterizos</span>
                    </div>
                    <div class="form-section-body">
                        <div class="form-grid-4">
                            <div class="field-group">
                                <label class="field-label">Ingresos EU</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-currency-exchange field-icon"></i>
                                    <input type="text" name="EURevenuAc" id="EURevenuAc" class="field-input font-monospace" maxlength="15" placeholder="Cuenta contable">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Gastos EU</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-currency-exchange field-icon"></i>
                                    <input type="text" name="EUExpensAc" id="EUExpensAc" class="field-input font-monospace" maxlength="15" placeholder="Cuenta contable">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Ingresos Extranjero (FR)</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-globe2 field-icon"></i>
                                    <input type="text" name="FrRevenuAc" id="FrRevenuAc" class="field-input font-monospace" maxlength="15" placeholder="Cuenta contable">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Gastos Extranjero (FR)</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-globe2 field-icon"></i>
                                    <input type="text" name="FrExpensAc" id="FrExpensAc" class="field-input font-monospace" maxlength="15" placeholder="Cuenta contable">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción del Formulario -->
                <div class="d-flex align-items-center justify-content-between p-3 bg-white border rounded-3 shadow-sm">
                    <button type="button" class="btn-secondary-action" onclick="activarTabGrupo('tab-list')">
                        <i class="bi bi-arrow-left"></i>
                        <span>Volver al Directorio</span>
                    </button>
                    <button type="submit" class="btn-primary-action" id="btnGuardarGrupo">
                        <i class="bi bi-check2-circle"></i>
                        <span>Guardar Grupo de Artículos</span>
                    </button>
                </div>
            </div>
        </form>
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

    function activarTabGrupo(tabId) {
        document.querySelectorAll('.module-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content-pane').forEach(p => p.classList.remove('pane-active'));

        if (tabId === 'tab-list') {
            document.getElementById('btn-tab-list').classList.add('active');
            document.getElementById('tab-list').classList.add('pane-active');
        } else {
            document.getElementById('btn-tab-form').classList.add('active');
            document.getElementById('tab-form').classList.add('pane-active');
        }
    }

    function editarGrupo(data) {
        document.getElementById('ItmsGrpCod').value = data.ItmsGrpCod;
        document.getElementById('ItmsGrpNam').value = data.ItmsGrpNam || '';
        document.getElementById('CycleCode').value = data.CycleCode || '';

        document.getElementById('BalInvntAc').value = data.BalInvntAc || '';
        document.getElementById('SaleCostAc').value = data.SaleCostAc || '';
        document.getElementById('TransferAc').value = data.TransferAc || '';
        document.getElementById('VarianceAc').value = data.VarianceAc || '';
        document.getElementById('DecreasAc').value = data.DecreasAc || '';
        document.getElementById('IncreasAc').value = data.IncreasAc || '';

        document.getElementById('RevenuesAc').value = data.RevenuesAc || '';
        document.getElementById('ReturnAc').value = data.ReturnAc || '';
        document.getElementById('ExpensesAc').value = data.ExpensesAc || '';
        document.getElementById('ExmptIncom').value = data.ExmptIncom || '';

        document.getElementById('EURevenuAc').value = data.EURevenuAc || '';
        document.getElementById('EUExpensAc').value = data.EUExpensAc || '';
        document.getElementById('FrRevenuAc').value = data.FrRevenuAc || '';
        document.getElementById('FrExpensAc').value = data.FrExpensAc || '';

        document.getElementById('tab-form-title').textContent = 'Editando: ' + (data.ItmsGrpNam.substring(0, 18));
        document.getElementById('btnGuardarGrupo').innerHTML = '<i class="bi bi-check2-circle"></i> <span>Actualizar Grupo</span>';

        activarTabGrupo('tab-form');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function limpiarFormGrupo() {
        document.getElementById('formGrupo').reset();
        document.getElementById('ItmsGrpCod').value = '';
        document.getElementById('tab-form-title').textContent = 'Ficha del Grupo';
        document.getElementById('btnGuardarGrupo').innerHTML = '<i class="bi bi-check2-circle"></i> <span>Guardar Grupo de Artículos</span>';
    }

    function filtrarGrupos() {
        const query = document.getElementById('filtroTextoGrupos').value.toLowerCase().trim();
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

    function limpiarFiltrosGrupos() {
        document.getElementById('filtroTextoGrupos').value = '';
        filtrarGrupos();
    }

    function validarFormGrupo() {
        const nombre = document.getElementById('ItmsGrpNam').value.trim();
        if (!nombre) {
            alert('Por favor ingrese el nombre del grupo de artículos.');
            document.getElementById('ItmsGrpNam').focus();
            return false;
        }
        return true;
    }
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>