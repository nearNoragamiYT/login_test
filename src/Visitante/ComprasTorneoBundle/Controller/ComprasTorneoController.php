<?php

namespace Visitante\ComprasTorneoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Utilerias\TextoBundle\Model\TextoModel;
use Visitante\ComprasBundle\Model\ComprasConfiguration;
use Visitante\ComprasBundle\Model\ComprasModel;

class ComprasTorneoController extends Controller {

    protected $TextoModel, $ComprasModel, $configuracion, $prueba;

    const TEMPLATE = 9;
    const MAIN_ROUTE = "compras_torneo";

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->configuracion = new ComprasConfiguration();
        $this->ComprasModel = new ComprasModel();
        $this->prueba = 1;
    }

    public function comprasTorneoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $content = array();

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado  */
          $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
          if (!$breadcrumb) {
          $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
          return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
          }
          $content["breadcrumb"] = $breadcrumb;

        /* Obtenemos textos del Template AE_AdminVisitantes */
        $section_text = $this->ComprasModel->getTexts($lang, self::TEMPLATE);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];

        /* Obtenemos los paises del PECC */
        $result_paises = $this->get('pecc')->getPaises($lang);
        if (!$result_paises['status']) {
            throw new \Exception($result_paises['data'], 409);
        }
        $content['paises'] = $result_paises['data'];

        /* Consultamos el tipo de cambio actual */
