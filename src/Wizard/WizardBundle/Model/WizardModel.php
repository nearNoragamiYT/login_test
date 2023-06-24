<?php

namespace Wizard\WizardBundle\Model;

/**
 * Description of WizardModel
 *
 * @author Javier
 */
use Utilerias\SQLBundle\Model\SQLModel;
use LoginBundle\Model\LoginModel;

class WizardModel {

    protected $SQLModel, $schema = "SAS", $allowedExts = array('jpg', 'jpeg', 'png', 'gif'), $mb = 1048576, $path_file = 'images/logos-co/';
    public $LoginModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->LoginModel = new LoginModel();
    }

    public function lastStepCompleted($configuration) {
        if (!$configuration['ComiteOrganizador']) {
            return "wizard_informacion_general";
        }
        if (!$configuration['EntidadFiscal']) {
            return "wizard_entidad_fiscal";
        }
        if (!$configuration['Contacto']) {
            return "wizard_contacto";
        }
        if (!$configuration['Evento']) {
            return "wizard_evento";
        }
        if (!$configuration['Edicion']) {
            return "wizard_edicion";
        }
        if (!$configuration['Producto']) {
            return "wizard_producto";
        }
        /*if (!$configuration['Usuario']) {
          return "wizard_usuario";
        }*/
        return "wizard_usuario";
    }

    public function getConfiguracionInicial($args = array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("ConfiguracionInicial", $fields, $args);
    }

    public function insertEditConfiguracionInicial($data) {
        /* Si trae el id editamos, de lo contrario insertamos */
        if (!$this->is_defined($data['idComiteOrganizador'])) {
            return array('status' => TRUE, 'data' => array());
        }

        $where = array('idComiteOrganizador' => $data['idComiteOrganizador']);
        $result_conf = $this->getConfiguracionInicial($where);
        if (!$result_conf['status']) {
            return $result_conf;
        }

        /* Si no hay existe el id del comite organizador, insertamos */
        if (count($result_conf['data']) == 0) {
            return $this->SQLModel->insertIntoTable('ConfiguracionInicial', $data, 'idComiteOrganizador');
        }
        /* de lo contrario editamos comite organizador */
        return $this->SQLModel->updateFromTable('ConfiguracionInicial', $data, $where);
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
