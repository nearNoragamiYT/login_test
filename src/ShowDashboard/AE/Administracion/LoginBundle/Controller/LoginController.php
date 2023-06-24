<?php

namespace ShowDashboard\AE\Administracion\LoginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\AE\Administracion\LoginBundle\Model\LoginModel;
use ShowDashboard\AE\AdministradorTextos\TemplateTextoBundle\Model\TemplateTextoModel;

class LoginController extends Controller {

    protected $LoginModel, $TextoModel, $TemplateTextoModel;

    const PLATFORM = 5, TEMPLATE = 8, MAIN_ROUTE = 'show_dashboard_ae_administracion_login';

    public function __construct() {
        $this->LoginModel = new LoginModel();
        $this->TextoModel = new TextoModel();
        $this->TemplateTextoModel = new TemplateTextoModel();
    }

    public function loginAction(Request $request, $lang) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        //$lang = $session->get('lang');
        $edicion = $session->get('edicion');

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Obtenemos textos de la sección del ShowDashboard AE 5 */
        $result_text = $this->TextoModel->getTexts($lang, self::PLATFORM);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        /* Obtenemos textos de la plantilla del Login del AE */
        $argsTemplate = array('idTemplate' => self::TEMPLATE);
        $result_templateTexto = $this->TemplateTextoModel->getTemplateTexto($argsTemplate, TRUE);
        if (!$result_templateTexto['status']) {
            throw new \Exception($result_templateTexto['data'], 409);
        }
        $templateTexto = $result_templateTexto['data'];
        $content = array();
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['template_text'] = $templateTexto;
        $content['idTemplate'] = self::TEMPLATE;
        $content['user'] = $user;
        $content['edicion'] = $edicion;
        $content['breadcrumb'] = $this->LoginModel->breadcrumb(self::MAIN_ROUTE, $lang);
        $content['lang'] = $lang;
        return $this->render('ShowDashboardAEAdministracionLoginBundle:Login:showLogin.html.twig', array('content' => $content));
    }

}
