<?php

namespace ShowDashboard\FP\FloorplanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ShowDashboard\FP\FloorplanBundle\Model\BoothModel;

class StandsController extends Controller {

    private $model;

    public function __construct() {
        $this->model = new BoothModel();
    }

    public function allAction($pavilionId) {        
        $request = $this->getRequest();
        $session = $request->getSession();
        $args["idEvento"] = $session->get('idEvento');
        $args = array(
            '"idEdicion"' => $app['idEdicion']
        );
        if ($session->get('idPabellon') !== null)
            $args['"idPabellon"'] = $session->get('idPabellon');
        $result = $this->model->all($args);
        return $this->response($result);
    }

    public function updateAction(Request $request) {
        $result = $this->model->update($this->getData($request));        
        return $this->response($result);
    }

    public function createAction(Request $request) {
        $data = $this->getData($request);
        $result = $this->model->create($data);        
        return $this->response($result);
    }

    public function destroyAction(Request $request) {
        $data = $this->getData($request);
        $result = $this->model->delete($data);
        return $this->response($result);
    }

    private function getData($request) {
        return json_decode($request->getContent(), true);
    }

    private function response($result) {
        $response = new JsonResponse();
        $response->setData($result);
        return $response;
    }

}
