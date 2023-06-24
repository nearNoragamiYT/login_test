<?php

namespace Visitante\AsociadoBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Visitante\VisitanteBundle\Model\MainModel;
use Utilerias\SQLBundle\Model\SQLModel;

class AsociadoModel extends MainModel {

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
        $qry .= ' ORDER BY "Orden"';
        $result = $this->SQLModel->executeQuery($qry);
        if (($result['status'] && count($result['data']) > 0)) {
            $data = Array();
            $contador = 0;
            foreach ($result['data'] as $value) {
                $data[$value['idNombreComercial']] = $value;
                $contador++;
            }
            return $result['data']; // Si marca error revicar el forEach $data;
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

    public function getAsociados($args) {
        $qry = ' SELECT ';
        $qry .= '"idVisitanteNoAutorizado",';
        $qry .= '"Email",';
        $qry .= '"Nombre",';
        $qry .= '"ApellidoPaterno",';
        $qry .= '"ApellidoMaterno",';
        $qry .= '"Telefono",';
        $qry .= '"NombreComercial",';
        $qry .= '"Cargo",';
        $qry .= '"Area",';
        $qry .= '"Ciudad",';
        $qry .= '"Estado",';
        $qry .= '"Pais",';
        $qry .= '"idStatusAutorizado",';
        $qry .= '"idVisitantePadre"';
        $qry .= ' FROM "AE"."VisitanteNoAutorizado"';
        $qry .= ' WHERE "idVisitantePadre"=' . $args['idVisitantePadre'];
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idVisitanteNoAutorizado']] = $value;
                }
            }
            $result['data'] = array();
            $result['data'] = $data;
        }
        return $result;
    }

    public function updateStatusAsociado($args) {
        $qry = 'SELECT * FROM "AE"."fn_ae_InsertarVisitanteNoAutorizado_sas"(' . $args['idVisitanteNoAutorizado'] . ', ' . $args['idStatusAutorizado'] . ', ' . $args['idEdicion'] . ', ' . $args['idEvento'] . ', ' . "'" . $args['MotivoRechazo'] . "'" . ');';

        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function getCount($where = '', $idEdicion = '') {
        $qry = ' SELECT COUNT("Email") FROM "AE"."vw_ae_VisitanteAsociado_temp" ';
        $qry .= $where;
        if ($where != "") {
            $qry .= ' AND "Comprador"= 0 AND "idEdicion" =' . $idEdicion;
        } else {
            $qry .= ' WHERE "Comprador"=0 AND "idEdicion" =' . $idEdicion;
        }
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
        $qry .= ' "AE"."vw_ae_VisitanteAsociado_temp"';
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
        $qry .= ' "Cargo",';
        //$qry .= ' "Asociado",';
        //$qry .= ' "Preregistrado",';
        $qry .= ' "FechaPreregistro",';
        $qry .= ' "NombreStatus",';
        $qry .= ' "NumeroEnvios",';
        $qry .= ' "NumeroDescargas",';
        $qry .= ' "idStatus" ';
        $qry .= ' FROM';
        $qry .= ' "AE"."vw_ae_VisitanteAsociado_temp"';
        $qry .= $where;
        if ($where != "") {
            $qry .= ' AND "Comprador"= 0 AND "idEdicion" =' . $idEdicion;
        } else {
            $qry .= ' WHERE "Comprador"=0 AND "idEdicion" =' . $idEdicion;
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

    public function updateStatusAsociadoList($args) {
        $qry = 'SELECT * FROM "AE"."fn_ae_InsertarActualizarVisitanteNoAutorizado"(' . "'" . $args . "'" . ');';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function updateDowloadLog($idEvento, $idEdicion, $idVisitante, $accion) {
        $qry = 'SELECT * FROM "AE"."fn_ae_LogEnvioGafete"(' . $idEvento . ',' . $idEdicion . ', ' . $idVisitante . ', ' . $accion . ');';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function setVisitanteDataWS($param) {
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->insertIntoTable('SyncData', $param, "idSyncData");
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function getVisitantesExport($where, $order, $idEdicion = '') {
        $qry = ' SELECT ';
        $qry .= ' "idVisitante",';
        $qry .= ' "NombreCompleto",';
        $qry .= ' "Email",';
        $qry .= ' "NombreComercial",';
        $qry .= ' "Cargo",';
        $qry .= ' "FechaPreregistro",';
        $qry .= ' "NumeroEnvios",';
        $qry .= ' "NumeroDescargas"';
        $qry .= ' FROM';
        $qry .= ' "AE"."vw_ae_VisitanteAsociado_temp"';
        if ($where != "") {
            $qry .= 'WHERE ' . $where . '  AND "Comprador"= 0 AND "idEdicion" =' . $idEdicion;
        } else {
            $qry .= '  WHERE "Comprador"=0 AND "idEdicion" =' . $idEdicion;
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
        $qry .= 'vis."CargoOtro", ';
        $qry .= 'vis."NombreComercialOtro", ';
        $qry .= 'vis."idVisitanteTipo", ';
        $qry .= 'vis."Email", ';
        $qry .= 'vistip."VisitanteTipoES", ';
        $qry .= 'vis."FechaAlta", ';
        $qry .= 'CASE WHEN vis."Asociado" =  1 THEN \'Asociado\' END AS "CategoriaAsociado" ';
        $qry .= 'FROM "AE"."Visitante" vis INNER JOIN "AE"."VisitanteTipo" vistip ON vis."idVisitanteTipo" = vistip."idVisitanteTipo" ';
        $qry .= 'WHERE "idVisitante" = ' . $idVisitante;
        $result = $this->SQLModel->executeQuery($qry);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function getAsociadosGafete() {

        $qry = 'SELECT ';
        $qry .= '"idVisitante"';
        $qry .= 'FROM "AE"."vw_ae_VisitanteAsociado_temp" ';
        $qry .= 'WHERE "idStatus" = 2 AND "Comprador" = 0 AND "idEdicion" = 11 AND "idVisitante" is NOT null LIMIT 999';
        $result = $this->SQLModel->executeQuery($qry);

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

}
