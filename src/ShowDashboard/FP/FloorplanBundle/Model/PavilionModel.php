<?php

namespace ShowDashboard\FP\FloorplanBundle\Model;

use Utilerias\PostgreSQLBundle\v9\PGSQLClient;

class PavilionModel {

    protected $metadata_type = Array(
        'floorplan' => Array(
            '"idEvento"' => 'integer',
            '"idEdicion"' => 'integer',
        ),
    );
    protected
            $pg_schema = '"SAS"',
            $pg_edi = '"Edicion"',
            $pg_sta = '"Stand"',
            $pg_pab = '"Pabellon"',
            $pg_tis = '"TipoStand"',
            $pg_sal = '"Sala"';

    public function __construct() {
        $this->PGSQL_Client = new PGSQLClient();
    }

    public function all($args) {
        $qry = 'SELECT ';
        $qry .= ' "idEvento"';
        $qry .= ', "idEdicion"';
        $qry .= ', "Edicion_ES"';
        $qry .= ', "Edicion_EN"';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_edi;
        $qry .= ' WHERE ' . $this->pg_schema . '.' . $this->pg_edi . '."idEdicion"=' . $args["idEdicion"];
        $qry .= ' ORDER BY "idEdicion" ASC';
        $re1 = $this->PGSQL_Client->execQueryString($qry);


        $qry = 'SELECT DISTINCT';
        $qry .= $this->pg_schema . '.' . $this->pg_sal . '."idSala",';
        $qry .= $this->pg_schema . '.' . $this->pg_sal . '."NombreES",';
        $qry .= $this->pg_schema . '.' . $this->pg_sal . '."NombreEN" ';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_sal;
        $re2 = $this->PGSQL_Client->execQueryString($qry);

        $qry = 'SELECT DISTINCT';
        $qry .= $this->pg_schema . '.' . $this->pg_pab . '."idPabellon",';
        $qry .= $this->pg_schema . '.' . $this->pg_pab . '."NombreES",';
        $qry .= $this->pg_schema . '.' . $this->pg_pab . '."NombreEN" ';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_pab;
        $qry .= ' WHERE ' . $this->pg_schema . '.' . $this->pg_pab . '."idEdicion"=' . $args["idEdicion"];
        $qry .= ' ORDER BY "NombreES" ASC';
        $re3 = $this->PGSQL_Client->execQueryString($qry);

        $qry = 'SELECT DISTINCT';
        $qry .= $this->pg_schema . '.' . $this->pg_tis . '."idTipoStand",';
        $qry .= $this->pg_schema . '.' . $this->pg_tis . '."TipoStand"';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_tis;
        $qry .= ' WHERE ' . $this->pg_schema . '.' . $this->pg_tis . '."idEdicion"=' . $args["idEdicion"];
        $qry .= ' ORDER BY "TipoStand" ASC';
        $re4 = $this->PGSQL_Client->execQueryString($qry);


        $result = array(
            'edition' => $re1["data"]
            , 'hall' => $re2["data"]
            , 'pavilion' => $re3["data"]
            , 'stand_type' => $re4["data"]
        );
        return $result;
    }

    private function query($qry) {
        $result = $this->PGSQL_Client->execQueryString($qry);
        return $result['data'];
    }

