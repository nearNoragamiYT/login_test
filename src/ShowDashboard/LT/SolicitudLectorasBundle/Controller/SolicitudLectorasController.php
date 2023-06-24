<?php

namespace ShowDashboard\LT\SolicitudLectorasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\LT\SolicitudLectorasBundle\Model\SolicitudLectorasModel;

class SolicitudLectorasController extends Controller {

    protected $text, $idPlataformaIxpo = 6, $model;
    protected $url = "https://expoantad.infoexpo.com.mx/2022/ed/web/utilerias/info/";

    public function __construct() {
        $this->model = new SolicitudLectorasModel();
        $this->text = new TextoModel();
    }

    const SECTION = 9, idForma = 401;

    public function mostrarAction(Request $request, $idEmpresa) {
        $session = $request->getSession();
        /* ---  si la edicion se perdiÃ³ lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $App = $this->get('ixpo_configuration')->getApp();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;
        /* Obtenemos textos generales */
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Obtenemos textos de la seccion 9 */
        $section_text = $this->text->getTexts($lang, self::SECTION);
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
        $content['estados'] = array();
        /* Comienza la logica propia del Action */
        $content["idEmpresa"] = $idEmpresa;
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
        $args = Array('p."idEdicion"' => $idEdicion);
        $content["packages"] = $this->model->getPackages($args);
        $args = Array('e."idEmpresa"' => $idEmpresa, 'ee."idEdicion"' => $idEdicion);
        $content["header"] = $this->model->getCompanyHeader($args);
        /* Consultamos los servicios de la edicion */
        $args = Array('"idEdicion"' => $idEdicion, '"idForma"' => self::idForma);
        $content['Services'] = $this->model->getServicios($args);
        /* Detalle de la forma de Lectoras */
        $args = Array('"idEmpresa"' => $idEmpresa, '"idEdicion"' => $idEdicion, '"idForma"' => self::idForma);
        $result_solicitud_detalle = $this->model->getSolicitudLectoras($args);
        if (count($result_solicitud_detalle) == 0) {
            $data = array();
            $data = Array('idEmpresa' => $idEmpresa,
                'idForma' => self::idForma,
                'idEvento' => $idEvento,
                'idEdicion' => $idEdicion,
                'Lang' => "'" . $lang . "'");
            $empresaForma = $this->model->generateEMFO($data);
            if (!$empresaForma['status']) {
                throw new \Exception($empresaForma['data'], 409);
            }
            $args = Array('"idEmpresa"' => $idEmpresa, '"idEdicion"' => $idEdicion, '"idForma"' => self::idForma);
            $result_solicitud_detalle = $this->model->getSolicitudLectoras($args);
        }
        $link = $this->model->getDataLink($idEdicion, $idEvento, self::idForma, $idEmpresa);
        // $content['linkSolicitudLectorasED'] = "https://expoantad.infoexpo.com.mx/2020/ed/web/utilerias/info/1/207/AtmAsXCZo7/es";
        $langForma = !empty($link['Lang']) ? $link['Lang'] : 'es';
        $content['linkSolicitudLectorasED'] = $this->url.$idEdicion."/".self::idForma."/".$link['Token']."/". $langForma;
        $content['solicitud_lectoras'] = json_decode($result_solicitud_detalle["DetalleServicioJSON"], true);
        $detalle_pago = json_decode($result_solicitud_detalle['DetallePagoJSON'], true);
        $content['DetallePago'] = $detalle_pago;
        $content['contactoPago'] = $this->model->getContactoPago($session->get('idEdicion'), $idEmpresa, $detalle_pago['idContacto']);
        $content['SolicitudLectora'] = $result_solicitud_detalle;
        /* $content['StatusPagoForma'] = $result_solicitud_detalle['StatusPago'];
          $content['idFormaPago'] = $result_solicitud_detalle['idFormaPago'];
          $content['FechaActualizacionStatusPago'] = $result_solicitud_detalle['FechaActualizacionStatusPago'];
          $content['lang_forma'] = $result_solicitud_detalle['Lang']; */
        /* Entidades Fiscales de la Empresa */
        $result_entidad_fiscal = $this->model->getEmpresaEntidadFiscal(Array('idEmpresa' => $idEmpresa));
        if (!$result_entidad_fiscal['status']) {
            throw new \Exception($result_entidad_fiscal['data'], 409);
        }
        $entidades = Array();
        foreach ($result_entidad_fiscal['data'] as $key => $value) {
            $entidades[$value['idEmpresaEntidadFiscal']] = $value;
        }
        $content['entidad_fiscal'] = $entidades;
        $content['solicitud_entidad_fiscal'] = $detalle_pago['idEmpresaEntidadFiscal'];
        $content['solicitud_lista_entidades_fiscales'] = $detalle_pago['ListaEmpresaEntidaFiscal'];

        /* Consultamos el catalogo de status de pago */
        $result_status_pago = $this->model->getStatusPago();
        if (!$result_status_pago['status']) {
            throw new \Exception($result_status_pago['data'], 409);
        }
        $statuspago = Array();
        foreach ($result_status_pago['data'] as $key => $value) {
            $statuspago[$value['idStatusPago']] = $value;
        }
        $content['StatusPago'] = $statuspago;
        /* Consultamos el catalogo de formas de pago */
        $result_forma_pago = $this->model->getFormaPago();
        if (!$result_forma_pago['status']) {
            throw new \Exception($result_forma_pago['data'], 409);
        }
        $formapago = Array();
        foreach ($result_forma_pago['data'] as $key => $value) {
            $formapago[$value['idFormaPago']] = $value;
        }
        $content['FormaPago'] = $formapago;
        /* Verificamos si tiene permiso en el modulo seleccionado */
        // if ($session->get("companyOrigin") == "lectoras") {
        //     $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "lista_expositores");
        // }
        // if ($session->get("companyOrigin") == "solicitud_lectoras") {
        //     $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "solicitud_lectora_reporte");
        // }
        // if (!$breadcrumb) {
        //     $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
        //     return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        // }
        // $content["breadcrumb"] = $breadcrumb;
        array_push($content["breadcrumb"], Array("Modulo_" . strtoupper($lang) => $content["header"]["DC_NombreComercial"], "Ruta" => "", 'Permisos' => array()));
        $content['companyOrigin'] = $session->get("companyOrigin");
        return $this->render('ShowDashboardLTSolicitudLectorasBundle:SolicitudLectoras:mostrar.html.twig', array("content" => $content));
    }

