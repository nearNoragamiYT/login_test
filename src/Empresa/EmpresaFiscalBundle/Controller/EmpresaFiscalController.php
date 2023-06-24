<?php

namespace Empresa\EmpresaFiscalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use Empresa\EmpresaFiscalBundle\Model\EmpresaFiscalModel;
use Empresa\EmpresaFiscalBundle\Model\EmpresaFiscalConfiguration;

class EmpresaFiscalController extends Controller {

    protected $TextoModel, $EmpresaFiscalModel, $EmpresaFiscalConfiguration;

    const SECTION = 4;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->EmpresaFiscalModel = new EmpresaFiscalModel();
        $this->EmpresaFiscalConfiguration = new EmpresaFiscalConfiguration();
    }

    public function financialCompaniesAction(Request $request, $idEmpresa) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['user'] = $user;
        $content['tabPermission'] = json_decode($this->EmpresaFiscalModel->tabsPermission($user),true);
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

        /* Obtenemos los paises del PECC */
        $result_paises = $this->get('pecc')->getPaises($lang);
        if (!$result_paises['status']) {
            throw new \Exception($result_paises['data'], 409);
        }
        $content['paises'] = $result_paises['data'];

        /* Comienza la logica propia del Action */
        $content["idEmpresa"] = $idEmpresa;
        $args = Array('eef."idEmpresa"' => $idEmpresa);
        $financialCompanies = $this->EmpresaFiscalModel->getFinancialCompanies($args);
        $content["financial_companies"] = $financialCompanies;

        $financial_metadata = $this->EmpresaFiscalConfiguration->getFinancialMetaData($content['section_text']);
        $content["financial_metadata"] = $financial_metadata;

        $idEdicion = $session->get('idEdicion');

        $args = Array('p."idEdicion"' => $idEdicion);
        //$content["packages"] = $this->EmpresaFiscalModel->getPackages($args);

        $args = Array('e."idEmpresa"' => $idEmpresa, 'ee."idEdicion"' => $idEdicion);
        $content["header"] = $this->EmpresaFiscalModel->getCompanyHeader($args);

        if ($session->get("companyOrigin") == "ventas")
            $content["breadcrumb"] = $this->EmpresaFiscalModel->breadcrumb("empresa_ventas", $lang);
        if ($session->get("companyOrigin") == "expositores")
            $content["breadcrumb"] = $this->EmpresaFiscalModel->breadcrumb("empresa", $lang);
        if ($session->get("companyOrigin") == "solicitud_lectoras")
            $content["breadcrumb"] = $this->EmpresaFiscalModel->breadcrumb("solicitud_lectora_reporte", $lang);
        if ($session->get("companyOrigin") == "lectoras")
            $content["breadcrumb"] = $this->EmpresaFiscalModel->breadcrumb("lista_expositores", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => $content["header"]["DC_NombreComercial"], "route" => ""));
        $content['companyOrigin'] = $session->get("companyOrigin");
        return $this->render('EmpresaEmpresaFiscalBundle:EmpresaFiscal:empresa_fiscal.html.twig', array('content' => $content));
    }

    public function addFinancialCompanyAction(Request $request) {
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
        /* Obtenemos los paises del PECC */
        $result_paises = $this->get('pecc')->getPaises($lang);
        if (!$result_paises['status']) {
            throw new \Exception($result_paises['data'], 409);
        }
        $paises = $result_paises['data'];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            if (isset($paises[$post['DF_idPais']])) {
                $post['DF_Pais'] = $paises[$post['DF_idPais']]['Pais_' . strtoupper($lang)];
                /* Obtenemos los Estados del PECC */
                $result_estados = $this->get('pecc')->getEstados($post['DF_idPais']);
                if (!$result_estados['status']) {
                    $response = new Response(json_encode($result_estados));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }
                $estados = $result_estados['data'];
                if (isset($estados[$post['DF_idEstado']])) {
                    $post['DF_Estado'] = $estados[$post['DF_idEstado']]['Estado'];
                }
            }

            $data = Array(
                'idEmpresa' => "'" . $post['idEmpresa'] . "'",
                'DF_RazonSocial' => "'" . $post['DF_RazonSocial'] . "'",
                'DF_RFC' => "'" . $post['DF_RFC'] . "'",
                'DF_idPais' => "'" . $post['DF_idPais'] . "'",
                'DF_Pais' => "'" . $post['DF_Pais'] . "'",
                'DF_idEstado' => "'" . $post['DF_idEstado'] . "'",
                'DF_Estado' => "'" . $post['DF_Estado'] . "'",
                'DF_CodigoPostal' => "'" . $post['DF_CodigoPostal'] . "'",
                'DF_Ciudad' => "'" . $post['DF_Ciudad'] . "'",
                'DF_Colonia' => "'" . $post['DF_Colonia'] . "'",
                'DF_Delegacion' => "'" . $post['DF_Delegacion'] . "'",
                'DF_Calle' => "'" . $post['DF_Calle'] . "'",
                /* 'DF_NumeroExterior' => "'" . $post['DF_NumeroExterior'] . "'",
                  'DF_RepresentanteLegal' => "'" . $post['DF_RepresentanteLegal'] . "'",
                  'DF_Email' => "'" . $post['DF_Email'] . "'",
                  'DF_NumeroInterior' => "'" . $post['DF_NumeroInterior'] . "'", */
                'Principal' => "'" . $post['Principal'] . "'",
            );
            $result = $this->EmpresaFiscalModel->insertFinancialCompany($data);
            if ($result['status']) {
                $post['idEmpresaEntidadFiscal'] = $result['data'][0]['idEmpresaEntidadFiscal'];
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

    public function updateFinancialCompanyAction(Request $request) {
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
        /* Obtenemos los paises del PECC */
        $result_paises = $this->get('pecc')->getPaises($lang);
        if (!$result_paises['status']) {
            throw new \Exception($result_paises['data'], 409);
        }
        $paises = $result_paises['data'];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            if (isset($paises[$post['DF_idPais']])) {
                $post['DF_Pais'] = $paises[$post['DF_idPais']]['Pais_' . strtoupper($lang)];
                /* Obtenemos los Estados del PECC */
                $result_estados = $this->get('pecc')->getEstados($post['DF_idPais']);
                if (!$result_estados['status']) {
                    $response = new Response(json_encode($result_estados));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }
                $estados = $result_estados['data'];
                if (isset($estados[$post['DF_idEstado']])) {
                    $post['DF_Estado'] = $estados[$post['DF_idEstado']]['Estado'];
                }
            }

            $data = Array(
                'idEmpresa' => "'" . $post['idEmpresa'] . "'",
                'DF_RazonSocial' => "'" . $post['DF_RazonSocial'] . "'",
                'DF_RFC' => "'" . $post['DF_RFC'] . "'",
                'DF_RepresentanteLegal' => "'" . $post['DF_RepresentanteLegal'] . "'",
                'DF_Email' => "'" . $post['DF_Email'] . "'",
                'DF_idPais' => "'" . $post['DF_idPais'] . "'",
                'DF_Pais' => "'" . $post['DF_Pais'] . "'",
                'DF_idEstado' => "'" . $post['DF_idEstado'] . "'",
                'DF_Estado' => "'" . $post['DF_Estado'] . "'",
                'DF_CodigoPostal' => "'" . $post['DF_CodigoPostal'] . "'",
                'DF_Ciudad' => "'" . $post['DF_Ciudad'] . "'",
                'DF_Colonia' => "'" . $post['DF_Colonia'] . "'",
                'DF_Delegacion' => "'" . $post['DF_Delegacion'] . "'",
                'DF_Calle' => "'" . $post['DF_Calle'] . "'",
                    /* 'DF_NumeroExterior' => "'" . $post['DF_NumeroExterior'] . "'",
                      'DF_NumeroInterior' => "'" . $post['DF_NumeroInterior'] . "'", */
            );
            $result = $this->EmpresaFiscalModel->updateFinancialCompany($data, $post["idEmpresaEntidadFiscal"]);
            if ($result['status']) {
                $post['idEmpresaEntidadFiscal'] = $result['data'][0]['idEmpresaEntidadFiscal'];
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

    public function deleteFinancialCompanyAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $result = $this->EmpresaFiscalModel->deleteFinancialCompany($post["idEmpresaEntidadFiscal"]);
            if ($result['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            } else {
                $result['error'] = $content['general_text']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function changeFinancialCompanyAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $result = $this->EmpresaFiscalModel->changeFinancialCompany($post);
            if ($result['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            } else {
                $result['error'] = $content['general_text']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}