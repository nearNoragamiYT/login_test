<?php

namespace Visitante\PerfilBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Visitante\VisitanteBundle\Model\MainModel;

class PerfilModel extends MainModel {

    Public function __construct(ContainerInterface $container = null) {
        parent::__construct($container);
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
        $qry .= '"zzOrden",';
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

}
