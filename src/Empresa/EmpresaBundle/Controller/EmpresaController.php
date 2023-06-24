<?php

namespace Empresa\EmpresaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Utilerias\TextoBundle\Model\TextoModel;
use Empresa\EmpresaBundle\Model\EmpresaConfiguration;
use Empresa\EmpresaBundle\Model\EmpresaModel;
use PKPass\PKPass;

class EmpresaController extends Controller {

    protected $TextoModel, $EmpresaModel, $configuracion;

    const SECTION = 4;
    const MAIN_ROUTE = "empresa";

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->EmpresaModel = new EmpresaModel();
        $this->configuracion = new EmpresaConfiguration();
    }

    public function companiesAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set("companyOrigin", "expositores");
        $content = array();
        $content["breadcrumb"] = $this->EmpresaModel->breadcrumb(self::MAIN_ROUTE, $lang);
        $content['routeName'] = $request->get('_route');
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

        /* Obtenemos los paises del PECC */
        $result_paises = $this->get('pecc')->getPaises($lang);
        if (!$result_paises['status']) {
            throw new \Exception($result_paises['data'], 409);
        }
        $content['paises'] = $result_paises['data'];

        $args = Array('c."Principal"' => "true", 'e."idEvento"' => $idEvento);
        $content['App'] = $App;
        $content['user'] = $user;
        $content['lang'] = $lang;

        $content["Empresa_table_column_categories"] = $this->configuracion->getColumnCategories($section_text);
        $content["Empresa_table_columns"] = $this->configuracion->getColumnDefs($section_text, $lang, $idEdicion);
        $session->set('columns_empresas', $content["Empresa_table_columns"]);

        return $this->render('EmpresaEmpresaBundle:Empresa:lista_companies.html.twig', array('content' => $content));
    }

    public function getToDataTableAction(Request $request) {
        $session = $request->getSession();
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set("edicion_empresas", $idEdicion);
        $session->remove('seting-dt');

        if ($request->getMethod() != 'POST') {
            throw new \Exception("No allowed method", 409);
        }

        $post = $request->request->all();

        $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
        if ($user['idTipoUsuario'] == 6)
            $params["where"][] = Array("name" => '"idUsuario"', "operator" => "=", "value" => $user['idUsuario'], "type" => \PDO::PARAM_STR, "clause" => "AND");
        $result_count = $this->EmpresaModel->getCountEmpresa(Array(), $params);

        if (!$result_count["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting all records"));
        } else {
            $session->set('qry_count', $result_count["count"]);
        }
        $total_records = $records_filtered = $result_count["data"][0]["total"];

        $result_build = $this->buildParamsAndColumnFromDTColumns($post["columns"], $lang, $idEdicion);
        $params = Array("where" => $result_build["params"]);

        $columns = $result_build["columns"];
        $column_defs = $this->configuracion->getColumnDefs($this->TextoModel->getTexts($lang, self::SECTION), $lang, $idEdicion);

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
            if ($user['idTipoUsuario'] == 6)
                $params["where"][] = Array("name" => '"idUsuario"', "operator" => "=", "value" => $user['idUsuario'], "type" => \PDO::PARAM_STR, "clause" => "AND");
            $result_filtered_count = $this->EmpresaModel->getCountEmpresa($columns, $params);
            if (!$result_filtered_count["status"]) {
                return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting filtered records"));
            } else {
                $session->set('count_filtered', $result_filtered_count["count"]);
            }
            $records_filtered = $result_filtered_count["data"][0]["total"];
        } else {
            $session->remove('count_filtered');
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
            if ($user['idTipoUsuario'] == 6)
                $params["where"][] = Array("name" => '"idUsuario"', "operator" => "=", "value" => $user['idUsuario'], "type" => \PDO::PARAM_STR, "clause" => "AND");
        }

        $order = $this->buildOrderColumns($post["order"], $raw_columns, $lang, $idEdicion);
        $result_query = $this->EmpresaModel->getEmpresaCustom($columns, $params, $order, $post["length"], $post["start"]);
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
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $edicion_empresas = $session->get("edicion_empresas");

        if ($idEdicion != $edicion_empresas) {
            $session->remove('seting-dt');
            $session->remove('qry_count');
            $session->remove('count_filtered');
            $session->remove('data_qry');
        }
        $session->set("edicion_empresas", $idEdicion);

        if ($request->getMethod() != 'POST') {
            throw new \Exception("No allowed method", 409);
        }

        $post = $request->request->all();
        if ($session->has('qry_count')) {
            $qry_count = $session->get('qry_count');
            $result_count = $this->EmpresaModel->getCountEmpresa(Array(), $qry_count["params"], $qry_count["qry"]);
        } else {
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
            if ($user['idTipoUsuario'] == 6)
                $params["where"][] = Array("name" => '"idUsuario"', "operator" => "=", "value" => $user['idUsuario'], "type" => \PDO::PARAM_STR, "clause" => "AND");
            $result_count = $this->EmpresaModel->getCountEmpresa(Array(), $params);
        }
        if (!$result_count["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting all records"));
        } else {
            $session->set('qry_count', $result_count["count"]);
        }
        $total_records = $records_filtered = $result_count["data"][0]["total"];

        $result_build = $this->buildParamsAndColumnFromDTColumns($post["columns"], $lang, $idEdicion);
        $params = Array("where" => $result_build["params"]);

        $columns = $result_build["columns"];
        $column_defs = $this->configuracion->getColumnDefs($this->TextoModel->getTexts($lang, self::SECTION), $lang, $idEdicion);

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

        if ($session->has("count_filtered")) {
            $count_filtered = $session->get('count_filtered');
            $params = $count_filtered["params"];
        }

        if (array_key_exists("where", $params) && COUNT($params["where"]) > 0) {
            if ($session->has("count_filtered")) {
                $count_filtered = $session->get('count_filtered');
                $result_filtered_count = $this->EmpresaModel->getCountEmpresa($columns, $count_filtered["params"], $count_filtered["qry"]);
            } else {
                $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
                if ($user['idTipoUsuario'] == 6)
                    $params["where"][] = Array("name" => '"idUsuario"', "operator" => "=", "value" => $user['idUsuario'], "type" => \PDO::PARAM_STR, "clause" => "AND");
                $result_filtered_count = $this->EmpresaModel->getCountEmpresa($columns, $params);
            }
            if (!$result_filtered_count["status"]) {
                return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting filtered records"));
            } else {
                $session->set('count_filtered', $result_filtered_count["count"]);
            }
            $records_filtered = $result_filtered_count["data"][0]["total"];
        } else {
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
            if ($user['idTipoUsuario'] == 6)
                $params["where"][] = Array("name" => '"idUsuario"', "operator" => "=", "value" => $user['idUsuario'], "type" => \PDO::PARAM_STR, "clause" => "AND");
        }

        $order = $this->buildOrderColumns($post["order"], $raw_columns, $lang, $idEdicion);

        if ($session->has("data_qry")) {
            $data_qry = $session->get('data_qry');
            $result_query = $this->EmpresaModel->getEmpresaCustom($columns, $data_qry["params"], $order, $post["length"], $post["start"]);
        } else {
            $result_query = $this->EmpresaModel->getEmpresaCustom($columns, $params, $order, $post["length"], $post["start"]);
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
        $column_defs = $this->configuracion->getColumnDefs($this->TextoModel->getTexts($lang, self::SECTION), $lang, $idEdicion);

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
        $column_defs = $this->configuracion->getColumnDefs($this->TextoModel->getTexts($lang, self::SECTION), $lang, $idEdicion);

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

        $column_defs = $this->configuracion->getColumnDefs($this->TextoModel->getTexts($lang, self::SECTION), $lang, $idEdicion);
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

    public function exportEmpresaDataAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);

        date_default_timezone_set("America/Mexico_City");

        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_Expositores " . date('d-m-Y G.i');

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
        $column_defs = $this->configuracion->getColumnDefs($section_text, $lang, $idEdicion);

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
            $result_query = $this->EmpresaModel->getEmpresaCustom($columns, $data_qry["params"], $order);
        } else {
            $result_query = $this->EmpresaModel->getEmpresaCustom($columns, $params, $order);
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
        $session->set('seting-dt', $post);
        return $this->jsonResponse($post);
    }

    public function getSessionDataAction(Request $request) {
        $session = $request->getSession();
        $data = array("seting" => $session->get('seting-dt'), "param" => $session->get('data_qry')["params"]["where"], "columns" => $session->get('columns_empresas'));
        return $this->jsonResponse($data);
    }

