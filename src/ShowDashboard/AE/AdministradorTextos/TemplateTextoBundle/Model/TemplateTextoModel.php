<?php

namespace ShowDashboard\AE\AdministradorTextos\TemplateTextoBundle\Model;

/**
 * Description of EditorFormaModel
 *
 * @author Javier
 */
use ShowDashboard\AE\Administracion\Configuracion\AjustesBundle\Model\AjustesModel;

class TemplateTextoModel extends AjustesModel {

    public function __construct() {
        parent::__construct();
    }

    public function getTemplateTexto($args = array(), $indexTagName = FALSE) {
        $qry = 'SELECT';
        $qry .= ' tt."idTemplateTexto",';
        $qry .= ' tt."idTemplate",';
        $qry .= ' tt."Etiqueta",';
        $qry .= ' tt."Texto_ES",';
        $qry .= ' tt."Texto_EN",';
        $qry .= ' tt."Default"';
        $qry .= ' FROM "AE"."TemplateTexto" tt';
        $qry .= ' JOIN "AE"."Template" t';
        $qry .= ' ON tt."idTemplate" = t."idTemplate"';
        $qry .= $this->buildWhere($args);
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->executeQuery($qry);
        $this->SQLModel->setSchema("SAS");
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

    public function insertEditTemplateTexto($data) {
        $this->SQLModel->setSchema("AE");
        /* Si trae el id editamos, de lo contrario insertamos */
        if ($this->is_defined($data['idTemplateTexto'])) {
            $where = array('idTemplateTexto' => $data['idTemplateTexto']);
            unset($data['idTemplateTexto']);
            $result = $this->SQLModel->updateFromTable('TemplateTexto', $data, $where, 'idTemplateTexto');
            $this->SQLModel->setSchema("SAS");
            return $result;
        }
        unset($data['idTemplateTexto']);
        $result = $this->SQLModel->insertIntoTable('TemplateTexto', $data, 'idTemplateTexto');
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function fn_insertEditTemplateTextos($data) {
        $qry = 'SELECT "AE"."fn_ae_InsertaActualizaTemplateTexto"';
        $qry .= ' (';
        $qry .= $data['idTemplate'] . ',';
        $qry .= "'" . $data['Etiqueta'] . "',";
        $qry .= "'" . str_replace("'", "\'", $data['Texto_ES']) . "',";
        $qry .= "'" . str_replace("'", "\'", $data['Texto_EN']) . "'";
        $qry .= ");";
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->executeQuery($qry);
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function deleteTemplateTexto($args) {
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->deleteFromTable('TemplateTexto', $args);
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

}
