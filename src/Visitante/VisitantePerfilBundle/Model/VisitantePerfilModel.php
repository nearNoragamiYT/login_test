<?php

namespace Visitante\VisitantePerfilBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Visitante\VisitanteBundle\Model\MainModel;

class VisitantePerfilModel extends MainModel {

    Public function __construct(ContainerInterface $container = null) {
        parent::__construct($container);
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
        $qry_order = '';

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
        $qry .= ' ON demo."idVisitante" = vis."idVisitante"';
        $qry .= ' INNER JOIN "AE"."VisitanteEdicion" vise';
        $qry .= ' ON vise."idVisitante" = vis."idVisitante" ';
        $qry .= $where;
        $qry_order .= 'ORDER BY ';
        foreach ($post['order'] as $value) {
            $qry_order .= $order[$value['column']] . ' ' . $value['dir'] . ',';
        }
        $qry .= $qry_order = substr($qry_order, 0, -1);
        $qry .= ' LIMIT ' . $post['length'];
        $qry .= ' OFFSET ' . $post['start'];

        $Result = $this->SQLModel->executeQuery($qry);
        $Result['order'] = $qry_order;
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

    public function getExport($param) {
        $qry = 'SELECT ';
        $qry .= $param['select'];
        $qry .= ' FROM "AE"."Visitante" vis';
        $qry .= ' INNER JOIN "AE"."VisitanteEdicion" vise';
        $qry .= ' ON vise."idVisitante" = vis."idVisitante" ';
        $qry .= $param['where'];
        $qry .= $param['order'];
       
        return $this->SQLModel->executeQuery($qry);
    }
}
