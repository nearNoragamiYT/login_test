<?php

namespace WebService\RestBundle\Controller;

date_default_timezone_set("America/Mexico_City");

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use \Symfony\Component\HttpFoundation\Request;
use WebService\RestBundle\Model\PeccWSModel;
use WebService\RestBundle\Controller\ApiWS;

class PeccWSController extends FOSRestController {

    protected $wsmodel, $api;

    public function __construct() {
        $this->wsmodel = new PeccWSModel();
        $this->api = new ApiWS();
    }

    public function peccAction(Request $request) {
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
        
        $result = $this->wsmodel->getPecc();
        if (!$result['status']) {
            $view->setData(array('status' => false, 'mensaje' => $result['mensaje']));
            return $this->handleView($view);
        }
        
        $view->setData(array('status' => true, 'data' => $result['data']));
        return $this->handleView($view);        
    }

}
