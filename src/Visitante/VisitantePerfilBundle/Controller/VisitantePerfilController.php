<?php

namespace Visitante\VisitantePerfilBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Utilerias\TextoBundle\Model\TextoModel;
use Visitante\VisitantePerfilBundle\Model\VisitantePerfilModel;
use Visitante\VisitantePerfilBundle\Model\VisitantePerfilConfiguration;

class VisitantePerfilController extends Controller {

    protected $TextoModel, $perfilModel, $configuracion;

    const TEMPLATE = 9;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->configuracion = new VisitantePerfilConfiguration();
    }

    public function VisitantePerfilAction(Request $request) {
        $this->perfilModel = new VisitantePerfilModel($this->container);
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

        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content["breadcrumb"] = $breadcrumb;

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Obtenemos textos del Template AE_AdminVisitantes */
        $section_text = $this->perfilModel->getTexts($lang, self::TEMPLATE);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];

        /* Obtenemos Filtros Visitante */
        $content['filtros_generales'] = $this->configuracion->getVisitorFilters();

        /* Obtenemos Encuesta de la edicion */
        $encuesta = $this->perfilModel->getEncuesta(array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'Activa' => 1));
        if (!$encuesta['status']) {
            throw new \Exception($encuesta['data'], 409);
        }
        $content['encuesta'] = $encuesta['data'];

        return $this->render('VisitanteVisitantePerfilBundle:VisitantePerfil:lista_perfil.html.twig', array('content' => $content));
    }

    public function getDataTableAction(Request $request) {
        $this->perfilModel = new VisitantePerfilModel($this->container);
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
        $encuesta = $this->perfilModel->getEncuesta(array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'Activa' => 1));
        if (!$encuesta['status']) {
            throw new \Exception($encuesta['data'], 409);
        }
        $encuesta = $encuesta['data'];

        /* Count total de registros Consultados */
        $result_count = $this->perfilModel->getCount($encuesta);
        if (!$result_count['status']) {
            throw new \Exception($result_count['data'], 409);
        }
        $result_count = $result_count['data'];

        /* Count de registros Filtrados */
        $result_count_filter = array();
        $where .= 'WHERE ';
        $where .= $this->setWhereGeneral($post['general_filter'], $post['general_filter_chip'], $filtros_generales, $post['logic']);

        if (array_key_exists('check_filter', $post)) {
            if (strlen($where) != 6)
                $where .= ' ' . $post['logic'] . ' ';
            $where .= $this->setWhereEncuesta($post['check_filter'], $post['logic']);
        }

        if (strlen($where) != 6) {
            $result_count_filter = $this->perfilModel->getCount($encuesta, $where);
            if (!$result_count_filter['status']) {
                throw new \Exception($result_count_filter['data'], 409);
            }
            $result_count_filter = $result_count_filter['data'];
        } else
            $where = '';

        $session->set('where_perfil', $where);

        /* Obtenemos Datos Consulta Paginada */
        $result_query = $this->perfilModel->getCustom($filtros_generales, $encuesta, $post, $where);
        if (!$result_query['status']) {
            throw new \Exception($result_query['data'], 409);
        }
        $session->set('order_perfil', $result_query['order']);
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

    public function setWhereGeneral($param_input, $param_chip, $data, $logic) {
        $where = '';
        if (!is_null($param_chip)) {
            foreach ($param_chip as $key => $value) {
                foreach ($value as $chip) {
                    if ($data[$key]['filter_options']['search_operator'] == 'ilike')
                        $where .= $data[$key]['table'] . '.' . '"' . $key . '" ILIKE \'%' . $chip['tag'] . '%\' ' . $logic . ' ';
                    else
                        $where .= $data[$key]['table'] . '.' . '"' . $key . '" ' . $data[$key]['filter_options']['search_operator'] . $chip['tag'] . ' ' . $logic . ' ';
                }
            }
        }
        foreach ($param_input as $value) {
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
        $this->perfilModel = new VisitantePerfilModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $where = $session->get('where_perfil');

        $encuesta = $this->perfilModel->getStats(array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'Activa' => 1), $where);
        if (!$encuesta['status']) {
            throw new \Exception($encuesta['data'], 409);
        }
        $content['status'] = true;
        $content['data'] = $encuesta['data'];

        return $this->jsonResponse($content);
    }

    public function ExportarAction(Request $request) {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '-1');
        date_default_timezone_set("America/Mexico_City");
               
        $this->perfilModel = new VisitantePerfilModel($this->container);
        $session = $request->getSession();
        $get = $request->query->all();
        $filtros = $this->configuracion->getVisitorFilters();
        $headers = array();
        $select = '';

        foreach ($get as $key => $value) {
            $select .= $filtros[$key]['table'] . '."' . $key . '",';
            $headers[] = $filtros[$key]['text'];
        }
        $select = substr($select, 0, -1);
        $where = $session->get('where_perfil');
        $order = $session->get('order_perfil');

        $data = $this->perfilModel->getExport(array('select' => $select, 'where' => $where, 'order' => $order));
        if (!$data['status']) {
            throw new \Exception($data['data'], 409);
        }
        $data = $data['data'];

        $file_name = str_replace(" ", "_", $session->get('edicion')['Edicion_EN']) . "_Exportar_Visitantes_Perfil_" . date('d-m-Y G.i');
        return $this->excelReport($data, $headers, $file_name);
    }

    public function excelReport($general, $table_metadata, $filename) {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("Infoexpo")
                ->setTitle($filename)
                ->setSubject($filename)
                ->setDescription($filename);
        $flag = 1;
        $lastColumn = "A";
        foreach ($table_metadata as $value) {
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

        $phpExcelObject->getActiveSheet()->getStyle("A1:" . $lastColumn . "1")->getFont()->setBold(true);
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

}
