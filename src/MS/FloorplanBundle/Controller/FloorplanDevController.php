<?php

namespace MS\FloorplanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use MS\FloorplanBundle\Model\FloorplanDevModel;

/**
 * Description of FloorplanDev
 *
 * @author Ernesto L <ernestol@infoexpo.com.mx>
 */
class FloorplanDevController extends Controller {

    protected $Textos;

    const SECTION = 8;
    const TEMPLATE = 25;

    public function __construct() {
        $this->FloorplanDevModel = new FloorplanDevModel();
        $this->TextoModel = new TextoModel();
    }

    public function floorplanAction(Request $request) {
        $this->mainInitial($request);
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
        $content['NombreEvento'] = $this->edicion['Edicion_' . strtoupper($this->lang)];
        $content['ini'] = '2016-01-01';
        $content["FechaIni"] = '2016-01-01';
        $current = date('Y-m-d');
        $content["fin"] = $current;
        $content["FechaFin"] = $current;

        return $this->render('MSFloorplanBundle:FloorplanDev:floorplan.html.twig', array("content" => $content));
    }

    public function exhibitorDetailAction(Request $request) {
        $this->mainInitial($request);
        $result = $this->FloorplanDevModel->getDetailsExhibitor($this->args);
        $resultApp = $this->FloorplanDevModel->getDetailsAppExhibitor($this->args);
        $countStatus = 0;
        $totalTour = 0; //Total of Tours
        $totalApp = 0; //Total of App Scans
        $totalScanner = 0; //Total of Scanner Mi/Mini
        if ($result['status']) {
            $countStatus++;
        }
        if ($resultApp['status']) {
            $countStatus++;
        }
        $exhibitorsDetails = ($result['status']) ? $result['data'] : array('data' => array(
                '0' => array(
                    'idExpositor' => 0,
                    'Nombre' => 0,
                    'Booth' => 0,
                    'List' => 0,
                    'Product' => 0,
                    'Webpage' => 0,
                    'Video' => 0,
                    'Location' => 0,
                    'Views' => 0,
                    'Photo' => 0,
                    'Product Directory' => 0,
                    'Tour' => 0,
                    'Retrieval' => 0,
                    'Location' => 0
                )
            )
        );
        $exhibitorsAppDetails = ($resultApp['status']) ? $resultApp['data'] : array('data' => array(
                '0' => array(
                    'idExpositor' => 0,
                    'ScannerApp' => 0
                )
            )
        );
        $exhibitor = array();
        $exhibitors = array();
        for ($i = 0; $i < count($exhibitorsDetails); $i++) {
            foreach ($exhibitorsDetails[$i] as $key => $value) {
                $exhibitor[$exhibitorsDetails[$i]['idExpositor']][$key] = $value;
                switch ($key) {
                    case 'Recorrido':
                        $totalTour += $value;
                        break;
                    case 'Lectura':
                        $totalScanner += $value;
                        break;
                }
            }
        }
        for ($i = 0; $i < count($exhibitorsAppDetails); $i++) {
            $exhibitor[$exhibitorsAppDetails[$i]['idExpositor']]['App'] = $exhibitorsAppDetails[$i]['ScannerApp'];
            $totalApp += $exhibitorsAppDetails[$i]['ScannerApp'];
        }
        array_push($exhibitors, $exhibitor);
        $response = array();
        if ($countStatus == 2) {
            $response['status'] = true;
        } else {
            $response['status'] = false;
        }
        $response['data']['totalTour'] = $totalTour;
        $response['data']['totalApp'] = $totalApp;
        $response['data']['totalMiMini'] = $totalScanner;
        $response['data']['exhibitors'] = $exhibitors;

        return $this->jsonResponse($response);
    }

