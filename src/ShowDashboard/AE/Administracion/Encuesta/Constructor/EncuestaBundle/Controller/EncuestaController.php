<?php

namespace ShowDashboard\AE\Administracion\Encuesta\Constructor\EncuestaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\AE\Administracion\Encuesta\Constructor\EncuestaBundle\Model\EncuestaModel;

class EncuestaController extends Controller {

    protected $EncuestaModel, $TextoModel;

    const PLATFORM = 5, SECTION = 3;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->EncuestaModel = new EncuestaModel();
    }

    public function encuestaAction(Request $request, $lang) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        //$lang = $session->get('lang');
        $edicion = $session->get('edicion');

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];
        
        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }

        /* Obtenemos textos de la sección del ShowDashboard AE 5 */
        $result_text = $this->TextoModel->getTexts($lang, self::PLATFORM);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            if (isset($post["Activa"])) {
                $post["Activa"] = "1";
            } else {
                $post["Activa"] = "0";
            }
            $post['Encabezado'] = "'" . $post['Encabezado'] . "'";
            $result = $this->EncuestaModel->insertEditEncuesta($post);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('show_dashboard_ae_administracion_encuesta_constructor_encuesta', array("lang" => $lang));
            }

            if (count($result['data']) == 0) {
                $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
                return $this->redirectToRoute('show_dashboard_ae_administracion_encuesta_constructor_encuesta', array("lang" => $lang));
            }

            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            return $this->redirectToRoute('show_dashboard_ae_administracion_encuesta_constructor_encuesta', array("lang" => $lang));
        }

        $args = array('ue."idUsuario"' => $user['idUsuario']);
        $result_eventoEdicion = $this->EncuestaModel->getEventoEdicionUsuario($args);
        if (!$result_eventoEdicion['status']) {
            throw new \Exception($result_eventoEdicion['data'], 409);
        }

        $eventoEdicion = array();
        if (count($result_eventoEdicion['data']) > 0) {
            foreach ($result_eventoEdicion['data'] as $key => $value) {
                $eventoEdicion[$value['idEdicion']] = $value;
            }
        }

        $result_encuestas = $this->EncuestaModel->getEncuesta();
        if (!$result_encuestas['status']) {
            throw new \Exception($result_encuestas['data'], 409);
        }
        $encuestas = array();
        if (count($result_encuestas['data']) > 0) {
            foreach ($result_encuestas['data'] as $key => $value) {
                $encuestas[$value['idEncuesta']] = $value;
            }
        }

        $content = array();
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['idSeccion'] = self::SECTION;
        $content['user'] = $user;
        $content['edicion'] = $edicion;
        $content['encuestas'] = $encuestas;
        $content['eventoEdicion'] = $eventoEdicion;
        $content['lang'] = $lang;
        $content['breadcrumb'] = $breadcrumb;
        return $this->render('ShowDashboardAEAdministracionEncuestaConstructorEncuestaBundle:Encuesta:showListaEncuesta.html.twig', array("content" => $content));
    }

    public function encuestaEliminarAction(Request $request, $lang) {
        $session = $request->getSession();
        //$lang = $session->get('lang');
        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];
        $post = $request->request->all();

        if ($post['idEncuesta'] == "") {
            $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
            return $this->redirectToRoute('show_dashboard_ae_administracion_encuesta_constructor_encuesta', array("lang" => $lang));
        }

        $args = array('idEncuesta' => $post['idEncuesta']);
        $result = $this->EncuestaModel->deleteEncuesta($args);
        if (!$result['status']) {
            $session->getFlashBag()->add('danger', $result['data']);
            return $this->redirectToRoute('show_dashboard_ae_administracion_encuesta_constructor_encuesta', array("lang" => $lang));
        }

        $session->getFlashBag()->add('success', $general_text['sas_eliminoExito']);
        return $this->redirectToRoute('show_dashboard_ae_administracion_encuesta_constructor_encuesta', array("lang" => $lang));
    }

}
