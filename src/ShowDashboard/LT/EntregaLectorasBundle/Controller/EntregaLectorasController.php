<?php

namespace ShowDashboard\LT\EntregaLectorasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\LT\EntregaLectorasBundle\Model\EntregaLectorasModel;

class EntregaLectorasController extends Controller {

    private $model, $text;

    const idForma = 401, SECTION = 9;

    public function __construct() {
        $this->model = new EntregaLectorasModel();
        $this->text = new TextoModel();
    }

    function mostrarAction(Request $request, $idEmpresa) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        $content['idEvento'] = $session->get("idEvento");
        $content['idEmpresa'] = $idEmpresa;
        // ------    Obtenemos los textos generales    ------ //
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* ---  Obtenemos los textos del modulo  --- */
        $section_text = $this->text->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* ---  comienza la logica del action  --- */
        /* ---  para el paquete del header  --- */
        $args = Array('p."idEdicion"' => $content['idEdicion']);
        $content["packages"] = $this->model->getPackages($args);
        $args = Array('e."idEmpresa"' => $idEmpresa, 'ee."idEdicion"' => $content['idEdicion']);
        $content["header"] = $this->model->getCompanyHeader($args);
        /* --- es necesario consultar primero el header donde viene el idContacto por si no hay EMFO   --- */
        $where = Array("idEvento" => $content['idEvento'], "idEdicion" => $content['idEdicion']);
        $where['idForma'] = self::idForma;
        $where['idEmpresa'] = $idEmpresa;
        /* ---  obtenemos el detalle de la forma donde vienen los detalles de los servicios y los pagos  --- */
        $content['EMFO'] = $this->model->getEmpresaForma($where, $conten['header']['idContacto']);
        /* ---  detalle de los servicios solicitados  --- */
        $content["detalleServicio"] = json_decode($content['EMFO']['DetalleServicioJSON'], true);
        /* ---  comparamos si tiene el lenguaje en que se lleno la forma sino por defecto la dejamos en español  --- */
        $content['langLlenado'] = ($content['EMFO']['Lang'] != NULL && $content['EMFO']['Lang'] != "") ? $content['EMFO']['Lang'] : 'es';
        /* ---  detalle de la entrega y devolucion de la lectora  --- */
        $content['detalleEntrega'] = json_decode($content['EMFO']['DetalleEntregaScannerJSON'], true);
        /* ---  optenemos todos los servicios/productos de lectoras  --- */
        $content['lectoras'] = $this->model->getLectoras($where);
        /* Consultamos el catalogo de status de pago /
          $result_status_pago = $this->model->getStatusPago();
          if (!$result_status_pago['status']) {
          throw new \Exception($result_status_pago['data'], 409);
          }
          $statuspago = Array();
          foreach ($result_status_pago['data'] as $key => $value) {
          $statuspago[$value['idStatusPago']] = $value;
          }
          $content['StatusPago'] = $statuspago; */

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
        array_push($content["breadcrumb"], Array("Modulo_" . strtoupper($lang) => $content["header"]["DC_NombreComercial"], "Ruta" => "", 'Permisos' => array()));
        $content['companyOrigin'] = $session->get("companyOrigin");
        return $this->render('ShowDashboardLTEntregaLectorasBundle:EntregaLectoras:mostrar.html.twig', Array("content" => $content));
    }

    function detalleEntregaAction(Request $request, $idEmpresa) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        $content['idEvento'] = $session->get("idEvento");

        $post = $request->request->all();
        $where = Array(
            "idEmpresa" => $idEmpresa,
            "idForma" => self::idForma,
            "idEvento" => $content['idEvento'],
            "idEdicion" => $content['idEdicion']
        );
        /* ---  en caso de ser devueltas les cambia el estatus de las lectoras en EmpresaScanner a devuelta(2)  --- */
        $this->model->actualizaStatus($post['DetalleEntregaScannerJSON'], $where);
        /* ---  actualiza el detalle de entrega en EmpresaForma en formato JSON  --- */
        $this->model->detalleEntrega(Array("DetalleEntregaScannerJSON" => json_encode($post['DetalleEntregaScannerJSON'], true)), $where);
        $content['data'] = $post;
        $content['ruta'] = substr($request->getUriForPath("login"), 0, -5);
        if ($post['status'] == "Entrega")
            $html = $this->renderView("ShowDashboardLTEntregaLectorasBundle:EntregaLectoras:responsiva.html.twig", array('content' => $content));
        else if ($post['status'] == "Devolucion")
            $html = $this->renderView("ShowDashboardLTEntregaLectorasBundle:EntregaLectoras:recibo.html.twig", array('content' => $content));
        $base64 = $this->createTCPDF($html, $post['status']);
        return $this->jsonResponse(Array("status" => TRUE, "pdf" => $base64));
    }

    private function createTCPDF($html, $status) {
        $pdf = $this->get("white_october.tcpdf")->create(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetAuthor('Infoexpo');

        $pdf->SetKeywords('Responsiva');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);

        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        if ($status == "Entrega")
            $pdf->SetFont('helvetica', '', 26, '', false);
        else if ($status == "Devolucion")
            $pdf->SetFont('helvetica', '', 26, '', false);

        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $file = $pdf->Output('Responsiva.pdf', 'S');
        $pdf_encode = base64_encode($file);
        return $pdf_encode;
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
