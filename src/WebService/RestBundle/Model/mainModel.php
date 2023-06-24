<?php

namespace WebService\RestBundle\Model;

use Utilerias\PostgreSQLBundle\v9\PGSQLClient;

class mainModel {

    protected $PGModelSAS, $PGModelAE, $FM_Client;
    protected $base_path_cache = '../var/cache/web_service/';

    function __construct($idEvento, $idEdicion) {
        $this->PGModelSAS = new PGSQLClient();
//        $this->setDBParam(array("idEvento" => $idEvento, "idEdicion" => $idEdicion));
//        $this->PGModelAE = new PGSQLClient($this->getDBData());

    }

    private function setDBParam($where) {
        $result_db = $this->getConexion($where);
        if ($result_db['status'] && count($result_db['data']) > 0) {
            $data = $result_db['data'];
            $db_data = array(
                'db_name' => $data[0]['Base'],
                'db_user' => $data[0]['Usuario'],
                'db_password' => $data[0]['Password'],
//                'db_server' => $data[0]['Servidor'],
                'db_server' => '10.8.0.1',
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
            $result = $this->getQryConexion($where);

            if (!$result['status']) {
                throw new \Exception($result['data'], 409);
            }

            if (count($result['data']) == 0) {
                throw new \Exception('No se encontraron los parametros de conexion');
            }
            
            $result['data'][0]['FmConexion'] = json_decode($result['data'][0]['FmConexion'], TRUE);
            $this->writeJSON($ruta, $result['data']);
            clearstatcache();
        }
        return $result;
    }

    protected function getQryConexion($where) {
        $qry = 'SELECT';
        $qry .= $this->getConexionFields();
        $qry .= ' FROM "SAS"."Conexion"';
        $qry .= ' WHERE "idEdicion" =' . $where['idEdicion'] . ' AND "idEvento" = ' . $where['idEvento'];
        $result = $this->PGModelSAS->execQueryString($qry);        
        return $result;        
    }

    protected function getConexionFields() {
        $qry = ' "Base",';
        $qry .= ' "Usuario",';
        $qry .= ' "Password",';
        $qry .= ' "Servidor",';
        $qry .= ' "Puerto",';
        $qry .= ' "FmConexion"';
        return $qry;
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

    public function writeJSON($filename, $array) {
        $json = json_encode($array);
        $fp = fopen($filename, "w");
        fwrite($fp, $json);
        fclose($fp);
        chmod($filename, 0777);
    }

}
