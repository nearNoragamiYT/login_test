<?php

namespace Visitante\VisitanteBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Visitante\VisitanteBundle\Model\MainModel;

class VisitanteModel extends MainModel {

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

        $qry = ' SELECT ' . $columns_str;
        $qry .= 'FROM "AE"."Visitante" vis INNER JOIN "AE"."VisitanteEdicion" vise ON vise."idVisitante" = vis."idVisitante"';
        $qry .= ' LEFT JOIN ( SELECT "idVisitante", "idEdicion", COUNT ( "idVisitante" ) AS "NumDescargar" FROM "AE"."LogEnvioGafete" WHERE "Accion" =1  GROUP BY "idVisitante", "idEdicion" )li  ON vise."idVisitante" = li."idVisitante"  AND vise."idEdicion" = li."idEdicion"';
        $qry .= ' LEFT JOIN ( SELECT "idVisitante", "idEdicion", COUNT ( "idVisitante" ) AS "NumEnvios" FROM "AE"."LogEnvioGafete"  WHERE "Accion" =2 GROUP BY "idVisitante", "idEdicion" )leg  ON vise."idVisitante" = leg."idVisitante"  AND vise."idEdicion" = leg."idEdicion" ';
        $qry .= ' {where}';
        $qry .= $group_by . ', vise."idEdicion"';
        $qry .= $order_by;
        $q = $qry;
        $q .= ' LIMIT 0';
        $q .= ' OFFSET 100';
        if ($limit != -1 && is_numeric($limit)) {
            $qry .= ' LIMIT ' . $limit;
        }
        if ($offset != -1 && is_numeric($offset)) {
            $qry .= ' OFFSET ' . $offset;
        }

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
            $qry .= ' LEFT JOIN ( SELECT "idVisitante", "idEdicion", COUNT ( "idVisitante" ) AS "NumDescargar" FROM "AE"."LogEnvioGafete" WHERE "Accion" =1  GROUP BY "idVisitante", "idEdicion" )li  ON vise."idVisitante" = li."idVisitante"  AND vise."idEdicion" = li."idEdicion"';
            $qry .= ' LEFT JOIN ( SELECT "idVisitante", "idEdicion", COUNT ( "idVisitante" ) AS "NumEnvios" FROM "AE"."LogEnvioGafete"  WHERE "Accion" =2 GROUP BY "idVisitante", "idEdicion" )leg  ON vise."idVisitante" = leg."idVisitante"  AND vise."idEdicion" = leg."idEdicion" ';
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
        $qry .= 'v."FechaAlta"';
        $qry .= 'FROM "AE"."Visitante" AS v ';
        $qry .= 'INNER JOIN "AE"."VisitanteEdicion" AS ve ON v."idVisitante" = ve."idVisitante" ';
        //$qry .= 'INNER JOIN "AE"."Compra" AS c ON c."idVisitante" = v."idVisitante" AND c."idEdicion" = ve."idEdicion" ';
        $qry .= 'INNER JOIN "AE"."VisitanteTipo" AS vt ON v."idVisitanteTipo" = vt."idVisitanteTipo" ';
        $qry .= 'WHERE ';
        $qry .= 've."idEdicion" = ' . $data["idEdicion"];
        $qry .= ' AND ve."idEvento" = ' . $data["idEvento"];
        $qry .= ' AND ve."idVisitante" = ' . $data["idVisitante"];
        $qry .= ' AND (c."idCompraStatus" = 2 OR c."idCompraStatus" = 4)';
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
