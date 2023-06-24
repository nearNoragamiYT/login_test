<?php

namespace Utilerias\EmailBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;

/**
 * Description of EmailModel
 *
 * @author Javier
 */
class EmailModel extends Controller {

    protected $bcc, $files, $ConfigurationModel, $App, $container;

    const APP = 'SAS';

    public function __construct(ContainerInterface $container = NULL) {
        $this->container = $container;
        $this->bcc = array();
        $this->files = array();
        $this->ConfigurationModel = new ConfigurationModel();
        if ($this->container == NULL) {
            $this->App = $this->ConfigurationModel->getApp();
        } else {
            $this->App = $this->container->get('ixpo_configuration')->getApp();
        }
    }

    /**
     *
     * @param type $subject Asunto del correo
     * @param type $to Opcional un sólo correo o arreglo de correos
     * @param type $body twig renderizado del mensaje
     * @param type $lang lenguale, por defecto Español
     * @return type Mailer Object
     */
    public function send_email($subject, $to, $body, $lang = 'es', $file_digi = null) {
        if (isset($lang)) {
            $lang = 'es';
        }
        $title = $this->sanear_string($subject);

        array_push($this->bcc, $this->App['mail_list']['MailDebug']);
        $message = \Swift_Message::newInstance();
        $message->setSubject($title);
        $message->setFrom(array($this->App['mail_list']['MailContacto'] => $this->App['Cliente_' . $lang]));
        $message->setTo($to);
        $message->setBcc($this->bcc);
        $message->setBody($body, 'text/html');
        /*         * Agregamos el cuerpo en texto plano* */
        // $html = new \Html2Text\Html2Text($body);
        //$bodyText = $html->getText();
        //$message->addPart($bodyText, 'text/plain');
        if (count($this->files) > 0) {
            foreach ($this->files as $value) {
                $message->attach(\Swift_Attachment::fromPath($value));
            }
        }
        if ($file_digi != FALSE) {
            $message->attach(\Swift_Attachment::fromPath($file_digi));
        }

        return $this->container->get('mailer')->send($message);
    }

    public function send_emailDocs($subject, $to, $body, $lang = 'es', $file_digi = null) {
        if (isset($lang)) {
            $lang = 'es';
        }
        $title = $this->sanear_string($subject);

        array_push($this->bcc, $this->App['mail_list']['MailDebug']);
        $message = \Swift_Message::newInstance();
        $message->setSubject($title);
        $message->setFrom(array($this->App['mail_list']['MailContacto'] => $this->App['Cliente_' . $lang]));
        $message->setTo($to);
        $message->setBcc($this->bcc);
        $message->setBody($body, 'text/html');
        /*         * Agregamos el cuerpo en texto plano* */
        /* $html = new \Html2Text\Html2Text($body);
          $bodyText = $html->getText();
          $message->addPart($bodyText, 'text/plain'); */
        if (count($this->files) > 0) {
            foreach ($this->files as $value) {
                $message->attach(\Swift_Attachment::fromPath($value));
            }
        }

        if ($file_digi != FALSE) {

            $message->attach(\Swift_Attachment::fromPath($file_digi));
        }

        return $this->container->get('mailer')->send($message);
    }

    public function send_emailAdditionalMail($subject, $to, $body, $lang = 'es') {
        if (isset($lang)) {
            $lang = 'es';
        }
        $title = $this->sanear_string($subject);

        array_push($this->bcc, $this->App['mail_list']['MailDebug']);
        $message = \Swift_Message::newInstance();
        $message->setSubject($title);
        $message->setFrom(array($this->App['mail_list']['MailContacto'] => $this->App['Cliente_' . $lang]));
        $message->setTo($to);
        $message->setBcc($this->bcc);
        $message->setBody($body, 'text/html');
        /*         * Agregamos el cuerpo en texto plano* */
        /* $html = new \Html2Text\Html2Text($body);
          $bodyText = $html->getText();
          $message->addPart($bodyText, 'text/plain'); */
        if (count($this->files) > 0) {
            foreach ($this->files as $value) {
                $message->attach(\Swift_Attachment::fromPath($value));
            }
        }

        if ($file_digi != FALSE) {
            for ($i = 0; $i < count($file_digi); $i++) {
                $message->attach(\Swift_Attachment::fromPath($file_digi[$i]));
            };
        }

        return $this->container->get('mailer')->send($message);
    }

