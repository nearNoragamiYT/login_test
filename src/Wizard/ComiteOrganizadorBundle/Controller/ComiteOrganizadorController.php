<?php

namespace Wizard\ComiteOrganizadorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use Utilerias\TextoBundle\Model\TextoModel;
use Wizard\ComiteOrganizadorBundle\Model\ComiteOrganizadorModel;

class ComiteOrganizadorController extends Controller {

    protected $ConfigurationModel, $App, $TextoModel, $ComiteOrganizadorModel;

    const SECTION = 2;

    public function __construct() {
        $this->ConfigurationModel = new ConfigurationModel();
        $this->App = $this->ConfigurationModel->getApp();
        $this->TextoModel = new TextoModel();
        $this->ComiteOrganizadorModel = new ComiteOrganizadorModel();
    }

    public function comiteOrganizadorAction(Request $request) {
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

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $this->ComiteOrganizadorModel->trimValues($post);

            /* Verificamos que no exista el nombre del comite organizador */
            $args = array('lower("ComiteOrganizador")' => "'" . mb_strtolower($post['ComiteOrganizador'], 'UTF-8') . "'");
            if ($this->ComiteOrganizadorModel->is_defined($post['idComiteOrganizador'])) {
                $args['idComiteOrganizador'] = array("operator" => "<>", "value" => $post['idComiteOrganizador']);
            }

            $result_co = $this->ComiteOrganizadorModel->getComiteOrganizador($args);
            if (!$result_co['status']) {
                $session->getFlashBag()->add('danger', $result_co['data']);
                return $this->redirectToRoute('wizard_comite_organizador');
            }

            if (count($result_co['data']) > 0) {
                $session->getFlashBag()->add('warning', $section_text['sas_COExistente']);
                return $this->redirectToRoute('wizard_comite_organizador');
            }

            $result_files = $this->ComiteOrganizadorModel->uploadFiles($_FILES, $general_text);
            if (!$result_files['status']) {
                $session->getFlashBag()->add('warning', $result_files['data']);
                return $this->redirectToRoute('wizard_comite_organizador');
            }

            if (count($result_files['data']) > 0) {
                foreach ($result_files['data'] as $key => $value) {
                    $post[$value['field']] = "'" . $value['name'] . "'";
                }
            }

            $post['ComiteOrganizador'] = "'" . $post['ComiteOrganizador'] . "'";
            $result = $this->ComiteOrganizadorModel->insertEditComiteOrganizador($post);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('wizard_comite_organizador');
            }

            if (count($result['data']) == 0) {
                $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
                return $this->redirectToRoute('wizard_comite_organizador');
            }
            $idComiteOrganizador = $result['data'][0]['idComiteOrganizador'];
            /* Insertamos o actualizamos los status de la configuracion inicial */
            $values = array(
                'idComiteOrganizador' => $idComiteOrganizador,
                'ComiteOrganizador' => "true"
            );
            $result_config = $this->ComiteOrganizadorModel->insertEditConfiguracionInicial($values);
            if (!$result_config['status']) {
                $session->getFlashBag()->add('danger', $result_config['data']);
                return $this->redirectToRoute('wizard_comite_organizador');
            }

            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            if ($this->ComiteOrganizadorModel->is_defined($post['idComiteOrganizador'])) {
                return $this->redirectToRoute('wizard_comite_organizador');
            }
            return $this->redirectToRoute('wizard_entidad_fiscal');
        }
        $comiteOrganizador = array();
        /* Obtenemos la Configuracion Inicial */
        $result_conf = $this->ComiteOrganizadorModel->getConfiguracionInicial();
        if (!$result_conf['status']) {
            throw new \Exception($result_conf['data'], 409);
        }

        if (count($result_conf['data']) > 1) {
            $session->getFlashBag()->add('info', $section_text['sas_multipleConfig']);
        }

        if (count($result_conf['data']) > 0) {
            $configuration = $result_conf['data'][0];

            $result_co = $this->ComiteOrganizadorModel->getComiteOrganizador(array('idComiteOrganizador' => $configuration['idComiteOrganizador']));
            if (!$result_co['status']) {
                throw new \Exception($result_co['data'], 409);
            }

            if (count($result_co['data']) > 0) {
                $comiteOrganizador = $result_co['data'][0];
            }
        }

        $content = array();
        $content['configuration'] = $configuration;
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['user'] = $user;
        $content['comiteOrganizador'] = $comiteOrganizador;
        $content['current_step'] = "comiteOrganizador";
        return $this->render('WizardComiteOrganizadorBundle:Section:comiteOrganizador.html.twig', array('content' => $content));
    }

}
