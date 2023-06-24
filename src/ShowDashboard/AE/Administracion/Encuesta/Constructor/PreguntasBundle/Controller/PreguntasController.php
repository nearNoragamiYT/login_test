<?php

namespace ShowDashboard\AE\Administracion\Encuesta\Constructor\PreguntasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\AE\Administracion\Encuesta\Constructor\PreguntasBundle\Model\PreguntasModel;
use ShowDashboard\AE\Administracion\Encuesta\Constructor\EncuestaBundle\Model\EncuestaModel;

class PreguntasController extends Controller {

    protected $TextoModel, $PreguntasModel, $EncuestaModel;

    const PLATFORM = 5, SECTION = 3;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->EncuestaModel = new EncuestaModel();
        $this->PreguntasModel = new PreguntasModel();
    }

    public function preguntasAction(Request $request, $idEncuesta, $lang) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        $edicion = $session->get('edicion');

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "show_dashboard_ae_administracion_encuesta_constructor_encuesta");
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }

        /* Obtenemos textos de la sección del ShowDashboard AE 5 */
        $result_text = $this->TextoModel->getTexts($lang, self::PLATFORM);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        $result_encuesta = $this->EncuestaModel->getEncuesta(array("idEncuesta" => $idEncuesta));
        if (!$result_encuesta['status']) {
            throw new \Exception($result_encuesta['data'], 409);
        }

        if (count($result_encuesta['data']) == 0) {
            return $this->redirectToRoute('show_dashboard_ae_administracion_encuesta_constructor_encuesta', array("lang" => $lang));
        }
        $encuesta = $result_encuesta['data'][0];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $obligatoria = "false";
            if (isset($post['Obligatoria'])) {
                $obligatoria = "true";
                unset($post['Obligatoria']);
            }
            if (isset($post['Activa'])) {
                $post['Activa'] = "1";
            } else {
                $post['Activa'] = "0";
            }
            $post['PreguntaES'] = ($post['PreguntaES'] != "") ? "'" . $post['PreguntaES'] . "'" : "";
            $post['PreguntaEN'] = ($post['PreguntaEN'] != "") ? "'" . $post['PreguntaEN'] . "'" : "";
            $post['DescripcionES'] = ($post['DescripcionES'] != "") ? "'" . $post['DescripcionES'] . "'" : "";
            $post['DescripcionEN'] = ($post['DescripcionEN'] != "") ? "'" . $post['DescripcionEN'] . "'" : "";
            $respuestas = $post['respuestas'];
            unset($post['respuestas']);
            /* Insertamos / Editamos la pregunta */
            $result = $this->PreguntasModel->insertEditPregunta($post);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('show_dashboard_ae_administracion_encuesta_constructor_preguntas', array("lang" => $lang, "idEncuesta" => $idEncuesta));
            }
            if (count($result['data']) == 0) {
                $session->getFlashBag()->add('danger', $general_text['sas_errorInterno']);
                return $this->redirectToRoute('show_dashboard_ae_administracion_encuesta_constructor_preguntas', array("lang" => $lang, "idEncuesta" => $idEncuesta));
            }
            $post['idPregunta'] = $result['data'][0]['idPregunta'];

            /* Insertamos / Editamos si es obligatoria la pregunta */
            $result_val = $this->PreguntasModel->insertEditValidacionPreguntaObligatoria($post['idPregunta'], $obligatoria);
            if (!$result_val['status']) {
                $session->getFlashBag()->add('danger', $result_val['data']);
                return $this->redirectToRoute('show_dashboard_ae_administracion_encuesta_constructor_preguntas', array("lang" => $lang, "idEncuesta" => $idEncuesta));
            }

            /* Insertamos / Editamos Respuestas */
            $result = $this->PreguntasModel->insertEditRespuestas($post, $respuestas);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('show_dashboard_ae_administracion_encuesta_constructor_preguntas', array("lang" => $lang, "idEncuesta" => $idEncuesta));
            }
            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            return $this->redirectToRoute('show_dashboard_ae_administracion_encuesta_constructor_preguntas', array("lang" => $lang, "idEncuesta" => $idEncuesta));
        }

        $args = array();
        $args['idEvento'] = $encuesta['idEvento'];
        $args['idEdicion'] = $encuesta['idEdicion'];
        $args['idEncuesta'] = $encuesta['idEncuesta'];
        $result_preguntas = $this->PreguntasModel->getPregunta($args);
        if (!$result_preguntas['status']) {
            throw new \Exception($result_preguntas['data'], 409);
        }
        $preguntas = array();
        if (count($result_preguntas['data']) > 0) {
            foreach ($result_preguntas['data'] as $key => $value) {
                $preguntas[$value['idPregunta']] = $value;
            }
        }
        $args = array('idEncuesta' => $encuesta['idEncuesta']);
        $result_respuestas = $this->PreguntasModel->getRespuesta($args);
        if (!$result_respuestas['status']) {
            throw new \Exception($result_respuestas['data'], 409);
        }
        if (count($result_respuestas['data']) > 0) {
            foreach ($result_respuestas['data'] as $key => $respuesta) {
                if (isset($preguntas[$respuesta['idPregunta']])) {
                    $preguntas[$respuesta['idPregunta']]["Respuestas"][$respuesta['idRespuesta']] = $respuesta;
                }
            }
        }

        $result_preguntaTipo = $this->PreguntasModel->getPreguntaTipo();
        if (!$result_preguntaTipo['status']) {
            throw new \Exception($result_preguntaTipo['data'], 409);
        }
        $preguntaTipo = $result_preguntaTipo['data'];

        $breadcrumb[] = array(
            "Modulo_" . strtoupper($lang) => $section_text['sas_constructorEncuesta'],
            "Permisos" => array("Ver" => TRUE, "Editar" => TRUE, "Borrar" => TRUE)
        );
        $content = array();
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['idSeccion'] = self::SECTION;
        $content['user'] = $user;
        $content['edicion'] = $edicion;
        $content['encuesta'] = $encuesta;
        $content['lang'] = $lang;
        $content['preguntas'] = $preguntas;
        $content['preguntaTipo'] = $preguntaTipo;
        $content['breadcrumb'] = $breadcrumb;
        return $this->render('ShowDashboardAEAdministracionEncuestaConstructorPreguntasBundle:Preguntas:showPreguntas.html.twig', array("content" => $content));
    }

    public function eliminarPreguntaAction(Request $request, $idEncuesta, $lang) {
        $session = $request->getSession();
        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];
        $post = $request->request->all()['delete'];

        $result = $this->PreguntasModel->deletePregunta($post);
        if (!$result['status']) {
            $session->getFlashBag()->add('danger', $result['data']);
            return $this->redirectToRoute('show_dashboard_ae_administracion_encuesta_constructor_preguntas', array("lang" => $lang, "idEncuesta" => $idEncuesta));
        }

        $session->getFlashBag()->add('success', $general_text['sas_eliminoExito']);
        return $this->redirectToRoute('show_dashboard_ae_administracion_encuesta_constructor_preguntas', array("lang" => $lang, "idEncuesta" => $idEncuesta));
    }

    public function eliminarRespuestaAction(Request $request) {
        $post = $request->request->all()['delete_r'];
        $result = $this->PreguntasModel->deleteRespuesta($post);
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function reordenarAction(Request $request) {
        $post = $request->request->all();
        if (count($post['items']) == 0) {
            $response = new Response(json_encode(array("status" => TRUE, "data" => "")));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
        $items = array();
        foreach ($post['items'] as $key => $value) {
            $items[] = array(
                "id" . $post['tabla'] => $key,
                "zzOrden" => $value
            );
        }
        $result = $this->PreguntasModel->reordenarElementos($items, $post['tabla']);
        $response = new Response(json_encode($result));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
