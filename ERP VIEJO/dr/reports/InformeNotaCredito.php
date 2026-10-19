<?php
// reports/InformeNotaCredito.php
require_once __DIR__ . '/../vendor/tcpdf/tcpdf.php';
require_once __DIR__ . '/../config/Database.php';

class InformeNotaCredito
{
    private $pdo;
    private $meses = [
        1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
        5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
        9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
    ];

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $this->pdo = (new Database())->getConnection();
    }

    /**
     * Resolver nombre completo del usuario que emite o genera el comprobante
     */
    private function resolverNombreUsuario($userField = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $userVal = trim((string)($userField ?? ''));

        // 1. Si el campo de usuario tiene valor y no es SYS / genérico, buscar nombre real en la tabla usuario
        if (!empty($userVal) && strtoupper($userVal) !== 'SYS') {
            try {
                $stmt = $this->pdo->prepare("SELECT TOP 1 nombre FROM usuario WHERE usuario = ? OR CAST(id_usuario AS VARCHAR(20)) = ? OR nombre = ?");
                $stmt->execute([$userVal, $userVal, $userVal]);
                $nombreDb = $stmt->fetchColumn();
                if (!empty($nombreDb)) {
                    return trim($nombreDb);
                }
            } catch (Throwable $e) {}

            // Si es un nombre descriptivo ya formateado (no 'ADMIN'), retornarlo
            if (strtoupper($userVal) !== 'ADMIN') {
                return $userVal;
            }
        }

        // 2. Si viene vacío o SYS/ADMIN, buscar el usuario autenticado en la sesión activa
        if (!empty($_SESSION['nombre'])) {
            return trim($_SESSION['nombre']);
        }
        if (!empty($_SESSION['usuario'])) {
            // Intentar buscar el nombre del usuario de sesión
            try {
                $stmt = $this->pdo->prepare("SELECT TOP 1 nombre FROM usuario WHERE usuario = ? OR CAST(id_usuario AS VARCHAR(20)) = ?");
                $stmt->execute([$_SESSION['usuario'], $_SESSION['usuario']]);
                $nombreSes = $stmt->fetchColumn();
                if (!empty($nombreSes)) {
                    return trim($nombreSes);
                }
            } catch (Throwable $e) {}
            return trim($_SESSION['usuario']);
        }

        // 3. Fallback de contingencia
        return 'SYS';
    }

    /**
     * Generar Comprobante Fiscal Oficial de Nota de Crédito individual
     */
    public function generarComprobante($id, $esCopia = 0, $output = 'I')
    {
        $id = (int)$id;
        $data = $this->obtenerDatosNC($id);

        if (!$data || empty($data['cabecera'])) {
            die("No se encontró la Nota de Crédito con ID: " . htmlspecialchars($id));
        }

        $c = $data['cabecera'];
        $lineas = $data['lineas'];

        // Orientación Vertical (Portrait) Letter
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
        $pdf->SetCreator('ERP Oftalmológico');
        $pdf->SetAuthor('Dra. Nellie Grace Olivo Marte');
        $pdf->SetTitle('Nota_Credito_' . ($c['NumNCF'] ?? $c['C_DocEntry']));

        $pdf->SetMargins(14, 12, 14);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(10);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        // 1. Encabezado Institucional y Banner de Nota de Crédito
        $this->dibujarCabeceraComprobante($pdf, $c, $esCopia);

        // 2. Información del Receptor (Cliente / Paciente) y Factura Afectada
        $this->dibujarDatosFiscalesYReceptor($pdf, $c);

        // 3. Tabla de Productos / Servicios
        $this->dibujarTablaLineas($pdf, $lineas, $c);

        // 4. Totales y Firmas
        $this->dibujarTotalesYFirmas($pdf, $c);

        return $pdf->Output('Nota_Credito_' . ($c['NumNCF'] ?: $c['C_DocEntry']) . '.pdf', $output);
    }

    /**
     * Obtener datos del emisor desde la tabla division con fallback
     */
    private function obtenerDatosEmisor()
    {
        try {
            $stmt = $this->pdo->query("SELECT TOP 1 * FROM division ORDER BY id_division ASC");
            $div = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            if ($div) {
                return [
                    'razon_social' => trim($div['razon_social'] ?? 'DRA. NELLIE GRACE OLIVO MARTE'),
                    'nombre'       => trim($div['nombre'] ?? 'UNIDAD OFTALMOLOGICA DRA. OLIVO MARTE'),
                    'rnc'          => trim($div['RNC'] ?? '05401240196'),
                    'direccion'    => trim($div['direccion'] ?? 'VILLA ESTELA, MOCA, PROVINCIA ESPAILLAT'),
                    'telefono'     => trim($div['telefono1'] ?? '809-284-7072'),
                    'email'        => trim($div['email'] ?? 'DRA.OLIVOMARTE@GMAIL.COM')
                ];
            }
        } catch (Throwable $e) {
            // fallback silencioso
        }
        return [
            'razon_social' => 'DRA. NELLIE GRACE OLIVO MARTE',
            'nombre'       => 'UNIDAD OFTALMOLOGICA DRA. OLIVO MARTE',
            'rnc'          => '05401240196',
            'direccion'    => 'VILLA ESTELA, MOCA, PROVINCIA ESPAILLAT',
            'telefono'     => '809-284-7072',
            'email'        => 'DRA.OLIVOMARTE@GMAIL.COM'
        ];
    }

    /**
     * Obtener Cabecera y Líneas de una NC
     */
    private function obtenerDatosNC($id)
    {
        $sqlH = "SELECT 
                    nc.C_DocEntry,
                    nc.DocEntry,
                    nc.DocNum,
                    nc.DocDate,
                    FORMAT(nc.DocDate, 'dd/MM/yyyy') AS DocDateFmt,
                    FORMAT(nc.C_Inserted, 'dd/MM/yyyy hh:mm tt') AS C_InsertedFmt,
                    nc.CardCode,
                    nc.CardName,
                    ISNULL(nc.Address, '') AS Address,
                    ISNULL(nc.U_FacNit, '') AS U_FacNit,
                    ISNULL(nc.U_FACNUM, '') AS NumNCF,
                    ISNULL(nc.U_FacSerie, 'E34') AS SerieNC,
                    ISNULL(nc.U_NCO, '') AS NumFacturaOrigen,
                    ISNULL(nc.C_FromNumAtCard, '') AS C_FromNumAtCard,
                    nc.C_DocOrigenNum,
                    nc.U_TipoNC,
                    ISNULL(ta.Name, 'Devolución / Corrección de Factura') AS TipoNC_Nombre,
                    ISNULL(nc.C_DocNet, 0) AS SubtotalNeto,
                    ISNULL(nc.VatSum, 0) AS ITBIS,
                    ISNULL(nc.DocTotal, 0) AS TotalNC,
                    ISNULL(nc.Comments, '') AS Motivo,
                    nc.U_user,
                    ISNULL(u.nombre, ISNULL(nc.U_user, 'SYS')) AS Usuario,
                    ISNULL(nc.U_terminal, '') AS Terminal,
                    ISNULL(nc.CANCELED, 'N') AS CANCELED,
                    inv.DocTotal AS FacturaDocTotal,
                    inv.NumAtCard AS FacturaNCF,
                    FORMAT(inv.DocDate, 'dd/MM/yyyy') AS FacturaDocDateFmt
                 FROM POSORIN nc
                 LEFT JOIN TIPOS_ANULACION ta ON nc.U_TipoNC = ta.code
                 LEFT JOIN POSOINV inv ON nc.C_DocOrigenNum = inv.C_DocEntry
                 LEFT JOIN usuario u ON (nc.U_user = u.usuario OR nc.U_user = CAST(u.id_usuario AS VARCHAR(20)) OR nc.U_user = u.nombre)
                 WHERE nc.C_DocEntry = ?";

        $stmtH = $this->pdo->prepare($sqlH);
        $stmtH->execute([$id]);
        $cabecera = $stmtH->fetch(PDO::FETCH_ASSOC);

        if (!$cabecera) {
            return null;
        }

        // Resolver nombre descriptivo del usuario emisor
        $cabecera['Usuario'] = $this->resolverNombreUsuario($cabecera['Usuario'] ?? $cabecera['U_user'] ?? '');


        $sqlL = "SELECT 
                    l.LineNum,
                    l.ItemCode,
                    l.Dscription,
                    l.Quantity,
                    l.Price,
                    l.PriceBefDi,
                    l.DiscPrcnt,
                    l.LineTotal,
                    l.VatPrcnt,
                    l.VatSum,
                    l.WhsCode
                 FROM POSRIN1 l
                 WHERE l.C_DocEntry = ?
                 ORDER BY l.LineNum ASC";
        $stmtL = $this->pdo->prepare($sqlL);
        $stmtL->execute([$id]);
        $lineas = $stmtL->fetchAll(PDO::FETCH_ASSOC);

        return ['cabecera' => $cabecera, 'lineas' => $lineas];
    }

    /**
     * Cabecera Institucional del Comprobante
     */
    private function dibujarCabeceraComprobante($pdf, $c, $esCopia)
    {
        $emisor = $this->obtenerDatosEmisor();
        $isElectronico = (stripos($c['SerieNC'], 'E') === 0 || stripos($c['NumNCF'], 'E') === 0);
        $tituloDoc = $isElectronico ? 'NOTA DE CRÉDITO ELECTRÓNICA (e-CF)' : 'NOTA DE CRÉDITO';

        // Marco superior izquierdo: Datos del Emisor
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(110, 6, mb_strtoupper($emisor['razon_social'], 'UTF-8'), 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->SetTextColor(51, 65, 85);
        $pdf->Cell(110, 4.5, 'RNC / CÉDULA: ' . $emisor['rnc'], 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(110, 4, 'ESPECIALIDAD: OFTALMOLOGÍA CLÍNICA Y QUIRÚRGICA', 0, 1, 'L');
        $pdf->Cell(110, 4, 'CENTRO: ' . mb_strtoupper($emisor['nombre'], 'UTF-8'), 0, 1, 'L');
        $pdf->Cell(110, 4, 'TEL: ' . $emisor['telefono'] . ' | CORREO: ' . mb_strtoupper($emisor['email'], 'UTF-8'), 0, 1, 'L');

        // Cuadro Superior Derecho: Caja de Comprobante Fiscal
        $pdf->SetXY(126, 12);
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.3);
        $pdf->RoundedRect(126, 12, 74, 34, 2.5, '1111', 'DF');

        $pdf->SetXY(128, 14);
        $pdf->SetFont('helvetica', 'B', 9.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(70, 5, $tituloDoc, 0, 1, 'C');

        $pdf->SetXY(128, 19.5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(185, 28, 28); // Rojo fiscal
        $pdf->Cell(70, 6, 'NCF: ' . ($c['NumNCF'] ? mb_strtoupper($c['NumNCF'], 'UTF-8') : 'PENDIENTE'), 0, 1, 'C');

        $pdf->SetXY(128, 26);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(32, 4, 'FECHA EMISIÓN:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(38, 4, $c['DocDateFmt'], 0, 1, 'R');

        $pdf->SetXY(128, 30);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(32, 4, 'SERIE COMPROBANTE:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(38, 4, mb_strtoupper($c['SerieNC'], 'UTF-8'), 0, 1, 'R');

        $pdf->SetXY(128, 34.5);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(32, 4, 'TIPO DOCUMENTO:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 8);
        if ($esCopia) {
            $pdf->SetTextColor(220, 38, 38);
            $pdf->Cell(38, 4, '*** COPIA ***', 0, 1, 'R');
        } else {
            $pdf->SetTextColor(16, 185, 129);
            $pdf->Cell(38, 4, 'ORIGINAL', 0, 1, 'R');
        }

        $pdf->SetY(48);
    }

    /**
     * Sección de Datos del Receptor y Modificación Fiscal (Factura Afectada)
     */
    private function dibujarDatosFiscalesYReceptor($pdf, $c)
    {
        $y = $pdf->GetY();

        // Cuadro 1: Datos del Paciente / Cliente (Izquierda)
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(226, 232, 240);
        $pdf->SetLineWidth(0.25);
        $pdf->RoundedRect(14, $y, 90, 26, 2, '1111', 'DF');

        $pdf->SetXY(16, $y + 1.5);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(86, 4, 'DATOS DEL RECEPTOR / CLIENTE', 'B', 1, 'L');

        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->SetXY(16, $y + 6.5);
        $pdf->Cell(20, 4, 'NOMBRE:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(66, 4, mb_substr($c['CardName'] ?: 'CONSUMIDOR FINAL', 0, 38, 'UTF-8'), 0, 1, 'L');

        $pdf->SetXY(16, $y + 11);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(20, 4, 'RNC/CÉDULA:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(66, 4, $c['U_FacNit'] ?: 'NO ESPECIFICADO', 0, 1, 'L');

        $pdf->SetXY(16, $y + 15.5);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(20, 4, 'CÓD. SOCIO:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(66, 4, $c['CardCode'], 0, 1, 'L');

        $pdf->SetXY(16, $y + 20);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(20, 4, 'DIRECCIÓN:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(66, 4, mb_substr($c['Address'] ?: 'SANTO DOMINGO, REP. DOM.', 0, 45, 'UTF-8'), 0, 1, 'L');

        // Cuadro 2: Datos de Modificación Fiscal (Factura Origen) (Derecha)
        $pdf->RoundedRect(108, $y, 92, 26, 2, '1111', 'DF');

        $pdf->SetXY(110, $y + 1.5);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(88, 4, 'MODIFICACIÓN FISCAL (FACTURA AFECTADA)', 'B', 1, 'L');

        $pdf->SetXY(110, $y + 6.5);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(34, 4, 'NCF FACTURA:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(2, 132, 199);
        $facAfectadaStr = mb_strtoupper($c['NumFacturaOrigen'] ?: ($c['FacturaNCF'] ?: 'NO ESPECIFICADO'), 'UTF-8');
        $pdf->Cell(54, 4, $facAfectadaStr, 0, 1, 'L');

        $pdf->SetXY(110, $y + 11);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(34, 4, 'FECHA FACTURA MODIF.:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(54, 4, $c['FacturaDocDateFmt'] ?: 'N/A', 0, 1, 'L');

        $pdf->SetXY(110, $y + 15.5);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(34, 4, 'TIPO MODIFICACIÓN:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(15, 23, 42);
        $tipoDesc = ($c['U_TipoNC'] ? $c['U_TipoNC'] . ' - ' : '') . $c['TipoNC_Nombre'];
        $pdf->Cell(54, 4, mb_substr($tipoDesc, 0, 36, 'UTF-8'), 0, 1, 'L');

        $pdf->SetXY(110, $y + 20);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(34, 4, 'MOTIVO / CONCEPTO:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'I', 7);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(54, 4, mb_substr($c['Motivo'] ?: 'EMISIÓN DE NOTA DE CRÉDITO', 0, 38, 'UTF-8'), 0, 1, 'L');

        $pdf->SetY($y + 29);
    }

    /**
     * Tabla de Artículos Afectados / Devueltos
     */
    private function dibujarTablaLineas($pdf, $lineas, $c)
    {
        // Anchos de columnas en mm (Total = 186 mm)
        $wCod = 22;
        $wDesc = 74;
        $wCant = 16;
        $wPrec = 22;
        $wDescP = 14;
        $wItbis = 18;
        $wTotal = 20;

        // Cabecera de Tabla
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetFillColor(241, 245, 249);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.2);

        $pdf->Cell($wCod, 5.5, 'CÓDIGO', 1, 0, 'C', true);
        $pdf->Cell($wDesc, 5.5, 'DESCRIPCIÓN DEL ARTÍCULO / SERVICIO', 1, 0, 'L', true);
        $pdf->Cell($wCant, 5.5, 'CANT.', 1, 0, 'C', true);
        $pdf->Cell($wPrec, 5.5, 'PRECIO UNIT.', 1, 0, 'R', true);
        $pdf->Cell($wDescP, 5.5, 'DESC.%', 1, 0, 'C', true);
        $pdf->Cell($wItbis, 5.5, 'ITBIS', 1, 0, 'R', true);
        $pdf->Cell($wTotal, 5.5, 'TOTAL (RD$)', 1, 1, 'R', true);

        // Filas de Datos
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(15, 23, 42);

        $fill = false;
        foreach ($lineas as $l) {
            $pdf->SetFillColor(248, 250, 252);
            $qty = (float)($l['Quantity'] ?? 1);
            $precio = (float)($l['PriceBefDi'] ?? $l['Price'] ?? 0);
            $disc = (float)($l['DiscPrcnt'] ?? 0);
            $vat = (float)($l['VatSum'] ?? 0);
            $totalLinea = (float)($l['LineTotal'] ?? 0);

            $pdf->Cell($wCod, 5, $l['ItemCode'], 'LR', 0, 'C', $fill);
            $pdf->Cell($wDesc, 5, mb_substr($l['Dscription'], 0, 52, 'UTF-8'), 'LR', 0, 'L', $fill);
            $pdf->Cell($wCant, 5, number_format($qty, 2), 'LR', 0, 'C', $fill);
            $pdf->Cell($wPrec, 5, number_format($precio, 2), 'LR', 0, 'R', $fill);
            $pdf->Cell($wDescP, 5, number_format($disc, 0) . '%', 'LR', 0, 'C', $fill);
            $pdf->Cell($wItbis, 5, number_format($vat, 2), 'LR', 0, 'R', $fill);
            $pdf->Cell($wTotal, 5, number_format($totalLinea, 2), 'LR', 1, 'R', $fill);

            $fill = !$fill;
        }

        // Línea inferior de la tabla
        $pdf->Cell(186, 0.1, '', 'T', 1, 'C');
        $pdf->Ln(2);
    }

    /**
     * Totales, Datos de Auditoría y Firmas
     */
    private function dibujarTotalesYFirmas($pdf, $c)
    {
        $y = $pdf->GetY();

        // Bloque Izquierdo: Información de Auditoría y Observaciones
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetDrawColor(226, 232, 240);
        $pdf->RoundedRect(14, $y, 100, 24, 2, '1111', 'DF');

        $pdf->SetXY(16, $y + 2);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(96, 4, 'AUDITORÍA Y CONTROL DE EMISIÓN', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(51, 65, 85);
        $pdf->SetXY(16, $y + 6.5);
        $pdf->Cell(96, 3.5, 'EMITIDO POR: ' . htmlspecialchars($c['Usuario']) . ' | TERMINAL: ' . htmlspecialchars($c['Terminal'] ?: 'POS-01'), 0, 1, 'L');

        $pdf->SetXY(16, $y + 10.5);
        $pdf->Cell(96, 3.5, 'FECHA REGISTRO: ' . htmlspecialchars($c['C_InsertedFmt'] ?: $c['DocDateFmt']), 0, 1, 'L');

        $pdf->SetXY(16, $y + 14.5);
        $pdf->SetFont('helvetica', 'I', 6.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(96, 3.5, 'Documento fiscal emitido conforme a normativas de comprobantes de la DGII.', 0, 1, 'L');

        // Bloque Derecho: Desglose de Totales Fiscales
        $subtotal = (float)$c['SubtotalNeto'];
        $itbis = (float)$c['ITBIS'];
        $total = (float)$c['TotalNC'];

        $pdf->SetXY(120, $y);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(51, 65, 85);
        $pdf->Cell(45, 5, 'SUBTOTAL NETO:', 0, 0, 'R');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(35, 5, 'RD$ ' . number_format($subtotal, 2), 0, 1, 'R');

        $pdf->SetXY(120, $y + 5.5);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(45, 5, 'TOTAL ITBIS (18%):', 0, 0, 'R');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(35, 5, 'RD$ ' . number_format($itbis, 2), 0, 1, 'R');

        // Total Final Destacado
        $pdf->SetXY(120, $y + 12);
        $pdf->SetFillColor(239, 246, 255);
        $pdf->SetDrawColor(191, 219, 254);
        $pdf->RoundedRect(120, $y + 11.5, 80, 11, 2, '1111', 'DF');

        $pdf->SetXY(122, $y + 13.5);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(30, 58, 138);
        $pdf->Cell(40, 7, 'TOTAL NOTA CRÉDITO:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 10.5);
        $pdf->SetTextColor(185, 28, 28);
        $pdf->Cell(36, 7, 'RD$ ' . number_format($total, 2), 0, 1, 'R');

        // Sección de Firmas al final
        $pdf->SetY($y + 36);
        $pdf->SetDrawColor(148, 163, 184);
        $pdf->SetLineWidth(0.3);

        $pdf->Line(24, $pdf->GetY() + 12, 84, $pdf->GetY() + 12);
        $pdf->Line(126, $pdf->GetY() + 12, 186, $pdf->GetY() + 12);

        $pdf->SetY($pdf->GetY() + 14);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(100, 4, 'FIRMA Y SELLO AUTORIZADO (CLÍNICA)', 0, 0, 'C');
        $pdf->Cell(100, 4, 'RECIBIDO CONFORME (CLIENTE / PACIENTE)', 0, 1, 'C');
    }

    /**
     * Generar Relación General de Notas de Crédito por Período (Reporte mensual de Auditoría)
     */
    public function generarRelacionGeneral($mes, $anio, $output = 'I')
    {
        $mes = (int)$mes;
        $anio = (int)$anio;
        $nombreMes = $this->meses[$mes] ?? 'PERIODO';
        $emisor = $this->obtenerDatosEmisor();

        $sql = "SELECT 
                    nc.C_DocEntry,
                    nc.DocDate,
                    FORMAT(nc.DocDate, 'dd/MM/yyyy') AS DocDateFmt,
                    nc.CardCode,
                    nc.CardName,
                    ISNULL(nc.U_FacNit, '') AS U_FacNit,
                    nc.U_FACNUM AS NumNCF,
                    ISNULL(nc.U_NCO, '') AS NumFacturaOrigen,
                    ISNULL(ta.Name, 'Devolución') AS TipoNC_Nombre,
                    ISNULL(nc.C_DocNet, 0) AS SubtotalNeto,
                    ISNULL(nc.VatSum, 0) AS ITBIS,
                    ISNULL(nc.DocTotal, 0) AS TotalNC,
                    nc.U_user,
                    ISNULL(u.nombre, ISNULL(nc.U_user, 'SYS')) AS Usuario,
                    ISNULL(nc.CANCELED, 'N') AS CANCELED
                FROM POSORIN nc
                LEFT JOIN TIPOS_ANULACION ta ON nc.U_TipoNC = ta.code
                LEFT JOIN usuario u ON (nc.U_user = u.usuario OR nc.U_user = CAST(u.id_usuario AS VARCHAR(20)) OR nc.U_user = u.nombre)
                WHERE MONTH(nc.DocDate) = ? AND YEAR(nc.DocDate) = ?
                ORDER BY nc.DocDate ASC, nc.C_DocEntry ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$mes, $anio]);
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Orientación Horizontal (Landscape)
        $pdf = new TCPDF('L', 'mm', 'LETTER', true, 'UTF-8', false);
        $pdf->SetCreator('ERP Oftalmológico');
        $pdf->SetAuthor($emisor['razon_social']);
        $pdf->SetTitle('Relacion_Notas_Credito_' . $nombreMes . '_' . $anio);

        $pdf->SetMargins(12, 12, 12);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(10);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        // Encabezado del Reporte
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(0, 6, 'RELACIÓN GENERAL DE NOTAS DE CRÉDITO FISCALES (NCF)', 0, 1, 'C');

        $usuarioGen = $this->resolverNombreUsuario();
        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(0, 4.5, 'PERÍODO: ' . $nombreMes . ' ' . $anio . ' | ' . mb_strtoupper($emisor['razon_social'], 'UTF-8'), 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(0, 4, 'GENERADO POR: ' . mb_strtoupper($usuarioGen, 'UTF-8') . ' | FECHA IMPRESIÓN: ' . date('d/m/Y h:i A'), 0, 1, 'C');

        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(12, $pdf->GetY() + 1, 267, $pdf->GetY() + 1);
        $pdf->Ln(4);

        // Anchos de columnas en Horizontal (Total = 255mm)
        $wFecha = 20;
        $wNCF = 30;
        $wFacOri = 30;
        $wRNC = 24;
        $wCliente = 60;
        $wTipo = 35;
        $wSubt = 18;
        $wItbis = 18;
        $wTotal = 20;

        // Cabecera Tabla
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetFillColor(241, 245, 249);
        $pdf->SetTextColor(30, 41, 59);

        $pdf->Cell($wFecha, 5.5, 'FECHA', 1, 0, 'C', true);
        $pdf->Cell($wNCF, 5.5, 'NCF NOTA CRÉD.', 1, 0, 'C', true);
        $pdf->Cell($wFacOri, 5.5, 'FACTURA AFECTADA', 1, 0, 'C', true);
        $pdf->Cell($wRNC, 5.5, 'RNC / CÉDULA', 1, 0, 'C', true);
        $pdf->Cell($wCliente, 5.5, 'CLIENTE / PACIENTE', 1, 0, 'L', true);
        $pdf->Cell($wTipo, 5.5, 'MOTIVO ANULACIÓN', 1, 0, 'L', true);
        $pdf->Cell($wSubt, 5.5, 'SUBTOTAL', 1, 0, 'R', true);
        $pdf->Cell($wItbis, 5.5, 'ITBIS', 1, 0, 'R', true);
        $pdf->Cell($wTotal, 5.5, 'TOTAL (RD$)', 1, 1, 'R', true);

        // Contenido
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(15, 23, 42);

        $sumSubt = 0;
        $sumItbis = 0;
        $sumTotal = 0;
        $fill = false;

        foreach ($registros as $r) {
            $pdf->SetFillColor(248, 250, 252);
            $sub = (float)$r['SubtotalNeto'];
            $itb = (float)$r['ITBIS'];
            $tot = (float)$r['TotalNC'];

            if ($r['CANCELED'] !== 'Y') {
                $sumSubt += $sub;
                $sumItbis += $itb;
                $sumTotal += $tot;
            }

            $pdf->Cell($wFecha, 5, $r['DocDateFmt'], 1, 0, 'C', $fill);
            $pdf->Cell($wNCF, 5, $r['NumNCF'], 1, 0, 'C', $fill);
            $pdf->Cell($wFacOri, 5, $r['NumFacturaOrigen'], 1, 0, 'C', $fill);
            $pdf->Cell($wRNC, 5, $r['U_FacNit'], 1, 0, 'C', $fill);
            $pdf->Cell($wCliente, 5, mb_substr($r['CardName'], 0, 38, 'UTF-8'), 1, 0, 'L', $fill);
            $pdf->Cell($wTipo, 5, mb_substr($r['TipoNC_Nombre'], 0, 22, 'UTF-8'), 1, 0, 'L', $fill);
            $pdf->Cell($wSubt, 5, number_format($sub, 2), 1, 0, 'R', $fill);
            $pdf->Cell($wItbis, 5, number_format($itb, 2), 1, 0, 'R', $fill);
            $pdf->Cell($wTotal, 5, number_format($tot, 2), 1, 1, 'R', $fill);

            $fill = !$fill;
        }

        // Fila de Totales
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetFillColor(226, 232, 240);
        $wTotalLabel = $wFecha + $wNCF + $wFacOri + $wRNC + $wCliente + $wTipo;
        $pdf->Cell($wTotalLabel, 6, 'TOTAL GENERAL DEL PERÍODO (' . count($registros) . ' NCs):', 1, 0, 'R', true);
        $pdf->Cell($wSubt, 6, number_format($sumSubt, 2), 1, 0, 'R', true);
        $pdf->Cell($wItbis, 6, number_format($sumItbis, 2), 1, 0, 'R', true);
        $pdf->SetTextColor(185, 28, 28);
        $pdf->Cell($wTotal, 6, number_format($sumTotal, 2), 1, 1, 'R', true);

        return $pdf->Output('Relacion_Notas_Credito_' . $nombreMes . '_' . $anio . '.pdf', $output);
    }

    /**
     * Generar Comprobante Fiscal Oficial de Nota de Débito individual (E33)
     */
    public function generarComprobanteND($id, $esCopia = 0, $output = 'I')
    {
        $id = (int)$id;
        $data = $this->obtenerDatosND($id);

        if (!$data || empty($data['cabecera'])) {
            die("No se encontró la Nota de Débito con ID: " . htmlspecialchars($id));
        }

        $c = $data['cabecera'];
        $lineas = $data['lineas'];

        // Orientación Vertical (Portrait) Letter
        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false);
        $pdf->SetCreator('ERP Oftalmológico');
        $pdf->SetAuthor('Dra. Nellie Grace Olivo Marte');
        $pdf->SetTitle('Nota_Debito_' . ($c['NumNCF'] ?? $c['C_DocEntry']));

        $pdf->SetMargins(14, 12, 14);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(10);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        // 1. Encabezado Institucional y Banner de Nota de Débito
        $this->dibujarCabeceraComprobanteND($pdf, $c, $esCopia);

        // 2. Información del Receptor y Nota de Crédito Afectada
        $this->dibujarDatosFiscalesYReceptorND($pdf, $c);

        // 3. Tabla de Productos / Servicios
        $this->dibujarTablaLineas($pdf, $lineas, $c);

        // 4. Totales y Firmas
        $this->dibujarTotalesYFirmas($pdf, $c);

        return $pdf->Output('Nota_Debito_' . ($c['NumNCF'] ?: $c['C_DocEntry']) . '.pdf', $output);
    }

    /**
     * Obtener Cabecera y Líneas de una ND
     */
    private function obtenerDatosND($id)
    {
        $sqlH = "SELECT 
                    nd.C_DocEntry,
                    nd.DocEntry,
                    nd.DocNum,
                    nd.DocDate,
                    FORMAT(nd.DocDate, 'dd/MM/yyyy') AS DocDateFmt,
                    FORMAT(nd.C_Inserted, 'dd/MM/yyyy hh:mm tt') AS C_InsertedFmt,
                    nd.CardCode,
                    nd.CardName,
                    ISNULL(nd.Address, '') AS Address,
                    ISNULL(nd.U_FacNit, '') AS U_FacNit,
                    ISNULL(nd.U_FACNUM, '') AS NumNCF,
                    ISNULL(nd.U_FacSerie, 'E33') AS SerieNC,
                    ISNULL(nd.U_NCO, '') AS NumFacturaOrigen,
                    ISNULL(nd.C_FromNumAtCard, '') AS C_FromNumAtCard,
                    nd.C_DocOrigenNum,
                    'Ajuste / Modificación de Factura' AS TipoNC_Nombre,
                    ISNULL(nd.C_DocNet, 0) AS SubtotalNeto,
                    ISNULL(nd.VatSum, 0) AS ITBIS,
                    ISNULL(nd.DocTotal, 0) AS TotalNC,
                    ISNULL(nd.Comments, '') AS Motivo,
                    nd.U_user,
                    ISNULL(u.nombre, ISNULL(nd.U_user, 'SYS')) AS Usuario,
                    ISNULL(nd.U_terminal, '') AS Terminal,
                    ISNULL(nd.CANCELED, 'N') AS CANCELED,
                    inv.DocTotal AS FacturaDocTotal,
                    ISNULL(inv.U_FACNUM, inv.NumAtCard) AS FacturaNCF,
                    FORMAT(inv.DocDate, 'dd/MM/yyyy') AS FacturaDocDateFmt
                 FROM POSODIN nd
                 LEFT JOIN POSOINV inv ON nd.C_DocOrigenNum = inv.C_DocEntry
                 LEFT JOIN usuario u ON (nd.U_user = u.usuario OR nd.U_user = CAST(u.id_usuario AS VARCHAR(20)) OR nd.U_user = u.nombre)
                 WHERE nd.C_DocEntry = ?";

        $stmtH = $this->pdo->prepare($sqlH);
        $stmtH->execute([$id]);
        $cabecera = $stmtH->fetch(PDO::FETCH_ASSOC);

        if (!$cabecera) {
            return null;
        }

        // Resolver nombre descriptivo del usuario emisor
        $cabecera['Usuario'] = $this->resolverNombreUsuario($cabecera['Usuario'] ?? $cabecera['U_user'] ?? '');


        $sqlL = "SELECT 
                    l.LineNum,
                    l.ItemCode,
                    l.Dscription,
                    l.Quantity,
                    l.Price,
                    l.PriceBefDi,
                    l.DiscPrcnt,
                    l.LineTotal,
                    l.VatPrcnt,
                    l.VatSum,
                    l.WhsCode
                 FROM POSDIN1 l
                 WHERE l.C_DocEntry = ?
                 ORDER BY l.LineNum ASC";
        $stmtL = $this->pdo->prepare($sqlL);
        $stmtL->execute([$id]);
        $lineas = $stmtL->fetchAll(PDO::FETCH_ASSOC);

        return [
            'cabecera' => $cabecera,
            'lineas'   => $lineas
        ];
    }

    /**
     * Cabecera Institucional del Comprobante de Nota de Débito
     */
    private function dibujarCabeceraComprobanteND($pdf, $c, $esCopia)
    {
        $emisor = $this->obtenerDatosEmisor();
        $tituloDoc = 'NOTA DE DÉBITO ELECTRÓNICA (e-CF)';

        // Marco superior izquierdo: Datos del Emisor
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(110, 6, mb_strtoupper($emisor['razon_social'], 'UTF-8'), 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 8.5);
        $pdf->SetTextColor(51, 65, 85);
        $pdf->Cell(110, 4.5, 'RNC / CÉDULA: ' . $emisor['rnc'], 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(110, 4, 'ESPECIALIDAD: OFTALMOLOGÍA CLÍNICA Y QUIRÚRGICA', 0, 1, 'L');
        $pdf->Cell(110, 4, 'CENTRO: ' . mb_strtoupper($emisor['nombre'], 'UTF-8'), 0, 1, 'L');
        $pdf->Cell(110, 4, 'TEL: ' . $emisor['telefono'] . ' | CORREO: ' . mb_strtoupper($emisor['email'], 'UTF-8'), 0, 1, 'L');

        // Cuadro Superior Derecho: Caja de Comprobante Fiscal
        $pdf->SetXY(126, 12);
        $pdf->SetFillColor(254, 243, 199); // Fondo ámbar
        $pdf->SetDrawColor(245, 158, 11);
        $pdf->SetLineWidth(0.3);
        $pdf->RoundedRect(126, 12, 74, 34, 2.5, '1111', 'DF');

        $pdf->SetXY(128, 14);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(180, 83, 9);
        $pdf->Cell(70, 5, $tituloDoc, 0, 1, 'C');

        $pdf->SetXY(128, 19.5);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(180, 83, 9);
        $pdf->Cell(70, 6, 'NCF: ' . ($c['NumNCF'] ? mb_strtoupper($c['NumNCF'], 'UTF-8') : 'PENDIENTE'), 0, 1, 'C');

        $pdf->SetXY(128, 26);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(32, 4, 'FECHA EMISIÓN:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(38, 4, $c['DocDateFmt'], 0, 1, 'R');

        $pdf->SetXY(128, 30);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(32, 4, 'SERIE COMPROBANTE:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(38, 4, $c['SerieNC'] ?? 'E33', 0, 1, 'R');

        $pdf->SetXY(128, 34.5);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(32, 4, 'TIPO DOCUMENTO:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 8);
        if ($esCopia) {
            $pdf->SetTextColor(220, 38, 38);
            $pdf->Cell(38, 4, '*** COPIA ***', 0, 1, 'R');
        } else {
            $pdf->SetTextColor(16, 185, 129);
            $pdf->Cell(38, 4, 'ORIGINAL', 0, 1, 'R');
        }

        $pdf->SetY(48);
    }

    /**
     * Sección de Datos del Receptor y Factura Afectada para Nota de Débito
     */
    private function dibujarDatosFiscalesYReceptorND($pdf, $c)
    {
        $y = $pdf->GetY();

        // Cuadro 1: Datos del Paciente / Cliente (Izquierda)
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(226, 232, 240);
        $pdf->SetLineWidth(0.25);
        $pdf->RoundedRect(14, $y, 90, 26, 2, '1111', 'DF');

        $pdf->SetXY(16, $y + 1.5);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(86, 4, 'DATOS DEL RECEPTOR / CLIENTE', 'B', 1, 'L');

        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->SetXY(16, $y + 6.5);
        $pdf->Cell(20, 4, 'NOMBRE:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(66, 4, mb_substr($c['CardName'] ?: 'CONSUMIDOR FINAL', 0, 38, 'UTF-8'), 0, 1, 'L');

        $pdf->SetXY(16, $y + 11);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(20, 4, 'RNC/CÉDULA:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(66, 4, $c['U_FacNit'] ?: 'NO ESPECIFICADO', 0, 1, 'L');

        $pdf->SetXY(16, $y + 15.5);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(20, 4, 'CÓD. SOCIO:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(66, 4, $c['CardCode'], 0, 1, 'L');

        $pdf->SetXY(16, $y + 20);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(20, 4, 'DIRECCIÓN:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(66, 4, mb_substr($c['Address'] ?: 'SANTO DOMINGO, REP. DOM.', 0, 45, 'UTF-8'), 0, 1, 'L');

        // Cuadro 2: Datos de Modificación Fiscal (Factura Origen Modificada) (Derecha)
        $pdf->RoundedRect(108, $y, 92, 26, 2, '1111', 'DF');

        $pdf->SetXY(110, $y + 1.5);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(67, 56, 202);
        $pdf->Cell(88, 4, 'FACTURA MODIFICADA (ORIGEN)', 'B', 1, 'L');

        $pdf->SetXY(110, $y + 6.5);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(32, 4, 'NCF FACTURA:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->SetTextColor(67, 56, 202);
        $facAfectadaStr = mb_strtoupper($c['FacturaNCF'] ?: ($c['NumFacturaOrigen'] ?: ($c['C_FromNumAtCard'] ?: 'ID ' . $c['C_DocOrigenNum'])), 'UTF-8');
        $pdf->Cell(56, 4, $facAfectadaStr, 0, 1, 'L');

        $pdf->SetXY(110, $y + 11);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(32, 4, 'FECHA FACTURA:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(56, 4, $c['FacturaDocDateFmt'] ?: 'No disponible', 0, 1, 'L');

        $pdf->SetXY(110, $y + 15.5);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(32, 4, 'TOTAL FACTURA:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(56, 4, 'RD$ ' . number_format((float)($c['FacturaDocTotal'] ?? 0), 2), 0, 1, 'L');

        $pdf->SetXY(110, $y + 20);
        $pdf->SetFont('helvetica', 'B', 7.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(32, 4, 'MOTIVO DE DÉBITO:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'I', 7);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(56, 4, mb_substr($c['Motivo'] ?: 'Ajuste / Modificación de Factura', 0, 42, 'UTF-8'), 0, 1, 'L');

        $pdf->SetY($y + 29);
    }
}
?>