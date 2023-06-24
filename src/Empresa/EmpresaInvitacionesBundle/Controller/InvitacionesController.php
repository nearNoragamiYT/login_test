<?php
namespace Empresa\EmpresaInvitacionesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Utilerias\TextoBundle\Model\TextoModel;
use Empresa\EmpresaInvitacionesBundle\Model\InvitacionesModel;
use Empresa\EmpresaInvitacionesBundle\Model\InvitacionesConfiguration;

class InvitacionesController extends Controller
{
    protected $TextoModel, $InvitacionesModel;
    const SECTION = 4;
    const MAIN_ROUTE = "empresa-invitaciones";
    
    public function __construct() {
        $this->TextoModel = new TextoModel();
        $this->InvitacionesModel = new InvitacionesModel();
        $this->InvitacionesConfiguration = new InvitacionesConfiguration();
    }

    public function getInvitacionesAction(Request $request, $idEmpresa)
    {
        $form = 900;
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();

        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');

        /* Construimos content array */
        $content = Array();
        $content["lang"] = $lang;         
        $content['idForm'] = $form;
        $content['lang'] = $lang;
        $content['idEmpresa'] = $idEmpresa;                    
        $content['idUsuario'] = $user["idUsuario"]; 
        
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

        /* Obtenemos los header */
        $args = Array('e."idEmpresa"' => $idEmpresa, 'ee."idEdicion"' => $idEdicion);
        $content["header"] = $this->InvitacionesModel->getCompanyHeader($args);
        
        /* Obtenemos los paquetes */
        $args = Array('p."idEdicion"' => $idEdicion);
        $content["packages"] = $this->InvitacionesModel->getPackages($args); 
        
        /* Obtenemos catalogo StatusCupon */
        $codeStatus = $this->InvitacionesModel->getCodeStatus();
        if (!$codeStatus['status']) {
            throw new \Exception($codeStatus['data'], 409);
        }
        $content["codes_status"] = $codeStatus['data'];
        
        /* Obtenemos VisitantesCupon */
        $codeVisitors = $this->InvitacionesModel->getCodeVisitors();
        if (!$codeVisitors['status']) {
            throw new \Exception($codeVisitors['data'], 409);
        }
        $content["codes_visitors"] = $codeVisitors['data'];
//        print_r($content["codes_visitors"]);
//        die('x_x');
        
        
        /* Obtenemos Invitaciones/Cupones por Empresa*/
        $args = Array('idEmpresa'=>$idEmpresa,'idEvento'=>$idEvento,'idEdicion'=>$idEdicion);
        $codes = $this->InvitacionesModel->getCodes($args);
        if (!$codes['status']) {
            throw new \Exception($codes['data'], 409);
        }
        $content['codes'] = $codes['data'];
//   
        $args = Array('idForma' => $form, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion);
        $campos_forma = $this->InvitacionesModel->getCamposForma($args);       
        $campos_forma=$campos_forma[0];     
        $content['camposforma'] = json_decode($campos_forma['CamposJSON'], true);
        
        /* Verificamos si tiene permiso en el modulo seleccionado */
        if ($session->get("companyOrigin") == "solicitud_lectoras") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "solicitud_lectora_reporte");
        }
        if ($session->get("companyOrigin") == "lectoras") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "show_dashboard_lt");
        }
        if ($session->get("companyOrigin") == "lectoras_simple") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "show_dashboard_lt_sf");
        }
        if ($session->get("companyOrigin") == "ventas") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "empresa_ventas");
        }
        if ($session->get("companyOrigin") == "expositores") {
            $breadcrumb = $this->get("verificador_modulo")->rastrearBreadcrumbs($request, "empresa");
        }
        if (!$breadcrumb) {
            $session->getFlashBag()->add('warning', $general_text['sas_moduloNoDisponible']);
            return $this->redirectToRoute('show_dashboard_edicion', array('idEdicion' => $session->get('idEdicion'), 'lang' => $lang));
        }
        $content["breadcrumb"] = $breadcrumb;
        
        //array_push($content["breadcrumb"], Array("breadcrumb" => $content["header"]["DC_NombreComercial"], "route" => ""));
        
        array_push($content["breadcrumb"], Array("Modulo_" . strtoupper($lang) => $content["header"]["DC_NombreComercial"], "Ruta" => "", 'Permisos' => array()));
        $content['companyOrigin'] = $session->get("companyOrigin");
        
         /* ---  Invitations Metadata (para construir la tabla) --- */
        $invitations_table_metadata = $this->InvitacionesConfiguration->getInvitationsMetaData($content['section_text'],$content['general_text']);
        $content["invitations_table_metadata"] = $invitations_table_metadata; 
                
        return $this->render('EmpresaEmpresaInvitacionesBundle:InvitacionesElectronicas:invitaciones_lista.html.twig', array('content'=>$content));
    }
    
    public function generateInvitationsAction(Request $request, $idEmpresa){
        $this->InvitacionesModel = new InvitacionesModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $get = $request->query->all();

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
        //Contruye Invitaciones
        if ($get['InvitationsNumber']) {
        $invitationsNumber = $get['InvitationsNumber'];
        
        /* Método para Generar Invitaciones por PGSQL */
        $args = Array('idEmpresa' => $idEmpresa, 'idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'invitationsNumber'=>$invitationsNumber);
        $result_generateCodes = $this->InvitacionesModel->generateCodes($args);
        if (!$result_generateCodes['status']) {
           throw new \Exception($result_generateCodes['data'], 409);
        }
        $session->getFlashBag()->add('success', 'Invitaciones generadas correctamente');
        return $this->redirectToRoute('empresa_empresa_invitaciones', array('idEmpresa' => $idEmpresa));     
        
        /* Método para Generar Invitaciones por PHP 
            $invitations_array = array();
            for($i = 0; $i < $get['InvitationsNumber']; $i++){ 
                //funcion para generar invitación
                $invitation = $this->generateInvitations(); 
                // Inserción en la tabla Cupon 
                $codeData = Array(
                    'idCuponCategoria' => '1',
                    'idEmpresa' => $idEmpresa,
                    'Cupon' => "'".$invitation."'",
                    'DescuentoCupon' => 'true',
                    'idCuponStatus' => '1',
                    'idEvento' => $idEvento,
                    'idEdicion' => $idEdicion
                        //'LinkCupon' => $post['LinkCupon'],
                );
                $merge = $codeData;                
                $result_insertCode = $this->InvitacionesModel->insertCode($codeData);
                
                //Inserción en la tabla CuponDescuento
                $codeDiscountData = Array(
                    'idCupon' => $result_insertCode['data'][0]['idCupon'],
                    'DescuentoFinal' => 'true',
                    'idDescuentoTipo' => 1,
                    'Porcentaje' => 100,
                );
                $merge = array_merge($merge, $codeDiscountData);  
                
                //Inserción en la tabla CuponDescuento
                $result_insertDiscount = $this->InvitacionesModel->insertDiscount($codeDiscountData);
                if (!$result_insertDiscount['status']) {
                    throw new \Exception($result_insertDiscount['data'], 409);
                }
                $merge = array_merge($merge, $result_insertDiscount['data'][0]);                
                $result_insertDiscount['data'] = $merge;
                $result=$result_insertDiscount;                                                                
                $invitations_array[]=$invitation;  
            }
            $session->getFlashBag()->add('success', 'Invitaciones generadas correctamente');
            return $this->redirectToRoute('empresa_empresa_invitaciones', array('idEmpresa' => $idEmpresa));
        */
            
        }                
    }
    
    public function generateInvitations() {
        //Método rand()     
                $length = 4;
                $possibleChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $str_letters = '';
                for ($i = 0; $i < $length; $i++) {
                    $rand = rand(0, strlen($possibleChars) - 1);
                    $str_letters .= substr($possibleChars, $rand, 1);
                }

                $length = 5;
                $possibleChars = "0123456789";
                $str_number = '';
                for ($i = 0; $i < $length; $i++) {
                    $rand = rand(0, strlen($possibleChars) - 1);
                    $str_number .= substr($possibleChars, $rand, 1);
                }
                return  $invitation = $str_letters.$str_number ;                
    }
    
    public function updateCodeStatusAction(Request $request, $idEmpresa){
        $this->InvitacionesModel = new InvitacionesModel($this->container);
        $session = $request->getSession();
        $lang = $session->get('lang');
        $App = $this->get('ixpo_configuration')->getApp();
        $profile = $this->getUser();
        $user = $profile->getData();
        $idEvento = $session->get('edicion')["idEvento"];
        $idEdicion = $session->get('idEdicion');
        $get = $request->query->all();
        
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
        
        if ($get['idCuponStatus']) {
            $args = Array('idEvento' => $idEvento, 'idEdicion' => $idEdicion, 'idEmpresa' => $idEmpresa,'idCupon' => $get['idCupon']);
            $result_codeStatus = $this->InvitacionesModel->updateCancelCode($args);                    
        }       
        if (!$result_codeStatus['status']) {
            throw new \Exception($result_codeStatus['data'], 409);
        }else{
            $cancelCode = $get['idCupon'];
            $newCode= $result_codeStatus['data'][0]['fn_ae_CancelaInvitacion'];
            $session->getFlashBag()->add('success', 'La invitación con ID: '.$cancelCode.', se ha cancelado correctamente. Se ha generado la invitación con ID: '.$newCode );
            return $this->redirectToRoute('empresa_empresa_invitaciones', array('idEmpresa' => $idEmpresa));
        }
    }
    
    public function deleteCodeAction(Request $request, $idEmpresa) {
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

            $result = $this->InvitacionesModel->deleteCode($post);
            $result['data']= $post['idCupon'];           
            return $this->jsonResponse($result);
        }
    }
    
    protected function jsonResponse($data) {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    

}



