<?php

namespace WebService\RestBundle\Controller;

date_default_timezone_set("America/Mexico_City");

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use \Symfony\Component\HttpFoundation\Request;
use WebService\RestBundle\Model\WebServiceModel;

class WebServiceController extends FOSRestController {

    protected $wsmodel;
    protected $salt = '*&7/SjqjVjIsI*';

    public function __construct() {
        $this->wsmodel = new WebServiceModel();
    }

    public function loginAction(Request $request) {
        $view = View::create();

        if ($request->getMethod() != 'POST') {
            $view->setData(array('status' => false, 'mensaje' => "Metodo no permitido"));
            return $this->handleView($view);
        }

        $post = $request->request->all();
        $ip = $request->getClientIp();
        $args['where'] = array(
            array("name" => '"Usuario"', "operator" => "=", "value" => $post['user']),
            array("name" => '"Password"', "operator" => "=", "value" => $post['password'], "clause" => "AND"));

        $result_find_user = $this->wsmodel->findUser($args);
        if (!$result_find_user['status']) {
            $view->setData(array('status' => false, 'mensaje' => $result_find_user['data']));
            return $this->handleView($view);
        }

        $ips = array();
        if (count($result_find_user['data']) > 0) {
            $user = $result_find_user['data'][0];
            if ($user['Activo']) {
                $ips = explode(',', $user['ListaIP']);
            } else {
                $view->setData(array('status' => false, 'mensaje' => 'Credenciales Inactivas'));
                return $this->handleView($view);
            }
            if (in_array($ip, $ips)) {
                $arg_session['where'] = array(
                    array("name" => '"idUsuarioServicio"', "operator" => "=", "value" => $user['idUsuarioServicio']),
                    array("name" => '"Activo"', "operator" => "=", "value" => 1, "clause" => "AND"));

                $result_find_session = $this->wsmodel->findSession($arg_session);
                if (!$result_find_session['status']) {
                    $view->setData(array('status' => false, 'mensaje' => $result_find_session['data']));
                    return $this->handleView($view);
                }

                if (count($result_find_session['data']) > 0) {
                    $u_session = $result_find_session['data'][0];
                    if (strtotime($u_session['FechaFin']) > time()) {
                        $view->setData(array('status' => false, 'mensaje' => 'La Sesion aun esta activa', 'token' => $u_session['Token']));
                        return $this->handleView($view);
                    } else {
                        $arg_update["set"] = Array(
                            Array("name" => '"Activo"', "operator" => "=", "value" => 'f'));
                        $arg_update['where'] = array(
                            array("name" => '"idUsuarioServicio"', "operator" => "=", "value" => $user['idUsuarioServicio']),
                            array("name" => '"idUsuarioSesion"', "operator" => "=", "value" => $u_session['idUsuarioSesion'], "clause" => "AND"));

                        $result_update_session = $this->wsmodel->updateSession($arg_update);
                        if (!$result_update_session['status']) {
                            $view->setData(array('status' => false, 'mensaje' => $result_update_session['data']));
                            return $this->handleView($view);
                        }

                        $result_insert_session = $this->addSession($user, $ip);
                        if (!$result_insert_session['status']) {
                            $view->setData(array('status' => false, 'mensaje' => $result_insert_session['data']));
                            return $this->handleView($view);
                        }
                      
//                        $result_update_sync = $this->wsmodel->updateSync();
//                        if (!$result_update_sync['status']) {
//                            $view->setData(array('status' => false, 'mensaje' => $result_update_sync['data']));
//                            return $this->handleView($view);
//                        }

                        $view->setData(array('status' => true, 'token' => $result_insert_session['data'][0]['Token']));
                        return $this->handleView($view);
                    }
                } else {
                    $result_insert_session = $this->addSession($user, $ip);
                    if (!$result_insert_session['status']) {
                        $view->setData(array('status' => false, 'mensaje' => $result_insert_session['data']));
                        return $this->handleView($view);
                    }
                    $view->setData(array('status' => true, 'token' => $result_insert_session['data'][0]['Token']));
                    return $this->handleView($view);
                }
            } else {
                $view->setData(array('status' => false, 'mensaje' => 'IP Invalida'));
                return $this->handleView($view);
            }
        } else {
            $view->setData(array('status' => false, 'mensaje' => 'Usuario o Contraseña incorrectos'));
            return $this->handleView($view);
        }
    }

    public function addSession($user, $ip) {
        $args = array(
            array("name" => 'idUsuarioServicio', "value" => $user['idUsuarioServicio']),
            array("name" => 'Token', "value" => sha1($user['idUsuarioServicio'] . $this->salt . time())),
            array("name" => 'Activo', "value" => 't'),
            array("name" => 'Ip', "value" => $ip),
            array("name" => 'FechaInicio', "value" => date("Y-m-d H:i:s", time())),
            array("name" => 'FechaFin', "value" => date("Y-m-d H:i:s", (time() + 120))));

        $result = $this->wsmodel->insertSession($args);
        return $result;
    }

    public function paramSync() {
        
    }

}
