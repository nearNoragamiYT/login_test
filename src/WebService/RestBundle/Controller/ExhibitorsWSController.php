<?php

namespace WebService\RestBundle\Controller;

date_default_timezone_set("America/Mexico_City");

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use \Symfony\Component\HttpFoundation\Request;
use WebService\RestBundle\Model\ExhibitorWSModel;
use WebService\RestBundle\Controller\ApiWS;

class ExhibitorsWSController extends FOSRestController {

    protected $wsmodel, $api;
    protected $base_path_cache = '../var/cache/web_service/';
    private $CACHE_EXPIRATION_TIME = 3600;
    private $productsURL = "https://expoantad.infoexpo.com.mx/2020/ed/web/doc/ED/productos/";
    private $idEdicion = 9;

    public function __construct() {
        $this->wsmodel = new ExhibitorWSModel();
        $this->api = new ApiWS();
    }

    public function indexAction(Request $request) {
        print_r("Authentication required");
        die();
    }

    public function boothsAction(Request $request) {
        $view = View::create();
        $ruta = $request->getPathInfo();

        if ($request->getMethod() != 'GET') {
            $view->setData(array('status' => false, 'mensaje' => "Metodo no permitido"));
            return $this->handleView($view);
        }

        $cache_name = "booths";
        $boothsPath = $this->base_path_cache . $cache_name . '.json';
        $lastModification = ( time() - filemtime($boothsPath) ) / $this->CACHE_EXPIRATION_TIME;
        if (file_exists($boothsPath)) {
            if ($lastModification > 1) {
                $result = $this->getBooths();
                $view->setData($result);
                return $this->handleView($view);
            } else {
                $view->setData($this->getArrayBooths(file_get_contents($boothsPath)));
                return $this->handleView($view);
            }
        } else {
            $result = $this->getBooths();
            $view->setData($result);
            return $this->handleView($view);
        }
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

    public function getBooths() {
        $cache_name = "booths";
        $boothsPath = $this->base_path_cache . $cache_name . '.json';
        $args['where'] = array(
            array("name" => '"idEdicion"', "operator" => "=", "value" => $this->idEdicion),
            array("name" => '"Stand_H"', "operator" => ">", "value" => '0', "clause" => "AND"),
            array("name" => '"Stand_W"', "operator" => ">", "value" => '0', "clause" => "AND"),
            array("name" => '"Stand_X"', "operator" => ">", "value" => '0', "clause" => "AND"),
            array("name" => '"Stand_Y"', "operator" => ">", "value" => '0', "clause" => "AND"));

        $result_pg = $this->wsmodel->getBooths($args);
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
            $tempBooth[$booth["idStand"]]["area"] = $booth["StandArea"];
            $tempBooth[$booth["idStand"]]["section"] = $booth["idSala"];
            $tempBooth[$booth["idStand"]]["status"] = $booth["StandStatus"];
        };
        return $tempBooth;
    }

    public function exhibitorsAction(Request $request) {
        $view = View::create();
        $ruta = $request->getPathInfo();

        if ($request->getMethod() != 'GET') {
            $view->setData(array('status' => false, 'mensaje' => "Metodo no permitido"));
            return $this->handleView($view);
        }

        $cache_name = "exhibitors";
        $exhibitorsPath = $this->base_path_cache . $cache_name . '.json';
        $lastModification = ( time() - filemtime($exhibitorsPath) ) / $this->CACHE_EXPIRATION_TIME;
        if (file_exists($exhibitorsPath)) {
            if ($lastModification > 1) {
                $result = $this->getExhibitors();
                $view->setData($result);
                return $this->handleView($view);
            } else {
                $view->setData(json_decode(file_get_contents($exhibitorsPath), true));
                return $this->handleView($view);
            }
        } else {
            $result = $this->getExhibitors();
            $view->setData($result);
            return $this->handleView($view);
        }
    }

    public function getExhibitors() {
        $cache_name = "exhibitors";
        $exhibitorsPath = $this->base_path_cache . $cache_name . '.json';
        $args["idEdicion"] = $this->idEdicion;
        $result_pg = $this->wsmodel->getExhibitors($args);
        if (!$result_pg['status']) {
            return array('status' => false, 'mensaje' => $result_pg['data']);
        }
        $exhibitors = $this->formatExhibitors($result_pg['data']);

        $this->writeJSON($exhibitorsPath, $exhibitors);
        return json_decode(file_get_contents($exhibitorsPath), true);
    }

