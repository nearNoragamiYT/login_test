<?php

namespace ShowDashboard\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use ShowDashboard\DashboardBundle\Model\DashboardModel;
use Utilerias\TextoBundle\Model\TextoModel;

class DashboardController extends Controller
{

    protected $DashboardModel, $TextoModel;

    const SECTION = 4, MAIN_ROUTE = 'show_dashboard_dashboard';

    public function __construct()
    {
        $this->DashboardModel = new DashboardModel();
        $this->TextoModel = new TextoModel();
    }

    public function mainDashboardAction(Request $request, $lang)
    {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();

        if ($lang == "") {
            $lang = 'es';
        }
        $session->set('lang', $lang);
        $session->remove('idEvento');
        $session->remove('idEdicion');
        $session->remove('edicion');
        $session->remove('idPlataformaIxpo');

        /* Validamos al Usuario en sesion */
        switch ($user['idTipoUsuario']) {
            case "1":
                if (!(isset($user['ComiteOrganizador']) && count($user['ComiteOrganizador']) > 0)) {
                    return $this->redirectToRoute('wizard');
                }

                if ($user['Ediciones'] == "") {
                    return $this->redirectToRoute('wizard');
                }

                break;
            case "2":
                if (!(isset($user['ComiteOrganizador']) && count($user['ComiteOrganizador']) > 0)) {
                    return $this->redirectToRoute('wizard');
                }
                break;
            case "3":
            default:
        }

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
        $args = array('"idUsuario"' => $user['idUsuario']);
        $result_edicion = $this->DashboardModel->getEventoEdicionUsuario($args);
        if (!$result_edicion['status']) {
            throw new \Exception($result_edicion['data'], 409);
        }
        $ediciones = $result_edicion['data'];

        $content = array();
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['user'] = $user;
        $content['ediciones'] = $ediciones;
        return $this->render('ShowDashboardDashboardBundle:Dashboard:showMainDashboard.html.twig', array('content' => $content));
    }

    public function dashboardEdicionAction(Request $request, $idEdicion, $lang)
    {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();

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
        $args = array(
            'ue."idEdicion"' => $idEdicion,
            'ue."idUsuario"' => $user['idUsuario'],
        );
        $result_edicion = $this->DashboardModel->getEventoEdicionUsuario($args);
        if (!$result_edicion['status']) {
            throw new \Exception($result_edicion['data'], 409);
        }

        if (count($result_edicion['data']) == 0) {
            $session->getFlashBag()->add('warning', $general_text['sas_edicionNoPermitida']);
            return $this->redirectToRoute('dashboard', array('lang' => $lang));
        }

        $edicion = $result_edicion['data'][0];
        $session->set('lang', $lang);
        $session->set('idEdicion', $idEdicion);
        $session->set('idEvento', $edicion['idEvento']);
        $session->set('edicion', $edicion);
        $session->remove('idPlataformaIxpo');

        $content = array();
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['user'] = $user;
        return $this->render('ShowDashboardDashboardBundle:Dashboard:showEdicionDashboard.html.twig', array('content' => $content));
    }
}
