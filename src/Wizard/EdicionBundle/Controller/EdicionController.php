<?php

namespace Wizard\EdicionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\ConfigurationBundle\Model\ConfigurationModel;
use Utilerias\TextoBundle\Model\TextoModel;
use Wizard\EventoBundle\Model\EventoModel;
use Wizard\EdicionBundle\Model\EdicionModel;

class EdicionController extends Controller {

    protected $ConfigurationModel, $App, $TextoModel, $EventoModel, $EdicionModel;

    const SECTION = 2;

    public function __construct() {
        $this->ConfigurationModel = new ConfigurationModel();
        $this->App = $this->ConfigurationModel->getApp();
        $this->TextoModel = new TextoModel();
        $this->EventoModel = new EventoModel();
        $this->EdicionModel = new EdicionModel();
    }

    public function edicionAction(Request $request) {
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
            $this->EdicionModel->trimValues($post);
            /* Verificamos que no exista la misma Edicion */
            $args = array(
                'lower("Edicion_ES")' => "'" . mb_strtolower($post['Edicion_ES'], 'UTF-8') . "'",
                'idEvento' => $post['idEvento'],
            );
            if ($this->EdicionModel->is_defined($post['idEdicion'])) {
                $args['idEdicion'] = array("operator" => "<>", "value" => $post['idEdicion']);
            }
            $result_edi = $this->EdicionModel->getEdicion($args);
            if (!$result_edi['status']) {
                $session->getFlashBag()->add('danger', $result_edi['data']);
                return $this->redirectToRoute('wizard_edicion');
            }

            if (count($result_edi['data']) > 0) {
                $session->getFlashBag()->add('warning', $section_text['sas_EdicionExistente']);
                return $this->redirectToRoute('wizard_edicion');
            }

            $result_files = $this->EdicionModel->uploadFiles($_FILES, $general_text, "header/");
            if (!$result_files['status']) {
                $session->getFlashBag()->add('warning', $result_files['data']);
                return $this->redirectToRoute('wizard_edicion');
            }

            if (count($result_files['data']) > 0) {
                foreach ($result_files['data'] as $key => $value) {
                    $post[$value['field']] = "'" . $value['name'] . "'";
                }
            }

            $data = array();
            $data['idEdicion'] = $post['idEdicion'];
            unset($post['idEdicion']);
            $data['idEvento'] = $post['idEvento'];
            unset($post['idEvento']);
            $data['idComiteOrganizador'] = $post['idComiteOrganizador'];
            unset($post['idComiteOrganizador']);
            unset($post['lang']);
            $post = array_merge($data, $this->EdicionModel->formatQuoteValue($post));

            $result = $this->EdicionModel->insertEditEdicion($post);
            if (!$result['status']) {
                $session->getFlashBag()->add('danger', $result['data']);
                return $this->redirectToRoute('wizard_edicion');
            }

            if (count($result['data']) == 0) {
                $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
                return $this->redirectToRoute('wizard_edicion');
            }

            /* Insertamos usuario edicion */
            if (!$this->EdicionModel->is_defined($post['idEdicion'])) {
                $args_ue = array(
                    'idEvento' => $post['idEvento'],
                    'idEdicion' => $result['data'][0]['idEdicion'],
                    'idUsuario' => $user['idUsuario']
                );
                $result_ue = $this->EdicionModel->insertUsuarioEdicion($args_ue);
                if (!$result_ue['status']) {
                    $session->getFlashBag()->add('warning', $result_ue['data']);
                    return $this->redirectToRoute('wizard_edicion');
                }
            }

            /* Insertamos los permisos de la edicion si no hay permisos */
            if ($post['idEdicion'] != "" && isset($user['Ediciones']) && count($user['Ediciones'][$post['idEdicion']]) == 0) {
                $result_moduloIxpo = $this->EdicionModel->getModuloIxpo();
                if (!$result_moduloIxpo['status']) {
                    $session->getFlashBag()->add('warning', $result_moduloIxpo['data']);
                    return $this->redirectToRoute('wizard_edicion');
                }

                $data_permisos = array();
                if (count($result_moduloIxpo['data']) > 0) {
                    foreach ($result_moduloIxpo['data'] as $key => $modulo) {
                        $data_permisos[] = array(
                            'idUsuario' => $user['idUsuario'],
                            'idEvento' => $post['idEvento'],
                            'idEdicion' => $result['data'][0]['idEdicion'],
                            'idModulo' => $modulo['idModuloIxpo'],
                            'Ver' => "true",
                            'Editar' => "true",
                            'Borrar' => "true",
                        );
                    }
                }
                $result_permisos = $this->EdicionModel->insertPermisosEdicion($data_permisos);
                if (!$result_permisos['status']) {
                    $session->getFlashBag()->add('warning', $result_permisos['data']);
                    return $this->redirectToRoute('wizard_edicion');
                }

                /* Actualizamos el usuario en sesion */
                $args = array('idUsuario' => $user['idUsuario']);
                $result_usuario = $this->EdicionModel->LoginModel->getUsuario($args);
                if (!$result_usuario['status']) {
                    throw new \Exception($result_usuario['data'], 409);
                }
                $usuario = $result_usuario['data'][0];
                $user['Ediciones'] = $usuario['Ediciones'];
                /* Modulos disponibles para el usuario */
                $result_modulo_user = $this->EdicionModel->LoginModel->getModulosEdicionUsuario($usuario);
                if (!$result_modulo_user['status']) {
                    throw new \Exception($result_modulo_user['data'], 409);
                }

                $profile->setData($user);
                $session->set('modulos_usuario', $result_modulo_user['data']);
                $session->set('plataformas_usuario', $result_modulo_user['plataformas']);
                /* Actualizamos el usuario en sesion */
            }

            /* Insertamos o actualizamos los status de la configuracion inicial */
            $values = array(
                'idComiteOrganizador' => $post['idComiteOrganizador'],
                'Edicion' => "true"
            );
            $result_config = $this->EdicionModel->insertEditConfiguracionInicial($values);
            if (!$result_config['status']) {
                $session->getFlashBag()->add('danger', $result_config['data']);
                return $this->redirectToRoute('wizard_edicion');
            }

            $session->getFlashBag()->add('success', $general_text['sas_guardoExito']);
            if ($this->EdicionModel->is_defined($post['idEdicion'])) {
                return $this->redirectToRoute('wizard_edicion');
            }
            return $this->redirectToRoute('wizard_producto');
        }

