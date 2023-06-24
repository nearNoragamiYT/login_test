<?php

namespace StatLink\EstadisticaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use StatLink\EstadisticaBundle\Model\EstadisticaModel;

class EstadisticaSemanaController extends Controller{
    const SECTION = 8;
    protected $TextoModel, $Estadistica;
    
    const TEMPLATE = 25;
    const MAIN_ROUTE = "stat_link_estadistica_semana";
    const MAIN_ROUTE_ELITE = "stat_link_estadistica_semana_elite";

    public function __construct() {
        $this->TextoModel = new TextoModel();
    }

    public function indexAction(Request $request, $userType) {
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
            'FechaInicio'=> date( 'Y-m-d', strtotime( 'previous sunday' )),
            'FechaFin'=> date( 'Y-m-d', strtotime( 'saturday this week' )),
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
        $table = $userType != 'elite' ? 'sl_EstadisticaSemana' : 'sl_EstadisticaSemana_CE';
        $result = $this->Estadistica->getRegistered(
                $table, '"FechaInicio" ASC', $idEdicion
        );

        $totalAcumulado = 0;
        foreach ($result as &$record) {
            $totalAcumulado += $record['Preregistro'];
            $record['PreregistroAcumulado'] += $totalAcumulado;
            unset($record['idEstadisticaSemana']);
            unset($record['idEdicion']);
            unset($record['SemanaRegistrada']);
            unset($record['NumeroSemana']);
            unset($record['idEvento']);
        }

        $content['result'] = count($result)==0? $vacio : $result;

        return $this->render('StatLinkEstadisticaBundle::semana.html.twig', array("content" => $content));
    }
}