    public function solicitarLectorasAction(Request $request, $idEmpresa) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        /* Comienza la logica propia del Action */
        $post = $request->request->all();
        $post['StatusForma'] = 1;
        $post['ModificacionComite'] = 1;
        //$post['OrigenEmpresaForma'] = 2; //La columna no existe en EmpresaForma
        /* Detalle de la forma de Lectoras */
        $args = Array('"idEmpresa"' => $idEmpresa, '"idEdicion"' => $idEdicion, '"idForma"' => self::idForma);
        $result_solicitud_detalle = $this->model->getSolicitudLectoras($args);
        if ($result_solicitud_detalle['StatusForma'] == 0) {
            date_default_timezone_set('America/Mexico_City');
            $post['FechaPrimerGuardado'] = date("Y-m-d");
        } else {
            $post['FechaPrimerGuardado'] = $result_solicitud_detalle["FechaPrimerGuardado"];
        }
        $where = Array(
            "idEvento" => $idEvento,
            "idEdicion" => $idEdicion,
            "idEmpresa" => $idEmpresa,
            "idForma" => self::idForma
        );
        $this->model->solicitarLectoras($post, $where);
        $this->model->insertarEmpresaFormaLog($user, $idEdicion, self::idForma, $idEmpresa);

        return $this->jsonResponse(Array("status" => true));
    }

    public function actualizarEstatusPagoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;

        /* Obtenemos textos generales */
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Comienza la logica propia del Action */
        $idEdicion = $session->get('idEdicion');
        $post = $request->request->all();
        $result = $this->model->updateStatusPago(array("StatusPago" => $post['status_pago']), array("idEmpresa" => $post['idEmpresa'], "idForma" => self::idForma));
        if ($result['status']) {
            $result['status'] = TRUE;
            $result['data'] = $post;
        } else {
            $result['error'] = $general_text['data']['sas_errorPeticion'];
        }

        return $this->jsonResponse($result);
    }

    public function actualizarFormaPagoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;

        /* Obtenemos textos generales */
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Comienza la logica propia del Action */
        $idEdicion = $session->get('idEdicion');

        $args = $post = $request->request->all();
        unset($args['idEmpresa']);
        $temp = strpos($args['HistorialPagoAcumulado'], $args['PagoAcumulado']);
        if ($temp === false) {
            if ($args['HistorialPagoAcumulado'] != '') {
                $args['HistorialPagoAcumulado'] = $args['HistorialPagoAcumulado'] . ',' . $args['PagoAcumulado'];
            } else {
                $args['HistorialPagoAcumulado'] = $args['PagoAcumulado'];
            }
        }
        $result = $this->model->updateDetallePago(array("DetallePagoJSON" => "'" . json_encode($args) . "'"), array("idEmpresa" => $post['idEmpresa'], "idForma" => 401, "idEdicion" => $idEdicion));
        if ($result['status']) {
            $result['status'] = TRUE;
            $result['data'] = $post;
        } else {
            $result['error'] = $general_text['data']['sas_errorPeticion'];
        }
        return $this->jsonResponse($result);
    }

    public function actualizarFechaPagoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;

        /* Obtenemos textos generales */
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Comienza la logica propia del Action */
        $idEdicion = $session->get('idEdicion');

        $post = $request->request->all();
        $result = $this->model->updateFechaPago(array("FechaActualizacionStatusPago" => "'" . $post['fecha_pago'] . "'"), array("idEmpresa" => $post['idEmpresa'], "idForma" => self::idForma, "idEdicion" => $idEdicion));
        if ($result['status']) {
            $result['status'] = TRUE;
            $result['data'] = $post;
        } else {
            $result['error'] = $general_text['data']['sas_errorPeticion'];
        }

        return $this->jsonResponse($result);
    }

    public function actualizarDetallePagoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;

        /* Obtenemos textos generales */
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Comienza la logica propia del Action */
        $idEdicion = $session->get('idEdicion');

        $args = $post = $request->request->all();
        unset($args['idEmpresa']);
        $temp = strpos($args['HistorialPagoAcumulado'], $args['PagoAcumulado']);
        if ($temp === false) {
            if ($args['HistorialPagoAcumulado'] != '') {
                $args['HistorialPagoAcumulado'] = $args['HistorialPagoAcumulado'] . ',' . $args['PagoAcumulado'];
            } else {
                $args['HistorialPagoAcumulado'] = $args['PagoAcumulado'];
            }
        }

        $result = $this->model->updateDetallePago(array("DetallePagoJSON" => "'" . json_encode($args) . "'"), array("idEmpresa" => $post['idEmpresa'], "idForma" => 401, "idEdicion" => $idEdicion));
        if ($result['status']) {
            $result['status'] = TRUE;
            $result['data'] = $post;
        } else {
            $result['error'] = $general_text['data']['sas_errorPeticion'];
        }

        return $this->jsonResponse($result);
    }

    public function NuevaEntidadFiscalAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $res = $this->model->insertEntidad($post);
            $result['data'] = $res['data'];
            if ($res['status']) {
                $result['status'] = TRUE;
                $data = Array(
                    'idEmpresaEntidadFiscal' => $res['data'][0]['idEmpresaEntidadFiscal'],
                    'idEmpresa' => $post['idEmpresa'],
                    'DF_RazonSocial' => $post['RazonSocial'],
                    'DF_RFC' => $post['RFC'],
                    'DF_RepresentanteLegal' => $post['RepresentanteLegal'],
                    'DF_Email' => $post['Email'],
                    //'DF_Telefono' => $post['Telefono'],
                    //'DF_Puesto' => $post['Puesto'],
                    'DF_Pais' => $post['Pais'],
                    'DF_Estado' => $post['Estado'],
                    'DF_idPais' => $post['idPais'],
                    'DF_idEstado' => $post['idEstado'],
                    'DF_Ciudad' => $post['Ciudad'],
                    'DF_Colonia' => $post['Colonia'],
                    'DF_Calle' => $post['Calle'],
                    'DF_NumeroExterior' => $post['NumeroExterior'],
                    'DF_NumeroInterior' => $post['NumeroInterior'],
                    'DF_CodigoPostal' => $post['CodigoPostal'],
                );
                $result['data'] = $data;
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function guardarEntidaFiscalAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;
        /* Obtenemos textos generales */
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Comienza la logica propia del Action */
        $idEdicion = $session->get('idEdicion');
        $post = $request->request->all();
        /* Detalle de la forma de Lectoras */
        $arg = array();
        $args = Array('"idEmpresa"' => $post['idEmpresa'], '"idEdicion"' => $idEdicion, '"idForma"' => 401);
        $result_solicitud_detalle = $this->model->getSolicitudLectoras($args);
        $content['solicitud_lectoras'] = json_decode($result_solicitud_detalle["DetalleServicioJSON"], true);
        $detalle_pago = json_decode($result_solicitud_detalle['DetallePagoJSON'], true);
        $detalle_pago['ListaEmpresaEntidaFiscal'] = $post['ListaEmpresaEntidaFiscal'];
        $result = $this->model->updateDetallePago(array("DetallePagoJSON" => "'" . json_encode($detalle_pago) . "'"), array("idEmpresa" => $post['idEmpresa'], "idForma" => 401, "idEdicion" => $idEdicion));
        if ($result['status']) {
            $result['status'] = TRUE;
            $result['data'] = $post;
        } else {
            $result['error'] = $general_text['data']['sas_errorPeticion'];
        }

        return $this->jsonResponse($result);
    }

    public function jsonResponse($data) {
        return new JsonResponse($data);
    }

}
