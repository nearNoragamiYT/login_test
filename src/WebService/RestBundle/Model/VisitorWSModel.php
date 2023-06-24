<?php

namespace WebService\RestBundle\Model;

use WebService\RestBundle\Model\mainModel;

class VisitorWSModel extends mainModel {

    public function __construct($idEvento = 1, $idEdicion = 1) {
        parent::__construct($idEvento, $idEdicion);
    }

    public function getVisitors($param) {
        $qry = ' SELECT ';
        $qry .= $this->getFieldsVisitante();
        $qry .= ' FROM "AE"."Visitante" vis';
        $qry .= ' INNER JOIN "AE"."VisitanteEdicion" vise';
        $qry .= ' ON vis."idVisitante" =  vise."idVisitante"';
        $qry .= ' LEFT JOIN "AE"."Compra" com';
        $qry .= ' ON com."idVisitante" = vise."idVisitante"';
        $qry .= ' AND com."idEvento" = vise."idEvento"';
        $qry .= ' AND com."idEdicion" = vise."idEdicion"';
        $qry .= ' AND com."idCompraStatus" = 1';
        $qry .= ' WHERE vise."idEdicion" = ' . $param['idEdicion'];
        $qry .= ' AND date(vis."FechaAlta_AE") = \'' . $param['fecha'] . "'";
        $qry .= ' AND vis."Prueba" <> 1';
        $qry .= ' GROUP BY';
        $qry .= $this->getGroupByVisitante();

        $result = $this->PGModelSAS->execQueryString($qry);
        return $result;
    }

    public function getFieldsVisitante() {
        $fileds = ' vis."idVisitante",';
        $fileds .= ' "Nombre",';
        $fileds .= ' "ApellidoPaterno",';
        $fileds .= ' "ApellidoMaterno",';
        $fileds .= ' "Email",';
        $fileds .= ' "DE_AreaPais",';
        $fileds .= ' "DE_AreaCiudad",';
        $fileds .= ' "DE_Telefono",';
        $fileds .= ' "DE_RazonSocial",';
        $fileds .= ' "DE_Cargo",';
        $fileds .= ' "Asociado",';
        $fileds .= ' "Comprador",';
        $fileds .= ' "Preregistrado",';
        $fileds .= ' vis."FechaAlta_AE",';
        $fileds .= ' "FechaPreregistro",';
        $fileds .= ' "CompraCompleta",';
        $fileds .= ' "idCompraStatus"';
        return $fileds;
    }

    public function getGroupByVisitante() {
        $fileds = ' vis."idVisitante",';
        $fileds .= ' "Preregistrado",';
        $fileds .= ' "FechaPreregistro",';
        $fileds .= ' "CompraCompleta",';
        $fileds .= ' "idCompraStatus"';
        return $fileds;
    }

    public function updateStatusVis($args) {
        $qry = 'UPDATE ';
        $qry .= '"AE"."Visitante" ';
        $qry .= '{set}';
        $qry .= '{where}';
        $result = $this->PGModelSAS->execQueryString($qry, $args);
        return $result;
    }

    public function getEncuesta() {
        $qry = 'SELECT ';
        $qry .= $this->getFieldsEncuesta();
        $qry .= 'FROM "AE"."Encuesta" ';
        $qry .= 'WHERE "idEncuesta" IN (1) ';
        $qry .= 'ORDER BY "idEncuesta"';
        $result = $this->PGModelSAS->execQueryString($qry);
        return $result;
    }

    public function getFieldsEncuesta() {
        $fileds = '"idEncuesta" AS "Encuesta_id", ';
        $fileds .= '"Encabezado" AS "Evento" ';
        return $fileds;
    }

    public function getPreguntas() {
        $qry = 'SELECT ';
        $qry .= $this->getFieldsPreguntas();
        $qry .= 'FROM "AE"."Pregunta" ';
        $qry .= 'WHERE "idEncuesta" IN (1) ';
        $qry .= 'ORDER BY "idPregunta"';
        $result = $this->PGModelSAS->execQueryString($qry);
        return $result;
    }

    public function getFieldsPreguntas() {
        $fileds = '"idPregunta" AS "Pregunta_id", ';
        $fileds .= '"idEncuesta" AS "Encuesta_id", ';
        $fileds .= '"PreguntaES" AS "Pregunta_ES", ';
        $fileds .= '"PreguntaEN" AS "Pregunta_EN" ';
        return $fileds;
    }

    public function getRespuestas($param) {
        $qry = 'SELECT ';
        $qry .= $this->getFieldsRespuestas();
        $qry .= 'FROM "AE"."Respuesta" ';
        $qry .= 'WHERE "idPregunta" IN (' . $param . ') ';
        $qry .= 'ORDER BY "idRespuesta"';
        $result = $this->PGModelSAS->execQueryString($qry);
        return $result;
    }

    public function getFieldsRespuestas() {
        $fileds = '"idRespuesta" AS "Respuesta_id", ';
        $fileds .= '"idPregunta" AS "Pregunta_id", ';
        $fileds .= '"RespuestaES" AS "Respuesta_ES", ';
        $fileds .= '"RespuestaEN" AS "Respuesta_EN" ';
        return $fileds;
    }

    public function getCupones() {
        $qry = 'SELECT ';
        $qry .= $this->getFieldsCupones();
        $qry .= 'FROM "AE"."Cupon" ';
        $qry .= 'WHERE "idEdicion" IN (1) ';
        $qry .= 'ORDER BY "idEdicion"';
        $result = $this->PGModelSAS->execQueryString($qry);
        return $result;
    }

