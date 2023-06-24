<?php

namespace ShowDashboard\FT\ConfiguracionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Utilerias\TextoBundle\Model\TextoModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use ShowDashboard\FT\FacturacionBundle\Model\FacturacionModel;
use ShowDashboard\FT\ConfiguracionBundle\Model\ConfiguracionModel;

class ConfiguracionController extends Controller
{
    protected $TextoModel, $ConfiguracionModel, $FacturacionModel;
    const MAIN_ROUTE = "show_dashboard_facturacion_configuracion";


    public function __construct()
    {
        $this->ConfiguracionModel = new ConfiguracionModel();
        $this->FacturacionModel = new FacturacionModel();
        $this->TextoModel = new TextoModel();
    }

    public function indexAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $path = $request->getUri();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set('OriginView', self::MAIN_ROUTE);
        $content = array();
        $Facturas = array();
        $general_text = $this->TextoModel->getTexts($lang);

        $configuracion = $this->ConfiguracionModel->getConfiguracion($App['IdConfiguracion']);
        $configuracionPortal = $this->ConfiguracionModel->getEdicion($idEvento, $idEdicion);

        // $resultFactura = $this->FacturacionModel->getfacturas($idFactura, $idEdicion);


        $img = $configuracion['data'][0]['LogoTipo'];


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

        $content["configuracionFT"] = $configuracion['data'][0];
        $content["configuracionPortal"] = $configuracionPortal['data'][0];
        /* CODE  */

        $path = explode('web', $path);
        $path = $path[0] . 'web/resources/images/logo-evento/' . $img;
        $content['logo'] = file_get_contents($path) ? $img : 0;

        return $this->render('ShowDashboardFTConfiguracionBundle:Configuracion:ConfiguracionFacturacion.html.twig', array('content' => $content));
    }

    protected function jsonResponse($data)
    {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    public function insertDatosAction(Request $request)
    {
        $App = $this->get('ixpo_configuration')->getApp();
        $post = $request->request->all();
        $insertConfiguracion = $this->ConfiguracionModel->getInsertConfiguracion($post, $App['IdConfiguracion']);
        return $this->jsonResponse($insertConfiguracion);
    }

    public function uploadFilesAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $post = $request->request->all();
        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];
        $_FILES = $_FILES;
        if ($_FILES[2]['name'] != '' || $_FILES[2]['name'] != null) {
            $tipoImagen = explode('/', $_FILES[2]['type']);
            /* */
            if ($tipoImagen[1] == "jpeg") {
                move_uploaded_file($_FILES[2]['name'], $_FILES[2]['name'] = $post['idConfiguracion'] . ".jpg");
            } else {
                move_uploaded_file($_FILES[2]['name'], $_FILES[2]['name'] = $post['idConfiguracion'] . "." . $tipoImagen[1]);
            }

            $result_files = $this->ConfiguracionModel->uploadFiles($_FILES, $general_text, $post['idConfiguracion']);
            $update_logo = $this->ConfiguracionModel->updateLogo($result_files['data'][0]['name'], $post['idConfiguracion']);

            $response = new Response(json_encode($result_files));
           
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
