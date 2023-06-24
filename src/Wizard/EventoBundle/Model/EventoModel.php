<?php

namespace Wizard\EventoBundle\Model;

use Wizard\WizardBundle\Model\WizardModel;

/**
 * Description of WizardModel
 *
 * @author Javier
 */
class EventoModel extends WizardModel {

    public function __construct() {
        parent::__construct();
    }

    public function getEvento($args = array()) {
        $fields = array('idEvento', 'idComiteOrganizador', 'Evento_ES', 'Evento_EN', 'Evento_FR', 'Evento_PT');
        return $this->SQLModel->selectFromTable("Evento", $fields, $args, array('"idEvento"' => 'ASC'));
    }

    public function insertEditEvento($data) {
        /* Si trae el id editamos, de lo contrario insertamos */
        if ($this->is_defined($data['idEvento'])) {
            $where = array('idEvento' => $data['idEvento']);
            unset($data['idEvento']);
            return $this->SQLModel->updateFromTable('Evento', $data, $where, 'idEvento');
        }
        unset($data['idEvento']);
        return $this->SQLModel->insertIntoTable('Evento', $data, 'idEvento');
    }

    public function deleteEvento($args) {
        return $this->SQLModel->deleteFromTable('Evento', $args);
    }

}
