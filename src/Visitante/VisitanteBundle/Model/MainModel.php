<?php

namespace Visitante\VisitanteBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use ShowDashboard\DashboardBundle\Model\DashboardModel;
use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\FileMakerBundle\API\FMAPI;

class MainModel extends DashboardModel {

    protected $SQLModelAE, $schemaAE = "AE", $DBData, $base_path_cache, $FM_Client, $DBDataFM;
    public $mirrorFields = array(
        'idVisitante' => '_id_Visitante',
        'idVisitantePadre' => '_id_Visitante_Padre',
        'idVisitanteTipo' => '_id_Visitante_Tipo',
        'Titulo' => 'Titulo',
        'Nombre' => 'Nombre',
        'ApellidoPaterno' => 'ApellidoPaterno',
        'ApellidoMaterno' => 'ApellidoMaterno',
        'NombreCompleto' => 'NombreCompleto',
        'Email' => 'Email',
        'Password' => 'Password',
        'CadenaUnica' => 'CadenaUnica',
        'Sexo' => 'Sexo',
        'FechaNacimiento' => 'FechaNacimiento',
        'Movil' => 'Movil',
        'Nextel' => 'Nextel',
        'Fax' => 'Fax',
        'EmailOpcional' => 'Email_Opcional',
        'TokenPassword' => 'TokenPassword',
        'DE_RazonSocial' => 'DE_Razon_Social',
        'DE_Cargo' => 'DE_Cargo',
        'DE_RFC' => 'DE_RFC',
        'DE_WebPage' => 'DE_WebPage',
        'DE_AreaPais' => 'DE_AreaPais',
        'DE_Telefono' => 'DE_Telefono',
        'DE_idPais' => 'DE_id_Pais',
        'DE_Pais' => 'DE_Pais',
        'DE_idEstado' => 'DE_id_Estado',
        'DE_Estado' => 'DE_Estado',
        'DE_CP' => 'DE_CP',
        'DE_Ciudad' => 'DE_Ciudad',
        'DE_Colonia' => 'DE_Colonia',
        'DE_Direccion' => 'DE_Direccion',
        'InteresEvento' => 'InteresEvento',
        'EntradaEvento' => 'EntradaEvento',
        'GafetePerfil' => 'Gafete_Perfil',
        'GafeteVIP' => 'Gafete_VIP',
        'Boletines' => 'Boletines',
        'Ip' => 'Ip',
        'NavegadorNombre' => 'Navegador_Nombre',
        'NavegadorVersion' => 'Navegador_Version',
        'NavegadorSO' => 'Navegador_SO',
        'Navegador_UserAgent' => 'Navegador_UserAgent',
        'SyncExport' => 'Sync_Export',
        'Lang' => 'lang',
        'CadenaUnica' => 'CadenaUnica',
        'SyncKey' => 'SyncKey',
    );

    public function __construct(ContainerInterface $container = null) {
        $this->base_path_cache = '../var/cache/visitante/';
//        $session = $container->get('session');
//        $where = array('idEvento' => $session->get('edicion')["idEvento"], 'idEdicion' => $session->get('idEdicion'));
        parent::__construct();
//        $this->setDBParam($where);
//        $this->SQLModelAE = new SQLModel($this->getDBData());
//        $this->SQLModelAE->setSchema("AE");
//        $this->FM_Client = new FMAPI($this->getDBDataFM());
    }

    private function setDBParam($where) {
        $result_db = $this->getConexion($where);
        if ($result_db['status'] && count($result_db['data']) > 0) {
            $data = $result_db['data'];
            $db_data = array(
                'db_name' => $data[0]['Base'],
                'db_user' => $data[0]['Usuario'],
                'db_password' => $data[0]['Password'],
                'db_server' => $data[0]['Servidor'],
                'db_port' => $data[0]['Puerto'],
            );
            $db_data_fm = array(
                'fm_database' => $data[0]['FmConexion']['fm_database'],
                'fm_user' => $data[0]['FmConexion']['fm_user'],
                'fm_password' => $data[0]['FmConexion']['fm_password'],
                'fm_server' => $data[0]['FmConexion']['fm_server'],
                'fm_port' => $data[0]['FmConexion']['fm_port'],
            );
            $this->setDBData($db_data);
            $this->setDBDataFM($db_data_fm);
        }
    }

