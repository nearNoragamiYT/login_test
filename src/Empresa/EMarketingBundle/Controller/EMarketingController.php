<?php

namespace Empresa\EMarketingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Empresa\EMarketingBundle\Model\EMarketingModel;
use Utilerias\TextoBundle\Model\TextoModel;

class EMarketingController extends Controller {

    protected $EMarketingModel, $TextoModel;

    const SECTION = 4, MAIN_ROUTE = 'empresa_emarketing';

    public function __construct() {
        $this->EMarketingModel = new EMarketingModel();
        $this->TextoModel = new TextoModel();
    }

    public function indexAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        if (!$session->has('idEdicion')) {
            return $this->redirectToRoute('dashboard', array('lang' => $lang));
        }
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        $content = array();
        $content["breadcrumb"] = $this->EMarketingModel->breadcrumb(self::MAIN_ROUTE, $lang);
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Obtenemos textos de la sección 4 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        $result_emarketing = $this->EMarketingModel->getEMarketing(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_emarketing['status']) {
            throw new \Exception($result_emarketing['data'], 409);
        }
        $emarketings = Array();
        foreach ($result_emarketing['data'] as $key => $value) {
            $emarketings[$value['idEMarketing']] = $value;
            $views = $this->EMarketingModel->getNumeroVistas(Array('idEMarketing' => $value['idEMarketing']));
            $emarketings[$value['idEMarketing']]['NumeroVistas'] = ($views['data'][0]['vistas'] != '') ? $views['data'][0]['vistas'] : 0;
        }
        $content['emarketing'] = $emarketings;
        return $this->render('EmpresaEMarketingBundle:EMarketing:lista_emarketing.html.twig', array('content' => $content));
    }

    public function emarketingNuevoAction(Request $request) {
        $user = $this->getUser()->getData();
        $post = $request->request->all();
        $session = $request->getSession();
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $post['idEvento'] = $idEvento;
            $post['idEdicion'] = $idEdicion;
            $post['idUsuario'] = $user['idUsuario'];
            $res = $this->EMarketingModel->insertEmarketing($post);
            $result['data'] = $res['data'];
            if ($res['status']) {
                $result['status'] = TRUE;
                $post['idEMarketing'] = $res['data'][0]['idEMarketing'];
                $result['data'] = $post;
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function detailAction(Request $request, $idEMarketing) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        if (!$session->has('idEdicion')) {
            return $this->redirectToRoute('dashboard', array('lang' => $lang));
        }
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        $content = array();
        $content["breadcrumb"] = $this->EMarketingModel->breadcrumb(self::MAIN_ROUTE, $lang);
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Obtenemos textos de la sección 4 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        $result_emarketing = $this->EMarketingModel->getEMarketing(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idEMarketing' => $idEMarketing));
        if (!$result_emarketing['status']) {
            throw new \Exception($result_emarketing['data'], 409);
        }
        $content['emarketing'] = $result_emarketing['data'][0];
        $result_empresa = $this->EMarketingModel->getEmpresas(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_empresa['status']) {
            throw new \Exception($result_empresa['data'], 409);
        }
        $empresas = Array();
        foreach ($result_empresa['data'] as $key => $value) {
            $empresas[$value['idEmpresa']] = $value;
            $empresas[$value['idEmpresa']]['EnvioMontajeDesmontaje'] = 0;
        }
        $content['empresas'] = $empresas;
        $result_detalle_emarketing = $this->EMarketingModel->getDetalleEMarketing(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idEMarketing' => $idEMarketing));
        if (!$result_detalle_emarketing['status']) {
            throw new \Exception($result_detalle_emarketing['data'], 409);
        }
        $detalle_emarketing = Array();
        foreach ($result_detalle_emarketing['data'] as $key => $value) {
            $value['DC_NombreComercial'] = $content['empresas'][$value['idEmpresa']]['DC_NombreComercial'];
            $value['Email'] = $content['empresas'][$value['idEmpresa']]['Email'];
            array_push($detalle_emarketing, $value);
            if ($value['EnvioMontajeDesmontaje'] == 1) {
                $content['empresas'][$value['idEmpresa']]['EnvioMontajeDesmontaje'] = 1;
            }
        }
        $content['historial'] = $detalle_emarketing;
        $result_empresa_tipo = $this->EMarketingModel->getEmpresaTipo();
        if (!$result_empresa_tipo['status']) {
            throw new \Exception($result_empresa_tipo['data'], 409);
        }
        $empresa_tipo = Array();
        foreach ($result_empresa_tipo['data'] as $key => $value) {
            $empresa_tipo[$value['idEmpresaTipo']] = $value;
        }
        $content['empresa_tipo'] = $empresa_tipo;
        $result_vendedor = $this->EMarketingModel->getVendedor();
        if (!$result_vendedor['status']) {
            throw new \Exception($result_vendedor['data'], 409);
        }
        $vendedor = Array();
        foreach ($result_vendedor['data'] as $key => $value) {
            $vendedor[$value['idUsuario']] = $value;
        }
        $content['vendedor'] = $vendedor;
        $result_numero_vistas = $this->EMarketingModel->getNumeroVistas(Array('idEMarketing' => $idEMarketing));
        $content['NumeroVistas'] = ($result_numero_vistas['data'][0]['vistas'] != '') ? $result_numero_vistas['data'][0]['vistas'] : 0;
        $content['idEMarketing'] = $idEMarketing;
        return $this->render('EmpresaEMarketingBundle:EMarketing:emarketing.html.twig', array('content' => $content));
    }

    public function emarketignEditarAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $res = $this->EMarketingModel->updateEmarketing($post);
            $result['data'] = $res['data'];
            if ($res['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function emarketignEnviarAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $t_envios = (int) $post['marketing']['TotalEnvios'];
            $n_envio = (int) $post['marketing']['NumeroEnvio'] + 1;
            $detalle_emarketing = Array();
            foreach ($post['send'] as $key => $value) {
                $ixpo_mailer = $this->get('ixpo_mailer');
                //verificamos si se requiere enviar la ficha de montaje/desmontaje
                if ((int) $post['montaje'] == (int) 1) {
                    $content = array();
                    $content["lang"] = $lang;
                    $content['idEvento'] = $idEvento;
                    $content['idEdicion'] = $idEdicion;
                    $content['desmontaje'] = 0;
                    //Obtenemos los datos de la empresa EmpresaForma y Pabellon
                    $result_data_empresa = $this->EMarketingModel->getDataEmpresa(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idEmpresa' => $value['idEmpresa']));
                    if ($result_data_empresa['status'] && count($result_data_empresa['data'][0])) {
                        $content['fichas_montaje'][$value['idEmpresa']] = array_merge($result_data_empresa['data'][0], $value);
                        $content['detalle_forma'][$value['idEmpresa']] = json_decode($content['fichas_montaje'][$value['idEmpresa']]['DetalleForma'], true);
                        $content['idVendedor'] = $value['idVendedor'];
                        //Obtenemos EmpresaTipo
                        $result_empresa_tipo = $this->EMarketingModel->getEmpresaTipo();
                        $content['empresa_tipo'] = Array();
                        foreach ($result_empresa_tipo['data'] as $key => $item) {
                            $content['empresa_tipo'][$item['idEmpresaTipo']] = $item;
                        }
                        //Obtenemos MontajeActividad
                        $result_montaje_actividad = $this->EMarketingModel->getMontajeActividad();
                        $content['montaje_actividad'] = Array();
                        foreach ($result_montaje_actividad['data'] as $key => $item) {
                            $content['montaje_actividad'][$item['idMontajeActividad']] = $item;
                        }
                        //Obtenemos MontajeVehiculo
                        $result_montaje_vehiculo = $this->EMarketingModel->getMontajeVehiculo();
                        $content['montaje_vehiculo'] = Array();
                        foreach ($result_montaje_vehiculo['data'] as $key => $item) {
                            $content['montaje_vehiculo'][$item['idMontajeVehiculo']] = $item;
                        }
                        //Obtenemos EmpresaMontaje
                        $result_empresa_montaje = $this->EMarketingModel->getEmpresaMontaje(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idEmpresa' => $value['idEmpresa']));
                        $content['empresa_montaje'] = $result_empresa_montaje['data'];
                        //Obtenemos OpcionPago
                        $result_opcion_pago = $this->EMarketingModel->getOpcionPago();
                        $content['tipo_espacio'] = Array();
                        foreach ($result_opcion_pago['data'] as $key => $item) {
                            $content['tipo_espacio'][$item['idOpcionPago']] = $item;
                        }
                        //Obtenemos Vendedor
                        $result_vendedor = $this->EMarketingModel->getVendedor();
                        $content['sellers'] = Array();
                        foreach ($result_vendedor['data'] as $key => $item) {
                            $content['sellers'][$item['idVendedor']] = $item;
                        }
                        //Obtenemos Vendedor
                        $result_entidad_fiscal = $this->EMarketingModel->getEmpresaEntidadFiscal(Array('idEmpresaEntidadFiscal' => $value['idEmpresaEntidadFiscal']));
                        $content['entidades_fiscales'] = Array();
                        foreach ($result_entidad_fiscal['data'] as $key => $item) {
                            $content['entidades_fiscales'][$item['idEmpresaEntidadFiscal']] = $item;
                        }
                        $content['without_print'] = TRUE;
                        $body = $this->renderView('EmpresaEmpresaFichaMontajeBundle:FichaMontaje:ficha_pdf_base.html.twig', array('content' => $content));
                        $content['desmontaje'] = 1;
                        $body .= $this->renderView('EmpresaEmpresaFichaMontajeBundle:FichaMontaje:ficha_pdf_base.html.twig', array('content' => $content));

                        $this->createTCPDF($body, Array('idEmpresa' => $value['idEmpresa'], 'DC_NombreComercial' => $this->srtClear($value['DC_NombreComercial'])));
                        $ixpo_mailer->setFiles(Array('administrador/fichas/' . $value['idEmpresa'] . '_' . $this->srtClear($value['DC_NombreComercial']) . '.pdf'));
                    } else {
                        continue;
                    }
                }
                $args = Array(
                    'idEMarketing' => $post['marketing']['idEMarketing'],
                    'idEmpresa' => $value['idEmpresa'],
                    'idEvento' => $idEvento,
                    'idEdicion' => $idEdicion,
                    'CopiaOculta' => "'" . $post['cc'] . "'",
                    'Estatus' => "'0'",
                    'FechaEnvio' => 'now()',
                    'NumeroEnvio' => $n_envio,
                );
                $result_insert_detalle_emarketing = $this->EMarketingModel->insertDetalleEMarketing($args);
                $body = $post['marketing']['Cuerpo'];
                $body = str_replace(Array("%exhibitor_name%", "%user%", "%password%", "%user_invitation%", "%password_invitation%"), Array($value['DC_NombreComercial'], $value['Email'], $value['Password'], $value['UsuarioInvitaciones'], $value['PasswordInvitaciones']), $body);
                $body .= '<img src="http://' . $_SERVER['HTTP_HOST'] . $this->generateUrl('empresa_emarketing_track', array('i' => $result_insert_detalle_emarketing['data'][0]['idDetalleEMarketing'])) . '" alt="" width="0px" height="0px" border="0" />';
                $cc = Array();
                if (!empty($post['cc'])) {
                    $cc = explode(",", $post['cc']);
                }
                $ixpo_mailer->setBCC($cc);
                if (filter_var($value['Email'], FILTER_VALIDATE_EMAIL)) {
                    $result_email = $ixpo_mailer->send_email($post['marketing']['Asunto'], $value['Email'], $body, $lang);                    $estatus = ($result_email) ? 1 : 0;
                } else {
                    $estatus = 0;
                }
                $t_envios = $t_envios + 1;
                $args = Array(
                    'idDetalleEMarketing' => $result_insert_detalle_emarketing['data'][0]['idDetalleEMarketing'],
                    'Estatus' => "'" . $estatus . "'"
                );
                $result_update_detalle_emarketing = $this->EMarketingModel->updateDetalleEMarketing($args);
                $args['FechaEnvio'] = $result_update_detalle_emarketing['data'][0]['FechaEnvio'];
                $args['DC_NombreComercial'] = $value['DC_NombreComercial'];
                $args['Email'] = $value['Email'];
                $args['CopiaOculta'] = $post['cc'];
                $args['Estatus'] = $estatus;
                $args['NumeroEnvio'] = $n_envio;
                array_push($detalle_emarketing, $args);
            }
            $args = Array(
                'idEMarketing' => $post['marketing']['idEMarketing'],
                'NumeroEnvio' => $n_envio,
                'TotalEnvios' => $t_envios,
                'Detalle' => $detalle_emarketing
            );
            $result_numeros_emarketing = $this->EMarketingModel->updateNumerosEmarketing($args);
            if ($result_numeros_emarketing['status']) {
                $result['status'] = TRUE;
                $result['data'] = $args;
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    private function createTCPDF($html, $data) {
        $pdf = $this->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetAuthor('Infoexpo');
        //$pdf->SetTitle(str_replace(" ", "", $data['post']['idEmpresa'] . '_' . $data['post']['DC_NombreComercial'] . '.pdf'));
        //$pdf->SetTitle('titulo del PDF');
        //$pdf->SetSubject('');
        $pdf->SetKeywords('Montaje, Desmontaje');

        // remove default header/footer
        $pdf->setPrintHeader(true);
        $pdf->setPrintFooter(true);

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, '2', PDF_MARGIN_RIGHT);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->SetFont('helvetica', '', 9, '', false);
        $pdf->AddPage();

        $pdf->writeHTML($html, true, false, true, false, '');
        $file = $pdf->Output(str_replace(" ", "", realpath('administrador/fichas') . "/" . $data['idEmpresa'] . '_' . $data['DC_NombreComercial'] . '.pdf'), 'F');
        $pdf_encode = base64_encode($file);
        return $pdf_encode;
    }

    public function srtClear($string) {
        $string = strtolower(trim($string));
        $search = array("á", "é", "í", "ó", "ú", "ä", "ë", "ï", "ö", "ü", "à", "è", "ì", "ò", "ù", "ñ", ".", ";", ":", "¡", "!", "¿", "?", "/", "*", "+", "´", "{", "}", "¨", "â", "ê", "î", "ô", "û", "^", "#", "|", "°", "=", "[", "]", "<", ">", "`", "(", ")", "&", "%", "$", "¬", "@", "Á", "É", "Í", "Ó", "Ú", "Ä", "Ë", "Ï", "Ö", "Ü", "Â", "Ê", "Î", "Ô", "Û", "~", "À", "È", "Ì", "Ò", "Ù", "_", "\\", ",", "'", "²", "º", "ª");
        $replace = array("a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "n", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", "a", "e", "i", "o", "u", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", " ", "A", "E", "I", "O", "U", "A", "E", "I", "O", "U", "A", "E", "I", "O", "U", "", "A", "E", "I", "O", "U", "_", " ", " ", " ", " ", " ", " ");
        $link = str_replace($search, $replace, $string);
        $find = array(' ',);
        $link = str_replace($find, '-', $link);
        $link = trim($link, '-');
        return strtolower($link);
    }

    public function trackingEMarketingAction(Request $request, $i) {
        $ua = $this->getDataUserAgent();
        $url = "https://freegeoip.net/json/" . $ua['ip'];
        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $location_info = curl_exec($c);
        curl_close($c);
        $location_info = json_decode($location_info, true);
        $args = Array();
        $args['Pais'] = $location_info['country_name'];
        $args['Region'] = $location_info['region_name'];
        $args['Ciudad'] = $location_info['city'];
        $args['Plataforma'] = $ua['platform'];
        $args['Navegador'] = $ua['name'];
        $args['UserAgent'] = $ua['userAgent'];
        $args['IP'] = $ua['ip'];
        $args['idDetalleEMarketing'] = $i;
        $result_update_tracking = $this->EMarketingModel->updateTrackingDetalleEMarketing($args);
        $path_img = "../web/images/image.png";
        $headers = Array('Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="image.png"'
        );
        return new Response($path_img, 200, $headers);
    }

    public function hexportEMarketingAction(Request $request, $id) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        if (!$session->has('idEdicion')) {
            return $this->redirectToRoute('dashboard', array('lang' => $lang));
        }
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        $content = array();
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* Obtenemos textos de la sección 4 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        $result_empresa = $this->EMarketingModel->getEmpresas(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion));
        if (!$result_empresa['status']) {
            throw new \Exception($result_empresa['data'], 409);
        }
        $empresas = Array();
        foreach ($result_empresa['data'] as $key => $value) {
            $empresas[$value['idEmpresa']] = $value;
        }
        $content['empresas'] = $empresas;
        $result_detalle_emarketing = $this->EMarketingModel->getDetalleEMarketing(Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idEMarketing' => $id));
        if (!$result_detalle_emarketing['status']) {
            throw new \Exception($result_detalle_emarketing['data'], 409);
        }
        $detalle_emarketing = Array();
        foreach ($result_detalle_emarketing['data'] as $key => $value) {
            $detalle_emarketing[$value['idDetalleEMarketing']]['DC_NombreComercial'] = $content['empresas'][$value['idEmpresa']]['DC_NombreComercial'];
            $detalle_emarketing[$value['idDetalleEMarketing']]['Email'] = $content['empresas'][$value['idEmpresa']]['Email'];
            $detalle_emarketing[$value['idDetalleEMarketing']]['CopiaOculta'] = $value['CopiaOculta'];
            $detalle_emarketing[$value['idDetalleEMarketing']]['Estatus'] = ($value['Estatus'] == 1) ? $content['general_text']['sas_enviado'] : $content['general_text']['sas_falloEnvio'];
            $detalle_emarketing[$value['idDetalleEMarketing']]['FechaEnvio'] = $value['FechaEnvio'];
            $detalle_emarketing[$value['idDetalleEMarketing']]['NumeroEnvio'] = $value['NumeroEnvio'];
            $detalle_emarketing[$value['idDetalleEMarketing']]['Vista'] = ($value['Vista']) ? $content['general_text']['sas_si'] : $content['general_text']['sas_no'];
            $detalle_emarketing[$value['idDetalleEMarketing']]['FechaUltimaVista'] = $value['FechaUltimaVista'];
            $detalle_emarketing[$value['idDetalleEMarketing']]['NumeroVistas'] = $value['NumeroVistas'];
            $detalle_emarketing[$value['idDetalleEMarketing']]['Pais'] = $value['Pais'];
            $detalle_emarketing[$value['idDetalleEMarketing']]['Region'] = $value['Region'];
            $detalle_emarketing[$value['idDetalleEMarketing']]['Ciudad'] = $value['Ciudad'];
            $detalle_emarketing[$value['idDetalleEMarketing']]['Plataforma'] = $value['Plataforma'];
            $detalle_emarketing[$value['idDetalleEMarketing']]['Navegador'] = $value['Navegador'];
        }
        $meta_data = Array(
            $content['section_text']['sas_nombreComercial'],
            $content['section_text']['sas_email'],
            $content['section_text']['sas_copiaOculta'],
            $content['section_text']['sas_estatus'],
            $content['section_text']['sas_fechaEnvio'],
            $content['section_text']['sas_version'],
            $content['section_text']['sas_visto?'],
            $content['section_text']['sas_ultimaVista'],
            $content['section_text']['sas_vistas'],
            $content['section_text']['sas_pais'],
            $content['section_text']['sas_region'],
            $content['section_text']['sas_ciudad'],
            $content['section_text']['sas_plataforma'],
            $content['section_text']['sas_navegador']
        );
        return $this->excelReport($detalle_emarketing, $meta_data, $id . '_history_' . date('d-m-Y G.i'));
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    protected function getDataUserAgent() {
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

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern,
            'ip' => $ip
        );
    }

    public function excelReport($table_data, $table_metadata, $filename) {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("Infoexpo")
                ->setTitle($filename)
                ->setSubject($filename)
                ->setDescription($filename);
        $flag = 1;
        $lastColumn = "A";
        foreach ($table_metadata as $value) {
            $phpExcelObject->getActiveSheet()->getColumnDimension($lastColumn)->setAutoSize(true);
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lastColumn . $flag, $value);
            $lastColumn++;
        }
        $flag++;
        foreach ($table_data as $index) {
            $lastColumn = "A";
            foreach ($index as $key => $value) {
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lastColumn . $flag, $value);
                $lastColumn++;
            }$flag++;
        }

        $phpExcelObject->getActiveSheet()->getStyle("A1:" . $lastColumn . "1")->getFont()->setBold(true);
        $phpExcelObject->setActiveSheetIndex(0);

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename . ".xlsx");

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        $response->headers->set('Expires', '0');

        return $response;
    }

}
