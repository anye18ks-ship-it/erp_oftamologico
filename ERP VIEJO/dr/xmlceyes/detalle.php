<?php
// Configuración conexión SQL Server
$serverName = "SERVIDOR\SQLEXPRESS";
$connectionOptions = [
    "Database" => "CMS",
    "Uid" => "cms",
    "PWD" => "cms2026",
    "CharacterSet" => "UTF-8"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die("Error de conexión: " . print_r(sqlsrv_errors(), true));
}

// Validar ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("ID inválido.");
}

// Consulta SQL
$sql = "SELECT id_record,doc_id, numatcard,date_process,ecf,ecf_rnc_emisor,ecf_rnc_comprador,xml_security_code, xml_file_sign_date,xml_file_qrcode, xml_ecf_status_name,xml_file,xml_track_response,xml_file_signed FROM cms.dbo.ecf_xml_invoice WHERE id_record = ?";
$params = [$id];

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die("Error en consulta: " . print_r(sqlsrv_errors(), true));
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$row) {
    die("Registro no encontrado.");
}

// Formatear fechas
$validationDate = ($row['xml_file_sign_date'] instanceof DateTime) ? $row['xml_file_sign_date']->format('Y-m-d H:i:s') : ($row['validation_date'] ?? '-');
$insertDate = ($row['date_process'] instanceof DateTime) ? $row['date_process']->format('Y-m-d H:i:s') : ($row['date_process'] ?? '-');

// Formatear XML
function formatXML($xml) {
    if (empty($xml)) return '-';
    try {
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($xml);
        return $doc->saveXML();
    } catch (Exception $e) {
        return htmlspecialchars($xml); // Fallback si el XML es inválido
    }
}

// Formatear JSON
function formatJSON($json) {
    if (empty($json)) return '-';
    try {
        $decoded = json_decode($json, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return htmlspecialchars($json); // Fallback si el JSON es inválido
        }
        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    } catch (Exception $e) {
        return htmlspecialchars($json); // Fallback si el JSON es inválido
    }
}

$xml_original_formatted = formatXML($row['xml_file']);
$xml_receive_formatted = formatXML($row['xml_track_response']);
$xml_firmado_formatted = formatXML($row['xml_file_signed']);
//$json_receive_formatted = formatJSON($row['json_receive']);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Registro ID <?= htmlspecialchars($row['id']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
        .card { border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .card-header { background-color: #e9ecef; font-weight: bold; }
        .accordion-button { font-weight: 600; }
        pre { background: #1e1e1e; color: #d4d4d4; padding: 16px; border-radius: 4px; overflow-x: auto; }
        .btn-primary { background-color: #007bff; border-color: #007bff; }
    </style>
</head>
<body>
    <div class="container mt-4 mb-4">
        <h2 class="mb-4">Detalle del Comprobante (<?= htmlspecialchars($row['ecf'] ) ?>)</h2>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">ID:</label>
                        <div><?= htmlspecialchars($row['id_record']) ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Documento:</label>
                        <div><?= htmlspecialchars($row['doc_id'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Referencia:</label>
                        <div><?= htmlspecialchars($row['numatcard'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Fecha Proceso:</label>
                        <div><?= htmlspecialchars($row['date_process'] ? $row['date_process']->format('Y-m-d H:i') : '-' ?? '-') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Vendedor:</label>
                        <div><?= htmlspecialchars($row['ecf_rnc_emisor'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">ENCF:</label>
                        <div><?= htmlspecialchars($row['ecf'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Comprador:</label>
                        <div><?= htmlspecialchars($row['ecf_rnc_comprador'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Fecha Validación:</label>
                        <div><?= htmlspecialchars($validationDate) ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Código Validación:</label>
                        <div><?= htmlspecialchars($row['xml_security_code'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">URL QR:</label>
                        <?php if (!empty($row['xml_file_qrcode'])): ?>
                            <div><a href="<?= htmlspecialchars($row['xml_file_qrcode']) ?>" target="_blank" rel="noopener" class="text-primary">Ver QR</a></div>
                        <?php else: ?>
                            <div>-</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Estado DGII:</label>
                        <div><?= htmlspecialchars($row['xml_ecf_status_name'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Fecha Insert:</label>
                        <div><?= htmlspecialchars($insertDate) ?></div>
                    </div>
                </div>

                <!-- Acordeón para XML y JSON -->
                <div class="accordion mt-3" id="dataAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="xmlOriginalHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#xmlOriginalCollapse" aria-expanded="false" aria-controls="xmlOriginalCollapse">
                                XML Original
                            </button>
                        </h2>
                        <div id="xmlOriginalCollapse" class="accordion-collapse collapse" aria-labelledby="xmlOriginalHeading" data-bs-parent="#dataAccordion">
                            <div class="accordion-body">
								<div class="accordion-body position-relative">
								<button class="btn btn-sm btn-secondary copy-btn" data-target="xmlOriginalContent" style="position:absolute; top:10px; right:10px;">
									Copiar
								</button>
								<pre id="xmlOriginalContent"><code class="language-xml"><?= htmlspecialchars($xml_original_formatted) ?></code></pre>
							</div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="xmlReceiveHeading">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#xmlReceiveCollapse" aria-expanded="false" aria-controls="xmlReceiveCollapse">
                                XML Recibido
                            </button>
                        </h2>
                        <div id="xmlReceiveCollapse" class="accordion-collapse collapse" aria-labelledby="xmlReceiveHeading" data-bs-parent="#dataAccordion">
                            <div class="accordion-body">
                                <div class="accordion-body position-relative">
								<button class="btn btn-sm btn-secondary copy-btn" data-target="xmlReceiveContent" style="position:absolute; top:10px; right:10px;">
									Copiar
								</button>
								<pre id="xmlReceiveContent"><code class="language-xml"><?= htmlspecialchars($xml_receive_formatted) ?></code></pre>
							</div>

                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="xmlReceiveHeading2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#xmlReceiveCollapse2" aria-expanded="false" aria-controls="xmlReceiveCollapse2">
                                XML Firmado
                            </button>
                        </h2>
                        <div id="xmlReceiveCollapse2" class="accordion-collapse collapse" aria-labelledby="xmlReceiveHeading2" data-bs-parent="#dataAccordion">
                            <div class="accordion-body">
                                <div class="accordion-body position-relative">
								<button class="btn btn-sm btn-secondary copy-btn" data-target="xmlFirmadoContent" style="position:absolute; top:10px; right:10px;">
									Copiar
								</button>
								<pre id="xmlFirmadoContent"><code class="language-xml"><?= htmlspecialchars($xml_firmado_formatted) ?></code></pre>
							</div>

                            </div>
                        </div>
                    </div>
                </div>

                <a href="index.php" class="btn btn-primary mt-4">← Regresar al listado</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-xml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-json.min.js"></script>
	
	<script>
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const content = document.getElementById(targetId).innerText;

        navigator.clipboard.writeText(content).then(() => {
            this.innerText = "¡Copiado!";
            this.classList.remove("btn-secondary");
            this.classList.add("btn-success");

            setTimeout(() => {
                this.innerText = "Copiar";
                this.classList.remove("btn-success");
                this.classList.add("btn-secondary");
            }, 2000);
        });
    });
});
</script>

	
</body>
</html>