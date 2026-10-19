<?php require_once __DIR__ . '/../Estructura/header.php'; ?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="views/Facturacion/styles/consulta_cierre.css">

<div class="container-fluid consulta-container">
    
    <!-- Header de Página -->
    <div class="page-header-module">
        <div class="page-header-title">
            <div class="page-header-icon">
                <i class="bi bi-search"></i>
            </div>
            <div>
                <h2 class="page-title">Historial y Reimpresión de Cierres de Caja</h2>
                <p class="page-subtitle">Consulte los arqueos de caja finalizados y reimprima los reportes de liquidación fiscal (Z)</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?page=cierre_caja" class="btn-header-action btn-header-primary">
                <i class="bi bi-lock-fill"></i> Ir a Cierre de Turno
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
                <i class="bi bi-list-check text-primary fs-5"></i>
                <span class="fw-bold text-dark">Registros de Cierre Fiscal</span>
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
                <button type="button" onclick="filtrarCierres()" class="btn-filter-apply">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <button type="button" onclick="limpiarCierres()" class="btn-filter-clear">
                    <i class="bi bi-x-circle text-secondary"></i> Limpiar
                </button>
            </div>
        </div>

        <div class="consulta-card-body">
            <table id="tablaCierres" class="table table-hover align-middle table-sm" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th style="width: 8%;">ID</th>
                        <th style="width: 14%;">Arqueo Z</th>
                        <th style="width: 14%;">Terminal / Caja</th>
                        <th style="width: 20%;">Fecha / Hora Cierre</th>
                        <th style="width: 18%;">Cajero de Cierre</th>
                        <th style="width: 14%;" class="text-end">Total Declarado</th>
                        <th style="width: 12%;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cierres as $c): ?>
                    <tr>
                        <td class="font-monospace text-muted"><?php echo $c['id_diario_caja']; ?></td>
                        <td>
                            <span class="badge-zcut">
                                Z-<?php echo str_pad($c['orden_cierre'] ?? $c['id_diario_caja'], 6, '0', STR_PAD_LEFT); ?>
                            </span>
                        </td>
                        <td class="fw-semibold text-primary"><?php echo htmlspecialchars($c['caja']); ?></td>
                        <td class="font-monospace text-secondary"><?php echo $c['fecha_cierre']; ?></td>
                        <td><?php echo htmlspecialchars($c['cajero_cierre'] ?? 'Sin cajero'); ?></td>
                        <td class="text-end monto-declarado-text">
                            RD$ <?php echo number_format($c['monto_declarado'] ?? 0, 2); ?>
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-1">
                                <a href="index.php?action=reporte_cierre_caja&id=<?php echo $c['id_diario_caja']; ?>" 
                                   class="btn-action-print" target="_blank" title="Imprimir Original">
                                    <i class="bi bi-printer"></i> Imprimir
                                </a>
                                <a href="index.php?action=reporte_cierre_caja&id=<?php echo $c['id_diario_caja']; ?>&copia=1" 
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
    var miTablaCierres;
    var datosOriginalesCierres = [];

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
        miTablaCierres = $('#tablaCierres').DataTable({ 
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
            columnDefs: [ { orderable: false, targets: 6 } ],
            initComplete: function() {
                // Asegurar que el select tenga las clases de Bootstrap
                $('div.dataTables_length select').addClass('form-select form-select-sm');
            }
        });
        
        datosOriginalesCierres = miTablaCierres.rows().data().toArray();
    });

    function filtrarCierres() {
        var desde = document.getElementById('filtroDesde').value;
        var hasta = document.getElementById('filtroHasta').value;

        var datosFiltrados = datosOriginalesCierres.filter(function(fila) {
            if (!desde && !hasta) return true;

            var soloFecha = fila[3].split(' ')[0]; 
            var partes = soloFecha.split('/');
            if(partes.length !== 3) return true;
            
            var fechaFila = new Date(partes[2], partes[1] - 1, partes[0]);

            var minDate = desde ? new Date(desde + "T00:00:00") : null;
            var maxDate = hasta ? new Date(hasta + "T23:59:59") : null;

            if (minDate && fechaFila < minDate) return false;
            if (maxDate && fechaFila > maxDate) return false;

            return true;
        });

        miTablaCierres.clear().rows.add(datosFiltrados).draw();
    }

    function limpiarCierres() {
        document.getElementById('filtroDesde').value = '';
        document.getElementById('filtroHasta').value = '';
        miTablaCierres.clear().rows.add(datosOriginalesCierres).draw();
    }
</script>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>