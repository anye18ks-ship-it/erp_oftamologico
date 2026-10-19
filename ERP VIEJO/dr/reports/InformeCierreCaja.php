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
            die("No se encontraron datos para el ID: " . $this->idDiarioCaja);
        }

        $pdf = new TCPDF('P', 'mm', [80, 420], true, 'UTF-8', false);
        $pdf->SetCreator('Sistema POS');
        $pdf->SetAuthor('Dr. Nellie');
        $pdf->SetTitle('Cierre de Caja Z');
        $pdf->SetMargins(4, 5, 4);
        $pdf->SetAutoPageBreak(true, 8);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 8);

        $this->cabecera($pdf, $data);
        $this->resumenGeneral($pdf, $data);
        $this->detalleMediosPago($pdf, $data);
        $this->desgloseDenominaciones($pdf);
        $this->retirosRealizados($pdf);
        $this->comentario($pdf, $data);

        $pdf->Output('Cierre_Z_' . str_pad($this->idDiarioCaja, 6, '0', STR_PAD_LEFT) . '.pdf', $output);
    }

    private function obtenerDatos()
    {
        $sql = "SELECT 
            T0.id_diario_caja,
            'Z-' + RIGHT(REPLICATE('0',5) + CAST(ISNULL(T0.orden_cierre,1) AS VARCHAR),6) as tipo_arqueo,
            FORMAT(T0.fecha_apertura,'dd/MM/yyyy hh:mm tt') as fecha_apertura,
            FORMAT(T0.fecha_cierre,'dd/MM/yyyy hh:mm tt') as fecha_cierre,
            T2.alias as caja,
            COALESCE(NULLIF(T3.nombre, ''), T3.usuario, 'N/D') as cajero_apertura,
            COALESCE(NULLIF(T4.nombre, ''), T4.usuario, 'N/D') as cajero_cierre,
            ISNULL(T0.monto_apertura,0) as monto_apertura,
            ISNULL(T0.monto_efectivo,0) as monto_efectivo,
            ISNULL(T0.monto_tarjeta,0) as monto_tarjeta,
            ISNULL(T0.monto_transferencia,0) as monto_transferencia,
            ISNULL(T0.monto_cheque,0) as monto_cheque,
            ISNULL(T0.monto_credito,0) as monto_credito,
            ISNULL(T0.comentario,'') as comentario,
            ISNULL(T5.fact_efectivo,0) as fact_efectivo,
            ISNULL(T5.fact_tarjeta,0) as fact_tarjeta,
            ISNULL(T5.fact_cheque,0) as fact_cheque,
            ISNULL(T5.fact_transferencia,0) as fact_transferencia,
            ISNULL(T5.fact_total,0) as fact_total,
            ISNULL(T6.retiro_efectivo,0) as retiro_efectivo,
            ISNULL(T6.retiro_cheque,0) as retiro_cheque,
            ISNULL(T6.retiro_total,0) as retiro_total,
            ISNULL(T7.total_cardnet_int,0) as total_cardnet_int
        FROM diario_caja T0
        INNER JOIN caja T2 ON T0.id_caja = T2.id_caja
        LEFT JOIN usuario T3 ON (TRY_CAST(T0.usuario_apertura AS INT) = T3.id_usuario OR T0.usuario_apertura = T3.usuario)
        LEFT JOIN usuario T4 ON (TRY_CAST(T0.usuario_cierre AS INT) = T4.id_usuario OR T0.usuario_cierre = T4.usuario)
        LEFT JOIN (
            SELECT id_diario_caja,
                SUM(CASE WHEN id_tipo_movimiento = 1 THEN monto_caja ELSE 0 END) fact_efectivo,
                SUM(CASE WHEN id_tipo_movimiento = 2 THEN monto_caja ELSE 0 END) fact_tarjeta,
                SUM(CASE WHEN id_tipo_movimiento = 3 THEN monto_caja ELSE 0 END) fact_cheque,
                SUM(CASE WHEN id_tipo_movimiento = 4 THEN monto_caja ELSE 0 END) fact_transferencia,
                SUM(CASE WHEN id_tipo_movimiento IN (1, 2, 3, 4) THEN monto_caja ELSE 0 END) fact_total
            FROM diario_caja_det WHERE id_diario_caja = ? GROUP BY id_diario_caja
        ) T5 ON T0.id_diario_caja = T5.id_diario_caja
        LEFT JOIN (
            SELECT id_diario_caja, 
                SUM(CASE WHEN id_medio_retiro = 1 THEN monto ELSE 0 END) retiro_efectivo,
                SUM(CASE WHEN id_medio_retiro = 2 THEN monto ELSE 0 END) retiro_cheque,
                SUM(monto) retiro_total 
            FROM retiro_caja WHERE id_diario_caja = ? AND anulado = 'N' GROUP BY id_diario_caja
        ) T6 ON T0.id_diario_caja = T6.id_diario_caja
        LEFT JOIN (
            SELECT id_diario_caja, SUM(total) total_cardnet_int 
            FROM cardnet_log WHERE id_diario_caja = ? AND operacion = 'CIERRE' GROUP BY id_diario_caja
        ) T7 ON T0.id_diario_caja = T7.id_diario_caja
        WHERE T0.id_diario_caja = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->idDiarioCaja, $this->idDiarioCaja, $this->idDiarioCaja, $this->idDiarioCaja]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function cabecera($pdf, $d)
    {
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(72, 7, 'INFORME CIERRE DE CAJA', 0, 1, 'C');
        if ($this->esCopia) {
            $pdf->SetTextColor(255,0,0);
            $pdf->Cell(72, 6, '*** COPIA ***', 0, 1, 'C');
            $pdf->SetTextColor(0,0,0);
        }
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Ln(2);

        $pdf->Cell(32, 5, 'Arqueo:', 0, 0); $pdf->Cell(0, 5, $d['tipo_arqueo'], 0, 1);
        $pdf->Cell(32, 5, 'Caja:', 0, 0); $pdf->Cell(0, 5, $d['caja'], 0, 1);
        $pdf->Cell(32, 5, 'Apertura:', 0, 0); $pdf->Cell(0, 5, $d['fecha_apertura'], 0, 1);
        $pdf->Cell(32, 5, 'Cierre:', 0, 0); $pdf->Cell(0, 5, $d['fecha_cierre'], 0, 1);
        if (!empty($d['cajero_apertura']) && $d['cajero_apertura'] !== 'N/D' && $d['cajero_apertura'] !== $d['cajero_cierre']) {
            $pdf->Cell(32, 5, 'Cajero Apert.:', 0, 0);
            $pdf->MultiCell(0, 5, $d['cajero_apertura'], 0, 'L');
        }
        $cajeroCierre = (!empty($d['cajero_cierre']) && $d['cajero_cierre'] !== 'N/D') 
            ? $d['cajero_cierre'] 
            : ((!empty($d['cajero_apertura']) && $d['cajero_apertura'] !== 'N/D') ? $d['cajero_apertura'] : 'N/D');
        $pdf->Cell(32, 5, 'Cajero Cierre:', 0, 0);
        $pdf->MultiCell(0, 5, $cajeroCierre, 0, 'L');
        $pdf->Ln(4);
    }

    private function resumenGeneral($pdf, $d)
    {
        $declarado = ($d['monto_efectivo'] + $d['monto_tarjeta'] + $d['monto_transferencia'] + $d['monto_cheque'] + $d['monto_credito']);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(72, 6, 'RESUMEN GENERAL', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);

        $ret_efec = abs($d['retiro_efectivo']);
        $ret_cheq = abs($d['retiro_cheque']);
        $ret_total = abs($d['retiro_total']);

        // Total teórico del sistema = (Fondo Inicial + Ventas Facturadas) - Total Retiros
        $total_esperado = ($d['monto_apertura'] + $d['fact_total']) - $ret_total;

        $this->lineaMonto($pdf, 'Fondo Inicial', $d['monto_apertura']);
        $this->lineaMonto($pdf, 'Ventas Facturadas', $d['fact_total']);
        if ($ret_total > 0) {
            $this->lineaMonto($pdf, 'Retiros Realizados (-)', $ret_total);
        }
        $this->lineaMonto($pdf, 'Total Teórico Sistema', $total_esperado);
        $this->lineaMonto($pdf, 'Total Declarado', $declarado);

        $dif = $declarado - $total_esperado;
        $this->lineaMonto($pdf, 'DIFERENCIA', $dif, true);
        $pdf->Ln(4);
    }

    private function detalleMediosPago($pdf, $d)
    {
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(72, 6, 'DETALLE POR MEDIO', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);

        $ret_efec = abs($d['retiro_efectivo']);
        $ret_cheq = abs($d['retiro_cheque']);
        $esp_efec = $d['monto_apertura'] + $d['fact_efectivo'] - $ret_efec;
        $esp_cheq = $d['fact_cheque'] - $ret_cheq;

        // EFECTIVO
        $pdf->Ln(1);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(72, 5, 'EFECTIVO', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);
        $this->lineaMonto($pdf, 'Declarado', $d['monto_efectivo']);
        $this->lineaMonto($pdf, 'Fondo Inicial', $d['monto_apertura']);
        $this->lineaMonto($pdf, 'Ventas', $d['fact_efectivo']);
        if ($ret_efec > 0) {
            $this->lineaMonto($pdf, 'Retiros Efectivo (-)', $ret_efec);
        }
        $this->lineaMonto($pdf, 'Esperado en Gaveta', $esp_efec);
        if ($d['monto_efectivo'] != $esp_efec) {
            $this->lineaMonto($pdf, 'Dif. Efectivo', $d['monto_efectivo'] - $esp_efec, true);
        }

        // TARJETA
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(72, 5, 'TARJETA', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);
        $this->lineaMonto($pdf, 'Declarado', $d['monto_tarjeta']);
        $this->lineaMonto($pdf, 'Ventas', $d['fact_tarjeta']);
        if ($d['monto_tarjeta'] != $d['fact_tarjeta']) {
            $this->lineaMonto($pdf, 'Dif. Tarjeta', $d['monto_tarjeta'] - $d['fact_tarjeta'], true);
        }

        // TRANSFERENCIA
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(72, 5, 'TRANSFERENCIA', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);
        $this->lineaMonto($pdf, 'Declarado', $d['monto_transferencia']);
        $this->lineaMonto($pdf, 'Ventas / Banco', $d['fact_transferencia']);
        if ($d['monto_transferencia'] != $d['fact_transferencia']) {
            $this->lineaMonto($pdf, 'Dif. Transferencia', $d['monto_transferencia'] - $d['fact_transferencia'], true);
        }

        // CHEQUE
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(72, 5, 'CHEQUE', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);
        $this->lineaMonto($pdf, 'Declarado', $d['monto_cheque']);
        $this->lineaMonto($pdf, 'Ventas', $d['fact_cheque']);
        if ($ret_cheq > 0) {
            $this->lineaMonto($pdf, 'Retiros Cheque (-)', $ret_cheq);
        }
        $this->lineaMonto($pdf, 'Esperado Cheques', $esp_cheq);
        if ($d['monto_cheque'] != $esp_cheq) {
            $this->lineaMonto($pdf, 'Dif. Cheque', $d['monto_cheque'] - $esp_cheq, true);
        }
    }

    private function desgloseDenominaciones($pdf)
    {
        $sql = "SELECT denominacion, cantidad, total FROM cierre_denominacion WHERE id_diario_caja = ? ORDER BY denominacion DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->idDiarioCaja]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) return;

        $pdf->Ln(4);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(72, 6, 'DESGLOSE DE EFECTIVO', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);

        foreach ($rows as $r) {
            $pdf->Cell(35, 5, number_format($r['denominacion']), 0, 0);
            $pdf->Cell(15, 5, $r['cantidad'], 0, 0, 'R');
            $pdf->Cell(22, 5, number_format($r['total'],2), 0, 1, 'R');
        }
    }

    private function retirosRealizados($pdf)
    {
        $sql = "SELECT R0.id_retiro_caja, R1.descripcion as concepto, R2.descripcion as medio, 
                       R0.referencia, R0.quien_recibe, R0.comentario, (R0.monto * -1) as monto
                FROM retiro_caja R0
                JOIN concepto_retiro R1 ON R0.id_concepto_retiro = R1.id_concepto_retiro
                JOIN medio_retiro R2 ON R0.id_medio_retiro = R2.id_medio_retiro
                WHERE R0.id_diario_caja = ? AND R0.anulado = 'N'
                ORDER BY R0.id_retiro_caja ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$this->idDiarioCaja]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) return;

        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(72, 6, 'RETIROS REALIZADOS', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);

        foreach ($rows as $r) {
            $refStr = !empty($r['referencia']) ? ' (Ref: ' . $r['referencia'] . ')' : '';
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->MultiCell(72, 4.5, $r['concepto'] . ' - ' . $r['medio'] . $refStr, 0, 'L');
            $pdf->SetFont('helvetica', '', 8);

            if (!empty($r['quien_recibe'])) {
                $pdf->MultiCell(72, 4, 'Recibe: ' . $r['quien_recibe'], 0, 'L');
            }
            if (!empty($r['comentario'])) {
                $pdf->MultiCell(72, 4, 'Motivo: ' . $r['comentario'], 0, 'L');
            }

            // Consultar desglose de billetes si se guardó en retiro_denominacion
            $stmtDen = $this->pdo->prepare("SELECT denominacion, cantidad FROM retiro_denominacion WHERE id_retiro_caja = ? ORDER BY denominacion DESC");
            $stmtDen->execute([$r['id_retiro_caja']]);
            $denoms = $stmtDen->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($denoms)) {
                $denomText = [];
                foreach ($denoms as $dn) {
                    $denomText[] = $dn['cantidad'] . 'x' . number_format($dn['denominacion']);
                }
                $pdf->SetFont('helvetica', 'I', 7);
                $pdf->MultiCell(72, 3.5, 'Billetes: [' . implode(', ', $denomText) . ']', 0, 'L');
                $pdf->SetFont('helvetica', '', 8);
            }

            $pdf->Cell(45, 5, 'Monto Retirado:', 0, 0);
            $pdf->Cell(27, 5, 'RD$ ' . number_format($r['monto'], 2), 0, 1, 'R');
            $pdf->Ln(2);
        }
    }

    private function lineaMonto($pdf, $label, $monto, $esDiferencia = false)
    {
        $pdf->Cell(40, 5, $label, 0, 0);
        $pdf->Cell(32, 5, number_format($monto, 2), 0, 1, 'R');
        if ($esDiferencia && $monto != 0) {
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(72, 4.5, $monto < 0 ? 'FALTANTE' : 'SOBRANTE', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 8);
        }
    }

    private function comentario($pdf, $d)
    {
        if (!empty(trim($d['comentario'] ?? ''))) {
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(0, 6, 'COMENTARIO:', 0, 1);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->MultiCell(0, 5, $d['comentario']);
        }
    }
}