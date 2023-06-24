<?php

namespace Visitante\VisitanteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Utilerias\TextoBundle\Model\TextoModel;
use Visitante\VisitanteBundle\Model\VisitanteConfiguration;
use Visitante\VisitanteBundle\Model\VisitanteModel;

class VisitanteController extends Controller {

    protected $TextoModel, $VisitanteModel, $configuracion;

    const TEMPLATE = 9;
    const MAIN_ROUTE = "visitante";

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->configuracion = new VisitanteConfiguration();
    }

    public function visitorAction(Request $request) {
        $this->VisitanteModel = new VisitanteModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set('OriginView', self::MAIN_ROUTE);
        $content = array();
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content["breadcrumb"] = $breadcrumb;
        $content["idModuloIxpo"] = $breadcrumb[0]['idModuloIxpo'];

        /* Obtenemos textos del Template AE_AdminVisitantes */
        $section_text = $this->VisitanteModel->getTexts($lang, self::TEMPLATE);
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

        $args = Array('c."Principal"' => "true", 'e."idEvento"' => $idEvento);

        $content['routeName'] = $request->get('_route');
        $content['App'] = $App;
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get('idEdicion');

        $content["Visitante_table_column_categories"] = $this->configuracion->getColumnCategories($section_text);
        $content["Visitante_table_columns"] = $this->configuracion->getColumnDefs($section_text, $lang, $idEdicion);
        $session->set('columns_visit', $content["Visitante_table_columns"]);
        $content['currentRoute'] = $request->get('_route');
        $content['tabPermission'] = json_decode($this->VisitanteModel->tabsPermission($user), true);
        return $this->render('VisitanteVisitanteBundle:Visitante:lista_visitante.html.twig', array('content' => $content));
    }

    public function getToDataTableAction(Request $request) {
        $this->VisitanteModel = new VisitanteModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set("edicion_visit", $idEdicion);
        $session->remove('seting-dt_visit');
        $text = $this->VisitanteModel->getTexts($lang, self::TEMPLATE);

        if ($request->getMethod() != 'POST') {
            throw new \Exception("No allowed method", 409);
        }

        $post = $request->request->all();

        $params["where"][] = Array("name" => 'vise."idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
        $params["where"][] = Array("name" => 'vis."Prueba"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
        $params["where"][] = Array("name" => 'vis."Asociado"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
        $params["where"][] = Array("name" => 'vis."Comprador"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
        $params["where"][] = Array("name" => 'vis."Prensa"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
        $result_count = $this->VisitanteModel->getCountVisitante(Array(), $params);

        if (!$result_count["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting all records"));
        } else {
            $session->set('qry_count_visit', $result_count["count"]);
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
            $params["where"][] = Array("name" => 'vis."Prueba"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vise."idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Asociado"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Comprador"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Prensa"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
            $result_filtered_count = $this->VisitanteModel->getCountVisitante($columns, $params);
            if (!$result_filtered_count["status"]) {
                return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting filtered records"));
            } else {
                $session->set('count_filtered_visit', $result_filtered_count["count"]);
            }
            $records_filtered = $result_filtered_count["data"][0]["total"];
        } else {
            $session->remove('count_filtered_visit');
            $params["where"][] = Array("name" => 'vis."Prueba"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR);
            $params["where"][] = Array("name" => 'vise."idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Asociado"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Comprador"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Prensa"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
        }

        $order = $this->buildOrderColumns($post["order"], $raw_columns, $lang, $idEdicion, $text);
        $result_query = $this->VisitanteModel->getVisitanteCustom($columns, $params, $order, $post["length"], $post["start"]);
        if (!$result_query["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error getting records"));
        } else {
            $session->set('data_qry_visit', $result_query["data_qry"]);
        }
        $data = $this->matchColumnDefsData($result_query["data"], $lang, $idEdicion, $text);
        foreach ($data as $key => $value) {
            $data[$key]['FechaAlta_AE'] = substr($value['FechaAlta_AE'], 0, 10);
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
        $this->VisitanteModel = new VisitanteModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $edicion_visit = $session->get("edicion_visit");
        $text = $this->VisitanteModel->getTexts($lang, self::TEMPLATE);

        if ($idEdicion != $edicion_visit) {
            $session->remove('seting-dt_visit');
            $session->remove('qry_count_visit');
            $session->remove('count_filtered_visit');
            $session->remove('data_qry_visit');
        }
        $session->set("edicion_visit", $idEdicion);

        if ($request->getMethod() != 'POST') {
            throw new \Exception("No allowed method", 409);
        }
        $post = $request->request->all();
        if ($session->has('qry_count_visit')) {
            $qry_count = $session->get('qry_count_visit');
            $result_count = $this->VisitanteModel->getCountVisitante(Array(), $qry_count["params"], $qry_count["qry"]);
        } else {
            $params["where"][] = Array("name" => 'vis."Prueba"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR);
            $params["where"][] = Array("name" => 'vise."idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Asociado"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Comprador"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Prensa"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
            $result_count = $this->VisitanteModel->getCountVisitante(Array(), $params);
        }
        if (!$result_count["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting all records"));
        } else {
            $session->set('qry_count_visit', $result_count["count"]);
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

        if ($session->has("count_filtered_visit")) {
            $count_filtered = $session->get('count_filtered_visit');
            $params = $count_filtered["params"];
        }

        if (array_key_exists("where", $params) && COUNT($params["where"]) > 0) {
            if ($session->has("count_filtered_visit")) {
                $count_filtered = $session->get('count_filtered_visit');
                $result_filtered_count = $this->VisitanteModel->getCountVisitante($columns, $count_filtered["params"], $count_filtered["qry"]);
            } else {
                $params["where"][] = Array("name" => 'vis."Prueba"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
                $params["where"][] = Array("name" => 'vise."idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
                $params["where"][] = Array("name" => 'vis."Asociado"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
                $params["where"][] = Array("name" => 'vis."Comprador"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
                $params["where"][] = Array("name" => 'vise."Preregistrado"', "operator" => "=", "value" => '1', "type" => \PDO::PARAM_STR, "clause" => "AND");
                $params["where"][] = Array("name" => 'vis."Prensa"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
                $result_filtered_count = $this->VisitanteModel->getCountVisitante($columns, $params);
            }
            if (!$result_filtered_count["status"]) {
                return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting filtered records"));
            } else {
                $session->set('count_filtered_visit', $result_filtered_count["count"]);
            }
            $records_filtered = $result_filtered_count["data"][0]["total"];
        } else {
            $params["where"][] = Array("name" => 'vis."Prueba"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR);
            $params["where"][] = Array("name" => 'vise."idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Asociado"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Comprador"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vise."Preregistrado"', "operator" => "=", "value" => '1', "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Prensa"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
        }

        $order = $this->buildOrderColumns($post["order"], $raw_columns, $lang, $idEdicion, $text);

        if ($session->has("data_qry_visit")) {
            $data_qry = $session->get('data_qry_visit');
            $result_query = $this->VisitanteModel->getVisitanteCustom($columns, $data_qry["params"], $order, $post["length"], $post["start"]);
        } else {
            $result_query = $this->VisitanteModel->getVisitanteCustom($columns, $params, $order, $post["length"], $post["start"]);
        }
        if (!$result_query["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error getting records"));
        } else {
            $session->set('data_qry_visit', $result_query["data_qry"]);
        }

        $data = $this->matchColumnDefsData($result_query["data"], $lang, $idEdicion, $text);
        foreach ($data as $key => $value) {
            $data[$key]['FechaAlta_AE'] = substr($value['FechaAlta_AE'], 0, 10);
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

    public function exportVisitorDataAction(Request $request) {
        $this->VisitanteModel = new VisitanteModel($this->container);
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $text = $this->VisitanteModel->getTexts($lang, self::TEMPLATE);

        date_default_timezone_set("America/Mexico_City");

        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_Visitantes " . date('d-m-Y G.i');

        $post = $request->request->all();
        $post_data = json_decode(str_replace('\"', '"', $post["post_data"]), TRUE);

        $result_build = $this->buildParamsAndColumnFromDTColumns($post_data["columns"], $lang, $idEdicion, $text);
        $params = Array("where" => $result_build["params"]);
        if (array_key_exists("where", $params) && COUNT($params["where"]) > 0) {
            $params["where"][] = Array("name" => 'vis."Prueba"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR);
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Comprador"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Asociado"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
        } else {
            $params["where"][] = Array("name" => 'vis."Prueba"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR);
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
            $params["where"][] = Array("name" => 'vis."Comprador"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
            $params["where"][] = Array("name" => 'vis."Asociado"', "operator" => "=", "value" => '0', "type" => \PDO::PARAM_STR, "clause" => "AND");
        }

        $columns = $result_build["columns"];
        $column_defs = $this->configuracion->getColumnDefs($text, $lang, $idEdicion);

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

        $order = $this->buildOrderColumns($post["order"], $raw_columns, $lang, $idEdicion, $text);
        if ($session->has("data_qry_visit")) {
            $data_qry = $session->get('data_qry_visit');
            $params["where"] = $data_qry["params"]["where"];
            $result_query = $this->VisitanteModel->getVisitanteCustom($columns, $data_qry["params"], $order);
        } else {
            $result_query = $this->VisitanteModel->getVisitanteCustom($columns, $params, $order);
        }
        $subheader = $section_text["data"]["sas_totalRegistros"] . "Total de Registros: " . count($result_query["data"]);
        if (!$result_query["status"]) {
            die("Error getting records");
        }

        $data = $this->matchColumnDefsData($result_query["data"], $lang, $idEdicion, $text);

        $header_report = "";
        $r = array("\"", "'", "jp", "jv", "%", "{", "}", "->>");
        if (COUNT($params["where"]) > 0) {
            $header_report = $section_text["data"]["sas_filtrosAplicados"] . "Filtros Aplicados: ";
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
        if ($header_report == "Filtros Aplicados: ") {
            $header_report .= "Ninguno";
        }
        array_pop($raw_columns);
        $response = $this->render('VisitanteVisitanteBundle:Visitante:generic_table.html.twig', array('header' => $header_report, 'subheader' => $subheader, 'columns' => $raw_columns, 'data' => $data));

        $response->headers->set("Content-Type", "application/vnd.ms-excel");
        $response->headers->set("charset", "utf-8");
        $response->headers->set("Content-Disposition", "attachment;filename=" . $file_name . ".xls");
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;

//        return $this->excelReport($data, $raw_columns, $file_name, $header_report, $subheader);
    }

    public function sendGafeteDigitalAction(Request $request){
        $this->VisitanteModel = new VisitanteModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        if($request->getMethod() == 'POST'){
            $post = $request->request->all();
            $idVisitante = $post['idVisitante'];
            $idEvento = $session->get('edicion')["idEvento"];
            $idEdicion = $session->get('idEdicion');
            $data = array(
                "idVisitante" => $idVisitante,
                "idEdicion" => $idEdicion,
                "idEvento" => $idEvento
            );
            $result_visitante = $this->VisitanteModel->getVisitanteBadge($data);
            if (!$result_visitante['status']) {
                throw new \Exception($result_visitante['data'], 409);
            }
            $content['datosVis'] = $result_visitante['data'];
            $bodyEmail = $this->renderView('VisitanteAsociadoBundle:Asociado:bodyEmail.html.twig', array('content' => $content));
            $body = "";
            $datosVis = $result_visitante['data'];
            $lang = 'ES';
            $qr = 'S15D69F88D4';
            $digibadge = 'AntadDigibage';
            $result_pdf = $this->createPDF($body, $lang, $datosVis, $digibadge);
            //$result_pdf = $this->createPDF1($datosVis, $lang);
            $file_digi = str_replace(" ", "", 'digibage/' . $idVisitante . '.pdf');
            ////////////////////////////creacion del wallet ///////////////////////////
            $crWallet = $this->createWallet($datosVis);
            $file_wall = str_replace(" ", "", 'wallet/' . $idVisitante . '.pkpass');
            ///////////////////////////////////////////////////////////////////////////
            $docs[]=$file_digi;
            $docs[]=$file_wall;
            $result = $this->get('ixpo_mailer')->send_emailDocs('Digibage Antad', $datosVis['Email'], $bodyEmail, 'es', $docs);
            $result_email = array(
                "status" => true,
                "data" => "Éxito"
            );
        }
        else{
            $result_email = array(
                "status" => false,
                "data" => "Metodo no permitido"
            );
        }
        $response = new Response(json_encode($result_email));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function createPDF($body, $lang, $aux, $digibadge) {
        /* generamos pdf para impresion */
        $medidas = array(140, 230);
        $pdf = $this->get("white_october.tcpdf")->create('vertical', 'mm', $medidas, true, 'UTF-8', false);
        $style = array(
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => array(255, 255, 255),
            'pt' => 20
        );
        $styleB = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            //            'hpadding' => 'auto',
            //            'vpadding' => 'auto',
            'fgcolor' => array(0, 0, 0),
            'bgcolor' => array(255, 255, 255),
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4,
            'cellfitalign' => 'L'
        );

        // set document information
        $pdf->SetAuthor('ANTAD_DIGIBADGE');
        $pdf->SetTitle('ANTAD');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $font_size = $pdf->pixelsToUnits('27');
        $pdf->SetFont('helvetica', '', $font_size, '', 'default', true);
        $pdf->SetMargins(-2, -5, -2,true);
        $pdf->SetAutoPageBreak(TRUE, 0);
       
        for ($i = 0; $i < count($aux); $i++) {
            $pdf->AddPage();
            $content = array();
            $content['datosVis'] = $aux[$i];
            $html = $this->renderView('VisitanteAsociadoBundle:Asociado:digibadge.html.twig', array('content' => $content));
            $pdf->writeHTML($html, false, false, false, false, '');
            $pdf->write2DBarcode($aux[$i]['idVisitante'], 'QRCODE,M', 34, 110, 70, 60, $style, 'N');
           // $pdf->write1DBarcode($aux[$i]['idVisitante'], 'C128A', 33, 155, 88, 25, 0.9, $styleB, 'N'); //cuando lleva cargo
            $footer = $this->renderView('VisitanteAsociadoBundle:Asociado:footerDigibage.html.twig');
            $pdf->writeHTMLCell(0, 0, '', 180, $footer, '', 0, '', true, '', false);
        
        }
        $pdf->lastPage();



        $pdf->Output(str_replace(" ", "", realpath('digibage') . "/" . $aux[0]['idVisitante'] . '.pdf'), 'F');

        $pdf_txt = $pdf->Output(str_replace(" ", "", realpath('digibage') . "/" . $aux[0]['idVisitante'] . '.pdf'), 'S');

        $base64 = base64_encode($pdf_txt);

        return ($base64);
    }

    public function createWallet($infoB) {
        for ($i = 0; $i < count($infoB); $i++) {
            $pass = new PKPass('https://demo.infoexpo.com.mx/demo_infoticket/web/Certificate/Certificados.p12', 'Ixpo1234');
            $data = [
                'description' => 'Descripcion',
                'formatVersion' => 1,
                'organizationName' => 'Expo-Antad',
                'passTypeIdentifier' => 'pass.com.infoticket', // Change this!
                'serialNumber' => '1234566',
                'teamIdentifier' => '2S9K34QZ63', // Change this!
                'eventTicket' => [
                    'headerFields' => [
                        [
                            'key' => 'eventHeader',
                            'label' => 'Categoria',
                            // 'value' => 'ESTUDIANTE',
                            'value' => $infoB[$i]['VisitanteTipoES'],
                            'textAlignment' => 'PKTextAlignmentNatural'
                        ]
                    ],
                    'primaryFields' => [
                        [
                            'key' => 'filmName',
                            'label' => 'Evento:',
                            // 'value' => $infoB[$i]['NombreEvento'],
                            'value' => '',
                            'textAlignment' => 'PKTextAlignmentNatural'
                        ],
                        
                        // [
                        //     'key' => 'dress',
                        //     'label' => 'Dirección:',
                        //     'value' => $infoB[$i]['DireccionRecinto'],
                        // ]
                    ],
                    'secondaryFields' => [
                        
                        [
                            'key' => 'orderNumber',
                            'label' => 'Nombre',
                            'value' => $infoB[$i]['NombreCompleto'],
                            'textAlignment' => 'PKTextAlignmentLeft'
                        ],
                        [
                            'key' => 'order',
                            'label' => 'Cargo:',
                            'value' => $infoB[$i]['DE_Cargo'],
                            'textAlignment' => 'PKTextAlignmentRight'
                        ]
                    
                        
                    ],
                    'auxiliaryFields' => [
                        [
                            'key' => 'orderNumber',
                            'label' => '',
                            'value' => '',
                            'textAlignment' => 'PKTextAlignmentLeft'
                        ],
                        [
                            'key' => 'site',
                            'label' => 'Empresa:',
                            'value' => $infoB[$i]['DE_RazonSocial'],
                            'textAlignment' => 'PKTextAlignmentCenter'

                        ],[
                            'key' => 'orderNumber',
                            'label' => '',
                            'value' => '',
                            'textAlignment' => 'PKTextAlignmentRight'
                        ],
                        // ],[
                        //     'key' => 'seat',
                        //     'label' => 'Hora:',
                        //     'value' => $infoB[$i]['HoraInicio'] . ' Hrs',
                        //     'textAlignment' => 'PKTextAlignmentRight'

                        // ]
                    ],
                ],
                'barcode' => [
                    'format' => 'PKBarcodeFormatQR',
                    'message' => $infoB[$i]['idVisitante'],
                    'messageEncoding' => 'iso-8859-1',
                ],
                // 'backgroundColor' => 'rgb(196, 216, 226)',
                // 'backgroundColor' => 'rgb(255, 220, 0)',
                // 'logoText' => 'INFOTICKET',
                'relevantDate' => date('Y-m-d\TH:i:sP')
            ];
            $pass->setData($data);
            $pass->addFile('images/wallet/icon.png');
            $pass->addFile('images/wallet/icon@2x.png');
            // $pass->addFile('images/wallet/logo.png');
            // $pass->addFile('images/background.png');
            $pass->addFile('images/wallet/strip.png');
            $pass->addFile('images/wallet/footer.png');



       $pass->create(true, $infoB[$i]['idVisitante']);
//        $pass->create(true);
// Create and output the pass
//            if (!) {
//                return False;
//            }
//            return True;
        }
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
        $session->set('seting-dt_visit', $post);
        return $this->jsonResponse($post);
    }

    public function getSessionDataAction(Request $request) {
        $session = $request->getSession();
        $data = array("seting" => $session->get('seting-dt_visit'), "param" => $session->get('data_qry_visit')["params"]["where"], "columns" => $session->get('columns_visit'));
        return $this->jsonResponse($data);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
