<?php

namespace ShowDashboard\RS\VisitantePerfilBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\RS\VisitantePerfilBundle\Model\VisitantePerfilConfiguration;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use ShowDashboard\RS\VisitantePerfilBundle\Model\VisitantePerfilModel;

class VisitantePerfilController extends Controller {

    protected $TextoModel, $VisitantePerfilModel, $Rs_Configuracion;

    const SECTION = 11;

//    const MAIN_ROUTE = "visitante";

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->Rs_Configuracion = new VisitantePerfilConfiguration();
        $this->VisitantePerfilModel = new VisitantePerfilModel();
    }

    public function visitantePerfilAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $content = array();
        $content["lang"] = $lang;
        $content["user"] = $user;

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Obtenemos textos de la seccion 4 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);

        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];

//        /* Verificamos si tiene permiso en el modulo seleccionado */
//        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
//        if (!$breadcrumb) {
//            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
//            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
//        }

        /* Obtenemos los paises del PECC */
        $result_paises = $this->get('pecc')->getPaises($lang);
        if (!$result_paises['status']) {
            throw new \Exception($result_paises['data'], 409);
        }
        $content['paises'] = $result_paises['data'];

        $args = Array('c."Principal"' => "true", 'e."idEvento"' => $idEvento);
        $content['breadcrumb'] = $breadcrumb;

        $content["rs_visitante_table_column_categories"] = $this->Rs_Configuracion->getColumnCategories($section_text);
        $content["rs_visitante_table_columns"] = $this->Rs_Configuracion->getColumnDefs($section_text, $lang, $idEdicion);
        $session->set('columns_RsVisitante', $content["rs_visitante_table_columns"]);
        return $this->render('ShowDashboardRSVisitantePerfilBundle:VisitantePerfil:lista_Rs_Visitante.html.twig', array('content' => $content));
    }

    //Filtros Visitante
    public function getToDataTableAction(Request $request) {

        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->remove('seting-dt_RsVisitante');

        if ($request->getMethod() != 'POST') {
            throw new \Exception("No allowed method", 409);
        }

        $post = $request->request->all();

        $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
        $result_count = $this->VisitantePerfilModel->getCountRsVisitante(Array(), $params);
        if (!$result_count["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting all records"));
        } else {
            $session->set('qry_count_RsVisitante', $result_count["count"]);
        }
        unset($post['columns'][11]);

        $total_records = $records_filtered = $result_count["data"][0]["total"];
        $result_build = $this->buildParamsAndColumnFromDTColumns($post["columns"], $lang, $idEdicion);
        print_r($result_build);
        die(';)');
        $params = Array("where" => $result_build["params"]);

        $columns = $result_build["columns"];
        $column_defs = $this->Rs_Configuracion->getColumnDefs($this->TextoModel->getTexts($lang, self::SECTION), $lang, $idEdicion);

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

            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $result_filtered_count = $this->VisitantePerfilModel->getCountRsVisitante($columns, $params);
            if (!$result_filtered_count["status"]) {
                return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting filtered records"));
            } else {
                $session->set('count_filtered_RsVisitante', $result_filtered_count["count"]);
            }
            $records_filtered = $result_filtered_count["data"][0]["total"];
        } else {
            $session->remove('count_filtered_RsVisitante');
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
        }

        $order = $this->buildOrderColumns($post["order"], $raw_columns, $lang, $idEdicion);
        $result_query = $this->VisitantePerfilModel->getRsVisitanteCustom($columns, $params, $order, $post["length"], $post["start"]);
        if (!$result_query["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error getting records"));
        } else {
            $session->set('data_qry', $result_query["data_qry"]);
        }
        $data = $this->matchColumnDefsData($result_query["data"], $lang, $idEdicion);

        $response_dt = Array(
            "status" => TRUE,
            "draw" => $post["draw"],
            "recordsTotal" => $total_records,
            "recordsFiltered" => $records_filtered,
            "data" => $data
        );
        return $this->jsonResponse($response_dt);
    }

    public function getToDataTableFilterAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $edicion_RsVisitante = $session->get("edicion_RsVisitante");

        if ($idEdicion != $edicion_RsVisitante) {
            $session->remove('seting-dt');
            $session->remove('qry_count');
            $session->remove('count_filtered');
            $session->remove('data_qry');
        }

        $session->set("edicion_RsVisitante", $idEdicion);

        if ($request->getMethod() != 'POST') {
            throw new \Exception("No allowed method", 409);
        }

        $post = $request->request->all();
        if ($session->has('qry_count_RsVisitante')) {
            $qry_count = $session->get('qry_count_RsVisitante');
            $result_count = $this->VisitantePerfilModel->getCountRsVisitante(Array(), $qry_count["params"], $qry_count["qry"]);
        } else {
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
            $result_count = $this->VisitantePerfilModel->getCountRsVisitante(Array(), $params);
        }
        if (!$result_count["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting all records"));
        } else {
            $session->set('qry_count_RsVisitante', $result_count["count"]);
        }
        $total_records = $records_filtered = $result_count["data"][0]["total"];

        $result_build = $this->buildParamsAndColumnFromDTColumns($post["columns"], $lang, $idEdicion);
        $params = Array("where" => $result_build["params"]);

        $columns = $result_build["columns"];
        $column_defs = $this->Rs_Configuracion->getColumnDefs($this->TextoModel->getTexts($lang, self::SECTION), $lang, $idEdicion);

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

        if ($session->has("count_filtered_RsVisitante")) {
            $count_filtered = $session->get('count_filtered_RsVisitante');
            $params = $count_filtered["params"];
        }

        if (array_key_exists("where", $params) && COUNT($params["where"]) > 0) {
            if ($session->has("count_filtered_RsVisitante")) {
                $count_filtered = $session->get('count_filtered_RsVisitante');
                $result_filtered_count = $this->VisitantePerfilModel->getCountRsVisitante($columns, $count_filtered["params"], $count_filtered["qry"]);
            } else {
                $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
                $result_filtered_count = $this->VisitantePerfilModel->getCountRsVisitante($columns, $params);
            }
            if (!$result_filtered_count["status"]) {
                return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting filtered records"));
            } else {
                $session->set('count_filtered_RsVisitante', $result_filtered_count["count"]);
            }
            $records_filtered = $result_filtered_count["data"][0]["total"];
        } else {
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
        }
