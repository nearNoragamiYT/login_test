<?php

namespace Visitante\CompradorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use Visitante\CompradorBundle\Model\CompradorModel;
use Visitante\CompradorBundle\Model\CompradorConfiguration;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use PKPass\PKPass;

class CompradorController extends Controller {

    protected $TextoModel, $CompradorModel, $configuracion;

    const TEMPLATE = 9;
    const MAIN_ROUTE = "compradores";

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->CompradorModel = new CompradorModel();
        $this->CompradorConfiguration = new CompradorConfiguration();
    }

    public function compradoresAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set('OriginView', self::MAIN_ROUTE);
        $content = array();
        $content['idVisitante'] = $idVisitante;
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado   */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content["breadcrumb"] = $breadcrumb;
        $content["idModuloIxpo"] = $breadcrumb[0]['idModuloIxpo'];

        /* Obtenemos textos del Template AE_AdminVisitantes */
        $section_text = $this->CompradorModel->getTexts($lang, self::TEMPLATE);
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

        $result_statusAsociados = $this->CompradorModel->getStatusAutorizacion();
        if (!$result_statusAsociados['status']) {
            throw new \Exception($result_statusAsociados['data'], 409);
        }
        $content['StatusAsociados'] = $result_statusAsociados['data'];

        $content['routeName'] = $request->get('_route');
        $content['App'] = $App;
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get('idEdicion');
        $content["Comprador_table_columns"] = $this->CompradorConfiguration->getCompradoresFilters();
        $content['currentRoute'] = $request->get('_route');
        $content['tabPermission'] = json_decode($this->CompradorModel->tabsPermission($user), true);
        return $this->render('VisitanteCompradorBundle:Comprador:compradores.html.twig', array('content' => $content));
    }

    public function getDataTableAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set("edicion_asoc", $idEdicion);
        $text = $this->CompradorModel->getTexts($lang, self::TEMPLATE);

        if ($request->getMethod() != 'POST') {
            throw new \Exception("No allowed method", 409);
        }

        $post = $request->request->all();

        $where = $this->setWhere($post);
        $session->set("where_asoc", $where);
        $order = $this->setOrder($post);
        $session->set("order_asoc", $where);
        $result_count = $this->CompradorModel->getCount('', $idEdicion);
        if (!$result_count['status']) {
            throw new \Exception($result_count['data'], 409);
        }
        $result_count = $result_count['data'];
        $result_list = $this->CompradorModel->getListidCompradores('', $idEvento);
        $result_count_filter = array();
        if ($where != '') {
            $where = 'WHERE ' . $where;
            $result_list = $this->CompradorModel->getListidCompradores($where, $idEdicion);
            $result_count_filter = $this->CompradorModel->getCount($where, $idEdicion);
            if (!$result_count_filter['status']) {
                throw new \Exception($result_count_filter['data'], 409);
            }
            $result_count_filter = $result_count_filter['data'];
        }
        if (empty($result_count_filter))
            $result_count_filter[0]['count'] = $result_count[0]['count'];

        $result_query = $this->CompradorModel->getCompradores($where, $order, $post, $idEdicion);
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
            $data[$key][] = '<input type="checkbox" id="e-' . $flag . '" value="' . $flag . '" class="socio-check" ' . $disabled . '/>
                     <label for="e-' . $flag . '"></label>';

            foreach ($value as $colum) {
                $data[$key][] = (string) $colum;
            }
            if ($idVisitante != "") {
                if ($idStatus == 2) {
                    if ($numeroEnvios > 0) {
                        if ($numeroDescargas > 0) {
                            $data[$key][] = '<i class="material-icons edit-record tooltipped" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Editar">mode_edit</i>';
                            $data[$key][] = '<i class="material-icons send-record tooltipped blue-text" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Enviar gafete">email</i>';
                            $data[$key][] = '<i class="material-icons download-record tooltipped blue-text" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Descargar gafete">file_download</i>';
                        } else {
                            $data[$key][] = '<i class="material-icons edit-record tooltipped" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Editar">mode_edit</i>';
                            $data[$key][] = '<i class="material-icons send-record tooltipped blue-text" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Enviar gafete">email</i>';
                            $data[$key][] = '<i class="material-icons download-record tooltipped" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Descargar gafete">file_download</i>';
                        }
                    } else if ($numeroDescargas > 0) {
                        $data[$key][] = '<i class="material-icons edit-record tooltipped" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Editar">mode_edit</i>';
                        $data[$key][] = '<i class="material-icons send-record tooltipped" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Enviar gafete">email</i>';
                        $data[$key][] = '<i class="material-icons download-record tooltipped blue-text" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Descargar gafete">file_download</i>';
                    }else{
                         $data[$key][] = '<i class="material-icons edit-record tooltipped" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Editar">mode_edit</i>';
                        $data[$key][] = '<i class="material-icons send-record tooltipped" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Enviar gafete">email</i>';
                        $data[$key][] = '<i class="material-icons download-record tooltipped" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Descargar gafete">file_download</i>';
                    }
                } else {
                    $data[$key][] = '<i class="material-icons edit-record tooltipped" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Editar">mode_edit</i>';
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

    public function downloadDigibadgeAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        /* Obtenemos textos generales del AE */
        $result_general_text = $this->CompradorModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        $result_template_text = $this->CompradorModel->getTexts($lang, 21);
        if (!$result_template_text['status']) {
            throw new \Exception($result_template_text['data'], 409);
        }
        $template_text = $result_template_text['data'];
        if ($request->getMethod() == "POST") {
            $post = $request->request->all();
            $idVisitante = $post['idVisitante'];
            $result_visitante = $this->CompradorModel->getVisitanteDG($idVisitante);
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
            $result_pdf = $this->createPDF($body, $lang, $datosVis, $digibadge);
            $accion = 1;
            $result_visitante = $this->CompradorModel->updateDowloadLog($idEvento, $idEdicion, $idVisitante, $accion);
            //$result_pdf = $this->createPDF1($datosVis, $lang);
            //$file_digi = str_replace(" ", "", 'digibage/' . $idVisitante . '.pdf');
            ////////////////////////////creacion del wallet ///////////////////////////
            //$crWallet = $this->createWallet($datosVis);
            //$file_wall = str_replace(" ", "", 'wallet/' . $idVisitante . '.pkpass');
            ///////////////////////////////////////////////////////////////////////////
        }
        $respuesta = new Response();
        $respuesta->setContent($result_pdf);
        $respuesta->headers->set('Content-Type', 'application/pdf');
        return $respuesta;
    }

    public function exportDataTableAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set("edicion_asoc", $idEdicion);

        $text = $this->CompradorModel->getTexts($lang, self::TEMPLATE);


        $where = $session->get("where_asoc");
        $order = $session->get("order_asoc");

        $result_query = $this->CompradorModel->getCompradoresExport($where, $order,$idEdicion);
        if (!$result_query['status']) {
            throw new \Exception($result_query['data'], 409);
        }
        $result_query = $result_query['data'];
        foreach ($result_query as $key => $value) {

            if ($value['Comprador'] == 1) {
                $result_query[$key]['Comprador'] = "Si";
            } else {
                $result_query[$key]['Comprador'] = "No";
            }
        }

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

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function setWhere($param) {
        $filtros = $this->CompradorConfiguration->getCompradoresFilters();
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
        $filtros = $this->CompradorConfiguration->getCompradoresFilters();
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

        $result_visitante = $this->CompradorModel->getVisitante($post);
        if (!$result_visitante['status']) {
            throw new \Exception($result_visitante['data'], 409);
        }
        $content['visitante'] = $result_visitante['data']['0'];

        /* Obtenemos textos generales del AE */
        $result_general_text = $this->CompradorModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        $result_template_text = $this->CompradorModel->getTexts($lang, 21);
        if (!$result_template_text['status']) {
            throw new \Exception($result_template_text['data'], 409);
        }
        $template_text = $result_template_text['data'];

        $content['general_text'] = $general_text;
        $content['template_text'] = $template_text;

        $content['edicion'] = $session->get('edicion');

        $content['lang'] = $lang;

        $email = $content['visitante']['Email'];

        /* Estructura envío de email */
        $body = $this->renderView('VisitanteCompradorBundle:Comprador:rechazo_email.html.twig', array('content' => $content));
        $res = $this->get('ixpo_mailer')->send_email($content['edicion']['Edicion_ES'], $email, $body);
        /* Fin estructura envio de Email */

        $response = new Response(json_encode(array('status' => TRUE, 'data' => 'Éxito')));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function emailConfirmacionAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');

        $post = $request->request->all();

        $result_visitante = $this->CompradorModel->getVisitante($post);
        if (!$result_visitante['status']) {
            throw new \Exception($result_visitante['data'], 409);
        }
        $content['visitante'] = $result_visitante['data']['0'];
        $idVisitante = $content['visitante']['idVisitante'];

        /* Obtenemos textos generales del AE */
        $result_general_text = $this->CompradorModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        $result_template_text = $this->CompradorModel->getTexts($lang, 21);
        if (!$result_template_text['status']) {
            throw new \Exception($result_template_text['data'], 409);
        }
        $template_text = $result_template_text['data'];

        $content['general_text'] = $general_text;
        $content['template_text'] = $template_text;

        $content['lang'] = $lang;

        $content['edicion'] = $session->get('edicion');

        $email = $content['visitante']['Email'];
        $content['visitante']['idVisitante'] = $this->CompradorModel->completeID($content['visitante']['idVisitante']);

        $result_visitante = $this->CompradorModel->getVisitanteDG($idVisitante);
        if (!$result_visitante['status']) {
            throw new \Exception($result_visitante['data'], 409);
        }

        $DE_RazonSocial = $result_visitante["data"][0]["DE_RazonSocial"];
        $decode = htmlspecialchars_decode($DE_RazonSocial, ENT_HTML5);
        $result_visitante["data"][0]["DE_RazonSocial"] = $decode;

        $content['datosVis'] = $result_visitante['data'];
        $content['template_text'] = $template_text;
        $body = $this->renderView('VisitanteCompradorBundle:Comprador:comprobante_email.html.twig', array('content' => $content));
        $datosVis = $result_visitante['data'];
        $lang = 'ES';
        $qr = 'S15D69F88D4';
        $digibadge = 'AntadDigibage';
        $result_pdf = $this->createPDF($body, $lang, $datosVis, $digibadge);
        //$result_pdf = $this->createPDF1($datosVis, $lang);
        $file_digi = str_replace(" ", "", 'digibage/' . $idVisitante . '.pdf');

        ////////////////////////////creacion del wallet ///////////////////////////
        //$crWallet = $this->createWallet($datosVis);
        //$file_wall = str_replace(" ", "", 'wallet/' . $idVisitante . '.pkpass');
        ///////////////////////////////////////////////////////////////////////////
        $docs[] = $file_digi;
        //$docs[] = $file_wall;
        $result = $this->get('ixpo_mailer')->send_emailDocs($general_text["ae_asuntoGafeteAntad2021"],$email, $body, 'es', $file_digi);
        $accion = 2;
        $result_visitante = $this->CompradorModel->updateDowloadLog($idEvento, $idEdicion, $idVisitante, $accion);

        $response = new Response(json_encode(array('status' => TRUE, 'data' => 'Éxito')));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function sendDigibadgEEEEEEeAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        /* Obtenemos textos generales del AE */
        $result_general_text = $this->CompradorModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        $result_template_text = $this->CompradorModel->getTexts($lang, 21);
        if (!$result_template_text['status']) {
            throw new \Exception($result_template_text['data'], 409);
        }
        $template_text = $result_template_text['data'];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            ini_set('max_execution_time', 1800);
            ini_set('memory_limit', '-1');
            $result_CompradoresVisitante = $this->CompradorModel->sendGafetesCompradores();
            $idVisitante = array();
            foreach ($result_CompradoresVisitante['data'] as $key => $value) {

                $idVisitante[$key] = $value['idVisitante'];
            }
            foreach ($idVisitante as $key => $value) {
                $idVisitante = $value;
                ini_set('max_execution_time', 1800);
                ini_set('memory_limit', '-1');
                $result_visitante = $this->CompradorModel->getVisitanteDG($idVisitante);
                if (!$result_visitante['status']) {
                    throw new \Exception($result_visitante['data'], 409);
                }

                $DE_RazonSocial = $result_visitante["data"][0]["DE_RazonSocial"];
                $decode = htmlspecialchars_decode($DE_RazonSocial, ENT_HTML5);
                $result_visitante["data"][0]["DE_RazonSocial"] = $decode;

                $content['datosVis'] = $result_visitante['data'];
                $content['template_text'] = $template_text;
                $bodyEmail = $this->renderView('VisitanteCompradorBundle:Comprador:bodyEmail.html.twig', array('content' => $content));
                $body = "";
                $datosVis = $result_visitante['data'];
                $lang = 'ES';
                $qr = 'S15D69F88D4';
                $digibadge = 'AntadDigibage';
                $result_pdf = $this->createPDF($body, $lang, $datosVis, $digibadge);
                //$result_pdf = $this->createPDF1($datosVis, $lang);
                $file_digi = str_replace(" ", "", 'digibage/' . $idVisitante . '.pdf');
                ///////////////////////////////////////////////////////////////////////////
                ini_set('max_execution_time', 1800);
                ini_set('memory_limit', '-1');
                $result = $this->get('ixpo_mailer')->send_emailDocs($general_text["ae_asuntoGafeteAntad2021"],$datosVis[0]['Email'] , $bodyEmail, 'es', $file_digi);
            }
            $response = new Response(json_encode(array('status' => TRUE, 'data' => 'Éxito')));
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function sendDigibadgeAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        /* Obtenemos textos generales del AE */
        $result_general_text = $this->CompradorModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        $result_template_text = $this->CompradorModel->getTexts($lang, 21);
        if (!$result_template_text['status']) {
            throw new \Exception($result_template_text['data'], 409);
        }
        $template_text = $result_template_text['data'];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $idVisitante = $post['idVisitante'];
            $result_visitante = $this->CompradorModel->getVisitanteDG($idVisitante);
            if (!$result_visitante['status']) {
                throw new \Exception($result_visitante['data'], 409);
            }

            $DE_RazonSocial = $result_visitante["data"][0]["DE_RazonSocial"];
            $decode = htmlspecialchars_decode($DE_RazonSocial, ENT_HTML5);
            $result_visitante["data"][0]["DE_RazonSocial"] = $decode;

            $content['datosVis'] = $result_visitante['data'];
            $content['template_text'] = $template_text;
            $bodyEmail = $this->renderView('VisitanteCompradorBundle:Comprador:bodyEmail.html.twig', array('content' => $content));
            $body = "";
            $datosVis = $result_visitante['data'];
            $lang = 'ES';
            $qr = 'S15D69F88D4';
            $digibadge = 'AntadDigibage';
            $result_pdf = $this->createPDF($body, $lang, $datosVis, $digibadge);
            //$result_pdf = $this->createPDF1($datosVis, $lang);
            $file_digi = str_replace(" ", "", 'digibage/' . $idVisitante . '.pdf');

            ////////////////////////////creacion del wallet ///////////////////////////
            //$crWallet = $this->createWallet($datosVis);
            //$file_wall = str_replace(" ", "", 'wallet/' . $idVisitante . '.pkpass');
            ///////////////////////////////////////////////////////////////////////////
            $docs[] = $file_digi;
            //$docs[] = $file_wall;
            $result = $this->get('ixpo_mailer')->send_emailDocs($general_text["ae_asuntoGafeteAntad2021"], $datosVis[0]['Email'], $bodyEmail, 'es', $docs);
            $accion = 2;
            $result_visitante = $this->CompradorModel->updateDowloadLog($idEvento, $idEdicion, $idVisitante, $accion);
        }

        $response = new Response(json_encode(array('status' => TRUE, 'data' => 'Éxito')));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
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

        $img_file = realpath('resources/Visitante/AsociadosBundle/imagenes') . '\Digibadge' . '.jpg';

        $pdf->SetXY(0, 0);
        $pdf->Image($img_file, '', '', 111, 263, 'jpg', '', 'T', false, 300, '', false, false, 1, false, false, false);

        for ($i = 0; $i < count($aux); $i++) {
            $pdf->AddPage();
            $pdf->SetFillColor(249, 148, 58);
            $pdf->Rect(0, 0, $pdf->getPageWidth(), $pdf->getPageHeight(), 'DF', "");

            $content = array();
            $content['datosVis'] = $aux[$i];

            $id = $aux[$i]["idVisitante"];
            $nombre = $aux[$i]["Nombre"];
            $apellidos = $aux[$i]["ApellidoPaterno"] . ' ' . $aux[$i]["ApellidoMaterno"];
            $cargo = $aux[$i]["DE_Cargo"];
            $empresa = $aux[$i]["DE_RazonSocial"];
            $qrGafete = 'CM22_' . $id . '|' . $nombre . '|' . $apellidos . '|' . $cargo . '|' . $empresa;

            $html = $this->renderView('VisitanteCompradorBundle:Gafete:digibadge.html.twig', array('content' => $content));
            $pdf->writeHTML($html, false, false, false, false, '');
            $pdf->write2DBarcode($qrGafete, 'QRCODE,M', 34, 90, 70, 60, $style, 'N');
            // $pdf->write1DBarcode($aux[$i]['idVisitante'], 'C128A', 33, 155, 88, 25, 0.9, $styleB, 'N'); //cuando lleva cargo
            $footer = $this->renderView('VisitanteCompradorBundle:Gafete:footerDigibage.html.twig');
            $pdf->writeHTMLCell(0, 0, '', 170, $footer, '', 0, '', true, '', false);
        }
        $pdf->lastPage();



        $pdf->Output(str_replace(" ", "", realpath('digibage') . "/" . $aux[0]['idVisitante'] . '.pdf'), 'F');

        $pdf_txt = $pdf->Output(str_replace(" ", "", realpath('digibage') . "/" . $aux[0]['idVisitante'] . '.pdf'), 'S');

        $base64 = base64_encode($pdf_txt);

        //return ($base64);
        return $pdf_txt;
    }

    public function createWallet($infoB) {
        for ($i = 0; $i < count($infoB); $i++) {
            //construccion de qr
            $id = $infoB[$i]['idVisitante'];
            $nombre = $infoB[$i]['Nombre'];
            $apellidos = $infoB[$i]['ApellidoPaterno'] . ' ' . $infoB[$i]['ApellidoMaterno'];
            $cargo = $infoB[$i]['DE_Cargo'];
            $empresa = $infoB[$i]['DE_RazonSocial'];
            $qrGafete = 'CM' . $id . '|' . $nombre . '|' . $apellidos . '|' . $cargo . '|' . $empresa;
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
                            'value' => 'COMPRADOR',
                            // 'value' => $infoB[$i]['VisitanteTipoES'],
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
                'backgroundColor' => 'rgb(244, 164, 96)',
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

}