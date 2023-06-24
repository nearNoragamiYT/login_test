<?php

namespace Visitante\DatosGeneralesBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Visitante\VisitanteBundle\Model\MainModel;

class DatosGeneralesModel extends MainModel {

    public function __construct(ContainerInterface $container = null) {
        parent::__construct($container);
    }

    public function insertEditVisitante($stringData, $idEvento, $idEdicion, $idVisitante = NULL, $cupon = NULL) {
        $qry = 'SELECT * FROM "AE"."fn_ae_InsertarEditarVisitantePreregistro"(';
        $qry .= $idEvento . ",";
        $qry .= $idEdicion . ",";
        if ($idVisitante) {
            $qry .= "$idVisitante,";
        } else {
            $qry .= "null,";
        }
        if ($cupon) {
            $qry .= "'$cupon',";
        } else {
            $qry .= "null,";
        }
        $qry .= "'$stringData'";
        $qry .= ') as "jsonVisitante"';

        $result = $this->SQLModel->executeQuery($qry);
        if ($result['status']) {
            $result['data'] = $this->decodeJSONVisitante($result['data']);
        }
        return $result;
    }

    public function set_elite($idEdicion, $idVisitante, $ClubElite) {
        $qry = 'UPDATE';
        $qry .= ' "AE"."VisitanteEdicion"';
        $qry .= ' SET';
        $qry .= ' "ClubElite" = ' . $ClubElite;
        $qry .= ' WHERE';
        $qry .= ' "idEdicion" = ' . $idEdicion;
        $qry .= ' AND "idVisitante" = ' . $idVisitante;
        
        return $result = $this->SQLModel->executeQuery($qry);
    }

    public function syncFMVisitante($visitante, $idEvento, $idEdicion) {
        $FMdata = $this->convertFieldsVisitor($visitante);
        if (isset($visitante['EdicionesVisitante'][$idEdicion])) {
            $FMdata['_id_Evento'] = $idEvento;
            $FMdata['_id_Edicion'] = $idEdicion;
        }
        $SParr = $this->createStringFM(1, $FMdata);
        return $this->insertEditVisitanteFM($SParr);
    }

    public function getVisitanteTipo() {
        $cache_name = "visitante_tipo";
        $ruta = $this->base_path_cache . $cache_name . '.json';
        if (file_exists($ruta)) {
            $result_cache = file_get_contents($ruta);
            $result = Array('status' => TRUE, 'data' => json_decode($result_cache, TRUE));
        } else {
            $qry = 'SELECT';
            $qry .= ' "idVisitanteTipo",';
            $qry .= ' "VisitanteTipoES",';
            $qry .= ' "VisitanteTipoEN"';
            $qry .= ' FROM "AE"."VisitanteTipo"';
            $qry .= ' ORDER BY "idVisitanteTipo"';
            $result = $this->SQLModel->executeQuery($qry);

            if (!$result['status']) {
                throw new \Exception($result['data'], 409);
            }
            $data = array();
            foreach ($result['data'] as $key => $value) {
                $data[$value['idVisitanteTipo']] = $value;
            }
            $this->writeJSON($ruta, $data);
            clearstatcache();
            $result['data'] = $data;
        }
        return $result;
    }

    public function getCargo() {
        $qry = ' SELECT ' . $this->getCargoFields();
        $qry .= ' FROM "AE"."Cargo" c';
        $qry .= ' ORDER BY c."idCargo"';
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idCargo']] = $value;
                }
            }
            return Array("status" => TRUE, "data" => $data);
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getCargoFields() {
        $fields = '';
        $fields .= ' c."idCargo",';
        $fields .= ' c."DescripcionEN",';
        $fields .= ' c."DescripcionES",';
        $fields .= ' c."Orden"';
        return $fields;
    }

    public function getArea() {
        $qry = ' SELECT ' . $this->getAreaFields();
        $qry .= ' FROM "AE"."Area" a';
        $qry .= ' ORDER BY a."idArea"';
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idArea']] = $value;
                }
            }
            return Array("status" => TRUE, "data" => $data);
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getAreaFields() {
        $fields = '';
        $fields .= ' a."idArea",';
        $fields .= ' a."DescripcionEN",';
        $fields .= ' a."DescripcionES",';
        $fields .= ' a."Orden"';
        return $fields;
    }

    public function getNombreComercial() {
        $qry = ' SELECT ' . $this->getNombreComercialFields();
        $qry .= ' FROM "AE"."NombreComercial" nc';
        $qry .= ' ORDER BY nc."idNombreComercial"';
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idNombreComercial']] = $value;
                }
            }
            return Array("status" => TRUE, "data" => $data);
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getNombreComercialFields() {
        $fields = '';
        $fields .= ' nc."idNombreComercial",';
        $fields .= ' nc."DescripcionEN",';
        $fields .= ' nc."DescripcionES",';
        $fields .= ' nc."Orden"';
        return $fields;
    }

}
