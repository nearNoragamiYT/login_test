<?php

namespace ShowDashboard\AE\Administracion\Encuesta\Constructor\EncuestaBundle\Model;

/**
 * Description of EncuestaModel
 *
 * @author Javier
 */
use ShowDashboard\AE\Administracion\ConfiguracionBundle\Model\ConfiguracionModel;

class EncuestaModel extends ConfiguracionModel {

    public function __construct() {
        parent::__construct();
    }

    public function getEncuesta($args = array()) {
        $fields = array("idEncuesta", "idEvento", "idEdicion", "Encabezado", "Activa");
        $order = array("idEncuesta" => "ASC");
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->selectFromTable("Encuesta", $fields, $args, $order);
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function insertEditEncuesta($data) {
        /* Si trae el id editamos, de lo contrario insertamos */
        if ($this->is_defined($data['idEncuesta'])) {
            $where = array('idEncuesta' => $data['idEncuesta']);
            unset($data['idEncuesta']);
            $this->SQLModel->setSchema("AE");
            $result = $this->SQLModel->updateFromTable('Encuesta', $data, $where, 'idEncuesta');
            $this->SQLModel->setSchema("SAS");
            return $result;
        }
        unset($data['idEncuesta']);
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->insertIntoTable('Encuesta', $data, 'idEncuesta');
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function deleteEncuesta($args) {
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->deleteFromTable('Encuesta', $args);
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

}
