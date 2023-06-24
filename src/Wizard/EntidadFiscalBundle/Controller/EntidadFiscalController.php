<?php

namespace Wizard\EntidadFiscalBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use Utilerias\TextoBundle\Model\TextoModel;
use Wizard\EntidadFiscalBundle\Model\EntidadFiscalModel;

class EntidadFiscalController extends Controller {

    protected $ConfigurationModel, $App, $TextoModel, $EntidadFiscalModel;

    const SECTION = 2;

    public function __construct() {
        $this->ConfigurationModel = new ConfigurationModel();
        $this->App = $this->ConfigurationModel->getApp();
        $this->TextoModel = new TextoModel();
        $this->EntidadFiscalModel = new EntidadFiscalModel();
    }

    public function entidadFiscalAction(Request $request) {
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

        /* Obtenemos los paises del PECC */
        $result_paises = $this->get('pecc')->getPaises($lang);
        if (!$result_paises['status']) {
            throw new \Exception($result_paises['data'], 409);
        }

        $paises = $result_paises['data'];
        $estados = array();

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $this->EntidadFiscalModel->trimValues($post);

            /* Verificamos que no exista el mismo RFC */
            $args = array(
                'idComiteOrganizador' => $post['idComiteOrganizador'],
                'lower("RFC")' => "'" . mb_strtolower($post['RFC'], 'UTF-8') . "'",
            );
            if ($this->EntidadFiscalModel->is_defined($post['idEntidadFiscal'])) {
                $args['idEntidadFiscal'] = array("operator" => "<>", "value" => $post['idEntidadFiscal']);
            }

            $result_ef = $this->EntidadFiscalModel->getEntidadFiscal($args);
            if (!$result_ef['status']) {
                $session->getFlashBag()->add('danger', $result_ef['data']);
                return $this->redirectToRoute('wizard_entidad_fiscal');
            }

            if (count($result_ef['data']) > 0) {
                $session->getFlashBag()->add('warning', $section_text['sas_RFCExistente']);
                return $this->redirectToRoute('wizard_entidad_fiscal');
            }
            /* --------------------------------------------------------- */

            $data = array();
            $data['idEntidadFiscal'] = $post['idEntidadFiscal'];
            unset($post['idEntidadFiscal']);
            $data['idComiteOrganizador'] = $post['idComiteOrganizador'];
            unset($post['idComiteOrganizador']);
            $data['idPais'] = $post['idPais'];
            unset($post['idPais']);
            if (isset($paises[$data['idPais']])) {
                $post['Pais'] = $paises[$data['idPais']]['Pais_' . strtoupper($lang)];
            }
            $data['idEstado'] = $post['idEstado'];
            unset($post['idEstado']);
            if (isset($paises[$data['idPais']])) {
                $post['Pais'] = $paises[$data['idPais']]['Pais_' . strtoupper($lang)];

                /* Obtenemos los paises del PECC */
                $result_estados = $this->get('pecc')->getEstados($data['idPais']);
                if (!$result_estados['status']) {
                    $response = new Response(json_encode($result_estados));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }
                $estados = $result_estados['data'];
                if (isset($estados[$data['idEstado']])) {
                    $post['Estado'] = $estados[$data['idEstado']]['Estado'];
                }
            }
            $post = array_merge($data, $this->EntidadFiscalModel->formatQuoteValue($post));
            $result = $this->EntidadFiscalModel->insertEditEntidadFiscal($post);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('wizard_entidad_fiscal');
            }

            if (count($result['data']) == 0) {
                $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
                return $this->redirectToRoute('wizard_entidad_fiscal');
            }

            /* Insertamos o actualizamos los status de la configuracion inicial */
            $values = array(
                'idComiteOrganizador' => $post['idComiteOrganizador'],
                'EntidadFiscal' => "true"
            );

            $result_config = $this->EntidadFiscalModel->insertEditConfiguracionInicial($values);
            if (!$result_config['status']) {
                $session->getFlashBag()->add('danger', $result_config['data']);
                return $this->redirectToRoute('wizard_entidad_fiscal');
            }

            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            if ($this->EntidadFiscalModel->is_defined($post['idEntidadFiscal'])) {
                return $this->redirectToRoute('wizard_entidad_fiscal');
            }
            return $this->redirectToRoute('wizard_contacto');
        }
        /* Obtenemos la Configuracion Inicial */
        $result_conf = $this->EntidadFiscalModel->getConfiguracionInicial();
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
        $result_ef = $this->EntidadFiscalModel->getEntidadFiscal(array('idComiteOrganizador' => $configuration['idComiteOrganizador']));
        if (!$result_ef['status']) {
            throw new \Exception($result_ef['data'], 409);
        }

        //$entidadFiscal = array();
        $entidadesFiscales = array();
        if (count($result_ef['data']) > 0) {
            foreach ($result_ef['data'] as $key => $value) {
                $entidadesFiscales[$value['idEntidadFiscal']] = $value;
            }
        }

        $content = array();
        $content['configuration'] = $configuration;
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['user'] = $user;
        //$content['entidadFiscal'] = $entidadFiscal;
        $content['entidadesFiscales'] = $entidadesFiscales;
        $content['current_step'] = "entidadFiscal";
        $content['paises'] = $paises;
        $content['estados'] = $estados;
        return $this->render('WizardEntidadFiscalBundle:Section:entidadFiscal.html.twig', array('content' => $content));
    }

    public function entidadFiscalEliminarAction(Request $request) {
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
        if ($post['idEntidadFiscal'] == "") {
            $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
            return $this->redirectToRoute('wizard_entidad_fiscal');
        }

        $args = array('idEntidadFiscal' => $post['idEntidadFiscal']);
        $result = $this->EntidadFiscalModel->deleteEntidadFiscal($args);
        if (!$result['status']) {
            $session->getFlashBag()->add('danger', $result['data']);
            return $this->redirectToRoute('wizard_entidad_fiscal');
        }

        $session->getFlashBag()->add('success', $general_text['sas_eliminoExito']);
        return $this->redirectToRoute('wizard_entidad_fiscal');
    }

    public function entidadFiscalOmitirAction(Request $request) {
        $session = $request->getSession();
        /* Obtenemos la Configuracion Inicial */
        $result_conf = $this->EntidadFiscalModel->getConfiguracionInicial();
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
            'EntidadFiscal' => "true"
        );

        $result_config = $this->EntidadFiscalModel->insertEditConfiguracionInicial($values);
        if (!$result_config['status']) {
            $session->getFlashBag()->add('danger', $result_config['data']);
            return $this->redirectToRoute('wizard_entidad_fiscal');
        }
        return $this->redirectToRoute('wizard_contacto');
    }

}
