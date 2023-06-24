<?php

namespace WebService\RestBundle\Model;

use WebService\RestBundle\Model\mainModel;

class WebServiceModel extends mainModel {

    public function __construct($idEvento = 1, $idEdicion = 1) {
        parent::__construct($idEvento, $idEdicion);
    }

    public function findUser($args) {
        $qry = 'SELECT ';
        $qry .= $this->getFieldsLogin();
        $qry .= 'FROM "SAS"."UsuarioServicio" ';
        $qry .= '{where}';
        $result = $this->PGModelSAS->execQueryString($qry, $args);
        return $result;
    }

    public function getFieldsLogin() {
        $fileds = '"idUsuarioServicio", ';
        $fileds .= '"idEdicion", ';
        $fileds .= '"idEvento", ';
        $fileds .= '"Activo", ';
        $fileds .= '"ListaIP" ';
        return $fileds;
    }

    public function findSession($args) {
        $qry = 'SELECT ';
        $qry .= $this->getFieldsSession();
        $qry .= 'FROM "SAS"."UsuarioSesion" ';
        $qry .= '{where}';
        $result = $this->PGModelSAS->execQueryString($qry, $args);
        return $result;
    }

    public function getFieldsSession() {
        $fileds = '"idUsuarioSesion", ';
        $fileds .= '"idUsuarioServicio", ';
        $fileds .= '"Token", ';
        $fileds .= '"FechaInicio", ';
        $fileds .= '"FechaFin", ';
        $fileds .= '"Ip" ';
        return $fileds;
    }

    public function updateSession($args) {
        $qry = 'UPDATE ';
        $qry .= '"SAS"."UsuarioSesion" ';
        $qry .= '{set}';
        $qry .= '{where}';
        $result = $this->PGModelSAS->execQueryString($qry, $args);
        return $result;
    }

    public function insertSession($args) {
        $qry = 'INSERT INTO ';
        $qry .= '"SAS"."UsuarioSesion" ';
        $qry .= '{columns}';
        $qry .= '{values}';
        $qry .= '{returning}';
        $result = $this->PGModelSAS->execQueryString($qry, $args);
        return $result;
    }

    public function findAccion($args) {
        $qry = 'SELECT * ';
        $qry .= 'FROM "SAS"."DetalleSesion" ';
        $qry .= '{where}';
        $result = $this->PGModelSAS->execQueryString($qry, $args);
        return $result;
    }

    public function insertAccion($args) {
        $qry = 'INSERT INTO ';
        $qry .= '"SAS"."DetalleSesion" ';
        $qry .= '{columns}';
        $qry .= '{values}';
        $result = $this->PGModelSAS->execQueryString($qry, $args);
        return $result;
    }
    
    public function updateSync() {
        $qry = 'UPDATE ';
        $qry .= '"AE"."Visitante" ';
        $qry .= 'SET "idSesion" = 0, "StatusSincronia" = 0 ';
        $qry .= 'WHERE	"StatusSincronia" = 1';
        $result = $this->PGModelSAS->execQueryString($qry);
        return $result;
    }
}
