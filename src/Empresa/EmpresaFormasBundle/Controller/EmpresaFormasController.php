<?php

namespace Empresa\EmpresaFormasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Empresa\EmpresaFormasBundle\Model\EmpresaFormasModel;
use Utilerias\TextoBundle\Model\TextoModel;

class EmpresaFormasController extends Controller {

    private $model, $text;

    const SECTION = 4;

    public function __construct() {
        $this->model = new EmpresaFormasModel();
        $this->text = new TextoModel();
    }

    public function mostrarAction(Request $request, $idEmpresa) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();

        $content['tabPermission'] = json_decode($this->model->tabsPermission($user),true);
        $content['currentRoute'] = $request->get('_route');

        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['idEmpresa'] = $idEmpresa;
        $content['idEvento'] = $session->get("idEvento");
        $content['idEdicion'] = $session->get("idEdicion");
        $content['routeName'] = 'empresa';
        /* Obtenemos textos generales */
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        /* Obtenemos textos de la seccion 4 */
        $section_text = $this->text->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* ---  detalle del breadcrumb  --- */
        $content["breadcrumb"] = $this->model->breadcrumb("empresa", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => $content["section_text"]["sas_formas"], "route" => ""));
        /* ---  formas por edicion  --- */
        $args = Array("idEvento" => $content['idEvento'], "idEdicion" => $content['idEdicion']);
        $formas = $this->model->getFormas($args, $lang);
        /* ---  formas por expositor  --- */
        $args['idEmpresa'] = $idEmpresa;
        $formasEmpresa = $this->model->getEmpresaFormas($args);
        /* ---  obtenemos el token por empresa  --- */
        $content['token'] = $this->model->getToken($args);
        $content["formas"] = $this->getDetallesFormas($formas, $formasEmpresa);
        /* ---  headers de la plantilla  --- */
        $params = Array('e."idEmpresa"' => $idEmpresa, 'ee."idEdicion"' => $content['idEdicion']);
        $content["header"] = $this->model->getCompanyHeader($params);
        /* ---  optenemos los paquetes para mostrarlo en los headers  --- */
        //$content["packages"] = $this->model->getPackages($args, $lang);
        /* ---  obtenemos las formas de cada paquete para hacer el filtro en la tabla  --- */
        $content['fopq'] = $this->model->getFormasPaquete($args);
        /* ---  obtenemos el detalle si la empresa es adicional para mostrar solo ciertas pestañas  --- */
        $content['Adicional'] = $this->model->getAditionalDetail(Array("idEmpresa" => $idEmpresa, "idEdicion" => $content['idEdicion']));

        return $this->render('EmpresaEmpresaFormasBundle:EmpresaFormas:mostrar.html.twig', Array("content" => $content));
    }

    public function actualizarBloqueoAction(Request $request, $idEmpresa) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['lang'] = $lang;
        $content['App'] = $App;
        $content['user'] = $user;
        $post = $request->request->all();
        $post['idEmpresa'] = $idEmpresa;
        $post['idEdicion'] = $session->get("idEdicion");
        $post['idEvento'] = $session->get("idEvento");
        $this->model->actualizarBloqueo($post);
        return new JsonResponse(Array('status' => TRUE));
    }

    public function getDetallesFormas($formas, $empresaFormas) {
        $formasExpositor = Array();
        foreach ($formas as $idForma => $forma) {
            if (!array_key_exists($idForma, $formasExpositor)) {
                $formasExpositor[$idForma] = $forma;
            }
            foreach ($empresaFormas as $idF => $F) {
                if ($idForma == $idF) {
                    $dataMerge = array_merge($formasExpositor[$idF], $F);
                    $formasExpositor[$idF] = $dataMerge;
                    break;
                }
            }
        }
        return $formasExpositor;
    }

}
