<?php

namespace AdministracionGlobal\ComiteOrganizadorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AdministracionGlobal\ComiteOrganizadorBundle\Model\ComiteOrganizadorModel;
use Utilerias\TextoBundle\Model\TextoModel;

class ComiteOrganizadorController extends Controller {

    protected $ComiteOrganizadorModel;

    const MAIN_ROUTE = 'comite_organizador', SECTION = 3;

    public function __construct() {
        $this->ComiteOrganizadorModel = new ComiteOrganizadorModel();
        $this->TextoModel = new TextoModel();
    }

    public function comiteOrganizadorAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();

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
        /* Obtenemos textos de la sección del Administracion Global 3 */
        $result_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        $result_co = $this->ComiteOrganizadorModel->getComiteOrganizador();
        if (!$result_co['status']) {
            throw new \Exception($result_co['data'], 409);
        }
        $co = $result_co['data'];

        $content = array();
        $content['breadcrumb'] = $breadcrumb;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['co'] = $co;
        $content['section_text'] = $section_text;
        $content['general_text'] = $general_text;
        return $this->render('AdministracionGlobalComiteOrganizadorBundle:ComiteOrganizador:lista_comite_organizador.html.twig', array('content' => $content));
    }

    public function comiteOrganizadorNuevoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "comite_organizador");
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }

        /* Obtenemos textos de la sección del Administracion Global 3 */
        $result_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $this->ComiteOrganizadorModel->trimValues($post);

            /* Verificamos que no exista el nombre del comite organizador */
            $args = array('lower("ComiteOrganizador")' => "'" . mb_strtolower($post['ComiteOrganizador'], 'UTF-8') . "'");
            if ($this->ComiteOrganizadorModel->is_defined($post['idComiteOrganizador'])) {
                $args['idComiteOrganizador'] = array("operator" => "<>", "value" => $post['idComiteOrganizador']);
            }

            $result_co = $this->ComiteOrganizadorModel->getComiteOrganizador($args);
            if (!$result_co['status']) {
                $session->getFlashBag()->add('danger', $result_co['data']);
                return $this->redirectToRoute('comite_organizador');
            }

            if (count($result_co['data']) > 0) {
                $session->getFlashBag()->add('warning', $section_text['sas_COExistente']);
                return $this->redirectToRoute('comite_organizador');
            }

            $result_files = $this->ComiteOrganizadorModel->uploadFiles($_FILES, $general_text);
            if (!$result_files['status']) {
                $session->getFlashBag()->add('warning', $result_files['data']);
                return $this->redirectToRoute('comite_organizador');
            }

            if (count($result_files['data']) > 0) {
                foreach ($result_files['data'] as $key => $value) {
                    $post[$value['field']] = "'" . $value['name'] . "'";
                }
            }

            $post['ComiteOrganizador'] = "'" . $post['ComiteOrganizador'] . "'";
            $result = $this->ComiteOrganizadorModel->insertEditComiteOrganizador($post);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('comite_organizador');
            }

            if (count($result['data']) == 0) {
                $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
                return $this->redirectToRoute('comite_organizador');
            }

            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            return $this->redirectToRoute('comite_organizador');
        }
        $content = array();
        $content['breadcrumb'] = $breadcrumb;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['section_text'] = $section_text;
        $content['general_text'] = $general_text;
        $content['action'] = $this->generateUrl('comite_organizador_nuevo');
        return $this->render('AdministracionGlobalComiteOrganizadorBundle:ComiteOrganizador:show_comite_organizador.html.twig', array('content' => $content));
    }

    public function comiteOrganizadorEditarAction(Request $request, $idComiteOrganizador) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "comite_organizador");
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }

        /* Obtenemos textos de la sección del Administracion Global 3 */
        $result_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $this->ComiteOrganizadorModel->trimValues($post);

            /* Verificamos que no exista el nombre del comite organizador */
            $args = array('lower("ComiteOrganizador")' => "'" . mb_strtolower($post['ComiteOrganizador'], 'UTF-8') . "'");
            if ($this->ComiteOrganizadorModel->is_defined($post['idComiteOrganizador'])) {
                $args['idComiteOrganizador'] = array("operator" => "<>", "value" => $post['idComiteOrganizador']);
            }

            $result_co = $this->ComiteOrganizadorModel->getComiteOrganizador($args);
            if (!$result_co['status']) {
                $session->getFlashBag()->add('danger', $result_co['data']);
                return $this->redirectToRoute('comite_organizador');
            }

            if (count($result_co['data']) > 0) {
                $session->getFlashBag()->add('warning', $section_text['sas_COExistente']);
                return $this->redirectToRoute('comite_organizador');
            }

            $result_files = $this->ComiteOrganizadorModel->uploadFiles($_FILES, $general_text);
            if (!$result_files['status']) {
                $session->getFlashBag()->add('warning', $result_files['data']);
                return $this->redirectToRoute('comite_organizador');
            }

            if (count($result_files['data']) > 0) {
                foreach ($result_files['data'] as $key => $value) {
                    $post[$value['field']] = "'" . $value['name'] . "'";
                }
            }

            $post['ComiteOrganizador'] = "'" . $post['ComiteOrganizador'] . "'";
            $result = $this->ComiteOrganizadorModel->insertEditComiteOrganizador($post);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('comite_organizador');
            }

            if (count($result['data']) == 0) {
                $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
                return $this->redirectToRoute('comite_organizador');
            }

            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            return $this->redirectToRoute('comite_organizador');
        }

        $args = array('idComiteOrganizador' => $idComiteOrganizador);
        $result_co = $this->ComiteOrganizadorModel->getComiteOrganizador($args);
        if (!$result_co['status']) {
            throw new \Exception($result_co['data'], 409);
        }

        if (!isset($result_co['data'][$idComiteOrganizador])) {
            $session->getFlashBag()->add('error', 'Tu peticion para editare el Comite Organizador no ha sido procesada correctamente, intentalo nuevamente');
            $url = $request->headers->get('referer');
            if ($url == "") {
                $url = $this->generateUrl('comite_organizador');
            }
            return $this->redirect($url);
        }

        $co = $result_co['data'][$idComiteOrganizador];

        $content = array();
        $content['breadcrumb'] = $breadcrumb;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['co'] = $co;
        $content['section_text'] = $section_text;
        $content['general_text'] = $general_text;
        $content['action'] = $this->generateUrl('comite_organizador_editar', array('idComiteOrganizador' => $idComiteOrganizador));
        array_push($content["breadcrumb"], Array("Modulo_" . strtoupper($lang) => $content['co']['ComiteOrganizador'], "Ruta" => "", 'Permisos' => array()));
        return $this->render('AdministracionGlobalComiteOrganizadorBundle:ComiteOrganizador:show_comite_organizador.html.twig', array('content' => $content));
    }

    public function comiteOrganizadorEliminarAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];
        $post = $request->request->all();

        if ($post['idComiteOrganizador'] == "") {
            $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
            return $this->redirectToRoute('comite_organizador');
        }

        $args = array('idComiteOrganizador' => $post['idComiteOrganizador']);
        $result = $this->ComiteOrganizadorModel->deleteCO($args);
        if (!$result['status']) {
            $session->getFlashBag()->add('danger', $result['data']);
            return $this->redirectToRoute('comite_organizador');
        }

        $session->getFlashBag()->add('success', $general_text['sas_eliminoExito']);
        return $this->redirectToRoute('comite_organizador');
    }

}
