<?php

namespace ShowDashboard\LT\ReportesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Utilerias\TextoBundle\Model\TextoModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use ShowDashboard\LT\ReportesBundle\Model\LectorasConfiguration;
use ShowDashboard\LT\ReportesBundle\Model\LectorasModel;
use Symfony\Component\HttpFoundation\JsonResponse;

class GridController extends Controller
{
    protected $TextoModel, $LectorasModel, $LectorasConfiguration;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->LectorasConfiguration = new LectorasConfiguration();
        $this->LectorasModel = new LectorasModel();
    }

    const SECTION = 9;
    
    public function globalReportGridAction(Request $request) {
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
        /* $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content["breadcrumb"] = $breadcrumb; */
        return $this->render('ShowDashboardLTReportesBundle:exAccessLeads:exAccess.html.twig', array('content' => $content));
    }

    public function configGridAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $args['text']['previousText'] = $lang == 'es' ? 'Anterior' : 'Previous'; //String
        $args['text']['nextText'] = $lang == 'es' ? 'Siguiente' : 'Next'; //String
        $args['text']['loadingText'] = $lang == 'es' ? 'Cargando...' : 'Loading...'; //String
        $args['text']['noDataText'] = $lang == 'es' ? 'No se encontraron Registros' : 'No data found'; //String
        $args['text']['pageText'] = $lang == 'es' ? 'Página' : 'Page'; //String
        $args['text']['ofText'] = $lang == 'es' ? 'de' : 'of'; //String
        $args['text']['rowsText'] = $lang == 'es' ? 'registros' : 'rows'; //String

        $args['text']['nombreComercial'] = $lang == 'es' ? 'Nombre comercial' : 'Trade name'; //String
        $args['text']['contactoContratacion'] = $lang == 'es' ? 'Contacto Contratación' : 'Contact contracting';
        $args['text']['emailContratacion'] = $lang == 'es' ? 'Email Contratación' : 'Email contracting';
        $args['text']['telefonoContratacion'] = $lang == 'es' ? 'Teléfono Contratación' : 'Phone contracting';
        $args['text']['CelularResponsiva'] = $lang == 'es' ? 'Teléfono Responsiva' : 'Phone responsive';
        $args['text']['standNumber'] = $lang == 'es' ? 'Numero de stand' : 'Stand number';
        $args['text']['appSinEquipoSolicitadas'] = $lang == 'es' ? 'App Sin Equipo Solicitadas' : 'App without equipment requested';
        $args['text']['appMasEquipoCelularSolicitadas'] = $lang == 'es' ? 'App + Equipo (Celular) Solicitadas' : 'App + Equipo (Celular) Solicitadas';
        $args['text']['paqueteA3A5AppsSinEquipoSolicitadas'] = $lang == 'es' ? 'Paquete A de 3 a 5 Apps solicitadas' : 'Paquete A de 3 a 5 Apps solicitadas';
        $args['text']['paqueteB6A10AppsSinEquipoSolicitadas'] = $lang == 'es' ? 'Paquete B de 6 a 10 Apps solicitadas' : 'Paquete B de 6 a 10 Apps solicitadas';
        $args['text']['miniScanWirelessSolicitadas'] = $lang == 'es' ? 'Mini Scan Wireless Solicitadas' : 'Mini Scan Wireless requested';
        $args['text']['appAsignadas'] = $lang == 'es' ? 'App Infoexpo Asignadas' : 'Assigned Infoexpo App';
        $args['text']['appMasEquipoCelularAsignadas'] = $lang == 'es' ? 'App Mobile + Equipo Asignadas' : 'App Mobile + Equipo Asignadas';
        $args['text']['miniScanWirelessAsignadas'] = $lang == 'es' ? 'Mini Scan Wireless Asignadas' : 'Mini Scan Wireless Asignadas';
        
        
        $args['text']['select'] = $lang == 'es' ? 'Selecciona los campos a visualizar en la tabla' : 'Select the fields to visualize on the table'; //String
        $args['text']['label'] = $lang == 'es' ? 'Campos a visualizar' : 'Fields to view'; //String
        $args['text']['todos'] = $lang == 'es' ? 'Todos' : 'All'; //String
        $args['text']['descarga'] = $lang == 'es' ? 'Descarga Global Report en Excel' : 'Download Global Report in Excel'; //String
        $args['text']['minSelect'] = $lang == 'es' ? 'Se deben seleccionar minimo 4 campos a la vez' : 'You must have at least 4 fields selected at a time'; //String
        $args['text']['maxSelect'] = $lang == 'es' ? 'Se pueden seleccionar máximo 8 campos a la vez' : 'You can display up to 8 fields at a time'; //String
        $args['pageSize'] = 10; //Integer
        $result['data'] = $args;
        $result['status'] = true;
        return $this->response($result);
    }

    public function globalLeadsAction(Request $request){
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $charset = 'UTF-8';
        $name = iconv($charset, 'ASCII//TRANSLIT', $session->get('edicion')["Edicion_ES"]);
        $event_name = preg_replace("/[^A-Za-z0-9 ]/", '', $name);
        $file_name = str_replace(" ", "_", $event_name) . "_Global_Report_" . date('d-m-Y G.i');
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

        $data = $this->LectorasModel->getReporteGlobal(array("idEdicion" => $idEdicion, "idEvento" => $idEvento));
        return $this->response($data);
    }

    public function detalleGlobalReportAction(Request $request, $id) {
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

        $data = $this->LectorasModel->getDetalleGR($id);
        // print_r($data);
        // die();

        /* $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content["breadcrumb"] = $breadcrumb; */
        return $this->render('ShowDashboardLTReportesBundle:exAccessLeads:reporte_detalle.html.twig', array('content' => $content, 'data' => $data));
    }

    private function response($result) {
        $response = new JsonResponse($result, 200, array('Content-Type', 'text/json'));
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, *');
        return $response;
    }
}
