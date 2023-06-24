<?php

namespace StatLink\DemographicsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ShowDashboard\DashboardBundle\Model\DashboardModel;
use StatLink\DemographicsBundle\Model\DemographicsModel;
use Utilerias\TextoBundle\Model\TextoModel;

class DemographicsController extends Controller {

    protected $DGModel, $DashboardModel, $TextoModel;

    //const TEMPLATE = 15;
    const MAIN_ROUTE = "stat_link_demographics_list";

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->DashboardModel = new DashboardModel();
        $this->DGModel = new DemographicsModel();
    }

    public function asociadosAction(Request $request) {
        return $this->redirect($request->getBaseUrl() . "/statlink/demographics?encuesta=" . 1);
    }

    public function compradoresAction(Request $request) {
        return $this->redirect($request->getBaseUrl() . "/statlink/demographics?encuesta=" . 2);
    }

    public function listDemographicsAction(Request $request) {
        $this->DGModel = new DemographicsModel($this->container);
        $session = $request->getSession();
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $lang = $session->get('lang');
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $get = $request->query->all();

        $content = array();
        $content['lang'] = $lang;
        $content['user'] = $user;
        $content['idEvento'] = $idEvento;
        $content['idEdicion'] = $idEdicion;

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        $content['breadcrumb'] = $this->DGModel->breadcrumb(self::MAIN_ROUTE, $lang);

//        /* Verificamos si tiene permiso en el modulo seleccionado */
//        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
//        if (!$breadcrumb) {
//            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
//            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
//        }
//        $content["breadcrumb"] = $breadcrumb;        
        $content['parameters'] = $this->DGModel->getParameters();
        $db_name = $content['parameters']['db_name'];

        if (isset($get['encuesta'])) {
            $session->set($idEdicion . '_idEncuesta', $get['encuesta']);
            $args = array("idEdicion" => $idEdicion, "idEvento" => $idEvento, "idEncuesta" => $get['encuesta'], "db_name" => $db_name);
            $solicitudes = $this->DGModel->getSolicitudes($args);
        } else {
            $session->remove($idEdicion . '_idEncuesta');
            $args = array("idEdicion" => $idEdicion, "idEvento" => $idEvento, "db_name" => $db_name);
            $solicitudes = $this->DGModel->getSolicitud($args);
        }

        $content['solicitudes'] = $solicitudes;
        foreach ($content['solicitudes'] as $value) {
            $procesando = $value['status'];
//            print_r($procesando);
//            die('x_x');
            /* --Validación que determina si se puede procesar una nueva solicitud (1) o nó (0)-- */
            if ($procesando == 3 || $procesando == 4) {//Si estatus solicitud Terminado o Erroneo
                $procesando = 0;
            } else {
                $procesando = 1;
                break;
            }
        }
        $content["procesando"] = $procesando;

        /* ---  Modification Request Metadata (para construir la tabla) --- */
        $demographics_metadata = $this->DGModel->getDemograficosMetaData($solicitudes);
        $content["demog_metadata"] = $demographics_metadata;
        //return $this->render('StatLinkDemographicsBundle:Default:index.html.twig',array('content' => $content)); 
        return $this->render('StatLinkDemographicsBundle:Main:dg_list.html.twig', array('content' => $content));
    }

    public function generateDemographicsAction(Request $request) {

        $this->DGModel = new DemographicsModel($this->container);
        $session = $request->getSession();
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $lang = $session->get('lang');
        $user = $profile->getData();
        $post = $request->request->all();

        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $content = Array();
        $content['lang'] = $lang;
        $content['user'] = $user;
        $content['idEvento'] = $idEvento;
        $content['idEdicion'] = $idEdicion;

        $content['parameters'] = $this->DGModel->getParameters();
        $db_name = $content['parameters']['db_name'];

        $args = Array("idEdicion" => $idEdicion, "idEvento" => $idEvento);
        if ($session->has($idEdicion . '_idEncuesta')) {
            $idEncuesta = $session->get($idEdicion . '_idEncuesta');
            $args['db_name'] = $db_name;
            $args['idEncuesta'] = $idEncuesta;
            $result = $this->DGModel->generateSolicitudesDG($args);
            $ultimaSolicitud = $result['data'];
            $solicitudes = $this->DGModel->getSolicitudes($args);
        } else {
            $idEncuesta = $this->DGModel->getEncuesta($args);
            $idEncuesta = $idEncuesta[0]['idEncuesta'];
            $args['db_name'] = $db_name;
            $result = $this->DGModel->generateSolicitudDG($args);
            $ultimaSolicitud = $result['data'];
            $solicitudes = $this->DGModel->getSolicitud($args);
        }

        $args = Array("idEdicion" => $idEdicion, "idEvento" => $idEvento, "idEncuesta" => $idEncuesta);
        $totalRegistros = $this->DGModel->getTotalRegistros($args);
        $totalRegistros = $totalRegistros[0]['count'];

        //calculo
        $tiempoUltimaSolicitud = $ultimaSolicitud[0]['segundosEjecucion'];
        $registrosUltimaSolicitud = $ultimaSolicitud[0]['totalFilas'];
        $tiempoNuevaSolicitud = ($totalRegistros * $tiempoUltimaSolicitud) / $registrosUltimaSolicitud;
        $tiempoNuevaSolicitud = $tiempoNuevaSolicitud + 60;

        
        reset($solicitudes);
        $nuevaSolicitud = current($solicitudes);
        if ($result['status']) {
            $data = Array("tiempoNuevaSolicitud" => $tiempoNuevaSolicitud, "datosNuevaSolicitud" => $nuevaSolicitud);
            $result = Array("status" => true, "data" => $data);
        } else {
            $result = Array("status" => false, "error" => $result['data']);
        }
        return $this->jsonResponse($result);
    }

    public function downloadDemographicsAction(Request $request) {
        $path_download_directory = "/var/docs/";
        $post = $request->request->all();
        $filename = $post['filename'];
        $absolute_path = "" . $path_download_directory . $filename . "";
        $result = Array("status" => true, "data" => $absolute_path);
        return $this->jsonResponse($result);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
