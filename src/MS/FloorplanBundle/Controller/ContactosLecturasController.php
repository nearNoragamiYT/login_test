<?php

namespace MS\FloorplanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use MS\FloorplanBundle\Model\ContactosLecturasModel;

/**
 * Description of ContactosLecturasController
 *
 * @author Ernesto L <ernestol@infoexpo.com.mx>
 */
class ContactosLecturasController extends Controller {

    protected $Textos;

    const SECTION = 8;
    const TEMPLATE = 25;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->ContactosLecturasModel = new ContactosLecturasModel();
    }

    public function configGridAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $args['text']['previousText'] = $lang == 'es' ? 'Anterior' : 'Previous'; //String
        $args['text']['nextText'] = $lang == 'es' ? 'Siguiente' : 'Next'; //String
        $args['text']['loadingText'] = $lang == 'es' ? 'Cargando...' : 'Loading...'; //String
        $args['text']['noDataText'] = $lang == 'es' ? 'No se encontraron Registros' : 'No data found'; //String
        $args['text']['pageText'] = $lang == 'es' ? 'Página' : 'Page'; //String
        $args['text']['ofText'] = $lang == 'es' ? 'de' : 'of'; //String
        $args['text']['rowsText'] = $lang == 'es' ? 'registros' : 'rows'; //String
        $args['text']['usuario'] = $lang == 'es' ? 'Usuario' : 'User'; //String
        $args['text']['fecha'] = $lang == 'es' ? 'Fecha' : 'Date'; //String
        $args['text']['hora'] = $lang == 'es' ? 'Hora' : 'Hour'; //String
        $args['text']['contacto'] = $lang == 'es' ? 'Contacto' : 'Lead'; //String
        $args['text']['nombre'] = $lang == 'es' ? 'Nombre' : 'Name'; //String
        $args['text']['apellidop'] = $lang == 'es' ? 'Apellido Paterno' : 'First Surname'; //String
        $args['text']['apellidom'] = $lang == 'es' ? 'Apellido Materno' : 'Second Surname'; //String
        $args['text']['empresa'] = $lang == 'es' ? 'Empresa' : 'Company'; //String
        $args['text']['area'] = $lang == 'es' ? 'Area' : 'Area'; //String
        $args['text']['giro'] = $lang == 'es' ? 'Giro' : 'Order'; //String
        $args['text']['sector'] = $lang == 'es' ? 'Sector' : 'Sector'; //String
        $args['text']['subcategoria'] = $lang == 'es' ? 'Subcategoría' : 'Subcategory'; //String
        $args['text']['puesto'] = $lang == 'es' ? 'Cargo' : 'Position'; //String
        $args['text']['email'] = $lang == 'es' ? 'Email' : 'Email'; //String
        $args['text']['tipolectora'] = $lang == 'es' ? 'Tipo de Lectora' : 'Scanner type'; //String
        $args['text']['direccion'] = $lang == 'es' ? 'Dirección' : 'Address'; //String
        $args['text']['colonia'] = $lang == 'es' ? 'Colonia' : 'Zone'; //String
        $args['text']['cp'] = $lang == 'es' ? 'C.P.' : 'Zip Code'; //String
        $args['text']['ciudad'] = $lang == 'es' ? 'Ciudad' : 'City'; //String
        $args['text']['estado'] = $lang == 'es' ? 'Estado' : 'State'; //String
        $args['text']['pais'] = $lang == 'es' ? 'País' : 'Country'; //String
        $args['text']['tags'] = $lang == 'es' ? 'Tags' : 'Tags'; //String
        $args['text']['comments'] = $lang == 'es' ? 'Comentarios' : 'Comments'; //String
        $args['text']['rankings'] = $lang == 'es' ? 'Rankings' : 'Rankings'; //String
        $args['text']['telefono'] = $lang == 'es' ? 'Teléfono' : 'Phone'; //String
        $args['text']['select'] = $lang == 'es' ? 'Selecciona los campos a visualizar en la tabla' : 'Select the fields to visualize on the table'; //String
        $args['text']['label'] = $lang == 'es' ? 'Campos a visualizar' : 'Fields to view'; //String
        $args['text']['todos'] = $lang == 'es' ? 'Todos' : 'All'; //String
        $args['text']['descarga'] = $lang == 'es' ? 'Descarga Total de Contactos en Excel' : 'Download Complete List of Contacts in Excel'; //String
        $args['text']['minSelect'] = $lang == 'es' ? 'Se deben seleccionar minimo 4 campos a la vez' : 'You must have at least 4 fields selected at a time'; //String
        $args['text']['maxSelect'] = $lang == 'es' ? 'Se pueden visualizar máximo 8 campos a la vez' : 'You can display up to 8 fields at a time'; //String
        $args['pageSize'] = 10; //Integer
        $result['data'] = $args;
        $result['status'] = true;
        return $this->response($result);
    }

    public function exhibitorLeadsAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $args['textNoArea'] = $lang == 'es' ? 'Sin Area' : 'Without Area';
        $args['textNoGiro'] = $lang == 'es' ? 'Sin Giro' : 'Without Order';
        $args['textNoSector'] = $lang == 'es' ? 'Sin Sector' : 'Without Sector';
        $args['textNoSubcategoria'] = $lang == 'es' ? 'Sin Subcategoría' : 'Without Subcategory';
