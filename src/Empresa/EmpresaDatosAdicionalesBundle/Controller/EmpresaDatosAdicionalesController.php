<?php

namespace Empresa\EmpresaDatosAdicionalesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use Empresa\EmpresaDatosAdicionalesBundle\Model\EmpresaDatosAdicionalesModel;

class EmpresaDatosAdicionalesController extends Controller {

    protected $TextoModel, $EmpresaDatosAdicionalesModel;

    const SECTION = 4;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->EmpresaDatosAdicionalesModel = new EmpresaDatosAdicionalesModel();
    }

    public function aditionalDataAction(Request $request, $idEmpresa) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $content = array();
        $content["lang"] = $lang;
        $content['tabPermission'] = json_decode($this->EmpresaDatosAdicionalesModel->tabsPermission($user), true);
        $content['currentRoute'] = $request->get('_route');

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Obtenemos textos de la seccion 4 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];

        $content['idEmpresa'] = $idEmpresa;

        $idEdicion = $session->get('idEdicion');
        $args = Array('e."idEmpresa"' => $idEmpresa, 'ee."idEdicion"' => $idEdicion);
        $content["header"] = $this->EmpresaDatosAdicionalesModel->getCompanyHeader($args);

        $args = Array('p."idEdicion"' => $idEdicion);
        //$content["packages"] = $this->EmpresaDatosAdicionalesModel->getPackages($args);

        $args = Array('ee."idEmpresa"' => $idEmpresa, 'ee."idEdicion"' => $idEdicion);
        $content["aditional_data"] = $this->EmpresaDatosAdicionalesModel->getAditionalData($args);

        if ($session->get("companyOrigin") == "ventas")
            $content["breadcrumb"] = $this->EmpresaDatosAdicionalesModel->breadcrumb("empresa_ventas", $lang);
        if ($session->get("companyOrigin") == "expositores")
            $content["breadcrumb"] = $this->EmpresaDatosAdicionalesModel->breadcrumb("empresa", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => $content["header"]["DC_NombreComercial"], "route" => ""));

        return $this->render('EmpresaEmpresaDatosAdicionalesBundle:DatosAdicionales:empresa_datos_adicionales.html.twig', array('content' => $content));
    }

    public function saveAditionalDataAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        /* Obtención de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* Obtención de textos de la sección */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            if (!$post['GafetesPagados'])
                $post['GafetesPagados'] = "false";
            if (!$post['Montaje'])
                $post['Montaje'] = "false";

            $data = Array(
                'ObservacionesFacturacion' => "'" . $post['ObservacionesFacturacion'] . "'",
                'EmpresasAdicionales' => "'" . $post['EmpresasAdicionales'] . "'",
                'NumeroGafetes' => "'" . $post['NumeroGafetes'] . "'",
                'NumeroGafetesCompra' => "'" . $post['NumeroGafetesCompra'] . "'",
                'GafetesPagados' => "'" . $post['GafetesPagados'] . "'",
                'GafetesComentario' => "'" . $post['GafetesComentario'] . "'",
                'NumeroVitrinas' => "'" . $post['NumeroVitrinas'] . "'",
                'NumeroCatalogos' => "'" . $post['NumeroCatalogos'] . "'",
                'NumeroInvitaciones' => "'" . $post['NumeroInvitaciones'] . "'",
                'UsuarioInvitaciones' => "'" . $post['UsuarioInvitaciones'] . "'",
                'PasswordInvitaciones' => "'" . $post['PasswordInvitaciones'] . "'",
                'UsuarioEncuentroNegocios' => "'" . $post['UsuarioEncuentroNegocios'] . "'",
                'PasswordEncuentroNegocios' => "'" . $post['PasswordEncuentroNegocios'] . "'",
                'Montaje' => "'" . $post['Montaje'] . "'",
                'MontajeAndenEntrada' => "'" . $post['MontajeAndenEntrada'] . "'",
                'MontajeSalaEntrada' => "'" . $post['MontajeSalaEntrada'] . "'",
                'MontajeDiaEntrada' => "'" . $post['MontajeDiaEntrada'] . "'",
                'MontajeHorarioEntrada' => "'" . $post['MontajeHorarioEntrada'] . "'",
                'MontajeAndenSalida' => "'" . $post['MontajeAndenSalida'] . "'",
                'MontajeSalaSalida' => "'" . $post['MontajeSalaSalida'] . "'",
                'MontajeDiaSalida' => "'" . $post['MontajeDiaSalida'] . "'",
                'MontajeHorarioSalida' => "'" . $post['MontajeHorarioSalida'] . "'"
            );
            $result = $this->EmpresaDatosAdicionalesModel->saveAditionalData($data, $post["idEmpresa"]);

            if ($result['status']) {
                $result['status_aux'] = TRUE;
                $result['status'] = TRUE;
                $result['data'] = $post;
                $result['message'] = $general_text['data']['sas_guardoExito'];
            } else {
                $result['error'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function sendAdditionalMailAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['App'] = $App;
        $content['user'] = $user;

        /* Obtenemos textos de la sección */
        $text = $this->TextoModel->getTexts("ES", self::SECTION);
        if (!$text['status']) {
            throw new \Exception($text['data'], 409);
        }
        $section_text["ES"] = $text["data"];

        $text = $this->TextoModel->getTexts("EN", self::SECTION);
        if (!$text['status']) {
            throw new \Exception($text['data'], 409);
        }
        $section_text["EN"] = $text["data"];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            
            $type = count($post);
            $content['lang'] = $lang;
            $content['section_text'] = $section_text[$lang];
            
            $data = $this->EmpresaDatosAdicionalesModel->getTokens(array("idEdicion" => $idEdicion, "idEmpresa" => $post['idEmpresa']), strtoupper($lang));
 
            $urlForma210='https://expoantad.infoexpo.com.mx/2022/ed/web/utilerias/info/1/210/'.$data[0]['Token'].'/'.$lang;
            
            $content['ruta'] = $urlForma210;
                    
            /* Estructura envío de email */
            $ixpo_mailer = $this->get('ixpo_mailer');

            $lang = strtoupper($lang);

            $body = $this->renderView('EmpresaEmpresaDatosAdicionalesBundle:DatosAdicionales:email_adicional.html.twig', array('content' => $content));
            
            $result = $this->get('ixpo_mailer')->send_emailAdditionalMail("Gafetes Adicionales", $post['correoAdicional'], $body, $lang);
        }

        $result = array();
        $result['status'] = TRUE;
        $result['status_aux'] = TRUE;
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
