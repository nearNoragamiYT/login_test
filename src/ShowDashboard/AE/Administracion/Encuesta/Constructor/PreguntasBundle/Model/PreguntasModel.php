<?php

namespace ShowDashboard\AE\Administracion\Encuesta\Constructor\PreguntasBundle\Model;

/**
 * Description of EncuestaModel
 *
 * @author Javier
 */
use ShowDashboard\AE\Administracion\ConfiguracionBundle\Model\ConfiguracionModel;

class PreguntasModel extends ConfiguracionModel {

    public function __construct() {
        parent::__construct();
    }

    public function getPregunta($args = array()) {
        $this->SQLModel->setSchema("AE");
        $qry = 'SELECT';
        $qry .= ' p."idPregunta",';
        $qry .= ' p."idEncuesta",';
        $qry .= ' p."idEvento",';
        $qry .= ' p."idEdicion",';
        $qry .= ' p."PreguntaES",';
        $qry .= ' p."PreguntaEN",';
        $qry .= ' p."DescripcionES",';
        $qry .= ' p."DescripcionEN",';
        $qry .= ' p."idPreguntaTipo",';
        $qry .= ' p."Columnas",';
        $qry .= ' p."zzOrden",';
        
        $qry .= ' ( SELECT vp."idPregunta"';
        $qry .= ' FROM "AE"."ValidacionPregunta" vp';
        $qry .= ' WHERE vp."idPregunta" = p."idPregunta"';
        $qry .= ' AND vp."idValidacion" = 2 LIMIT 1 )';
        $qry .= ' AS "Obligatoria",';
        
        $qry .= ' p."Activa"';
        $qry .= ' FROM';
        $qry .= ' "AE"."Pregunta" p';
        $qry .= $this->SQLModel->buildWhere($args);
        $qry .= ' ORDER BY';
        $qry .= ' p."Activa" DESC,';
        $qry .= ' p."zzOrden" ASC,';
        $qry .= ' p."idPregunta" ASC';
        $result = $this->SQLModel->executeQuery($qry);
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function getRespuesta($args = array()) {
        $this->SQLModel->setSchema("AE");
        $qry = 'SELECT';
        $qry .= ' r."idPregunta",';
        $qry .= ' r."idRespuesta",';
        $qry .= ' r."RespuestaES",';
        $qry .= ' r."RespuestaEN",';
        $qry .= ' r."DescripcionES",';
        $qry .= ' r."DescripcionEN",';
        $qry .= ' r."RespuestaAbierta",';
        $qry .= ' r."RespuestaAbiertaEtiquetaES",';
        $qry .= ' r."RespuestaAbiertaEtiquetaEN",';
        $qry .= ' r."RespuestaAbiertaObligatoria",';
        $qry .= ' r."zzOrden",';
        $qry .= ' r."cHabilitaPregunta",';
        $qry .= ' r."cDeshabilitaPregunta",';
        $qry .= ' r."Activa"';
        $qry .= ' FROM';
        $qry .= ' "AE"."Pregunta" p';
        $qry .= ' JOIN "AE"."Respuesta" r ON p."idPregunta" = r."idPregunta"';
        $qry .= $this->SQLModel->buildWhere($args);
        $qry .= ' ORDER BY';
        $qry .= ' p."idPregunta" ASC,';
        $qry .= ' r."zzOrden" ASC,';
        $qry .= ' r."idRespuesta" ASC';
        $result = $this->SQLModel->executeQuery($qry);
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function getPreguntaTipo($args = array()) {
        $fields = array("idPreguntaTipo", "PreguntaTipo");
        $order = array("idPreguntaTipo" => "ASC");
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->selectFromTable("PreguntaTipo", $fields, $args, $order);
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function insertEditPregunta($data) {
        /* Si trae el id editamos, de lo contrario insertamos */
        if ($this->is_defined($data['idPregunta'])) {
            $where = array('idPregunta' => $data['idPregunta']);
            unset($data['idPregunta']);
            $this->SQLModel->setSchema("AE");
            $result = $this->SQLModel->updateFromTable('Pregunta', $data, $where, 'idPregunta');
            $this->SQLModel->setSchema("SAS");
            return $result;
        }
        unset($data['idPregunta']);
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->insertIntoTable('Pregunta', $data, 'idPregunta');
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function deletePregunta($args) {
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->deleteFromTable('Pregunta', $args);
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function insertEditRespuestas($pregunta, $respuestas) {
        if (count($respuestas) == 0) {
            return array("status" => TRUE, "data" => NULL);
        }
        $resp_tmp = array();
        foreach ($respuestas as $respuesta) {
            $respuesta["idPregunta"] = $pregunta['idPregunta'];
            if (isset($respuesta['Activa'])) {
                $respuesta['Activa'] = "1";
            } else {
                $respuesta['Activa'] = "0";
            }
            if ($respuesta["idRespuesta"] == "") {
                unset($respuesta["idRespuesta"]);
            }
            $resp_tmp[] = $respuesta;
        }

        $qry = 'SELECT';
        $qry .= ' "AE"."fn_ae_InsertaActualizaRespuestas"';
        $qry .= " ('" . json_encode($resp_tmp) . "')";
        return $this->SQLModel->executeQuery($qry);
    }

    public function deleteRespuesta($args) {
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->deleteFromTable('Respuesta', $args);
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function insertEditValidacionPreguntaObligatoria($idPregunta, $obligatoria) {
        $this->SQLModel->setSchema("AE");
        $qry = 'SELECT "AE"."fn_ae_InsertarEditarValidacionPreguntaObligatoria"(' . $idPregunta . ' , ' . $obligatoria . ')';
        $result = $this->SQLModel->executeQuery($qry);
        $this->SQLModel->setSchema("SAS");
        return $result;
    }
    
    public function reordenarElementos($items, $tabla) {
        $this->SQLModel->setSchema("AE");
        $qry = 'SELECT "AE"."fn_ae_ActualizaOrden_Preguntas-Respuestas"(\'' . $tabla . '\', \'' . json_encode($items) . '\')';
        $result = $this->SQLModel->executeQuery($qry);
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

}
