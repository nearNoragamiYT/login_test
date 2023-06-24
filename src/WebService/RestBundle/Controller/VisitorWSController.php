<?php

namespace WebService\RestBundle\Controller;

date_default_timezone_set("America/Mexico_City");

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use \Symfony\Component\HttpFoundation\Request;
use WebService\RestBundle\Model\VisitorWSModel;
use WebService\RestBundle\Controller\ApiWS;
use date;

class VisitorWSController extends FOSRestController {

    protected $wsmodel, $api;
    private $salt = '*;7/SjqjVjIsI*';
    private $idEdicion = 9;

    public function __construct() {
        $this->wsmodel = new VisitorWSModel();
        $this->api = new ApiWS();
    }

    public function getVisitorsAction(Request $request) {
        $view = View::create();
        $ruta = $request->getPathInfo();

        if ($request->getMethod() != 'POST') {
            $view->setData(array('status' => false, 'mensaje' => "Metodo Request no permitido"));
            return $this->handleView($view);
        }

        $post = $request->request->all();
        if (!isset($post['date'])) {
            $view->setData(array('status' => false, 'mensaje' => "Faltan Parametros"));
            return $this->handleView($view);
        }

        if (!$this->checkFecha($post['date'])) {
            $view->setData(array('status' => false, 'mensaje' => "Formato de Fecha invalido"));
            return $this->handleView($view);
        }

        $params = array("idEdicion" => $this->idEdicion, "fecha" => str_replace(' ', '', $post['date']));
        $result = $this->wsmodel->getVisitors($params);

        if (!$result['status']) {
            $view->setData(array('status' => false, 'mensaje' => $result['data']));
            return $this->handleView($view);
        }

        $data = $this->formatVisitors($result['data']);
        $view->setData(array('status' => true, 'data' => $data));
        return $this->handleView($view);
    }

    public function setSyncVisitorsAction(Request $request) {
        $view = View::create();
        $ruta = $request->getPathInfo();

        if ($request->getMethod() != 'GET') {
            $view->setData(array('status' => false, 'mensaje' => "Metodo no permitido"));
            return $this->handleView($view);
        }

        $get = $request->query->all();
        if (!isset($get['token']) || !isset($get['sync_id']) || !isset($get['response'])) {
            $view->setData(array('status' => false, 'mensaje' => "Faltan Parametros"));
            return $this->handleView($view);
        }

        $check = $this->api->check($get['token'], $ruta);
        if (!$check['status']) {
            $view->setData(array('status' => false, 'mensaje' => $check['mensaje']));
            return $this->handleView($view);
        }
        $u_session = $check['data'];

        if ($get['response'] == 1) {
            $arg_update['set'] = array(Array("name" => '"StatusSincronia"', "operator" => "=", "value" => 2));
            $arg_update['where'] = array(array("name" => '"idSesion"', "operator" => "=", "value" => $get['sync_id']));

            $result = $this->wsmodel->updateStatusVis($arg_update);
            if (!$result['status']) {
                $view->setData(array('status' => false, 'mensaje' => $result['data']));
                return $this->handleView($view);
            }
        } else {
            $this->wsmodel->updateSync();
        }

        $view->setData(array('status' => true, 'mensaje' => 'Sincronizado'));
        return $this->handleView($view);
    }

    public function CatalogAction(Request $request) {
        $view = View::create();
        $ruta = $request->getPathInfo();

        if ($request->getMethod() != 'GET') {
            $view->setData(array('status' => false, 'mensaje' => "Metodo no permitido"));
            return $this->handleView($view);
        }

        $get = $request->query->all();
        if (!isset($get['token'])) {
            $view->setData(array('status' => false, 'mensaje' => "Faltan Parametros"));
            return $this->handleView($view);
        }

        $check = $this->api->check($get['token'], $ruta);
        if (!$check['status']) {
            $view->setData(array('status' => false, 'mensaje' => $check['mensaje']));
            return $this->handleView($view);
        }
        $u_session = $check['data'];

        $result = $this->wsmodel->getCatalog();
        if (!$result['status']) {
            $view->setData(array('status' => false, 'mensaje' => $result['mensaje']));
            return $this->handleView($view);
        }

        $view->setData(array('status' => true, 'data' => $result['data']));
        return $this->handleView($view);
    }

    public function getVisitorAction(Request $request) {
        $view = View::create();
        $ruta = $request->getPathInfo();

        if ($request->getMethod() != 'POST') {
            $view->setData(array('status' => false, 'mensaje' => "Metodo no permitido"));
            return $this->handleView($view);
        }

        $post = $request->request->all();

        if (!isset($post["email"]) || $post["email"] == null || $post["email"] == "" || $post["email"] == "*") {
            $view->setData(array('status' => FALSE, 'data' => array(), "mensaje" => "Email Requerido"));
            return $this->handleView($view);
        }
        if (!isset($post["id"]) || $post["id"] == null || $post["id"] == "" || $post["id"] == "*") {
            $view->setData(array('status' => FALSE, 'data' => array(), "mensaje" => "ID Requerido"));
            return $this->handleView($view);
        }
        if (!filter_var($post["email"], FILTER_VALIDATE_EMAIL)) {
            $view->setData(array('status' => FALSE, 'data' => array(), "message" => "Formato de Email invalido"));
            return $this->handleView($view);
        }

        $email = $this->wsmodel->sanear_string($post["email"], 'email');
        $args = array(
            'Email' => "'" . mb_strtolower(strtolower($email), 'UTF-8') . "'",
            'idVisitante' => $post["id"],
            'idEdicion' => $this->idEdicion,
        );

        $result = $this->wsmodel->getVisitor($args);
        if (!$result['status']) {
            $view->setData(array('status' => false, 'mensaje' => $result['mensaje']));
            return $this->handleView($view);
        }
        if ($result['data'] == null && count($result['data']) == 0) {
            $view->setData(array('status' => TRUE, 'data' => array(), "message" => "No se encontro visitante, email  o contraseña inválidos"));
            return $this->handleView($view);
        }
        $visitor = $result['data'][0];

        $visitor = $this->formatVisitor($visitor);
        $view->setData(array('status' => TRUE, 'data' => $visitor));
        return $this->handleView($view);
    }

