<?php

namespace ShowDashboard\FP\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use ShowDashboard\FP\AppBundle\Model\AppModel;

class AppController extends Controller {

    protected $AppModel;
    private $CACHE_EXPIRATION_TIME = 3600;
    private $base_path_cache = '../var/cache/fp/';

    public function __construct() {
        $this->AppModel = new AppModel();
    }

    public function CategoriasAction(Request $request) {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '-1');
        date_default_timezone_set("America/Mexico_City");

        $meta_data = array(
            "IdCategoria",
            "IdPadre",
            "NombreCategoriaES",
            "NombreCategoriaEN",
        );
        $session = $request->getSession();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_categoriasApp " . date('d-m-Y G.i');

        $param = Array('idEdicion' => $idEdicion, 'idEvento' => $idEvento);
        $result_query = $this->AppModel->getCategoriasCSV($param);

        $data = $result_query['data'];

        return $this->excelReportCSV($data, $meta_data, $file_name, '', '');
    }

    public function exhibitorsAction(Request $request) {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '-1');
        date_default_timezone_set("America/Mexico_City");

        $session = $request->getSession();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $meta_data = array('IdentificadorUnico', 'Nombre', 'Stands', 'DescripcionES', 'DescripcionEN', 'Logo', 'Direccion', 'Telefono', 'Email', 'PaginaWeb', 'Categorias');
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_ES"]) . "_ExhibitorsApp " . date('d-m-Y G.i');

        $cache_name = "exhibitors";
        $exhibitorsPath = $this->base_path_cache . $cache_name . '_' . $idEdicion . '.json';
        $lastModification = ( time() - filemtime($exhibitorsPath) ) / $this->CACHE_EXPIRATION_TIME;
        if (file_exists($exhibitorsPath)) {
            if ($lastModification > 1) {
                $result = $this->getExhibitors($idEdicion);
                return $this->excelReportCSV($result, $meta_data, $file_name, '', '');
            } else {
                $result = json_decode(file_get_contents($exhibitorsPath), true);
                return $this->excelReportCSV($result, $meta_data, $file_name, '', '');
            }
        } else {
            $result = $this->getExhibitors($idEdicion);
            return $this->excelReportCSV($result, $meta_data, $file_name, '', '');
        }
    }

    public function getExhibitors($idEdicion) {
        $cache_name = "exhibitors_";
        $exhibitorsPath = $this->base_path_cache . $cache_name . $idEdicion . '.json';
        $result_pg = $this->AppModel->getQueryWS($idEdicion);
        if (!$result_pg['status']) {
            return array('status' => false, 'mensaje' => $result_pg['data']);
        }
        $exhibitors = $this->formatExhibitors($result_pg['data'], $idEdicion);
        $this->writeJSON($exhibitorsPath, $exhibitors);
        return json_decode(file_get_contents($exhibitorsPath), true);
    }

    public function formatExhibitors($exhibitors, $idEdicion) {
        $tempExhibitors = array();
        $this->getBooths($idEdicion);
        $boothsPath = '../var/cache/fp/booths.json';
        $boothsCache = json_decode(file_get_contents($boothsPath), true);
        foreach ($exhibitors as $exhibitor) {
            $tempExhibitor = array();
            $tempExhibitor["IdentificadorUnico"] = 'EXP' . $exhibitor["idEmpresa"];
            $tempExhibitor["Nombre"] = (!empty($exhibitor["DC_NombreComercial"])) ? $exhibitor["DC_NombreComercial"] : $exhibitor["DD_NombreComercial"];

            $tempExhibitor["Stands"] = '';
            foreach (json_decode($exhibitor["EmpresaStand"], true) as $booth) {
                if (array_key_exists($booth["idStand"], $boothsCache)) {
                    $tempExhibitor["Stands"] .= $booth["StandNumber"] . ',';
                }
            }
            $tempExhibitor["Stands"] = substr($tempExhibitor["Stands"], 0, -1);
            if (strlen($tempExhibitor["Stands"]) <= 0) {
                continue;
            }

            $tempExhibitor["DescripcionES"] = (!empty($exhibitor["DC_DescripcionES"])) ? $exhibitor["DC_DescripcionES"] : $exhibitor["DD_DescripcionES"];
            $tempExhibitor["DescripcionEN"] = (!empty($exhibitor["DC_DescripcionEN"])) ? $exhibitor["DC_DescripcionEN"] : $exhibitor["DD_DescripcionEN"];
            $tempExhibitor["Logo"] = $exhibitor["DD_Logo"];
            $tempExhibitor["Direccion"] = $this->getAddress($exhibitor);
            $tempExhibitor["Telefono"] = $this->getPhone($exhibitor);
            $tempExhibitor["Email"] = (!empty($exhibitor["DC_Email"])) ? $exhibitor["DC_Email"] : $exhibitor["DD_Email"];
            $tempExhibitor["PaginaWeb"] = (!empty($exhibitor["DC_PaginaWeb"])) ? $exhibitor["DC_PaginaWeb"] : $exhibitor["DD_PaginaWeb"];
            $tempExhibitor["PaginaWeb"] = str_ireplace(array('https://', 'http://'), '', $tempExhibitor["PaginaWeb"]);

            $tempExhibitor["categorias"] = '';
            foreach (json_decode($exhibitor["EmpresaCategoria"], true) as $category) {
                $tempExhibitor["categorias"] .= $category["idCategoria"] . ',';
            }
            $tempExhibitor["categorias"] = substr($tempExhibitor["categorias"], 0, -1);

            array_push($tempExhibitors, $tempExhibitor);
        }
        return $tempExhibitors;
    }

    public function getAddress($exhibitor) {
        $addressDC = (!empty($exhibitor["DC_CalleNum"])) ? $exhibitor["DC_CalleNum"] . ', ' : '';
        $addressDC .= (!empty($exhibitor["DC_Colonia"])) ? $exhibitor["DC_Colonia"] . ', ' : '';
        $addressDC .= (!empty($exhibitor["DC_Ciudad"])) ? $exhibitor["DC_Ciudad"] . ', ' : '';
        $addressDC .= (!empty($exhibitor["DC_CodigoPostal"])) ? $exhibitor["DC_CodigoPostal"] . ', ' : '';
        $addressDC .= (!empty($exhibitor["DC_Estado"])) ? $exhibitor["DC_Estado"] . ', ' : '';
        $addressDC .= (!empty($exhibitor["DC_Pais"])) ? $exhibitor["DC_Pais"] . ', ' : '';


        $addressDD = (!empty($exhibitor["DD_CalleNum"])) ? $exhibitor["DD_CalleNum"] . ', ' : '';
        $addressDD .= (!empty($exhibitor["DD_Colonia"])) ? $exhibitor["DD_Colonia"] . ', ' : '';
        $addressDD .= (!empty($exhibitor["DD_Ciudad"])) ? $exhibitor["DD_Ciudad"] . ', ' : '';
        $addressDD .= (!empty($exhibitor["DD_CodigoPostal"])) ? $exhibitor["DD_CodigoPostal"] . ', ' : '';
        $addressDD .= (!empty($exhibitor["DD_Estado"])) ? $exhibitor["DD_Estado"] . ', ' : '';
        $addressDD .= (!empty($exhibitor["DD_Pais"])) ? $exhibitor["DD_Pais"] . ', ' : '';

        $address = (!empty($addressDC) ) ? $addressDC : $addressDD;
        return $address;
    }

    public function getPhone($exhibitor) {
        $phoneDC = (!empty($exhibitor["DC_TelefonoAreaPais"])) ? '+' . $exhibitor["DC_TelefonoAreaPais"] . ' ' : '';
        $phoneDC .= (!empty($exhibitor["DC_TelefonoAreaCiudad"])) ? '(' . $exhibitor["DC_TelefonoAreaCiudad"] . ') ' : '';
        $phoneDC .= (!empty($exhibitor["DC_Telefono"])) ? $exhibitor["DC_Telefono"] : '';
        $phoneDC .= (!empty($exhibitor["DC_TelefonoExtension"])) ? ' Ext. ' . $exhibitor["DC_TelefonoExtension"] : '';

        $phoneDD = (!empty($exhibitor["DD_TelefonoAreaPais"])) ? '+' . $exhibitor["DD_TelefonoAreaPais"] . ' ' : '';
        $phoneDD .= (!empty($exhibitor["DD_TelefonoAreaCiudad"])) ? '(' . $exhibitor["DD_TelefonoAreaCiudad"] . ') ' : '';
        $phoneDD .= (!empty($exhibitor["DD_Telefono"])) ? $exhibitor["DD_Telefono"] : '';
        $phoneDD .= (!empty($exhibitor["DD_TelefonoExtension"])) ? ' Ext. ' . $exhibitor["DD_TelefonoExtension"] : '';

        $phone = (!empty($phoneDC) ) ? $phoneDC : $phoneDD;
        return $phone;
    }

    public function getBooths($idEdicion) {
        $cache_name = "booths";
        $boothsPath = $this->base_path_cache . $cache_name . '.json';
        $result_pg = $this->AppModel->getBooths($idEdicion);
        if (!$result_pg['status']) {
            return array('status' => false, 'mensaje' => $result_pg['data']);
        }
        $booths = $this->formatBooths($result_pg['data']);
        $this->writeJSON($boothsPath, $booths);
        return $this->getArrayBooths(file_get_contents($boothsPath));
    }

    public function formatBooths($booths) {
        $tempBooth = array();
        foreach ($booths as $booth) {
            $tempBooth[$booth["idStand"]]["booth_id"] = $booth["idStand"];
            $tempBooth[$booth["idStand"]]["number"] = $booth["StandNumber"];
            $tempBooth[$booth["idStand"]]["info"] = $booth["EtiquetaStand"];
            $tempBooth[$booth["idStand"]]["x"] = $booth["Stand_X"];
            $tempBooth[$booth["idStand"]]["y"] = $booth["Stand_Y"];
            $tempBooth[$booth["idStand"]]["width"] = $booth["Stand_W"];
            $tempBooth[$booth["idStand"]]["height"] = $booth["Stand_H"];
            $tempBooth[$booth["idStand"]]["section"] = $booth["idSala"];
            $tempBooth[$booth["idStand"]]["status"] = $booth["StandStatus"];
        };
        return $tempBooth;
    }

    public function getArrayBooths($param) {
        $paramData = json_decode($param, true);
        $tempBooth = array_values($paramData['data']);
        $array = array(
            'status' => $paramData['status'],
            'data' => $tempBooth
        );
        return $array;
    }

    public function excelReportCSV($general, $table_metadata, $filename, $header, $subheader) {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("Infoexpo")
                ->setTitle($filename)
                ->setSubject($filename)
                ->setDescription($filename);
        $flag = 1;
        $lastColumn = "A";
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

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'CSV');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename . ".csv");

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        $response->headers->set('Expires', '0');

        return $response;
    }

    private function writeJSON($fileName, $array) {
        $json = json_encode($array);
        $fp = fopen($fileName, "w");
        fwrite($fp, $json);
        fclose($fp);
    }

}