    public function searchDetailAction(Request $request) {
        $this->mainInitial($request);
        $result_text = $this->FloorplanDevModel->getDetailsSearchedText($this->args);
        $totalSearchedText = $result_text ? count($result_text['data']) : 0;
        $result_category = $this->FloorplanDevModel->getDetailsSearchedCategory($this->args);
        $totalSearchedCategory = $result_text ? count($result_category['data']) : 0;
        $countStatus = 0;
        if ($result_text['status']) {
            $countStatus++;
        }
        if ($result_category['status']) {
            $countStatus++;
        }
        $response = array();
        if ($countStatus == 2) {
            $response['status'] = true;
        } else {
            $response['status'] = false;
        }
        $response['data']['text'] = $result_text['data'];
        $response['data']['category'] = $result_category['data'];
        $response['data']['totalSearchText'] = $totalSearchedText;
        $response['data']['totalSearchCategory'] = $totalSearchedCategory;
        $response['data']['totalSearch'] = $totalSearchedText + $totalSearchedCategory;
        return $this->jsonResponse($response);
    }

    public function floorplanDetailAction(Request $request) {
        $this->mainInitial($request);
        $resultTotalClicks = $this->FloorplanDevModel->getTotalClicks($this->args);
        $resultTotalViews = $this->FloorplanDevModel->getTotalViews($this->args);
        $resultTotalVisits = $this->FloorplanDevModel->getTotalVisits($this->args);
        $resultTotalOneTimeVisitors = $this->FloorplanDevModel->getTotalOneTimeVisitors($this->args);
        $resultTotalComebackVisitors = $this->FloorplanDevModel->getTotalComebackVisitors($this->args);
        $countStatus = 0;
        $totalClicks = 0;
        //Start for Total Clicks Summary
        if ($resultTotalClicks['status']) {
            $countStatus++;
        }
        $sumClicks = ($resultTotalClicks['status']) ? $resultTotalClicks['data']['0'] : array('data' => array(
                '0' => array(
                    'sumBooth' => 0,
                    'sumList' => 0,
                    'sumProduct' => 0,
                    'sumWebpage' => 0,
                    'sumVideo' => 0,
                    'sumLocation' => 0,
                    'sumPhoto' => 0,
                    'sumProductDirectory' => 0,
                )
            )
        );
        foreach ($sumClicks as $key => $value) {
            $totalClicks += $value;
        }
        //End for Total Clicks Summary
        //Start for Total Views Summary
        if ($resultTotalViews['status']) {
            $countStatus++;
        }
        $totalViews = $resultTotalViews['status'] ? $resultTotalViews['data']['0']['sumViews'] : 0;
        //End for Total Views Summary
        //Start for Total Visits Summary
        if ($resultTotalVisits['status']) {
            $countStatus++;
        }
        $totalVisits = $resultTotalVisits['status'] ? $resultTotalVisits['data']['0']['sumVisits'] : 0;
        //End for Total Visits Summary
        //Start for Total One Time Visitors Summary
        if ($resultTotalOneTimeVisitors['status']) {
            $countStatus++;
        }
        $totalOneTimeVisits = $resultTotalOneTimeVisitors['status'] ? $resultTotalOneTimeVisitors['data']['0']['sumOneTimeVisits'] : 0;
        //End for Total One Time Visitors Summary
        //Start for Total Comeback Visitors Summary
        if ($resultTotalComebackVisitors['status']) {
            $countStatus++;
        }
        $totalComebackVisits = $resultTotalComebackVisitors['status'] ? $resultTotalComebackVisitors['data']['0']['sumComebackVisits'] : 0;
        //End for Total Comeback Visitors Summary
        $response = array();
        if ($countStatus == 5) {
            $response['status'] = true;
        } else {
            $response['status'] = false;
        }
        $response['data']['totalClicks'] = (integer)$totalClicks;
        $response['data']['totalViews'] = (integer)$totalViews;
        $response['data']['totalVisits'] = (integer)$totalVisits;
        $response['data']['totalOneTimeVisitors'] = (integer)$totalOneTimeVisits;
        $response['data']['totalComebackVisitors'] = (integer)$totalComebackVisits;

        return $this->jsonResponse($response);
    }

    //-------------------------   Funciones Genericas    ---------------------------------//
    public function jsonResponse($data) {
        return new Response(json_encode($data), 200, Array('Content-Type', 'text/json'));
    }

    public function mainInitial($request) {
        $session = $request->getSession();
        $this->lang = $session->get('lang');
        $this->edicion = $session->get('edicion');
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
