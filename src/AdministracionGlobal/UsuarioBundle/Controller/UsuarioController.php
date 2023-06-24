<?php

namespace AdministracionGlobal\UsuarioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use AdministracionGlobal\UsuarioBundle\Model\UsuarioModel;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;

class UsuarioController extends Controller {

    protected $UsuarioModel, $TextoModel, $App;

    const SECTION = 3, MAIN_ROUTE = 'usuario';

    public function __construct() {
        $this->UsuarioModel = new UsuarioModel();
        $this->TextoModel = new TextoModel();
        $ConfigurationModel = new ConfigurationModel();
        $this->App = $ConfigurationModel->getApp();
    }

    public function usuarioAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');

        /* Obtención de textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }

        /* Obtención de textos de la sección */
        $result_section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_section_text['status']) {
            throw new \Exception($result_section_text['data'], 409);
        }
        $section_text = $result_section_text['data'];

        $result_edicion = $this->UsuarioModel->getEdicion();
        if (!$result_edicion['status']) {
            throw new \Exception($result_edicion['data'], 409);
        }
        $edicion = $result_edicion['data'];

        $result_usuarios = $this->UsuarioModel->getUsuario();
        if (!$result_usuarios['status']) {
            throw new \Exception($result_usuarios['data'], 409);
        }

        $usuarios = NULL;
        if (count($result_usuarios['data'])) {
            foreach ($result_usuarios['data'] as $usuario) {
                $usuarios[$usuario['idUsuario']] = $usuario;
            }
        }

        $content = array();
        $content['breadcrumb'] = $breadcrumb;
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['edicion'] = $edicion;
        $content['usuarios'] = $usuarios;
        return $this->render('AdministracionGlobalUsuarioBundle:Usuario:listaUsuario.html.twig', array('content' => $content));
    }

    public function usuarioInformacionAction(Request $request, $idUsuario) {
        $session = $request->getSession();
        $lang = $session->get('lang');

        /* Obtención de textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "usuario");
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }

        /* Obtención de textos de la sección */
        $result_section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_section_text['status']) {
            throw new \Exception($result_section_text['data'], 409);
        }
        $section_text = $result_section_text['data'];

        $form_action = $this->generateUrl("usuario_agregar");
        if ($idUsuario != "") {
            $form_action = $this->generateUrl("usuario_editar", array("idUsuario" => $idUsuario));
        }

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $data = array();
            $data['Nombre'] = $post['Nombre'];
            $data['Puesto'] = $post['Puesto'];
            $data['Email'] = $post['Email'];
            if ($post['Password'] != "") {
                $data['Password'] = sha1($post['Password'] . $this->App['salt']);
            }
            $data['idComiteOrganizador'] = $post['idComiteOrganizador'];
            $data['idTipoUsuario'] = $post['idTipoUsuario'];
            $stringData = $this->UsuarioModel->createString($data);
            $permisos = $this->UsuarioModel->formatearModuloPermisos($post['idEdicion'], $post['idModuloIxpo']);
            $result = $this->UsuarioModel->insertEditUsuario($post['idUsuario'], $stringData, $permisos);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirect($form_action);
            }

            /* if ($result['data'][0]['idUsuario'] == "") {
              $session->getFlashBag()->add('danger', $general_text['sas_errorPeticion']);
              return $this->redirect($form_action);
              } */

            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            return $this->redirectToRoute("usuario");
        }

        $result_comiteOrganizador = $this->UsuarioModel->getComiteOrganizador();
        if (!$result_comiteOrganizador['status']) {
            throw new \Exception($result_comiteOrganizador['data'], 409);
        }
        $comiteOrganizador = $result_comiteOrganizador['data'];

        $result_ediciones = $this->UsuarioModel->getEdicion();
        if (!$result_ediciones['status']) {
            throw new \Exception($result_ediciones['data'], 409);
        }
        $ediciones = $result_ediciones['data'];

        $result_tipoUsuario = $this->UsuarioModel->getTipoUsuario();
        if (!$result_tipoUsuario['status']) {
            throw new \Exception($result_tipoUsuario['data'], 409);
        }
        $tipoUsuario = $result_tipoUsuario['data'];

        $result_modulos = $this->UsuarioModel->getModulos();
        if (!$result_modulos['status']) {
            throw new \Exception($result_modulos['data'], 409);
        }
        $modulos = $result_modulos['data'];
        $plataformas = $result_modulos['plataformas'];

        $usuario = NULL;
        if ($idUsuario != "") {
            $args = array('idUsuario' => $idUsuario);
            $result_usuarios = $this->UsuarioModel->getUsuario($args);
            if (!$result_usuarios['status']) {
                throw new \Exception($result_usuarios['data'], 409);
            }
            $usuario = array_shift($result_usuarios['data']);
        }
        $content = array();
        $content['breadcrumb'] = $breadcrumb;
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['comiteOrganizador'] = $comiteOrganizador;
        $content['ediciones'] = $ediciones;
        $content['tipoUsuario'] = $tipoUsuario;
        $content['modulos'] = $modulos;
        $content['plataformas'] = $plataformas;
        $content['usuario'] = $usuario;
        $content['form_action'] = $form_action;
        array_push($content["breadcrumb"], Array("Modulo_" . strtoupper($lang) => $content['usuario']['Nombre'], "Ruta" => "", 'Permisos' => array()));
        return $this->render('AdministracionGlobalUsuarioBundle:Usuario:informacionUsuario.html.twig', array('content' => $content));
    }

    public function usuarioEliminarAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Obtenemos textos de la sección del Asistente 2 */
        $result_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        $post = $request->request->all();

        if ($post['idUsuario'] == "") {
            $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
            return $this->redirectToRoute('usuario');
        }

        $args = array('idUsuario' => $post['idUsuario']);
        $result = $this->UsuarioModel->desactivarUsuario($args);
        if (!$result['status']) {
            $session->getFlashBag()->add('danger', $result['data']);
            return $this->redirectToRoute('usuario');
        }

        $session->getFlashBag()->add('success', $section_text['sas_desactivadoExito']);
        return $this->redirectToRoute('usuario');
    }

    public function usuarioReactivarAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Obtenemos textos de la sección */
        $result_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        $post = $request->request->all();

        if ($post['idUsuario'] == "") {
            $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
            return $this->redirectToRoute('usuario');
        }

        $args = array('idUsuario' => $post['idUsuario']);
        $result = $this->UsuarioModel->reactivarUsuario($args);
        if (!$result['status']) {
            $session->getFlashBag()->add('danger', $result['data']);
            return $this->redirectToRoute('usuario');
        }

        $session->getFlashBag()->add('success', $section_text['sas_reactivadoExito']);
        return $this->redirectToRoute('usuario_editar', array('idUsuario' => $post['idUsuario']));
    }

}
