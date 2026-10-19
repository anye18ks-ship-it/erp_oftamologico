<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

class Helper {
    
    // --- FUNCIONES DEL SISTEMA ---
    
    public static function setMensaje(string $tipo, string $texto): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['mensaje'] = ['tipo' => $tipo, 'texto' => $texto];
    }

    public static function getMensaje(): string {
        if (isset($_SESSION['mensaje'])) {
            $msg = $_SESSION['mensaje'];
            unset($_SESSION['mensaje']);
            $html = "<div class='alert alert-{$msg['tipo']} alert-dismissible fade show' role='alert'>";
            $html .= htmlspecialchars($msg['texto'] ?? '');
            $html .= "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
            return $html;
        }
        return "";
    }

    public static function md5(string $input): string {
        return md5($input);
    }

    // --- FUNCIONES DE NEGOCIO ---

    public static function validarCedula(string $cedula): bool {
        $cedula = str_replace('-', '', trim($cedula));
        if (strlen($cedula) !== 11 || !is_numeric($cedula)) return false;
        
        $digitos = str_split($cedula);
        $suma = 0;
        $multiplicador = [1, 2, 1, 2, 1, 2, 1, 2, 1, 2];
        
        for ($i = 0; $i < 10; $i++) {
            $producto = (int)$digitos[$i] * $multiplicador[$i];
            if ($producto >= 10) {
                $producto = (int)floor($producto / 10) + ($producto % 10);
            }
            $suma += $producto;
        }
        
        $digitoVerificador = (10 - ($suma % 10)) % 10;
        return $digitoVerificador == (int)$digitos[10];
    }

    public static function validarRNC(string $rnc): bool {
        $rnc = str_replace('-', '', trim($rnc));
        if (strlen($rnc) !== 9 || !is_numeric($rnc)) return false;
        
        $digitos = str_split($rnc);
        $suma = 0;
        $multiplicador = [9, 8, 7, 6, 5, 4, 3, 2];
        
        for ($i = 0; $i < 8; $i++) {
            $suma += (int)$digitos[$i] * $multiplicador[$i];
        }
        
        $digitoVerificador = (11 - ($suma % 11)) % 11;
        if ($digitoVerificador == 10) $digitoVerificador = 0;
        if ($digitoVerificador == 11) $digitoVerificador = 1;
        
        return $digitoVerificador == (int)$digitos[8];
    }

    /**
     * Limpia RNC / Cédula eliminando guiones y caracteres no alfanuméricos para persistencia en BD
     * (En ninguna tabla de base de datos se debe guardar con guiones).
     */
    public static function limpiarNit(?string $nit): string {
        $nitRaw = trim((string)($nit ?? ''));
        if ($nitRaw === '') return '';
        // Eliminar guiones y caracteres de formateo
        return preg_replace('/[^0-9A-Za-z]/', '', $nitRaw);
    }

    /**
     * Alias de limpiarNit para asegurar que al formatear para BD siempre vaya sin guiones
     */
    public static function formatearNit(?string $nit, ?string $tipoNegocio = null): string {
        return self::limpiarNit($nit);
    }

    /**
     * Formatea RNC / Cédula con guiones únicamente para presentación visual / reportes
     */
    public static function formatearNitParaVista(?string $nit, ?string $tipoNegocio = null): string {
        $cleanDigits = self::limpiarNit($nit);
        if ($cleanDigits === '') return '';

        // Cédula: 11 dígitos -> XXX-XXXXXXX-X
        if (strlen($cleanDigits) === 11 && ($tipoNegocio === '2' || empty($tipoNegocio))) {
            return substr($cleanDigits, 0, 3) . '-' . substr($cleanDigits, 3, 7) . '-' . substr($cleanDigits, 10, 1);
        }

        // RNC: 9 dígitos -> X-XX-XXXXX-X o XXX-XXXXX-X
        if (strlen($cleanDigits) === 9 && ($tipoNegocio === '1' || empty($tipoNegocio))) {
            return substr($cleanDigits, 0, 1) . '-' . substr($cleanDigits, 1, 2) . '-' . substr($cleanDigits, 3, 5) . '-' . substr($cleanDigits, 8, 1);
        }

        return $cleanDigits;
    }

    /**
     * Determina el código de tipo de negocio (1=RNC, 2=Cédula, 3=Pasaporte/Otros)
     */
    public static function determinarTipoNegocio(?string $nit, ?string $tipoNegocio = null): string {
        if (!empty($tipoNegocio) && in_array(trim((string)$tipoNegocio), ['1', '2', '3'], true)) {
            return trim((string)$tipoNegocio);
        }

        $cleanDigits = preg_replace('/[^0-9]/', '', (string)($nit ?? ''));
        if (strlen($cleanDigits) === 11) {
            return '2'; // Cédula
        } elseif (strlen($cleanDigits) === 9) {
            return '1'; // RNC
        }

        return '3'; // Pasaporte / Otros
    }

    public static function getInvoiceDate(): string {
        try {
            if (function_exists('getSelectFirstRow')) {
                $row = getSelectFirstRow("SELECT CASE WHEN (getdate() > CONVERT(datetime, U_valpar, 103) OR 'N' = 'N') THEN getdate() ELSE CONVERT(datetime, U_valpar, 103) END as fecha_facturar FROM [@parametros] WHERE code = 600");
                if (!empty($row) && isset($row['fecha_facturar'])) {
                    return date('Y-m-d', strtotime((string)$row['fecha_facturar']));
                }
            }
        } catch (Exception $e) {
            error_log('Error getInvoiceDate: ' . $e->getMessage());
        }
        return date('Y-m-d');
    }

    // --- FUNCIÓN PARA LLENAR COMBOS DESDE BD ---
    public static function llenarCombo(PDO $pdo, string $sql, string $valueCol, string $textCol, string $nameAttr, mixed $selectedValue = null): string {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $html = "<select name='{$nameAttr}' id='{$nameAttr}' class='form-select form-select-sm'>";
        $html .= "<option value=''>-- Seleccionar --</option>";
        
        $foundSelected = false;
        if ($rows) {
            foreach ($rows as $row) {
                $val = htmlspecialchars((string)($row[$valueCol] ?? ''));
                $text = htmlspecialchars((string)($row[$textCol] ?? ''));
                $isSelected = ($selectedValue !== null && (string)$val === (string)$selectedValue);
                if ($isSelected) $foundSelected = true;
                $selected = $isSelected ? 'selected' : '';
                $html .= "<option value='{$val}' {$selected}>{$text}</option>";
            }
        }
        
        // Compatibilidad histórica: si el valor seleccionado no está en el catálogo actual (ej: B02/B01 antiguos), preservarlo
        if (!$foundSelected && $selectedValue !== null && trim((string)$selectedValue) !== '') {
            $valEsc = htmlspecialchars((string)$selectedValue);
            $html .= "<option value='{$valEsc}' selected>{$valEsc} (Histórico)</option>";
        }
        
        $html .= "</select>";
        return $html;
    }

    // --- FUNCIÓN PARA MOVIMIENTOS DE INVENTARIO ---
    public static function ajustarInventario(PDO $pdo, string $ItemCode, string $WhsCode, float $cantidad, string $tipo): bool {
        return true; 
    }

    public static function toDouble(mixed $pValue): float {
        $clean = str_replace(',', '', (string)($pValue ?? 0));
        return (float)($clean !== '' ? $clean : 0);
    }    
}
?>