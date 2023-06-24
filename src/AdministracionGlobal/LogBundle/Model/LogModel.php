<?php

namespace AdministracionGlobal\LogBundle\Model;

Use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class LogModel extends DashboardModel{

    protected $SQLModel;
    protected $PGSQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->PGSQLModel = new PGSQLClient();
    }

    public function getLog($args = array()) {
        $fields = array("idLog", "idUsuario", "idModuloIxpo", "Accion", "IP", "Navegador","SistemaOperativo", "FechaModificacion",);
        $result = $this->SQLModel->selectFromTable("Log", $fields, $args, array('"idLog"' => 'ASC'));
        if (!($result['status'] && count($result['data']) <= 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idLog']] = $value;
        }
        $result['data'] = $data;
        return $result;
    }
    
        public function getLogCustom($columns = Array(), $params = Array(), $order = Array(), $limit = -1, $offset = -1) {
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
        $qry .= 'FROM "SAS"."Log"';
        $qry .= ' {where}';
        $qry .= $group_by;
        $qry .= $order_by;

        if ($limit != -1 && is_numeric($limit)) {
            $qry .= ' LIMIT ' . $limit;
        }
        if ($offset != -1 && is_numeric($offset)) {
            $qry .= ' OFFSET ' . $offset;
        }
        $result_query = $this->PGSQLModel->execQueryString($qry, $params);

        if ($result_query["status"]) {
            return Array("status" => TRUE, "data" => $result_query['data']);
        } else {
            return Array("status" => FALSE, "error" => $result_query["error"]["string"]);
        }
    }
    
      public function getCountLogs($columns = Array(), $params = Array()) {

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
        $qry .= '     FROM "SAS"."Log"';
        $qry .= '     {where}';
        $qry .= '     ' . $group_by;
        $qry .= ') sq';
        return $this->PGSQLModel->execQueryString($qry, $params);
    }

}