    public function send_document($subject, $to, $body, $lang = 'es', $documents, $bcc = Array()) {
        if (isset($lang)) {
            $lang = 'es';
        }
        $title = $this->sanear_string($subject);
        array_push($bcc, $this->App['mail_list']['MailDebug']);
        $message = \Swift_Message::newInstance();
        $message->setSubject($title);
        $message->setFrom(array($this->App['mail_list']['MailContacto'] => $this->App['Cliente_' . $lang]));
        $message->setTo($to);
        $message->setBcc($bcc);
        $message->setBody($body, 'text/html');
        foreach ($documents as $key => $value) {
            $message->attach(\Swift_Attachment::newInstance($value['doc'], $value['name'], $value['type']));
        }
        return $this->container->get('mailer')->send($message);
    }

    public function send_email_factura($subject, $to, $body, $lang = 'es', $file_factu = null) {
        if (isset($lang)) {
            $lang = 'es';
        }
        $container = new ContainerBuilder();
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . "/../../../../app/config"));
        $loader->load('parameters.yml');
        $mailer_user = $container->getParameter('mailer_user');
        
        $subject = $this->sanear_string($subject);
        array_push($this->bcc, $this->App['mail_list']['MailDebug']);
        $message = \Swift_Message::newInstance();
        $message->setSubject($subject);
        $from = $mailer_user;
        $message->setFrom($from, "ANTAD 2022");
        $message->setTo($to);
        $message->setBcc($this->bcc);
        $message->setBody($body, 'text/html');

        if (count($this->files) > 0) {
            foreach ($this->files as $key => $value) {
                $message->attach(\Swift_Attachment::fromPath($value));
            }
        }

        if ($file_factu != FALSE) {
            for ($i = 0; $i < count($file_factu); $i++) {
                $message->attach(\Swift_Attachment::fromPath($file_factu[$i]));
            }
        }

        return $this->container->get('mailer')->send($message);
    }

    public function setBCC($bcc) {
        $this->bcc = $bcc;
    }

    public function setFiles($files) {
        $this->files = $files;
    }

    /**
     *
     * @param type $error Error message
     * @param type $qry Query excecuted
     * @param type $API PDO PostgreSQL (Default) / WEB
     */
    public function notify_server_error($error = "", $error_description = "", $error_code = "", $API = 'PDO PostgreSQL') {
        $request_uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if (!($this->App['notity_error_server'] && !strstr($request_uri, 'app_dev') && !strstr($request_uri, 'localhost'))) {
            return;
        }

        $container = new ContainerBuilder();
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . "/../../../../app/config"));
        $loader->load('parameters.yml');
        $parameters_db = $container->getParameter('PGSQL_SAS');
        $user_agent = $this->getBrowser();

        $content = array();
        $content['Error Code'] = $error_code;
        $content['URL'] = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $content['Error'] = $error;
        $content['Error Description'] = $error_description;
        $content['Error Type'] = $API;
        $content['Platform'] = self::APP;
        $content['Event'] = $this->App['Cliente_es'];
        $content['Server'] = $parameters_db['db_server'];
        $content['DataBase'] = $parameters_db['db_name'];
        $content['IP'] = $_SERVER['REMOTE_ADDR'];
        $content['Browser'] = $user_agent['name'];
        $content['Version'] = $user_agent['version'];
        $content['OS'] = $user_agent['platform'];
        $content['User Agent'] = $user_agent['userAgent'];
        date_default_timezone_set('America/Mexico_City');
        $content['Date'] = date('m/d/Y h:i:s a', time());

        $body = $this->serverErrorTemplate($content);
        $gm_param = $this->ConfigurationModel->getParametersGmailNotify();

        try {
            $transport = \Swift_SmtpTransport::newInstance($gm_param['mailer_host'], 465, 'ssl')->setUsername($gm_param['mailer_user'])->setPassword($gm_param['mailer_password']);
            $mailer = \Swift_Mailer::newInstance($transport);
            $message = \Swift_Message::newInstance()
                    ->setSubject('Server Error: ' . $API . ' - ' . $parameters_db['db_server'])
                    ->setFrom($gm_param['mailer_user'])
                    ->setTo($this->App['mail_list']['MailNotify'])
                    ->setBody($body, 'text/html');
            $result = $mailer->send($message);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    private function serverErrorTemplate($content) {
        $dom = new \DOMDocument();
        $html = $dom->createElement('html');
        $dom->appendChild($html);

        $head = $dom->createElement('head');
        $head->setAttribute('charset', 'UTF-8');
        $html->appendChild($head);

        $body = $dom->createElement('body');
        $body->setAttribute('style', 'color :#616467; font-family: Arial, Helvetica, sans-serif; margin: 8px; font-size: 12px;');
        $html->appendChild($body);

        $header_error = $dom->createElement('h1');
        $header_error->setAttribute('style', 'width: 100%; color: red; border-bottom: 1px solid #DDD;');
        $header_error->appendChild($dom->createTextNode('An Error Has Ocurred!'));
        $body->appendChild($header_error);

        $table = $dom->createElement('table');
        $table->setAttribute('style', 'width: 100%; border-collapse: collapse; font-family: monospace;');
        $body->appendChild($table);

        foreach ($content as $key => $value) {
            $tr = $dom->createElement('tr');
            $table->appendChild($tr);

            $td = $dom->createElement('td');
            $td->setAttribute('style', 'background: #f5f5f5; border: 1px solid #DDD; padding: 5px;');
            $td->appendChild($dom->createTextNode($key));
            $tr->appendChild($td);

            $td = $dom->createElement('td');
            $td->setAttribute('style', 'border: 1px solid #DDD; padding: 5px;');
            $td->appendChild($dom->createTextNode($value));
            $tr->appendChild($td);
        }

        $html = $dom->saveHTML();
        return html_entity_decode($html, ENT_QUOTES, "UTF-8");
    }

    /**
     * Limpia una cadena de caracteres raros
     * @param type $string Asunto para el correo
     * @return type cadena saneada de caracteres raros
     */
    private function sanear_string($string) {
        $string = trim($string);
        $string = str_replace(array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string);
        $string = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string);
        $string = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string);
        $string = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string);
        $string = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string);
        $string = str_replace(array('ñ', 'Ñ', 'ç', 'Ç'), array('n', 'N', 'c', 'C',), $string);
        //Esta parte se encarga de eliminar cualquier caracter extrano
        $string = str_replace(array("¨", "º", "-", "~", "#", "@", "|", "!", "·", "$", "%", "&", "/", "(", ")", "?", "'", "¡", "¿", "[", "^", "<code>", "]", "+", "}", "{", "¨", "´", ">", "< "), '', $string);
        return $string;
    }

    private function getBrowser() {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'Linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'Mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'Windows';
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

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }
    
    public function send_emailZips($subject, $to, $body, $lang = 'es', $file_digi = null) {
        if (isset($lang)) {
            $lang = 'es';
        }
        $title = $this->sanear_string($subject);

        array_push($this->bcc, $this->App['mail_list']['MailDebug']);
        $message = \Swift_Message::newInstance();
        $message->setSubject($title);
        $message->setFrom(array($this->App['mail_list']['MailContacto'] => $this->App['Cliente_' . $lang]));
        $message->setTo($to);
        $message->setBcc($this->bcc);
        $message->setBody($body, 'text/html');
        
        if ($file_digi != FALSE) {
            for( $i = 0; $i < count($file_digi); $i++){
                $message->attach(\Swift_Attachment::fromPath($file_digi[$i]));
             }
        }

        return $this->container->get('mailer')->send($message);
    }

    


}
