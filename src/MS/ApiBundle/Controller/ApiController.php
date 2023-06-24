<?php

namespace MS\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use MS\ApiBundle\Model\ApiModel;

class ApiController extends Controller {

    function __construct() {
        $this->ApiModel = new ApiModel();
    }

    public function visitAction(Request $request) {
        $response = array("status" => false, "data" => array());
        $post = $request->request->all();
        $msApiVisitData = isset($post["msApiVisitData"]) ? $post["msApiVisitData"] : array();
        $data = array();
        $data["idVisitante"] = $msApiVisitData["msUserId"];
        $data["idEdicion"] = $msApiVisitData["edition"];
        $data["idEvento"] = $msApiVisitData["event"];
        $data["UrlOrigen"] = "'" . $msApiVisitData["httpReferer"] . "'";
        $data["Ip"] = "'" . $request->getClientIp() . "'";
        $data["Navegador"] = "'" . $this->getBrowser() . "'";
        $data["UserAgent"] = "'" . $this->getUserAgent() . "'";
        $data["Key"] = "'" . $msApiVisitData["key"] . "'";
        $data["UrlOrigen"] = "'" . $msApiVisitData["originUrl"] . "'";
        $data["HttpReferer"] = "'" . $msApiVisitData["httpReferer"] . "'";
        $visitResult = $this->ApiModel->insertVisit($data);
        if (!$visitResult['status']) {
            throw new \Exception($visitResult['data'], 409);
        }
        $response["status"] = $visitResult["status"];
        $response["data"] = $visitResult["data"];
        return $this->getResponse($response);
    }

    public function leavingAction(Request $request) {
        $response = array("status" => false, "data" => array());
        $post = $request->request->all();
        $msApiVisitData = isset($post["msApiActionData"]) ? $post["msApiActionData"] : array();
        $actionResult = $this->insertActions($msApiVisitData);
        $response["status"] = $actionResult["status"];
        $response["data"] = $actionResult["data"];
        return $this->getResponse($response);
    }

    public function searchAction(Request $request) {
        $response = array("status" => false, "data" => array());
        $post = $request->request->all();
        $msApiSearchData = isset($post["msApiSearchData"]) ? $post["msApiSearchData"] : array();
        $data = array();
        $data["idVisitante"] = $msApiSearchData["msUserId"];
        $data["Query"] = "'" . $msApiSearchData["query"] . "'";
        $data["Ip"] = "'" . $this->getClientIp() . "'";
        $data["Navegador"] = "'" . $this->getBrowser() . "'";
        $data["UserAgent"] = "'" . $this->getUserAgent() . "'";
        $data["Key"] = "'" . $msApiSearchData["key"] . "'";
        $data["Type"] = $msApiSearchData["type"];
        $data["idRef"] = $msApiSearchData["idRef"];
        $data["idEdicion"] = $msApiSearchData["edition"];
        $data["idEvento"] = $msApiSearchData["event"];
        $searchResult = $this->ApiModel->insertSearch($data);
        if (!$searchResult['status']) {
            throw new \Exception($searchResult['data'], 409);
        }
        $response["status"] = $searchResult["status"];
        $response["data"] = $searchResult["data"];
        return $this->getResponse($response);
    }

    public function insertActions($array) {
        foreach ($array as $key => $value) {
            if ($key === 'values') {
                $data = array();
                $data["idExpositor"] = $value["e"];
                $data["idVisitante"] = $value["r"];
                $data["idObjeto"] = $value["x"];
                $data["Valor"] = "'" . $value["v"] . "'";
                $data["Ip"] = "'" . $this->getClientIp() . "'";
                $data["Navegador"] = "'" . $this->getBrowser() . "'";
                $data["UserAgent"] = "'" . $this->getUserAgent() . "'";
                $data["Key"] = "'" . $value["key"] . "'";
                $data["Cantidad"] = $value["amount"];
                $data["idEdicion"] = $value["edition"];
                $data["idEvento"] = $value["event"];
                $actionResult = $this->ApiModel->insertAction($data);
                if (!$actionResult['status']) {
                    throw new \Exception($actionResult['data'], 409);
                }
                return true;
            } if (is_array($value))
                $this->insertActions($value);
        }
    }

    public function getResponse($data) {
        $response = new Response(json_encode($data), 200, array('Content-Type', 'text/json'));
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, *');
        return $response;
    }

    private function getBrowser() {
        //http://user-agents.me/ busqueda de agentes
        $userAgent = $this->getUserAgent();
        if (preg_match("/iPad/", $userAgent))
            return "iPad";
        else if (preg_match("/iPhone/", $userAgent) || preg_match("/iPod/", $userAgent))
            return "iPhone";
        else if (preg_match("/Android/", $userAgent))
            return "Android";
        else if (preg_match("/QuickLook/", $userAgent))
            return "Apple Mail Preview";
        else if (preg_match("/Thunderbird/", $userAgent))
            return "Thunderbird Mail Preview";
        else if (preg_match("/Nintendo/", $userAgent))
            return "Nintendo Browser";
        else if (preg_match("/PlayStation/", $userAgent) || preg_match("/PLAYSTATION/", $userAgent))
            return "PlayStation Vita Browser";
        else if (preg_match("/Firefox/", $userAgent))
            return "Firefox";
        else if (preg_match("/Opera/", $userAgent) || preg_match("/OPR/", $userAgent))//antes de chrome y safari (la cadena incluye los nombres de esos 2 navegadores) Chrome/43.0.2357.81 Safari/537.36 OPR/30.0.1835.52
            return "Opera";
        else if (preg_match("/Chrome/", $userAgent))
            return "Chrome";
        else if (preg_match("/Explorer/", $userAgent) || preg_match("/Trident/", $userAgent) || preg_match("/MSIE/", $userAgent))
            return "Explorer";
        else if (preg_match("/Safari/", $userAgent))
            return "Safari";
        return "unknown";
    }

    private function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    function getClientIp() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

}
