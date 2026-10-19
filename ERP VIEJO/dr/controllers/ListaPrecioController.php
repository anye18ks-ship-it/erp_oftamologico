<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class ListaPrecioController {

    public function index() {
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            $sql = "SELECT ListNum, ListName, BASE_NUM, Factor, IsGrossPrc, ValidFor, CreateDate 
                    FROM posOPLN ORDER BY ListNum ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $listas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            die("Error al consultar listas de precios: " . $e->getMessage());
        }
        
        include __DIR__ . '/../views/Articulos/listas_precio.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();

            $id = isset($_POST['ListNum']) && $_POST['ListNum'] !== '' ? (int)$_POST['ListNum'] : null;
            $nombre = trim($_POST['ListName'] ?? '');
            
            $baseNum = (isset($_POST['BASE_NUM']) && $_POST['BASE_NUM'] !== '') ? (int)$_POST['BASE_NUM'] : null;
            $rawFactor = str_replace(',', '', $_POST['Factor'] ?? '');
            $factor = ($rawFactor !== '' && is_numeric($rawFactor)) ? (float)$rawFactor : null;

            $checkToYN = function($field) {
                return (isset($_POST[$field]) && $_POST[$field] !== 'N') ? 'Y' : 'N';
            };

            if (empty($nombre)) {
                Helper::setMensaje('danger', 'El nombre de la lista de precios es obligatorio.');
                header("Location: index.php?page=listas_precio");
                exit();
            }

            $params = [
                ':nam'   => $nombre,
                ':base'  => $baseNum,
                ':fac'   => $factor,
                ':gross' => $checkToYN('IsGrossPrc'),
                ':valid' => $checkToYN('ValidFor')
            ];

            try {
                if (empty($id)) {
                    // INSERTAR NUEVA LISTA
                    $params[':date'] = date('Y-m-d H:i:s');
                    $sql = "INSERT INTO posOPLN (ListName, BASE_NUM, Factor, IsGrossPrc, ValidFor, CreateDate) 
                            VALUES (:nam, :base, :fac, :gross, :valid, :date)";
                } else {
                    // ACTUALIZAR LISTA
                    $sql = "UPDATE posOPLN SET ListName=:nam, BASE_NUM=:base, Factor=:fac, 
                            IsGrossPrc=:gross, ValidFor=:valid 
                            WHERE ListNum=:id";
                    $params[':id'] = $id;
                }

                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute($params)) {
                    $msg = empty($id) ? "Lista de precios '{$nombre}' creada exitosamente." : "Lista de precios '{$nombre}' actualizada exitosamente.";
                    Helper::setMensaje('success', $msg);
                } else {
                    Helper::setMensaje('danger', 'Error al guardar la lista de precios.');
                }
            } catch (PDOException $e) {
                Helper::setMensaje('danger', 'Error de Base de Datos: ' . $e->getMessage());
            }
            
            header("Location: index.php?page=listas_precio");
            exit();
        }
    }
}
?>
