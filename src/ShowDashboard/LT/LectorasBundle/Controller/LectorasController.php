<?php

namespace ShowDashboard\LT\LectorasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Utilerias\TextoBundle\Model\TextoModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use ShowDashboard\LT\LectorasBundle\Model\LectorasConfiguration;
use ShowDashboard\LT\LectorasBundle\Model\LectorasModel;

class LectorasController extends Controller {

    protected $TextoModel, $LectorasModel, $LectorasConfiguration;

    public $url_ws_app = "https://expoantad.infoexpo.com.mx/2022/ws_app_scanner/web/api/";
    public $showCode = "ieantad2022";

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->LectorasConfiguration = new LectorasConfiguration();
        $this->LectorasModel = new LectorasModel();
    }

    const SECTION = 9;

    public function listaExpositoresAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $content = array();
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
        $session->set("companyOrigin", "lectoras");
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
        // $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        // if (!$breadcrumb) {
        //     $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
        //     return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        // }
        // $content["breadcrumb"] = $breadcrumb;
        $content['lang'] = $lang;
        #-- obtenemos las configuraciones para los filtros y el query --#
        $content["exhibitorsMetaData"] = $this->LectorasConfiguration->getExhibitorsMetaData($section_text['data']);
        $conditions = ($session->get('filters_query_exhibitors') == null) ? "" : $session->get('filters_query_exhibitors');
        $args = Array();
        $args = Array("idEvento" => $idEvento, "idEdicion" => $idEdicion);
        //if ($session->get('count_paydate') == null) {
        $count = $this->LectorasModel->getCountExhibtors($args, $conditions);
        $session->set("count_exhibitors", $count);
        //}
        $args = Array();
        $args = Array("idEvento" => $idEvento, "idEdicion" => $idEdicion);
        $content['exhibitors'] = $this->LectorasModel->getExhibitors($args, $conditions);
        $content["count"] = $count;
        $content['filters_post'] = ($session->get('filters_post_exhibitors') == null) ? "" : $session->get('filters_post_exhibitors');
        return $this->render('ShowDashboardLTLectorasBundle:Lectoras:lista_expositores.html.twig', array('content' => $content));
    }

    public function getToDataTableFilterAction(Request $request) {
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        /* Obtenemos textos de la seccion 4 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);

        $meta = $this->LectorasConfiguration->getExhibitorsMetaData($section_text['data']);
        $metaData = $meta['fields'];
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $filters = $post;
            $conditions = Array();
            foreach ($filters as $key => $value) {
                if ($value != "") {
                    $type = $metaData[$key]["data-type"];
                    switch ($type) {
                        case "text":
                            $conditions[$key] = '("' . $key . '" ILIKE ' . "'%" . $value . "%')";
                            break;
                        case "numeric":
                            $conditions[$key] = '("' . $key . '" = ' . $value . ")";
                            break;
                        case "select":
                            if ($value != 0)
                                $conditions[$key] = '("' . $key . '" = ' . $value . ")";
                            break;
                        case "date":
                            $conditions[$key] = '("' . $key . '" BETWEEN ' . "'" . $value["BeginDate"] . "' AND '" . $value["EndDate"] . "')";
                            break;
                        default:
                            break;
                    }
                }
            }
            $args = Array("idEvento" => $idEvento, "idEdicion" => $idEdicion);
            $data = $this->LectorasModel->getExhibitors($args, $conditions);
            $session->set("filters_query_exhibitors", $conditions);
            $session->set("filters_post_exhibitors", $post);
            $result['status'] = TRUE;
            $result['data'] = $data;
            $result['filters_post'] = $session->get('filters_post_exhibitors');
            $count = $this->LectorasModel->getCountExhibtors($args, $conditions);
            $session->set("count_exhibitors", $count);
            $result['count'] = $count;
        }else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function lectorasEmpresaAction(Request $request, $idEmpresa) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Obtenemos textos de la seccion 9 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* Verificamos si tiene permiso en el modulo seleccionado */
        // if ($session->get("companyOrigin") == "lectoras") {
        //     $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "lista_expositores");
        // }
        // if ($session->get("companyOrigin") == "solicitud_lectoras") {
        //     $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "solicitud_lectora_reporte");
        // }
        // if (!$breadcrumb) {
        //     $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
        //     return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        // }
        // $content["breadcrumb"] = $breadcrumb;
        /* Comienza la logica propia del Action */
        $content["idEmpresa"] = $idEmpresa;
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
        $args = Array();
        $args = Array('"idEmpresa"' => $idEmpresa, '"idEdicion"' => $idEdicion);
        $lectorasempresa = $this->LectorasModel->getLectorasEmpresa($args);
        $content["lectoras_empresa"] = $lectorasempresa;
        $lectoras_metadata = $this->LectorasConfiguration->getEmpresaScannersMetaData($content['section_text']);
        $content["lectoras_metadata"] = $lectoras_metadata;
        $types = $this->LectorasModel->getTypes($idEdicion);
        $content["types"] = $types;
        $args = Array('p."idEdicion"' => $idEdicion);
        $content["packages"] = $this->LectorasModel->getPackages($args);
        $args = Array('e."idEmpresa"' => $idEmpresa, 'ee."idEdicion"' => $idEdicion);
        $content["header"] = $this->LectorasModel->getCompanyHeader($args);
        //Obtnemos las lectoras Solicitadas
        $args = Array('"idEmpresa"' => $idEmpresa, '"idEdicion"' => $idEdicion, '"idForma"' => 401);
        $result_solicitud_detalle = $this->LectorasModel->getSolicitudLectoras($args);
        // $content['solicitud_lectoras'] = json_decode($result_solicitud_detalle["DetalleServicioJSON"], true);
        foreach ($result_solicitud_detalle as $key => $value) {
            $test[$result_solicitud_detalle[$key]['idServicio']] = Array(
                "Cantidad" => $result_solicitud_detalle[$key]['Cantidad']
            );
        }
        $content['solicitud_lectoras'] = $test;
        //Obtenemos los servicios
        /* Consultamos los servicios de la edicion */
        $args = Array('"idEdicion"' => $idEdicion, '"idForma"' => 401);
        $content['Services'] = $this->LectorasModel->getServicios($args);
        foreach ($content['solicitud_lectoras'] as $key => $value) {
            $content['solicitud_lectoras'][$key]["TituloES"] = $content['Services'][$key]["TituloES"];
            $content['solicitud_lectoras'][$key]["TituloEN"] = $content['Services'][$key]["TituloEN"];
        }
        // print_r($content['solicitud_lectoras']); die();
        //Obtenemos el correo principal de la empresa
        $args = Array("idEmpresa" => $idEmpresa, "idEdicion" => $idEdicion, "idEvento" => $idEvento, "idContacto" => $content["header"]["idContacto"]);
        $result_principal = $this->LectorasModel->getMailContactoPrincipal($args);
        $content["EmailEmpresa"] = $result_principal['Email'];
        /* Obtenemos el catalogo de tipos de Lectora */
        $args = Array('"idEdicion"' => $idEdicion, '"Puerta"' => "FAlSE");
        $lectorastipo = $this->LectorasModel->getScannerTipo($args);
        $content['lectoras_tipo'] = $lectorastipo;
        /* Obtenemos el catalogo de status de Lectora */
        $statusScanner = $this->LectorasModel->getStatusScanner();
        $content['status_scanner'] = $statusScanner;
        /* Obtenemos el token para consultas al ws-app */
        /* Link para generar token */
        $link_peticion = "starts/webs/sessions";
        $parametros_peticion = array(
            "idEmpresa" => $idEmpresa,
            "idEvento" => $idEvento,
            "idEdicion" => $idEdicion);
        $result_peticion = $this->peticionWebSercice($link_peticion, $parametros_peticion);
        if (!$result_peticion['status']) {
            $result['data'] = $result_peticion['tokenData']['token'];
        } else {
            $result['data'] = $result_peticion['msg'];
        }
        $session->set('app_token', $result['data']);
        array_push($content["breadcrumb"], Array("Modulo_" . strtoupper($lang) => $content["header"]["DC_NombreComercial"], "Ruta" => "", 'Permisos' => array()));
        $content['companyOrigin'] = $session->get("companyOrigin");
        // print_r($content); die();
        return $this->render('ShowDashboardLTLectorasBundle:Lectoras:empresa_lectoras.html.twig', array('content' => $content));
    }

    public function addEmpresaLectoraAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
        /* ObtenciÃƒÂ³n de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* ObtenciÃƒÂ³n de textos de la secciÃƒÂ³n */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        //Consultamos los tipo de lectoras para verificar si existe el tipo App Ixpo
        $args = Array('"idEdicion"' => $idEdicion, '"Puerta"' => "FAlSE");
        $lectorastipo = $this->LectorasModel->getScannerTipo($args);
        //Consultamos la EmpresaScanner para obtener sus datos
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            if ($post['Cortesia']) {
                $post['Cortesia'] = "TRUE";
            } else {
                $post['Cortesia'] = "FALSE";
            }
            //Verificamos si el Scanner es de tipo AppIxpo para hacer la peticion al ws_app
            if ($lectorastipo[$post['idScannerTipo']]['AppIxpo']) {
                //Tomamos el token de la session
                $token = $session->get('app_token');
                //Link para crear nueva licencia
                $link_peticion = "news/users";
                $parametros_peticion = array(
                    "idEmpresa" => $post["idEmpresa"],
                    "idEvento" => $idEvento,
                    "idEdicion" => $idEdicion,
                    "nlicencias" => 1,
                    "cortesia" => "'" . $post['Cortesia'] . "'",
                    "etiquetasApp" => Array($post['EtiquetaApp']),
                    "token" => $token,
                    "idScannerTipo" => $post["idScannerTipo"]);
                $result_peticion = $this->peticionWebSercice($link_peticion, $parametros_peticion);
                if (!$result_peticion['status']) {
                    $post["idEmpresaScanner"] = $result_peticion['licencias'][0]['idEmpresaScanner'];
                    $post['CodigoScanner'] = $result_peticion['licencias'][0]['licencia'];
                    $result['status'] = TRUE;
                    $result['data'] = $post;
                    $result['message'] = $general_text['data']['sas_guardoExito'];
                } else {
                    $result['data'] = $result_peticion['msg'];
                }
            } else {
                //Verificamos que la lectora exista, si no existe la creamos, si existe solo actualizamos el tipo
                $args = array();
                $args = array("CodigoScanner" => "'" . $post['CodigoScanner'] . "'");
                $result_lectoras = $this->LectorasModel->getLectoras($args);
                if ($result_lectoras['ScannerActivo'] != TRUE) {
                    if (count($result_lectoras) == 0) {
                        $data = Array(
                            'CodigoScanner' => "'" . $post['CodigoScanner'] . "'",
                            'idScannerTipo' => "'" . $post['idScannerTipo'] . "'",
                            'idEvento' => "'" . $idEvento . "'",
                            'idEdicion' => "'" . $idEdicion . "'",
                        );
                        $result_lectora = $this->LectorasModel->insertLectora($data);
                    } else {
                        $args = array("idScanner" => "'" . $result_lectoras['idScanner'] . "'");
                        $data = Array(
                            'idEvento' => "'" . $idEvento . "'",
                            'idEdicion' => "'" . $idEdicion . "'",
                            'idScannerTipo' => "'" . $post['idScannerTipo'] . "'",
                            'CodigoScanner' => "'" . $post['CodigoScanner'] . "'",
                            'ScannerActivo' => "'" . TRUE . "'",
                        );
                        $result_lectora = $this->LectorasModel->UpdateLectora($data, $args);
                    }
                    if ($result_lectora['status']) {
                        $data = Array(
                            'idEmpresa' => "'" . $post['idEmpresa'] . "'",
                            'idEvento' => "'" . $idEvento . "'",
                            'idEdicion' => "'" . $idEdicion . "'",
                            'idScanner' => $result_lectora['data'][0]['idScanner'],
                            'idStatusScanner' => $post['idStatusScanner'],
                            'Cortesia' => $post['Cortesia'],
                            "EtiquetaApp" => "'" . $post['EtiquetaApp'] . "'",
                        );
                        $result_empresa_lectora = $this->LectorasModel->insertEmpresaScanner($data);
                        $post["idEmpresaScanner"] = $result_empresa_lectora["data"] [0]["idEmpresaScanner"];
                        if ($result_empresa_lectora['status']) {
                            $result['status'] = TRUE;
                            $result['data'] = $post;
                            $result['message'] = $general_text['data']['sas_guardoExito'];
                        } else {
                            $result['data'] = $section_text['data']['sas_errorAsignarLectora'];
                        }
                    } else {
                        $result['data'] = $general_text['data']['sas_errorGuardarLectora'];
                    }
                } else {
                    $result['data'] = $section_text['data']['sas_lectoraYaAsignada'];
                }
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function updateEmpresaLectoraAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
        /* ObtenciÃƒÂ³n de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* ObtenciÃƒÂ³n de textos de la secciÃƒÂ³n */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            //Consultamos los tipo de lectoras para verificar si existe el tipo App Ixpo
            $args = Array();
            $args = Array('"idEdicion"' => $idEdicion, '"Puerta"' => "FAlSE");
            $lectorastipo = $this->LectorasModel->getScannerTipo($args);
            //Consultamos la EmpresaScanner para obtener sus datos
            $args = array();
            $args = array("idEmpresaScanner" => "'" . $post["idEmpresaScanner"] . "'");
            $result_empresa_lectoras = $this->LectorasModel->getLectorasEmpresa($args);
            $codigo_lectora = $result_empresa_lectoras[$post["idEmpresaScanner"]]['CodigoScanner'];
            $idScanner = $result_empresa_lectoras[$post["idEmpresaScanner"]]['idScanner'];
            $idStatusAsignacion = $result_empresa_lectoras[$post["idEmpresaScanner"]]['idStatusAsignacion'];
            if ($result_empresa_lectoras[$post["idEmpresaScanner"]]['EstadoDisponibilidad']) {
                $estado_disponibilidad = "TRUE";
            } else {
                $estado_disponibilidad = "FALSE";
            }
            //verificamos el tipo de licencia
            if ($result_empresa_lectoras[$post["idEmpresaScanner"]]['AppIxpo'] && !$lectorastipo[$post['idScannerTipo']]['AppIxpo']) {
                if ($result_empresa_lectoras[$post["idEmpresaScanner"]]['idStatusAsignacion'] == 2) {
                    $result['data'] = "No se puede editar el tipo de la lectora, por que ya fue usada en un dispositivo";
                    return $this->jsonResponse($result);
                }
            }
            //Verificamos que se este actualizando a una App Ixpo para generar la licencia
            if ($lectorastipo[$post['idScannerTipo']]['AppIxpo'] && !$lectorastipo[$result_empresa_lectoras[$post["idEmpresaScanner"]]['idScannerTipo']]['AppIxpo']) {
                $result['data'] = "No se puede modificar la lectora por una licencia";
                return $this->jsonResponse($result);
            }
            //Consultamos si el scanner ya esta asignado
            $args = array();
            $args = array("CodigoScanner" => "'" . $post['CodigoScanner'] . "'");
            $result_lectoras = $this->LectorasModel->getLectorasEmpresa($args);
            if (count($result_lectoras) == 0 || $codigo_lectora == $post['CodigoScanner']) {
                if ($post['Cortesia']) {
                    $post['Cortesia'] = "TRUE";
                } else {
                    $post['Cortesia'] = "FALSE";
                }
                //Buscamos la Lectora para verificar si el scanner existe
                $args = array();
                $args = array("CodigoScanner" => "'" . $post['CodigoScanner'] . "'");
                $result_lectora = $this->LectorasModel->getLectoras($args);
                if (count($result_lectora) > 0 && $codigo_lectora != $post['CodigoScanner']) {
                    $where_lectora = array("idScanner" => "'" . $result_lectora['idScanner'] . "'");
                    if ($where_lectora["idScanner"] != $idScanner) {
                        $data = Array(
                            'ScannerActivo' => "FALSE",
                        );
                        $args = array("idScanner" => $idScanner);
                        $result_update_lectora = $this->LectorasModel->updateLectora($data, $args);
                        if (!$result_update_lectora['status']) {
                            $result['data'] = "Error al actualizar la primera lectora";
                            return $this->jsonResponse($result);
                        }
                    }
                } else {
                    $where_lectora = array("idScanner" => "'" . $idScanner . "'");
                }
            } else {
                $result['data'] = $section_text['data']['sas_lectoraYaAsignada'];
                return $this->jsonResponse($result);
            }
            $data = Array(
                'idEvento' => "'" . $idEvento . "'",
                'idEdicion' => "'" . $idEdicion . "'",
                'idScannerTipo' => "'" . $post['idScannerTipo'] . "'",
                'CodigoScanner' => "'" . $post['CodigoScanner'] . "'",
                'ScannerActivo' => "'" . TRUE . "'",
            );
            $result_update_lectora = $this->LectorasModel->updateLectora($data, $where_lectora);
            if ($result_update_lectora['status']) {
                //Actualizamos el EmpresaScanner :D
                //Verificamos que el Scanner no sea una licencia de AppIxpo
                if ($lectorastipo[$post['idScannerTipo']]['AppIxpo']) {
                    $data = Array(
                        'idEmpresa' => "'" . $post['idEmpresa'] . "'",
                        'idEvento' => "'" . $idEvento . "'",
                        'idEdicion' => "'" . $idEdicion . "'",
                        'idScanner' => $where_lectora['idScanner'],
                        'idStatusScanner' => $post['idStatusScanner'],
                        'BanderaPuerta' => "FALSE",
                        'Cortesia' => $post['Cortesia'],
                        'EtiquetaApp' => "'" . $post['EtiquetaApp'] . "'",
                        'idStatusAsignacion' => $idStatusAsignacion,
                        'EmailEmpresa' => "'" . $post['EmailEmpresa'] . "'",
                        'EstadoDisponibilidad' => $estado_disponibilidad
                    );
                } else {
                    $data = Array(
                        'idEmpresa' => "'" . $post['idEmpresa'] . "'",
                        'idEvento' => "'" . $idEvento . "'",
                        'idEdicion' => "'" . $idEdicion . "'",
                        'idScanner' => $where_lectora['idScanner'],
                        'idStatusScanner' => $post['idStatusScanner'],
                        'BanderaPuerta' => "FALSE",
                        'Cortesia' => $post['Cortesia'],
                        "EtiquetaApp" => "'" . $post['EtiquetaApp'] . "'",
                        'EstadoActivacion' => 'NULL',
                        'EstadoDisponibilidad' => 'NULL',
                        'idStatusAsignacion' => 0,
                        'EmailEmpresa' => 'NULL',
                        'UID' => 'NULL'
                    );
                }
                $args = Array("idEmpresaScanner" => $post["idEmpresaScanner"]);
                $resul_update_empresa_scanner = $this->LectorasModel->updateEmpresaScanner($data, $args);
                if ($resul_update_empresa_scanner['status']) {
                    $result['status'] = TRUE;
                    $result['data'] = $post;
                    $result['message'] = $general_text['data']['sas_guardoExito'];
                } else {
                    $result['data'] = "Error al actualizar la lectora de la empresa";
                }
            } else {
                $result['data'] = "Error al actualizar la lectora para asignar";
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function deleteEmpresaLectoraAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        /* ObtenciÃƒÂ³n de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* ObtenciÃƒÂ³n de textos de la secciÃƒÂ³n */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            //Consultamos la empresalectora para obtener sus datos
            $args = array();
            $args = array("idEmpresaScanner" => "'" . $post["idEmpresaScanner"] . "'");
            $result_empresa_lectoras = $this->LectorasModel->getLectorasEmpresa($args);
            if ($result_empresa_lectoras[$post["idEmpresaScanner"]]['idStatusAsignacion'] == 2) {
                $result['data'] = "No se puede eliminar la lectora, por que ya fue usada en un dispositivo";
                return $this->jsonResponse($result);
            }
            //Actualizamos los El campo ScannerActivo
            $args = array();
            $args = array("idScanner" => "'" . $result_empresa_lectoras[$post["idEmpresaScanner"]]['idScanner'] . "'");
            $data = Array(
                'ScannerActivo' => "FALSE"
            );
            $result_lectora = $this->LectorasModel->UpdateLectora($data, $args);
            if ($result_lectora['status']) {
                $result = $this->LectorasModel->deleteEmpresaScanner(array("idEmpresaScanner" => "'" . $post["idEmpresaScanner"] . "'"));
                if ($result['status']) {
                    $result['status'] = TRUE;
                    $result['data'] = $post;
                } else {
                    $result['data'] = $general_text['data']['sas_errorPeticion'];
                }
            } else {
                $result['data'] = $general_text['data']['sas_errorGuardarLectora'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function lectorasEmpresaReporteAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Obtenemos textos de la seccion 9 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content["breadcrumb"] = $breadcrumb;
        /* Comienza la logica propia del Action */
        $idEdicion = $session->get('idEdicion');
        $args = Array();
        $args = Array('"idEdicion"' => $idEdicion);
        $lectorasempresa = $this->LectorasModel->getLectorasEmpresaReporte($args);
        $content["empresa_lectoras_reporte"] = $lectorasempresa;
        $lectoras_metadata = $this->LectorasConfiguration->getEmpresaScannersReporteMetaData($content['section_text']);
        $content["empresa_lectoras_reporte_metadata"] = $lectoras_metadata;
        if ($status == "descargar") {
            $metadata = array();
            foreach ($lectoras_metadata as $key => $value) {
                $metadata[$key] = $value['text'];
            }
            $charset = 'UTF-8';
            $name = iconv($charset, 'ASCII//TRANSLIT', $session->get('edicion')["Edicion_ES"]);
            $event_name = preg_replace("/[^A-Za-z0-9 ]/", '', $name);
            $file_name = str_replace(" ", "_", $event_name) . "_Lectoras_" . date('d-m-Y G.i');
            return $this->excelReport($lectorasempresa, $metadata, $file_name);
        }

        return $this->render('ShowDashboardLTLectorasBundle:Lectoras:empresa_lectoras_reporte.html.twig', array('content' => $content));
    }

    public function solicitudLectorasAction(Request $request, $idEmpresa) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Obtenemos textos de la seccion 9 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        if ($session->get("companyOrigin") == "solicitud_lectoras") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "solicitud_lectora_reporte");
        }
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content["breadcrumb"] = $breadcrumb;
        /* Comienza la logica propia del Action */
        $content["idEmpresa"] = $idEmpresa;
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
        $args = Array('p."idEdicion"' => $idEdicion);
        $content["packages"] = $this->LectorasModel->getPackages($args);
        $args = Array('e."idEmpresa"' => $idEmpresa, 'ee."idEdicion"' => $idEdicion);
        $content["header"] = $this->LectorasModel->getCompanyHeader($args);
        /* Consultamos los servicios de la edicion */
        $args = Array('"idEdicion"' => $idEdicion, '"idForma"' => 401);
        $content['Services'] = $this->LectorasModel->getServicios($args);
        /* Detalle de la forma de Lectoras */
        $args = Array('"idEmpresa"' => $idEmpresa, '"idEdicion"' => $idEdicion, '"idForma"' => 401);
        $result_solicitud_detalle = $this->LectorasModel->getSolicitudLectoras($args);
        if (count($result_solicitud_detalle) == 0) {
            $data = array();
            $data = Array('idEmpresa' => $idEmpresa,
                'idForma' => 401,
                'idEvento' => $idEvento,
                'idEdicion' => $idEdicion,
                'Lang' => "'" . $lang . "'");
            $empresaForma = $this->LectorasModel->generateEMFO($data);
            if (!$empresaForma['status']) {
                throw new \Exception($empresaForma['data'], 409);
            }
            $args = Array('"idEmpresa"' => $idEmpresa, '"idEdicion"' => $idEdicion, '"idForma"' => 401);
            $result_solicitud_detalle = $this->LectorasModel->getSolicitudLectoras($args);
        }
        $content['solicitud_lectoras'] = json_decode($result_solicitud_detalle["DetalleServicioJSON"], true);
        $detalle_pago = json_decode($result_solicitud_detalle['DetallePagoJSON'], true);
        $content['DetallePago'] = $detalle_pago;
        $content['contactoPago'] = $this->LectorasModel->getContactoPago($session->get('idEdicion'), $idEmpresa, $detalle_pago['idContacto']);
        $content['SolicitudLectora'] = $result_solicitud_detalle;
        /* $content['StatusPagoForma'] = $result_solicitud_detalle['StatusPago'];
          $content['idFormaPago'] = $result_solicitud_detalle['idFormaPago'];
          $content['FechaActualizacionStatusPago'] = $result_solicitud_detalle['FechaActualizacionStatusPago'];
          $content['lang_forma'] = $result_solicitud_detalle['Lang']; */
        /* Entidades Fiscales de la Empresa */
        $result_entidad_fiscal = $this->LectorasModel->getEmpresaEntidadFiscal(Array('idEmpresa' => $idEmpresa));
        if (!$result_entidad_fiscal['status']) {
            throw new \Exception($result_entidad_fiscal['data'], 409);
        }
        $entidades = Array();
        foreach ($result_entidad_fiscal['data'] as $key => $value) {
            $entidades[$value['idEmpresaEntidadFiscal']] = $value;
        }
        $content['entidad_fiscal'] = $entidades[$detalle_pago['idEmpresaEntidadFiscal']];
        /* Consultamos el catalogo de status de pago */
        $result_status_pago = $this->LectorasModel->getStatusPago();
        if (!$result_status_pago['status']) {
            throw new \Exception($result_status_pago['data'], 409);
        }
        $statuspago = Array();
        foreach ($result_status_pago['data'] as $key => $value) {
            $statuspago[$value['idStatusPago']] = $value;
        }
        $content['StatusPago'] = $statuspago;
        /* Consultamos el catalogo de formas de pago */
        $result_forma_pago = $this->LectorasModel->getFormaPago();
        if (!$result_forma_pago['status']) {
            throw new \Exception($result_forma_pago['data'], 409);
        }
        $formapago = Array();
        foreach ($result_forma_pago['data'] as $key => $value) {
            $formapago[$value['idFormaPago']] = $value;
        }
        $content['FormaPago'] = $formapago;
        array_push($content["breadcrumb"], Array("Modulo_" . strtoupper($lang) => $content["header"]["DC_NombreComercial"], "Ruta" => "", 'Permisos' => array()));
        $content['companyOrigin'] = $session->get("companyOrigin");
        return $this->render('ShowDashboardLTLectorasBundle:Lectoras:solicitud_lectoras.html.twig', array('content' => $content));
    }

    public function updateStatusPagoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Comienza la logica propia del Action */
        $idEdicion = $session->get('idEdicion');
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $result = $this->LectorasModel->updateStatusPago(array("StatusPago" => $post['status_pago']), array("idEmpresa" => $post['idEmpresa'], "idForma" => 401));
            if ($result['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            } else {
                $result['data'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function updateFormaPagoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Comienza la logica propia del Action */
        $idEdicion = $session->get('idEdicion');

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $result = $this->LectorasModel->updateFormaPago(array("idFormaPago" => $post['forma_pago']), array("idEmpresa" => $post['idEmpresa'], "idForma" => 401, "idEdicion" => $idEdicion));
            if ($result['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            } else {
                $result['data'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function updateFechaPagoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Comienza la logica propia del Action */
        $idEdicion = $session->get('idEdicion');

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $result = $this->LectorasModel->updateFechaPago(array("FechaActualizacionStatusPago" => "'" . $post['fecha_pago'] . "'"), array("idEmpresa" => $post['idEmpresa'], "idForma" => 401, "idEdicion" => $idEdicion));
            if ($result['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            } else {
                $result['data'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function updateDetallePagoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Comienza la logica propia del Action */
        $idEdicion = $session->get('idEdicion');

        if ($request->getMethod() == 'POST') {
            $args = $post = $request->request->all();
            unset($args['idEmpresa']);
            $temp = strpos($args['HistorialPagoAcumulado'], $args['PagoAcumulado']);
            if ($temp === false) {
                if ($args['HistorialPagoAcumulado'] != '') {
                    $args['HistorialPagoAcumulado'] = $args['HistorialPagoAcumulado'] . ',' . $args['PagoAcumulado'];
                } else {
                    $args['HistorialPagoAcumulado'] = $args['PagoAcumulado'];
                }
            }

            $result = $this->LectorasModel->updateDetallePago(array("DetallePagoJSON" => "'" . json_encode($args) . "'"), array("idEmpresa" => $post['idEmpresa'], "idForma" => 401, "idEdicion" => $idEdicion));
            if ($result['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            } else {
                $result['data'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function solicitudLectorasReporteAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;
        $session->set("companyOrigin", "solicitud_lectoras");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Obtenemos textos de la seccion 9 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* Verificamos si tiene permiso en el modulo seleccionado */
        // $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "solicitud_lectora_reporte");
        // if (!$breadcrumb) {
        //     $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
        //     return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        // }
        // $content["breadcrumb"] = $breadcrumb;
        /* Comienza la logica propia del Action */
        $idEdicion = $session->get('idEdicion');
        $args = Array();
        $args = Array('"idEdicion"' => $idEdicion);
        $solicitudlectoras = $this->LectorasModel->getSoliciudLectorasReporte($args);
        /* Calculo de Rentas en Sito */
        foreach ($solicitudlectoras as $key => $value) {
            $resta_lectoras = $value['LectorasSolicitadas'] + $value['SustitucionEquipo'] + $value['NumeroLectorasCortesia'];
            if ($value['RentasSitio'] > 0 && ($value['RentasSitio'] - $resta_lectoras) > 0) {
                $solicitudlectoras[$key]['RentasSitio'] = $value['RentasSitio'] - $resta_lectoras;
            } else {
                $solicitudlectoras[$key]['RentasSitio'] = 0;
            }
        }
        $content["solicitud_lectoras_reporte"] = $solicitudlectoras;
        $lectoras_metadata = $this->LectorasConfiguration->getColumnSLDefs($section_text, $lang, $idEdicion);
        $content["solicitu_lectoras_reporte_metadata"] = $lectoras_metadata;

        if ($status == "descargar") {
            $metadata = array();
            foreach ($lectoras_metadata as $key => $value) {
                if ($value['help-lb'] != "") {
                    $metadata[$key] = $value['help-lb'];
                } else {
                    $metadata[$key] = $value['text'];
                }
            }
            $charset = 'UTF-8';
            $name = iconv($charset, 'ASCII//TRANSLIT', $session->get('edicion')["Edicion_ES"]);
            $event_name = preg_replace("/[^A-Za-z0-9 ]/", '', $name);
            $file_name = str_replace(" ", "_", $event_name) . "_SolicitudLectoras$file_" . date('d-m-Y G.i');
            return $this->excelReport($solicitudlectoras, $metadata, $file_name);
        }
        unset($content["solicitu_lectoras_reporte_metadata"]['Email']);
        return $this->render('ShowDashboardLTLectorasBundle:Lectoras:solicitud_lectoras_reporte.html.twig', array('content' => $content));
    }

    public function excelReport($general, $table_metadata, $filename) {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("Infoexpo")
                ->setTitle($filename)
                ->setSubject($filename)
                ->setDescription($filename);
        $flag = 1;
        $lastColumn = "A";
        foreach ($table_metadata as $value) {
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

    public function reportesLectorasListAction(Request $request) {
        $session = $request->getSession();
        $modulosUsuario = $session->get('modulos_usuario');
        $lang = $session->get('lang');
        $content = array();
        $edicion = $session->get('edicion');
        $session->set("companyOrigin", "lectoras_simple");
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
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content["breadcrumb"] = $breadcrumb;
        return $this->render('ShowDashboardLTLectorasBundle:Lectoras:reportes_lectoras.html.twig', array('content' => $content));
    }

    public function reportSeguimientoLectorasAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $charset = 'UTF-8';
        $name = iconv($charset, 'ASCII//TRANSLIT', $session->get('edicion')["Edicion_ES"]);
        $event_name = preg_replace("/[^A-Za-z0-9 ]/", '', $name);
        $file_name = str_replace(" ", "_", $event_name) . "_Seguimiento_Lectoras_" . date('d-m-Y G.i');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];
        $status_result = $this->LectorasModel->getStatusPago();
        if (COUNT($status_result) > 0) {
            foreach ($status_result['data'] as $value) {
                $status[$value['idStatusPago']] = $value;
            }
        }
        $types = $this->LectorasModel->getServicios(array("idEdicion" => $idEdicion));
        $data = $this->LectorasModel->getReporteSeguimiento(array("idEdicion" => $idEdicion));
        foreach ($data as $key => $value) {
            $formData = json_decode($value["Detalle_Servicio"], true);

            $cantidad = 0;
            $total = 0;
            $stringCantidades = "";
            $nofactura = $data[$key]["NoFactura"];
            unset($data[$key]["NoFactura"]);
            foreach ($formData as $keyForm => $valueForm) {
                $cantidad = $cantidad + $formData[$keyForm]["Cantidad"];
                $total = $total + $formData[$keyForm]["Total"];
                $stringCantidades .= $types[$keyForm]["TituloES"] . ": " . $formData[$keyForm]["Cantidad"] . "    ";
            }
            $data[$key]["CantidadTipo"] = $stringCantidades;
            $data[$key]["Cantidad"] = $cantidad;
            $data[$key]["Total"] = $total;
            unset($data[$key]["Detalle_Servicio"]);

            $data[$key]["Tipo"] = $data[$key]["TipoLectora"];
            $data[$key]["Status"] = $status[$data[$key]["StatusPago"]]["StatusPagoES"];
            unset($data[$key]["TipoLectora"]);
            unset($data[$key]["StatusPago"]);
            $data[$key]["NoFactura"] = $nofactura;
        }
        $meta_data = array(
            $text['sas_idEmpresa'],
            $text['sas_codigoCliente'],
            $text['sas_nombreComercial'],
            $text['sas_listadoStands'],
            "Contacto Contratación",
            "Email Contratación",
            "Telefono Contratación",
            "Contacto Forma",
            "Email Forma",
            "Telefono Forma",
            "Celular Forma",
            "Cantidad por Tipo",
            "Cantidad Total",
            "Costo Total",
            "Tipos Solicitados",
            "Status de Solicitud",
            "Número de Factura",
            "Status TMK "
        );
        return $this->excelReport($data, $meta_data, $file_name);
    }

    public function getLicenciasAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
        /* ObtenciÃƒÂ³n de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* ObtenciÃƒÂ³n de textos de la secciÃƒÂ³n */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            //Tomamos el token de la session
            $token = $session->get('app_token');
            //Link para solicitar licencias
            $link_peticion = "shows/licences";
            $parametros_peticion = array(
                "idEmpresa" => $post["idEmpresa"],
                "idEvento" => $idEvento,
                "idEdicion" => $idEdicion,
                "token" => $token);
            $result_peticion = $this->peticionWebSercice($link_peticion, $parametros_peticion);
            if (!$result_peticion['status']) {
                $result['status'] = TRUE;
                $result['data'] = $result_peticion;
            } else {
                $result['data'] = $result_peticion['msg'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function getQrAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        /* ObtenciÃƒÂ³n de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* ObtenciÃƒÂ³n de textos de la secciÃƒÂ³n */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            //Tomamos el token de la session
            $token = $session->get('app_token');
            //Link para solicitar licencias
            $link_peticion = "criptos";
            $parametros_peticion = array(
                "licence" => $post["licencia"],
                "token" => $token);
            $result_peticion = $this->peticionWebSercice($link_peticion, $parametros_peticion);
            if (!$result_peticion['status']) {
                $result['status'] = TRUE;
                $result['data'] = $result_peticion;
            } else {
                $result['data'] = $result_peticion['msg'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function sendEmailLicenciasAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
        $content = array();
        $content['lang'] = $lang;
        $content['showCode'] = $this->showCode;
        // ------    Obtenemos los textos generales    ------ //
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* ---  Obtenemos los textos del modulo  --- */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* ---  si la edicion se perdiÃ³ lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            //print_r($post);die("X_X");
            //Obtenemos la informacion de la empresa para el mail
            $args = Array();
            $args = Array('e."idEmpresa"' => $post["idEmpresa"], 'ee."idEdicion"' => $idEdicion, 'ee."idEvento"' => $idEvento);
            $content["empresa"] = $this->LectorasModel->getCompanyHeader($args);
            //obtenemos qr de las licencias
            $licencias = $post['licencias'];
            foreach ($licencias as $key => $value) {
                $licencias[$key]['qr'] = "";
                $link_qr = "criptos";
                $parametros_qr = array("licence" => $value['textoLicencia']);
                $result_peticion = $this->peticionWebSercice($link_qr, $parametros_qr);
                $licenciab = (isset($result_peticion['qrText'])) ? str_replace("\\", "", $result_peticion['qrText']) : null;
                $licencia = (isset($licenciab)) ? str_replace(" ", "+", $licenciab) : null;
                $licencias[$key]['qr'] = $licencia;
            }
            $content["licencias"] = $licencias;
            /* Verificamos si se enviara al mail opcional o al principal de la empresa */
            //$mail_principal = "emmanuelg@infoexpo.com.mx";
            if ($post['mail'] !== "") {
                $mail_principal = $post['mail'];
            } else {
                //$mail_principal = "ricardog.infoexpo@gmail.com";
                $mail_principal = $post['EmailEmpresa'];
            }
            /* $copias = "desarrollo@ixpo.mx"; */
            /* Comenzamos proceso de envio de mail */
            $body = $this->renderView('ShowDashboardLTLectorasBundle:Licencias:mail_licencias.html.twig', array('content' => $content));
            $ixpo_mailer = $this->get('ixpo_mailer');
            //$ixpo_mailer->setBCC($copias);
            $result = $this->get('ixpo_mailer')->send_email("Licencias para App Scanner Infoexpo", $mail_principal, $body, $lang);
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

    public function liberacionLicenciasAction(Request $request) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');
        /* ObtenciÃƒÂ³n de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* ObtenciÃƒÂ³n de textos de la secciÃƒÂ³n */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            //Tomamos el token de la session
            $token = $session->get('app_token');
            //Link para solicitar licencias
            $link_peticion = "panics/logouts";
            $parametros_peticion = array(
                "idEmpresa" => $post["idEmpresa"],
                "idScanner" => $post["licencia"]['idScanner'],
                "user" => $user['Nombre'],
                "idEmpresaScanner" => $post["licencia"]['idEmpresaScanner'],
                "token" => $token);
            $result_peticion = $this->peticionWebSercice($link_peticion, $parametros_peticion);
            if ($result_peticion['status'] != 'error') {
                $result['status'] = TRUE;
                $result['data'] = $result_peticion;
            } else {
                $result['data'] = $result_peticion['msg'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function actualizacionFechaExpiracionAction(Request $request) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');
        /* ObtenciÃƒÂ³n de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* ObtenciÃƒÂ³n de textos de la secciÃƒÂ³n */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            //Tomamos el token de la session
            $token = $session->get('app_token');
            //Link para solicitar licencias
            $link_peticion = "updates/expiracions";
            $parametros_peticion = array(
                "idEmpresa" => $post["idEmpresa"],
                "idScanner" => $post["idScanner"],
                "user" => $user['Nombre'],
                "expiracion" => $post['newDate'],
                "token" => $token);
            $result_peticion = $this->peticionWebSercice($link_peticion, $parametros_peticion);
            if ($result_peticion['status'] != 'error') {
                $result['status'] = TRUE;
                $result['data'] = $result_peticion;
            } else {
                $result['data'] = $result_peticion['msg'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function peticionWebSercice($link_peticion, $parametros_peticion) {
        //Iniciamos proceso peticion a través del ws de la app
        $url_peticion = $this->url_ws_app . $link_peticion;
        //Inicializar el --Curl--
        $ch = curl_init($url_peticion);
        //Parametros para obtener la licencia
        $parametros = json_encode($parametros_peticion);
        //opciones del CURL
        $opt = array(
            // Activamos el metodo POST para el Curl
            CURLOPT_POST => true,
            //Enviamos los parametros por post
            CURLOPT_POSTFIELDS => array("jsonx" => $parametros),
            //Maximo Tiempo de Espera de Respuesta del WS
            CURLOPT_CONNECTTIMEOUT => 30,
            //Activamos la respuesta del Curl
            CURLOPT_RETURNTRANSFER => TRUE,
            //Permitimoas las redirecciones en el server
            CURLOP_FOLLOWLOCATION => TRUE
        );
        //Aplicamos las opciones al CURL
        curl_setopt_array($ch, $opt);
        //Ejecutamos la peticion
        $result_curl = curl_exec($ch);
        /* Verificamos que no exista un error en la ejecucion del CURL */
        //Obtenemos el codigo
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            $result['status'] = TRUE;
            $result['msg'] = "Error " . $httpCode . ": " . curl_error($ch);
        } else {
            $result = json_decode($result_curl, true);
        }
        //Ceramos el Curl
        curl_close($ch);
        return $result;
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
