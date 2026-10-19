<?php
require_once __DIR__ . '/../config/Database.php';

class AuthController {
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = new Database();
            $db = $database->getConnection();

            $usuario = $_POST['usuario'];
            $clave_md5 = md5($_POST['clave']); 

            $sql = "SELECT id_usuario, usuario, nombre, activo FROM usuario WHERE usuario = :u AND clave = :c AND activo = 'Y'";
            $stmt = $db->prepare($sql);
            $stmt->execute([':u' => $usuario, ':c' => $clave_md5]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $_SESSION['logueado'] = true;
                $_SESSION['usuario_id'] = $row['id_usuario'];
                $_SESSION['usuario'] = $row['usuario'];
                $_SESSION['nombre'] = $row['nombre'];
                
                $_SESSION['caja_id'] = $_POST['caja_id'];
                
                $stmtCaja = $db->prepare("SELECT alias FROM caja WHERE id_caja = ?");
                $stmtCaja->execute([$_POST['caja_id']]);
                $_SESSION['caja_alias'] = $stmtCaja->fetchColumn();

                header("Location: index.php?page=dashboard");
            } else {
                header("Location: index.php?page=login&error=1");
            }
        }
    }

    // MÉTODO FALTANTE
    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: index.php?page=login");
        exit();
    }
}
?>