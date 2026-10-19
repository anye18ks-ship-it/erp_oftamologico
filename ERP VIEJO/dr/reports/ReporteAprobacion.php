<?php
// reports/ReporteAprobacion.php
require_once __DIR__ . '/../vendor/tcpdf/tcpdf.php';
require_once __DIR__ . '/../config/Database.php';

class ReporteAprobacion
{
    private $pdo;
    private $mes;
    private $anio;
    private $arsSeleccionada;
    private $docEntryARS;
    private $meses = [
        1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
        5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
        9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
    ];

    public function __construct($mes = null, $anio = null, $ars = 'TODAS', $docEntryARS = null)
    {
        $this->pdo = (new Database())->getConnection();
        $this->mes = !empty($mes) ? (int)$mes : (int)date('n');
        $this->anio = !empty($anio) ? (int)$anio : (int)date('Y');
        $this->arsSeleccionada = !empty($ars) ? trim($ars) : 'TODAS';
        $this->docEntryARS = !empty($docEntryARS) ? (int)$docEntryARS : null;
    }

    public function generar($output = 'I')
    {
        $datos = $this->obtenerDatos();

        // Configuración TCPDF en orientación horizontal (Landscape) Letter para legibilidad de tablas clínicas
        $pdf = new TCPDF('L', 'mm', 'LETTER', true, 'UTF-8', false);
        $pdf->SetCreator('ERP Oftalmológico');
        $pdf->SetAuthor('Dra. Nellie Grace Olivo Marte');
        $nombreMes = $this->meses[$this->mes] ?? 'PERIODO';
        $tituloReporte = $this->docEntryARS ? ('Relacion_Reclamaciones_Factura_' . $this->docEntryARS) : ('Relacion_Reclamaciones_' . $nombreMes . '_' . $this->anio);
        $pdf->SetTitle($tituloReporte);
        
        // Configuración de márgenes
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(10);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        $this->dibujarEncabezadoInstitucional($pdf);
        $this->dibujarTablaReclamaciones($pdf, $datos);

        return $pdf->Output($tituloReporte . '.pdf', $output);
    }

