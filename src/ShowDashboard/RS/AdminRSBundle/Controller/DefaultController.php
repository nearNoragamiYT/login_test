<?php

namespace ShowDashboard\RS\AdminRSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\RS\AdminRSBundle\Model\AdminRSModel;

class DefaultController extends Controller {

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->AdminRSModel = new AdminRSModel();
    }

    const SECTION = 11;

    public function adminAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $content = array();
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();

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

        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content["breadcrumb"] = $breadcrumb;

        $content['lang'] = $lang;

        $result_config = $this->AdminRSModel->getConfigRs($lang);

        if (!$result_config['status']) {
            throw new \Exception($result_config['data'], 409);
        }
        /* obtiene estado del sistema */
        $content['ConfigRS'] = $result_config[data];
        $result_configuracion = $this->AdminRSModel->getEstadoSistema();
        $content['configuraciones'] = $result_configuracion['data'];


        /* lista tipo capturas */
        $result_nodo = $this->AdminRSModel->getNodo($lang);
        if (!$result_nodo['status']) {
            throw new \Exception($result_nodo['data'], 409);
        }
        $content['Nodo'] = $result_nodo[data];

        /* lista de eventos */
        $result_eventos = $this->AdminRSModel->getEvento($idEvento);
        if (!$result_eventos['status']) {
            throw new \Exception($result_eventos['data'], 409);
        }
        $content['Evento'] = $result_eventos[data];

        /* INDICES PARA EL SELECT DEL DATATABLE */
        $campos = array(
            "idEdicion" => "Nombre de la edición",
            "idEvento" => "Evento",
            "idCaptura" => "Contingencía",
            "ClubElite" => "Club Elite",
            "Tienda" => "Tienda",
            "AutoRegistro" => "AutoRegistro",
            "GafeteMultiple" => "GafeteMultiples",
            "Preregistro" => "Preregistro",
        );
        $content['campos'] = $campos;

