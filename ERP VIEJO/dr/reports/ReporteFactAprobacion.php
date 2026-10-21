<?php
// reports/ReporteFactAprobacion.php
require_once __DIR__ . '/../vendor/tcpdf/tcpdf.php';
require_once __DIR__ . '/../config/Database.php';

class ReporteFactAprobacion
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    /**
     * Generar Comprobante Oficial de Factura a Aseguradora (Diseño Profesional y Limpio)
     */
    public function generarFacturaARS($docEntry, $output = 'I')
    {
        $docEntry = (int)$docEntry;
        $datos = $this->obtenerDatosFacturaARS($docEntry);

        if (!$datos || empty($datos['cabecera'])) {
            die("No se encontró la Factura de Aseguradora con DocEntry: " . htmlspecialchars($docEntry));
        }

        $c = $datos['cabecera'];
        $servicios = $datos['servicios'];
        $aprobaciones = $datos['aprobaciones'];

        // Orientación Vertical Letter (Portrait)
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
        $pdf->SetCreator('ERP Oftalmológico');
        $pdf->SetAuthor('Dra. Nellie Grace Olivo Marte');
        $ncfTitle = !empty($c['U_FACNUM']) ? $c['U_FACNUM'] : ('DOC-' . $c['C_DocEntry']);
        $pdf->SetTitle('Factura_Aseguradora_' . $ncfTitle);

        $pdf->SetMargins(14, 12, 14);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(10);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        // 1. Barra superior morada institucional (#701262)
        $this->dibujarBarraSuperior($pdf);

        // 2. Encabezado: Datos del Prestador Médico y Caja Fiscal NCF
        $this->dibujarEncabezadoFactura($pdf, $c);

        // 3. Bloques de Información: Datos del Cliente (ARS) y Datos de Cobro/Lote
        $this->dibujarDatosAseguradoraYLote($pdf, $c, count($aprobaciones));

        // 4. Tabla Principal de Servicios Agrupados (Dinámica sin filas vacías de relleno)
        $this->dibujarTablaServicios($pdf, $servicios, $c);

        // 5. Bloque de Firmas
        $this->dibujarFirmas($pdf);

        // 6. Pie de página institucional
        $this->dibujarPiePagina($pdf);

        // 7. Si hay aprobaciones vinculadas, generar Anexo Detallado de Reclamaciones
        if (!empty($aprobaciones)) {
            $this->dibujarAnexoAprobaciones($pdf, $aprobaciones, $c);
        }

        return $pdf->Output('Factura_ARS_' . $ncfTitle . '.pdf', $output);
    }

    /**
     * Obtener los datos completos de la factura ARS y sus aprobaciones vinculadas
     */
    private function obtenerDatosFacturaARS($docEntry)
    {
        // 1. Cabecera de la factura en POSOINV
        $sqlH = "SELECT 
                    f.C_DocEntry,
                    f.DocNum,
                    f.DocDate,
                    FORMAT(f.DocDate, 'dd/MM/yyyy') AS DocDateFmt,
                    f.CardCode,
                    f.CardName,
                    ISNULL(f.U_FacNit, ISNULL(soc.U_NIT, '')) AS U_FacNit,
                    ISNULL(f.U_FacNom, f.CardName) AS U_FacNom,
                    ISNULL(f.U_FACNUM, f.NumAtCard) AS U_FACNUM,
                    ISNULL(f.NumAtCard, '') AS NumAtCard,
                    ISNULL(f.U_FacSerie, 'E31') AS U_FacSerie,
                    FORMAT(f.U_NCFVence, 'dd/MM/yyyy') AS U_NCFVenceFmt,
                    ISNULL(f.DocTotal, 0) AS DocTotal,
                    ISNULL(f.DiscPrcnt, 0) AS DiscPrcnt,
                    ISNULL(f.DiscSum, 0) AS DiscSum,
                    ISNULL(f.VatSum, 0) AS VatSum,
                    ISNULL(f.ExtraDays, 0) AS ExtraDays,
                    ISNULL(f.U_user, 'ADMIN') AS U_user,
                    ISNULL(soc.Address, '') AS Address,
                    ISNULL(soc.Phone1, ISNULL(soc.Phone2, '')) AS Phone,
                    ISNULL(soc.E_Mail, '') AS E_Mail
                FROM POSOINV f
                LEFT JOIN posOCRD soc ON f.CardCode = soc.CardCode
                WHERE f.C_DocEntry = ?";
        
        $stmtH = $this->pdo->prepare($sqlH);
        $stmtH->execute([$docEntry]);
        $cabecera = $stmtH->fetch(PDO::FETCH_ASSOC);

        if (!$cabecera) return null;

        // 2. Aprobaciones agrupadas por Tipo de Servicio
        $sqlServ = "SELECT 
                        ISNULL(NULLIF(RTRIM(a.TipoServicio), ''), 'CONSULTA MEDICA') AS TipoServicio,
                        COUNT(*) AS Cantidad,
                        SUM(ISNULL(a.MontoAprobado, 0)) AS Total
                    FROM AprobacionARS a
                    WHERE a.DocEntry_FacturaARS = ?
                    GROUP BY ISNULL(NULLIF(RTRIM(a.TipoServicio), ''), 'CONSULTA MEDICA')";
        
        $stmtServ = $this->pdo->prepare($sqlServ);
        $stmtServ->execute([$docEntry]);
        $servicios = $stmtServ->fetchAll(PDO::FETCH_ASSOC);

        // Si no se encontraron por DocEntry_FacturaARS, buscar en POSINV1
        if (empty($servicios)) {
            $sqlLines = "SELECT 
                            d.Dscription AS TipoServicio,
                            d.Quantity AS Cantidad,
                            d.LineTotal AS Total
                         FROM POSINV1 d
                         WHERE d.C_DocEntry = ?";
            $stmtLines = $this->pdo->prepare($sqlLines);
            $stmtLines->execute([$docEntry]);
            $servicios = $stmtLines->fetchAll(PDO::FETCH_ASSOC);
        }

        // Si sigue vacío, crear una línea con el total de la factura
        if (empty($servicios)) {
            $servicios = [
                [
                    'TipoServicio' => 'SERVICIOS MEDICOS OFTALMOLOGICOS',
                    'Cantidad' => 1,
                    'Total' => $cabecera['DocTotal']
                ]
            ];
        }

        // 3. Detalle individual de aprobaciones vinculadas
        $sqlAp = "SELECT 
                    a.IdARS, a.NumAprobacion, a.CardName AS Paciente, a.NumAfiliado,
                    COALESCE(NULLIF(LTRIM(RTRIM(a.NumExpediente)), ''), pac.U_expediente, '') AS NumExpediente,
                    a.TipoServicio, a.MontoAprobado, a.NumFactura AS FacturaPaciente,
                    FORMAT(a.FechaAprobacionD, 'dd/MM/yyyy') AS FechaAprobacionFmt
                  FROM AprobacionARS a
                  LEFT JOIN posOCRD pac ON a.CardCode = pac.CardCode
                  WHERE (a.DocEntry_FacturaARS = ? OR a.DocEntry_Factura = ?) AND a.Status <> 'R'
                  ORDER BY a.IdARS ASC";
        $stmtAp = $this->pdo->prepare($sqlAp);
        $stmtAp->execute([$docEntry, $docEntry]);
        $aprobaciones = $stmtAp->fetchAll(PDO::FETCH_ASSOC);

        return [
            'cabecera' => $cabecera,
            'servicios' => $servicios,
            'aprobaciones' => $aprobaciones
        ];
    }

    /**
     * 1. Barra superior morada institucional (#701262)
     */
    private function dibujarBarraSuperior($pdf)
    {
        $pdf->SetFillColor(112, 18, 98); // Morado institucional #701262
        $pdf->Rect(14, 8, 187.9, 3.0, 'F');
        $pdf->SetY(14);
    }

    /**
     * 2. Encabezado del Prestador Médico y Caja Fiscal NCF
     */
    private function dibujarEncabezadoFactura($pdf, $c)
    {
        $startY = 14;

        // --- LADO IZQUIERDO: Datos del Prestador Médico ---
        $pdf->SetXY(14, $startY);
        $pdf->SetFont('helvetica', 'B', 12.5);
        $pdf->SetTextColor(112, 18, 98); // Morado institucional
        $pdf->Cell(105, 5.5, 'DRA. NELLIE GRACE OLIVO MARTE', 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(51, 65, 85);
        $pdf->SetX(14);
        $pdf->Cell(105, 4.5, 'OFTALMOLOGÍA CLÍNICA Y QUIRÚRGICA', 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 8.2);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetX(14);
        $pdf->Cell(105, 4.2, 'RNC: 054-0124019-6', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->SetX(14);
        $pdf->Cell(105, 4, 'INSTITUTO DE ESPECIALIDADES MÉDICAS DR. GREGORIO HDEZ.', 0, 1, 'L');
        $pdf->SetX(14);
        $pdf->Cell(105, 4, 'MOCA, PROVINCIA ESPAILLAT, REPÚBLICA DOMINICANA', 0, 1, 'L');
        $pdf->SetX(14);
        $pdf->Cell(105, 4, 'TELÉFONOS: (809) 578-2840', 0, 1, 'L');

        // --- LADO DERECHO: Tarjeta de Comprobante Fiscal NCF ---
        $wCardNCF = 78;
        $xCardNCF = 201.9 - $wCardNCF; // 123.9mm
        $hCardNCF = 32;

        $pdf->SetXY($xCardNCF, $startY);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(203, 213, 225); // Slate 300
        $pdf->SetLineWidth(0.3);
        $pdf->RoundedRect($xCardNCF, $startY, $wCardNCF, $hCardNCF, 2.5, '1111', 'DF');

        // Tipo de Comprobante Fiscal
        $serie = mb_strtoupper(mb_substr($c['U_FacSerie'] ?? 'E31', 0, 3, 'UTF-8'), 'UTF-8');
        $tipoDocNombre = 'FACTURA DE CRÉDITO FISCAL ELECTRÓNICA';
        if ($serie === 'E31') {
            $tipoDocNombre = 'COMPROBANTE CRÉDITO FISCAL ELECTRÓNICO ';
        } elseif ($serie === 'E32' || $serie === 'B02') {
            $tipoDocNombre = 'FACTURA DE CONSUMO ELECTRÓNICA ';
        } elseif ($serie === 'E34' || $serie === 'B04') {
            $tipoDocNombre = 'NOTA DE CRÉDITO ELECTRÓNICA ';
        } elseif ($serie === 'E44' || $serie === 'B14') {
            $tipoDocNombre = 'REGÍMENES ESPECIALES ELECTRÓNICO ';
        } elseif ($serie === 'E45' || $serie === 'B15') {
            $tipoDocNombre = 'COMPROBANTE GUBERNAMENTAL ELECTRÓNICO ';
        }

        $pdf->SetXY($xCardNCF + 2, $startY + 2);
        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell($wCardNCF - 4, 4.5, $tipoDocNombre, 0, 1, 'C');

        // NCF en Grande y Destacado
        $ncf = !empty($c['U_FACNUM']) ? $c['U_FACNUM'] : ('DOC-' . $c['C_DocEntry']);
        $pdf->SetXY($xCardNCF + 2, $startY + 7.5);
        $pdf->SetFont('helvetica', 'B', 11.5);
        $pdf->SetTextColor(185, 28, 28); // Rojo fiscal
        $pdf->Cell($wCardNCF - 4, 6, 'NCF: ' . $ncf, 0, 1, 'C');

        // Línea divisoria interna
        $pdf->SetDrawColor(226, 232, 240);
        $pdf->Line($xCardNCF + 4, $startY + 15, $xCardNCF + $wCardNCF - 4, $startY + 15);

        // Metadatos de Factura
        $pdf->SetFont('helvetica', 'B', 7.8);
        $pdf->SetTextColor(71, 85, 105);

        $pdf->SetXY($xCardNCF + 4, $startY + 16.5);
        $pdf->Cell(28, 3.8, 'FECHA EMISIÓN:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell($wCardNCF - 36, 3.8, $c['DocDateFmt'] ?? date('d/m/Y'), 0, 1, 'R');

        if (!empty($c['U_NCFVenceFmt'])) {
            $pdf->SetXY($xCardNCF + 4, $startY + 21);
            $pdf->SetFont('helvetica', 'B', 7.8);
            $pdf->SetTextColor(71, 85, 105);
            $pdf->Cell(28, 3.8, 'VENCE SECUENCIA:', 0, 0, 'L');
            $pdf->SetFont('helvetica', '', 7.8);
            $pdf->SetTextColor(15, 23, 42);
            $pdf->Cell($wCardNCF - 36, 3.8, $c['U_NCFVenceFmt'], 0, 1, 'R');
        }

        $pdf->SetXY($xCardNCF + 4, $startY + 25.5);
        $pdf->SetFont('helvetica', 'B', 7.8);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(28, 3.8, 'ORIGEN / SERIE:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(21, 128, 61);
        $pdf->Cell($wCardNCF - 36, 3.8, mb_strtoupper($c['U_FacSerie'] ?? 'E31', 'UTF-8') . ' (ORIGINAL)', 0, 1, 'R');

        $pdf->SetY(49);
    }

    /**
     * 3. Bloques de Información: Datos del Cliente (Aseguradora) y Datos de Cobro/Lote
     */
    private function dibujarDatosAseguradoraYLote($pdf, $c, $totalCasos)
    {
        $y = $pdf->GetY();
        $wBox = 92;
        $xBox2 = 14 + $wBox + 3.9; // 109.9mm

        $dir = !empty($c['Address']) ? mb_strtoupper(trim($c['Address']), 'UTF-8') : 'SANTO DOMINGO, REPÚBLICA DOMINICANA';

        // Calcular altura dinámica del texto de la dirección
        $pdf->SetFont('helvetica', '', 7.2);
        $dirWidth = $wBox - 26;
        $dirHeight = max(3.6, $pdf->getStringHeight($dirWidth, $dir));

        // Ubicar la fila 4 (TELÉFONO / EMITIDO POR) de manera fluida inmediatamente después de DIRECCIÓN
        $yRow4 = $y + 15.5 + $dirHeight + 0.8;
        $hBox = max(27.0, ($yRow4 + 4.8) - $y);

        // --- TARJETA 1: DATOS DE LA ASEGURADORA ---
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(203, 213, 225); // Slate 300
        $pdf->SetLineWidth(0.3);
        $pdf->RoundedRect(14, $y, $wBox, $hBox, 2, '1111', 'DF');
 
        // Header de la tarjeta 1
        $pdf->SetFillColor(248, 250, 252);
        $pdf->RoundedRect(14, $y, $wBox, 5.5, 2, '1100', 'DF');
        $pdf->SetXY(16, $y + 0.8);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell($wBox - 4, 4, 'DATOS DE LA ASEGURADORA', 0, 1, 'L');

        // Contenido de la tarjeta 1
        $pdf->SetXY(16, $y + 6.8);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(22, 3.8, 'RAZÓN SOCIAL:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell($wBox - 26, 3.8, mb_strtoupper($c['U_FacNom'] ?? $c['CardName'], 'UTF-8'), 0, 1, 'L');

        $pdf->SetXY(16, $y + 11.0);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(22, 3.8, 'RNC / CÉDULA:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(15, 23, 42);
        $rnc = !empty($c['U_FacNit']) ? $c['U_FacNit'] : 'NO ESPECIFICADO';
        $pdf->Cell(30, 3.8, $rnc, 0, 0, 'L');

        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(14, 3.8, 'CÓDIGO:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(20, 3.8, $c['CardCode'], 0, 1, 'L');

        // Dirección fluida con MultiCell
        $pdf->SetXY(16, $y + 15.2);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(22, 3.5, 'DIRECCIÓN:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7.2);
        $pdf->SetTextColor(51, 65, 85);
        $pdf->SetXY(38, $y + 15.2);
        $pdf->MultiCell($dirWidth, 3.2, $dir, 0, 'L');

        // Teléfono ubicado directamente debajo de la dirección
        $pdf->SetXY(16, $yRow4);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(22, 3.8, 'TELÉFONO:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 7.8);
        $pdf->SetTextColor(15, 23, 42);
        $tel = !empty($c['Phone']) ? $c['Phone'] : '809-000-0000';
        $pdf->Cell($wBox - 26, 3.8, $tel, 0, 1, 'L');

        // --- TARJETA 2: INFORMACIÓN DE COBRO Y LOTE ---
        $pdf->SetFillColor(255, 255, 255);
        $pdf->RoundedRect($xBox2, $y, $wBox, $hBox, 2, '1111', 'DF');

        // Header de la tarjeta 2
        $pdf->SetFillColor(248, 250, 252);
        $pdf->RoundedRect($xBox2, $y, $wBox, 5.5, 2, '1100', 'DF');
        $pdf->SetXY($xBox2 + 2, $y + 0.8);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell($wBox - 4, 4, 'INFORMACIÓN DE FACTURACIÓN Y LOTE', 0, 1, 'L');

        // Contenido de la tarjeta 2
        $pdf->SetXY($xBox2 + 2, $y + 6.8);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(28, 3.8, 'NO. DOCUMENTO:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(15, 23, 42);
        $numDoc = !empty($c['NumAtCard']) ? $c['NumAtCard'] : ('DOC-' . $c['C_DocEntry']);
        $pdf->Cell($wBox - 32, 3.8, $numDoc, 0, 1, 'L');

        $pdf->SetXY($xBox2 + 2, $y + 11.0);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(28, 3.8, 'CONDICIÓN PAGO:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 8);
        $dias = intval($c['ExtraDays'] ?? 0);
        $condPago = ($dias > 1) ? ("CRÉDITO ($dias DÍAS)") : "CONTADO";
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell($wBox - 32, 3.8, $condPago, 0, 1, 'L');

        $pdf->SetXY($xBox2 + 2, $y + 15.2);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(28, 3.8, 'TOTAL RECLAMOS:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell($wBox - 32, 3.8, $totalCasos . ' CASO(S) AUTORIZADO(S)', 0, 1, 'L');

        // Emitido por alineado con Teléfono
        $pdf->SetXY($xBox2 + 2, $yRow4);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(28, 3.8, 'EMITIDO POR:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(51, 65, 85);
        $pdf->Cell($wBox - 32, 3.8, mb_strtoupper($c['U_user'] ?? 'ADMIN', 'UTF-8'), 0, 1, 'L');

        $pdf->SetY($y + $hBox + 4);
    }

    /**
     * 4. Tabla Principal de Servicios Agrupados (Sin fondo negro, sin columnas artificiales y dinámica)
     */
    private function dibujarTablaServicios($pdf, $servicios, $c)
    {
        // Anchos de columnas en mm (Total utilizable = 187.9 mm)
        $wCant = 26;      // Cantidad (26 mm)
        $wDesc = 97.9;    // Descripción del Servicio Médico (97.9 mm)
        $wUnit = 32;      // Precio Unitario (32 mm)
        $wTotal = 32;     // Total (32 mm)
        $wTablaTotal = 187.9;

        // Encabezado de la Tabla (Fondo Transparente / Blanco con Bordes Limpios)
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('helvetica', 'B', 8.2);
        $pdf->SetTextColor(15, 23, 42);

        $pdf->SetX(14);
        $pdf->Cell($wCant, 6.5, 'CANTIDAD', 1, 0, 'C', false);
        $pdf->Cell($wDesc, 6.5, ' DESCRIPCIÓN DEL SERVICIO MÉDICO', 1, 0, 'L', false);
        $pdf->Cell($wUnit, 6.5, 'PRECIO UNITARIO', 1, 0, 'R', false);
        $pdf->Cell($wTotal, 6.5, 'TOTAL (RD$) ', 1, 1, 'R', false);

        // Filas de Datos
        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->SetTextColor(15, 23, 42);

        $totalCantidad = 0;
        $subTotal = 0;

        foreach ($servicios as $s) {
            $cant = (int)$s['Cantidad'];
            
            // Limpiar descripción de barras o caracteres residuales y formatear limpiamente
            $descRaw = trim(str_replace('|', '', $s['TipoServicio']));
            $descSinAcentos = str_replace(
                ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ'],
                ['a','e','i','o','u','A','E','I','O','U','n','N'],
                $descRaw
            );
            $desc = strtoupper($descSinAcentos);
            
            $monto = floatval($s['Total']);
            $unit = ($cant > 0) ? ($monto / $cant) : $monto;

            $totalCantidad += $cant;
            $subTotal += $monto;

            $pdf->SetX(14);
            $pdf->Cell($wCant, 6, $cant, 1, 0, 'C', false);
            $pdf->Cell($wDesc, 6, ' ' . $desc, 1, 0, 'L', false);
            $pdf->Cell($wUnit, 6, number_format($unit, 2) . ' ', 1, 0, 'R', false);
            $pdf->Cell($wTotal, 6, number_format($monto, 2) . ' ', 1, 1, 'R', false);
        }

        // ==========================================
        // SECCIÓN DE TOTALES Y RESUMEN FINANCIERO
        // ==========================================
        $yTotales = $pdf->GetY() + 3;
        $wLetras = 107.9;
        $wTotalesBox = 80;

        $granTotal = floatval($c['DocTotal'] ?? $subTotal);
        $montoEnLetras = $this->numeroALetras($granTotal);

        // --- LADO IZQUIERDO: MONTO EN LETRAS ---
        $pdf->SetXY(14, $yTotales);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell($wLetras, 4, 'TOTAL EN LETRAS:', 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetX(14);
        $pdf->MultiCell($wLetras, 4, 'SON: ' . $montoEnLetras, 0, 'L');

        // --- LADO DERECHO: CUADRO DE SUBTOTAL, DESCUENTO Y TOTAL ---
        $xTotales = 14 + $wLetras;
        $pdf->SetXY($xTotales, $yTotales);

        // Fila 1: Subtotal
        $pdf->SetFont('helvetica', 'B', 8.2);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(45, 6, 'SUB-TOTAL RD$:', 1, 0, 'R', false);
        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell($wTotalesBox - 45, 6, number_format($subTotal, 2) . ' ', 1, 1, 'R', false);

        // Fila 2: Descuento (Si aplica o 0.00)
        $discPrcnt = floatval($c['DiscPrcnt'] ?? 0);
        $discSum = floatval($c['DiscSum'] ?? 0);
        $labelDesc = ($discPrcnt > 0) ? ('DESCUENTO (' . number_format($discPrcnt, 0) . '%) RD$:') : 'DESCUENTO (0%) RD$:';
        
        $pdf->SetX($xTotales);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(45, 6, $labelDesc, 1, 0, 'R', false);
        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->SetTextColor(185, 28, 28); // Rojo fiscal
        $pdf->Cell($wTotalesBox - 45, 6, ($discSum > 0 ? ('- ' . number_format($discSum, 2)) : '0.00') . ' ', 1, 1, 'R', false);

        // Fila 3: ITBIS (0% Exento para servicios médicos)
        $vatSum = floatval($c['VatSum'] ?? 0);
        $pdf->SetX($xTotales);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(45, 6, 'ITBIS (0% EXENTO) RD$:', 1, 0, 'R', false);
        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell($wTotalesBox - 45, 6, number_format($vatSum, 2) . ' ', 1, 1, 'R', false);

        // Fila 4: TOTAL GENERAL (Destacado)
        $pdf->SetX($xTotales);
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetFont('helvetica', 'B', 9.2);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(45, 7.5, 'TOTAL GENERAL RD$:', 1, 0, 'R', true);
        $pdf->SetFont('helvetica', 'B', 10.5);
        $pdf->SetTextColor(21, 128, 61);
        $pdf->Cell($wTotalesBox - 45, 7.5, number_format($granTotal, 2) . ' ', 1, 1, 'R', true);

        $pdf->SetY($yTotales + 36);
    }

    /**
     * 5. Bloque de Firmas y Responsabilidad
     */
    private function dibujarFirmas($pdf)
    {
        $currentY = $pdf->GetY();
        if ($currentY > 215) {
            $pdf->AddPage();
            $currentY = 40;
        } else {
            $pdf->SetY(max($currentY, 218));
        }

        $wFirma = 75;
        $pdf->SetDrawColor(148, 163, 184); // Slate 400
        $pdf->SetLineWidth(0.4);

        // Línea Firma Prestador (Izquierda)
        $pdf->Line(20, $pdf->GetY(), 20 + $wFirma, $pdf->GetY());
        // Línea Firma Receptor ARS (Derecha)
        $pdf->Line(118, $pdf->GetY(), 118 + $wFirma, $pdf->GetY());

        $pdf->SetY($pdf->GetY() + 2);
        $pdf->SetFont('helvetica', 'B', 8.2);
        $pdf->SetTextColor(15, 23, 42);

        $pdf->SetX(20);
        $pdf->Cell($wFirma, 4, 'DRA. NELLIE GRACE OLIVO MARTE', 0, 0, 'C');
        $pdf->SetX(118);
        $pdf->Cell($wFirma, 4, 'RECIBIDO Y CONFORME (ARS)', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->SetX(20);
        $pdf->Cell($wFirma, 3.5, 'Médico Oftalmólogo / Prestador', 0, 0, 'C');
        $pdf->SetX(118);
        $pdf->Cell($wFirma, 3.5, 'Firma, Fecha y Sello de Recepción', 0, 1, 'C');
    }

    /**
     * 6. Pie de página institucional
     */
    private function dibujarPiePagina($pdf)
    {
        $pdf->SetY(260);
        $pdf->SetDrawColor(226, 232, 240);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(14, 259, 201.9, 259);

        $pdf->SetFont('helvetica', '', 7.2);
        $pdf->SetTextColor(148, 163, 184);
        $pdf->Cell(95, 4, 'ERP Oftalmológico - Comprobante Oficial de Aseguradoras', 0, 0, 'L');
        $pdf->Cell(92.9, 4, 'Impreso el: ' . date('d/m/Y, h:i A'), 0, 1, 'R');
    }

    /**
     * 7. Anexo de Autorizaciones y Reclamaciones ARS para Auditoría Médica
     */
    private function dibujarAnexoAprobaciones($pdf, $aprobaciones, $c)
    {
        $pdf->AddPage();
        $this->dibujarBarraSuperior($pdf);

        // Encabezado del Anexo
        $pdf->SetY(14);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(112, 18, 98); // Morado institucional #701262
        $pdf->Cell(187.9, 5, 'ANEXO DE AUTORIZACIONES - RELACIÓN DE RECLAMACIONES ARS', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 7.8);
        $pdf->SetTextColor(71, 85, 105);
        $arsNombre = $c['U_FacNom'] ?: $c['CardName'];
        $ncfDoc = $c['U_FACNUM'] ?: ('DOC-' . $c['C_DocEntry']);
        $pdf->Cell(187.9, 4, "Aseguradora: $arsNombre  |  Factura Fiscal NCF: $ncfDoc  |  Fecha Emisión: " . ($c['DocDateFmt'] ?? date('d/m/Y')), 0, 1, 'L');
        $pdf->Ln(2);

        // Tabla de Autorizaciones (Ancho = 187.9 mm)
        $wAut = 28;     // No. Autorización (28 mm)
        $wPac = 46.9;   // Paciente (46.9 mm)
        $wExp = 18;     // Expediente (18 mm)
        $wAfil = 25;    // No. Afiliado (25 mm)
        $wServ = 38;    // Servicio (38 mm)
        $wMonto = 32;   // Monto Aprobado (32 mm)

        // Cabecera
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetFillColor(241, 245, 249);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.25);

        $pdf->SetX(14);
        $pdf->Cell($wAut, 5.5, 'NO. AUTORIZACIÓN', 1, 0, 'C', true);
        $pdf->Cell($wPac, 5.5, 'PACIENTE', 1, 0, 'L', true);
        $pdf->Cell($wExp, 5.5, 'EXPEDIENTE', 1, 0, 'C', true);
        $pdf->Cell($wAfil, 5.5, 'NO. AFILIADO', 1, 0, 'C', true);
        $pdf->Cell($wServ, 5.5, 'SERVICIO / PROCEDIMIENTO', 1, 0, 'L', true);
        $pdf->Cell($wMonto, 5.5, 'MONTO ARS (RD$)', 1, 1, 'R', true);

        // Filas
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(15, 23, 42);
        $fill = false;
        $totalAprob = 0.0;

        foreach ($aprobaciones as $ap) {
            $monto = (float)($ap['MontoAprobado'] ?? 0);
            $totalAprob += $monto;
            $expStr = !empty($ap['NumExpediente']) ? ('EXP-' . $ap['NumExpediente']) : 'S/E';

            $pdf->SetFillColor(248, 250, 252);
            $pdf->SetX(14);
            $pdf->Cell($wAut, 5, $ap['NumAprobacion'], 'LR', 0, 'C', $fill);
            $pdf->Cell($wPac, 5, mb_substr($ap['Paciente'] ?? '', 0, 28, 'UTF-8'), 'LR', 0, 'L', $fill);
            $pdf->Cell($wExp, 5, $expStr, 'LR', 0, 'C', $fill);
            $pdf->Cell($wAfil, 5, mb_substr($ap['NumAfiliado'] ?? '', 0, 16, 'UTF-8'), 'LR', 0, 'C', $fill);
            $pdf->Cell($wServ, 5, mb_substr($ap['TipoServicio'] ?? '', 0, 24, 'UTF-8'), 'LR', 0, 'L', $fill);
            $pdf->Cell($wMonto, 5, number_format($monto, 2) . ' ', 'LR', 1, 'R', $fill);

            $fill = !$fill;
        }

        // Fila de Cierre y Total
        $pdf->SetX(14);
        $pdf->Cell(187.9, 0.1, '', 'T', 1, 'C');

        $pdf->SetX(14);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(241, 245, 249);
        $pdf->Cell($wAut + $wPac + $wExp + $wAfil + $wServ, 6, 'TOTAL AUTORIZADO EN ANEXO (RD$):', 1, 0, 'R', true);
        $pdf->SetTextColor(21, 128, 61);
        $pdf->Cell($wMonto, 6, number_format($totalAprob, 2) . ' ', 1, 1, 'R', true);

        $this->dibujarPiePagina($pdf);
    }

    /**
     * Convertir número a letras en español
     */
    private function numeroALetras($numero)
    {
        $numero = round($numero, 2);
        $enteros = floor($numero);
        $centavos = round(($numero - $enteros) * 100);

        $centavosStr = str_pad($centavos, 2, '0', STR_PAD_LEFT) . '/100';

        if ($enteros == 0) {
            return 'CERO PESOS CON ' . $centavosStr;
        }

        $letras = $this->convertirEnteroALetras($enteros);
        $moneda = ($enteros == 1) ? 'PESO CON ' : 'PESOS CON ';

        return trim($letras) . ' ' . $moneda . $centavosStr;
    }

    private function convertirEnteroALetras($n)
    {
        $unidades = [
            '', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
            'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE',
            'DIECIOCHO', 'DIECINUEVE', 'VEINTE'
        ];
        $decenas = [
            '', '', 'VEINTI', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA',
            'OCHENTA', 'NOVENTA'
        ];
        $centenas = [
            '', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS',
            'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'
        ];

        if ($n == 100) return 'CIEN';
        if ($n <= 20) return $unidades[$n];

        if ($n < 30) {
            return 'VEINTI' . $unidades[$n - 20];
        }

        if ($n < 100) {
            $u = $n % 10;
            $d = floor($n / 10);
            return $decenas[$d] . ($u > 0 ? ' Y ' . $unidades[$u] : '');
        }

        if ($n < 1000) {
            $c = floor($n / 100);
            $resto = $n % 100;
            return $centenas[$c] . ($resto > 0 ? ' ' . $this->convertirEnteroALetras($resto) : '');
        }

        if ($n < 1000000) {
            $miles = floor($n / 1000);
            $resto = $n % 1000;
            $strMiles = ($miles == 1) ? 'MIL' : $this->convertirEnteroALetras($miles) . ' MIL';
            return $strMiles . ($resto > 0 ? ' ' . $this->convertirEnteroALetras($resto) : '');
        }

        if ($n < 1000000000) {
            $millones = floor($n / 1000000);
            $resto = $n % 1000000;
            $strMillones = ($millones == 1) ? 'UN MILLÓN' : $this->convertirEnteroALetras($millones) . ' MILLONES';
            return $strMillones . ($resto > 0 ? ' ' . $this->convertirEnteroALetras($resto) : '');
        }

        return (string)$n;
    }
}
