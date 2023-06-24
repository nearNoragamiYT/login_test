<?php

namespace Visitante\VisitantesGeneralesBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Visitante\VisitantesGeneralesBundle\Model\MainModel;

class VisitantesGeneralesModel extends MainModel {

    public function __construct(ContainerInterface $container = null) {
        parent::__construct($container);
    }

    public function getVisitanteCustom($columns = Array(), $params = Array(), $order = Array(), $limit = -1, $offset = -1) {
        $columns_str = '';
        $group_by = ' ';
        if (is_array($columns) && COUNT($columns) > 0) {
            $group_by .= 'GROUP BY';
            foreach ($columns as $column) {
                $columns_str .= ' ' . $column . ',';

                $column_name = $column;
                if (strpos(strtoupper($column), " AS ")) {
                    $column_name = substr($column, 0, strpos(strtoupper($column), " AS "));
                }
                $group_by .= ' ' . $column_name . ',';
            }
            $columns_str = substr($columns_str, 0, -1);
            $group_by = substr($group_by, 0, -1);
        }

        $order_by = ' ';
        if (is_array($order) && COUNT($order) > 0) {
            $order_by .= 'ORDER BY';
            foreach ($order as $order_column) {
                $order_by .= ' ' . $order_column["name"] . ' ' . $order_column["dir"] . ',';
            }
            $order_by = substr($order_by, 0, -1);
        }

        $qry = ' SELECT ';
        $qry .= 's."idVisitante",';
        $qry .= 's."NombreCompleto",';
        $qry .= 's."Email",';
        $qry .= 's."DE_RazonSocial",';
        $qry .= 's."VisitanteTipo",';
        $qry .= 's."DE_idCargo",';
        $qry .= 's."Preregistrado",';
        $qry .= 's."FechaAlta_AE",';
        $qry .= 's."DE_Telefono",';
        $qry .= 's."DE_AreaPais",';
        $qry .= 's."DE_AreaCiudad"';
        $qry .= 'FROM ( ';
        $qry .= ' SELECT vis."idVisitante", vis."NombreCompleto", vis."Email", vis."DE_RazonSocial", vis."DE_idCargo", vise."Preregistrado", vise."FechaAlta_AE", vis."DE_Telefono", vis."DE_AreaPais", vis."DE_AreaCiudad",  ';
        $qry .= ' CASE
                    WHEN vis."RegistroMultiple" = 1 THEN \'Registro Multiple\'
                    WHEN  vis."Prensa" = 1 THEN \'Prensa\'
                    WHEN  vis."Asociado" = 1 and vis."Comprador" = 1 THEN  \'Asociado\'
                    WHEN  vis."Comprador" = 1 THEN  \'Comprador\'
                    WHEN vis."Asociado" = 1 THEN \'Asociado\'
                ELSE \'Visitante\'
              END "VisitanteTipo"';
        $qry .= 'FROM "AE"."Visitante" vis INNER JOIN "AE"."VisitanteEdicion" vise ON vise."idVisitante" = vis."idVisitante"';
        $qry .= ' {where}';
        $qry .= 'GROUP BY vise."idVisitante",vis."idVisitante", vis."NombreCompleto", vis."Email", vis."DE_RazonSocial", vis."DE_idCargo", vise."Preregistrado", vise."FechaAlta_AE", vis."DE_Telefono", vis."DE_AreaPais", vis."DE_AreaCiudad"';
        $qry .= 'ORDER BY vis."idVisitante" ASC ';
        $q = $qry;
        $q .= ' LIMIT 0';
        $q .= ' OFFSET 100';
        if ($limit != -1 && is_numeric($limit)) {
            $qry .= ' LIMIT ' . $limit;
        }
        if ($offset != -1 && is_numeric($offset)) {
            $qry .= ' OFFSET ' . $offset;
        }
        $qry .= ') s';


        foreach ($params['where'] as $key => $value) {
            if (in_array('vise."FechaAlta_AE"', $value))
                $params['where'][$key]['timestamp'] = TRUE;
        }



        $result_query = $this->SQLModel->executeQueryWhitWhere($qry, $params);

        if ($result_query["status"]) {
            return Array("status" => TRUE, "data" => $result_query['data'], "data_qry" => array("qry" => $q, "params" => $params));
        } else {
            return Array("status" => FALSE, "error" => $result_query["error"]["string"]);
        }
    }

