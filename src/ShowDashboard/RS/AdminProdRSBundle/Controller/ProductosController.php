<?php

namespace ShowDashboard\RS\AdminProdRSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Utilerias\TextoBundle\Model\TextoModel;
use ShowDashboard\RS\AdminProdRSBundle\Model\ProductosModel;

class ProductosController extends Controller {

    protected $ProductosModel;

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->ProductosModel = new ProductosModel();
    }

    const SECTION = 11;

    public function ProductosAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $content = array();
        $idEdicion = $session->get('idEdicion');
        $idEvento = $session->get('idEvento');
//        $session->set("companyOrigin", "lectoras");
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
        $result_products = $this->ProductosModel->getProductos($lang);
        if (!$result_products['status']) {
            throw new \Exception($result_products['data'], 409);
        }
        /* se obtienen los productos */
        $content['Productos'] = $result_products[data];
        $content['lang'] = $lang;
        
        $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content["breadcrumb"] = $breadcrumb;

        return $this->render('ShowDashboardRSAdminProdRSBundle:Default:ShowProductos.html.twig', array('content' => $content));
    }

    public function insertProdRSAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        /* Obtención de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $data = array(
                'ProductoES' => "'" . $post['ProductoES'] . "'",
                'DescripcionES' => "'" . $post['DescripcionES'] . "'",
                'Precio' => $post['Precio'],
                'PrecioSitio' => $post['PrecioSitio'],
                'idEvento' => $idEvento,
                'idEdicion' => $idEdicion,
            );

            $result = $this->ProductosModel->insertProducto($data);
            if (!$result['status']) {
                throw new \Exception($result_products['data'], 409);
            } else {
                $result['status'] = true;
                $data['idProducto'] = $result['data'][0]['idProducto'];
                $data['ProductoES'] = $post['ProductoES'];
                $data['DescripcionES'] = $post['DescripcionES'];
                $data['PrecioSitio'] = $post['PrecioSitio'];
                $result['data'] = $data;
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }

        return $this->jsonResponse($result);
    }

    public function deleteProdRSAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        /* Obtención de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $data = array(
                'idProducto' => $post['idProducto']
            );
            $result = $this->ProductosModel->deleteProducto($data);
            if ($result['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            } else {
                $result['error'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }
        return $this->jsonResponse($result);
    }

    public function updateProdRSAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        /* Obtención de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();
            $data = array(
                'ProductoES' => "'" . $post['ProductoES'] . "'",
                'DescripcionES' => "'" . $post['DescripcionES'] . "'",
                'Precio' => $post['Precio'],
                'PrecioSitio' => $post['PrecioSitio'],
            );

            $where = array(
                'idProducto' => $post['idProducto']
            );

            $result = $this->ProductosModel->updateProducto($data, $where);
            if ($result['status']) {
                $result['status'] = TRUE;
                $result['data'] = $post;
            } else {
                $result['error'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }

        return $this->jsonResponse($result);
    }

    public function updateProdStatRSAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');
        /* Obtención de textos generales */
        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $data_prod = array(
                'idProducto' => $post['idProducto']
            );
            $result_prod = $this->ProductosModel->getProducto($data_prod);
            if ($result_prod['data'][0]['Activo'] == null) {
                $data = array(
                    'ProductoES' => "'" . $result_prod['data'][0]['ProductoES'] . "'",
                    'DescripcionES' => "'" . $result_prod['data'][0]['DescripcionES'] . "'",
                    'Precio' => $result_prod['data'][0]['Precio'],
                    'PrecioSitio' => $result_prod['data'][0]['PrecioSitio'],
                    'Activo' => "'" . t . "'",
                );
            } else {
                $data = array(
                    'ProductoES' => "'" . $result_prod['data'][0]['ProductoES'] . "'",
                    'DescripcionES' => "'" . $result_prod['data'][0]['DescripcionES'] . "'",
                    'Precio' => $result_prod['data'][0]['Precio'],
                    'PrecioSitio' => $result_prod['data'][0]['PrecioSitio'],
                    'Activo' => "'" . f . "'",
                );
            }
            $where = array(
                'idProducto' => $result_prod['data'][0]['idProducto']
            );

            $result = $this->ProductosModel->updateProducto($data, $where);

            if ($result['status']) {
                $result['status'] = TRUE;
                $result['data'] = $data;
            } else {
                $result['error'] = $general_text['data']['sas_errorPeticion'];
            }
        } else {
            $result['data'] = $general_text['data']['sas_metodoNoPermitido'];
        }

        return $this->jsonResponse($result);
    }

    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
