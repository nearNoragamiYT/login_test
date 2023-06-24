<?php

namespace ShowDashboard\FP\FloorplanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Utilerias\TextoBundle\Model\TextoModel;
use Symfony\Component\HttpFoundation\Request;

class FloorplanController extends Controller {

    const SECTION = 6;

    public function __construct() {
        $this->TextoModel = new TextoModel();
    }

    public function indexAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        if (empty($idEdicion) || !isset($idEdicion) || $idEdicion == "") {
            return $this->redirectToRoute('dashboard');
        }  
        $content = array();

        $content['App'] = $this->get('ixpo_configuration')->getApp();
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Obtenemos textos de la sección 4 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['user'] = $this->getUser()->getData();
        $content['textos'] = $section_text['data'];

        return $this->render(
                        'ShowDashboardFPFloorplanBundle:Floorplan:floorplan.html.twig', array('content' => $content)
        );
    }

}
