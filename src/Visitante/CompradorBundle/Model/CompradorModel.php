<?php

namespace Visitante\CompradorBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Visitante\VisitanteBundle\Model\MainModel;

class CompradorModel extends MainModel {

    public function __construct(ContainerInterface $container = null) {
        parent::__construct($container);
    }

    public function getCount($where = '', $idEdicion = '') {
        $qry = ' SELECT COUNT("Email") FROM "AE"."vw_ae_VisitanteAsociado_temp" ';
        $qry .= $where;
        if ($where != "") {
            $qry .= ' AND "Comprador"= 1 AND "idEdicion" =' . $idEdicion;
        } else {
            $qry .= ' WHERE "Comprador"=1 AND "idEdicion" =' . $idEdicion;
        }
        $result = $this->SQLModel->executeQuery($qry);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function getListidCompradores($where = '', $idEdicion = '') {
        $qry = ' SELECT ';
        $qry .= ' "idVisitante",';
        $qry .= ' "idVisitanteNoAutorizado"';
        $qry .= ' FROM';
        $qry .= ' "AE"."vw_ae_VisitanteAsociado_temp"';
        $qry .= $where;
        if ($where != "") {
            $qry .= ' AND "Comprador"=1 AND "idEdicion" =' . $idEdicion;
        } else {
            $qry .= ' WHERE "Comprador"=1 AND "idEdicion" =' . $idEdicion;
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

    public function getCompradores($where, $order, $param, $idEdicion) {
        $qry = ' SELECT ';
        $qry .= ' "idVisitante",';
        $qry .= ' "NombreCompleto",';
        $qry .= ' "Email",';
        $qry .= ' "NombreComercial",';
        $qry .= ' "Cargo",';
        //$qry .= ' "Comprador",';
        //$qry .= ' "Preregistrado",';
        $qry .= ' "FechaPreregistro",';
        $qry .= ' "NombreStatus",';
        $qry .= ' "NumeroEnvios",';
        $qry .= ' "NumeroDescargas",';
        $qry .= '"idStatus"';
        $qry .= ' FROM';
        $qry .= ' "AE"."vw_ae_VisitanteAsociado_temp"';
        $qry .= $where;
        if ($where != "") {
            $qry .= ' AND "Comprador"=1 AND "idEdicion" =' . $idEdicion;
        } else {
            $qry .= ' WHERE "Comprador"=1 AND "idEdicion" =' . $idEdicion;
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

    public function getCompradoresExport($where, $order, $idEdicion) {
        $qry = ' SELECT ';
        $qry .= ' "idVisitante",';
        $qry .= ' "NombreCompleto",';
        $qry .= ' "Email",';
        $qry .= ' "NombreComercial",';
        $qry .= ' "Cargo",';
        //$qry .= ' "Comprador",';
        //$qry .= ' "Preregistrado",';
        $qry .= ' "FechaPreregistro",';
        $qry .= ' "NombreStatus",';
        $qry .= ' "NumeroEnvios",';
        $qry .= ' "NumeroDescargas"';
        $qry .= ' FROM';
        $qry .= ' "AE"."vw_ae_VisitanteAsociado_temp"';
        if ($where != "") {
            $qry .= ' WHERE ' . $where . ' AND "Comprador"= 1 AND "idEdicion" =' . $idEdicion;
        } else {
            $qry .= ' WHERE "Comprador"=1 AND "idEdicion" =' . $idEdicion;
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

    public function tabsPermission($user) {
        $idUserType = $user["idTipoUsuario"];
        $qry = 'SELECT "TabsPermisos" FROM "SAS"."TipoUsuario" WHERE "idTipoUsuario" = ' . $idUserType;
        $result = $this->SQLModel->executeQuery($qry);

        return $result["data"][0]["TabsPermisos"];
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
        $qry .= 'vis."NombreComercial", ';
        $qry .= 'vis."DE_Area", ';
        $qry .= 'vis."idVisitanteTipo", ';
        $qry .= 'vis."Email", ';
        $qry .= 'vistip."VisitanteTipoES", ';
        $qry .= 'vis."FechaAlta", ';
        $qry .= 'CASE WHEN vis."Comprador" =  1 THEN \'Comprador\' END AS "CategoriaAsociado" ';
        $qry .= 'FROM "AE"."Visitante" vis INNER JOIN "AE"."VisitanteTipo" vistip ON vis."idVisitanteTipo" = vistip."idVisitanteTipo" ';
        $qry .= 'WHERE "idVisitante" = ' . $idVisitante;
        $result = $this->SQLModel->executeQuery($qry);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }
     public function updateDowloadLog($idEvento, $idEdicion, $idVisitante, $accion) {
        $qry = 'SELECT * FROM "AE"."fn_ae_LogEnvioGafete"(' . $idEvento . ',' . $idEdicion . ', ' . $idVisitante . ', ' . $accion . ');';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function sendGafetesCompradores() {
        $qry = ' SELECT ';
        $qry .= ' "idVisitante"';
        $qry .= ' FROM';
        $qry .= ' "AE"."vw_ae_VisitanteAsociado_temp"';
        $qry .= ' WHERE "Comprador"=1 AND "idEdicion" =11 AND "idStatus" = 2';

        $result = $this->SQLModel->executeQuery($qry);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }
    

}