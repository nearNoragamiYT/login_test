<?php

namespace ShowDashboard\RS\DatosGeneralesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\RS\VisitanteBundle\Model\VisitanteConfiguration;
use ShowDashboard\RS\DatosGeneralesBundle\Model\DatosGeneralesModel;
use ShowDashboard\RS\DatosGeneralesBundle\Model\VisitantePerfilRSConfiguration;

class DatosGeneralesController extends Controller {

    protected $TextoModel, $DatosGeneralesModel, $configuracion;

    const TEMPLATE = array("DatosGenerales" => 4, "AdminVisitantes" => 9);
    const MAIN_ROUTE = "visitante";

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->configuracion = new VisitantePerfilRSConfiguration();
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
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Obtenemos textos del Template AE_DatosGenerales */
        $section_text = $this->DatosGeneralesModel->getTexts($lang, self::TEMPLATE['DatosGenerales']);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['template_text'] = $section_text['data'];
//
//        /* Verificamos si tiene permiso en el modulo seleccionado */
//        if ($session->get('OriginView') == "elite") {
//            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "elite");
//        }
//        if ($session->get('OriginView') == "visitante") {
//            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "visitante");
//        }
//        if (!$breadcrumb) {
//            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
//            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
//        }
//        $content['breadcrumb'] = $breadcrumb;

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
        
        /* Obtenemos los Tipos de Visitante */
        $result_visitanteTipo = $this->DatosGeneralesModel->getVisitanteTipo();
        if (!$result_visitanteTipo['status']) {
            throw new \Exception($result_visitanteTipo['data'], 409);
        }
        $content['visitanteTipo'] = $result_visitanteTipo['data'];

        $content['titulos'] = $this->configuracion->getTitulos();

        if (!empty($content['visitante']['DE_idPais'])) {
            $result_estados = $this->get('pecc')->getEstados($content['visitante']['DE_idPais']);
            if (!$result_estados['status']) {
                throw new \Exception($result_estados['data'], 409);
            }
            $content['estados'] = $result_estados['data'];
        }

