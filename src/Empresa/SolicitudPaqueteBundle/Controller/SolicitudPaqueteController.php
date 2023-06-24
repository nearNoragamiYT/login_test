<?php

namespace Empresa\SolicitudPaqueteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use Empresa\SolicitudPaqueteBundle\Model\SolicitudPaqueteModel;

class SolicitudPaqueteController extends Controller {

    protected $model, $text;

    public function __construct() {
        $this->model = new SolicitudPaqueteModel();
        $this->text = new TextoModel();
    }

    public function mostrarAction(request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        // ------    Obtenemos los textos generales    ------ //
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* ---  muestra un error en caso de que la edicion se pierda  --- */
        if (!isset($content['idEdicion'])) {
            $session->getFlashBag()->add('danger', $general_text['sas_errorEdicionCaduco']);
            return $this->redirectToRoute('dashboard', array('lang' => $lang));
        }
        $content['general_text'] = $general_text['data'];
        /* ---  Obtenemos los textos del modulo  --- */
        $section_text = $this->text->getTexts($lang, 4);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* ---  inicia la logica del módulo  --- */
        //$breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        /* if (!$breadcrumb) {
          $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
          return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
          } */
        $content["breadcrumb"] = $breadcrumb;
        /* ---  obtenmos todas las solicitudes de paquetes  --- */
        $content['solicitudes'] = $this->model->getSolicitudes($content['idEvento'], $content['idEdicion']);
        $content['paquetes'] = $this->model->getPaquetes($content['idEvento'], $content['idEdicion'], $lang);

        return $this->render('EmpresaSolicitudPaqueteBundle:SolicitudPaquete:mostrar.html.twig', Array("content" => $content));
    }

    public function cancelarSolicitudAction(request $request, $idSolicitud) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        /* ---  logica del action  --- */
        $post = $request->request->all();
        date_default_timezone_set("America/Mexico_City");
        $post['FechaCancelacion'] = date("d/m/Y h:i A");
        $this->model->cancelarSolicitud($content['idEdicion'], $idSolicitud, $post);
        return $this->jsonResponse($post);
    }

    public function aprobarSolicitudAction(request $request, $idSolicitud) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        /* ---  logica del action  --- */
        $post = $request->request->all();
        $this->model->aprobarSolicitud($content['idEdicion'], $idSolicitud, $post);
        return $this->jsonResponse($post);
    }

    public function enviarEmailAction(request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        $content['general_text'] = $general_text['data'];
        /* ---  Obtenemos los textos del modulo  --- */
        $section_text = $this->text->getTexts($lang, 4);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* ---  logica del action  --- */
        $post = $request->request->all();
        $content['correo'] = $post['Correo'];

        $body = $this->renderView("EmpresaSolicitudPaqueteBundle:SolicitudPaquete:enviar_correo.html.twig", array('content' => $content, 'lang' => $lang));

        /* ENVIO DE EMAIL */
        $result = $this->get("ixpo_mailer")->send_email($post['Asunto'], /* $post['Email'] */ "eduardoc.infoexpo@gmail.com", $body, $lang);

        return $this->jsonResponse($post);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
