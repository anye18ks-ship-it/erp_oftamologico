<?php 
// ==========================================================================
// CONTROLADOR Y DATOS DEL SOCIO SELECCIONADO
// ==========================================================================
$soc = $socioSeleccionado ?? null;
$esEdicion = ($soc !== null);
$val = [
    'CardCode' => $soc['CardCode'] ?? ($defaultCardCode ?? ''),
    'CardName' => $soc['CardName'] ?? '',
    'CardType' => $soc['CardType'] ?? 'C',
    'GroupCode' => $soc['GroupCode'] ?? ($esEdicion ? '' : 2), // Default 2 = NO ASEGURADOS
    'Address' => $soc['Address'] ?? '',
    'Phone1' => $soc['Phone1'] ?? '',
    'Phone2' => $soc['Phone2'] ?? '',
    'Fax' => $soc['Fax'] ?? '',
    'Notes' => $soc['Notes'] ?? '',
    'Balance' => number_format((float)($soc['Balance'] ?? 0), 2),
    'GroupNum' => $soc['GroupNum'] ?? ($esEdicion ? '' : 1), // Default 1 = CONTADO
    'CreditLine' => number_format((float)($soc['CreditLine'] ?? 0), 2, '.', ''),
    'ListNum' => $soc['ListNum'] ?? '',
    'Free_Text' => $soc['Free_Text'] ?? '',
    'E_Mail' => $soc['E_Mail'] ?? '',
    'U_NIT' => $soc['U_NIT'] ?? '',
    'U_Comprob' => $soc['U_Comprob'] ?? ($esEdicion ? '' : 'E32'), // Default E32 = Consumidor Final Electrónico
    'U_TNegocio' => $soc['U_TNegocio'] ?? ($esEdicion ? '' : '2'), // Default 2 = Cédula
    'U_CodAnt' => $soc['U_CodAnt'] ?? '',
    'U_CadenaID' => $soc['U_CadenaID'] ?? '',
    'U_SubGrupo' => $soc['U_SubGrupo'] ?? '',
    'U_expediente' => $soc['U_expediente'] ?? 0
];

$readonlyCode = $esEdicion ? 'readonly' : '';

// Helper para verificar checkboxes 'Y' / 'N'
function chkY($val, $field) { return ($val === 'Y') ? 'checked' : ''; }

// Total de socios para la pestaña y paginación
$totalSocios = count($socios ?? []);

// Mapa de Propiedades SAP (QryGroup) - Estructurado y Fácilmente Escalable para Desarrolladores
$propiedadesPrincipales = [
    1 => [
        'label' => 'Aseguradora / ARS',
        'desc' => 'Define a este socio como entidad aseguradora (Habilita combo)',
        'icon' => 'bi-hospital',
        'destacada' => true
    ],
    2 => [
        'label' => 'Empresa / Convenio',
        'desc' => 'Cliente corporativo o acuerdo institucional de salud',
        'icon' => 'bi-building',
        'destacada' => false
    ],
    3 => [
        'label' => 'Médico / Especialista',
        'desc' => 'Oftalmólogo o especialista referido para interconsultas',
        'icon' => 'bi-person-badge',
        'destacada' => false
    ],
    4 => [
        'label' => 'Paciente Frecuente / VIP',
        'desc' => 'Atención preferencial o descuento recurrente',
        'icon' => 'bi-star',
        'destacada' => false
    ],
    5 => [
        'label' => 'Exento de Impuestos',
        'desc' => 'Régimen especial o exención fiscal DGII',
        'icon' => 'bi-percent',
        'destacada' => false
    ],
];
?>

<?php require_once __DIR__ . '/../Estructura/header.php'; ?>

<!-- Estilos exclusivos del módulo Socios de Negocio -->
<style>
    <?php include __DIR__ . '/styles/socios.css'; ?>
</style>

