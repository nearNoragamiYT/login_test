<?php

namespace AdministracionGlobal\ComitePersonalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use AdministracionGlobal\ComitePersonalBundle\Model\ComitePersonalModel;

class ComitePersonalController extends Controller {

    protected $ComitePersonalModel, $TextoModel;

    const SECTION = 3, MAIN_ROUTE = 'administracion_global_comite_personal_homepage';

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->ComitePersonalModel = new ComitePersonalModel();
    }

    public function comitePersonalAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['App'] = $App;
        $content['user'] = $user;
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
        /* ---  Comienza la logica propia del Action   --- */
        $result_co = $this->ComitePersonalModel->getComiteOrganizador();
        if (!$result_co['status']) {
            throw new \Exception($result_co['data'], 409);
        }
        $co = $result_co['data'];
        $result_coPe = $this->ComitePersonalModel->getComitePersonal();
        if (!$result_coPe['status']) {
            throw new \Exception($result_coPe['data'], 409);
        }
        $coPe = $result_coPe['data'];
        $content['breadcrumb'] = $breadcrumb;
        $content['comite'] = $co;
        $content['comitePersonal'] = $coPe;
        return $this->render('AdministracionGlobalComitePersonalBundle:ComitePersonal:lista_comite_personal.html.twig', array('content' => $content));
    }

    public function agregarComitePersonalAction(Request $request) {
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
        /* Obtenemos textos de la secciÃ³n del AdministraciÃ³n Global 2 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* ---  Comienza la logica propia del Action   --- */
        $response = array('status' => FALSE, 'error' => $content['section_text']['sas_errorInterno']);
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $result = $this->ComitePersonalModel->addPersonal($post);
            $response['error'] = $content['section_text']['sas_errorGuardarInformacion'];
            if ($result['status']) {
                unset($response['error']);
                $post['idContactoComiteOrganizador'] = $result['data'][0]['idContactoComiteOrganizador'];
                $response['status'] = TRUE;
                $response['data'] = $post;
            }
        }
        return $this->jsonResponse($response);
    }

    public function editarComitePersonalAction(Request $request, $idContacto) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Obtenemos textos de la secciÃ³n del AdministraciÃ³n Global 2 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* ---  Comienza la logica propia del Action   --- */
        $response = array('status' => FALSE, 'error' => $content['section_text']['sas_errorInterno']);
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $result = $this->ComitePersonalModel->editPersonal($post, $idContacto);
            $response['error'] = $content['section_text']['sas_errorEditarInformacion'];
            if ($result['status']) {
                unset($response['error']);
                $post['idContactoComiteOrganizador'] = $idContacto;
                $response['status'] = TRUE;
                $response['data'] = $post;
            }
        }
        return $this->jsonResponse($response);
    }

    public function eliminarComitePersonalAction(Request $request, $idContacto) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Obtenemos textos de la secciÃ³n del AdministraciÃ³n Global 2 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* ---  Comienza la logica propia del Action   --- */
        $response = array('status' => FALSE, 'error' => $content['section_text']['sas_errorInterno']);
        if ($request->getMethod() == 'GET') {
            $result = $this->ComitePersonalModel->deletePersonal($idContacto);
            $response['error'] = $content['section_text']['sas_errorEliminarInformacion'];
            if ($result['status']) {
                unset($response['error']);
                $response['data']['idContactoComiteOrganizador'] = $idContacto;
                $response['data']['action'] = 'delete';
                $response['status'] = TRUE;
            }
        }
        return $this->jsonResponse($response);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