    public function formatExhibitors($exhibitors) {
        $tempExhibitors = array();
        $boothsPath = '../var/cache/web_service/booths.json';
        $pavilionsPath = '../var/cache/web_service/pavilions.json';
        $this->getBooths();
        $boothsCache = json_decode(file_get_contents($boothsPath), true);
        $adicionales = array();
//        $this->getPavilions();
//        $pavilionsCache = json_decode(file_get_contents($pavilionsPath), true);
        foreach ($exhibitors as $exhibitor) {
            if ($exhibitor["idStatusContrato"] != 4) {
                if ($exhibitor["EmpresaAdicional"] == 1) {
                    if (!array_key_exists($exhibitor["idEmpresa"], $adicionales))
                        continue;
                } else
                    continue;
            }            
            $tempExhibitor["exhibitor_id"] = $exhibitor["idEmpresa"];
            $tempExhibitor["antad_code"] = $exhibitor["CodigoCliente"];
            $tempExhibitor["contract_auth_date"] = $exhibitor["FechaAutorizacion"];
            $tempExhibitor["name_es"] = (!empty($exhibitor["DD_NombreComercial"])) ? $exhibitor["DD_NombreComercial"] : $exhibitor["DC_NombreComercial"];
            $tempExhibitor["name_en"] = (!empty($exhibitor["DD_NombreComercial"])) ? $exhibitor["DD_NombreComercial"] : $exhibitor["DC_NombreComercial"];
            $tempExhibitor["address"] = $this->getAddress($exhibitor);
            $tempExhibitor["info_es"] = (!empty($exhibitor["DD_DescripcionES"])) ? $exhibitor["DD_DescripcionES"] : $exhibitor["DC_DescripcionES"];
            $tempExhibitor["info_en"] = (!empty($exhibitor["DD_DescripcionEN"])) ? $exhibitor["DD_DescripcionEN"] : $exhibitor["DC_DescripcionEN"];
            $tempExhibitor["site"] = (!empty($exhibitor["DD_PaginaWeb"])) ? $exhibitor["DD_PaginaWeb"] : $exhibitor["DC_PaginaWeb"];
            $tempExhibitor["phone"] = $this->getPhone($exhibitor);
            $tempExhibitor["country"] = $exhibitor["DC_Pais"];
            $tempExhibitor["rfc"] = $exhibitor["DF_RFC"];
            $tempExhibitor["razon_social"] = $exhibitor["DF_RazonSocial"];
            $tempBooth = array();
            $tempExhibitor["place"] = array();
//            $tempExhibitor["pavilions"] = array();

            foreach (json_decode($exhibitor["EmpresaStand"], true) as $booth) {
                if (array_key_exists($booth["idStand"], $boothsCache["data"])) {
                    $tempBooth["booth_id"] = $booth["idStand"];
                    $tempBooth["booth_number"] = $booth["StandNumber"];
                    $tempBooth["booth_label"] = $booth["EtiquetaStand"];
                    $tempBooth["width"] = $booth["Stand_W"];
                    $tempBooth["height"] = $booth["Stand_H"];
                    $tempBooth["area"] = (float) $booth["Stand_H"] * (float) $booth["Stand_W"];
                    $tempBooth["pavilion"] = $booth["NombreES"];
                    array_push($tempExhibitor["place"], $tempBooth);
//                    if (isset($booth["idPabellon"]) && !empty($booth["idPabellon"])) {
//                        $tempPavilion["pavilion_id"] = $booth["idPabellon"];
//                        array_push($tempExhibitor["pavilions"], $tempPavilion);
//                    }
                }
            }

            if (count($tempExhibitor["place"]) == 0) {
                continue;
            }
            foreach (json_decode($exhibitor["EmpresasAdicionalesJson"], true) as $adicional) {
                $adicionales[$adicional['idEmpresa']] = NULL;            
            }

            $tempCategory = array();
            $tempExhibitor["categories"] = array();
            foreach (json_decode($exhibitor["EmpresaCategoria"], true) as $category) {
                $tempCategory["category_id"] = $category["idCategoria"];
                $tempCategory["category"] = $category["NombreCategoriaES"];
                $tempCategory["parents"] = $category["Padres"];
                $tempCategory["text_other"] = $category["DC_TextoCategoria"];
                array_push($tempExhibitor["categories"], $tempCategory);
            }
            $tempProduct = array();
            $tempExhibitor["products"] = array();
            foreach (json_decode($exhibitor["EmpresaProducto"], true) as $product) {
                $tempProduct["title"] = $product["Titulo"];
                $tempProduct["description"] = $product["Descripcion"];
                $tempProduct["image"] = '';
                if ($product['idForma'] == 222)
                    $tempProduct["image"] = $this->productsURL . $exhibitor["idEmpresa"] . "/" . $product["Imagen"];
                array_push($tempExhibitor["products"], $tempProduct);
            }
            if (!empty($exhibitor["Contacto"])) {
                $contact = json_decode($exhibitor["Contacto"], true);
                $tempExhibitor["contact_name"] = $contact[0]["Nombre"] . " " . $contact[0]["ApellidoPaterno"] . " " . $contact[0]["ApellidoMaterno"];
                $tempExhibitor["contact_email"] = (!empty($contact[0]["Email"]) ) ? $contact[0]["Email"] : "";
                $tempExhibitor["contact_jobtitle"] = (!empty($contact[0]["Puesto"]) ) ? $contact[0]["Puesto"] : "";
                $tempExhibitor["contact_phone"] = (!empty($contact[0]["Telefono"]) ) ? $contact[0]["Telefono"] : "";
            } else {
                $contact = json_decode($exhibitor["ContactoEvento"], true);
                $tempExhibitor["contact_name"] = $contact[0]["Nombre"] . " " . $contact[0]["ApellidoPaterno"] . " " . $contact[0]["ApellidoMaterno"];
                $tempExhibitor["contact_email"] = (!empty($contact[0]["Email"]) ) ? $contact[0]["Email"] : "";
                $tempExhibitor["contact_jobtitle"] = (!empty($contact[0]["Puesto"]) ) ? $contact[0]["Puesto"] : "";
                $tempExhibitor["contact_phone"] = (!empty($contact[0]["Telefono"]) ) ? $contact[0]["Telefono"] : "";
            }
            $tempExhibitor["image"] = $exhibitor["DD_Logo"];
            $tempExhibitor["co_exhibitor"] = $exhibitor["Coexpositor"] == 1 ? true : false;
            $tempExhibitor["additional_exhibitor"] = $exhibitor["EmpresaAdicional"] == 1 ? true : false;
            $tempExhibitor["parent_idexhibitor"] = $exhibitor["idEmpresaPadre"];
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
        $addressDC .= (!empty($exhibitor["DC_Pais"])) ? $exhibitor["DC_Pais"] : '';

        $addressDD = (!empty($exhibitor["DD_CalleNum"])) ? $exhibitor["DD_CalleNum"] . ', ' : '';
        $addressDD .= (!empty($exhibitor["DD_Colonia"])) ? $exhibitor["DD_Colonia"] . ', ' : '';
        $addressDD .= (!empty($exhibitor["DD_Ciudad"])) ? $exhibitor["DD_Ciudad"] . ', ' : '';
        $addressDD .= (!empty($exhibitor["DD_CodigoPostal"])) ? $exhibitor["DD_CodigoPostal"] . ', ' : '';
        $addressDD .= (!empty($exhibitor["DD_Estado"])) ? $exhibitor["DD_Estado"] . ', ' : '';
        $addressDD .= (!empty($exhibitor["DD_Pais"])) ? $exhibitor["DD_Pais"] : '';

        $address = (!empty($addressDD) ) ? $addressDD : $addressDC;
        return $address;
    }

    public function getPhone($exhibitor) {
        $phoneDC = (!empty($exhibitor["DC_TelefonoAreaPais"])) ? '+' . str_replace('+', '', $exhibitor["DC_TelefonoAreaPais"]) . ' ' : '';
        $phoneDC .= (!empty($exhibitor["DC_TelefonoAreaCiudad"])) ? '(' . $exhibitor["DC_TelefonoAreaCiudad"] . ') ' : '';
        $phoneDC .= (!empty($exhibitor["DC_Telefono"])) ? $exhibitor["DC_Telefono"] : '';
        $phoneDC .= (!empty($exhibitor["DC_TelefonoExtension"])) ? ' Ext. ' . $exhibitor["DC_TelefonoExtension"] : '';

        $phoneDD = (!empty($exhibitor["DD_TelefonoAreaPais"])) ? '+' . str_replace('+', '', $exhibitor["DD_TelefonoAreaPais"]) . ' ' : '';
        $phoneDD .= (!empty($exhibitor["DD_TelefonoAreaCiudad"])) ? '(' . $exhibitor["DD_TelefonoAreaCiudad"] . ') ' : '';
        $phoneDD .= (!empty($exhibitor["DD_Telefono"])) ? $exhibitor["DD_Telefono"] : '';
        $phoneDD .= (!empty($exhibitor["DD_TelefonoExtension"])) ? ' Ext. ' . $exhibitor["DD_TelefonoExtension"] : '';

        $phone = (!empty($phoneDD) ) ? $phoneDD : $phoneDC;
        return $phone;
    }

    public function CategoriesAction(Request $request) {
        $view = View::create();
        $ruta = $request->getPathInfo();

        if ($request->getMethod() != 'GET') {
            $view->setData(array('status' => false, 'mensaje' => "Metodo no permitido"));
            return $this->handleView($view);
        }

        $cache_name = "categories";
        $categoriesPath = $this->base_path_cache . $cache_name . '.json';
        $lastModification = ( time() - filemtime($categoriesPath) ) / $this->CACHE_EXPIRATION_TIME;
        if (file_exists($categoriesPath)) {
            if ($lastModification > 1) {
                $result = $this->getCategories();
                $view->setData($result);
                return $this->handleView($view);
            } else {
                $view->setData(json_decode(file_get_contents($categoriesPath), true));
                return $this->handleView($view);
            }
        } else {
            $result = $this->getCategories();
            $view->setData($result);
            return $this->handleView($view);
        }
    }

    public function getCategories() {
        $cache_name = "categories";
        $categoriesPath = $this->base_path_cache . $cache_name . '.json';
        $args['where'] = array(
            array("name" => '"idEdicion"', "operator" => "=", "value" => $this->idEdicion),
            array("name" => '"idCategoria"', "operator" => ">", "value" => '0', "clause" => "AND"));
        $result_pg = $this->wsmodel->getCategories($args);
        if (!$result_pg['status']) {
            return array('status' => false, 'mensaje' => $result_pg['data']);
        }
        $categories = $this->formatCategories($result_pg['data']);
        $this->writeJSON($categoriesPath, $categories);
        return json_decode(file_get_contents($categoriesPath), true);
    }

    public function formatCategories($categories) {
        $tempCategories = array();
        foreach ($categories as $categorie) {
            $tempCategorie["category_id"] = $categorie["idCategoria"];
            $tempCategorie["name_en"] = !empty($categorie["NombreCategoriaEN"]) ? $categorie["NombreCategoriaEN"] : "";
            $tempCategorie["name_es"] = !empty($categorie["NombreCategoriaES"]) ? $categorie["NombreCategoriaES"] : "";
            //$tempCategorie["name_pt"] = !empty($categorie["NombreCategoriaPT"]) ? $categorie["NombreCategoriaPT"] : "";
            $tempCategorie["level"] = $categorie["Nivel"];
            $tempCategorie["parent_id"] = $categorie["idPadre"];
            array_push($tempCategories, $tempCategorie);
        }
        return $tempCategories;
    }

    public function pavilionsAction(Request $request) {
        $view = View::create();
        $ruta = $request->getPathInfo();

        if ($request->getMethod() != 'GET') {
            $view->setData(array('status' => false, 'mensaje' => "Metodo no permitido"));
            return $this->handleView($view);
        }

        $cache_name = "pavilions";
        $pavilionsPath = $this->base_path_cache . $cache_name . '.json';
        $lastModification = ( time() - filemtime($pavilionsPath) ) / $this->CACHE_EXPIRATION_TIME;
        if (file_exists($pavilionsPath)) {
            if ($lastModification > 1) {
                $result = $this->getPavilions();
                $view->setData($result);
                return $this->handleView($view);
            } else {
                $view->setData(json_decode(file_get_contents($pavilionsPath), true));
                return $this->handleView($view);
            }
        } else {
            $result = $this->getPavilions();
            $view->setData($result);
            return $this->handleView($view);
        }
    }

    public function getPavilions() {
        $cache_name = "pavilions";
        $pavilionsPath = $this->base_path_cache . $cache_name . '.json';
        $args['where'] = array(
            array("name" => '"idEdicion"', "operator" => "=", "value" => $this->idEdicion));
        $result_pg = $this->wsmodel->getPavilions($args);
        if (!$result_pg['status']) {
            return array('status' => false, 'mensaje' => $result_pg['data']);
        }
        $pavilions = $this->formatPavilions($result_pg['data']);
        $this->writeJSON($pavilionsPath, $pavilions);
        return json_decode(file_get_contents($pavilionsPath), true);
    }

    public function formatPavilions($pavilions) {
        $tempPavilions = array();
        foreach ($pavilions as $pavilion) {
            $tempPavilions[$pavilion["idPabellon"]]["pavilion_id"] = $pavilion["idPabellon"];
            $tempPavilions[$pavilion["idPabellon"]]["name_es"] = $pavilion["NombreES"];
            $tempPavilions[$pavilion["idPabellon"]]["name_en"] = $pavilion["NombreEN"];
        };
        return $tempPavilions;
    }

    private function writeJSON($fileName, $array) {
        $json = json_encode(array('status' => TRUE, 'data' => $array));
        $fp = fopen($fileName, "w");
        fwrite($fp, $json);
        fclose($fp);
    }

}
