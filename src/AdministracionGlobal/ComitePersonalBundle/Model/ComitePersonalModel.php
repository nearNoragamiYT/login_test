<?php

namespace AdministracionGlobal\ComitePersonalBundle\Model;

/**
 * Description of ComitePersonal
 *
 * @author Eduardo
 */
use Utilerias\SQLBundle\Model\SQLModel;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class ComitePersonalModel extends DashboardModel{

    protected $SQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getComiteOrganizador($args = array()) {
        $fields = array('idComiteOrganizador', 'ComiteOrganizador');
        return $this->SQLModel->selectFromTable("ComiteOrganizador", $fields, $args);
        }

    public function getComitePersonal($args = array()) {
        $fields = array('idContactoComiteOrganizador', 'idComiteOrganizador', 'Nombre', 'Puesto', 'Email', 'Telefono', 'RedSocial');
        $result = $this->SQLModel->selectFromTable("ContactoComiteOrganizador", $fields, $args, array('"Nombre"' => 'ASC'));
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idContactoComiteOrganizador']] = $value;
        }
        $result['data'] = $data;
        return $result;
    }

    public function addPersonal($data) {
        $values = array(
            'idComiteOrganizador' => $data['idComiteOrganizador'],
            'Nombre' => "'" . $data['Nombre'] . "'",
            'Email' => "'" . $data['Email'] . "'",
            'Telefono' => "'" . $data['Telefono'] . "'",
            'Puesto' => "'" . $data['Puesto'] . "'",
            'RedSocial' => "'" . $data['RedSocial'] . "'"
        );
        $result = $this->SQLModel->insertIntoTable("ContactoComiteOrganizador", $values, "idContactoComiteOrganizador");
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function editPersonal($data, $id) {
        $values = array(
            'Nombre' => "'" . $data['Nombre'] . "'",
            'Email' => "'" . $data['Email'] . "'",
            'Telefono' => "'" . $data['Telefono'] . "'",
            'Puesto' => "'" . $data['Puesto'] . "'",
            'RedSocial' => "'" . $data['RedSocial'] . "'"
        );
        $result = $this->SQLModel->updateFromTable("ContactoComiteOrganizador", $values, array("idContactoComiteOrganizador" => $id), "idContactoComiteOrganizador");
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function deletePersonal($id) {
        $result = $this->SQLModel->deleteFromTable("ContactoComiteOrganizador", array("idContactoComiteOrganizador" => $id));
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

}
