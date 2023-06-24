<?php

namespace ShowDashboard\AE\Administracion\Configuracion\AjustesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\AE\Administracion\Configuracion\AjustesBundle\Model\AjustesModel;
use ShowDashboard\AE\AdministradorTextos\TemplateTextoBundle\Model\TemplateTextoModel;

class AjustesController extends Controller {

    protected $AjustesModel, $TextoModel, $TemplateTextoModel;

    const SECTION = 3;

    public function __construct() {
        $this->AjustesModel = new AjustesModel();
        $this->TextoModel = new TextoModel();
        $this->TemplateTextoModel = new TemplateTextoModel();
    }

    public function ajustesAction(Request $request) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');
        $edicion = $session->get('edicion');
        //$session->remove('idPlataformaIxpo', 2);

        /* Si no tiene una edicion seteada, lo regresamos al dashboard  */
        if (count($edicion) == 0) {
            return $this->redirectToRoute('dashboard', array("lang" => $lang));
        }

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "show_dashboard_ae_administracion_configuracion_ajustes");
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
        /* Traemos la lista de campos de la configuracion */
        $result_columnasConfiguracion = $this->AjustesModel->getColumnasConfiguracion();
        if (!$result_columnasConfiguracion['status']) {
            throw new \Exception($result_columnasConfiguracion['data'], 409);
        }

        $configuracion = NULL;
        $columnasConfiguracion = NULL;
        /* Traemos la configuracion del AE */
        if (count($result_columnasConfiguracion['data']) > 0) {
            $columnasConfiguracion = $result_columnasConfiguracion['data'];

            $key = array_search("jsonSync", $columnasConfiguracion);
            if (false !== $key) {
                unset($columnasConfiguracion[$key]);
            }
            $args = array('idEvento' => $edicion['idEvento'], 'idEdicion' => $edicion['idEdicion']);
            $result_configuracion = $this->AjustesModel->getConfiguracion($columnasConfiguracion, $args);
            if (!$result_configuracion['status']) {
                throw new \Exception($result_configuracion['data'], 409);
            }
            if (count($result_configuracion['data']) > 0) {
                $configuracion = $result_configuracion['data'][0];
                $configuracion['ListaEmail'] = json_decode($configuracion['ListaEmail'], TRUE);
                $configuracion['Templates'] = json_decode($configuracion['Templates'], TRUE);
            }
        }

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $listaEmail = $this->AjustesModel->formatJSONLista($post['ListaEmail'], "email");
            $post['ListaEmail'] = ($listaEmail) ? "'" . $listaEmail . "'" : "";
            $templates = $this->AjustesModel->formatJSONLista($post['Templates'], "idTemplate");
            $post['Templates'] = ($templates) ? "'" . $templates . "'" : "";
            $post['CierreAutomaticoFecha'] = ($post['CierreAutomaticoFecha'] != "") ? "'" . $post['CierreAutomaticoFecha'] . "'" : "";
            $post['GoogleAnalyticsTracking'] = ($post['GoogleAnalyticsTracking'] != "") ? "$$" . $post['GoogleAnalyticsTracking'] . "$$" : "";
            $post['Salt'] = ($post['Salt'] != "") ? "'" . $post['Salt'] . "'" : "";
            $post['UrlSAS'] = ($post['UrlSAS'] != "") ? "'" . $post['UrlSAS'] . "'" : "";
            $post['UrlAE'] = ($post['UrlAE'] != "") ? "'" . $post['UrlAE'] . "'" : "";
            $post['UrlAEDev'] = ($post['UrlAEDev'] != "") ? "'" . $post['UrlAEDev'] . "'" : "";
            foreach ($columnasConfiguracion as $field) {
                $post[$field] = (isset($post[$field])) ? $post[$field] : "false";
            }
            $result = $this->AjustesModel->insertEditConfiguracion($post);
            if (!$result['status']) {
                throw new \Exception($result['data'], 409);
            }
            /* Si tiene la ruta del AE, eliminamos su cache */
            $this->AjustesModel->deleteCacheAE($configuracion['UrlAEDev']);
            $this->AjustesModel->deleteCacheAE($configuracion['UrlAE']);
            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            return $this->redirectToRoute('show_dashboard_ae_administracion_configuracion_ajustes');
        }
        $breadcrumb[] = array(
            'Ruta' => "",
            'Modulo_' . strtoupper($lang) => $section_text['sas_configuracion'] . " AE",
            'Permisos' => array(
                "Ver" => TRUE,
                "Editar" => TRUE,
                "Borrar" => TRUE,
            ),
        );

        $args = array('idEvento' => $edicion['idEvento'], 'idEdicion' => $edicion['idEdicion']);
        $result_templates = $this->AjustesModel->getTemplate($args);
        if (!$result_templates['status']) {
            throw new \Exception($result_templates['data'], 409);
        }
        $templates = $result_templates['data'];

        $content = array();
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['user'] = $user;
        $content['edicion'] = $edicion;
        $content['columnasConfiguracion'] = $columnasConfiguracion;
        $content['configuracion'] = $configuracion;
        $content['templates'] = $templates;
        $content['breadcrumb'] = $breadcrumb;
        return $this->render('ShowDashboardAEAdministracionConfiguracionAjustesBundle:Ajustes:showAjustes.html.twig', array('content' => $content));
    }

    public function ajustesEliminarAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        $post = $request->request->all();
        if ($post['idConfiguracion'] == "") {
            $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
            return $this->redirectToRoute('show_dashboard_ae_administracion_configuracion_ajustes');
        }

        $result = $this->AjustesModel->deleteConfiguracion($post);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }

        $session->getFlashBag()->add('success', $general_text['sas_eliminoExito']);
        return $this->redirectToRoute('show_dashboard_ae_administracion_configuracion_ajustes');
    }

}
