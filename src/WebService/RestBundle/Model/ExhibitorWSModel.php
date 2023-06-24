<?php

namespace WebService\RestBundle\Model;

use WebService\RestBundle\Model\mainModel;

class ExhibitorWSModel extends mainModel {

    protected
            $pg_schemaSAS = '"SAS"',
            $pg_schemaAE = '"AE"',
            $pg_std = '"Stand"',
            $pg_cat = '"Categoria"',
            $pg_exh = '"CacheEmpresa"',
            $pg_pav = '"Pabellon"';

    public function __construct($idEvento = 1, $idEdicion = 8) {
        parent::__construct($idEvento, $idEdicion);
    }

    public function getBooths($args = array()) {
        $qry = 'SELECT ';
        $qry .= $this->getFieldsBooths();
        $qry .= 'FROM ';
        $qry .= $this->pg_schemaSAS . '.' . $this->pg_std;
        $qry .= '{where}';
        $result = $this->PGModelSAS->execQueryString($qry, $args);
        return $result;
    }

    public function getFieldsBooths() {
        $fileds = '"idEvento", ';
        $fileds .= '"idEdicion", ';
        $fileds .= '"StandNumber", ';
        $fileds .= '"EtiquetaStand", ';
        $fileds .= '"Stand_X", ';
        $fileds .= '"Stand_Y", ';
        $fileds .= '"Stand_W", ';
        $fileds .= '"Stand_H", ';
        $fileds .= '"StandArea", ';
        $fileds .= '"idSala", ';
        $fileds .= '"idStand", ';
        $fileds .= '"StandStatus" ';
        return $fileds;
    }

    public function getCategories($args = array()) {
        $qry = 'SELECT ';
        $qry .= $this->getFieldsCategories();
        $qry .= 'FROM ';
        $qry .= $this->pg_schemaSAS . '.' . $this->pg_cat;
        $qry .= '{where}';
        $result = $this->PGModelSAS->execQueryString($qry, $args);
        return $result;
    }

    public function getFieldsCategories() {
        $fileds = '"idCategoria", ';
        $fileds .= '"NombreCategoriaES", ';
        $fileds .= '"NombreCategoriaEN", ';
        $fileds .= '"NombreCategoriaPT", ';
        $fileds .= '"idEdicion", ';
        $fileds .= '"Nivel", ';
        $fileds .= '"idPadre" ';
        return $fileds;
    }

    public function getExhibitors($args = null) {
        $qry = $this->getQueryWS($args);
        return $this->PGModelSAS->execQueryString($qry);
    }

    public function getQueryWS($args) {
        $qry = 'SELECT';
        $qry .= ' "idEmpresa",';
        $qry .= ' "idEvento",';
        $qry .= ' "idEdicion",';
        $qry .= ' "DC_CalleNum",';
        $qry .= ' "DC_Ciudad",';
        $qry .= ' "DC_CodigoPostal",';
        $qry .= ' "DC_Colonia",';
        $qry .= ' "DC_Delegacion",';
        $qry .= ' "DC_idPais",';
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
        $qry .= ' "EmpresaStand",';
        $qry .= ' "EmpresaFoto",';
        $qry .= ' "EmpresaProducto",';
        $qry .= ' "EmpresaCategoria",';
        $qry .= ' "Contacto",';
        $qry .= ' "ContactoEvento",';
        $qry .= ' "Coexpositor",';
        $qry .= ' "CodigoCliente",';
        $qry .= ' "FechaAutorizacion",';
        $qry .= ' "DF_RFC",';
        $qry .= ' "DF_RazonSocial",';
        $qry .= ' "idEmpresaPadre",';
        $qry .= ' "EmpresaAdicional",';
        $qry .= ' "EmpresasAdicionalesJson",';
        $qry .= ' "idStatusContrato"';
        $qry .= 'FROM ';
        $qry .= $this->pg_schemaSAS . '.' . $this->pg_exh;
        $qry .= ' WHERE "idEdicion" = ' . $args["idEdicion"];
        $qry .= ' AND "EMSTDListadoStand" is not NULL';
        $qry .= ' AND "EmpresaStand" is not NULL';
        $qry .= ' ORDER BY "EmpresasAdicionalesJson"::text ASC';

        return $qry;
    }

    public function getPavilions($args = null) {
        $qry = 'SELECT "idPabellon", "NombreES", "NombreEN"';
        $qry .= 'FROM ';
        $qry .= $this->pg_schemaSAS . '.' . $this->pg_pav;
        $qry .= '{where}';
        $result = $this->PGModelSAS->execQueryString($qry, $args);
        return $result;
    }
}
