<?php
// @media print { body { margin: 0; padding: 5px; } .no-print { display: none; } }
//body { font-family: 'Courier New', Courier, monospace; font-size: 12px; width: 80mm; margin: 0 auto; padding: 10px; background-color: #fff; }
// ===================================================================
// INCLUSIÓN DE LA LIBRERÍA QR (MÉTODO MANUAL, SIN COMPOSER)
// ===================================================================
require_once 'phpqrcode/qrlib.php';

// Configuración conexión SQL Server (sin cambios)
$serverName = "SERVIDOR\SQLEXPRESS";
$connectionOptions = [
    "Database" => "CMS",
    "Uid" => "CMS",
    "PWD" => "cms2026",
    "CharacterSet" => "UTF-8"
];

 $conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die("Error al conectar a la base de datos: " . print_r(sqlsrv_errors(), true));
}

// Obtener el ID del registro
 $id_record = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id_record <= 0) {
    die("<h1>Error</h1><p>ID de factura no válido.</p>");
}

// Consulta para obtener los datos de la factura
$sql = "SELECT id_record, doc_id, NumAtCard, ecf, ecf_type, ecf_vence, ecf_rnc_emisor, ecf_rnc_comprador, date_process, xml_file, xml_file_qrcode, xml_security_code, xml_file_sign_date FROM cms.dbo.ecf_xml_invoice WHERE id_record = ?";
$params = [$id_record];
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    die("Error al consultar la factura: " . print_r(sqlsrv_errors(), true));
}

$factura_data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$factura_data) {
    die("<h1>Error</h1><p>Factura no encontrada.</p>");
}

$fecha = $factura_data['xml_file_sign_date'] ?? null;
if ($fecha instanceof DateTime) {
    $fecha = $fecha->format('d-m-Y H:i:s');
}

// ===================================================================
// GENERACIÓN DEL CÓDIGO QR 
// ===================================================================
$qr_base64 = '';
$qr_url = $factura_data['xml_file_qrcode'] ?? null;

if (!empty($qr_url)) {
    // 1. Iniciamos un "buffer" de salida para capturar la imagen
    ob_start(); 
    
    // 2. Generamos el código QR y lo enviamos al buffer (no a la pantalla)
    //    Parámetros: datos, nombre de archivo (null para salida directa), nivel de corrección, tamaño
    @QRcode::png($qr_url, null, QR_ECLEVEL_M, 4);
    
    // 3. Recuperamos los datos de la imagen del buffer
    $qr_image_data = ob_get_contents(); 
    
    // 4. Limpiamos el buffer
    ob_end_clean();

    // 5. Codificamos los datos de la imagen a Base64 para poder mostrarla en HTML
    $qr_base64 = base64_encode($qr_image_data);
}

// --- Procesamiento de Factura: XML firmado o Modo Preliminar (Firmador inactivo) ---
$xml_string = $factura_data['xml_file'] ?? null;
$comprador = null;

