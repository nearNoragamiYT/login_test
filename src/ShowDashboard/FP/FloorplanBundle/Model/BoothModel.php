<?php

namespace ShowDashboard\FP\FloorplanBundle\Model;

use Utilerias\PostgreSQLBundle\v9\PGSQLClient;

class BoothModel {

    protected $metadata_type = Array(
        'floorplan' => Array(
            '"idEvento"' => 'integer',
            '"idEdicion"' => 'integer',
            '"idStand"' => 'integer',
            '"StandNumber"' => 'character',
            '"Stand_X"' => 'integer',
            '"Stand_Y"' => 'integer',
            '"Stand_W"' => 'double',
            '"Stand_H"' => 'double',
            '"idTipoStand"' => 'integer'
        ),
    );
    protected
            $pg_schema = '"SAS"',
            $pg_edi = '"Edicion"',
            $pg_sta = '"Stand"';

    public function __construct() {
        $this->PGSQL_Client = new PGSQLClient();
    }

    public function all($args = '') {
        $qry = 'SELECT ';
        $qry .= ' "idStand"';
        $qry .= ', "EtiquetaStand"';
        $qry .= ', "Stand_H"';
        $qry .= ', "Stand_W"';
        $qry .= ', "Stand_X"';
        $qry .= ', "Stand_Y"';
        $qry .= ', "StandNumber"';
        $qry .= ', "StandStatus"';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_sta;
        $qry .= ' {where}';
        $where = $this->formatArgsPG($args);
        $Args['where'] = $this->bindWherePG($where);
        $result = $this->PGSQL_Client->execQueryString($qry, $Args);

        if (
                isset($result['status']['ws_status']) &&
                $result['status']['ws_status'] === 0
        )
            return $result['data'];
        else
            return NULL;
    }

    public function update($data) {
        if (empty($data['idPabellon']))
            $data['idPabellon'] = $data['idEdicion'];
        $area = $data['Stand_W'] * $data['Stand_H'];
        $data_pre = Array();
        $data_pre['idEvento'] = $data['idEvento'];
        $data_pre['idPabellon'] = $data['idPabellon'];
        $data_pre['idSala'] = $data['idSala'];
        $data_pre['Stand_X'] = (integer)$data['Stand_X'];
        $data_pre['Stand_Y'] = (integer)$data['Stand_Y'];
        $data_pre['Stand_W'] = $data['Stand_W'];
        $data_pre['Stand_H'] = $data['Stand_H'];
        $data_pre['EtiquetaStand'] = $data['EtiquetaStand'];
        $data_pre['StandArea'] = $area;
        $data_pre['StandNumber'] = $data['StandNumber'];
        $data_pre['idTipoStand'] = $data['idTipoStand'];
        $args["idStand"] = $data['idStand'];
        $qry = 'UPDATE ';
        $qry .= $this->pg_schema . '.' . $this->pg_sta;
        $qry .= ' {set} {where} RETURNING "Stand_X","Stand_Y","Stand_W","Stand_H","StandArea"';
        $Args['set'] = $this->bindWherePG($this->formatArgsPG($data_pre));
        $Args['where'] = $this->bindWherePG($this->formatArgsPG($args));
        $result = $this->PGSQL_Client->execQueryString($qry, $Args);
        if ($result["data"][0] == "E" || $result["status"] == '')
            return null;
        else
            return $result["data"]['0'];
    }

    public function create($data) {
        $area = $data['Stand_W'] * $data['Stand_H'];
        if (empty($data['StandStatus']))
            $data['StandStatus'] = 'libre';
        if (empty($data['idSala']))
            $data['idSala'] = 1;
        $qry = 'INSERT INTO ' . $this->pg_schema . '.' . $this->pg_sta;
        $qry .= ' ("idEdicion","idEvento","idPabellon","idSala"'
                . ',"Stand_H","Stand_W","Stand_X","Stand_Y"'
                . ',"StandArea","StandNumber","StandStatus", "idTipoStand")';
        $qry .= ' VALUES (';
        $qry .= $data['idEdicion'];
        $qry .= ',' . $data['idEvento'];
        $qry .= ',' . $data['idPabellon'];
        $qry .= ',' . $data['idSala'];
        $qry .= ',' . $data['Stand_H'];
        $qry .= ',' . $data['Stand_W'];
        $qry .= ',' . (integer)$data['Stand_X'];
        $qry .= ',' . (integer)$data['Stand_Y'];
        $qry .= ',' . $area;
        $qry .= ",'" . $data['StandNumber'];
        $qry .= "','" . $data['StandStatus'];
        $qry .= "'," . $data['idTipoStand'];
        $qry .= ") ";
        $qry .= 'RETURNING "idStand"';
        $result = $this->PGSQL_Client->execQueryString($qry);
        if ($result["data"][0] == "E" || $result["status"] == '')
            return null;
        else
            return $result['data']['0']['idStand'];
    }

    public function delete($data) {
        $args["idStand"] = $data['idStand'];
        $qry = 'DELETE FROM ' . $this->pg_schema . '.' . $this->pg_sta;
        $qry .= ' {where} RETURNING "StandNumber"';
        $where = $this->formatArgsPG($args);
        $Args['where'] = $this->bindWherePG($where);
        $result = $this->PGSQL_Client->execQueryString($qry, $Args);
        if ($result["data"][0] == "E" || $result["status"] == '')
            return null;
        else
            return $result['data']['0'];
    }

    public function formatArgsPG($Args) {
        $Args_temp = Array();
        if (count($Args) > 0) {
            foreach ($Args as $key => $value) {
                $val = str_replace("'", '', $value);
                if ($val == "" || strtoupper($val) == strtoupper('NULL')) {
                    $val = NULL;
                }
                $Args_temp['"' . str_replace('"', '', $key) . '"'] = $val;
            }
        }
        return $Args_temp;
    }

    public function bindWherePG($Args) {

        $where = Array();

        if (!(is_Array($Args) && COUNT($Args) > 0 )) {
            return NULL;
        }

        $i = 0;
        foreach ($Args as $key => $value) {
            $condicion = Array(
                "name" => $key,
                "operator" => "=",
                "value" => $value,
                "type" => $this->getBindParameterType($this->metadata_type['floorplan'][$key]),
            );
            if ($i > 0) {
                $condicion['clause'] = "AND";
            }
            $i++;
            array_push($where, $condicion);
        }

        return $where;
    }

    private function getBindParameterType($var) {
        switch ($var) {
            case "boolean":
                return \PDO::PARAM_BOOL;
            case "integer":
                return \PDO::PARAM_INT;
            case "null":
                return \PDO::PARAM_NULL;
            case "double":
            case "string":
            default :
                return \PDO::PARAM_STR;
        }
    }

}
