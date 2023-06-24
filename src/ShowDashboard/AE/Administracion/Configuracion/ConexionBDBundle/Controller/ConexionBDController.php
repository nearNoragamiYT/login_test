<?php

namespace ShowDashboard\AE\Administracion\Configuracion\ConexionBDBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use ShowDashboard\AE\Administracion\Configuracion\ConexionBDBundle\Model\ConexionBDModel;
use Utilerias\TextoBundle\Model\TextoModel;

class ConexionBDController extends Controller {

    protected $ConexionBDModel;

    const SECTION = 3;

    public function __construct() {
        $this->ConexionBDModel = new ConexionBDModel();
        $this->TextoModel = new TextoModel();
    }

    public function conexionBDAction(Request $request) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');
        $edicion = $session->get('edicion');
        //$session->set('idPlataformaIxpo', 2);

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

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $data = array();
            $data['idConexion'] = $post['idConexion'];
            $data['idEdicion'] = $post['idEdicion'];
            $data['idEvento'] = $post['idEvento'];
            $data['Nombre'] = "'" . $post['Nombre'] . "'";
            $data['Servidor'] = "'" . $post['Servidor'] . "'";
            $data['Base'] = "'" . $post['Base'] . "'";
            $data['Usuario'] = "'" . $post['Usuario'] . "'";
            $data['Password'] = "'" . $post['Password'] . "'";
            $data['Puerto'] = "'" . $post['Puerto'] . "'";
            $data['Soap'] = "'" . $post['Soap'] . "'";
            $result = $this->ConexionBDModel->insertEditConexion($data);
            if (!$result['status']) {
                throw new \Exception($result['data'], 409);
            }
            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            return $this->redirectToRoute('show_dashboard_ae_administracion_configuracion_conexion');
        }

        /* Obtenemos los datos de conexion a la base del AE */
        $args = array('idEvento' => $edicion['idEvento'], 'idEdicion' => $edicion['idEdicion']);
        $result_conexion = $this->ConexionBDModel->getConexionAE($args);
        if (!$result_conexion['status']) {
            throw new \Exception($result_conexion['data'], 409);
        }

        $conexion = NULL;
        if (count($result_conexion['data']) > 0) {
            $conexion = $result_conexion['data'][0];
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
        $content['user'] = $user;
        $content['edicion'] = $edicion;
        $content['conexion'] = $conexion;
        $content['breadcrumb'] = $breadcrumb;
        return $this->render('ShowDashboardAEAdministracionConfiguracionConexionBDBundle:ConexionBD:showConexionBD.html.twig', array('content' => $content));
    }

    public function conexionBDEliminarAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        $post = $request->request->all();
        if ($post['idConexion'] == "") {
            $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
            return $this->redirectToRoute('show_dashboard_ae_administracion_configuracion_conexion');
        }
        $result = $this->ConexionBDModel->deleteConexionAE($post);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }

        $session->getFlashBag()->add('success', $general_text['sas_eliminoExito']);
        return $this->redirectToRoute('show_dashboard_ae_administracion_configuracion_conexion');
    }

}