    private function getConexion($where) {
        $cache_name = "conexion";
        $ruta = $this->base_path_cache . $cache_name . '.json';
        if (file_exists($ruta)) {
            $result_cache = file_get_contents($ruta);
            $result = Array('status' => TRUE, 'data' => json_decode($result_cache, TRUE));
        } else {
            $fields = $this->getConexionFields();
            $result = $this->SQLModel->selectFromTable('Conexion', $fields, $where);
            if (!$result['status']) {
                throw new \Exception($result['data'], 409);
            }

            if (count($result['data']) == 0) {
                throw new \Exception('No se encontraron los parametros de conexion hacia el Attendee Experience', 409);
            }
            $result['data'][0]['FmConexion'] = json_decode($result['data'][0]['FmConexion'], TRUE);
            $this->writeJSON($ruta, $result['data']);
            clearstatcache();
        }
        return $result;
    }

    protected function getConexionFields() {
        return Array(
            'Base',
            'Usuario',
            'Password',
            'Servidor',
            'Puerto',
            'FmConexion',
        );
    }

    public function setDBData($data) {
        $this->DBData = $data;
    }

    public function getDBData() {
        return $this->DBData;
    }

    public function setDBDataFM($data) {
        $this->DBDataFM = $data;
    }

    public function getDBDataFM() {
        return $this->DBDataFM;
    }

    public function getVisitante($args) {
        $qry = 'SELECT * FROM';
        $qry .= ' "AE"."fn_ae_ConsultarVisitante"(' . $args['idVisitante'] . ') as "jsonVisitante"';
        $result = $this->SQLModel->executeQuery($qry);
        if ($result['status']) {
            $result['data'] = $this->decodeJSONVisitante($result['data']);
        }
        return $result;
    }