    public function get($args) {
        $qry = 'SELECT ';
        $qry .= ' "idStand"';
        $qry .= ', "idEdicion"';
        $qry .= ', "idEvento"';
        $qry .= ', "idPabellon"';
        $qry .= ', "idSala"';
        $qry .= ', "EtiquetaStand"';
        $qry .= ', "Stand_H"';
        $qry .= ', "Stand_W"';
        $qry .= ', "Stand_X"';
        $qry .= ', "Stand_Y"';
        $qry .= ', "StandArea"';
        $qry .= ', "StandNumber"';
        $qry .= ', "StandStatus"';
        $qry .= ', "idTipoStand"';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_sta;
        $qry .= ' {where} ';
        $where = $this->formatArgsPG($args);
        $Args['where'] = $this->bindWherePG($where);
        $boo = $this->PGSQL_Client->execQueryString($qry, $Args);
        $booths = $boo['data'];

        $qry = 'SELECT ';
        $qry .= ' "idStand"';
        $qry .= ', "idEdicion"';
        $qry .= ', "idEvento"';
        $qry .= ', "idPabellon"';
        $qry .= ', "idSala"';
        $qry .= ', "EtiquetaStand"';
        $qry .= ', "Stand_H"';
        $qry .= ', "Stand_W"';
        $qry .= ', "Stand_X"';
        $qry .= ', "Stand_Y"';
        $qry .= ', "StandArea"';
        $qry .= ', "StandNumber"';
        $qry .= ', "StandStatus"';
        $qry .= ', "idTipoStand"';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_sta;
        $qry .= ' WHERE "idEdicion"=' . $args['idEdicion'];
        $qry .= ' AND "Stand_W" > 0';
        $qry .= ' AND "Stand_H" > 0';
        $qry .= ' AND "Stand_X" > 0';
        $qry .= ' AND "Stand_Y" > 0';
        $abooths = $this->PGSQL_Client->execQueryString($qry);

        $qry = 'SELECT ';
        $qry .= ' "idPabellon"';
        $qry .= ', "Color"';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_pab;
        $color = $this->PGSQL_Client->execQueryString($qry);
        $colors = $color['data'];

        $qry = 'SELECT ';
        $qry .= ' "idPabellon"';
        $qry .= ', "Color"';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_pab;
        $qry .= ' WHERE "idEdicion"='.$args['idEdicion'];
        $color = $this->PGSQL_Client->execQueryString($qry);
        $colors = $color['data'];

        $qry = 'SELECT ';
        $qry .= $this->pg_schema . '.' . $this->pg_tis . '."idTipoStand"';
        $qry .= ',' . $this->pg_schema . '.' . $this->pg_tis . '."AnchoStand"';
        $qry .= ',' . $this->pg_schema . '.' . $this->pg_tis . '."AltoStand"';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_tis . 'ORDER BY "TipoStand" ASC';
        $stand = $this->PGSQL_Client->execQueryString($qry);
        $stand_size = $stand["data"];

        $result = array(
            'id' => 1,
            'hallId' => $args["idSala"],
            'color' => $colors,
            'stand_size' => $stand_size,
            'evento' => $args['idEvento'],
            'edicion' => $args["idEdicion"],
            'layout' => $args["idEdicion"] . '_' . $args["idSala"] . '_' . 'layout.png',
            'booths' => $booths,
            'abooths' => $abooths['data'],
        );

        return $result;
    }

    private function getCheckinsByBooth() {
        $path = '../app/cache/sa/checkins.json';

        $last_modified = ( time() - filemtime($path) ) / 14400;
        if (file_exists($path) && $last_modified <= 1) {
            return json_decode(file_get_contents($path), true);
        }

        $assignments = $this->getAssignments();
        $checkins = $this->getCheckins();
        $result = array();

        foreach ($checkins as $checkin) {
            $boothIds = array();

            foreach ($assignments as $assignment) {
                if ($assignment['idEmpresa'] === $checkin['idEmpresa'])
                    array_push($boothIds, $assignment['idStand']);
            }
            foreach ($boothIds as $boothId) {
                $result[$boothId] = $checkin;
            }
        }

        $this->writeJSON($path, $result);

        return $result;
    }

    private function getCheckins() {
        $q = 'SELECT DISTINCT';
        $q .= ' COUNT(lec."_id_Empresa") as Cantidad,';
        $q .= ' lec."_id_Empresa",';
        $q .= ' lec."NombreExpositor"';
        $q .= ' FROM PD_LEC_LecturasExpositores lec';
        $q .= ' WHERE lec."Tipo_VisitanteInteresado" = 1';
        $q .= ' AND lec."RegistroUnico" = 1';
        $q .= ' AND lec."_id_Empresa" IS NOT NULL';
        $q .= ' GROUP BY lec."_id_Empresa",lec."NombreExpositor"';

        $result = $this->PGSQL_Client->execQueryString($qry);
        //$this->FM_Client->setQuery($qry);  Se usaban estas 3 lineas para FM
        //$e = $this->FM_Client->exec();
        //$result = $this->FM_Client->getResultAssoc();
        return $result['data'];
    }

    private function getAssignments() {
        $qry = 'SELECT ';
        $qry .= ' "idStand"';
        $qry .= ', "idEdicion"';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_sta;
        $qry .= ' {where}';
        $where = $this->formatArgsPG($args);
        $Args['where'] = $this->bindWherePG($where);
        $result = $this->PGSQL_Client->execQueryString($qry, $Args);
        return $result['data'];
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
