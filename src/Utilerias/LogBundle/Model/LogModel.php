<?php

namespace Utilerias\LogBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use Utilerias\SQLBundle\Model\SQLModel;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class LogModel extends Controller {

    protected $container, $SQLModel, $DashboardModel;

    public function __construct(ContainerInterface $container = NULL) {
        $this->container = $container;
        $this->SQLModel = new SQLModel();
        $this->DashboardModel = new DashboardModel();
    }

    public function insertActionLog($qry = "", $json = Array()) {
        print_r("xxxxx");
        die("xD");
        $request = $this->get('request_stack')->getCurrentRequest();
        $session = $request->getSession();
        $modulo = $this->DashboardModel->rastrearBreadcrumbs($request);

        $idSesion = $session->get('idSesion');
        $idAction = $this->setCurrentAction($qry);
        $idPlataformaIxpo = $session->get('idPlataformaIxpo');

        $idModuloIxpo = (isset($modulo[0]["idModuloIxpo"])) ? $modulo[0]["idModuloIxpo"] : "";
        $url = $_SERVER["REQUEST_URI"];
        foreach ($json as $key => $value) {
            $json[$key] = str_replace("'", "", $value);
        }
        $json = json_encode($json);
        $fields = Array(
            'idControlSesion' => $idSesion,
            'idAccion' => $idAction,
            'idPlataformaIxpo' => $idPlataformaIxpo,
            'idModuloIxpo' => $idModuloIxpo,
            'Url' => "'" . $url . "'",
            'Consulta' => "$" . "chv$" . $qry . "$" . "chv$",
            'ContenidoJSON' => "'" . $json . "'"
        );
        $res_pg = $this->SQLModel->insertIntoTable("LogAccion", $fields, "idLogAccion");

        if (!($res_pg['status'] && count($res_pg['data']) > 0)) {
            return $res_pg;
        }
        return $res_pg;
    }

    private function getActions() {
        $fields = array("idAccion", "Accion");
        $result = $this->SQLModel->selectFromTable("Accion", $fields);
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            foreach ($result['data'] as $value) {
                $data[$value["Accion"]] = $value['idAccion'];
            }
            return $data;
        } else {
            return NULL;
        }
        return $this->SQLModel->selectFromTable("Accion", $fields);
    }

    private function setCurrentAction($qry = "") {
        $actions = $this->getActions();
        $qry = explode(" ", $qry);
        $sintaxis_query = $qry[0];
        $action = "undefined";


//        if (stristr($qry, 'select') !== false) {
//            if (stristr($qry, 'fn_') !== false) {
//                $action = "function";
//            } else {
//                $action = "select";
//            }
//        }
//        if (stristr($qry, 'insert') !== false) {
//            $action = "insert";
//        }
//        if (stristr($qry, 'update') !== false) {
//            $action = "update";
//        }
//        print_r($action);
//        die("xD");
//        if (stristr($qry, 'delete') !== false) {
//            $action = "delete";
//        }
        switch (true) {
            case (stristr($sintaxis_query, 'select') !== false):
                if (stristr($qry['3'], 'fn_') !== false) {
                    $action = "function";
                } else {
                    $action = "select";
                }
                break;
            case (stristr($sintaxis_query, 'insert') !== false):
                $action = "insert";
                break;
            case (stristr($sintaxis_query, 'update') !== false):
                $action = "update";
                break;
            case(stristr($sintaxis_query, 'delete') !== false):
                $action = "delete";
        }
        $idAction = (isset($actions[$action])) ? $actions[$action] : 5;

        return $idAction;
    }

    public function insertLoginLog($data) {
        print_r();
        die("xD");
        $request = $this->get('request_stack')->getCurrentRequest();
        $session = $request->getSession();
        $session->set('idSesion', 0);
        $profile = $this->getUser();
        $user = $profile->getData();
        $values = Array(
            'SesionPHP' => "'" . session_id() . "'",
            'idUsuario' => $user['idUsuario'],
            'Equipo' => "'" . $data['Equipo'] . "'",
            'IP' => "'" . $data['ip'] . "'",
            'SistemaOperativo' => "'" . $data['Sistema'] . "'",
            'Navegador' => "'" . $data['Navegador'] . "'",
            'Resolucion' => "'" . $data['Resolucion'] . "'",
            'StatusSesion' => "'" . $data['Status'] . "'",
        );
        $result = $this->SQLModel->insertIntoTable("ControlSesion", $values, 'idControlSesion');
        if (($result['status'] && count($result['data']) > 0)) {
            $session->set('idSesion', $result['data'][0]['idControlSesion']);
        }
    }

    public function updateLoginLog($data) {
        print_r();
        die("xD");
        $request = $this->get('request_stack')->getCurrentRequest();
        $session = $request->getSession();
        $where = Array('idControlSesion' => $session->get('idSesion'));
        $values = Array(
            'StatusSesion' => "'" . $data['Status'] . "'",
            'FechaHoraTermino' => "'" . $data['FechaHoraTermino'] . "'"
        );
        $result = $this->SQLModel->updateFromTable("ControlSesion", $values, $where);
    }

    public function InsertLogSeguimiento($qry = "", $json = Array()) {
        $request = $this->get('request_stack')->getCurrentRequest();
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idAction = $this->setCurrentAction($qry);
        $idPlataformaIxpo = $session->get('idPlataformaIxpo');
        if (isset($json['ContenidoNuevo_'])) {
            $json_ = $json['ContenidoNuevo_'];
        } else {
            $json_ = json_encode($json, JSON_FORCE_OBJECT);
        }
        $ModuloIxpo = (isset($json["Modulo"])) ? $json["Modulo"] : "";
        $values = array(
            'idEvento' => $json['idEvento'],
            'idEdicion' => $json['idEdicion'],
            'AccionTipo' => $idAction,
            'Modulo' => "'" . $ModuloIxpo . "'",
            'ContenidoNuevo' => "'" . $json_ . "'",
            'idPlataforma' => $idPlataformaIxpo,
            'idUsuario' => $user['idUsuario'],
            'Consulta' => "$" . "chv$" . $qry . "$" . "chv$"
        );
        $res_pg = $this->SQLModel->insertIntoTable("LogSeguimiento", $values, "idLogSeguimiento");
        if (!($res_pg['status'] && count($res_pg['data']) > 0)) {
            return $res_pg;
        }
        return $res_pg;
    }

}
