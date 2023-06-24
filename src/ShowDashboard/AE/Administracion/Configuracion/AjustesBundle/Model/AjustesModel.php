<?php

namespace ShowDashboard\AE\Administracion\Configuracion\AjustesBundle\Model;

/**
 * Description of EditorFormaModel
 *
 * @author Javier
 */
use ShowDashboard\AE\MainBundle\Model\MainModel;

class AjustesModel extends MainModel {

    public function __construct() {
        parent::__construct();
    }

//    public function getTextos($where) {
//        $fields = array('Etiqueta', 'Texto_ES', 'Texto_EN');
//        $result = $this->SQLModel->selectFromTable('Texto', $fields, $where);
//        if (!($result['status'] && count($result['data']) > 0)) {
//            return $result;
//        }
//        $data = array();
//
//        foreach ($result['data'] as $key => $value) {
//            $etiqueta = $value['Etiqueta'];
//            unset($value['Etiqueta']);
//            $data[$etiqueta] = $value;
//        }
//
//        $result['data'] = $data;
//        return $result;
//    }
//
//    public function insertEditTextos($data) {
//        $qry = 'SELECT "SAS"."fn_InsertaActualizaTextos"';
//        $qry .= ' (';
//        $qry .= $data['idPlataformaIxpo'] . ',';
//        $qry .= $data['idEdicion'] . ',';
//        $qry .= $data['Seccion'] . ',';
//        $qry .= "'" . $data['Etiqueta'] . "',";
//        $qry .= "'" . $data['Texto_ES'] . "',";
//        $qry .= "'" . $data['Texto_EN'] . "'";
//        $qry .= ");";
//        $result = $this->SQLModel->executeQuery($qry);
//        return $result;
//    }

    public function getColumnasConfiguracion() {
        $qry = "SELECT";
        $qry .= " COLUMN_NAME";
        $qry .= " FROM information_schema.COLUMNS";
        $qry .= " WHERE TABLE_NAME='Configuracion'";
        $qry .= " ORDER BY ordinal_position";
        $result = $this->SQLModel->executeQuery($qry);

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }

        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[] = $value['column_name'];
        }
        $result['data'] = $data;
        return $result;
    }

    public function getConfiguracion($fields = array(), $args = array()) {
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->selectFromTable('Configuracion', $fields, $args);
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function getTemplate($args = array()) {
        $this->SQLModel->setSchema("AE");
        $fields = array('idTemplate', 'Template');
        $result = $this->SQLModel->selectFromTable("Template", $fields, $args);
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function formatJSONLista($list, $field = "value") {
        if (count($list) == 0) {
            return NULL;
        }

        $data = NULL;
        foreach ($list as $value) {
            $data[$value['key']] = $value[$field];
        }

        return json_encode($data);
    }

    public function insertEditConfiguracion($data) {
        /* Si trae el id editamos, de lo contrario insertamos */
        $this->SQLModel->setSchema("AE");
        if ($data['idConfiguracion'] != "") {
            $where = array('idConfiguracion' => $data['idConfiguracion']);
            unset($data['idConfiguracion']);
            $result = $this->SQLModel->updateFromTable('Configuracion', $data, $where, 'idConfiguracion');
            $this->SQLModel->setSchema("SAS");
            return $result;
        }
        unset($data['idConfiguracion']);
        $result = $this->SQLModel->insertIntoTable('Configuracion', $data, 'idConfiguracion');
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function deleteConfiguracion($args) {
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->deleteFromTable('Configuracion', $args);
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

}
