<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class NcfController {

    public function index() {
        $database = new Database();
        $pdo = $database->getConnection();

        // Obtener todas las series
        $sqlSeries = "SELECT * FROM serie_ncf ORDER BY serie";
        $stmtSeries = $pdo->prepare($sqlSeries);
        $stmtSeries->execute();
        $series = $stmtSeries->fetchAll(PDO::FETCH_ASSOC);

        // Si se seleccionó una serie específica, obtener sus cargas
        $serieSeleccionada = isset($_GET['serie']) ? strtoupper(trim($_GET['serie'])) : null;
        $cargas = [];

        if ($serieSeleccionada) {
            $sqlCargas = "SELECT * FROM carga_ncf WHERE UPPER(serie) = :serie ORDER BY id_carga DESC";
            $stmtCargas = $pdo->prepare($sqlCargas);
            $stmtCargas->execute([':serie' => $serieSeleccionada]);
            $cargas = $stmtCargas->fetchAll(PDO::FETCH_ASSOC);
        }

        include __DIR__ . '/../views/Registros/ncf.php';
    }

    public function guardarSerie() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();

            $serieOriginal = strtoupper(trim($_POST['serie_original'] ?? ''));
            $serie = strtoupper(trim($_POST['serie'] ?? ''));
            $descripcion = trim($_POST['descripcion'] ?? '');
            $longitud = intval($_POST['longitud'] ?? 10);
            if ($longitud <= 0) $longitud = 10;
            $alerta = intval($_POST['alerta'] ?? 100);
            $activo = isset($_POST['activo']) ? 'Y' : 'N';

            try {
                if (empty($serie)) {
                    throw new Exception("El código de la serie es obligatorio.");
                }

                if (empty($serieOriginal)) {
                    $sql = "INSERT INTO serie_ncf (serie, descripcion, longitud, alerta, activo, tipo_ncf) 
                            VALUES (:s, :d, :l, :a, :ac, 0)";
                    $params = [':s' => $serie, ':d' => $descripcion, ':l' => $longitud, ':a' => $alerta, ':ac' => $activo];
                } else {
                    $sql = "UPDATE serie_ncf SET descripcion=:d, longitud=:l, alerta=:a, activo=:ac WHERE serie=:s";
                    $params = [':s' => $serieOriginal, ':d' => $descripcion, ':l' => $longitud, ':a' => $alerta, ':ac' => $activo];
                }
                $pdo->prepare($sql)->execute($params);
                Helper::setMensaje('success', "Serie electrónica {$serie} guardada correctamente.");
            } catch (Throwable $e) {
                Helper::setMensaje('danger', 'Error al guardar serie: ' . $e->getMessage());
            }
            header("Location: index.php?page=ncf&serie=" . urlencode(!empty($serieOriginal) ? $serieOriginal : $serie));
            exit();
        }
    }

    public function guardarRango() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();

            $serie = strtoupper(trim($_POST['serie'] ?? ''));
            $inicial = intval($_POST['inicial'] ?? 1);
            $final = intval($_POST['final'] ?? 1);
            $vencimiento = !empty($_POST['vencimiento']) ? trim($_POST['vencimiento']) : NULL;
            $usuario = $_SESSION['nombre'] ?? $_SESSION['usuario'] ?? 'Sistema';

            try {
                if (empty($serie)) {
                    throw new Exception("Debe especificar una serie para el rango.");
                }
                if ($final < $inicial) {
                    throw new Exception("La secuencia final ({$final}) no puede ser menor a la inicial ({$inicial}).");
                }

                $contadorInicial = max(0, $inicial - 1);
                $sql = "INSERT INTO carga_ncf (fecha_carga, serie, vencimiento, inicial, contador, final, usuario) 
                        VALUES (GETDATE(), ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$serie, $vencimiento, $inicial, $contadorInicial, $final, $usuario]);

                Helper::setMensaje('success', "Nuevo rango electrónico autorizado para {$serie} ({$inicial} al {$final}).");
            } catch (Throwable $e) {
                Helper::setMensaje('danger', 'Error SQL al autorizar rango: ' . $e->getMessage());
            }
            header("Location: index.php?page=ncf&serie=" . urlencode($serie));
            exit();
        }
    }
}
?>