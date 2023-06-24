<?php

namespace MS\FloorplanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use MS\FloorplanBundle\Model\FloorplanExhibitorDevModel;
use MS\FloorplanBundle\Model\FloorplanExhibitorLeadsDevModel;
use MS\FloorplanBundle\Model\LeadsDevConfiguration;

/**
 *
 * @author Ernesto
 */
class FloorplanExhibitorLeadsDevController extends Controller {

    protected $Textos, $args, $lang, $edicion;

    const SECTION = 8;
    const TEMPLATE = 25;

    public function __construct() {
        $this->FloorplanExhibitorDevModel = new FloorplanExhibitorDevModel();
        $this->FloorplanExhibitorLeadsDevModel = new FloorplanExhibitorLeadsDevModel();
        $this->TextoModel = new TextoModel();
        $this->LeadsDevConfiguration = new LeadsDevConfiguration();
    }

    public function exhibitorLeadsAction(Request $request, $idExpositor) {
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
        $content["visitor_table_column_categories"] = $this->LeadsDevConfiguration->getColumnCategories($content["textos"]);
        $content["visitor_table_columns"] = $this->LeadsDevConfiguration->getColumnDefs($content["textos"]);

        return $this->render('MSFloorplanBundle:FloorplanDev:exhibitor_leads.html.twig', array("content" => $content));
    }

//Visitors Table on Exhibitors window
    public function getLeadsToDataTableAction(Request $request, $idExpositor) {
        $session = $request->getSession();
        $lang = $session->get('lang');

        $post = $request->request->all();

        $args['idExhibitor'] = $idExpositor;
        $args["FechaIni"] = $session->get('FechaIni');
        $args["FechaFin"] = $session->get('FechaFin');
        $args['idEdicion'] = $session->get('idEdicion');
        $args['idEvento'] = $session->get('idEvento'); 

        $result_count = $this->FloorplanExhibitorLeadsDevModel->getCountVisitors($args);
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
        $column_defs = $this->LeadsDevConfiguration->getColumnDefs($text_lang);

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
            $result_filtered_count = $this->FloorplanExhibitorLeadsDevModel->getCountVisitors($args, $params, true);
            if (!$result_filtered_count["status"]) {
                return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting filtered records"));
            }
            $records_filtered = $result_filtered_count["data"][0]["total"];
        }
        $order = $this->buildOrderColumns($request, $post["order"], $raw_columns);
        $result_query = $this->FloorplanExhibitorLeadsDevModel->getVisitorCustom($args, $columns, $params, $order, $post["length"], $post["start"]);

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
