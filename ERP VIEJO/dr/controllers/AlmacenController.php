<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class AlmacenController {

    public function index() {
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            $sql = "SELECT WhsCode, WhsName FROM posOWHS ORDER BY WhsCode ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $almacenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            die("Error al consultar almacenes: " . $e->getMessage());
        }
        
        include __DIR__ . '/../views/Articulos/almacenes.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();

            $codigo = strtoupper(trim($_POST['WhsCode'] ?? ''));
            $nombre = trim($_POST['WhsName'] ?? '');
            $codigo_original = trim($_POST['codigo_original'] ?? '');

            if (empty($codigo) || empty($nombre)) {
                Helper::setMensaje('danger', 'El código y el nombre del almacén son obligatorios.');
                header("Location: index.php?page=almacenes");
                exit();
            }

            try {
                if (empty($codigo_original)) {
                    // INSERTAR NUEVO
                    $sql = "INSERT INTO posOWHS (WhsCode, WhsName) VALUES (:cod, :nom)";
                    $params = [':cod' => $codigo, ':nom' => $nombre];
                } else {
                    // ACTUALIZAR (Mantenemos el código original)
                    $sql = "UPDATE posOWHS SET WhsName = :nom WHERE WhsCode = :cod";
                    $params = [':cod' => $codigo_original, ':nom' => $nombre];
                }

                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute($params)) {
                    $targetCode = empty($codigo_original) ? $codigo : $codigo_original;
                    $msg = empty($codigo_original) ? "Almacén '{$targetCode}' creado exitosamente." : "Almacén '{$targetCode}' actualizado exitosamente.";
                    Helper::setMensaje('success', $msg);
                } else {
                    Helper::setMensaje('danger', 'Error al procesar la solicitud del almacén.');
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 2627 || strpos($e->getMessage(), 'PRIMARY KEY') !== false) { 
                    Helper::setMensaje('warning', "El código de almacén '{$codigo}' ya existe en el sistema.");
                } else {
                    Helper::setMensaje('danger', 'Error de Base de Datos: ' . $e->getMessage());
                }
            }
            
            header("Location: index.php?page=almacenes");
            exit();
        }
    }
}
?>
