<?php

namespace AdministracionGlobal\EventoBundle\Controller;

/**
 * Description of EventoController
 *
 * @author Eric
 */
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AdministracionGlobal\EventoBundle\Model\EventoModel;
use Utilerias\TextoBundle\Model\TextoModel;
use Symfony\Component\HttpFoundation\Response;

class EventoController extends Controller {

    protected $EventoModel, $TextoModel;

    const SECTION = 3, MAIN_ROUTE = 'evento';

    public function __construct() {
        $this->EventoModel = new EventoModel();
        $this->TextoModel = new TextoModel();
    }

    public function listaEventosAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
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

        /* Obtenemos textos de la sección del Administración Global 2 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* Obtenemos los eventos */
        $result_evento = $this->EventoModel->getEvento();
        if (!$result_evento['status']) {
            throw new \Exception($result_evento['data'], 409);
        }
        $content['breadcrumb'] = $breadcrumb;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['ev'] = $result_evento['data'];

        return $this->render('AdministracionGlobalEventoBundle:Evento:lista_eventos.html.twig', array('content' => $content));
    }

    public function eventoNuevoAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        $profile = $this->getUser();
        $user = $profile->getData();
        #print_r($profile);echo'<br><br><br>';print_r($user);die('profile y user');
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        $post['idComiteOrganizador'] = $user['ComiteOrganizador']['idComiteOrganizador'];
        if ($request->getMethod() == 'POST') {
            $res = $this->EventoModel->insertEvento($post);
            $result['data'] = $res['data'];
            if ($res['status']) {
                $result['status'] = TRUE;
                $post['idEvento'] = $res['data'][0]['idEvento'];
                $result['data'] = $post;
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function eventoEditarAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $res = $this->EventoModel->updateEvento($post);
            $result['data'] = $res['data'];
            if ($res['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function eventoEliminarAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $res = $this->EventoModel->deleteEvento($post);
            $result['data'] = $res['data'];
            if ($res['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