//        $args = array("idEvento" => $idEvento, "idEdicion" => $idEdicion);
//        $result_tipoCambio = $this->ComprasModel->getTipoCambio($args);
//        if (!$result_tipoCambio['status']) {
//            throw new \Exception($result_tipoCambio['data'], 409);
//        }
//        $content['TipoCambio'] = $result_tipoCambio['data'][0];
        $args = Array('c."Principal"' => "true", 'e."idEvento"' => $idEvento);

        $content['routeName'] = $request->get('_route');
        $content['App'] = $App;
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $idEdicion;

        $content["Visitante_table_column_categories"] = $this->configuracion->getColumnCategories($section_text);
        $content["Visitante_table_columns"] = $this->configuracion->getColumnDefs($section_text, $lang, $idEdicion);
        $session->set('columns_comp_torneos', $content["Visitante_table_columns"]);
        $content['currentRoute'] = $request->get('_route');
        $content['tabPermission'] = json_decode($this->ComprasModel->tabsPermission($user), true);
        $session->set("purchase_origin", "visitante_compras_torneo");
        return $this->render('VisitanteComprasTorneoBundle:ComprasTorneo:lista_compras_torneo.html.twig', array('content' => $content));
    }

    public function getToDataTableAction(Request $request) {
        $this->ComprasModel = new ComprasModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set("edicion_comp_torneos", $idEdicion);
        $session->remove('seting-dt_comp_torneos');
        $text = $this->ComprasModel->getTexts($lang, self::TEMPLATE);

        if ($request->getMethod() != 'POST') {
            throw new \Exception("No allowed method", 409);
        }

        $post = $request->request->all();

        $params["where"][] = Array("name" => 'vise."idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
        $params["where"][] = Array("name" => 'vis."Prueba"', "operator" => "=", "value" => $this->prueba, "type" => \PDO::PARAM_STR, "clause" => "AND");
        $params["where"][] = Array("name" => 'comp."VisitanteTorneo"', "operator" => "=", "value" => 1, "type" => \PDO::PARAM_STR, "clause" => "AND");
        $result_count = $this->ComprasModel->getCountVisitante(Array(), $params);

        if (!$result_count["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting all records"));
        } else {
            $session->set('qry_count_comp_torneos', $result_count["count"]);
        }
        $total_records = $records_filtered = $result_count["data"][0]["total"];

        $result_build = $this->buildParamsAndColumnFromDTColumns($post["columns"], $lang, $idEdicion, $text);
        $params = Array("where" => $result_build["params"]);

        $columns = $result_build["columns"];
        $column_defs = $this->configuracion->getColumnDefs($text, $lang, $idEdicion);

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

            $params["where"][] = Array("name" => 'vise."idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Prueba"', "operator" => "=", "value" => $this->prueba, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'comp."VisitanteTorneo"', "operator" => "=", "value" => 1, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $result_filtered_count = $this->ComprasModel->getCountVisitante($columns, $params);
            if (!$result_filtered_count["status"]) {
                return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting filtered records"));
            } else {
                $session->set('count_filtered_comp_torneos', $result_filtered_count["count"]);
            }
            $records_filtered = $result_filtered_count["data"][0]["total"];
        } else {
            $session->remove('count_filtered_comp_torneos');
            $params["where"][] = Array("name" => 'vise."idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
            $params["where"][] = Array("name" => 'vis."Prueba"', "operator" => "=", "value" => $prueba, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'comp."VisitanteTorneo"', "operator" => "=", "value" => 1, "type" => \PDO::PARAM_STR, "clause" => "AND");
        }

        $order = $this->buildOrderColumns($post["order"], $raw_columns, $lang, $idEdicion, $text);

        $result_query = $this->ComprasModel->getVisitanteCustom($columns, $params, $order, $post["length"], $post["start"]);
        if (!$result_query["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error getting records"));
        } else {
            $session->set('data_qry_comp_torneos', $result_query["data_qry"]);
        }
        $data = $this->matchColumnDefsData($result_query["data"], $lang, $idEdicion, $text);
        foreach ($data as $key => $value) {
            $data[$key]['Total'] = number_format($value['Total'], 2, '.', ',');
            $data[$key]['FechaCreacion'] = substr($value['FechaCreacion'], 0, 10);
        }

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
        $this->ComprasModel = new ComprasModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $edicion_visit = $session->get("edicion_comp_torneos");
        $text = $this->ComprasModel->getTexts($lang, self::TEMPLATE);

        if ($idEdicion != $edicion_visit) {
            $session->remove('seting-dt_comp_torneos');
            $session->remove('qry_count_comp_torneos');
            $session->remove('count_filtered_comp_torneos');
            $session->remove('data_qry_comp_torneos');
        }
        $session->set("edicion_comp_torneos", $idEdicion);

        if ($request->getMethod() != 'POST') {
            throw new \Exception("No allowed method", 409);
        }

        $post = $request->request->all();
        if ($session->has('qry_count_comp_torneos')) {
            $qry_count = $session->get('qry_count_comp_torneos');
            $result_count = $this->ComprasModel->getCountVisitante(Array(), $qry_count["params"], $qry_count["qry"]);
        } else {
            $params["where"][] = Array("name" => 'vise."idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
            $params["where"][] = Array("name" => 'vis."Prueba"', "operator" => "=", "value" => $this->prueba, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'comp."VisitanteTorneo"', "operator" => "=", "value" => 1, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $result_count = $this->ComprasModel->getCountVisitante(Array(), $params);
        }
        if (!$result_count["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting all records"));
        } else {
            $session->set('qry_count_comp_torneos', $result_count["count"]);
        }
        $total_records = $records_filtered = $result_count["data"][0]["total"];

        $result_build = $this->buildParamsAndColumnFromDTColumns($post["columns"], $lang, $idEdicion, $text);
        $params = Array("where" => $result_build["params"]);

        $columns = $result_build["columns"];
        $column_defs = $this->configuracion->getColumnDefs($text, $lang, $idEdicion);

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

        if ($session->has("count_filtered_comp_torneos")) {
            $count_filtered = $session->get('count_filtered_comp_torneos');
            $params = $count_filtered["params"];
        }

        if (array_key_exists("where", $params) && COUNT($params["where"]) > 0) {
            if ($session->has("count_filtered_comp_torneos")) {
                $count_filtered = $session->get('count_filtered_comp_torneos');
                $result_filtered_count = $this->ComprasModel->getCountVisitante($columns, $count_filtered["params"], $count_filtered["qry"]);
            } else {
                $params["where"][] = Array("name" => 'vise."idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
                $params["where"][] = Array("name" => 'vis."Prueba"', "operator" => "=", "value" => $this->prueba, "type" => \PDO::PARAM_STR, "clause" => "AND");
                $params["where"][] = Array("name" => 'comp."VisitanteTorneo"', "operator" => "=", "value" => 1, "type" => \PDO::PARAM_STR, "clause" => "AND");
                $result_filtered_count = $this->ComprasModel->getCountVisitante($columns, $params);
            }
            if (!$result_filtered_count["status"]) {
                return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting filtered records"));
            } else {
                $session->set('count_filtered_comp_torneos', $result_filtered_count["count"]);
            }
            $records_filtered = $result_filtered_count["data"][0]["total"];
        } else {
            $params["where"][] = Array("name" => 'vise."idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
            $params["where"][] = Array("name" => 'vis."Prueba"', "operator" => "=", "value" => $this->prueba, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'comp."VisitanteTorneo"', "operator" => "=", "value" => 1, "type" => \PDO::PARAM_STR, "clause" => "AND");
        }

        $order = $this->buildOrderColumns($post["order"], $raw_columns, $lang, $idEdicion, $text);

        if ($session->has("data_qry_comp_torneos")) {
            $data_qry = $session->get('data_qry_comp_torneos');
            $result_query = $this->ComprasModel->getVisitanteCustom($columns, $data_qry["params"], $order, $post["length"], $post["start"]);
        } else {
            $result_query = $this->ComprasModel->getVisitanteCustom($columns, $params, $order, $post["length"], $post["start"]);
        }
        if (!$result_query["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error getting records"));
        } else {
            $session->set('data_qry_comp_torneos', $result_query["data_qry"]);
        }

        $data = $this->matchColumnDefsData($result_query["data"], $lang, $idEdicion, $text);
        foreach ($data as $key => $value) {
            $data[$key]['Total'] = number_format($value['Total'], 2, '.', ',');
            $data[$key]['FechaCreacion'] = substr($value['FechaCreacion'], 0, 10);
        }

        $response_dt = Array(
            "status" => TRUE,
            "draw" => $post["draw"],
            "recordsTotal" => $total_records,
            "recordsFiltered" => $records_filtered,
            "data" => $data
        );
        return $this->jsonResponse($response_dt);
    }

    public function buildParamsAndColumnFromDTColumns($dt_columns, $lang, $idEdicion, $text) {
        $result_bind = Array("params" => Array(), "columns" => Array());
        $column_defs = $this->configuracion->getColumnDefs($text, $lang, $idEdicion);

        $total_columns = 0;
        //Seteamos columnas a consultar y los parámetros where
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
                    $result_bind["columns"][] = $column_values["table"] . "." . $column_name . $column_alias;
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

                            $filter = Array("name" => $column_values["table"] . "." . $column_name, "operator" => $operator, "value" => $value, "type" => \PDO::PARAM_STR, "text" => $column_raw_name);
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

    public function buildOrderColumns($order_columns, $all_columns, $lang, $idEdicion, $text) {
        //Seteamos columnas a ordenar
        $column_defs = $this->configuracion->getColumnDefs($text, $lang, $idEdicion);

        $order = Array();
        if (is_array($order_columns) && COUNT($order_columns) > 0) {
            //parse order colums
            foreach ($order_columns as $order_column) {
                $column_index = 0;
                foreach ($all_columns as $column_name => $column_text) {
                    if ($order_column["column"] == $column_index++) {
                        $pos = substr($column_name, strpos($column_name, ".") + 1);
                        if (array_key_exists($pos, $column_defs)) {
                            if (array_key_exists("json_column", $column_defs[$column_name]["filter_options"]) && is_string($column_defs[$column_name]["filter_options"]["json_column"]) && $column_defs[$column_name]["filter_options"]["json_column"] != "") {
                                $column_name = '"' . $column_defs[$pos]["filter_options"]["json_column"] . '"->>' . '\'' . $pos . '\'';
                            } else {
                                $column_name = '"' . $pos . '"';
                            }
                            $order[] = Array("name" => $column_defs[$pos]["table"] . "." . $column_name, "dir" => $order_column["dir"]);
                        }
                    }
                }
            }
        }
        return $order;
    }

    function matchColumnDefsData($records, $lang, $idEdicion, $text) {
        $data = Array();

        $column_defs = $this->configuracion->getColumnDefs($text, $lang, $idEdicion);
        //En caso de que algún campo cuente con múltiples valores le seteamos el correspondiente en base a la definición de los valores de la columna
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

    public function exportComprasTorneoDataAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->ComprasModel->getTexts($lang, self::TEMPLATE);

        date_default_timezone_set("America/Mexico_City");

        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_Compras " . date('d-m-Y G.i');

        $post = $request->request->all();
        $post_data = json_decode(str_replace('\"', '"', $post["post_data"]), TRUE);

        $result_build = $this->buildParamsAndColumnFromDTColumns($post_data["columns"], $lang, $idEdicion, $section_text);
        $params = Array("where" => $result_build["params"]);
        if (array_key_exists("where", $params) && COUNT($params["where"]) > 0) {
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Prueba"', "operator" => "=", "value" => $this->prueba, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'comp."VisitanteTorneo"', "operator" => "=", "value" => 1, "type" => \PDO::PARAM_STR, "clause" => "AND");
        } else {
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'comp."VisitanteTorneo"', "operator" => "=", "value" => 1, "type" => \PDO::PARAM_STR, "clause" => "AND");
        }
        $params["where"][] = Array("name" => 'comp."VisitanteTorneo"', "operator" => "=", "value" => 1, "type" => \PDO::PARAM_STR, "clause" => "AND");

        $columns = $result_build["columns"];
        $column_defs = $this->configuracion->getColumnDefs($section_text, $lang, $idEdicion);

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

        $order = $this->buildOrderColumns($post_data["order"], $raw_columns, $lang, $idEdicion, $section_text);

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

        if ($session->has("data_qry_comp_torneos")) {
            $data_qry = $session->get('data_qry_comp_torneos');
            $params["where"] = $data_qry["params"]["where"];
            $result_query = $this->ComprasModel->getVisitanteCustom($columns, $data_qry["params"], $order);
        } else {
            $result_query = $this->ComprasModel->getVisitanteCustom($columns, $params, $order);
        }
        $subheader = "Total de compras registradas:" . $section_text["data"]["sas_totalRegistros"] . " " . count($result_query["data"]);
        if (!$result_query["status"]) {
            die("Error getting records");
        }

        $data = $this->matchColumnDefsData($result_query["data"], $lang, $idEdicion, $text);
        $header_report = "";
        $r = array("\"", "'", "jp", "jv", "%", "{", "}", "->>", "comp.", "vis.", "vise.");
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
                $header_report .= " ";
            }
        }
        $header_report = substr($header_report, 0, strlen($header_report) - 2);
        if ($header_report == "Filtros Aplicados: ") {
            $header_report .= "Ninguno";
        }
        return $this->excelReport($data, $raw_columns, $file_name, $header_report, $subheader);
    }

    public function excelReport($general, $table_metadata, $filename, $header, $subheader) {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("Infoexpo")
                ->setTitle($filename)
                ->setSubject($filename)
                ->setDescription($filename);
        $flag = 1;
        $lastColumn = "A";
        $phpExcelObject->setActiveSheetIndex(0)->mergeCells('A1:Z1');
        $phpExcelObject->setActiveSheetIndex(0)->mergeCells('A2:Z2');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lastColumn . $flag, $header);
        $flag++;
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lastColumn . $flag, $subheader);
        $flag += 2;
        foreach ($table_metadata as $key => $value) {
            $phpExcelObject->getActiveSheet()->getColumnDimension($lastColumn)->setAutoSize(true);
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lastColumn . $flag, $value);
            $lastColumn++;
        }
        $flag++;
        foreach ($general as $index) {
            $lastColumn = "A";
            foreach ($index as $key => $value) {
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lastColumn . $flag, $value);
                $lastColumn++;
            }$flag++;
        }

        $phpExcelObject->getActiveSheet()->getStyle("A1:" . $lastColumn . "4")->getFont()->setBold(true);
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
        $session->set('seting-dt_comp_torneos', $post);
        return $this->jsonResponse($post);
    }

    public function getSessionDataAction(Request $request) {
        $session = $request->getSession();
        $data = array("seting" => $session->get('seting-dt_comp_torneos'), "param" => $session->get('data_qry_comp_torneos')["params"]["where"], "columns" => $session->get('columns_comp_torneos'));
        return $this->jsonResponse($data);
    }

    public function getCompraDataAction(Request $request, $idVisitante, $idCompra) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $content = array();

        /* Obtenemos textos generales sas */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $general_text = $general_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado */
