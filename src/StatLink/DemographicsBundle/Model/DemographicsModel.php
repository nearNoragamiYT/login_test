<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 * 
 * COMENTARIOS IMPORTANTES
 * Ajuste los parametros en la función getConexionDG, para trabajar en entorno Producción o Desarrollo
 */

namespace StatLink\DemographicsBundle\Model;

use ShowDashboard\DashboardBundle\Model\DashboardModel;
use Utilerias\SQLBundle\Model\SQLModel;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class DemographicsModel extends DashboardModel {

    //put your code here
    protected $SQLModelDG, $schemaDG = "Demograficos", $DBData, $PGSQLClient;
    //Ruta del Directorio de Archivos .xlsx
    const path_dg_file = '../var/docs/';

public function __construct() {
        parent::__construct();
        $this->setDBParam();
        $this->SQLModelDG = new SQLModel($this->getDBData());
        //$this->PGSQLClient = new PGSQLClient();
        //$this->SQLModelDG->setSchema("AE");   
    }
    
    //Parametros de la base ServidorDemograficos
    protected function getConexionDG() {
        $conection_data = array(
            //HAbilitar para localhost
            'Servidor' => '10.8.0.1',
            /*//Habilitar para producción
            'Servidor' => 'localhost',*/
            'Puerto' => '5432',
            'Base' => 'ServidorDemograficos',
            'Usuario' => 'php_ServidorDemograficos',
            'Password' => '3GHjuo7_l1PHfT1V'
        );
        return $conection_data;
    }
    
    
    public function getEncuesta($args){
        $qry ='SELECT "idEncuesta" FROM "AE"."Encuesta"'  ;
        $qry .= ' WHERE "idEvento" = ' . $args['idEvento'] .' AND "idEdicion"='.$args['idEdicion'];
        $result = $this->SQLModel->executeQuery($qry);    
            if (isset($result['status']) && $result['status'] == 1) {
                return $result['data'];   
            }else{
                return Array("status" => FALSE, "data" => $result_pg['status']);
            }    
    }
    
    public function getTotalRegistros($args){
        $qry ='SELECT COUNT("idVisitante") FROM "DEMOGRAFICOS"."Demograficos_'.$args['idEvento'].'_'.$args['idEdicion'].'_'.$args['idEncuesta'].'"';
        $result = $this->SQLModel->executeQuery($qry); 
            if (isset($result['status']) && $result['status'] == 1) {
                return $result['data'];   
            }else{
                return Array("status" => FALSE, "data" => $result_pg['status']);
            }           
    }
        public function getSolicitudes($args) {
        $qry = 'SELECT * FROM "DEMOGRAFICOS".fn_ae_getSolicitud_1(' . $args['idEvento'] . ',' . $args['idEdicion'] . ',' . $args['idEncuesta'] . ',\'' . $args['db_name'] . '\')';
        $result = $this->SQLModelDG->executeQuery($qry);
        
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $descripcion = $value['descripcion'];
                    $solicitud = $value['idSolicitud'];                    
                    $filename = $descripcion . "_demograficos_" . $solicitud . ".xlsx";
                    $value['file_name'] = $filename;
                    
                    //recorta milisegundos en string fecha
                    $dt= $value['fechaCreacion'];                                                               
                    $date = substr($dt,0,-7);                                      
                    $value['fechaCreacion']=$date;
                    
                    $ruta = self::path_dg_file . $filename;

                    if (file_exists($ruta)) {
                        //unlink($ruta);     
                        if ($value['status'] == "3") {
                            $filesize = filesize($ruta);
                            $filesize = $this->FileSizeConvert($filesize);
                            $value['file_size'] = $filesize;
                        } else {
                            $filesize = "";
                            $value['file_size'] = $this->FileSizeConvert($filesize);;
                        }
                    } else {
                        $filesize = "";
                        $value['file_size'] = $this->FileSizeConvert($filesize);;
                    }
                   
                    $data[$value['idSolicitud']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
        //return $result;         
    }
    
    public function getSolicitud($args) {
        $qry = 'SELECT * FROM "DEMOGRAFICOS".fn_ae_getSolicitud(' . $args['idEvento'] . ',' . $args['idEdicion'] . ',\'' . $args['db_name'] . '\')';
        $result = $this->SQLModelDG->executeQuery($qry);
        
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $descripcion = $value['descripcion'];
                    $solicitud = $value['idSolicitud'];                    
                    $filename = $descripcion . "_demograficos_" . $solicitud . ".xlsx";
                    $value['file_name'] = $filename;
                    
                    //recorta milisegundos en string fecha
                    $dt= $value['fechaCreacion'];                                                               
                    $date = substr($dt,0,-7);                                      
                    $value['fechaCreacion']=$date;
                    
                    $ruta = self::path_dg_file . $filename;

                    if (file_exists($ruta)) {
                        //unlink($ruta);     
                        if ($value['status'] == "3") {
                            $filesize = filesize($ruta);
                            $filesize = $this->FileSizeConvert($filesize);
                            $value['file_size'] = $filesize;
                        } else {
                            $filesize = "";
                            $value['file_size'] = $this->FileSizeConvert($filesize);;
                        }
                    } else {
                        $filesize = "";
                        $value['file_size'] = $this->FileSizeConvert($filesize);;
                    }
                   
                    $data[$value['idSolicitud']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
        //return $result;         
    }

    public function getParameters() {
        $db_prefix = "PGSQL_SAS";
        $this->container = new ContainerBuilder();
        $loader = new YamlFileLoader($this->container, new FileLocator(__DIR__ . "/../../../../app/config"));
        $loader->load('parameters.yml');
        $db_data = $this->container->getParameter($db_prefix);

        return $db_data;
    }

    private function setDBParam() {
        $result_db = $this->getConexionDG();
        $data = $result_db;
        $db_data = array(
            'db_name' => $data['Base'],
            'db_user' => $data['Usuario'],
            'db_password' => $data['Password'],
            'db_server' => $data['Servidor'],
            'db_port' => $data['Puerto'],
        );
        $this->setDBData($db_data);
    }
    
    public function setDBData($data) {
        $this->DBData = $data;
    }

    public function getDBData() {
        return $this->DBData;
    }
    
    public function generateSolicitudesDG($args){
        $qry = 'SELECT * FROM "DEMOGRAFICOS".fn_ae_solicitud_1(' . $args['idEvento'] . ',' . $args['idEdicion'] . ',' . $args['idEncuesta']. ',\'' . $args['db_name'] . '\')';
        $result = $this->SQLModelDG->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result;   
        }else{
            return Array("status" => FALSE, "data" => $result_pg['status']);
        }        
        
    }
    
    public function generateSolicitudDG($args){
        $qry = 'SELECT * FROM "DEMOGRAFICOS".fn_ae_solicitud(' . $args['idEvento'] . ',' . $args['idEdicion'] . ',\'' . $args['db_name'] . '\')';
        $result = $this->SQLModelDG->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result;   
        }else{
            return Array("status" => FALSE, "data" => $result_pg['status']);
        }        
        
    }
    
    public function getDemograficosMetaData($solicitudes) {

        $file_names = Array();
        foreach ($solicitudes as $key => $value) {
            $file_names[$key] = $value["file_name"];
        }
//        print_r($file_names);
//        die('x_x');

        $file_size = Array();
        foreach ($solicitudes as $key => $value) {
            $file_size[$key] = $value["file_size"];
        }
//        print_r($solicitudes);
//        die('x_x');
        return Array(
            "id_solicitud" => Array(
                'file_name' => 1,
                'text' => "#",
                //'values' => $file_names, 
                'help-lb' => "",
                //'class' => 'sorting_desc',
                'filter_options' => Array(),
                'is_visible' => TRUE,
            ),
            "file_name" => Array(
                'file_name' => 1,
                'text' => "Archivo",
                //'values' => $file_names, 
                'help-lb' => "",
                'filter_options' => Array(),
                'is_visible' => TRUE,
            ),
            "file_size" => Array(
                'category_id' => 1,
                'text' => "Tamaño",
                //'values' => $campos2,                    
                'help-lb' => "",
                'filter_options' => Array(
//                    'is_optional_column' => TRUE,
//                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "date" => Array(
                'category_id' => 1,
                'text' => "Fecha de Creación",
                'help-lb' => "",
                'filter_options' => Array(
//                    'is_optional_column' => TRUE,
//                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "status" => Array(
                'category_id' => 1,
                'text' => "Estatus de Solicitud",
                'values' => Array("1" => "Pendiente", "2" => "Procesando", "3" => "Terminado", "4" => "Error"),
                'help-lb' => "",
                'filter_options' => Array(
//                    'is_optional_column' => TRUE,
//                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "download" => Array(
                'category_id' => 1,
                'text' => "Descarga",
                //'values' => $categorias2,                
                'help-lb' => "",
                'filter_options' => Array(
//                    'is_optional_column' => TRUE,
//                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            )
        );
    }

    function FileSizeConvert($bytes) {
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach ($arBytes as $arItem) {
            if ($bytes >= $arItem["VALUE"]) {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", ",", strval(round($result, 2))) . " " . $arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

}
