<?php

namespace AdministracionGlobal\PlataformaBundle\Controller;

/**
 * Description of PlataformaControllers
 *
 * @author Juan
 */
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AdministracionGlobal\PlataformaBundle\Model\PlataformaModel;
use Utilerias\TextoBundle\Model\TextoModel;
use Symfony\Component\HttpFoundation\Response;

class PlataformaController extends Controller {

    protected $PlataformaModel, $TextoModel;

    const SECTION = 3, MAIN_ROUTE = 'plataforma';

    public function __construct() {
        $this->PlataformaModel = new PlataformaModel();
        $this->TextoModel = new TextoModel();
    }

    public function plataformaAction(Request $request) {
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

        /* Obtenemos textos de la secciÃ³n del AdministraciÃ³n Global 2 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* Obtenemos las plataformas */
        $result_pl = $this->PlataformaModel->getPlataforma();
        if (!$result_pl['status']) {
            throw new \Exception($result_pl['data'], 409);
        }
        $pl = $result_pl['data'];
        $content['breadcrumb'] = $breadcrumb;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['pl'] = $pl;
        return $this->render('AdministracionGlobalPlataformaBundle:Plataforma:lista_plataforma.html.twig', array('content' => $content));
    }

    public function plataformaNuevoAction(Request $request) {
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
            $res = $this->PlataformaModel->insertPlataforma($post);
            $result['data'] = $res['data'];
            if ($res['status']) {
                $result['status'] = TRUE;
                $post['idPlataformaIxpo'] = $res['data'][0]['idPlataformaIxpo'];
                $result['data'] = $post;
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function plataformaEditarAction(Request $request) {
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
            $res = $this->PlataformaModel->updatePlataforma($post);
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

    public function plataformaEliminarAction(Request $request) {
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
            $res = $this->PlataformaModel->deletePlataforma($post);
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
