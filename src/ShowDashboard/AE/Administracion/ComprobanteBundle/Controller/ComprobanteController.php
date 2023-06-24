<?php

namespace ShowDashboard\AE\Administracion\ComprobanteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ComprobanteController extends Controller {

    public function comprobanteAction() {
        return $this->render('ShowDashboardAEAdministracionComprobanteBundle:Comprobante:showComprobante.html.twig');
    }

}
