<?php

namespace MS\FloorplanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Utilerias\TextoBundle\Model\TextoModel;
use MS\FloorplanBundle\Model\ExFloorplanModel;
use MS\FloorplanBundle\Model\ExLeadsConfiguration;

/**
 *
 * @author Neto
 */
class ExAccessController extends Controller {

    protected $Textos;
    const SECTION = 8;
    private $nombreEvento = array(1 => 'DEMO');

    const TEMPLATE = 25;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->conf = new ExLeadsConfiguration();
    }

    public function exhibitorAction(Request $request, $lang, $token) {              
        $model = new ExFloorplanModel();
        $session = $request->getSession();
        $session->set('lang',$lang);
        $resultToken = $model->getEdicion($token);
        switch ($resultToken) {
            case '206':
                return $this->render('MSFloorplanBundle:exAccess:unkown_exhibitor.html.twig', array("lang" => $lang));
                break;

            case '404':
                return $this->render('MSFloorplanBundle:exAccess:unkown_exhibitor.html.twig', array("lang" => $lang));
                break;
        }
        $updateToken = $model->updateToken($resultToken);
        $session->set('idEdicion', $resultToken['idEdicion']);
        $session->set('idEvento', $resultToken['idEvento']);
        $args['idEdicion'] = $resultToken['idEdicion'];
        $args['idEvento'] = $resultToken['idEvento'];
        $session->set('idExhibitor', $resultToken['idEmpresa']);
        $content = array();
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }  
        $content['textos'] = $section_text['data'];
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        $content['FechaIni'] = (!empty($content['FechaIni'])) ? $content['FechaIni'] : '2019/07/01';
        $current = date('Y/m/d');
        $content['FechaFin'] = (!empty($content['FechaFin'])) ? $content['FechaFin'] : $current;
        $content['idExpositor'] = $resultToken['idEmpresa'];
        $content['NombreEvento'] = $this->nombreEvento[$resultToken['idEdicion']];
        $session->set('evName', $content['NombreEvento']);
        $args['idEdicion'] = $resultToken['idEdicion'];
        $content['packages'] = $model->getPackages($args);
        $content['ini'] = (!empty($dates["FechaIni"])) ? $dates["FechaIni"] : '2019-07-01';
        $current = date('Y-m-d');
        $content["fin"] = (!empty($dates["FechaFin"])) ? $dates["FechaFin"] : $current;
        $session->set('iniTimestamp', $content["ini"]);
        $session->set('endTimestamp', $content["fin"]); 
        //-------- Array to Set in the thead of the Visitor Table  -----------//
        $content["visitor_table_column_categories"] = $this->conf->getColumnCategories($content["textos"]);
        $content["visitor_table_columns"] = $this->conf->getColumnDefs($content["textos"]);
        //-------------------------   END    ---------------------------------//

        return $this->render('MSFloorplanBundle:exAccess:exAccess.html.twig', array("content" => $content, "lang" => $lang));
    }

    public function getExhibitorDetailsPostAction(Request $request) {
        $session = $request->getSession();
        $model = new ExFloorplanModel($session->get('idEdicion'));
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
        $session = $request->getSession();
        $model = new ExFloorplanModel($session->get('idEdicion'));
        $post = $request->request->all();
        $args['iniTimestamp'] = $post['ini'];
        $args['endTimestamp'] = $post['end'];
        $args['idExhibitor'] = $session->get('idExhibitor');;
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
        $model = new ExFloorplanModel($session->get('idEdicion'));
        $lang = 'es';
        $lang = (empty($lang)) ? 'es' : $lang;

        $post["iniTimestamp"] = $post["ini"];
        $post["endTimestamp"] = $post["end"];

        $args = $post;

        //$products = $model->getProductsByEx(strtoupper($lang), $args);

        $result["status"] = false;
        $result["data"] = empty($products) ? '' : $products;

        if (count($products) > 0) {
            $result["status"] = true;
        }

        return $this->jsonResponse($result);
    }

    public function getExProductsPostAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $model = new ExFloorplanModel($session->get('idEdicion'));
        $lang = 'es';
        $lang = (empty($lang)) ? 'es' : $lang;

        $post["iniTimestamp"] = $post["ini"];
        $post["endTimestamp"] = $post["end"];

        $args = $post;

        //$products = $model->getProductsByEx(strtoupper($lang), $args);

        $result["status"] = false;
        $result["data"] = empty($products) ? '' : $products;

        if (count($products) > 0) {
            $result["status"] = true;
        }

        return $this->jsonResponse($result);
    }

    public function getKioskDatesPostAction(Request $request) {
        $post = $request->request->all();
        $args = $post;
        $session = $request->getSession();
        $model = new ExFloorplanModel($session->get('idEdicion'));

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

    public function jsonResponse($data) {
        return new Response(json_encode($data), 200, Array('Content-Type', 'text/json'));
    }

    public function buildParamsAndColumnFromDTColumns($request, $dt_columns) {
        $session = $request->getSession();
        $lang = $session->get('lang');

        $result_bind = Array("params" => Array(), "columns" => Array());
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }  
        $text_lang = $section_text['data'];
        $column_defs = $this->conf->getColumnDefs($text_lang);

        $total_columns = 0;
        //Seteamos columnas a consultar y los parÃƒÂ¡metros where
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
        $lang = $session->get('lang');
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
        $lang = 'es';

        $data = Array();
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }  
        $text_lang = $section_text['data'];
        $column_defs = $this->conf->getColumnDefs($text_lang);
        //En caso de que algÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºn campo cuente con mÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Âºltiples valores le seteamos el correspondiente en base a la definiciÃƒÆ’Ã†â€™Ãƒâ€šÃ‚Â³n de los valores de la columna
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
        $model = new ExFloorplanModel($session->get('idEdicion'));
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
        $lang = 'es';
        $lang = (empty($lang)) ? 'es' : $lang;
        $post["iniTimestamp"] = $post["ini"];
        $post["endTimestamp"] = $post["end"];
        $idEdicion = $post["idEdition"];
        $args = $post;
        $model = new ExFloorplanModel($this->eventoEdicion);
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
