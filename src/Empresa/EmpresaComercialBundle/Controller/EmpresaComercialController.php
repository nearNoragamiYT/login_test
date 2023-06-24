<?php

namespace Empresa\EmpresaComercialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use Empresa\EmpresaComercialBundle\Model\EmpresaComercialModel;

class EmpresaComercialController extends Controller {

    protected $TextoModel, $EmpresaComercialModel;

    const SECTION = 4;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->EmpresaComercialModel = new EmpresaComercialModel();
    }

    public function comercialCompanyAction(Request $request, $idEmpresa) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['tabPermission'] = json_decode($this->EmpresaComercialModel->tabsPermission($user), true);
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
        $args = Array('e."idEmpresa"' => $idEmpresa);
        $comercialCompany = $this->EmpresaComercialModel->getComercialCompany($args);
        $content["comercial_company"] = $comercialCompany;

        $events = $this->EmpresaComercialModel->getEvents();
        $content["events"] = $events;

        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        $types = $this->EmpresaComercialModel->getTypes($idEdicion, $lang);
        $content["types"] = $types;

        $sellers = $this->EmpresaComercialModel->getSellers();
        $content["sellers"] = $sellers;

        $pavilions = $this->EmpresaComercialModel->getPavilions($idEdicion);
        $content["pavilions"] = $pavilions;

        $args = Array('p."idEdicion"' => $idEdicion);
        //$content["packages"] = $this->EmpresaComercialModel->getPackages($args);

        $args = Array('e."idEmpresa"' => $idEmpresa, 'ee."idEdicion"' => $idEdicion);
        $content["header"] = $this->EmpresaComercialModel->getCompanyHeader($args);
        /* ---  obtenemos el detalle si la empresa es adicional para mostrar solo ciertas pestañas  --- */
        $content['Adicional'] = $this->EmpresaComercialModel->getAditionalDetail(Array("idEmpresa" => $idEmpresa, "idEdicion" => $idEdicion));
        /* Traemos el catalogo de categorias */
        $result_category = $this->EmpresaComercialModel->getCategory(Array('idEvento' => $session->get('idEvento'), 'idEdicion' => $session->get('idEdicion')));
        if (!$result_category['status']) {
            throw new \Exception($result_category['data'], 409);
        }
        $content['categoria'] = Array();
        foreach ($result_category['data'] as $key => $value) {
            $content['categoria'][$value['idCategoria']] = $value;
        }
        /* Traemos las categorias de la empresa */
        $result_companycategory = $this->EmpresaComercialModel->getCompanyCategory(Array('idEvento' => $session->get('idEvento'), 'idEdicion' => $session->get('idEdicion'), 'idEmpresa' => $idEmpresa, 'CategoriaComite' => 1));
        if (!$result_companycategory['status']) {
            throw new \Exception($result_companycategory['data'], 409);
        }
        $content['empresa_categoria'] = Array();
        foreach ($result_companycategory['data'] as $key => $value) {
            $content['empresa_categoria'][$value['idCategoria']] = $value;
        }
        //Obtenemos el listado de empresas Padre
        $args = array();
        $args = array("idEvento" => $idEvento, "idEdicion" => $idEdicion);
        $result_parents = $this->EmpresaComercialModel->getParents($args);
        if (!$result_parents['status']) {
            throw new \Exception($result_parents['data'], 409);
        }
        $content['empresa_padres'] = Array();
        foreach ($result_parents['data'] as $key => $value) {
            $content['empresa_padres'][$value['idEmpresa']] = $value;
        }
        if ($session->get("companyOrigin") == "ventas")
            $content["breadcrumb"] = $this->EmpresaComercialModel->breadcrumb("empresa_ventas", $lang);
        if ($session->get("companyOrigin") == "expositores")
            $content["breadcrumb"] = $this->EmpresaComercialModel->breadcrumb("empresa", $lang);
        if ($session->get("companyOrigin") == "solicitud_lectoras")
            $content["breadcrumb"] = $this->EmpresaComercialModel->breadcrumb("solicitud_lectora_reporte", $lang);
        if ($session->get("companyOrigin") == "lectoras")
            $content["breadcrumb"] = $this->EmpresaComercialModel->breadcrumb("lista_expositores", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => $content["header"]["DC_NombreComercial"], "route" => ""));
        $content['companyOrigin'] = $session->get("companyOrigin");
        return $this->render('EmpresaEmpresaComercialBundle:EmpresaComercial:empresa_comercial.html.twig', array('content' => $content));
    }

    public function saveComercialCompanyAction(Request $request) {
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
            if (isset($paises[$post['DC_idPais']])) {
                $post['DC_Pais'] = $paises[$post['DC_idPais']]['Pais_' . strtoupper($lang)];
                /* Obtenemos los Estados del PECC */
                $result_estados = $this->get('pecc')->getEstados($post['DC_idPais']);
                if (!$result_estados['status']) {
                    $response = new Response(json_encode($result_estados));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }
                $estados = $result_estados['data'];
                if (isset($estados[$post['DC_idEstado']])) {
                    $post['DC_Estado'] = $estados[$post['DC_idEstado']]['Estado'];
                }
            }
            $data = Array(
                'DC_NombreComercial' => "'" . $post['DC_NombreComercial'] . "'",
                'idEmpresaUUID' => "'" . $post['idEmpresaUUID'] . "'",
                'CodigoCliente' => "'" . $post['CodigoCliente'] . "'",
                'idEmpresaTipo' => $post['idEmpresaTipo'],
                'DC_idPais' => $post['DC_idPais'],
                'DC_Pais' => "'" . $post['DC_Pais'] . "'",
                'DC_idEstado' => $post['DC_idEstado'],
                'DC_Estado' => "'" . $post['DC_Estado'] . "'",
                'DC_Ciudad' => "'" . $post['DC_Ciudad'] . "'",
                'DC_CodigoPostal' => "'" . $post['DC_CodigoPostal'] . "'",
                'DC_Colonia' => "'" . $post['DC_Colonia'] . "'",
                'DC_CalleNum' => "'" . $post['DC_CalleNum'] . "'",
                'DC_TelefonoAreaPais' => "'" . $post['DC_TelefonoAreaPais'] . "'",
                'DC_TelefonoAreaCiudad' => "'" . $post['DC_TelefonoAreaCiudad'] . "'",
                'DC_Telefono' => "'" . $post['DC_Telefono'] . "'",
                'DC_TelefonoExtension' => "'" . $post['DC_TelefonoExtension'] . "'",
                'DC_PaginaWeb' => "'" . $post['DC_PaginaWeb'] . "'",
                'DC_DescripcionES' => "'" . $post['DC_DescripcionES'] . "'",
                'DC_DescripcionEN' => "'" . $post['DC_DescripcionEN'] . "'"
            );

            if (isset($post["VisibleDirectorio"])) {
                $view = Array(
                    'VisibleDirectorio' => "'" . $post['VisibleDirectorio'] . "'"
                );

                $visible = $this->EmpresaComercialModel->changeVisible($view, $post["idEmpresa"]);
            }
            if (isset($post["parent"])) {
                $view = Array(
                    'idEmpresaPadre' => $post['parent']
                );

                $visible = $this->EmpresaComercialModel->changeVisible($view, $post["idEmpresa"]);
            }
            $result = $this->EmpresaComercialModel->saveComercialCompany($data, $post["idEmpresa"]);
            $result_categorias = $this->EmpresaComercialModel->saveCategories(Array('idEmpresa' => $post['idEmpresa'], 'idEvento' => $session->get('idEvento'), 'idEdicion' => $session->get('idEdicion'), 'ListadoCategorias' => $post['ListadoCategorias']));
            if ($result['status'] && $visible['status']) {
                $post['idEmpresa'] = $result['data'][0]['idEmpresa'];
                $result['status_aux'] = TRUE;
                $result['status'] = TRUE;
                $result['data'] = $post;
                $result['message'] = $general_text['data']['sas_guardoExito'];
                $result['categorias'] = $result_categorias;
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
