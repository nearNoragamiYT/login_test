<?php

namespace Wizard\ContactoBundle\Model;

use Wizard\WizardBundle\Model\WizardModel;

/**
 * Description of WizardModel
 *
 * @author Javier
 */
class ContactoModel extends WizardModel {

    public function __construct() {
        parent::__construct();
    }

    public function getContacto($args = array()) {
        $fields = array(
            'idContactoComiteOrganizador',
            'idComiteOrganizador',
            'Nombre',
            'Puesto',
            'Email',
            'Telefono',
            'RedSocial',
        );
        return $this->SQLModel->selectFromTable("ContactoComiteOrganizador", $fields, $args, array('"idContactoComiteOrganizador"' => 'ASC'));
    }

    public function getContactosCO($idComiteOrganizador = "") {
        $qry = 'SELECT';
        $qry .= ' c."idContactoComiteOrganizador",';
        $qry .= ' c."idComiteOrganizador",';
        $qry .= ' c."Nombre",';
        $qry .= ' c."Puesto",';
        $qry .= ' c."Email",';
        $qry .= ' c."Telefono",';
        $qry .= ' c."RedSocial",';
        $qry .= ' co."ComiteOrganizador",';
        $qry .= ' co."Logo"';
        $qry .= ' FROM "' . $this->schema . '"."ContactoComiteOrganizador" c';
        $qry .= ' JOIN "' . $this->schema . '"."ComiteOrganizador" co';
        $qry .= ' ON c."idComiteOrganizador"=co."idComiteOrganizador"';
        $qry .= ' WHERE co."idComiteOrganizador"=' . $idComiteOrganizador;
        return $this->SQLModel->executeQuery($qry);
    }

    public function insertEditContacto($data) {
        /* Si trae el id editamos, de lo contrario insertamos */
        if ($this->is_defined($data['idContactoComiteOrganizador'])) {
            $where = array('idContactoComiteOrganizador' => $data['idContactoComiteOrganizador']);
            unset($data['idContactoComiteOrganizador']);
            return $this->SQLModel->updateFromTable('ContactoComiteOrganizador', $data, $where, 'idContactoComiteOrganizador');
        }
        unset($data['idContactoComiteOrganizador']);
        return $this->SQLModel->insertIntoTable('ContactoComiteOrganizador', $data, 'idContactoComiteOrganizador');
    }

    public function deleteContacto($args) {
        return $this->SQLModel->deleteFromTable('ContactoComiteOrganizador', $args);
    }

}
