<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class GrupoArticuloController {

    public function index() {
        try {
            $database = new Database();
            $pdo = $database->getConnection();
            
            $sql = "SELECT ItmsGrpCod, ItmsGrpNam, BalInvntAc, SaleCostAc, TransferAc, RevenuesAc, 
                           VarianceAc, DecreasAc, IncreasAc, ReturnAc, ExpensesAc, 
                           EURevenuAc, EUExpensAc, FrRevenuAc, FrExpensAc, ExmptIncom, CycleCode 
                    FROM posOITB ORDER BY ItmsGrpCod ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            die("Error al consultar grupos de artículos: " . $e->getMessage());
        }
        
        include __DIR__ . '/../views/Articulos/grupos_articulo.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();

            $id = isset($_POST['ItmsGrpCod']) && $_POST['ItmsGrpCod'] !== '' ? (int)$_POST['ItmsGrpCod'] : null;
            $nombre = trim($_POST['ItmsGrpNam'] ?? '');
            
            if (empty($nombre)) {
                Helper::setMensaje('danger', 'El nombre del grupo de artículos es obligatorio.');
                header("Location: index.php?page=grupos_articulo");
                exit();
            }

            $sanAcc = function($field) {
                $val = trim($_POST[$field] ?? '');
                return ($val !== '') ? substr($val, 0, 15) : null;
            };

            $cycleCode = (isset($_POST['CycleCode']) && $_POST['CycleCode'] !== '') ? (int)$_POST['CycleCode'] : null;

            $params = [
                ':nam'   => $nombre,
                ':bal'   => $sanAcc('BalInvntAc'),
                ':sale'  => $sanAcc('SaleCostAc'),
                ':trans' => $sanAcc('TransferAc'),
                ':rev'   => $sanAcc('RevenuesAc'),
                ':var'   => $sanAcc('VarianceAc'),
                ':dec'   => $sanAcc('DecreasAc'),
                ':inc'   => $sanAcc('IncreasAc'),
                ':ret'   => $sanAcc('ReturnAc'),
                ':exp'   => $sanAcc('ExpensesAc'),
                ':eur'   => $sanAcc('EURevenuAc'),
                ':eue'   => $sanAcc('EUExpensAc'),
                ':frr'   => $sanAcc('FrRevenuAc'),
                ':fre'   => $sanAcc('FrExpensAc'),
                ':exm'   => $sanAcc('ExmptIncom'),
                ':cyc'   => $cycleCode
            ];

            try {
                if (empty($id)) {
                    // INSERTAR NUEVO GRUPO
                    $sql = "INSERT INTO posOITB (
                                ItmsGrpNam, BalInvntAc, SaleCostAc, TransferAc, RevenuesAc, VarianceAc, 
                                DecreasAc, IncreasAc, ReturnAc, ExpensesAc, EURevenuAc, EUExpensAc, FrRevenuAc, FrExpensAc, 
                                ExmptIncom, CycleCode
                            ) VALUES (
                                :nam, :bal, :sale, :trans, :rev, :var, 
                                :dec, :inc, :ret, :exp, :eur, :eue, :frr, :fre, 
                                :exm, :cyc
                            )";
                } else {
                    // ACTUALIZAR GRUPO EXISTENTE
                    $sql = "UPDATE posOITB SET 
                                ItmsGrpNam=:nam, BalInvntAc=:bal, SaleCostAc=:sale, TransferAc=:trans, 
                                RevenuesAc=:rev, VarianceAc=:var, DecreasAc=:dec, IncreasAc=:inc, ReturnAc=:ret, 
                                ExpensesAc=:exp, EURevenuAc=:eur, EUExpensAc=:eue, FrRevenuAc=:frr, FrExpensAc=:fre, 
                                ExmptIncom=:exm, CycleCode=:cyc 
                            WHERE ItmsGrpCod=:id";
                    $params[':id'] = $id;
                }

                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute($params)) {
                    $msg = empty($id) ? "Grupo '{$nombre}' creado exitosamente." : "Grupo '{$nombre}' actualizado exitosamente.";
                    Helper::setMensaje('success', $msg);
                } else {
                    Helper::setMensaje('danger', 'Error al guardar el grupo de artículos.');
                }
            } catch (PDOException $e) {
                Helper::setMensaje('danger', 'Error de Base de Datos: ' . $e->getMessage());
            }
            
            header("Location: index.php?page=grupos_articulo");
            exit();
        }
    }
}
?>
