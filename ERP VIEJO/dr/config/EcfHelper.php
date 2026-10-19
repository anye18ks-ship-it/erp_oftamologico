<?php
if (!defined('APP_RUNNING')) { exit('Acceso denegado'); }

/**
 * EcfHelper - Integración y sincronización con el servicio e-CF / DGII
 * Inserta los registros en la tabla ecf_xml_invoice para que el centry externo
 * genere, firme y transmita los comprobantes electrónicos (e-CF) a la DGII.
 */
class EcfHelper {
    
    private static ?string $cachedRncEmisor = null;

    /**
     * Obtiene el RNC de la empresa emisora desde la tabla division.
     *
     * @param PDO $pdo Conexión PDO activa
     * @return string RNC de la clínica (limpio, solo dígitos)
     */
    public static function getRncEmisor(PDO $pdo): string {
        if (self::$cachedRncEmisor !== null) {
            return self::$cachedRncEmisor;
        }

        try {
            $stmt = $pdo->query("SELECT TOP 1 RNC FROM division WHERE RNC IS NOT NULL AND LTRIM(RTRIM(RNC)) <> '' ORDER BY id_division ASC");
            $rnc = $stmt->fetchColumn();
            if (!empty($rnc)) {
                $cleanRnc = preg_replace('/[^0-9]/', '', (string)$rnc);
                if (!empty($cleanRnc)) {
                    self::$cachedRncEmisor = $cleanRnc;
                    return self::$cachedRncEmisor;
                }
            }
        } catch (Throwable $e) {
            error_log("Error al consultar RNC en tabla division: " . $e->getMessage());
        }

        // Fallback por defecto si no se encuentra en division
        self::$cachedRncEmisor = '05401240196';
        return self::$cachedRncEmisor;
    }

