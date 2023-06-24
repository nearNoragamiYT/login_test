<?php

namespace Utilerias\HandleErrorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExceptionController extends Controller {

    protected $container;

    public function __construct(ContainerInterface $container = null) {
        $this->container = $container;
    }

    public function onKernelException(GetResponseForExceptionEvent $event) {
        return $this->defaultError($event);
    }

    public function defaultError(GetResponseForExceptionEvent $event) {
        // We get the exception object from the received event
        $exception = $event->getException();
        $request = $event->getRequest();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $ruta = __DIR__ . '/../Resources/private/text.json';
        $result_cache = file_get_contents($ruta);
        $textos = json_decode($result_cache, TRUE);

        $content = array();
        $content['lang'] = ($lang != "") ? $lang : 'es';
        $content['textos'] = $textos;
        $status_code = "";
        if (method_exists($exception, 'getStatusCode')) {
            $status_code = $exception->getStatusCode();
        }
        $error_code = $exception->getCode();
        $content['code'] = ($error_code != 0) ? $error_code : $status_code;
        $content['referer'] = $request->headers->get('referer');
        $content['file'] = $event->getException()->getFile();
        $content['line'] = $event->getException()->getLine();
        $content['message'] = $exception->getMessage();

        $this->container->get('ixpo_mailer')->notify_server_error($content['message'], 'En ' . $content['file'] . ' linea ' . $content['line'], $content['code'], 'Web Code');

        $body = $this->container->get('templating')->render('UtileriasHandleErrorBundle:Exception:error.html.twig', array("content" => $content));
        $response = new Response($body);
        $event->setResponse($response);
    }

    public function customtErrorAction($msg) {

        return $this->render('UtileriasHandleErrorBundle:Exception:error.html.twig', array(
                    "error_code" => "Something was wrong",
                    "sorry_message" => "",
                    "error_description" => $msg
        ));
    }

    public function indexAction($name) {
        return $this->render('UtileriasHandleErrorBundle:Default:index.html.twig', array('name' => $name));
    }

}
