<?php

namespace ShowDashboard\RS\ArchivosRSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\RS\ArchivosRSBundle\Model\ArchivoRSModel;
use ShowDashboard\RS\ArchivosRSBundle\PHPExcel\IOFactory;

class ArchivosRSController extends Controller {

    protected $AsistenteRSModel;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->ArchivoRSModel = new ArchivoRSModel();
    }

    const SECTION = 11;

    public function ArchivosRSAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $content = array();
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
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
        $content['lang'] = $lang;

        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request); ////
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content["breadcrumb"] = $breadcrumb;

        return $this->render('ShowDashboardRSArchivosRSBundle:Default:archivosRS.html.twig', array('content' => $content));
    }

    public function tipoVisitanteArchivosRSAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $content = array();
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
//        $session->set("companyOrigin", "lectoras");
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
        $content['lang'] = $lang;

//        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request); ////
//        if (!$breadcrumb) {
//            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
//            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
//        }
//        $content["breadcrumb"] = $breadcrumb;

        return $this->render('ShowDashboardRSArchivosRSBundle:Default:visitantetipo_archivosRS.html.twig', array('content' => $content));
    }

    public function ArchivoTextoAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $content = array();
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
//        $session->set("companyOrigin", "lectoras");
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
        $content['lang'] = $lang;

        /* se obtiene el catalogo de puertas */
        $resultPuertas = $this->ArchivoRSModel->getPuertas();

        if (!$resultPuertas['status']) {
            throw new \Exception($resultPuertas['data'], 409);
        }

        $content['Puertas'] = $resultPuertas[data];

        /* se obtinen los tipos de lectoras */

        $resultTipoScanners = $this->ArchivoRSModel->getTipoScanners();

        if (!$resultTipoScanners['status']) {
            throw new \Exception($resultTipoScanners['data'], 409);
        }

        $content['ScannerTipo'] = $resultTipoScanners[data];

