<?php

namespace CuentaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use CuentaBundle\Model\CuentaModel;
use AdministracionGlobal\UsuarioBundle\Model\UsuarioModel;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use Utilerias\TextoBundle\Model\TextoModel;

class CuentaController extends Controller {

    protected $CuentaModel, $UsuarioModel, $TextoModel, $App;

    const SECTION = 3, MAIN_ROUTE = 'cuenta';

    public function __construct() {
        $ConfigurationModel = new ConfigurationModel();
        $this->App = $ConfigurationModel->getApp();
        $this->CuentaModel = new CuentaModel();
        $this->UsuarioModel = new UsuarioModel();
        $this->TextoModel = new TextoModel();
    }

    public function cuentaAction(Request $request) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Obtenemos textos de la sección del ShowDashboard 4 */
        $result_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        $result_comiteOrganizador = $this->UsuarioModel->getComiteOrganizador();
        if (!$result_comiteOrganizador['status']) {
            throw new \Exception($result_comiteOrganizador['data'], 409);
        }
        $comiteOrganizador = $result_comiteOrganizador['data'];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            /* Verificamos que no exista el mismo Nombre de Contacto */
            $args = array();
            $args['lower("Email")'] = "'" . strtolower(trim($post['Email'])) . "'";
            $args['"idUsuario"'] = array("operator" => "<>", "value" => $post['idUsuario']);
            $result_c = $this->UsuarioModel->getUsuario($args);
            if (!$result_c['status']) {
                $session->getFlashBag()->add('danger', $result_c['data']);
                return $this->redirectToRoute('cuenta');
            }

            if (count($result_c['data']) > 0) {
                $session->getFlashBag()->add('warning', $section_text['sas_UsuarioExistente']);
                return $this->redirectToRoute('cuenta');
            }

            $data = array();
            $data['idUsuario'] = $post['idUsuario'];
            $data['Nombre'] = "'" . $post['Nombre'] . "'";
            $data['Puesto'] = "'" . $post['Puesto'] . "'";
            $data['Email'] = "'" . $post['Email'] . "'";
            if ($post['Password'] != "") {
                $data['Password'] = "'" . sha1($post['Password'] . $this->App['salt']) . "'";
            }
            $data['idComiteOrganizador'] = $post['idComiteOrganizador'];
            $data['idTipoUsuario'] = $post['idTipoUsuario'];
            $result = $this->CuentaModel->editUsuario($data);

            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('cuenta');
            }

            if (count($result['data']) == 0) {
                $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
                return $this->redirectToRoute('cuenta');
            }

            $idUsuario = $result['data'][0]['idUsuario'];
            $result = $this->UsuarioModel->getUsuario(array('idUsuario' => $idUsuario));

            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('cuenta');
            }

            if (count($result['data']) == 0) {
                $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
                return $this->redirectToRoute('cuenta');
            }
            $usuario = $result['data'][0];
            $usuario['ComiteOrganizador'] = FALSE;

            if (isset($comiteOrganizador[$usuario['idComiteOrganizador']])) {
                $user['ComiteOrganizador'] = $comiteOrganizador[$usuario['idComiteOrganizador']];
            }
            $profile->setData($usuario);

            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            return $this->redirectToRoute('cuenta');
        }

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
        if ($user['idTipoUsuario'] != 1) {
            unset($tipoUsuario[1]);
        }

        $result_modulos = $this->UsuarioModel->getModulos();
        if (!$result_modulos['status']) {
            throw new \Exception($result_modulos['data'], 409);
        }
        $modulos = $result_modulos['data'];
        $plataformas = $result_modulos['plataformas'];

        $content = array();
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['usuario'] = $user;
        $content['comiteOrganizador'] = $comiteOrganizador;
        $content['ediciones'] = $ediciones;
        $content['tipoUsuario'] = $tipoUsuario;
        $content['modulos'] = $modulos;
        $content['plataformas'] = $plataformas;
        return $this->render('CuentaBundle:Cuenta:showCuenta.html.twig', array('content' => $content));
    }

}
