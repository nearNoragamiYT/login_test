<?php

namespace Empresa\VentasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Utilerias\TextoBundle\Model\TextoModel;
use Empresa\VentasBundle\Model\VentasConfiguration;
use Empresa\VentasBundle\Model\VentasModel;

class VentasController extends Controller {

    protected $TextoModel, $EmpresaModel, $configuracion;

    const SECTION = 4;
    const MAIN_ROUTE = "empresa_ventas";

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->EmpresaModel = new VentasModel();
        $this->configuracion = new VentasConfiguration();
    }

    public function companiesAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set("companyOrigin", "ventas");
        $content = array();

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

        /* Obtenemos los Tipos de Empresa */
        $where = ' WHERE "idEdicion" = ' . $idEdicion;
        $resultEmpresaTipo = $this->EmpresaModel->getEmpresaTipo($lang, $where);
        $content['empresaTipo'] = $resultEmpresaTipo;

        $args = Array('c."Principal"' => "true", 'e."idEvento"' => $idEvento);
        $content['breadcrumb'] = $this->EmpresaModel->breadcrumb(self::MAIN_ROUTE, $lang);

        $content['routeName'] = $request->get('_route');
        $content['App'] = $App;
        $content['user'] = $user;
        $content['lang'] = $lang;

        $content["Empresa_table_column_categories"] = $this->configuracion->getColumnCategories($section_text);
        $content["Empresa_table_columns"] = $this->configuracion->getColumnDefs($section_text, $lang, $idEdicion);
        $session->set('columns_ventas', $content["Empresa_table_columns"]);

        return $this->render('EmpresaVentasBundle:Ventas:lista_companies.html.twig', array('content' => $content));
    }

    public function getToDataTableAction(Request $request) {
        $session = $request->getSession();
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->remove('seting-dt_ventas');

        if ($request->getMethod() != 'POST') {
            throw new \Exception("No allowed method", 409);
        }

        $post = $request->request->all();

        if ($user['idTipoUsuario'] == 6)
            $params["where"][] = Array("name" => '"idUsuario"', "operator" => "=", "value" => $user['idUsuario'], "type" => \PDO::PARAM_STR);
        else
            $params = Array();
        $result_count = $this->EmpresaModel->getCountEmpresa($idEdicion, Array(), $params);

        if (!$result_count["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting all records"));
        } else {
            $session->set('qry_count_ventas', $result_count["count"]);
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

            if ($user['idTipoUsuario'] == 6)
                $params["where"][] = Array("name" => '"idUsuario"', "operator" => "=", "value" => $user['idUsuario'], "type" => \PDO::PARAM_STR, "clause" => "AND");
            $result_filtered_count = $this->EmpresaModel->getCountEmpresa($idEdicion, $columns, $params);
            if (!$result_filtered_count["status"]) {
                return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting filtered records"));
            } else {
                $session->set('count_filtered_ventas', $result_filtered_count["count"]);
            }
            $records_filtered = $result_filtered_count["data"][0]["total"];
        } else {
            $session->remove('count_filtered_ventas');
            if ($user['idTipoUsuario'] == 6)
                $params["where"][] = Array("name" => '"idUsuario"', "operator" => "=", "value" => $user['idUsuario'], "type" => \PDO::PARAM_STR);
            else
                $params = Array();
        }

        $order = $this->buildOrderColumns($post["order"], $raw_columns, $lang, $idEdicion);
        $result_query = $this->EmpresaModel->getEmpresaCustom($columns, $params, $order, $post["length"], $post["start"], $idEdicion);
        if (!$result_query["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error getting records"));
        } else {
            $session->set('data_qry_ventas', $result_query["data_qry"]);
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
        $edicion_empresas = $session->get("edicion_ventas");

        if ($idEdicion != $edicion_empresas) {
            $session->remove('seting-dt_ventas');
            $session->remove('qry_count_ventas');
            $session->remove('count_filtered_ventas');
            $session->remove('data_qry_ventas');
        }
        $session->set("edicion_ventas", $idEdicion);

        if ($request->getMethod() != 'POST') {
            throw new \Exception("No allowed method", 409);
        }

        $post = $request->request->all();
        if ($session->has('qry_count_ventas')) {
            $qry_count = $session->get('qry_count_ventas');
            $result_count = $this->EmpresaModel->getCountEmpresa($idEdicion, Array(), $qry_count["params"], $qry_count["qry"]);
        } else {
            if ($user['idTipoUsuario'] == 6)
                $params["where"][] = Array("name" => '"idUsuario"', "operator" => "=", "value" => $user['idUsuario'], "type" => \PDO::PARAM_STR);
            else
                $params = Array();
            $result_count = $this->EmpresaModel->getCountEmpresa($idEdicion, Array(), $params);
        }
        if (!$result_count["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting all records"));
        } else {
            $session->set('qry_count_ventas', $result_count["count"]);
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

        if ($session->has("count_filtered_ventas")) {
            $count_filtered = $session->get('count_filtered_ventas');
            $params = $count_filtered["params"];
        }

        if (array_key_exists("where", $params) && COUNT($params["where"]) > 0) {
            if ($session->has("count_filtered_ventas")) {
                $count_filtered = $session->get('count_filtered_ventas');
                $result_filtered_count = $this->EmpresaModel->getCountEmpresa($idEdicion, $columns, $count_filtered["params"], $count_filtered["qry"]);
            } else {
                if ($user['idTipoUsuario'] == 6)
                    $params["where"][] = Array("name" => '"idUsuario"', "operator" => "=", "value" => $user['idUsuario'], "type" => \PDO::PARAM_STR, "clause" => "AND");
                $result_filtered_count = $this->EmpresaModel->getCountEmpresa($idEdicion, $columns, $params);
            }
            if (!$result_filtered_count["status"]) {
                return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting filtered records"));
            } else {
                $session->set('count_filtered_ventas', $result_filtered_count["count"]);
            }
            $records_filtered = $result_filtered_count["data"][0]["total"];
        } else {
            if ($user['idTipoUsuario'] == 6)
                $params["where"][] = Array("name" => '"idUsuario"', "operator" => "=", "value" => $user['idUsuario'], "type" => \PDO::PARAM_STR);
            else
                $params = Array();
        }

        $order = $this->buildOrderColumns($post["order"], $raw_columns, $lang, $idEdicion);

        if ($session->has("data_qry_ventas")) {
            $data_qry = $session->get('data_qry_ventas');
            $result_query = $this->EmpresaModel->getEmpresaCustom($columns, $data_qry["params"], $order, $post["length"], $post["start"], $idEdicion);
        } else {
            $result_query = $this->EmpresaModel->getEmpresaCustom($columns, $params, $order, $post["length"], $post["start"], $idEdicion);
        }
        if (!$result_query["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error getting records"));
        } else {
            $session->set('data_qry_ventas', $result_query["data_qry"]);
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
        $file_name = str_replace(" ", "_", $App["Cliente_" . $lang]) . "_log_Data";

        $post = $request->request->all();
        $post_data = json_decode(str_replace('\"', '"', $post["post_data"]), TRUE);

        $result_build = $this->buildParamsAndColumnFromDTColumns($post_data["columns"], $lang, $idEdicion);
        $params = Array("where" => $result_build["params"]);

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
        $result_query = $this->LogModel->getLogCustom($columns, $params, $order);
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
        $session->set('seting-dt_ventas', $post);
        return $this->jsonResponse($post);
    }

    public function getSessionDataAction(Request $request) {
        $session = $request->getSession();
        $data = $session->get('seting-dt_ventas');
        $data = array("seting" => $session->get('seting-dt_ventas'), "param" => $session->get('data_qry_ventas')["params"]["where"], "columns" => $session->get('columns_ventas'));
        return $this->jsonResponse($data);
    }

    public function addCompanyAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        /* Obtención de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* Obtención de textos de la sección */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        /* Obtenemos los paises del PECC */
        $result_paises = $this->get('pecc')->getPaises($lang);
        if (!$result_paises['status']) {
            throw new \Exception($result_paises['data'], 409);
        }

        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');

        $paises = $result_paises['data'];
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            if (isset($paises[$post['DC_idPais']])) {
                $post['DC_Pais'] = $paises[$post['DC_idPais']]['Pais_' . strtoupper($lang)];
            }

            $post["idEvento"] = $idEvento;
            $post["idEdicion"] = $idEdicion;
            $result_empresa = $this->EmpresaModel->getEmpresa(Array('DC_NombreComercial' => "'" . $post['DC_NombreComercial'] . "'"));
            if (count($result_empresa['data'])) {
                $result['status'] = FALSE;
                $result['data'] = $general_text['data']['sas_empresaNombreYaExiste'];
                return $this->jsonResponse($result);
            }

            $result = $this->EmpresaModel->addCompany($post);

            if ($result['status']) {
                $post['idEmpresa'] = $result['data'][0]['fn_sas_AgregarEmpresa'];
                $result['status_aux'] = TRUE;
                $result['status'] = TRUE;
                $result['data'] = $post;
                $result['message'] = $general_text['data']['sas_guardoExito'];
                $this->EmpresaModel->assignUser($user['idUsuario'], /* $user['idTipoUsuario'], */ $post['idEmpresa']);
            } else {
                $result['error'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function deleteCompanyAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $args = Array("idEmpresa" => $post["idEmpresa"]);
            $result = $this->EmpresaModel->deleteCompany($args);
            if ($result['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            } else {
                $result['error'] = $content['general_text']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
