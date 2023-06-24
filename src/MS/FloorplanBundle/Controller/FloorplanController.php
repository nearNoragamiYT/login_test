<?php

namespace MS\FloorplanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use MS\FloorplanBundle\Model\FloorplanModel;
use MS\FloorplanBundle\Model\LeadsConfiguration;
//use Utilerias\RalfBundle\v1_1\Ralf;

class FloorplanController extends Controller {

    protected $Textos;
    const SECTION = 8;
    //Change
    //private $conexion = array(25 => 'MobLee_RALF', 26 => 'MobLee_RALF_2');
    private $moduloEdicion = array(6 => '531');

    const TEMPLATE = 25;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->conf = new LeadsConfiguration();
    }

    public function floorplanAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEdicion = $session->get('idEdicion');
        if (empty($idEdicion) || !isset($idEdicion) || $idEdicion == "") {
            return $this->redirectToRoute('dashboard');
        }

        $content = array();
        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content['breadcrumb'] = $breadcrumb;
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        $content["FechaIni"] = (!empty($content["FechaIni"])) ? $content["FechaIni"] : '2018/07/01';
        $current = date('Y/m/d');
        $content["FechaFin"] = (!empty($content["FechaFin"])) ? $content["FechaFin"] : $current;
        $content["FechaFin"] = ($content["FechaFin"] < $current) ? $content["FechaFin"] : $current;
        $content['NombreEvento'] = $this->nombreEvento[$idEdicion];

        /* Obtenemos textos del Template MS */
        //$section_text = $this->TemplateText->getTexts($lang, self::TEMPLATE);
//        if (!$section_text['status']) {
//            throw new \Exception($section_text['data'], 409);
//        }
        $content['section_text'] = $section_text['data'];
        $content['App'] = $App;
        $content['user'] = $user;
        $content['lang'] = $lang;
        return $this->render('MSFloorplanBundle:Floorplan:floorplan.html.twig', array("content" => $content));
    }

    public function floorplanAdminViewAction($idEdicion) {
        $content = array();

        return $this->render('MSFloorplanBundle:Floorplan:floorplan.html.twig', array("content" => $content));
    }

    public function exhibitorAction(Request $request, $idExpositor) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        if (empty($idEdicion) || !isset($idEdicion) || $idEdicion == "") {
            return $this->redirectToRoute('dashboard');
        }
        $model = new FloorplanModel($this->container);
        
        $content = array();
        //$content["breadcrumb"] = $this->TemplateText->breadcrumb(self::MAIN_ROUTE, $lang);
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
        $content['FechaIni'] = (!empty($content['FechaIni'])) ? $content['FechaIni'] : '2019/07/01';
        $current = date('Y/m/d');
        $content['FechaFin'] = (!empty($content['FechaFin'])) ? $content['FechaFin'] : $current;
        $content['idExpositor'] = $idExpositor;
        $content['NombreEvento'] = $this->nombreEvento[$idEdicion];
        $session->set('evName', $content['NombreEvento']);
        $args['idEdicion'] = $idEdicion;
        $args['idModuloEdicion'] = $this->moduloEdicion[$idEdicion];
        $content['packages'] = $model->getPackages($args);
        /* Obtenemos textos del Template MS */
        //$section_text = $this->TemplateText->getTexts($lang, self::TEMPLATE);
