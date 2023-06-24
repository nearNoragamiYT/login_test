<?php

namespace AdministracionGlobal\ModuloBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AdministracionGlobal\ModuloBundle\Model\ModuloModel;
use AdministracionGlobal\PlataformaBundle\Model\PlataformaModel;
use Utilerias\TextoBundle\Model\TextoModel;
use Symfony\Component\HttpFoundation\Response;

class ModuloController extends Controller
{

    protected $ModuloModel, $TextoModel;

    const SECTION = 3, MAIN_ROUTE = 'modulo';

    public function __construct()
    {
        $this->ModuloModel = new ModuloModel();
        $this->PlataformaModel = new PlataformaModel();
        $this->TextoModel = new TextoModel();
    }

    public function moduloAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $content = array();
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            if (isset($post['Modulo_ES'])) {
                $post['Modulo_ES'] = ($post['Modulo_ES']) ? "'" . $post['Modulo_ES'] . "'" : "";
            }
            if (isset($post['Modulo_EN'])) {
                $post['Modulo_EN'] = ($post['Modulo_EN']) ? "'" . $post['Modulo_EN'] . "'" : "";
            }
            if (isset($post['Ruta'])) {
                $post['Ruta'] = ($post['Ruta']) ? "'" . $post['Ruta'] . "'" : "";
            }
            $post['Publicado'] = ($post['Publicado']) ? "true" : "false";
            $res = $this->ModuloModel->insertEditModuloIxpo($post);

            if (!$res['status']) {
                $session->getFlashBag()->add('warning', $res['data']);
                return $this->redirectToRoute("modulo");
            }
            $session->getFlashBag()->add('success', $content['general_text']['sas_exitoGuardado']);
            return $this->redirectToRoute("modulo");
        }

        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }

        /* Obtenemos textos de la secciÃ³n del AdministraciÃ³n Global 3 */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* Obtenemos las plataformas */
        $result_pl = $this->ModuloModel->getPlataforma();
        if (!$result_pl['status']) {
            throw new \Exception($result_pl['data'], 409);
        }
        $pl = $result_pl["data"];

        $result_mo = $this->ModuloModel->getModulo();
        if (!$result_mo['status']) {
            throw new \Exception($result_mo['data'], 409);
        }
        $mo = $result_mo['data'];
        $items = array();
        if (count($mo) > 0) {
            foreach ($mo as $idModuloIxpo => $modulo) {
                if (!isset($items[$modulo['idPlataformaIxpo']])) {
                    $plataforma = array(
                        "idPlataformaIxpo" => $modulo['idPlataformaIxpo'],
                        "PlataformaIxpo" => $pl[$modulo['idPlataformaIxpo']]['PlataformaIxpo'],
                        "Modulos" => NULL
                    );
                    $items[$modulo['idPlataformaIxpo']] = $plataforma;
                }

                $m = array();
                $m['idModuloIxpo'] = $modulo['idModuloIxpo'];
                $m['ModuloIxpo'] = $modulo['Modulo_' . strtoupper($lang)];
                switch ($modulo['Nivel']) {
                    case 1:
                        $m['SubModulos'] = NULL;
                        $items[$modulo['idPlataformaIxpo']]["Modulos"][$modulo['idModuloIxpo']] = $m;
                        break;
                    case 2:
                        if (isset($items[$modulo['idPlataformaIxpo']]['Modulos'][$modulo['idPadre']])) {
                            $items[$modulo['idPlataformaIxpo']]['Modulos'][$modulo['idPadre']]['SubModulos'][$modulo['idModuloIxpo']] = $m;
                        }
                        break;
                    default:
                }
            }
        }

        $content['breadcrumb'] = $breadcrumb;
        $content['App'] = $App;
        $content['user'] = $user;
        $content['mo'] = $mo;
        $content['items'] = $items;
        $content['pl'] = $pl;
        $content['lang'] = $lang;
        return $this->render('AdministracionGlobalModuloBundle:Modulo:lista_modulo.html.twig', array('content' => $content));
    }

    public function moduloEditarAction(Request $request)
    {
        if ($request->getMethod() != 'POST') {
            return $this->redirectToRoute("modulo");
        }
        $post = $request->request->all();
        $res = $this->ModuloModel->insertEditModuloIxpo($post);
        return $this->jsonResponse($res);
    }

    public function moduloEliminarAction(Request $request)
    {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');

        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];

        $res = $this->ModuloModel->deleteModulo($post);
        if (!$res['status']) {
            $session->getFlashBag()->add('warning', $res['data']);
            return $this->redirectToRoute("modulo");
        }

        $session->getFlashBag()->add('success', $content['general_text']['sas_eliminoExito']);
        return $this->redirectToRoute("modulo");
    }

    protected function jsonResponse($data)
    {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
