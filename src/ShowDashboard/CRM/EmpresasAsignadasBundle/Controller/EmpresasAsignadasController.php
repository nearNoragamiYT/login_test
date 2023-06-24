<?php

namespace ShowDashboard\CRM\EmpresasAsignadasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\CRM\EmpresasAsignadasBundle\Model\EmpresasAsignadasModel;
use ShowDashboard\CRM\EmpresasAsignadasBundle\Model\EmpresasAsignadasCongfiguracion;

class EmpresasAsignadasController extends Controller {

    private $model, $text, $config;

    const seccion = 9, idTipoUsario1 = 3, idTipoUsario2 = 6;

    public function __construct() {
        $this->model = new EmpresasAsignadasModel();
        $this->text = new TextoModel();
        $this->config = new EmpresasAsignadasCongfiguracion();
    }

    public function mostrarAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;
        $idEvento = $session->get("idEvento");
        $idEdicion = $session->get("idEdicion");

        /* Obtenemos textos generales */
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Obtenemos textos de la seccion correspondiente */
        $section_text = $this->text->getTexts($lang, self::seccion);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* Comienza la logica propia del Action */
        #-- obtenemos los usuarios tipo vendedor --#
        $vendedores = $this->model->getVendedores(self::idTipoUsario1, self::idTipoUsario2);
        $args = Array("idEvento" => $idEvento, "idEdicion" => $idEdicion);
        #-- optenemos el listado de tipos de empresa para los filtros por select --#
        $empresasTipo = $this->model->getEmpresaTipo($args, $lang);
        #-- optenemos la configuracion de los filtros y la query --#
        $configuracion = $this->config->getEmpresasAsignadasCongfiguracion($vendedores, $empresasTipo, $content['section_text']);
        #-- obtenemos los filtros acomodados para la consulta --#
        $conditions = ($session->get("filters_query_empresas_asignadas") == null) ? "" : $session->get("filters_query_empresas_asignadas");
        #-- obtenemos los fitros en forma de array para su aplicacion por js --#
        $content['filters_empresas_asignadas'] = ($session->get("filters_post_empresas_asignadas") == null) ? "" : $session->get("filters_post_empresas_asignadas");
        #-- optenemos la configuración de la query para js --#
        $content['config'] = ($session->get("config_empresas_asignadas") == null ) ? $configuracion['config'] : $session->get("config_empresas_asignadas");
        #-- optenemos los campos que van a poder ser filtrados en js --#
        $content['fields'] = $configuracion['fields'];
        #-- si el usuario es un vendedor le agregamos a los filtros que solo le muestre sus empresas --#
        /* if ($user['idTipoUsuario'] == self::idTipoUsario) {
          $args['idUsuario'] = $user['idUsuario'];
          } */
        #-- obtenemos los datos por medio de la configuracion y los filtros propiamente preparados para la consulta --#
        $data = $this->model->getEmpresasAsignadas($args, $conditions, $content['config']);
        #-- en caso de que los datos lleven algo de html los preparamos u se los asignamos para que en js los tome como tal --#
        $content['empresas_asignadas'] = $this->agregarHTML($data, $vendedores, $section_text['data']);
        #-- obtenemos el total de los registros por medio de los filtros para ponerlo en el datatables --#
        $content['count'] = ($session->get("count_empresas_asignadas") == null) ? $this->model->getEmpresasAsignadasCount($args, $conditions) : $session->get("count_empresas_asignadas");
        #-- hacemos el breadcrumb para mostrarlo en el header de la pagina --#
        $content["breadcrumb"] = $this->model->breadcrumb($request->get("_route"), $lang);

