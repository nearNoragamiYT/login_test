<?php

namespace ShowDashboard\FT\DatosFiscalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Utilerias\TextoBundle\Model\TextoModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ShowDashboard\FT\DatosFiscalesBundle\Model\DatosFiscalesModel;

class DatosFiscalesController extends Controller
{
    protected $TextoModel, $DatosFiscalesModel;
    const MAIN_ROUTE = "show_dashboard_facturacion_datosfiscales";


    public function __construct()
    {
        $this->DatosFiscalesModel = new DatosFiscalesModel();
        $this->TextoModel = new TextoModel();
    }

    public function indexAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set('OriginView', self::MAIN_ROUTE);
        $content = array();
        $Facturas = array();
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

        $content["breadcrumb"] = $breadcrumb;
        $content["idModuloIxpo"] = $breadcrumb[0]['idModuloIxpo'];

        $Facturas = $this->DatosFiscalesModel->getfacturas();

        return $this->render('ShowDashboardFTDatosFiscalesBundle:DatosFiscales:DatosFiscalesFacturacion.html.twig', array('content' => $content));
    }

    protected function jsonResponse($data)
    {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
