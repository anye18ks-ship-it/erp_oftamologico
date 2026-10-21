<?php
// Configuración conexión SQL Server
$serverName = "SERVIDOR\SQLEXPRESS";
$connectionOptions = [
    "Database" => "CMS",
    "Uid" => "cms2",
    "PWD" => "cms2026",
    "CharacterSet" => "UTF-8"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die("Error de conexión: " . print_r(sqlsrv_errors(), true));
}

// Parámetros de filtro y ordenación
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : null;
$dgii_state = isset($_GET['dgii_state']) && !empty($_GET['dgii_state']) ? $_GET['dgii_state'] : null;
$sort_by = isset($_GET['sort_by']) && !empty($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
$sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] === 'DESC' ? 'DESC' : 'ASC';

// Validar columnas de ordenación para prevenir inyección
$valid_columns = ['id_record','doc_id', 'numatcard','date_process','ecf','ecf_rnc_emisor','ecf_rnc_comprador','xml_security_code', 'xml_file_sign_date','xml_file_qrcode','xml_ecf_status_name'];
if (!in_array($sort_by, $valid_columns)) {
    $sort_by = 'id_record';
}
// --DATEPART(month, GETDATE()) = DATEPART(month, date_process)
// Construir consulta SQL con filtros dinámicos
$sql = " SELECT id_record, doc_id, numatcard, date_process, ecf, 
               ecf_rnc_emisor, ecf_rnc_comprador, xml_security_code, 
               xml_file_sign_date, xml_file_qrcode, xml_ecf_status_name
        FROM cms.dbo.ecf_xml_invoice ";

  //      WHERE date_process >= getdate()-7 ";
 /*
$conditions = [];
$params = [];
 // convert(varchar,validation_date,111 )
if ($start_date) {
    $conditions[] = " date_process >= ?";
    $params[] = $start_date;
}
if ($end_date) {
    $conditions[] = " date_process <= ?";
    $params[] = $end_date . ' 23:59:59';
}
*/

$conditions = [];
$params = [];
if ($start_date && $end_date) {
    // Ambas fechas → entre rango
    $conditions[] = " date_process BETWEEN ? AND ? ";
    $params[] = $start_date . ' 00:00:00';
    $params[] = $end_date . ' 23:59:59';
} elseif ($start_date) {
    // Solo fecha inicio → exacta
    $conditions[] = " CONVERT(date, date_process) = CONVERT(date, ?) ";
    $params[] = $start_date;
} elseif ($end_date) {
    // Solo fecha fin → exacta
    $conditions[] = " CONVERT(date, date_process) = CONVERT(date, ?) ";
    $params[] = $end_date;
} else {
    // Sin fechas → últimos 7 días
    $conditions[] = " date_process >= DATEADD(DAY, -7, GETDATE()) ";
}

if ($dgii_state) {
    $conditions[] = " xml_ecf_status_name = ?";
    $params[] = $dgii_state;
}

// Añadir condiciones al WHERE si existen
/*if (!empty($conditions)) {
	
    $sql .=" AND ". implode(" AND ", $conditions);
}*/

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY " . $sort_by . " " . $sort_order;
echo $sql;
$stmt = sqlsrv_prepare($conn, $sql, $params);

if ($stmt === false) {
    die("Error en consulta: " . print_r(sqlsrv_errors(), true));
}

if (!sqlsrv_execute($stmt)) {
    die("Error al ejecutar consulta: " . print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Documentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .table th, .table td { vertical-align: middle; }
        .table th a { color: inherit; text-decoration: none; }
        .table th a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">Listado de Documentos</h2>

        <!-- Formulario de filtros -->
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date ?? '') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="dgii_state" class="form-label">DGII Estado</label>
                    <select class="form-select" id="dgii_state" name="dgii_state">
                        <option value="">Todos</option>
                        <option value="Aceptado" <?= $dgii_state === 'Aceptado' ? 'selected' : '' ?>>Aceptado</option>
                        <option value="Aceptado Condicional" <?= $dgii_state === 'Aceptado Condicional' ? 'selected' : '' ?>>Aceptado Condicional</option>
                        <option value="Rechazado" <?= $dgii_state === 'Rechazado' ? 'selected' : '' ?>>Rechazado</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </div>
        </form>

		<div class="mb-3">
    <button id="exportBtn" class="btn btn-success">Exportar a Excel</button>
</div>
		
        <!-- Tabla de resultados -->
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <?php
                    $columns = [
    'id_record' => 'ID',
    'doc_id' => 'Doc ID',
    'numatcard' => 'Num At Card',
    'date_process' => 'Fecha Proceso',
    'ecf' => 'ECF',
    'ecf_rnc_emisor' => 'Emisor',
    'ecf_rnc_comprador' => 'Comprador',
    'xml_security_code' => 'Código Seguridad',
    'xml_file_sign_date' => 'Fecha Firma',
    'xml_file_qrcode' => 'QR',
    'xml_ecf_status_name' => 'Estado DGII'
];

                    foreach ($columns as $col => $label) {
                        $new_order = ($sort_by === $col && $sort_order === 'ASC') ? 'DESC' : 'ASC';
                        echo "<th><a href=\"?sort_by=$col&sort_order=$new_order&start_date=" . urlencode($start_date ?? '') . "&end_date=" . urlencode($end_date ?? '') . "&status=" . urlencode($status ?? '') . "&dgii_state=" . urlencode($dgii_state ?? '') . "\">$label " . ($sort_by === $col ? ($sort_order === 'ASC' ? '↑' : '↓') : '') . "</a></th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)):
                    $validationDate = ($row['date_process'] instanceof DateTime) ? $row['date_process']->format('Y-m-d H:i:s') : ($row['date_process'] ?? '-');
                    $dgii_state_display = $row['xml_ecf_status_name'] ?? '-';
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id_record']) ?></td>
                        <td><?= htmlspecialchars($row['doc_id'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['numatcard'] ?? '-') ?></td>
                        <td><?= $row['date_process'] ? $row['date_process']->format('Y-m-d') : '-' ?></td>
                        <td><a href="detalle.php?id=<?= urlencode($row['id_record']) ?>" class="text-primary"><?= htmlspecialchars($row['ecf'] ?? '-') ?></a></td>
						<td><?= htmlspecialchars($row['ecf_rnc_emisor'] ?? '-') ?></td>
						<td><?= htmlspecialchars($row['ecf_rnc_comprador'] ?? '-') ?></td>
						<td><?= htmlspecialchars($row['xml_security_code'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($validationDate) ?></td>
                        
                        <td>
                            <?php if (!empty($row['xml_file_qrcode'])): ?>
                                <a href="<?= htmlspecialchars($row['xml_file_qrcode']) ?>" target="_blank" rel="noopener" class="text-primary">Ver QR</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($dgii_state_display) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php if (sqlsrv_has_rows($stmt) === false): ?>
            <div class="alert alert-warning">No se encontraron resultados para los filtros aplicados.</div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
	
	<script>
	document.getElementById('exportBtn').addEventListener('click', function () {
		let table = document.querySelector('table');
		let rows = Array.from(table.querySelectorAll('tr'));
		let csvContent = rows.map(row => {
			let cols = Array.from(row.querySelectorAll('th, td'));
			return cols.map(col => `"${col.innerText.replace(/"/g, '""')}"`).join(',');
		}).join('\n');

		let blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
		let url = URL.createObjectURL(blob);
		let link = document.createElement("a");
		link.setAttribute("href", url);
		link.setAttribute("download", "documentos.csv");
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	});
	</script>

	
    <?php
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    ?>
</body>
</html>