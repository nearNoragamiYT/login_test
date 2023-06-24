<?php

namespace ShowDashboard\AE\Administracion\Configuracion\SyncFMPGBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ShowDashboard\AE\Administracion\Configuracion\SyncFMPGBundle\Model\SyncModel;
use Utilerias\TextoBundle\Model\TextoModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SyncController extends Controller {

    protected $SyncModel, $idPlataformaIxpo = 2;
    const SECTION = 3;

    public function __construct() {
        $this->SyncModel = new SyncModel();
        $this->TextoModel = new TextoModel();
    }

    public function syncAction(Request $request) {
        $session = $request->getSession();
        $session->set('idPlataformaIxpo', $this->idPlataformaIxpo);
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $content = array();
        $content['App'] = $this->get('ixpo_configuration')->getApp();
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "show_dashboard_ae_administracion_configuracion_ajustes");
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content['breadcrumb'] = $breadcrumb;
        
        /* Obtenemos textos de la sección del Administracion Global 3 */
        $result_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $content['section_text'] = $result_text['data'];

        $tablesPG = $this->SyncModel->getTablasPG("AE");
        if (!$tablesPG['status']) {
            throw new \Exception($tablesPG['data'], 409);
        }
        $content['tablesPG'] = $tablesPG['data'];
        $tablesFM = $this->SyncModel->getTablasFM();
        if (!$tablesFM['status']) {
            throw new \Exception($tablesFM['data'], 409);
        }
        $content['tablesFM'] = $tablesFM['data'];
        
        //Consultar jsonSyn en la tabla de AE.Configuración
        $jsonSync = $this->SyncModel->getJson($idEvento, $idEdicion);
        if (!$jsonSync['status']) {
            throw new \Exception($jsonSync['data'], 409);
        }
        if (count($jsonSync['data'])) {
            $content['jsonTable'] = json_decode($jsonSync['data'][0]['jsonSync']);
        }
        else{
            $content['jsonTable'] = array();
        }
        
//        print_r($content);
//        die();
        return $this->render('VisitanteSyncFMPGBundle:Sync:sync.html.twig', array('content' => $content));
    }

    public function fieldsPGAction(Request $request) {
        $content = array();
        $post = $request->request->all();


        $fields = $this->SyncModel->getCamposPG($post['tableNamePG']);
        if (!$fields['status']) {
            throw new \Exception($fields['data'], 409);
        }
        $content['fieldsPG'] = $fields['data'];

        return $this->getResponse($content);
    }

    public function fieldsFMAction(Request $request) {
        $content = array();
        $post = $request->request->all();

        $fields = $this->SyncModel->getCamposFM($post['tableNameFM']);

        if (!$fields['status']) {
            throw new \Exception($fields['data'], 409);
        }

        $content['fieldsFM'] = $fields['data'];

        return $this->getResponse($content);
    }

    public function updateJsonAction(Request $request) {
        $session = $request->getSession();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $response = array("status" => false, "data" => array());

        if ($request->getMethod() != 'POST') {
            $response['data'] = "Not Authorized";
        }
        
        $post = $request->request->all();
        $jsonTable = json_encode($post['jsonTable']);
        
        $resultUpdate = $this->SyncModel->updateJson($jsonTable, $idEvento, $idEdicion);
        if (!$resultUpdate['status']) {
            throw new \Exception($resultUpdate['data'], 409);
        }
        return $this->getResponse($response);
    }
    

    public function getResponse($data) {
        $response = new Response(json_encode($data), 200, array('Content-Type', 'text/json'));
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, *');
        return $response;
    }

}
