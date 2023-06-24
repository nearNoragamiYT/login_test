<?php

namespace Wizard\ContactoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use Utilerias\TextoBundle\Model\TextoModel;
use Wizard\ContactoBundle\Model\ContactoModel;
use Wizard\ComiteOrganizadorBundle\Model\ComiteOrganizadorModel;

class ContactoController extends Controller {

    protected $ConfigurationModel, $App, $TextoModel, $ContactoModel, $ComiteOrganizadorModel;

    const SECTION = 2;

    public function __construct() {
        $this->ConfigurationModel = new ConfigurationModel();
        $this->App = $this->ConfigurationModel->getApp();
        $this->TextoModel = new TextoModel();
        $this->ContactoModel = new ContactoModel();
        $this->ComiteOrganizadorModel = new ComiteOrganizadorModel();
    }

    public function contactoAction(Request $request) {
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
            $this->ContactoModel->trimValues($post);

            /* Verificamos que no exista el mismo Nombre de Contacto */
            $args = array('lower("Nombre")' => "'" . mb_strtolower($post['Nombre'], 'UTF-8') . "'");
            if ($this->ContactoModel->is_defined($post['idContactoComiteOrganizador'])) {
                $args['idContactoComiteOrganizador'] = array("operator" => "<>", "value" => $post['idContactoComiteOrganizador']);
            }
            $result_c = $this->ContactoModel->getContacto($args);
            if (!$result_c['status']) {
                $session->getFlashBag()->add('danger', $result_c['data']);
                return $this->redirectToRoute('wizard_contacto');
            }

            if (count($result_c['data']) > 0) {
                $session->getFlashBag()->add('warning', $section_text['sas_ContactoExistente']);
                return $this->redirectToRoute('wizard_contacto');
            }

            $data = array();
            $data['idContactoComiteOrganizador'] = $post['idContactoComiteOrganizador'];
            unset($post['idContactoComiteOrganizador']);
            $data['idComiteOrganizador'] = $post['idComiteOrganizador'];
            unset($post['idComiteOrganizador']);
            $post = array_merge($data, $this->ContactoModel->formatQuoteValue($post));

            $result = $this->ContactoModel->insertEditContacto($post);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('wizard_contacto');
            }

            if (count($result['data']) == 0) {
                $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
                return $this->redirectToRoute('wizard_contacto');
            }

            /* Insertamos o actualizamos los status de la configuracion inicial */
            $values = array(
                'idComiteOrganizador' => $post['idComiteOrganizador'],
                'Contacto' => "true"
            );
            $result_config = $this->ContactoModel->insertEditConfiguracionInicial($values);
            if (!$result_config['status']) {
                $session->getFlashBag()->add('danger', $result_config['data']);
                return $this->redirectToRoute('wizard_contacto');
            }

            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            return $this->redirectToRoute('wizard_contacto');
        }

        /* Obtenemos la Configuracion Inicial */
        $result_conf = $this->ContactoModel->getConfiguracionInicial();
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

        $result_contactos = $this->ContactoModel->getContactosCO($configuration['idComiteOrganizador']);
        if (!$result_contactos['status']) {
            throw new \Exception($result_contactos['data'], 409);
        }

        $contactos = array();
        if (count($result_contactos['data']) > 0) {
            foreach ($result_contactos['data'] as $key => $value) {
                $contactos[$value['idContactoComiteOrganizador']] = $value;
            }
        }

        $content = array();
        $content['configuration'] = $configuration;
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['user'] = $user;
        $content['contactos'] = $contactos;
        $content['current_step'] = "contacto";
        return $this->render('WizardContactoBundle:Section:contacto.html.twig', array('content' => $content));
    }

    public function contactoEliminarAction(Request $request) {
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
        if ($post['idContactoComiteOrganizador'] == "") {
            $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
            return $this->redirectToRoute('wizard_contacto');
        }

        $args = array('idContactoComiteOrganizador' => $post['idContactoComiteOrganizador']);
        $result = $this->ContactoModel->deleteContacto($args);
        if (!$result['status']) {
            $session->getFlashBag()->add('danger', $result['data']);
            return $this->redirectToRoute('wizard_contacto');
        }

        $session->getFlashBag()->add('success', $general_text['sas_eliminoExito']);
        return $this->redirectToRoute('wizard_contacto');
    }

    public function contactoOmitirAction(Request $request) {
        $session = $request->getSession();
        /* Obtenemos la Configuracion Inicial */
        $result_conf = $this->ContactoModel->getConfiguracionInicial();
        if (!$result_conf['status']) {
            throw new \Exception($result_conf['data'], 409);
        }

        /* Si no tiene CO, debe ingresar previamente la informacion */
        if (count($result_conf['data']) == 0) {
            return $this->redirectToRoute('wizard_comite_organizador');
        }

        $configuration = $result_conf['data'][0];

        /* Insertamos o actualizamos los status de la configuracion inicial */
        $values = array(
            'idComiteOrganizador' => $configuration['idComiteOrganizador'],
            'Contacto' => "true"
        );

        $result_config = $this->ContactoModel->insertEditConfiguracionInicial($values);
        if (!$result_config['status']) {
            $session->getFlashBag()->add('danger', $result_config['data']);
            return $this->redirectToRoute('wizard_contacto');
        }
        return $this->redirectToRoute('wizard_evento');
    }

}