//        $args['idEdicion'] = 1;
//        $args['idEvento'] = 1;
//        $args['idEmpresa'] = 42;
        $args['idEdicion'] = $session->get('idEdicion');
        $args['idEdicionSecond'] = $session->get('idEvento') == 1 ? $session->get('idEdicion') + 1 : $session->get('idEdicion') - 1;
        $args['idEvento'] = $session->get('idEvento');
        $args['idEventoSecond'] = $session->get('idEvento') == 1 ? $session->get('idEvento') + 1 : $session->get('idEvento') - 1;
        $args['idEmpresa'] = $session->get('idEmpresa');
        $dataApp = $this->ContactosLecturasModel->getLeadsApp($args);
        $dataLeads = $this->ContactosLecturasModel->getLeads($args);
        if ($dataApp['status'] && $dataLeads['status']) {
            $data['status'] = true;
            $data['data'] = array_merge($dataApp['data'], $dataLeads['data']);
        } else if ($dataApp['status']) {
            $data['status'] = true;
            $data['data'] = $dataApp['data'];
        } else if ($dataLeads['status']) {
            $data['status'] = true;
            $data['data'] = $dataLeads['data'];
        } else {
            $data['status'] = false;
        }
        $result = $data['status'] === true ? (array) $data['data'] : null;
        return $this->response($result);
    }

    private function response($result) {
        $response = new JsonResponse($result, 200, array('Content-Type', 'text/json'));
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, *');
        return $response;
    }

    // Excel Report
    public function totalLeadsAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $event = $session->get('evName');
        $exhibitor = $session->get('exName');
        $args['textNoArea'] = $lang == 'es' ? 'Sin Area' : 'Without Area';
        $args['textNoGiro'] = $lang == 'es' ? 'Sin Giro' : 'Without Order';
        $args['textNoSector'] = $lang == 'es' ? 'Sin Sector' : 'Without Sector';
        $args['textNoSubcategoria'] = $lang == 'es' ? 'Sin Subcategoría' : 'Without Subcategory';
//        $args['idEdicion']=28;
//        $args['idEvento']=2;
//        $args['idEmpresa']=2636;
        $args['idEdicion'] = $session->get('idEdicion');
        $args['idEdicionSecond'] = $session->get('idEvento') == 1 ? $session->get('idEdicion') + 1 : $session->get('idEdicion') - 1;
        $args['idEvento'] = $session->get('idEvento');
        $args['idEventoSecond'] = $session->get('idEvento') == 1 ? $session->get('idEvento') + 1 : $session->get('idEvento') - 1;
        $args['idEmpresa'] = $session->get('idEmpresa');

        date_default_timezone_set("America/Mexico_City");
        $file_name = $lang == 'es' ? str_replace(" ", "_", $event) . "_Listado_de_Contactos_" . str_replace(" ", "_", $exhibitor) . "_" . date('d-m-Y G.i') : str_replace(" ", "_", $event) . "_Leads_List_" . str_replace(" ", "_", $exhibitor) . "_" . date('d-m-Y G.i');
        $file_name = preg_replace('/[^a-z0-9]+/i', '_', $file_name);

        $dataApp = $this->ContactosLecturasModel->getAppsReport($args);
        $dataLeads = $this->ContactosLecturasModel->getLeadsReport($args);
        $preguntas=$this->ContactosLecturasModel->getEncuesta($args['idEmpresa']);
        if ($dataApp['status'] && $dataLeads['status']) {
            $data = array_merge($dataApp['data'], $dataLeads['data']);
        } else if ($dataApp['status']) {
            $data = $dataApp['data'];
        } else if ($dataLeads['status']) {
            $data = $dataLeads['data'];
        } else {
            $data = null;
        }

        if ($data != null) {
            $args = array();
            $args['idContacto'] = $session->get('idContacto');
            $args['idEvento'] = $session->get('idEvento');
            $args['idEdicion'] = $session->get('idEdicion');
            $args['idEmpresa'] = $session->get('idEmpresa');
            $this->ContactosLecturasModel->insertGridDownload($args);
        }

        $meta_data = $lang == 'es' ? array(
            "Usuario",
            "Fecha",
            "Hora",
            "Nombre",
            "Apellido Paterno",
            "Apellido Materno",
            "Empresa",
            "Cargo",
            "Email",
            "Tipo de Lectora",
            "Direccion",
            "Colonia",
            "C.P.",
            "Ciudad",
            "Estado",
            "Pais",
            "Tags",
            "Comentarios",
            "Ranking",
            "Telefono"
                ) :
                array(
            "User",
            "Date",
            "Hour",
            "Name",
            "First Surname",
            "Second Surname",
            "Company",
            "Position",
            "Email",
            "Scanner type",
            "Address",
            "Zone",
            "Zip Code",
            "City",
            "State",
            "Country",
            "Tags",
            "Comments",
            "Rankings",
            "Phone"
        );
        // Agregamos las preguntas de la encuesta al encabezados del excel
        foreach(json_decode($preguntas['data'][0]['encuesta'], True) as $valor=>$key ){
            array_push($meta_data,$key['Descripcion']);
        }
        return $this->excelReport($data, $meta_data, $file_name);
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
