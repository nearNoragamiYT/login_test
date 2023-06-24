<?php

namespace Wizard\UsuarioBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use Utilerias\TextoBundle\Model\TextoModel;
use Wizard\UsuarioBundle\Model\UsuarioModel;
use Wizard\EdicionBundle\Model\EdicionModel;

class UsuarioController extends Controller {

    protected $ConfigurationModel, $App, $TextoModel, $UsuarioModel, $EdicionModel;

    const SECTION = 2;

    public function __construct() {
        $this->ConfigurationModel = new ConfigurationModel();
        $this->App = $this->ConfigurationModel->getApp();
        $this->TextoModel = new TextoModel();
        $this->UsuarioModel = new UsuarioModel();
        $this->EdicionModel = new EdicionModel();
    }

    public function usuarioAction(Request $request) {
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

        /* Obtención de textos de la seccion de la administracion general */
        $result_admon_text = $this->TextoModel->getTexts($lang, 3);
        if (!$result_admon_text['status']) {
            throw new \Exception($result_admon_text['data'], 409);
        }
        $admon_text = $result_admon_text['data'];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $this->UsuarioModel->trimValues($post);
            $password = $post['Password'];
            if ($password != "____") {
                $post['Password'] = sha1($password . $this->App['salt']);
            }
            $post['idPlantillaAcceso'] = "0"; // Temporal

            /* Verificamos que no exista el mismo Evento */
            $args = array(
                'idComiteOrganizador' => $post['idComiteOrganizador'],
                'lower("Email")' => "'" . strtolower($post['Email']) . "'",
            );
            if ($this->UsuarioModel->is_defined($post['idUsuario'])) {
                $args['idUsuario'] = array("operator" => "<>", "value" => $post['idUsuario']);
            }
            $result_u = $this->UsuarioModel->getUsuario($args);

            if (!$result_u['status']) {
                $session->getFlashBag()->add('danger', $result_u['data']);
                return $this->redirectToRoute('wizard_usuario');
            }

            if (count($result_u['data']) > 0) {
                $session->getFlashBag()->add('warning', $section_text['sas_UsuarioExistente']);
                return $this->redirectToRoute('wizard_usuario');
            }

            $data = array();
            $data['idUsuario'] = $post['idUsuario'];
            unset($post['idUsuario']);
            $data['idPlantillaAcceso'] = $post['idPlantillaAcceso'];
            unset($post['idPlantillaAcceso']);
            $data['idContactoComiteOrganizador'] = $post['idContactoComiteOrganizador'];
            unset($post['idContactoComiteOrganizador']);
            $data['idComiteOrganizador'] = $post['idComiteOrganizador'];
            unset($post['idComiteOrganizador']);
            $post = array_merge($data, $this->UsuarioModel->formatQuoteValue($post));

            $result = $this->UsuarioModel->insertEditUsuario($post);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('wizard_usuario');
            }

            if (count($result['data']) == 0) {
                $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
                return $this->redirectToRoute('wizard_usuario');
            }
            /* Insertamos o actualizamos los status de la configuracion inicial */
            $values = array(
                'idComiteOrganizador' => $post['idComiteOrganizador'],
                'Usuario' => "true"
            );
            $result_config = $this->UsuarioModel->insertEditConfiguracionInicial($values);
            if (!$result_config['status']) {
                $session->getFlashBag()->add('danger', $result_config['data']);
                return $this->redirectToRoute('wizard_usuario');
            }

            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            return $this->redirectToRoute('wizard_usuario');
        }

        /* Obtenemos la Configuracion Inicial */
        $result_conf = $this->UsuarioModel->getConfiguracionInicial();
        if (!$result_conf['status']) {
            throw new \Exception($result_conf['data'], 409);
        }

        /* Si no tiene CO, debe ingresar previamente la informacion */
        if (count($result_conf['data']) == 0) {
            $session->getFlashBag()->add('info', $section_text['sas_sinCO']);
            return $this->redirectToRoute('wizard_comite_organizador');
        }

        /* Si tiene mas de una configuracion inicial adjuntamos mensaje */
        if (count($result_conf['data']) > 1) {
            $session->getFlashBag()->add('info', $section_text['sas_multipleConfig']);
        }

        $configuration = $result_conf['data'][0];

        $result_usuarios = $this->get("login.model")->getUsuario();
        if (!$result_usuarios['status']) {
            throw new \Exception($result_usuarios['data'], 409);
        }

        $usuarios = $result_usuarios['data'];

        $result_ediciones = $this->EdicionModel->getEdicion();
        if (!$result_ediciones['status']) {
            throw new \Exception($result_ediciones['data'], 409);
        }

        $ediciones = array();
        if (count($result_ediciones['data']) > 0) {
            foreach ($result_ediciones['data'] as $key => $value) {
                $ediciones[$value['idEdicion']] = $value;
            }
        }

        $content = array();
        $content['configuration'] = $configuration;
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['admon_text'] = $admon_text;
        $content['user'] = $user;
        $content['usuarios'] = $usuarios;
        $content['ediciones'] = $ediciones;
        $content['current_step'] = "usuario";
        return $this->render('WizardUsuarioBundle:Section:usuario.html.twig', array('content' => $content));
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
            return $this->redirectToRoute('wizard_usuario');
        }

        $args = array('idUsuario' => $post['idUsuario']);
        $result = $this->UsuarioModel->deleteUsuario($args);
        if (!$result['status']) {
            $session->getFlashBag()->add('danger', $result['data']);
            return $this->redirectToRoute('wizard_usuario');
        }

        $session->getFlashBag()->add('success', $general_text['sas_eliminoExito']);
        return $this->redirectToRoute('wizard_usuario');
    }

}
