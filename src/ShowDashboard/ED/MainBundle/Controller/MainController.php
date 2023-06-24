<?php

namespace ShowDashboard\ED\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\DashboardBundle\Model\DashboardModel;
use ShowDashboard\ED\MainBundle\Model\MainModel;

class MainController extends Controller {

    protected $TextoModel, $model, $DashboardModel, $idPlataformaIxpo = 4;

    public function __construct() {
        date_default_timezone_set("America/Mexico_City");
        $this->TextoModel = new TextoModel();
        $this->DashboardModel = new DashboardModel();
        $this->model = new MainModel();
    }

    const SECTION = 7;

    public function mostrarAction(Request $request) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');
        $edicion = $session->get('edicion');
        $content = array();
        $content['user'] = $user;
        $session->set('idPlataformaIxpo', $this->idPlataformaIxpo);
        /* Verificamos si tiene permiso sobre la plataforma en la edicion seleccionada /
          if (!$this->DashboardModel->verificarPermisoPlataforma($request, $this->idPlataformaIxpo)) {
          $result_general_text = $this->TextoModel->getTexts($lang);
          if (!$result_general_text['status']) {
          throw new \Exception($result_general_text['data'], 409);
          }
          $session->remove('idPlataformaIxpo');
          $session->getFlashBag()->add('warning', $general_text['sas_plataformaNoDisponible']);
          return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $edicion['idEdicion'], 'lang' => $lang));
          }
          /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];
        $content['general_text'] = $general_text;
        /* Obtenemos textos de la sección del ED 4 */
        $result_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];
        $content['section_text'] = $section_text;
        /* ---  verificamos si tiene acceso al módulo  --- /
          $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
          if (!$breadcrumb) {
          $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
          return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
          }
          //return $this->redirectToRoute('empresa_ventas');
          /* ---  logica del action para mostrar las estadísticas en el dashboard  --- */
        $time = $session->get("StatsTime");
        if (!$time || $time + (10 * 60) < time()) {
            $session->set("StatsTime", time());
            $args = Array("idEvento" => $edicion['idEvento'], "idEdicion" => $edicion['idEdicion']);
            $stats = $this->getEstadisticas($args, $lang);
            $session->set("Estadisticas", $stats);
        }
        return $this->render('ShowDashboardEDMainBundle:Main:mostrar.html.twig', array('content' => $content));
    }

    public function getEstadisticas($where, $lang) {
        $expositores = $this->model->getExpositoresDetalle($where);
        $exhibitorDashboard = $this->model->getExhibitorDashboardDetalles($where);
        $marketingFerial = $this->model->getMarketingferial($where, $lang);
        $floorPlan = $this->model->getFloorPlan($where, $lang);
        return Array(
            "Exhibitors" => $expositores,
            "ED" => $exhibitorDashboard,
            "MKF" => $marketingFerial,
            "FP" => $floorPlan
        );
    }

    public function actualizarClientesAction(Request $request) {
        $session = $request->getSession();
        $content = array();
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        $where = Array("idEvento" => $content['idEvento'], "idEdicion" => $content['idEdicion']);
        $totalC = $this->model->getClientes($where);
        $totalE = $this->model->getExpositores($where);
        $data = Array(
            "TotalClientes" => $totalC,
            "TotalContratos" => $totalE,
            "TotalPrecontratos" => $totalC - $totalE
        );
        $stats = $session->get("Estadisticas");
        $update = array_merge($stats['Exhibitors'], $data);
        $stats['Exhibitors'] = $update;
        $session->set("Estadisticas", $stats);
        /* ---  el status nos sirve para saber que hacer a la respuesta del ajax ya que solo hay uno para todo y el json para actualizar el json en js con el data --- */
        $response = Array("status" => 'clients', 'json' => "Exhibitors", "data" => $data);
        return new JsonResponse($response);
    }

    public function actualizarExpositoresAction(Request $request) {
        $session = $request->getSession();
        $content = array();
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        $where = Array("idEvento" => $content['idEvento'], "idEdicion" => $content['idEdicion']);
        $totalE = $this->model->getExpositores($where);
        $totalC = $this->model->getCoexpositores($where);
        $data = Array(
            "TotalED" => $totalE,
            "TotalCoexpositores" => $totalC,
            "TotalExpositores" => $totalE - $totalC
        );
        $stats = $session->get("Estadisticas");
        $update = array_merge($stats['Exhibitors'], $data);
        $stats['Exhibitors'] = $update;
        $session->set("Estadisticas", $stats);
        /* ---  el status nos sirve para saber que hacer a la respuesta del ajax ya que solo hay uno para todo y el json para actualizar el json en js con el data --- */
        $response = Array("status" => 'exhibitors', 'json' => "Exhibitors", "data" => $data);
        return new JsonResponse($response);
    }

    public function actualizarExpositoresEntraronAction(Request $request) {
        $session = $request->getSession();
        $content = array();
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        $where = Array("idEvento" => $content['idEvento'], "idEdicion" => $content['idEdicion']);
        $totalE = $this->model->getExpositores($where);
        $totalEE = $this->model->getExpositoresEntraron($where);
        $data = Array(
            "TotalED" => $totalE,
            "TotalExpositoresEntraron" => $totalEE,
            "TotalExpositoresNoEntraron" => $totalE - $totalEE
        );
        $stats = $session->get("Estadisticas");
        $update = array_merge($stats['Exhibitors'], $data);
        $stats['Exhibitors'] = $update;
        $session->set("Estadisticas", $stats);
        /* ---  el status nos sirve para saber que hacer a la respuesta del ajax ya que solo hay uno para todo y el json para actualizar el json en js con el data --- */
        $response = Array("status" => 'singup', 'json' => "Exhibitors", "data" => $data);
        return new JsonResponse($response);
    }

    public function actualizarTodasFormasAction(Request $request) {
        $session = $request->getSession();
        $content = array();
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        $where = Array("idEvento" => $content['idEvento'], "idEdicion" => $content['idEdicion']);
        $totalF = $this->model->getFormasManual($where);
        $totalE = $this->model->getExpositores($where);
        $totalFC = $this->model->getFormasCompletas($where);
        $totalFSI = $this->model->getFormasSinInteres($where);
        $data = Array(
            "TotalFormasManual" => $totalF,
            "TotalExpositores" => $totalE,
            "TotalFormasCompletas" => $totalFC,
            "TotalFormasSinInteres" => $totalFSI,
            "TotalFormasPendientes" => ($totalE * $totalF) - ($totalFC + $totalFSI)
        );
        $stats = $session->get("Estadisticas");
        $update = array_merge($stats['ED'], $data);
        $stats['ED'] = $update;
        $session->set("Estadisticas", $stats);
        /* ---  el status nos sirve para saber que hacer a la respuesta del ajax ya que solo hay uno para todo y el json para actualizar el json en js con el data --- */
        $response = Array("status" => 'forms', 'json' => "ED", "data" => $data);
        return new JsonResponse($response);
    }

    public function actualizarGafetesAction(Request $request) {
        $session = $request->getSession();
        $content = array();
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        $where = Array("idEvento" => $content['idEvento'], "idEdicion" => $content['idEdicion']);
        $totalG = $this->model->getGafetes($where);
        $data = Array(
            "TotalGafetes" => $totalG
        );
        $stats = $session->get("Estadisticas");
        $update = array_merge($stats['ED'], $data);
        $stats['ED'] = $update;
        $session->set("Estadisticas", $stats);
        /* ---  el status nos sirve para saber que hacer a la respuesta del ajax ya que solo hay uno para todo y el json para actualizar el json en js con el data --- */
        $response = Array("status" => 'badges', 'json' => "ED", "data" => $data);
        return new JsonResponse($response);
    }

    public function actualizarLectorasAction(Request $request) {
        $session = $request->getSession();
        $content = array();
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        $where = Array("idEvento" => $content['idEvento'], "idEdicion" => $content['idEdicion']);
        $totalL = $this->model->getLectoras($where);
        $data = Array(
            "TotalLectoras" => $totalL
        );
        $stats = $session->get("Estadisticas");
        $update = array_merge($stats['ED'], $data);
        $stats['ED'] = $update;
        $session->set("Estadisticas", $stats);
        /* ---  el status nos sirve para saber que hacer a la respuesta del ajax ya que solo hay uno para todo y el json para actualizar el json en js con el data --- */
        $response = Array("status" => 'retrievals', 'json' => "ED", "data" => $data);
        return new JsonResponse($response);
    }

    public function actualizarFormasObligatoriasAction(Request $request) {
        $session = $request->getSession();
        $content = array();
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        $where = Array("idEvento" => $content['idEvento'], "idEdicion" => $content['idEdicion']);
        $totalE = $this->model->getExpositores($where);
        $totalFMO = $this->model->getFormasManual($where, TRUE);
        $totalFCO = $this->model->getFormasObligatoriasCompletas($where);
        $data = Array(
            "TotalFormasObligatorias" => $totalFMO,
            "TotalFormasObligatoriasCompletas" => $totalFCO,
            "TotalFormasObligatoriasPendientes" => ($totalE * $totalFMO) - $totalFCO
        );
        $stats = $session->get("Estadisticas");
        $update = array_merge($stats['ED'], $data);
        $stats['ED'] = $update;
        $session->set("Estadisticas", $stats);
        /* ---  el status nos sirve para saber que hacer a la respuesta del ajax ya que solo hay uno para todo y el json para actualizar el json en js con el data --- */
        $response = Array("status" => 'obligatories', 'json' => "ED", "data" => $data);
        return new JsonResponse($response);
    }

    public function actualizarPaquetesAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $content = array();
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        $where = Array("idEvento" => $content['idEvento'], "idEdicion" => $content['idEdicion']);
        $totalP = $this->model->getPaquetes($where, $lang);
        $data = Array(
            "TotalPaquetes" => $totalP
        );
        $stats = $session->get("Estadisticas");
        $update = array_merge($stats['MKF'], $data);
        $stats['MKF'] = $update;
        $session->set("Estadisticas", $stats);
        /* ---  el status nos sirve para saber que hacer a la respuesta del ajax ya que solo hay uno para todo y el json para actualizar el json en js con el data --- */
        $response = Array("status" => 'packages', 'json' => "MKF", "data" => $data);
        return new JsonResponse($response);
    }

    public function actualizarSolicitudesPaquetesAction(Request $request) {
        $session = $request->getSession();
        $content = array();
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        $where = Array("idEvento" => $content['idEvento'], "idEdicion" => $content['idEdicion']);
        //$totalSP = $this->model->getSolicitudesPaquetes($where);
        $data = Array(
            "TotalPaquetesSolicitados" => $totalSP
        );
        $stats = $session->get("Estadisticas");
        $update = array_merge($stats['MKF'], $data);
        $stats['MKF'] = $update;
        $session->set("Estadisticas", $stats);
        /* ---  el status nos sirve para saber que hacer a la respuesta del ajax ya que solo hay uno para todo y el json para actualizar el json en js con el data --- */
        $response = Array("status" => 'mkf-requests', 'json' => "MKF", "data" => $data);
        return new JsonResponse($response);
    }

    public function actualizarEstatusStandsAction(Request $request) {
        $session = $request->getSession();
        $content = array();
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        $where = Array("idEvento" => $content['idEvento'], "idEdicion" => $content['idEdicion']);
        $totalSP = $this->model->getEstatusStands($where);
        $data = Array(
            "TotalEstatusStands" => $totalSP
        );
        $stats = $session->get("Estadisticas");
        $update = array_merge($stats['FP'], $data);
        $stats['FP'] = $update;
        $session->set("Estadisticas", $stats);
        /* ---  el status nos sirve para saber que hacer a la respuesta del ajax ya que solo hay uno para todo y el json para actualizar el json en js con el data --- */
        $response = Array("status" => 'status-stands', 'json' => "FP", "data" => $data);
        return new JsonResponse($response);
    }

    public function actualizarSolicitudesModificacionAction(Request $request) {
        $session = $request->getSession();
        $content = array();
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        $where = Array("idEvento" => $content['idEvento'], "idEdicion" => $content['idEdicion']);
        $totalSP = $this->model->getSolicitudesModificacion($where);
        $data = Array(
            "TotalSolicitudesModificacion" => $totalSP
        );
        $stats = $session->get("Estadisticas");
        $update = array_merge($stats['FP'], $data);
        $stats['FP'] = $update;
        $session->set("Estadisticas", $stats);
        /* ---  el status nos sirve para saber que hacer a la respuesta del ajax ya que solo hay uno para todo y el json para actualizar el json en js con el data --- */
        $response = Array("status" => 'fp-modifications', 'json' => "FP", "data" => $data);
        return new JsonResponse($response);
    }

    public function actualizarEstadisticasAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $content = array();
        $content['idEdicion'] = $session->get('idEdicion');
        $content['idEvento'] = $session->get('idEvento');
        $where = Array("idEvento" => $content['idEvento'], "idEdicion" => $content['idEdicion']);
        $stats = $this->getEstadisticas($where, $lang);
        $session->set("Estadisticas", $stats);
        $session->set("StatsTime", time());
        /* ---  el status nos sirve para saber que hacer a la respuesta del ajax ya que solo hay uno para todo y el json para actualizar el json en js con el data --- */
        $response = Array("stats" => $stats);
        return new JsonResponse($response);
    }

}
