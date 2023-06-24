<?php

namespace AdministracionGlobal\EntidadFiscalBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use ShowDashboard\DashboardBundle\Model\DashboardModel;
/*
 * 
 *
 */

class EntidadFiscalModel extends DashboardModel{

    protected $SQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getEntidadFiscal($args = array()) {
        $fields = array('idEntidadFiscal', 'RazonSocial', 'RFC', 'RepresentanteLegal', 'Email', 'Pais', 'Estado', 'Ciudad', 'Colonia', 'Delegacion', 'Calle', 'NumeroExterior', 'NumeroInterior', 'CodigoPostal',"idPais","idEstado");
        $result = $this->SQLModel->selectFromTable("EntidadFiscal", $fields, $args, array('"idEntidadFiscal"' => 'ASC'));
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idEntidadFiscal']] = $value;
        }
        $result['data'] = $data;
        return $result;
    }

    public function insertEntidadFiscal($args) {
        $result = $this->SQLModel->insertIntoTable("EntidadFiscal", $args, "idEntidadFiscal");
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function updateEntidadFiscal($args, $id) {
        $result = $this->SQLModel->updateFromTable("EntidadFiscal", $args, array("idEntidadFiscal" => $id), "idEntidadFiscal");
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function deleteEntidadFiscal($id) {
        $result = $this->SQLModel->deleteFromTable("EntidadFiscal", array("idEntidadFiscal" => $id));
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }
    
     public function is_defined($value) {
        if (isset($value) && !empty($value) && $value != NULL && $value != "") {
            return TRUE;
        }
        return FALSE;
    }

}
