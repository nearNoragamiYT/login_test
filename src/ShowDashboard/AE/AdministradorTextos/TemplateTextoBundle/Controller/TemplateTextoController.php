<?php

namespace ShowDashboard\AE\AdministradorTextos\TemplateTextoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\AE\AdministradorTextos\TemplateTextoBundle\Model\TemplateTextoModel;
use ShowDashboard\AE\AdministradorTextos\TemplateBundle\Model\TemplateModel;

class TemplateTextoController extends Controller {

    protected $TemplateTextoModel, $TemplateModel, $TextoModel;

    const PLATFORM = 5, SECTION = 1, MAIN_ROUTE = 'show_dashboard_ae_administrador_textos_template_texto';

    public function __construct() {
        $this->TemplateTextoModel = new TemplateTextoModel();
        $this->TemplateModel = new TemplateModel();
        $this->TextoModel = new TextoModel();
    }

    public function templateTextoAction(Request $request) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');

        if (!$session->has('edicion')) {
            return $this->redirectToRoute('dashboard', array('lang' => $lang));
        }
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
            /* Verificamos que no exista el nombre del template */
            $args = array(
                'tt."idTemplate"' => $post['idTemplate'],
                'lower(tt."Etiqueta")' => "'" . strtolower($post['Etiqueta']) . "'",
            );
            if ($this->TemplateTextoModel->is_defined($post['idTemplateTexto'])) {
                $args['tt."idTemplateTexto"'] = array("operator" => "<>", "value" => $post['idTemplateTexto']);
            }
            $result = $this->TemplateTextoModel->getTemplateTexto($args);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template_texto');
            }

            if (count($result['data']) > 0) {
                $session->getFlashBag()->add('warning', $section_text['sas_templateTextoExistente']);
                return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template_texto');
            }

            $post['Etiqueta'] = "'" . $post['Etiqueta'] . "'";
            $post['Texto_ES'] = $post['Texto_ES'] ? "'" . str_replace("'", "''", $post['Texto_ES']) . "'" : "";
            $post['Texto_EN'] = $post['Texto_EN'] ? "'" . str_replace("'", "''", $post['Texto_EN']) . "'" : "";
            $post['Default'] = isset($post['Default']) ? $post['Default'] : "false";
            $result = $this->TemplateTextoModel->insertEditTemplateTexto($post);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template_texto');
            }

            if (count($result['data']) == 0) {
                $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
                return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template_texto');
            }

            $args = array('idEvento' => $edicion['idEvento'], 'idEdicion' => $edicion['idEdicion']);
            $result_configuracion = $this->TemplateTextoModel->getConfiguracion(array("UrlAE", "UrlAEDev"), $args);
            $configuracion = NULL;
            if (count($result_configuracion['data']) > 0) {
                $configuracion = $result_configuracion['data'][0];
            }
            $this->TemplateTextoModel->deleteCacheAE($configuracion['UrlAEDev'], "textos");
            $this->TemplateTextoModel->deleteCacheAE($configuracion['UrlAE'], "textos");

            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template_texto');
        }
        /* Obtenemos los templates del AE */
        $args = array(
            'idEvento' => $edicion['idEvento'],
            'idEdicion' => $edicion['idEdicion'],
        );
        $result_template = $this->TemplateModel->getTemplate($args);
        if (!$result_template['status']) {
            throw new \Exception($result_template['data'], 409);
        }
        $template = $result_template['data'];

        /* Obtenemos los textos de los templates del AE */
        $args = array(
            't."idEvento"' => $edicion['idEvento'],
            't."idEdicion"' => $edicion['idEdicion'],
        );
        $result_templateTexto = $this->TemplateTextoModel->getTemplateTexto($args);
        if (!$result_templateTexto['status']) {
            throw new \Exception($result_templateTexto['data'], 409);
        }
        $templateTexto = $result_templateTexto['data'];

        $content = array();
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['user'] = $user;
        $content['edicion'] = $edicion;
        $content['template'] = $template;
        $content['templateTexto'] = $templateTexto;
        $content['breadcrumb'] = $breadcrumb;
        return $this->render('ShowDashboardAEAdministradorTextosTemplateTextoBundle:TemplateTexto:showTemplateTexto.html.twig', array('content' => $content));
    }

    public function templateTextoEliminarAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];
        $post = $request->request->all();
        if ($post['idTemplateTexto'] == "") {
            $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
            return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template_texto');
        }

        $args = array('idTemplateTexto' => $post['idTemplateTexto']);
        $result = $this->TemplateTextoModel->deleteTemplateTexto($args);
        if (!$result['status']) {
            $session->getFlashBag()->add('danger', $result['data']);
            return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template_texto');
        }

        $args = array('idEvento' => $edicion['idEvento'], 'idEdicion' => $edicion['idEdicion']);
        $result_configuracion = $this->TemplateTextoModel->getConfiguracion(array("UrlAE", "UrlAEDev"), $args);
        $configuracion = NULL;
        if (count($result_configuracion['data']) > 0) {
            $configuracion = $result_configuracion['data'][0];
        }
        $this->TemplateTextoModel->deleteCacheAE($configuracion['UrlAEDev'], "textos");
        $this->TemplateTextoModel->deleteCacheAE($configuracion['UrlAE'], "textos");

        $session->getFlashBag()->add('success', $general_text['sas_eliminoExito']);
        return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template_texto');
    }

}
