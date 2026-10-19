<?php 
require_once __DIR__ . '/../Estructura/header.php'; 

// Total de usuarios para la pestaña del directorio
$totalUsuarios = count($usuarios ?? []);
?>

<!-- Estilos exclusivos del módulo Usuarios -->
<style>
    <?php include __DIR__ . '/styles/usuarios.css'; ?>
</style>

<div class="usuarios-container">
    <!-- Header de la Pantalla -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <div>
                <h2 class="page-title">Gestión de Usuarios</h2>
                <p class="page-subtitle">Administración de cuentas, accesos y series para el personal de la clínica</p>
            </div>
        </div>

        <button type="button" class="btn-primary-action" onclick="activarTab('tab-form'); limpiarForm();">
            <i class="bi bi-person-plus-fill"></i>
            <span>Nuevo Usuario</span>
        </button>
    </div>

    <!-- Mensajes de Sistema -->
    <?php echo Helper::getMensaje(); ?>



    <!-- Pestañas de Navegación del Módulo -->
    <div class="module-tabs">
        <button type="button" class="module-tab-btn active" id="btn-tab-list" onclick="activarTab('tab-list')">
            <i class="bi bi-list-columns-reverse"></i>
            <span>Directorio de Usuarios</span>
            <span class="tab-badge" id="badge-total"><?php echo $totalUsuarios; ?></span>
        </button>
        <button type="button" class="module-tab-btn" id="btn-tab-form" onclick="activarTab('tab-form')">
            <i class="bi bi-person-gear"></i>
            <span id="tab-form-title">Crear / Editar Usuario</span>
        </button>
    </div>

    <!-- PESTAÑA 1: LISTADO DE USUARIOS CON FILTROS -->
    <div class="tab-content-pane pane-active" id="tab-list">
        <!-- Barra de Búsqueda y Filtros en Tiempo Real -->
        <div class="table-filter-bar">
            <div class="filter-group-left">
                <div class="search-input-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="filtroTexto" placeholder="Buscar por nombre, usuario o almacén SAP..." oninput="filtrarUsuarios()">
                </div>
                <select id="filtroEstado" class="filter-select" onchange="filtrarUsuarios()">
                    <option value="todos">Todos los Estados</option>
                    <option value="Y">Solo Activos</option>
                    <option value="N">Solo Inactivos</option>
                </select>
                <select id="filtroSerie" class="filter-select" onchange="filtrarUsuarios()">
                    <option value="todas">Todas las Series</option>
                    <option value="V">Serie V</option>
                    <option value="C">Serie C</option>
                </select>
            </div>
            <div class="filter-group-right">
                <button type="button" class="btn-secondary-action" onclick="limpiarFiltros()">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Restablecer</span>
                </button>
            </div>
        </div>

        <!-- Tabla Moderna -->
        <div class="modern-table-card">
            <div class="modern-table-header">
                <h3 class="modern-table-title">
                    <i class="bi bi-shield-lock text-primary"></i>
                    <span>Listado de Cuentas</span>
                </h3>
                <span class="text-muted" style="font-size: 0.82rem;" id="contador-filtrados">
                    Mostrando <?php echo $totalUsuarios; ?> de <?php echo $totalUsuarios; ?> usuarios
                </span>
            </div>

            <div class="table-responsive">
                <table class="custom-table" id="tablaUsuarios">
                    <thead>
                        <tr>
                            <th style="width: 70px;">ID</th>
                            <th>Usuario / Empleado</th>
                            <th>Almacén SAP</th>
                            <th>Serie</th>
                            <th>Estado</th>
                            <th style="width: 100px; text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyUsuarios">
                        <?php foreach ($usuarios as $u): 
                            $inicial = mb_strtoupper(mb_substr(trim($u['nombre'] ?? 'U'), 0, 1, 'UTF-8'), 'UTF-8');
                            $esActivo = ($u['activo'] === 'Y');
                        ?>
                            <tr class="fila-usuario" 
                                data-nombre="<?php echo strtolower(htmlspecialchars($u['nombre'])); ?>"
                                data-usuario="<?php echo strtolower(htmlspecialchars($u['usuario'])); ?>"
                                data-sap="<?php echo strtolower(htmlspecialchars($u['sap_whscode'] ?? '')); ?>"
                                data-estado="<?php echo $u['activo']; ?>"
                                data-serie="<?php echo $u['id_serie']; ?>">
                                
                                <td class="text-muted fw-bold">#<?php echo $u['id_usuario']; ?></td>
                                <td>
                                    <div class="user-cell-wrap">
                                        <div class="user-table-avatar">
                                            <?php echo $inicial; ?>
                                        </div>
                                        <div class="user-cell-meta">
                                            <div class="user-full-name"><?php echo htmlspecialchars($u['nombre']); ?></div>
                                            <div class="user-handle"><i class="bi bi-at"></i><?php echo htmlspecialchars($u['usuario']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if(!empty($u['sap_whscode'])): ?>
                                        <span class="badge-whscode"><?php echo htmlspecialchars($u['sap_whscode']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size: 0.8rem;">No asignado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge-serie">Serie <?php echo htmlspecialchars($u['id_serie'] ?? 'V'); ?></span>
                                </td>
                                <td>
                                    <?php if($esActivo): ?>
                                        <span class="status-pill status-active">
                                            <span class="status-dot"></span> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="status-pill status-inactive">
                                            <span class="status-dot"></span> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <button type="button" 
                                            class="btn-table-action" 
                                            title="Editar información del usuario"
                                            onclick='editarUsuario(<?php echo htmlspecialchars(json_encode($u)); ?>)'>
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div id="sin-resultados" style="display: none; padding: 40px; text-align: center; color: #64748b;">
                <i class="bi bi-search" style="font-size: 2.5rem; opacity: 0.4;"></i>
                <h5 class="mt-2 fw-bold text-dark">No se encontraron usuarios</h5>
                <p style="font-size: 0.88rem;">Intenta con otro término de búsqueda o cambia los filtros de estado.</p>
            </div>

            <!-- PAGINACIÓN PURO CSS (SIN BOOTSTRAP) -->
            <div class="pure-pagination-card">
                <div class="pure-length-selector">
                    <span>Mostrar</span>
                    <select id="usuariosPorPagina" class="pure-select-input" onchange="cambiarLimitePaginacionUsuarios()">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="all">Todos</option>
                    </select>
                    <span>usuarios por página</span>
                </div>

                <div class="pure-pagination-info" id="purePaginacionInfoUsuarios">
                    Mostrando 1 a 25 de <?php echo $totalUsuarios; ?> usuarios
                </div>

                <div class="pure-pagination-nav" id="purePaginacionNavUsuarios">
                    <!-- Los botones de página se generan dinámicamente con puro CSS / JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- PESTAÑA 2: FORMULARIO DE USUARIO (CREAR / EDITAR) -->
    <div class="tab-content-pane" id="tab-form">
        <div class="form-card-container">
            <div class="form-card-header">
                <h3 class="form-header-title" id="titulo_form">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>Crear Nuevo Usuario</span>
                </h3>
   
            </div>
            <div class="form-card-body">
                <form action="index.php?action=guardar_usuario" method="POST" id="formUsuario">
                    <input type="hidden" name="id_usuario" id="id_usuario" value="">

                    <div class="form-grid-2">
                        <div class="field-group">
                            <label class="field-label">Usuario de Acceso <span class="required">*</span></label>
                            <div class="field-input-wrap">
                                <i class="bi bi-person field-icon"></i>
                                <input type="text" name="usuario" id="usuario" class="field-input" placeholder="Ej: mrodriguez" required autocomplete="off">
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label">Contraseña <span id="req-clave" class="required">*</span></label>
                            <div class="field-input-wrap">
                                <i class="bi bi-key field-icon"></i>
                                <input type="password" name="clave" id="clave" class="field-input" placeholder="••••••••" autocomplete="new-password">
                            </div>
                            <div class="field-hint" id="claveHelp">Obligatorio al crear una nueva cuenta.</div>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Nombre Completo del Personal / Médico <span class="required">*</span></label>
                        <div class="field-input-wrap">
                            <i class="bi bi-person-badge field-icon"></i>
                            <input type="text" name="nombre" id="nombre" class="field-input" placeholder="Ej: Dra. María Rodríguez" required>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="field-group">
                            <label class="field-label">SAP WHSCode (Almacén)</label>
                            <div class="field-input-wrap">
                                <i class="bi bi-building field-icon"></i>
                                <input type="text" name="sap_whscode" id="sap_whscode" class="field-input" placeholder="Ej: ALM-01">
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label">Serie Asignada</label>
                            <div class="field-input-wrap">
                                <i class="bi bi-tag field-icon"></i>
                                <select name="id_serie" id="id_serie" class="field-select">
                                    <option value="V">Serie V (Ventas)</option>
                                    <option value="C">Serie C (Consultas / Caja)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Estado de la Cuenta</label>
                        <label class="active-toggle-wrap" for="activo">
                            <input type="checkbox" name="activo" id="activo" checked>
                            <span class="active-toggle-label">Cuenta activa (Permite iniciar sesión en el sistema)</span>
                        </label>
                    </div>

                    <div class="form-actions-bar">
                        <button type="button" class="btn-secondary-action" onclick="activarTab('tab-list')">
                            <i class="bi bi-arrow-left"></i>
                            <span>Cancelar y Volver</span>
                        </button>
                        <button type="submit" class="btn-primary-action" id="btnGuardar">
                            <i class="bi bi-check2-circle"></i>
                            <span>Guardar Usuario</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // ==========================================
    // ESTADO DE PAGINACIÓN Y FILTROS (PURO JS & CSS)
    // ==========================================
    let paginaActualUsuarios = 1;
    let registrosPorPaginaUsuarios = 25;
    let filasFiltradasUsuarios = [];

    // Inicializar al cargar el DOM
    document.addEventListener('DOMContentLoaded', function() {
        filtrarUsuarios();
    });

    // Cambio interactivo entre pestañas
    function activarTab(tabId) {
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

    // Edición de usuario y cambio a formulario
    function editarUsuario(data) {
        document.getElementById('id_usuario').value = data.id_usuario;
        document.getElementById('usuario').value = data.usuario;
        document.getElementById('clave').value = '';
        document.getElementById('nombre').value = data.nombre;
        document.getElementById('sap_whscode').value = data.sap_whscode || '';
        document.getElementById('id_serie').value = data.id_serie || 'V';
        document.getElementById('activo').checked = (data.activo === 'Y');

        document.getElementById('titulo_form').innerHTML = '<i class="bi bi-pencil-square"></i> <span>Modificando Usuario: ' + data.nombre + '</span>';
        document.getElementById('tab-form-title').textContent = 'Editar Usuario';
        document.getElementById('claveHelp').textContent = 'Dejar en blanco para conservar la contraseña actual.';
        document.getElementById('req-clave').style.display = 'none';
        document.getElementById('clave').removeAttribute('required');
        document.getElementById('btnGuardar').innerHTML = '<i class="bi bi-arrow-repeat"></i> <span>Actualizar Cambios</span>';

        activarTab('tab-form');
    }

    // Limpiar formulario para nuevo usuario
    function limpiarForm() {
        document.getElementById('formUsuario').reset();
        document.getElementById('id_usuario').value = '';
        document.getElementById('titulo_form').innerHTML = '<i class="bi bi-person-plus-fill"></i> <span>Crear Nuevo Usuario</span>';
        document.getElementById('tab-form-title').textContent = 'Crear / Editar Usuario';
        document.getElementById('claveHelp').textContent = 'Obligatorio al crear una nueva cuenta.';
        document.getElementById('req-clave').style.display = 'inline';
        document.getElementById('clave').setAttribute('required', 'required');
        document.getElementById('btnGuardar').innerHTML = '<i class="bi bi-check2-circle"></i> <span>Guardar Usuario</span>';
        document.getElementById('activo').checked = true;
    }

    // Filtro instantáneo de usuarios en la tabla
    function filtrarUsuarios() {
        const texto = document.getElementById('filtroTexto').value.toLowerCase().trim();
        const estado = document.getElementById('filtroEstado').value;
        const serie = document.getElementById('filtroSerie').value;

        const todasFilas = Array.from(document.querySelectorAll('.fila-usuario'));
        filasFiltradasUsuarios = [];

        todasFilas.forEach(fila => {
            const nombre = fila.getAttribute('data-nombre') || '';
            const usuario = fila.getAttribute('data-usuario') || '';
            const sap = fila.getAttribute('data-sap') || '';
            const fEstado = fila.getAttribute('data-estado') || '';
            const fSerie = fila.getAttribute('data-serie') || '';

            const coincideTexto = !texto || nombre.includes(texto) || usuario.includes(texto) || sap.includes(texto);
            const coincideEstado = (estado === 'todos') || (fEstado === estado);
            const coincideSerie = (serie === 'todas') || (fSerie === serie);

            if (coincideTexto && coincideEstado && coincideSerie) {
                filasFiltradasUsuarios.push(fila);
            } else {
                fila.style.display = 'none';
            }
        });

        // Ocultar o mostrar mensaje si no hay registros
        const divSinResultados = document.getElementById('sin-resultados');
        if (filasFiltradasUsuarios.length === 0) {
            divSinResultados.style.display = 'block';
        } else {
            divSinResultados.style.display = 'none';
        }

        paginaActualUsuarios = 1;
        renderizarPaginacionUsuarios();
    }

    function limpiarFiltros() {
        document.getElementById('filtroTexto').value = '';
        document.getElementById('filtroEstado').value = 'todos';
        document.getElementById('filtroSerie').value = 'todas';
        filtrarUsuarios();
    }

    // ==========================================
    // MOTOR DE PAGINACIÓN PURO CSS (USUARIOS)
    // ==========================================
    function cambiarLimitePaginacionUsuarios() {
        const val = document.getElementById('usuariosPorPagina').value;
        registrosPorPaginaUsuarios = (val === 'all') ? -1 : parseInt(val, 10);
        paginaActualUsuarios = 1;
        renderizarPaginacionUsuarios();
    }

    function irAPaginaUsuarios(numPagina) {
        paginaActualUsuarios = numPagina;
        renderizarPaginacionUsuarios();
    }

    function renderizarPaginacionUsuarios() {
        const total = filasFiltradasUsuarios.length;
        const info = document.getElementById('purePaginacionInfoUsuarios');
        const resumen = document.getElementById('contador-filtrados');
        const nav = document.getElementById('purePaginacionNavUsuarios');

        if (total === 0) {
            if (info) info.textContent = 'Mostrando 0 usuarios';
            if (resumen) resumen.textContent = 'Mostrando 0 usuarios';
            if (nav) nav.innerHTML = '';
            return;
        }

        let totalPaginas = (registrosPorPaginaUsuarios === -1) ? 1 : Math.ceil(total / registrosPorPaginaUsuarios);
        if (paginaActualUsuarios > totalPaginas) paginaActualUsuarios = totalPaginas;
        if (paginaActualUsuarios < 1) paginaActualUsuarios = 1;

        let inicio = (registrosPorPaginaUsuarios === -1) ? 0 : (paginaActualUsuarios - 1) * registrosPorPaginaUsuarios;
        let fin = (registrosPorPaginaUsuarios === -1) ? total : Math.min(inicio + registrosPorPaginaUsuarios, total);

        // Mostrar únicamente las filas del rango
        filasFiltradasUsuarios.forEach((fila, index) => {
            if (index >= inicio && index < fin) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });

        // Actualizar textos informativos
        if (info) info.textContent = `Mostrando ${inicio + 1} a ${fin} de ${total} usuarios`;
        if (resumen) resumen.textContent = `Mostrando ${total} de <?php echo $totalUsuarios; ?> usuarios`;

        // Generar botones de navegación
        let htmlNav = '';

        // Botón Primero y Anterior
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaUsuarios(1)" ${paginaActualUsuarios === 1 ? 'disabled' : ''} title="Primera página"><i class="bi bi-chevron-double-left"></i></button>`;
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaUsuarios(${paginaActualUsuarios - 1})" ${paginaActualUsuarios === 1 ? 'disabled' : ''} title="Página anterior"><i class="bi bi-chevron-left"></i></button>`;

        // Rango de páginas numeradas
        let maxBotones = 5;
        let inicioPag = Math.max(1, paginaActualUsuarios - Math.floor(maxBotones / 2));
        let finPag = Math.min(totalPaginas, inicioPag + maxBotones - 1);
        if (finPag - inicioPag + 1 < maxBotones) {
            inicioPag = Math.max(1, finPag - maxBotones + 1);
        }

        for (let i = inicioPag; i <= finPag; i++) {
            const esActiva = (i === paginaActualUsuarios) ? 'active' : '';
            htmlNav += `<button type="button" class="pure-page-btn ${esActiva}" onclick="irAPaginaUsuarios(${i})">${i}</button>`;
        }

        // Botón Siguiente y Último
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaUsuarios(${paginaActualUsuarios + 1})" ${paginaActualUsuarios === totalPaginas ? 'disabled' : ''} title="Página siguiente"><i class="bi bi-chevron-right"></i></button>`;
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaUsuarios(${totalPaginas})" ${paginaActualUsuarios === totalPaginas ? 'disabled' : ''} title="Última página"><i class="bi bi-chevron-double-right"></i></button>`;

        if (nav) nav.innerHTML = htmlNav;
    }
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>