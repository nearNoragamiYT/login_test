<?php

namespace Empresa\EmpresaGafetesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utilerias\TextoBundle\Model\TextoModel;
use Empresa\EmpresaGafetesBundle\Model\EmpresaGafetesModel;

class EmpresaGafetesController extends Controller {

    protected $text, $model;

    const SECTION = 4;
    const MAIN_ROUTE = "empresa_empresa_gafetes_mostrar_gafetes";

    public function __construct() {
        $this->model = new EmpresaGafetesModel();
        $this->text = new TextoModel();
    }

    public function mostrarGafetesAction(Request $request) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['routeName'] = self::MAIN_ROUTE;
        $content['idEdicion'] = $session->get("idEdicion");
        $content['idEvento'] = $session->get("idEvento");
        // ------    Obtenemos los textos generales    ------ //
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* ---  Obtenemos los textos del modulo  --- */
        $section_text = $this->text->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* ---  Obtenemos los vendedores  --- */
        $args = Array('idEvento' => $content['idEvento'], 'idEdicion' => $content['idEdicion']);
        $content['vendedores'] = $this->model->getVendedores($args, $lang);
        /* ---  Obtenemos las primeras empresas que mostramos   --- */
        $content['empresas'] = $this->model->getEmpresas($args, $lang);
        return $this->render('EmpresaEmpresaGafetesBundle:EmpresaGafetes:mostrar_gafetes.html.twig', array('content' => $content));
    }

    public function mostrarGafetesVendedorAction(Request $request, $idVendedor) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        // ------    Obtenemos los textos generales    ------ //
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* ---  Obtenemos los textos del modulo  --- */
        $section_text = $this->text->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* ---  Obtenemos los detalles de los gafetes por vendedor  --- */
        $args["idUsuario"] = $idVendedor;
        $args["idEdicion"] = $content['idEdicion'];
        $content['empresas'] = $this->model->getGafetes($args);
        if (COUNT($content['empresas']) == 0) {
            $session->getFlashBag()->add('warning', $content['section_text']['sas_empresaSinGafetes']);
            return $this->redirectToRoute("empresa_empresa_gafetes_mostrar_gafetes");
        }

        return $this->render('EmpresaEmpresaGafetesBundle:EmpresaGafetes:gafetes.html.twig', array('content' => $content));
    }

    public function mostrarGafetesEmpresaAction(Request $request, $idVendedor, $idEmpresa) {
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        // ------    Obtenemos los textos generales    ------ //
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* ---  Obtenemos los textos del modulo  --- */
        $section_text = $this->text->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* ---  Obtenemos los detalles de los gafetes por vendedor  --- */
        $args["idVendedor"] = $idVendedor;
        $args["idEmpresa"] = $idEmpresa;
        $args["idEdicion"] = $content['idEdicion'];
        $content['empresas'] = $this->model->getGafetes($args);
        if (COUNT($content['empresas']) == 0) {
            $session->getFlashBag()->add('warning', $content['section_text']['sas_empresaSinGafetes']);
            return $this->redirectToRoute("empresa_empresa_gafetes_mostrar_gafetes");
        }

        return $this->render('EmpresaEmpresaGafetesBundle:EmpresaGafetes:gafetes.html.twig', array('content' => $content));
    }

    public function mostrarTodosGafetesAction(Request $request) {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '-1');
        $session = $request->getSession();
        /* ---  si la edicion se perdió lo mandamos al dashboard  --- */
        if (empty($session->get("idEdicion"))) {
            $lang = $session->get('lang');
            $general_text = $this->text->getTexts($lang);
            $session->getFlashBag()->add('danger', $general_text['data']['sas_errorEdicionCaduco']);
            return $this->redirectToRoute("dashboard");
        }
        $lang = $session->get('lang');
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEdicion'] = $session->get("idEdicion");
        // ------    Obtenemos los textos generales    ------ //
        $general_text = $this->text->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        /* ---  Obtenemos los textos del modulo  --- */
        $section_text = $this->text->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* ---  Obtenemos los detalles de los gafetes por vendedor  --- */
        $args["idEdicion"] = $content['idEdicion'];
        $content['empresas'] = $this->model->getGafetes($args);
        if (COUNT($content['empresas']) == 0) {
            $session->getFlashBag()->add('warning', $content['section_text']['sas_empresaSinGafetes']);
            return $this->redirectToRoute("empresa_empresa_gafetes_mostrar_gafetes");
        }

        return $this->render('EmpresaEmpresaGafetesBundle:EmpresaGafetes:gafetes.html.twig', array('content' => $content));
    }

}
