<?php

namespace StatLink\EstadisticaBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

class EstadisticaModel {

    public $dbh;
    protected $PGModel;
    protected $pg_schema = '';
    protected $base_path_cache = '../var/cache/estadistica/';

    function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getRegistered($table, $order = '', $idEdition) {
        $qry = 'SELECT * FROM "MS_SL"."' . $table . '"';
        $qry .= $table != 'sl_EstadisticaAsistencia' ? ' WHERE "Preregistro" > 0' : ' WHERE "Asistencia" > 0';

        if (!empty($idEdition)) {
            $qry .= ' AND "idEdicion" = ' . $idEdition;
        }

        if (!empty($order)) {
            $qry .= ' ORDER BY ' . $order;
        }
        $result_pg = $this->SQLModel->executeQuery($qry);
        $data = ($result_pg['status']) ? $result_pg['data'] : array();
        return $data;
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
                "type" => $this->getBindParameterType($this->metadata_type['vis'][$key]),
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

    public function getStats($params, $where) {
        $cache_name = "cache_stats_perfil_" . $params['idEdicion'];
        $ruta = $this->base_path_cache . $cache_name . '.json';
        if (file_exists($ruta)) {
            $result_cache = file_get_contents($ruta);
            $cache_encuesta = json_decode($result_cache, TRUE);
            $preguntas['data'] = $cache_encuesta[0];
            $respuestas['data'] = $cache_encuesta[1];
        } else {
            $fields = $this->getPregFields();
            $this->SQLModel->setSchema("AE");
            $preguntas = $this->SQLModel->selectFromTable('Pregunta', $fields, $params, array('"zzOrden"' => 'ASC'));
            $this->SQLModel->setSchema("SAS");
            if (!$preguntas['status']) {
                throw new \Exception($preguntas['data'], 409);
            }

            $qry = $this->getRespQry();
            $qry .= '"idEvento" = ' . $params['idEvento'];
            $qry .= ' AND "idEdicion" = ' . $params['idEdicion'];
            $qry .= ' AND "Activa" = 1 ) AND "Activa" = 1 ORDER BY "idRespuesta"';
            $respuestas = $this->SQLModel->executeQuery($qry);
            if (!$respuestas['status']) {
                throw new \Exception($respuestas['data'], 409);
            }
            $this->writeJSON($ruta, array($preguntas['data'], $respuestas['data']));
            clearstatcache();
        }

        $qry = 'SELECT ' . $this->getQryFinalStats($respuestas['data']);
        $qry .= ' FROM "DEMOGRAFICOS"."Demograficos_' . $params['idEvento'] . '_' . $params['idEdicion'] . '_' . $preguntas['data'][0]['idEncuesta'] . '" demo';
        $qry .= ' INNER JOIN "AE"."VisitanteCupon" vic';
        $qry .= ' ON demo."idVisitante" = vic."idVisitante" ';
        $qry .= ' INNER JOIN "AE"."Cupon" cup';
        $qry .= ' ON vic."idCupon" =  cup."idCupon"';
        $qry .= $where;
        
        $stats = $this->SQLModel->executeQuery($qry);
        if (!$stats['status']) {
            throw new \Exception($stats['data'], 409);
        }
        $data = $this->setStats($preguntas['data'], $respuestas['data'], $stats['data'][0]);

        $result = Array('status' => TRUE, 'data' => $data);

        return $result;
    }

    public function getPregFields() {
        return Array(
            'idPregunta',
            'idPreguntaTipo',
            'idEncuesta',
            'cSubpregunta',
            'PreguntaES',
            'PreguntaEN',
        );
    }

    protected function getRespQry() {
        $qry = 'SELECT ';
        $qry .= '"idPregunta", ';
        $qry .= '"idRespuesta", ';
        $qry .= '"RespuestaES", ';
        $qry .= '"RespuestaEN" ';
        $qry .= 'FROM ';
        $qry .= '"AE"."Respuesta" ';
        $qry .= 'WHERE ';
        $qry .= '"idPregunta" IN ( ';
        $qry .= 'SELECT ';
        $qry .= '"idPregunta" ';
        $qry .= 'FROM ';
        $qry .= '"AE"."Pregunta" ';
        $qry .= 'WHERE ';
        return $qry;
    }

    public function getQryFinalStats($respuestas) {
        $qry = '';
        foreach ($respuestas as $respuesta) {
            $qry .= 'SUM(demo."Respuesta' . $respuesta['idRespuesta'] . '") AS "' . $respuesta['idRespuesta'] . '",';
        }
        return $qry = substr($qry, 0, -1);
    }

    public function setStats($preguntas, $respuestas, $stats) {
        $data = array();
        $aux = array();
        $idpadre = 0;

        foreach ($preguntas as $key => $pregunta) {
            $pregunta['stat'] = 0;
            if ($pregunta['cSubpregunta'] == 0)
                $idpadre = $pregunta['idPregunta'];
            else
                $pregunta['idpadre'] = $idpadre;
            $data[$pregunta['idPregunta']] = $pregunta;
        }

        foreach ($respuestas as $key => $respuesta) {
            $data[$respuesta['idPregunta']]['stat'] += (int) $stats[$respuesta['idRespuesta']];
            $respuestas[$key]['stat'] = (int) $stats[$respuesta['idRespuesta']];
            $aux[$key] = $respuestas[$key]['stat'];
//            $data[$respuesta['idPregunta']]['Respuestas'][$respuesta['idRespuesta']] = $respuesta;            
        }
        array_multisort($aux, SORT_DESC, $respuestas);
        foreach ($respuestas as $respuesta) {
            $data[$respuesta['idPregunta']]['Respuestas']['"' . $respuesta['idRespuesta'] . '"'] = $respuesta;
        }

        return $data;
    }

    public function writeJSON($filename, $array) {
        $json = json_encode($array);
        $fp = fopen($filename, "w");
        fwrite($fp, $json);
        fclose($fp);
        chmod($filename, 0777);
    }

}
