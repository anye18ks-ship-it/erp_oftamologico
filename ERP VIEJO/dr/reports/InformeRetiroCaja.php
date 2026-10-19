<?php
// reports/InformeRetiroCaja.php
require_once __DIR__ . '/../vendor/tcpdf/tcpdf.php';
require_once __DIR__ . '/../config/Database.php';

class InformeRetiroCaja
{
    private $pdo;
    private $idRetiroCaja;
    private $esCopia;

    public function __construct($idRetiroCaja, $esCopia = 0)
    {
        $this->pdo = (new Database())->getConnection();
        $this->idRetiroCaja = (int)$idRetiroCaja;
        $this->esCopia = (int)$esCopia;
    }

    public function generar($output = 'I')
    {
        $data = $this->obtenerDatos();
        if (!$data) {
            die("No se encontró el retiro para el ID: " . $this->idRetiroCaja);
        }

        $pdf = new TCPDF('P', 'mm', [80, 420], true, 'UTF-8', false);
        $pdf->SetCreator('Sistema POS');
        $pdf->SetAuthor('Dr. Nellie');
        $pdf->SetTitle('Retiro de Caja');
        $pdf->SetMargins(4, 5, 4);
        $pdf->SetAutoPageBreak(true, 8);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 8);

        $this->cabecera($pdf, $data);
        $this->detalleRetiro($pdf, $data);
        $this->desgloseDenominaciones($pdf);
        $this->firmas($pdf);

        $pdf->Output('Retiro_' . str_pad($this->idRetiroCaja, 7, '0', STR_PAD_LEFT) . '.pdf', $output);
    }

    private function obtenerDatos()
    {
        $sql = "SELECT 
                    R0.id_retiro_caja,
                    'RET-' + RIGHT(REPLICATE('0',7) + CAST(R0.id_retiro_caja AS VARCHAR),7) as num_retiro,
                    CONVERT(varchar, T0.fecha_apertura, 103) as fecha_caja,
                    CONVERT(varchar, T0.hora_apertura, 108) as hora_caja,
                    T1.alias as caja,
                    R1.descripcion as concepto,
                    R2.descripcion as medio,
                    (R0.monto * -1) as monto, -- Se guarda negativo en la BD
                    ISNULL(R0.referencia, '') as referencia,
                    ISNULL(R0.quien_entrega, '') as quien_entrega,
                    ISNULL(R0.quien_recibe, '') as quien_recibe,
                    ISNULL(R0.comentario, '') as comentario
                FROM retiro_caja R0
                INNER JOIN diario_caja T0 ON R0.id_diario_caja = T0.id_diario_caja
                INNER JOIN caja T1 ON T0.id_caja = T1.id_caja
                LEFT JOIN concepto_retiro R1 ON R0.id_concepto_retiro = R1.id_concepto_retiro
                LEFT JOIN medio_retiro R2 ON R0.id_medio_retiro = R2.id_medio_retiro
                WHERE R0.id_retiro_caja = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->idRetiroCaja]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function cabecera($pdf, $d)
    {
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(72, 7, 'COMPROBANTE DE RETIRO', 0, 1, 'C');
        if ($this->esCopia) {
            $pdf->SetTextColor(255, 0, 0);
            $pdf->Cell(72, 6, '*** COPIA ***', 0, 1, 'C');
            $pdf->SetTextColor(0, 0, 0);
        }
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Ln(2);

        $pdf->Cell(36, 5, 'Retiro:', 0, 0); $pdf->Cell(0, 5, $d['num_retiro'], 0, 1);
        $pdf->Cell(36, 5, 'Fecha Caja:', 0, 0); $pdf->Cell(0, 5, $d['fecha_caja'] . ' ' . $d['hora_caja'], 0, 1);
        $pdf->Cell(36, 5, 'Caja:', 0, 0); $pdf->Cell(0, 5, $d['caja'], 0, 1);
        $pdf->Ln(3);
    }

    private function detalleRetiro($pdf, $d)
    {
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(72, 6, 'DETALLE', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);

        $pdf->Cell(36, 5, 'Concepto:', 0, 0); $pdf->Cell(0, 5, $d['concepto'], 0, 1);
        $pdf->Cell(36, 5, 'Medio:', 0, 0); $pdf->Cell(0, 5, $d['medio'], 0, 1);
        
        if (!empty($d['referencia'])) {
            $pdf->Cell(36, 5, 'Referencia:', 0, 0); $pdf->Cell(0, 5, $d['referencia'], 0, 1);
        }

        $pdf->Ln(2);
        $this->lineaMonto($pdf, 'MONTO:', $d['monto'], true);
        $pdf->Ln(2);

        $pdf->Cell(36, 5, 'Entrega:', 0, 0); $pdf->Cell(0, 5, $d['quien_entrega'], 0, 1);
        $pdf->Cell(36, 5, 'Recibe:', 0, 0); $pdf->Cell(0, 5, $d['quien_recibe'], 0, 1);

        if (!empty(trim($d['comentario']))) {
            $pdf->Ln(2);
            $pdf->Cell(36, 5, 'Comentario:', 0, 0); 
            $pdf->MultiCell(0, 5, $d['comentario']);
        }
    }

    private function desgloseDenominaciones($pdf)
    {
        // Solo mostramos denominaciones si el concepto fue Efectivo (1) y Medio Efectivo (1)
        $sql = "SELECT denominacion, cantidad, total 
                FROM retiro_denominacion 
                WHERE id_retiro_caja = ? 
                ORDER BY denominacion DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->idRetiroCaja]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) return;

        $pdf->Ln(4);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(72, 6, 'DESGLOSE DE EFECTIVO', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);

        foreach ($rows as $r) {
            $pdf->Cell(35, 5, number_format($r['denominacion']), 0, 0);
            $pdf->Cell(15, 5, $r['cantidad'], 0, 0, 'R');
            $pdf->Cell(22, 5, number_format($r['total'], 2), 0, 1, 'R');
        }
    }

    private function firmas($pdf)
    {
        $pdf->Ln(8);
        $pdf->Cell(72, 5, '_________________________', 0, 1, 'C');
        $pdf->Cell(72, 5, 'Firma Quien Entrega', 0, 1, 'C');
        
        $pdf->Ln(5);
        $pdf->Cell(72, 5, '_________________________', 0, 1, 'C');
        $pdf->Cell(72, 5, 'Firma Quien Recibe', 0, 1, 'C');
    }

    private function lineaMonto($pdf, $label, $monto, $esBold = false)
    {
        if ($esBold) {
            $pdf->SetFont('helvetica', 'B', 9);
        }
        $pdf->Cell(36, 6, $label, 0, 0);
        $pdf->Cell(36, 6, 'RD$ ' . number_format($monto, 2), 0, 1, 'R');
        if ($esBold) {
            $pdf->SetFont('helvetica', '', 8);
        }
    }
}