//        } else {
//            $session->set('data_qry_hoteles', $result_query["data_qry"]);
//        }

        $order = $this->buildOrderColumns($post["order"], $raw_columns, $lang, $idEdicion);

        if ($session->has("data_qry")) {
            $data_qry = $session->get('data_qry');
            $result_query = $this->VisitantePerfilModel->getRsVisitanteCustom($columns, $data_qry["params"], $order, $post["length"], $post["start"]);
        } else {
            $result_query = $this->VisitantePerfilModel->getRsVisitanteCustom($columns, $params, $order, $post["length"], $post["start"]);
        }
        if (!$result_query["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error getting records"));
        } else {
            $session->set('data_qry', $result_query["data_qry"]);
        }

        $data = $this->matchColumnDefsData($result_query["data"], $lang, $idEdicion);
        $response_dt = Array(
            "status" => TRUE,
            "draw" => $post["draw"],
            "recordsTotal" => $total_records,
            "recordsFiltered" => $records_filtered,
            "data" => $data
        );
        return $this->jsonResponse($response_dt);
    }

    public function buildParamsAndColumnFromDTColumns($dt_columns, $lang, $idEdicion) {
        $result_bind = Array("params" => Array(), "columns" => Array());
        $column_defs = $this->Rs_Configuracion->getColumnDefs($this->TextoModel->getTexts($lang, self::SECTION), $lang, $idEdicion);
        ;
        $total_columns = 0;
        //Seteamos columnas a consultar y los parÃƒÂ¡metros where
        foreach ($dt_columns as $column) {
            foreach ($column_defs as $column_raw_name => $column_values) {
                if (strtolower($column["name"]) == strtolower($column_values["text"])) {
                    $column_alias = "";
                    if (array_key_exists("json_column", $column_values["filter_options"]) && is_string($column_values["filter_options"]["json_column"]) && $column_values["filter_options"]["json_column"] != "") {
                        $column_name = '"' . $column_values["filter_options"]["json_column"] . '"->>' . '\'' . $column_raw_name . '\'';
                        $column_alias = ' AS "' . $column_raw_name . '"';
                    } else {
                        $column_name = '"' . $column_raw_name . '"';
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

                            $filter = Array("name" => $column_name, "operator" => $operator, "value" => $value, "type" => \PDO::PARAM_STR);
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

    public function buildOrderColumns($order_columns, $all_columns, $lang, $idEdicion) {
        //Seteamos columnas a ordenar
        $column_defs = $this->Rs_Configuracion->getColumnDefs($this->TextoModel->getTexts($lang, self::SECTION), $lang, $idEdicion);

        $order = Array();
        if (is_array($order_columns) && COUNT($order_columns) > 0) {
            //parse order colums
            foreach ($order_columns as $order_column) {
                $column_index = 0;
                foreach ($all_columns as $column_name => $column_text) {
                    if ($order_column["column"] == $column_index++) {
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

    function matchColumnDefsData($records, $lang, $idEdicion) {
        $data = Array();

        $column_defs = $this->Rs_Configuracion->getColumnDefs($this->TextoModel->getTexts($lang, self::SECTION), $lang, $idEdicion);
        //En caso de que algÃƒÂºn campo cuente con mÃƒÂºltiples valores le seteamos el correspondiente en base a la definiciÃƒÂ³n de los valores de la columna
        if (is_array($records) && COUNT($records) > 0) {
            foreach ($records as $record) {
                $row = Array();
                foreach ($column_defs as $column_name => $column_values) {
                    if (array_key_exists($column_name, $record)) {
                        if (
                                array_key_exists("is_select", $column_values["filter_options"]) && $column_values["filter_options"]["is_select"] && is_array($column_values["filter_options"]["values"]) && COUNT($column_values["filter_options"]["values"]) > 0
                        //problems with boolean columns
                        //&& array_key_exists($record[$column_name], $column_values["values"])
                        ) {
                            $row[$column_name] = $column_values["filter_options"]["values"][$record[$column_name]];
                        } else {
                            $row[$column_name] = $record[$column_name];
                        }
                    }
                }
                $data[] = $row;
            }
        }
        return $data;
    }

    public function exportRsVisitanteDataAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);

        date_default_timezone_set("America/Mexico_City");

        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $App["Cliente_" . $lang]) . "_log_Data";

        $post = $request->request->all();
        $post_data = json_decode(str_replace('\"', '"', $post["post_data"]), TRUE);

        $result_build = $this->buildParamsAndColumnFromDTColumns($post_data["columns"], $lang, $idEdicion);
        $params = Array("where" => $result_build["params"]);
        if (array_key_exists("where", $params) && COUNT($params["where"]) > 0) {
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
        } else {
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
        }

        $columns = $result_build["columns"];
        $column_defs = $this->Rs_Configuracion->getColumnDefs($section_text, $lang, $idEdicion);

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

        $order = $this->buildOrderColumns($post["order"], $raw_columns, $lang, $idEdicion);
        if ($session->has("data_qry")) {
            $data_qry = $session->get('data_qry');
            $params["where"] = $data_qry["params"]["where"];

            $result_query = $this->VisitantePerfilModel->getRsVisitanteCustom($columns, $data_qry["params"], $order);
        } else {

            $result_query = $this->VisitantePerfilModel->getRsVisitanteCustom($columns, $params, $order);
        }
        $subheader = $section_text["data"]["sas_totalRegistros"] . " " . count($result_query["data"]);
        if (!$result_query["status"]) {
            die("Error getting records");
        }

        $data = $this->matchColumnDefsData($result_query["data"], $lang, $idEdicion);

        $header_report = "";
        $r = array("\"", "'", "jp", "jv", "%", "{", "}", "->>");
        if (COUNT($params["where"]) > 0) {
            $header_report = $section_text["data"]["sas_filtrosAplicados"] . " ";
            foreach ($params["where"] as $param) {
                $param_raw_name = str_replace($r, "", $param["name"]);

                if (array_key_exists($param_raw_name, $column_defs)) {
                    $header_report .= $column_defs[$param_raw_name]["text"] . ": ";
                    if (
                            array_key_exists("is_select", $column_defs[$param_raw_name]["filter_options"]) && $column_defs[$param_raw_name]["filter_options"]["is_select"] && is_array($column_defs[$param_raw_name]["filter_options"]["values"]) && COUNT($column_defs[$param_raw_name]["filter_options"]["values"]) > 0
                    ) {
                        $header_report .= $column_defs[$param_raw_name]["filter_options"]["values"][$param["value"]];
                    } else {
                        $header_report .= str_replace($r, "", $param["value"]);
                    }
                }
                $header_report .= ", ";
            }
        }
        $header_report = substr($header_report, 0, strlen($header_report) - 2);

        return $this->excelReport($data, $raw_columns, $file_name);
    }

    public function excelReport($general, $table_metadata, $filename) {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("Infoexpo")
                ->setTitle($filename)
                ->setSubject($filename)
                ->setDescription($filename);

        $lastColumn = $phpExcelObject->getActiveSheet()->getHighestColumn();
        foreach ($table_metadata as $key => $value) {
            $phpExcelObject->getActiveSheet()->getColumnDimension($lastColumn)->setAutoSize(true);
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lastColumn . 1, $value);
            $lastColumn++;
        }
        $flag = 2;
        foreach ($general as $index) {
            $lastColumn = "A";
            foreach ($index as $key => $value) {
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lastColumn . $flag, $value);
                $lastColumn++;
            }$flag++;
        }

        $phpExcelObject->getActiveSheet()->getStyle("A1:" . $lastColumn . "1")->getFont()->setBold(true);
        $phpExcelObject->setActiveSheetIndex(0);

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename . ".xlsx");

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        $response->headers->set('Expires', '0');

        return $response;
    }

    public function setSessionDataAction(Request $request) {
        $session = $request->getSession();
        $post = $request->request->all();
        $session->set('seting-dt', $post);
        return $this->jsonResponse($post);
    }

    public function getSessionDataAction(Request $request) {
        $session = $request->getSession();
        $data = array("seting" => $session->get('seting-dt'), "param" => $session->get('data_qry')["params"]["where"], "columns" => $session->get('columns_RsVisitante'));

        return $this->jsonResponse($data);
    }

    public function DatosGeneralesAction(Request $request, $idVisitante) {
        $this->VisitantePerfilModel = new VisitantePerfilModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $content = array();
        $content['idVisitante'] = $idVisitante;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEvento'] = $idEvento;
        $content['idEdicion'] = $idEdicion;
        $content["breadcrumb"] = array();
        $content['view'] = $session->get('OriginView');
        $content["breadcrumb"] = $this->VisitantePerfilModel->breadcrumb($session->get('OriginView'), $lang);
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Obtenemos textos del Template AE_DatosGenerales */
        $section_text = $this->VisitantePerfilModel->getTexts($lang, self::TEMPLATE);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['template_text'] = $section_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado */
        if ($session->get('OriginView') == "elite") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "elite");
        }
        if ($session->get('OriginView') == "visitante") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "visitante");
        }
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content['breadcrumb'] = $breadcrumb;
        /* Obtenemos los paises del PECC */
        $result_paises = $this->get('pecc')->getPaises($lang);
        if (!$result_paises['status']) {
            throw new \Exception($result_paises['data'], 409);
        }
        $content['paises'] = $result_paises['data'];

        /* Obtenemos datos del Visitante */
        $result_visitante = $this->VisitantePerfilModel->getVisitante($content);
        if (!$result_visitante['status']) {
            throw new \Exception($result_visitante['data'], 409);
        }
        $content['visitante'] = $result_visitante['data']['0'];

        /* Obtenemos los Tipos de Visitante */
//        $result_visitanteTipo = $this->DatosGeneralesModel->getVisitanteTipo();
//        if (!$result_visitanteTipo['status']) {
//            throw new \Exception($result_visitanteTipo['data'], 409);
//        }
//        $content['visitanteTipo'] = $result_visitanteTipo['data'];

        $content['titulos'] = $this->Rs_configuracion->getTitulos();

        if (!empty($content['visitante']['DE_idPais'])) {
            $result_estados = $this->get('pecc')->getEstados($content['visitante']['DE_idPais']);
            if (!$result_estados['status']) {
                throw new \Exception($result_estados['data'], 409);
            }
            $content['estados'] = $result_estados['data'];
        }
        array_push($content["breadcrumb"], Array("Modulo_" . strtoupper($lang) => $content['visitante']['NombreCompleto'], "Ruta" => "", 'Permisos' => array()));
        return $this->render('ShowDashboardRSVisitantePerfilBundle:VisitantePerfil:visitantePerfil.html.twig', array('content' => $content));
    }

    public function updateGeneralDataAction(Request $request) {
        $this->VisitantePerfilModel = new VisitantePerfilModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        if ($request->getMethod() != 'POST') {
            return Array('status' => FALSE, 'error' => "No allowed access method");
        }

        $post = $request->request->all();

        if (!empty($post['DE_idPais'])) {
            $result_paises = $this->get('pecc')->getPaises($lang);
            if (!$result_paises['status']) {
                throw new \Exception($result_paises['data'], 409);
            }
            $post['DE_Pais'] = $result_paises['data'][$post['DE_idPais']]['Pais_ES'];
        }

        if (!empty($post['DE_idEstado'])) {
            $result_estados = $this->get('pecc')->getEstados($post['DE_idPais']);
            if (!$result_estados['status']) {
                throw new \Exception($result_estados['data'], 409);
            }
            $post['DE_Estado'] = $result_estados['data'][$post['DE_idEstado']]['Estado'];
        }
        $post['CadenaUnica'] = $this->VisitantePerfilModel->sanear_string(strtolower($post['Nombre']) . strtolower($post['ApellidoPaterno']) . strtolower($post['Email']));

        $elite = $this->VisitantePerfilModel->set_elite($idEdicion, $post['idVisitante'], $post['ClubElite']);
        if (!$elite['status']) {
            throw new \Exception($elite['data'], 409);
        }
        unset($post['ClubElite']);

        $stringData = $this->VisitantePerfilModel->createString($post);
        $result_inserted = $this->VisitantePerfilModel->insertEditVisitante($stringData, $idEvento, $idEdicion, $post['idVisitante']);
        if (!$result_inserted['status']) {
            throw new \Exception($result_inserted['data'], 409);
        }
        $visitante = $result_inserted;

//        $result_syncFM = $this->DatosGeneralesModel->syncFMVisitante($visitante['data'][0], $idEvento, $idEdicion);
//        if (!$result_syncFM['status']) {
//            throw new \Exception($result_syncFM['data'], 409);
//        }


        return $this->jsonResponse($visitante);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
