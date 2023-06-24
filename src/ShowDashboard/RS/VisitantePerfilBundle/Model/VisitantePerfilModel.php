<?php

namespace ShowDashboard\RS\VisitantePerfilBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use ShowDashboard\RS\VisitantePerfilBundle\Model\MainModel;

class VisitantePerfilModel extends MainModel {

    protected $SQLModel;

    public function __construct(ContainerInterface $container = null) {
        parent::__construct($container);
        $this->SQLModel = new SQLModel();
        $this->PGSQLModel = new PGSQLClient();
        $this->SQLModel->setSchema('AE');
    }

    public function getRsVisitanteCustom($columns = Array(), $params = Array(), $order = Array(), $limit = -1, $offset = -1) {
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

    public function getCountRsVisitante($columns = Array(), $params = Array(), $query = "") {
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

}
