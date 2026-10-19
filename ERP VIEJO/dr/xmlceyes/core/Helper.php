<?php

class Helper {

    /**
     * Ejecuta un comando SQL (SELECT, INSERT, UPDATE, DELETE).
     * Devuelve un array para SELECT, o el número de filas afectadas para INSERT/UPDATE/DELETE.
     */
    public static function ejecutarSQL($pdo, $sql, $params = []) {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            // Si es un SELECT, devolver todas las filas
            if (stripos(trim($sql), 'SELECT') === 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            // Si es INSERT, UPDATE, DELETE, devolver las filas afectadas
            return $stmt->rowCount();
            
        } catch (PDOException $e) {
            // Aquí podrías llamar a un método de log si quieres
            error_log("Error SQL: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Genera el HTML completo de un <select> de Bootstrap 5 a partir de una consulta SQL.
     */
    public static function llenarCombo($pdo, $sql, $valueCol, $textCol, $nameAttr, $selectedValue = null, $idAttr = null, $classExtra = "") {
        $rows = self::ejecutarSQL($pdo, $sql);
        $id = $idAttr ?? $nameAttr;
        
        $html = "<select name='{$nameAttr}' id='{$id}' class='form-select {$classExtra}'>";
        $html .= "<option value=''>-- Seleccione --</option>";
        
        if ($rows) {
            foreach ($rows as $row) {
                $val = htmlspecialchars($row[$valueCol]);
                $text = htmlspecialchars($row[$textCol]);
                $selected = ($val == $selectedValue) ? 'selected' : '';
                $html .= "<option value='{$val}' {$selected}>{$text}</option>";
            }
        }
        
        $html .= "</select>";
        return $html;
    }

    /**
     * Genera una tabla HTML de Bootstrap 5 automáticamente a partir de una consulta SQL.
     */
    public static function generarTablaSQL($pdo, $sql, $params = [], $tableId = "tablaDatos") {
        $rows = self::ejecutarSQL($pdo, $sql, $params);
        
        if (!$rows) {
            return "<div class='alert alert-info'>No se encontraron registros.</div>";
        }

        $html = "<table id='{$tableId}' class='table table-bordered table-striped table-hover align-middle'>";
        $html .= "<thead class='table-dark'><tr>";
        
        // Crear los encabezados usando los nombres de las columnas de la BD
        $columnas = array_keys($rows[0]);
        foreach ($columnas as $col) {
            $html .= "<th>" . htmlspecialchars($col) . "</th>";
        }
        $html .= "</tr></thead><tbody>";
        
        // Llenar los datos
        foreach ($rows as $row) {
            $html .= "<tr>";
            foreach ($row as $cell) {
                $html .= "<td>" . htmlspecialchars($cell) . "</td>";
            }
            $html .= "</tr>";
        }
        
        $html .= "</tbody></table>";
        return $html;
    }

    /**
     * Equivalente a tu MessageBox de Java, pero para Web.
     * Guarda un mensaje en sesión que se mostrará en la vista (con Bootstrap Alerts).
     */
    public static function setMensaje($tipo, $texto) {
        if (!isset($_SESSION)) session_start();
        $_SESSION['mensaje'] = [
            'tipo' => $tipo, // 'success', 'danger', 'warning', 'info'
            'texto' => $texto
        ];
    }

    public static function getMensaje() {
        if (isset($_SESSION['mensaje'])) {
            $msg = $_SESSION['mensaje'];
            unset($_SESSION['mensaje']);
            
            $html = "<div class='alert alert-{$msg['tipo']} alert-dismissible fade show' role='alert'>";
            $html .= htmlspecialchars($msg['texto']);
            $html .= "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
            $html .= "</div>";
            return $html;
        }
        return "";
    }

    // --- LÓGICA DE NEGOCIO REUTILIZABLE (Traducida de tu Java) ---

    public static function validarCedula($pCedula) {
        if ($pCedula == null || strlen($pCedula) != 11 || !preg_match('/^\d+$/', $pCedula)) {
            return false;
        }
        $suma = 0;
        $peso = [1, 2];
        for ($i = 0; $i < 10; $i++) {
            $digito = (int)$pCedula[$i];
            $producto = $digito * $peso[$i % 2];
            $suma += ($producto > 9) ? $producto - 9 : $producto;
        }
        $digitoVerificador = (int)$pCedula[10];
        $modulo = (10 - ($suma % 10)) % 10;
        return $modulo == $digitoVerificador;
    }

    public static function validarRNC($pRNC) {
        if ($pRNC == null || strlen($pRNC) != 9 || !preg_match('/^\d+$/', $pRNC)) {
            return false;
        }
        $pesos = [7, 9, 8, 6, 5, 4, 3, 2];
        $suma = 0;
        for ($i = 0; $i < 8; $i++) {
            $digito = (int)$pRNC[$i];
            $suma += $digito * $pesos[$i];
        }
        $modulo = $suma % 11;
        $digitoVerificador = ($modulo == 0) ? 0 : 11 - $modulo;
        return $digitoVerificador == (int)$pRNC[8];
    }

    public static function md5($pInput) {
        return md5($pInput);
    }
}
?>