<?php require_once __DIR__ . '/../Estructura/header.php'; ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="views/Facturacion/styles/consulta_retiro.css">

<div class="container-fluid consulta-container">
    
    <!-- Header de Página -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <h2 class="page-title">Historial y Reimpresión de Retiros</h2>
                <p class="page-subtitle">Consulte todas las salidas de efectivo registradas y reimprima los comprobantes de retiro</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?page=retiro_caja" class="btn-header-action btn-header-danger">
                <i class="bi bi-dash-circle"></i> Registrar Retiro
            </a>
            <a href="index.php?page=dashboard" class="btn-header-action btn-header-home">
                <i class="bi bi-house"></i> Inicio
            </a>
        </div>
    </div>

    <div id="alertContainer">
        <?php echo Helper::getMensaje(); ?>
    </div>

    <div class="consulta-card">
        
        <!-- Barra de Filtros por Rango de Fechas -->
        <div class="consulta-filter-bar">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-list-check text-danger fs-5"></i>
                <span class="fw-bold text-dark">Registros de Salidas de Caja</span>
            </div>

            <div class="filter-controls-group">
                <div class="filter-date-group">
                    <span class="filter-date-label"><i class="bi bi-calendar-event me-1"></i>Desde:</span>
                    <input type="date" id="filtroDesde" class="filter-date-input">
                </div>
                <div class="filter-date-group">
                    <span class="filter-date-label"><i class="bi bi-calendar-event me-1"></i>Hasta:</span>
                    <input type="date" id="filtroHasta" class="filter-date-input">
                </div>
                <button type="button" onclick="filtrarRetiros()" class="btn-filter-apply-danger">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <button type="button" onclick="limpiarRetiros()" class="btn-filter-clear">
                    <i class="bi bi-x-circle text-secondary"></i> Limpiar
                </button>
            </div>
        </div>

        <div class="consulta-card-body">
            <table id="tablaRetiros" class="table table-hover align-middle table-sm" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th style="width: 6%;">ID</th>
                        <th style="width: 14%;">N° Retiro</th>
                        <th style="width: 12%;">Fecha Caja</th>
                        <th style="width: 12%;">Caja / Terminal</th>
                        <th style="width: 14%;">Concepto</th>
                        <th style="width: 12%;">Medio</th>
                        <th style="width: 12%;" class="text-end">Monto Retirado</th>
                        <th style="width: 16%;">Quien Recibe</th>
                        <th style="width: 12%;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($retiros as $r): ?>
                    <tr>
                        <td class="font-monospace text-muted"><?php echo $r['id_retiro_caja']; ?></td>
                        <td>
                            <span class="badge-retiro">
                                RET-<?php echo str_pad($r['id_retiro_caja'], 7, '0', STR_PAD_LEFT); ?>
                            </span>
                        </td>
                        <td class="font-monospace text-secondary"><?php echo $r['fecha_caja']; ?></td>
                        <td class="fw-semibold text-primary"><?php echo htmlspecialchars($r['caja']); ?></td>
                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($r['concepto']); ?></span></td>
                        <td><?php echo htmlspecialchars($r['medio']); ?></td>
                        <td class="text-end monto-retiro-text">
                            RD$ <?php echo number_format($r['monto'] ?? 0, 2); ?>
                        </td>
                        <td class="fw-semibold text-dark"><?php echo htmlspecialchars($r['quien_recibe']); ?></td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1">
                                <a href="index.php?action=reporte_retiro_caja&id=<?php echo $r['id_retiro_caja']; ?>" 
                                   class="btn-action-print" target="_blank" title="Imprimir Original">
                                    <i class="bi bi-printer"></i> Imprimir
                                </a>
                                <a href="index.php?action=reporte_retiro_caja&id=<?php echo $r['id_retiro_caja']; ?>&copia=1" 
                                   class="btn-action-print" target="_blank" title="Imprimir Copia">
                                    Copia
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>


<style>
    /* Espaciar el select de "Mostrar X registros" */
    div.dataTables_length select {
        width: 80px !important;
        min-width: 80px !important;
        margin: 0 10px !important;
        padding-left: 12px !important;
        padding-right: 35px !important;
        display: inline-block !important;
    }
    
    /* Ajustar el contenedor del length menu */
    div.dataTables_length {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
</style>

<script>
    var miTablaRetiros;
    var datosOriginales = [];

    // Auto-dismiss de mensajes de alerta tras 5 segundos
    setTimeout(function() {
        const alerts = document.querySelectorAll('#alertContainer .alert');
        alerts.forEach(function(el) {
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(() => el.remove(), 600);
        });
    }, 5000);

    $(document).ready(function() {
        miTablaRetiros = $('#tablaRetiros').DataTable({ 
            language: {
                processing: "Procesando...",
                search: "Buscar en tabla:",
                lengthMenu: "Mostrar _MENU_ registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros en total)",
                loadingRecords: "Cargando...",
                zeroRecords: "No se encontraron registros coincidentes",
                emptyTable: "No hay datos disponibles en la tabla",
                paginate: {
                    first: "Primero",
                    previous: "Anterior",
                    next: "Siguiente",
                    last: "Último"
                }
            },
            pageLength: 25, 
            order: [[ 0, 'desc' ]], 
            columnDefs: [ { orderable: false, targets: 8 } ],
            initComplete: function() {
                // Asegurar que el select tenga las clases de Bootstrap
                $('div.dataTables_length select').addClass('form-select form-select-sm');
            }
        });
        
        datosOriginales = miTablaRetiros.rows().data().toArray();
    });

    function filtrarRetiros() {
        var desde = document.getElementById('filtroDesde').value;
        var hasta = document.getElementById('filtroHasta').value;

        var datosFiltrados = datosOriginales.filter(function(fila) {
            if (!desde && !hasta) return true;

            var partes = fila[2].trim().split('/');
            if(partes.length !== 3) return true;
            
            var fechaFila = new Date(partes[2], partes[1] - 1, partes[0]);

            var minDate = desde ? new Date(desde + "T00:00:00") : null;
            var maxDate = hasta ? new Date(hasta + "T23:59:59") : null;

            if (minDate && fechaFila < minDate) return false;
            if (maxDate && fechaFila > maxDate) return false;

            return true;
        });

        miTablaRetiros.clear().rows.add(datosFiltrados).draw();
    }

    function limpiarRetiros() {
        document.getElementById('filtroDesde').value = '';
        document.getElementById('filtroHasta').value = '';
        miTablaRetiros.clear().rows.add(datosOriginales).draw();
    }
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>
