<?php

namespace Empresa\EmpresaContactoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use Empresa\EmpresaContactoBundle\Model\EmpresaContactoModel;
use Empresa\EmpresaContactoBundle\Model\EmpresaContactoConfiguration;

class EmpresaContactoController extends Controller {

    protected $TextoModel, $EmpresaContactoModel, $EmpresaContactoConfiguration;

    const SECTION = 4;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->EmpresaContactoModel = new EmpresaContactoModel();
        $this->EmpresaContactoConfiguration = new EmpresaContactoConfiguration();
    }

    public function generalContactsAction(Request $request, $idEmpresa) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['tabPermission'] = json_decode($this->EmpresaContactoModel->tabsPermission($user), true);
        $content['currentRoute'] = $request->get('_route');

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Obtenemos textos de la seccion 4 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];

        /* Comienza la logica propia del Action */
        $idEdicion = $session->get('idEdicion');
        $content["idEmpresa"] = $idEmpresa;

        $args = Array('e."idEmpresa"' => $idEmpresa, 'ee."idEdicion"' => $idEdicion);
        $content["header"] = $this->EmpresaContactoModel->getCompanyHeader($args);

        $args = Array('p."idEdicion"' => $idEdicion);
        //$content["packages"] = $this->EmpresaContactoModel->getPackages($args);

        $contact_metadata = $this->EmpresaContactoConfiguration->getGeneralContactMetaData($content['section_text']);
        $content["contact_metadata"] = $contact_metadata;
        /* ---  obtenemos el detalle si la empresa es adicional para mostrar solo ciertas pestañas  --- */
        $content['Adicional'] = $this->EmpresaContactoModel->getAditionalDetail(Array("idEmpresa" => $idEmpresa, "idEdicion" => $idEdicion));
        $args = Array('c."idEmpresa"' => $idEmpresa, 'ce."idEdicion"' => $session->get('idEdicion'));
        $general_contacts = $this->EmpresaContactoModel->getGeneralContacts($args);
        $edition_contacts = $this->EmpresaContactoModel->getEditionidContacts($args);
        /* ---  hacemos merge entre los contactos generales y los contactos por edicion para mostrar el password en la tabla  --- */
        $contacts = Array();
        foreach ($general_contacts as $key => $value) {
            if (COUNT($edition_contacts[$key]) > 0) {
                unset($edition_contacts[$key]["idContactoTipo"]);
                $contacts[$key] = array_merge($value, $edition_contacts[$key]);
            } else {
                /* ---  es necesario poner la variable password para que la pinte en twig  --- */
                $value['Password'] = "";
                $contacts[$key] = $value;
            }
        }
        $content["contacts"] = $contacts;
        if ($session->get("companyOrigin") == "ventas")
            $content["breadcrumb"] = $this->EmpresaContactoModel->breadcrumb("empresa_ventas", $lang);
        if ($session->get("companyOrigin") == "expositores")
            $content["breadcrumb"] = $this->EmpresaContactoModel->breadcrumb("empresa", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => $content["header"]["DC_NombreComercial"], "route" => ""));

        return $this->render('EmpresaEmpresaContactoBundle:EmpresaContacto:empresa_contacto_general.html.twig', array('content' => $content));
    }

    public function editionContactsAction(Request $request, $idEmpresa) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['tabPermission'] = json_decode($this->EmpresaContactoModel->tabsPermission($user), true);
        $content['currentRoute'] = $request->get('_route');

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Obtenemos textos de la seccion 4 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        ;

        /* Comienza la logica propia del Action */
        $idEdicion = $session->get('idEdicion');
        $content["idEmpresa"] = $idEmpresa;

        $args = Array('e."idEmpresa"' => $idEmpresa, 'ee."idEdicion"' => $idEdicion);
        $content["header"] = $this->EmpresaContactoModel->getCompanyHeader($args);

        $args = Array('p."idEdicion"' => $idEdicion);
        //$content["packages"] = $this->EmpresaContactoModel->getPackages($args);

        $contact_metadata = $this->EmpresaContactoConfiguration->getEditionContactMetaData($content['section_text']);
        $content["contact_metadata"] = $contact_metadata;

        $args = Array('c."idEmpresa"' => $idEmpresa, 'ce."idEdicion"' => $idEdicion);
        $contacts = $this->EmpresaContactoModel->getEditionContacts($args);
        $content["contacts"] = $contacts;

        $contact_types = $this->EmpresaContactoModel->getContactTypes($lang);
        $content["contact_types"] = $contact_types;

        $args = Array('c."idEmpresa"' => $idEmpresa, "tipo" => 1, 'ce."idEdicion"' => $idEdicion);
        $generalContacts = $this->EmpresaContactoModel->getGeneralContacts($args);
        $content["generalContacts"] = $generalContacts;

        /* ---  obtenemos el detalle si la empresa es adicional para mostrar solo ciertas pestañas  --- */
        $content['Adicional'] = $this->EmpresaContactoModel->getAditionalDetail(Array("idEmpresa" => $idEmpresa, "idEdicion" => $idEdicion));
        if ($session->get("companyOrigin") == "ventas")
            $content["breadcrumb"] = $this->EmpresaContactoModel->breadcrumb("empresa_ventas", $lang);
        if ($session->get("companyOrigin") == "expositores")
            $content["breadcrumb"] = $this->EmpresaContactoModel->breadcrumb("empresa", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => $content["header"]["DC_NombreComercial"], "route" => ""));

        return $this->render('EmpresaEmpresaContactoBundle:EmpresaContacto:empresa_contacto_edicion.html.twig', array('content' => $content));
    }

    public function addContactAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        /* Obtención de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* Obtención de textos de la sección */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }

        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $post["idEvento"] = $idEvento;
            $post["idEdicion"] = $idEdicion;
            if (isset($post['idContactoTipo'])) {
                $result_contacto_email = $this->EmpresaContactoModel->getEditionContact(Array("idEmpresa" => $post['idEmpresa'], "Email" => "'" . $post['Email'] . "'", "Nombre" => "'" . $post['Nombre'] . "'", "ApellidoPaterno" => "'" . $post['ApellidoPaterno'] . "'", "idContactoTipo" => $post['idContactoTipo']));
                if ($result_contacto_email['status'] && count($result_contacto_email['data']) > 0) {
                    $result['status'] = FALSE;
                    $result['data'] = $general_text['data']['sas_emailContactoYaExiste'];
                    return $this->jsonResponse($result);
                }
            } else {
                $result_contacto_email = $this->EmpresaContactoModel->getContact(Array("idEmpresa" => $post['idEmpresa'], "Email" => "'" . $post['Email'] . "'", "Nombre" => "'" . $post['Nombre'] . "'", "ApellidoPaterno" => "'" . $post['ApellidoPaterno'] . "'"));
                if ($result_contacto_email['status'] && count($result_contacto_email['data']) > 0) {
                    $result['status'] = FALSE;
                    $result['data'] = $general_text['data']['sas_emailContactoYaExiste'];
                    return $this->jsonResponse($result);
                }
            }
            $result = $this->EmpresaContactoModel->insertContact($post);
            if ($result['status']) {
                $post['idContacto'] = $result['data'][0]['_idContacto'];
                $post['Password'] = $result['data'][0]['_Password'];
                $result['status_aux'] = TRUE;
                $result['status'] = TRUE;
                $result['data'] = $post;
                $result['message'] = $general_text['data']['sas_guardoExito'];
            } else {
                $result['error'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function updateContactAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        /* Obtención de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        /* Obtención de textos de la sección */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }

        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            if (isset($post['idContactoTipo'])) {
                $result_contacto_email = $this->EmpresaContactoModel->getEditionContact(Array("idEmpresa" => $post['idEmpresa'], "Email" => "'" . $post['Email'] . "'", "Nombre" => "'" . $post['Nombre'] . "'", "ApellidoPaterno" => "'" . $post['ApellidoPaterno'] . "'", "idContactoTipo" => $post['idContactoTipo'], 'idContacto' => $post['idContacto'], 'update_flag' => TRUE));
                if ($result_contacto_email['status'] && count($result_contacto_email['data']) > 0) {
                    $result['status'] = FALSE;
                    $result['data'] = $general_text['data']['sas_emailContactoYaExiste'];
                    return $this->jsonResponse($result);
                }
            } else {
                $result_contacto_email = $this->EmpresaContactoModel->getContact(Array("idEmpresa" => $post['idEmpresa'], "Email" => "'" . $post['Email'] . "'", "Nombre" => "'" . $post['Nombre'] . "'", "ApellidoPaterno" => "'" . $post['ApellidoPaterno'] . "'", 'idContacto' => Array('operator' => '<>', 'value' => $post['idContacto'])));
                if ($result_contacto_email['status'] && count($result_contacto_email['data']) > 0) {
                    $result['status'] = FALSE;
                    $result['data'] = $general_text['data']['sas_emailContactoYaExiste'];
                    return $this->jsonResponse($result);
                }
            }

            $data1 = Array(
                'idEmpresa' => "'" . $post['idEmpresa'] . "'",
                'Nombre' => "'" . $post['Nombre'] . "'",
                'ApellidoPaterno' => "'" . $post['ApellidoPaterno'] . "'",
                'ApellidoMaterno' => "'" . $post['ApellidoMaterno'] . "'",
                'Email' => "'" . $post['Email'] . "'",
                'EmailAlterno' => "'" . $post['EmailAlterno'] . "'",
                'Puesto' => "'" . $post['Puesto'] . "'",
                'Telefono' => "'" . $post['Telefono'] . "'",
                'Celular' => "'" . $post['Celular'] . "'"
            );
            $data2 = Array(
                'idEmpresa' => "'" . $post['idEmpresa'] . "'",
                'idEdicion' => "'" . $idEdicion . "'",
                'idEvento' => "'" . $idEvento . "'",
                'idContactoTipo' => "'" . $post['idContactoTipo'] . "'",
                'idContactoTipoActual' => "'" . $post['idContactoTipoActual'] . "'",
                'Password' => "'" . $post['Password'] . "'",
            );
            $result = $this->EmpresaContactoModel->updateContact($data1, $data2, $post["idContacto"]);
            if ($result['status']) {
                $result['status_aux'] = TRUE;
                $result['status'] = TRUE;
                $result['data'] = $post;
                $result['message'] = $general_text['data']['sas_guardoExito'];
            } else {
                $result['error'] = $general_text['data']['sas_emailContactoYaExiste'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function deleteContactAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();

        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $post["idEvento"] = $idEvento;
            $post["idEdicion"] = $idEdicion;

            $result = $this->EmpresaContactoModel->deleteContact($post);

            if ($result['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            } else {
                $result['error'] = $content['general_text']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function changeContactAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();

        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $post["idEvento"] = $idEvento;
            $post["idEdicion"] = $idEdicion;

            $result = $this->EmpresaContactoModel->changeContact($post);
            if ($result['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            } else {
                $result['error'] = $content['general_text']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
