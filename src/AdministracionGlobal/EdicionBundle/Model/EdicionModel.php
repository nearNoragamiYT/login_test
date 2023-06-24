<?php

namespace AdministracionGlobal\EdicionBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class EdicionModel extends DashboardModel{
    
    protected $SQLModel, $allowedExts = array('jpg', 'jpeg', 'png', 'gif'), $mb = 1048576, $path_file = 'images/logos-co/';
    
    public function __construct() {
        $this->SQLModel = new SQLModel();
    }
    
    public function getEdition($args = array()) {
        $fields = array();
        $fields = array(
            'idEvento', 'idEdicion', 'idComiteOrganizador',
            'Abreviatura', 'Descripcion', 'Ciudad',
            'FechaInicio', 'FechaFin', 
            'Edicion_ES',  'Edicion_EN',  'Edicion_PT', 'Edicion_FR',
            'Logo_ES_1', 'Logo_ES_2', 'Logo_ES_3',
            'Logo_EN_1', 'Logo_EN_2', 'Logo_EN_3',
            'Logo_FR_1', 'Logo_FR_2', 'Logo_FR_3', 
            'Logo_PT_1', 'Logo_PT_2', 'Logo_PT_3',
            'Slogan_ES', 'Slogan_EN', 'Slogan_PT', 'Slogan_FR',
            );
        $result = $this->SQLModel->selectFromTable("Edicion", $fields, $args, array('"idEdicion"' => 'ASC'));

        /*if (!($result['status'] && count($result['data']) <= 0)) {
            return $result;
        }*/

        if (!is_array($result['data'])) {
            return $result;
        }

        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idEdicion']] = $value;
        }
        $result['data'] = $data;
        return $result;
    }    

    public function deleteEdition($args = array()){
        $where = Array('idEdicion' => $args['idEdicion']);
        $res_pg = $this->SQLModel->deleteFromTable("Edicion", $where);
        return $res_pg;
    }

    public function insertEdition($data) {
        /* Si trae el id editamos, de lo contrario insertamos */
        if ($this->is_defined($data['idEdicion'])) {
            $where = array('idEdicion' => $data['idEdicion']);
            unset($data['idEdicion']);
            return $this->SQLModel->updateFromTable('Edicion', $data, $where, 'idEdicion');
        }
        unset($data['idEdicion']);
        return $this->SQLModel->insertIntoTable('Edicion', $data, 'idEdicion');
    }

    /* Configuracion Inicial */

    public function getConfiguracionInicial($args = array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("ConfiguracionInicial", $fields, $args);
    }
    
    /* Configuracion Inicial */

    public function formatQuoteValue($args) {
        $args_tmp = array();
        foreach ($args as $key => $value) {
            /* Si el valor tiene operadores relacionales, construimos la condicion */
            if ((is_array($value) && count($value) > 0)) {
                if (substr($value['value'], 0, 1) == "'" && substr($value['value'], -1) == "'") {
                    $value['value'] = substr($value['value'], 1, -1);
                }
                $value['value'] = (empty($value['value'])) ? "" : "'" . $value['value'] . "'";
            } else {
                if (substr($value, 0, 1) == "'" && substr($value, -1) == "'") {
                    $value = substr($value, 1, -1);
                }
                $value = (empty($value)) ? "" : "'" . $value . "'";
            }
            $args_tmp[$key] = $value;
        }
        return $args_tmp;
    }

    public function trimValues(&$post) {
        if (count($post) == 0) {
            return $post;
        }

        foreach ($post as $key => $value) {
            $post[$key] = trim($value);
        }
        return $post;
    }

    public function is_defined($value) {
        if (isset($value) && !empty($value) && $value != NULL && $value != "") {
            return TRUE;
        }
        return FALSE;
    }


    public function uploadFiles($files, $general_text, $path = "") {
        $result = array("status" => TRUE, "data" => "");
        if (count($files) == 0) {
            return $result;
        }

        $files_tmp = array();
        foreach ($files as $key => $file) {
            if (isset($file['name']) && $file['name'] != "") {
                $error = FALSE;
                /* verificamos si se puede abrir el archivo */
                if ($file["error"] > 0) {
                    $error = $general_text['sas_errorArchivo'] . ' "' . $file['name'] . '"';
                }

                /* verificamos si es válida la extension */
                $temp = explode(".", $file['name']);
                $extension = strtolower(end($temp));

                if (!in_array(strtolower($extension), $this->allowedExts)) {
                    $error = '"' . $file['name'] . '" ' . $general_text['sas_archivoInvalido'];
                }

                /* verificamos el tamaño del archivo menor a 3 MB */
                if ($file["size"] > 3 * $this->mb) {
                    $find = array('{0}', '%file%');
                    $replace = array('3', ' "' . $file['name'] . '"');
                    $error = str_replace($find, $replace, $general_text['sas_tamanoInvalido']);
                }

                /* guardamos el archivo en nuestro servidor */
                if (!move_uploaded_file($file['tmp_name'], $this->path_file . $path . basename($file['name']))) {
                    $error = $general_text['sas_errorSubirArchivo'] . ' "' . $file['name'] . '"';
                }

                /* Si hubo algun error lo regresamos */
                if ($error) {
                    $result['status'] = FALSE;
                    $result['data'] = $error;
                    return $result;
                }
                $file = Array(
                    'name' => $file['name'],
                    'tmp_name' => $file['tmp_name'],
                    'type' => $extension,
                    'field' => $key,
                );

                $files_tmp[] = $file;
            }
        }

        $result['data'] = $files_tmp;
        return $result;
    }
}