    public function formatVisitor($visitor) {
        $tempVisitor = array();
        $tempVisitor["visitor_id"] = $visitor["idVisitante"];
        $tempVisitor["email"] = $visitor["Email"];
        $tempVisitor["name"] = trim($visitor["Nombre"] . " " . $visitor["ApellidoPaterno"] . " " . $visitor["ApellidoMaterno"]);
        $tempVisitor["company"] = $visitor["DE_RazonSocial"];
        $tempVisitor["job_title"] = $visitor["DE_Cargo"];
        if ($visitor["Asociado"] == 0 && $visitor["Comprador"] == 0) {
            $tempVisitor["type"] = 'visitante';
        } elseif ($visitor["Asociado"] == 1) {
            $tempVisitor["type"] = 'asociado';
        } elseif ($visitor["Comprador"] == 1) {
            $tempVisitor["type"] = 'comprador';
        } else {
            $tempVisitor["type"] = '';
        }
        if ($visitor["Asociado"] == 1 || $visitor["Comprador"] == 1) {
            switch ($visitor["idStatusAutorizacion"]) {
                case 1:
                    $tempVisitor["status"] = "pendiente";
                    break;
                case 2:
                    $tempVisitor["status"] = "autorizado";
                    break;
                case 3:
                    $tempVisitor["status"] = "cancelado";
                    break;
            }
        } else {
            if ($visitor["Preregistrado"] == 0) {
                $tempVisitor["status"] = "pendiente";
            } else {
                $tempVisitor["status"] = "completo";
            }
        }
        return $tempVisitor;
    }

    public function formatVisitors($visitors) {
        $tempVisitor = array();
        foreach ($visitors as $key => $visitor) {
            $tempVisitor[$key]["visitor_id"] = $visitor["idVisitante"];
            $tempVisitor[$key]["name"] = $visitor["Nombre"];
            $tempVisitor[$key]["first_name"] = $visitor["ApellidoPaterno"];
            $tempVisitor[$key]["second_name"] = $visitor["ApellidoMaterno"];
            $tempVisitor[$key]["email"] = $visitor["Email"];
            $phone = (!empty($visitor["DE_AreaPais"])) ? '+' . str_replace('+', '', $visitor["DE_AreaPais"]) . ' ' : '';
            $phone .= (!empty($visitor["DE_AreaCiudad"])) ? '(' . $visitor["DE_AreaCiudad"] . ') ' : '';
            $phone .= (!empty($visitor["DE_Telefono"])) ? $visitor["DE_Telefono"] : '';
            $tempVisitor[$key]["phone"] = $phone;
            $tempVisitor[$key]["company"] = $visitor["DE_RazonSocial"];
            $tempVisitor[$key]["job_title"] = $visitor["DE_Cargo"];
            if ($visitor["Asociado"] == 0 && $visitor["Comprador"] == 0) {
                $tempVisitor[$key]["type"] = 'Visitante';
            } elseif ($visitor["Asociado"] == 1) {
                $tempVisitor[$key]["type"] = 'Asociado';
            } elseif ($visitor["Comprador"] == 1) {
                $tempVisitor[$key]["type"] = 'Comprador';
            } else {
                $tempVisitor[$key]["type"] = '';
            }
            $tempVisitor[$key]["complete_register"] = $visitor["Preregistrado"] == 1 ? true : false;
            $tempVisitor[$key]["start_date"] = $visitor["FechaAlta_AE"];
            $tempVisitor[$key]["end_date"] = $visitor["FechaPreregistro"];
            $tempVisitor[$key]["purchase"] = $visitor["CompraCompleta"] == 1 ? true : false;
            $tempVisitor[$key]["pending_purchase"] = $visitor["idCompraStatus"] == 1 ? true : false;
        }
        return $tempVisitor;
    }

    public function getVisitorTypeCache() {
        $visitorTypePath = '../var/cache/web_service/visitor_type.json';
        if (file_exists($visitorTypePath)) {
            return json_decode(file_get_contents($visitorTypePath), true);
        } else {
            $this->getVisitorType();
            return json_decode(file_get_contents($visitorTypePath), true);
        }
    }

    public function getVisitorType() {
        $visitorTypePath = '../var/cache/web_service/visitor_type.json';
        $response = array("status" => false, "data" => "");
        $result_pg = $this->wsmodel->getVisitorType();
        if (!$result_pg['status']) {
            return json_encode($response);
        }
        $tempVisitorType = array();
        foreach ($result_pg["data"] as $visitorType) {
            $tempVisitorType[$visitorType["idVisitanteTipo"]] = $visitorType;
        }
        $this->writeJSON($visitorTypePath, $tempVisitorType);
    }

    private function writeJSON($fileName, $array) {
        $json = json_encode(array('status' => TRUE, 'data' => $array));
        $fp = fopen($fileName, "w");
        fwrite($fp, $json);
        fclose($fp);
    }

    function checkFecha($date) {
        $temp = explode('-', $date);
        if (count($temp) == 3 && strlen($temp[0]) == 4 and checkdate($temp[1], $temp[2], $temp[0])) {
            return true;
        } else {
            return false;
        }
    }

}
