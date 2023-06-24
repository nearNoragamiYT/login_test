<?php

namespace Visitante\PrensaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Utilerias\TextoBundle\Model\TextoModel;
use Visitante\PrensaBundle\Model\PrensaModel;
use Visitante\PrensaBundle\Model\PrensaConfiguration;
use PKPass\PKPass;

class PrensaController extends Controller {

    protected $TextoModel, $PrensaModel, $configuracion, $PrensaConfiguration;

    const TEMPLATE = 9;
    const MAIN_ROUTE = "prensa";

    public function __construct() {
        $this->TextoModel = new TextoModel();
//        $this->configuracion = new VisitanteConfiguration();
        $this->PrensaModel = new PrensaModel();
        $this->PrensaConfiguration = new PrensaConfiguration();
    }

    public function prensaAction(Request $request) {
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
        $section_text = $this->PrensaModel->getTexts($lang, self::TEMPLATE);
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

        $result_statusPrensa = $this->PrensaModel->getStatusAutorizacion();
        if (!$result_statusPrensa['status']) {
            throw new \Exception($result_statusPrensa['data'], 409);
        }
        $content['StatusPrensa'] = $result_statusPrensa['data'];

        $content['routeName'] = $request->get('_route');
        $content['App'] = $App;
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get('idEdicion');
        $content["Prensa_table_columns"] = $this->PrensaConfiguration->getVisitantesFilters();
        $content['currentRoute'] = $request->get('_route');
        $content['tabPermission'] = json_decode($this->PrensaModel->tabsPermission($user), true);
        return $this->render('VisitantePrensaBundle:Prensa:lista_prensa.html.twig', array('content' => $content));
    }
    public function getDataTableAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set("edicion_asoc", $idEdicion);
        $text = $this->PrensaModel->getTexts($lang, self::TEMPLATE);

        if ($request->getMethod() != 'POST') {
            throw new \Exception("No allowed method", 409);
        }

        $post = $request->request->all();

        $where = $this->setWhere($post);
        $session->set("where_asoc", $where);
        $order = $this->setOrder($post);
        $session->set("order_asoc", $where);
        $result_count = $this->PrensaModel->getCount('', $idEdicion);
        if (!$result_count['status']) {
            throw new \Exception($result_count['data'], 409);
        }
        $result_count = $result_count['data'];
        $result_list = $this->PrensaModel->getListidVisitantes('', $idEdicion);
        $result_count_filter = array();
        if ($where != '') {
            $where = 'WHERE ' . $where;
            $result_list = $this->PrensaModel->getListidVisitantes($where, $idEdicion);
            $result_count_filter = $this->PrensaModel->getCount($where, $idEdicion);
            if (!$result_count_filter['status']) {
                throw new \Exception($result_count_filter['data'], 409);
            }
            $result_count_filter = $result_count_filter['data'];
        }
        if (empty($result_count_filter))
            $result_count_filter[0]['count'] = $result_count[0]['count'];

        $result_query = $this->PrensaModel->getVisitantes($where, $order, $post, $idEdicion);
        if (!$result_query['status']) {
            throw new \Exception($result_query['data'], 409);
        }
        $result_query = $result_query['data'];

