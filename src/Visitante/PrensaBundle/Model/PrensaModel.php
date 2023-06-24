<?php

namespace Visitante\PrensaBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Visitante\VisitanteBundle\Model\MainModel;
use Utilerias\SQLBundle\Model\SQLModel;

class PrensaModel extends MainModel {

    public $SQLModel, $PGSQLModel;

    public function __construct(ContainerInterface $container = null) {
        parent::__construct($container);
        $this->SQLModel = new SQLModel();
    }

    public function getNombreComercial() {
        $qry = ' SELECT ';
        $qry .= '"idNombreComercial",';
        $qry .= '"DescripcionES"';
        $qry .= ' FROM "AE"."NombreComercial"';
        $qry .= ' ORDER BY "idNombreComercial"';
        $result = $this->SQLModel->executeQuery($qry);
        if (($result['status'] && count($result['data']) > 0)) {
            $data = Array();
            $contador = 0;
            foreach ($result['data'] as $value) {
                $data[$value['idNombreComercial']] = $value;
                $contador++;
            }
            return $data;
        } else {
            return Array("status" => FALSE, "data" => $result['status']);
        }
    }

    public function getCargo() {
        $qry = ' SELECT ';
        $qry .= '"idCargo",';
        $qry .= '"DescripcionES"';
        $qry .= ' FROM "AE"."Cargo"';
        $qry .= ' ORDER BY "idCargo"';
        $result = $this->SQLModel->executeQuery($qry);
        if (($result['status'] && count($result['data']) > 0)) {
            $data = Array();
            foreach ($result['data'] as $value) {
                $data[$value['idCargo']] = $value;
            }
            return $data;
        } else {
            return Array("status" => FALSE, "data" => $result['status']);
        }
    }

    public function getPrensa($args) {
        $qry = ' SELECT ';
        $qry .= '"idVisitante",';
        $qry .= '"Email",';
        $qry .= '"Nombre",';
        $qry .= '"ApellidoPaterno",';
        $qry .= '"ApellidoMaterno",';
        $qry .= '"Telefono",';
        $qry .= '"NombreComercial",';
        $qry .= '"DE_Cargo",';
        $qry .= '"Area",';
        $qry .= '"Ciudad",';
        $qry .= '"Estado",';
        $qry .= '"Pais"';
        $qry .= ' FROM "AE"."Visitante"';
        $qry .= ' WHERE "Prensa"=1 ; ';
        $result = $this->SQLModel->executeQuery($qry);

        return $result;
    }

