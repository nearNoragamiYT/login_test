<?php

namespace ShowDashboard\AE\AdministradorTextos\TemplateBundle\Model;

/**
 * Description of EditorFormaModel
 *
 * @author Javier
 */
use ShowDashboard\AE\Administracion\ConfiguracionBundle\Model\ConfiguracionModel;

class TemplateModel extends ConfiguracionModel {

    public function __construct() {
        parent::__construct();
    }

    public function getProductoEdicion($args = array()) {
        $qry = 'SELECT ';
        $qry .= ' epi."idEdicion",';
        $qry .= ' epi."idProductoIxpo",';
        $qry .= ' pi."ProductoIxpo"';
        $qry .= ' FROM "' . $this->schema . '"."EdicionProductoIxpo" epi';
        $qry .= ' JOIN "' . $this->schema . '"."ProductoIxpo" pi';
        $qry .= ' ON epi."idProductoIxpo" = pi."idProductoIxpo"';
        $qry .= $this->buildWhere($args);
        $result = $this->SQLModel->executeQuery($qry);
        if (!($result['status'] && count($result['status']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idProductoIxpo']] = $value;
        }
        $result['data'] = $data;
        return $result;
    }

    public function getTemplate($args = array()) {
        $fields = array('idTemplate', 'idEvento', 'idEdicion', 'idProductoIxpo', 'idModuloIxpo', 'idVisitanteTipo', 'Template');
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->selectFromTable("Template", $fields, $args);
        $this->SQLModel->setSchema("SAS");
        if (!($result['status'] && count($result['status']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idTemplate']] = $value;
        }
        $result['data'] = $data;
        return $result;
    }

    public function insertEditTemplate($data) {
        $this->SQLModel->setSchema("AE");
        /* Si trae el id editamos, de lo contrario insertamos */
        if ($this->is_defined($data['idTemplate'])) {
            $where = array('idTemplate' => $data['idTemplate']);
            unset($data['idTemplate']);
            $result = $this->SQLModel->updateFromTable('Template', $data, $where, 'idTemplate');
            return $result;
        }
        unset($data['idTemplate']);
        $result = $this->SQLModel->insertIntoTable('Template', $data, 'idTemplate');
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function deleteTemplate($args) {
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->deleteFromTable('Template', $args);
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function getVisitanteTipo($args = array()) {
        $fields = array('idVisitanteTipo', 'VisitanteTipoES', 'VisitanteTipoES');
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->selectFromTable("VisitanteTipo", $fields, $args);
        $this->SQLModel->setSchema("SAS");
        if (!($result['status'] && count($result['status']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idVisitanteTipo']] = $value;
        }
        $result['data'] = $data;
        return $result;
    }

}
