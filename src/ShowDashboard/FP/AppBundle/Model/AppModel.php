<?php

namespace ShowDashboard\FP\AppBundle\Model;

use Utilerias\PostgreSQLBundle\v9\PGSQLClient;

class AppModel {
    
    protected  $SQLModel;

    public function __construct() {
        $this->SQLModel = new PGSQLClient();
    }

    public function getCategoriasCSV($param) {
        $qry = 'SELECT';
        $qry .= ' "idCategoria",';
        $qry .= ' "idPadre",';
        $qry .= ' "NombreCategoriaES",';
        $qry .= ' "NombreCategoriaEN"';
        $qry .= ' FROM ';
        $qry .= ' "SAS"."Categoria"';
        $qry .= ' WHERE ';
        $qry .= ' "idEvento"= ' . $param['idEvento'] . ' AND ';
        $qry .= ' "idEdicion"  = ' . $param['idEdicion'] . '';

        $result = $this->SQLModel->execQueryString($qry);

        return $result;
    }
    
        public function getQueryWS($idEdicion) {
        $qry = 'SELECT';
        $qry .= ' "idEmpresa",';
        $qry .= ' "idEvento",';
        $qry .= ' "idEdicion",';
        $qry .= ' "DC_CalleNum",';
        $qry .= ' "DC_Ciudad",';
        $qry .= ' "DC_CodigoPostal",';
        $qry .= ' "DC_Colonia",';
        $qry .= ' "DC_Delegacion",';
        $qry .= ' "DC_Pais",';
        $qry .= ' "DC_DescripcionEN",';
        $qry .= ' "DC_DescripcionES",';
        $qry .= ' "DC_Email",';
        $qry .= ' "DC_Estado",';
        $qry .= ' "DC_NombreComercial",';
        $qry .= ' "DC_PaginaWeb",';
        $qry .= ' "DC_Puesto",';
        $qry .= ' "DC_Telefono",';
        $qry .= ' "DC_TelefonoAreaCiudad",';
        $qry .= ' "DC_TelefonoAreaPais",';
        $qry .= ' "DC_TelefonoExtension",';
        $qry .= ' "EMSTDListadoStand",';
        $qry .= ' "idPaquete",';
        $qry .= ' "DD_Logo",';
        $qry .= ' "DD_NombreComercial",';
        $qry .= ' "DD_CalleNum",';
        $qry .= ' "DD_Ciudad",';
        $qry .= ' "DD_Colonia",';
        $qry .= ' "DD_Pais",';
        $qry .= ' "DD_Estado",';
        $qry .= ' "DD_CodigoPostal",';
        $qry .= ' "DD_DescripcionEN",';
        $qry .= ' "DD_DescripcionES",';
        $qry .= ' "DD_DescripcionPT",';
        $qry .= ' "DD_Email",';
        $qry .= ' "DD_NombreComercial",';
        $qry .= ' "DD_PaginaWeb",';
        $qry .= ' "DD_Telefono",';
        $qry .= ' "DD_TelefonoAreaCiudad",';
        $qry .= ' "DD_TelefonoAreaPais",';
        $qry .= ' "DD_TelefonoExtension",';
        $qry .= ' "DD_TelefonoExtension",';
//        $qry .= ' "ExpositorNuevo",';
        $qry .= ' "EmpresaStand",';
        $qry .= ' "EmpresaFoto",';
        $qry .= ' "EmpresaProducto",';
        $qry .= ' "EmpresaCategoria",';
        $qry .= ' "Coexpositor"';
        $qry .= 'FROM ';
        $qry .= '"SAS"."CacheEmpresa"';
        $qry .= ' WHERE "idEdicion" = ' . $idEdicion;
        $qry .= ' AND "EMSTDListadoStand" is not NULL';
        $qry .= ' AND "EmpresaStand" is not NULL';

        $result = $this->SQLModel->execQueryString($qry);

        return $result;
    }

    public function getBooths($idEdicion) {
        $qry = ' SELECT ';
        $qry .= $this->getFieldsBooths();
        $qry .= ' FROM "SAS"."Stand"';    
        $qry .= ' WHERE "idEdicion" = ' . $idEdicion;
        $qry .= ' AND "Stand_H" > 0';
        $qry .= ' AND "Stand_W" > 0';
        $qry .= ' AND "Stand_X" > 0';
        $qry .= ' AND "Stand_Y" > 0';
        $result = $this->SQLModel->execQueryString($qry);
        return $result;
    }

    public function getFieldsBooths() {
        $fileds = ' "idEvento",';
        $fileds .= ' "idEdicion",';
        $fileds .= ' "StandNumber",';
        $fileds .= ' "EtiquetaStand",';
        $fileds .= ' "Stand_X",';
        $fileds .= ' "Stand_Y",';
        $fileds .= ' "Stand_W",';
        $fileds .= ' "Stand_H",';
        $fileds .= ' "idSala",';
        $fileds .= ' "idStand",';
        $fileds .= ' "StandStatus"';
        return $fileds;
    }

}
