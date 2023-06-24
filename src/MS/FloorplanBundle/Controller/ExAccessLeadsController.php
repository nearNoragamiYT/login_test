<?php

namespace MS\FloorplanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Utilerias\TextoBundle\Model\TextoModel;
use MS\FloorplanBundle\Model\ExAccessLeadsModel;

/**
 * Description of ExAccessLeadsController
 *
 * @author ernestol
 */
class ExAccessLeadsController extends Controller {

    protected $Textos,$TextoModel, $ExAccessLeadsModel;

    const SECTION = 8;
    const TEMPLATE = 25;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->ExAccessLeadsModel = new ExAccessLeadsModel();
    }

    public function exhibitorAction(Request $request, $lang, $token) {
        $session = $request->getSession();
        $session->set('lang', $lang);
//        $session->set('idContacto', $idContacto);
        $resultToken = $this->ExAccessLeadsModel->getEdicion($token);
        switch ($resultToken) {
            case '206':
                return $this->render('MSFloorplanBundle:exAccessLeads:unkown_exhibitor.html.twig', array("lang" => $lang));
                break;

            case '404':
                return $this->render('MSFloorplanBundle:exAccessLeads:unkown_exhibitor.html.twig', array("lang" => $lang));
                break;
        }
        //$updateToken = $this->ExAccessLeadsModel->updateToken($resultToken);
        $session->set('idEdicion', $resultToken['idEdicion']);
        $session->set('idEvento', $resultToken['idEvento']);
        $args = array();
//        $args['idContacto'] = $idContacto;
        $args['idEvento'] = $resultToken['idEvento'];
        $args['idEdicion'] = $resultToken['idEdicion'];
        $args['idEmpresa'] = $resultToken['idEmpresa'];
        $this->ExAccessLeadsModel->insertGridView($args);
        $resultEditionName = $this->ExAccessLeadsModel->getEditionName($resultToken['idEdicion'], $resultToken['idEvento']);
        $editionName = $resultEditionName['status'] ? $resultEditionName['data']['0']['Edicion_' . strtoupper($lang)] : 'No Data';
        $session->set('idEmpresa', $resultToken['idEmpresa']);
        $session->set('evName', $editionName);
        $session->set('exName', $resultToken['DC_NombreComercial']);
        $content = array();
        $content['Platform'] = $lang == 'es' ? 'Base de Contactos' : 'Leads Database';
        $content['NombreEvento'] = $editionName;
        $content['NombreExpositor'] = $resultToken['DC_NombreComercial'];
        $content['Location'] = $lang == 'es' ? 'Base de Contactos' : 'Leads Database';
        $session->set('evName', $content['NombreEvento']);
        return $this->render('MSFloorplanBundle:exAccessLeads:exAccess.html.twig', array("content" => $content, "lang" => $lang));
    }

}