    public function getFieldsCupones() {
        $fileds = '"idCupon" AS "id_CodigoCampania", ';
        $fileds .= '"Cupon" AS "Codigo", ';
        $fileds .= '"DescripcionES" AS "Descripcion", ';
        $fileds .= '"idEdicion" AS "Edicion" ';
        return $fileds;
    }

    public function getCatalog() {
        $cache_name = "visitor_catalog";
        $ruta = $this->base_path_cache . $cache_name . '.json';
        if (file_exists($ruta)) {
            $result_cache = file_get_contents($ruta);
            $result = Array('status' => true, 'data' => json_decode($result_cache, TRUE));
            $cupones = $this->getCupones();
            if (!$cupones['status']) {
                return array('status' => false, 'mensaje' => $cupones['data']);
            }
            $result['data']['codigos'] = $cupones['data'];
        } else {
            $encuesta = $this->getEncuesta();
            if (!$encuesta['status']) {
                return array('status' => false, 'mensaje' => $encuesta['data']);
            }
            $preguntas = $this->getPreguntas();
            if (!$preguntas['status']) {
                return array('status' => false, 'mensaje' => $preguntas['data']);
            }
            foreach ($preguntas['data'] AS $key) {
                $param .= $key['Pregunta_id'] . ',';
            }
            $param = substr($param, 0, -1);
            $respuestas = $this->getRespuestas($param);
            if (!$respuestas['status']) {
                return array('status' => false, 'mensaje' => $respuestas['data']);
            }

            $catalogo = array('encuesta' => $encuesta['data'], 'preguntas' => $preguntas['data'], 'respuestas' => $respuestas['data']);
            $catalogo['registo_origen'] = array(array('RegistroOrigen' => 1, 'Origen' => 'Registro Elite'),
                array('RegistroOrigen' => 2, 'Origen' => 'Registro Plano'),
                array('RegistroOrigen' => 3, 'Origen' => 'Registro App Movil'));
            $this->writeJSON($ruta, $catalogo);
            clearstatcache();
            $cupones = $this->getCupones();
            if (!$cupones['status']) {
                return array('status' => false, 'mensaje' => $cupones['data']);
            }
            $catalogo['codigos'] = $cupones['data'];
            $result = Array('status' => true, 'data' => $catalogo);
        }
        return $result;
    }

    public function updateSync() {
        $qry = 'UPDATE ';
        $qry .= '"AE"."Visitante" ';
        $qry .= 'SET "idSesion" = 0, "StatusSincronia" = 0 ';
        $qry .= 'WHERE  "StatusSincronia" = 1';
        $result = $this->PGModelSAS->execQueryString($qry);
        return $result;
    }

    public function getEdicion($token) {
        $qry = 'SELECT';
        $qry .= ' "idEvento",';
        $qry .= ' "idEdicion"';
        $qry .= ' FROM "SAS"."UsuarioServicio"';
        $qry .= ' WHERE "idUsuarioServicio" =( ';
        $qry .= ' SELECT "idUsuarioServicio"';
        $qry .= ' FROM "SAS"."UsuarioSesion"';
        $qry .= ' WHERE  "Token" = \'' . $token . '\' )';
        $result = $this->PGModelSAS->execQueryString($qry);
        return $result;
    }

    public function getVisitor($args) {
        $qry = 'SELECT';
        $qry .= ' v."idVisitante",';
        $qry .= ' v."Email",';
        $qry .= ' v."Nombre",';
        $qry .= ' v."ApellidoPaterno",';
        $qry .= ' v."ApellidoMaterno",';
        $qry .= ' v."DE_Cargo",';
        $qry .= ' v."DE_RazonSocial",';
        $qry .= ' v."Asociado",';
        $qry .= ' v."Comprador",';
        $qry .= ' v."idStatusAutorizacion",';
        $qry .= ' vi."Preregistrado"';
        $qry .= ' FROM "AE"."Visitante" v';
        $qry .= ' INNER JOIN "AE"."VisitanteEdicion" vi';
        $qry .= ' ON v."idVisitante" = vi."idVisitante"';
        $qry .= ' WHERE v."Email" = ' . $args['Email'];
        $qry .= ' AND v."idVisitante" = ' . $args['idVisitante'];
        $qry .= ' AND vi."idEdicion" = ' . $args['idEdicion'];
        $result = $this->PGModelSAS->execQueryString($qry);
        return $result;
    }

    public function getVisitorType() {
        $qry = 'SELECT';
        $qry .= ' "idVisitanteTipo",';
        $qry .= ' "VisitanteTipoES",';
        $qry .= ' "VisitanteTipoEN"';
        $qry .= ' FROM "AE"."VisitanteTipo"';
        $result = $this->PGModelSAS->execQueryString($qry);
        return $result;
    }

    public function sanear_string($string, $type = 'other') {
        $string = trim($string);
        $string = str_replace(array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string);
        $string = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string);
        $string = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string);
        $string = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string);
        $string = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string);
        $string = str_replace(array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $string);
        //Esta parte se encarga de eliminar cualquier caracter extrano
        if ($type == 'email') {
            $string = str_replace(array("¨", "º", "-", "~", "#", "|", "!", "·", "$", "%", "&", "/", "(", ")", "?", "'", "¡", "¿", "[", "^", "<code>", "]", "+", "}", "{", "¨", "´", ">", "< "), '', $string);
        } else {
            $string = str_replace(array("¨", "º", "-", "~", "#", "@", "|", "!", "·", "$", "%", "&", "/", "(", ")", "?", "'", "¡", "¿", "[", "^", "<code>", "]", "+", "}", "{", "¨", "´", ">", "< "), '', $string);
        }
        return $string;
    }

}
