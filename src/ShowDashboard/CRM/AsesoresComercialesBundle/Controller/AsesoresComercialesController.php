<?php

namespace ShowDashboard\CRM\AsesoresComercialesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Utilerias\TextoBundle\Model\TextoModel;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use ShowDashboard\CRM\AsesoresComercialesBundle\Model\AsesoresComercialesModel;

class AsesoresComercialesController extends Controller {

    private $model, $text, $App;

    const seccion = 9, idTipoUsario = 6, idComiteOrganizador = 2;

    public function __construct() {
        $this->model = new AsesoresComercialesModel();
        $this->text = new TextoModel();
        $ConfigurationModel = new ConfigurationModel();
        $this->App = $ConfigurationModel->getApp();
    }

    public function mostrarAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;
        $idEvento = $session->get("idEvento");
        $idEdicion = $session->get("idEdicion");

        /* Obtenemos textos generales */
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Obtenemos textos de la seccion correspondiente */
        $section_text = $this->text->getTexts($lang, self::seccion);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* Comienza la logica propia del Action */
        $args = Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, "idTipoUsuario" => self::idTipoUsario);
        $content['asesoresComerciales'] = $this->model->getAsesoresComerciales($args);

        $content["breadcrumb"] = $this->model->breadcrumb($request->get("_route"), $lang);
        return $this->render('ShowDashboardCRMAsesoresComercialesBundle:AsesoresComerciales:mostrar.html.twig', Array("content" => $content));
    }

    public function agregarEditarAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get("idEvento");
        $idEdicion = $session->get("idEdicion");

        $post = $request->request->all();
        if (isset($post['Password'])) {
            $password = $post['Password'];
            unset($post['Password']);
            if ($password != "____") {
                $post['Password'] = sha1($password . $this->App['salt']);
            }
        }
        $this->model->agregarEdiarAsesor($idEvento, $idEdicion, self::idComiteOrganizador, self::idTipoUsario, $post);
        return new JsonResponse(Array("status" => TRUE, "data" => $post));
    }

    public function activarAction(Request $request) {
        $post = $request->request->all();
        $this->model->activarAsesor($post);
        return new JsonResponse(Array("status" => TRUE));
    }

}
