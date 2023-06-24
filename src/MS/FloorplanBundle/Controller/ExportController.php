<?php

namespace MS\FloorplanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of ExportController
 *
 * @author Neto
 */
class ExportController extends Controller {

    public function exportGenericDataAction(Request $request) {
        $post = $request->request->all();

        $title = str_replace(",", "", $post['title_report']);

        $order = (empty($post["order_report"])) ? 0 : $post["order_report"];

        $json = $post["data"];
        $json = str_replace("\\", "", $json);
        $json = str_replace("\\n", "", $json);
        $json = str_replace("\\t", "", $json);
        $data = json_decode($json, TRUE);

        $ordered_data = ($order >= 0) ? $this->orderByField($data, $order) : $data;

        $response = $this->render('MSFloorplanBundle:export:generic_table.html.twig', array('columns' => $post['columns'], 'rows' => $ordered_data));

        $response->headers->set("Content-Type", "application/vnd.ms-excel");
        $response->headers->set("charset", "utf-8");
        $response->headers->set("Content-Disposition", "attachment;filename=" . $title . ".xls");
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }
    
    public function orderByField($myArray, $key) {
        $tmp = Array();
        $tmpO = Array();
        foreach ($myArray as &$ma) {
            $tmp[] = &$ma[$key];
        }
        array_multisort($tmp, $myArray);
        return $myArray;
    }
    
    public function jsonResponse($data) {
        return new Response(json_encode($data), 200, Array('Content-Type', 'text/json'));
    }

}