//        if (!$section_text['status']) {
//            throw new \Exception($section_text['data'], 409);
////        }
//        
//        if (!file_exists($ruta)) {
//            $pack = $model->getPackages();
//            $this->writeJSON($ruta, $pack['data']);
//            clearstatcache();
//            $content['packages'] = ($pack['status']) ? $pack['data'] : array();
//        } else {
//            $result_cache = file_get_contents($ruta);
//            $content['packages'] = json_decode($result_cache, TRUE);
//        }
//      $content["ini"] = $dates["FechaIni"];
        $content['ini'] = (!empty($dates["FechaIni"])) ? $dates["FechaIni"] : '2019-07-01';
        $current = date('Y-m-d');
        $content["fin"] = (!empty($dates["FechaFin"])) ? $dates["FechaFin"] : $current;
        $session->set('iniTimestamp', $content["ini"]);
        $session->set('endTimestamp', $content["fin"]); 
        //-------- Array to Set in the thead of the Visitor Table  -----------//
        $content["visitor_table_column_categories"] = $this->conf->getColumnCategories($content["textos"]);
        $content["visitor_table_columns"] = $this->conf->getColumnDefs($content["textos"]);
        //-------------------------   END    ---------------------------------//

        return $this->render('MSFloorplanBundle:Floorplan:exhibitor.html.twig', array("content" => $content));
    }

    public function kioskAction(Request $request, $idEdicion) {
        $session = $request->getSession();
        $content = array();
        $idEdicion = $session->get('idEdicion');
        $content["idEdicion"] = $idEdicion;

        $content["NombreEvento"] = $evento["Evento"] . " " . $evento["Edicion"];

        return $this->render('MSFloorplanBundle:Floorplan:kiosk.html.twig', array("content" => $content));
    }

    public function getKioskClicksPostAction() {
        $model = new FloorplanModel($this->container);
        $request = $this->getRequest();
        $post = $request->request->all();
        $post["object"] = 7;
        $post["iniTimestamp"] = $post["ini"];
        $post["endTimestamp"] = $post["end"];

        $period = $this->createDateRangeArray($post["iniTimestamp"], $post["endTimestamp"]); //si las fechas son iguales regresa un array con solo esa fecha

        $args = $post;

        $clicks = $model->getKioskClicksByDay($args);
        $views = $model->getKioskViewsByDay($args);
        $args['kiosko'] = 1;
        $clicksk = $model->getKioskClicksByDay($args);
        $viewsk = $model->getKioskViewsByDay($args);
        $searchesk = $model->getKioskSearchByDay($args);

        if (!(count($clicks) > 0 && count($views) > 0)) {
            $result["status"] = false;
            $result["data"] = array();
            return $this->jsonResponse($result);
        }

        $kioskos = array();
        foreach ($viewsk as &$row) {

            if (!array_key_exists($row['kiosko'], $kioskos)) {
                $kioskos[$row['kiosko']] = array();
            }

            foreach ($period as $key => $value) {
                $date = new \DateTime($row['year'] . "-" . $row['month'] . "-" . $row['day']);
                $date = $date->format('Y-m-d');

                if (!array_key_exists($key, $kioskos[$row['kiosko']])) {
                    $kioskos[$row['kiosko']][$key] = array();
                    $kioskos[$row['kiosko']][$key]['date'] = $value;
                    $kioskos[$row['kiosko']][$key]['data'] = array();
                    $kioskos[$row['kiosko']][$key]['data']['views'] = 0;
                    $kioskos[$row['kiosko']][$key]['data']['clicks'] = 0;
                    $kioskos[$row['kiosko']][$key]['data']['searches'] = 0;
                    $kioskos[$row['kiosko']][$key]['data']['views'] += ($value == $date) ? $row['amount'] : 0;
                } else {
                    $kioskos[$row['kiosko']][$key]['data']['views'] += ($value == $date) ? $row['amount'] : 0;
                }
            }
        }

        foreach ($clicksk as &$row) {

            if (!array_key_exists($row['kiosko'], $kioskos)) {
                $kioskos[$row['kiosko']] = array();
            }

            foreach ($period as $key => $value) {
                $date = new \DateTime($row['year'] . "-" . $row['month'] . "-" . $row['day']);
                $date = $date->format('Y-m-d');

                if (!array_key_exists('clicks', $kioskos[$row['kiosko']][$key]['data'])) {
                    $kioskos[$row['kiosko']][$key]['data']['clicks'] = 0;
                    $kioskos[$row['kiosko']][$key]['data']['clicks'] += ($value == $date) ? $row['amount'] : 0;
                } else {
                    $kioskos[$row['kiosko']][$key]['data']['clicks'] += ($value == $date) ? $row['amount'] : 0;
                }
            }
        }

        foreach ($searchesk as &$row) {

            if (!array_key_exists($row['kiosko'], $kioskos)) {
                $kioskos[$row['kiosko']] = array();
            }

            foreach ($period as $key => $value) {
                $date = new \DateTime($row['year'] . "-" . $row['month'] . "-" . $row['day']);
                $date = $date->format('Y-m-d');

                if (!array_key_exists('clicks', $kioskos[$row['kiosko']][$key]['data'])) {
                    $kioskos[$row['kiosko']][$key]['data']['searches'] = 0;
                    $kioskos[$row['kiosko']][$key]['data']['searches'] += ($value == $date) ? $row['amount'] : 0;
                } else {
                    $kioskos[$row['kiosko']][$key]['data']['searches'] += ($value == $date) ? $row['amount'] : 0;
                }
            }
        }

        ksort($kioskos); //ordenar por nÃƒÆ’Ã‚Âºmero de kiosko

        $result["status"] = false;
        $result["data"]["chart"]['clicks'] = $clicks;
        $result["data"]["chart"]['views'] = $views;
        $result["data"]["kioskos"] = $kioskos;

        if (count($clicks) > 0 && count($views) > 0) {
            $result["status"] = true;
        }

        return $this->jsonResponse($result);
    }

    //http://boonedocks.net/mike/archives/137-Creating-a-Date-Range-Array-with-PHP.html
    function createDateRangeArray($strDateFrom, $strDateTo) {
        // takes two dates formatted as YYYY-MM-DD and creates an
        // inclusive array of the dates between the from and to dates.
        // could test validity of dates here but I'm already doing
        // that in the main script

        $aryRange = array();

        $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));

        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry

            while ($iDateFrom < $iDateTo) {
                $iDateFrom += 86400; // add 24 hours
                array_push($aryRange, date('Y-m-d', $iDateFrom));
            }
        }
        return $aryRange;
    }

    public function getExhibitorsDetailsPostAction(Request $request) {
        $model = new FloorplanModel($this->container);
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $args["iniTimestamp"] = $post["ini"];
        $args["endTimestamp"] = $post["end"];
        $args['idEvento'] = $session->get('edicion')["idEvento"];
        $args['idEdicion'] = $session->get('idEdicion');
        //Gets the exhibitors Data
        $result = $model->getExhibitorDetails(strtoupper($lang), $args);
        if (count($result)) {
            $response["status"] = true;
            $response["data"] = $result;
        } else {
            $response["status"] = false;
            $response["data"] = array();
        }
        return $this->jsonResponse($response);
    }

