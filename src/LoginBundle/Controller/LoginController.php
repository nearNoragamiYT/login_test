<?php

namespace LoginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use LoginBundle\Model\Profile;
use LoginBundle\Model\LoginModel;
use Utilerias\TextoBundle\Model\TextoModel;

class LoginController extends Controller {

    protected $LoginModel, $App, $TextoModel;

    const SECTION = 1;

    public function __construct() {
        $ConfigurationModel = new ConfigurationModel();
        $this->App = $ConfigurationModel->getApp();
        $this->LoginModel = new LoginModel();
        $this->TextoModel = new TextoModel();
    }

    public function loginAction(Request $request, $lang) {
        if ($request->getHost() != "localhost" && $this->container->get('kernel')->getEnvironment() == 'prod' && !$this->LoginModel->is_ssl()) {
            return $this->redirect(str_replace('http', 'https', $request->getUri()));
        }
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('dashboard');
        }
        $session = $request->getSession();
        if (!$this->LoginModel->is_defined($lang)) {
            $lang = "es";
        }
        $session->set('lang', $lang);

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Obtenemos textos de la sección del Login 1 */
        $result_section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_section_text['status']) {
            throw new \Exception($result_section_text['data'], 409);
        }

        $section_text = $result_section_text['data'];

        if ($request->getMethod() == 'POST') {
            if (!$request->isXmlHttpRequest()) {
                $session->getFlashBag()->add('danger', $general_text['sas_errorPeticion']);
                $url = $request->headers->get('referer');
                if (!$this->LoginModel->is_defined($url)) {
                    $url = $this->generateUrl('login', array('lang' => $lang));
                }
                return $this->redirect($url);
            }

            $post = $request->request->all();
            $this->LoginModel->trimValues($post);
            $args = array(
                '"Email"' => "'" . strtolower($post['Email']) . "'",
                '"Password"' => "'" . sha1($post['Password'] . $this->App['salt']) . "'",
                '"Activo"' => "true",
            );

            $result = $this->LoginModel->getUsuario($args);
            if (!$result['status']) {
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            if (count($result['data']) == 0) {
                $result['status_aux'] = FALSE;
                $result['data'] = $general_text['sas_credencialesInvalidas'];
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $user = $result['data'][0];
            if ($user['Rol'] == "") {
                $result['status_aux'] = FALSE;
                $result['data'] = $general_text['sas_tipoUsuarioIndefinido'];
                $response = new Response(json_encode($result));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            /* Comite Organizador del Usuario */
            $args = array('Staff' => 'false');
            $result_co = $this->LoginModel->getComiteOrganizador($args);
            if (!$result_co['status']) {
                $response = new Response(json_encode($result_co));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $user['ComiteOrganizador'] = FALSE;
            if (count($result_co['data']) > 0) {
                $user['ComiteOrganizador'] = $result_co['data'][0];
            }

            /* Modulos disponibles para el usuario */
            $result_modulo_user = $this->LoginModel->getModulosEdicionUsuario($user);
            if (!$result_modulo_user['status']) {
                $response = new Response(json_encode($result_modulo_user));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            //Creamos el objeto Profile con los datos presentados por el formulario
            $profile = new Profile($user['Email'], $user['Password'], $this->App['salt'], array($user['Rol']));
            $profile->setData($user);

            // Creamos el token
            $token = new UsernamePasswordToken($profile, $profile->getPassword(), 'main', $profile->getRoles());
            $this->container->get('security.token_storage')->setToken($token);

            // Creamos e iniciamos la sesión
            $session->set('_security_main', serialize($token));
            $session->set('modulos_usuario', $result_modulo_user['data']);
            $session->set('plataformas_usuario', $result_modulo_user['plataformas']);

            $result['status_aux'] = TRUE;
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            /*$sesion_data = $this->getDataLog();
            $sesion_data['Resolucion'] = $post['Resolucion'];
            $sesion_data['Status'] = 1;
            $this->get('ixpo_log')->insertLoginLog($sesion_data);*/
            return $response;
        }

        $comiteOrganizador = NULL;
        $eventos = NULL;
        /* Obtenemos el comite organizador */
        $result_co = $this->LoginModel->getComiteOrganizadorConfiguracion();
        if (!$result_co['status']) {
            throw new \Exception($result_co['data'], 409);
        }
        /* Si no tiene CO, consultamos sus eventos */
        if (count($result_co['data']) > 0) {
            $comiteOrganizador = $result_co['data'][0];
            $result_eventos = $this->LoginModel->getEvento(array('idComiteOrganizador' => $comiteOrganizador['idComiteOrganizador']));
            if (!$result_eventos['status']) {
                throw new \Exception($result_eventos['data'], 409);
            }
            $eventos = $result_eventos['data'];
        }

        $content = array();
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['comite_organizador'] = $comiteOrganizador;
        $content['eventos'] = $eventos;
        /* Captar la ruta hacia donde intenta dirigirse */
        $content['_target_path'] = $session->has('_security.main.target_path') ? $session->get('_security.main.target_path') : $this->generateUrl('dashboard');
        $session->remove('_security.main.target_path');

        return $this->render('LoginBundle:Login:show_login.html.twig', array('content' => $content));
    }

    public function previewEmailAction(Request $request, $twig, $lang) {
        $content = array();
        $content['lang'] = $lang;
        return $this->render($twig, array('content' => $content));
    }

    public function resetPasswordRequestAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        if (!$this->LoginModel->is_defined($lang)) {
            $lang = "es";
        }

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Obtenemos textos de la sección del Login 1 */
        $result_section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_section_text['status']) {
            throw new \Exception($result_section_text['data'], 409);
        }

        $section_text = $result_section_text['data'];

        if (!$request->isXmlHttpRequest()) {
            $session->getFlashBag()->add('danger', $general_text['sas_errorPeticion']);
            $url = $request->headers->get('referer');
            if (!$this->LoginModel->is_defined($url)) {
                $url = $this->generateUrl('login', array('lang' => $lang));
            }
            return $this->redirect($url);
        }

        $post = $request->request->all();
        $this->LoginModel->trimValues($post);

        $args = array(
            '"Email"' => "'" . strtolower($post['Email']) . "'",
        );

        $result = $this->LoginModel->getUsuario($args);

        if (!$result['status']) {
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }

        if (count($result['data']) == 0) {
            $result['status_aux'] = FALSE;
            $result['data'] = $general_text['sas_emailNoEncontrado'];
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $user = $result['data'][0];

        if ($this->LoginModel->is_defined($user['TokenPassword'])) {
            $token = $user['TokenPassword'];
        } else {
            $token = sha1(strrev($user['idUsuario']) . time());

            $values = array('TokenPassword' => "'" . $token . "'");
            $args = array('idUsuario' => $user['idUsuario']);
            $result_update = $this->LoginModel->updateUserData($values, $args);
            if (!$result_update['status']) {
                $response = new Response(json_encode($result_update));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $user['TokenPassword'] = $token;
        }

        /* Estructura envío de email */
        $content = array();
        $content['App'] = $this->App;
        $content['lang'] = $lang;
        $content['user'] = $user;
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $body = $this->renderView('LoginBundle:ResetPassword:reset_password_email.html.twig', array('content' => $content));
        $result_send = $this->get('ixpo_mailer')->send_email($section_text['sas_peticionRestablecerPassword'], $user['Email'], $body);
        /* Fin estructura envio de Email */

        $result = array();
        $result['status'] = TRUE;
        $result['status_aux'] = TRUE;
        $result['data'] = $general_text['sas_emailRecuperarPasswordEnviado'];
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function resetPasswordTokenAction(Request $request, $token, $lang) {
        $session = $request->getSession();
        $lang = strtolower($lang);
        if (!$this->LoginModel->is_defined($lang)) {
            $lang = "es";
        }
        $session->set('lang', $lang);

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        $args = array(
            '"TokenPassword"' => "'" . trim($token) . "'",
            '"Activo"' => "true",
        );

        $result = $this->LoginModel->getUsuario($args);
        if (!$result['status']) {
            $session->getFlashBag()->add('danger', $result['data']);
            return $this->redirectToRoute('login', array('lang' => $lang));
        }

        if (count($result['data']) == 0) {
            $session->getFlashBag()->add('warning', $general_text['sas_tokenInvalido']);
            return $this->redirectToRoute('login', array('lang' => $lang));
        }

        $user = $result['data'][0];

        if ($user['Rol'] == "") {
            $session->getFlashBag()->add('warning', $general_text['sas_tipoUsuarioIndefinido']);
            return $this->redirectToRoute('login', array('lang' => $lang));
        }

        /* Comite Organizador del Usuario */
        $args = array('Staff' => 'false');
        $result_co = $this->LoginModel->getComiteOrganizador($args);
        if (!$result_co['status']) {
            $session->getFlashBag()->add('danger', $result_co['data']);
            return $this->redirectToRoute('login', array('lang' => $lang));
        }

        $user['ComiteOrganizador'] = FALSE;
        if (count($result_co['data']) > 0) {
            $user['ComiteOrganizador'] = $result_co['data'][0];
        }

        /* Modulos disponibles para el usuario */
        $result_modulo_user = $this->LoginModel->getModulosEdicionUsuario($user);
        if (!$result_modulo_user['status']) {
            $session->getFlashBag()->add('danger', $result_modulo_user['data']);
            return $this->redirectToRoute('login', array('lang' => $lang));
        }

        //Creamos el objeto Profile con los datos presentados por el formulario
        $profile = new Profile($user['Email'], $user['Password'], $this->App['salt'], array($user['Rol']));
        $profile->setData($user);

        // Creamos el token
        $token = new UsernamePasswordToken($profile, $profile->getPassword(), 'main', $profile->getRoles());
        $this->container->get('security.token_storage')->setToken($token);

        // Creamos e iniciamos la sesión
        $session->set('_security_main', serialize($token));
        $session->set('modulos_usuario', $result_modulo_user['data']);
        $session->set('plataformas_usuario', $result_modulo_user['plataformas']);

        return $this->redirectToRoute('reset_password');
    }

    public function resetPasswordAction(Request $request) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Obtenemos textos de la sección del Login 1 */
        $result_section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_section_text['status']) {
            throw new \Exception($result_section_text['data'], 409);
        }

        $section_text = $result_section_text['data'];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $this->LoginModel->trimValues($post);

            $password = sha1($post['Password'] . $this->App['salt']);
            $values = array(
                'TokenPassword' => "",
                'Password' => "'" . $password . "'",
            );

            $args = array('idUsuario' => $user['idUsuario']);
            $result_update = $this->LoginModel->updateUserData($values, $args);

            if (!$result_update['status']) {
                $session->getFlashBag()->add('danger', $result_update['data']);
                return $this->redirectToRoute('reset_password');
            }

            $user['Password'] = $password;
            $user['TokenPassword'] = "";
            $profile->setData($user);
            $session->getFlashBag()->add('success', $section_text['sas_passwordActualizado']);
            return $this->redirectToRoute('dashboard');
        }

        $content = array();
        $content['App'] = $this->App;
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['user'] = $user;
        return $this->render('LoginBundle:ResetPassword:show_reset_password.html.twig', array('content' => $content));
    }

    public function getDataLog() {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason

        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Trident/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer 11';
            $ub = "Trident";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } else {
            $bname = 'Unknown';
            $ub = "Unknown";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
                ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = isset($matches['version'][0]) ? $matches['version'][0] : '';
            } else {
                $version = (isset($matches['version'][1])) ? $matches['version'][1] : '';
            }
        } else {
            $version = isset($matches['version'][0]) ? $matches['version'][0] : '';
        }

        // check if we have a number
        if ($version == null || $version == "") {
            $version = "?";
        }

        // getting the IP
        $ip = "";
        if (isset($_SERVER)) {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_CLIENT_IP')) {
                $ip = getenv('HTTP_CLIENT_IP');
            } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
            } else {
                $ip = getenv('REMOTE_ADDR');
            }
        }

        // En algunos casos muy raros la ip es devuelta repetida dos veces separada por coma
        if (strstr($ip, ',')) {
            $ip = array_shift(explode(',', $ip));
        }
        $host = gethostbyaddr($ip);
        return array(
            'Navegador' => $bname,
            'version' => $version,
            'Sistema' => $platform,
            'ip' => $ip,
            'Equipo' => $host
        );
    }

    public function logoutAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        date_default_timezone_set('America/Mexico_City');
        /* Actualizamos el Status del log de Control de Session a 0 = Inactiva */
        /*$data_log['Status'] = 0;
        $data_log['FechaHoraTermino'] = date("Y-m-d h:i:s");
        $this->get('ixpo_log')->updateLoginLog($data_log);*/
        /* Redirige sino lo manda al login en modo produccion */
        $url = $this->generateUrl('login', array('lang' => $lang));
        //clear the token, cancel session and redirect
        $this->container->get('security.token_storage')->setToken(null);
        $this->get('session')->invalidate();
        $session->clear();
        $session->migrate();
        return $this->redirect($url);
    }

}
