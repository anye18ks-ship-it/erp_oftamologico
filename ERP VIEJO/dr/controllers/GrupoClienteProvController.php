<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class GrupoClienteProvController {

    public function index() {
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            $sql = "SELECT GroupCode, GroupName, GroupType FROM posOCRG ORDER BY GroupCode";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            die("Error al consultar grupos: " . $e->getMessage());
        }
        
        include __DIR__ . '/../views/SocioNegocio/grupos_cliente_prov.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();

            $groupName = trim($_POST['GroupName'] ?? '');
            $groupType = strtoupper(trim($_POST['GroupType'] ?? ''));
            $codigo_original = $_POST['codigo_original'] ?? '';

            try {
                if (empty($codigo_original)) {
                    // INSERTAR NUEVO
                    $sql = "INSERT INTO posOCRG (GroupName, GroupType) VALUES (:groupName, :groupType)";
                    $params = [':groupName' => $groupName, ':groupType' => $groupType];
                } else {
                    // ACTUALIZAR
                    $sql = "UPDATE posOCRG SET GroupName = :groupName, GroupType = :groupType WHERE GroupCode = :groupCode";
                    $params = [':groupCode' => $codigo_original, ':groupName' => $groupName, ':groupType' => $groupType];
                }

                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute($params)) {
                    $msg = empty($codigo_original) ? 'Grupo creado exitosamente' : 'Grupo actualizado';
                    Helper::setMensaje('success', $msg);
                } else {
                    Helper::setMensaje('danger', 'Error al guardar el grupo.');
                }
            } catch (PDOException $e) {
                Helper::setMensaje('danger', 'Error BD: ' . $e->getMessage());
            }
            
            header("Location: index.php?page=grupos_cliente_prov");
            exit();
        }
    }
}
?>