    public function getCountVisitante($columns = Array(), $params = Array(), $query = "") {
        if ($query != "") {

            $Result = $this->SQLModel->executeQueryWhitWhere($query, $params);
            $Result['count']['qry'] = $query;
            $Result['count']['params'] = $params;
        } else {
            if (is_array($columns) && COUNT($columns) > 0) {
                $group_by .= 'GROUP BY';
                foreach ($columns as $column) {
                    $columns_str .= ' ' . $column . ',';

                    $column_name = $column;
                    if (strpos(strtoupper($column), " AS ")) {
                        $column_name = substr($column, 0, strpos(strtoupper($column), " AS "));
                    }
                    $group_by .= ' ' . $column_name . ',';
                }
                $columns_str = substr($columns_str, 0, -1);
                $group_by = substr($group_by, 0, -1);
            }

            $qry = ' select COUNT(*) as "total"';
            $qry .= ' FROM (';
            $qry .= '     SELECT ' . $columns_str;
            $qry .= '     FROM "AE"."Visitante" vis INNER JOIN "AE"."VisitanteEdicion" vise ON vise."idVisitante" = vis."idVisitante"';
            $qry .= ' {where}';
            $qry .= '     ' . $group_by;
            $qry .= ') sq';

            foreach ($params['where'] as $key => $value) {
                if (in_array('vise."FechaAlta_AE"', $value))
                    $params['where'][$key]['timestamp'] = TRUE;
            }

            $Result = $this->SQLModel->executeQueryWhitWhere($qry, $params);
            $Result['count']['qry'] = $qry;
            $Result['count']['params'] = $params;
        }
        return $Result;
    }

    public function getVisitanteBadge($data) {
        $qry = 'SELECT DISTINCT ';
        $qry .= 'v."idVisitante",';
        $qry .= 'v."Nombre",';
        $qry .= 'v."ApellidoPaterno",';
        $qry .= 'v."ApellidoMaterno",';
        $qry .= 'v."NombreCompleto",';
        $qry .= 'v."DE_Cargo",';
        $qry .= 'v."DE_RazonSocial",';
        $qry .= 'v."idVisitanteTipo",';
        $qry .= 'v."Email",';
        $qry .= 'vt."VisitanteTipoES",';
        $qry .= 'v."FechaAlta",';
        $qry .= 'CASE WHEN v."Asociado" =  1 THEN \'Asociado\' ELSE \'Visitante\' END AS "CategoriaAsociado" ';
        $qry .= 'FROM "AE"."Visitante" AS v ';
        $qry .= 'INNER JOIN "AE"."VisitanteEdicion" AS ve ON v."idVisitante" = ve."idVisitante" ';
        $qry .= 'INNER JOIN "AE"."Compra" AS c ON c."idVisitante" = v."idVisitante" AND c."idEdicion" = ve."idEdicion" ';
        $qry .= 'INNER JOIN "AE"."VisitanteTipo" AS vt ON v."idVisitanteTipo" = vt."idVisitanteTipo" ';
        $qry .= 'WHERE ';
        $qry .= 've."idEdicion" = ' . $data["idEdicion"];
        $qry .= ' AND ve."idEvento" = ' . $data["idEvento"];
        $qry .= ' AND ve."idVisitante" = ' . $data["idVisitante"];
        $qry .= ' AND c."idCompraStatus" = 2';
        $result = $this->SQLModel->executeQuery($qry);
        if (($result['status'] == 1) && (count($result['data']) > 0)) {
            return $result;
        } else {
            return array("status" => false, "data" => "No hay Compra");
        }
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
        $qry .= 'vis."FechaAlta", ';
        $qry .= 'vis."DE_visitanteTipo", ';
        $qry .= 'vistip."VisitanteTipoES" ';
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

}
