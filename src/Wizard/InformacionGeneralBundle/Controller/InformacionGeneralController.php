<?php

namespace Wizard\InformacionGeneralBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use Utilerias\TextoBundle\Model\TextoModel;
use Wizard\InformacionGeneralBundle\Model\InformacionGeneralModel;

class InformacionGeneralController extends Controller {

    protected $ConfigurationModel, $App, $TextoModel, $InformacionGeneralModel;

    const SECTION = 2;

    public function __construct() {
        $this->ConfigurationModel = new ConfigurationModel();
        $this->App = $this->ConfigurationModel->getApp();
        $this->TextoModel = new TextoModel();
        $this->InformacionGeneralModel = new InformacionGeneralModel();
    }

    public function informacionGeneralAction(Request $request) {
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
        $result_conf = $this->InformacionGeneralModel->getConfiguracionInicial();
        if (!$result_conf['status']) {
            throw new \Exception($result_conf['data'], 409);
        }
        if (count($result_conf['data']) > 0) {
            $configuration = $result_conf['data'][0];
        }
        
        $content = array();
        $content['configuration'] = $configuration;
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['user'] = $user;
        $content['current_step'] = "informacionGeneral";
        return $this->render('WizardInformacionGeneralBundle:Section:informacionGeneral.html.twig', array('content' => $content));
    }

}
