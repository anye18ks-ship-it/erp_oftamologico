<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class CajaController {

      public function index() {
        $database = new Database();
        $pdo = $database->getConnection();

        $caja_id = $_SESSION['caja_id'] ?? 0;

        // Buscamos si esta TERMINAL tiene una caja abierta
        $sqlCheck = "SELECT TOP 1 id_diario_caja, CONVERT(varchar, fecha_apertura, 103) as fecha, CONVERT(varchar, hora_apertura, 108) as hora 
                     FROM diario_caja WHERE id_caja = ? AND fecha_cierre IS NULL ORDER BY id_diario_caja DESC";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$caja_id]);
        $cajaAbierta = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($cajaAbierta) {
            // Restauramos la sesión para que cierre y retiro funcionen
            $_SESSION['caja_apertura_id'] = $cajaAbierta['id_diario_caja'];
            
            // Formatear hora a 12 horas AM/PM
            $hora12 = !empty($cajaAbierta['hora']) ? date('g:i A', strtotime($cajaAbierta['hora'])) : date('g:i A');
            
            // Mostramos pantalla de aviso de caja ya aperturada
            $num_z = str_pad($cajaAbierta['id_diario_caja'], 7, "0", STR_PAD_LEFT);
            $data = [
                'num_z' => $num_z,
                'fecha' => $cajaAbierta['fecha'],
                'hora' => $hora12
            ];
            include __DIR__ . '/../views/Facturacion/caja_ya_abierta.php';
            return; // Detenemos aquí, no cargue el formulario de apertura
        }

        // Si no tiene caja abierta, mostramos el formulario de apertura
        $sqlCaja = "SELECT nombre, alias FROM caja WHERE id_caja = ?";
        $stmtCaja = $pdo->prepare($sqlCaja);
        $stmtCaja->execute([$caja_id]);
        $caja = $stmtCaja->fetch(PDO::FETCH_ASSOC);

        $cajaAlias = $caja['alias'] ?? 'Sin asignar';
        $fechaActual = date('Y-m-d');
        $horaActual = date('g:i A');

        include __DIR__ . '/../views/Facturacion/apertura_caja.php';
    }

    public function abrir() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();

            $monto = filter_var($_POST['monto'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $usuario_id = $_SESSION['usuario_id'] ?? 0;
            $caja_id = $_SESSION['caja_id'] ?? 0;

            if ($monto < 0) {
                Helper::setMensaje('danger', 'El monto de apertura no puede ser negativo.');
                header("Location: index.php?page=apertura_caja");
                exit();
            }

            try {
                $pdo->beginTransaction();

                // 1. Insertar en diario_caja y obtener el ID generado usando OUTPUT
                $sqlCaja = "INSERT INTO diario_caja (fecha_apertura, hora_apertura, monto_apertura, monto_diferencia, usuario_apertura, id_caja) 
                             OUTPUT INSERTED.id_diario_caja 
                             VALUES (GETDATE(), GETDATE(), ?, 0, ?, ?)";
                
                $stmtCaja = $pdo->prepare($sqlCaja);
                $stmtCaja->execute([$monto, $usuario_id, $caja_id]);
                $id_diario_caja = $stmtCaja->fetchColumn();

                // 2. Insertar en el detalle usando el ID obtenido
                $sqlDet = "INSERT INTO diario_caja_det (id_diario_caja, id_tipo_movimiento, id_documento, monto_factura, monto_caja, comentario) 
                           VALUES (?, 6, 0, 0, ?, 'Apertura de caja')";
                $stmtDet = $pdo->prepare($sqlDet);
                $stmtDet->execute([$id_diario_caja, $monto]);

                $pdo->commit();

                // Guardar en sesión que la caja está abierta
                $_SESSION['caja_apertura_id'] = $id_diario_caja;

                Helper::setMensaje('success', 'Apertura de caja aplicada correctamente.');

            } catch (PDOException $e) {
                $pdo->rollBack();
                Helper::setMensaje('danger', 'Error al abrir la caja: ' . $e->getMessage());
            }

            // Redirigir a apertura_caja para que renderice inmediatamente caja_ya_abierta con el turno activo
            header("Location: index.php?page=apertura_caja");
            exit();
        }
    }
	
	public function cierre() {
        $database = new Database();
        $pdo = $database->getConnection();
        $caja_id = $_SESSION['caja_id'] ?? 0;

        $id_diario_caja = $_SESSION['caja_apertura_id'] ?? 0;

        if ($id_diario_caja == 0) {
            // Buscamos la caja abierta de ESTA terminal
            $sqlBackup = "SELECT TOP 1 id_diario_caja FROM diario_caja WHERE id_caja = ? AND fecha_cierre IS NULL";
            $stmtBackup = $pdo->prepare($sqlBackup);
            $stmtBackup->execute([$caja_id]);
            $backup = $stmtBackup->fetch(PDO::FETCH_ASSOC);
            
            if ($backup) {
                $_SESSION['caja_apertura_id'] = $backup['id_diario_caja'];
                $id_diario_caja = $backup['id_diario_caja'];
            } else {
                Helper::setMensaje('danger', "No se encontró ninguna caja aperturada para esta terminal. Debe aperturar una caja antes de realizar el cierre.");
                header("Location: index.php?page=apertura_caja");
                exit();
            }
        }

        // El resto del método sigue exactamente igual...
        $sql = "SELECT 
                    T1.id_diario_caja,
                    CONVERT(varchar, T1.fecha_apertura, 103) as fecha_apertura,
                    CONVERT(varchar, T1.hora_apertura, 108) as hora_apertura,
                    COALESCE(NULLIF(T2.nombre, ''), T2.usuario, ISNULL(T1.usuario_apertura, '')) as usuario,
                    ISNULL(T0.alias, 'Sin alias') as alias
                FROM diario_caja T1 
                LEFT JOIN caja T0 ON T1.id_caja = T0.id_caja
                LEFT JOIN usuario T2 ON (TRY_CAST(T1.usuario_apertura AS INT) = T2.id_usuario OR T1.usuario_apertura = T2.usuario)
                WHERE T1.id_diario_caja = ?";
            
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_diario_caja]);
        $apertura = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$apertura) {
            Helper::setMensaje('warning', 'No se pudo encontrar la información de la caja abierta.');
            header("Location: index.php?page=apertura_caja");
            exit();
        }

        // Formatear hora de apertura a 12 horas AM/PM
        $apertura['hora_apertura'] = !empty($apertura['hora_apertura']) ? date('g:i A', strtotime($apertura['hora_apertura'])) : date('g:i A');
        $apertura['num_z'] = str_pad($id_diario_caja, 7, "0", STR_PAD_LEFT);

        $sqlTotales = "SELECT 
                            ISNULL(SUM(CASE WHEN id_tipo_movimiento = 6 THEN monto_caja ELSE 0 END), 0) as deposito,
                            ISNULL(SUM(CASE WHEN id_tipo_movimiento = 7 AND monto_caja < 0 THEN monto_caja * -1 ELSE 0 END), 0) as retiro,
                            ISNULL(SUM(CASE WHEN id_tipo_movimiento = 7 THEN 1 ELSE 0 END), 0) as cant_retiro
                        FROM diario_caja_det 
                        WHERE id_diario_caja = ?";
        $stmtTot = $pdo->prepare($sqlTotales);
        $stmtTot->execute([$id_diario_caja]);
        $totales = $stmtTot->fetch(PDO::FETCH_ASSOC);

        // Ventas por medio de pago del turno (datos halados)
        $sqlVentas = "SELECT
                            ISNULL(SUM(CASE WHEN id_tipo_movimiento = 1 THEN monto_caja ELSE 0 END), 0) as fact_efectivo,
                            ISNULL(SUM(CASE WHEN id_tipo_movimiento = 2 THEN monto_caja ELSE 0 END), 0) as fact_tarjeta,
                            ISNULL(SUM(CASE WHEN id_tipo_movimiento = 3 THEN monto_caja ELSE 0 END), 0) as fact_cheque,
                            ISNULL(SUM(CASE WHEN id_tipo_movimiento = 4 THEN monto_caja ELSE 0 END), 0) as fact_transferencia
                        FROM diario_caja_det 
                        WHERE id_diario_caja = ?";
        $stmtVentas = $pdo->prepare($sqlVentas);
        $stmtVentas->execute([$id_diario_caja]);
        $ventas = $stmtVentas->fetch(PDO::FETCH_ASSOC);

        // Retiros desglosados por Efectivo y Cheques
        $sqlRetiros = "SELECT 
                           ISNULL(SUM(CASE WHEN id_medio_retiro = 1 THEN monto ELSE 0 END), 0) as retiro_efectivo,
                           ISNULL(SUM(CASE WHEN id_medio_retiro = 2 THEN monto ELSE 0 END), 0) as retiro_cheque,
                           ISNULL(SUM(monto), 0) as monto_retiro
                       FROM retiro_caja 
                       WHERE id_diario_caja = ? AND anulado = 'N'";
        $stmtRet = $pdo->prepare($sqlRetiros);
        $stmtRet->execute([$id_diario_caja]);
        $retiroRow = $stmtRet->fetch(PDO::FETCH_ASSOC);

        $fondo_apertura = floatval($totales['deposito'] ?? 0);
        $monto_retiro_efec = abs(floatval($retiroRow['retiro_efectivo'] ?? 0));
        $monto_retiro_cheq = abs(floatval($retiroRow['retiro_cheque'] ?? 0));
        $monto_retiro_total = abs(floatval($retiroRow['monto_retiro'] ?? 0));

        $ventas_efec = floatval($ventas['fact_efectivo'] ?? 0);
        $ventas_tarj = floatval($ventas['fact_tarjeta'] ?? 0);
        $ventas_cheq = floatval($ventas['fact_cheque'] ?? 0);
        $ventas_trans = floatval($ventas['fact_transferencia'] ?? 0);

        $efectivo_con_fondo = $fondo_apertura + $ventas_efec;
        $efectivo_neto = max(0, $efectivo_con_fondo - $monto_retiro_efec);
        $cheque_neto = max(0, $ventas_cheq - $monto_retiro_cheq);
        $total_teorico = ($efectivo_con_fondo - $monto_retiro_efec) + $ventas_tarj + $ventas_trans + ($ventas_cheq - $monto_retiro_cheq);

        $teorico = [
            'fondo_apertura'       => $fondo_apertura,
            'ventas_efectivo'      => $ventas_efec,
            'efectivo_con_fondo'   => $efectivo_con_fondo,
            'ventas_tarjeta'       => $ventas_tarj,
            'ventas_transferencia' => $ventas_trans,
            'ventas_cheque'        => $ventas_cheq,
            'monto_retiro_efec'    => $monto_retiro_efec,
            'monto_retiro_cheq'    => $monto_retiro_cheq,
            'monto_retiro_total'   => $monto_retiro_total,
            'efectivo_neto'        => $efectivo_neto,
            'cheque_neto'          => $cheque_neto,
            'total_teorico'        => $total_teorico
        ];

        $data = [
            'apertura'     => $apertura,
            'totales'      => $totales,
            'teorico'      => $teorico,
            'fecha_cierre' => date('d/m/Y'),
            'hora_cierre'  => date('g:i A')
        ];

        include __DIR__ . '/../views/Facturacion/cierre_caja.php';
    }

    public function ejecutarCierre() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: index.php?page=cierre_caja");
        exit();
    }

    $database = new Database();
    $pdo = $database->getConnection();
    
    $id_diario     = (int)($_POST['id_diario_caja'] ?? 0);
    $efectivo      = filter_var($_POST['monto_efectivo'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $tarjeta       = filter_var($_POST['monto_tarjeta'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $cheque        = filter_var($_POST['monto_cheque'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $transferencia = filter_var($_POST['monto_transferencia'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $credito       = filter_var($_POST['monto_credito'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $comentario    = trim($_POST['comentario'] ?? '');

    $usuario_cierre = $_SESSION['usuario_id'] ?? ($_SESSION['id_usuario'] ?? ($_SESSION['usuario'] ?? 0));

    if ($id_diario <= 0) {
        Helper::setMensaje('danger', 'ID de diario de caja inválido.');
        header("Location: index.php?page=cierre_caja");
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Obtener retiros reales
        $stmtRet = $pdo->prepare("SELECT ISNULL(SUM(monto), 0) FROM retiro_caja WHERE id_diario_caja = ? AND anulado = 'N'");
        $stmtRet->execute([$id_diario]);
        $retiro = $stmtRet->fetchColumn();

        $sqlUpdate = "UPDATE diario_caja 
                      SET fecha_cierre = GETDATE(),
                          hora_cierre = GETDATE(),
                          monto_efectivo = ?,
                          monto_tarjeta = ?,
                          monto_cheque = ?,
                          monto_transferencia = ?,
                          monto_credito = ?,
                          monto_retiro = ?,
                          usuario_cierre = ?,
                          comentario = ?,
                          orden_cierre = ISNULL((SELECT MAX(orden_cierre)+1 FROM diario_caja WHERE id_caja = (SELECT id_caja FROM diario_caja WHERE id_diario_caja = ?)), 1)
                      WHERE id_diario_caja = ?";

        $pdo->prepare($sqlUpdate)->execute([
            $efectivo, $tarjeta, $cheque, $transferencia, $credito, $retiro, 
            $usuario_cierre, $comentario, $id_diario, $id_diario
        ]);

        // Guardar desglose físico de monedas y billetes contados (cierre_denominacion)
        $denominaciones = [2000, 1000, 500, 200, 100, 50, 25, 10, 5, 1];
        $stmtDen = $pdo->prepare("INSERT INTO cierre_denominacion (id_diario_caja, denominacion, cantidad, total) VALUES (?, ?, ?, ?)");
        foreach ($denominaciones as $den) {
            $cant = intval($_POST['mon_' . $den] ?? 0);
            if ($cant > 0) {
                $stmtDen->execute([$id_diario, $den, $cant, ($den * $cant)]);
            }
        }

        $pdo->commit();

        unset($_SESSION['caja_apertura_id']);
        Helper::setMensaje('success', 'Cierre de caja realizado correctamente.');
        header("Location: index.php?page=apertura_caja");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        Helper::setMensaje('danger', 'Error al cerrar la caja: ' . $e->getMessage());
        header("Location: index.php?page=cierre_caja");
        exit();
    }
}

    public function visualizarArqueo(){
    try {
        $database = new Database();
        $pdo = $database->getConnection();
        
        $id_diario = (int)($_GET['id'] ?? 0);

        if ($id_diario <= 0) {
            throw new Exception("ID de diario inválido");
        }

        // Ventas por medio de pago
        $sql = "SELECT
                    ISNULL(SUM(CASE WHEN id_tipo_movimiento IN (1,6) THEN monto_caja ELSE 0 END), 0) as monto_efectivo,
                    ISNULL(SUM(CASE WHEN id_tipo_movimiento = 2 THEN monto_caja ELSE 0 END), 0) as monto_tarjeta,
                    ISNULL(SUM(CASE WHEN id_tipo_movimiento = 3 THEN monto_caja ELSE 0 END), 0) as monto_cheque,
                    ISNULL(SUM(CASE WHEN id_tipo_movimiento = 4 THEN monto_caja ELSE 0 END), 0) as monto_transferencia
                FROM diario_caja_det 
                WHERE id_diario_caja = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_diario]);
        $ventas = $stmt->fetch(PDO::FETCH_ASSOC);

        // Retiros desglosados por Efectivo y Cheques
        $sqlRetiros = "SELECT 
                           ISNULL(SUM(CASE WHEN id_medio_retiro = 1 THEN monto ELSE 0 END), 0) as retiro_efectivo,
                           ISNULL(SUM(CASE WHEN id_medio_retiro = 2 THEN monto ELSE 0 END), 0) as retiro_cheque,
                           ISNULL(SUM(monto), 0) as monto_retiro
                       FROM retiro_caja 
                       WHERE id_diario_caja = ? AND anulado = 'N'";
        
        $stmtRet = $pdo->prepare($sqlRetiros);
        $stmtRet->execute([$id_diario]);
        $retiroRow = $stmtRet->fetch(PDO::FETCH_ASSOC);

        $monto_retiro_efec = abs(floatval($retiroRow['retiro_efectivo'] ?? 0));
        $monto_retiro_cheq = abs(floatval($retiroRow['retiro_cheque'] ?? 0));
        $monto_retiro_total = abs(floatval($retiroRow['monto_retiro'] ?? 0));

        $ventas_efec = floatval($ventas['monto_efectivo'] ?? 0);
        $ventas_tarj = floatval($ventas['monto_tarjeta'] ?? 0);
        $ventas_cheq = floatval($ventas['monto_cheque'] ?? 0);
        $ventas_trans = floatval($ventas['monto_transferencia'] ?? 0);

        // Efectivo esperado en gaveta (Ventas + Fondo - Retiros Efectivo)
        $efectivo_neto = max(0, $ventas_efec - $monto_retiro_efec);
        // Cheques esperados en gaveta (Ventas Cheque - Retiros Cheque)
        $cheque_neto = max(0, $ventas_cheq - $monto_retiro_cheq);

        $total_teorico = ($ventas_efec - $monto_retiro_efec) + $ventas_tarj + ($ventas_cheq - $monto_retiro_cheq) + $ventas_trans;

        $resultado = [
            "monto_efectivo"        => $ventas_efec,
            "monto_tarjeta"         => $ventas_tarj,
            "monto_cheque"          => $ventas_cheq,
            "monto_transferencia"   => $ventas_trans,
            "monto_retiro_efectivo" => $monto_retiro_efec,
            "monto_retiro_cheque"   => $monto_retiro_cheq,
            "monto_retiro"          => $monto_retiro_total,
            "efectivo_neto"         => $efectivo_neto,
            "cheque_neto"           => $cheque_neto,
            "monto_total"           => $total_teorico
        ];

        header('Content-Type: application/json');
        echo json_encode($resultado);

    } catch (Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}
	
	public function retiro() {
        $database = new Database();
        $pdo = $database->getConnection();
        $caja_id = $_SESSION['caja_id'] ?? 0;

        // Buscamos por la terminal (caja_id)
        $sqlCheck = "SELECT TOP 1 id_diario_caja FROM diario_caja WHERE id_caja = ? AND fecha_cierre IS NULL";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$caja_id]);
        $caja = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$caja) {
            Helper::setMensaje('danger', 'No hay ninguna caja abierta para hacer retiros. Debe aperturar una caja primero.');
            header("Location: index.php?page=apertura_caja");
            exit();
        }

        // Restauramos la sesión correctamente
        $_SESSION['caja_apertura_id'] = $caja['id_diario_caja'];

        $sqlConceptos = "SELECT id_concepto_retiro, descripcion FROM concepto_retiro WHERE activo='Y' ORDER BY descripcion";
        $sqlMedios = "SELECT id_medio_retiro, descripcion FROM medio_retiro WHERE activo='Y' ORDER BY descripcion";
        
        $conceptos = $pdo->query($sqlConceptos)->fetchAll(PDO::FETCH_ASSOC);
        $medios = $pdo->query($sqlMedios)->fetchAll(PDO::FETCH_ASSOC);

        $fechaActual = date('d/m/Y');
        $horaActual = date('g:i A');
        
        $cajaAlias = $pdo->prepare("SELECT alias FROM caja WHERE id_caja = ?");
        $cajaAlias->execute([$caja_id]);
        $alias = $cajaAlias->fetchColumn();

        include __DIR__ . '/../views/Facturacion/retiro_caja.php';
    }

    public function guardarRetiro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();

            $id_diario_caja = $_SESSION['caja_apertura_id'] ?? 0;
            $id_concepto = $_POST['id_concepto_retiro'];
            $id_medio = $_POST['id_medio_retiro'];
            $monto = filter_var($_POST['monto'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $referencia = $_POST['referencia'] ?? '';
            $quien_recibe = trim($_POST['quien_recibe'] ?? '');
            $comentario = substr(trim($_POST['comentario'] ?? ''), 0, 50); // Limitar a 50 como en Java
            $quien_entrega = $_SESSION['nombre'] ?? '';

            if ($monto <= 0) {
                Helper::setMensaje('warning', 'Debe indicar el monto a retirar mayor a cero.');
                header("Location: index.php?page=retiro_caja");
                exit();
            }

            // El monto en BD se guarda negativo
            $montoNegativo = $monto * -1;

            try {
                $pdo->beginTransaction();

                // 1. Insertar en retiro_caja y obtener ID
                $sqlRetiro = "INSERT INTO retiro_caja (id_diario_caja, id_concepto_retiro, id_medio_retiro, monto, referencia, quien_entrega, quien_recibe, comentario) 
                              OUTPUT INSERTED.id_retiro_caja 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmtRetiro = $pdo->prepare($sqlRetiro);
                $stmtRetiro->execute([$id_diario_caja, $id_concepto, $id_medio, $montoNegativo, $referencia, $quien_entrega, $quien_recibe, $comentario]);
                $id_retiro = $stmtRetiro->fetchColumn();

                // 2. Insertar en diario_caja_det (id_tipo_movimiento = 7)
                $sqlDet = "INSERT INTO diario_caja_det (id_diario_caja, id_tipo_movimiento, id_documento, monto_factura, monto_caja, comentario) 
                           VALUES (?, 7, ?, 0, ?, ?)";
                $pdo->prepare($sqlDet)->execute([$id_diario_caja, $id_retiro, $montoNegativo, $comentario]);

                // 3. Si el medio es Efectivo (Medio 1), guardar denominaciones si fueron desglosadas
                if ($id_medio == 1) {
                    $denominaciones = [2000, 1000, 500, 200, 100, 50, 25, 10, 5, 1];
                    $stmtDen = $pdo->prepare("INSERT INTO retiro_denominacion (id_retiro_caja, denominacion, cantidad, total) VALUES (?, ?, ?, ?)");
                    foreach ($denominaciones as $den) {
                        $cant = intval($_POST['mon_' . $den] ?? 0);
                        if ($cant > 0) {
                            $stmtDen->execute([$id_retiro, $den, $cant, ($den * $cant)]);
                        }
                    }
                }

                $pdo->commit();
                Helper::setMensaje('success', 'Retiro de RD$ ' . number_format($monto, 2) . ' aplicado correctamente.');

            } catch (PDOException $e) {
                $pdo->rollBack();
                Helper::setMensaje('danger', 'Error al procesar el retiro: ' . $e->getMessage());
            }

            header("Location: index.php?page=dashboard");
            exit();
        }
    }
	
	public function reporteCierre(){
		$id = $_GET['id'] ?? 0;
		$copia = $_GET['copia'] ?? 0;

		if (!$id) {
			die("ID requerido");
		}

		require_once __DIR__ . '/../reports/InformeCierreCaja.php';
		$reporte = new InformeCierreCaja($id, $copia);
		$reporte->generar('I'); 
	}
	
	public function consultaCierre()
    {
        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "SELECT 
                    T0.id_diario_caja,
                    T0.orden_cierre,
                    T2.alias as caja,
                    FORMAT(T0.fecha_cierre, 'dd/MM/yyyy hh:mm tt') as fecha_cierre,
                    COALESCE(NULLIF(T4.nombre, ''), T4.usuario, 'Sin cajero') as cajero_cierre,
                    ISNULL(T0.monto_efectivo,0) + ISNULL(T0.monto_tarjeta,0) + ISNULL(T0.monto_cheque,0) + ISNULL(T0.monto_transferencia,0) + ISNULL(T0.monto_credito,0) as monto_declarado
                FROM diario_caja T0
                INNER JOIN caja T2 ON T0.id_caja = T2.id_caja
                LEFT JOIN usuario T4 ON (TRY_CAST(T0.usuario_cierre AS INT) = T4.id_usuario OR T0.usuario_cierre = T4.usuario)
                WHERE T0.fecha_cierre IS NOT NULL
                ORDER BY T0.fecha_cierre DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $cierres = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/Facturacion/consulta_cierre.php';
    }
	
	public function consultaRetiro()
    {
        $database = new Database();
        $pdo = $database->getConnection();

        $sql = "SELECT 
                    R0.id_retiro_caja,
                    CONVERT(varchar, T0.fecha_apertura, 103) as fecha_caja,
                    T1.alias as caja,
                    R1.descripcion as concepto,
                    R2.descripcion as medio,
                    (R0.monto * -1) as monto,
                    R0.quien_recibe
                FROM retiro_caja R0
                INNER JOIN diario_caja T0 ON R0.id_diario_caja = T0.id_diario_caja
                INNER JOIN caja T1 ON T0.id_caja = T1.id_caja
                LEFT JOIN concepto_retiro R1 ON R0.id_concepto_retiro = R1.id_concepto_retiro
                LEFT JOIN medio_retiro R2 ON R0.id_medio_retiro = R2.id_medio_retiro
                WHERE R0.anulado = 'N'
                ORDER BY T0.fecha_apertura DESC, R0.id_retiro_caja DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $retiros = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/Facturacion/consulta_retiro.php';
    }

    public function reporteRetiro()
    {
        $id = $_GET['id'] ?? 0;
        $copia = $_GET['copia'] ?? 0;

        if (!$id) {
            die("ID de retiro requerido");
        }

        require_once __DIR__ . '/../reports/InformeRetiroCaja.php';
        $reporte = new InformeRetiroCaja($id, $copia);
        $reporte->generar('I'); // 'I' para mostrar en navegador, 'D' para forzar descarga
    }
}
?>