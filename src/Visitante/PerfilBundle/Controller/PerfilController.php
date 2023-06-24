<?php

namespace Visitante\PerfilBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use Visitante\PerfilBundle\Model\PerfilModel;

class PerfilController extends Controller {

    protected $TextoModel, $PerfilModel;

    const TEMPLATE = 15;
    const MAIN_ROUTE = "visitante";

    public function __construct() {
        $this->TextoModel = new TextoModel();
    }

    public function PerfilAction(Request $request, $idVisitante) {
        $this->PerfilModel = new PerfilModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $content = array();
        $content['idVisitante'] = $idVisitante;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['lang'] = $lang;

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Verificamos si tiene permiso en el modulo seleccionado */
        if ($session->get('OriginView') == "elite") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "elite");
        }
        if ($session->get('OriginView') == "visitante") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "visitante");
        }
        if ($session->get('OriginView') == "asociados") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "asociados");
        }
        if ($session->get('OriginView') == "compradores") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "visitante_comprador");
        }
        if ($session->get('OriginView') == "registro_multiple") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "registro_multiple");
        }
        if ($session->get('OriginView') == "encuentro_negocios") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "encuentro_negocios");
        }
        if ($session->get('OriginView') == "visitantes_generales") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "visitantes_generales");
        }
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content['breadcrumb'] = $breadcrumb;
        /* Obtenemos textos del Template AE_AdminVisitantes */
        $section_text = $this->PerfilModel->getTexts($lang, self::TEMPLATE);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];

        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '-1');
        $preguntas = $this->PerfilModel->getQuestions(array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$preguntas['status']) {
            throw new \Exception($preguntas['data'], 409);
        }
        $content['preguntas'] = $preguntas['data'];

        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '-1');
        $respuestas = $this->PerfilModel->getAnswers($idEvento, $idEdicion);
        if (!$respuestas['status']) {
            throw new \Exception($respuestas['data'], 409);
        }
        $content['respuestas'] = $respuestas['data'];

        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '-1');
        $profile = $this->PerfilModel->getProfile(array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idVisitante' => $idVisitante));
        if (!$profile['status']) {
            throw new \Exception($profile['data'], 409);
        }

        $perfil = array();
        foreach ($profile['data'] as $key => $value) {
            if ($value != '') {
                $perfil[$content['respuestas'][$key]['idPregunta']][$key] = $value;
            } else {
                $perfil[$content['respuestas'][$key]['idPregunta']][$key] = '';
            }
        }

        $content['profile'] = $perfil;
        $content['view'] = $session->get('OriginView');

        array_push($content["breadcrumb"], Array("Modulo_" . strtoupper($lang) => $profile['Nombre'], "Ruta" => "", 'Permisos' => array()));

        return $this->render('VisitantePerfilBundle:Perfil:Perfil.html.twig', array('content' => $content));
    }

}