//    public function _companiesAction(Request $request) {
//        $session = $request->getSession();
//        $lang = $session->get('lang');
//        $App = $this->get('ixpo_configuration')->getApp();
//        $profile = $this->getUser();
//        $user = $profile->getData();
//        $content = array();
//        $content['lang'] = $lang;
//        $content['App'] = $App;
//        $content['user'] = $user;
//
//        /* Obtenemos textos generales */
//        $general_text = $this->TextoModel->getTexts($lang);
//        if (!$general_text['status']) {
//            throw new \Exception($general_text['data'], 409);
//        }
//        $content['general_text'] = $general_text['data'];
//
//        /* Obtenemos textos de la seccion 4 */
//        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
//        if (!$section_text['status']) {
//            throw new \Exception($section_text['data'], 409);
//        }
//        $content['section_text'] = $section_text['data'];
//
//        /* Obtenemos los paises del PECC */
//        $result_paises = $this->get('pecc')->getPaises($lang);
//        if (!$result_paises['status']) {
//            throw new \Exception($result_paises['data'], 409);
//        }
//        $content['paises'] = $result_paises['data'];
//
//        /* Comienza la logica propia del Action */
//        $idEvento = $session->get('edicion')["idEvento"];
//        $idEdicion = $session->get('idEdicion');
//
//        $args = Array('c."Principal"' => "true", 'e."idEvento"' => $idEvento);
//        $companies = $this->EmpresaModel->getCompanies($args, $idEdicion);
//        $content["companies"] = $companies;
//
//        $companies_metadata = $this->EmpresaConfiguration->getCompanyMetaData($content['section_text']);
//        $content["companies_metadata"] = $companies_metadata;
//
//        $events = $this->EmpresaModel->getEvents();
//        $content["events"] = $events;
//
//        $idEdicion = $session->get('idEdicion');
//
//        $args = Array('p."idEdicion"' => $idEdicion);
//        $content["packages"] = $this->EmpresaModel->getPackages($args);
//
//        return $this->render('EmpresaEmpresaBundle:Empresa:empresa.html.twig', array('content' => $content));
//    }

    public function sendWelcomeEmailAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
        $App = $this->get('ixpo_configuration')->getApp();
        $ModuloIxpo = $this->EmpresaModel->breadcrumb(self::MAIN_ROUTE, $lang);
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['App'] = $App;
        $content['user'] = $user;

        /* Obtenemos textos de la sección */
        $text = $this->TextoModel->getTexts("ES", self::SECTION);
        if (!$text['status']) {
            throw new \Exception($text['data'], 409);
        }
        $section_text["ES"] = $text["data"];

        $text = $this->TextoModel->getTexts("EN", self::SECTION);
        if (!$text['status']) {
            throw new \Exception($text['data'], 409);
        }
        $section_text["EN"] = $text["data"];

        $editions = $this->EmpresaModel->getEditions();
        $content["editions"] = $editions;

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $type = count($post);
            
            /* Envio masivo de email, de lo contrario envio individual de email */
            if ($type == 0) {
                $content['tipoCorreo'] = 0;
                #$result = $this->EmpresaModel->getExpositoresMasivos(array("idEdicion" => $idEdicion, "idEtapa" => "'2'"));
                $result = $this->EmpresaModel->getExpositoresMasivos(array("idEdicion" => $idEdicion, "idEtapa" => "'2'", "idEmpresa" => "'3244'"));#Solo TEST                
                $mail_es = Array(9, 21, 25, 40, 44, 48, 50, 58, 60, 83, 87, 89, 134, 151, 162, 164, 165, 193, 210, 222, 226);
                /* Funcionalidad para insertar los log */
                $ContenidoNuevo = array();
                $consulta = "Envío Masivo de Email";
                $data_log = array(
                    'idEvento' => $idEvento,
                    'idEdicion' => $idEdicion,
                    'Modulo' => $ModuloIxpo[0]['breadcrumb'],
                    'Consulta' => "'" . $consulta . "'"
                );
                /* Fin de la funcionalidad de los log */
                foreach ($result as $exhibitor) {
                    if (filter_var($exhibitor['Email'], FILTER_VALIDATE_EMAIL)) {
                        if ($exhibitor['Email'] != "") {
                            if (in_array($exhibitor['DC_idPais'], $mail_es))
                                $lang = "ES";
                            else
                                $lang = "EN";

                            $content['lang'] = $lang;
                            $content['exhibitor'] = $exhibitor;
                            $vendedor = $content['exhibitor']['Vendedores'];
                            if ($this->before(",", $vendedor) != "") {
                                $content['exhibitor']['Vendedores'] = $this->before(",", $vendedor);
                            }
                            $content['section_text'] = $section_text[$lang];

                            $edition_name = $content['editions'][$exhibitor["idEdicion"]]["Edicion_" . $lang];
                            /* Estructura envío de email */
                            $ixpo_mailer = $this->get('ixpo_mailer');
                            $body = $this->renderView('EmpresaEmpresaBundle:Empresa:email_bienvenida.html.twig', array('content' => $content));
                            $result = $ixpo_mailer->send_email($content["section_text"]["sas_bienvenidaED"], $exhibitor["Email"], $body, $lang);
                            /* Fin estructura envio de Email */

                            /* Funcionalidad log */
                            $cont_new = array(
                                'idEmpresa' => $exhibitor['idEmpresa'],
                                'DC_NombreComercial' => $exhibitor['DC_NombreComercial'],
                                'Email' => $exhibitor['Email'],
                                'Lang' => $lang
                            );
                            array_push($ContenidoNuevo, $cont_new);
                            $status = true;
                            /* Fin de la funcionalidad log */
                        }
                    }
                }
                /* Agregar la key 'ContenidoNuevo_' al arreglo $ContenidoNuevo de tipo json, funcionalidad log */
                $data_log['ContenidoNuevo_'] = json_encode($ContenidoNuevo, JSON_FORCE_OBJECT);
                /* Bandera para saber si los correos se hicieron bien para que proceda hacer el log */
                if ($status) {
                    $this->get("ixpo_log")->InsertLogSeguimiento($consulta, $data_log);
                }
            } else {
                $result = $this->EmpresaModel->getExpositores(array("idEmpresa" => $post["idEmpresa"], "idEdicion" => $idEdicion));
                $dataGafete = $this->EmpresaModel->getDetalleGafete(array("idEmpresa" => $post["idEmpresa"], "idEdicion" => $idEdicion));

                /* Funcionalidad para insertar los log */
                $ContenidoNuevo = array();
                $consulta = "Envío Individual de Email";
                $data_log = array(
                    'idEvento' => $idEvento,
                    'idEdicion' => $idEdicion,
                    'Modulo' => $ModuloIxpo[0]['breadcrumb'],
                    'Consulta' => "'" . $consulta . "'"
                );
                /* Fin de la funcionalidad de los log */
                //                Individual
                $idEmpresa = $post['idEmpresa'];
                $content['tipoCorreo'] = $post['tipoCorreo'];

                foreach ($result as $exhibitor) {
                    if (filter_var($exhibitor['Email'], FILTER_VALIDATE_EMAIL)) {
                        if ($exhibitor['Email'] != "") {
                            if (!isset($post['lang']))
                                $lang = "ES";
                            else
                                $lang = $post['lang'];

                            $content['lang'] = $lang;
                            $content['exhibitor'] = $exhibitor;
                            $vendedor = $content['exhibitor']['Vendedores'];
                            if ($this->before(",", $vendedor) != "") {
                                $content['exhibitor']['Vendedores'] = $this->before(",", $vendedor);
                            }
                            $content['section_text'] = $section_text[$lang];

                            $edition_name = $content['editions'][$exhibitor["idEdicion"]]["Edicion_" . strtoupper($lang)];
                            /* Estructura envío de email */
                            $ixpo_mailer = $this->get('ixpo_mailer');

                            //tipoCorreo = 0 es bienvenida, tipoCorreo = 1 es Gafetes
                            if ($post['tipoCorreo'] == "0") {
                                $body = $this->renderView('EmpresaEmpresaBundle:Empresa:email_bienvenida.html.twig', array('content' => $content));
                            } else if ($post['tipoCorreo'] == "1") {
                                $body = $this->renderView('EmpresaEmpresaBundle:Empresa:email_bienvenida.html.twig', array('content' => $content));
                                $datosExp = $dataGafete;
                                $lang = $session->get('lang');
                                $qr = 'S15D69F88D4';
                                $digibadge = 'Antad Digibage';

                                $gafete = Array();

                                for ($i = 0; $i < count($datosExp); $i++) {
                                    $result_pdf = $this->createPDF($body, $lang, $datosExp[$i], $digibadge);
                                    $nombrecompleto = $datosExp[$i]['DGNombre'] . $datosExp[$i]['DGApellidoPaterno'];
                                    $gafete[] = str_replace(" ", "", 'digibage/' . $datosExp[$i]['idDetalleGafete'] . "_" . $nombrecompleto . '.pdf');
                                }

                                /*/Creacion del Wallet
                                $crWallet = $this->createWallet($datosExp);
                                $wallets = Array();
                                for ($i = 0; $i < count($datosExp); $i++) {
                                    $nombrecompleto = $datosExp[$i]['DGNombre'] . $datosExp[$i]['DGApellidoPaterno'];
                                    $wallets[] = str_replace(" ", "", 'wallet/' . $datosExp[$i]['idDetalleGafete'] . '.pkpass');
                                }*/

                                //Empieza el codigo para comprimir los gafetes y los wallets a un zip
                                $Zip = new \ZipArchive();

                                // The name of the Zip documents.
                                $zipName = "zip/" . "Gafetes_" . $idEmpresa . '.zip';

                                $Zip->open($zipName, \ZipArchive::CREATE);
                                foreach ($gafete as $file) {
                                    $Zip->addFromString(basename($file), file_get_contents($file));
                                }
                                $Zip->close();

                                /*$Zip = new \ZipArchive();
                                // The name of the Zip documents.
                                $zipName = "zip/" . "Wallet_" . $idEmpresa . '.zip';
                                $Zip->open($zipName, \ZipArchive::CREATE);
                                foreach ($wallets as $file) {
                                    $Zip->addFromString(basename($file), file_get_contents($file));
                                }
                                $Zip->close();*/

                                //Termina el codigo para comprimir los gafetes y los wallets a un zip

                                $rutaZip = Array(
                                    $rutaZip[] = str_replace(" ", "", 'zip/' . "Gafetes_" . $idEmpresa . '.zip'),
                                    #$rutaZip[] = str_replace(" ", "", 'zip/' . "Wallet_" . $idEmpresa . '.zip')
                                );

                                //$rutaZip[] = str_replace(" ", "", 'zip/'."Gafetes-Wallet_".$idEmpresa.'.zip');
                            }

//                            $correos = Array(
//                                "emilionito9@gmail.com",
//                                "marco.balderaspq@gmail.com",
//                                "didierer.infoexpo@gmail.com",
//                                "juantm72@gmail.com"
//                                "marizaa@infoexpo.com.mx",
//                                "yesenian@infoexpo.com.mx",
//                                "emmanuelg@infoexpo.com.mx"
//                            );

                            $lang = strtoupper($lang);

                            if ($post['tipoCorreo'] == "0") {
                                $result = $this->get('ixpo_mailer')->send_emailZips($section_text[$lang]["sas_bienvenidaED"], $exhibitor["Email"], $body, $lang, $rutaZip);
                            } else {
//                                $result = $this->get('ixpo_mailer')->send_emailDocs($section_text[$lang]["sas_asuntoGafetes"], $exhibitor["Email"], $body, $lang, $rutaZip);
                                $result = $this->get('ixpo_mailer')->send_emailZips($section_text[$lang]["sas_asuntoGafetes2022"], $exhibitor["Email"], $body, $lang, $rutaZip);
                            }


                            #$body = $this->renderView('EmpresaEmpresaBundle:Empresa:email_bienvenida.html.twig', array('content' => $content));
                            //$result = $ixpo_mailer->send_email($content["section_text"]["sas_bienvenidaED"] . ' ' . $edition_name, $exhibitor["Email"], $body, $lang);
                            /* Fin estructura envio de Email */

                            /* Funcionalidad log */
                            $cont_new = array(
                                'idEmpresa' => $post["idEmpresa"],
                                'DC_NombreComercial' => $exhibitor['DC_NombreComercial'],
                                'Email' => $exhibitor['Email'],
                                'Lang' => $lang
                            );
                            array_push($ContenidoNuevo, $cont_new);
                            $status = true;
                            /* Fin de la funcionalidad log */
                        }
                    }
                }
                /* Agregar la key 'ContenidoNuevo_' al arreglo $ContenidoNuevo de tipo json, funcionalidad log */
                $data_log['ContenidoNuevo_'] = json_encode($ContenidoNuevo, JSON_FORCE_OBJECT);
                /* Bandera para saber si los correos se hicieron bien para que proceda hacer el log */
                if ($status) {
                    $this->get("ixpo_log")->InsertLogSeguimiento($consulta, $data_log);
                }
            }
        }

        $result = array();
        $result['status'] = TRUE;
        $result['status_aux'] = TRUE;
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function updateFloorplanAction(Request $request) {
        $url = 'http://expoantad.infoexpo.com.mx/2018/ae/web/plano/refresh_json';

        if ($request->getMethod() == 'POST') {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(Array('ajax' => 'true')));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $remote_server_output = curl_exec($ch);
            curl_close($ch);

            return $this->jsonResponse(json_decode($remote_server_output));
        } else {
            die('No puede accesar a este link de esta manera');
        }
    }

    public function downloadLogosAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $headers = $request->headers->all();

            if ($headers["apitoken"][0] === "an78fgh3-050f-3e51-x2fz-sa21w354xxzx") {
                $source = "../../2020/ed/web/doc/ED/logos/";
                $destination = "logos-download.zip";
                if (extension_loaded('zip') === true) {
                    if (file_exists($source) === true) {
                        if (count(glob($source . '{*.jpg,*.png,*.jpeg}', GLOB_BRACE)) > 0 || count(glob($source, GLOB_ONLYDIR)) > 0) {
                            $zip = new \ZipArchive();
                            if ($zip->open($destination, \ZipArchive::CREATE) === true) {
                                $source = realpath($source);
                                if (is_dir($source) === true) {
                                    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);
                                    foreach ($files as $file) {
                                        $file = realpath($file);
                                        if (is_dir($file) === true) {
                                            //$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                                        } else if (is_file($file) === true) {
                                            $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                                        }
                                    }
                                } else if (is_file($source) === true) {
                                    $zip->addFromString(basename($source), file_get_contents($source));
                                }
                            }
                            $zip->close();
                            // Creamos las cabezeras que forzaran la descarga del archivo como archivo zip.
                            header("Content-type: application/zip");
                            header("Content-disposition: attachment;
                            filename = " . $destination);
                            $content['url'] = "https://expoantad.infoexpo.com.mx/sas/web/" . $destination;
                            $content['status'] = TRUE;
                            $content['name-file'] = $destination;
                            return $this->render('EmpresaEmpresaBundle:Empresa:download_file_zip.html.twig', array('content' => $content));
                        } else {
                            die("No existen archivos a comprimir");
                        }
                    } else {
                        die("No existe esta ruta");
                    }
                }
                return false;
            } else {
                header("HTTP/1.1 401 No autorizado");
                exit;
            }
        } else {
            $result = $request->getMethod() . ":Method Not Allowed ";
        }
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/zip');
        return $response;
    }

    public function existCompressLogAction(Request $request) {
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $files = '../web/' . $post['name']; //get all file names in array
            if (file_exists($files)) {
                unlink($files);
                $result = array("status" => TRUE);
            } else {
                $result = array("status" => FALSE);
                die("El fichero $files no existe");
            }
        }
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/zip');
        return $response;
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    protected function before($a, $inthat) {
        return substr($inthat, 0, strpos($inthat, $a));
    }

    public function createPDF($body, $lang, $aux, $digibadge) {
        /* generamos pdf para impresion */

        $medidas = array(140, 230);
        $pdf = $this->get("white_october.tcpdf")->create('vertical', 'mm', $medidas, true, 'UTF-8', false);

        $style = array(
            'border' => true,
            'padding' => 2,
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
        $pdf->SetMargins(-4, -5, -2);
        $pdf->SetAutoPageBreak(TRUE, 0);

        //agrega imagen

        $img_file = realpath('resources/Empresa/EmpresaBundle/imagenes') . '\Digibadge' . '.jpg';

        $pdf->SetXY(0, 0);
        $pdf->Image($img_file, '', '', 111, 263, 'jpg', '', 'T', false, 300, '', false, false, 1, false, false, false);


//        for ($i = 0; $i < count($aux); $i++) {
        $pdf->AddPage();
        $pdf->SetFillColor(0, 187, 45);
        $pdf->Rect(0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), 'DF', "");
        $content = array();
        $content['datosExp'] = $aux;

        //construccion de qr
        $id = $aux['idDetalleGafete'];
        $nombre = $aux['DGNombre'];
        $apellidos = $aux['DGApellidoPaterno'];
        $cargo = $aux['DescripcionES'];
        $empresa = $aux['DGEmpresa'];
        $qrGafete = 'EX22_' . $id . '|' . $nombre . '|' . $apellidos . '|' . $cargo . '|' . $empresa;
        //fin construccion de qr

        $nombrecompleto = $nombre . " " . $apellidos;

        $html = $this->renderView('EmpresaEmpresaBundle:Empresa:digibadge.html.twig', array('content' => $content));

        $pdf->writeHTML($html, false, false, false, false, '');

        $pdf->write2DBarcode($qrGafete, 'QRCODE,M', 34, 95, 70, 60, $style, 'N');
//            $pdf->write1DBarcode($aux[$i]['idEmpresa'], 'C128A', 33, 160, 88, 25, 0.9, $styleB, 'N'); //cuando lleva cargo

        $footer = $this->renderView('EmpresaEmpresaBundle:Empresa:footer.html.twig', array('content' => $content));
        $pdf->writeHTMLCell(0, 0, '', 170, $footer, '', 0, '', true, '', false);
//        }
        $pdf->lastPage();

        $pdf->Output(str_replace(" ", "", realpath('digibage') . "/" . $aux['idDetalleGafete'] . "_" . $nombrecompleto . '.pdf'), 'F');

        $pdf_txt = $pdf->Output(str_replace(" ", "", realpath('digibage') . "/" . $aux['idDetalleGafete'] . "_" . $nombrecompleto . '.pdf'), 'S');

        $base64 = base64_encode($pdf_txt);


        return ($base64);
    }

    public function createWallet($infoB) {
        for ($i = 0; $i < count($infoB); $i++) {
            //construccion de qr
            $id = $infoB[$i]['idDetalleGafete'];
            $nombre = $infoB[$i]['DGNombre'];
            $apellidos = $infoB[$i]['DGApellidoPaterno'];
            $cargo = $infoB[$i]['DescripcionES'];
            $empresa = $infoB[$i]['DGEmpresa'];
            $qrGafete = 'EX22_' . $id . '|' . $nombre . '|' . $apellidos . '|' . $cargo . '|' . $empresa;
            //fin construccion de qr
            
            $nombrecompleto = strtoupper($nombrecompleto);
            $infoB[$i]['DGEmpresa'] = strtoupper($infoB[$i]['DGEmpresa']);

            $nombrecompleto = $nombre . " " . $apellidos;

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
                            'label' => '',
                            // 'value' => 'ESTUDIANTE',
                            'value' => 'EXPOSITOR',
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
                            'label' => '',
                            'value' => '',
                            'textAlignment' => 'PKTextAlignmentLeft'
                        ],
                            [
                            'key' => 'orderNumber',
                            'label' => '',
                            'value' => $nombrecompleto,
                            'textAlignment' => 'PKTextAlignmentLeft'
                        ],
                            [
                            'key' => 'orderNumber',
                            'label' => '',
                            'value' => '',
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
                            'label' => '',
                            'value' => $infoB[$i]['DGEmpresa'],
                            'textAlignment' => 'PKTextAlignmentCenter'
                        ], [
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
                    'message' => $qrGafete,
                    'messageEncoding' => 'iso-8859-1',
                ],
                'backgroundColor' => 'rgb(0, 187, 45)',
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



            $pass->create(true, $infoB[$i]['idDetalleGafete']);
//        $pass->create(true);
// Create and output the pass
//            if (!) {
//                return False;
//            }
//            return True;
        }
    }

}