//    public function getBookmarkRefreshAction() {
//        $model = new FloorplanModel($this->container);
//        $cache_recorrido = $model->getExhibitorsBookmarks();
//        $exhibitors_catalog = $model->getAllExhibitors();
//        date_default_timezone_set("America/Mexico_City");
//        $current = time();
//        $last_date = strtotime(empty($cache_recorrido['data'][0]['fechamodificacion']) ? time() + 3600 : $cache_recorrido['data'][0]['fechamodificacion']) + 3600; //Adds 1 hours to the timestamp of modification
//        $catalog = array();
//        $idSesion = date("y"). date("m"). date("d").date("H").date("i");
//        //Catalog for the use of id
//        foreach ($exhibitors_catalog['data'] as $key => $exhibitor) {
//            switch ($exhibitor['idEdicion']) {
//                case 28:
//                    $catalog[$exhibitor['idEmpresa']]['ELA']['idEdicion'] = $exhibitor['idEdicion'];
//                    $catalog[$exhibitor['idEmpresa']]['ELA']['idEvento'] = $exhibitor['idEvento'];
//                    $catalog[$exhibitor['idEmpresa']]['ELA']['idModuloEdicion'] = $this->moduloEdicion[$exhibitor['idEdicion']];
//                    $catalog[$exhibitor['idEmpresa']]['ELA']['idSesionRalf']= $idSesion;
//                    break;
//            }
//        }
//        //Verify of the 1 hours limit for cache          
//        if (count($cache_recorrido['data'])) {
//            if ($last_date < $current) {
//                $response_ralf = $this->ralfBookmarks($catalog);
//                if (!empty($response_ralf)) {
//                    $result = $model->setExhibitorsBookmarks($response_ralf);
//                    //$model->calculateExhibitorsBookmarks($idSesion);
//                }
//            }
//        } else {
//            $response_ralf = $this->ralfBookmarks($catalog);
//            if (!empty($response_ralf)) {
//                $result = $model->setExhibitorsBookmarks($response_ralf);
//                //$model->calculateExhibitorsBookmarks($idSesion);
//            }
//        }
//        if (count($result)) {
//            $response["status"] = true;
//            $response["data"] = $idSesion;
//        } else {
//            $response["status"] = false;
//        }
//        return $this->jsonResponse($response);
//    }
//
//    public function ralfBookmarks($catalog) {
//        $ralf_ela = new Ralf('', '', $this->conexion[28]);
//        $result_ralf_ela = $ralf_ela->getExhibitorsWithBookmarks();
//        $temp_qry = array();
//        $count = count($result_ralf_ela['data']);
//        foreach ($result_ralf_ela['data'] as $key => $bookmarkELA) {
//            //Query generator for insert on ELA
//            if (count($catalog[$bookmarkELA['exhibitor_id']]['ELA'])) {
//                $temp_qry['insert']['ela'] .= '(';
//                $temp_qry['insert']['ela'] .= $bookmarkELA['exhibitor_id'] . ',\'';
//                $temp_qry['insert']['ela'] .= json_encode($bookmarkELA['tours']);
//                $temp_qry['insert']['ela'] .= '\',' . $catalog[$bookmarkELA['exhibitor_id']]['ELA']['idModuloEdicion'];
//                $temp_qry['insert']['ela'] .= ',' . $catalog[$bookmarkELA['exhibitor_id']]['ELA']['idEvento'];
//                $temp_qry['insert']['ela'] .= ',' . $catalog[$bookmarkELA['exhibitor_id']]['ELA']['idEdicion'];
//                $temp_qry['insert']['ela'] .= ',' . $catalog[$bookmarkELA['exhibitor_id']]['ELA']['idSesionRalf'];
//                $key == $count - 1 ? $temp_qry['insert']['ela'] .= ")" : $temp_qry['insert']['ela'] .= "),";
//            }
//        }
//        if (substr($temp_qry['insert']['ela'], -1) == ',') {
//            $temp_qry['insert']['ela'] = substr_replace($temp_qry['insert']['ela'], "", -1);
//        }
//
//        return $temp_qry['insert'];
//    }

    public function getDatesPostAction(Request $request) {
        $post = $request->request->all();

        $args = array();

        // $model = new FloorplanModel($this->container);
//      $fields = $model->getCaseFields($args);
        $result["data"]["kiosk"] = (isset($fields['data'][0]['kiosko'])) ? true : false;

        if (count($fields) > 0) {
            $result["status"] = true;
        }

        return $this->jsonResponse($result);
    }

    public function getAmountOfClicksPostAction(Request $request) {
        $model = new FloorplanModel($this->container);
        $session = $request->getSession();
        $post = $request->request->all();
        $args["object"] = 7;
        $args["iniTimestamp"] = $post["ini"];
        $args["endTimestamp"] = $post["end"];
        $args['idEdicion'] = $session->get('idEdicion');
        $args['idEvento'] = $session->get('idEvento');
        $details = $model->getClickViewByDay($args);

        $result["status"] = false;
        $result["data"]["chart"] = $details;

        if (count($details) > 0) {
            $result["status"] = true;
        }

        return $this->jsonResponse($result);
    }

    public function getSearchPostAction(Request $request) {
        $model = new FloorplanModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $lang = (empty($lang)) ? 'es' : $lang;
        $post = $request->request->all();
        $args["type"] = $post["type"];
        $args["iniTimestamp"] = $post["ini"];
        $args["endTimestamp"] = $post["end"];
        $args['idEdicion'] = $session->get('idEdicion');
        $args['idEvento'] = $session->get('idEvento');        
        if ($args["type"] == 0) {
            $search = $model->getSearchResult($args);
        } else {
            $search = $model->getCategoryResult(strtoupper($lang), $args);
            $search = (count($search) < 1) ? $model->getCategoryResult('ES', $args) : $search; //si no existe el campo del idioma en que se estÃƒÆ’Ã‚Â¡ logueado
        }

        $result["status"] = false;
        $result["data"] = $search;

        if (count($search) > 0) {
            $result["status"] = true;
        }

        return $this->jsonResponse($result);
    }

    public function getVisitorsInfoPostAction(Request $request) {//en esta funciÃƒÆ’Ã‚Â³n se recibe idModuloEdicion pero viene con el valor de idEdicion
        $model = new FloorplanModel($this->container);
        $session = $request->getSession();
        $post = $request->request->all();
        $args["iniTimestamp"] = $post["ini"];
        $args["endTimestamp"] = $post["end"];

        $args['idEdicion'] = $session->get('idEdicion');
        $args['idEvento'] = $session->get('idEvento');        
        $idModuloEdicion = $args['idModuloEdicion'];


        $auxiliar = array(); //arreglo usado para regresar un resultado en caso de que no se deba ejecutar la consulta getVisitorsUsedTour
        $auxiliar[0]["cont"] = 0;

        $uniqueVisitors = $model->getVisitorsInformation($args);

        $visits = $model->getAmountVisit($args);

        $uniquetour = $model->getVisitorsUsedTour($args);

        $comeback = $model->getComeBackVisitors($args);

        $registered = $model->getRegisteredVisitorsAccess($args);
        $data = array();
        $data["visits"] = $visits[0]["cont"];
        $data["uniqueVisitors"] = $uniqueVisitors[0]["cont"];
        $data["uniqueTour"] = $uniquetour[0]["cont"];
        $data["comeback"] = $comeback[0]["cont"];
        $data["registered"] = $registered[0]["cont"];

        $result["status"] = false;
        $result["data"] = $data;

        if (count($data) > 0) {
            $result["status"] = true;
        }

        return $this->jsonResponse($result);
    }

    //Action for the Exhibitor MS Description
    public function getExhibitorDetailsPostAction(Request $request) {
        $model = new FloorplanModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $post = $request->request->all();
        $args["iniTimestamp"] = $post["ini"];
        $args["endTimestamp"] = $post["end"];
        $args["idExhibitor"] = $post["idExhibitor"];
        $args['idEdicion'] = $session->get('idEdicion');
        $args['idEvento'] = $session->get('idEvento');        
        $session->set('iniTimestamp', $args["iniTimestamp"]);
        $session->set('endTimestamp', $args["endTimestamp"]); 
        $result = $model->getEdExhibitorDetails(strtoupper($lang), $args);
        $response = array("status" => true, "data" => $result);
        return $this->jsonResponse($response);
    }

    public function getExhibitorAmountPostAction(Request $request) {
        $model = new FloorplanModel($this->container);
        $session = $request->getSession();
        $post = $request->request->all();
        $args['iniTimestamp'] = $post['ini'];
        $args['endTimestamp'] = $post['end'];
        $args['idExhibitor'] = $post['idExhibitor'];
        $args['idEdicion'] = $session->get('idEdicion');
        $args['idEvento'] = $session->get('idEvento');        
        $session->set('iniTimestamp', $args["iniTimestamp"]);
        $session->set('endTimestamp', $args["endTimestamp"]); 

        $clicks = $model->getAmountOfClicksByEx($args);
        $views = $model->getAmountOfViewsByEx($args);

        $uniqueVisitors = $model->getAmountUniqueVisitorsByEx($args);
        $totalUniqueVisitors = $model->getTotalUniqueVisitorsByEx($args);
        
        $result_data = array("chart" => $clicks,
            "viewsChart" => $views,
            "uniqueTour" => 0,
            "retrieval" => 0,
            "uniqueVisitors" => $uniqueVisitors,
            "idEx" => $args["idExhibitor"],
            "totalUniqueVisitors" => $totalUniqueVisitors);
        $result["data"] = $result_data;
        $result["status"] = true;
        return $this->jsonResponse($result);
    }

    public function getProductsPostAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $lang = (empty($lang)) ? 'es' : $lang;

        $post["iniTimestamp"] = $post["ini"];
        $post["endTimestamp"] = $post["end"];

        $args = $post;

        $model = new FloorplanModel($this->container);

        //$products = $model->getProductsByEx(strtoupper($lang), $args);

        $result["status"] = false;
        $result["data"] = empty($products)?'':$products;

        if (count($products) > 0) {
            $result["status"] = true;
        }

        return $this->jsonResponse($result);
    }

    public function getExProductsPostAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $lang = (empty($lang)) ? 'es' : $lang;

        $post["iniTimestamp"] = $post["ini"];
        $post["endTimestamp"] = $post["end"];

        $args = $post;

        $model = new FloorplanModel($this->container);

        //$products = $model->getProductsByEx(strtoupper($lang), $args);

        $result["status"] = false;
        $result["data"] = empty($products)?'':$products;

        if (count($products) > 0) {
            $result["status"] = true;
        }

        return $this->jsonResponse($result);
    }

    public function getKioskDatesPostAction(Request $request) {
        $post = $request->request->all();
        $args = $post;

        $model = new FloorplanModel($this->container);

        $records = $model->getKioskDates($args);
        $length = count($records);

        $result["status"] = false;
        if ($length > 0) {
            $date1 = $records[0]["fecha"];
            $date2 = $records[$length - 1]["fecha"];

            $date1 = str_replace("-", "/", $date1);
            $date2 = str_replace("-", "/", $date2);

            $dates = array("ini" => $date1, "end" => $date2);
            $result["status"] = true;
        } else {
            $dates = array("ini" => "", "end" => "");
        }

        $result["data"] = $dates;

        return $this->jsonResponse($result);
    }

    /*  GENERICAS  */

    public function jsonResponse($data) {
        return new Response(json_encode($data), 200, Array('Content-Type', 'text/json'));
    }

    public function buildParamsAndColumnFromDTColumns($request, $dt_columns) {
        $session = $request->getSession();
        $lang = $session->get("lang");

        $result_bind = Array("params" => Array(), "columns" => Array());
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }  
        $text_lang = $section_text['data'];

        $column_defs = $this->conf->getColumnDefs($text_lang);

        $total_columns = 0;
        //Seteamos columnas a consultar y los parÃ¡metros where
        foreach ($dt_columns as $column) {
            foreach ($column_defs as $column_raw_name => $column_values) {
                if (strtolower($column["name"]) == strtolower($column_values["text"])) {
                    $column_alias = "";
                    if (array_key_exists("json_column", $column_values["filter_options"]) && is_string($column_values["filter_options"]["json_column"]) && $column_values["filter_options"]["json_column"] != "") {
                        $column_name = '"' . $column_values["filter_options"]["json_column"] . '"->>' . '\'' . $column_raw_name . '\'';
                        $column_alias = ' AS "' . $column_raw_name . '"';
                        $tipo = 1;
                    } else {
                        $column_name = '"' . $column_raw_name . '"';
                        $tipo = 0;
                        if ($column_raw_name == "idVisitanteTipo" || $column_raw_name == "idModuloEdicion")
                            $tipo = 1;
                    }
                    $result_bind["columns"][] = $column_name . $column_alias;
                    if ($column["searchable"]) {
                        if (is_array($column["search"]) && trim($column["search"]["value"]) != "") {
                            $operator = "";
                            $value = "";
                            if (strpos($column["search"]["value"], "op:") !== FALSE) {
                                $char_long = strlen($column["search"]["value"]);
                                /* position of ";" delimiter, is initialized in 3 because (op:)=3positions */
                                $end_delimiter_pos = 3;
                                for ($i = (strpos("op:", "op:") + 3); $i < $char_long; $i++) {
                                    if ($column["search"]["value"][$i] == ";") {
                                        break;
                                    }
                                    $value .= $column["search"]["value"][$i];
                                    $end_delimiter_pos++;
                                }
                                $operator = $value;
                                $value = trim(substr($column["search"]["value"], $end_delimiter_pos + 1));
                            } else {
                                $operator = "=";
                                $value = $column["search"]["value"];

                                if (array_key_exists("search_operator", $column_values["filter_options"])) {
                                    switch (strtolower($column_values["filter_options"]["search_operator"])) {
                                        case "ilike":
                                            $operator = "ilike";
                                            $value = "%" . $column["search"]["value"] . "%";
                                            break;
                                        case "@>":
                                            $operator = "@>";
                                            $value = "{" . $column["search"]["value"] . "}";
                                            break;
                                    }
                                }
                            }

                            $filter = Array("name" => $column_name, "operator" => $operator, "value" => $value, "type" => \PDO::PARAM_STR, "tipo" => $tipo);
                            if ($total_columns++ > 0) {
                                $filter["clause"] = "AND";
                            }
                            $result_bind["params"][] = $filter;
                        }
                    }
                }
            }
        }
        return $result_bind;
    }

    public function buildOrderColumns($request, $order_columns, $all_columns) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        //Seteamos columnas a ordenar
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }  
        $text_lang = $section_text['data'];
        $column_defs = $this->conf->getColumnDefs($text_lang);
        $order = Array();
        if (is_array($order_columns) && COUNT($order_columns) > 0) {
            //parse order colums
            foreach ($order_columns as $order_column) {
                $column_index = 0;
                foreach ($all_columns as $column_name => $column_text) {

                    if ($order_column["column"] == $column_index++) {
                        $pos = strpos($column_name, ' AS ');

                        if ($pos) {
                            list($a, $b) = explode(' AS ', $column_name);
                            $column_name = $b;
                        }

                        if (array_key_exists($column_name, $column_defs)) {

                            if (array_key_exists("json_column", $column_defs[$column_name]["filter_options"]) && is_string($column_defs[$column_name]["filter_options"]["json_column"]) && $column_defs[$column_name]["filter_options"]["json_column"] != "") {
                                $column_name = '"' . $column_defs[$column_name]["filter_options"]["json_column"] . '"->>' . '\'' . $column_name . '\'';
                            } else {
                                $column_name = '"' . $column_name . '"';
                            }
                            $order[] = Array("name" => $column_name, "dir" => $order_column["dir"]);
                        }
                    }
                }
            }
        }

        return $order;
    }

    function matchColumnDefsData($request, $records) {
        $session = $request->getSession();
        $lang = $session->get("lang");

        $data = Array();
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }  
        $text_lang = $section_text['data'];
        $column_defs = $this->conf->getColumnDefs($text_lang);
        //En caso de que algÃƒÆ’Ã‚Âºn campo cuente con mÃƒÆ’Ã‚Âºltiples valores le seteamos el correspondiente en base a la definiciÃƒÆ’Ã‚Â³n de los valores de la columna
        if (is_array($records) && COUNT($records) > 0) {
            foreach ($records as $record) {
                $row = Array();
                foreach ($column_defs as $column_name => $column_values) {
                    if (array_key_exists($column_name, $record)) {
                        if (
                                array_key_exists("is_select", $column_values["filter_options"]) && $column_values["filter_options"]["is_select"] && is_array($column_values["filter_options"]["values"]) && COUNT($column_values["filter_options"]["values"]) > 0
                        //problems with boolean columns
                        ) {

                            if (array_key_exists($record[$column_name], $column_values["filter_options"]["values"])) {
                                $row[$column_name] = $column_values["filter_options"]["values"][$record[$column_name]];
                            } else {
                                $row[$column_name] = '-';
                            }
                        } else {
                            $row[$column_name] = (trim($record[$column_name]) !== '') ? trim($record[$column_name]) : '-';
                        }
                    }
                }
                $data[] = $row;
            }
        }
        return $data;
    }

    //Visitors Table on Exhibitors window
    public function getToDataTableAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');

        if ($request->getMethod() != 'POST') {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "No allowed method"));
        }
        $post = $request->request->all();

        $content = array();
        $args['idExhibitor'] = $session->get('idExhibitor');
        $args["iniTimestamp"] = $session->get('iniTimestamp');
        $args["endTimestamp"] = $session->get('endTimestamp');
        $args['idEdicion'] = $session->get('idEdicion');
        $args['idEvento'] = $session->get('idEvento');        

        $model = new FloorplanModel($this->container);

        $result_count = $model->getCountVisitors($args);
        if (!$result_count["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting all visitor records"));
        }
        $total_records = $records_filtered = $result_count["data"][0]["total"];

        $result_build = $this->buildParamsAndColumnFromDTColumns($request, $post["columns"]);
        $params = Array("where" => $result_build["params"]);
        $columns = $result_build["columns"];
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }  
        $text_lang = $section_text['data'];        
        $column_defs = $this->conf->getColumnDefs($text_lang);

        $param_raw_name = "";
        $raw_columns = Array();
        foreach ($columns as $column) {
            $param_raw_name = str_replace("\"", "", $column);
            if (array_key_exists($param_raw_name, $column_defs)) {
                $raw_columns[$param_raw_name] = $column_defs[$param_raw_name]["text"];
            } else {
                $raw_columns[$param_raw_name] = $param_raw_name;
            }
        }
        
        if (array_key_exists("where", $params) && COUNT($params["where"]) > 0) {
            $result_filtered_count = $model->getCountVisitors($args, $params, true);
            if (!$result_filtered_count["status"]) {
                return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting filtered records"));
            }
            $records_filtered = $result_filtered_count["data"][0]["total"];
        }
        $order = $this->buildOrderColumns($request, $post["order"], $raw_columns);
        $result_query = $model->getVisitorCustom($args, $columns, $params, $order, $post["length"], $post["start"]);

        if (!$result_query["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error getting records"));
        }

        $data = $this->matchColumnDefsData($request, $result_query["data"]);

        $response_dt = Array(
            "status" => TRUE,
            "draw" => $post["draw"],
            "recordsTotal" => $total_records,
            "recordsFiltered" => $records_filtered,
            "data" => $data
        );
        return $this->jsonResponse($response_dt);
    }

    public function exportGeneralDataAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get("lang");

        date_default_timezone_set("America/Mexico_City");
        $args = array();

        $post = $request->request->all();
        $post_data = json_decode(str_replace('\"', '"', $post["post_data"]), TRUE);
        $args['idExhibitor'] = $session->get('idExhibitor');
        $args["iniTimestamp"] = $session->get('iniTimestamp');
        $args["endTimestamp"] = $session->get('endTimestamp');
        $args["evName"] = $session->get('evName');
        $args["exName"] = $session->get('exName');
        $args['idEdicion'] = $session->get('idEdicion');
        $args['idEvento'] = $session->get('idEvento');

        $model = new FloorplanModel($this->container);
