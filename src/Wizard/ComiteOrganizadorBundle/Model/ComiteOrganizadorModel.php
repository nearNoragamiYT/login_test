<?php

namespace Wizard\ComiteOrganizadorBundle\Model;

use Wizard\WizardBundle\Model\WizardModel;

/**
 * Description of WizardModel
 *
 * @author Javier
 */
class ComiteOrganizadorModel extends WizardModel {

    public function __construct() {
        parent::__construct();
    }

    public function getComiteOrganizador($args = array()) {
        $fields = array('idComiteOrganizador', 'ComiteOrganizador', 'Logo', 'Licencias');
        return $this->SQLModel->selectFromTable("ComiteOrganizador", $fields, $args, array('"idComiteOrganizador"' => 'ASC'));
    }

    public function insertEditComiteOrganizador($data) {
        /* Si trae el id editamos, de lo contrario insertamos */
        if ($this->is_defined($data['idComiteOrganizador'])) {
            $where = array('idComiteOrganizador' => $data['idComiteOrganizador']);
            unset($data['idComiteOrganizador']);
            return $this->SQLModel->updateFromTable('ComiteOrganizador', $data, $where, 'idComiteOrganizador');
        }
        unset($data['idComiteOrganizador']);
        return $this->SQLModel->insertIntoTable('ComiteOrganizador', $data, 'idComiteOrganizador');
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