        return $this->render('ShowDashboardCRMEmpresasAsignadasBundle:EmpresasAsignadas:mostrar.html.twig', Array("content" => $content));
    }

    public function aplicarFiltrosAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get("idEvento");
        $idEdicion = $session->get("idEdicion");
        /* Obtenemos textos de la seccion correspondiente */
        $section_text = $this->text->getTexts($lang, self::seccion);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        #-- obtenemos los usuarios tipo vendedor --#
        $vendedores = $this->model->getVendedores(self::idTipoUsario1, self::idTipoUsario2);
        #-- optenemos el listado de tipos de empresa para los filtros por select --#
        $args = Array("idEvento" => $idEvento, "idEdicion" => $idEdicion);
        $empresasTipo = $this->model->getEmpresaTipo($args, $lang);
        #-- optenemos la configuracion de los filtros y la query --#
        $configuracion = $this->config->getEmpresasAsignadasCongfiguracion($vendedores, $empresasTipo, $content['section_text']);
        $fields = $configuracion['fields'];
        $config = $configuracion['config'];
        $post = $request->request->all();
        $variable = $post['variable'];

        if (trim($variable) == "filters_post_empresas_asignadas") {
            $filters = $post['filters'];
            if ($filters["BeginDate"] != "" && $filters["EndDate"] != "")
                $filters["FechaPago"] = Array("BeginDate" => $filters["BeginDate"], "EndDate" => $filters["EndDate"]);
            unset($filters["BeginDate"]);
            unset($filters["EndDate"]);

            $conditions = Array();
            foreach ($filters as $key => $value) {
                if ($value != "") {
                    $type = $fields[$key]["data-type"];
                    switch ($type) {
                        case "text":
                            if (isset($config[$key])) {
                                $conditions[$key] = '(' . $config[$key] . '."' . $key . '" ILIKE ' . "'%" . $value . "%')";
                            } else {
                                $conditions[$key] = '("' . $key . '" LIKE ' . "'%" . $value . "%')";
                            }
                            break;
                        case "numeric":
                            if (isset($config[$key])) {
                                $conditions[$key] = '(' . $config[$key] . '."' . $key . '" = ' . $value . ")";
                            } else {
                                $conditions[$key] = '("' . $key . '" = ' . $value . ")";
                            }
                            break;
                        case "select":
                            if (is_numeric($value)) {
                                if (isset($config[$key])) {
                                    $conditions[$key] = '(' . $config[$key] . '."' . $key . '" = ' . $value . ")";
                                } else {
                                    $conditions[$key] = '("' . $key . '" = ' . $value . ")";
                                }
                            } else {
                                if (isset($config[$key])) {
                                    $conditions[$key] = '(' . $config[$key] . '."' . $key . '" IS ' . $value . ")";
                                } else {
                                    $conditions[$key] = '("' . $key . '" IS ' . $value . ")";
                                }
                            }
                            break;
                        case "date":
                            if (isset($config[$key])) {
                                $conditions[$key] = '(' . $config[$key] . '."' . $key . '" BETWEEN ' . "'" . $value["BeginDate"] . "' AND '" . $value["EndDate"] . "')";
                            } else {
                                $conditions[$key] = '("' . $key . '" BETWEEN ' . "'" . $value["BeginDate"] . "' AND '" . $value["EndDate"] . "')";
                            }
                            break;
                        default:
                            if (isset($config[$key])) {
                                $conditions[$key] = '(' . $config[$key] . '."' . $key . '" ILIKE ' . "'%" . $value . "%')";
                            } else {
                                $conditions[$key] = '("' . $key . '" LIKE ' . "'%" . $value . "%')";
                            }
                    }
                }
            }
            $config = ($session->get("config_empresas_asignadas") != null) ? $session->get("config_empresas_asignadas") : $configuracion['config'];
            $config['limit'] = $configuracion['config']['limit'];
            $config['offset'] = $configuracion['config']['offset'];
            $config['star_record'] = 0;
            $session->set("filters_empresas_asignadas", $config);
            #-- si el usuario es un vendedor le agregamos a los filtros que solo le muestre sus empresas --#
            /* if ($user['idTipoUsuario'] == self::idTipoUsario) {
              $args['idUsuario'] = $user['idUsuario'];
              } */
            #-- optenemos la configuracion de los filtros y la query --#
            $data = $this->model->getEmpresasAsignadas($args, $conditions, $config);
            $session->set("filters_query_empresas_asignadas", $conditions);
            $session->set($variable, $filters);
            $dataResponse = $this->agregarHTML($data, $vendedores, $section_text['data']);
            $result['status'] = TRUE;
            $result['data'] = $dataResponse;
            $count = $this->model->getEmpresasAsignadasCount($args, $conditions);
            $session->set("count_empresas_asignadas", $count);
            $result['count'] = $count;
        } else if (trim($variable) == "config_empresas_asignadas") {
            $config = $post['config'];
            $session_config = $session->get("config_empresas_asignadas");
            if ($config['get_data'] == 1) {
                $conditions = $session->get("filters_query_empresas_asignadas");
                #-- obtenemos los usuarios tipo vendedor --#
                $vendedores = $this->model->getVendedores(self::idTipoUsario);
                #-- si el usuario es un vendedor le agregamos a los filtros que solo le muestre sus empresas --#
                if ($user['idTipoUsuario'] == self::idTipoUsario) {
                    $args['idUsuario'] = $user['idUsuario'];
                }
                $data = $this->model->getEmpresasAsignadas($args, $conditions, $config);
                $dataResponse = $this->agregarHTML($data, $vendedores, $section_text['data']);
                $result['data'] = $dataResponse;
            }
            $session->set($variable, $config);
            $result['status'] = TRUE;
        }

        return new JsonResponse($result);
    }

    public function agregarAsesorAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        /* Obtenemos textos de la seccion correspondiente */
        $section_text = $this->text->getTexts($lang, self::seccion);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }

        $post = $request->request->all();
        $_response = Array("status" => true);
        if ($post['idOriginal'] == "-1") {
            $this->model->agregarAsesor($post);
        } else {
            $result = $this->model->validarEmpresaUsuario($post);
            if (!$result) {
                $this->model->cambiarAsesor($post);
            } else {
                $_response['status'] = !$result;
                $_response['msj'] = $section_text['data']['sas_asesorTieneEmpresa'];
            }
        }

        return new JsonResponse($_response);
    }

    public function agregarHTML($data, $vendedores, $text) {
        $result = Array();
        foreach ($data as $key => $value) {
            $result[$key]['idEmpresa'] = $value['idEmpresa'];
            $result[$key]['CodigoCliente'] = $value['CodigoCliente'];
            $result[$key]['DC_NombreComercial'] = $value['DC_NombreComercial'];
            $result[$key]['idEmpresaTipo'] = $value['idEmpresaTipo'];
            $result[$key]['idUsuario'] = $this->generateSelect($value['idEmpresa'], $value['idUsuario'], $vendedores, $text);
        }
        return $result;
    }

    public function generateSelect($idEmpresa, $idUsuario, $vendedores, $text) {
        if ($idUsuario == null) {
            $sinUsuario = '<option value="-1" selected="selected">' . $text['sas_sinAsesor'] . '</option>';
            $idUsuario = "-1";
        } else {
            $sinUsuario = '<option value="-1">' . $text['sas_sinAsesor'] . '</option>';
        }
        $option = '<select data-id="' . $idEmpresa . '" data-original="' . $idUsuario . '" class="browser-default change-user">';
        $option .= $sinUsuario;
        foreach ($vendedores as $id => $name) {
            $selected = ($id == $idUsuario) ? 'selected="selected"' : "";
            $option .= '<option value="' . $id . '" ' . $selected . '>' . $name . '</option>';
        }
        $option .= '</select>';
        return $option;
    }

}
