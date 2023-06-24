<?php

namespace ShowDashboard\FT\FacturacionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Utilerias\TextoBundle\Model\TextoModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use ShowDashboard\FT\FacturacionBundle\Model\FacturacionModel;
use SWServices\Authentication\AuthenticationService as Authentication;
use SWServices\Cancelation\CancelationService as CancelationService;

class FacturacionController extends Controller
{
    protected $TextoModel, $FacturacionModel, $cancel,  $usoToken;
    const MAIN_ROUTE = "show_dashboard_facturacion";

    public function __construct()
    {
        $this->TextoModel = new TextoModel();
        $this->FacturacionModel = new FacturacionModel();
    }

    public function timbradasAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set('OriginView', self::MAIN_ROUTE);
        $content = array();
        $facturasTimbradas = array();
        $general_text = $this->TextoModel->getTexts($lang);

        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content["breadcrumb"] = $breadcrumb;
        $content["idModuloIxpo"] = $breadcrumb[0]['idModuloIxpo'];
        $content['general_text'] = $general_text['data'];
        $content['url_pdfs'] = $App['url_pdfs'];
        $content['url_xmls'] = $App['url_xmls'];

        return $this->render('ShowDashboardFTFacturacionBundle:Facturacion:FacturasTimbradas.html.twig', array('content' => $content));
    }

    public function getFacturasTimbradasAction(Request $request)
    {
        $session = $request->getSession();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $params = [
            'idEvento' => $idEvento,
            'idEdicion' => $idEdicion,
        ];
        $response = $this->FacturacionModel->getfacturasTimbradas($params);
        return new JsonResponse($response);
    }

    public function canceldasAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set('OriginView', self::MAIN_ROUTE);
        $content = array();
        $FacturasTimbradas = array();
        $general_text = $this->TextoModel->getTexts($lang);

        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }

        $content["breadcrumb"] = $breadcrumb;
        $content["idModuloIxpo"] = $breadcrumb[0]['idModuloIxpo'];

        $content['general_text'] = $general_text['data'];

        return $this->render('ShowDashboardFTFacturacionBundle:Facturacion:FacturasCanceladas.html.twig', array('content' => $content));
    }

    public function pendientesAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set('OriginView', self::MAIN_ROUTE);
        $content = array();
        $Facturas = array();
        $general_text = $this->TextoModel->getTexts($lang);

        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }

        $content["breadcrumb"] = $breadcrumb;
        $content["idModuloIxpo"] = $breadcrumb[0]['idModuloIxpo'];

        $content['general_text'] = $general_text['data'];
        return $this->render('ShowDashboardFTFacturacionBundle:Facturacion:FacturasPendientes.html.twig', array('content' => $content));
    }

    public function getFacturasPendientesAction(Request $request)
    {
        $post = $request->request->all();
        $session = $request->getSession();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $params = [
            'idEvento' => $idEvento,
            'idEdicion' => $idEdicion,
        ];
        $response = $this->FacturacionModel->getfacturasPendientes($params);
        return new JsonResponse($response);
    }

    public function detalleFacturaAction(Request $request, $idFactura)
    {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $session->set('OriginView', self::MAIN_ROUTE);
        $content = array();
        /////texto generales////
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        ////// fin textos generales////
        ///traemos la informacion del visitante, mediante la factura///
        $resultFactura = $this->FacturacionModel->getFacturaID($idFactura, $idEdicion);
        $motivoCancelacion = $this->FacturacionModel->getMotivoCancelacion();
        ///////////////////////////////////////////////////////////////
        $configuracionPortal = $this->FacturacionModel->getEdicion($idEvento, $idEdicion);
        $resultConfig = $this->FacturacionModel->getConfiguracionPortal($App['IdConfiguracion']);
        $compra = $this->FacturacionModel->getCompraDatos();
        // print_r($compra); die('X_x');

        $content['Factura'] = $resultFactura['data'][0];
        $content['configuracion'] = $resultConfig['data'][0];
        $content["configuracionPortal"] = $configuracionPortal['data'][0];
        $content["motivoCancelacion"] = $motivoCancelacion['data'];

        return $this->render('ShowDashboardFTFacturacionBundle:Facturacion:BaseFacturacion.html.twig', array('content' => $content));
    }

    public function sendEmailFacturaAction(Request $request)
    {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        //////////////textos//////////////////////////////
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        ///////////////////////////////////////////////////

        ////////////Envio de Email/////////////////////////
        $filePdf = $App['url_pdfs'] . $post['idCompra'] . ".pdf";
        $fileXml = $App['url_xmls'] . $post['idCompra'] . ".xml";
        $docs[] = $filePdf;
        $docs[] = $fileXml;
        $body = $this->renderView('ShowDashboardFTFacturacionBundle:Facturacion:emailFacturas.html.twig');
        $res = $this->get('ixpo_mailer')->send_email_factura('ANTAD 2022 Factura', 'juann.infoexpo@gmail.com', $body, 'es', $docs);
        if ($res == 2) {
            $response['status'] = true;
            $response['message'] = 'Factura envida con exito';
        } else {
            $response['status'] = false;
            $response['message'] = 'Error al enviar';
        }
        return new JsonResponse($response);
    }
    // cancelacion 
    public function getFacturasCanceladasAction(Request $request)
    {
        $post = $request->request->all();
        $session = $request->getSession();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $params = [
            'idEvento' => $idEvento,
            'idEdicion' => $idEdicion,
        ];
        $response = $this->FacturacionModel->getfacturasCanceladas($params);
        return new JsonResponse($response);
    }

    //envio de correos mas de un correo
    public function sendEmailFacturasAction(Request $request)
    {
        //aumenta el tiempo de espera de la funcion
        ini_set('max_execution_time', 1800);
        ini_set('memory_limit', '-1');

        $post = $request->request->all();
        $session = $request->getSession();
        $idEdicion = $session->get('idEdicion');
        $App = $this->get('ixpo_configuration')->getApp();

        foreach ($post['idFactura'] as $key => $value) {
            $informacionFactura = $this->FacturacionModel->getFacturaInfo($value, $idEdicion);
            //guarda el id de la factura como esta guardada 
            $idfactura = $informacionFactura['data'][0]['idCompra'];

            //se guardan todos los correos de la base de datos
            // $correo = $informacionFactura['data'][0]['Email'];
            $correo = [
                // /* array 1--- 2*/ 'juan32nb@gmail.com', //valido
                /* array 0--- 1*/
                'juan32nb @@gmail.com', //invalido
                /* array 1--- 2*/ 'juan32nb@gmail.com', //valido
                /* array 2--- 3*/ 'juann.infoexpo@gmail.com', //valido
                /* array 3--- 4*/ 'juann.infoexpo @@gmail.com', //invalido
                /* array 4--- 5*/ '', //invalido
                /* array 5--- 6*/ 'juan32@@gmail.com', //invalido
                /* array 6--- 7*/ 'juan32n@.com' //invalido
            ]; //valido
            //          expresion regular
            $regex = "/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/";

            // Eliminar todos los caracteres ilegales del correo electrónico
            $email = filter_var($correo[$key], FILTER_SANITIZE_EMAIL);
            //aplicamos la expresion regular 
            if (preg_match($regex, $email) == true) {
                // valida el correo donde se elimiaron los caracteres ilegales 
                if (filter_var($email, FILTER_VALIDATE_EMAIL) == true) {
                    ////////////Envio de Email/////////////////////////
                    $filePdf = $App['url_pdfs'] . $idfactura . ".pdf";
                    $fileXml = $App['url_xmls'] . $idfactura . ".xml";
                    $docs[] = $filePdf;
                    $docs[] = $fileXml;
                    $body = $this->renderView('ShowDashboardFTFacturacionBundle:Facturacion:emailFacturas.html.twig');
                    $res = $this->get('ixpo_mailer')->send_email_factura('ANTAD 2022 Factura', $email, $body, 'es', $docs);
                    unset($docs);
                    if ($res) {
                        // $response['status'] = true;
                        $response['message'] = 'Factura envida con exito';
                    }
                }
            } else {
                //almacena los correos y los idFacturas para despues mandarlos al JS 
                $response['datos'][$key][$key] = [
                    $response['listaDeCorreosInvalido'][$key] = $email,
                    $response['listaDeIdInvalido'][$key] = $idfactura
                ];
                //almacena el status y mensaje que usamos en el js 
                $response['status'] = false;
                $response['message'] = 'Error al enviar  la factura ';
            }
        }
        return new JsonResponse($response);
    }
    /************************************************************************************************************** */
    public function cancelacionFacturaAction(Request $request)
    {
        $session = $request->getSession();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $post = $request->request->all();
        $resultConfig = $this->FacturacionModel->getConfiguracionPortal($post['idConfiguracionPortal']);
        /*datos produccion */
        // $rfc = $post['RFC']; /*Producion*/
        // $uuid = $post['UUID'];
        $motivo = $post['ClaveMotivo'];
        // $foliosustitucion=" ";
        /*datos produccion final */
        //llenar los datos con el tipo de usuario dependiedo de la base de datos
        $usoToken = $this->tipo_usuario($resultConfig);
        try { //generamos token
            Authentication::auth($usoToken);
            $result = Authentication::Token();

            if ($result->status == "success") {
                $token = $result->data->token;
            } else
                echo ($result->message);
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), '\n';
        }
        /*Autenticación final */
        /*datos de prueba  */
        $rfc = "EKU9003173C9";
        $uuid = "fe4e71b0-8959-4fb9-8091-f5ac4fb0fef8";
        // $motivo = "03";
        $foliosustitucion = "0e4c30b8-11d8-40d8-894d-ef8b32eb4bdf";

        $cancel = array(
            "url" => $usoToken['url'],
            "token" => $token,
            "rfc" => $rfc,
            "uuid" => $uuid,
            "motivo" => $motivo,
            "foliosustitucion" => $foliosustitucion

        );
        /* */
        if ($motivo != 1) {
            $action =  $motivo;
            try {
                //se llama el metodo cancelacion
                $this->cancel = new CancelationService($usoToken);
                $resultadoJson = $this->cancel->CancelationByUUID($cancel['rfc'], $cancel['uuid'], $action);
            } catch (Exception $e) {
                header('Content-type: text/plain');
                echo $e->getMessage();
            }
        } else {
            $action =  $motivo . "/" . $foliosustitucion;
            try {
                //se llama el metodo cancelacion
                $this->cancel = new CancelationService($usoToken);
                $resultadoJson = $this->cancel->CancelationByUUID($cancel['rfc'], $cancel['uuid'], $action);
            } catch (Exception $e) {
                header('Content-type: text/plain');
                echo $e->getMessage();
            }
        }

        /* cancelationService::Set($cancel);
                $consultaRelacionados = cancelationService::ConsultarCFDIRelacionadosUUID($rfc, $uuid);
                print_r($consultaRelacionados); die('X_x'); 
        */



        /* */
        // try {
        //     //se llama el metodo cancelacion
        //     $this->cancel = new CancelationService($usoToken);
        //     $resultadoJson = $this->cancel->CancelationByUUID($cancel['rfc'], $cancel['uuid'], $action);
        // } catch (Exception $e) {
        //     header('Content-type: text/plain');
        //     echo $e->getMessage();
        // }
        // converto la respues a un array
        $covertirArray = (array) $resultadoJson;
        if ($covertirArray['status'] == 'success') {
            $idClaveMotivo = $this->FacturacionModel->getMotivoCancelacion();
            foreach ($idClaveMotivo['data'] as $key => $value) {
                if ($motivo == $value['Clave']) {
                    $idMotivoC = $value['idMotivoCancelacion'];


                    $JSONDatos = json_encode($resultadoJson);
                    $remplazarJSONDatos = str_replace("'", " ", $JSONDatos);
                    //fecha del servidor
                    date_default_timezone_set('America/Mexico_City');
                    $fechaCancelado = date('Y-m-d\TH:i:s');

                    // $jsonFecha = json_encode($fechaCancelado);
                    $fechaCancelacion = str_replace('T', ' ', $fechaCancelado);

                    $insertarDatos = $this->FacturacionModel->getInsertCancelacionMotivo($post['idFactura'], $idMotivoC, $motivo, $idEdicion, $idEvento, $remplazarJSONDatos, $fechaCancelacion);
                    // print_r($insertarDatos); die('X_x');
                }
            }
        } else if ($covertirArray['status'] == 'error') {
            $idClaveMotivo = $this->FacturacionModel->getMotivoCancelacion();
            foreach ($idClaveMotivo['data'] as $key => $value) {
                if ($motivo == $value['Clave']) {
                    $idMotivoC = $value['idMotivoCancelacion'];


                    $JSONDatos = json_encode($resultadoJson);
                    $remplazarJSONDatos = str_replace("'", " ", $JSONDatos);
                    //fecha del servidor
                    date_default_timezone_set('America/Mexico_City');
                    $fechaCancelado = date('Y-m-d\TH:i:s');

                    // $jsonFecha = json_encode($fechaCancelado);
                    $fechaCancelacion = str_replace('T', ' ', $fechaCancelado);

                    $insertarDatos = $this->FacturacionModel->getInsertCancelacionMotivo($post['idFactura'], $idMotivoC, $motivo, $idEdicion, $idEvento, $remplazarJSONDatos, $fechaCancelacion);
                    // print_r($insertarDatos); die('X_x');
                }
            }
        }
        /* final cancelacion */
        return $this->jsonResponse($insertarDatos);
    }
    // este tipo de usuario son los datos que traemos en la base de datos Facturacion
    function tipo_usuario($baseConfig)
    {
        // -------el idTipoUsuario 1 es para pruebas ------//
        if ($baseConfig['data'][0]['idTipoUsuario'] == 1) {
            $paramst = array(
                'url' => $baseConfig['data'][0]['URL'],
                'user' => $baseConfig['data'][0]['Email'],
                'password' => $baseConfig['data'][0]['Password']
            );
        } // -------el idTipoUsuario 2 es para produccion ------// 
        else if ($baseConfig['data'][0]['idTipoUsuario'] == 2) {
            $paramst = array(
                'url' => $baseConfig['data'][0]['URL'],
                'user' => $baseConfig['data'][0]['Email'],
                'password' => $baseConfig['data'][0]['Password']
            );
        }
        return $paramst;
    }
    protected function jsonResponse($data)
    {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    /*consultar */
    public function consultarAction(Request $request)
    {
        $session = $request->getSession();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $post = $request->request->all();
        print_r($request); die('X_x');
        $resultConfig = $this->FacturacionModel->getConfiguracionPortal($post['idConfiguracionPortal']);
        
        /* final cancelacion */
        // return $this->jsonResponse($insertarDatos);
    }
}
