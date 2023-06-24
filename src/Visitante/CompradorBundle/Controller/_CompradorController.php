<?php

namespace Visitante\CompradorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use Visitante\CompradorBundle\Model\CompradorModel;
use Visitante\CompradorBundle\Model\CompradorConfiguration;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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
        $result_count = $this->CompradorModel->getCount();
        if (!$result_count['status']) {
            throw new \Exception($result_count['data'], 409);
        }
        $result_count = $result_count['data'];
        $result_list = $this->CompradorModel->getListidCompradores();
        $result_count_filter = array();
        if ($where != '') {
            $where = 'WHERE ' . $where;
            $result_list = $this->CompradorModel->getListidCompradores($where);
            $result_count_filter = $this->CompradorModel->getCount($where);
            if (!$result_count_filter['status']) {
                throw new \Exception($result_count_filter['data'], 409);
            }
            $result_count_filter = $result_count_filter['data'];
        }
        if (empty($result_count_filter))
            $result_count_filter[0]['count'] = $result_count[0]['count'];

        $result_query = $this->CompradorModel->getCompradores($where, $order, $post);
        if (!$result_query['status']) {
            throw new \Exception($result_query['data'], 409);
        }
        $result_query = $result_query['data'];
        $data = array();
        foreach ($result_query as $key => $value) {
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
            unset($value['idStatus']);
            $data[$key][] = '<input type="checkbox" id="e-' . $flag . '" value="' . $flag . '" class="socio-check" ' . $disabled . '/>
                     <label for="e-' . $flag . '"></label>';

            foreach ($value as $colum) {
                $data[$key][] = (string) $colum;
            }
            if ($idVisitante != "") {
                $data[$key][] = '<i class="material-icons edit-record tooltipped" idVisitante="' . $idVisitante . '" data-position="left" data-tooltip="Editar">mode_edit</i>';
            } else {
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

        $text = $this->CompradorModel->getTexts($lang, self::TEMPLATE);


        $where = $session->get("where_asoc");
        $order = $session->get("order_asoc");

        $result_query = $this->CompradorModel->getCompradoresExport($where, $order);
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
            "Comprador",
            "Preregistrado",
            "Fecha Preregistro",
            "Estatus",
        );
        $data = $result_query;
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_ES"]) . "_Comprador_" . date('d-m-Y G.i');
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

        $email = $content['visitante']['Email'];
        $content['visitante']['idVisitante'] = $this->CompradorModel->completeID($content['visitante']['idVisitante']);
        /* Estructura envío de email */
        $body = $this->renderView('VisitanteCompradorBundle:Comprador:comprobante_email.html.twig', array('content' => $content));
        $res = $this->get('ixpo_mailer')->send_email($content['edicion']['Edicion_ES'], $email, $body);
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

}
