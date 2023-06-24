<?php

namespace Wizard\WizardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use Utilerias\TextoBundle\Model\TextoModel;
use Wizard\WizardBundle\Model\WizardModel;

class WizardController extends Controller {

    const SECTION = 2;

    protected $ConfigurationModel, $App, $WizardModel;

    public function __construct() {
        $this->ConfigurationModel = new ConfigurationModel();
        $this->App = $this->ConfigurationModel->getApp();
        $this->TextoModel = new TextoModel();
        $this->WizardModel = new WizardModel();
    }

    public function wizardAction(Request $request) {
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

        /* Obtenemos la Configuracion Inicial */
        $result_conf = $this->WizardModel->getConfiguracionInicial();
        if (!$result_conf['status']) {
            throw new \Exception($result_conf['data'], 409);
        }

        if (count($result_conf['data']) == 0) {
            return $this->redirectToRoute('wizard_informacion_general');
        }

        if (count($result_conf['data']) > 1) {
            $session->getFlashBag()->add('info', $section_text['sas_multipleConfig']);
        }

        $configuration = $result_conf['data'][0];
        /* Identificamos cual fue el ultimo paso completado */
        $route = $this->WizardModel->lastStepCompleted($configuration);
        return $this->redirectToRoute($route);
    }

}
