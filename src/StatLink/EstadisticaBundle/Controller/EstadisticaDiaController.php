<?php

namespace StatLink\EstadisticaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use StatLink\EstadisticaBundle\Model\EstadisticaModel;

class EstadisticaDiaController extends Controller {

    const SECTION = 8;
    protected $TextoModel, $Estadistica;

    const TEMPLATE = 16;
    const MAIN_ROUTE = "stat_link_estadistica_dia";
    const MAIN_ROUTE_ELITE = "stat_link_estadistica_dia_elite";

    public function __construct() {
        $this->TextoModel = new TextoModel();
    }

    public function indexAction(Request $request, $userType = 'general') {
        $this->Estadistica = new EstadisticaModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEdicion = $session->get('idEdicion');
        date_default_timezone_set("America/Mexico_City");
        $current = time();
        $vacio=array();
        $vacio[0]=array(
            'Dia'=> date('Y-m-d', $current),
            'Preregistro'=>0,
            'PreregistroAcumulado'=>0
        );

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
        $table = $userType != 'elite' ? 'sl_EstadisticaDia' : 'sl_EstadisticaDia_CE';
        $result = $this->Estadistica->getRegistered(
                $table, '"Dia" ASC', $idEdicion
        );

        $totalAcumulado = 0;
        foreach ($result as &$record) {
            $totalAcumulado += $record['Preregistro'];
            $record['PreregistroAcumulado'] += $totalAcumulado;
            unset($record['idEstadisticaDia']);
            unset($record['idEdicion']);
            unset($record['DiaRegistrado']);
            unset($record['idEvento']);
        }
        

        $content['result'] = count($result)==0? $vacio : $result;

        return $this->render('StatLinkEstadisticaBundle::dia.html.twig', array("content" => $content));
    }

}
