<?php

namespace ShowDashboard\RS\ReporteComprasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\RS\ReporteComprasBundle\Model\ReporteComprasModel;

class ComprasController extends Controller {

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->ReporteComprasModel = new ReporteComprasModel();
    }

    const SECTION = 11;

    public function indexAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $general_text = $this->TextoModel->getTexts($lang);
        $content = array();
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* indices para el select de reportes */
        $campo = array(
            0 => "Total general",
            1 => "Total por tarjeta",
            2 => "Total por nodo",
            3 => "Total por efectivo"
        );
        $content['campo'] = $campo;
        /* indice para el select filtro */
        $filtro = $this->ReporteComprasModel->getFiltro();

        if (!$filtro['status']) {
            throw new \Exception($filtro['data'], 409);
        }
        $filtros = $filtro['data'];
        $content['filtro'] = $filtros;
        /* ------------------------------------------- */
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

        $content['lang'] = $lang;

        return $this->render('ShowDashboardRSReporteComprasBundle:Default:compras.html.twig', array('content' => $content));
    }

    public function reportAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $post = $request->request->all();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $date_start = $post['fechaInicial'] . ' ' . $post['horaInicial'];
        $date_end = $post['fechaFinal'] . ' ' . $post['horaFinal'];
        $App = $this->get('ixpo_configuration')->getApp();
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);

        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }

        $text = $section_text['data'];
        $data = $this->ReporteComprasModel->getReporteCaja($date_start, $date_end);
        if (!$data['status']) {
            throw new \Exception($data['data'], 409);
        }
        $session->set('purchaseReport', $data['data']);

        return $this->jsonResponse($data);
    }

    public function reportesAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $post = $request->request->all();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $date_start = $post['fechaInicial'] . ' ' . $post['horaInicial'];
        $date_end = $post['fechaFinal'] . ' ' . $post['horaFinal'];

        if ($idTarjeta = 1) {
            $idTarjeta = $post['campo']; //Tarjeta de credito
        } else if ($idTarjeta = 3) {
            $idTarjeta = $post['campo']; //Efectivo
        }

        $App = $this->get('ixpo_configuration')->getApp();
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);

        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }

        $text = $section_text['data'];
        $data = $this->ReporteComprasModel->getReporte($date_start, $date_end, $idTarjeta);
        if (!$data['status']) {
            throw new \Exception($data['data'], 409);
        }
        $session->set('purchaseReport', $data['data']);

        return $this->jsonResponse($data);
    }

    public function reportNodoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $post = $request->request->all();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $date_start = $post['fechaInicial'] . ' ' . $post['horaInicial'];
        $date_end = $post['fechaFinal'] . ' ' . $post['horaFinal'];

        $App = $this->get('ixpo_configuration')->getApp();
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);

        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }

        $text = $section_text['data'];
        $data = $this->ReporteComprasModel->getReporteNodo($date_start, $date_end);
        if (!$data['status']) {
            throw new \Exception($data['data'], 409);
        }
        $session->set('purchaseReport', $data['data']);

        return $this->jsonResponse($data);
    }

    public function updateGeneralAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $post = $request->request->all();

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $args = array(
                'idNodo' => $post['filtro'],
                'fechaInicial' => $post['fechaInicial'] . ' ' . $post['horaInicial'] . ":00",
                'fechaFinal' => $post['fechaFinal'] . ' ' . $post['horaFinal'] . ":00"
            );
            $result = $this->ReporteComprasModel->getupdateGeneral($args);

            if (!$result['status']) {
                throw new \Exception($result['data'], 409);
            }
            return $this->jsonResponse($result);
        }
    }

    public function downloadAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);

        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];
        $general = $session->get('purchaseReport');
        $filename = str_replace(" ", "_", $session->get('edicion')['Edicion_ES']) . "-Reporte_Caja-" . date('d-m-Y');
        $table_metadata = array(
            "idCompra",
            "FechaPagado",
            "NombreCompleto",
            "Email",
            "ProductoES",
            "Total"
        );

        return $this->excelReport($general, $table_metadata, $filename);
    }

    public function downloadRAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);

        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];
        $general = $session->get('purchaseReport');
        $filename = str_replace(" ", "_", $session->get('edicion')['Edicion_ES']) . "-Reporte_Caja-" . date('d-m-Y');
        $table_metadata = array(
            "idCompra",
            "idFormaPago",
            "FechaPagado",
            "NombreCompleto",
            "Email",
            "ProductoES",
            "Total"
        );

        return $this->excelReport($general, $table_metadata, $filename);
    }

    public function downloadNAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);

        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];
        $general = $session->get('purchaseReport');
        $filename = str_replace(" ", "_", $session->get('edicion')['Edicion_ES']) . "-Reporte_Caja-" . date('d-m-Y');
        $table_metadata = array(
            "idCompra",
            "idNodo",
            "FechaPagado",
            "NombreCompleto",
            "Email",
            "ProductoES",
            "Total"
        );

        return $this->excelReport($general, $table_metadata, $filename);
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

    public function compraStatusAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $data = $this->ReporteComprasModel->getCompraStatus();

        if (!$data['status']) {
            throw new \Exception($data['data'], 409);
        }

        return $this->jsonResponse($data);
    }

    public function compraTicketAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();

        $general_text = $this->TextoModel->getTexts($lang);

        $content = array();
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }

        $content['general_text'] = $general_text['data'];
//        $content = array();
        $content['tipos'] = array('Comprobante-caja', 'Comprobante-pre-registro', 'Comprobante-visitante');

        /* Obtenemos los registros de la compra */
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $id = $post['idCompra'];
            $result = $this->ReporteComprasModel->getimpresionTicket($id);

            if (!$result['status']) {
                throw new \Exception($result['data'], 409);
            }
            return $this->jsonResponse($result);
        }


        return $this->render('ShowDashboardRSReporteComprasBundle:Default:generar_ticket.html.twig', array('content' => $content));
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