//        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request); ////
//        if (!$breadcrumb) {
//            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
//            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
//        }
//        $content["breadcrumb"] = $breadcrumb;
        return $this->render('ShowDashboardRSArchivosRSBundle:Default:archivosRS_texto.html.twig', array('content' => $content));
    }

    public function insertArchivosRSAction(Request $request) {
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
            $allowedFileType = ['application/vnd.ms-excel', 'text/xls', 'text/xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];

            if (in_array($_FILES["fileSelected"]["type"], $allowedFileType)) {
                $directorio = '../web/administrador/files/Databases/';

                if (file_exists($directorio)) {
                    
                } else {
                    if (!mkdir($directorio, 0777, true)) {
                        die('Fallo al crear las carpetas...');
                    }
                }

                $targetPath = '../web/administrador/files/Databases/' . $_FILES['fileSelected']['name'];
                move_uploaded_file($_FILES['fileSelected']['tmp_name'], $targetPath);
                $objPHPExcel = \PHPExcel_IOFactory::load($targetPath);
                //Asigno la hoja de calculo activa
                $objPHPExcel->setActiveSheetIndex(0);
                //Obtengo el numero de filas del archivo
                $numRows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();

                for ($i = 2; $i <= $numRows; $i++) {
                    if ($objPHPExcel->getActiveSheet()->getCell('A' . $i)->getCalculatedValue() == null || $objPHPExcel->getActiveSheet()->getCell('B' . $i)->getCalculatedValue() == null || $objPHPExcel->getActiveSheet()->getCell('C' . $i)->getCalculatedValue() == null || $objPHPExcel->getActiveSheet()->getCell('D' . $i)->getCalculatedValue() == null) {
                        $data_incompletos [$i] = Array(
//                    'idVisitante' => $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getCalculatedValue(),
                            'Nombre' => $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getCalculatedValue(),
                            'ApellidoPaterno' => $objPHPExcel->getActiveSheet()->getCell('B' . $i)->getCalculatedValue(),
                            'ApellidoMaterno' => $objPHPExcel->getActiveSheet()->getCell('C' . $i)->getCalculatedValue(),
                            'Email' => $objPHPExcel->getActiveSheet()->getCell('D' . $i)->getCalculatedValue(),
                        );
                    } else {
                        $data_completos[$i] = Array(
//                    'idVisitante' => $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getCalculatedValue(),
                            'Nombre' => $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getCalculatedValue(),
                            'ApellidoPaterno' => $objPHPExcel->getActiveSheet()->getCell('B' . $i)->getCalculatedValue(),
                            'ApellidoMaterno' => $objPHPExcel->getActiveSheet()->getCell('C' . $i)->getCalculatedValue(),
                            'Email' => $objPHPExcel->getActiveSheet()->getCell('D' . $i)->getCalculatedValue(),
                        );
                    }
                }

                $json_insertVisitantes = array(
                    1 => $data_completos
                );
                $json_dataComplete = (json_encode($json_insertVisitantes));
                $json_dataIncomplete = (json_encode($data_incompletos));
                $VisitantesResult = $this->ArchivoRSModel->insertVisitantes($json_dataComplete, $idEdicion, $idEvento);
            } else {
                $result['data'] = $general_text['data']['sas_ArchivoNoPermitido'];
            }
        }
        return $this->jsonResponse($VisitantesResult);
    }

    public function insertArchivoVisitanteTipoAction(Request $request) {
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
            $allowedFileType = ['application/vnd.ms-excel', 'text/xls', 'text/xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            if (in_array($_FILES["fileSelected_visitanteTipo"]["type"], $allowedFileType)) {
                $directorio = '../web/administrador/files/VisitanteTipo/';

                if (file_exists($directorio)) {
                    
                } else {
                    if (!mkdir($directorio, 0777, true)) {
                        die('Fallo al crear las carpetas...');
                    }
                }

                $targetPath = '../web/administrador/files/VisitanteTipo/' . $_FILES['fileSelected_visitanteTipo']['name'];
                move_uploaded_file($_FILES['fileSelected_visitanteTipo']['tmp_name'], $targetPath);
                $objPHPExcel = \PHPExcel_IOFactory::load($targetPath);
                //Asigno la hoja de calculo activa
                $objPHPExcel->setActiveSheetIndex(0);
                //Obtengo el numero de filas del archivo
                $numRows = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();

                for ($i = 2; $i <= $numRows; $i++) {
                    if ($objPHPExcel->getActiveSheet()->getCell('A' . $i)->getCalculatedValue() == null || $objPHPExcel->getActiveSheet()->getCell('B' . $i)->getCalculatedValue() == null) {
                        $data_TipoIncompletos[$i] = Array(
//                        'idVisitanteTipo' => $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getCalculatedValue(),
                            'VisitanteTipoES' => $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getCalculatedValue(),
                            'VisitanteTipoEN' => $objPHPExcel->getActiveSheet()->getCell('B' . $i)->getCalculatedValue(),
                        );
                    } else {
                        $data_TipoCompletos[$i] = Array(
//                        'idVisitanteTipo' => $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getCalculatedValue(),
                            'VisitanteTipoES' => $objPHPExcel->getActiveSheet()->getCell('A' . $i)->getCalculatedValue(),
                            'VisitanteTipoEN' => $objPHPExcel->getActiveSheet()->getCell('B' . $i)->getCalculatedValue(),
                        );
                    }
                }

                $json_insertCatalogo = array(
                    2 => $data_TipoCompletos
                );
                $json_dataTipComplete = (json_encode($json_insertCatalogo));
                $json_dataTipIncomplete = (json_encode($data_TipoIncompletos));
                $VisitanteTipResult = $this->ArchivoRSModel->insertVisitanteTip($json_dataTipComplete, $idEdicion, $idEvento);
            } else {
                $result['data'] = $general_text['data']['sas_ArchivoNoPermitido'];
            }
        }
        return $this->jsonResponse($result);
    }

    public function insertArchivoTextoAction(Request $request) {
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
            
            if ($post['idPuerta'] == 10000) {
                $puertaNueva = array(
                    'NombrePuerta' => "'" . $post['NombrePuerta'] . "'",
                    'idEvento' => $idEvento,
                    'idEdicion' => $idEdicion
                );

                $result_nuevaPuerta = $this->ArchivoRSModel->insertPuerta($puertaNueva);
                $puerta = $result_nuevaPuerta['data'][0]['idPuerta'];
            } else {
                $puerta = $post['idPuerta'];
            }
            $idScannerTipo = $post['idScannerTipo'];
            $allowedFileType = ['text/csv', 'text/plain'];

            if (in_array($_FILES["fileSelected_rsTexto"]["type"], $allowedFileType)) {
                $directorio = '../web/administrador/files/ArchivosTexto/';
                if (file_exists($directorio)) {
                    
                } else {
                    if (!mkdir($directorio, 0777, true)) {
                        die('Fallo al crear las carpetas...');
                    }
                }

                $targetPath = '../web/administrador/files/ArchivosTexto/' . $_FILES['fileSelected_rsTexto']['name'];
                $MiArchivo = $_FILES ['fileSelected_rsTexto']['tmp_name'];
                $fh = fopen($MiArchivo, 'r');
                $myFileContents = fread($fh, filesize($MiArchivo)); //lista de los datos           
                $columnasDatos = preg_split('/\r\n|\r|\n/ ', $myFileContents); //lista de datos en array

                $data = array();
                foreach ($columnasDatos as $key => $value) {
                    $data[$key] = explode(",", $value);
                    $data[$key]["batchId"] = $data[$key][0];
                    $data[$key]["idVisitante"] = 0;
                    unset($data[$key][0]);
                    $data[$key]["headerRow"] = $data[$key][1];
                    unset($data[$key][1]);
                    $data[$key]["horaEscaneo"] = $data[$key][2];
                    unset($data[$key][2]);
                    $data[$key]["fechaEscaneo"] = $data[$key][3];
                    unset($data[$key][3]);
                    $data[$key]["Serial"] = $data[$key][4];
                    unset($data[$key][4]);
                    $data[$key]["idPuerta"] = $puerta;
                    $data[$key]["idScannerTipo"] = $idScannerTipo;

                    $json_insertLecturas = array(
                        3 => $data[$key]
                    );

                    $json_dataLecComplete = json_encode($json_insertLecturas, JSON_FORCE_OBJECT); /* ($json_insertLecturas) */
                    $result = $this->ArchivoRSModel->insertLecturas($json_dataLecComplete, $idEdicion, $idEvento);//                  
                }

                fclose($fh);
                move_uploaded_file($_FILES['fileSelected_rsTexto']['tmp_name'], $targetPath);
            } else {
                $result['data'] = 'Tipo de archivo no permitido.';
            }
        }
        return $this->jsonResponse($result);
    }

    public function insertArchivoTextoAPPAction(data $data) {
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

            if ($post['idPuerta'] == 10000) {
                $puertaNueva = array(
                    'NombrePuerta' => "'" . $post['NombrePuerta'] . "'",
                    'idEvento' => $idEvento,
                    'idEdicion' => $idEdicion
                );

                $result_nuevaPuerta = $this->ArchivoRSModel->insertPuerta($puertaNueva);
                $puerta = $result_nuevaPuerta['data'][0]['idPuerta'];
            } else {
                $puerta = $post['idPuerta'];
            }
            $idScannerTipo = $post['idScannerTipo'];
            $allowedFileType = ['text/csv', 'text/plain'];

            if (in_array($_FILES["fileSelected_rsTexto"]["type"], $allowedFileType)) {
                $directorio = '../web/administrador/files/ArchivosTexto/';

                if (file_exists($directorio)) {
                    
                } else {
                    if (!mkdir($directorio, 0777, true)) {
                        die('Fallo al crear las carpetas...');
                    }
                }

                $targetPath = '../web/administrador/files/ArchivosTexto/' . $_FILES['fileSelected_rsTexto']['name'];
                $nombreArchivo = $_FILES['fileSelected_rsTexto']['name'];

                $MiArchivo = $_FILES ['fileSelected_rsTexto']['tmp_name'];
                $fh = fopen($MiArchivo, 'r');
                $myFileContents = fread($fh, filesize($MiArchivo));

                $jsonFile = json_decode($myFileContents, true);

                $data = array();

                foreach ($jsonFile as $key => $value) {

                    $today = getdate();
                    $fecha = $today['year'] . "-" . $today['mon'] . "-" . $today['mday'];
                    $hora = $today['hours'] . ':' . $today['minutes'] . ':' . $today['seconds'];
                    $data[$key]["idVisitante"] = $jsonFile[$key]['BadgeID'];
                    unset($data[$key][0]);
                    $data[$key]["headerRow"] = 'ECI';
                    unset($data[$key][1]);
                    $data[$key]["horaEscaneo"] = $jsonFile[$key]['Hora'];
                    unset($data[$key][2]);
                    $data[$key]["fechaEscaneo"] = $jsonFile[$key]['Fecha'];
                    unset($data[$key][3]);
                    $data[$key]["horaActual"] = $fecha;
                    unset($data[$key][4]);
                    $data[$key]["fechaActual"] = $hora;
                    unset($data[$key][5]);
                    $data[$key]["Serial"] = $nombreArchivo;
                    unset($data[$key][6]);
                    $data[$key]["idPuerta"] = $puerta;
                    $data[$key]["idScannerTipo"] = $idScannerTipo;
                }

                fclose($fh);
                unset($data[count($data) - 1]); //se elimina la ultima posición por ir vacia.

                $json_insertLecturas = array(
                    3 => $data
                );

                $json_dataLecComplete = json_encode($json_insertLecturas, JSON_FORCE_OBJECT); /* ($json_insertLecturas) */

//                $result = $this->ArchivoRSModel->insertLecturas($json_dataLecComplete, $idEdicion, $idEvento);

                move_uploaded_file($_FILES['fileSelected_rsTexto']['tmp_name'], $targetPath);
            } else {
                $result['data'] = 'Tipo de archivo no permitido.';
            }
        }

        return $this->jsonResponse($result);
    }

    function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