    public function updateStatusPrensa($args) {
        $qry = 'SELECT * FROM "AE"."fn_ae_InsertarVisitanteNoAutorizado_sas"(' . $args['idVisitanteNoAutorizado'] . ', ' . $args['idStatusAutorizado'] . ', ' . $args['idEdicion'] . ', ' . $args['idEvento'] . ', ' . "'" . $args['MotivoRechazo'] . "'" . ');';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function getCount($where = '', $idEdicion) {
        $qry = ' SELECT COUNT("Email") FROM "AE"."vw_ae_VisitantePrensa_temp" WHERE "idEdicion" =' . $idEdicion;
        $result = $this->SQLModel->executeQuery($qry);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function getListidVisitantes($where = '', $idEdicion = '') {
        $qry = ' SELECT ';
        $qry .= ' "idVisitante",';
        $qry .= ' "idVisitanteNoAutorizado"';
        $qry .= ' FROM';
        $qry .= ' "AE"."vw_ae_VisitantePrensa_temp"';
        $qry .= $where;
        if ($where != "") {
            $qry .= ' AND "Comprador"= 0 AND "idEdicion" =' . $idEdicion;
        } else {
            $qry .= ' WHERE "Comprador"=0 AND "idEdicion" =' . $idEdicion;
        }
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $flag = "";
                    $idVisitante = $value['idVisitante'];
                    $idVisitanteNoAutorizado = $value['idVisitanteNoAutorizado'];
                    if ($idVisitante == "" && $idVisitanteNoAutorizado != "") {

                        $flag = "0-" . $idVisitanteNoAutorizado;
                    } else if ($idVisitanteNoAutorizado == "" && $idVisitante != "") {
                        $flag = $idVisitante . "-0";
                    } else {
                        $flag = $idVisitante . "-" . $idVisitanteNoAutorizado;
                    }
                    $data[$flag] = $value;
                }
            }
            $result['data'] = array();
            $result['data'] = $data;
        }
        return $result;
    }

    public function getVisitantes($where, $order, $param, $idEdicion = '') {
        $qry = ' SELECT ';
        $qry .= ' "idVisitanteNoAutorizado",';
        $qry .= ' "idVisitante",';
        $qry .= ' "NombreCompleto",';
        $qry .= ' "Email",';
        $qry .= ' "NombreComercial",';
        $qry .= ' "DE_Cargo",';
        $qry .= ' "FechaPreregistro",';
        $qry .= ' "NombreStatus",';
        $qry .= ' "NumeroEnvios",';
        $qry .= ' "NumeroDescargas",';
        $qry .= '"idStatus" ';
        $qry .= ' FROM';
        $qry .= ' "AE"."vw_ae_VisitantePrensa_temp"';
        $qry .= $where;
        if ($where != "") {
            $qry .= ' AND "idEdicion" =' . $idEdicion;
        } else {
            $qry .= ' WHERE  "idEdicion" =' . $idEdicion;
        }

        if ($order == "") {
            $order = '"idVisitante" ASC ';
        }
        $qry .= ' ORDER BY ' . $order;
        $qry .= ' LIMIT ' . $param['length'];
        $qry .= ' OFFSET ' . $param['start'];
        $result = $this->SQLModel->executeQuery($qry);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function updateStatusPrensaList($args) {
        $qry = 'SELECT * FROM "AE"."fn_ae_InsertarActualizarVisitanteNoAutorizadoPrensa"(' . "'" . $args . "'" . ');';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function setVisitanteDataWS($param) {
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->insertIntoTable('SyncData', $param, "idSyncData");
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function getVisitantesExport($where, $order, $idEdicion) {
        $qry = ' SELECT ';
        $qry .= ' "idVisitante",';
        $qry .= ' "NombreCompleto",';
        $qry .= ' "Email",';
        $qry .= ' "NombreComercial",';
        $qry .= ' "DE_Cargo",';
        $qry .= ' "FechaPreregistro",';
        $qry .= ' "NombreStatus",';
        $qry .= ' "NumeroEnvios",';
        $qry .= ' "NumeroDescargas"';
        $qry .= ' FROM';
        $qry .= ' "AE"."vw_ae_VisitantePrensa_temp"';
        if ($where != "") {
            $qry .= ' AND "idEdicion" =' . $idEdicion;
        } else {
            $qry .= ' WHERE  "idEdicion" =' . $idEdicion;
        }
//        $qry .= $where;
        if ($order == "") {
            $order = '"NombreCompleto" ASC ';
        }
        $qry .= ' ORDER BY ' . $order;
        $result = $this->SQLModel->executeQuery($qry);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function completeID($id, $length = 6) {
        $i = strlen($id);
        $compl = '';
        for (; $i < $length; $i++) {
            $compl .= '0';
        }
        return $compl . $id;
    }

    public function getVisitanteDG($idVisitante) {
        $qry = 'SELECT ';
        $qry .= 'vis."idVisitante", ';
        $qry .= 'vis."Nombre", ';
        $qry .= 'vis."ApellidoPaterno", ';
        $qry .= 'vis."ApellidoMaterno", ';
        $qry .= 'vis."NombreCompleto", ';
        $qry .= 'vis."DE_Cargo", ';
        $qry .= 'vis."DE_RazonSocial", ';
        $qry .= 'vis."idVisitanteTipo", ';
        $qry .= 'vis."Email", ';
        $qry .= 'vistip."VisitanteTipoES", ';
        $qry .= 'vis."FechaAlta", ';
        $qry .= 'CASE WHEN vis."Prensa" =  1 THEN \'Prensa\' END AS "CategoriaPrensa" ';
        $qry .= 'FROM "AE"."Visitante" vis INNER JOIN "AE"."VisitanteTipo" vistip ON vis."idVisitanteTipo" = vistip."idVisitanteTipo" ';
        $qry .= 'WHERE "idVisitante" = ' . $idVisitante;
        $result = $this->SQLModel->executeQuery($qry);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function getStatus() {
        $qry = ' SELECT ';
        $qry .= '"idStatus",';
        $qry .= '"NombreStatus"';
        $qry .= ' FROM "AE"."StatusAutorizacion"';
        $qry .= ' ORDER BY "idStatus"';
        $result = $this->SQLModel->executeQuery($qry);
        if (($result['status'] && count($result['data']) > 0)) {
            $data = Array();
            foreach ($result['data'] as $value) {
                $data[$value['idStatus']] = $value;
            }
            return $data;
        } else {
            return Array("status" => FALSE, "data" => $result['status']);
        }
    }

    public function updateDowloadLog($idEvento, $idEdicion, $idVisitante, $accion) {
        $qry = 'SELECT * FROM "AE"."fn_ae_LogEnvioGafete"(' . $idEvento . ',' . $idEdicion . ', ' . $idVisitante . ', ' . $accion . ');';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

}