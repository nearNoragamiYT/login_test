<?php

namespace Empresa\EmpresaInvitacionesBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class InvitacionesModel extends DashboardModel{
    
    protected $SQLModel, $PGSQLModel;
    protected $path_cache_ed = "../app/cache/ed/";

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }
    
    public function getCompanyHeader($args = "") {
        $qry = ' SELECT e."idEmpresa", e."DC_NombreComercial"';
        $qry .= ' FROM "SAS"."Empresa" e';
        $qry .= ' WHERE e."idEmpresa" = ' . $args['e."idEmpresa"'];
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $qry = ' SELECT ee."idEtapa", ee."EMSTDListadoStand", ee."idPaquete"';
            $qry .= ' FROM "SAS"."EmpresaEdicion" ee';
            $qry .= ' WHERE ee."idEmpresa" = ' . $args['e."idEmpresa"'] . ' AND ee."idEdicion" = ' . $args['ee."idEdicion"'];
            $result2 = $this->SQLModel->executeQuery($qry);

            if (isset($result2['status']) && $result2['status'] == 1 && isset($result2['data'][0])) {
                $data = array_merge($result["data"][0],$result2["data"][0]);
                return $data;
            }
            else{
                return $result["data"][0];
            }
            
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }
    
    public function getPackages($args){
        $qry = ' SELECT ' . $this->getPackagesFields();
        $qry .= ' FROM "SAS"."Paquete" p';
        $qry .= ' WHERE p."idEdicion" = ' . $args['p."idEdicion"'];
        $qri .= ' ORDER BY p."idPaquete"';
        $result= $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idPaquete']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }
    private function getPackagesFields(){
        $fields = '';
        $fields .= ' p."idPaquete",';
        $fields .= ' p."PaqueteES",';
        $fields .= ' p."PaqueteEN",';
        $fields .= ' p."PaquetePT",';
        $fields .= ' p."PaqueteFR" ';
        return $fields;
    }
    
    public function getCamposForma($args) {
        $qry = 'SELECT "CamposJSON" ';
        $qry .= 'FROM "SAS"."Forma" ';
        $qry .= 'WHERE "idForma" = ' . $args["idForma"] . ' AND ';       
        $qry .= '"idEvento" = ' . $args['idEvento'] . ' AND ';
        $qry .= '"idEdicion" = ' . $args['idEdicion'];
        $qry .= ';';
        $result = $this->SQLModel->executeQuery($qry);
        
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result['data'];
    }
    
    public function getCodeStatus() {

        $fields = $this->getCodeStatusFields();
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->selectFromTable('CuponStatus', $fields);
        $this->SQLModel->setSchema("SAS");
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idCuponStatus']] = $value;
        }        
        $result['data'] = $data;

        return $result;
    }
    
    protected function getCodeStatusFields(){
        return Array(
            'idCuponStatus',
            'CuponStatusES',
            'CuponStatusEN',
        );
    }
    
    public function getCodeVisitors() {

        $fields = $this->getCodeVisitorsFields();
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->selectFromTable('VisitanteCupon', $fields);
        $this->SQLModel->setSchema("SAS");
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idCupon']] = $value;
        }        
        $result['data'] = $data;

        return $result;
    }
    
    protected function getCodeVisitorsFields(){
        return Array(
            'idVisitanteCupon',
            'idVisitante',
            'idCupon',            
        );
    }
    
    public function getCodes($params) {
        $this->SQLModel->setSchema("AE");
        $fields = $this->getCodesFields();
        $qry = 'SELECT '.$fields.'';        
        $qry.=' FROM "AE"."Cupon" c';       
        $qry.=' WHERE "idEvento" ='.$params['idEvento'].' AND "idEdicion" ='.$params['idEdicion'].' AND "idEmpresa" ='.$params['idEmpresa']; 
        $result = $this->SQLModel->executeQuery($qry);   
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idCupon']] = $value;
        }
        $result['data'] = $data;

        return $result;
    }
    
     protected function getCodesFields() {
        $fields = '';
        $fields .= 'c."idCupon",';        
        $fields .= 'c."Cupon",';       
        //$fields .= 'c."DescripcionES",';
        //$fields .= 'c."DescripcionEN",';                     
        $fields .= 'c."idCuponCategoria",';
        $fields .= 'c."idCuponStatus",';  
        $fields .= 'c."FechaCreacion"';
        return $fields;
    }
    
    public function generateCodes($args){ 
        $this->SQLModel->setSchema("AE");
        $qry = 'SELECT * FROM "AE"."fn_ae_GenerarCuponesElectronicos"(';
        $qry .= $args['idEvento'] . ', ';
        $qry .= $args['idEdicion'] . ', ';
        $qry .= $args['idEmpresa'] . ', ';
        $qry .= $args['invitationsNumber']. '); ';    

        $res_pg = $this->SQLModel->executeQuery($qry);
        $this->SQLModel->setSchema("SAS");
        
        return $res_pg;                                        
    }
    
    public function insertCode($data) {        
        $this->SQLModel->setSchema("AE");
        return $this->SQLModel->insertIntoTable('Cupon', $data, "idCupon");
        $this->SQLModel->setSchema("SAS");
    }
    
    public function insertDiscount($data) {
        $this->SQLModel->setSchema("AE");
        return $this->SQLModel->insertIntoTable('CuponDescuento', $data, "idCuponDescuento");
        $this->SQLModel->setSchema("SAS");
    }
    
    public function updateCodeStatus($idCupon, $idCuponStatus) {
        $args = array('idCupon' => $idCupon);
        $values = array(
            'idCuponStatus' => $idCuponStatus,
        );
        $this->SQLModel->setSchema("AE");
        return $this->SQLModel->updateFromTable("Cupon", $values, $args, 'idCupon');
        $this->SQLModel->setSchema("SAS");
    }
    
    public function updateCancelCode($args){ 
        $this->SQLModel->setSchema("AE");
        $qry = 'SELECT * FROM "AE"."fn_ae_CancelaInvitacion"(';
        $qry .= $args['idEvento'] . ', ';
        $qry .= $args['idEdicion'] . ', ';
        $qry .= $args['idEmpresa'] . ', ';
        $qry .= $args['idCupon']. '); ';    

        $res_pg = $this->SQLModel->executeQuery($qry);
        $this->SQLModel->setSchema("SAS");
        
        return $res_pg;                                        
    }
    
    
    public function deleteCode($data) {  
        $this->SQLModel->setSchema("AE");
        //Elimina registro de cuponDescuento
        $result_codeDiscount = $this->SQLModel->deleteFromTable('CuponDescuento', array("idCupon" => $data['idCupon']));   
        if (($result_codeDiscount['status'])) {
            //Elimina registro de Cupon
            $result_code = $this->SQLModel->deleteFromTable('Cupon', array("idCupon" => $data['idCupon']));        
            if (($result_code['status'])) {
                $this->SQLModel->setSchema("SAS");
                return $result_code;
            }else{
                throw new \Exception($result_code['data'], 409);
            }        
        }else{
            throw new \Exception($result_codeDiscount['data'], 409);
        }        
    }    
    
//    public function deleteContact($args) {
//        $qry = 'SELECT * FROM "SAS"."fn_sas_EliminaContacto"(';
//        $qry .= "'" . $args['idContacto'] . "',";
//        $qry .= "'" . $args['idEmpresa'] . "',";
//        $qry .= "'" . $args['idEvento'] . "',";
//        $qry .= "'" . $args['idEdicion'] . "');";
//        $result = $this->SQLModel->executeQuery($qry);
//
//        return $result;
//    }

}