        /* Lo que se agrego del Bundle de VisitantePerfil */
        /* Obtenemos textos del Template AE_AdminVisitantes */
        $section_text = $this->DatosGeneralesModel->getTexts($lang, self::TEMPLATE['AdminVisitantes']);

        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];

        /* Obtenemos Filtros Visitante */
        $content['filtros_generales'] = $this->configuracion->getVisitorFilters();

        /* Obtenemos Encuesta de la edicion */
        $encuesta = $this->DatosGeneralesModel->getEncuesta(array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'Activa' => 1));
        if (!$encuesta['status']) {
            throw new \Exception($encuesta['data'], 409);
        }
        $content['encuesta'] = $encuesta['data'];
        /* Lo que se agrego del Bundle de Perfil */
        $preguntas = $this->DatosGeneralesModel->getQuestions(array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$preguntas['status']) {
            throw new \Exception($preguntas['data'], 409);
        }
        $content['preguntas'] = $preguntas['data'];
        
        $respuestas = $this->DatosGeneralesModel->getAnswers($idEvento, $idEdicion);
        if (!$respuestas['status']) {
            throw new \Exception($respuestas['data'], 409);
        }
        $content['respuestas'] = $respuestas['data'];

        $profile = $this->DatosGeneralesModel->getProfile(array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idVisitante' => $idVisitante));
        if (!$profile['status']) {
            throw new \Exception($profile['data'], 409);
        }

        $perfil = array();
        foreach ($profile['data'] as $key => $value) {
            if ($value != '') {
                $perfil[$content['respuestas'][$key]['idPregunta']][$key] = $value;
            } else {
                $perfil[$content['respuestas'][$key]['idPregunta']][$key] = '';
            }
        }

        $content['profile'] = $perfil;

        /* lista de eventos */
        $result_eventos = $this->DatosGeneralesModel->getEvento($lang);
        if (!$result_eventos['status']) {
            throw new \Exception($result_eventos['data'], 409);
        }
        $content['Evento'] = $result_eventos[data];
        //        $content['EventoActual'] = $idEvento;

        /* lista ediciones */
        $result_ediciones = $this->DatosGeneralesModel->getEdicion($lang);
        if (!$result_ediciones['status']) {
            throw new \Exception($result_ediciones['data'], 409);
        }
        $content['Edicion'] = $result_ediciones[data];


        /* lista lecturas */
        $result_lecturas = $this->DatosGeneralesModel->getLecturas($idEdicion, $idVisitante);
        if (!$result_lecturas['status']) {
            throw new \Exception($result_lecturas['data'], 409);
        }
        $content['Lecturas'] = $result_lecturas[data];       

        array_push($content["breadcrumb"], Array("Modulo_" . strtoupper($lang) => $content['visitante']['NombreCompleto'], "Ruta" => "", 'Permisos' => array()));
        return $this->render('ShowDashboardRSDatosGeneralesBundle:DatosGenerales:DatosGenerales.html.twig', array('content' => $content));
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

        $elite = $this->DatosGeneralesModel->set_elite($idEdicion, $post['idVisitante'], $post['ClubElite']);
        if (!$elite['status']) {
            throw new \Exception($elite['data'], 409);
        }
        unset($post['ClubElite']);

        $stringData = $this->DatosGeneralesModel->createString($post);

        $result_inserted = $this->DatosGeneralesModel->insertEditVisitante($stringData, $idEvento, $idEdicion, $post['idVisitante']);
        if (!$result_inserted['status']) {
            throw new \Exception($result_inserted['data'], 409);
        }
        $visitante = $result_inserted;

//        $result_syncFM = $this->DatosGeneralesModel->syncFMVisitante($visitante['data'][0], $idEvento, $idEdicion);
//        if (!$result_syncFM['status']) {
//            throw new \Exception($result_syncFM['data'], 409);
//        }


        return $this->jsonResponse($visitante);
    }

    public function getDataTableAction(Request $request) {
        $this->DatosGeneralesModel = new DatosGeneralesModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        if ($request->getMethod() != 'POST') {
            throw new \Exception("No allowed method", 409);
        }

        $post = $request->request->all();
        $where = '';

        /* Obtenemos Filtros Visitante */
        $filtros_generales = $this->configuracion->getVisitorFilters();

        /* Obtenemos Encuesta de la edicion */
        $encuesta = $this->DatosGeneralesModel->getEncuesta(array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'Activa' => 1));
        if (!$encuesta['status']) {
            throw new \Exception($encuesta['data'], 409);
        }
        $encuesta = $encuesta['data'];

        /* Count total de registros Consultados */
        $result_count = $this->DatosGeneralesModel->getCount($encuesta);
        if (!$result_count['status']) {
            throw new \Exception($result_count['data'], 409);
        }
        $result_count = $result_count['data'];

        /* Count de registros Filtrados */
        $result_count_filter = array();
        $where .= 'WHERE ';
        $where .= $this->setWhereGeneral($post['general_filter'], $filtros_generales, $post['logic']);

        if (array_key_exists('check_filter', $post)) {
            if (strlen($where) != 6)
                $where .= ' ' . $post['logic'] . ' ';
            $where .= $this->setWhereEncuesta($post['check_filter'], $post['logic']);
        }

        if (strlen($where) != 6) {
            $result_count_filter = $this->DatosGeneralesModel->getCount($encuesta, $where);
            if (!$result_count_filter['status']) {
                throw new \Exception($result_count_filter['data'], 409);
            }
            $result_count_filter = $result_count_filter['data'];
        } else
            $where = '';

        $session->set('where_perfil', $where);

        /* Obtenemos Datos Consulta Paginada */
        $result_query = $this->DatosGeneralesModel->getCustom($filtros_generales, $encuesta, $post, $where);
        if (!$result_query['status']) {
            throw new \Exception($result_query['data'], 409);
        }
        $result_query = $result_query['data'];

        $data = array();
        foreach ($result_query as $key => $value) {
            foreach ($value as $colum) {
                $data[$key][] = (string) $colum;
            }
        }

        if (empty($result_count_filter))
            $result_count_filter[0]['count'] = $result_count[0]['count'];

        $response_dt = Array(
            "draw" => $post["draw"],
            "recordsTotal" => $result_count[0]['count'],
            "recordsFiltered" => $result_count_filter[0]['count'],
            "data" => $data
        );

        return $this->jsonResponse($response_dt);
    }

    public function setWhereGeneral($param, $data, $logic) {
        $where = '';
        foreach ($param as $value) {
            if ($value['value'] != '') {
                if ($data[$value['name']]['filter_options']['search_operator'] == 'ilike')
                    $where .= $data[$value['name']]['table'] . '.' . '"' . $value['name'] . '" ILIKE \'%' . $value['value'] . '%\' ' . $logic . ' ';
                else
                    $where .= $data[$value['name']]['table'] . '.' . '"' . $value['name'] . '" ' . $data[$value['name']]['filter_options']['search_operator'] . $value['value'] . ' ' . $logic . ' ';
            }
        }
        $where = substr($where, 0, -(strlen($logic) + 1));
        return $where;
    }

    public function setWhereEncuesta($param, $logic) {
        $where = '';
        foreach ($param as $value) {
            $where .= 'demo."' . $value['name'] . '" = 1 ' . $logic . ' ';
        }
        $where = substr($where, 0, -(strlen($logic) + 1));
        return $where;
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function getStatsAction(Request $request) {
        $this->DatosGeneralesModel = new DatosGeneralesModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $where = $session->get('where_perfil');

        $encuesta = $this->DatosGeneralesModel->getStats(array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'Activa' => 1), $where);
        if (!$encuesta['status']) {
            throw new \Exception($encuesta['data'], 409);
        }
        $content['status'] = true;
        $content['data'] = $encuesta['data'];

        return $this->jsonResponse($content);
    }

}
