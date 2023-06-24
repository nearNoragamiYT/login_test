<?php

namespace Empresa\ReportesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Utilerias\TextoBundle\Model\TextoModel;
use Empresa\ReportesBundle\Model\ReportesModel;

class ReportesController extends Controller {

    protected $TextoModel, $ReportesModel;

    const SECTION = 4;
    const MAIN_ROUTE = "empresa_reportes";

    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->ReportesModel = new ReportesModel();
    }

    public function reportesListAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $content = array();
        $content['routeName'] = self::MAIN_ROUTE;
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

        /* Obtenemos los paises del PECC */
        $result_paises = $this->get('pecc')->getPaises($lang);
        if (!$result_paises['status']) {
            throw new \Exception($result_paises['data'], 409);
        }
        $content['paises'] = $result_paises['data'];

        $args = Array('c."Principal"' => "true", 'e."idEvento"' => $idEvento);
        $content['breadcrumb'] = $this->ReportesModel->breadcrumb(self::MAIN_ROUTE, $lang);


        return $this->render('EmpresaReportesBundle:Reportes:reportes.html.twig', array('content' => $content));
    }

    public function reportExpositorAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_Expositores " . date('d-m-Y G.i');

        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];
        $data = $this->ReportesModel->getExpositores(array("idEdicion" => $idEdicion, "idStatusContrato" => 4), strtoupper($lang));
        
        foreach ($data as $key => $value) {
            $data[$key] = array_chunk($data[$key], 34);            
            $temp = explode("|", $data[$key][0][33]);
            for ($i = 0; $i < 4; $i++) {
                if (array_key_exists($i, $temp)){
                    $temp2 = explode("-", $temp[$i]);
                    $temp3 = explode(".", $temp2[0]);
                    $temp4 = explode("_", $temp2[1]); 
                    #echo'<br>' . $temp3[0] . ' - ' . $temp3[1] . ' - ' . $temp4[0] . ' - ' . $temp4[1];
                    $data[$key][0]['categoria_id_' . $i] = $temp3[0];
                    $data[$key][0]['categoria_' . $i] = $temp3[1];
                    $data[$key][0]['subcategoria_id_' . $i] = $temp4[0];
                    $data[$key][0]['subcategoria_' . $i] = $temp4[1];
                }else{
                    $data[$key][0]['categoria_id_' . $i] = '';
                    $data[$key][0]['categoria_' . $i] = '';
                    $data[$key][0]['subcategoria_id_' . $i] = '';
                    $data[$key][0]['subcategoria_' . $i] = '';
                }
            }
            unset($data[$key][0][33]);
            $data[$key] = array_merge($data[$key][0], $data[$key][1], $data[$key][2]);            
        }
        $meta_data = array(
            $text['sas_CodigoCliente'],
            "Fecha Autorización Contrato",
            $text['sas_EmpresaTipo'],
            $text['sas_RazonSocial'],
            $text['sas_Rfc'],
            $text['sas_Calle'],
            $text['sas_ColoniaDF'],
            $text['sas_CodigoPostalDF'],
            $text['sas_Ciudad'],
            $text['sas_Estado'],
            $text['sas_Pais'],
            $text['sas_NombreCompletoDF'],
            $text['sas_ContactoNombreDF'],
            $text['sas_ContactoApellidoPaternoDF'],
            $text['sas_ContactoApellidoMaternoDF'],
            $text['sas_ContactoPuestoDF'],
            $text['sas_ContactoEmailDF'],
            $text['sas_NombreComercialDD'],
            $text['sas_NombreCompletoDD'],
            "NombreDD",
            "ApellidoPaternoDD",
            "ApellidoMaternoDD",
            $text['sas_PuestoDD'],
            $text['sas_EmailDD'],
            $text['sas_CalleDD'],
            $text['sas_ColoniaDD'],
            $text['sas_CodigoPostalDD'],
            $text['sas_CiudadDD'],
            $text['sas_EstadoDD'],
            $text['sas_PaisDD'],
            $text['sas_TelefonoDD'],
            $text['sas_PaginaWebDD'],
            $text['sas_DescripcionESDD'],
            #$text['sas_Categoria1DD'],
            #$text['sas_Categoria2DD'],
            #$text['sas_Categoria3DD'],
            #$text['sas_Categoria4DD'],
            "ID CATEGORIA 1",
            "CATEGORIA 1",
            "ID SUBCATEGORIA 1",
            "SUBCATEGORIA 1",
            "ID CATEGORIA 2",
            "CATEGORIA 2",
            "ID SUBCATEGORIA 2",
            "SUBCATEGORIA 2",
            "ID CATEGORIA 3",
            "CATEGORIA 3",
            "ID SUBCATEGORIA 3",
            "SUBCATEGORIA 3",
            "ID CATEGORIA 4",
            "CATEGORIA 4",
            "ID SUBCATEGORIA 4",
            "SUBCATEGORIA 4",
            $text['sas_NombreCompletoCE'],
            $text['sas_NombreCE'],
            $text['sas_ApellidoPaternoCE'],
            $text['sas_ApellidoMaternoCE'],
            $text['sas_PuestoCE'],
            $text['sas_EmailCE'],
            $text['sas_EmailAlternoCE'],
            $text['sas_TelefonoCE'],
            $text['sas_StandCantidad'],
            $text['sas_StandTipo'],
            $text['sas_TotalAreaM2'],
            $text['sas_StandNumero'],
            $text['sas_StandAC'],
            $text['sas_StandBC'],
            $text['sas_StandBP'],
            $text['sas_StandCC'],
            $text['sas_StandCP'],
            $text['sas_StandDC'],
            $text['sas_StandDP'],
            $text['sas_StandEC'],
            $text['sas_StandEP'],
            $text['sas_StandFC'],
            $text['sas_StandFP'],
            $text['sas_StandGC'],
            $text['sas_StandGP'],
            $text['sas_StandM2'],
            $text['sas_StandPrecio'],
            $text['sas_StandTipoPrecio'],
            $text['sas_OpcionPagoR'],
            "Moneda",
            $text['sas_Pabellon'],
            $text['sas_PrecioStands'],
            $text['sas_PrecioDescuento'],
            $text['sas_PrecioDecoracion'],
            $text['sas_SubtotalFinal'],
            $text['sas_PrecioIVA'],
            $text['sas_PrecioConIVA'],
            $text['sas_AsesorComercial'],
            $text['sas_AccesoManualUsuario'],
            $text['sas_AccesoManualPassword'],
            $text['sas_AccesoInvitacionesUsuario'],
            $text['sas_AccesoInvitacionesPassword'],
            $text['sas_GafetesCorresponden'],
            $text['sas_InvitacionesCorresponden'],
            $text['sas_Observaciones'],
            'Empresa Padre',
            'Gafetes Edecan',
            'Gafetes Edecan Pago',
            'Catalogo Nuevos Productos',
            'Productos Vitrinas'
        );
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
        $content['headers'] = $meta_data;
        $content['data'] = $data;
        $content['title'] = 'Reporte General Expositores';
        $content['path'] = 'empresa_reportes_generalContratos';
        $content["breadcrumb"] = $this->ReportesModel->breadcrumb("empresa_reportes", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => "General Expositores", "route" => ""));
        if ($status == "descargar") {
            return $this->excelReport($data, $meta_data, $file_name);
        } else {
            return $this->render('EmpresaReportesBundle:Reportes:showReports.html.twig', Array('content' => $content));
        }
    }

    public function reportEmpresasAdicionalesAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_EmpresasAdicionales" . date('d-m-Y G.i');

        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];
        $data = $this->ReportesModel->getEmpresasAdicionales(array("idEdicion" => $idEdicion, "EmpresaAdicional" => 1), strtoupper($lang));
        foreach ($data as $key => $value) {
            $data[$key] = array_chunk($data[$key], 20);
            $temp = explode('|', $data[$key][0][19]);
            for ($i = 0; $i < 4; $i++) {
                if (array_key_exists($i, $temp))
                    $data[$key][0]['categoria_' . $i] = $temp[$i];
                else
                    $data[$key][0]['categoria_' . $i] = '';
            }
            unset($data[$key][0][19]);
            $data[$key] = array_merge($data[$key][0], $data[$key][1]);
        }
        $meta_data = array(
            $text['sas_CodigoCliente'],
            $text['sas_EmpresaTipo'],
            $text['sas_RazonSocial'],
            $text['sas_NombreComercialDD'],
            $text['sas_NombreCompletoCE'],
            $text['sas_NombreCE'],
            $text['sas_ApellidoPaternoCE'],
            $text['sas_ApellidoMaternoCE'],
            $text['sas_PuestoCE'],
            $text['sas_EmailCE'],
            $text['sas_CalleDD'],
            $text['sas_ColoniaDD'],
            $text['sas_CodigoPostalDD'],
            $text['sas_CiudadDD'],
            $text['sas_EstadoDD'],
            $text['sas_PaisDD'],
            $text['sas_TelefonoDD'],
            $text['sas_PaginaWebDD'],
            $text['sas_DescripcionESDD'],
            $text['sas_Categoria1DD'],
            $text['sas_Categoria2DD'],
            $text['sas_Categoria3DD'],
            $text['sas_Categoria4DD'],
            $text['sas_StandNumero']
        );
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
        $content['headers'] = $meta_data;
        $content['data'] = $data;
        $content['title'] = 'Reporte Empresas Adicionales';
        $content['path'] = 'empresa_reportes_empresas_adicionales';
        $content["breadcrumb"] = $this->ReportesModel->breadcrumb("empresa_reportes", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => "Empresas Adicionales", "route" => ""));
        if ($status == "descargar") {
            return $this->excelReport($data, $meta_data, $file_name);
        } else {
            return $this->render('EmpresaReportesBundle:Reportes:showReports.html.twig', Array('content' => $content));
        }
    }

    public function reportGafetesCobroAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_GafetesConCosto" . date('d-m-Y G.i');

        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];
        $data = $this->ReportesModel->getGafetesCobro(array("idEdicion" => $idEdicion), strtoupper($lang));
        foreach ($data as $key => $value) {
            unset($data[$key]['idEdicion']);
        }

        $meta_data = array(
            $text['sas_GCC_CodigoAntad'],
            $text['sas_GCC_DFRazonSocial'],
            $text['sas_GCC_DFCalle'],
            $text['sas_GCC_DFColonia'],
            $text['sas_GCC_DFCodigoPostal'],
            $text['sas_GCC_Ciudad'],
            $text['sas_GCC_DFEstado'],
            $text['sas_GCC_DFPais'],
            $text['sas_GCC_Sector'],
            $text['sas_GCC_Empresa'],
            $text['sas_GCC_Nombre'],
            $text['sas_GCC_ApellidoPaterno'],
            $text['sas_GCC_Cargo'],
            $text['sas_GCC_Area'],
            $text['sas_GCC_Email'],
            $text['sas_GCC_FechaCreacion'],
            $text['sas_GCC_FechaMod'],
            'Pago',
            'Asesor Comercial'
        );
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
        $content['headers'] = $meta_data;
        $content['data'] = $data;
        $content['title'] = 'Reporte Gafetes con Cobro';
        $content['path'] = 'empresa_reportes_gafetes_cobro';
        $content["breadcrumb"] = $this->ReportesModel->breadcrumb("empresa_reportes", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => "Gafetes con Cobro", "route" => ""));
        if ($status == "descargar") {
            return $this->excelReport($data, $meta_data, $file_name);
        } else {
            return $this->render('EmpresaReportesBundle:Reportes:showReports.html.twig', Array('content' => $content));
        }
    }

    public function reportGafetesAction(Request $request, $status) {
        ini_set('max_execution_time', 600);
        ini_set('memory_limit', '-1');
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_Gafetes" . date('d-m-Y G.i');

        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];
        $data = $this->ReportesModel->getGafetes(array("idEdicion" => $idEdicion), strtoupper($lang));
        foreach ($data as $key => $value) {
            unset($data[$key]['idEdicion']);
        }
        $meta_data = array(
            $text['sas_GCC_CodigoAntad'],
            $text['sas_GCC_DFRazonSocial'],
            $text['sas_GCC_DFCalle'],
            $text['sas_GCC_DFColonia'],
            $text['sas_GCC_DFCodigoPostal'],
            $text['sas_GCC_Ciudad'],
            $text['sas_GCC_DFEstado'],
            $text['sas_GCC_DFPais'],
            $text['sas_GCC_Sector'],
            $text['sas_GCC_Empresa'],
            $text['sas_GCC_Nombre'],
            $text['sas_GCC_ApellidoPaterno'],
            $text['sas_GCC_Cargo'],
            $text['sas_GCC_Area'],
            $text['sas_GCC_Email'],
            $text['sas_GCC_FechaCreacion'],
            $text['sas_GCC_FechaMod'],
        );

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
        $content['headers'] = $meta_data;
        $content['data'] = $data;
        $content['title'] = 'Reporte Gafetes';
        $content['path'] = 'empresa_reportes_gafetes';
        $content["breadcrumb"] = $this->ReportesModel->breadcrumb("empresa_reportes", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => "Reporte Gafetes", "route" => ""));
        if ($status == "descargar") {
            return $this->excelReport($data, $meta_data, $file_name);
        } else {
            return $this->render('EmpresaReportesBundle:Reportes:showReports.html.twig', Array('content' => $content));
        }
    }

    public function reportCambiosDirectorioAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_CambiosDirectorio" . date('d-m-Y G.i');

        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];
        $data = $this->ReportesModel->getCambiosDirectorio(array("idEdicion" => $idEdicion, "idForma" => 220), strtoupper($lang));
        $catalogoCampos = $this->ReportesModel->getCatalogos("Forma", Array("CamposJSON"), array("idEdicion" => $idEdicion, "idForma" => 220), strtoupper($lang));
        $catalogoCategorias = $this->ReportesModel->getCatalogos("Categoria", array(), array("idEdicion" => $idEdicion), strtoupper($lang));        
        $catalogoCampos = $catalogoCampos[0];
        $catalogoCampos = json_decode($catalogoCampos['CamposJSON'], true);
        $statusCambio = array(0 => "Rechazada", 1 => "Completada", 2 => "Pendiente");
        $i = 1;
        $categorias = array();
        foreach ($catalogoCategorias as $key1 => $value1) {
            $categorias[$value1['idCategoria']] = $value1;
        }
        
        foreach ($data as $key => $value) {
            $detalleForma = json_decode($data[$key]['DetalleForma'], true);            
            if (isset($detalleForma) && !empty($detalleForma)) {                
                foreach ($detalleForma as $key2 => $value2) {
                    if(!empty($detalleForma[$key2]['FechaSolicitudCambio'])){
                    $data2[$i]['CodigoCliente'] = $data[$key]['CodigoCliente'];
                    $data2[$i]['DF_RazonSocial'] = $data[$key]['DF_RazonSocial'];
                    $data2[$i]['DC_NombreComercial'] = $data[$key]['DC_NombreComercial'];
                    $data2[$i]['CampoCambio'] = $catalogoCampos[$detalleForma[$key2]['idCampoModificacion']]['NombreCampo' . strtoupper($lang)];
                    $data2[$i]['CategoriaSecundariaCambio'] = $categorias[$detalleForma[$key2]['CategoriaSecundaria']]['NombreCategoria' . strtoupper($lang)];
                    $data2[$i]['Observacion'] = $detalleForma[$key2]['Observacion'];
                    $data2[$i]['StatusSolicitud'] = $status[$detalleForma[$key2]['StatusSolicitudCambio']];
                    $data2[$i]['FechaSolicitudCambio'] = $detalleForma[$key2]['FechaSolicitudCambio'];
                    $i = $i + 1;
                    }
                }
            }
        }


        $meta_data = array(
            $text['sas_GCC_CodigoAntad'],
            $text['sas_GCC_DFRazonSocial'],
            $text['sas_NombreComercialDD'],
            $text['sas_CampoCambio'],
            $text['sas_CategoriaSecundariaCambio'],
            $text['sas_report_Observacion'],
            $text['sas_StatusSolicitud'],
            $text['sas_FechaSolicitudCambio']
        );
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
        $content['headers'] = $meta_data;
        $content['data'] = $data2;
        $content['title'] = 'Reporte Cambios Directorio';
        $content['path'] = 'empresa_reportes_cambios_directorio';
        $content["breadcrumb"] = $this->ReportesModel->breadcrumb("empresa_reportes", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => "Cambios Directorio", "route" => ""));
        if ($status == "descargar") {
            return $this->excelReport($data2, $meta_data, $file_name);
        } else {
            return $this->render('EmpresaReportesBundle:Reportes:showReports.html.twig', Array('content' => $content));
        }
    }

    public function reportCatalogoProductosAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_ReporteCatalogoProductos" . date('d-m-Y G.i');

        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];

        $fields = array(
            "CodigoCliente",
            "DF_RazonSocial",
            "EMSTDListadoStand",
            "Titulo",
            "NombreCategoriaES",
            "categoriasecundaria",
            "DC_NombreComercial",
            "tipoproducto"
        );

        $data = $this->ReportesModel->getCatalogoProductos($fields, array("idEdicion" => $idEdicion, "idForma" => 218), strtoupper($lang));


        $meta_data = array(
            $text['sas_GCC_CodigoAntad'],
            $text['sas_GCC_DFRazonSocial'],
            $text['sas_StandNumero'],
            $text['sas_TituloProducto'],
            $text['sas_Categoria1Producto'],
            $text['sas_Categoria2Producto'],
            $text['sas_DCNombreComercialProducto'],
            $text['sas_TipoProducto']
        );
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
        $content['headers'] = $meta_data;
        $content['data'] = $data;
        $content['title'] = 'Reporte Catalogo de Productos';
        $content['path'] = 'empresa_reportes_catalogo_productos';
        $content["breadcrumb"] = $this->ReportesModel->breadcrumb("empresa_reportes", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => "Catalogo de Productos", "route" => ""));
        if ($status == "descargar") {
            return $this->excelReport($data, $meta_data, $file_name);
        } else {
            return $this->render('EmpresaReportesBundle:Reportes:showReports.html.twig', Array('content' => $content));
        }
    }

    public function reportProductosVitrinasAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_NuevosProductos" . date('d-m-Y G.i');

        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];


        $fields = array(
            "CodigoCliente",
            "DF_RazonSocial",
            "EMSTDListadoStand",
            "Titulo",
            "NombreCategoriaES",
            "categoriasecundaria",
            "Empresa",
            "medidas",
            "DPPeso",
            "tipoproducto"
        );
        $data = $this->ReportesModel->getCatalogoProductos($fields, array("idEdicion" => $idEdicion, "idForma" => 237), strtoupper($lang));
        $meta_data = array(
            $text['sas_GCC_CodigoAntad'],
            $text['sas_GCC_DFRazonSocial'],
            $text['sas_StandNumero'],
            $text['sas_TituloProducto'],
            $text['sas_Categoria1Producto'],
            $text['sas_Categoria2Producto'],
            $text['sas_DCNombreComercialProducto'],
            $text['sas_medidasVitrina'],
            $text['sas_PesoVitrinas'],
            $text['sas_TipoProducto']
        );
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
        $content['headers'] = $meta_data;
        $content['data'] = $data;
        $content['title'] = 'Reporte Catalogo de Vitrinas';
        $content['path'] = 'empresa_reportes_productos_vitrinas';
        $content["breadcrumb"] = $this->ReportesModel->breadcrumb("empresa_reportes", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => "Catalogo de Vitrinas", "route" => ""));
        if ($status == "descargar") {
            return $this->excelReport($data, $meta_data, $file_name);
        } else {
            return $this->render('EmpresaReportesBundle:Reportes:showReports.html.twig', Array('content' => $content));
        }
    }

    public function excelReport($general, $table_metadata, $filename) {
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
            }$flag++;
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

    public function reportInventarioStandsAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_ReporteInventarioStands_" . date('d-m-Y G.i');

        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];
        $data = $this->ReportesModel->getStands(array("idEdicion" => $idEdicion));
        $meta_data = array(
            $text['sas_tipoStand'],
            $text['sas_salonJaliscoStand'],
            $text['sas_salonJaliscoMetros'],
            $text['sas_salonInternacionalStand'],
            $text['sas_salonInternacionalMetros'],
//            $text['sas_alimentosBebidasStand'],
//            $text['sas_alimentosBebidasMetros'],
//            $text['sas_mercaciasGeneralesStand'],
//            $text['sas_mercanciasGeneralesMetros'],
//            $text['sas_mobiliarioEquipamientoStand'],
//            $text['sas_mobiliarioEquipamientoMetros'],
//            $text['sas_higieneSaludCuidadStand'],
//            $text['sas_higieneSaludCuidadMetros'],
//            $text['sas_tegnologiaStand'],
//            $text['sas_tegnologiaMetros'],
//            $text['sas_alimentosBebidasInternacionalStand'],
//            $text['sas_alimentosBebidasInternacionalMetros'],
            $text['sas_vestibuloStand'],
            $text['sas_vestibuloMetros'],
            $text['sas_vestibuloStandOp2'],
            $text['sas_vestibuloMetrosOp2'],
            $text['sas_vestibuloStandOp3'],
            $text['sas_vestibuloMetrosOp3'],
            $text['sas_vestibuloStandOp4'],
            $text['sas_vestibuloMetrosOp4']
//            $text['sas_transporteStand'],
//            $text['sas_transporteMetros']
        );
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
        $content['headers'] = $meta_data;
        $content['data'] = $data;
        $content['title'] = 'Reporte Inventario Stands';
        $content['path'] = 'empresa_reportes_inventario_stands';
        $content["breadcrumb"] = $this->ReportesModel->breadcrumb("empresa_reportes", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => "Inventario Stands", "route" => ""));
        if ($status == "descargar") {
            return $this->excelReport($data, $meta_data, $file_name);
        } else {
            return $this->render('EmpresaReportesBundle:Reportes:showReports.html.twig', Array('content' => $content));
        }
    }

    public function reportPabellonesAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_ReportePabellones" . date('d-m-Y G.i');

        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];
        $data = $this->ReportesModel->getPabellones(array("idEdicion" => $idEdicion));
        foreach ($data as $key => $value) {
            $data2['linea0 ' . $data[$key]['DescripcionPabellon']]['DescripcionPabellon'] = $data[$key]['DescripcionPabellon'];
            $data2['linea0 ' . $data[$key]['DescripcionPabellon']]['standstotales'] = $data[$key]['standstotales'];
            $data2['linea0 ' . $data[$key]['DescripcionPabellon']]['m2totales'] = $data[$key]['m2totales'];
            $data2['linea0 ' . $data[$key]['DescripcionPabellon']]['preciototales'] = $data[$key]['preciototales'];
            $data2['linea1 ' . $data[$key]['DescripcionPabellon']]['reservados'] = "Reservados";
            $data2['linea1 ' . $data[$key]['DescripcionPabellon']]['standReservados'] = $data[$key]["standsreservados"];
            $data2['linea1 ' . $data[$key]['DescripcionPabellon']]['m2Reservados'] = $data[$key]["m2reservados"];
            $data2['linea1 ' . $data[$key]['DescripcionPabellon']]['PrecioReservados'] = $data[$key]["precioreservados"];
            $data2['linea2 ' . $data[$key]['DescripcionPabellon']]['contratados'] = "Contratados";
            $data2['linea2 ' . $data[$key]['DescripcionPabellon']]['standContratados'] = $data[$key]["standscontratados"];
            $data2['linea2 ' . $data[$key]['DescripcionPabellon']]['m2Contratados'] = $data[$key]["m2contratados"];
            $data2['linea2 ' . $data[$key]['DescripcionPabellon']]['PrecioContratados'] = $data[$key]["preciocontratados"];
            $data2['linea3 ' . $data[$key]['DescripcionPabellon']]['libre'] = "Por Colocar";
            $data2['linea3 ' . $data[$key]['DescripcionPabellon']]['standLibres'] = $data[$key]["standslibres"];
            $data2['linea3 ' . $data[$key]['DescripcionPabellon']]['m2Libres'] = $data[$key]["m2libres"];
            $data2['linea3 ' . $data[$key]['DescripcionPabellon']]['PrecioLibres'] = $data[$key]["preciolibres"];
            $data2['linea4 ' . $data[$key]['DescripcionPabellon']]['SaltoLinea1'] = '';
            $data2['linea4 ' . $data[$key]['DescripcionPabellon']]['SaltoLinea2'] = '';
            $data2['linea4 ' . $data[$key]['DescripcionPabellon']]['SaltoLinea3'] = '';
            $data2['linea4 ' . $data[$key]['DescripcionPabellon']]['SaltoLinea4'] = '';
        }

        $meta_data = array(
            $text['sas_Pabellon'],
            $text['sas_Stands'],
            $text['sas_MetrosCuadrados'],
            $text['sas_Importe']
        );
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
        $content['headers'] = $meta_data;
        $content['data'] = $data2;
        $content['title'] = 'Reporte Pabellones';
        $content['path'] = 'empresa_reportes_inventario_pabellones';
        $content["breadcrumb"] = $this->ReportesModel->breadcrumb("empresa_reportes", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => "Reporte Pabellones", "route" => ""));
        if ($status == "descargar") {
            return $this->excelReport($data2, $meta_data, $file_name);
        } else {
            return $this->render('EmpresaReportesBundle:Reportes:showReports.html.twig', Array('content' => $content));
        }
    }

    public function reportMontajeAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_ReporteMontaje_" . date('d-m-Y G.i');

        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];
        $data = $this->ReportesModel->getMontaje(array("idEdicion" => $idEdicion));
        
        $meta_data = array(
            $text['sas_codigoCliente'],
            $text['sas_numeroStand'],
            $text['sas_nombreComercial'],
            "CE_Nombre",
            "CE_Cargo",
            "CE_Mail",
            "CE_Celular",
            $text['sas_tipoEspacio'],
//            $text['sas_rotulo'],
            "Mampara",
            "Marquesina",
            "Lámpara Slim Line",
            "Contacto eléctrico",
            "Bote de basura",
            "Contacto eléctrico",
            "Bote de basura",
            $text['sas_observaciones'],
            $text['sas_segundoPiso'],
            $text['sas_tipoProveedor'],
            "REMO_EmpresaMontado:Empresa",
            "REMO_EmpresaMontado:Responsable",
            "REMO_EmpresaMontado:Cargo",
            "REMO_EmpresaMontado:Celular",
            "REMO_EmpresaMontado:Telefono Oficina",
            "REMO_EmpresaMontado:Email",
            "REMO_EmpresaMontado:Nombre_Es",
            "REMO_EmpresaMontado:CantidadVehiculos"
        );
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
        $content['headers'] = $meta_data;
        $content['data'] = $data;
        $content['title'] = 'Ficha de Montaje';
        $content['path'] = 'empresa_reportes_montaje';
        $content["breadcrumb"] = $this->ReportesModel->breadcrumb("empresa_reportes", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => "Ficha de Montaje", "route" => ""));
        if ($status == "descargar") {
            return $this->excelReport($data, $meta_data, $file_name);
        } else {
            return $this->render('EmpresaReportesBundle:Reportes:showReports.html.twig', Array('content' => $content));
        }
    }

    public function reportStandPabellonAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_ReporteStandPabellon_" . date('d-m-Y G.i');

        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];
        $data = $this->ReportesModel->getStandPabellon(array("idEdicion" => $idEdicion));
        $meta_data = array(
            $text['sas_codigoCliente'],
            $text['sas_nombreComercial'],
            "Alto",
            "Ancho",
            $text['sas_areaTotal'],
            $text['sas_numeroStand'],
            $text['sas_estatus'],
            $text['sas_Pabellon']
        );
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
        $content['headers'] = $meta_data;
        $content['data'] = $data;
        $content['title'] = 'Stand Pabellón';
        $content['path'] = 'empresa_reportes_stand_pabellon';
        $content["breadcrumb"] = $this->ReportesModel->breadcrumb("empresa_reportes", $lang);
        array_push($content["breadcrumb"], Array("breadcrumb" => "Stand Pabellón", "route" => ""));
        if ($status == "descargar") {
            return $this->excelReport($data, $meta_data, $file_name);
        } else {
            return $this->render('EmpresaReportesBundle:Reportes:showReports.html.twig', Array('content' => $content));
        }
    }

    public function reportExpositorPDFAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_Expositores " . date('d-m-Y G.i');


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
        $content['idEdicion'] = $idEdicion;

        $data = $this->ReportesModel->getExpositoresPDF(array("idEdicion" => $idEdicion, "idStatusContrato" => 4), strtoupper($lang));
        foreach ($data as $key => $value) {
            if($value['DD_PaginaWeb'] == ''){
                $data[$key]['DD_PaginaWeb'] = $data[$key]['DC_PaginaWeb'];
            }
        }
        
        $content['data'] = $data;

        #print_r($content['data']);die(' <=Exp_Alfabetico');
        if ($status == "descargar") {
            return $this->render('EmpresaReportesBundle:Reportes:reporteExpositorAlfabeticoPDF.html.twig', array('content' => $content));
        } else {
            $response = new Response(json_encode(Array('headers' => $meta_data, "data" => $data)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    public function reportCategoriasPDFAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_Expositores " . date('d-m-Y G.i');


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
        $content['idEdicion'] = $idEdicion;

        $catalogoCategorias = $this->ReportesModel->getCatalogoCategorias(array("idEdicion" => $idEdicion));

        $categorias = array();
        foreach ($catalogoCategorias as $key1 => $value1) {
            $categorias[$value1['idCategoria']] = $value1;
        }

        $data = $this->ReportesModel->getEmpresaCategoriaPDF(array("idEdicion" => $idEdicion), strtoupper($lang));

        foreach ($data AS $key => $value) {
            $data1[$categorias[$value['idCategoria']]['idPadre']][$value['idCategoria']][$value['DC_NombreComercial']] = $value;
        }

        $content['data'] = $data1;
        $content['catalogo'] = $categorias;

        if ($status == "descargar") {
            return $this->render('EmpresaReportesBundle:Reportes:reporteCategoriasPDF.html.twig', array('content' => $content));
        } else {
            $response = new Response(json_encode(Array('headers' => $meta_data, "data" => $data)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    public function reportExpositorStandPDFAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_Stand " . date('d-m-Y G.i');


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
        $content['idEdicion'] = $idEdicion;

        $data = $this->ReportesModel->getExpositoresListadoStandPDF(array("idEdicion" => $idEdicion, "idStatusContrato" => 4), strtoupper($lang));

        $content['data'] = $data;        
        if ($status == "descargar") {
            return $this->render('EmpresaReportesBundle:Reportes:reporteExpositorStandPDF.html.twig', array('content' => $content));            
        } else {
            $response = new Response(json_encode(Array('headers' => $meta_data, "data" => $data)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    public function reportExpositorPaisPDFAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_Stand " . date('d-m-Y G.i');


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
        $content['idEdicion'] = $idEdicion;

        $data = $this->ReportesModel->getExpositoresPaisPDF(array("idEdicion" => $idEdicion, "idStatusContrato" => 4), strtoupper($lang));

        foreach ($data as $key => $value) {
            $data1[$value['DC_Pais']][$value['DC_NombreComercial']] = $value;
        }

        $content['data'] = $data1;
        $content['limite'] = count($data1);
        if ($status == "descargar") {
            return $this->render('EmpresaReportesBundle:Reportes:reportePaisPDF.html.twig', array('content' => $content));
        } else {
            $response = new Response(json_encode(Array('headers' => $meta_data, "data" => $data)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    public function reportExpositorPabellonPDFAction(Request $request, $status) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = str_replace(" ", "_", $session->get('edicion')["Edicion_EN"]) . "_Pabellon " . date('d-m-Y G.i');


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
        $content['idEdicion'] = $idEdicion;

        $catalogo = $this->ReportesModel->getCatalogoPabellones();

        foreach ($catalogo as $key => $value) {
            $catalogoPabellon[$value['idPabellon']] = $value;
        }


        $data = $this->ReportesModel->getExpositoresPabellonPDF(array("idEdicion" => $idEdicion, "idStatusContrato" => 4), strtoupper($lang));
        foreach ($data as $key => $value) {
            $temp = explode(',', $value['cadenapabellon']);
            for ($i = 0; $i < count($temp); $i++) {
                $data1[$value['DC_NombreComercial'] . '_' . $i] = array("DC_NombreComercial" => $value['DC_NombreComercial'], "EMSTDListadoStand" => $value['EMSTDListadoStand'], "cadenapabellon" => $temp[$i]);
            }
        }
        foreach ($data1 as $key => $value) {
            $data2[$value['cadenapabellon']][$value['DC_NombreComercial']] = $value;
        }

        $content['catalogo'] = $catalogoPabellon;
        $content['data'] = $data2;
        $content['limite'] = count($data2);
        if ($status == "descargar") {
            return $this->render('EmpresaReportesBundle:Reportes:reportePabellonPDF.html.twig', array('content' => $content));
        } else {
            $response = new Response(json_encode(Array('headers' => $meta_data, "data" => $data)));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    public function totalStandsAction(Request $request) {
        $session = $request->getSession();
        $lang = $session->get("lang");
        $idEvento = $session->get('idEvento');
        $idEdicion = $session->get('idEdicion');

        date_default_timezone_set("America/Mexico_City");
        $App = $this->get('ixpo_configuration')->getApp();
        $file_name = "Total_Stands-" . date('d-m-Y G.i');

        $section_text = $this->TextoModel->getTexts($lang, self::SECTION);
        if (!$section_text['status']) {
            throw new \Exception($section_text['data'], 409);
        }
        $text = $section_text['data'];

        if ($idEdicion == 6)
            $editions = Array(6);
        if ($idEdicion == 7)
            $editions = Array(7);

//        if ($idEdicion = 5 || $idEdicion = 26)
//            $editions = Array(25, 26);
//        if ($idEdicion = 27 || $idEdicion = 28)
//            $editions = Array(27, 28);

        $data = $this->ReportesModel->getTotalStands(array("editions" => $editions), strtoupper($lang));

        foreach ($data as $key => $value) {
            if ($value['idEdicion'] == 6)
                $data[$key]['idEdicion'] = "ANTAD 2017";
            if ($value['idEdicion'] == 7)
                $data[$key]['idEdicion'] = "ANTAD 2018";
        }

        $meta_data = array(
            "Edicion",
            "Stand ID",
            "Numero Stand",
            "Status",
            "Alto",
            "Ancho",
            "Area",
            "Etiqueta Stand",
            "Empresa ID",
            "Nombre Comercial"
        );
        return $this->excelReport($data, $meta_data, $file_name);
    }

}
