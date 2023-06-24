<?php

namespace AdministracionGlobal\ProductoBundle\Controller;

/**
 * Description of PlataformaControllers
 *
 * @author Juan
 */
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AdministracionGlobal\ProductoBundle\Model\ProductoModel;
use Utilerias\TextoBundle\Model\TextoModel;
use Symfony\Component\HttpFoundation\Response;

class ProductoController extends Controller {

    protected $ProductoModel, $TextoModel;

    const SECTION = 3, MAIN_ROUTE = 'producto';

    public function __construct() {
        $this->ProductoModel = new ProductoModel();
        $this->TextoModel = new TextoModel();
    }

    public function productoAction(Request $request) {
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

        /* Verificamos si tiene permiso en el modulo seleccionado */
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }

        /* Obtenemos textos de la sección del Administración Global */
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $content['section_text'] = $section_text['data'];
        /* Obtenemos los productos */
        $result_pr = $this->ProductoModel->getProducto();
        if (!$result_pr['status']) {
            throw new \Exception($result_pr['data'], 409);
        }
        /* Obtenemos los modulos */
        $result_md = $this->ProductoModel->getModulo();
        if (!$result_md['status']) {
            throw new \Exception($result_md['data'], 409);
        }
        /* Obtenemos los modulosproductos */
        $result_mdpr = $this->ProductoModel->getModuloProducto();
        if (!$result_mdpr['status']) {
            throw new \Exception($result_mdpr['data'], 409);
        }
        $content['breadcrumb'] = $breadcrumb;
        $content['pr'] = $result_pr['data'];
        $content['md'] = $result_md['data'];
        $content['App'] = $App;
        $content['user'] = $user;
        foreach ($content['pr'] as $kprod => $prod) {
            $content['pr'][$kprod]['Modulos'] = Array();
            foreach ($result_mdpr['data'] as $kmod => $mod) {
                if ($mod['idProductoIxpo'] == $prod['idProductoIxpo']) {
                    array_push($content['pr'][$kprod]['Modulos'], $mod['idModuloIxpo']);
                }
            }
        }
        return $this->render('AdministracionGlobalProductoBundle:Producto:lista_producto.html.twig', array('content' => $content));
    }

    public function productoNuevoAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $res = $this->ProductoModel->insertProducto($post);
            if ($res['status']) {
                $result['status'] = TRUE;
                $result['data']['Modulos'] = explode(",", $post['modulos']);
                $result['data']['EstandarIxpo'] = FALSE;
                $result['data']['ProductoIxpo'] = $post['Nombre'];
                $result['data']['idProductoIxpo'] = $res['data'][0]['fn_InsertarProductoModulosIxpo'];
            } else {
                $result['data'] = $res['data'];
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function productoEditarAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $res = $this->ProductoModel->updateProducto($post);
            if ($res['status']) {
                $result['status'] = TRUE;
                $result['data']['Modulos'] = explode(",", $post['modulos']);
                $result['data']['EstandarIxpo'] = FALSE;
                $result['data']['ProductoIxpo'] = $post['Nombre'];
                $result['data']['idProductoIxpo'] = $post['idProducto'];
            } else {
                $result['data'] = $res['data'];
            }
        } else {
            $result['data'] = $content['general_text']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function productoDuplicarAction(Request $request) {
        $post = $request->request->all();
        $session = $request->getSession();
        $lang = $session->get('lang');
        $result = Array('status' => FALSE, 'data' => "");
        /* Obtenemos textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];
        if ($request->getMethod() == 'POST') {
            $res = $this->ProductoModel->duplicateProducto($post);
            if ($res['status']) {
                $result['status'] = TRUE;
                $result['data']['Modulos'] = Array();
                foreach ($res['data'] as $key => $value) {
                    array_push($result['data']['Modulos'], $value['idModulo']);
                }
                $result['data']['EstandarIxpo'] = FALSE;
                $result['data']['ProductoIxpo'] = $res['data'][0]['Producto'];
                $result['data']['idProductoIxpo'] = $res['data'][0]['idProducto'];
            } else {
                $result['data'] = $res['data'];
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
