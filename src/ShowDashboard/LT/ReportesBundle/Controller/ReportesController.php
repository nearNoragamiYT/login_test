<?php

namespace ShowDashboard\LT\ReportesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Utilerias\TextoBundle\Model\TextoModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use ShowDashboard\LT\ReportesBundle\Model\LectorasConfiguration;
use ShowDashboard\LT\ReportesBundle\Model\LectorasModel;

class ReportesController extends Controller
{
    protected $TextoModel, $LectorasModel, $LectorasConfiguration;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->LectorasConfiguration = new LectorasConfiguration();
        $this->LectorasModel = new LectorasModel();
    }

    const SECTION = 9;

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
        // $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        // if (!$breadcrumb) {
        //     $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
        //     return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        // }
        // $content["breadcrumb"] = $breadcrumb;
        return $this->render('ShowDashboardLTReportesBundle:Reportes:reportes_lectoras.html.twig', array('content' => $content));
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
        $types = $this->LectorasModel->getServicios(array("idEdicion" => $idEdicion, "idForma" => 401, "idEvento" => $idEvento));
        $empresas = $this->LectorasModel->getReporteSeguimiento(array("idEdicion" => $idEdicion, "idEvento" => $idEvento));
        $lectoras = $this->LectorasModel->getReporteSeguimientoDetalle($idEdicion, $idEvento);
        foreach ($empresas as $key => $value) {

            foreach ($types as $keyForm => $valueForm) {
                $cantidad = 0;
                foreach($lectoras as $i => $val){
                    if(($empresas[$key]['idEmpresa'] == $lectoras[$i]['idEmpresa']) && ($types[$keyForm]["idServicio"] == $lectoras[$i]['Servicio'])){
                        $cantidad = $cantidad + $lectoras[$i]['Cantidad'];
                    }
                }
                $empresas[$key][$types[$keyForm]["TituloES"]] = $cantidad;
                if($types[$keyForm]['TituloES']!= ''){
                    $headers2[] = $types[$keyForm]['TituloES'];
                }
            }

            $subtotal = 0;
            foreach($lectoras as $i => $val){
                if($empresas[$key]['idEmpresa'] == $lectoras[$i]['idEmpresa']){
                    $subtotal = $subtotal + ($lectoras[$i]['Cantidad'] * $lectoras[$i]['Precio']);
                }
            }
            $empresas[$key]['SubTotal'] = $subtotal;
            $iva = $subtotal * 0.16;
            $empresas[$key]['IVA'] = $iva;
            $empresas[$key]['Total'] = $subtotal + $iva;
        }


        $headers1 = array(
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
            "Celular Forma"
        );

        $headers3 = array(
            'SUBTOTAL',
            'IVA',
            'TOTAL'
        );
        $data = $empresas;
        $meta_data = array_merge($headers1, array_unique($headers2), $headers3);
        
        return $this->excelReport($data, $meta_data, $file_name);
    }

    public function reportGlobalAction(Request $request){
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

        foreach ($data as $key => $value) {
            unset($data[$key]["idEmpresa"]);
        }



        $meta_data = array(
            "Nombre Comercial",
            "Contacto Contratación",
            "Email Contratación",
            "Telefono Contratación",
            "Teléfono Responsiva",
            "Numero de Stand",
            "App Sin Equipo Solicitadas",
            "App + Equipo (Celular) Solicitadas",
            "Paquete A de 3 a 5 apps solicitadas",
            "Paquete B de 6 a 10 apps solicitadas",
            "Mini Scan Wireless Solicitadas",
            "App sin Equipo Asignadas",
            "App + Equipo Asignadas",
            "Mini Scan Wireless Asignadas"
        );
        return $this->excelReport($data, $meta_data, $file_name);
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
