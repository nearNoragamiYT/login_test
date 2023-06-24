<?php

namespace Empresa\ContratoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Empresa\ContratoBundle\Model\ContratoModel;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Empresa\ContratoBundle\Model\ContratoConfiguration;
use Utilerias\TextoBundle\Model\TextoModel;

class ContratoController extends Controller {

    protected $ContratoModel, $TextoModel, $ContratoConfiguration;

    const SECTION = 4, MAIN_ROUTE = 'show_dashboard_dashboard';

    public function __construct() {
        $this->ContratoModel = new ContratoModel();
        $this->TextoModel = new TextoModel();
        $this->ContratoConfiguration = new ContratoConfiguration();
    }

    public function contratoAction(Request $request, $idEmpresa, $idContrato) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        if (!$session->has('idEdicion')) {
            return $this->redirectToRoute('dashboard', array('lang' => $lang));
        }
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        $result_contrato = Array();
        if ($idContrato != 0) {
            $result_contrato = $this->ContratoModel->getContrato(Array('idEmpresa' => $idEmpresa, 'idContrato' => $idContrato, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion), TRUE);
        }
        $step = 0;
        if (isset($result_contrato['data'][0]['AvanceContrato'])) {
            $step = ($result_contrato['data'][0]['AvanceContrato'] == 0) ? 1 : $result_contrato['data'][0]['AvanceContrato'];
            $idContrato = $result_contrato['data'][0]['idContrato'];
        }
        switch ($step) {
            case 0:
                return $this->redirectToRoute('empresa_contrato_informacion', array('idEmpresa' => $idEmpresa));
                break;
            case 1:
                return $this->redirectToRoute('empresa_contrato_informacion', array('idEmpresa' => $idEmpresa, 'idContrato' => $idContrato));
                break;
            case 2:
                return $this->redirectToRoute('empresa_contrato_espacio', array('idEmpresa' => $idEmpresa, 'idContrato' => $idContrato));
                break;
            case 3:
                return $this->redirectToRoute('empresa_contrato_resumen', array('idEmpresa' => $idEmpresa, 'idContrato' => $idContrato));
                break;
            case 4:
                return $this->redirectToRoute('empresa_contrato_resumen', array('idEmpresa' => $idEmpresa, 'idContrato' => $idContrato));
                break;
        }
    }

    public function informacionAction(Request $request, $idEmpresa, $idContrato) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        if (!$session->has('idEdicion')) {
            return $this->redirectToRoute('dashboard', array('lang' => $lang));
        }
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        $content = array();
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Obtenemos textos de la sección 4 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* Verificamos si la Empresa existe */
        $result_company = $this->ContratoModel->getCompany(Array('idEmpresa' => $idEmpresa));
        if (!$result_company['status'] || !COUNT($result_company['data'])) {
            throw new \Exception($result_company['data'] . ' ' . $content['section_text']['sas_empresaNoEncontrada'], 409);
        }
        
        /* Verificamos la empresa padre si la empresa  esta en la edicion actual */
         $result_empresa_padre= $this->ContratoModel->getEmpresaPadre(Array('idEmpresa' => $idEmpresa,'idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_empresa_padre['status']) {
            throw new \Exception($result_empresa_padre['data'], 409);
        }
        $empresa_padre = $result_empresa_padre['data'][0]['idEmpresaPadre'] ;
        
        /* Contactos de la Empresa */
        $result_contactos = $this->ContratoModel->getContactos(Array('idEmpresa' => $idEmpresa));
        if (!$result_contactos['status']) {
            throw new \Exception($result_contactos['data'], 409);
        }
        $contactos = Array();
        foreach ($result_contactos['data'] as $key => $value) {
            $contactos[$value['idContacto']] = $value;
        }
        /* EmpresaTipo */
        $result_empresa_tipo = $this->ContratoModel->getEmpresaTipo(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_empresa_tipo['status']) {
            throw new \Exception($result_empresa_tipo['data'], 409);
        }
        $empresa_tipo = Array();
        foreach ($result_empresa_tipo['data'] as $key => $value) {
            $empresa_tipo[$value['idEmpresaTipo']] = $value;
        }
        /* Entidades Fiscales de la Empresa */
        $result_entidad_fiscal = $this->ContratoModel->getEmpresaEntidadFiscal(Array('idEmpresa' => $idEmpresa));
        if (!$result_entidad_fiscal['status']) {
            throw new \Exception($result_entidad_fiscal['data'], 409);
        }
        $entidades = Array();
        foreach ($result_entidad_fiscal['data'] as $key => $value) {
            $entidades[$value['idEmpresaEntidadFiscal']] = $value;
        }
        /* Catalogo de vendedores */
        $args = Array("idEvento" => $idEvento, "idEdicion" => $idEdicion);
        $result_vendedores = $this->ContratoModel->getVendedores($args);
        if (!$result_vendedores['status']) {
            throw new \Exception($result_vendedores['data'], 409);
        }
        $vendedores = Array();
        foreach ($result_vendedores['data'] as $key => $value) {
            $vendedores[$value['idVendedor']] = $value;
        }
        /* Catalogo de Estatus de Contrato */
        $result_status_contrato = $this->ContratoModel->getStatusContrato();
        if (!$result_status_contrato['status']) {
            throw new \Exception($result_status_contrato['data'], 409);
        }
        $status_contrato = Array();
        foreach ($result_status_contrato['data'] as $key => $value) {
            $status_contrato[$value['idStatusContrato']] = $value;
        }
        /* Obtenemos los paises del PECC */
        $result_paises = $this->get('pecc')->getPaises($lang);
        if (!$result_paises['status']) {
            throw new \Exception($result_paises['data'], 409);
        }
        $estados = array();
        /* Consultas para contrato existente */
        $content['contrato'] = Array();
        $content['completed_step'] = FALSE;
        if ($idContrato > 0) {
            /* Contrato */
            $result_contrato = $this->ContratoModel->getContrato(Array('idEmpresa' => $idEmpresa, 'idContrato' => $idContrato, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion));
            if (!$result_contrato['status']) {
                throw new \Exception($result_contrato['data'], 409);
            }
            $content['contrato'] = $result_contrato['data'][0];
            $contactos_contrato = json_decode($content['contrato']['Contactos'], TRUE);
            $content['contrato']['Contactos'] = Array();
            foreach ($contactos_contrato as $key => $value) {
                $result_contacto = $this->ContratoModel->getContactos(Array('idContacto' => $value['idContacto']));
                if ($result_contacto['status'] && count($result_contacto['data'][0]) > 0) {
                    $content['contrato']['Contactos'][$key] = $result_contacto['data'][0];
                }
            }
            $content['completed_step'] = $content['contrato']['AvanceContrato'];
        }
        
        /* Obtenemos el listado de empresas Padre */
        $args = array();
        $args = array("idEvento"=>$idEvento,"idEdicion"=>$idEdicion);
        $result_parents = $this->ContratoModel->getParents($args);
        if (!$result_parents['status']) {
            throw new \Exception($result_parents['data'], 409);
        }
        $content['empresa_padres'] = Array();
        foreach ($result_parents['data'] as $key => $value) {
            $content['empresa_padres'][$value['idEmpresa']] = $value;
        }
        $content['contacto'] = $contactos;
        $content['empresa_tipo'] = $result_empresa_tipo['data'];
        $content['entidad_fiscal'] = $entidades;
        $content['vendedor'] = $vendedores;
        $content['status_contrato'] = $status_contrato;
        $content['paises'] = $result_paises['data'];
        $content['estados'] = $estados;
        $result_company['data'][0]['idEmpresa'] = $idEmpresa;
        $content['empresa'] = $result_company['data'][0];
        $content['empresa']['idEmpresaPadre'] =$empresa_padre ;
        $content['current_step'] = 'informacion';
        return $this->render('EmpresaContratoBundle:Contrato:informacion.html.twig', array('content' => $content));
    }

    public function espacioAction(Request $request, $idEmpresa, $idContrato) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        if (!$session->has('idEdicion')) {
            return $this->redirectToRoute('dashboard', array('lang' => $lang));
        }
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        $content = array();
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Obtenemos textos de la sección 4 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        if (empty($idEmpresa) || empty($idContrato)) {
            throw new \Exception($content['section_text']['sas_empresaNoEncontrada'] . ' ERROR - NO ID', 409);
        }
        /* Verificamos si la Empresa existe */
        $result_company = $this->ContratoModel->getCompany(Array('idEmpresa' => $idEmpresa));
        if (!$result_company['status'] || !COUNT($result_company['data'])) {
            throw new \Exception($result_company['data'] . ' ' . $content['section_text']['sas_empresaNoEncontrada'], 409);
        }
        /* Stands Libres */
        $result_stands = $this->ContratoModel->getStandsPavellon(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'StandStatus' => "'libre'"), $lang);
        if (!$result_stands['status']) {
            throw new \Exception($result_stands['data'], 409);
        }
        $stands = Array();
        foreach ($result_stands['data'] as $key => $value) {
            $stands[$value['idStand']] = $value;
        }
        /* Pabellones */
        $result_tipo_stand = $this->ContratoModel->getPabellones(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_tipo_stand['status']) {
            throw new \Exception($result_tipo_stand['data'], 409);
        }
        $pabellon = Array();
        foreach ($result_tipo_stand['data'] as $key => $value) {
            $pabellon[$value['idPabellon']] = $value;
        }
        /* Tipos de Stand */
        $result_tipo_stand = $this->ContratoModel->getTipoStand(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_tipo_stand['status']) {
            throw new \Exception($result_tipo_stand['data'], 409);
        }
        $tipo_stand = Array();
        foreach ($result_tipo_stand['data'] as $key => $value) {
            $tipo_stand[$value['idTipoStand']] = $value;
        }
        /* Catalogo de tipos de precio */
        $result_tipo_precio = $this->ContratoModel->getTipoPrecioStand(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_tipo_precio['status']) {
            throw new \Exception($result_vendedores['data'], 409);
        }
        $tipo_precio = Array();
        foreach ($result_tipo_precio['data'] as $key => $value) {
            $tipo_precio[$value['idTipoPrecioStand']] = $value;
        }
        /* Catalogo de tipo precio - tipo stand */
        $result_tipo_precio_tipo_stand = $this->ContratoModel->getTipoPrecioTipoStand(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_tipo_precio_tipo_stand['status']) {
            throw new \Exception($result_tipo_precio_tipo_stand['data'], 409);
        }
        $tipo_precio_tipo_stand = $result_tipo_precio_tipo_stand["data"];
        /* Catalogo de vendedores */
        $result_vendedores = $this->ContratoModel->getVendedores(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_vendedores['status']) {
            throw new \Exception($result_vendedores['data'], 409);
        }
        $vendedores = Array();
        foreach ($result_vendedores['data'] as $key => $value) {
            $vendedores[$value['idUsuario']] = $value;
        }
        /* Catalogo de opciones de Pago */
        $result_opcion_pago = $this->ContratoModel->getOpcionPago(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_opcion_pago['status']) {
            throw new \Exception($result_opcion_pago['data'], 409);
        }
        /* Catalogo de Estatus de Contrato */
        $result_status_contrato = $this->ContratoModel->getStatusContrato();
        if (!$result_status_contrato['status']) {
            throw new \Exception($result_status_contrato['data'], 409);
        }
        $status_contrato = Array();
        foreach ($result_status_contrato['data'] as $key => $value) {
            $status_contrato[$value['idStatusContrato']] = $value;
        }
        /* Contrato */
        $result_contrato = $this->ContratoModel->getContrato(Array('idEmpresa' => $idEmpresa, 'idContrato' => $idContrato, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_contrato['status']) {
            throw new \Exception($result_contrato['data'], 409);
        }
        /* EmpresaStand */
        $result_empresa_stand = $this->ContratoModel->getEmpresaStand(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idContrato' => $idContrato, 'idEmpresa' => $idEmpresa));
        if (!$result_empresa_stand['status']) {
            throw new \Exception($result_empresa_stand['data'], 409);
        }
        /* EmpresaUsuario */
        $result_empresa_usuario = $this->ContratoModel->getEmpresaUsuario($idEmpresa);
        if (!$result_empresa_usuario['status']) {
            throw new \Exception($result_empresa_usuario['data'], 409);
        }
        $content['idUsuario'] = $result_empresa_usuario['data'][0]['idUsuario'];
        $content['contrato'] = $result_contrato['data'][0];
        $content['concepto'] = json_decode($content['contrato']['OtrosConceptos'], true);
        $content['empresa_stand'] = $result_empresa_stand['data'];
        $content['stand'] = $stands;
        $content['pabellon'] = $pabellon;
        $content['tipo_stand'] = $tipo_stand;
        $content['tipo_precio'] = $tipo_precio;
        $content['tipo_precio_tipo_stand'] = $tipo_precio_tipo_stand;
        $content['vendedor'] = $vendedores;
        $content['opcion_pago'] = $result_opcion_pago['data'];
        $content['status_contrato'] = $status_contrato;
        $content['socio_tipo'] = $result_socio_tipo['data'];
        $content['empresa'] = $result_company['data'][0];
        $content['current_step'] = 'espacio';
        $content['completed_step'] = $content['contrato']['AvanceContrato'];
        return $this->render('EmpresaContratoBundle:Contrato:espacio.html.twig', array('content' => $content));
    }

    public function productosAction(Request $request, $idEmpresa, $idContrato) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        if (!$session->has('idEdicion')) {
            return $this->redirectToRoute('dashboard', array('lang' => $lang));
        }
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        $content = array();
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Obtenemos textos de la sección 4 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        if (empty($idEmpresa) || empty($idContrato)) {
            throw new \Exception($result_company['data'] . ' ' . $content['section_text']['sas_empresaNoEncontrada'] . ' ERROR - NO ID', 409);
        }
        /* Verificamos si la Empresa existe */
        $result_company = $this->ContratoModel->getCompany(Array('idEmpresa' => $idEmpresa));
        if (!$result_company['status'] || !COUNT($result_company['data'])) {
            throw new \Exception($result_company['data'] . ' ' . $content['section_text']['sas_empresaNoEncontrada'], 409);
        }
        /* Catalogo de CostoAdicional */
        $result_costo_adicional = $this->ContratoModel->getCostoAdicional(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_costo_adicional['status']) {
            throw new \Exception($result_costo_adicional['data'], 409);
        }
        /* Catalogo de EmpresaCostoAdicional */
        $result_empresa_costo_adicional = $this->ContratoModel->getContratoCostoAdicional(Array('idContrato' => $idContrato));
        if (!$result_empresa_costo_adicional['status']) {
            throw new \Exception($result_empresa_costo_adicional['data'], 409);
        }
        $empresa_costo_adicional = Array();
        foreach ($result_empresa_costo_adicional['data'] as $key => $value) {
            $empresa_costo_adicional[$value['idCostoAdicional']] = $value;
        }
        /* Catalogo de Estatus de Contrato */
        $result_status_contrato = $this->ContratoModel->getStatusContrato();
        if (!$result_status_contrato['status']) {
            throw new \Exception($result_status_contrato['data'], 409);
        }
        $status_contrato = Array();
        foreach ($result_status_contrato['data'] as $key => $value) {
            $status_contrato[$value['idStatusContrato']] = $value;
        }
        /* Contrato */
        $result_contrato = $this->ContratoModel->getContrato(Array('idEmpresa' => $idEmpresa, 'idContrato' => $idContrato, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_contrato['status']) {
            throw new \Exception($result_contrato['data'], 409);
        }

        /* Catalogo de Premios */
        $result_premio = $this->ContratoModel->getPremio();
        if (!$result_premio['status']) {
            throw new \Exception($result_premio['data'], 409);
        }
        $premios = Array();
        foreach ($result_premio['data'] as $key => $value) {
            $premios[$value['idPremio']] = $value;
        }

        $content['contrato'] = $result_contrato['data'][0];
        $content['contrato']['Premios'] = json_decode($content['contrato']['Premios'], true);
        $content['status_contrato'] = $status_contrato;
        $content['empresa'] = $result_company['data'][0];
        $content['costo_adicional'] = $result_costo_adicional['data'];
        $content['empresa_costo_adicional'] = $empresa_costo_adicional;
        $content['premio'] = $premios;
        $content['current_step'] = 'productos';
        $content['completed_step'] = $content['contrato']['AvanceContrato'];
        return $this->render('EmpresaContratoBundle:Contrato:productos.html.twig', array('content' => $content));
    }

    public function resumenAction(Request $request, $idEmpresa, $idContrato) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        if (!$session->has('idEdicion')) {
            return $this->redirectToRoute('dashboard', array('lang' => $lang));
        }
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        $content = array();

        $content['tabPermission'] = json_decode($this->ContratoModel->tabsPermission($user), true);
        /* Permisos para el usuario 45(Vendedor) Autorizar contratos */
        if ($user ['idUsuario'] == 45) {
            $content['tabPermission']['empresa_contrato_resumen']['contract-status']['value'] = 'block';
        }
        $content['currentRoute'] = $request->get('_route');
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Obtenemos textos de la sección 4 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        if (empty($idEmpresa) || empty($idContrato)) {
            throw new \Exception($content['section_text']['sas_empresaNoEncontrada'] . ' ERROR - NO ID', 409);
        }
        /* Obtenemos los datos de la edición */
        $result_edicion = $this->ContratoModel->getEdicion(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_edicion['status']) {
            throw new \Exception($result_edicion['data'] . ' ' . $content['section_text']['sas_empresaNoEncontrada'], 409);
        }
        /* Verificamos si la Empresa existe */
        $result_company = $this->ContratoModel->getCompany(Array('idEmpresa' => $idEmpresa));
        if (!$result_company['status'] || !COUNT($result_company['data'])) {
            throw new \Exception($result_company['data'] . ' ' . $content['section_text']['sas_empresaNoEncontrada'], 409);
        }
        /* Contrato */
        $result_contrato = $this->ContratoModel->getContrato(Array('idEmpresa' => $idEmpresa, 'idContrato' => $idContrato, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_contrato['status']) {
            throw new \Exception($result_contrato['data'], 409);
        }
        /* Entidades Fiscales de la Empresa */
        $result_entidad_fiscal = $this->ContratoModel->getEmpresaEntidadFiscal(Array('idEmpresa' => $idEmpresa));
        if (!$result_entidad_fiscal['status']) {
            throw new \Exception($result_entidad_fiscal['data'], 409);
        }
        $entidades = Array();
        foreach ($result_entidad_fiscal['data'] as $key => $value) {
            $entidades[$value['idEmpresaEntidadFiscal']] = $value;
        }
        /* Empresa Stand */
        $result_empresa_stand = $this->ContratoModel->getEmpresaStand(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idContrato' => $idContrato, 'idEmpresa' => $idEmpresa));
        if (!$result_empresa_stand['status']) {
            throw new \Exception($result_empresa_stand['data'], 409);
        }
        /* Catalogo de opciones de Pago */
        $result_opcion_pago = $this->ContratoModel->getOpcionPago(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_opcion_pago['status']) {
            throw new \Exception($result_opcion_pago['data'], 409);
        }
        $opciones_pago = Array();
        foreach ($result_opcion_pago['data'] as $key => $value) {
            $opciones_pago[$value['idOpcionPago']] = $value;
        }
        /* Catalogo de CostoAdicional */
        $result_costo_adicional = $this->ContratoModel->getCostoAdicional(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_costo_adicional['status']) {
            throw new \Exception($result_costo_adicional['data'], 409);
        }
        /* Catalogo de EmpresaCostoAdicional */
        $result_empresa_costo_adicional = $this->ContratoModel->getContratoCostoAdicional(Array('idContrato' => $idContrato));
        if (!$result_empresa_costo_adicional['status']) {
            throw new \Exception($result_empresa_costo_adicional['data'], 409);
        }
        $empresa_costo_adicional = Array();
        foreach ($result_empresa_costo_adicional['data'] as $key => $value) {
            $empresa_costo_adicional[$value['idCostoAdicional']] = $value;
        }
        /* Catalogo de Estatus de Contrato */
        $result_status_contrato = $this->ContratoModel->getStatusContrato();
        if (!$result_status_contrato['status']) {
            throw new \Exception($result_status_contrato['data'], 409);
        }
        $status_contrato = Array();
        foreach ($result_status_contrato['data'] as $key => $value) {
            $status_contrato[$value['idStatusContrato']] = $value;
        }
        /* Catalogo de vendedores */
        $result_vendedores = $this->ContratoModel->getVendedores(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_vendedores['status']) {
            throw new \Exception($result_vendedores['data'], 409);
        }
        $vendedores = Array();
        foreach ($result_vendedores['data'] as $key => $value) {
            $vendedores[$value['idUsuario']] = $value;
        }
        /* Empresa Tipo */
        $result_empresa_tipo = $this->ContratoModel->getEmpresaTipo(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_empresa_tipo['status']) {
            throw new \Exception($result_empresa_tipo['data'], 409);
        }
        $empresa_tipo = Array();
        foreach ($result_empresa_tipo['data'] as $key => $value) {
            $empresa_tipo[$value['idEmpresaTipo']] = $value;
        }
        /* Socio Tipo */
        $result_socio_tipo = $this->ContratoModel->getSocioTipo();
        if (!$result_socio_tipo['status']) {
            throw new \Exception($result_socio_tipo['data'], 409);
        }
        /* Categorias de la empresa */
        $result_empresa_categoria = $this->ContratoModel->getEmpresaCategoria(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, "idEmpresa" => $idEmpresa));
        if (!$result_socio_tipo['status']) {
            throw new \Exception($result_socio_tipo['data'], 409);
        }
        $socio_tipo = Array();
        foreach ($result_socio_tipo['data'] as $key => $value) {
            $socio_tipo[$value['idSocioTipo']] = $value;
        }
        /* EmpresaUsuario */
        $result_empresa_usuario = $this->ContratoModel->getEmpresaUsuario($idEmpresa);
        if (!$result_empresa_usuario['status']) {
            throw new \Exception($result_empresa_usuario['data'], 409);
        }
        $content['idUsuario'] = $result_empresa_usuario['data'][0]['idUsuario'];
        $content['empresa'] = $result_company['data'][0];
        $content['status_contrato'] = $status_contrato;
        $content['contrato'] = $result_contrato['data'][0];
        $content["otros_conceptos"] = ($content['contrato']['OtrosConceptos'] != null) ? json_decode($content['contrato']['OtrosConceptos'], true) : Array();
        $content['concepto'] = json_decode($content['contrato']['OtrosConceptos'], TRUE);
        $contactos_contrato = json_decode($content['contrato']['Contactos'], TRUE);
        $content['contrato']['Contactos'] = Array();
        foreach ($contactos_contrato as $key => $value) {
            $result_contacto = $this->ContratoModel->getContactos(Array('idContacto' => $value['idContacto']));
            if ($result_contacto['status'] && count($result_contacto['data'][0]) > 0) {
                $content['contrato']['Contactos'][$key] = $result_contacto['data'][0];
            }
        }
        $content['contrato']['Premios'] = json_decode($content['contrato']['Premios'], TRUE);
        $content['entidad_fiscal'] = $entidades[$content['contrato']['idEmpresaEntidadFiscal']];
        $content['empresa_stand'] = $result_empresa_stand['data'];
        $content['opcion_pago'] = $opciones_pago[$content['contrato']['idOpcionPago']];
        $content['costo_adicional'] = $result_costo_adicional['data'];
        $content['empresa_costo_adicional'] = $empresa_costo_adicional;
        $content['current_step'] = 'resumen';
        $content['completed_step'] = $content['contrato']['AvanceContrato'];
        $content['vendedor'] = $vendedores[$content['idUsuario']];
        $content['lang'] = $lang;
        $content['edicion'] = $result_edicion['data'][0];
        $content['empresa_tipo'] = $empresa_tipo[$content['empresa']['idEmpresaTipo']];
        $content['socio_tipo'] = $socio_tipo[$content['contrato']['idTipoSocio']];
        $content['empresa_categoria'] = $result_empresa_categoria["data"];
        #return $this->render('EmpresaContratoBundle:PDF:base.html.twig', array('content' => $content));//TEMPORAL PDF
        /* Verificar si ya existe un pdf guardado y el contrato esta en etapa de 3.-Guardado en este caso solo se consulta el pdf */
        if ($content['contrato']['idStatusContrato'] == 3 && strlen($content['contrato']['ContratoPDF']) > 0) {
            $this->createPDF($content['contrato']['ContratoPDF'], Array('idEmpresa' => $idEmpresa, 'Name' => $content['empresa']['DC_NombreComercial']));
        } else {
            $content['ruta'] = substr($request->getUriForPath("login"), 0, -5);
            $html = $this->renderView('EmpresaContratoBundle:PDF:base.html.twig', array('content' => $content));
            $pdf = $this->createTCPDF($html, Array('idEmpresa' => $idEmpresa, 'Name' => $content['empresa']['DC_NombreComercial']));
            $result_update_contrato = $this->ContratoModel->setContrato(Array('idEmpresa' => $idEmpresa, 'idContrato' => $idContrato, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion), Array('ContratoPDF' => "'" . $pdf . "'"));
            if (!$result_update_contrato['status']) {
                throw new \Exception($result_update_contrato['data'], 409);
            }
            $this->createPDF($pdf, Array('idEmpresa' => $idEmpresa, 'Name' => $content['empresa']['DC_NombreComercial']));
        }
        return $this->render('EmpresaContratoBundle:Contrato:resumen.html.twig', array('content' => $content));
    }

    public function entidadNuevoAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $res = $this->ContratoModel->insertEntidad($post);
            $result['data'] = $res['data'];
            if ($res['status']) {
                $result['status'] = TRUE;
                $data = Array(
                    'idEmpresaEntidadFiscal' => $res['data'][0]['idEmpresaEntidadFiscal'],
                    'idEmpresa' => $post['idEmpresa'],
                    'DF_RazonSocial' => $post['RazonSocial'],
                    'DF_RFC' => $post['RFC'],
                    /* 'DF_RepresentanteLegal' => $post['RepresentanteLegal'],
                      'DF_Email' => $post['Email'], */
                    'DF_Pais' => $post['Pais'],
                    'DF_Estado' => $post['Estado'],
                    'DF_idPais' => $post['idPais'],
                    'DF_idEstado' => $post['idEstado'],
                    'DF_Ciudad' => $post['Ciudad'],
                    'DF_Colonia' => $post['Colonia'],
                    'DF_Calle' => $post['Calle'],
                    /* 'DF_NumeroExterior' => $post['NumeroExterior'],
                      'DF_NumeroInterior' => $post['NumeroInterior'], */
                    'DF_CodigoPostal' => $post['CodigoPostal'],
                );
                $result['data'] = $data;
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function contactoNuevoAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            $result['data'] = $general_text['data'];
            return $this->jsonResponse($result);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $result_contacto_email = $this->ContratoModel->getContactos(Array("idEmpresa" => $post['idEmpresa'], "Email" => "'" . $post['Email'] . "'", "Nombre" => "'" . $post['Nombre'] . "'", "ApellidoPaterno" => "'" . $post['ApellidoPaterno'] . "'"));
            if ($result_contacto_email['status'] && count($result_contacto_email['data']) > 0) {
                $result['data'] = $content['general_text']['sas_emailContactoYaExiste'];
                return $this->jsonResponse($result);
            }
            $res = $this->ContratoModel->insertContacto($post);
            $result['data'] = $res['data'];
            if ($res['status']) {
                $result['status'] = TRUE;
                $post['idContacto'] = $res['data'][0]['idContacto'];
                $result['data'] = $post;
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function guardarInformacionAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $post['idEvento'] = $session->get('idEvento');
            $post['idEdicion'] = $session->get('idEdicion');
            $res = $this->ContratoModel->saveInformation($post);
            $result['data'] = $res['data'];
            if ($res['status']) {
                $result['status'] = TRUE;
                $post['idContrato'] = $res['data'][0]['fn_sas_ContratoInformacion'];
                $result['data'] = $post;
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function guardarEspacioAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $post['idEvento'] = $session->get('idEvento');
            $post['idEdicion'] = $session->get('idEdicion');
            $res = $this->ContratoModel->saveEspacio($post);
            $result['data'] = $res['data'];
            if ($res['status']) {
                $result['status'] = TRUE;
                if (COUNT($res['data'])) {
                    foreach ($res['data'] as $key => $value) {
                        $result['data'][$value['idStand']] = $value;
                    }
                }
                $result['data'] = $res['data'];
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function eliminarEspacioAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $post['idEvento'] = $session->get('idEvento');
            $post['idEdicion'] = $session->get('idEdicion');
            $res = $this->ContratoModel->deleteEspacio($post);
            $result['data'] = $res['data'];
            if ($res['status']) {
                $result['status'] = TRUE;
                $stand = explode(",", $res['data'][0]['fn_sas_DesasignarStandContrato']);
                /* Stands Libres */
                $result_stands = $this->ContratoModel->getStands(Array('idEvento' => $post['idEvento'], 'idEdicion' => $post['idEdicion'], 'StandStatus' => "'libre'", 'idStand' => (int) $stand[0]));
                $result['data'] = $result_stands['data'][0];
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function guardarProductosAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $res = $this->ContratoModel->saveProducts($post);
            $result['data'] = $res['data'];
            if ($res['status']) {
                $result['status'] = TRUE;
                $result['data'] = $res['data'];
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    private function createPDF($content, $data) {
        $path = '../web/administrador/contratos/' . $data['idEmpresa'] . '_' . $data['Name'] . '.pdf';
        $pdf_decoded = base64_decode($content);
        $pdf = fopen($path, 'w');
        fwrite($pdf, $pdf_decoded);
        fclose($pdf);
    }

    private function createTCPDF($html, $data) {
        $pdf = $this->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetAuthor('Infoexpo');
        //$pdf->SetTitle(str_replace(" ", "", $data['post']['idEmpresa'] . '_' . $data['post']['DC_NombreComercial'] . '.pdf'));
        //$pdf->SetTitle('titulo del PDF');
        //$pdf->SetSubject('');
        $pdf->SetKeywords('Contrato, Contratc');

        // remove default header/footer
        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(PDF_MARGIN_LEFT, '10', PDF_MARGIN_RIGHT);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        //$pdf->SetFont('helvetica', '', 9, '', false);
        $pdf->AddPage();

        $pdf->writeHTML($html, true, false, true, false, '');
        $file = $pdf->Output(str_replace(" ", "", realpath('administrador/contratos') . "/" . $data['idEmpresa'] . '_' . $data['Name'] . '.pdf'), 'S');
        $pdf_encode = base64_encode($file);
        return $pdf_encode;
    }

    public function statusAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $post['idEvento'] = $session->get('idEvento');
            $post['idEdicion'] = $session->get('idEdicion');
            if ($post['status'] == 4) {
                $res = $this->ContratoModel->autContract($post);
                if ($res['status'] && $res['data'][0]['fn_sas_AutorizarContrato'] == 1) {
                    $result['status'] = TRUE;
                }
                $result['data'] = $res['data'][0]['fn_sas_AutorizarContrato'];
            } else {
                $res = $this->ContratoModel->canContract($post);
                if ($res['status'] && $res['data'][0]['fn_sas_CancelarContrato'] == 1) {
                    $result['status'] = TRUE;
                }
                $result['data'] = $res['data'][0]['fn_sas_CancelarContrato'];
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function importContactAction() {
        $result_importacion_contratos = $this->ContratoModel->getImportacionContratos();
        if (!$result_importacion_contratos['status']) {
            die($result_importacion_contratos['data']);
        }
        foreach ($result_importacion_contratos['data'] as $key => $value) {
            if ($value['idEmpresa'] != "") {
                $c_array = Array(
                    1 => Array(
                        "idContacto" => (string) $value['idContactoPrincipal'],
                        "Nombre" => $value['CE_Nombre'],
                        "ApellidoPaterno" => $value['CE_ApellidoPaterno'],
                        "ApellidoMaterno" => $value['CE_ApellidoMaterno'],
                        "Puesto" => $value['CE_Puesto'],
                        "Email" => $value['CE_Email'],
                        "Telefono" => $value['CE_Telefono']
                    ),
                    2 => Array(
                        "idContacto" => (string) $value['idContactoPrincipal'],
                        "Nombre" => $value['CE_Nombre'],
                        "ApellidoPaterno" => $value['CE_ApellidoPaterno'],
                        "ApellidoMaterno" => $value['CE_ApellidoMaterno'],
                        "Puesto" => $value['CE_Puesto'],
                        "Email" => $value['CE_Email'],
                        "Telefono" => $value['CE_Telefono']
                    )
                );
                $result_update = $this->ContratoModel->updateContactos(Array('idEmpresa' => $value['idEmpresa'], 'json' => json_encode($c_array)));
                if (!$result_update['status']) {
                    echo "<b>ERROR UPDATE CONTACTOS:<b>" . $result_update['data'] . "<br>";
                    echo json_encode($c_array);
                }
                echo "id: " . $value['idEmpresa'] . "<br>";
            }
        }
        return $this->render('EmpresaContratoBundle:Contrato:import.html.twig');
    }

    public function importBoothAction() {
        $result_importacion_contratos = $this->ContratoModel->getImportacionContratos();
        if (!$result_importacion_contratos['status']) {
            die($result_importacion_contratos['data']);
        }
        foreach ($result_importacion_contratos['data'] as $key => $value) {
            if ($value['idEmpresa'] != "") {
                $booths = explode(",", $value['ListadoStands']);
                $booth_list = Array();
                echo "--------------<br>";
                foreach ($booths as $j => $b) {
                    if ($b != "") {
                        $result_booth = $this->ContratoModel->getStands(Array('StandNumber' => "'" . $b . "'"));
                        if (!$result_booth['status']) {
                            echo '<b>ERROR CONSULTANDO STAND' . $b . '</b>: ' . $result_booth['data'] . '<br>';
                        }
                        if (isset($result_booth['data'][0]['idStand']) && $result_booth['data'][0]['idStand'] != "") {
                            $booth_list[$result_booth['data'][0]['idStand']] = Array(
                                "idStand" => $result_booth['data'][0]['idStand'],
                                "EtiquetaStand" => $value['DC_NombreComercial'],
                                "Precio" => '0.00',
                                "idTipoPrecioStand" => $value['idTipoPrecioStand'],
                                "PrecioModificado" => 'false'
                            );
                            echo "std: " . $result_booth['data'][0]['idStand'] . "-" . $b . "<br>";
                        }
                    }
                }
                $result_update = $this->ContratoModel->updateStands(Array('idEmpresa' => $value['idEmpresa'], 'json' => json_encode($booth_list)));
                echo "id: " . $value['idEmpresa'] . "<br>";
            }
        }
        return $this->render('EmpresaContratoBundle:Contrato:import.html.twig');
    }

    public function contractsAction() {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set("companyOrigin", "ventas");
        $content = array();

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
        $content["Contrato_table_column_categories"] = $this->ContratoConfiguration->getColumnCategories($content['section_text']);
        $content["Contrato_table_columns"] = $this->ContratoConfiguration->getContractMetaData($content['section_text']);
        $session->set('columns_contrato', $content["Contrato_table_columns"]);
        return $this->render('EmpresaContratoBundle:Contrato:contratos.html.twig', array('content' => $content));
    }

    /* ---  Listado de contratos con filtros 24/Feb Eduardo Cervantes  --- */

    public function mostarTablaContratosAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->TextoModel->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['routeName'] = $request->get('_route');

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
        /* ---  para pintar el breadcrumb al inicio  --- */
        $content['breadcrumb'] = $this->ContratoModel->breadcrumb($request->get('_route'), $lang);

        $content["Contrato_table_column_categories"] = $this->ContratoConfiguration->getColumnCategories($content['section_text']);
        $content["Contrato_table_columns"] = $this->ContratoConfiguration->getContractMetaData($content['section_text']);
        $session->set('columns_contratos', $content["Contrato_table_columns"]);

        return $this->render('EmpresaContratoBundle:ListaContratos:lista_contratos.html.twig', array('content' => $content));
    }

    public function obtenerTablaAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->TextoModel->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        $session->set("edicion_contratos", $idEdicion);
        $session->remove('seting-dt');

        $post = $request->request->all();

        $params = Array();
        $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
        $result_count = $this->ContratoModel->getCountContrato(Array(), $params);

        if (!$result_count["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting all records"));
        } else {
            $session->set('qry_count', $result_count["count"]);
        }
        $total_records = $records_filtered = $result_count["data"][0]["total"];

        $result_build = $this->buildParamsAndColumnFromDTColumns($post["columns"], $lang, $idEdicion);
        $params = Array("where" => $result_build["params"]);

        $columns = $result_build["columns"];
        $column_defs = $this->ContratoConfiguration->getContractMetaData($this->TextoModel->getTexts($lang, self::SECTION)['data'], $lang, $idEdicion);

        $param_raw_name = "";
        $raw_columns = Array();
        foreach ($columns as $column) {
            $param_raw_name = str_replace("\"", "", $column);
            if (array_key_exists($param_raw_name, $column_defs)) {
                $raw_columns[$param_raw_name] = $column_defs[$param_raw_name]["text"];
            } else {
                $raw_columns[$param_raw_name] = $param_raw_name;
            }
        }


        if (array_key_exists("where", $params) && COUNT($params["where"]) > 0) {

            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
            $result_filtered_count = $this->ContratoModel->getCountContrato($columns, $params);
            if (!$result_filtered_count["status"]) {
                return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting filtered records"));
            } else {
                $session->set('count_filtered', $result_filtered_count["count"]);
            }
            $records_filtered = $result_filtered_count["data"][0]["total"];
        } else {
            $session->remove('count_filtered');
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
        }

        $order = $this->buildOrderColumns($post["order"], $raw_columns, $lang, $idEdicion);
        $result_query = $this->ContratoModel->getContratoCustom($columns, $params, $order, $post["length"], $post["start"]);
        if (!$result_query["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error getting records"));
        } else {
            $session->set('data_qry', $result_query["data_qry"]);
        }
        $data = $this->matchColumnDefsData($result_query["data"], $lang, $idEdicion);

        $response_dt = Array(
            "status" => TRUE,
            "draw" => $post["draw"],
            "recordsTotal" => $total_records,
            "recordsFiltered" => $records_filtered,
            "data" => $data
        );
        return $this->jsonResponse($response_dt);
    }

    public function obtenerTablaFiltroAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->TextoModel->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $idEdicion = $session->get('idEdicion');
        $edicion_contratos = $session->get("edicion_contratos");
        if ($idEdicion != $edicion_contratos) {
            $session->remove('seting-dt');
            $session->remove('qry_count');
            $session->remove('count_filtered');
            $session->remove('data_qry');
        }
        $session->set("edicion_contratos", $idEdicion);

        $post = $request->request->all();
        if ($session->has('qry_count')) {
            $qry_count = $session->get('qry_count');
            $result_count = $this->ContratoModel->getCountContrato(Array(), $qry_count["params"], $qry_count["qry"]);
        } else {
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
            $result_count = $this->ContratoModel->getCountContrato(Array(), $params);
        }
        if (!$result_count["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting all records"));
        } else {
            $session->set('qry_count', $result_count["count"]);
        }
        $total_records = $records_filtered = $result_count["data"][0]["total"];

        $result_build = $this->buildParamsAndColumnFromDTColumns($post["columns"], $lang, $idEdicion);
        $params = Array("where" => $result_build["params"]);
        $columns = $result_build["columns"];
        $column_defs = $this->ContratoConfiguration->getContractMetaData($this->TextoModel->getTexts($lang, self::SECTION)['data'], $lang, $idEdicion);

        $param_raw_name = "";
        $raw_columns = Array();
        foreach ($columns as $column) {
            $param_raw_name = str_replace("\"", "", $column);
            if (array_key_exists($param_raw_name, $column_defs)) {
                $raw_columns[$param_raw_name] = $column_defs[$param_raw_name]["text"];
            } else {
                $raw_columns[$param_raw_name] = $param_raw_name;
            }
        }

        if ($session->has("count_filtered")) {
            $count_filtered = $session->get('count_filtered');
            $params = $count_filtered["params"];
        }

        if (array_key_exists("where", $params) && COUNT($params["where"]) > 0) {
            if ($session->has("count_filtered")) {
                $count_filtered = $session->get('count_filtered');
                $result_filtered_count = $this->ContratoModel->getCountContrato($columns, $count_filtered["params"], $count_filtered["qry"]);
            } else {
                $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
                $result_filtered_count = $this->ContratoModel->getCountContrato($columns, $params);
            }
            if (!$result_filtered_count["status"]) {
                return $this->jsonResponse(Array("status" => FALSE, "error" => "Error counting filtered records"));
            } else {
                $session->set('count_filtered', $result_filtered_count["count"]);
            }
            $records_filtered = $result_filtered_count["data"][0]["total"];
        } else {
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
        }

        $order = $this->buildOrderColumns($post["order"], $raw_columns, $lang, $idEdicion);
        if ($session->has("data_qry")) {
            $data_qry = $session->get('data_qry');
            $result_query = $this->ContratoModel->getContratoCustom($columns, $data_qry["params"], $order, $post["length"], $post["start"]);
        } else {
            $result_query = $this->ContratoModel->getContratoCustom($columns, $params, $order, $post["length"], $post["start"]);
        }
        if (!$result_query["status"]) {
            return $this->jsonResponse(Array("status" => FALSE, "error" => "Error getting records"));
        } else {
            $session->set('data_qry', $result_query["data_qry"]);
        }

        $data = $this->matchColumnDefsData($result_query["data"], $lang, $idEdicion);
        $response_dt = Array(
            "status" => TRUE,
            "draw" => $post["draw"],
            "recordsTotal" => $total_records,
            "recordsFiltered" => $records_filtered,
            "data" => $data
        );
        return $this->jsonResponse($response_dt);
    }

    public function exportarDatosAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->TextoModel->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get("lang");
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);

        date_default_timezone_set("America/Mexico_City");

        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_Contratos " . date('d-m-Y G.i');

        $post = $request->request->all();
        $post_data = json_decode(str_replace('\"', '"', $post["post_data"]), TRUE);

        $result_build = $this->buildParamsAndColumnFromDTColumns($post_data["columns"], $lang, $idEdicion);
        $params = Array("where" => $result_build["params"]);
        if (array_key_exists("where", $params) && COUNT($params["where"]) > 0) {
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR, "clause" => "AND");
        } else {
            $params["where"][] = Array("name" => '"idEdicion"', "operator" => "=", "value" => $idEdicion, "type" => \PDO::PARAM_STR);
        }

        $columns = $result_build["columns"];
        $column_defs = $this->ContratoConfiguration->getContractMetaData($section_text, $lang, $idEdicion);

        $param_raw_name = "";
        $raw_columns = Array();
        foreach ($post_data["columns"] as $column) {
            if ($column["data"] == "img_edit") {
                continue;
            }
            if (array_key_exists($column["data"], $column_defs)) {
                $raw_columns[$column["data"]] = $column_defs[$column["data"]]["text"];
            } else {
                $raw_columns[$column["data"]] = $param_raw_name;
            }
        }

        $order = $this->buildOrderColumns($post["order"], $raw_columns, $lang, $idEdicion);
        if ($session->has("data_qry")) {
            $data_qry = $session->get('data_qry');
            $params["where"] = $data_qry["params"]["where"];
            $result_query = $this->ContratoModel->getContratoCustom($columns, $data_qry["params"], $order);
        } else {
            $result_query = $this->ContratoModel->getContratoCustom($columns, $params, $order);
        }
        $subheader = $section_text["data"]["sas_totalRegistros"] . " " . count($result_query["data"]);
        if (!$result_query["status"]) {
            die("Error getting records");
        }

        $data = $this->matchColumnDefsData($result_query["data"], $lang, $idEdicion);

        $header_report = "";
        $r = array("\"", "'", "jp", "jv", "%", "{", "}", "->>");
        if (COUNT($params["where"]) > 0) {
            $header_report = $section_text["data"]["sas_filtrosAplicados"] . " ";
            foreach ($params["where"] as $param) {
                $param_raw_name = str_replace($r, "", $param["name"]);

                if (array_key_exists($param_raw_name, $column_defs)) {
                    $header_report .= $column_defs[$param_raw_name]["text"] . ": ";
                    if (
                            array_key_exists("is_select", $column_defs[$param_raw_name]["filter_options"]) && $column_defs[$param_raw_name]["filter_options"]["is_select"] && is_array($column_defs[$param_raw_name]["filter_options"]["values"]) && COUNT($column_defs[$param_raw_name]["filter_options"]["values"]) > 0
                    ) {
                        $header_report .= $column_defs[$param_raw_name]["filter_options"]["values"][$param["value"]];
                    } else {
                        $header_report .= str_replace($r, "", $param["value"]);
                    }
                }
                $header_report .= ", ";
            }
        }
        $header_report = substr($header_report, 0, strlen($header_report) - 2);
        if ($header_report == "Filtros Aplicados: ") {
            $header_report .= "Ninguno";
        }

        return $this->excelReport($data, $raw_columns, $file_name, $header_report, $subheader);
    }

    public function excelReport($general, $table_metadata, $filename, $header, $subheader) {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("Infoexpo")
                ->setTitle($filename)
                ->setSubject($filename)
                ->setDescription($filename);
        $flag = 1;
        $lastColumn = "A";
        $phpExcelObject->setActiveSheetIndex(0)->mergeCells('A1:Z1');
        $phpExcelObject->setActiveSheetIndex(0)->mergeCells('A2:Z2');
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lastColumn . $flag, $header);
        $flag++;
        $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lastColumn . $flag, $subheader);
        $flag += 2;
        foreach ($table_metadata as $key => $value) {
            $phpExcelObject->getActiveSheet()->getColumnDimension($lastColumn)->setAutoSize(true);
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lastColumn . $flag, $value);
            $lastColumn++;
        }
        $flag++;
        foreach ($general as $index) {
            $lastColumn = "A";
            foreach ($index as $key => $value) {
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lastColumn . $flag, $value);
                $lastColumn++;
            }$flag++;
        }

        $phpExcelObject->getActiveSheet()->getStyle("A1:" . $lastColumn . "4")->getFont()->setBold(true);
        $phpExcelObject->setActiveSheetIndex(0);

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename . ".xlsx");

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        $response->headers->set('Expires', '0');

        return $response;
    }

    function matchColumnDefsData($records, $lang, $idEdicion) {
        $data = Array();

        $column_defs = $this->ContratoConfiguration->getContractMetaData($this->TextoModel->getTexts($lang, self::SECTION)['data'], $lang, $idEdicion);
        //En caso de que algún campo cuente con múltiples valores le seteamos el correspondiente en base a la definición de los valores de la columna
        if (is_array($records) && COUNT($records) > 0) {
            foreach ($records as $record) {
                $row = Array();
                foreach ($column_defs as $column_name => $column_values) {
                    if (array_key_exists($column_name, $record)) {
                        if (
                                array_key_exists("is_select", $column_values["filter_options"]) && $column_values["filter_options"]["is_select"] && is_array($column_values["filter_options"]["values"]) && COUNT($column_values["filter_options"]["values"]) > 0
                        ) {
                            $row[$column_name] = $column_values["filter_options"]["values"][$record[$column_name]];
                        } else {
                            $row[$column_name] = $record[$column_name];
                        }
                    }
                }
                $data[] = $row;
            }
        }
        return $data;
    }

    public function buildParamsAndColumnFromDTColumns($dt_columns, $lang, $idEdicion) {
        $result_bind = Array("params" => Array(), "columns" => Array());
        $column_defs = $this->ContratoConfiguration->getContractMetaData($this->TextoModel->getTexts($lang, self::SECTION)['data'], $lang, $idEdicion);

        $total_columns = 0;
        //Seteamos columnas a consultar y los parámetros where
        foreach ($dt_columns as $column) {
            foreach ($column_defs as $column_raw_name => $column_values) {
                if (strtolower($column["name"]) == strtolower($column_values["text"])) {
                    $column_alias = "";
                    if (array_key_exists("json_column", $column_values["filter_options"]) && is_string($column_values["filter_options"]["json_column"]) && $column_values["filter_options"]["json_column"] != "") {
                        $column_name = '"' . $column_values["filter_options"]["json_column"] . '"->>' . '\'' . $column_raw_name . '\'';
                        $column_alias = ' AS "' . $column_raw_name . '"';
                    } else {
                        $column_name = '"' . $column_raw_name . '"';
                    }
                    $result_bind["columns"][] = $column_name . $column_alias;
                    if ($column["searchable"]) {
                        if (is_array($column["search"]) && trim($column["search"]["value"]) != "") {
                            $operator = "";
                            $value = "";
                            if (strpos($column["search"]["value"], "op:") !== FALSE) {
                                $char_long = strlen($column["search"]["value"]);
                                /* position of ";" delimiter, is initialized in 3 because (op:)=3positions */
                                $end_delimiter_pos = 3;
                                for ($i = (strpos("op:", "op:") + 3); $i < $char_long; $i++) {
                                    if ($column["search"]["value"][$i] == ";") {
                                        break;
                                    }
                                    $value .= $column["search"]["value"][$i];
                                    $end_delimiter_pos++;
                                }
                                $operator = $value;
                                $value = trim(substr($column["search"]["value"], $end_delimiter_pos + 1));
                            } else {
                                $operator = "=";
                                $value = $column["search"]["value"];

                                if (array_key_exists("search_operator", $column_values["filter_options"])) {
                                    switch (strtolower($column_values["filter_options"]["search_operator"])) {
                                        case "ilike":
                                            $operator = "ilike";
                                            $value = "%" . $column["search"]["value"] . "%";
                                            break;
                                        case "@>":
                                            $operator = "@>";
                                            $value = "{" . $column["search"]["value"] . "}";
                                            break;
                                    }
                                }
                            }
                            $filter = Array("name" => $column_name, "operator" => $operator, "value" => $value, "type" => \PDO::PARAM_STR);
                            if ($total_columns++ > 0) {
                                $filter["clause"] = "AND";
                            }
                            $result_bind["params"][] = $filter;
                        }
                    }
                }
            }
        }
        return $result_bind;
    }

    public function buildOrderColumns($order_columns, $all_columns, $lang, $idEdicion) {
        //Seteamos columnas a ordenar
        $column_defs = $this->ContratoConfiguration->getContractMetaData($this->TextoModel->getTexts($lang, self::SECTION)['data'], $lang, $idEdicion);

        $order = Array();
        if (is_array($order_columns) && COUNT($order_columns) > 0) {
            //parse order colums
            foreach ($order_columns as $order_column) {
                $column_index = 0;
                foreach ($all_columns as $column_name => $column_text) {
                    if ($order_column["column"] == $column_index++) {
                        if (array_key_exists($column_name, $column_defs)) {
                            if (array_key_exists("json_column", $column_defs[$column_name]["filter_options"]) && is_string($column_defs[$column_name]["filter_options"]["json_column"]) && $column_defs[$column_name]["filter_options"]["json_column"] != "") {
                                $column_name = '"' . $column_defs[$column_name]["filter_options"]["json_column"] . '"->>' . '\'' . $column_name . '\'';
                            } else {
                                $column_name = '"' . $column_name . '"';
                            }
                            $order[] = Array("name" => $column_name, "dir" => $order_column["dir"]);
                        }
                    }
                }
            }
        }
        return $order;
    }

    public function obtenerSesionAction(Request $request) {
        $session = $request->getSession();
        $data = array("seting" => $session->get('seting-dt'), "param" => $session->get('data_qry')["params"]["where"], "columns" => $session->get('columns_contratos'));
        return $this->jsonResponse($data);
    }

    public function establecerSesionAction(Request $request) {
        $session = $request->getSession();
        $post = $request->request->all();
        $session->set('seting-dt', $post);
        return $this->jsonResponse($post);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
