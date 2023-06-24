<?php

namespace Visitante\DatosGeneralesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use Visitante\VisitanteBundle\Model\VisitanteConfiguration;
use Visitante\DatosGeneralesBundle\Model\DatosGeneralesModel;

class DatosGeneralesController extends Controller {

    protected $TextoModel, $DatosGeneralesModel, $configuracion;

    const TEMPLATE = 4;
    const MAIN_ROUTE = "visitante";

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->configuracion = new VisitanteConfiguration();
    }

    public function DatosGeneralesAction(Request $request, $idVisitante) {
        $this->DatosGeneralesModel = new DatosGeneralesModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $content = array();
        $content['idVisitante'] = $idVisitante;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEvento'] = $idEvento;
        $content['idEdicion'] = $idEdicion;
        $content["breadcrumb"] = array();
        $content['view'] = $session->get('OriginView');
        $content["breadcrumb"] = $this->DatosGeneralesModel->breadcrumb($session->get('OriginView'), $lang);
        $content['idEdicion'] = $session->get('idEdicion');
        //print($session->get('OriginView'));die("X_X");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Obtenemos textos del Template AE_DatosGenerales */
        $section_text = $this->DatosGeneralesModel->getTexts($lang, self::TEMPLATE);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['template_text'] = $section_text['data'];

        /* Obtenemos cargos AE */
        $result_cargo = $this->DatosGeneralesModel->getCargo();
        if (!$result_cargo['status']) {
            throw new \Exception($result_cargo['data'], 409);
        }
        $content['cargo'] = $result_cargo['data'];

        /* Obtenemos area AE */
        $result_area = $this->DatosGeneralesModel->getArea();
        if (!$result_area['status']) {
            throw new \Exception($result_area['data'], 409);
        }
        $content['area'] = $result_area['data'];

        /* Obtenemos NombreComercial AE */
        $result_nc = $this->DatosGeneralesModel->getNombreComercial();
        if (!$result_nc['status']) {
            throw new \Exception($result_nc['data'], 409);
        }
        $content['nc'] = $result_nc['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado */
        if ($session->get('OriginView') == "elite") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "elite");
        }
        if ($session->get('OriginView') == "visitante") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "visitante");
        }
        if ($session->get('OriginView') == "asociados") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "asociados");
        }
        if ($session->get('OriginView') == "compradores") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "visitante_comprador");
        }
        if ($session->get('OriginView') == "prensa") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "prensa");
        }
        if ($session->get('OriginView') == "registro_multiple") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "registro_multiple");
        }
        if ($session->get('OriginView') == "encuentro_negocios") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "encuentro_negocios");
        }
        if ($session->get('OriginView') == "visitantes_generales") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "visitantes_generales");
        }
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content['breadcrumb'] = $breadcrumb;
        $content["idModuloIxpo"] = $breadcrumb[0]['idModuloIxpo'];
        /* Obtenemos los paises del PECC */
        $result_paises = $this->get('pecc')->getPaises($lang);
        if (!$result_paises['status']) {
            throw new \Exception($result_paises['data'], 409);
        }
        $content['paises'] = $result_paises['data'];

        /* Obtenemos datos del Visitante */
        $result_visitante = $this->DatosGeneralesModel->getVisitante($content);

        if (!$result_visitante['status']) {
            throw new \Exception($result_visitante['data'], 409);
        }
        $content['visitante'] = $result_visitante['data']['0'];

        if ($session->get('OriginView') == "prensa") {
            ini_set('max_execution_time', 1800);
            ini_set('memory_limit', '-1');
            $result_cuentas = $this->DatosGeneralesModel->getCuentasGafetePrensa($content);
            if (!$result_cuentas['status']) {
                throw new \Exception($result_cuentas['data'], 409);
            }
            $content['cuentasGafete'] = $result_cuentas['data']['0'];
        }
        if ($session->get('OriginView') == "compradores") {
            ini_set('max_execution_time', 1800);
            ini_set('memory_limit', '-1');
            $result_cuentas = $this->DatosGeneralesModel->getCuentasGafeteCompradores($content);
            if (!$result_cuentas['status']) {
                throw new \Exception($result_cuentas['data'], 409);
            }
            $content['cuentasGafete'] = $result_cuentas['data']['0'];
        }
        if ($session->get('OriginView') == "asociados") {
            ini_set('max_execution_time', 1800);
            ini_set('memory_limit', '-1');
            $result_cuentas = $this->DatosGeneralesModel->getCuentasGafete($content);
            if (!$result_cuentas['status']) {
                throw new \Exception($result_cuentas['data'], 409);
            }
            $content['cuentasGafete'] = $result_cuentas['data']['0'];
        }
        if ($session->get('OriginView') == "registro_multiple") {
            ini_set('max_execution_time', 1800);
            ini_set('memory_limit', '-1');

            $result_descargar = $this->DatosGeneralesModel->getDownloadGafeteMultipleCount($content);
            if (!$result_descargar['status']) {
                throw new \Exception($result_descargar['data'], 409);
            }
            $content['cuentasGafete']['NumeroDescargas'] = $result_descargar['data']['0']['count'];

            $result_enviar = $this->DatosGeneralesModel->getSendGafeteMultipleCount($content);
            if (!$result_enviar['status']) {
                throw new \Exception($result_enviar['data'], 409);
            }
            $content['cuentasGafete']['NumeroEnvios'] = $result_enviar['data']['0']['count'];
        }
        if ($session->get('OriginView') == "encuentro_negocios") {
            ini_set('max_execution_time', 1800);
            ini_set('memory_limit', '-1');

            $result_descargar = $this->DatosGeneralesModel->getDownloadGafeteEncuentroCount($content);
            if (!$result_descargar['status']) {
                throw new \Exception($result_descargar['data'], 409);
            }
            $content['cuentasGafete']['NumeroDescargas'] = $result_descargar['data']['0']['count'];

            $result_enviar = $this->DatosGeneralesModel->getSendGafeteEncuentroCount($content);
            if (!$result_enviar['status']) {
                throw new \Exception($result_enviar['data'], 409);
            }
            $content['cuentasGafete']['NumeroEnvios'] = $result_enviar['data']['0']['count'];
        }
        if ($session->get('OriginView') == "visitante") {
            ini_set('max_execution_time', 1800);
            ini_set('memory_limit', '-1');

            $result_descargar = $this->DatosGeneralesModel->getDownloadGafeteMultipleCount($content);
            if (!$result_descargar['status']) {
                throw new \Exception($result_descargar['data'], 409);
            }
            $content['cuentasGafete']['NumeroDescargas'] = $result_descargar['data']['0']['count'];

            $result_enviar = $this->DatosGeneralesModel->getSendGafeteMultipleCount($content);
            if (!$result_enviar['status']) {
                throw new \Exception($result_enviar['data'], 409);
            }
            $content['cuentasGafete']['NumeroEnvios'] = $result_enviar['data']['0']['count'];
        }
        $count_send = $this->DatosGeneralesModel->lastUpdateSend($content);
        if (!$count_send['status']) {
            throw new \Exception($count_send['data'], 409);
        }
        $fechasend = strtotime($count_send['data'][0]['FechaModificacion']);
        $ultimoSend = date("d-m-Y", $fechasend);

        $content['countSend'] = $ultimoSend;
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '-1');
        $count_download = $this->DatosGeneralesModel->lastUpdateDownload($content);
        if (!$count_download['status']) {
            throw new \Exception($count_download['data'], 409);
        }
        $fechaDownload = strtotime($count_download['data'][0]['FechaModificacion']);
        $ultimoDownload = date("d-m-Y", $fechaDownload);
        $content['countDownload'] = $ultimoDownload;
        $content['titulos'] = $this->configuracion->getTitulos();
        $resultArea = $this->DatosGeneralesModel->getAreaMultiple();
        $content['AreaMultiple'] = $resultArea['data'];

        if (!empty($content['visitante']['DE_idPais'])) {
            $result_estados = $this->get('pecc')->getEstados($content['visitante']['DE_idPais']);
            if (!$result_estados['status']) {
                throw new \Exception($result_estados['data'], 409);
            }
            $content['estados'] = $result_estados['data'];
        }
        array_push($content["breadcrumb"], Array("Modulo_" . strtoupper($lang) => $content['visitante']['NombreCompleto'], "Ruta" => "", 'Permisos' => $content["breadcrumb"][0]["Permisos"]));
        return $this->render('VisitanteDatosGeneralesBundle:DatosGenerales:DatosGenerales.html.twig', array('content' => $content));
    }

    public function updateGeneralDataAction(Request $request) {
        $this->DatosGeneralesModel = new DatosGeneralesModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        if ($request->getMethod() != 'POST') {
            return Array('status' => FALSE, 'error' => "No allowed access method");
        }

        $post = $request->request->all();
        if (!empty($post['DE_idPais'])) {
            $result_paises = $this->get('pecc')->getPaises($lang);
            if (!$result_paises['status']) {
                throw new \Exception($result_paises['data'], 409);
            }
            $post['DE_Pais'] = $result_paises['data'][$post['DE_idPais']]['Pais_ES'];
        }

        if (!empty($post['DE_idEstado'])) {
            $result_estados = $this->get('pecc')->getEstados($post['DE_idPais']);
            if (!$result_estados['status']) {
                throw new \Exception($result_estados['data'], 409);
            }
            $post['DE_Estado'] = $result_estados['data'][$post['DE_idEstado']]['Estado'];
        }
        $post['CadenaUnica'] = $this->DatosGeneralesModel->sanear_string(strtolower($post['Nombre']) . strtolower($post['ApellidoPaterno']) . strtolower($post['Email']));


        $stringData = $this->DatosGeneralesModel->createString($post);
        $result_inserted = $this->DatosGeneralesModel->insertEditVisitante($stringData, $idEvento, $idEdicion, $post['idVisitante']);
        if (!$result_inserted['status']) {
            throw new \Exception($result_inserted['data'], 409);
        }
        $visitante = $result_inserted;

        return $this->jsonResponse($visitante);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}