    private function obtenerDatos()
    {
        $params = [];
        if ($this->docEntryARS) {
            $where = "a.DocEntry_FacturaARS = ?";
            $params[] = $this->docEntryARS;
        } else {
            $where = "((MONTH(COALESCE(a.FechaAprobacionD, a.CreateDateD)) = ? AND YEAR(COALESCE(a.FechaAprobacionD, a.CreateDateD)) = ?))";
            $params[] = $this->mes;
            $params[] = $this->anio;

            if ($this->arsSeleccionada !== 'TODAS' && !empty($this->arsSeleccionada)) {
                $where .= " AND ars_real.CardCode = ?";
                $params[] = $this->arsSeleccionada;
            }
        }

        $sql = "SELECT 
                    a.IdARS,
                    a.FechaAprobacionD,
                    a.NumAprobacion,
                    a.CardName,
                    a.NumAfiliado,
                    a.TipoServicio,
                    ISNULL(a.MontoAprobado, 0) AS MontoAprobado,
                    ISNULL(a.PorcentajeCober, 0) AS PorcentajeCober,
                    ISNULL(a.MontoResponsable, 0) AS MontoResponsable,
                    a.NumFactura,
                    a.DocEntry_FacturaARS,
                    a.NumFacturaARS,
                    a.CardCode_ARS,
                    CASE 
                        WHEN ars_real.CardName IS NOT NULL AND RTRIM(ars_real.CardName) <> '' THEN UPPER(ars_real.CardName)
                        WHEN a.CardCode_ARS IS NOT NULL AND RTRIM(a.CardCode_ARS) <> '' THEN UPPER(a.CardCode_ARS)
                        ELSE 'OTRAS ASEGURADORAS'
                    END AS NombreARS
                FROM AprobacionARS a
                LEFT JOIN posOCRD pac ON pac.CardCode = a.CardCode
                OUTER APPLY (
                    SELECT TOP 1 x.CardCode, x.CardName
                    FROM posOCRD x
                    WHERE x.QryGroup1 = 'Y'
                      AND (
                            x.CardCode = a.CardCode_ARS
                         OR (NULLIF(a.CardCode_ARS, '') IS NULL AND x.CardCode = pac.U_SubGrupo)
                          )
                    ORDER BY x.CardCode
                ) ars_real
                WHERE $where
                ORDER BY NombreARS ASC, a.FechaAprobacionD ASC, a.IdARS ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function dibujarEncabezadoInstitucional($pdf)
    {
        $nombreMes = $this->meses[$this->mes] ?? 'PERÍODO';
        $titulo = $this->docEntryARS ? ('RELACIÓN DE RECLAMACIONES DE FACTURA ARS (LOTE ' . $this->docEntryARS . ')') : ('RELACIÓN GENERAL DE RECLAMACIONES (' . $nombreMes . ' ' . $this->anio . ')');

        // Título Principal
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(15, 23, 42); // Slate dark
        $pdf->Cell(0, 7, $titulo, 0, 1, 'C');
        
        // Línea divisoria suave
        $pdf->SetDrawColor(203, 213, 225); // Slate 300
        $pdf->SetLineWidth(0.4);
        $pdf->Line(12, $pdf->GetY() + 1, 267, $pdf->GetY() + 1);
        $pdf->Ln(3);

        // Bloque de Datos Generales Estáticos (TODO EN MAYÚSCULAS)
        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->SetTextColor(30, 41, 59);

        // Fila 1
        $pdf->Cell(130, 4.5, 'PRESTADOR: DRA. NELLIE GRACE OLIVO MARTE', 0, 0, 'L');
        $pdf->Cell(125, 4.5, 'CENTRO: INSTITUTO DE ESPECIALIDADES MÉDICAS GREGORIO HDEZ.', 0, 1, 'L');

        // Fila 2
        $pdf->Cell(130, 4.5, 'ESPECIALIDAD: OFTALMOLOGÍA', 0, 0, 'L');
        $pdf->Cell(125, 4.5, 'CONTACTO:  evelyn_arroyo29@yahoo.com', 0, 1, 'L');

        // Fila 3
        $pdf->Cell(130, 4.5, 'CÉDULA: 054-0124019-6', 0, 0, 'L');
        $pdf->Cell(125, 4.5, 'TELÉFONO: 829-974-3255', 0, 1, 'L');

        $pdf->Ln(4);
    }

    private function dibujarTablaReclamaciones($pdf, $datos)
    {
        // Anchos de columnas en orientación horizontal (Letter = 279.4mm - 24mm márgenes = 255.4mm utilizables)
        $wFecha = 24;       // 24mm
        $wAuto = 38;        // 38mm
        $wAfiliado = 80;    // 80mm
        $wCarnet = 38;      // 38mm
        $wServicio = 45;    // 45mm
        $wMonto = 30.4;     // 30.4mm
        $wTotalFila = 255.4;

        if (empty($datos)) {
            $pdf->Ln(10);
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->Cell(0, 10, 'No se encontraron reclamaciones registradas para el período seleccionado.', 1, 1, 'C');
            return;
        }

        // Agrupar por ARS
        $gruposARS = [];
        foreach ($datos as $row) {
            $ars = !empty($row['NombreARS']) ? $row['NombreARS'] : 'SIN ASEGURADORA';
            if (!isset($gruposARS[$ars])) {
                $gruposARS[$ars] = [];
            }
            $gruposARS[$ars][] = $row;
        }

        $granTotalMonto = 0;
        $granTotalCasos = 0;

        foreach ($gruposARS as $nombreARS => $reclamaciones) {
            $subtotalMonto = 0;
            $subtotalCasos = count($reclamaciones);
            $granTotalCasos += $subtotalCasos;

            // Verificar si queda espacio suficiente en la página para encabezado + al menos 2 filas
            if ($pdf->GetY() > 175) {
                $pdf->AddPage();
                $this->dibujarEncabezadoInstitucional($pdf);
            }

            // 1. Cabecera del Grupo ARS
            $pdf->SetFont('helvetica', 'B', 9.5);
            $pdf->SetFillColor(241, 245, 249); // Slate 100
            $pdf->SetTextColor(15, 23, 42);     // Slate 900
            $pdf->SetDrawColor(148, 163, 184); // Slate 400
            $pdf->SetLineWidth(0.3);

            $pdf->Cell($wTotalFila, 6.5, ' ' . mb_strtoupper($nombreARS, 'UTF-8') . '  (' . $subtotalCasos . ' ' . ($subtotalCasos === 1 ? 'Reclamación' : 'Reclamaciones') . ')', 1, 1, 'L', true);

            // 2. Encabezados de Columna de la Tabla
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetFillColor(226, 232, 240); // Slate 200
            $pdf->SetTextColor(51, 65, 85);    // Slate 700
            
            $pdf->Cell($wFecha, 5.5, 'FECHA', 1, 0, 'C', true);
            $pdf->Cell($wAuto, 5.5, 'N° AUTORIZACIÓN', 1, 0, 'C', true);
            $pdf->Cell($wAfiliado, 5.5, 'NOMBRE DEL AFILIADO', 1, 0, 'L', true);
            $pdf->Cell($wCarnet, 5.5, 'CARNET / AFILIADO', 1, 0, 'C', true);
            $pdf->Cell($wServicio, 5.5, 'SERVICIO', 1, 0, 'L', true);
            $pdf->Cell($wMonto, 5.5, 'MONTO (RD$)', 1, 1, 'R', true);

            // 3. Filas de Reclamaciones
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(15, 23, 42);
            $fill = false;

            foreach ($reclamaciones as $rec) {
                if ($pdf->GetY() > 185) {
                    $pdf->AddPage();
                    $this->dibujarEncabezadoInstitucional($pdf);

                    // Re-dibujar encabezados de columna
                    $pdf->SetFont('helvetica', 'B', 8);
                    $pdf->SetFillColor(226, 232, 240);
                    $pdf->SetTextColor(51, 65, 85);
                    $pdf->Cell($wFecha, 5.5, 'FECHA', 1, 0, 'C', true);
                    $pdf->Cell($wAuto, 5.5, 'N° AUTORIZACIÓN', 1, 0, 'C', true);
                    $pdf->Cell($wAfiliado, 5.5, 'NOMBRE DEL AFILIADO', 1, 0, 'L', true);
                    $pdf->Cell($wCarnet, 5.5, 'CARNET / AFILIADO', 1, 0, 'C', true);
                    $pdf->Cell($wServicio, 5.5, 'SERVICIO', 1, 0, 'L', true);
                    $pdf->Cell($wMonto, 5.5, 'MONTO (RD$)', 1, 1, 'R', true);

                    $pdf->SetFont('helvetica', '', 8);
                    $pdf->SetTextColor(15, 23, 42);
                }

                $monto = (float)$rec['MontoAprobado'];
                $subtotalMonto += $monto;
                $granTotalMonto += $monto;

                $fechaFormat = !empty($rec['FechaAprobacionD']) ? date('d/m/Y', strtotime($rec['FechaAprobacionD'])) : '-';
                $numAuto = !empty($rec['NumAprobacion']) ? mb_strtoupper($rec['NumAprobacion'], 'UTF-8') : '-';
                $nombreAfiliado = !empty($rec['CardName']) ? mb_strtoupper($rec['CardName'], 'UTF-8') : '-';
                $carnet = !empty($rec['NumAfiliado']) ? mb_strtoupper($rec['NumAfiliado'], 'UTF-8') : '-';
                $rawServ = !empty($rec['TipoServicio']) ? $rec['TipoServicio'] : 'CONSULTA MEDICA';
                $servicio = strtoupper(str_replace(
                    ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ'],
                    ['a','e','i','o','u','A','E','I','O','U','n','N'],
                    $rawServ
                ));

                $pdf->SetFillColor(248, 250, 252); // Alternating zebra fill

                $pdf->Cell($wFecha, 5.2, $fechaFormat, 1, 0, 'C', $fill);
                $pdf->Cell($wAuto, 5.2, $numAuto, 1, 0, 'C', $fill);
                $pdf->Cell($wAfiliado, 5.2, ' ' . mb_substr($nombreAfiliado, 0, 42, 'UTF-8'), 1, 0, 'L', $fill);
                $pdf->Cell($wCarnet, 5.2, $carnet, 1, 0, 'C', $fill);
                $pdf->Cell($wServicio, 5.2, ' ' . mb_substr($servicio, 0, 26, 'UTF-8'), 1, 0, 'L', $fill);
                $pdf->Cell($wMonto, 5.2, number_format($monto, 2) . ' ', 1, 1, 'R', $fill);

                $fill = !$fill;
            }

            // 4. Fila de Subtotal por ARS
            $pdf->SetFont('helvetica', 'B', 8.5);
            $pdf->SetFillColor(234, 243, 250); // Soft sky blue
            $pdf->SetTextColor(15, 23, 42);     // Slate 900
            
            $pdf->Cell($wFecha + $wAuto + $wAfiliado + $wCarnet + $wServicio, 6, 'SUBTOTAL ' . mb_strtoupper($nombreARS, 'UTF-8') . ' (' . $subtotalCasos . '):', 1, 0, 'R', true);
            $pdf->Cell($wMonto, 6, 'RD$ ' . number_format($subtotalMonto, 2) . ' ', 1, 1, 'R', true);
            
            $pdf->Ln(4);
        }

        // 5. Gran Total General
        if ($pdf->GetY() > 175) {
            $pdf->AddPage();
            $this->dibujarEncabezadoInstitucional($pdf);
        }

        $pdf->SetFont('helvetica', 'B', 9.5);
            $pdf->SetFillColor(234, 243, 250); // Soft sky blue
            $pdf->SetTextColor(14, 116, 144);  // Cyan dark

        $pdf->Cell($wFecha + $wAuto + $wAfiliado + $wCarnet + $wServicio, 7, 'GRAN TOTAL GENERAL (' . $granTotalCasos . ' RECLAMACIONES):', 1, 0, 'R', true);
        $pdf->Cell($wMonto, 7, 'RD$ ' . number_format($granTotalMonto, 2) . ' ', 1, 1, 'R', true);
 
    }
}