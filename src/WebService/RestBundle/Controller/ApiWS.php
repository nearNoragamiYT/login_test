<?php

namespace WebService\RestBundle\Controller;

date_default_timezone_set("America/Mexico_City");

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use WebService\RestBundle\Model\WebServiceModel;

class ApiWS {

    protected $model;

    public function __construct() {
        $this->model = new WebServiceModel();
    }

    public function check($token, $ruta) {

        $arg_session['where'] = array(
            array("name" => '"Token"', "operator" => "=", "value" => $token),
            array("name" => '"Activo"', "operator" => "=", "value" => 1, "clause" => "AND"));

        $result_find_session = $this->model->findSession($arg_session);
        if (!$result_find_session['status']) {
            return array('status' => false, 'mensaje' => $result_find_session['data']);
        }

        if (count($result_find_session['data']) > 0 && strtotime($result_find_session['data'][0]['FechaFin']) >= time()) {
            $u_session = $result_find_session['data'][0];
            $arg_accion['where'] = array(
                array("name" => '"idUsuarioSesion"', "operator" => "=", "value" => $u_session['idUsuarioSesion']),
                array("name" => '"Token"', "operator" => "=", "value" => $u_session['Token'], "clause" => "AND"),
                array("name" => '"Servicio"', "operator" => "=", "value" => $ruta, "clause" => "AND"));

            $result_find_accion = $this->model->findAccion($arg_accion);
            if (!$result_find_accion['status']) {
                return array('status' => false, 'mensaje' => $result_find_accion['data']);
            }

//            if (count($result_find_accion['data']) > 0) {
//                return array('status' => false, 'mensaje' => 'Servicio consumido durante la Sesion actual');
//            }

            $result_insert_accion = $this->addAccion($u_session, $ruta);
            if (!$result_insert_accion['status']) {
                return array('status' => false, 'mensaje' => $result_insert_accion['data']);
            }            
            return array('status' => true, 'data' => $u_session);
            
        } else {
            return array('status' => false, 'mensaje' => 'Sesion Expirada');
        }
    }

    public function addAccion($user, $ruta) {
        $args = array(
            array("name" => 'idUsuarioSesion', "value" => $user['idUsuarioSesion']),
            array("name" => 'Token', "value" => $user['Token']),
            array("name" => 'Servicio', "value" => $ruta));

        $result = $this->model->insertAccion($args);
        return $result;
    }

}
