<?php

namespace ShowDashboard\RS\EstadisticasRSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ShowDashboard\RS\EstadisticasRSBundle\Model\EstadisticasModel;
use AdministracionGlobal\UsuarioBundle\Model\UsuarioModel;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use Utilerias\TextoBundle\Model\TextoModel;

class EstadisticasController extends Controller {

    protected $EstadisticasModel, $UsuarioModel, $TextoModel, $App;

    public function __construct() {

//        $session = $request->getSession();
//        $lang = $session->get('lang');
//        $content = array();
//        $idEdicion = $session->get('idEdicion');
//        $idEvento = $session->get('idEvento')

        $ConfigurationModel = new ConfigurationModel();
        $this->App = $ConfigurationModel->getApp();
        $this->EstadisticasModel = new EstadisticasModel();
        $this->UsuarioModel = new UsuarioModel();
        $this->TextoModel = new TextoModel();
    }

    public function estadisticasAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');

        $content = array();
        $content['App'] = $App;
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $idEdicion;

        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content["breadcrumb"] = $breadcrumb;

        return $this->render('ShowDashboardRSEstadisticasRSBundle:Default:EstadisticasRS.html.twig', array('content' => $content));
    }

    public function asistenciadiaAction(Request $request) {

        $session = $request->getSession();
        $idEdicion = $session->get('idEdicion');

        $result_config = $this->EstadisticasModel->getAsistenciaDia($idEdicion);
        if (!$result_config['status']) {
            throw new \Exception($result_config['data'], 409);
        }
        return $this->jsonResponse($result_config[data]);
    }

    public function asistenciahoraAction(Request $request) {

        $session = $request->getSession();
        $idEdicion = $session->get('idEdicion');

        /* Obtenemos los datos de servicios de AE */
        $result_config = $this->EstadisticasModel->getAsistenciaHora($idEdicion);
        if (!$result_config['status']) {
            throw new \Exception($result_config['data'], 409);
        }
        return $this->jsonResponse($result_config[data]);
    }

    public function comparacionasistenciaAction(Request $request) {

        $session = $request->getSession();
        $idEdicion = $session->get('idEdicion');

        /* Obtenemos los datos de servicios de AE */
        $result_config = $this->EstadisticasModel->getComparacionAsistencia($idEdicion);
        if (!$result_config['status']) {
            throw new \Exception($result_config['data'], 409);
        }
        return $this->jsonResponse($result_config[data]);
    }
    
    /*public function clubEliteAction(Request $request) {

        $session = $request->getSession();
        $idEdicion = $session->get('idEdicion');

        Obtenemos los datos de servicios de AE 
        $result_config = $this->EstadisticasModel->getClubElite($idEdicion);
        if (!$result_config['status']) {
            throw new \Exception($result_config['data'], 409);
        }
        return $this->jsonResponse($result_config[data]);
    }*/

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
