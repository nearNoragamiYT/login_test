<?php

namespace Wizard\UsuarioBundle\Model;

use Wizard\WizardBundle\Model\WizardModel;

/**
 * Description of WizardModel
 *
 * @author Javier
 */
class UsuarioModel extends WizardModel {

    public function __construct() {
        parent::__construct();
    }

    public function getUsuariosCO() {
        $qry = 'SELECT';
        $qry .= ' c."idContactoComiteOrganizador",';
        $qry .= ' c."idComiteOrganizador",';
        $qry .= ' c."Nombre",';
        $qry .= ' c."Puesto",';
        $qry .= ' c."Email" as "EmailContacto",';
        $qry .= ' co."Staff",';
        $qry .= ' co."ComiteOrganizador",';
        $qry .= ' u."idUsuario",';
        $qry .= ' u."Email",';
        $qry .= ' u."Password",';
        $qry .= ' u."TipoUsuario",';
        $qry .= ' u."ListaAcceso"';
        $qry .= ' FROM "' . $this->schema . '"."ContactoComiteOrganizador" c';
        $qry .= ' JOIN "' . $this->schema . '"."ComiteOrganizador" co';
        $qry .= ' ON c."idComiteOrganizador"=co."idComiteOrganizador"';
        $qry .= ' LEFT JOIN "' . $this->schema . '"."Usuario" u';
        $qry .= ' ON c."idContactoComiteOrganizador"=u."idContactoComiteOrganizador"';
        
        $qry .= ' WHERE co."idComiteOrganizador" <> 1';
        return $this->SQLModel->executeQuery($qry);
    }
    
    public function getUsuario($args = array()) {
        $fields = array('idUsuario', 'idContactoComiteOrganizador', 'TipoUsuario', 'idPlantillaAcceso', 'Email', 'Password', 'TokenPassword');
        return $this->SQLModel->selectFromTable("Usuario", $fields, $args, array('"idUsuario"' => 'ASC'));
    }

    public function insertEditUsuario($data) {
        /* Si trae el id editamos, de lo contrario insertamos */
        if ($this->is_defined($data['idUsuario'])) {
            $where = array('idUsuario' => $data['idUsuario']);
            unset($data['idUsuario']);
            return $this->SQLModel->updateFromTable('Usuario', $data, $where, 'idUsuario');
        }
        unset($data['idUsuario']);
        return $this->SQLModel->insertIntoTable('Usuario', $data, 'idUsuario');
    }

    public function deleteUsuario($args) {
        return $this->SQLModel->deleteFromTable('Usuario', $args);
    }
    
}