//        $estructura = $EstructuraWSDL['Estructura'];
//        $odbc_wsdl = $EstructuraWSDL['Soap'];
//        $fmEdicion = ($estructura == 3 || $estructura == 4 || $estructura == 5) ? $EstructuraWSDL['FmEdicion'] : '';
//        $fmEdicion = ($fmEdicion == 0) ? '' : $fmEdicion;

        $file_name = str_replace(" ", "_", $args['evName']) . "_" . $args['exName'] . '_ Leads Generados';
        $file_name = preg_replace('/[^a-z0-9]+/i', '_', $file_name);

        $result_build = $this->buildParamsAndColumnFromDTColumns($request, $post_data["columns"]);
        $params = Array("where" => $result_build["params"]);

        $columns = $result_build["columns"];

        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }  
        $text_lang = $section_text['data'];
        $column_defs = $this->conf->getColumnDefs($text_lang);

        $param_raw_name = "";
        $raw_columns = Array();
        foreach ($post_data["columns"] as $column) {
            if ($column["data"] == "img_edit") {
                continue;
            }
            if (array_key_exists($column["data"], $column_defs)) {
                $raw_columns[$column["data"]] = $column_defs[$column["data"]]["text"];
            } else {
                $raw_columns[$column["data"]] = $param_raw_name;
            }
        }

        $order = $this->buildOrderColumns($request, $post_data["order"], $raw_columns);
        $result_query = $model->getVisitorCustom($args, $columns, $params);
        if (!$result_query["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error getting records"));
        }
        $data = $this->matchColumnDefsData($request, $result_query["data"]);
        $header_report = "";
        $subheader_report = "";
        if (COUNT($params["where"]) > 0) {
            $header_report = "Filtros aplicados";
            foreach ($params["where"] as $param) {
                $param_raw_name = str_replace("\"", "", $param["name"]);
                $subheader_report .= $param_raw_name;

                if (array_key_exists($param_raw_name, $column_defs)) {
                    $subheader_report .= $column_defs[$param_raw_name]["text"] . ": ";
                    if (
                            array_key_exists("is_select", $column_defs[$param_raw_name]) && $column_defs[$param_raw_name]["is_select"] && is_array($column_defs[$param_raw_name]["values"]) && COUNT($column_defs[$param_raw_name]["values"]) > 0
                    ) {
                        $subheader_report .= $column_defs[$param_raw_name]["values"][$param["value"]];
                    }
                }
                $subheader_report .= ", ";
            }
        }
        $subheader_report = substr($subheader_report, 0, strlen($subheader_report) - 2);

        $response = $this->render('MSFloorplanBundle:export:generic_table_visitor.html.twig', array('header' => $header_report, 'subheader' => $subheader_report, 'columns' => $raw_columns, 'data' => $data));

        $response->headers->set("Content-Type", "application/vnd.ms-excel");
        $response->headers->set("charset", "utf-8");
        $response->headers->set("Content-Disposition", "attachment;filename=" . $file_name . "_" . date('d-m-Y G.i') . ".xls");
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function getVisitorToursAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $lang = (empty($lang)) ? 'es' : $lang;
        $post["iniTimestamp"] = $post["ini"];
        $post["endTimestamp"] = $post["end"];
        $idEdicion = $post["idEdition"];
        $args = $post;
        $model = new FloorplanModel($this->container);
        $estructura = $EstructuraWSDL['Estructura'];
        $fmEdicion = ($estructura == 3 || $estructura == 4 || $estructura == 5) ? $EstructuraWSDL['FmEdicion'] : '';
        $fmEdicion = ($fmEdicion == 0) ? '' : $fmEdicion;
        $visitors = $model->getVisitorTours($fmEdicion, strtoupper($lang), $args);
        $result["status"] = false;
        $result["data"] = $visitors;
        if (count($visitors) > 0) {
            $result["status"] = true;
        }
        return $this->jsonResponse($result);
    }

}
