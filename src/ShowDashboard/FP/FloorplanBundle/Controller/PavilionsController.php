<?php

namespace ShowDashboard\FP\FloorplanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ShowDashboard\FP\FloorplanBundle\Model\PavilionModel;

class PavilionsController extends Controller {
  protected $model;

  public function __construct() {
    $this->model = new PavilionModel();
  }

  public function indexAction(Request $request) {
    $session = $request->getSession();
    $idEdicion = $session->get('idEdicion');
    $args["idEdicion"] = $idEdicion;
    $result = $this->model->all($args);
    return $this->response($result);
  }

  public function getAction(Request $request,$id,$sala) { //$id,$sala,$pab
      $session = $request->getSession();
      $idEvento = $session->get('idEvento');
      $args["idEvento"] = $idEvento; 
      $args["idEdicion"] = $id; 
      $args["idSala"]=$sala;
      $result = $this->model->get($args);
      return $this->response($result);
  }

  private function getData() {
    $request = $this->getRequest();
    return json_decode($request->getContent(), true);
  }

  private function response($result) {
    $response = new JsonResponse();
    $response->setData($result);
    return $response;
  }
}
