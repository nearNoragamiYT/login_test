<?php

namespace ShowDashboard\FT\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class MainController extends Controller
{

    protected $TextoModel, $DashboardModel, $idPlataformaIxpo = 10;

    public function __construct()
    {
        $this->TextoModel = new TextoModel();
        $this->DashboardModel = new DashboardModel();
    }

    const SECTION = 9;

    public function indexAction(Request $request)
    {
        $session = $request->getSession();
        $profile = $this->getUser();
        $user = $profile->getData();
        $lang = $session->get('lang');
        $edicion = $session->get('edicion');

        /* Obtenemos textos generales */
        $result_general_text = $this->TextoModel->getTexts($lang);
        if (!$result_general_text['status']) {
            throw new \Exception($result_general_text['data'], 409);
        }
        $general_text = $result_general_text['data'];

        /* Obtenemos textos de la sección del AE 5 */
        $result_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        /* Verificamos si tiene permiso sobre la plataforma en la edicion seleccionada */
        if (!$this->DashboardModel->verificarPermisoPlataforma($request, $this->idPlataformaIxpo)) {
            $session->remove('idPlataformaIxpo');
            $session->getFlashBag()->add('warning', $general_text['sas_plataformaNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $edicion['idEdicion'], 'lang' => $lang));
        }
        $session->set('idPlataformaIxpo', $this->idPlataformaIxpo);

        $modulos = $session->get('modulos_usuario')[$edicion['idEdicion']][$this->idPlataformaIxpo];
        foreach ($modulos as $modulo) {
            if ($modulo['Ruta'] != "") {
                return $this->redirectToRoute($modulo['Ruta']);
            }
        }
        return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $edicion['idEdicion'], 'lang' => $lang));
    }
}
