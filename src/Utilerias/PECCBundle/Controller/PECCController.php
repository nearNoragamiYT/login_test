<?php

namespace Utilerias\PECCBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\PECCBundle\Model\PECCModel;

class PECCController extends Controller {

    protected $PECCModel;

    public function __construct() {
        $this->PECCModel = new PECCModel();
    }

    public function paisesAction($lang) {
        $result = $this->PECCModel->getPaises($lang);
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    public function estadosAction($idPais) {
        $result = $this->PECCModel->getEstados($idPais);
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    
    public function codigoPostalAction($codigoPostal) {
        $result = $this->PECCModel->getPECC($codigoPostal);
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
