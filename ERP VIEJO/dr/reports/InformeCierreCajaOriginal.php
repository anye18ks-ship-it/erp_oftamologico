<?php
// reports/InformeCierreCaja.php

require_once __DIR__ . '/../vendor/tcpdf/tcpdf.php';
require_once __DIR__ . '/../config/Database.php';

class InformeCierreCaja
{
    private $pdo;
    private $idDiarioCaja;
    private $esCopia;

    public function __construct($idDiarioCaja, $esCopia = 0)
    {
        $this->pdo = (new Database())->getConnection();
        $this->idDiarioCaja = (int)$idDiarioCaja;
        $this->esCopia = (int)$esCopia;
    }

    public function generar($output = 'I')
    {
        $data = $this->obtenerDatos();

        if (!$data) {
            die("<h2>Error: No se encontraron datos para el ID: " . $this->idDiarioCaja . "</h2>");
        }

        $pdf = new TCPDF('P', 'mm', array(80, 350), true, 'UTF-8', false);
        
        $pdf->SetCreator('Sistema POS');
        $pdf->SetAuthor('Dr. Nellie');
        $pdf->SetTitle('Cierre de Caja Z');
        $pdf->SetMargins(4, 5, 4);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();

        // Configuración de fuente
        $pdf->SetFont('helvetica', '', 8);

        $this->cabecera($pdf, $data);
        $this->resumenGeneral($pdf, $data);
        $this->detalleMediosPago($pdf, $data);
        $this->comentario($pdf, $data);

        $pdf->Output('Cierre_Caja_Z_' . $this->idDiarioCaja . '.pdf', $output);
    }

    private function obtenerDatos()
    {
        $sql = "SELECT 
            T0.id_diario_caja,
            'Z-' + RIGHT(REPLICATE('0',5) + CAST(ISNULL(T0.orden_cierre,1) AS VARCHAR),6) as tipo_arqueo,
            FORMAT(T0.fecha_apertura,'dd/MM/yyyy hh:mm tt') as fecha_apertura,
            FORMAT(T0.fecha_cierre,'dd/MM/yyyy hh:mm tt') as fecha_cierre,
            T2.alias as caja,
            T3.usuario + ' - ' + T3.nombre as cajero_apertura,
            T4.usuario + ' - ' + T4.nombre as cajero_cierre,
            ISNULL(T0.monto_apertura,0) as monto_apertura,
            ISNULL(T0.monto_efectivo,0) as monto_efectivo,
            ISNULL(T0.monto_tarjeta,0) as monto_tarjeta,
            ISNULL(T0.monto_cheque,0) as monto_cheque,
            ISNULL(T0.monto_transferencia,0) as monto_transferencia,
            ISNULL(T0.monto_credito,0) as monto_credito,
            ISNULL(T0.comentario,'') as comentario,
            ISNULL(T5.fact_efectivo,0) as fact_efectivo,
            ISNULL(T5.fact_tarjeta,0) as fact_tarjeta,
            ISNULL(T5.fact_cheque,0) as fact_cheque,
            ISNULL(T5.fact_total,0) as fact_total,
            ISNULL(T6.retiro_total,0) as retiro_total
        FROM diario_caja T0
        INNER JOIN caja T2 ON T0.id_caja = T2.id_caja
        LEFT JOIN usuario T3 ON T0.usuario_apertura = T3.usuario
        LEFT JOIN usuario T4 ON T0.usuario_cierre = T4.usuario
        LEFT JOIN (
            SELECT id_diario_caja,
                SUM(CASE WHEN id_tipo_movimiento = 1 THEN monto_caja ELSE 0 END) as fact_efectivo,
                SUM(CASE WHEN id_tipo_movimiento = 2 THEN monto_caja ELSE 0 END) as fact_tarjeta,
                SUM(CASE WHEN id_tipo_movimiento = 3 THEN monto_caja ELSE 0 END) as fact_cheque,
                SUM(monto_caja) as fact_total
            FROM diario_caja_det 
            WHERE id_diario_caja = ?
            GROUP BY id_diario_caja
        ) T5 ON T0.id_diario_caja = T5.id_diario_caja
        LEFT JOIN (
            SELECT id_diario_caja, SUM(monto) as retiro_total 
            FROM retiro_caja 
            WHERE id_diario_caja = ? 
            GROUP BY id_diario_caja
        ) T6 ON T0.id_diario_caja = T6.id_diario_caja
        WHERE T0.id_diario_caja = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->idDiarioCaja, $this->idDiarioCaja, $this->idDiarioCaja]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function cabecera($pdf, $d)
    {
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(72, 6, 'INFORME CIERRE DE CAJA', 0, 1, 'C');

        if ($this->esCopia) {
            $pdf->SetTextColor(255, 0, 0);
            $pdf->Cell(72, 6, '*** COPIA ***', 0, 1, 'C');
            $pdf->SetTextColor(0, 0, 0);
        }

        $pdf->SetFont('helvetica', '', 8);
        $pdf->Ln(3);

        $pdf->Cell(35, 5, 'Arqueo:', 0, 0);
        $pdf->Cell(0, 5, $d['tipo_arqueo'], 0, 1);

        $pdf->Cell(35, 5, 'Caja:', 0, 0);
        $pdf->Cell(0, 5, $d['caja'], 0, 1);

        $pdf->Cell(35, 5, 'Apertura:', 0, 0);
        $pdf->Cell(0, 5, $d['fecha_apertura'], 0, 1);

        $pdf->Cell(35, 5, 'Cierre:', 0, 0);
        $pdf->Cell(0, 5, $d['fecha_cierre'], 0, 1);

        $pdf->Cell(35, 5, 'Cajero Apertura:', 0, 0);
        $pdf->MultiCell(0, 5, $d['cajero_apertura'], 0, 'L');

        $pdf->Cell(35, 5, 'Cajero Cierre:', 0, 0);
        $pdf->MultiCell(0, 5, $d['cajero_cierre'], 0, 'L');

        $pdf->Ln(4);
        $pdf->Line(4, $pdf->GetY(), 76, $pdf->GetY());
        $pdf->Ln(4);
    }

