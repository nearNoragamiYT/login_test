<?php

namespace ShowDashboard\AE\Administracion\Configuracion\EstilosBundle\Model;

/**
 * Description of EditorFormaModel
 *
 * @author Javier
 */
use ShowDashboard\AE\Administracion\Configuracion\AjustesBundle\Model\AjustesModel;
use Utilerias\SQLBundle\Model\SQLModel;

class EstilosModel extends AjustesModel {

    protected $SQLModel, $allowedExts = array('jpg', 'jpeg', 'png', 'gif'), $mb = 1048576, $path_file = 'images/logos-co/';

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getTemplateTexto($args = array(), $indexTagName = FALSE) {
        $fields = array(
            'idTemplateTexto',
            'idTemplate',
            'Etiqueta',
            'Texto_ES',
            'Texto_EN',
        );
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->selectFromTable('TemplateTexto', $fields, $args);
        if (!($result['status'] && count($result['status']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            if ($indexTagName) {
                $data[$value['Etiqueta']] = $value;
            } else {
                $data[$value['idTemplateTexto']] = $value;
            }
        }
        $result['data'] = $data;
        return $result;
    }

    public function insertEditEstilos($post, $idTemplate) {
        if (count($post) == 0) {
            return array("status" => TRUE, "data" => "");
        }

        foreach ($post as $etiqueta => $valor) {
            $data = array(
                'idTemplate' => $idTemplate,
                'Etiqueta' => $etiqueta,
                'Texto_ES' => $valor,
                'Texto_EN' => $valor,
            );
            $result = $this->fn_insertEditTemplateTextos($data);
            if (!$result['status']) {
                return $result;
            }
        }

        return array("status" => TRUE, "data" => "");
    }

    public function fn_insertEditTemplateTextos($data) {
        $qry = 'SELECT "AE"."fn_ae_InsertaActualizaTemplateTexto"';
        $qry .= ' (';
        $qry .= $data['idTemplate'] . ',';
        $qry .= "'" . $data['Etiqueta'] . "',";
        $qry .= "'" . str_replace("'", "\'", $data['Texto_ES']) . "',";
        $qry .= "'" . str_replace("'", "\'", $data['Texto_EN']) . "'";
        $qry .= ");";
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

//    public function uploadFiles($files, $general_text, $path = "") {
//        $result = array("status" => TRUE, "data" => "");
//        if (count($files) == 0) {
//            return $result;
//        }
//
//        $files_tmp = array();
//        foreach ($files as $key => $file) {
//            if (isset($file['name']) && $file['name'] != "") {
//                $error = FALSE;
//                /* verificamos si se puede abrir el archivo */
//                if ($file["error"] > 0) {
//                    $error = $general_text['sas_errorArchivo'] . ' "' . $file['name'] . '"';
//                }
//
//                /* verificamos si es válida la extension */
//                $temp = explode(".", $file['name']);
//                $extension = strtolower(end($temp));
//
//                if (!in_array(strtolower($extension), $this->allowedExts)) {
//                    $error = '"' . $file['name'] . '" ' . $general_text['sas_archivoInvalido'];
//                }
//
//                /* verificamos el tamaño del archivo menor a 3 MB */
//                if ($file["size"] > 3 * $this->mb) {
//                    $find = array('{0}', '%file%');
//                    $replace = array('3', ' "' . $file['name'] . '"');
//                    $error = str_replace($find, $replace, $general_text['sas_tamanoInvalido']);
//                }
//
//                /* guardamos el archivo en nuestro servidor */
//                if (!move_uploaded_file($file['tmp_name'], $this->path_file . $path . basename($file['name']))) {
//                    $error = $general_text['sas_errorSubirArchivo'] . ' "' . $file['name'] . '"';
//                }
//
//                /* Si hubo algun error lo regresamos */
//                if ($error) {
//                    $result['status'] = FALSE;
//                    $result['data'] = $error;
//                    return $result;
//                }
//                $file = Array(
//                    'name' => $file['name'],
//                    'tmp_name' => $file['tmp_name'],
//                    'type' => $extension,
//                    'field' => $key,
//                );
//
//                $files_tmp[] = $file;
//            }
//        }
//
//        $result['data'] = $files_tmp;
//        return $result;
//    }
}
