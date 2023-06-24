<?php

namespace ShowDashboard\ED\Formas\AdministradorFormasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\ED\Formas\AdministradorFormasBundle\Model\AdministradorFormasModel;

class AdministradorFormasController extends Controller {

    protected $model, $text;

    const MAIN_ROUTE = "show_dashboard_ed_formas_administrador_formas_mostrar";

    public function __construct() {
        $this->model = new AdministradorFormasModel();
        $this->text = new TextoModel();
    }

    public function mostrarAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['routeName'] = self::MAIN_ROUTE;
        $content['edicion'] = $session->get("edicion");
        // ------    Obtenemos los textos generales    ------ //
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* ---  Obtenemos los textos del modulo  --- */
        $section_text = $this->text->getTexts($lang, 4);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];

        //$content['textosNotificaciones'] = $this->texts->obtenerTextos(1, $lang);
        $content['EDApp'] = $this->get('ed_configuration')->EDApp($content['section_text']);
        // ------    Comienzan los datos específicos para este módulo    ------ //
        $content['idioms'] = $content['EDApp']['Idioms'];
        $content['url_ed'] = $content['EDApp']['link_ed'];
        $content["forms_table_metadata"] = $content['EDApp']['setFormsTableMetaData'];
        // ------    Obtenemos las secciones de la base por el idEdicion    ------ //
        $sections = $this->model->getSectionForms($content["idioms"], $content['edicion']['idEdicion']);
        if (!$sections['status']) {
            throw new \Exception($sections['data'], 409);
        }
        $content['sections'] = $sections['data'];
        // ------    Obtenemos las formas de la base por el idEdicion    ------ //
        $result_forms = $this->model->getFormas($content["idioms"], $content['edicion']['idEdicion']);
        if (!$result_forms['status']) {
            throw new \Exception($result_forms['data'], 409);
        }
        $content['forms'] = array();
        $content['forms'] = $result_forms['data'];
        /* ---  genera las rutas para el editor de texto de las formas  --- */
        foreach ($content['forms'] as $id => $form) {
            $content['forms'][$id]['rutaTextosEN'] = $this->generateUrl('show_dashboard_ed_formas_editor_forma', array('idEdicion' => $content['edicion']['idEdicion'], 'idForma' => $form['idForma'], "lang" => 'en'));
            $content['forms'][$id]['rutaTextosES'] = $this->generateUrl('show_dashboard_ed_formas_editor_forma', array('idEdicion' => $content['edicion']['idEdicion'], 'idForma' => $form['idForma'], "lang" => 'es'));
            $content['forms'][$id]['rutaTextosFR'] = $this->generateUrl('show_dashboard_ed_formas_editor_forma', array('idEdicion' => $content['edicion']['idEdicion'], 'idForma' => $form['idForma'], "lang" => 'fr'));
            $content['forms'][$id]['rutaTextosPT'] = $this->generateUrl('show_dashboard_ed_formas_editor_forma', array('idEdicion' => $content['edicion']['idEdicion'], 'idForma' => $form['idForma'], "lang" => 'pt'));
        }
        if (COUNT($content['forms']) > 0) {
            $array_visitors = array_keys($content['forms']);
            $keys = array_keys($content['forms'][$array_visitors[0]]);
            foreach ($content['forms_table_metadata'] as $metadata_key => $metadata_value) {
                $content['forms_table_metadata'][$metadata_key]['col'] = array_search($metadata_key, $keys);
            }
            $content['forms_table_metadata']['check']['col'] = 0;
            $content['forms_table_metadata']['check']['visible'] = true;
            $content['forms_table_metadata']['check']['values'] = 0;
            $content['forms_table_metadata']['check']['text'] = '<input type="checkbox">';
            //text: "Address"values: array[0]length: 0__proto__: array[0]visible: false
        }
        $result_count = $this->model->getNumeroExpositores($content['edicion']['idEdicion']);
        $content['c_exhibitors'] = 0;
        if ($result_count["status"]) {
            $content['c_exhibitors'] = $result_count["data"];
        }
        $content["exhibitors_table_metadata"] = $content['EDApp']['getExhibitorTableMetaData'];
        $content['breadcrumb'] = $this->model->breadcrumb(self::MAIN_ROUTE, $lang);

        return $this->render('ShowDashboardEDFormasAdministradorFormasBundle:Forms:show.html.twig', array('content' => $content));
    }

    public function ordenarAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        if ($request->getMethod() != "POST") {
            die('Error! method incorrect :(');
        }
        $post = $request->request->all();
        $result = $this->model->acualizarOrden($post, $content['idEdicion']);
        $_response = $result;
        $this->deleteCacheED($session->get('edicion'));
        return $this->jsonResponse($_response);
    }

    public function mostrarEmpresaFormaAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $idEdicion = $session->get("idEdicion");
        if ($request->getMethod() != "POST") {
            die('Error! method incorrect :(');
        }
        $post = $request->request->all();
        $Args = array('ee."idEdicion"' => $idEdicion);
        if ($post['StatusForma']) {
            $Args['ef."idForma"'] = $post["idForma"];
            $Args['ef."StatusForma"'] = 1;
            $Args['ef."idEdicion"'] = $idEdicion;
            $Args['ct."idEdicion"'] = $idEdicion;
        } else {
            /* ---  Solo se consulta las empresas que esten en etapa de expositor o que tengan una forma bloqueada sin guardar  --- */
            $post['idEdicion'] = $idEdicion;
            $Args['ee."idEdicion"'] = $idEdicion;
            $Args['ee."idEtapa"'] = 2;
            $Args['ce."Principal"'] = "'TRUE'";            
        }
        $_response = $this->model->getEmpresaForma($Args, $post);
        return $this->jsonResponse($_response);
    }

    public function editarEmalAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $idEdicion = $session->get("idEdicion");
        if ($request->getMethod() != "POST") {
            die('Error! method incorrect your method is ' . $request->getMethod());
        }
        $post = $request->request->all();
        $post['idEdicion'] = $idEdicion;
        $post['lang'] = $lang;
        $_response = $this->model->editarHTML($post);
        $filename = "../var/cache/textos/1_4_" . strtoupper($lang) . ".json";
        unlink($filename);
        return $this->jsonResponse($_response);
    }

    public function enviarEmailExpositoresAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        if ($request->getMethod() != "POST") {
            die('Error! method incorrect your method is ' . $request->getMethod());
        }
        $post = $request->request->all();
        // ------    Obtenemos los textos generales    ------ //
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* ---  Obtenemos los textos del modulo  --- */
        $section_text = $this->text->getTexts($lang, 4);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        $EDApp = $this->get('ed_configuration')->EDApp($content['section_text']);
        $edicion = $session->get("edicion");
        $count = 0; //contador para errores de emails
        $expositores = count($post['exhibitors']);
        if ($expositores > 0) {
            $idioms = $EDApp['Idioms'];
            $exhibitor_list = "";
            $exhibitor_list_error = "";
            foreach ($post['exhibitors'] as $exhibitor) {
                if (array_key_exists($exhibitor['idPais'], $idioms))
                    $lang = $idioms[$exhibitor['idPais']];
                else  //EL RESTO idioma INGLES
                    $lang = 'EN';
                if ($exhibitor['Email'] === "" || $exhibitor['Email'] === null || $exhibitor['Email'] === 'undefined')
                    $exhibitor_list_error .= $exhibitor_list_error . " No email in exhibitor " . $exhibitor['idEmpresa'];
                if (filter_var($exhibitor['Email'], FILTER_VALIDATE_EMAIL)) {
                    $section = $this->text->getTexts($lang, 4);
                    $content['section'] = $section['data'];
                    $content['edicion'] = $edicion;
                    $content['exhibitor'] = $exhibitor;
                    $body = $this->renderView("ShowDashboardEDFormasAdministradorFormasBundle:Email:pending_email.html.twig", array('content' => $content, 'lang' => $lang));

                    /* ENVIO DE EMAIL */
                    $result = $this->get("ixpo_mailer")->send_email($content['section']['sas_emailPendienteLlenar'], trim($exhibitor['Email']), $body, $lang);
                    /* ENVIO DE EMAIL */
                    if ($exhibitor_list != "")
                        $exhibitor_list .= ",";
                    $exhibitor_list .= $exhibitor['idEmpresa'];
                } else {
                    $count = $count + 1;
                    if ($exhibitor_list_error != "")
                        $exhibitor_list_error .= ",";
                    $exhibitor_list_error .= $exhibitor['idEmpresa'] . " - " . $exhibitor['Email'];
                }
            }
        }
        if ($exhibitor_list == "")
            die("Error! Not select Exhibitors :(" . $exhibitor_list);
        if ($exhibitor_list_error != "")
            die('Error! not send email to' . $exhibitor_list_error);
        $result_mail = array('status' => false, 'band' => '');
        if ($result) {
            $result_mail['status'] = true;
            $result_mail['band'] = $count;
        }
        return $this->jsonResponse($result_mail);
    }

    public function desbloquearBloquearAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $idEdicion = $session->get("idEdicion");
        if ($request->getMethod() != "POST") {
            die('Error! method incorrect your method is ' . $request->getMethod());
        }
        $post = $request->request->all();
        $post['idEdicion'] = $idEdicion;
        $edicion = $session->get('edicion');
        $post['idEvento'] = $edicion['idEvento'];
        $_response = $this->model->desbloquarBloquearForma($post);
        return $this->jsonResponse($_response);
    }

    public function mostrarFormasSinInteresAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $idEdicion = $session->get("idEdicion");
        if ($request->getMethod() != "POST") {
            die('Error! method incorrect your method is ' . $request->getMethod());
        }
        $post = $request->request->all();
        $edicion = $session->get('edicion');
        $condition = Array(
            'ee."idEtapa"' => 2,
            'ee."idEdicion"' => $idEdicion,
            'ef."idForma"' => $post['idForma'],
            'ef."idEdicion"' => $idEdicion,
            'ef."idEvento"' => $edicion['idEvento'],
            'ef."Interes"' => 0,
            'ce."idEdicion"' => $idEdicion,
            'ce."Principal"' => "'TRUE'"
        );
        $_response = $this->model->mostrarFormasSinInteres($condition);
        return $this->jsonResponse($_response);
    }

    public function actualizarFechaAction(Request $request, $idForma = 0) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        if ($request->getMethod() != "POST") {
            die('Error! method incorrect :(');
        } else {
            $post = $request->request->all();
            $result = $this->model->acualizarFecha($post, array('idForma' => $idForma, 'idEdicion' => $content['idEdicion']));
            $_response = $result;
        }
        $this->deleteCacheED($session->get('edicion'));
        return $this->jsonResponse($_response);
    }

    public function cambiarEstatusAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        if ($request->getMethod() != "POST") {
            die('Error! method incorrect :(');
        } else {
            $post = $request->request->all();
            $result = $this->model->acualizarEstatus($post, array('idForma' => $post['idForma'], 'idEdicion' => $content['idEdicion']));
            $_response = $result;
        }

        $this->deleteCacheED($session->get('edicion'));
        return $this->jsonResponse($_response);
    }

    public function actualizarPDFAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        if ($request->getMethod() != "POST") {
            die('Error! method incorrect :(');
        } else {
            $post = $request->request->all();
            $archivo = $_FILES['modal-pdf'];
            $result = $this->model->actualizarPDF($archivo, $post, $content['idEdicion']);
            $_response = $result;
        }

        return $this->jsonResponse($_response);
    }

    public function actualizarLinkAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        if ($request->getMethod() != "POST") {
            die('Error! method incorrect :(');
        } else {
            $post = $request->request->all();
            $result = $this->model->actualizarLink($post, $content['idEdicion']);
            $_response = $result;
        }

        $this->deleteCacheED($session->get('edicion'));
        return $this->jsonResponse($_response);
    }

    public function verGraficasAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        if ($request->getMethod() != "POST") {
            die('Error! method incorrect :(');
        } else {
            $post = $request->request->all();
            $result = $this->model->actualizarLink($post, $content['idEdicion']);
            $_response = $result;
        }

        return $this->jsonResponse($_response);
    }

    public function agregarSeccionAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        /* ---  Obtenemos los textos del modulo  --- */
        $section_text = $this->text->getTexts($lang, 4);
        $content['section_text'] = $section_text['data'];
        $content['EDApp'] = $this->get('ed_configuration')->EDApp($content['section_text']);
        if ($request->getMethod() != "POST") {
            die('Error! method incorrect :(');
        } else {
            $post = $request->request->all();
            $visible = array_key_exists("VisibleWeb", $post) ? $post['VisibleWeb'] : 0;
            $habilitado = array_key_exists("HabilitarSeccion", $post) ? $post['HabilitarSeccion'] : 0;
            $post['VisibleWeb'] = $visible;
            $post['HabilitarSeccion'] = $habilitado;
            $result = $this->model->agregarSecccion($post, $content['idEdicion'], $content['EDApp']['Image']['Extensions']);
            $post['idSeccionFormatos'] = $result['idSeccionFormatos'];
            $post['Imagen'] = $result['Imagen'];
            $_response = Array(
                'status' => TRUE,
                'data' => $post
            );
        }
        $this->deleteCacheED($session->get('edicion'));
        return $this->jsonResponse($_response);
    }

    public function agregarImagenAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        /* ---  Obtenemos los textos del modulo  --- */
        $section_text = $this->text->getTexts($lang, 4);
        $content['section_text'] = $section_text['data'];
        $content['EDApp'] = $this->get('ed_configuration')->EDApp($content['section_text']);
        if ($request->getMethod() != "POST") {
            die('Error! Method incorrect');
        }
        $post = $request->request->all();
        $idSeccionFormatos = $post['idSeccionFormatos'];
        $archivo = $_FILES['modal-imagen'];
        $result = $this->model->agregarImagen($archivo, $idSeccionFormatos, $content['idEdicion'], $content['EDApp']['Image']['Extensions']);
        $result["data"]['idSeccionFormatos'] = $idSeccionFormatos;
        $_response = $result;

        return $this->jsonResponse($_response);
    }

    public function eliminarSeccionAction(Request $request, $idSeccionFormatos) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        if ($request->getMethod() != "GET") {
            die('Error! method incorrect :(');
        }
        $this->model->elimiarSeccion($idSeccionFormatos, $content['idEdicion']);

        $_response = Array('idSeccionFormatos' => $idSeccionFormatos);
        $this->deleteCacheED($session->get('edicion'));
        return $this->jsonResponse($_response);
    }

    public function agregarFormaAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        if ($request->getMethod() != "POST") {
            die('Error! method incorrect :(');
        }
        $post = $request->request->all();
        $visible = array_key_exists("FO_FormaVisibleWeb", $post) ? $post['FO_FormaVisibleWeb'] : 0;
        $habilitado = array_key_exists("FO_Habilitado", $post) ? $post['FO_Habilitado'] : 0;
        $post['FO_FormaVisibleWeb'] = $visible;
        $post['FO_Habilitado'] = $habilitado;
        $result = $this->model->agregarForma($post, $content['idEdicion'], $session->get('idEvento'));
        $_response = Array(
            'status' => TRUE,
            'data' => $result
        );

        $this->deleteCacheED($session->get('edicion'));
        return $this->jsonResponse($_response);
    }

    public function editarFormaAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        if ($request->getMethod() != "POST") {
            die('Error! method incorrect :(');
        }
        $post = $request->request->all();
        $visible = array_key_exists("FormaVisibleWeb", $post) ? $post['FormaVisibleWeb'] : 0;
        $habilitado = array_key_exists("Habilitado", $post) ? $post['Habilitado'] : 0;
        $post['FormaVisibleWeb'] = $visible;
        $post['Habilitado'] = $habilitado;
        $this->model->editarForma($post, Array('idEdicion' => $content['idEdicion'], 'idForma' => $post['idForma']));
        $_response = Array(
            'status' => TRUE,
            'data' => $post
        );

        $this->deleteCacheED($session->get('edicion'));
        return $this->jsonResponse($_response);
    }

    public function eliminarFormaAction(Request $request, $idForma) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        if ($request->getMethod() != "POST") {
            die('Error! method incorrect :(');
        }
        $this->model->eliminarForma($idForma, $content['idEdicion']);
        $_response = Array('idForma' => $idForma);
        $this->deleteCacheED($session->get('edicion'));
        return $this->jsonResponse($_response);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function deleteCacheED($edicion) {
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
            die("Error! Not delete cache ");
        }
    }

}
