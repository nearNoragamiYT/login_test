<?php

namespace ShowDashboard\FP\GraphicBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\FP\GraphicBundle\Model\GraphicModel;
use Symfony\Component\HttpFoundation\Request;

class GraphicController extends Controller {

    private $model;

    const MAIN_ROUTE = "admin_floorplan_homepage";
    const SECTION = 6;

    public function __construct() {
        $this->model = new GraphicModel();
        $this->TextoModel = new TextoModel();
    }

    public function graphicAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $content = array();
        $content['App'] = $this->get('ixpo_configuration')->getApp();
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Obtenemos textos de la sección 6 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content['breadcrumb'] = $breadcrumb;
        $content['user'] = $this->getUser()->getData();
        $content['textos'] = $section_text['data'];
        $idEdicion = $session->get('idEdicion');
        $total = array(
            'quantity' => 0,
            'quantityAvailable' => 0,
            'quantityReserved' => 0,
            'quantityOccupied' => 0,
            'available' => 0,
            'occupied' => 0,
            'reserved' => 0,
            'total' => 0
        );
        $content["edicion"] = $session->get('edicion');
        $content["lang"] = $lang;
        $summary = array();
        $result = $this->model->getHallsTotal($idEdicion);
        $summary['0'] = $result['data'] == 'error' ? $total : $result['data'];
        return $this->render(
                        'ShowDashboardFPGraphicBundle:Graphic:graphic.html.twig', array('content' => $content, 'stands' => $summary, 'idEdicion' => $idEdicion, 'totalResinto' => $totalB)
        );
    }

}