if (!empty($xml_string)) {
    try {
        $xml = new SimpleXMLElement($xml_string);
        $emisor = $xml->xpath('//Encabezado/Emisor')[0];
        $id_doc = $xml->xpath('//Encabezado/IdDoc')[0];
        
        if (htmlspecialchars($id_doc->TipoeCF) != '43' && htmlspecialchars($id_doc->TipoeCF) != '47') {
            $comprador_arr = $xml->xpath('//Encabezado/Comprador');
            $comprador = !empty($comprador_arr) ? $comprador_arr[0] : null;
        }

        $totales = $xml->xpath('//Encabezado/Totales')[0];
        $items = $xml->xpath('//DetallesItems/Item');
    } catch (Exception $e) {
        die("<h1>Error</h1><p>No se pudo procesar el XML de la factura: " . $e->getMessage() . "</p>");
    }
} else {
    // --- MODO PRELIMINAR: Generar datos básicos desde BD para pruebas mientras el firmador esté apagado ---
    $doc_id = (int)($factura_data['doc_id'] ?? 0);
    $numAtCard = $factura_data['NumAtCard'] ?? '';
    $ecf = $factura_data['ecf'] ?? '';
    $ecf_type = $factura_data['ecf_type'] ?? '32';
    $ecf_vence = $factura_data['ecf_vence'] ?? '';
    $rncEmisor = $factura_data['ecf_rnc_emisor'] ?? '';
    $rncComprador = $factura_data['ecf_rnc_comprador'] ?? '';

    // Datos Emisor (desde tabla division)
    $divStmt = sqlsrv_query($conn, "SELECT TOP 1 razon_social, nombre, RNC, direccion FROM division");
    $div = ($divStmt) ? sqlsrv_fetch_array($divStmt, SQLSRV_FETCH_ASSOC) : null;

    $emisor = (object)[
        'RazonSocialEmisor' => $div['razon_social'] ?? 'DRA. NELLIE GRACE OLIVO MARTE',
        'NombreComercial' => $div['nombre'] ?? 'OFTALMOLOGO',
        'RNCEmisor' => $rncEmisor ?: ($div['RNC'] ?? '05401240196'),
        'DireccionEmisor' => $div['direccion'] ?? 'VILLA ESTELA, MOCA, PROVINCIA ESPALLAT',
        'FechaEmision' => ($factura_data['date_process'] instanceof DateTime) ? $factura_data['date_process']->format('d-m-Y') : date('d-m-Y'),
        'NumeroFacturaInterna' => $numAtCard ?: ('F-' . $doc_id)
    ];

    // Encabezado Factura (desde POSOINV)
    $invStmt = sqlsrv_query($conn, "SELECT C_DocEntry, DocNum, DocDate, CardCode, CardName, DocTotal, VatSum, U_FACNUM, U_FacNit FROM POSOINV WHERE C_DocEntry = ?", [$doc_id]);
    $inv = ($invStmt) ? sqlsrv_fetch_array($invStmt, SQLSRV_FETCH_ASSOC) : null;

    if ($inv && $inv['DocDate'] instanceof DateTime) {
        $emisor->FechaEmision = $inv['DocDate']->format('d-m-Y');
    }

    $id_doc = (object)[
        'TipoeCF' => (string)$ecf_type,
        'eNCF' => $ecf ?: ($inv['U_FACNUM'] ?? ''),
        'FechaVencimientoSecuencia' => ($ecf_vence instanceof DateTime) ? $ecf_vence->format('d-m-Y') : (!empty($ecf_vence) ? substr($ecf_vence, 0, 10) : '')
    ];

    $comprador = (object)[
        'RazonSocialComprador' => trim((string)($inv['CardName'] ?? 'CLIENTE CONTADO')),
        'RNCComprador' => trim((string)($inv['U_FacNit'] ?? ($rncComprador ?: '')))
    ];

    // Consulta de Copago en AprobacionARS
    $arsStmt = sqlsrv_query($conn, "SELECT MontoAprobado, PorcentajeCober, MontoResponsable FROM AprobacionARS WHERE DocEntry_Factura = ? OR NumFactura = ?", [$doc_id, $numAtCard]);
    $ars = ($arsStmt) ? sqlsrv_fetch_array($arsStmt, SQLSRV_FETCH_ASSOC) : null;

    $docTotal = (float)($inv['DocTotal'] ?? 0.0);
    $copagoARS = ($ars && (float)$ars['MontoResponsable'] > 0) ? (float)$ars['MontoResponsable'] : 0.0;

    // Si la factura tiene copago registrado y el DocTotal de POSOINV era aún el bruto original, escalar proporcionalmente al copago
    $ratioEscala = 1.0;
    if ($copagoARS > 0 && abs($docTotal - $copagoARS) > 0.01 && $docTotal > 0) {
        $ratioEscala = $copagoARS / $docTotal;
        $docTotal = $copagoARS;
    }

    // Líneas de Factura (desde POSINV1)
    $linesStmt = sqlsrv_query($conn, "SELECT LineNum, ItemCode, Dscription, Quantity, Price, LineTotal, VatSum FROM POSINV1 WHERE C_DocEntry = ? ORDER BY LineNum ASC", [$doc_id]);
    $items = [];
    $totalExento = 0.0;
    $totalGravado = 0.0;
    $totalItbis = (float)($inv['VatSum'] ?? 0.0) * $ratioEscala;

    if ($linesStmt) {
        while ($line = sqlsrv_fetch_array($linesStmt, SQLSRV_FETCH_ASSOC)) {
            $qty = (float)$line['Quantity'];
            $price = (float)$line['Price'] * $ratioEscala;
            $lineTotal = (float)$line['LineTotal'] * $ratioEscala;
            $vat = (float)($line['VatSum'] ?? 0) * $ratioEscala;

            if ($vat > 0) {
                $totalGravado += $lineTotal;
            } else {
                $totalExento += $lineTotal;
            }

            $items[] = (object)[
                'NombreItem' => $line['Dscription'],
                'CantidadItem' => $qty,
                'PrecioUnitarioItem' => $price,
                'MontoItem' => $lineTotal
            ];
        }
    }

    $totales = (object)[
        'MontoGravadoTotal' => ($totalGravado > 0) ? $totalGravado : null,
        'MontoExento' => ($totalExento > 0 || $totalGravado == 0) ? ($totalExento ?: $docTotal) : null,
        'TotalITBIS' => $totalItbis,
        'MontoTotal' => $docTotal
    ];
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura de Crédito Fiscal</title>
    <style>
     /* body { font-family: 'Courier New', Courier, monospace; font-size: 12px; width: 80mm; margin: 0 auto; padding: 10px; background-color: #fff; }   */
body {
    font-family: "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif;
    font-size: 13px;
    line-height: 1.45;
    width: 80mm;
    margin: 0 auto;
    padding: 15px;
    background-color: #fff;
    color: #333;
}		
		
		
        .header, .footer { text-align: center; margin-bottom: 15px; }
        .header h2, .header p { margin: 2px 0; font-size: 14px; }
        .info-box { border: 1px dashed #000; padding: 5px; margin-bottom: 10px; }
        .info-box p { margin: 2px 0; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { padding: 2px 4px; font-size: 11px; }
        th.desc, td.desc { width: 55%; text-align: left; }
        th.num, td.num { width: 15%; text-align: right; }
        th { border-bottom: 1px solid #000; }
        .totales { text-align: right; margin-top: 10px; }
        .totales p { margin: 2px 0; font-size: 12px; }
        .qr-code { text-align: center; margin-top: 15px; }
        .qr-code img { max-width: 150px; height: auto; }
       
		
			@media print {
			@page {
				size: 80mm auto; /* ancho fijo, largo dinámico */
				margin: 0;
			}

			body {
				width: 80mm;
				margin: 0;
				padding: 5px;
			}

			.no-print {
				display: none;
			}
		}
		
	.sprite {
    width: 300px;           /* change to your desired size */
    height: auto;
    
    /* Convert to Black & White + High Contrast for Thermal Printer */
    filter: grayscale(100%) contrast(100%) brightness(1);
    
    image-rendering: crisp-edges;
}	
		
		
    </style>
</head>
<body>
    <p><img src="img/DRA.NELLIE.OLIVO.png" width="303" height="257" alt="Sprite" class=""/></p>
	
	
	
    <div class="header">
        <h2>
          <?= htmlspecialchars($emisor->RazonSocialEmisor) ?>
        </h2>
        <p>
          <?= htmlspecialchars($emisor->NombreComercial) ?>
        </p>
        <p>RNC: <?= htmlspecialchars($emisor->RNCEmisor) ?></p>
        <p><?= htmlspecialchars($emisor->DireccionEmisor) ?></p>
        
		<?php if (htmlspecialchars($id_doc->TipoeCF) == '31' )  {?>
		<p><strong>Factura de Crédito Fiscal Electrónico</strong></p>
		<?php }?>
		<?php if (htmlspecialchars($id_doc->TipoeCF) == '32' )  {?>
		<p><strong>Factura de Consumo Electrónica</strong></p>
		<?php }?>
		<?php if (htmlspecialchars($id_doc->TipoeCF) == '44' )  {?>
		<p><strong>Regímenes Especiales Electrónico</strong></p>
		<?php }?>
		<?php if (htmlspecialchars($id_doc->TipoeCF) == '45' )  {?>
		<p><strong>Gubernamental Electrónico</strong></p>
		<?php }?>
		<?php if (htmlspecialchars($id_doc->TipoeCF) == '43' )  {?>
		<p><strong>Gastos Menores Electrónicos</strong></p>
		<?php }?>
		<?php if (htmlspecialchars($id_doc->TipoeCF) == '34' )  {?>
		<p><strong>Nota de Créditro Electrónica</strong></p>
		<?php }?>
</div>

    <div class="info-box">
        <p><strong>NCF:</strong> <?= htmlspecialchars($id_doc->eNCF) ?></p>
		<?php if (htmlspecialchars($id_doc->FechaVencimientoSecuencia) != '' ) { ?>
		<p><strong>Fecha Vencimiento:</strong> <?= htmlspecialchars($id_doc->FechaVencimientoSecuencia) ?></p>
		<?php }?>
        <p><strong>Fecha Emisión:</strong> <?= htmlspecialchars($emisor->FechaEmision) ?></p>
        <p><strong>Número Interno:</strong> <?= htmlspecialchars($emisor->NumeroFacturaInterna) ?></p>
    </div>
   
	<?php if (!empty($comprador)) { ?>
<div class="info-box">
        <p><strong>Cliente:</strong> <?= htmlspecialchars($comprador->RazonSocialComprador ?? '') ?></p>
        <p><strong>RNC/Céd:</strong> <?= htmlspecialchars($comprador->RNCComprador ?? '') ?></p>
		
    </div>
<?php }?>
	
<table>
        <thead>
            <tr>
                <th colspan="5" class="desc">Descripción</th>
            </tr>
			<tr>
               
                <th width="23%" class="num">Cant.</th>
                
                <th width="19%" class="num">Precio</th>
                
				<th width="19%" class="num">ITBIS</th>
                <th width="18%" class="num">&nbsp;</th>
				<th width="37%" class="num">Imprte</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td colspan="5" class="desc"><?= htmlspecialchars($item->NombreItem) ?></td>
            </tr>
			<tr>                
                <td class="num"><?= number_format((float)$item->CantidadItem, 2) ?></td>
                <td class="num"><?= number_format((float)$item->PrecioUnitarioItem, 2) ?></td>
                <td class="num">0.0</td>
                <td class="num">&nbsp;</td>
                <td class="num"><?= number_format((float)$item->MontoItem, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totales">
        <?php if (isset($totales->MontoGravadoTotal)): ?>
      <p><strong>Monto Gravado:</strong> RD$<?= number_format((float)$totales->MontoGravadoTotal, 2) ?></p>
        <?php endif; ?>
        <?php if (isset($totales->MontoExento)): ?>
            <p><strong>Monto Exento:</strong> RD$<?= number_format((float)$totales->MontoExento, 2) ?></p>
        <?php endif; ?>
        
            <p><strong>ITBIS:</strong> RD$<?= number_format((float)$totales->TotalITBIS, 2) ?></p>
        
        <p><strong>TOTAL:</strong> RD$<?= number_format((float)$totales->MontoTotal, 2) ?></p>
</div>

    <div class="qr-code">
        <?php if (!empty($qr_base64)): ?>
            <img src="data:image/png;base64,<?= $qr_base64 ?>" alt="Código QR">
            <p style="font-size: 9px; margin-top: 5px; word-wrap: break-word;"><?= 'Código de Seguridad: '.htmlspecialchars($factura_data['xml_security_code'] ?? '') ?></p>
		    <p style="font-size: 9px; margin-top: 5px; word-wrap: break-word;"><?= 'Fecha Firma Digital: '.htmlspecialchars($fecha ?? '') ?></p>
        <?php else: ?>
            <div style="border: 1px dashed #718096; padding: 6px; margin: 10px auto; max-width: 240px; background-color: #f8fafc; border-radius: 4px;">
                <p style="font-weight: bold; font-size: 11px; margin: 0; color: #b7791f;">*** FACTURA PRELIMINAR ***</p>
                <p style="font-size: 9px; margin: 3px 0 0; color: #4a5568;">Pendiente de firma electrónica DGII (Firmador inactivo)</p>
            </div>
        <?php endif;  ?>  
    </div>

    <div class="footer">
        <p><strong>¡Gracias por su compra!</strong></p>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Imprimir Factura</button>
    </div>
</body>
</html>