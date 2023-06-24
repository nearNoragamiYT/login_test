<?php

namespace Empresa\EmpresaContratoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use Empresa\EmpresaContratoBundle\Model\EmpresaContratoModel;
use Empresa\EmpresaContratoBundle\Model\EmpresaContratoConfiguration;

class EmpresaContratoController extends Controller {

    protected $TextoModel, $EmpresaContratoModel, $EmpresaContratoConfiguration;

    const SECTION = 4;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->EmpresaContratoModel = new EmpresaContratoModel();
        $this->EmpresaContratoConfiguration = new EmpresaContratoConfiguration();
    }

    public function contractsByCompanyAction(Request $request, $idEmpresa) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEdicion = $session->get('idEdicion');

        $content = array();
        $content['tabPermission'] = json_decode($this->EmpresaContratoModel->tabsPermission($user),true);
        $content['currentRoute'] = $request->get('_route');
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;

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

        /* Comienza la logica propia del Action */
        $content["idEmpresa"] = $idEmpresa;
        $args = Array('c."idEmpresa"' => $idEmpresa, 'c."idEdicion"' => $idEdicion);
        $contracts = $this->EmpresaContratoModel->getContractsByCompany($args);
        $content["contracts"] = $contracts;

        $contract_metadata = $this->EmpresaContratoConfiguration->getContractMetaData($content['section_text']);
        $content["contract_metadata"] = $contract_metadata;

        $status = $this->EmpresaContratoModel->getStatus();
        $content["status"] = $status;

        $editions = $this->EmpresaContratoModel->getEditions();
        $content["editions"] = $editions;



        $args = Array('p."idEdicion"' => $idEdicion);
        $content["packages"] = $this->EmpresaContratoModel->getPackages($args);

        $args = Array('e."idEmpresa"' => $idEmpresa, 'ee."idEdicion"' => $idEdicion);
        $content["header"] = $this->EmpresaContratoModel->getCompanyHeader($args);

        if ($session->get("companyOrigin") == "ventas")
            $content["breadcrumb"] = $this->EmpresaContratoModel->breadcrumb("empresa_ventas", $lang);
        if ($session->get("companyOrigin") == "expositores")
            $content["breadcrumb"] = $this->EmpresaContratoModel->breadcrumb("empresa", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => $content["header"]["DC_NombreComercial"], "route" => ""));

        return $this->render('EmpresaEmpresaContratoBundle:EmpresaContrato:empresa_contrato.html.twig', array('content' => $content));
    }

    public function cancelContractAction(Request $request) {
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
            $result = $this->EmpresaContratoModel->cancelContract($post);

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

    public function changeUpgradeAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $post["idEdicion"] = $session->get('idEdicion');
            $result = $this->EmpresaContratoModel->changeUpgrade($post);

            if ($result['status']) {
                $result['status_aux'] = TRUE;
                $result['status'] = TRUE;
                $result['data'] = $post;
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

}
