<?php

namespace Wizard\EventoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use Utilerias\TextoBundle\Model\TextoModel;
use Wizard\EventoBundle\Model\EventoModel;
use Wizard\ComiteOrganizadorBundle\Model\ComiteOrganizadorModel;

class EventoController extends Controller {

    protected $ConfigurationModel, $App, $TextoModel, $EventoModel, $ComiteOrganizadorModel;

    const SECTION = 2;

    public function __construct() {
        $this->ConfigurationModel = new ConfigurationModel();
        $this->App = $this->ConfigurationModel->getApp();
        $this->TextoModel = new TextoModel();
        $this->EventoModel = new EventoModel();
        $this->ComiteOrganizadorModel = new ComiteOrganizadorModel();
    }

    public function eventoAction(Request $request) {
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
            $this->EventoModel->trimValues($post);

            /* Verificamos que no exista el mismo Evento */
            $args = array('lower("Evento_ES")' => "'" . mb_strtolower($post['Evento_ES'], 'UTF-8') . "'");
            if ($this->EventoModel->is_defined($post['idEvento'])) {
                $args['idEvento'] = array("operator" => "<>", "value" => $post['idEvento']);
            }
            $result = $this->EventoModel->getEvento($args);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('wizard_evento');
            }

            if (count($result['data']) > 0) {
                $session->getFlashBag()->add('warning', $section_text['sas_EventoExistente']);
                return $this->redirectToRoute('wizard_evento');
            }

            $data = array();
            $data['idEvento'] = $post['idEvento'];
            unset($post['idEvento']);
            $data['idComiteOrganizador'] = $post['idComiteOrganizador'];
            unset($post['idComiteOrganizador']);
            $post = array_merge($data, $this->EventoModel->formatQuoteValue($post));

            $result = $this->EventoModel->insertEditEvento($post);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('wizard_evento');
            }

            if (count($result['data']) == 0) {
                $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
                return $this->redirectToRoute('wizard_evento');
            }
            /* Insertamos o actualizamos los status de la configuracion inicial */
            $values = array(
                'idComiteOrganizador' => $post['idComiteOrganizador'],
                'Evento' => "true"
            );
            $result_config = $this->EventoModel->insertEditConfiguracionInicial($values);
            if (!$result_config['status']) {
                $session->getFlashBag()->add('danger', $result_config['data']);
                return $this->redirectToRoute('wizard_evento');
            }

            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            if ($this->EventoModel->is_defined($post['idEvento'])) {
                return $this->redirectToRoute('wizard_evento');
            }
            return $this->redirectToRoute('wizard_edicion');
        }

        /* Obtenemos la Configuracion Inicial */
        $result_conf = $this->EventoModel->getConfiguracionInicial();
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
        $result = $this->EventoModel->getEvento(array('idComiteOrganizador' => $configuration['idComiteOrganizador']));
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }

        $eventos = array();
        if (count($result['data']) > 0) {
            foreach ($result['data'] as $key => $value) {
                $eventos[$value['idEvento']] = $value;
            }
        }

        $content = array();
        $content['configuration'] = $configuration;
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['user'] = $user;
        $content['eventos'] = $eventos;
        $content['current_step'] = "evento";
        return $this->render('WizardEventoBundle:Section:evento.html.twig', array('content' => $content));
    }

    public function eventoEliminarAction(Request $request) {
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
        if ($post['idEvento'] == "") {
            $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
            return $this->redirectToRoute('wizard_evento');
        }

        $args = array('idEvento' => $post['idEvento']);
        $result = $this->EventoModel->deleteEvento($args);
        if (!$result['status']) {
            $session->getFlashBag()->add('danger', $result['data']);
            return $this->redirectToRoute('wizard_evento');
        }

        $session->getFlashBag()->add('success', $general_text['sas_eliminoExito']);
        return $this->redirectToRoute('wizard_evento');
    }

}
