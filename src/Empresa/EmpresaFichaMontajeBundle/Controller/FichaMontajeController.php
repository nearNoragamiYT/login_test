<?php

namespace Empresa\EmpresaFichaMontajeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use Empresa\EmpresaFichaMontajeBundle\Model\FichaMontajeModel;

class FichaMontajeController extends Controller {

    protected $TextoModel, $FichaMontajeModel;

    const SECTION = 4;
    const MAIN_ROUTE = "ficha-montaje";

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->FichaMontajeModel = new FichaMontajeModel();
    }

    public function getSellersAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $content = array();
        $content["lang"] = $lang;
        $content['idEvento'] = $idEvento;
        $content['idEdicion'] = $idEdicion;

        /* Obtenemos textos generales */
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

        //Obtenemos Vendedores
        $args = Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion);
        $content['sellers'] = $this->FichaMontajeModel->getSellers($args);
        /* ---  Obtenemos las primeras empresas que mostramos   --- */
        $content['empresas'] = $this->FichaMontajeModel->getEmpresas($args, $lang);

        return $this->render('EmpresaEmpresaFichaMontajeBundle:FichaMontaje:ficha_montaje.html.twig', array('content' => $content));
    }

    //Genera la Ficha de MONTAJE
    public function showPDFAction(Request $request, $idVendedor) {
        $desmontaje = 0;
        $form = 217;
        $etapa = 2;
        $contractStatus = 4;
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $content = array();
        $content["lang"] = $lang;
        $content['idEvento'] = $idEvento;
        $content['idEdicion'] = $idEdicion;
        $content['idUsuario'] = $idVendedor;
        $content['desmontaje'] = $desmontaje;

        /* Obtenemos textos generales */
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

        if ($idVendedor > 0) {
            /* ---  Obtenemos DatosFicha de acuerdo al Vendedor  --- */
            $args = Array('idForma' => $form, 'idEtapa' => $etapa, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idUsuario' => $idVendedor, 'idContratoStatus' => $contractStatus);
            $result_fichas_montaje = $this->FichaMontajeModel->getFichasMontaje($args);
            $content['fichas_montaje'] = $result_fichas_montaje;
        } else {
            /* ---  Obtenemos DatosFicha de todos los vendedores  --- */
            $args = Array('idForma' => $form, 'idEtapa' => $etapa, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idContratoStatus' => $contractStatus);
            $result_fichas_montaje = $this->FichaMontajeModel->getFichasMontajeAll($args);
            $content['fichas_montaje'] = $result_fichas_montaje;
        }
        if (COUNT($result_fichas_montaje) == 0) {
            $session->getFlashBag()->add('warning', $content['section_text']['sas_vendedorSinExpositores']);
            return $this->redirectToRoute("empresa_empresa_ficha_montaje_homepage");
        }

        /*
          //Obtenemos los contratos
          $content['contratos'] = $this->FichaMontajeModel->getContrato($args);

          //Obtenemos los ContratosStands
          //$content['contratostand'] = $this->FichaMontajeModel->getContratoStands($args);
         */
        /* ---  Obtenemos EmpresaForma  */
        /* $args = Array('idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
          $result_empresa_forma = $this->FichaMontajeModel->getEmpresaForma($args);

          //Obtenemos el campo JSON DetalleForma
          $detalle_forma = array();
          foreach ($result_empresa_forma as $key => $value) {
          $detalle_forma[$key] = json_decode($value['DetalleForma'], true);
          }
          $content['detalle_forma'] = $detalle_forma; */

        /* ---  Obtenemos EmpresaTipo  --- */
        $args = Array('idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
        $result_empresa_tipo = $this->FichaMontajeModel->getEmpresaTipo($args);
        $content['empresa_tipo'] = $result_empresa_tipo;

        //Obtenemos un CATALOGO DE ACTIVIDADES MONTAJE
        $content['montaje_actividad'] = $this->FichaMontajeModel->getMontajeActividad($args);

        //Obtenemos un CATALOGO DE VEHICULOS MONTAJE
        $content['montaje_vehiculo'] = $this->FichaMontajeModel->getMontajeVehiculo($args);

        //Obtenemos un CATALOGO DE todas las empresas de montaje
        $content['empresa_montaje'] = $this->FichaMontajeModel->getEmpresaMontaje($args);

        //Obtenemos un CATALOGO DE las Opciones de Pago
        $content['tipo_espacio'] = $this->FichaMontajeModel->getOpcionesPago($args);

        //Obtenemos las entidades fiscales
        $content['entidades_fiscales'] = $this->FichaMontajeModel->getEntidadFiscal($args);

        //Obtenemos los Pabellones
        //$content['pabellones'] = $this->FichaMontajeModel->getPabellones($args);
        //Obtenemos Vendedores
        $args = Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion);
        $content['sellers'] = $this->FichaMontajeModel->getSellers($args);


        return $this->render('EmpresaEmpresaFichaMontajeBundle:FichaMontaje:ficha_pdf_base.html.twig', array('content' => $content));
    }

    //Genera la Ficha de DESMONTAJE
    public function showPDFDesmontajeAction(Request $request, $idVendedor) {

        $desmontaje = 1;
        $form = 217;
        $etapa = 2;
        $contractStatus = 4;
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $content = array();
        $content["lang"] = $lang;
        $content['idEvento'] = $idEvento;
        $content['idEdicion'] = $idEdicion;
        $content['idUsuario'] = $idVendedor;
        $content['desmontaje'] = $desmontaje;

        /* Obtenemos textos generales */
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

        if ($idVendedor > 0) {
            /* ---  Obtenemos DatosFicha de acuerdo al Vendedor  --- */
            $args = Array('idForma' => $form, 'idEtapa' => $etapa, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idUsuario' => $idVendedor, 'idContratoStatus' => $contractStatus);
            $result_fichas_montaje = $this->FichaMontajeModel->getFichasMontaje($args);
            $content['fichas_montaje'] = $result_fichas_montaje;
        } else {
            /* ---  Obtenemos DatosFicha de todos los vendedores  --- */
            $args = Array('idForma' => $form, 'idEtapa' => $etapa, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idContratoStatus' => $contractStatus);
            $result_fichas_montaje = $this->FichaMontajeModel->getFichasMontajeAll($args);
            $content['fichas_montaje'] = $result_fichas_montaje;
        }

        if (COUNT($result_fichas_montaje) == 0) {
            $session->getFlashBag()->add('warning', $content['section_text']['sas_vendedorSinExpositores']);
            return $this->redirectToRoute("empresa_empresa_ficha_montaje_homepage");
        }

        /*
          //Obtenemos los contratos
          $content['contratos'] = $this->FichaMontajeModel->getContrato($args);

          //Obtenemos los ContratosStands
          //$content['contratostand'] = $this->FichaMontajeModel->getContratoStands($args);
         */
        /* ---  Obtenemos EmpresaForma  */
        $args = Array('idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
        $result_empresa_forma = $this->FichaMontajeModel->getEmpresaForma($args);

        //Obtenemos el campo JSON DetalleForma
        $detalle_forma = array();
        foreach ($result_empresa_forma as $key => $value) {
            $detalle_forma[$key] = json_decode($value['DetalleForma'], true);
        }
        $content['detalle_forma'] = $detalle_forma;

        /* ---  Obtenemos EmpresaTipo  --- */
        $args = Array('idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
        $result_empresa_tipo = $this->FichaMontajeModel->getEmpresaTipo($args);
        $content['empresa_tipo'] = $result_empresa_tipo;

        //Obtenemos un CATALOGO DE ACTIVIDADES MONTAJE
        $content['montaje_actividad'] = $this->FichaMontajeModel->getMontajeActividad($args);

        //Obtenemos un CATALOGO DE VEHICULOS MONTAJE
        $content['montaje_vehiculo'] = $this->FichaMontajeModel->getMontajeVehiculo($args);

        //Obtenemos un CATALOGO DE todas las empresas de montaje
        $content['empresa_montaje'] = $this->FichaMontajeModel->getEmpresaMontaje($args);
//print_r($content['empresa_montaje']); die("X_x");
        //Obtenemos un CATALOGO DE las Opciones de Pago
        $content['tipo_espacio'] = $this->FichaMontajeModel->getOpcionesPago($args);

        //Obtenemos las entidades fiscales
        $content['entidades_fiscales'] = $this->FichaMontajeModel->getEntidadFiscal($args);

        //Obtenemos los Pabellones
        //$content['pabellones'] = $this->FichaMontajeModel->getPabellones($args);
        //print_r($content['pabellones']); die("X_x");
        //Obtenemos Vendedores
        $args = Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion);
        $content['sellers'] = $this->FichaMontajeModel->getSellers($args);


        return $this->render('EmpresaEmpresaFichaMontajeBundle:FichaMontaje:ficha_pdf_base.html.twig', array('content' => $content));
    }

    public function descaragarFichaAction(Request $request, $type, $idVendedor, $idEmpresa) {
        $desmontaje = ($type == "desmontaje") ? 1 : 0;
        $form = 217;
        $etapa = 2;
        $contractStatus = 4;
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $content = array();
        $content["lang"] = $lang;
        $content['idEvento'] = $session->get("idEvento");
        $content['idEdicion'] = $session->get("idEdicion");
        $content['idUsuario'] = $idVendedor;
        $content['desmontaje'] = $desmontaje;
        // ------    Obtenemos los textos generales    ------ //
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* ---  Obtenemos los textos del modulo  --- */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* ---  Obtenemos DatosFicha de acuerdo al Vendedor  --- */
        $args = Array('idForma' => $form, 'idEtapa' => $etapa, 'idEvento' => $content['idEvento'], 'idEdicion' => $content['idEdicion'], 'idUsuario' => $idVendedor, 'idContratoStatus' => $contractStatus);
        $result_fichas_montaje = $this->FichaMontajeModel->getFichasMontaje($args, $idEmpresa);
        $content['fichas_montaje'] = $result_fichas_montaje;
        /* ---  Obtenemos EmpresaForma  */
        /* $args = Array('idForma' => $form, 'idEvento' => $content['idEvento'], 'idEdicion' => $content['idEdicion']);
          $result_empresa_forma = $this->FichaMontajeModel->getEmpresaForma($args, $idEmpresa);
          if (COUNT($result_empresa_forma) == 0) {
          $session->getFlashBag()->add('warning', $content['section_text']['sas_expositorNoTieneMontaje']);
          return $this->redirectToRoute("empresa_empresa_ficha_montaje_homepage");
          }
          //Obtenemos el campo JSON DetalleForma
          $detalle_forma = array();
          foreach ($result_empresa_forma as $key => $value) {
          $detalle_forma[$key] = json_decode($value['DetalleForma'], true);
          }
          $content['detalle_forma'] = $detalle_forma; */
        /* ---  Obtenemos EmpresaTipo  --- */
        $args = Array('idForma' => $form, 'idEvento' => $content['idEvento'], 'idEdicion' => $content['idEdicion']);
        $result_empresa_tipo = $this->FichaMontajeModel->getEmpresaTipo($args);
        $content['empresa_tipo'] = $result_empresa_tipo;
        //Obtenemos un CATALOGO DE ACTIVIDADES MONTAJE
        $content['montaje_actividad'] = $this->FichaMontajeModel->getMontajeActividad($args);
        //Obtenemos un CATALOGO DE VEHICULOS MONTAJE
        $content['montaje_vehiculo'] = $this->FichaMontajeModel->getMontajeVehiculo($args);
        //Obtenemos un CATALOGO DE todas las empresas de montaje
        $content['empresa_montaje'] = $this->FichaMontajeModel->getEmpresaMontaje($args);
        //Obtenemos un CATALOGO DE las Opciones de Pago
        $content['tipo_espacio'] = $this->FichaMontajeModel->getOpcionesPago($args);
        //Obtenemos las entidades fiscales
        $content['entidades_fiscales'] = $this->FichaMontajeModel->getEntidadFiscal($args);
        //Obtenemos Vendedores
        $args = Array('idEvento' => $content['idEvento'], 'idEdicion' => $content['idEdicion']);
        $content['sellers'] = $this->FichaMontajeModel->getSellers($args);

        return $this->render('EmpresaEmpresaFichaMontajeBundle:FichaMontaje:ficha_pdf_base.html.twig', array('content' => $content));
    }

//Envio Ficha Montaje----->Comienza Envio de Email
    public function sendEmailFichasAction(Request $request) {
        $desmontaje = 0;
        $form = 217;
        $etapa = 2;
        $contractStatus = 4;
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $content = array();
        $content["lang"] = $lang;
        $content['idEvento'] = $idEvento;
        $content['idEdicion'] = $idEdicion;
        $content['desmontaje'] = $desmontaje;

        /* Obtenemos textos generales */
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
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $idVendedor = $post['idVendedor'];
            $content['idUsuario'] = $idVendedor;
            if ($idVendedor > 0) {
                /* ---  Obtenemos DatosFicha de acuerdo al Vendedor  --- */
                $args = Array('idForma' => $form, 'idEtapa' => $etapa, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idUsuario' => $idVendedor, 'idContratoStatus' => $contractStatus);
                $result_fichas_montaje = $this->FichaMontajeModel->getFichasMontaje($args);
                $content['fichas_montaje'] = $result_fichas_montaje;
            } else {
                /* ---  Obtenemos DatosFicha de todos los vendedores  --- */
                $args = Array('idForma' => $form, 'idEtapa' => $etapa, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idContratoStatus' => $contractStatus);
                $result_fichas_montaje = $this->FichaMontajeModel->getFichasMontajeAll($args);
                $content['fichas_montaje'] = $result_fichas_montaje;
            }
            if (COUNT($result_fichas_montaje) == 0) {
                $session->getFlashBag()->add('warning', $content['section_text']['sas_vendedorSinExpositores']);
                return $this->redirectToRoute("empresa_empresa_ficha_montaje_homepage");
            }

            /* ---  Obtenemos EmpresaTipo  --- */
            $args = Array('idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
            $result_empresa_tipo = $this->FichaMontajeModel->getEmpresaTipo($args);
            $content['empresa_tipo'] = $result_empresa_tipo;

            //Obtenemos un CATALOGO DE ACTIVIDADES MONTAJE
            $content['montaje_actividad'] = $this->FichaMontajeModel->getMontajeActividad($args);

            //Obtenemos un CATALOGO DE VEHICULOS MONTAJE
            $content['montaje_vehiculo'] = $this->FichaMontajeModel->getMontajeVehiculo($args);

            //Obtenemos un CATALOGO DE todas las empresas de montaje
            $content['empresa_montaje'] = $this->FichaMontajeModel->getEmpresaMontaje($args);

            //Obtenemos un CATALOGO DE las Opciones de Pago
            $content['tipo_espacio'] = $this->FichaMontajeModel->getOpcionesPago($args);

            //Obtenemos las entidades fiscales
            $content['entidades_fiscales'] = $this->FichaMontajeModel->getEntidadFiscal($args);

            //Obtenemos los Pabellones
            //$content['pabellones'] = $this->FichaMontajeModel->getPabellones($args);
            //Obtenemos Vendedores
            $args = Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion);
            $content['sellers'] = $this->FichaMontajeModel->getSellers($args);
            $content['without_print'] = TRUE;
            $html = $this->renderView('EmpresaEmpresaFichaMontajeBundle:FichaMontaje:ficha_pdf_base.html.twig', array('content' => $content));
            $base64 = $this->createTCPDF($html, "FichaMontaje");
            $ixpo_mailer = $this->get('ixpo_mailer');
            //Obtenemos Vendedor para enviarle el mail
            $argsv = Array('idVendedor' => $idVendedor);
            $vendedor = $this->FichaMontajeModel->getSeller($argsv);
            $to = Array($vendedor['Email']);
            #print_r($vendedor);echo'<br><br>';print_r($to);die(' <= to1');
            #$to = Array("erict@infoexpo.com.mx");
            $doc = Array(
                Array("doc" => base64_decode($base64), "type" => "application/pdf", "name" => "Ficha de Montaje.pdf")
            );
            $htmlbody = $this->renderView('EmpresaEmpresaFichaMontajeBundle:FichaMontaje:ficha_email.html.twig', array('content' => $content));
            $ixpo_mailer->setFiles($doc);
            $result_email = $ixpo_mailer->send_document("Ficha de Montaje", $to, $htmlbody, Array(), $doc);
            $result = array('status' => false, 'band' => '');
            if ($result_email) {
                $result['status'] = true;
            }
        } else {
            $result['data'] = 'Metodo No Permitido';
        }
        return $this->jsonResponse($result);
    }

//Envio Ficha Desmontaje
    public function sendEmailFichasDesmontajeAction(Request $request) {
        $desmontaje = 1;
        $form = 217;
        $etapa = 2;
        $contractStatus = 4;
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $content = array();
        $content["lang"] = $lang;
        $content['idEvento'] = $idEvento;
        $content['idEdicion'] = $idEdicion;
        $content['idUsuario'] = $idVendedor;
        $content['desmontaje'] = $desmontaje;

        /* Obtenemos textos generales */
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

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $idVendedor = $post['idVendedor'];
            if ($idVendedor > 0) {
                /* ---  Obtenemos DatosFicha de acuerdo al Vendedor  --- */
                $args = Array('idForma' => $form, 'idEtapa' => $etapa, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idUsuario' => $idVendedor, 'idContratoStatus' => $contractStatus);
                $result_fichas_montaje = $this->FichaMontajeModel->getFichasMontaje($args);
                $content['fichas_montaje'] = $result_fichas_montaje;
            } else {
                /* ---  Obtenemos DatosFicha de todos los vendedores  --- */
                $args = Array('idForma' => $form, 'idEtapa' => $etapa, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idContratoStatus' => $contractStatus);
                $result_fichas_montaje = $this->FichaMontajeModel->getFichasMontajeAll($args);
                $content['fichas_montaje'] = $result_fichas_montaje;
            }

            if (COUNT($result_fichas_montaje) == 0) {
                $session->getFlashBag()->add('warning', $content['section_text']['sas_vendedorSinExpositores']);
                return $this->redirectToRoute("empresa_empresa_ficha_montaje_homepage");
            }

            /*
              //Obtenemos los contratos
              $content['contratos'] = $this->FichaMontajeModel->getContrato($args);

              //Obtenemos los ContratosStands
              //$content['contratostand'] = $this->FichaMontajeModel->getContratoStands($args);
             */
            /* ---  Obtenemos EmpresaForma  */
            $args = Array('idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
            $result_empresa_forma = $this->FichaMontajeModel->getEmpresaForma($args);

            //Obtenemos el campo JSON DetalleForma
            $detalle_forma = array();
            foreach ($result_empresa_forma as $key => $value) {
                $detalle_forma[$key] = json_decode($value['DetalleForma'], true);
            }
            $content['detalle_forma'] = $detalle_forma;


            /* ---  Obtenemos EmpresaTipo  --- */
            $args = Array('idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
            $result_empresa_tipo = $this->FichaMontajeModel->getEmpresaTipo($args);
            $content['empresa_tipo'] = $result_empresa_tipo;

            //Obtenemos un CATALOGO DE ACTIVIDADES MONTAJE
            $content['montaje_actividad'] = $this->FichaMontajeModel->getMontajeActividad($args);

            //Obtenemos un CATALOGO DE VEHICULOS MONTAJE
            $content['montaje_vehiculo'] = $this->FichaMontajeModel->getMontajeVehiculo($args);

            //Obtenemos un CATALOGO DE todas las empresas de montaje
            $content['empresa_montaje'] = $this->FichaMontajeModel->getEmpresaMontaje($args);
//print_r($content['empresa_montaje']); die("X_x");
            //Obtenemos un CATALOGO DE las Opciones de Pago
            $content['tipo_espacio'] = $this->FichaMontajeModel->getOpcionesPago($args);

            //Obtenemos las entidades fiscales
            $content['entidades_fiscales'] = $this->FichaMontajeModel->getEntidadFiscal($args);

            //Obtenemos los Pabellones
            //$content['pabellones'] = $this->FichaMontajeModel->getPabellones($args);
            //print_r($content['pabellones']); die("X_x");
            //Obtenemos Vendedores
            $args = Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion);
            $content['sellers'] = $this->FichaMontajeModel->getSellers($args);
            $content['without_print'] = TRUE;

            $html = $this->renderView('EmpresaEmpresaFichaMontajeBundle:FichaMontaje:ficha_pdf_base.html.twig', array('content' => $content));
            $base64 = $this->createTCPDF($html, "FichaDesmontaje");
            $ixpo_mailer = $this->get('ixpo_mailer');
            $argsv = Array('idVendedor' => $idVendedor);
            $vendedor = $this->FichaMontajeModel->getSeller($argsv);
            $to = Array($vendedor['Email']);            
            #print_r($to);die(' <= to2');
            #$to = Array("erict@infoexpo.com.mx");
            $doc = Array(
                Array("doc" => base64_decode($base64), "type" => "application/pdf", "name" => "Ficha de Desmontaje.pdf")
            );
            $htmlbody = $this->renderView('EmpresaEmpresaFichaMontajeBundle:FichaMontaje:ficha_email.html.twig', array('content' => $content));
            $ixpo_mailer->setFiles($doc);
            $result_email = $ixpo_mailer->send_document("Ficha de Montaje", $to, $htmlbody, Array(), $doc);
            $result = array('status' => false, 'band' => '');
            if ($result_email) {
                $result['status'] = true;
            }
        } else {
            $result['data'] = 'Metodo No Permitido';
        }
        return $this->jsonResponse($result);
    }

//Envio Email Montaje Vendedor
    public function sendFichasMontajeEmpresaAction(Request $request) {
        $desmontaje = 0;
        $form = 217;
        $etapa = 2;
        $contractStatus = 4;
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $content = array();
        $content["lang"] = $lang;
        $content['idEvento'] = $idEvento;
        $content['idEdicion'] = $idEdicion;
        $content['idUsuario'] = $idVendedor;
        $content['desmontaje'] = $desmontaje;

        /* Obtenemos textos generales */
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
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $idVendedor = $post['idUsuario'];
            if ($idVendedor > 0) {
                /* ---  Obtenemos DatosFicha de acuerdo al Vendedor  --- */
                $args = Array('idForma' => $form, 'idEtapa' => $etapa, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idUsuario' => $idVendedor, 'idContratoStatus' => $contractStatus);
                $result_fichas_montaje = $this->FichaMontajeModel->getFichasMontaje($args);
                $content['fichas_montaje'] = $result_fichas_montaje;
            } else {
                /* ---  Obtenemos DatosFicha de todos los vendedores  --- */
                $args = Array('idForma' => $form, 'idEtapa' => $etapa, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idContratoStatus' => $contractStatus);
                $result_fichas_montaje = $this->FichaMontajeModel->getFichasMontajeAll($args);
                $content['fichas_montaje'] = $result_fichas_montaje;
            }
            if (COUNT($result_fichas_montaje) == 0) {
                $session->getFlashBag()->add('warning', $content['section_text']['sas_vendedorSinExpositores']);
                return $this->redirectToRoute("empresa_empresa_ficha_montaje_homepage");
            }

            /* ---  Obtenemos EmpresaTipo  --- */
            $args = Array('idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
            $result_empresa_tipo = $this->FichaMontajeModel->getEmpresaTipo($args);
            $content['empresa_tipo'] = $result_empresa_tipo;

            //Obtenemos un CATALOGO DE ACTIVIDADES MONTAJE
            $content['montaje_actividad'] = $this->FichaMontajeModel->getMontajeActividad($args);

            //Obtenemos un CATALOGO DE VEHICULOS MONTAJE
            $content['montaje_vehiculo'] = $this->FichaMontajeModel->getMontajeVehiculo($args);

            //Obtenemos un CATALOGO DE todas las empresas de montaje
            $content['empresa_montaje'] = $this->FichaMontajeModel->getEmpresaMontaje($args);

            //Obtenemos un CATALOGO DE las Opciones de Pago
            $content['tipo_espacio'] = $this->FichaMontajeModel->getOpcionesPago($args);

            //Obtenemos las entidades fiscales
            $content['entidades_fiscales'] = $this->FichaMontajeModel->getEntidadFiscal($args);

            //Obtenemos los Pabellones
            //$content['pabellones'] = $this->FichaMontajeModel->getPabellones($args);
            //Obtenemos Vendedores
            $args = Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion);
            $content['sellers'] = $this->FichaMontajeModel->getSellers($args);
            $content['without_print'] = TRUE;

            $Args = Array('idEmpresa' => $post['idEmpresa'], 'idEdicion' => $idEdicion);
            $resultContacto = $this->FichaMontajeModel->getContactoPrincipal($Args);
            $content['contacto'] = $resultContacto[0];
            $html = $this->renderView('EmpresaEmpresaFichaMontajeBundle:FichaMontaje:ficha_pdf_base.html.twig', array('content' => $content));
            $base64 = $this->createTCPDF($html, "FichaMontaje");
            $ixpo_mailer = $this->get('ixpo_mailer');
            $to = Array($content['contacto']['Email']);
            #print_r($to);die(' <= to3');
            #$to = Array("erict@infoexpo.com.mx");
            $doc = Array(
                Array("doc" => base64_decode($base64), "type" => "application/pdf", "name" => "Ficha de Montaje.pdf")
            );
            $htmlbody = $this->renderView('EmpresaEmpresaFichaMontajeBundle:FichaMontaje:ficha_email.html.twig', array('content' => $content));
            $ixpo_mailer->setFiles($doc);
            $result_email = $ixpo_mailer->send_document("Ficha de Montaje", $to, "$htmlbody", Array(), $doc);
            $result = array('status' => false, 'band' => '');
            if ($result_email) {
                $result['status'] = true;
            }
        } else {
            $result['data'] = 'Metodo No Permitido';
        }
        return $this->jsonResponse($result);
    }

//Envio Email Desmontaje Vendedor
    public function sendFichasDesmontajeEmpresaAction(Request $request) {
        $desmontaje = 1;
        $form = 217;
        $etapa = 2;
        $contractStatus = 4;
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $content = array();
        $content["lang"] = $lang;
        $content['idEvento'] = $idEvento;
        $content['idEdicion'] = $idEdicion;
        $content['idUsuario'] = $idVendedor;
        $content['desmontaje'] = $desmontaje;

        /* Obtenemos textos generales */
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

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $idVendedor = $post['idUsuario'];
            if ($idVendedor > 0) {
                /* ---  Obtenemos DatosFicha de acuerdo al Vendedor  --- */
                $args = Array('idForma' => $form, 'idEtapa' => $etapa, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idUsuario' => $idVendedor, 'idContratoStatus' => $contractStatus);
                $result_fichas_montaje = $this->FichaMontajeModel->getFichasMontaje($args);
                $content['fichas_montaje'] = $result_fichas_montaje;
            } else {
                /* ---  Obtenemos DatosFicha de todos los vendedores  --- */
                $args = Array('idForma' => $form, 'idEtapa' => $etapa, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idContratoStatus' => $contractStatus);
                $result_fichas_montaje = $this->FichaMontajeModel->getFichasMontajeAll($args);
                $content['fichas_montaje'] = $result_fichas_montaje;
            }

            if (COUNT($result_fichas_montaje) == 0) {
                $session->getFlashBag()->add('warning', $content['section_text']['sas_vendedorSinExpositores']);
                return $this->redirectToRoute("empresa_empresa_ficha_montaje_homepage");
            }

            /*
              //Obtenemos los contratos
              $content['contratos'] = $this->FichaMontajeModel->getContrato($args);

              //Obtenemos los ContratosStands
              //$content['contratostand'] = $this->FichaMontajeModel->getContratoStands($args);
             */
            /* ---  Obtenemos EmpresaForma  */
            $args = Array('idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
            $result_empresa_forma = $this->FichaMontajeModel->getEmpresaForma($args);

            //Obtenemos el campo JSON DetalleForma
            $detalle_forma = array();
            foreach ($result_empresa_forma as $key => $value) {
                $detalle_forma[$key] = json_decode($value['DetalleForma'], true);
            }
            $content['detalle_forma'] = $detalle_forma;


            /* ---  Obtenemos EmpresaTipo  --- */
            $args = Array('idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
            $result_empresa_tipo = $this->FichaMontajeModel->getEmpresaTipo($args);
            $content['empresa_tipo'] = $result_empresa_tipo;

            //Obtenemos un CATALOGO DE ACTIVIDADES MONTAJE
            $content['montaje_actividad'] = $this->FichaMontajeModel->getMontajeActividad($args);

            //Obtenemos un CATALOGO DE VEHICULOS MONTAJE
            $content['montaje_vehiculo'] = $this->FichaMontajeModel->getMontajeVehiculo($args);

            //Obtenemos un CATALOGO DE todas las empresas de montaje
            $content['empresa_montaje'] = $this->FichaMontajeModel->getEmpresaMontaje($args);
//print_r($content['empresa_montaje']); die("X_x");
            //Obtenemos un CATALOGO DE las Opciones de Pago
            $content['tipo_espacio'] = $this->FichaMontajeModel->getOpcionesPago($args);

            //Obtenemos las entidades fiscales
            $content['entidades_fiscales'] = $this->FichaMontajeModel->getEntidadFiscal($args);

            //Obtenemos los Pabellones
            //$content['pabellones'] = $this->FichaMontajeModel->getPabellones($args);
            //print_r($content['pabellones']); die("X_x");
            //Obtenemos Vendedores
            $args = Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion);
            $content['sellers'] = $this->FichaMontajeModel->getSellers($args);
            $content['without_print'] = TRUE;
            
            $Args = Array('idEmpresa' => $post['idEmpresa'], 'idEdicion' => $idEdicion);
            $resultContacto = $this->FichaMontajeModel->getContactoPrincipal($Args);
            $content['contacto'] = $resultContacto[0];
            
            $html = $this->render('EmpresaEmpresaFichaMontajeBundle:FichaMontaje:ficha_pdf_base.html.twig', array('content' => $content));
            $base64 = $this->createTCPDF($html, "FichaDesmontaje");
            $ixpo_mailer = $this->get('ixpo_mailer');
            $to = Array($content['contacto']['Email']);
            #print_r($to);die(' <= to4');
            #$to = Array("erict@infoexpo.com.mx");
            $doc = Array(
                Array("doc" => base64_decode($base64), "type" => "application/pdf", "name" => "Ficha de Desmontaje.pdf")
            );
            $htmlbody = $this->renderView('EmpresaEmpresaFichaMontajeBundle:FichaMontaje:ficha_email.html.twig', array('content' => $content));
            $ixpo_mailer->setFiles($doc);
            $result_email = $ixpo_mailer->send_document("Ficha de Desmontaje", $to, "$htmlbody", Array(), $doc);
            $result = array('status' => false, 'band' => '');
            if ($result_email) {
                $result['status'] = true;
            }
        } else {
            $result['data'] = 'Metodo No Permitido';
        }
        return $this->jsonResponse($result);
    }

    private function createTCPDF($html, $nombre) {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '-1');
        $pdf = $this->get("white_october.tcpdf")->create(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetAuthor('Infoexpo');

        $pdf->SetKeywords('Responsiva');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 8, 10);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);

        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $font_size = $pdf->pixelsToUnits('8');
        $pdf->SetFont('helvetica', '', $font_size, '', 'default', true);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $file = $pdf->Output($nombre . '.pdf', 'S');
        $pdf_encode = base64_encode($file);
        return $pdf_encode;
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
