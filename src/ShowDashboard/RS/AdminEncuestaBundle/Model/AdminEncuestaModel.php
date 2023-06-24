<?php

namespace ShowDashboard\RS\AdminEncuestaBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

class AdminEncuestaModel {

    protected $SQLModel, $schema = "AE";

    public function __construct() {//se crea la conexion
        $this->SQLModel = new SQLModel();
        $this->SQLModel->setSchema($this->schema);
    }

    public function getPreguntas() {//obtenemos preguntas
        $qry = ' SELECT ';
        $qry .= ' pre."idPregunta", ';
        $qry .= ' pre."Activa", ';
        $qry .= ' pre."PreguntaES", ';
//        $qry .= ' pre."ClubElite", ';
        $qry .= ' eve."idEvento", ';
        $qry .= ' eve."Evento_ES", ';
        $qry .= ' edi."idEdicion", ';
        $qry .= ' edi."Edicion_ES" ';
        $qry .= ' FROM "AE"."Pregunta" pre ';
        $qry .= ' JOIN "SAS"."Evento" eve ON eve."idEvento" = pre."idEvento" ';
        $qry .= ' JOIN "SAS"."Edicion" edi ON edi."idEdicion" = pre."idEdicion" ';
        $qry .= ' ORDER BY "idPregunta" ASC';

        $result = $this->SQLModel->executeQuery($qry);

        if (($result['status'] && count($result['data']) > 0)) {
            foreach ($result['data'] as $value) {
                $data[$value['idPregunta']] = $value;
            }
            $result['data'] = $data;
        }

        return $result;
    }

    public function getRespuestas($where) {
        $qry = ' SELECT ';
        $qry .= ' "idRespuesta", ';
        $qry .= ' "idPregunta", ';
        $qry .= ' "RespuestaES", ';
        $qry .= ' "Activa" ';
        $qry .= ' FROM "AE"."Respuesta" ';
        $qry .= ' WHERE "idPregunta" = ' . $where;

        $result = $this->SQLModel->executeQuery($qry);

        return $result;
    }

    public function updateRespuestas($data, $where) {
        $result = $this->SQLModel->updateFromTable("Respuesta", $data, $where);

        if (!$result['status']) {
            return $result;
        }
        return $result;
    }

    public function getStatus($idPregunta) {
        $qry = ' SELECT ';
        $qry .= ' "idPregunta", ';
        $qry .= ' "Activa" ';
        $qry .= ' FROM "AE"."Pregunta" ';
        $qry .= ' WHERE "idPregunta" = ' . $idPregunta;

        $result = $this->SQLModel->executeQuery($qry);

        return $result;
    }

    public function updatePregunta($data, $where) {
        $result = $this->SQLModel->updateFromTable("Pregunta", $data, $where);

        if (!$result['status']) {
            return $result;
        }
        return $result;
    }

    public function getRout($idEdicion) {
        $qry = ' SELECT ';
        $qry .= ' "idEdicion",';
        $qry .= ' "LinkAE" ';
        $qry .= ' FROM "SAS"."Edicion" ';
        $qry .= ' WHERE "idEdicion"=' . $idEdicion;
        
        $result = $this->SQLModel->executeQuery($qry);

        return $result;
    }

}
