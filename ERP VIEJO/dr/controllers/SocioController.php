<?php
if (!defined('APP_RUNNING')) { header("Location: ../index.php?page=login"); exit(); }

require_once __DIR__ . '/../config/Helper.php';
require_once __DIR__ . '/../config/Database.php';

class SocioController {

    public function index() {
        $database = new Database();
        $pdo = $database->getConnection();

        // Traemos los socios para la tabla junto a su Grupo Comercial desde posOCRG
        $sql = "SELECT c.CardCode, c.CardName, c.CardType, c.GroupCode, ISNULL(g.GroupName, '') AS GroupName, g.GroupType,
                       c.Address, c.Phone1, c.Balance, c.E_Mail, c.validFor,
                       c.QryGroup1,c.QryGroup2,c.QryGroup3,c.QryGroup4,c.QryGroup5,c.QryGroup6,c.QryGroup7,c.QryGroup8,c.QryGroup9,c.QryGroup10,
                       c.QryGroup54,c.QryGroup55,c.QryGroup56,c.QryGroup57,c.QryGroup58,c.QryGroup59,c.QryGroup60,c.QryGroup61,c.QryGroup62,c.QryGroup63,c.QryGroup64,
                       c.U_NIT, c.U_TNegocio, c.U_Comprob, c.U_CodAnt, c.U_CadenaID, c.U_expediente
                FROM posOCRD c
                LEFT JOIN posOCRG g ON c.GroupCode = g.GroupCode
                ORDER BY c.CardCode";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $socios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lógica de Selección (Edición)
        $socioSeleccionado = null;
        $cardSeleccionado = isset($_GET['card']) ? $_GET['card'] : null;

        if ($cardSeleccionado) {
            $sqlItem = "SELECT * FROM posOCRD WHERE CardCode = ?";
            $stmtItem = $pdo->prepare($sqlItem);
            $stmtItem->execute([$cardSeleccionado]);
            $socioSeleccionado = $stmtItem->fetch(PDO::FETCH_ASSOC);
        }

        // Combos y listas dinámicas desde la Base de Datos
        $stmtGrupos = $pdo->query("SELECT GroupCode, GroupName, GroupType FROM posOCRG ORDER BY GroupName");
        $listaGrupos = $stmtGrupos->fetchAll(PDO::FETCH_ASSOC);

        $sqlCondPago = "SELECT GroupNum, PymntGroup FROM posOCTG ORDER BY PymntGroup";
        $sqlListas = "SELECT ListNum, ListName FROM posOPLN ORDER BY ListName";
        $sqlNCF = "SELECT DISTINCT s.serie, s.serie + ' - ' + s.descripcion AS desc_completa 
                   FROM serie_ncf s
                   INNER JOIN carga_ncf c ON UPPER(s.serie) = UPPER(c.serie)
                   WHERE s.activo = 'Y' 
                     AND UPPER(s.serie) NOT IN ('E33', 'E34', 'B03', 'B04', '33', '34') 
                     AND c.contador < c.final
                     AND (c.vencimiento IS NULL OR c.vencimiento >= CAST(GETDATE() AS DATE))
                   ORDER BY s.serie";
		$sqlAseguradoras = "SELECT CardCode, CardCode + ' - ' + CardName AS NombreAseguradora 
                            FROM posOCRD 
                            WHERE QryGroup1 = 'Y' OR GroupCode = 4 OR CardType = 'S'
                            ORDER BY CardName";
        
        // Valores por defecto si no es edición
        $defaultGroupCode = $socioSeleccionado ? ($socioSeleccionado['GroupCode'] ?? 2) : 2; // 2 = NO ASEGURADOS
        $defaultGroupNum = $socioSeleccionado ? ($socioSeleccionado['GroupNum'] ?? 1) : 1;   // 1 = CONTADO
        $defaultCardCode = $socioSeleccionado ? ($socioSeleccionado['CardCode'] ?? '') : self::obtenerSiguienteCodigo($pdo, 'C');
        
        // Comprobante electrónico por defecto: E32 para pacientes regulares, E31 para aseguradoras o RNC
        if ($socioSeleccionado) {
            $defaultNCF = $socioSeleccionado['U_Comprob'] ?? 'E32';
        } else {
            $defaultNCF = ($defaultGroupCode == 4 || $defaultGroupCode == 5) ? 'E31' : 'E32';
        }

        $comboGrupos = Helper::llenarCombo($pdo, "SELECT GroupCode, GroupName FROM posOCRG ORDER BY GroupName", 'GroupCode', 'GroupName', 'GroupCode', $defaultGroupCode);
        $comboCondPago = Helper::llenarCombo($pdo, $sqlCondPago, 'GroupNum', 'PymntGroup', 'GroupNum', $defaultGroupNum);
        $comboListas = Helper::llenarCombo($pdo, $sqlListas, 'ListNum', 'ListName', 'ListNum', $socioSeleccionado['ListNum'] ?? null);
        $comboNCF = Helper::llenarCombo($pdo, $sqlNCF, 'serie', 'desc_completa', 'U_Comprob', $defaultNCF);
        $comboAseguradoras = Helper::llenarCombo($pdo, $sqlAseguradoras, 'CardCode', 'NombreAseguradora', 'U_SubGrupo', $socioSeleccionado['U_SubGrupo'] ?? null);

        include __DIR__ . '/../views/SocioNegocio/socios.php';
    }

