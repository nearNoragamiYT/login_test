<?php

namespace AdministracionGlobal\EdicionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AdministracionGlobal\EdicionBundle\Model\EdicionModel;
use Utilerias\TextoBundle\Model\TextoModel;
use Symfony\Component\HttpFoundation\Response;

class EdicionController extends Controller {

    protected $EdicionModel, $TextoModel;

    //a qué pertenece está sección?
    const SECTION = 3, MAIN_ROUTE = 'edicion_bundle_homepage';

    public function __construct() {
        $this->EdicionModel = new EdicionModel();
        $this->TextoModel = new TextoModel();
    }

//Funciones para administrar las ediciones en una NUEVA VENTANA
    //función para obtener las Ediciones 
    public function getEditionAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();

        //Obtenemos textos generales 
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

        //Obtenemos textos de la seccion de Administración Global 2 
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];

        //Obtenemos las Ediciones
        $result_edition = $this->EdicionModel->getEdition();
        if (!$result_edition['status']) {
            throw new \Exception($result_edition['data'], 409);
        }
        $edition = $result_edition['data'];
        $content['breadcrumb'] = $breadcrumb;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['edition'] = $edition;
        return $this->render('AdministracionGlobalEdicionBundle:Edicion:lista_edicion.html.twig', array('content' => $content));
    }

    //inserta una nueva Edicion
    public function insertEditionAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "edicion_bundle_homepage");
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }

        /* Obtenemos textos de la sección del Asistente 2 */
        $result_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        if ($request->getMethod() == 'POST') {
            /* if (!$request->isXmlHttpRequest()) {
              $session->getFlashBag()->add('danger', $general_text['sas_errorPeticion']);
              return $this->redirectToRoute('wizard');
              } */

            $post = $request->request->all();
            $this->EdicionModel->trimValues($post);

            /* ------------------------------------------------ */

            $data = array();
            $data['idEdicion'] = $post['idEdicion'];
            unset($post['idEdicion']);
            $data['idEvento'] = $post['idEvento'];
            unset($post['idEvento']);
            $data['idComiteOrganizador'] = $post['idComiteOrganizador'];
            unset($post['idComiteOrganizador']);
            $post = array_merge($data, $this->EdicionModel->formatQuoteValue($post));

            $result = $this->EdicionModel->insertEdition($post);

            if (!$result['status']) {
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            if (count($result['data']) == 0) {
                $result['status_aux'] = FALSE;
                $result['data'] = $general_text['sas_errorPeticion'];
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            $idEdicion = $result['data'][0]['idEdicion'];
            /* Insertamos o actualizamos los status de la configuracion inicial */
            $values = array(
                'idComiteOrganizador' => $post['idComiteOrganizador'],
                'Edicion' => "true"
            );

            if (!$result_config['status']) {
                $response = new Response(json_encode($result_config));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            /* Traemos la informacion de la Edicion insertada */
            $args = array('idEdicion' => $idEdicion);
            $result = $this->EdicionModel->getEdition($args);
            if (!$result['status']) {
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $result['status_aux'] = TRUE;
            $result['data'] = $result['data'][0];
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        /* Obtenemos la Configuracion Inicial */
        $result_conf = $this->EdicionModel->getConfiguracionInicial();
        if (!$result_conf['status']) {
            throw new \Exception($result_conf['data'], 409);
        }

        $configuration = FALSE;
        if (count($result_conf['data']) > 0) {
            $configuration = $result_conf['data'][0];
        }

        $content = array();
        $content['breadcrumb'] = $breadcrumb;
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['configuration'] = $configuration;
        $content['user'] = $user;
        $edition = $result_edition['data'];
        $content['edition'] = $edition;
        return $this->render('AdministracionGlobalEdicionBundle:Edicion:show_edicion.html.twig', array('content' => $content));
    }

    public function informacionGeneralLogoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];
        $result_files = $this->EdicionModel->uploadFiles($_FILES, $general_text);
        $response = new Response(json_encode($result_files));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function edicionLogosAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];
        $result_files = $this->EdicionModel->uploadFiles($_FILES, $general_text, "header/");
        $response = new Response(json_encode($result_files));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    //Actualiza los datos de una Edición existente
    public function updateEditionAction(Request $request, $idEdicion) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();

        /* Obtenemos la Configuracion Inicial */
        $result_conf = $this->EdicionModel->getConfiguracionInicial();
        if (!$result_conf['status']) {
            throw new \Exception($result_conf['data'], 409);
        }

        $configuration = FALSE;
        if (count($result_conf['data']) > 0) {
            $configuration = $result_conf['data'][0];
        }

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "edicion_bundle_homepage");
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }

        /* Obtenemos textos de la sección del Asistente 2 */
        $result_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
        }
        /* Traemos la informacion de la Edicion actualizada */
        $args = array('idEdicion' => $idEdicion);
        $result_edition = $this->EdicionModel->getEdition($args);
        if (!$result_edition['status']) {
            throw new \Exception($result_edition['data'], 409);
        }

        if (!isset($result_edition['data'][$idEdicion])) {
            $session->getFlashBag()->add('error', 'Su peticion para editar esta Edicion no ha sido procesada correctamente');
            $url = $request->headers->get('referer');
            if ($url == "") {
                $url = $this->generateUrl('edicion_bundle_homepage');
            }
            return $this->redirect($url);
        }

        $edition = $result_edition['data'][$idEdicion];

        $content = array();
        $content['breadcrumb'] = $breadcrumb;
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['configuration'] = $configuration;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['edition'] = $edition;
        $content['action'] = $this->generateUrl('edicion_bundle_editar', array('idEdicion' => $idEdicion));
        array_push($content["breadcrumb"], Array("Modulo_" . strtoupper($lang) => $content['edition']['Edicion_' . strtoupper($lang)], "Ruta" => "", 'Permisos' => array()));
        return $this->render('AdministracionGlobalEdicionBundle:Edicion:show_edicion.html.twig', array('content' => $content));
    }

    //Elimina una Edición existente
    public function deleteEditionAction(Request $request) {
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
            $res = $this->EdicionModel->deleteEdition($post);
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
