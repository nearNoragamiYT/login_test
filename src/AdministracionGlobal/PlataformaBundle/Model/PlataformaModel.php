<?php

namespace AdministracionGlobal\PlataformaBundle\Model;

/**
 * Description of Plataforma
 *
 * @author Juan
 */
use Utilerias\SQLBundle\Model\SQLModel;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class PlataformaModel extends DashboardModel {

    protected $SQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getPlataforma($args = array()) {
        $fields = array();
        $result = $this->SQLModel->selectFromTable("PlataformaIxpo", $fields, $args, array('"idPlataformaIxpo"' => 'ASC'));
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idPlataformaIxpo']] = $value;
        }
        $result['data'] = $data;
        return $result;
    }

    public function insertPlataforma($args = array()) {
        $data = Array();
        $data['Prefijo'] = "'" . $args['Prefijo'] . "'";
        $data['PlataformaIxpo'] = "'" . $args['PlataformaIxpo'] . "'";
        $data['Ruta'] = (isset($args['Ruta'])) ? "'" . $args['Ruta'] . "'" : "";
        $res_pg = $this->SQLModel->insertIntoTable("PlataformaIxpo", $data, "idPlataformaIxpo");
        return $res_pg;
    }

    public function updatePlataforma($args = array()) {
        $data = Array();
        $data['Prefijo'] = "'" . $args['Prefijo'] . "'";
        $data['PlataformaIxpo'] = "'" . $args['PlataformaIxpo'] . "'";
        $data['Ruta'] = (isset($args['Ruta'])) ? "'" . $args['Ruta'] . "'" : "";
        $where = Array('idPlataformaIxpo' => $args['idPlataformaIxpo']);
        $res_pg = $this->SQLModel->updateFromTable("PlataformaIxpo", $data, $where, "idPlataformaIxpo");
        return $res_pg;
    }

    public function deletePlataforma($args = array()) {
        $where = Array('idPlataformaIxpo' => $args['idPlataformaIxpo']);
        $res_pg = $this->SQLModel->deleteFromTable("PlataformaIxpo", $where);
        return $res_pg;
    }

}
