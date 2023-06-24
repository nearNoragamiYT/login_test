<?php

namespace Wizard\ProductoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use Utilerias\TextoBundle\Model\TextoModel;
use Wizard\ProductoBundle\Model\ProductoModel;
use Wizard\EventoBundle\Model\EventoModel;
use Wizard\EdicionBundle\Model\EdicionModel;

class ProductoController extends Controller {

    protected $ConfigurationModel, $App, $TextoModel, $ProductoModel, $EventoModel, $EdicionModel;

    const SECTION = 2;

    public function __construct() {
        $this->ConfigurationModel = new ConfigurationModel();
        $this->App = $this->ConfigurationModel->getApp();
        $this->TextoModel = new TextoModel();
        $this->ProductoModel = new ProductoModel();
        $this->EventoModel = new EventoModel();
        $this->EdicionModel = new EdicionModel();
    }

    public function productoAction(Request $request) {
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

            $result_update = $this->ProductoModel->deleteEdicionProducto(array('idEdicion' => $post['idEdicion']));
            if (!$result_update['status']) {
                $session->getFlashBag()->add('danger', $result_update['data']);
                return $this->redirectToRoute('wizard_producto');
            }

            $data = array();
            $data['idEdicion'] = $post['idEdicion'];
            foreach ($post['idProductoIxpo'] as $k => $idPlataforma) {
                foreach ($idPlataforma as $key => $idProductoIxpo) {
                    $data['idProductoIxpo'] = $idProductoIxpo;
                    $result = $this->ProductoModel->insertEdicionProducto($data);
                    if (!$result['status']) {
                        $session->getFlashBag()->add('danger', $result['data']);
                        return $this->redirectToRoute('wizard_producto');
                    }

                    if (count($result['data']) == 0) {
                        $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
                        return $this->redirectToRoute('wizard_producto');
                    }
                }
            }

            /* Insertamos o actualizamos los status de la configuracion inicial */
            $values = array(
                'idComiteOrganizador' => $post['idComiteOrganizador'],
                'Producto' => "true",
                'Usuario' => "true"
            );
            $result_config = $this->ProductoModel->insertEditConfiguracionInicial($values);
            if (!$result_config['status']) {
                $session->getFlashBag()->add('danger', $result_config['data']);
                return $this->redirectToRoute('wizard_producto');
            }

            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            return $this->redirectToRoute('wizard_usuario');
        }

        /* Obtenemos la Configuracion Inicial */
        $result_conf = $this->ProductoModel->getConfiguracionInicial();
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

        /* Ediciones de Evento */
        $result_evento_edicion = $this->ProductoModel->getEventoEdicion($configuration['idComiteOrganizador']);
        if (!$result_evento_edicion['status']) {
            throw new \Exception($result_evento_edicion['data'], 409);
        }
        if (count($result_evento_edicion['data']) == 0) {
            $session->getFlashBag()->add('info', $section_text['sas_sinEdicion']);
            return $this->redirectToRoute('wizard_edicion');
        }
        $eventoEdicion = array();
        $edicionesTemp = array();
        foreach ($result_evento_edicion['data'] as $key => $edicion) {
            $idEvento = $edicion['idEvento'];
            if (!isset($eventoEdicion[$idEvento])) {
                $eventoEdicion[$idEvento] = array(
                    'idEvento' => $idEvento,
                    'Evento_ES' => $edicion['Evento_ES'],
                    'Ediciones' => array()
                );
            }
            unset($edicion['idEvento']);
            unset($edicion['Evento_ES']);
            $eventoEdicion[$idEvento]['Ediciones'][$edicion['idEdicion']] = $edicion;
            $edicionesTemp[] = $edicion['idEdicion'];
        }
        /* Ediciones de Evento */

        /* Modulo Producto Ixpo */
        $result_modulo_producto = $this->EdicionModel->getModuloProductoIxpo();
        if (!$result_modulo_producto['status']) {
            throw new \Exception($result_modulo_producto['data'], 409);
        }

        $moduloProducto = array();
        if (count($result_modulo_producto['data']) > 0) {
            $moduloProducto = $this->EdicionModel->formatModuloProductoIxpo($result_modulo_producto['data']);
        }
        /* Modulo Producto Ixpo */

        /* Traemos los productos de la Edicion */
        $result_edicion_producto = $this->EdicionModel->getEdicionProductoIxpo($edicionesTemp);
        if (!$result_edicion_producto['status']) {
            throw new \Exception($result_edicion_producto['data'], 409);
        }
        $edicionProducto = array();
        if (count($result_edicion_producto['data']) > 0) {
            foreach ($result_edicion_producto['data'] as $key => $value) {
                if (!isset($edicionProducto[$value['idEdicion']])) {
                    $edicionProducto[$value['idEdicion']] = array();
                }
                $edicionProducto[$value['idEdicion']][] = $value;
            }
        }

        $content = array();
        $content['configuration'] = $configuration;
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['user'] = $user;
        $content['eventoEdicion'] = $eventoEdicion;
        $content['moduloProducto'] = $moduloProducto;
        $content['edicionProducto'] = $edicionProducto;
        $content['current_step'] = "producto";
        return $this->render('WizardProductoBundle:Section:producto.html.twig', array('content' => $content));
    }

}
