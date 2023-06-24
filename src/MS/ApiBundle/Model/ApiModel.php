<?php

namespace MS\ApiBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

class ApiModel {

    function __construct() {
        $this->SQLModel = new SQLModel();
        $this->SQLModel->setSchema("MS_SL");
    }

    public function insertVisit($data = Array()) {
        $pgResponse = $this->SQLModel->insertIntoTable("ms_VisitaRawData", $data, "idVisitaRawData");
        return $pgResponse;
    }

    public function insertSearch($data = Array()) {
        $pgResponse = $this->SQLModel->insertIntoTable("ms_BusquedaRawData", $data, "idBusquedaRawData");
        return $pgResponse;
    }

    public function insertAction($data = Array()) {
        $pgResponse = $this->SQLModel->insertIntoTable("ms_AccionRawData", $data, "idAccionRawData");
        return $pgResponse;
    }

}