    public static function obtenerSiguienteCodigo($pdo, $prefijo = 'C') {
        $prefijo = strtoupper(substr($prefijo, 0, 1));
        if (!in_array($prefijo, ['C', 'P', 'S'])) {
            $prefijo = 'C';
        }

        $sql = "SELECT CardCode FROM posOCRD WHERE CardCode LIKE ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$prefijo . '%']);
        $codes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $maxNumero = 0;
        foreach ($codes as $code) {
            $numPart = substr(trim($code), 1);
            if (is_numeric($numPart)) {
                $n = (int)$numPart;
                // Considerar secuencia estándar (hasta 9999 o longitud de 4 dígitos)
                if ($n > $maxNumero && $n < 10000) {
                    $maxNumero = $n;
                }
            }
        }

        $nuevoNumero = $maxNumero + 1;
        return $prefijo . str_pad($nuevoNumero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Busca la aseguradora en posOCRD o la inserta automáticamente si no existe en base a s_Seguro de Expediente.
     */
    public static function buscarOInsertarARS($pdo, array $seguroExp): ?array {
        $seguId = trim((string)($seguroExp['segu_IdSeguro'] ?? $seguroExp['expe_IdSeguroMedico'] ?? ''));
        $seguNombre = trim((string)($seguroExp['segu_Nombre'] ?? $seguroExp['expe_NombreSeguro'] ?? ''));
        $seguRnc = preg_replace('/[^0-9]/', '', (string)($seguroExp['segu_rnc'] ?? ''));
        $codCliSin = trim((string)($seguroExp['CodCliSin'] ?? ''));
        $estatus = strtoupper(trim((string)($seguroExp['segu_Estatus'] ?? 'A')));

        if (empty($seguNombre) || is_numeric($seguNombre) || in_array(strtoupper($seguNombre), ['NO ITEM', '0', '19', '20', '22', 'SIN SEGURO', 'NINGUNO', 'PRIVADO']) || $estatus === 'N') {
            return null;
        }

        $normalizar = function(string $str): string {
            $str = mb_strtoupper($str, 'UTF-8');
            $str = str_replace(
                ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ', '(', ')', '-', '.', ',', '/'],
                ['A', 'E', 'I', 'O', 'U', 'U', 'N', ' ', ' ', ' ', ' ', ' ', ' '],
                $str
            );
            return trim(preg_replace('/\s+/', ' ', $str));
        };

        $seguNomNorm = $normalizar($seguNombre);

        // Obtener aseguradoras existentes en posOCRD
        $stmtAll = $pdo->query("
            SELECT CardCode, CardName, GroupCode, U_NIT, QryGroup1 
            FROM posOCRD 
            WHERE QryGroup1 = 'Y' OR GroupCode = 4 OR CardType = 'S'
            ORDER BY CardCode
        ");
        $allARS = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

        // 1. Coincidencia por RNC limpio (si tiene al menos 9 dígitos)
        if (!empty($seguRnc) && strlen($seguRnc) >= 9) {
            foreach ($allARS as $ars) {
                $arsRnc = preg_replace('/[^0-9]/', '', (string)($ars['U_NIT'] ?? ''));
                if (!empty($arsRnc) && $arsRnc === $seguRnc) {
                    return $ars;
                }
            }
        }

        // 2. Coincidencia por CodCliSin con CardCode
        if (!empty($codCliSin)) {
            foreach ($allARS as $ars) {
                if (strtoupper($ars['CardCode']) === strtoupper($codCliSin)) {
                    return $ars;
                }
            }
        }

        // 3. Coincidencia por Nombre
        $palabrasClaveSeguro = array_filter(explode(' ', $seguNomNorm), function($w) {
            return strlen($w) >= 3 && !in_array($w, ['ARS', 'SEGURO', 'SEGUROS', 'SALUD', 'DE', 'DEL', 'LA', 'EL', 'LOS', 'LAS', 'NACIONAL', 'ASOCIADOS', 'ASOCIADA']);
        });

        foreach ($allARS as $ars) {
            $arsNomNorm = $normalizar($ars['CardName']);
            
            if (strpos($arsNomNorm, $seguNomNorm) !== false || strpos($seguNomNorm, $arsNomNorm) !== false) {
                return $ars;
            }

            foreach ($palabrasClaveSeguro as $palabra) {
                if (strpos($arsNomNorm, $palabra) !== false) {
                    return $ars;
                }
                $palabraVar = str_replace(['S', 'Z'], 'C', $palabra);
                $arsVar = str_replace(['S', 'Z'], 'C', $arsNomNorm);
                if (strpos($arsVar, $palabraVar) !== false) {
                    return $ars;
                }
            }
        }

        // 4. Si NO existe en posOCRD, insertarlo automáticamente
        try {
            $nextCardCode = self::obtenerSiguienteCodigo($pdo, 'S');
            $sqlInsert = "INSERT INTO posOCRD (
                            CardCode, CardName, CardType, GroupCode, Address, MailAddres,
                            Phone1, Phone2, Fax, Notes, GroupNum, CreditLine,
                            ListNum, Free_Text, E_Mail, validFor, U_NIT, U_Comprob,
                            U_TNegocio, U_CodAnt, U_ParteRel, U_TipoIngreso, U_SubGrupo,
                            U_CadenaID, U_TipoMembresia, U_expediente,
                            QryGroup1, QryGroup2, QryGroup3, QryGroup4, QryGroup5, QryGroup6,
                            QryGroup7, QryGroup8, QryGroup9, QryGroup10, QryGroup54, QryGroup55,
                            QryGroup56, QryGroup57, QryGroup58, QryGroup59, QryGroup60, QryGroup61,
                            QryGroup62, QryGroup63, QryGroup64
                        ) VALUES (
                            ?, ?, 'S', 4, ?, '',
                            ?, ?, '', 'Creado automáticamente desde Expediente', 1, 0,
                            1, '', '', 'Y', ?, 'E31',
                            '1', ?, 'N', '1', '',
                            '', '', 0,
                            'Y', 'N', 'N', 'N', 'N', 'N',
                            'N', 'N', 'N', 'N', 'N', 'N',
                            'N', 'N', 'N', 'N', 'N', 'N',
                            'N', 'N', 'N'
                        )";
            
            $stmtIns = $pdo->prepare($sqlInsert);
            $stmtIns->execute([
                $nextCardCode,
                substr($seguNombre, 0, 100),
                substr($seguroExp['segu_Direccion'] ?? '', 0, 100),
                substr($seguroExp['segu_Telefono1'] ?? '', 0, 20),
                substr($seguroExp['segu_Telefono2'] ?? '', 0, 20),
                substr($seguRnc, 0, 20),
                substr($seguId, 0, 50)
            ]);

            return [
                'CardCode'     => $nextCardCode,
                'CardName'     => $seguNombre,
                'GroupCode'    => 4,
                'U_NIT'        => $seguRnc,
                'creado_nuevo' => true
            ];
        } catch (Throwable $e) {
            error_log("Error al auto-insertar ARS desde Expediente: " . $e->getMessage());
            return null;
        }
    }

    public function buscarExpediente() {
        $database = new Database();
        $pdo = $database->getConnection();
        $exp = $_GET['exp'] ?? '';
        $prefijo = $_GET['prefijo'] ?? 'C';

        // Consulta cruzada con base de datos Expediente uniendo s_Expediente y s_Seguro
        $sql = "SELECT 
                    e.expe_NumeroExpediente, 
                    ISNULL(e.expe_Nombres, '') + ' ' + ISNULL(e.expe_Apellidos, '') as NombreCompleto, 
                    ISNULL(e.expe_Direccion, '') as Direccion, 
                    ISNULL(e.expe_Cedula, '') as Cedula, 
                    ISNULL(e.expe_Celular, '') as Celular, 
                    ISNULL(e.expe_TelefonoCasa, '') as TelefonoCasa, 
                    ISNULL(e.expe_NumeroAfiliado, '') as NumeroAfiliado,
                    ISNULL(e.expe_NumeroPoliza, '') as NumeroPoliza,
                    e.expe_IdSeguroMedico,
                    e.expe_NombreSeguro,
                    e.expe_IdPlanSeguro,
                    s.segu_IdSeguro,
                    s.segu_Nombre,
                    s.segu_rnc,
                    s.CodCliSin,
                    s.segu_Telefono1,
                    s.segu_Telefono2,
                    s.segu_Direccion,
                    s.segu_Estatus
                FROM [Expediente].[dbo].[s_Expediente] e
                LEFT JOIN [Expediente].[dbo].[s_Seguro] s 
                    ON e.expe_IdSeguroMedico = s.segu_IdSeguro
                WHERE e.expe_NumeroExpediente = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$exp]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        if ($data) {
            $data['next_card_code'] = self::obtenerSiguienteCodigo($pdo, $prefijo);

            // Verificar aseguradora vinculada
            $arsInfo = self::buscarOInsertarARS($pdo, $data);
            if ($arsInfo && !empty($arsInfo['CardCode'])) {
                $data['tiene_seguro'] = true;
                $data['CardCode_ARS'] = $arsInfo['CardCode'];
                $data['Nombre_ARS'] = $arsInfo['CardName'];
                $data['GroupCode'] = 1; // 1 = ASEGURADOS
                $data['NumeroAfiliado'] = !empty($data['NumeroAfiliado']) ? $data['NumeroAfiliado'] : ($data['NumeroPoliza'] ?? '');
                $data['combo_ars_item'] = [
                    'CardCode' => $arsInfo['CardCode'],
                    'NombreAseguradora' => $arsInfo['CardCode'] . ' - ' . $arsInfo['CardName']
                ];
            } else {
                $data['tiene_seguro'] = false;
                $data['CardCode_ARS'] = '';
                $data['Nombre_ARS'] = '';
                $data['GroupCode'] = 2; // 2 = NO ASEGURADOS
                $data['NumeroAfiliado'] = '';
                $data['combo_ars_item'] = null;
            }

            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Expediente no encontrado']);
        }
        exit();
    }

	public function generarCodigo() {
        $prefijo = strtoupper(substr($_GET['prefijo'] ?? 'C', 0, 1));
        $database = new Database();
        $pdo = $database->getConnection();
        $nuevoCodigo = self::obtenerSiguienteCodigo($pdo, $prefijo);
        
        header('Content-Type: application/json');
        echo json_encode(['codigo' => $nuevoCodigo]);
        exit();
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $pdo = $database->getConnection();

            $codigo_original = $_POST['codigo_original'] ?? '';
            $checkToYN = function($field) { return isset($_POST[$field]) ? 'Y' : 'N'; };
            
            $CardCode = strtoupper(trim($_POST['CardCode']));
            $CardName = trim($_POST['CardName']);
            $CardType = $_POST['CardType'];
            $GroupCode = !empty($_POST['GroupCode']) ? $_POST['GroupCode'] : null;
            $Address = $_POST['Address'] ?? null;
            $MailAddres = null; // Lo quitamos de la vista, enviamos nulo
            $Phone1 = $_POST['Phone1'] ?? null;
            $Phone2 = $_POST['Phone2'] ?? null;
            $Fax = $_POST['Fax'] ?? null;
            $Notes = $_POST['Notes'] ?? null;
            $GroupNum = !empty($_POST['GroupNum']) ? $_POST['GroupNum'] : null;
            $CreditLine = !empty($_POST['CreditLine']) ? $_POST['CreditLine'] : null;
            $ListNum = !empty($_POST['ListNum']) ? $_POST['ListNum'] : null;
            $Free_Text = $_POST['Free_Text'] ?? null;
            $E_Mail = $_POST['E_Mail'] ?? null;
            $validFor = $checkToYN('validFor');

            $U_NIT_raw = trim($_POST['U_NIT'] ?? '');
            $U_TNegocio = Helper::determinarTipoNegocio($U_NIT_raw, $_POST['U_TNegocio'] ?? null);
            $U_NIT = !empty($U_NIT_raw) ? Helper::limpiarNit($U_NIT_raw) : null;
            $U_Comprob = !empty($_POST['U_Comprob']) ? strtoupper(trim($_POST['U_Comprob'])) : null;
            if ($U_Comprob && in_array($U_Comprob, ['E33', 'E34', 'B03', 'B04', '33', '34'])) {
                $U_Comprob = 'E32';
            }
            $U_CodAnt = $_POST['U_CodAnt'] ?? null;
            $U_ParteRel = null; // Lo quitamos de la vista
            $U_TipoIngreso = null; // Lo quitamos de la vista
            $U_SubGrupo = !empty($_POST['U_SubGrupo']) ? trim($_POST['U_SubGrupo']) : null;
            $U_CadenaID = !empty($_POST['U_CadenaID']) ? trim($_POST['U_CadenaID']) : null;
            $U_TipoMembresia = null; // Lo quitamos de la vista
            $U_expediente = !empty($_POST['U_expediente']) ? $_POST['U_expediente'] : 0;

            // Validación estricta: si el paciente es asegurado o tiene una ARS vinculada, el N° Carnet es obligatorio
            $esAsegurado = ($GroupCode == 1 || !empty($U_SubGrupo));
            if ($esAsegurado && empty($U_CadenaID)) {
                Helper::setMensaje('danger', 'Error de Validación: El campo "N° Carnet / Seguro Médico" es obligatorio para pacientes asegurados con ARS.');
                $redirItem = empty($codigo_original) ? '' : '&card=' . $codigo_original;
                header("Location: index.php?page=socios" . $redirItem);
                exit();
            }

            $Q1=$checkToYN('QryGroup1');$Q2=$checkToYN('QryGroup2');$Q3=$checkToYN('QryGroup3');$Q4=$checkToYN('QryGroup4');$Q5=$checkToYN('QryGroup5');
            $Q6=$checkToYN('QryGroup6');$Q7=$checkToYN('QryGroup7');$Q8=$checkToYN('QryGroup8');$Q9=$checkToYN('QryGroup9');$Q10=$checkToYN('QryGroup10');
            $Q54=$checkToYN('QryGroup54');$Q55=$checkToYN('QryGroup55');$Q56=$checkToYN('QryGroup56');$Q57=$checkToYN('QryGroup57');$Q58=$checkToYN('QryGroup58');
            $Q59=$checkToYN('QryGroup59');$Q60=$checkToYN('QryGroup60');$Q61=$checkToYN('QryGroup61');$Q62=$checkToYN('QryGroup62');$Q63=$checkToYN('QryGroup63');
            $Q64=$checkToYN('QryGroup64');

            try {
                if (empty($codigo_original)) {
                    // INSERT EN UNA SOLA LÍNEA LIMPIA (47 signos de interrogación exactos)
                    $sql = "INSERT INTO posOCRD (CardCode, CardName, CardType, GroupCode, Address, MailAddres, Phone1, Phone2, Fax, Notes, GroupNum, CreditLine, ListNum, Free_Text, E_Mail, validFor, U_NIT, U_Comprob, U_TNegocio, U_CodAnt, U_ParteRel, U_TipoIngreso, U_SubGrupo, U_CadenaID, U_TipoMembresia, U_expediente, QryGroup1, QryGroup2, QryGroup3, QryGroup4, QryGroup5, QryGroup6, QryGroup7, QryGroup8, QryGroup9, QryGroup10, QryGroup54, QryGroup55, QryGroup56, QryGroup57, QryGroup58, QryGroup59, QryGroup60, QryGroup61, QryGroup62, QryGroup63, QryGroup64) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                    
                    $params = [$CardCode,$CardName,$CardType,$GroupCode,$Address,$MailAddres,$Phone1,$Phone2,$Fax,$Notes,$GroupNum,$CreditLine,$ListNum,$Free_Text,$E_Mail,$validFor,$U_NIT,$U_Comprob,$U_TNegocio,$U_CodAnt,$U_ParteRel,$U_TipoIngreso,$U_SubGrupo,$U_CadenaID,$U_TipoMembresia,$U_expediente,$Q1,$Q2,$Q3,$Q4,$Q5,$Q6,$Q7,$Q8,$Q9,$Q10,$Q54,$Q55,$Q56,$Q57,$Q58,$Q59,$Q60,$Q61,$Q62,$Q63,$Q64];
                } else {
                    // UPDATE EN UNA SOLA LÍNEA LIMPIA
                    $sql = "UPDATE posOCRD SET CardName=?, CardType=?, GroupCode=?, Address=?, MailAddres=?, Phone1=?, Phone2=?, Fax=?, Notes=?, GroupNum=?, CreditLine=?, ListNum=?, Free_Text=?, E_Mail=?, validFor=?, U_NIT=?, U_Comprob=?, U_TNegocio=?, U_CodAnt=?, U_ParteRel=?, U_TipoIngreso=?, U_SubGrupo=?, U_CadenaID=?, U_TipoMembresia=?, U_expediente=?, QryGroup1=?, QryGroup2=?, QryGroup3=?, QryGroup4=?, QryGroup5=?, QryGroup6=?, QryGroup7=?, QryGroup8=?, QryGroup9=?, QryGroup10=?, QryGroup54=?, QryGroup55=?, QryGroup56=?, QryGroup57=?, QryGroup58=?, QryGroup59=?, QryGroup60=?, QryGroup61=?, QryGroup62=?, QryGroup63=?, QryGroup64=? WHERE CardCode=?";
                    
                    $params = [$CardName,$CardType,$GroupCode,$Address,$MailAddres,$Phone1,$Phone2,$Fax,$Notes,$GroupNum,$CreditLine,$ListNum,$Free_Text,$E_Mail,$validFor,$U_NIT,$U_Comprob,$U_TNegocio,$U_CodAnt,$U_ParteRel,$U_TipoIngreso,$U_SubGrupo,$U_CadenaID,$U_TipoMembresia,$U_expediente,$Q1,$Q2,$Q3,$Q4,$Q5,$Q6,$Q7,$Q8,$Q9,$Q10,$Q54,$Q55,$Q56,$Q57,$Q58,$Q59,$Q60,$Q61,$Q62,$Q63,$Q64,$codigo_original];
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $msg = empty($codigo_original) ? 'Socio de negocio creado' : 'Socio de negocio actualizado';
                Helper::setMensaje('success', $msg);

            } catch (PDOException $e) {
                if ($e->getCode() == 2627) { 
                    Helper::setMensaje('warning', 'El Código de Socio ya existe.'); 
                } else { 
                    Helper::setMensaje('danger', 'Error BD: ' . $e->getMessage()); 
                }
            }
            
            header("Location: index.php?page=socios");
            exit();
        }
    }
}