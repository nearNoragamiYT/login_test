<?php

namespace Empresa\EmpresaSolicitudModificacionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use Empresa\EmpresaSolicitudModificacionBundle\Model\SolicitudModificacionModel;

class SolicitudModificacionController extends Controller {

    protected $TextoModel, $SolicitudModificacionModel;

    const SECTION = 4;
    const MAIN_ROUTE = "solicitudes-modificacion";

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->SolicitudModificacionModel = new SolicitudModificacionModel();
    }

    public function modificationRequestAction(Request $request, $idEmpresa) {
        $form = 220;
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $content = array();
        $content["lang"] = $lang;

        $content['tabPermission'] = json_decode($this->SolicitudModificacionModel->tabsPermission($user), true);
        $content['currentRoute'] = $request->get('_route');
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

        $content['idForma'] = $form;
        $content['lang'] = $lang;

        //Obtiene la empresa
        $content['idEmpresa'] = $idEmpresa;

        //Obtiene el idUsuario del usuario
        $content['idUsuario'] = $user["idUsuario"];

        //Obtenemos el token de la empresa
        $args = Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idEmpresa' => $idEmpresa);
        $token = $this->SolicitudModificacionModel->getEmpresaToken($args);
        $content["token"] = $token["Token"];

        $args = Array('e."idEmpresa"' => $idEmpresa, 'ee."idEdicion"' => $idEdicion);
        $content["header"] = $this->SolicitudModificacionModel->getCompanyHeader($args);

        $args = Array('p."idEdicion"' => $idEdicion);
        //$content["packages"] = $this->SolicitudModificacionModel->getPackages($args);

        $args = Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion);
        $content["categories"] = $this->SolicitudModificacionModel->getCategorias($args);

        $args = Array('idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
        $campos_forma = $this->SolicitudModificacionModel->getCamposForma($args);
        $campos_forma = $campos_forma[0];
        $content['camposforma'] = json_decode($campos_forma['CamposJSON'], true);

        if ($session->get("companyOrigin") == "ventas")
            $content["breadcrumb"] = $this->SolicitudModificacionModel->breadcrumb("empresa_ventas", $lang);
        if ($session->get("companyOrigin") == "expositores")
            $content["breadcrumb"] = $this->SolicitudModificacionModel->breadcrumb("empresa", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => $content["header"]["DC_NombreComercial"], "route" => ""));


        /* ---  Obtenemos EmpresaForma  --- */
        $args = array();
        $args = Array('idEmpresa' => $idEmpresa, 'idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
        $result_empresa_forma = $this->SolicitudModificacionModel->getEmpresaForma($args);
        if (!$result_empresa_forma['status']) {
            throw new \Exception($result_empresa_forma['data'], 409);
        }
        //Obtenemos el campo JSON DetalleForma
        $empresa_forma = $result_empresa_forma[$form];

        $content['detalleForma'] = json_decode($empresa_forma['DetalleForma'], true);

        /* //Agregar un caracter a cualquiera a los camposque vienen vacíos dentro del JSON detalleForma
          foreach ($content['detalleForma'] as $key => $value){
          if($value['CategoriaPrincipal'] == '')
          $content['detalleForma'][$key]['CategoriaPrincipal']= 'x';
          if($value['CategoriaSecundaria'] == '')
          $content['detalleForma'][$key]['CategoriaSecundaria']= 'x';
          if($value['OtraCategoria'] == '')
          {
          $content['detalleForma'][$key]['OtraCategoria']='No especificado';
          //$categorias2['x'] = "No especificado";
          }
          }
         */

        /* ---  Modification Request Metadata (para construir la tabla) --- */
        $modif_request_metadata = $this->SolicitudModificacionModel->getModificationRequestMetaData($content['section_text'], $content["categories"], $content['camposforma']);
        $content["modif_request_metadata"] = $modif_request_metadata;

        return $this->render('EmpresaEmpresaSolicitudModificacionBundle:SolicitudModificacion:request_list.html.twig', array('content' => $content));
    }

    public function updateModificationRequestAction(Request $request) {

        $form = 220;
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $content = array();
        $content["lang"] = $lang;

        $post = $request->request->all();
        $idEmpresa = $post['idEmpresa'];
        $args = Array('idEmpresa' => $idEmpresa, 'idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);

        /* ---  Obtenemos EmpresaForma  --- */
        $result_empresa_forma = $this->SolicitudModificacionModel->getEmpresaForma($args);
        if (!$result_empresa_forma['status']) {
            throw new \Exception($result_empresa_forma['data'], 409);
        }

        //Obtenemos el campo JSON DetalleForma
        $empresa_forma = $result_empresa_forma[$form];
        $idSolicitud = json_decode($empresa_forma['DetalleForma'], true);

        //Modificamos el JSON con los elementos modificados del POST
        $idSolicitud [$post['idSolicitudCambio']]['StatusSolicitudCambio'] = $post['StatusSolicitudCambio'];
        $idSolicitud [$post['idSolicitudCambio']]['ObservacionComite'] = $post['ObservacionComite'];

        //Creamos un nuevo JSON solo con el idSolicitudCambio modificado
        $idSolicitud2 = $idSolicitud[$post['idSolicitudCambio']];

        $idSolicitud = json_encode($idSolicitud);
        $idSolicitud2 = json_encode($idSolicitud2);

        //Actualizamos la tabla EmpresaForma
        $result = $this->SolicitudModificacionModel->updateEmpresaForma($args, $idSolicitud);

        if ($result['status']) {
            $data = json_decode($idSolicitud2, true);
            $result = Array("status" => true, "data" => $data);
        } else {
            $result = Array("status" => false, "error" => $resultUpdate['data']);
        }
        return $this->jsonResponse($result);
    }

    public function deleteModificationRequestAction(Request $request) {
        $form = 220;
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();

        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $content = array();
        $content["lang"] = $lang;

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $idEmpresa = $post['idEmpresa'];
            $args = Array('idEmpresa' => $idEmpresa, 'idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);

            /* ---  Obtenemos EmpresaForma  --- */
            $result_empresa_forma = $this->SolicitudModificacionModel->getEmpresaForma($args);
            if (!$result_empresa_forma['status']) {
                throw new \Exception($result_empresa_forma['data'], 409);
            }
            //Obtenemos el campo JSON DetalleForma
            $empresa_forma = $result_empresa_forma[$form];
            $idSolicitud = json_decode($empresa_forma['DetalleForma'], true);

            //Eliminamos del Array, el subarray correspondiente al idSolicitudCambio
            unset($idSolicitud [$post['idSolicitudCambio']]);
            $idSolicitud = json_encode($idSolicitud);

            //Actualizamos la tabla EmpresaForma
            $result = $this->SolicitudModificacionModel->updateEmpresaForma($args, $idSolicitud);
            if ($result['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            } else {
                $result['error'] = $content['general_text']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /* --Action para el Modulo General-- */

    public function generalRequestsAction(Request $request) {
        $form = 220;
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $content = array();
        $content["lang"] = $lang;
        $content["idUsuario"] = $user['idUsuario'];

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


        //Obtenemos las categorias
        $args = Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion);
        $content["categories"] = $this->SolicitudModificacionModel->getCategorias($args);


        //Obtenemos un arreglo con los campos de las formas
        $args = Array('idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
        $campos_forma = $this->SolicitudModificacionModel->getCamposForma($args);
        $campos_forma = $campos_forma[0];
        $content['camposforma'] = json_decode($campos_forma['CamposJSON'], true);

        $content['breadcrumb'] = $this->SolicitudModificacionModel->breadcrumb(self::MAIN_ROUTE, $lang);

        //Obtenemos un arreglo con los idEmpresaForma
        $id_empresa_forma = $this->SolicitudModificacionModel->getRegistrosEmpresas($args);
        $content['empresas'] = $id_empresa_forma;

        /* ---  Obtenemos DetalleForma  --- */
        $result_detalle_forma = $this->SolicitudModificacionModel->getDetalleForma($args);

        //Obtenemos un arreglo asociativo con los keys de Empresa y su campo JSON DetalleForma
        $result_detalle_forma2 = Array();
        foreach ($result_detalle_forma as $key => $value) {
            $result_detalle_forma2[$value['idEmpresa']] = json_decode($value["DetalleForma"], true);
        }
        $content['detalleForma'] = $result_detalle_forma2;

//       /* //Agregar un caracter a cualquiera a los camposque vienen vacíos dentro del JSON detalleForma
//        foreach ($content['detalleForma'] as $key => $value){
//            if($value['CategoriaPrincipal'] == '')
//                $content['detalleForma'][$key]['CategoriaPrincipal']= 'x';
//            if($value['CategoriaSecundaria'] == '')
//                $content['detalleForma'][$key]['CategoriaSecundaria']= 'x';
//            if($value['OtraCategoria'] == '')
//            {
//                $content['detalleForma'][$key]['OtraCategoria']='No especificado';
//                //$categorias2['x'] = "No especificado";
//            }
//        }
//        */
//
        /* ---  Modification Request Metadata (para construir la tabla) --- */
        $modif_request_metadata = $this->SolicitudModificacionModel->getModificationRequestMetaData($content['section_text'], $content["categories"], $content['camposforma'], $content['empresas']);
        $content["modif_request_metadata"] = $modif_request_metadata;
        return $this->render('EmpresaEmpresaSolicitudModificacionBundle:SolicitudModificacion:general_request_list.html.twig', array('content' => $content));
    }

}
