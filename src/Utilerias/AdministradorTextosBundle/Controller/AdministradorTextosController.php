<?php

namespace Utilerias\AdministradorTextosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use Utilerias\AdministradorTextosBundle\Model\AdministradorTextosModel;

class AdministradorTextosController extends Controller {

    protected $AdminstradorTextosModel, $TextoModel;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->AdministradorTextosModel = new AdministradorTextosModel();
    }

    public function administradorTextosAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEvento"))) {
            $lang = $session->get('lang');
            $general_text = $this->TextoModel->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEventoCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['App'] = $App;
        $content['user'] = $user;
        $content['routeName'] = $request->get('_route');
        $content['edicion'] = $session->get("edicion");
        /* $content['breadcrumb'] = $this->AdminstradorTextosModel->breadcrumb($content['routeName'], $lang);
          $content['texto'] = true; */
        $idEvento = $session->get("idEvento");
        $content['idEvento'] = $idEvento;
        $general = $this->TextoModel->getTexts($lang);
        if (!$general['status']) {
            throw new \Exception($general['data'], 409);
        }
        $content['general_text'] = $general['data'];
        /* Obtenemos textos generales */
        $general_text = $this->AdministradorTextosModel->getTexts(array("Seccion" => "0"));
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general'] = $general_text['data'];
        /* Obtenemos del login */
        $login_text = $this->AdministradorTextosModel->getTexts(array("Seccion" => "1"));
        if (!$login_text['status']) {
            throw new \Exception($login_text['data'], 409);
        }
        $content['login_text'] = $login_text['data'];
        /* Obtenemos textos del Wizard */
        $wizard_text = $this->AdministradorTextosModel->getTexts(array("Seccion" => "2"));
        if (!$wizard_text['status']) {
            throw new \Exception($wizard_text['data'], 409);
        }
        $content['wizard_text'] = $wizard_text['data'];
        /* Obtenemos textos de la Administración global */
        $admin_text = $this->AdministradorTextosModel->getTexts(array("Seccion" => "3"));
        if (!$admin_text['status']) {
            throw new \Exception($admin_text['data'], 409);
        }
        $content['admin_text'] = $admin_text['data'];
        /* Obtenemos textos del show dashboard */
        $dash_text = $this->AdministradorTextosModel->getTexts(array("Seccion" => "4"));
        if (!$dash_text['status']) {
            throw new \Exception($dash_text['data'], 409);
        }
        $content['dash_text'] = $dash_text['data'];
        /* Obtenemos textos del AE */
        $ae_text = $this->AdministradorTextosModel->getTexts(array("Seccion" => "5"));
        if (!$ae_text['status']) {
            throw new \Exception($ae_text['data'], 409);
        }
        $content['ae_text'] = $ae_text['data'];
        /* Obtenemos textos del FP */
        $fp_text = $this->AdministradorTextosModel->getTexts(array("Seccion" => "6"));
        if (!$fp_text['status']) {
            throw new \Exception($fp_text['data'], 409);
        }
        $content['fp_text'] = $fp_text['data'];
        /* Obtenemos textos del ED */
        $ed_text = $this->AdministradorTextosModel->getTexts(array("Seccion" => "7"));
        if (!$ed_text['status']) {
            throw new \Exception($ed_text['data'], 409);
        }
        $content['ed_text'] = $ed_text['data'];
        /* Obtenemos textos de las stats */
        $stats_text = $this->AdministradorTextosModel->getTexts(array("Seccion" => "8"));
        if (!$stats_text['status']) {
            throw new \Exception($stats_text['data'], 409);
        }
        $content['stats_text'] = $stats_text['data'];
        //enviamos los textos para que se puedan vizualizar
        return $this->render('UtileriasAdministradorTextosBundle:AdministradorTextos:textos.html.twig', array('content' => $content));
    }

    public function agregarTextoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        /* ---  Comienza la logica propia del Action   --- */
        $response = array('status' => FALSE, 'error' => "Su información no ha sido guardada correctamente debido a un error interno, por favor vuelva a intentarlo más tarde.");
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $post['idEdicion'] = 0;
            $result = $this->AdministradorTextosModel->addText($post);
            $response['error'] = "Se ha porducido un error al guradar su información, por favor intentelo de nuevo.";
            if ($result['status']) {
                unset($response['error']);
                $post['idTexto'] = $result['data'][0]['idTexto'];
                $response['status'] = TRUE;
                $response['data'] = $post;
            }
        }
        return $this->jsonResponse($response);
    }

    public function editarTextoAction(Request $request, $idTexto) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        /* ---  Comienza la logica propia del Action   --- */
        $response = array('status' => FALSE, 'error' => "Su información no ha sido guardada correctamente debido a un error interno, por favor vuelva a intentarlo más tarde.");
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $result = $this->AdministradorTextosModel->editText($post, $idTexto);
            $response['error'] = "Se ha porducido un error al guradar su información, por favor intentelo de nuevo.";
            if ($result['status']) {
                unset($response['error']);
                $post['idContactoComiteOrganizador'] = $result['data'][0]['idContactoComiteOrganizador'];
                $response['status'] = TRUE;
                $response['data'] = $post;
            }
        }
        return $this->jsonResponse($response);
    }

    public function eliminarTextoAction(Request $request, $seccion, $idTexto) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        /* ---  Comienza la logica propia del Action   --- */
        $response = array('status' => FALSE, 'error' => "Su información no ha sido guardada correctamente debido a un error interno, por favor vuelva a intentarlo más tarde.");
        if ($request->getMethod() == 'GET') {
            $result = $this->AdministradorTextosModel->deleteText($seccion, $idTexto);
            $response['error'] = "Se ha porducido un error al guradar su información, por favor intentelo de nuevo.";
            if ($result['status']) {
                unset($response['error']);
                $post['idTexto'] = $idTexto;
                $post['action'] = 'delete';
                $response['status'] = TRUE;
                $response['data'] = $post;
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
