<?php

namespace ShowDashboard\RS\AsistenteRSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\RS\AsistenteRSBundle\Model\AsistenteRSModel;

class AsistenteRSController extends Controller {

    protected $AsistenteRSModel;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->AsistenteRSModel = new AsistenteRSModel();
    }

    const SECTION = 11;

    public function AsistenteRSAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $content = array();
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');


        $session->set("companyOrigin", "lectoras");
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
        $result_confEdicion = $this->AsistenteRSModel->getConfiguracionEdicion($lang, $idEdicion);

        if (!$result_confEdicion['status']) {
            throw new \Exception($result_configuracion['data'], 409);
        }

        $result_confRS = $this->AsistenteRSModel->getConfiguracionRS($lang, $idEdicion);

        if (!$result_confRS['status']) {
            throw new \Exception($result_confRS['data'], 409);
        }
        $validacion_es = $result_confRS['data'][1]['Edicion_ES'];
        $validacion_en = $result_confRS['data'][1]['Edicion_EN'];

        $content['Configuracion'] = $result_confRS[data][$idEdicion];

        $result_ediciones = $this->AsistenteRSModel->getEdicion($lang);

        if (!$result_ediciones['status']) {
            throw new \Exception($result_ediciones['data'], 409);
        }
        $content['Edicion'] = $result_ediciones[data];

        return $this->render('ShowDashboardRSAsistenteRSBundle:Default:AsistenteRS.html.twig', array('content' => $content));
    }

    public function insertConfRSAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');

        $result_Edicion = $this->AsistenteRSModel->getNombreEdicion($lang, $idEdicion);

        $edicionEs = $result_Edicion['data'][30]['Edicion_ES'];
        $edicionEn = $result_Edicion['data'][30]['Edicion_EN'];

        /* Obtención de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $permitidos = array("image/png", "image/jpeg", "image/jpg");
            $tamaño = 2;
            foreach ($_FILES as $key => $value) {
                $directorio = '../web/images/logos-co';
                $dir = opendir($directorio); //Abrimos el directorio de destino
                //Movemos y validamos que el archivo se haya cargado correctamente
                //El primer campo es el origen y el segundo el destino
                $source = $_FILES[$key]["tmp_name"];
                $filename = $_FILES[$key]["name"];
                $target_path = $directorio;
                $target_path = $directorio . '/' . $filename;
                if (move_uploaded_file($source, $target_path)) {
                    $result['data'] = "El archivo $filename se ha almacenado de forma exitosa.<br>";
                } else {
                    $result['error'] = "Ha ocurrido un error, por favor inténtelo de nuevo.<br>";
                }
            }
            $multiplicacion = $tamaño * 1024;
            if (isset($_FILES[$key]["size"]) <= $multiplicacion) {

                if ($_FILES['Logo_Es_1']['name'] == "" || $_FILES['Logo_En_1']['name'] == "") {
                    $Logo_EN_1 = $post['Logo_En_1_name'];
                    $Logo_ES_1 = $post['Logo_Es_1_name'];
                } else {
                    $Logo_ES_1 = $_FILES['Logo_Es_1']['name'];
                    $Logo_EN_1 = $_FILES['Logo_En_1']['name'];
                }


                $data = Array(
                    'idEdicion' => $idEdicion,
                    'idEvento' => $idEvento,
                    'Abreviatura' => "'" . $post['Abreviatura'] . "'",
                    'ColorHeader' => "'" . $post['ColorHeader'] . "'",
                    'ColorButton' => "'" . $post['ColorButton'] . "'",
                    'FechaInicio' => "'" . $post['FechaInicio'] . "'",
                    'FechaFin' => "'" . $post['FechaFin'] . "'",
                    'LlaveEncriptacion' => "'" . $post['LlaveEncriptacion'] . "'",
                    'Descripcion' => "'" . $post['Descripcion'] . "'",
                    'Edicion_ES' => "'" . $edicionEs . "'",
                    'Edicion_EN' => "'" . $edicionEn . "'",
                    'Logo_ES_1' => "'" . $Logo_ES_1 . "'",
                    'Logo_ES_2' => "'" . $_FILES['Logo_Es_2']['name'] . "'",
                    'Logo_ES_3' => "'" . $_FILES['Logo_Es_3']['name'] . "'",
                    'Logo_EN_1' => "'" . $Logo_EN_1 . "'",
                    'Logo_EN_2' => "'" . $_FILES['Logo_En_2']['name'] . "'",
                    'Logo_EN_3' => "'" . $_FILES['Logo_En_3']['name'] . "'",
                );

                $result = $this->AsistenteRSModel->insertConfig($data);
                if ($result['status']) {
                    $result['status'] = TRUE;
                    $result['data'] = $post;
                    $result['message'] = $general_text['data']['sas_guardoExito'];
                } else {
                    $result['error'] = $general_text['data']['sas_errorPeticion'];
                }
            } else {
                $result['data'] = $general_text['data']['sas_TamañoImagen'];
            }
            return $this->jsonResponse($result);
        }
    }

    public function updateConfRSAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');

        $result_Edicion = $this->AsistenteRSModel->getNombreEdicion($lang, $idEdicion);

        $edicionEs = $result_Edicion['data'][30]['Edicion_ES'];
        $edicionEn = $result_Edicion['data'][30]['Edicion_EN'];

        /* Obtención de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $permitidos = array("image/png", "image/jpeg", "image/jpg");
            $tamaño = 2;
            foreach ($_FILES as $key => $value) {
                $directorio = '../web/images/logos-co';
                $dir = opendir($directorio); //Abrimos el directorio de destino
                //Movemos y validamos que el archivo se haya cargado correctamente
                //El primer campo es el origen y el segundo el destino
                $source = $_FILES[$key]["tmp_name"];
                $filename = $_FILES[$key]["name"];
                $target_path = $directorio;
                $target_path = $directorio . '/' . $filename;
                if (move_uploaded_file($source, $target_path)) {
                    $result['data'] = "El archivo $filename se ha almacenado de forma exitosa.<br>";
                } else {
                    $result['error'] = "Ha ocurrido un error, por favor inténtelo de nuevo.<br>";
                }
            }
            $multiplicacion = $tamaño * 1024;
            if (isset($_FILES[$key]["size"]) <= $multiplicacion) {

                if ($_FILES['Logo_Es_1']['name'] == "" || $_FILES['Logo_En_1']['name'] == "") {
                    $Logo_EN_1 = $post['Logo_En_1_name'];
                    $Logo_ES_1 = $post['Logo_Es_1_name'];
                } else {
                    $Logo_ES_1 = $_FILES['Logo_Es_1']['name'];
                    $Logo_EN_1 = $_FILES['Logo_En_1']['name'];
                }

                $data = Array(
                    'idEdicion' => $idEdicion,
                    'idEvento' => $idEvento,
                    'Abreviatura' => "'" . $post['Abreviatura'] . "'",
                    'ColorHeader' => "'" . $post['ColorHeader'] . "'",
                    'ColorButton' => "'" . $post['ColorButton'] . "'",
                    'FechaInicio' => "'" . $post['FechaInicio'] . "'",
                    'FechaFin' => "'" . $post['FechaFin'] . "'",
                    'LlaveEncriptacion' => "'" . $post['LlaveEncriptacion'] . "'",
                    'Descripcion' => "'" . $post['Descripcion'] . "'",
                    'Edicion_ES' => "'" . $edicionEs . "'",
                    'Edicion_EN' => "'" . $edicionEn . "'",
                    'Logo_ES_1' => "'" . $Logo_ES_1 . "'",
                    'Logo_ES_2' => "'" . $_FILES['Logo_Es_2']['name'] . "'",
                    'Logo_ES_3' => "'" . $_FILES['Logo_Es_3']['name'] . "'",
                    'Logo_EN_1' => "'" . $Logo_EN_1 . "'",
                    'Logo_EN_2' => "'" . $_FILES['Logo_En_2']['name'] . "'",
                    'Logo_EN_3' => "'" . $_FILES['Logo_En_3']['name'] . "'",
                );

                $where = array(
                    'idConfiguracion' => $post['idConfiguracion']
                );

                $result = $this->AsistenteRSModel->updateConfig($data, $where);

                if ($result['status']) {
                    $result['status'] = TRUE;
                    $result['data'] = $post;
                    $result['message'] = $general_text['data']['sas_guardoExito'];
                } else {
                    $result['error'] = $general_text['data']['sas_errorPeticion'];
                }
            } else {
                $result['data'] = $general_text['data']['sas_TamañoImagen'];
            }
            return $this->jsonResponse($result);
        }
    }

    function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
