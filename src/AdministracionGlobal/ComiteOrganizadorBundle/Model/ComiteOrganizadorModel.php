<?php

namespace AdministracionGlobal\ComiteOrganizadorBundle\Model;

/**
 * Description of ComiteOrganizador
 *
 * @author Javier
 */
use Utilerias\SQLBundle\Model\SQLModel;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class ComiteOrganizadorModel extends DashboardModel{

    protected $SQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getComiteOrganizador($args = array()) {
        $fields = array('idComiteOrganizador', 'ComiteOrganizador', 'Logo', 'Licencias', 'Staff');
        $result = $this->SQLModel->selectFromTable("ComiteOrganizador", $fields, $args, array('"idComiteOrganizador"' => 'ASC'));

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idComiteOrganizador']] = $value;
        }
        $result['data'] = $data;
        return $result;
    }
    
    public function insertEditComiteOrganizador($data) {
        /* Si trae el id editamos, de lo contrario insertamos */
        if ($this->is_defined($data['idComiteOrganizador'])) {
            $where = array('idComiteOrganizador' => $data['idComiteOrganizador']);
            unset($data['idComiteOrganizador']);
            return $this->SQLModel->updateFromTable('ComiteOrganizador', $data, $where, 'idComiteOrganizador');
        }
        unset($data['idComiteOrganizador']);
        return $this->SQLModel->insertIntoTable('ComiteOrganizador', $data, 'idComiteOrganizador');
    }
    
    public function deleteCO($args) {
        return $this->SQLModel->deleteFromTable('ComiteOrganizador', $args);
    }

}
