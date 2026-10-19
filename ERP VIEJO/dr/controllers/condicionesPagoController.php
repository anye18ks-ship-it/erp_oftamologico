<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class CondicionesPagoController {

    public function index() {
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            $sql = "SELECT GroupNum, PymntGroup, ExtraDays FROM posOCTG ORDER BY GroupNum";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $condiciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            die("Error al consultar condiciones de pago: " . $e->getMessage());
        }
        
        include __DIR__ . '/../views/SocioNegocio/condiciones_pago.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();

            $pymntGroup = trim($_POST['PymntGroup'] ?? '');
            $extraDays = (int)($_POST['ExtraDays'] ?? 0);
            $codigo_original = $_POST['codigo_original'] ?? '';

            try {
                if (empty($codigo_original)) {
                    // INSERTAR NUEVO (GroupNum ya es automático, NO lo enviamos)
                    $sql = "INSERT INTO posOCTG (PymntGroup, ExtraDays) VALUES (:pymntGroup, :extraDays)";
                    $params = [':pymntGroup' => $pymntGroup, ':extraDays' => $extraDays];
                } else {
                    // ACTUALIZAR
                    $sql = "UPDATE posOCTG SET PymntGroup = :pymntGroup, ExtraDays = :extraDays WHERE GroupNum = :groupNum";
                    $params = [':groupNum' => $codigo_original, ':pymntGroup' => $pymntGroup, ':extraDays' => $extraDays];
                }

                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute($params)) {
                    $msg = empty($codigo_original) ? 'Condición de pago creada exitosamente' : 'Condición de pago actualizada';
                    Helper::setMensaje('success', $msg);
                } else {
                    Helper::setMensaje('danger', 'Error al guardar la condición de pago.');
                }
            } catch (PDOException $e) {
                Helper::setMensaje('danger', 'Error BD: ' . $e->getMessage());
            }
            
            header("Location: index.php?page=condiciones_pago");
            exit();
        }
    }
}
?>