    public function getCuentasGafete($args) {
        $qry = 'SELECT * FROM';
        $qry .= ' "AE"."vw_ae_VisitanteAsociado_temp" WHERE "idVisitante" = ' . $args['idVisitante'];
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getCuentasGafetePrensa($args) {
        $qry = 'SELECT * FROM';
        $qry .= ' "AE"."vw_ae_VisitantePrensa_temp" WHERE "idVisitante" = ' . $args['idVisitante'];
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }
    public function getCuentasGafeteCompradores($args) {
        $qry = 'SELECT * FROM';
        $qry .= ' "AE"."vw_ae_VisitanteAsociado_temp" WHERE "idVisitante" = ' . $args['idVisitante'];
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }


    public function getCuentasGafeteMultiple($args) {
        $qry = 'SELECT * FROM';
        $qry .= ' "AE"."vw_ae_VisitantePrensa_temp" WHERE "idVisitante" = ' . $args['idVisitante'];
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getSendGafeteMultipleCount($args) {
        $qry = 'SELECT COUNT("idVisitante") FROM "AE"."LogEnvioGafete" WHERE "idVisitante" =' . $args['idVisitante'] . ' AND "Accion" =2';
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getDownloadGafeteMultipleCount($args) {
        $qry = 'SELECT COUNT("idVisitante") FROM "AE"."LogEnvioGafete" WHERE "idVisitante" =' . $args['idVisitante'] . ' AND "Accion" =1';
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }
    public function getSendGafeteEncuentroCount($args) {
        $qry = 'SELECT COUNT("idVisitante") FROM "AE"."LogEnvioGafete" WHERE "idVisitante" =' . $args['idVisitante'] . ' AND "Accion" =2';
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getDownloadGafeteEncuentroCount($args) {
        $qry = 'SELECT COUNT("idVisitante") FROM "AE"."LogEnvioGafete" WHERE "idVisitante" =' . $args['idVisitante'] . ' AND "Accion" =1';
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function lastUpdateSend($idVisitante) {
        $qry = ' SELECT *';
        $qry .= ' FROM "AE"."LogEnvioGafete"';
        $qry .= ' WHERE "idVisitante" =' . $idVisitante['idVisitante'];
        $qry .= ' AND "Accion" = 2';
        $qry .= ' ORDER BY "FechaModificacion" DESC ';
        $qry .= ' LIMIT 1 ';
        $result = $this->SQLModel->executeQuery($qry);

        return $result;

        return $result;
    }

    public function lastUpdateDownload($idVisitante) {
        $qry = ' SELECT *';
        $qry .= ' FROM "AE"."LogEnvioGafete"';
        $qry .= ' WHERE "idVisitante" =' . $idVisitante['idVisitante'];
        $qry .= ' AND "Accion" = 1';
        $qry .= ' ORDER BY "FechaModificacion" DESC ';
        $qry .= ' LIMIT 1 ';
        $result = $this->SQLModel->executeQuery($qry);

        return $result;
    }

    public function convertFieldsVisitor($data) {
        $dataFM = array();
        foreach ($this->mirrorFields as $key => $keyFM) {
            if (isset($data[$key])) {
                $dataFM[$keyFM] = $data[$key];
            }
        }
        return $dataFM;
    }

    public function insertEditVisitanteFM($stringData) {
        $result = $this->FM_Client->Run_Script('P_GE|VIS', 'PD|STD_InsertEditVisitorPreregistro', $stringData);
        if (!$result['status']) {
            return $result;
        }

        if (COUNT($result['data']) > 0) {
            return Array('status' => TRUE, 'data' => $result['data'][0]);
        } else {
            return Array('status' => FALSE, 'data' => 'Ha ocurrido un error al procesar sus datos, intete una vez mas.');
        }
    }

    public function getTexts($lang = 'ES', $idTemplate = "1") {
        $lang = ($lang == "") ? 'ES' : strtoupper($lang);
        $cache = 'ae_' . str_replace("'", "", $idTemplate) . '_' . strtoupper($lang) . '.json';
        $path = '../var/cache/textos/' . $cache;
        $data = Array();
        if (file_exists($path)) {
            $result_cache = file_get_contents($path);
            $data = json_decode($result_cache, TRUE);
            return Array("status" => TRUE, "data" => $data);
        }
        $result = $this->getPGTexts($idTemplate, $lang);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        if (COUNT($result['data']) > 0) {
            $this->writeJSON($path, $result['data']);
            clearstatcache();
        }
        return $result;
    }

    protected function getPGTexts($idTemplate, $lang) {
        $fields = $this->getTextFields($lang);
        $where = Array('idTemplate' => $idTemplate);
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->selectFromTable("TemplateTexto", $fields, $where);
        $this->SQLModel->setSchema("SAS");
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = Array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['Etiqueta']] = $value['Texto_' . $lang];
        }
        $result['data'] = $data;
        return $result;
    }

    protected function getTextFields($lang) {
        return Array(
            'idTemplateTexto',
            'Etiqueta',
            'Texto_' . $lang,
        );
    }

    public function writeJSON($filename, $array) {
        $json = json_encode($array);
        $fp = fopen($filename, "w");
        fwrite($fp, $json);
        fclose($fp);
        chmod($filename, 0777);
    }

    public function formatJSONEdiciones($data) {
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $edicionesVisitante = NULL;
                if (count(json_decode($value['EdicionesVisitante'], TRUE)) > 0) {
                    foreach (json_decode($value['EdicionesVisitante'], TRUE) as $k => $edicion) {
                        $edicionesVisitante[$edicion['idEdicion']] = $edicion;
                    }
                }
                $data[$key]['EdicionesVisitante'] = $edicionesVisitante;
            }
        }
        return $data;
    }

    public function createString($post) {
        $SParr = "";
        if (count($post) == 0) {
            return $SParr;
        }

        foreach ($post as $key => $value) {
            $value = trim($value);
            $SParr .= "$key:=" . str_replace("'", "\'", $value) . "|||";
        }
        return substr($SParr, 0, -3);
    }

    public function createStringFM($ar = "", $post) {
        $SParr = "";
        if ($ar != "") {
            $SParr .= "_ar: $ar; ";
        }

        foreach ($post as $key => $value) {
            $value = trim($value);
            $SParr .= "$key: $value; ";
        }
        return $SParr;
    }

    public function decodeJSONVisitante($data) {
        if (count($data) > 0) {
            foreach ($data as $key => $value) {
                $visitante = json_decode($value['jsonVisitante'], TRUE);
                $edicionesVisitante = NULL;
                if (count($visitante['EdicionesVisitante']) > 0) {
                    foreach ($visitante['EdicionesVisitante'] as $k => $edicion) {
                        $edicionesVisitante[$edicion['idEdicion']] = $edicion;
                    }
                }
                $visitante['EdicionesVisitante'] = $edicionesVisitante;
                $data[$key] = $visitante;
            }
        }
        return $data;
    }

    public function sanear_string($string) {
        $string = trim($string);
        $string = str_replace(array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string);
        $string = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string);
        $string = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string);
        $string = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string);
        $string = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string);
        $string = str_replace(array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $string);
        //Esta parte se encarga de eliminar cualquier caracter extrano
        $string = str_replace(array("¨", "º", "-", "~", "#", "@", "|", "!", "·", "$", "%", "&", "/", "(", ")", "?", "'", "¡", "¿", "[", "^", "<code>", "]", "+", "}", "{", "¨", "´", ">", "< "), '', $string);
        return $string;
    }
    
    public function getNombreComercial() {
        $qry = ' SELECT ';
        $qry .= '"idNombreComercial",';
        $qry .= '"DescripcionES"';
        $qry .= ' FROM "AE"."NombreComercial"';
        $qry .= ' ORDER BY "idNombreComercial"';
        $result = $this->SQLModel->executeQuery($qry);
        if (($result['status'] && count($result['data']) > 0)) {
            $data = Array();
            $contador = 0;
            foreach ($result['data'] as $value) {
                $data[$value['idNombreComercial']] = $value;
                $contador++;
            }
            return $data;
        } else {
            return Array("status" => FALSE, "data" => $result['status']);
        }
    }

    public function getCargo() {
        $qry = ' SELECT ';
        $qry .= '"idCargo",';
        $qry .= '"DescripcionES"';
        $qry .= ' FROM "AE"."Cargo"';
        $qry .= ' ORDER BY "idCargo"';
        $result = $this->SQLModel->executeQuery($qry);
        if (($result['status'] && count($result['data']) > 0)) {
            $data = Array();
            foreach ($result['data'] as $value) {
                $data[$value['idCargo']] = $value;
            }
            return $data;
        } else {
            return Array("status" => FALSE, "data" => $result['status']);
        }
    }
    
    public function getArea() {
        $qry = ' SELECT ';
        $qry .= '"idArea",';
        $qry .= '"DescripcionES"';
        $qry .= ' FROM "AE"."Area"';
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getAreaMultiple() {
        $qry = ' SELECT ';
        $qry .= '"idVisitanteTipo",';
        $qry .= '"VisitanteTipoES"';
        $qry .= ' FROM "AE"."VisitanteTipo"';
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }
    
    public function getStatusAutorizacion() {
        $qry = ' SELECT ';
        $qry .= '"idStatus",';
        $qry .= '"NombreStatus"';
        $qry .= ' FROM "AE"."StatusAutorizacion"';
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idStatus']] = $value;
                }
            }
            $result['data'] = array();
            $result['data'] = $data;
        }
        return $result;
    }
    
    
    public function getStatus() {
        $qry = ' SELECT ';
        $qry .= '"idStatus",';
        $qry .= '"NombreStatus"';
        $qry .= ' FROM "AE"."StatusAutorizacion"';
        $qry .= ' ORDER BY "idStatus"';
        $result = $this->SQLModel->executeQuery($qry);
        if (($result['status'] && count($result['data']) > 0)) {
            $data = Array();
            foreach ($result['data'] as $value) {
                $data[$value['idStatus']] = $value;
            }
            return $data;
        } else {
            return Array("status" => FALSE, "data" => $result['status']);
        }
    }

}
