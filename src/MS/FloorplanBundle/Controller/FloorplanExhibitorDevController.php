<?php

namespace MS\FloorplanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use MS\FloorplanBundle\Model\FloorplanExhibitorDevModel;
use MS\FloorplanBundle\Model\LeadsDevConfiguration;
use MS\FloorplanBundle\Model\AppLeadsDevConfiguration;

/**
 * Description of FloorplanDev
 *
 * @author Ernesto L <ernestol@infoexpo.com.mx>
 */
class FloorplanExhibitorDevController extends Controller {

    protected $Textos, $args, $lang,$edicion;

    const SECTION = 8;
    const TEMPLATE = 25;

    public function __construct() {
        $this->FloorplanExhibitorDevModel = new FloorplanExhibitorDevModel();
        $this->TextoModel = new TextoModel();
        $this->LeadsDevConfiguration = new LeadsDevConfiguration();
        $this->AppLeadsDevConfiguration = new AppLeadsDevConfiguration();
    }

    public function exhibitorAction(Request $request, $idExpositor) {
        $this->mainInitial($request, $idExpositor);
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($this->lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $section_text = $this->TextoModel->getTexts($this->lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content = array();
        $content['textos'] = $section_text['data'];
        $content['general_text'] = $general_text['data'];        
        $content['idExpositor'] = $idExpositor;
        $content['NombreEvento'] = $this->edicion['Edicion_' . strtoupper($this->lang)];        
        $content['NombreExpositor'] = $this->FloorplanExhibitorDevModel->getExhibitorName($this->args);
        $content['ini'] = '2016-01-01';
        $content["FechaIni"] = '2016-01-01';
        $current = date('Y-m-d');
        $content["fin"] = $current;
        $content["FechaFin"] = $current;

        return $this->render('MSFloorplanBundle:FloorplanDev:exhibitor.html.twig', array("content" => $content));
    }

    public function exhibitorGraphicAction(Request $request, $idExpositor) {
        $this->mainInitial($request, $idExpositor);
        $result = $this->FloorplanExhibitorDevModel->getExhibitorGraphic($this->args);
        return $this->jsonResponse($result);
    }

    public function exhibitorTourAction(Request $request, $idExpositor) {
        $this->mainInitial($request, $idExpositor);
        $result = $this->FloorplanExhibitorDevModel->getExhibitorTour($this->args);
        return $this->jsonResponse($result);
    }

    public function exhibitorScannerAppAction(Request $request, $idExpositor) {
        $this->mainInitial($request, $idExpositor);
        $result = $this->FloorplanExhibitorDevModel->getExhibitorScannerApp($this->args);
        return $this->jsonResponse($result);
    }

    public function exhibitorScannerThirdAction(Request $request, $idExpositor) {
        $this->mainInitial($request, $idExpositor);
        $result = $this->FloorplanExhibitorDevModel->getExhibitorScannerThird($this->args);
        return $this->jsonResponse($result);
    }
    
    public function exhibitorInterAction(Request $request, $idExpositor) {
        $this->mainInitial($request, $idExpositor);
        $totalInfoClick = $this->FloorplanExhibitorDevModel->getExhibitorInteractions($this->args);
        return $this->jsonResponse($totalInfoClick);
    }
    
    public function exhibitorLeadsAction(Request $request, $idExpositor) {

        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        $edicion = $session->get('edicion');
        $content = array();

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['textos'] = $section_text['data'];
        $session->set('idExhibitor', $idExpositor);

        $content['general_text'] = $general_text['data'];
        $content['idExpositor'] = $idExpositor;
        $content['NombreEvento'] = $edicion['Edicion_' . strtoupper($lang)];
        $session->set('evName', $content['NombreEvento']);
        $args['idEdicion'] = $idEdicion;

        //-------- Array to Set in the thead of the Visitor Table  -----------//
        $content["visitor_table_column_categories"] = $this->LeadsDevConfiguration->getColumnCategories($content["textos"]);
        $content["visitor_table_columns"] = $this->LeadsDevConfiguration->getColumnDefs($content["textos"]);
        //-------------------------   END    ---------------------------------//

        return $this->render('MSFloorplanBundle:FloorplanDev:exhibitor_leads.html.twig', array("content" => $content));
    }

    public function exhibitorAppLeadsAction(Request $request, $idExpositor) {

        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        $edicion = $session->get('edicion');
        $content = array();

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['textos'] = $section_text['data'];
        $session->set('idExhibitor', $idExpositor);

        $content['general_text'] = $general_text['data'];
        $content['idExpositor'] = $idExpositor;
        $content['NombreEvento'] = $edicion['Edicion_' . strtoupper($lang)];
        $session->set('evName', $content['NombreEvento']);
        $args['idEdicion'] = $idEdicion;

        //-------- Array to Set in the thead of the Visitor Table  -----------//
        $content["visitor_table_column_categories"] = $this->AppLeadsDevConfiguration->getColumnCategories($content["textos"]);
        $content["visitor_table_columns"] = $this->AppLeadsDevConfiguration->getColumnDefs($content["textos"]);
        //-------------------------   END    ---------------------------------//

        return $this->render('MSFloorplanBundle:FloorplanDev:exhibitor_app_leads.html.twig', array("content" => $content));
    }

    //-------------------------   Funciones Genericas    ---------------------------------//
    public function jsonResponse($data) {
        return new Response(json_encode($data), 200, Array('Content-Type', 'text/json'));
    }

    public function mainInitial($request, $idExpositor) {
        $session = $request->getSession();
        $this->lang = $session->get('lang');
        $this->edicion = $session->get('edicion');
        $this->args['idExpositor'] = $idExpositor;
        $this->args['lang'] = $session->get('lang');
        $this->args['idEdicion'] = $session->get('idEdicion');
        if (empty($this->args['idEdicion']) || !isset($this->args['idEdicion']) || $this->args['idEdicion'] == "") {
            return $this->redirectToRoute('dashboard');
        }
        $this->args['idEvento'] = $session->get('idEvento');
        $post = $request->request->all();
        $this->args['FechaIni'] = $post['Start'];
        $this->args['FechaFin'] = $post['End'];
    }
}
