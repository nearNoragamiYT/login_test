<?php

namespace WebService\RestBundle\Model;

use WebService\RestBundle\Model\mainModel;

class PeccWSModel extends mainModel {

    public function __construct($idEvento = 1, $idEdicion = 1) {
        parent::__construct($idEvento, $idEdicion);
    }

    public function getPecc() {
        $cache_name = "pecc";
        $ruta = $this->base_path_cache . $cache_name . '.json';
        if (file_exists($ruta)) {
            $result_cache = file_get_contents($ruta);
            $result = Array('status' => true, 'data' => json_decode($result_cache, TRUE));
        } else {

            $pais = $this->getPais();
            if (!$pais['status']) {
                return array('status' => false, 'mensaje' => $pais['data']);
            }

            $estado = $this->getEstado();
            if (!$estado['status']) {
                return array('status' => false, 'mensaje' => $estado['data']);
            }
            
//            $ciudad = $this->getCiudad();
//            if (!$ciudad['status']) {
//                return array('status' => false, 'mensaje' => $ciudad['data']);
//            }
//
//            $colonia = $this->getColonia();
//            if (!$colonia['status']) {
//                return array('status' => false, 'mensaje' => $colonia['data']);
//            }

            $pecc = array('Pais' => $pais['data'], 'Estado' => $estado['data']);
            $this->writeJSON($ruta, $pecc);
            clearstatcache();
            $result = Array('status' => true, 'data' => $pecc);
        }

        return $result;
    }

    public function getPais() {
        $qry = 'SELECT ';
        $qry .= $this->getFieldsPais();
        $qry .= 'FROM "PECC"."Pais" ';
        $qry .= 'ORDER BY "idPais"';
        $result = $this->PGModelSAS->execQueryString($qry);
        return $result;
    }

    public function getFieldsPais() {
        $fileds = '"idPais" AS Pais_id, ';
        $fileds .= '"CodigoTelefonoInt" AS Codigo_Telefonico, ';
        $fileds .= '"Pais_EN" AS Pais ';
        return $fileds;
    }

    public function getEstado() {
        $qry = 'SELECT ';
        $qry .= $this->getFieldsEstado();
        $qry .= 'FROM "PECC"."Estado" ';
        $qry .= 'ORDER BY "idEstado"';
        $result = $this->PGModelSAS->execQueryString($qry);
        return $result;
    }

    public function getFieldsEstado() {
        $fileds = '"idEstado" AS Estado_id, ';
        $fileds .= '"idPais" AS Pais_id, ';
        $fileds .= '"Estado" ';
        return $fileds;
    }

    public function getCiudad() {
        $qry = 'SELECT ';
        $qry .= $this->getFieldsCiudad();
        $qry .= 'FROM "PECC"."Ciudad" ';
        $result = $this->PGModelSAS->execQueryString($qry);
        return $result;
    }

    public function getFieldsCiudad() {
        $fileds = '"idCiudad" AS Ciudad_id, ';
        $fileds .= '"Ciudad" ';
        return $fileds;
    }

    public function getColonia() {
        $qry = 'SELECT ';
        $qry .= $this->getFieldsColonia();
        $qry .= 'FROM "PECC"."Colonia" ';
        $result = $this->PGModelSAS->execQueryString($qry);
        return $result;
    }

    public function getFieldsColonia() {
        $fileds = '"idColoniaCP" AS Colonia_id, ';
        $fileds .= '"Colonia", ';
        $fileds .= '"CP" AS Codigo_Postal ';
        return $fileds;
    }

}
