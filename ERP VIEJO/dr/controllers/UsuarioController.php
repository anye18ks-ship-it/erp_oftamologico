<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class UsuarioController {

    public function index() {
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            $sql = "SELECT id_usuario, usuario, nombre, sap_whscode, activo, id_serie FROM usuario ORDER BY nombre";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            // Si hay un error en la base de datos, lo mostramos en pantalla directamente
            die("ERROR EN LA CONSULTA DE USUARIOS: " . $e->getMessage());
        }
        
        include __DIR__ . '/../views/Registros/usuarios.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();

            $id_usuario = $_POST['id_usuario'] ?? null;
            $usuario = $_POST['usuario'];
            $clave_raw = $_POST['clave'];
            $nombre = $_POST['nombre'];
            $sap_whscode = $_POST['sap_whscode'];
            $activo = isset($_POST['activo']) ? 'Y' : 'N';
            $id_serie = $_POST['id_serie'];

            try {
                if (empty($id_usuario)) {
                    if (empty($clave_raw)) {
                        Helper::setMensaje('danger', 'La clave es obligatoria para un nuevo usuario.');
                        header("Location: index.php?page=usuarios");
                        exit();
                    }
                    $sql = "INSERT INTO usuario (usuario, clave, nombre, sap_whscode, activo, id_serie,id_departamento,codigo_empleado) 
                            VALUES (:u, :c, :n, :s, :a, :is, :id, :ie)";
                    $params = [
                        ':u' => $usuario, ':c' => Helper::md5($clave_raw), ':n' => $nombre, 
                        ':s' => $sap_whscode, ':a' => $activo, ':is' => $id_serie, ':id' => 0, ':ie' => 0
                    ];
                } else {
                    if (!empty($clave_raw)) {
                        $sql = "UPDATE usuario SET usuario=:u, clave=:c, nombre=:n, sap_whscode=:s, activo=:a, id_serie=:is 
                                WHERE id_usuario=:id";
                        $params = [
                            ':u' => $usuario, ':c' => Helper::md5($clave_raw), ':n' => $nombre, 
                            ':s' => $sap_whscode, ':a' => $activo, ':is' => $id_serie, ':id' => $id_usuario
                        ];
                    } else {
                        $sql = "UPDATE usuario SET usuario=:u, nombre=:n, sap_whscode=:s, activo=:a, id_serie=:is 
                                WHERE id_usuario=:id";
                        $params = [
                            ':u' => $usuario, ':n' => $nombre, 
                            ':s' => $sap_whscode, ':a' => $activo, ':is' => $id_serie, ':id' => $id_usuario
                        ];
                    }
                }

                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute($params)) {
                    $msgType = empty($id_usuario) ? 'creado' : 'modificado';
                    Helper::setMensaje('success', "Usuario $msgType exitosamente.");
                } else {
                    Helper::setMensaje('danger', 'Error al guardar el usuario.');
                }
            } catch (PDOException $e) {
                Helper::setMensaje('danger', 'Error BD: ' . $e->getMessage());
            }
            
            header("Location: index.php?page=usuarios");
            exit();
        }
    }
}
?>