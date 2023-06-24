<?php

namespace ShowDashboard\AE\Administracion\Encuesta\ExportEncuestaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\AE\Administracion\Encuesta\ExportEncuestaBundle\Model\ExportEncuestaModel;

class ExportEncuestaController extends Controller {

    protected $TextoModel;

    const PLATFORM = 5, SECTION = 1, MAIN_ROUTE = 'show_dashboard_ae_administracion_encuesta_export_encuesta';

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->ExportEncuestaModel = new ExportEncuestaModel();
    }

    public function ExportEncuestaAction(Request $request) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');

        if (!$session->has('edicion')) {
            return $this->redirectToRoute('dashboard', array('lang' => $lang));
        }
        $edicion = $session->get('edicion');

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado */
        /* $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
          if (!$breadcrumb) {
          $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
          return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
          } */

        /* Obtenemos textos de la sección del ShowDashboard AE 5 */
        $result_text = $this->TextoModel->getTexts($lang, self::PLATFORM);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        $content = array();
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['user'] = $user;
        $content['edicion'] = $edicion;
        $content['breadcrumb'] = $breadcrumb;
        return $this->render('ShowDashboardAEAdministracionEncuestaExportEncuestaBundle:ExportEncuesta:ExportEncuesta.html.twig', array('content' => $content));
    }

    public function QuizExportAction(Request $request) {
        $session = $request->getSession();
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');


        /* Obtenemos los Encuesta */
        $result_encuesta = $this->ExportEncuestaModel->getEncuesta($idEvento, $idEdicion);
        if (!$result_encuesta['status']) {
            throw new \Exception($result_encuesta['data'], 409);
        }
        $entiti = $result_encuesta['data'];
        $data = Array(
            'setLastModifiedBy' => 'Infoexpo',
            'setTitle' => 'ENC',
            'setDescription' => 'Información de tabla encuesta',
            'fields' => Array(
                1 => '_id_Encuesta',
                2 => '_id_Evento',
                3 => '_id_EventoEdicion',
                4 => 'Activa',
                5 => 'Encabezado',
            ),
        );
        return $this->Export($data, $entiti);
    }

    public function PreExportAction(Request $request) {
        $session = $request->getSession();
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');

        /* Obtenemos los Encuesta */
        $result_pregunta = $this->ExportEncuestaModel->getPregunta($idEvento, $idEdicion);
        if (!$result_pregunta['status']) {
            throw new \Exception($result_pregunta['data'], 409);
        }
        $entiti = $result_pregunta['data'];
        $data = Array(
            'setLastModifiedBy' => 'Infoexpo',
            'setTitle' => 'PRE',
            'setDescription' => 'Información de tabla pregunta',
            'fields' => Array(
                1 => '_id_Pregunta',
                2 => '_id_PreguntaTipo',
                3 => '_id_Encuesta',
                4 => 'Activa',
                5 => 'Pregunta_ES',
                6 => 'Pregunta_EN',
                7 => 'Columnas',
                8 => 'FechaCreacion',
                9 => 'FechaModificacion',
                10 => 'c_Subpregunta',
                11 => 'zz_Orden',
                12 => '_id_Evento',
                13 => '_id_EventoEdicion',
            ),
        );

        return $this->Export($data, $entiti);
    }

    public function ResExportAction() {
        /* Obtenemos los Encuesta */
        $result_respuesta = $this->ExportEncuestaModel->getRespuesta();
        if (!$result_respuesta['status']) {
            throw new \Exception($result_respuesta['data'], 409);
        }
        $entiti = $result_respuesta['data'];
        $data = Array(
            'setLastModifiedBy' => 'Infoexpo',
            'setTitle' => 'RES',
            'setDescription' => 'Información de tabla respuesta',
            'fields' => Array(
                1 => '_id_Respuesta',
                2 => '_id_Pregunta',
                3 => 'Respuesta_ES',
                4 => 'Respuesta_EN',
                5 => 'zz_Orden',
                6 => 'Activa',
                7 => 'RespuestaAbierta',
                8 => 'RespuestaAbiertaEtiqueta_ES',
                9 => 'RespuestaAbiertaEtiqueta_EN',
                10 => 'Descripcion_ES',
                11 => 'Descripcion_EN',
                12 => 'FechaCreacion',
                13 => 'FechaModificacion',
                14 => '_id_Visitante_Tipo',
                15 => 'RespuestaAbiertaObligatoria',
                16 => 'c_HabilitaPregunta',
                17 => 'c_DeshabilitaPregunta',
            ),
        );

        return $this->Export($data, $entiti);
    }

    public function ApExportAction() {
        /* Obtenemos los Encuesta */
        $result_ap = $this->ExportEncuestaModel->getAP();
        if (!$result_ap['status']) {
            throw new \Exception($result_ap['data'], 409);
        }
        $entiti = $result_ap['data'];
        $data = Array(
            'setLastModifiedBy' => 'Infoexpo',
            'setTitle' => 'AP',
            'setDescription' => 'Información de tabla activa activa pregunta',
            'fields' => Array(
                1 => '_id_RespuestaPregunta',
                2 => '_id_Respuesta',
                3 => '_id_Pregunta',
                4 => 'Habilita',
            ),
        );

        return $this->Export($data, $entiti);
    }

    public function ValPExportAction() {
        /* Obtenemos los Encuesta */
        $result_valp = $this->ExportEncuestaModel->getValP();
        if (!$result_valp['status']) {
            throw new \Exception($result_valp['data'], 409);
        }
        $entiti = $result_valp['data'];
        $data = Array(
            'setLastModifiedBy' => 'Infoexpo',
            'setTitle' => 'VALP',
            'setDescription' => 'Información de tabla validación pregunta',
            'fields' => Array(
                1 => '_id_ValidacionPregunta',
                2 => '_id_Pregunta',
                3 => '_id_Validacion',
                4 => 'Valor',
            ),
        );

        return $this->Export($data, $entiti);
    }

    public function Export($data, $entiti) {
//      ask the service for a Excel5
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("liuggio")
                ->setLastModifiedBy($data['setLastModifiedBy'])
                ->setTitle($data['setTitle'])
                ->setSubject("")
                ->setDescription($data['setDescription'])
                ->setKeywords("")
                ->setCategory("");

        $phpExcelObject->getActiveSheet()
                ->fromArray(
                        $data['fields'], // The data to set
                        NULL, // Array values with this value will not be set
                        'A1' // Top left coordinate of the worksheet range where
                        //    we want to set these values (default is A1)
        );
        $i = 2;
        foreach ($entiti as $entidad) {
            $celda = "A" . $i++;
            $phpExcelObject->getActiveSheet()
                    ->fromArray(
                            $entidad, // The data to set
                            NULL, // Array values with this value will not be set
                            $celda // Top left coordinate of the worksheet range where
            );
        }
        $phpExcelObject->getActiveSheet()->setTitle($data['setTitle']);
//       Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

//         create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
//        create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
//          adding headers
        $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT, $data['setTitle'] . '.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

}
