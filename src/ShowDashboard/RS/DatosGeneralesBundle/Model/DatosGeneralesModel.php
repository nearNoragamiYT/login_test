<?php

namespace ShowDashboard\RS\DatosGeneralesBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use ShowDashboard\RS\VisitanteBundle\Model\MainModel;

class DatosGeneralesModel extends MainModel {

    public function __construct(ContainerInterface $container = null) {
        parent::__construct($container);
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

    public function getEncuesta($params) {
        $cache_name = "encuesta_" . $params['idEdicion'];
        $ruta = $this->base_path_cache . $cache_name . '.json';
        if (file_exists($ruta)) {
            $result_cache = file_get_contents($ruta);
            $result = Array('status' => TRUE, 'data' => json_decode($result_cache, TRUE));
        } else {

            $encuesta = $this->SQLModel->executeQuery('SELECT "AE"."Encuesta"."idEncuesta" FROM "AE"."Encuesta" '
                    . 'WHERE "AE"."Encuesta"."idEdicion" = ' . $params['idEdicion'] . 'AND "AE"."Encuesta"."Activa" = 1');
            if (!$encuesta['status']) {
                throw new \Exception($encuesta['data'], 409);
            }
            $fields = $this->getPreguntasFields();
            $this->SQLModel->setSchema("AE");
            $pregunta = $this->SQLModel->selectFromTable('Pregunta', $fields, $params, array('"zzOrden"' => 'ASC'));
            $this->SQLModel->setSchema("SAS");
            if (!$pregunta['status']) {
                throw new \Exception($pregunta['data'], 409);
            }
            $qry = $this->getRespuestasQry();
            $qry .= '"idEvento" = ' . $params['idEvento'];
            $qry .= ' AND "idEdicion" = ' . $params['idEdicion'];
            $qry .= ' AND "Activa" = 1 ) AND "Activa" = 1 ORDER BY "idRespuesta"';
            $respuesta = $this->SQLModel->executeQuery($qry);
            if (!$respuesta['status']) {
                throw new \Exception($respuesta['data'], 409);
            }
            $data = $this->setEncuesta($pregunta['data'], $respuesta['data']);
            $data['idEncuesta'] = $encuesta['data']['0']['idEncuesta'];
            $data['from'] = '"Demograficos_' . $params['idEvento'] . '_' . $params['idEdicion'] . '_' . $data['idEncuesta'] . '"';
            $this->writeJSON($ruta, $data);
            clearstatcache();
            $result = Array('status' => TRUE, 'data' => $data);
        }
        return $result;
    }

    public function getPreguntasFields() {
        return Array(
            'idPregunta',
            'idPreguntaTipo',
            'PreguntaES',
            'PreguntaEN',
        );
    }

    protected function getRespuestasQry() {
        $qry = 'SELECT ';
        $qry .= '"idPregunta", ';
        $qry .= '"idRespuesta", ';
        $qry .= '"RespuestaES", ';
        $qry .= '"RespuestaEN", ';
        $qry .= '"RespuestaAbierta"';
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

    public function setEncuesta($preguntas, $respuestas) {
        $data = array();
        foreach ($respuestas as $respuesta) {
            $data['order'][] = '"Respuesta' . $respuesta['idRespuesta'] . '"';
            $data['select'] .= 'demo."Respuesta' . $respuesta['idRespuesta'] . '",';
            if ($respuesta['RespuestaAbierta'] == 1) {
                $data['order'][] = '"RespuestaAbierta' . $respuesta['idRespuesta'] . '"';
                $data['select'] .= 'demo."RespuestaAbierta' . $respuesta['idRespuesta'] . '",';
            }
        }
        $data['select'] = substr($data['select'], 0, -1);
        foreach ($preguntas as $pregunta) {
            $data['encuesta'][$pregunta['idPregunta']] = $pregunta;
            $data['encuesta'][$pregunta['idPregunta']]['PreguntaAbierta'] = 0;
        }
        foreach ($respuestas as $respuesta) {
            $data['encuesta'][$respuesta['idPregunta']]['Respuestas'][$respuesta['idRespuesta']] = $respuesta;
            if ($respuesta['RespuestaAbierta'] == 1)
                $data['encuesta'][$respuesta['idPregunta']]['PreguntaAbierta'] = 1;
        }
        return $data;
    }

    public function getCount($encuesta, $where = '') {
        $qry = 'SELECT COUNT(demo."idVisitante") FROM "DEMOGRAFICOS".' . $encuesta['from'];
        $qry .= ' demo INNER JOIN "AE"."Visitante" vis';
        $qry .= ' ON demo."idVisitante" =  vis."idVisitante"';
        $qry .= ' INNER JOIN "AE"."VisitanteEdicion" vise';
        $qry .= ' ON vise."idVisitante" =  vis."idVisitante"';
        $qry .= $where;

        $Result = $this->SQLModel->executeQuery($qry);
        return $Result;
    }

    public function getCustom($filtros_generales, $encuesta, $post, $where = '') {
        $order = Array();
        $qry = ' SELECT ';

        foreach ($filtros_generales as $key => $value) {
            $order[] = '"' . $key . '"';
            if (array_key_exists('is_select', $value['filter_options']) && $value['filter_options']['is_select']) {
                $qry .= ' CASE';
                foreach ($value['filter_options']['values'] as $id => $option) {
                    $qry .= ' WHEN (' . $value['table'] . '."' . $key . '" = ' . $id . ") THEN '" . $option . "'";
                }
                $qry .= ' END AS "' . $key . '",';
                continue;
            }
            $qry .= $value['table'] . '."' . $key . '",';
        }
        $order = array_merge($order, $encuesta['order']);

        $qry .= $encuesta['select'];
        $qry .= ' FROM "DEMOGRAFICOS".' . $encuesta['from'];
        $qry .= ' demo INNER JOIN "AE"."Visitante" vis';
        $qry .= ' ON demo."idVisitante" =  vis."idVisitante"';
        $qry .= ' INNER JOIN "AE"."VisitanteEdicion" vise';
        $qry .= ' ON vise."idVisitante" =  vis."idVisitante"';
        $qry .= $where;
        $qry .= ' ORDER BY ' . $order[$post['order'][0]['column']];
        $qry .= ' ' . $post['order'][0]['dir'];
        $qry .= ' LIMIT ' . $post['length'];
        $qry .= ' OFFSET ' . $post['start'];

        $Result = $this->SQLModel->executeQuery($qry);
        return $Result;
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
        $qry .= ' INNER JOIN "AE"."Visitante" vis';
        $qry .= ' ON demo."idVisitante" = vis."idVisitante" ';
        $qry .= ' INNER JOIN "AE"."VisitanteEdicion" vise';
        $qry .= ' ON vise."idVisitante" =  vis."idVisitante"';
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

    public function getProfile($params) {
        $qry = $this->getProfileQry();
        $qry .= '"idVisitante" = ' . $params['idVisitante'];
        $qry .= ' AND "idEvento" = ' . $params['idEvento'];
        $qry .= ' AND "idEdicion" = ' . $params['idEdicion'];
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $qry = $this->getBreadcrumbQry();
        $qry .= '"idVisitante" = ' . $params['idVisitante'];
        $result_breadcrumb = $this->SQLModel->executeQuery($qry);
        if (!$result_breadcrumb['status']) {
            throw new \Exception($result_breadcrumb['data'], 409);
        }
        $result['data'] = json_decode($result['data'][0]['ListadoRespuestasJSON'], TRUE);
        $result['Nombre'] = $result_breadcrumb['data'][0]['NombreCompleto'];
        return $result;
    }

    protected function getProfileQry() {
        $qry = 'SELECT ';
        $qry .= '"ListadoRespuestasJSON" ';
        $qry .= 'FROM ';
        $qry .= '"AE"."VisitanteEdicion" ';
        $qry .= 'WHERE ';
        return $qry;
    }

    protected function getBreadcrumbQry() {
        $qry = 'SELECT ';
        $qry .= '"NombreCompleto" ';
        $qry .= 'FROM ';
        $qry .= '"AE"."Visitante" ';
        $qry .= 'WHERE ';
        return $qry;
    }

    public function getQuestions($params) {
        $cache_name = "preguntas";
        $ruta = $this->base_path_cache . $cache_name . '.json';
        if (file_exists($ruta)) {
            $result_cache = file_get_contents($ruta);
            $result = Array('status' => TRUE, 'data' => json_decode($result_cache, TRUE));
        } else {
            $qry = $this->getQuestionsQry();
            $qry .= '"idEvento" = ' . $params['idEvento'];
            $qry .= ' AND "idEdicion" = ' . $params['idEdicion'];
            $result = $this->SQLModel->executeQuery($qry);
            if (!$result['status']) {
                throw new \Exception($result['data'], 409);
            }
            $data = array();
            foreach ($result['data'] as $key => $value) {
                $data[$value['idPregunta']] = $value;
            }
            $this->writeJSON($ruta, $data);
            clearstatcache();
            $result['data'] = $data;
        }
        return $result;
    }

    protected function getQuestionsQry() {
        $qry = 'SELECT ';
        $qry .= '"idPregunta", ';
        $qry .= '"PreguntaES", ';
        $qry .= '"PreguntaEN" ';
        $qry .= 'FROM ';
        $qry .= '"AE"."Pregunta" ';
        $qry .= 'WHERE ';
        return $qry;
    }

    public function getAnswers($idEvento, $idEdicion) {
        $cache_name = "respuestas";
        $ruta = $this->base_path_cache . $cache_name . '.json';
        if (file_exists($ruta)) {
            $result_cache = file_get_contents($ruta);
            $result = Array('status' => TRUE, 'data' => json_decode($result_cache, TRUE));
        } else {
            $qry = $this->getAnswersQry();
            $qry .= '"idEvento" = ' . $idEvento;
            $qry .= ' AND "idEdicion" = ' . $idEdicion . ' )';
            $result = $this->SQLModel->executeQuery($qry);
            if (!$result['status']) {
                throw new \Exception($result['data'], 409);
            }
            $data = array();
            foreach ($result['data'] as $key => $value) {
                $data[$value['idRespuesta']] = $value;
            }
            $this->writeJSON($ruta, $data);
            clearstatcache();
            $result['data'] = $data;
        }
        return $result;
    }

    protected function getAnswersQry() {
        $qry = 'SELECT ';
        $qry .= '"idRespuesta", ';
        $qry .= '"idPregunta", ';
        $qry .= '"RespuestaES", ';
        $qry .= '"RespuestaEN", ';
        $qry .= '"RespuestaAbiertaEtiquetaES", ';
        $qry .= '"RespuestaAbiertaEtiquetaEN"';
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

    public function getEdicion($args = array()) {
        $qry = 'SELECT';
        $qry .= ' "idEdicion", ';
        $qry .= ' "Edicion_ES"';
        $qry .= ' FROM';
        $qry .= ' "SAS"."Edicion"';

        $result = $this->SQLModel->executeQuery($qry);

        if (($result['status'] && count($result['data']) > 0)) {
            foreach ($result['data'] as $value) {
                $data[$value['idEdicion']] = $value;
            }
            $result['data'] = $data;
        }
        return $result;
    }

    public function getEvento($args = array()) {
        $qry = 'SELECT';
        $qry .= ' "idEvento",';
        $qry .= ' "Evento_ES" ';
        $qry .= 'FROM';
        $qry .= ' "SAS"."Evento"';

        $result = $this->SQLModel->executeQuery($qry);

        if (($result['status'] && count($result['data']) > 0)) {
            foreach ($result['data'] as $value) {
                $data[$value['idEvento']] = $value;
            }
            $result['data'] = $data;
        }
        return $result;
    }

    public function getLecturas($idEdicion, $idVisitante) {
        $qry = 'SELECT';
        $qry .= ' vise."idVisitante", ';
        $qry .= ' lec."Fecha", ';
        $qry .= ' lec."Hora", ';
        $qry .= ' lec."BadgeID", ';
        $qry .= ' pue."NombrePuerta" ';
        $qry .= ' FROM ';
        $qry .= ' "AE"."VisitanteEdicion" vise ';
        $qry .= ' INNER JOIN "LECTORAS"."Lecturas" lec ON vise."idVisitante" = lec."idVisitante" ';
        $qry .= ' INNER JOIN "LECTORAS"."EmpresaScanner" es ON lec."idEmpresaScanner" = es."idEmpresaScanner" ';
        $qry .= ' LEFT JOIN "LECTORAS"."Puerta" pue ON pue."idPuerta" = es."idPuerta" ';
        $qry .= ' WHERE ';
        $qry .= ' vise."idEdicion" = ' . $idEdicion . '';
        $qry .= ' AND vise."idVisitante" = ' . $idVisitante . '';
        
        $result = $this->SQLModel->executeQuery($qry);
//        if (($result['status'] && count($result['data']) > 0)) {
//            foreach ($result['data'] as $value) {
//                $data[$value['idEdicion']] = $value;
//            }
//            $result['data'] = $data;
//        }
        return $result;
    }

}
