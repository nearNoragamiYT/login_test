<?php

namespace StatLink\EstadisticaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use StatLink\EstadisticaBundle\Model\EstadisticaModel;

class EstadisticaCampaniaController extends Controller {

    const SECTION = 8;

    protected $TextoModel, $Estadistica;

    const TEMPLATE = 25;
    const MAIN_ROUTE = "stat_link_estadistica_campania";

    public function __construct() {
        $this->TextoModel = new TextoModel();
    }

    public function indexAction(Request $request) {
        $this->Estadistica = new EstadisticaModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEdicion = $session->get('idEdicion');

        $content = array();
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content['breadcrumb'] = $breadcrumb;

        /* Obtenemos textos del Template MS_SL sl_EstadisticaDia */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        $content['App'] = $App;
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['chartType'] = 'Date';
        $result = $this->Estadistica->getRegistered(
                'sl_EstadisticaCupon', '"Preregistro" DESC', $idEdicion
        );

        $totalAcumulado = 0;
        foreach ($result as &$record) {
            $record = array("Cupon" => $record['Cupon'], "Descripcion" => $record['Descripcion' . strtoupper($content['lang'])] == "" ? "-" : $record['Descripcion' . strtoupper($content['lang'])], "Preregistro" => $record['Preregistro']);
            $totalAcumulado += $record['Preregistro'];
        }

        $content['result'] = $result;
        $content['total_cupones'] = $totalAcumulado;

        return $this->render('StatLinkEstadisticaBundle::campania.html.twig', array("content" => $content));
    }

    public function getStatsAction(Request $request) {
        $this->Estadistica = new EstadisticaModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $post = $request->request->all();
        $where = ' WHERE "Cupon" = \'' . $post['where'] . "'";

        $encuesta = $this->Estadistica ->getStats(array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'Activa' => 1), $where);
        if (!$encuesta['status']) {
            throw new \Exception($encuesta['data'], 409);
        }
        $content['status'] = true;
        $content['data'] = $encuesta['data'];
        return $this->jsonResponse($content);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
