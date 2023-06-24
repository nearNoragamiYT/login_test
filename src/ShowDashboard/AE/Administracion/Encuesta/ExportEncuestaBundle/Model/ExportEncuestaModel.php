<?php

namespace ShowDashboard\AE\Administracion\Encuesta\ExportEncuestaBundle\Model;

/**
 * Description of ExportEncuestaModel
 *
 * @author Raúl B
 */
use Utilerias\SQLBundle\Model\SQLModel;

class ExportEncuestaModel {

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getEncuesta($idEvento, $idEdicion) {
        $qry = 'SELECT "idEncuesta", "idEvento", "idEdicion", "Activa", "Encabezado" ';
        $qry.= 'FROM "AE"."Encuesta" ';
        $qry.= 'WHERE "AE"."Encuesta"."Activa" = 1';
        $qry.= 'AND "AE"."Encuesta"."idEvento" = ' . $idEvento;
        $qry.= 'AND "AE"."Encuesta"."idEdicion" =' . $idEdicion . ';';
        $result = $this->SQLModel->executeQuery($qry);
        unset($result['query']);
        return $result;
    }

    public function getPregunta($idEvento, $idEdicion) {
        $qry = 'SELECT "idPregunta", "idPreguntaTipo", "idEncuesta", "Activa", "PreguntaES", "PreguntaEN", "Columnas", "FechaCreacion", "FechaModificacion", "cSubpregunta", "zzOrden", "idEvento", "idEdicion" ';
        $qry.= 'FROM "AE"."Pregunta" ';
        $qry.= 'WHERE "AE"."Pregunta"."idEvento" = ' . $idEvento;
        $qry.= ' AND "AE"."Pregunta"."idEdicion" = ' . $idEdicion;
        $qry.= ' ORDER BY "idPregunta" ASC;';
        $result = $this->SQLModel->executeQuery($qry);
        unset($result['query']);
        return $result;
    }

    public function getRespuesta() {
        $qry = 'SELECT "idRespuesta", "idPregunta", "RespuestaES", "RespuestaEN", "zzOrden", "Activa", ';
        $qry.= '"RespuestaAbierta", "RespuestaAbiertaEtiquetaES", "RespuestaAbiertaEtiquetaEN", "DescripcionES", "DescripcionEN" ,';
        $qry.= '"FechaCreacion", "FechaModificacion", "idVisitanteTipo", "RespuestaAbiertaObligatoria", "cHabilitaPregunta", "cDeshabilitaPregunta"';
        $qry.= 'FROM "AE"."Respuesta"';
        $qry.= 'ORDER BY "idRespuesta" ASC;';
        $result = $this->SQLModel->executeQuery($qry);
        unset($result['query']);
        return $result;
    }

    public function getAP() {
        $qry = 'SELECT "idRespuestaPregunta", "idRespuesta", "idPregunta", "Habilita" ';
        $qry.= 'FROM "AE"."ActivaPregunta"';
        $qry.= 'ORDER BY "idRespuestaPregunta" ASC;';
        $result = $this->SQLModel->executeQuery($qry);
        unset($result['query']);
        return $result;
    }

    public function getValP() {
        $qry = 'SELECT "idValidacionPregunta", "idPregunta", "idValidacion", "Valor"';
        $qry.= 'FROM "AE"."ValidacionPregunta"';
        $qry.= 'ORDER BY "idValidacionPregunta" ASC;';
        $result = $this->SQLModel->executeQuery($qry);
        unset($result['query']);
        return $result;
    }

}
