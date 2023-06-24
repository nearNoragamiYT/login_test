<?php

namespace ShowDashboard\AE\Administracion\RedSocialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RedSocialController extends Controller {

    public function redSocialAction() {
        return $this->render('ShowDashboardAEAdministracionRedSocialBundle:RedSocial:showRedSocial.html.twig');
    }

}
