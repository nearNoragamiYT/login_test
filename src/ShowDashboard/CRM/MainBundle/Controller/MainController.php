<?php

namespace ShowDashboard\CRM\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;

class MainController extends Controller {

    private $model, $text;

    const session = 9, plataformaIxpo = 9;

    public function __construct() {
        //$this->model = new ();
        $this->text = new TextoModel();
    }

    public function indexAction(Request $request) {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');
        $edicion = $session->get('edicion');
        $modulosUsuario = $session->get('modulos_usuario');
        /* Obtenemos textos generales */
        $result_general_text = $this->text->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Obtenemos textos de la sección del AE 5 */
        $result_text = $this->text->getTexts($lang, self::session);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        /*if (!isset($modulosUsuario[self::plataformaIxpo])) {
            $session->getFlashBag()->add('warning', $general_text['sas_plataformaNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $edicion['idEdicion'], 'lang' => $lang));
        }*/
        $session->set('idPlataformaIxpo', self::plataformaIxpo);

        $content = array();
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['user'] = $user;
        
        return $this->redirectToRoute('show_dashboard_crm_empresas_asignadas_mostrar');
        return $this->render('ShowDashboardCRMMainBundle:Main:index.html.twig', Array("content" => $content));
    }
}