//        $content['EventoActual'] = $idEvento;

        /* lista ediciones */
        $result_ediciones = $this->AdminRSModel->getEdicion($lang);

        if (!$result_ediciones['status']) {
            throw new \Exception($result_ediciones['data'], 409);
        }
        $content['Edicion'] = $result_ediciones[data];
        return $this->render('ShowDashboardRSAdminRSBundle:Default:AdminRs.html.twig', array('content' => $content));
    }

    public function estadoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');

        /* Obtención de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            if ($post['idNodo'] == 1000) {
                $data = array(
                    'NombreNodo' => "'" . $post['NombreNodo'] . "'"
                );
                $NodeResult = $this->AdminRSModel->insertNodo($data);

                $data = array('idEdicion' => $post['idEdicion'],
                    'idEvento' => $post['idEvento'],
                    'idNodo' => $NodeResult['data'][0]['idNodo'],
                    'idCaptura' => $post['idCaptura'],
                    'ip' => "'" . $post['ip'] . "'",
                    'ClubElite' => $post['ClubElite'],
                    'Tienda' => $post['Tienda'],
                    'AutoRegistro' => $post['AutoRegistro'],
                    'GafeteMultiple' => $post['GafeteMultiple'],
                    'Preregistro' => $post['Preregistro'],
                );
            } else {
                $data = array('idEdicion' => $post['idEdicion'],
                    'idEvento' => $post['idEvento'],
                    'idNodo' => $post['idNodo'],
                    'idCaptura' => $post['idCaptura'],
                    'ip' => "'" . $post['ip'] . "'",
                    'ClubElite' => $post['ClubElite'],
                    'Tienda' => $post['Tienda'],
                    'AutoRegistro' => $post['AutoRegistro'],
                    'GafeteMultiple' => $post['GafeteMultiple'],
                    'Preregistro' => $post['Preregistro'],
                );
            }

            $result = $this->AdminRSModel->insertEstado($data);

            if ($result['status']) {
                $id = $result['data'][0]['idConfiguracion'];
                $result['status'] = TRUE;
                $data['ip'] = $post['ip'];
                ($post['ClubElite'] == "True" ) ? $data['ClubElite'] = TRUE : $data['ClubElite'] = FALSE;
                ($post['Tienda'] == "True" ) ? $data['Tienda'] = TRUE : $data['Tienda'] = FALSE;
                ($post['AutoRegistro'] == "True" ) ? $data['AutoRegistro'] = TRUE : $data['AutoRegistro'] = FALSE;
                ($post['GafeteMultiple'] == "True" ) ? $data['GafeteMultiple'] = TRUE : $data['GafeteMultiple'] = FALSE;
                ($post['Preregistro'] == "True" ) ? $data['Preregistro'] = TRUE : $data['Preregistro'] = FALSE;
                $result['data'] = $data;
                $result['data']['idConfiguracion'] = $id;
                $result['data']['nodo'] = array("idNodo" => $NodeResult['data'][0]['idNodo'], "NombreNodo" => $post['NombreNodo']);
                $result['message'] = $general_text['data']['sas_guardoExito'];
            } else {
                $result['error'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }

        return $this->jsonResponse($result);
    }

    public function UpdateEstadoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        /* Obtención de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            if ($post['idNodo'] == 1000) {
                $data = array(
                    'NombreNodo' => "'" . $post['NombreNodo'] . "'"
                );

                $NodeResult = $this->AdminRSModel->insertNodo($data);

                $data = array('idEdicion' => $post['idEdicion'],
                    'idEvento' => $post['idEvento'],
                    'idCaptura' => $post['idCaptura'],
                    'idNodo' => $NodeResult['data'][0]['idNodo'],
                    'ClubElite' => $post['ClubElite'],
                    'Tienda' => $post['Tienda'],
                    'AutoRegistro' => $post['AutoRegistro'],
                    'ip' => "'" . $post['ip'] . "'",
                    'GafeteMultiple' => $post['GafeteMultiple'],
                    'Preregistro' => $post['Preregistro']);
            } else {
                $data = array('idEdicion' => $post['idEdicion'],
                    'idEvento' => $post['idEvento'],
                    'idCaptura' => $post['idCaptura'],
                    'idNodo' => $post['idNodo'],
                    'ClubElite' => $post['ClubElite'],
                    'Tienda' => $post['Tienda'],
                    'AutoRegistro' => $post['AutoRegistro'],
                    'ip' => "'" . $post['ip'] . "'",
                    'GafeteMultiple' => $post['GafeteMultiple'],
                    'Preregistro' => $post['Preregistro']);
            }

            $where = array('idConfiguracion' => $post['idConfiguracion']);
            $result = $this->AdminRSModel->updateEstado($data, $where);

            if ($result['status']) {
                $result['status'] = TRUE;
                $post['ip'] = $post['ip'];
                ($post['ClubElite'] == "True" ) ? $post['ClubElite'] = TRUE : $post['ClubElite'] = FALSE;
                ($post['Tienda'] == "True" ) ? $post['Tienda'] = TRUE : $post['Tienda'] = FALSE;
                ($post['AutoRegistro'] == "True" ) ? $post['AutoRegistro'] = TRUE : $post['AutoRegistro'] = FALSE;
                ($post['GafeteMultiple'] == "True" ) ? $post['GafeteMultiple'] = TRUE : $post['GafeteMultiple'] = FALSE;
                ($post['Preregistro'] == "True" ) ? $post['Preregistro'] = TRUE : $post['Preregistro'] = FALSE;
                $result['data'] = $post;
                $result['data']['nodo'] = array("idNodo" => $NodeResult['data'][0]['idNodo'], "NombreNodo" => $post['NombreNodo']);
                $result['message'] = $general_text['data']['sas_guardoExito'];
            } else {
                $result['error'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }

        return $this->jsonResponse($result);
    }

    public function DeleteEstadoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        /* Obtención de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $args = array('idConfiguracion' => $post['idConfiguracion']);

//            $args = array('idConfiguracion' => $post);

            $result = $this->AdminRSModel->deleteEstado($args);

            if ($result['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            } else {
                $result['error'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    /* FUNCION PARA LA ACTUALIZACION DE DATOS EN EL DATATABLE */

    public function UpdateGeneralAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        /* Obtención de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $args = array($post['Campo'] => $post['value']);
            $where = array('idConfiguracion' => $post['where']);
            $result = $this->AdminRSModel->updateGeneral($args, $where);

            if ($result['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            } else {
                $result['error'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    /* FUNCION PARA VOLVER A PINTAR DATOS EN UNA TABLA */

    public function getConfiguracionAction(Request $request) {
        $result = $this->AdminRSModel->getEstadoSistema();

        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        return $this->jsonResponse($result);
    }  

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