<div class="socios-container">
    <!-- Header de la Pantalla -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <div>
                <h2 class="page-title">Socios de Negocio</h2>
                <p class="page-subtitle">Gestión integral de clientes, pacientes, aseguradoras y suplidores</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <?php if($esEdicion): ?>
     
            <?php endif; ?>
            <button type="button" class="btn-primary-action" onclick="activarTabSocio('tab-form'); limpiarFormSocio();">
                <i class="bi bi-person-plus-fill"></i>
                <span>Nuevo Socio</span>
            </button>
        </div>
    </div>

    <!-- Mensajes de Sistema -->
    <?php echo Helper::getMensaje(); ?>



    <!-- Pestañas de Navegación -->
    <div class="module-tabs">
        <button type="button" class="module-tab-btn <?php echo !$esEdicion ? 'active' : ''; ?>" id="btn-tab-list" onclick="activarTabSocio('tab-list')">
            <i class="bi bi-list-columns-reverse"></i>
            <span>Directorio de Socios</span>
            <span class="tab-badge"><?php echo $totalSocios; ?></span>
        </button>
        <button type="button" class="module-tab-btn <?php echo $esEdicion ? 'active' : ''; ?>" id="btn-tab-form" onclick="activarTabSocio('tab-form')">
            <i class="bi bi-person-gear"></i>
            <span id="tab-form-title"><?php echo $esEdicion ? 'Editando: ' . htmlspecialchars(substr($val['CardName'], 0, 22)) : 'Ficha del Socio'; ?></span>
        </button>
    </div>

    <!-- PESTAÑA 1: DIRECTORIO DE SOCIOS CON FILTROS EN TIEMPO REAL -->
    <div class="tab-content-pane <?php echo !$esEdicion ? 'pane-active' : ''; ?>" id="tab-list">
        <!-- Barra de Búsqueda y Filtros -->
        <div class="table-filter-bar">
            <div class="filter-group-left">
                <div class="search-input-wrap">
                    <i class="bi bi-search"></i>
                    <input type="text" id="filtroTexto" placeholder="Buscar por nombre, código, RNC/cédula, expediente o teléfono..." oninput="filtrarSocios()">
                </div>
                <select id="filtroTipo" class="filter-select" onchange="filtrarSocios()">
                    <option value="todos">Todos los Tipos</option>
                    <option value="C">Clientes / Pacientes</option>
                    <option value="S">Seguros (ARS)</option>
                    <option value="P">Proveedores / Suplidores</option>
                </select>
                <select id="filtroGrupo" class="filter-select" onchange="filtrarSocios()">
                    <option value="todos">Todos los Grupos</option>
                    <?php foreach (($listaGrupos ?? []) as $g): ?>
                        <option value="<?php echo htmlspecialchars($g['GroupCode']); ?>"><?php echo htmlspecialchars($g['GroupName']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="filtroEstado" class="filter-select" onchange="filtrarSocios()">
                    <option value="todos">Todos los Estados</option>
                    <option value="Y">Activos</option>
                    <option value="N">Inactivos</option>
                </select>
            </div>
            <div class="filter-group-right">
                <button type="button" class="btn-secondary-action" onclick="limpiarFiltrosSocios()">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Restablecer</span>
                </button>
            </div>
        </div>

        <!-- Tabla de Socios -->
        <div class="modern-table-card">
            <div class="modern-table-header">
                <h3 class="modern-table-title">
                    <i class="bi bi-person-lines-fill text-primary"></i>
                    <span>Socios Registrados</span>
                </h3>
                <span class="text-muted" style="font-size: 0.82rem;" id="contador-socios">
                    Mostrando <?php echo $totalSocios; ?> socios
                </span>
            </div>

            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="custom-table" id="tablaSocios">
                    <thead>
                        <tr>
                            <th>Socio de Negocio</th>
                            <th>Tipo / Clasificación</th>
                            <th>RNC / Cédula / Exp</th>
                            <th>Contacto</th>
                            <th style="text-align: right;">Balance</th>
                            <th style="text-align: center;">Estado</th>
                            <th style="text-align: center; width: 70px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tbodySocios">
                        <?php foreach ($socios as $s): 
                            $tipo = $s['CardType'] ?? 'C';
                            $grpUpper = mb_strtoupper($s['GroupName'] ?? '', 'UTF-8');
                            $esAseguradora = ($tipo === 'S') || (($s['QryGroup1'] ?? '') === 'Y') || strpos($grpUpper, 'ASEGURADORA') !== false || strpos($grpUpper, 'SEGURO') !== false;
                            $esProveedor = ($tipo === 'P') || (($s['GroupType'] ?? '') === 'S') || strpos($grpUpper, 'PROVEEDOR') !== false || strpos($grpUpper, 'SUPLIDOR') !== false;
                            $inicial = mb_strtoupper(mb_substr(trim($s['CardName'] ?? 'S'), 0, 1, 'UTF-8'), 'UTF-8');
                            
                            $nombreGrupoDisplay = !empty($s['GroupName']) ? trim($s['GroupName']) : ($esAseguradora ? 'Aseguradoras' : ($esProveedor ? 'Proveedores' : 'Clientes'));

                            if ($esAseguradora) {
                                $claseAvatar = 'avatar-aseguradora';
                                $labelTipo = $nombreGrupoDisplay;
                                $claseTipo = 'tipo-aseguradora';
                                $dataTipo = 'S';
                            } elseif ($esProveedor) {
                                $claseAvatar = 'avatar-suplidor';
                                $labelTipo = $nombreGrupoDisplay;
                                $claseTipo = 'tipo-suplidor';
                                $dataTipo = 'P';
                            } else {
                                $claseAvatar = 'avatar-cliente';
                                $labelTipo = $nombreGrupoDisplay;
                                $claseTipo = 'tipo-cliente';
                                $dataTipo = 'C';
                            }

                            $esActivo = (($s['validFor'] ?? '') === 'Y');
                        ?>
                            <tr class="fila-socio" 
                                data-nombre="<?php echo strtolower(htmlspecialchars($s['CardName'] ?? '')); ?>"
                                data-codigo="<?php echo strtolower(htmlspecialchars($s['CardCode'] ?? '')); ?>"
                                data-nit="<?php echo strtolower(htmlspecialchars($s['U_NIT'] ?? '')); ?>"
                                data-exp="<?php echo strtolower(htmlspecialchars($s['U_CodAnt'] ?? '')); ?>"
                                data-telefono="<?php echo strtolower(htmlspecialchars($s['Phone1'] ?? '')); ?>"
                                data-tipo="<?php echo $dataTipo; ?>"
                                data-grupo="<?php echo htmlspecialchars((string)($s['GroupCode'] ?? '')); ?>"
                                data-estado="<?php echo $s['validFor'] ?? 'Y'; ?>">
                                
                                <td>
                                    <div class="socio-cell-wrap">
                                        <div class="socio-table-avatar <?php echo $claseAvatar; ?>">
                                            <?php echo $inicial; ?>
                                        </div>
                                        <div class="socio-cell-meta">
                                            <div class="socio-name"><?php echo htmlspecialchars($s['CardName']); ?></div>
                                            <div class="socio-code"><?php echo htmlspecialchars($s['CardCode']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-tipo <?php echo $claseTipo; ?>">
                                        <?php if($esAseguradora): ?><i class="bi bi-hospital"></i><?php endif; ?>
                                        <?php echo $labelTipo; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if(!empty($s['U_NIT'])): ?>
                                        <div style="font-size: 0.84rem; font-weight: 600; color: #1e293b;">
                                            <i class="bi bi-card-heading text-muted me-1"></i><?php echo htmlspecialchars($s['U_NIT']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if(!empty($s['U_CodAnt'])): ?>
                                        <div style="font-size: 0.76rem; color: #0284c7; font-weight: 700;">
                                            <i class="bi bi-folder2-open me-1"></i>Exp: <?php echo htmlspecialchars($s['U_CodAnt']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if(empty($s['U_NIT']) && empty($s['U_CodAnt'])): ?>
                                        <span class="text-muted" style="font-size: 0.8rem;">Sin documento</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-size: 0.84rem; color: #334155;">
                                        <?php echo htmlspecialchars($s['Phone1'] ?? $s['Phone2'] ?? '—'); ?>
                                    </div>
                                    <?php if(!empty($s['E_Mail'])): ?>
                                        <div style="font-size: 0.74rem; color: #64748b;">
                                            <?php echo htmlspecialchars(substr($s['E_Mail'], 0, 25)); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right; font-weight: 700; font-family: monospace; color: <?php echo ((float)($s['Balance'] ?? 0) > 0) ? '#dc2626' : '#1e293b'; ?>;">
                                    RD$ <?php echo number_format((float)($s['Balance'] ?? 0), 2); ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if($esActivo): ?>
                                        <span class="status-pill status-active"><span class="status-dot"></span> Activo</span>
                                    <?php else: ?>
                                        <span class="status-pill status-inactive"><span class="status-dot"></span> Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <a href="index.php?page=socios&card=<?php echo urlencode($s['CardCode']); ?>" 
                                       class="btn-table-action" 
                                       title="Editar ficha del socio">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div id="sin-socios" style="display: none; padding: 40px; text-align: center; color: #64748b;">
                <i class="bi bi-search" style="font-size: 2.5rem; opacity: 0.4;"></i>
                <h5 class="mt-2 fw-bold text-dark">No se encontraron socios de negocio</h5>
                <p style="font-size: 0.88rem;">Intenta con otro término de búsqueda o cambia los filtros.</p>
            </div>

            <!-- PAGINACIÓN PURO CSS (SIN BOOTSTRAP) -->
            <div class="pure-pagination-card">
                <div class="pure-length-selector">
                    <span>Mostrar</span>
                    <select id="sociosPorPagina" class="pure-select-input" onchange="cambiarLimitePaginacionSocios()">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="all">Todos</option>
                    </select>
                    <span>socios por página</span>
                </div>

                <div class="pure-pagination-info" id="purePaginacionInfoSocios">
                    Mostrando 1 a 25 de <?php echo $totalSocios; ?> socios
                </div>

                <div class="pure-pagination-nav" id="purePaginacionNavSocios">
                    <!-- Los botones de página se generan dinámicamente con puro CSS / JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- PESTAÑA 2: FORMULARIO MODERNO EN SECCIONES MODULARES -->
    <div class="tab-content-pane <?php echo $esEdicion ? 'pane-active' : ''; ?>" id="tab-form">
        
        <!-- BANNER DE EDICIÓN ACTIVA CON BOTÓN DE CANCELAR -->
        <?php if($esEdicion): ?>
            <div class="edit-status-banner">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-warning fs-5"></i>
                    <div>
                        <strong class="text-dark">Modo Edición:</strong>
                        <span class="text-primary fw-bold"><?php echo htmlspecialchars($val['CardName']); ?> (<?php echo htmlspecialchars($val['CardCode']); ?>)</span>
                    </div>
                </div>
                <a href="index.php?page=socios" class="btn-cancel-edit">
                    <i class="bi bi-x-circle"></i>
                    <span>Cancelar Edición</span>
                </a>
            </div>
        <?php endif; ?>

        <form action="index.php?action=guardar_socio" method="POST" id="formSocio" onsubmit="return validarFormulario()">
            <input type="hidden" name="codigo_original" id="codigo_original" value="<?php echo $esEdicion ? htmlspecialchars($val['CardCode']) : ''; ?>">
            <input type="hidden" name="U_expediente" id="U_expediente" value="<?php echo $val['U_expediente']; ?>">

            <div class="form-sections-wrap">
                <!-- SECCIÓN 1: IDENTIFICACIÓN PRINCIPAL -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <h4 class="form-section-title">
                            <i class="bi bi-person-vcard text-primary"></i>
                            <span>1. Identificación Principal</span>
                        </h4>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="validFor" id="validFor" <?php echo chkY($soc['validFor'] ?? 'Y', 'validFor'); ?> style="accent-color: #0284c7; cursor: pointer;">
                            <label class="form-check-label fw-bold ms-1" for="validFor" style="font-size: 0.82rem; color: #334155;">Socio Activo</label>
                        </div>
                    </div>
                    <div class="form-section-body">
                        <div class="form-grid-3">
                            <div class="field-group">
                                <label class="field-label">Tipo de Socio <span class="required">*</span></label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-diagram-2 field-icon"></i>
                                    <select name="CardType" id="CardType" class="field-select" onchange="onCardTypeChange()" required>
                                        <option value="">-- Seleccionar --</option>
                                        <option value="C" <?php echo ($val['CardType']=='C')?'selected':''; ?>>Cliente / Paciente</option>
                                        <option value="S" <?php echo ($val['CardType']=='S')?'selected':''; ?>>Seguros</option>
                                        <option value="P" <?php echo ($val['CardType']=='P')?'selected':''; ?>>Proveedor / Suplidor</option>
                                    </select>
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Código de Socio <span class="required">*</span></label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-hash field-icon"></i>
                                    <input type="text" name="CardCode" id="CardCode" class="field-input text-uppercase fw-bold" value="<?php echo htmlspecialchars($val['CardCode']); ?>" required <?php echo $readonlyCode; ?> placeholder="Ej: C0001, S0001, P0001">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Grupo Comercial</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-tags field-icon"></i>
                                    <?php echo $comboGrupos; ?>
                                </div>
                            </div>
                        </div>

                        <div class="field-group mb-0">
                            <label class="field-label">Nombre Completo / Razón Social <span class="required">*</span></label>
                            <div class="field-input-wrap">
                                <i class="bi bi-person field-icon"></i>
                                <input type="text" name="CardName" id="CardName" class="field-input fw-semibold" value="<?php echo htmlspecialchars($val['CardName']); ?>" required placeholder="Nombre del paciente, empresa o proveedor">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: DATOS CLÍNICOS & IDENTIFICACIÓN FISCAL -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <h4 class="form-section-title">
                            <i class="bi bi-heart-pulse text-danger"></i>
                            <span>2. Datos Clínicos & Documento Fiscal</span>
                        </h4>
                        <span class="text-muted" style="font-size: 0.78rem;">Expediente oftálmico, seguro médico y DGII</span>
                    </div>
                    <div class="form-section-body">
                        <!-- Banner informativo estilizado para búsqueda de expediente -->
                        <div class="info-banner-expediente">
                            <i class="bi bi-info-circle-fill fs-6"></i>
                            <span><strong>Búsqueda Asistida:</strong> Al ingresar el <strong>N° de Expediente</strong> y presionar <code>Tab</code>, el sistema buscará y autocompletará automáticamente el nombre, dirección, cédula y teléfonos del paciente.</span>
                        </div>

                        <div class="form-grid-3">
                            <div class="field-group">
                                <label class="field-label" style="color: #0284c7;">N° Expediente Clínico</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-folder2-open field-icon text-primary"></i>
                                    <input type="text" name="U_CodAnt" id="U_CodAnt" class="field-input fw-bold" value="<?php echo htmlspecialchars($val['U_CodAnt']); ?>" placeholder="Buscar y presionar Tab..." style="border-color: #bae6fd;">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label" id="lbl_CadenaID">N° Carnet / Seguro Médico <span class="carnet-req-star text-danger fw-bold" style="display:none;">*</span></label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-shield-lock field-icon"></i>
                                    <input type="text" name="U_CadenaID" id="U_CadenaID" class="field-input" value="<?php echo htmlspecialchars($val['U_CadenaID']); ?>" placeholder="N° de afiliado ARS">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label" style="color: #dc2626;">Aseguradora Vinculada (ARS) <span class="carnet-req-star text-danger fw-bold" style="display:none;">*</span></label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-hospital field-icon text-danger"></i>
                                    <?php echo $comboAseguradoras; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="field-group mb-0">
                                <label class="field-label">Tipo de Identificación Fiscal</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-card-heading field-icon"></i>
                                    <select name="U_TNegocio" id="U_TNegocio" class="field-select">
                                        <option value="">-- Seleccionar Tipo --</option>
                                        <option value="1" <?php echo ($val['U_TNegocio']=='1')?'selected':''; ?>>1 — RNC (9 Dígitos)</option>
                                        <option value="2" <?php echo ($val['U_TNegocio']=='2')?'selected':''; ?>>2 — Cédula (11 Dígitos)</option>
                                        <option value="3" <?php echo ($val['U_TNegocio']=='3')?'selected':''; ?>>3 — Pasaporte / Otros</option>
                                    </select>
                                </div>
                            </div>

                            <div class="field-group mb-0">
                                <label class="field-label">Documento RNC / Cédula</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-upc-scan field-icon"></i>
                                    <input type="text" name="U_NIT" id="U_NIT" class="field-input" value="<?php echo htmlspecialchars($val['U_NIT']); ?>" placeholder="Ej: 40200000000">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: CONTACTO Y DIRECCIÓN -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <h4 class="form-section-title">
                            <i class="bi bi-telephone text-success"></i>
                            <span>3. Contacto & Ubicación</span>
                        </h4>
                    </div>
                    <div class="form-section-body">
                        <div class="form-grid-4">
                            <div class="field-group">
                                <label class="field-label">Teléfono Principal</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-telephone field-icon"></i>
                                    <input type="text" name="Phone1" id="Phone1" class="field-input" value="<?php echo htmlspecialchars($val['Phone1']); ?>" placeholder="Ej: 809-000-0000">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Teléfono Secundario / Casa</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-phone field-icon"></i>
                                    <input type="text" name="Phone2" id="Phone2" class="field-input" value="<?php echo htmlspecialchars($val['Phone2']); ?>" placeholder="Ej: 829-000-0000">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Fax</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-printer field-icon"></i>
                                    <input type="text" name="Fax" class="field-input" value="<?php echo htmlspecialchars($val['Fax']); ?>">
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Correo Electrónico</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-envelope field-icon"></i>
                                    <input type="email" name="E_Mail" class="field-input" value="<?php echo htmlspecialchars($val['E_Mail']); ?>" placeholder="correo@ejemplo.com">
                                </div>
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label">Dirección Física</label>
                            <div class="field-input-wrap">
                                <i class="bi bi-geo-alt field-icon"></i>
                                <input type="text" name="Address" id="Address" class="field-input" value="<?php echo htmlspecialchars($val['Address']); ?>" placeholder="Calle, Número, Sector, Ciudad">
                            </div>
                        </div>

                        <div class="field-group mb-0">
                            <label class="field-label">Observaciones / Notas Clínicas o Comerciales</label>
                            <textarea name="Notes" class="field-textarea" rows="2" placeholder="Información adicional..."><?php echo htmlspecialchars($val['Notes']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 4: FACTURACIÓN & CONDICIONES COMERCIALES -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <h4 class="form-section-title">
                            <i class="bi bi-receipt text-warning"></i>
                            <span>4. Facturación & Condiciones Comerciales</span>
                        </h4>
                    </div>
                    <div class="form-section-body">
                        <div class="form-grid-4">
                            <div class="field-group">
                                <label class="field-label">Condición de Pago</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-calendar2-check field-icon"></i>
                                    <?php echo $comboCondPago; ?>
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Lista de Precios</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-currency-dollar field-icon"></i>
                                    <?php echo $comboListas; ?>
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Comprobante NCF</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-file-earmark-text field-icon"></i>
                                    <?php echo $comboNCF; ?>
                                </div>
                            </div>

                            <div class="field-group">
                                <label class="field-label">Línea de Crédito Autorizada</label>
                                <div class="field-input-wrap">
                                    <i class="bi bi-credit-card field-icon"></i>
                                    <input type="number" step="0.01" name="CreditLine" class="field-input" value="<?php echo $val['CreditLine']; ?>" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 5: PROPIEDADES & CLASIFICACIÓN SAP (ESCALABLE) -->
                <div class="form-section-card">
                    <div class="form-section-header">
                        <h4 class="form-section-title">
                            <i class="bi bi-toggles text-info"></i>
                            <span>5. Propiedades Especiales</span>
                        </h4>
                        <span class="text-muted" style="font-size: 0.78rem;">Estructura modular para segmentación</span>
                    </div>
                    <div class="form-section-body">
                        <!-- Propiedades Principales con Tarjetas Interactivas -->
                        <div class="props-main-grid">
                            <?php foreach ($propiedadesPrincipales as $numProp => $p): 
                                $fieldName = 'QryGroup' . $numProp;
                                $isChecked = (($soc[$fieldName] ?? '') === 'Y');
                                $esDestacada = $p['destacada'] ? 'prop-featured' : '';
                            ?>
                                <label class="prop-card-item <?php echo $esDestacada; ?>" for="<?php echo $fieldName; ?>">
                                    <div class="prop-info-wrap">
                                        <div class="prop-title">
                                            <i class="bi <?php echo $p['icon']; ?> me-1"></i>
                                            <?php echo htmlspecialchars($p['label']); ?>
                                        </div>
                                        <div class="prop-desc"><?php echo htmlspecialchars($p['desc']); ?></div>
                                    </div>
                                    <input type="checkbox" name="<?php echo $fieldName; ?>" id="<?php echo $fieldName; ?>" class="prop-checkbox" <?php echo $isChecked ? 'checked' : ''; ?>>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Botón para desplegar propiedades extendidas adicionales (QryGroup6 - 10 y 54 - 64) -->
                        <button type="button" class="props-extended-toggle" onclick="togglePropsExtendidas()">
                            <i class="bi bi-chevron-down" id="propsChevron"></i>
                            <span>Ver / Configurar Propiedades Adicionales (6 a 10 y 54 a 64)</span>
                        </button>

                        <div class="props-extended-grid" id="propsExtendedGrid">
                            <?php for($i=6; $i<=10; $i++): 
                                $fName = 'QryGroup' . $i;
                            ?>
                                <label class="prop-mini-item" for="<?php echo $fName; ?>">
                                    <input type="checkbox" name="<?php echo $fName; ?>" id="<?php echo $fName; ?>" class="form-check-input" <?php echo chkY($soc[$fName] ?? '', $fName); ?>>
                                    <span>Propiedad <?php echo $i; ?></span>
                                </label>
                            <?php endfor; ?>

                            <?php for($i=54; $i<=64; $i++): 
                                $fName = 'QryGroup' . $i;
                            ?>
                                <label class="prop-mini-item" for="<?php echo $fName; ?>">
                                    <input type="checkbox" name="<?php echo $fName; ?>" id="<?php echo $fName; ?>" class="form-check-input" <?php echo chkY($soc[$fName] ?? '', $fName); ?>>
                                    <span>Propiedad <?php echo $i; ?></span>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Barra de Acciones del Formulario -->
                <div class="d-flex align-items-center justify-content-between p-3 bg-white border rounded-3 shadow-sm mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn-secondary-action" onclick="activarTabSocio('tab-list')">
                            <i class="bi bi-arrow-left"></i>
                            <span>Volver al Directorio</span>
                        </button>
                        <?php if($esEdicion): ?>

                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn-primary-action" id="btnGuardar">
                        <i class="bi bi-check2-circle"></i>
                        <span><?php echo $esEdicion ? 'Actualizar Socio de Negocio' : 'Guardar Socio de Negocio'; ?></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script>
    // ==========================================
    // ESTADO DE PAGINACIÓN Y FILTROS (PURO JS & CSS)
    // ==========================================
    let paginaActualSocios = 1;
    let registrosPorPaginaSocios = 25;
    let filasFiltradasSocios = [];

    // Auto-dismiss de alertas
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(el) {
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(() => el.remove(), 600);
        });
    }, 5000);

    // Inicializar al cargar el DOM
    document.addEventListener('DOMContentLoaded', function() {
        filtrarSocios();
    });

    // ==========================================
    // CAMBIO DE PESTAÑAS (LISTA VS FORMULARIO)
    // ==========================================
    function activarTabSocio(tabId) {
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

    // ==========================================
    // SINCRONIZACIÓN Y CAMBIO DE TIPO DE SOCIO
    // ==========================================
    function onCardTypeChange() {
        let tipo = $('#CardType').val();
        let esEdicion = $('#codigo_original').val();

        if (tipo === 'S') {
            // SEGUROS: sincronizar Grupo Comercial -> ASEGURADORAS (4)
            $('#GroupCode').val('4');
        } else if (tipo === 'P') {
            // PROVEEDOR / SUPLIDOR: sincronizar Grupo Comercial -> PROVEEDORES (5)
            $('#GroupCode').val('5');
        } else if (tipo === 'C') {
            // CLIENTE / PACIENTE: si estaba en aseguradoras o proveedores, cambiar a NO ASEGURADOS (2)
            let currentGroup = $('#GroupCode').val();
            if (currentGroup == '4' || currentGroup == '5' || !currentGroup) {
                $('#GroupCode').val('2');
            }
        }

        ajustarCamposSegunGrupo();
        autoGenerarCodigo();
    }

    // ==========================================
    // CONTROL DINÁMICO DE CAMPOS SEGÚN GRUPO COMERCIAL
    // ==========================================
    function ajustarCamposSegunGrupo() {
        let grupo = $('#GroupCode').val();
        let $carnet = $('#U_CadenaID');
        let $ars = $('#U_SubGrupo');
        let $chkARS = $('#QryGroup1');
        let arsVal = ($ars.val() || '').trim();

        if (grupo == '4') {
            // ASEGURADORAS:
            if ($('#CardType').val() !== 'S') {
                $('#CardType').val('S');
            }
            $chkARS.prop('checked', true);
            $carnet.prop('disabled', true).val('').attr('placeholder', 'No aplica para seguros').addClass('field-disabled');
            $ars.prop('disabled', true).val('').addClass('field-disabled');
            $('.carnet-req-star').hide();
            
            // Asignar comprobante de Crédito Fiscal Electrónico (E31)
            if (!$('#U_Comprob').val() || $('#U_Comprob').val() === 'E32') {
                $('#U_Comprob').val('E31');
            }
            if (!$('#U_TNegocio').val()) {
                $('#U_TNegocio').val('1'); // RNC
            }
        } else if (grupo == '5') {
            // PROVEEDORES:
            if ($('#CardType').val() !== 'P') {
                $('#CardType').val('P');
            }
            $chkARS.prop('checked', false);
            $carnet.prop('disabled', true).val('').attr('placeholder', 'No aplica para proveedores').addClass('field-disabled');
            $ars.prop('disabled', true).val('').addClass('field-disabled');
            $('.carnet-req-star').hide();
            
            if (!$('#U_Comprob').val() || $('#U_Comprob').val() === 'E32') {
                $('#U_Comprob').val('E31');
            }
        } else if (grupo == '1') {
            // ASEGURADOS:
            if ($('#CardType').val() === 'S' || $('#CardType').val() === 'P') {
                $('#CardType').val('C');
            }
            $carnet.prop('disabled', false).attr('placeholder', 'N° de afiliado ARS').removeClass('field-disabled');
            $ars.prop('disabled', false).removeClass('field-disabled');
            $chkARS.prop('checked', false);
            $('.carnet-req-star').show();
            if (!$('#U_Comprob').val()) {
                $('#U_Comprob').val('E32');
            }
        } else if (grupo == '2') {
            // NO ASEGURADOS:
            if ($('#CardType').val() === 'S' || $('#CardType').val() === 'P') {
                $('#CardType').val('C');
            }
            if (arsVal !== '') {
                // Si el usuario seleccionó una ARS, el paciente pasa automáticamente a Asegurados
                $('#GroupCode').val('1');
                ajustarCamposSegunGrupo();
                return;
            }
            $carnet.prop('disabled', true).val('').attr('placeholder', 'No aplica a pacientes no asegurados').addClass('field-disabled');
            $ars.prop('disabled', true).val('').addClass('field-disabled');
            $chkARS.prop('checked', false);
            $('.carnet-req-star').hide();
            if (!$('#U_Comprob').val()) {
                $('#U_Comprob').val('E32');
            }
        } else {
            // OTROS GRUPOS:
            $carnet.prop('disabled', false).attr('placeholder', 'N° de afiliado').removeClass('field-disabled');
            $ars.prop('disabled', false).removeClass('field-disabled');
            $chkARS.prop('checked', false);
            if (arsVal !== '') {
                $('.carnet-req-star').show();
            } else {
                $('.carnet-req-star').hide();
            }
            if (!$('#U_Comprob').val()) {
                $('#U_Comprob').val('E32');
            }
        }
    }

    function onGroupCodeChange() {
        let grupo = $('#GroupCode').val();
        let tipoActual = $('#CardType').val();

        if (grupo == '4' && tipoActual !== 'S') {
            $('#CardType').val('S');
            autoGenerarCodigo();
        } else if (grupo == '5' && tipoActual !== 'P') {
            $('#CardType').val('P');
            autoGenerarCodigo();
        } else if ((grupo == '1' || grupo == '2') && (tipoActual === 'S' || tipoActual === 'P')) {
            $('#CardType').val('C');
            autoGenerarCodigo();
        }

        ajustarCamposSegunGrupo();
    }

    // ==========================================
    // LIMPIAR FORMULARIO PARA NUEVO SOCIO
    // ==========================================
    function limpiarFormSocio() {
        document.getElementById('formSocio').reset();
        document.getElementById('codigo_original').value = '';
        document.getElementById('U_expediente').value = 0;
        document.getElementById('CardCode').removeAttribute('readonly');
        document.getElementById('tab-form-title').textContent = 'Ficha del Socio';
        document.getElementById('validFor').checked = true;

        // Valores por defecto para nuevo socio
        $('#CardType').val('C');
        $('#GroupCode').val('2');       // NO ASEGURADOS
        $('#GroupNum').val('1');        // CONTADO
        $('#U_Comprob').val('E32');     // Factura Consumo Electrónica E32
        $('#U_TNegocio').val('2');      // Cédula
        $('#QryGroup1').prop('checked', false);

        ajustarCamposSegunGrupo();
        autoGenerarCodigo();

        document.getElementById('btnGuardar').innerHTML = '<i class="bi bi-check2-circle"></i> <span>Guardar Socio de Negocio</span>';
    }

    // ==========================================
    // DESPLEGAR PROPIEDADES EXTENDIDAS
    // ==========================================
    function togglePropsExtendidas() {
        const grid = document.getElementById('propsExtendedGrid');
        const chevron = document.getElementById('propsChevron');
        const isOpen = grid.classList.contains('open');

        if (isOpen) {
            grid.classList.remove('open');
            chevron.className = 'bi bi-chevron-down';
        } else {
            grid.classList.add('open');
            chevron.className = 'bi bi-chevron-up';
        }
    }

    // ==========================================
    // FILTRO EN VIVO DEL DIRECTORIO DE SOCIOS
    // ==========================================
    function filtrarSocios() {
        const texto = document.getElementById('filtroTexto').value.toLowerCase().trim();
        const tipo = document.getElementById('filtroTipo').value;
        const grupo = document.getElementById('filtroGrupo') ? document.getElementById('filtroGrupo').value : 'todos';
        const estado = document.getElementById('filtroEstado').value;

        const todasFilas = Array.from(document.querySelectorAll('.fila-socio'));
        filasFiltradasSocios = [];

        todasFilas.forEach(fila => {
            const nombre = fila.getAttribute('data-nombre') || '';
            const codigo = fila.getAttribute('data-codigo') || '';
            const nit = fila.getAttribute('data-nit') || '';
            const exp = fila.getAttribute('data-exp') || '';
            const tel = fila.getAttribute('data-telefono') || '';
            const fTipo = fila.getAttribute('data-tipo') || '';
            const fGrupo = fila.getAttribute('data-grupo') || '';
            const fEstado = fila.getAttribute('data-estado') || '';

            const coincideTexto = !texto || nombre.includes(texto) || codigo.includes(texto) || nit.includes(texto) || exp.includes(texto) || tel.includes(texto);
            const coincideTipo = (tipo === 'todos') || (fTipo === tipo);
            const coincideGrupo = (grupo === 'todos') || (fGrupo === grupo);
            const coincideEstado = (estado === 'todos') || (fEstado === estado);

            if (coincideTexto && coincideTipo && coincideGrupo && coincideEstado) {
                filasFiltradasSocios.push(fila);
            } else {
                fila.style.display = 'none';
            }
        });

        // Ocultar o mostrar mensaje si no hay registros
        const divSinSocios = document.getElementById('sin-socios');
        if (filasFiltradasSocios.length === 0) {
            divSinSocios.style.display = 'block';
        } else {
            divSinSocios.style.display = 'none';
        }

        paginaActualSocios = 1;
        renderizarPaginacionSocios();
    }

    function limpiarFiltrosSocios() {
        document.getElementById('filtroTexto').value = '';
        document.getElementById('filtroTipo').value = 'todos';
        if (document.getElementById('filtroGrupo')) document.getElementById('filtroGrupo').value = 'todos';
        document.getElementById('filtroEstado').value = 'todos';
        filtrarSocios();
    }

    // ==========================================
    // MOTOR DE PAGINACIÓN PURO CSS (SOCIOS)
    // ==========================================
    function cambiarLimitePaginacionSocios() {
        const val = document.getElementById('sociosPorPagina').value;
        registrosPorPaginaSocios = (val === 'all') ? -1 : parseInt(val, 10);
        paginaActualSocios = 1;
        renderizarPaginacionSocios();
    }

    function irAPaginaSocios(numPagina) {
        paginaActualSocios = numPagina;
        renderizarPaginacionSocios();
    }

    function renderizarPaginacionSocios() {
        const total = filasFiltradasSocios.length;
        const info = document.getElementById('purePaginacionInfoSocios');
        const resumen = document.getElementById('contador-socios');
        const nav = document.getElementById('purePaginacionNavSocios');

        if (total === 0) {
            if (info) info.textContent = 'Mostrando 0 socios';
            if (resumen) resumen.textContent = 'Mostrando 0 socios';
            if (nav) nav.innerHTML = '';
            return;
        }

        let totalPaginas = (registrosPorPaginaSocios === -1) ? 1 : Math.ceil(total / registrosPorPaginaSocios);
        if (paginaActualSocios > totalPaginas) paginaActualSocios = totalPaginas;
        if (paginaActualSocios < 1) paginaActualSocios = 1;

        let inicio = (registrosPorPaginaSocios === -1) ? 0 : (paginaActualSocios - 1) * registrosPorPaginaSocios;
        let fin = (registrosPorPaginaSocios === -1) ? total : Math.min(inicio + registrosPorPaginaSocios, total);

        // Mostrar únicamente las filas del rango
        filasFiltradasSocios.forEach((fila, index) => {
            if (index >= inicio && index < fin) {
                fila.style.display = '';
            } else {
                fila.style.display = 'none';
            }
        });

        // Actualizar textos informativos
        if (info) info.textContent = `Mostrando ${inicio + 1} a ${fin} de ${total} socios`;
        if (resumen) resumen.textContent = `Mostrando ${total} socios`;

        // Generar botones de navegación
        let htmlNav = '';

        // Botón Primero y Anterior
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaSocios(1)" ${paginaActualSocios === 1 ? 'disabled' : ''} title="Primera página"><i class="bi bi-chevron-double-left"></i></button>`;
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaSocios(${paginaActualSocios - 1})" ${paginaActualSocios === 1 ? 'disabled' : ''} title="Página anterior"><i class="bi bi-chevron-left"></i></button>`;

        // Rango de páginas numeradas
        let maxBotones = 5;
        let inicioPag = Math.max(1, paginaActualSocios - Math.floor(maxBotones / 2));
        let finPag = Math.min(totalPaginas, inicioPag + maxBotones - 1);
        if (finPag - inicioPag + 1 < maxBotones) {
            inicioPag = Math.max(1, finPag - maxBotones + 1);
        }

        for (let i = inicioPag; i <= finPag; i++) {
            const esActiva = (i === paginaActualSocios) ? 'active' : '';
            htmlNav += `<button type="button" class="pure-page-btn ${esActiva}" onclick="irAPaginaSocios(${i})">${i}</button>`;
        }

        // Botón Siguiente y Último
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaSocios(${paginaActualSocios + 1})" ${paginaActualSocios === totalPaginas ? 'disabled' : ''} title="Página siguiente"><i class="bi bi-chevron-right"></i></button>`;
        htmlNav += `<button type="button" class="pure-page-btn" onclick="irAPaginaSocios(${totalPaginas})" ${paginaActualSocios === totalPaginas ? 'disabled' : ''} title="Última página"><i class="bi bi-chevron-double-right"></i></button>`;

        if (nav) nav.innerHTML = htmlNav;
    }

    // ==========================================
    // AUTO-GENERAR CÓDIGO AL SELECCIONAR TIPO
    // ==========================================
    function autoGenerarCodigo() {
        let tipo = $('#CardType').val();
        let esEdicion = $('#codigo_original').val(); // Si tiene valor, es edición

        if (tipo === '' || (esEdicion && esEdicion !== '')) {
            if (esEdicion && esEdicion !== '') $('#CardCode').val(esEdicion);
            return;
        }

        $('#CardCode').val('Generando...').prop('disabled', true);

        $.get('index.php?action=generar_codigo_socio&prefijo=' + encodeURIComponent(tipo), function(resp){
            if (resp && resp.codigo) {
                $('#CardCode').val(resp.codigo).prop('disabled', false);
                
                // Si es Paciente/Cliente, foco al expediente
                if (tipo === 'C') {
                    $('#U_CodAnt').focus();
                }
            } else {
                $('#CardCode').val('').prop('disabled', false);
            }
        }, 'json').fail(function(){
            alert('Error al generar código.');
            $('#CardCode').val('').prop('disabled', false);
        });
    }

    // ==========================================
    // LÓGICA DE EXPEDIENTE (DISPARADO POR U_CodAnt)
    // ==========================================
    $('#U_CodAnt').on('blur', function(){
        let exp = $(this).val().trim();
        if(exp === '') return;

        let $input = $(this);
        $input.addClass('border-warning');
        
        let tipoActual = $('#CardType').val() || 'C';

        $.get('index.php?action=buscar_expediente&exp=' + encodeURIComponent(exp) + '&prefijo=' + encodeURIComponent(tipoActual), function(resp){
            if(resp.success){
                let d = resp.data;
                $('#CardName').val(d.NombreCompleto.trim());
                $('#Address').val(d.Direccion);
                $('#U_NIT').val(d.Cedula);
                $('#Phone1').val(d.Celular);
                $('#Phone2').val(d.TelefonoCasa);
                $('#U_expediente').val(d.expe_NumeroExpediente);

                // Auto-asignar tipo 'C' si no tiene uno seleccionado
                if (!$('#CardType').val()) {
                    $('#CardType').val('C');
                }

                // Asignar el código secuencial de socio automáticamente
                let esEdicion = $('#codigo_original').val().trim();
                let codigoActual = $('#CardCode').val().trim();
                if (d.next_card_code && (!codigoActual || esEdicion === '')) {
                    $('#CardCode').val(d.next_card_code).prop('disabled', false);
                }

                // Cédula por defecto
                if (d.Cedula && d.Cedula.trim() !== '') {
                    $('#U_TNegocio').val('2');
                }

                // Si tiene seguro / aseguradora vinculada
                if (d.tiene_seguro && d.CardCode_ARS) {
                    $('#GroupCode').val('1'); // 1 = ASEGURADOS
                    ajustarCamposSegunGrupo();

                    // Si la opción no existe aún en el select (ej. se creó nueva), agregarla
                    if ($("#U_SubGrupo option[value='" + d.CardCode_ARS + "']").length === 0) {
                        let optText = d.combo_ars_item ? d.combo_ars_item.NombreAseguradora : (d.CardCode_ARS + ' - ' + (d.Nombre_ARS || 'Aseguradora'));
                        $('#U_SubGrupo').append(new Option(optText, d.CardCode_ARS, true, true));
                    }
                    $('#U_SubGrupo').val(d.CardCode_ARS);
                    $('#U_CadenaID').val(d.NumeroAfiliado || '');
                } else {
                    $('#GroupCode').val('2'); // 2 = NO ASEGURADOS
                    ajustarCamposSegunGrupo();
                    $('#U_SubGrupo').val('');
                    $('#U_CadenaID').val('');
                }

                // Condición de pago CONTADO si está vacía
                if (!$('#GroupNum').val()) {
                    $('#GroupNum').val('1');
                }
                // Comprobante E32 si está vacío y disponible
                if (!$('#U_Comprob').val()) {
                    if ($("#U_Comprob option[value='E32']").length > 0) {
                        $('#U_Comprob').val('E32');
                    }
                }

                $input.removeClass('border-warning').css('border-color', '#10b981');
            } else {
                alert('Expediente clínico no encontrado en el sistema.');
                $input.removeClass('border-warning');
            }
        }, 'json').fail(function(){
            alert('Error de conexión al buscar expediente.');
            $input.removeClass('border-warning');
        });
    });

    // ==========================================
    // VALIDACIÓN ANTES DE GUARDAR
    // ==========================================
    function validarFormulario() {
        // Asegurar que los inputs se habiliten para no bloquear envío
        $('#U_CadenaID, #U_SubGrupo').prop('disabled', false);

        let tipo = $('#CardType').val();
        let nit = $('#U_NIT').val().trim();
        let codigo = $('#CardCode').val().trim();
        let tipoNegocio = $('#U_TNegocio').val();
        let grupo = $('#GroupCode').val();
        let ars = ($('#U_SubGrupo').val() || '').trim();
        let carnet = ($('#U_CadenaID').val() || '').trim();

        if(codigo === '') {
            alert('El código de socio es obligatorio.');
            ajustarCamposSegunGrupo();
            return false;
        }

        // VALIDACIÓN: Paciente asegurado o con ARS seleccionada requiere N° Carnet / Seguro
        if (grupo == '1' || ars !== '') {
            if (grupo == '1' && ars === '') {
                alert('Atención: Al marcar al socio como Asegurado en el grupo comercial, debe seleccionar la Aseguradora Vinculada (ARS).');
                $('#U_SubGrupo').focus();
                ajustarCamposSegunGrupo();
                return false;
            }
            if (carnet === '') {
                alert('Atención: El campo "N° Carnet / Seguro Médico" no puede estar vacío para pacientes asegurados con ARS.');
                $('#U_CadenaID').focus();
                ajustarCamposSegunGrupo();
                return false;
            }
        }

        // Validación de longitud de RNC / Cédula
        if(nit !== '' && tipoNegocio !== '') { 
            let nitLimpio = nit.replace(/-/g, '');
            if(tipoNegocio === '1' && nitLimpio.length !== 9) {
                alert('El Tipo de Negocio indica RNC (1). El RNC debe tener exactamente 9 dígitos.');
                ajustarCamposSegunGrupo();
                $('#U_NIT').focus();
                return false;
            }
            if(tipoNegocio === '2' && nitLimpio.length !== 11) {
                alert('El Tipo de Negocio indica Cédula (2). La Cédula debe tener exactamente 11 dígitos.');
                ajustarCamposSegunGrupo();
                $('#U_NIT').focus();
                return false;
            }
        }
        
        return true;
    }

    // Inicializar listeners al cargar el DOM
    document.addEventListener('DOMContentLoaded', function() {
        filtrarSocios();
        
        // Listener para cambio dinámico de Grupo Comercial
        $('#GroupCode').on('change', function() {
            onGroupCodeChange();
        });

        // Listener para cambio de Aseguradora Vinculada (ARS)
        $('#U_SubGrupo').on('change', function() {
            let arsVal = ($(this).val() || '').trim();
            if (arsVal !== '') {
                if ($('#GroupCode').val() != '1') {
                    $('#GroupCode').val('1');
                }
                ajustarCamposSegunGrupo();
                $('#U_CadenaID').focus();
            } else {
                if ($('#GroupCode').val() == '1') {
                    $('.carnet-req-star').show();
                } else {
                    $('.carnet-req-star').hide();
                }
            }
        });

        // Listener para cambio de Tipo de Identificación (RNC -> E31, Cédula/Otro -> E32)
        $('#U_TNegocio').on('change', function() {
            let tipoDoc = $(this).val();
            let grupo = $('#GroupCode').val();
            let compActual = $('#U_Comprob').val();
            if (tipoDoc === '1') {
                // RNC -> Sugerir Factura Crédito Fiscal Electrónica (E31) si está disponible
                if ($("#U_Comprob option[value='E31']").length > 0) {
                    if (compActual === 'E32' || !compActual) {
                        $('#U_Comprob').val('E31');
                    }
                }
            } else {
                // Cédula u otros -> Consumo Electrónico (E32) si está disponible
                if ($("#U_Comprob option[value='E32']").length > 0) {
                    if (compActual === 'E31' && grupo != '4' && grupo != '5') {
                        $('#U_Comprob').val('E32');
                    }
                }
            }
        });

        // Auto-generar código al cargar la pantalla si está vacío y no es modo edición
        if (!$('#codigo_original').val() && !$('#CardCode').val()) {
            autoGenerarCodigo();
        }

        // Aplicar estado inicial de los campos según el grupo cargado
        ajustarCamposSegunGrupo();
    });
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>