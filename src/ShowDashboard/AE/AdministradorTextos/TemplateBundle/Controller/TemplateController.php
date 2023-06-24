<?php

namespace ShowDashboard\AE\AdministradorTextos\TemplateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\AE\AdministradorTextos\TemplateBundle\Model\TemplateModel;

class TemplateController extends Controller {

    protected $TemplateModel, $TextoModel;

    const PLATFORM = 5, SECTION = 1, MAIN_ROUTE = 'show_dashboard_ae_administrador_textos_template';

    public function __construct() {
        $this->TemplateModel = new TemplateModel();
        $this->TextoModel = new TextoModel();
    }

    public function templateAction(Request $request) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');

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
            /* Verificamos que no exista el nombre del template en la edicion */
            $args = array(
                'idEvento' => $edicion['idEvento'],
                'idEdicion' => $edicion['idEdicion'],
                'lower("Template")' => "'" . mb_strtolower($post['Template'], 'UTF-8') . "'"
            );
            if ($this->TemplateModel->is_defined($post['idTemplate'])) {
                $args['idTemplate'] = array("operator" => "<>", "value" => $post['idTemplate']);
            }
            $result = $this->TemplateModel->getTemplate($args);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template');
            }

            if (count($result['data']) > 0) {
                $session->getFlashBag()->add('warning', $section_text['sas_templateExistente']);
                return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template');
            }

            $post['Template'] = "'" . $post['Template'] . "'";
            $result = $this->TemplateModel->insertEditTemplate($post);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template');
            }

            if (count($result['data']) == 0) {
                $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
                return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template');
            }

            $args = array('idEvento' => $edicion['idEvento'], 'idEdicion' => $edicion['idEdicion']);
            $result_configuracion = $this->TemplateModel->getConfiguracion(array("UrlAE", "UrlAEDev"), $args);
            $configuracion = NULL;
            if (count($result_configuracion['data']) > 0) {
                $configuracion = $result_configuracion['data'][0];
            }
            $this->TemplateModel->deleteCacheAE($configuracion['UrlAEDev']);
            $this->TemplateModel->deleteCacheAE($configuracion['UrlAE']);
            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template');
        }

        /* Obtenemos los productos de la edicion */
        $args = array('epi."idEdicion"' => $edicion['idEdicion']);
        $result_productoEdicion = $this->TemplateModel->getProductoEdicion($args);
        if (!$result_productoEdicion['status']) {
            throw new \Exception($result_productoEdicion['data'], 409);
        }
        $productoEdicion = $result_productoEdicion['data'];

        /* Obtenemos los modulos del AE */
        $args = array('"idPlataformaIxpo"' => 2);
        $result_modulo = $this->TemplateModel->getModulo($args);
        if (!$result_modulo['status']) {
            throw new \Exception($result_modulo['data'], 409);
        }

        $modulo = array();
        if (count($result_modulo['data']) > 0) {
            foreach ($result_modulo['data'] as $key => $value) {
                $modulo[$value['idModuloIxpo']] = $value;
            }
        }

        /* Obtenemos los modulos del AE */
        $args = array(
            'idEvento' => $edicion['idEvento'],
            'idEdicion' => $edicion['idEdicion'],
        );
        $result_template = $this->TemplateModel->getTemplate($args);
        if (!$result_template['status']) {
            throw new \Exception($result_template['data'], 409);
        }
        $template = $result_template['data'];

        /* Obtenemos los tipos de visitante del AE */
        $result_visitanteTipo = $this->TemplateModel->getVisitanteTipo();
        if (!$result_visitanteTipo['status']) {
            throw new \Exception($result_visitanteTipo['data'], 409);
        }
        $visitanteTipo = $result_visitanteTipo['data'];

        $content = array();
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['idSeccion'] = self::SECTION;
        $content['user'] = $user;
        $content['edicion'] = $edicion;
        $content['productoEdicion'] = $productoEdicion;
        $content['modulo'] = $modulo;
        $content['template'] = $template;
        $content['visitanteTipo'] = $visitanteTipo;
        $content['breadcrumb'] = $breadcrumb;
        return $this->render('ShowDashboardAEAdministradorTextosTemplateBundle:Template:showTemplate.html.twig', array('content' => $content));
    }

    public function templateEliminarAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];
        $post = $request->request->all();
        if ($post['idTemplate'] == "") {
            $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
            return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template');
        }

        $args = array('idTemplate' => $post['idTemplate']);
        $result = $this->TemplateModel->deleteTemplate($args);
        if (!$result['status']) {
            $session->getFlashBag()->add('danger', $result['data']);
            return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template');
        }

        $session->getFlashBag()->add('success', $general_text['sas_eliminoExito']);
        return $this->redirectToRoute('show_dashboard_ae_administrador_textos_template');
    }

}
