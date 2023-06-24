<?php

namespace ShowDashboard\AE\Administracion\Configuracion\EstilosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use ShowDashboard\AE\Administracion\Configuracion\EstilosBundle\Model\EstilosModel;
use Utilerias\TextoBundle\Model\TextoModel;

class EstilosController extends Controller {

    protected $EstilosModel;

    const SECTION = 3;

    public function __construct() {
        $this->EstilosModel = new EstilosModel();
        $this->TextoModel = new TextoModel();
    }

    public function estilosAction(Request $request) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');
        $edicion = $session->get('edicion');

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
        $columnasConfiguracion = array('Templates', 'UrlAE', 'UrlAEDev');
        $args = array('idEvento' => $edicion['idEvento'], 'idEdicion' => $edicion['idEdicion']);
        $result_configuracion = $this->EstilosModel->getConfiguracion($columnasConfiguracion, $args);
        if (!$result_configuracion['status']) {
            $session->getFlashBag()->add('danger', $result_configuracion['data']);
            return $this->redirectToRoute('show_dashboard_ae_administracion_configuracion_conexion');
        }
        $configuracion = $result_configuracion['data'][0];
        $configuracion['Templates'] = json_decode($configuracion['Templates'], TRUE);

        /* Si no tiene configurado el template de textos generales, lo regresamos */
        $idTemplateGeneral = $configuracion['Templates']['generales'];
        if ($idTemplateGeneral == "") {
            $session->getFlashBag()->add('info', 'Obligatorio "generales" en la Lista de Templates');
            return $this->redirectToRoute('show_dashboard_ae_administracion_configuracion_conexion');
        }

        /* Traemos los Textos Generales del AE */
        $result_ae_text = $this->EstilosModel->getTemplateTexto();
        if (!$result_ae_text['status']) {
            $session->getFlashBag()->add('danger', $result_ae_text['data']);
            return $this->redirectToRoute('show_dashboard_ae_administracion_configuracion_conexion');
        }
        $ae_text = NULL;
        foreach ($result_ae_text['data'] as $key => $value) {
            $idTemplate = $value['idTemplate'];
            unset($value['idTemplate']);
            if (!isset($ae_text[$idTemplate])) {
                $ae_text[$idTemplate] = array();
            }
            $ae_text[$idTemplate][$value['Etiqueta']] = $value;
        }

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $result = $this->EstilosModel->insertEditEstilos($post, $idTemplateGeneral);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('show_dashboard_ae_administracion_configuracion_estilos');
            }
            /* Si tiene la ruta del AE, eliminamos su cache */
            $this->EstilosModel->deleteCacheAE($configuracion['UrlAEDev'], "textos");
            $this->EstilosModel->deleteCacheAE($configuracion['UrlAE'], "textos");
            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            return $this->redirectToRoute('show_dashboard_ae_administracion_configuracion_estilos');
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

        $content = array();
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['ae_text'] = $ae_text;
        $content['user'] = $user;
        $content['edicion'] = $edicion;
        $content['configuracion'] = $configuracion;
        $content['breadcrumb'] = $breadcrumb;
        return $this->render('ShowDashboardAEAdministracionConfiguracionEstilosBundle:Estilos:showEstilos.html.twig', array('content' => $content));
    }

}