    /**
     * Registra un comprobante fiscal en la tabla ecf_xml_invoice para procesamiento DGII.
     *
     * @param PDO $pdo Conexión PDO activa
     * @param int $docId C_DocEntry del documento (de POSOINV o POSORIN)
     * @param string $numAtCard Número de documento o secuencia interna
     * @param string $serie Serie del comprobante (E31, E32, E34, B01, etc.)
     * @param string $ncf Comprobante fiscal completo (ej: E310000000072, E340000000001)
     * @param string|null $ncfVence Fecha de vencimiento (YYYY-MM-DD o null)
     * @param string|null $rncComprador RNC o Cédula del cliente/receptor
     * @param int $extraDays Días de crédito/gracia (si > 1 => ecf_print_pages = 'Y')
     * @param string|null $docDate Fecha del documento (YYYY-MM-DD)
     * @param string|null $docType Tipo de documento (opcional/no requerido)
     * @return bool True si se insertó o ya existía, False si hubo error
     */
    public static function registrarEcfXml(
        PDO $pdo,
        int $docId,
        string $numAtCard,
        string $serie,
        string $ncf,
        ?string $ncfVence,
        ?string $rncComprador,
        int $extraDays = 0,
        ?string $docDate = null,
        ?string $docType = null
    ): bool {
        if ($docId <= 0 || empty($ncf)) {
            return false;
        }

        try {
            $rncEmisor = self::getRncEmisor($pdo);
            $ncfClean = strtoupper(trim($ncf));
            $serieClean = strtoupper(trim($serie));
            $numAtCardClean = strtoupper(trim($numAtCard));

            // Limpieza de RNC comprador (solo números)
            $rncCompClean = preg_replace('/[^0-9]/', '', (string)$rncComprador);
            if (empty($rncCompClean)) {
                $rncCompClean = '00000000000'; // Default Consumidor Final
            }

            // ecf_type: solo numeración sin la letra (ej: E32 -> 32, E31 -> 31, E34 -> 34, E33 -> 33)
            $ecfTypeNum = preg_replace('/[^0-9]/', '', (string)$serieClean);
            if ($ecfTypeNum === '' && !empty($ncfClean)) {
                if (preg_match('/^[A-Z]*([0-9]{2})/i', $ncfClean, $matches)) {
                    $ecfTypeNum = $matches[1];
                }
            }

            // NumAtCard: F para Facturas (ej: F0000001), N para Notas de Crédito (ej: N0000001), D para Notas de Débito (ej: D0000001)
            if (empty($numAtCardClean)) {
                try {
                    if ($ecfTypeNum === '34' || $docType === 'CREDIT_NOTE' || stripos($serieClean, '34') !== false) {
                        $stmtNC = $pdo->prepare("SELECT TOP 1 NumAtCard FROM POSORIN WHERE C_DocEntry = ?");
                        $stmtNC->execute([$docId]);
                        $found = $stmtNC->fetchColumn();
                        if (!empty($found)) {
                            $numAtCardClean = strtoupper(trim($found));
                        }
                    } elseif ($ecfTypeNum === '33' || $docType === 'DEBIT_NOTE' || stripos($serieClean, '33') !== false) {
                        $stmtND = $pdo->prepare("SELECT TOP 1 NumAtCard FROM POSODIN WHERE C_DocEntry = ?");
                        $stmtND->execute([$docId]);
                        $found = $stmtND->fetchColumn();
                        if (!empty($found)) {
                            $numAtCardClean = strtoupper(trim($found));
                        }
                    } else {
                        $stmtFac = $pdo->prepare("SELECT TOP 1 NumAtCard FROM POSOINV WHERE C_DocEntry = ?");
                        $stmtFac->execute([$docId]);
                        $found = $stmtFac->fetchColumn();
                        if (!empty($found)) {
                            $numAtCardClean = strtoupper(trim($found));
                        }
                    }
                } catch (Throwable $e) {}
            }

            // C_Posted: 'N' (Nuevo / No procesado. El daemon Centry lo valida, firma y actualiza a 'F')
            $cPosted = 'N';

            $xmlFileName = strtoupper($rncEmisor . $ncfClean); // e.g. 05401240196E310000000072
            $printPages = ($extraDays > 1) ? 'Y' : 'N';
            $fechaEmision = !empty($docDate) ? substr($docDate, 0, 10) : date('Y-m-d');
            
            $fechaVence = null;
            // Para comprobantes tipo 32 (Consumo) y 34 (Nota de Crédito), ecf_vence es NULL
            if ($ecfTypeNum !== '32' && $ecfTypeNum !== '34' && !in_array($serieClean, ['E32', 'E34', 'B02', 'B04', '32', '34'])) {
                if (!empty($ncfVence) && $ncfVence !== '0000-00-00') {
                    $fechaVence = substr($ncfVence, 0, 10);
                }
            }

            // Evitar duplicados para el mismo doc_id y ecf
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM ecf_xml_invoice WHERE doc_id = ? AND UPPER(ecf) = ?");
            $checkStmt->execute([$docId, $ncfClean]);
            if ((int)$checkStmt->fetchColumn() > 0) {
                return true;
            }

            $sql = "INSERT INTO ecf_xml_invoice (
                        doc_id, NumAtCard, ecf_type, ecf, xml_file_name,
                        ecf_print_pages, ecf_vence, ecf_rnc_emisor,
                        ecf_rnc_comprador, ecf_invoice_date, date_process,
                        datetime_process, C_Posted, active
                    ) VALUES (
                        ?, ?, ?, ?, ?,
                        ?, ?, ?,
                        ?, ?, CAST(GETDATE() AS DATE),
                        GETDATE(), ?, 'Y'
                    )";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $docId,
                substr($numAtCardClean, 0, 20),
                substr($ecfTypeNum, 0, 20),
                substr($ncfClean, 0, 50),
                substr($xmlFileName, 0, 50),
                $printPages,
                $fechaVence,
                $rncEmisor,
                substr($rncCompClean, 0, 50),
                $fechaEmision,
                $cPosted
            ]);

            return true;
        } catch (Throwable $e) {
            error_log("Error al registrar ecf_xml_invoice para doc_id $docId ($ncf): " . $e->getMessage());
            return false;
        }
    }
}
?>
