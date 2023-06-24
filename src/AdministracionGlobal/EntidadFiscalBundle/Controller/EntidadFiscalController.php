<?php

namespace AdministracionGlobal\EntidadFiscalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AdministracionGlobal\EntidadFiscalBundle\Model\EntidadFiscalModel;
use Utilerias\TextoBundle\Model\TextoModel;

class EntidadFiscalController extends Controller {

    protected $EntidadFiscalModel, $TextoModel;

    const SECTION = 3, MAIN_ROUTE = 'entidad_fiscal';

    public function __construct() {
        $this->EntidadFiscalModel = new EntidadFiscalModel();
        $this->TextoModel = new TextoModel();
    }

    public function entidadFiscalAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        /* Obtención de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }

        /* Obtención de textos de la sección */
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
        /* Obtención de las entidades fiscales */
        $entidades_fiscales = NULL;
        if (isset($user['ComiteOrganizador']['idComiteOrganizador'])) {
            $result_entidades_fiscales = $this->EntidadFiscalModel->getEntidadFiscal(array('"idComiteOrganizador"' => $user['ComiteOrganizador']['idComiteOrganizador']));
            if (!$result_entidades_fiscales['status']) {
                throw new \Exception($result_entidades_fiscales['data'], 409);
            }
            $entidades_fiscales = $result_entidades_fiscales['data'];
        }
        $content['breadcrumb'] = $breadcrumb;
        $content['entity'] = $entidades_fiscales;
        return $this->render('AdministracionGlobalEntidadFiscalBundle:EntidadFiscal:lista_entidad_fiscal.html.twig', array('content' => $content));
    }

    public function entidadFiscalNuevoAction(Request $request) {
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

            /* Verificamos que no exista el mismo RFC */
            $args = array('lower("RFC")' => "'" . strtolower($post['RFC']) . "'");
            if ($this->EntidadFiscalModel->is_defined($post['idEntidadFiscal'])) {
                $args['idEntidadFiscal'] = array("operator" => "<>", "value" => $post['idEntidadFiscal']);
            }
            $result_ef = $this->EntidadFiscalModel->getEntidadFiscal($args);
            if (count($result_ef['data']) > 0) {
                $result['status'] = TRUE;
                $result_ef['status_aux'] = FALSE;
                $result_ef['message'] = $section_text['data']['sas_RFCExistenteEntidadFiscal'];
                $result = new Response(json_encode($result_ef));
                $result->headers->set('Content-Type', 'application/json');
                return $result;
            }

            if (isset($paises[$post['idPais']])) {
                $post['Pais'] = $paises[$post['idPais']]['Pais_' . strtoupper($lang)];
            }
            if (isset($paises[$post['idPais']])) {
                $post['Pais'] = $paises[$post['idPais']]['Pais_' . strtoupper($lang)];
                /* Obtenemos los Estados del PECC */
                $result_estados = $this->get('pecc')->getEstados($post['idPais']);
                if (!$result_estados['status']) {
                    $response = new Response(json_encode($result_estados));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }
                $estados = $result_estados['data'];
                if (isset($estados[$post['idEstado']])) {
                    $post['Estado'] = $estados[$post['idEstado']]['Estado'];
                }
            }

            $data = Array(
                'RazonSocial' => "'" . $post['RazonSocial'] . "'",
                'idComiteOrganizador' => "'" . $user['ComiteOrganizador']['idComiteOrganizador'] . "'",
                'RFC' => "'" . $post['RFC'] . "'",
                'RepresentanteLegal' => "'" . $post['RepresentanteLegal'] . "'",
                'Email' => "'" . $post['Email'] . "'",
                'Pais' => "'" . $post['Pais'] . "'",
                'idPais' => "'" . $post['idPais'] . "'",
                'Estado' => "'" . $post['Estado'] . "'",
                'idEstado' => "'" . $post['idEstado'] . "'",
                'Ciudad' => "'" . $post['Ciudad'] . "'",
                'Colonia' => "'" . $post['Colonia'] . "'",
                'Delegacion' => "'" . $post['Delegacion'] . "'",
                'Calle' => "'" . $post['Calle'] . "'",
                'NumeroExterior' => "'" . $post['NumeroExterior'] . "'",
                'NumeroInterior' => "'" . $post['NumeroInterior'] . "'",
                'CodigoPostal' => "'" . $post['CodigoPostal'] . "'",
            );
            $result = $this->EntidadFiscalModel->insertEntidadFiscal($data);
            if ($result['status']) {
                $post['idEntidadFiscal'] = $result['data'][0]['idEntidadFiscal'];
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

    public function entidadFiscalEditarAction(Request $request, $idEntidadFiscal) {
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
            /* Verificamos que no exista el mismo RFC */
            $args = array('lower("RFC")' => "'" . strtolower($post['RFC']) . "'");
            if ($this->EntidadFiscalModel->is_defined($post['idEntidadFiscal'])) {
                $args['idEntidadFiscal'] = array("operator" => "<>", "value" => $post['idEntidadFiscal']);
            }
            /* Obtenemos los paises del PECC */
            $result_paises = $this->get('pecc')->getPaises($lang);
            if (!$result_paises['status']) {
                throw new \Exception($result_paises['data'], 409);
            }
            $paises = $result_paises['data'];
            /* Obtenemos las Entidades Fiscales */
            $result_ef = $this->EntidadFiscalModel->getEntidadFiscal($args);



            if (isset($paises[$post['idPais']])) {
                $post['Pais'] = $paises[$post['idPais']]['Pais_' . strtoupper($lang)];
            }
            if (isset($paises[$post['idPais']])) {
                $post['Pais'] = $paises[$post['idPais']]['Pais_' . strtoupper($lang)];
                /* Obtenemos los Estados del PECC */
                $result_estados = $this->get('pecc')->getEstados($post['idPais']);
                if (!$result_estados['status']) {
                    $response = new Response(json_encode($result_estados));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }
                $estados = $result_estados['data'];
                if (isset($estados[$post['idEstado']])) {
                    $post['Estado'] = $estados[$post['idEstado']]['Estado'];
                }
            }
            if (count($result_ef['data']) > 0 and $result_ef['data'][$idEntidadFiscal]['RFC'] <> $post['RFC']) {
                $result['status'] = TRUE;
                $result_ef['status_aux'] = FALSE;
                $result_ef['message'] = $section_text['data']['sas_RFCExistenteEntidadFiscal'];
                $result = new Response(json_encode($result_ef));
                $result->headers->set('Content-Type', 'application/json');
                return $result;
            }
            $data = Array(
                'RazonSocial' => "'" . $post['RazonSocial'] . "'",
                'idComiteOrganizador' => "'" . $user['ComiteOrganizador']['idComiteOrganizador'] . "'",
                'RFC' => "'" . $post['RFC'] . "'",
                'RepresentanteLegal' => "'" . $post['RepresentanteLegal'] . "'",
                'Email' => "'" . $post['Email'] . "'",
                'Pais' => "'" . $post['Pais'] . "'",
                'idPais' => "'" . $post['idPais'] . "'",
                'Estado' => "'" . $post['Estado'] . "'",
                'idEstado' => "'" . $post['idEstado'] . "'",
                'Ciudad' => "'" . $post['Ciudad'] . "'",
                'Colonia' => "'" . $post['Colonia'] . "'",
                'Delegacion' => "'" . $post['Delegacion'] . "'",
                'Calle' => "'" . $post['Calle'] . "'",
                'NumeroExterior' => "'" . $post['NumeroExterior'] . "'",
                'NumeroInterior' => "'" . $post['NumeroInterior'] . "'",
                'CodigoPostal' => "'" . $post['CodigoPostal'] . "'",
            );
            $result = $this->EntidadFiscalModel->updateEntidadFiscal($data, $idEntidadFiscal);
            if ($result['status']) {
                $post['idEntidadFiscal'] = $idEntidadFiscal;
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

    public function entidadFiscalEliminarAction(Request $request, $idEntidadFiscal) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        if ($request->getMethod() == 'POST') {
            $result = $this->EntidadFiscalModel->deleteEntidadFiscal($idEntidadFiscal);
            if ($result['status']) {
                $post['idEntidadFiscal'] = $idEntidadFiscal;
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
