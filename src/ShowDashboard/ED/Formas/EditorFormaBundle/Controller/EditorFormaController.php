<?php

namespace ShowDashboard\ED\Formas\EditorFormaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\ED\Formas\EditorFormaBundle\Model\EditorFormaModel;

class EditorFormaController extends Controller {

    const SECTION = 4, PLATFORM = 4, MAIN_ROUTE = 'show_dashboard_ed_formas_administrador_formas_mostrar', image_rute = "administrador/textos/";

    protected $ConfigurationModel, $App, $EditorFormaModel;

    public function __construct() {
        $this->ConfigurationModel = new ConfigurationModel();
        $this->App = $this->ConfigurationModel->getApp();
        $this->TextoModel = new TextoModel();
        $this->EditorFormaModel = new EditorFormaModel();
    }

    public function editorFormaAction(Request $request, $idEdicion, $idForma, $lang) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        if ($lang == "") {
            $lang = 'es';
        }

        //$session->set('lang', $lang);

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Obtenemos textos de la sección del ShowDashboard 4 */
        $result_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        /* Obtenemos textos de la forma  */
        $args = array(
            'idPlataformaIxpo' => self::PLATFORM,
            'Seccion' => $idForma,
            'idEdicion' => $idEdicion,
        );
        $result_text = $this->EditorFormaModel->getTextosForma($args);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $form_text = $result_text['data'];

        $argsPath = array(
            'idEdicion' => $idEdicion,
            'idForma' => $idForma,
            'lang' => $lang,
        );
        $url_editor = $this->generateUrl('show_dashboard_ed_formas_editor_forma', $argsPath);

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $data = array();
            $data['idPlataformaIxpo'] = self::PLATFORM;
            $data['idEdicion'] = $idEdicion;
            $data['Seccion'] = $idForma;
            $data['Etiqueta'] = $post['Etiqueta'];
            $data['Texto_ES'] = (isset($form_text[$post['Etiqueta']])) ? $form_text[$post['Etiqueta']]['Texto_ES'] : "";
            $data['Texto_EN'] = (isset($form_text[$post['Etiqueta']])) ? $form_text[$post['Etiqueta']]['Texto_EN'] : "";
            $data['Texto_' . strtoupper($lang)] = $post['Texto'];

            $result = $this->EditorFormaModel->insertEditTextosForma($data);
            $this->deleteCacheED($session->get('edicion'), "post");
            if (!$result['status']) {
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            if (count($result['data']) == 0) {
                $result = array('status' => FALSE, 'data' => $general_text['sas_errorPeticion']);
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        /* Obtenemos la forma  */
        $args = array(
            'idForma' => $idForma,
            'idEdicion' => $idEdicion,
        );
        $result_form = $this->EditorFormaModel->getForma($args);
        if (!$result_form['status']) {
            throw new \Exception($result_form['data'], 409);
        }

        if (count($result_form['data']) == 0) {
            $session->getFlashBag()->add('danger', $section_text['ed_formaNoExiste']);
            return $this->redirectToRoute('show_dashboard_ed_formas_administrador_formas_mostrar');
        }
        $form = $result_form['data'][0];

        /* Si el tipo de Forma es de Servicios, los consultamos */
        $servicios = NULL;
        if ($form['idTipoForma'] == 3) {
            $args = array("idForma" => $idForma, "idEdicion" => $idEdicion);
            $result_servicios = $this->EditorFormaModel->getServiciosForma($args);
            if (!$result_servicios['status']) {
                throw new \Exception($result_servicios['data'], 409);
            }
            $servicios = $result_servicios['data'];
        }

        $content = array();
        $content['routeName'] = self::MAIN_ROUTE;
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['form_text'] = $form_text;
        $content['user'] = $user;
        $content['forma'] = $form;
        $content['servicios'] = $servicios;
        $content['url_save_html'] = $url_editor;
        $content['lang'] = $lang;

        $content["breadcrumb"] = $this->EditorFormaModel->breadcrumb(self::MAIN_ROUTE, $lang);
        array_push($content["breadcrumb"], Array("Modulo_" . strtoupper($lang) => $form["NombreForma" . strtoupper($lang)], "Ruta" => self::MAIN_ROUTE, 'Permisos' => array()));
        return $this->render('ShowDashboardEDFormasEditorFormaBundle:EditorForma:showEditorForma.html.twig', array('content' => $content));
    }

    public function servicioAction(Request $request) {
        $session = $request->getSession();
        /*  $profile = $this->getUser();
          $user = $profile->getData(); */
        $lang = $session->get('lang');
        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Obtenemos textos de la sección del ShowDashboard 4 */
        $result_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];
        $post = $request->request->all();
        $this->EditorFormaModel->trimValues($post);
        $servicio = $post;

        $data = array();
        $data['idServicio'] = $post['idServicio'];
        unset($post['idServicio']);
        $data['idEdicion'] = $post['idEdicion'];
        unset($post['idEdicion']);
        $data['idEvento'] = $post['idEvento'];
        unset($post['idEvento']);
        $data['idForma'] = $post['idForma'];
        unset($post['idForma']);
        $data['Orden'] = $post['Orden'];
        unset($post['Orden']);
        $post = array_merge($data, $this->EditorFormaModel->formatQuoteValue($post));
        $result = $this->EditorFormaModel->insertEditServicio($post);
        $this->deleteCacheED($session->get('edicion'), "ajax");
        if (!$result['status'] || count($result['data']) == 0) {
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $servicio['idServicio'] = $result['data'][0]['idServicio'];
        $result['data'] = $servicio;
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function servicioEliminarAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            $response = new Response(json_encode($result_general_text));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $general_text = $result_general_text['data'];

        $post = $request->request->all();

        if ($post['idServicio'] == "") {
            $result = array("status" => FALSE, "data" => $general_text['sas_errorPeticion']);
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        $args = array('idServicio' => $post['idServicio']);
        $result = $this->EditorFormaModel->deleteServicio($args);
        $this->deleteCacheED($session->get('edicion'), "ajax");
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function guardarImagenAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            $response = new Response(json_encode($result_general_text));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $general_text = $result_general_text['data'];

        $archivo = $request->files->get("file");
        $name = $archivo->getClientOriginalName();

        $archivo->move(self::image_rute, $name);
        $result = Array("link" => $request->getRelativeUriForPath('/../../' . self::image_rute . $name));
        $this->deleteCacheED($session->get('edicion'), "ajax");
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function eliminarImagenAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            $response = new Response(json_encode($result_general_text));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $general_text = $result_general_text['data'];
        $post = $request->request->all();
        unlink(self::image_rute . $post["name"]);
        $response = new Response(json_encode(Array("status" => TRUE)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function deleteCacheED($edicion, $type) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $edicion['LinkED'] . "utilerias/deleteCache");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, TRUE);
        if (!$result['status']) {
            if ($type == "ajax") {
                die("Error! Not delete cache");
            } elseif ($type == "post") {
                throw new \Exception("Error! Not delete cache ", 409);
            }
        }
    }

}
