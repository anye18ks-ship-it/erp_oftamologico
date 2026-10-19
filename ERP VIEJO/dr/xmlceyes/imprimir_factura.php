<?php
//require_once 'db_connection.php';


// Configuración conexión SQL Server
$serverName = "SERVIDOR\SQLEXPRESS";
$connectionOptions = [
    "Database" => "CMS",
    "Uid" => "CMS",
    "PWD" => "cms2026",
    "CharacterSet" => "UTF-8"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

// Validar ID
 $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("ID inválido.");
}

 //$conn = getDbConnection();

// Consulta SQL para obtener datos de la factura
 $sql = "SELECT 
        e.id_record, e.doc_id, e.numatcard, e.date_process, e.ecf, 
        e.ecf_rnc_emisor, e.ecf_rnc_comprador, e.xml_security_code, 
        e.xml_file_sign_date, e.xml_file_qrcode, e.xml_ecf_status_name,
        e.xml_file, e.xml_file_signed
        FROM cms.dbo.ecf_xml_invoice e
        WHERE e.id_record = ?";
 $params = [$id];

 $stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die("Error en consulta: " . print_r(sqlsrv_errors(), true));
}

 $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$row) {
    die("Registro no encontrado.");
}

// Parsear el XML para obtener detalles adicionales
 $xml = simplexml_load_string($row['xml_file_signed']);

// Obtener datos del emisor y receptor
 $emisor = $xml->Emisor;
 $receptor = $xml->Receptor;
 $encabezado = $xml->Encabezado;
 $detalles = $xml->Detalles;

// Datos del emisor
 $rncEmisor = (string)$emisor->RNCEmisor;
 $nombreEmisor = (string)$emisor->NombreComercial;
 $actividadEconomica = (string)$emisor->ActividadEconomica;

// Datos del receptor
 $rncReceptor = (string)$receptor->RNCComprador;
 $nombreReceptor = (string)$receptor->NombreComprador;

// Datos de la factura
 $encf = (string)$encabezado->Id->Encf;
 $fechaEmision = (string)$encabezado->FechaEmision;
 $fechaVencimiento = isset($encabezado->FechaVencimiento) ? (string)$encabezado->FechaVencimiento : '';
 $moneda = (string)$encabezado->Moneda;
 $montoTotal = (float)$encabezado->TotalMonto;

// Generar código QR
require_once('phpqrcode/qrlib.php');

// Ruta temporal para Windows
 $tempDir = sys_get_temp_dir();
 $qrTempFile = tempnam($tempDir, 'qr_') . '.png';

// Generar el código QR
QRcode::png($row['xml_file_qrcode'], $qrTempFile, 'L', 4, 0);

// Convertir a base64 para incrustar en HTML
 $qrData = file_get_contents($qrTempFile);
 $qrBase64 = base64_encode($qrData);

// Eliminar archivo temporal
unlink($qrTempFile);

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?= htmlspecialchars($encf) ?></title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-family: 'Courier New', monospace;
                font-size: 10px;
                width: 72mm; /* Ancho típico de impresora térmica */
            }
            .no-print {
                display: none !important;
            }
            .break-after {
                page-break-after: always;
            }
        }
        
        body {
            margin: 0;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 10px;
            max-width: 72mm;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 10px;
            font-size: 9px;
        }
        
        .invoice-info, .customer-info {
            font-size: 9px;
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }
        
        .items-table th, .items-table td {
            padding: 2px 0;
        }
        
        .items-table th {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            font-weight: bold;
        }
        
        .items-table td {
            border-bottom: 1px dashed #ccc;
        }
        
        .totals {
            text-align: right;
            margin-bottom: 10px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .qr-section {
            text-align: center;
            margin-top: 15px;
        }
        
        .qr-code img {
            width: 120px !important;
            height: 120px !important;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 8px;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }
        
        .btn-container {
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="btn-container no-print">
        <button onclick="window.print()" class="btn btn-primary">Imprimir Factura</button>
        <a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
    </div>
    
    <div class="header">
        COMPROBANTE FISCAL ELECTRÓNICO
    </div>
    
    <div class="company-info">
        <strong><?= htmlspecialchars($nombreEmisor) ?></strong><br>
        RNC: <?= htmlspecialchars($rncEmisor) ?><br>
        Actividad Económica: <?= htmlspecialchars($actividadEconomica) ?>
    </div>
    
    <div class="invoice-info">
        <strong>ENCF:</strong> <?= htmlspecialchars($encf) ?><br>
        <strong>Fecha Emisión:</strong> <?= htmlspecialchars($fechaEmision) ?><br>
        <?php if (!empty($fechaVencimiento)): ?>
        <strong>Fecha Vencimiento:</strong> <?= htmlspecialchars($fechaVencimiento) ?><br>
        <?php endif; ?>
        <strong>Moneda:</strong> <?= htmlspecialchars($moneda) ?>
    </div>
    
    <div class="customer-info">
        <strong>Cliente:</strong> <?= htmlspecialchars($nombreReceptor) ?><br>
        <strong>RNC/Cédula:</strong> <?= htmlspecialchars($rncReceptor) ?>
    </div>
    
    <table class="items-table">
        <thead>
            <tr>
                <th>Descripción</th>
                <th>Cant.</th>
                <th>Precio</th>
                <th>ITBIS</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalles->Detalle as $detalle): ?>
            <tr>
                <td><?= htmlspecialchars((string)$detalle->Descripcion) ?></td>
                <td><?= htmlspecialchars((string)$detalle->Cantidad) ?></td>
                <td><?= number_format((float)$detalle->PrecioUnitario, 2) ?></td>
                <td><?= number_format((float)$detalle->MontoITBIS, 2) ?></td>
                <td><?= number_format((float)$detalle->MontoTotal, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="totals">
        <strong>Subtotal:</strong> <?= number_format((float)$encabezado->MontoTotal - (float)$encabezado->MontoITBIS, 2) ?><br>
        <strong>ITBIS:</strong> <?= number_format((float)$encabezado->MontoITBIS, 2) ?><br>
        <strong>Total a Pagar:</strong> <?= number_format($montoTotal, 2) ?>
    </div>
    
    <div class="qr-section">
        <div class="qr-code">
            <img src="data:image/png;base64,<?= $qrBase64 ?>" alt="Código QR">
        </div>
        <p><strong>Código de Validación:</strong><br><?= htmlspecialchars($row['xml_security_code']) ?></p>
    </div>
    
    <div class="footer">
        <p>Este comprobante fiscal es válido para crédito fiscal</p>
        <p>Estado DGII: <?= htmlspecialchars($row['xml_ecf_status_name']) ?></p>
    </div>
    
    <div class="break-after"></div>
</body>
</html>