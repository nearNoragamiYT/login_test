<?php

namespace ShowDashboard\AE\Administracion\DatosGeneralesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\AE\Administracion\DatosGeneralesBundle\Model\DatosGeneralesModel;

class DatosGeneralesController extends Controller {

    protected $DatosGeneralesModel, $TextoModel;

    const PLATFORM = 5, SECTION = 3, MAIN_ROUTE = 'show_dashboard_ae_administracion_datos_generales';

    public function __construct() {
        $this->DatosGeneralesModel = new DatosGeneralesModel();
        $this->TextoModel = new TextoModel();
    }

    public function datosGeneralesAction(Request $request) {
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

        /* Obtenemos textos de la sección del ShowDashboard AE 5 */
        $result_text = $this->TextoModel->getTexts($lang, self::PLATFORM);
        if (!$result_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $section_text = $result_text['data'];

        /* Obtenemos textos generales del AE 3 seccion 0 */
        $args = array(
            'Seccion' => "0",
            'idPlataformaIxpo' => self::PLATFORM,
        );

        $result_ae_general_text = $this->DatosGeneralesModel->getTextos($args);
        if (!$result_ae_general_text['status']) {
            throw new \Exception($result_ae_general_text['data'], 409);
        }
        $ae_general_text = $result_ae_general_text['data'];
        
        /* Obtenemos textos de la sección Datos Generales del AE 3 */
        $args = array(
            'idPlataformaIxpo' => self::PLATFORM,
            'Seccion' => self::SECTION,
            'idEdicion' => $edicion['idEdicion']
        );

        $result_ae_text = $this->DatosGeneralesModel->getTextos($args);
        if (!$result_ae_text['status']) {
            throw new \Exception($result_text['data'], 409);
        }
        $ae_text = $result_ae_text['data'];

        $content = array();
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['ae_text'] = $ae_text;
        $content['ae_general_text'] = $ae_general_text;
        $content['idSeccion'] = self::SECTION;
        $content['user'] = $user;
        $content['edicion'] = $edicion;
        $content['breadcrumb'] = $this->DatosGeneralesModel->breadcrumb(self::MAIN_ROUTE, $lang);
        return $this->render('ShowDashboardAEAdministracionDatosGeneralesBundle:DatosGenerales:showDatosGenerales.html.twig', array('content' => $content));
    }

}
