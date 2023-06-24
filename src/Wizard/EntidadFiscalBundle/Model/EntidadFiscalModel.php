<?php

namespace Wizard\EntidadFiscalBundle\Model;

use Wizard\WizardBundle\Model\WizardModel;

/**
 * Description of WizardModel
 *
 * @author Javier
 */
class EntidadFiscalModel extends WizardModel {

    public function __construct() {
        parent::__construct();
    }

    public function getEntidadFiscal($args = array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("EntidadFiscal", $fields, $args, array('"idEntidadFiscal"' => 'ASC'));
    }

    public function insertEditEntidadFiscal($data) {
        /* Si trae el id editamos, de lo contrario insertamos */
        if ($this->is_defined($data['idEntidadFiscal'])) {
            $where = array('idEntidadFiscal' => $data['idEntidadFiscal']);
            unset($data['idEntidadFiscal']);
            return $this->SQLModel->updateFromTable('EntidadFiscal', $data, $where, 'idEntidadFiscal');
        }
        unset($data['idEntidadFiscal']);
        return $this->SQLModel->insertIntoTable('EntidadFiscal', $data, 'idEntidadFiscal');
    }

    public function deleteEntidadFiscal($args) {
        return $this->SQLModel->deleteFromTable('EntidadFiscal', $args);
    }

}