//        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, 'compras');
//        if (!$breadcrumb) {
//            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
//            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
//        }
//        $content["breadcrumb"] = $breadcrumb;
        $content["idModuloIxpo"] = $breadcrumb[0]['idModuloIxpo'];
        /* Obtenemos textos generales del AE */
        $result_general_text = $this->ComprasModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text_ae = $result_general_text['data'];

        /* Obtenemos textos de la sección del comprobante */
        $result_template_text = $this->ComprasModel->getTexts($lang, 7);
        if (!$result_template_text['status']) {
            throw new \Exception($result_template_text['data'], 409);
        }
        $template_text = $result_template_text['data'];

        /* Obtenemos textos de la sección de la tienda */
        $result_template_tienda_text = $this->ComprasModel->getTexts($lang, 11);
        if (!$result_template_tienda_text['status']) {
            throw new \Exception($result_template_tienda_text['data'], 409);
        }
        $template_tienda_text = $result_template_tienda_text['data'];

        /* Obtenemos datos del visitante */
        $args = array('idVisitante' => $idVisitante);
        $result_visitante = $this->ComprasModel->getVisitante($args);
        if (!$result_visitante['status']) {
            throw new \Exception($result_visitante['data'], 409);
        }
        $visitante = $result_visitante['data'][0];

        /* Buscamos las compras */
        $args = array('idVisitante' => $idVisitante, 'c."idEvento"' => $idEvento, 'c."idEdicion"' => $idEdicion, 'idCompra' => $idCompra);
        $result_compras = $this->ComprasModel->getComprasVisitante($args);
        if (!$result_compras['status']) {
            throw new \Exception($result_compras['data'], 409);
        }
        $compras = $result_compras['data'];

        /* Catalogo de Forma de Pago */
        $formas_pago = $this->ComprasModel->getFormasPago($args);

        $args = array('Activo' => true, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
        $result_programas = $this->ComprasModel->getProgramas();
        if (!$result_programas['status']) {
            throw new \Exception($result_programas['data'], 409);
        }
        $programas = $result_programas['data'];
        /* Buscamos las compras */

        $result_visitanteTipoSocio = $this->ComprasModel->getVisitanteTipoSocio();
        if (!$result_visitanteTipoSocio['status']) {
            throw new \Exception($result_visitanteTipoSocio['data'], 409);
        }
        $visitanteTipoSocio = $result_visitanteTipoSocio['data'];

        $twig = "showComprobante";
        $visitante['idVisitante'] = $idVisitante;

        $content['App'] = $App;
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['general_text'] = $general_text;
        $content['general_text_ae'] = $general_text_ae;
        $content['template_text'] = $template_text;
        $content['template_tienda_text'] = $template_tienda_text;
        $content['edicion'] = $session->get('edicion');
        $content['idEdicion'] = $session->get('idEdicion');
        $content['visitante_tipo_socio'] = $visitanteTipoSocio;
        $content['visitante'] = $visitante;
        $content['compras'] = $compras;
        $content['formas_pago'] = $formas_pago;
        $content['programas'] = $programas;
        $content['twig'] = $twig;
        $content['lang'] = $lang;
        $content['subject'] = str_replace("%edicion%", $session->get('edicion')["Edicion_" . strtoupper($lang)], $template_text['ae_subjectComprobanteRegistro']);
        $content['LinkAE'] = $session->get('edicion')['LinkAE'];
        array_push($content["breadcrumb"], Array("Modulo_" . strtoupper($lang) => $idCompra, "Ruta" => "", 'Permisos' => array()));

        return $this->render('VisitanteComprasBundle:Compras:showComprobante.html.twig', array('content' => $content));
    }

    public function comprobanteReenviarAction(Request $request) {
        $session = $request->getSession();
        $idEdicion = $session->get('idEdicion');
        $lang = $session->get('lang');
        $post = $request->request->all();

        $url = $session->get('edicion')['LinkAE'] . 'utilerias/comprobante/email/' . $post['idVisitante'] . '/' . $lang;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $remote_server_output = curl_exec($ch);
        curl_close($ch);
        $result["status"] = true;
        return $this->jsonResponse($result);
    }

    public function updateStatusComprasAction(Request $request, $idVisitante, $idCompra) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $get = $request->query->all();

        /* Obtenemos textos generales sas */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $general_text = $general_text['data'];

        //Actualiza Estatus de Compra
        if ($get['idStatus']) {
            $result_compraCancelada = $this->ComprasModel->updateCompra($idCompra, $get['idStatus']);
            if (!$result_compraCancelada['status']) {
                throw new \Exception($result_compraCancelada['data'], 409);
            }
        }
        //Actualiza la Forma de Pago
        if ($get['idFormaPago']) {
            $result_formaPago = $this->ComprasModel->updateCompraFormaPago($idCompra, $get['idFormaPago']);
        } else {
            //Actualiza Compra Facturada
            if ($get['CompraFacturada'] == 0 || $get['CompraFacturada'] == 1) {//             
                $result_compraFacturada = $this->ComprasModel->updateCompraFacturada($idCompra, $get['CompraFacturada'], $get['FolioFactura']);
            }
        }

        $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
        return $this->redirectToRoute('compras_resumen', array('idVisitante' => $idVisitante, 'idCompra' => $idCompra));
    }

    public function reportdetalleComprasAction(Request $request) {
        $status = array(1 => 'Pendiente', 2 => 'Pagada', 3 => 'Cancelada', 4 => 'Cortesia');
        $boleano = array(1 => 'Si', 0 => 'No', null => 'No');
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();

        //$file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_ES"]) . "_Detalle_Compras " . date('d-m-Y G.i');
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_Detalle_Compras " . date('d-m-Y G.i');
        $args = array("idEdicion" => $idEdicion);
        //$header = $session->get('edicion')["Edicion_ES"];
        $header = $session->get('edicion')["Edicion_EN"];
        $subheader = "Detalle de Compras";

        $section_text = $this->ComprasModel->getTexts($lang, self::TEMPLATE);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];

        $data = $this->ComprasModel->getComprasReport($args);
        if (!$data['status']) {
            throw new \Exception($data['data'], 409);
        }
        $data = $data['data'];

        foreach ($data as $key => $value) {
            $data[$key]['idCompraStatus'] = $status[$value['idCompraStatus']];
            $data[$key]['Total'] = number_format($value['Total'], 2, '.', ',');
            $data[$key]['FechaCreacion'] = substr($value['FechaCreacion'], 0, 10);
            $data[$key]['FechaPagado'] = substr($value['FechaPagado'], 0, 10);
            $data[$key]['FechaCancelado'] = substr($value['FechaCancelado'], 0, 10);
            $data[$key]['ReqFactura'] = $boleano[$value['ReqFactura']];
            $data[$key]['CompraFacturada'] = $boleano[$value['CompraFacturada']];
        }

        $meta_data = array(
            "ID Compra",
            "Estatus Compra",
            "Monto Compra",
            "Fecha Compra",
            "Fecha Pago",
            "Fecha Cancelacion",
            "Producto",
            "Descripcion Producto",
            "Cantidad",
            "Precio Unitario",
            "Precio",
            "Descuento",
            "Cupon",
            "Descripcion Cupon",
            "Requiere Factura",
            "Compra Facturada",
            "ID Visitante",
            "Nombre",
            "Email",
            "Telefono",
        );

        return $this->excelReport($data, $meta_data, $file_name, $header, $subheader);
    }

    public function viewFileAction(Request $request, $idCompra) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $visitante = $profile->getData();

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $data = array('idCompra' => $idCompra);
            $resultView = $this->ComprasModel->view_file($data);
            if ($resultView['status']) {
                $result['status'] = TRUE;
                $result['data'] = $resultView['data'][0];
                //$result['message'] = $general_text['data']['sas_guardoExito'];
            } else {
                $result['error'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function ticketReenviarAction(Request $request) {
        $session = $request->getSession();
        $idEdicion = $session->get('idEdicion');
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        /* Obtenemos textos generales del AE */
        $result_general_text = $this->ComprasModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text_ae = $result_general_text['data'];

        /* Obtenemos textos de la sección del comprobante */
        $result_template_text = $this->ComprasModel->getTexts($lang, 7);
        if (!$result_template_text['status']) {
            throw new \Exception($result_template_text['data'], 409);
        }
        $template_text = $result_template_text['data'];

        /* Obtenemos textos de la sección de la tienda */
        $result_template_tienda_text = $this->ComprasModel->getTexts($lang, 11);
        if (!$result_template_tienda_text['status']) {
            throw new \Exception($result_template_tienda_text['data'], 409);
        }
        $template_tienda_text = $result_template_tienda_text['data'];
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $idVisitante = $post['idVisitante'];
            $idCompra = $post['idCompra'];
            /* Obtenemos datos del visitante */
            $args = array('idVisitante' => $idVisitante);
            $result_visitante = $this->ComprasModel->getVisitante($args);
            if (!$result_visitante['status']) {
                throw new \Exception($result_visitante['data'], 409);
            }
            $visitante = $result_visitante['data'][0];

            /* Buscamos las compras */
            $args = array('idVisitante' => $idVisitante, 'c."idEvento"' => $idEvento, 'c."idEdicion"' => $idEdicion, 'idCompra' => $idCompra);
            $result_compras = $this->ComprasModel->getComprasVisitante($args);
            if (!$result_compras['status']) {
                throw new \Exception($result_compras['data'], 409);
            }
            $compras = $result_compras['data'];

            /* Catalogo de Forma de Pago */
            $formas_pago = $this->ComprasModel->getFormasPago($args);

            $args = array('Activo' => true, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
            $result_programas = $this->ComprasModel->getProgramas();
            if (!$result_programas['status']) {
                throw new \Exception($result_programas['data'], 409);
            }
            $programas = $result_programas['data'];
            /* Buscamos las compras */

            $result_visitanteTipoSocio = $this->ComprasModel->getVisitanteTipoSocio();
            if (!$result_visitanteTipoSocio['status']) {
                throw new \Exception($result_visitanteTipoSocio['data'], 409);
            }
            $visitanteTipoSocio = $result_visitanteTipoSocio['data'];

            $visitante['idVisitante'] = $idVisitante;
            $content['App'] = $App;
            $content['lang'] = $lang;
            $content['general_text'] = $general_text;
            $content['general_text_ae'] = $general_text_ae;
            $content['template_text'] = $template_text;
            $content['template_tienda_text'] = $template_tienda_text;
            $content['edicion'] = $session->get('edicion');
            $content['visitante'] = $visitante;
            $content['compras'] = $compras;
            $content['lang'] = $lang;
            /* Comenzamos proceso de envio de mail */
            $body = $this->renderView('VisitanteComprasBundle:Compras:mail_ticket.html.twig', array('content' => $content));
            $content['visitante']['Email'] = "ricardog.infoexpo@gmail.com";
            $ixpo_mailer = $this->get('ixpo_mailer');
            $result = $this->get('ixpo_mailer')->send_email("Ticket Facturación de Compra", $content['visitante']['Email'], $body, $lang);
            $result_mail = array('status' => false, 'band' => '');
            if ($result) {
                $result_mail['status'] = true;
            }
            /* Fin estructura envio de Email */
        } else {
            $result_mail['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result_mail);
    }

    public function updateTasaCambioAction(Request $request) {
        $session = $request->getSession();
        $idEdicion = $session->get('idEdicion');
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $profile = $this->getUser();
        $user = $profile->getData();
        /* Obtenemos textos generales del AE */
        $result_general_text = $this->ComprasModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text_ae = $result_general_text['data'];

        /* Obtenemos textos de la sección del comprobante */
        $result_template_text = $this->ComprasModel->getTexts($lang, 7);
        if (!$result_template_text['status']) {
            throw new \Exception($result_template_text['data'], 409);
        }
        $template_text = $result_template_text['data'];

        /* Obtenemos textos de la sección de la tienda */
        $result_template_tienda_text = $this->ComprasModel->getTexts($lang, 11);
        if (!$result_template_tienda_text['status']) {
            throw new \Exception($result_template_tienda_text['data'], 409);
        }
        $template_tienda_text = $result_template_tienda_text['data'];
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $values = array("TasaCambioUSD" => $post['TasaCambio'], "idUsuario" => $user['idUsuario'], "idEvento" => $idEvento, "idEdicion" => $idEdicion);
            $result = $this->ComprasModel->insertTasaCambio($values);

            if ($result['status']) {
                $data['idUsuario'] = $user['idUsuario'];
                $data['TasaCambioUSD'] = $post['TasaCambio'];
                $data['idTasaCambio'] = $result['data'][0]['idTasaCambio'];
                $result['status'] = TRUE;
                $result['data'] = $data;
                $result['message'] = $general_text['data']['sas_guardoExito'];
            } else {
                $result['error'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result_mail['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
