<?php

namespace Visitante\ReportesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Utilerias\TextoBundle\Model\TextoModel;
use Visitante\ReportesBundle\Model\ReportesModel;

class ReportesController extends Controller
{

    protected $TextoModel, $ReportesModel;

    const MAIN_ROUTE = "visitante";

    public function __construct()
    {
        $this->TextoModel = new TextoModel();
        $this->ReportesModel = new ReportesModel();
    }

    public function reportesGeneralesListAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        $content = array();
        $content['App'] = $App;
        $content['user'] = $user;
        $content['lang'] = $lang;
        $content['idEvento'] = $idEvento;
        $content['idEdicion'] = $idEdicion;
        $content["breadcrumb"] = array();


        $general_text = $this->TextoModel->getTexts($lang);
        if (!$general_text['status']) {
            throw new \Exception($general_text['data'], 409);
        }
        $content['general_text'] = $general_text['data'];


        /* Verificamos si tiene permiso en el modulo seleccionado 
          $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request);
          if (!$breadcrumb) {
          $session->getFlashBag()->add('warning', $content['general_text']['sas_moduloNoDisponible']);
          return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
          } */
        $content["breadcrumb"] = $breadcrumb;


        $where = array(
            "idUsuario" => $user["idUsuario"],
            "idPlataformaIxpo" => $session->get('idPlataformaIxpo'),
            "idEvento" => $idEvento,
            "idEdicion" => $idEdicion,
            "Ver" => "true",
        );
        $rp = $this->ReportesModel->getReportes($where);
        if (!$rp['status']) {
            throw new \Exception($rp['data'], 409);
        }
        $content["rp"] = $rp['data'];
        return $this->render('VisitanteReportesBundle:Reportes:reportesGenerales.html.twig', array('content' => $content));
    }

    public function reporteComprasAction(Request $request, $idProducto)
    {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');

        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = "Reporte_Compras-" . date('d-m-Y G.i');

        $args = array("idEvento" => $idEvento, "idEdicion" => $idEdicion);
        if ($idProducto != 0) {
            if ($idProducto == 13) {
                $args['idProducto'] = array($idProducto, "15");
                $args["StatusES"] = "'Completada'";
                $args["Total"] = '0';
            } else {
                $args['idProducto'] = $idProducto;
            }
        } else {
            if ($idProducto == 0) {
                $args["StatusES"] = "'Completada'";
            }
        }
        $data = $this->ReportesModel->getDetalleCompras($args);
        if (!$data['status']) {
            throw new \Exception($data['data'], 409);
        }
        $data = $data['data'];

        $meta_data = array(
            "ID Compra",
            "Estatus Compra",
            "Monto Compra",
            "Producto",
            "Forma Pago",
            "Requiere Factura",
            "Numero de Referencia",
            "Fecha Pago",
            "Compra Facturada",
            "Folio",
            "Serie",
            "Fecha Timbrado",
            "UUID",
            "Fecha Factura",
            "Regimen Fiscal",
            "Razón Social Receptor",
            "RFC Receptor",
            "Domicilio Fiscal Receptor",
            "ID Visitante",
            "Nombre",
            "Email",
            "Lada Paìs",
            "Lada",
            "Teléfono",

            "Razón Social",
            "C.P",
            "País",
            "Estado"
        );
        if ($idProducto == 13) {
            array_push($meta_data, "Citas Extras");
        }
        return $this->excelReport($data, $meta_data, $file_name);
    }

    public function reporteTransacionAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');

        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = "Reporte_transacciones_Compras-" . date('d-m-Y G.i');

        $args = array("idEvento" => $idEvento, "idEdicion" => $idEdicion);
        $data = $this->ReportesModel->getDetalleTransacciones($args);
        if (!$data['status']) {
            throw new \Exception($data['data'], 409);
        }
        $data = $data['data'];
        foreach ($data as $key => $value) {
            $data[$key]['FechaPagado'] = substr($value['FechaPagado'], 0, 10);
            $data[$key]['FechaCancelado'] = substr($value['FechaCancelado'], 0, 10);
        }
        $meta_data = array(
            "idVisitante",
            "idCompra",
            "Nombre Completo",
            "Email",
            "Estatus",
            "Código de autorización",
            "Tipo tarjeta",
            "Últimos dígitos tarjeta",
            "Código de error",
            "Mensaje de error",
            "Fecha de Pago",
            "Fecha de cancelación"
        );
        return $this->excelReport($data, $meta_data, $file_name);
    }

    public function reporteAsociadosProductoAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');

        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = "Reporte_Asociados_Productos-" . date('d-m-Y G.i');

        $args = array("idEvento" => $idEvento, "idEdicion" => $idEdicion, "Asociado" => 1);
        $data = $this->ReportesModel->getAsociadosProducto($args);
        if (!$data['status']) {
            throw new \Exception($data['data'], 409);
        }
        $data = $data['data'];

        $meta_data = array(
            "idVisitante",
            "Nombre Completo",
            "Email",
            "Nombre de la empresa",
            "Productos de interés",
        );
        return $this->excelReport($data, $meta_data, $file_name);
    }

    public function reporteCompradoresInvitadosAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');

        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = "Reporte_Comprador_Invitado-EDN-" . date('d-m-Y G.i');

        $data = $this->ReportesModel->getCompradoresInvitados();

        if (!$data['status']) {
            throw new \Exception($data['data'], 409);
        }
        $data = $data['data'];

        $meta_data = array(
            "id_Visitante",
            "Nombre(s)",
            "Apellido Paterno",
            "Apellido Materno",
            "Email",
            "Nombre de la empresa",
            "País",
            "Estado",
            "Fecha Preregistro",
            "¿Participará en la Agenda de citas de negocio?",
            "Productos de interés",
            "Alimentos (Seleccione máximo 5 productos)",
            "Bebidas (Seleccione máximo 5 productos)",
            "Farmacias (Seleccione máximo 5 productos)",
            "Mercancias (Generales Seleccione máximo 5 productos)",
            "Mobiliario y Equipamiento (Seleccione máximo 5 productos)",
            "Organismos e Instituciones",
            "Servicios (Seleccione máximo 5 productos)",
            "Tecnología (Seleccione máximo 5 productos)",
            "Transportación",
            "Decisión de compra",
            "¿Cómo se enteró del evento?",
            "Estatus"
        );
        return $this->excelReport($data, $meta_data, $file_name);
    }

    public function reporteGeneralAsociadosAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');

        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = "Reporte_Asociados-EDN-" . date('d-m-Y G.i');

        $data = $this->ReportesModel->getGeneralAsociados();

        if (!$data['status']) {
            throw new \Exception($data['data'], 409);
        }
        $data = $data['data'];

        $meta_data = array(
            "id_Visitante",
            "Nombre(s)",
            "Apellido Paterno",
            "Apellido Materno",
            "Email",
            "Nombre de la empresa",
            "Estado",
            "Fecha Preregistro",
            "¿Participará en la Agenda de citas de negocio?",
            "Productos de interés",
            "Alimentos (Seleccione máximo 5 productos)",
            "Bebidas (Seleccione máximo 5 productos)",
            "Farmacias (Seleccione máximo 5 productos)",
            "Mercancias (Generales Seleccione máximo 5 productos)",
            "Mobiliario y Equipamiento (Seleccione máximo 5 productos)",
            "Organismos e Instituciones",
            "Servicios (Seleccione máximo 5 productos)",
            "Tecnología (Seleccione máximo 5 productos)",
            "Transportación",
            "Decisión de compra",
            "¿Cómo se enteró del evento?"
        );
        return $this->excelReport($data, $meta_data, $file_name);
    }

    public function reporteSinComprasAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');

        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = "Reporte_Visitantes-EDN-" . date('d-m-Y G.i');

        $data = $this->ReportesModel->getSinCompras();

        if (!$data['status']) {
            throw new \Exception($data['data'], 409);
        }
        $data = $data['data'];

        $meta_data = array(
            "id_Visitante",
            "Nombre(s)",
            "Apellido Paterno",
            "Apellido Materno",
            "Email",
            "Nombre de la empresa",
            "Estado",
            "Fecha Preregistro",
            "¿Participará en la Agenda de citas de negocio?",
            "Productos de interés",
            "Alimentos (Seleccione máximo 5 productos)",
            "Bebidas (Seleccione máximo 5 productos)",
            "Farmacias (Seleccione máximo 5 productos)",
            "Mercancias (Generales Seleccione máximo 5 productos)",
            "Mobiliario y Equipamiento (Seleccione máximo 5 productos)",
            "Organismos e Instituciones",
            "Servicios (Seleccione máximo 5 productos)",
            "Tecnología (Seleccione máximo 5 productos)",
            "Transportación",
            "Decisión de compra",
            "¿Cómo se enteró del evento?",
            "Pagado",
            "Producto",
            "Fecha de Compra"
        );
        return $this->excelReport($data, $meta_data, $file_name);
    }

    public function reporteAsociadosClickerAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');

        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = "Reporte_Asociados_Clicker-" . date('d-m-Y G.i');

        $data = $this->ReportesModel->getAsociadosClicker();
        if (!$data['status']) {
            throw new \Exception($data['data'], 409);
        }
        $data = $data['data'];

        $meta_data = array(
            "Id Visitante",
            "Nombre(s)",
            "A.Paterno",
            "A.Materno",
            "Email",
            "Telèfono",
            "Codigo Postal",
            "Paìs",
            "Estado",
            "Ciudad",
            "¿Participa en agenda de encuentros de negocio?",
            "Fecha de Registro"
        );
        return $this->excelReport($data, $meta_data, $file_name);
    }

    public function reporteVisitantesClickerAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');

        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = "Reporte_Visitantes_Clicker-" . date('d-m-Y G.i');

        $data = $this->ReportesModel->getVisitanteClicker();

        if (!$data['status']) {
            throw new \Exception($data['data'], 409);
        }
        $data = $data['data'];

        $meta_data = array(
            "Id Visitante",
            "Nombre(s)",
            "A.Paterno",
            "A.Materno",
            "Email",
            "Telèfono",
            "Codigo Postal",
            "Paìs",
            "Estado",
            "Ciudad",
            "¿Participa en agenda de encuentros de negocio?",
            "Pagado",
            "Fecha de Registro"
        );
        return $this->excelReport($data, $meta_data, $file_name);
    }

    public function reporteCompradoresClickerAction(Request $request)
    {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');

        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = "Reporte_Compradores_Clicker-" . date('d-m-Y G.i');

        $data = $this->ReportesModel->getCompradoresClicker();

        if (!$data['status']) {
            throw new \Exception($data['data'], 409);
        }
        $data = $data['data'];

        $meta_data = array(
            "Id Visitante",
            "Nombre(s)",
            "A.Paterno",
            "A.Materno",
            "Email",
            "Telèfono",
            "Codigo Postal",
            "Paìs",
            "Estado",
            "Ciudad",
            "¿Participa en agenda de encuentros de negocio?",
            "Fecha de Registro"
        );
        return $this->excelReport($data, $meta_data, $file_name);
    }

    public function excelReport($general, $table_metadata, $filename)
    {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("Infoexpo")
            ->setTitle($filename)
            ->setSubject($filename)
            ->setDescription($filename);
        $flag = 1;
        $lastColumn = "A";
        foreach ($table_metadata as $value) {
            $phpExcelObject->getActiveSheet()->getColumnDimension($lastColumn)->setAutoSize(true);
            $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lastColumn . $flag, $value);
            $lastColumn++;
        }
        $flag++;
        foreach ($general as $index) {
            $lastColumn = "A";
            foreach ($index as $key => $value) {
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lastColumn . $flag, $value);
                $lastColumn++;
            }
            $flag++;
        }

        $phpExcelObject->getActiveSheet()->getStyle("A1:" . $lastColumn . "1")->getFont()->setBold(true);
        $phpExcelObject->setActiveSheetIndex(0);

        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename . ".xlsx");

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        $response->headers->set('Expires', '0');

        return $response;
    }
}