        /* Obtenemos la Configuracion Inicial */
        $result_conf = $this->EdicionModel->getConfiguracionInicial();
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

        /* Eventos */
        $result_evento = $this->EventoModel->getEvento(array('idComiteOrganizador' => $configuration['idComiteOrganizador']));
        if (!$result_evento['status']) {
            throw new \Exception($result_evento['data'], 409);
        }

        if (count($result_evento['data']) == 0) {
            $session->getFlashBag()->add('info', $section_text['sas_sinEvento']);
            return $this->redirectToRoute('wizard_evento');
        }

        foreach ($result_evento['data'] as $key => $value) {
            $eventos[$value['idEvento']] = $value;
        }

        /* Edicion */
        $result_edicion = $this->EdicionModel->getEdicion(array('idComiteOrganizador' => $configuration['idComiteOrganizador']));
        if (!$result_edicion['status']) {
            throw new \Exception($result_edicion['data'], 409);
        }

        $ediciones = array();
        if (count($result_edicion['data']) > 0) {
            foreach ($result_edicion['data'] as $k => $edicion) {
                $ediciones[$edicion['idEdicion']] = $edicion;
            }
        }

        /* Modulos de los Productos Ixpo */
        /* $result_modulo_producto = $this->EdicionModel->getModuloProductoIxpo();
          if (!$result_modulo_producto['status']) {
          throw new \Exception($result_modulo_producto['data'], 409);
          }

          $moduloProducto = array();
          if (count($result_modulo_producto['data']) > 0) {
          $moduloProducto = $this->EdicionModel->formatModuloProductoIxpo($result_modulo_producto['data']);
          } */

        $content = array();
        $content['configuration'] = $configuration;
        $content['general_text'] = $general_text;
        $content['section_text'] = $section_text;
        $content['user'] = $user;
        $content['eventos'] = $eventos;
        //$content['moduloProducto'] = $moduloProducto;
        $content['current_step'] = "edicion";
        $content['ediciones'] = $ediciones;
        return $this->render('WizardEdicionBundle:Section:edicion.html.twig', array('content' => $content));
    }

    public function edicionEliminarAction(Request $request) {
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
        if ($post['idEdicion'] == "") {
            $session->getFlashBag()->add('warning', $general_text['sas_errorPeticion']);
            return $this->redirectToRoute('wizard_edicion');
        }

        /* Eliminamos usuario edicion */
        $args_ue = array('idEdicion' => $post['idEdicion'], 'idUsuario' => $user['idUsuario']);
        $result_ue = $this->EdicionModel->deleteUsuarioEdicion($args_ue);
        if (!$result_ue['status']) {
            $session->getFlashBag()->add('danger', $result_ue['data']);
            return $this->redirectToRoute('wizard_edicion');
        }

        $args = array('idEdicion' => $post['idEdicion']);
        $result = $this->EdicionModel->deleteEdicion($args);
        if (!$result['status']) {
            $session->getFlashBag()->add('danger', $result['data']);
            return $this->redirectToRoute('wizard_edicion');
        }

        $session->getFlashBag()->add('success', $general_text['sas_eliminoExito']);
        return $this->redirectToRoute('wizard_edicion');
    }

}
