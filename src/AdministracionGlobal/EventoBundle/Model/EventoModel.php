<?php

namespace AdministracionGlobal\EventoBundle\Model;

/**
 * Description of EventoModel
 *
 * @author Eric
 */
use Utilerias\SQLBundle\Model\SQLModel;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class EventoModel extends DashboardModel{

    protected $SQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getEvento($args = array()) {
        $fields = array();#array('idEvento', 'Evento', 'Logo', 'Licencias');
        $result = $this->SQLModel->selectFromTable("Evento", $fields, $args, array('"idEvento"' => 'ASC'));

        if (!is_array($result['data'])) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idEvento']] = $value;
        }
        $result['data'] = $data;
        return $result;
    }
    
    public function insertEvento($args = array()) {
        $data = Array(
                'idComiteOrganizador' => $args['idComiteOrganizador'],
                'Evento_ES' => "'" . $args['Evento_ES'] . "'",
                'Evento_EN' => "'" . $args['Evento_EN'] . "'",
                'Evento_PT' => "'" . $args['Evento_PT'] . "'",
                'Evento_FR' => "'" . $args['Evento_FR'] . "'",
            );
        $res_pg = $this->SQLModel->insertIntoTable("Evento", $data, "idEvento");
        return $res_pg;
    }
    
     public function updateEvento($args = array()) {
        $data = Array(
            'Evento_ES' => "'" . $args['Evento_ES'] . "'",
            'Evento_EN' => "'" . $args['Evento_EN'] . "'",
            'Evento_PT' => "'" . $args['Evento_PT'] . "'",
            'Evento_FR' => "'" . $args['Evento_FR'] . "'",
        );
        $where = Array('idEvento' => $args['idEvento']);
        $res_pg = $this->SQLModel->updateFromTable("Evento", $data, $where, "idEvento");
        return $res_pg;
    }

    public function deleteEvento($args = array()) {
        $where = Array('idEvento' => $args['idEvento']);
        $res_pg = $this->SQLModel->deleteFromTable("Evento", $where);
        return $res_pg;
    }

}