    private function resumenGeneral($pdf, $d)
    {
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(72, 6, 'RESUMEN GENERAL', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);

        $this->lineaMonto($pdf, 'Total Declarado', $d['monto_efectivo'] + $d['monto_tarjeta'] + $d['monto_cheque']);
        $this->lineaMonto($pdf, 'Ventas', $d['fact_total']);
        $this->lineaMonto($pdf, 'Retiros', $d['retiro_total']);

        $diferencia = ($d['monto_efectivo'] + $d['monto_tarjeta'] + $d['monto_cheque']) - $d['fact_total'] + $d['retiro_total'];
        $this->lineaMonto($pdf, 'DIFERENCIA', $diferencia, true);

        $pdf->Ln(5);
    }

    private function detalleMediosPago($pdf, $d)
    {
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(72, 6, 'DETALLE POR MEDIO', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);

        // Efectivo
        $pdf->Cell(72, 6, 'EFECTIVO', 0, 1, 'C');
        $this->lineaMonto($pdf, 'Declarado', $d['monto_efectivo']);
        $this->lineaMonto($pdf, 'Ventas', $d['fact_efectivo']);

        // Tarjeta
        $pdf->Cell(72, 6, 'TARJETA', 0, 1, 'C');
        $this->lineaMonto($pdf, 'Declarado', $d['monto_tarjeta']);
        $this->lineaMonto($pdf, 'Ventas', $d['fact_tarjeta']);

        // Cheque
        $pdf->Cell(72, 6, 'CHEQUE', 0, 1, 'C');
        $this->lineaMonto($pdf, 'Declarado', $d['monto_cheque']);
        $this->lineaMonto($pdf, 'Ventas', $d['fact_cheque']);
    }

    private function lineaMonto($pdf, $label, $monto, $esDiferencia = false)
    {
        $pdf->Cell(40, 5, $label);
        $pdf->Cell(32, 5, number_format($monto, 2), 0, 1, 'R');

        if ($esDiferencia) {
            $pdf->SetFont('', 'B');
            $pdf->Cell(40, 5, $monto < 0 ? 'FALTANTE' : 'SOBRANTE', 0, 1);
            $pdf->SetFont('', '');
        }
    }

    private function comentario($pdf, $d)
    {
        if (!empty(trim($d['comentario']))) {
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(0, 6, 'COMENTARIO:', 0, 1);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell(0, 5, $d['comentario']);
        }
    }
}