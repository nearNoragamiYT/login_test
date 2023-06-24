<?php

namespace ShowDashboard\RS\AdminEncuestaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\RS\AdminEncuestaBundle\Model\AdminEncuestaModel;
use Symfony\Component\Finder\Finder;

class AdminEncuestaController extends Controller {

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->AdminEncuestaModel = new AdminEncuestaModel();
    }

    const SECTION = 11;

    public function adminEncuestaAction(Request $request) {
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
        /* Obtenemos las preguntas */
        $resultPreguntas = $this->AdminEncuestaModel->getPreguntas();

        if (!$resultPreguntas['status']) {
            throw new \Exception($resultPreguntas['data'], 409);
        }
        $content['Preguntas'] = $resultPreguntas['data'];
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content["breadcrumb"] = $breadcrumb;
        $content['lang'] = $lang;

        return $this->render('ShowDashboardRSAdminEncuestaBundle:Default:EncuestaDashboard.html.twig', array('content' => $content));
    }

    public function pollAnswersAction(Request $request) {//consultar repuestas de pregunta
        $post = $request->request->all();
        $idPregunta = $post['idPregunta'];

        $result_Answers = $this->AdminEncuestaModel->getRespuestas($idPregunta);
        if (!$result_Answers['status']) {
            throw new \Exception($result_Answers['data'], 409);
        }

        return $this->jsonResponse($result_Answers);
    }

    public function updateMultipleAnswersAction(Request $request) {//desactivar respuestas-pregunta
        $post = $request->request->all();
        $pregunta = $post['pregunta'];
        $respuestas = $post['respuestas'];

        foreach ($respuestas as $key => $value) {
            $data = array("Activa" => $value['value']);
            $where = array("idRespuesta" => $value['name']);

            $resultRespuestas = $this->AdminEncuestaModel->updateRespuestas($data, $where);

            if (!$resultRespuestas['status']) {
                throw new \Exception($resultRespuestas['data'], 409);
            }
        }

        return $this->jsonResponse($resultRespuestas);
    }

    public function deactivateQuestionAction(Request $request) {//desactivar-activar pregunta        
        $session = $request->getSession();
        $idEdicion = $session->get('idEdicion');
        $post = $request->request->all();
        $idPregunta = $post['idPregunta'];
        $resultStatus = $this->AdminEncuestaModel->getStatus($idPregunta);
        $status = $resultStatus['data'][0]['Activa'];

        if ($status) {//desactivar
            $estadoNuevo = 0;
            $data = array('Activa' => $estadoNuevo);
            $where = array('idPregunta' => $idPregunta);
            $resultNuevo = $this->AdminEncuestaModel->updatePregunta($data, $where);
            if (!$resultNuevo['status']) {
                throw new \Exception($resultNuevo['data'], 409);
            }
            $action = 'Desactivada';
        } else {//activar
            $estadoNuevo = 1;
            $data = array('Activa' => $estadoNuevo);
            $where = array('idPregunta' => $idPregunta);
            $resultNuevo = $this->AdminEncuestaModel->updatePregunta($data, $where);
            if (!$resultNuevo['status']) {
                throw new \Exception($resultNuevo['data'], 409);
            }
            $action = 'Activada';
        }

        $resultRoute = $this->AdminEncuestaModel->getRout($idEdicion);
        $path = $resultRoute['data'][0]['LinkAE'];

        $directorio = $path;
        $result = $this->rmdir($directorio);

        $data = array('status' => $resultNuevo['status'],
            'action' => $action);

        return $this->jsonResponse($data);
    }

    public function rmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir")
                        rrmdir($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
