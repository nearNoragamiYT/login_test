<?php

namespace ShowDashboard\AE\Administracion\ConfiguracionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\AE\Administracion\ConfiguracionBundle\Model\ConfiguracionModel;
use ShowDashboard\AE\AdministradorTextos\TemplateTextoBundle\Model\TemplateTextoModel;

class ConfiguracionController extends Controller {

    protected $ConfiguracionModel, $TextoModel, $TemplateTextoModel;

    const SECTION = 3;

    public function __construct() {
        $this->ConfiguracionModel = new ConfiguracionModel();
        $this->TextoModel = new TextoModel();
        $this->TemplateTextoModel = new TemplateTextoModel();
    }

    public function configuracionAction(Request $request) {
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
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "show_dashboard_ae_administracion_configuracion");
        /* if (!$breadcrumb) {
          $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
          return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
          } */

        /* Obtenemos textos de la sección del Administracion Global 3 */
        $result_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];
        /* Traemos la lista de campos de la configuracion */
        $result_columnasConfiguracion = $this->ConfiguracionModel->getColumnasConfiguracion();
        if (!$result_columnasConfiguracion['status']) {
            throw new \Exception($result_columnasConfiguracion['data'], 409);
        }

        $configuracion = NULL;
        $columnasConfiguracion = NULL;
        /* Traemos la configuracion del AE */
        if (count($result_columnasConfiguracion['data']) > 0) {
            $columnasConfiguracion = $result_columnasConfiguracion['data'];
            $args = array('idEvento' => $edicion['idEvento'], 'idEdicion' => $edicion['idEdicion']);
            $result_configuracion = $this->ConfiguracionModel->getConfiguracion($columnasConfiguracion, $args);
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
            $listaEmail = $this->ConfiguracionModel->formatJSONLista($post['ListaEmail'], "email");
            $post['ListaEmail'] = ($listaEmail) ? "'" . $listaEmail . "'" : "";
            $templates = $this->ConfiguracionModel->formatJSONLista($post['Templates'], "idTemplate");
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
            $result = $this->ConfiguracionModel->insertEditConfiguracion($post);
            if (!$result['status']) {
                throw new \Exception($result['data'], 409);
            }
            /* Si tiene la ruta del AE, eliminamos su cache */
            $this->ConfiguracionModel->deleteCacheAE($configuracion['UrlAEDev']);
            $this->ConfiguracionModel->deleteCacheAE($configuracion['UrlAE']);
            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            return $this->redirectToRoute('show_dashboard_ae_administracion_configuracion');
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
        $result_templates = $this->ConfiguracionModel->getTemplate($args);
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
        return $this->render('ShowDashboardAEAdministracionConfiguracionBundle:Configuracion:showAjustes.html.twig', array("content" => $content));
    }

    public function configuracionEliminarAction(Request $request) {
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
            return $this->redirectToRoute('show_dashboard_ae_administracion_configuracion');
        }

        $result = $this->ConfiguracionModel->deleteConfiguracion($post);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }

        $session->getFlashBag()->add('success', $general_text['sas_eliminoExito']);
        return $this->redirectToRoute('show_dashboard_ae_administracion_configuracion');
    }

    public function guardarHTMLAction(Request $request, $lang) {
        $session = $request->getSession();
        $post = $request->request->all();
        //$lang = $session->get('lang');
        $edicion = $session->get('edicion');

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Obtenemos los textos de la plantilla para ver si lo insertamos o editamos */
        $args = array(
            'idTemplate' => $post['idTemplate'],
            'Etiqueta' => "'" . $post['Etiqueta'] . "'",
        );
        $result_templateTexto = $this->TemplateTextoModel->getTemplateTexto($args, TRUE);
        if (!$result_templateTexto['status']) {
            throw new \Exception($result_templateTexto['data'], 409);
        }
        $templateTexto = $result_templateTexto['data'];
        $data = $args;
        $data['Etiqueta'] = $post['Etiqueta'];
        $data['Texto_ES'] = (isset($templateTexto[$post['Etiqueta']])) ? $templateTexto[$post['Etiqueta']]['Texto_ES'] : "";
        $data['Texto_EN'] = (isset($templateTexto[$post['Etiqueta']])) ? $templateTexto[$post['Etiqueta']]['Texto_EN'] : "";
        $data['Texto_' . strtoupper($lang)] = $post['Texto'];
        $result = $this->TemplateTextoModel->fn_insertEditTemplateTextos($data);
        if (!$result['status']) {
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        if (count($result['data']) == 0) {
            $result = array('status' => FALSE, 'data' => $general_text['sas_errorPeticion']);
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function guardarImagenAction(Request $request, $lang) {
        $session = $request->getSession();
        $edicion = $session->get('edicion');

        $post = $request->request->all();
        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);

        if (!$result_general_text['status']) {
            $response = new Response(json_encode($result_general_text));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $general_text = $result_general_text['data'];

        $result_files = $this->ConfiguracionModel->uploadFiles($_FILES, $general_text, "../sponsor/ae/");
        if (!($result_files['status'] && count($result_files['data']) > 0)) {
            $response = new Response(json_encode($result_files));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        foreach ($result_files['data'] as $key => $value) {
            $args = array(
                'idTemplate' => $post['idTemplate'],
                'Etiqueta' => "'" . $value['field'] . "'",
            );
            $result_templateTexto = $this->TemplateTextoModel->getTemplateTexto($args, TRUE);
            if (!$result_templateTexto['status']) {
                throw new \Exception($result_templateTexto['data'], 409);
            }
            $templateTexto = $result_templateTexto['data'];
            $data = $args;
            $data['Etiqueta'] = $value['field'];
            $data['Texto_ES'] = (isset($templateTexto[$value['field']])) ? $templateTexto[$value['field']]['Texto_ES'] : "";
            $data['Texto_EN'] = (isset($templateTexto[$value['field']])) ? $templateTexto[$value['field']]['Texto_EN'] : "";
            $data['Texto_' . strtoupper($lang)] = $value['name'];
            $result = $this->TemplateTextoModel->fn_insertEditTemplateTextos($data);

            if (!$result['status']) {
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            if (count($result['data']) == 0) {
                $result = array('status' => FALSE, 'data' => $general_text['sas_errorPeticion']);
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
        }

        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
