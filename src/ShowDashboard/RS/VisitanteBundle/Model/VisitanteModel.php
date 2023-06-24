<?php

namespace ShowDashboard\RS\VisitanteBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use ShowDashboard\RS\VisitanteBundle\Model\MainModel;

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
        $qry .= ' {where}';
        $qry .= $group_by;
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
            $qry .= '     {where}';
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

}