        $data = array();
        foreach ($result_query as $key => $value) {
            $numeroEnvios = $value['NumeroEnvios'];
            $numeroDescargas = $value['NumeroDescargas'];

            unset($value['NumeroEnvios']);
            unset($value['NumeroDescargas']);

            $flag = "";
            $idVisitante = $value['idVisitante'];
            $idVisitanteNoAutorizado = $value['idVisitanteNoAutorizado'];
            if ($idVisitante == "" && $idVisitanteNoAutorizado != "") {
                $flag = "0-" . $idVisitanteNoAutorizado;
            } else if ($idVisitanteNoAutorizado == "" && $idVisitante != "") {
                $flag = $idVisitante . "-0";
            } else {
                $flag = $idVisitante . "-" . $idVisitanteNoAutorizado;
            }
            $disabled = "";
            if ($value['idStatus'] != 1) {
                $disabled = "disabled";
                unset($result_list['data'][$flag]);
            }
            $idStatus = $value['idStatus'];

            unset($value['idStatus']);
            $data[$key][] = '<input type="checkbox" id="e-' . $flag . '" value="' . $flag . '" class="prensa-check" ' . $disabled . '/>
                     <label for="e-' . $flag . '"></label>';

            foreach ($value as $colum) {
                $data[$key][] = (string) $colum;
            }
            if ($idVisitante != "") {
                if ($idStatus == 2) {
                    if ($numeroEnvios > 0) {
                        if ($numeroDescargas > 0) {
                            $data[$key][] = '<i style="color: red important;" class="material-icons edit-record tooltipped 1" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Editar">mode_edit</i>';
                            $data[$key][] = '<i class="material-icons send-record tooltipped blue-text" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Enviar gafete">email</i>'
                                    . '<br>' . '<i class="material-icons download-gafete tooltipped blue-text" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Descargar gafete">file_download</i>';
                        } else {
                            $data[$key][] = '<i style="color: red important;" class="material-icons edit-record tooltipped 2" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Editar">mode_edit</i>';
                            $data[$key][] = '<i class="material-icons send-record tooltipped blue-text" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Enviar gafete">email</i>'
                                    . '<br>' . '<i class="material-icons download-gafete tooltipped" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Descargar gafete">file_download</i>';
                        }
                    } else if($numeroDescargas > 0) {
                        $data[$key][] = '<i style="color: red important;" class="material-icons edit-record tooltipped 3" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Editar">mode_edit</i>';
                        $data[$key][] = '<i class="material-icons send-record tooltipped" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Enviar gafete">email</i>'
                                . '<br>' . '<i class="material-icons download-gafete tooltipped blue-text" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Descargar gafete">file_download</i>';
                    }else{
                         $data[$key][] = '<i style="color: red important;" class="material-icons edit-record tooltipped 3" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Editar">mode_edit</i>';
                        $data[$key][] = '<i class="material-icons send-record tooltipped" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Enviar gafete">email</i>'
                                . '<br>' . '<i class="material-icons download-gafete tooltipped" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Descargar gafete">file_download</i>';
                    }
                } else {
                    $data[$key][] = "";
                    $data[$key][] = "";
                }
            } else {
                $data[$key][] = "";
                $data[$key][] = "";
                $data[$key][] = "";
            }
        }
        $response_dt = Array(
            "draw" => $post["draw"],
            "recordsTotal" => $result_count[0]['count'],
            "recordsFiltered" => $result_count_filter[0]['count'],
            "data" => $data,
            "listDataId" => $result_list['data']
        );
        return $this->jsonResponse($response_dt);
    }


    public function exportDataTableAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set("edicion_asoc", $idEdicion);

        $text = $this->PrensaModel->getTexts($lang, self::TEMPLATE);


        $where = $session->get("where_asoc");
        $order = $session->get("order_asoc");

        $result_query = $this->PrensaModel->getVisitantesExport($where, $order, $idEdicion);
        if (!$result_query['status']) {
            throw new \Exception($result_query['data'], 409);
        }
        $result_query = $result_query['data'];


        $meta_data = array(
            "ID",
            "Nombre Completo",
            "Email",
            "Nombre Comercial",
            "Cargo",
            "Fecha Preregistro",
            "Nombre Estatus",
            "Numero Gafetes Enviados",
            "Numero Gafetes Descargados",
        );
        $data = $result_query;
        $fileName = str_replace(" ", "_", $session->get('edicion')["Edicion_ES"]) . "_Asociados_" . date('d-m-Y G.i');
        $file_name = str_replace("é", "e", $fileName);
        return $this->excelReport($data, $meta_data, $file_name);
    }

    public function showPrensaAction(Request $request, $idVisitante) {

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
        $content["breadcrumb"] = $this->PrensaModel->breadcrumb($session->get('OriginView'), $lang);
        $content['idEdicion'] = $session->get('idEdicion');
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];


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
        $content["idModuloIxpo"] = $breadcrumb[0]['idModuloIxpo'];

        /* Obtenemos datos del Visitante */
        $result_visitante = $this->PrensaModel->getVisitante($content);
        if (!$result_visitante['status']) {
            throw new \Exception($result_visitante['data'], 409);
        }
        $content['visitante'] = $result_visitante['data']['0'];

        /* Obtenemos los prensaInvitados */
        $args = array();
        $args = array("idVisitante" => $idVisitante);
        $result_prensa = $this->PrensaModel->getPrensa($args);
        if (!$result_prensa['status']) {
            throw new \Exception($result_prensa['data'], 409);
        }
        $content['prensa'] = $result_prensa['data'];
        /* obtenemos las configuraciones para la tabla */
        $result_statusPrensa = $this->PrensaModel->getStatusAutorizacion();
        if (!$result_statusPrensa['status']) {
            throw new \Exception($result_statusPrensa['data'], 409);
        }
        $content['StatusPrensa'] = $result_statusPrensa['data'];
        array_push($content["breadcrumb"], Array("Modulo_" . strtoupper($lang) => $content['visitante']['NombreCompleto'], "Ruta" => "", 'Permisos' => array()));
        return $this->render('VisitantePrensaBundle:Prensa:prensa.html.twig', array('content' => $content));
    }

    public function updateStatusPrensaAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $result_update = $this->PrensaModel->updateStatusPrensa($post);

            if ($result_update['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            } else {
                $result['status'] = FALSE;
                $result['data'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function updateStatusPrensaListAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
        $ModuloIxpo = $this->PrensaModel->breadcrumb($session->get('OriginView'), $lang);
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $post['idEvento'] = $idEvento;
            $post['idEdicion'] = $idEdicion;
            $post = json_encode($post, JSON_FORCE_OBJECT);
            $result_update = $this->PrensaModel->updateStatusPrensaList($post);

            if ($result_update['status']) {
                /* Insert log */
                $post = json_decode($post, JSON_FORCE_OBJECT);
                if ($post['idStatus'] == 2) {
                    $result_sync = $this->syncTo($result_update['data'], $idEdicion);
                    $post['Sync'] = $result_sync;
                }
                $post['Modulo'] = $ModuloIxpo['0']['breadcrumb'];
                $this->get("ixpo_log")->InsertLogSeguimiento($this->PrensaModel->SQLModel->PGModel->getQuery(), $post);
                $result['status'] = TRUE;
                $result['data'] = $result_update['data'];
            } else {
                $result['status'] = FALSE;
                $result['data'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function syncTo($data, $idEdicion) {
        $idsVisitante = array();
        foreach ($data as $value) {
            array_push($idsVisitante, $value['idvisitante']);
        }
        $fields = Array(
            'Data' => "'" . json_encode($idsVisitante, JSON_FORCE_OBJECT) . "'",
            'idEdicion' => $idEdicion,
            'Tipo' => "'comprador'"
        );
        $result = $this->PrensaModel->setVisitanteDataWS($fields);

        return $result;
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function setWhere($param) {
        $filtros = $this->PrensaConfiguration->getVisitantesFilters();
        $where = '';
        foreach ($param['columns'] as $value) {
            if ($value['search']['value'] != '') {
                if ($where != '') {
                    $where .= ' AND ';
                }
                if ($filtros[$value['name']]['filter_options']['search_operator'] == 'ilike') {
                    $where .= $filtros[$value['name']]['field'] . ' ILIKE \'%' . $value['search']['value'] . '%\'';
                } else if ($filtros[$value['name']]['filter_options']['type'] == "date") {
                    $where .= 'DATE(' . $filtros[$value['name']]['field'] . ')' . $filtros[$value['name']]['filter_options']['search_operator'] . "'" . $value['search']['value'] . "'";
                } else {
                    $where .= $filtros[$value['name']]['field'] . $filtros[$value['name']]['filter_options']['search_operator'] . $value['search']['value'];
                }
            }
        }
//        if ($where != '') {
//            $where .= ' AND ';
//        }

        return $where;
    }

    public function setOrder($param) {
        $filtros = $this->PrensaConfiguration->getVisitantesFilters();
        $order = '';
        foreach ($param['order'] as $value) {
            $flag = $param['columns'][$value['column']]['name'];
            $order .= $filtros[$flag]['field'] . ' ' . $value['dir'] . ',';
        }
        $order = substr($order, 0, -1);
        return $order;
    }

    public function emailRechazoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');

        $post = $request->request->all();

        $result_visitante = $this->PrensaModel->getVisitante($post);
        if (!$result_visitante['status']) {
            throw new \Exception($result_visitante['data'], 409);
        }
        $content['visitante'] = $result_visitante['data']['0'];

        /* Obtenemos textos generales del AE */
        $result_general_text = $this->PrensaModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        $result_template_text = $this->PrensaModel->getTexts($lang, 21);
        if (!$result_template_text['status']) {
            throw new \Exception($result_template_text['data'], 409);
        }
        $template_text = $result_template_text['data'];

        $content['general_text'] = $general_text;
        $content['template_text'] = $template_text;

        $content['edicion'] = $session->get('edicion');
        $content['lang'] = $lang;

        $email = $content['visitante']['Email'];

        /* Estructura envÃ­o de email */
        $body = $this->renderView('VisitantePrensaBundle:Prensa:rechazo_email.html.twig', array('content' => $content));
        $res = $this->get('ixpo_mailer')->send_email($content['edicion']['Edicion_ES'], $email, $body);
        /* Fin estructura envio de Email */

        $response = new Response(json_encode(array('status' => TRUE, 'data' => 'Éxito')));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function emailConfirmacionAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $visitante = $profile->getData();
        $edicion = $session->get('edicion');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');


        $post = $request->request->all();
        $idVisitante = $post['idVisitante'];
        $result_visitantei = $this->PrensaModel->getVisitante($post);
        if (!$result_visitantei['status']) {
            throw new \Exception($result_visitantei['data'], 409);
        }
        $content['visitante'] = $result_visitantei['data']['0'];

        /* Traemos el visitante */
        $result_visitante = $this->PrensaModel->getVisitanteDG($idVisitante);
        if (!$result_visitante['status']) {
            throw new \Exception($result_visitante['data'], 409);
        }
        $content['datosVis'] = $result_visitante['data'][0];
        /* Obtenemos textos generales del AE */
        $result_general_text = $this->PrensaModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        $result_template_text = $this->PrensaModel->getTexts($lang, 21);
        if (!$result_template_text['status']) {
            throw new \Exception($result_template_text['data'], 409);
        }
        $template_text = $result_template_text['data'];

        $content['general_text'] = $general_text;
        $content['template_text'] = $template_text;
        $content['lang'] = $lang;
        $content['edicion'] = $session->get('edicion');
        $email = $content['visitante']['Email'];
        $content['visitante']['idVisitante'] = $this->PrensaModel->completeID($content['visitante']['idVisitante']);

        /* Estructura envÃ­o de email */
        $bodyEmail = $this->renderView('VisitantePrensaBundle:Prensa:comprobante_email.html.twig', array('content' => $content));
        $datosVis = $result_visitante['data'];
        $digibadge = 'ANTAD DIGIBADGE';
        $body = '';

        /* Estructura pdf */
        $result_pdf = $this->createPDF($body, $lang, $datosVis, $digibadge);
        $file_digi = str_replace(" ", "", 'digibage/' . $datosVis[0]['idVisitante'] . '.pdf');

        ////////////////////////////creacion del wallet ///////////////////////////
        //$crWallet = $this->createWallet($datosVis);
        //$file_wall = str_replace(" ", "", 'wallet/' . $datosVis[0]['idVisitante'] . '.pkpass');
        ///////////////////////////////////////////////////////////////////////////

        $docs = array();
        $docs[] = $file_digi;
        //$docs[] = $file_wall;
        $result = $this->get('ixpo_mailer')->send_emailDocs($content['edicion']['Edicion_ES'], $email, $bodyEmail, 'es', $file_digi);

        $accion = 2;
        $result_visitante = $this->PrensaModel->updateDowloadLog($idEvento, $idEdicion, $idVisitante, $accion);

        /* Fin estructura envio de Email */

        $response = new Response(json_encode(array('status' => TRUE, 'data' => 'Éxito')));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
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

    public function sendDigibageAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $visitante = $profile->getData();
        $edicion = $session->get('edicion');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $post = $request->request->all();
        $idVisitante = $post['idVisitante'];
        /* Obtenemos textos generales del AE */
        $result_general_text = $this->PrensaModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];
        $result_template_text = $this->PrensaModel->getTexts($lang, 21);
        if (!$result_template_text['status']) {
            throw new \Exception($result_template_text['data'], 409);
        }
        $template_text = $result_template_text['data'];

        $content['general_text'] = $general_text;
        $content['template_text'] = $template_text;
        /* Traemos el visitante */
        $result_visitante = $this->PrensaModel->getVisitanteDG($idVisitante);
        if (!$result_visitante['status']) {
            throw new \Exception($result_visitante['data'], 409);
        }

        $content = array();
        $content['general_text'] = $general_text;
        $content['template_text'] = $template_text;
        $content['edicion'] = $edicion;
        $content['visitante'] = $result_visitante['data'][0];

        $bodyEmail = $this->renderView('VisitantePrensaBundle:Prensa:email.html.twig', array('content' => $content));

        $datosVis = $result_visitante['data'];
        $digibadge = 'ANTAD DIGIBADGE';
        $body = '';

        /* Estructura pdf */
        $result_pdf = $this->createPDF($body, $lang, $datosVis, $digibadge);
        $file_digi = str_replace(" ", "", 'digibage/' . $datosVis[0]['idVisitante'] . '.pdf');

        ////////////////////////////creacion del wallet ///////////////////////////
        //$crWallet = $this->createWallet($datosVis);
        //$file_wall = str_replace(" ", "", 'wallet/' . $datosVis[0]['idVisitante'] . '.pkpass');
        ///////////////////////////////////////////////////////////////////////////

        $docs = array();
        $docs[] = $file_digi;
        //$docs[] = $file_wall;
        $result = $this->get('ixpo_mailer')->send_emailDocs($content['edicion']['Edicion_ES'], $datosVis[0]['Email'], $bodyEmail, 'es', $file_digi);
        
        $accion = 2;
        $result_visitante = $this->PrensaModel->updateDowloadLog($idEvento, $idEdicion, $idVisitante, $accion);
        /* Fin estructura envio de Email */

        $result = array('status' => TRUE, 'data' => NULL);
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function createPDF($body, $lang, $aux, $digibadge) {
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
        $pdf->SetAuthor('AE_DIGIBADGE');
        $pdf->SetTitle($aux[0]['NombreEvento']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $font_size = $pdf->pixelsToUnits('27');
        $pdf->SetFont('helvetica', '', $font_size, '', 'default', true);
        $pdf->SetMargins(-4, -6, -3, true);
        $pdf->SetAutoPageBreak(TRUE, 0);

        for ($i = 0; $i < count($aux); $i++) {
            $pdf->AddPage();
            $pdf->SetFillColor(215, 185, 235);
            $pdf->Rect(0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), 'DF', "");
            $content = array();
            $content['datosVis'] = $aux[$i];

            //construccion de qr
            $id = $aux[$i]['idVisitante'];
            $nombre = $aux[$i]['Nombre'];
            $apellidos = $aux[$i]['ApellidoPaterno'] . ' ' . $aux[$i]['ApellidoMaterno'];
            $cargo = $aux[$i]['DE_Cargo'];
            $empresa = $aux[$i]['DE_RazonSocial'];
            $qrGafete = 'PR22_' . $id . '|' . $nombre . '|' . $apellidos . '|' . $cargo . '|' . $empresa;
            //fin construccion de qr

            $html = $this->renderView('VisitantePrensaBundle:Prensa:Digibage.html.twig', array('content' => $content));
            $pdf->writeHTML($html, false, false, false, false, '');
            $pdf->write2DBarcode($qrGafete, 'QRCODE,M', 34, 95, 70, 60, $style, 'N');
//            $pdf->write1DBarcode($aux[$i]['idVisitante'], 'C128A', 33, 160, 88, 25, 0.9, $styleB, 'N'); //cuando lleva cargo
            $footer = $this->renderView('VisitantePrensaBundle:Prensa:footer.html.twig', array('content' => $content));
            $pdf->writeHTMLCell(0, 0, '', 170, $footer, '', 0, '', true, '', false);
        }
        $pdf->lastPage();

        $pdf->Output(str_replace(" ", "", realpath('digibage') . "/" . $aux[0]['idVisitante'] . '.pdf'), 'F');
        $pdf_txt = $pdf->Output(str_replace(" ", "", realpath('digibage') . "/" . $aux[0]['idVisitante'] . '.pdf'), 'S');
        $base64 = base64_encode($pdf_txt);

        return ($base64);
    }

    public function createWallet($infoB) {
        for ($i = 0; $i < count($infoB); $i++) {
            //construccion de qr
            $id = $infoB[$i]['idVisitante'];
            $nombre = $infoB[$i]['Nombre'];
            $apellidos = $infoB[$i]['ApellidoPaterno'] . ' ' . $infoB[$i]['ApellidoMaterno'];
            $cargo = $infoB[$i]['DE_Cargo'];
            $empresa = $infoB[$i]['DE_RazonSocial'];
            $qrGafete = 'PR' . $id . '|' . $nombre . '|' . $apellidos . '|' . $cargo . '|' . $empresa;
            //fin construccion de qr
            $infoB[$i]['VisitanteTipoES'] = strtoupper($infoB[$i]['VisitanteTipoES']);
             $infoB[$i]['NombreCompleto'] = strtoupper($infoB[$i]['NombreCompleto']);
             $infoB[$i]['DE_RazonSocial'] = strtoupper($infoB[$i]['DE_RazonSocial']);
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
                            'value' => 'PRENSA',
                            //'value' => $infoB[$i]['VisitanteTipoES'],
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
                            'value' => $infoB[$i]['NombreCompleto'],
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
                            'value' => $infoB[$i]['DE_RazonSocial'],
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
                'backgroundColor' => 'rgb(215, 185, 235)',
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
    public function downloadDigibadgeAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        /* Obtenemos textos generales del AE */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $idVisitante = $post['idVisitante'];
            $result_visitante = $this->PrensaModel->getVisitanteDG($idVisitante);
            if (!$result_visitante['status']) {
                throw new \Exception($result_visitante['data'], 409);
            }

            $DE_RazonSocial = $result_visitante["data"][0]["DE_RazonSocial"];
            $decode = htmlspecialchars_decode($DE_RazonSocial, ENT_HTML5);
            $result_visitante["data"][0]["DE_RazonSocial"] = $decode;

            $content['datosVis'] = $result_visitante['data'];
            $content['template_text'] = $template_text;
            $body = "";
            $datosVis = $result_visitante['data'];
            $lang = 'ES';
            $qr = 'S15D69F88D4';
            $digibadge = 'AntadDigibage';
            $result_pdf = $this->createPDFDownload($body, $lang, $datosVis, $digibadge);
            $accion = 1;
            $result_visitante = $this->PrensaModel->updateDowloadLog($idEvento, $idEdicion, $idVisitante, $accion);
        }
        $respuesta = new Response();
        $respuesta->setContent($result_pdf);
        $respuesta->headers->set('Content-Type', 'application/pdf');
        return $respuesta;
    }

    public function createPDFDownload($body, $lang, $aux, $digibadge) {
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
        $pdf->SetAuthor('AE_DIGIBADGE');
        $pdf->SetTitle($aux[0]['NombreEvento']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $font_size = $pdf->pixelsToUnits('27');
        $pdf->SetFont('helvetica', '', $font_size, '', 'default', true);
        $pdf->SetMargins(-4, -6, -3, true);
        $pdf->SetAutoPageBreak(TRUE, 0);

        for ($i = 0; $i < count($aux); $i++) {
            $pdf->AddPage();
            $pdf->SetFillColor(215, 185, 235);
            $pdf->Rect(0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), 'DF', "");
            $content = array();
            $content['datosVis'] = $aux[$i];

            //construccion de qr
            $id = $aux[$i]['idVisitante'];
            $nombre = $aux[$i]['Nombre'];
            $apellidos = $aux[$i]['ApellidoPaterno'] . ' ' . $aux[$i]['ApellidoMaterno'];
            $cargo = $aux[$i]['DE_Cargo'];
            $empresa = $aux[$i]['DE_RazonSocial'];
            $qrGafete = 'PR22_' . $id . '|' . $nombre . '|' . $apellidos . '|' . $cargo . '|' . $empresa;
            //fin construccion de qr

            $html = $this->renderView('VisitantePrensaBundle:Prensa:Digibage.html.twig', array('content' => $content));
            $pdf->writeHTML($html, false, false, false, false, '');
            $pdf->write2DBarcode($qrGafete, 'QRCODE,M', 34, 90, 70, 60, $style, 'N');
//            $pdf->write1DBarcode($aux[$i]['idVisitante'], 'C128A', 33, 160, 88, 25, 0.9, $styleB, 'N'); //cuando lleva cargo
            $footer = $this->renderView('VisitantePrensaBundle:Prensa:footer.html.twig', array('content' => $content));
            $pdf->writeHTMLCell(0, 0, '', 170, $footer, '', 0, '', true, '', false);
        }
        $pdf->lastPage();

        $pdf->Output(str_replace(" ", "", realpath('digibage') . "/" . $aux[0]['idVisitante'] . '.pdf'), 'F');
        $pdf_txt = $pdf->Output(str_replace(" ", "", realpath('digibage') . "/" . $aux[0]['idVisitante'] . '.pdf'), 'S');
        $base64 = base64_encode($pdf_txt);

        return ($pdf_txt);
    }


}
