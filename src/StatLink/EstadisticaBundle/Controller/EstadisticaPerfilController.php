<?php

namespace StatLink\EstadisticaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use StatLink\EstadisticaBundle\Model\EstadisticaModel;

class EstadisticaPerfilController extends Controller{
    const SECTION = 8;
    protected $TextoModel, $Estadistica;
    
    const TEMPLATE = 25;
    const MAIN_ROUTE = "stat_link_estadistica_perfil";

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
        
        $result = $this-> Estadistica->getRegistered('sl_EstadisticaPerfil', '"Preregistro" DESC', $idEdicion);

        foreach ($result as &$record) {
            $record = array("Cupon" => $record['Cupon'], "Descripcion" => $record['Descripcion' . strtoupper($content['lang'])]== "" ? "-" : $record['Descripcion' . strtoupper($content['lang'])], "Preregistro" => $record['Preregistro']);
        }

        $content['result'] = $result;
        

        return $this->render('StatLinkEstadisticaBundle::perfil.html.twig', array("content" => $content));
    }
}
