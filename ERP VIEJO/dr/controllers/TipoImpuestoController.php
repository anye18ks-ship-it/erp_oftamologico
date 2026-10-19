<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class TipoImpuestoController {

    public function index() {
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            $sql = "SELECT Code, Name, Rate FROM posOSTC ORDER BY Code ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $impuestos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            die("Error al consultar impuestos: " . $e->getMessage());
        }
        
        include __DIR__ . '/../views/Articulos/tipos_impuesto.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();

            $codigo = strtoupper(trim($_POST['Code'] ?? ''));
            $nombre = trim($_POST['Name'] ?? '');
            
            $rawRate = str_replace(',', '', $_POST['Rate'] ?? '0');
            $tasa = is_numeric($rawRate) ? (float)$rawRate : 0.00;
            
            $codigo_original = trim($_POST['codigo_original'] ?? '');

            if (empty($codigo) || empty($nombre)) {
                Helper::setMensaje('danger', 'El código y la descripción del impuesto son obligatorios.');
                header("Location: index.php?page=tipos_impuesto");
                exit();
            }

            try {
                if (empty($codigo_original)) {
                    // INSERTAR NUEVO
                    $sql = "INSERT INTO posOSTC (Code, Name, Rate) VALUES (:cod, :nom, :rate)";
                    $params = [':cod' => $codigo, ':nom' => $nombre, ':rate' => $tasa];
                } else {
                    // ACTUALIZAR
                    $sql = "UPDATE posOSTC SET Name = :nom, Rate = :rate WHERE Code = :cod";
                    $params = [':cod' => $codigo_original, ':nom' => $nombre, ':rate' => $tasa];
                }

                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute($params)) {
                    $targetCode = empty($codigo_original) ? $codigo : $codigo_original;
                    $msg = empty($codigo_original) ? "Tipo de impuesto '{$targetCode}' creado exitosamente." : "Tipo de impuesto '{$targetCode}' actualizado exitosamente.";
                    Helper::setMensaje('success', $msg);
                } else {
                    Helper::setMensaje('danger', 'Error al procesar el tipo de impuesto.');
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 2627 || strpos($e->getMessage(), 'PRIMARY KEY') !== false) { 
                    Helper::setMensaje('warning', "El código de impuesto '{$codigo}' ya existe en el sistema.");
                } else {
                    Helper::setMensaje('danger', 'Error de Base de Datos: ' . $e->getMessage());
                }
            }
            
            header("Location: index.php?page=tipos_impuesto");
            exit();
        }
    }
}
?>
