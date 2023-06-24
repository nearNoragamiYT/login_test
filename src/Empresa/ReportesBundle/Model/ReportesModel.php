<?php

namespace Empresa\ReportesBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class ReportesModel extends DashboardModel {

    protected $SQLModel, $PGSQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->PGSQLModel = new PGSQLClient();
    }

    public function getExpositores($params, $lang) {
        $fields = array(
            "CodigoCliente",
            "FechaAutorizacion",
            "TipoES",
            "DF_RazonSocial",
            "DF_RFC",
            "DF_Calle",
            "DF_Colonia",
            "DF_CodigoPostal",
            "DF_Ciudad",
            "DF_Estado",
            "DF_Pais",
            "NombreCompleto",
            "Nombre",
            "ApellidoPaterno",
            "ApellidoMaterno",
            "Puesto",
            "mail",
            "DC_NombreComercial",
            "dc_nombre",
            "Nombre2",
            "ApellidoPaterno2",
            "ApellidoMaterno2",
            "dc_puesto",
            "dc_email",
            "DC_CalleNum",
            "DC_Colonia",
            "DC_CodigoPostal",
            "DC_Ciudad",
            "DC_Estado",
            "DC_Pais",
            "DC_Telefono",
            "DD_PaginaWeb",
            "DC_DescripcionES",
            "categorias",
            "NombreCompletoContacto",
            "nom",
            "ap",
            "am",
            "pues",
            "emai",
            "emaila",
            "telefono",
            "standcantidad",
            "standtipo",
            "EMSTDMetrosCuadrados",
            "EMSTDListadoStand",
            "ac",
            "bc",
            "bp",
            "cc",
            "cp",
            "dc",
            "dp",
            "ec",
            "ep",
            "fc",
            "fp",
            "gc",
            "gp",
            "m2",
            "standprecio",
            "standtipoprecio",
            "idopcionpago",
            "moneda",
            "pabellon",
            "SubtotalStand",
            "DescuentoCantidad",
            "DecoracionCantidad",
            "SubTotalContrato",
            "IvaTotalContrato",
            "TotalContrato",
            "NombreUsuario",
            "Email",
            "Password",
            "UsuarioInvitaciones",
            "PasswordInvitaciones",
            "NumeroGafetes",
            "NumeroInvitaciones",
            "ObservacionesFacturacion",
            "razonsocialpadre",
            "gafetesedecanforma",
            "gafetesedecanpagoforma",
            "catalogoproductos",
            "vitrinasproductos"
        );

        $result = $this->SQLModel->selectFromTable("vw_sas_ReporteExpositores", $fields, $params, array('CodigoCliente' => 'ASC'));
       
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getExpositoresPDF($params, $lang) {
        $fields = array(
            "DC_NombreComercial",
            "NombreCompletoTipo3",
            "PuestoTipo3",
            "DC_CalleNum",
            "DC_Colonia",
            "DC_CodigoPostal",
            "DC_Ciudad",
            "DC_Estado",
            "DC_Pais",
            "DC_Telefono",
            "mailtipo3",
            "DD_PaginaWeb",
            "EMSTDListadoStand",
            "categorias",
            "DC_DescripcionES",
            "DC_PaginaWeb"
        );
        $result = $this->SQLModel->selectFromTable("vw_sas_ReporteExpositores", $fields, $params, array('DC_NombreComercial' => 'ASC'));
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    /*public function getEmpresasAdicionales($params, $lang) {
        $fields = array(
            "codigoclientepadre",
            "TipoES",
            "razonsocialpadre",
            "DC_NombreComercial",
            "NombreCompletoTipo3",
            "NombreTipo3",
            "ApellidoPaternoTipo3",
            "ApellidoMaternoTipo3",
            "PuestoTipo3",
            "mailtipo3",
            "DC_CalleNum",
            "DC_Colonia",
            "DC_CodigoPostal",
            "DC_Ciudad",
            "DC_Estado",
            "DC_Pais",
            "DC_Telefono",
            "DD_PaginaWeb",
            "DC_DescripcionES",
            "categorias",
            "EMSTDListadoStand"
        );
        $result = $this->SQLModel->selectFromTable("vw_sas_ReporteExpositores", $fields, $params, array('codigoclientepadre' => 'ASC'));        
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }*/
    
    public function getEmpresasAdicionales($params, $lang) {
        $qry .= 'SELECT DISTINCT ON (ep."CodigoCliente", e."idEmpresa")';
        $qry .= ' ed."idEmpresaPadre",';
        $qry .= ' e."idEmpresa",';
        $qry .= ' ep."CodigoCliente",';
        $qry .= ' et."TipoES",';
        $qry .= ' ef."DF_RazonSocial",';
        $qry .= ' e."DC_NombreComercial",';
        $qry .= ' (((((co."Nombre")::text || \' \'::text) || (co."ApellidoPaterno")::text) || \' \'::text) || ((CASE WHEN (co."ApellidoMaterno" IS NULL) THEN \'\'::character varying ELSE co."ApellidoMaterno" END)::text)) AS "NombreCompleto",';
        $qry .= ' co."Nombre",';
        $qry .= ' co."ApellidoPaterno",';
        $qry .= ' co."ApellidoMaterno",';
        $qry .= ' co."Puesto",';
        $qry .= ' co."Email",';
        $qry .= ' e."DC_CalleNum",';
        $qry .= ' e."DC_Colonia",';
        $qry .= ' e."DC_CodigoPostal",';
        $qry .= ' e."DC_Ciudad",';
        $qry .= ' e."DC_Estado",';
        $qry .= ' e."DC_Pais",';
        $qry .= ' e."DC_Telefono",';
        $qry .= ' co."ApellidoPaterno",';
        $qry .= ' ed."DD_PaginaWeb",';
        $qry .= ' e."DC_DescripcionES",';
        $qry .= ' ec.categorias,';
        $qry .= ' ed."EMSTDListadoStand"';
        $qry .= ' FROM "SAS"."Empresa" e';
        $qry .= ' FULL JOIN "SAS"."EmpresaEdicion" ed ON e."idEmpresa" = ed."idEmpresa"';
        $qry .= ' LEFT JOIN "SAS"."Empresa" ep ON ed."idEmpresaPadre" = ep."idEmpresa"';
        $qry .= ' LEFT JOIN "SAS"."Contrato" con ON ed."idEmpresaPadre" = con."idEmpresa" AND ed."idEdicion" = con."idEdicion" AND ed."idEvento" = con."idEvento" AND con."idStatusContrato" = 4';
        $qry .= ' LEFT JOIN "SAS"."EmpresaEntidadFiscal" ef ON con."idEmpresaEntidadFiscal" = ef."idEmpresaEntidadFiscal"';
        $qry .= ' LEFT JOIN "SAS"."EmpresaTipo" et ON e."idEmpresaTipo" = et."idEmpresaTipo"';
        $qry .= ' INNER JOIN "SAS"."ContactoEdicion" ce ON ed."idEmpresa" = ce."idEmpresa" AND ed."idEdicion" = ce."idEdicion" AND ce."Principal" = true';
        $qry .= ' INNER JOIN "SAS"."Contacto" co ON co."idContacto" = ce."idContacto" AND co."idEmpresa" = ce."idEmpresa"';
        $qry .= ' LEFT JOIN ( SELECT ec_1."idEmpresa",';
        $qry .= ' string_agg((((padre."NombreCategoriaES")::text || \'-\'::text) || (cat."NombreCategoriaES")::text), \'|\'::text) AS categorias,';
        $qry .= ' ec_1."idEvento",';
        $qry .= ' ec_1."idEdicion"';
        $qry .= ' FROM (("SAS"."EmpresaCategoria" ec_1';
        $qry .= ' JOIN "SAS"."Categoria" cat ON ((((ec_1."idCategoria" = cat."idCategoria") AND (ec_1."idEvento" = cat."idEvento")) AND (ec_1."idEdicion" = cat."idEdicion"))))';
        $qry .= ' JOIN "SAS"."Categoria" padre ON ((cat."idPadre" = padre."idCategoria")))';
        $qry .= ' GROUP BY ec_1."idEmpresa", ec_1."idEvento", ec_1."idEdicion") ec ON ((((ec."idEmpresa" = ed."idEmpresa") AND (ec."idEvento" = ed."idEvento")) AND (ec."idEdicion" = ed."idEdicion")))';        
        $qry .= ' WHERE ed."idEdicion" = ' . $params['idEdicion'];                
        $qry .= ' AND ce."idEdicion" = ' . $params['idEdicion'];
        $qry .= ' AND ed."EmpresaAdicional" = 1';
        $qry .= ' ORDER BY ep."CodigoCliente"';
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            foreach ($result["data"] as $key => $value) {                
                unset($result["data"][$key]['idEmpresaPadre']);
                unset($result["data"][$key]['idEmpresa']);
            }
            #print_r($result["data"]);die(' <=result_getEmpresasAdicionales');
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getGafetesCobro($params, $lang) {
        $fields = array();
        $result = $this->SQLModel->selectFromTable("vw_sas_ReporteGafetesCobro", $fields, $params);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getGafetes($params, $lang) {
        $fields = array();
        $result = $this->SQLModel->selectFromTable("vw_sas_ReporteGafetes", $fields, $params);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getCambiosDirectorio($params, $lang) {
        $fields = array(
            "CodigoCliente",
            "DF_RazonSocial",
            "DC_NombreComercial",
            "DetalleForma"
        );
        $result = $this->SQLModel->selectFromTable("vw_sas_ReporteExpositores", $fields, $params, array('CodigoCliente' => 'ASC'));
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getCatalogoProductos($fields, $params, $lang) {

        $result = $this->SQLModel->selectFromTable("vw_sas_ReporteCatalogoProductos", $fields, $params, array('CodigoCliente' => 'ASC'));
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getCatalogos($table, $fields, $params, $lang) {
        $result = $this->SQLModel->selectFromTable($table, $fields, $params);        
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getGafetesPendientes($params, $lang) {
        $fields = array(
            "DC_NombreComercial",
            "DF_RazonSocial",
            "Cargado",
            "StatusForma"
        );
        $result = $this->SQLModel->selectFromTable("vw_sas_ReporteGafetesPendientes", $fields, $params);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getStands($params) {
        $fields = array(
            "TipoStand",
            "salonjalisco",
            "areajalisco",
            "saloninternacional",
            "areainternacional",
            "pabellon1",
            "pabellonarea1",
            "pabellon2",
            "pabellonarea2",
            "pabellon3",
            "pabellonarea3",
            "pabellon4",
            "pabellonarea4"
//            "pabellon5",
//            "pabellonarea5",
//            "pabellon6",
//            "pabellonarea6",
//            "pabellon7",
//            "pabellonarea7",
//            "pabellon8",
//            "pabellonarea8"
        );
        $result = $this->SQLModel->selectFromTable("vw_sas_ReporteInventarioStands", $fields, $params);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getPabellones($params) {
        $fields = array(
            "DescripcionPabellon",
            "standstotales",
            "m2totales",
            "preciototales",
            "standsreservados",
            "m2reservados",
            "precioreservados",
            "standscontratados",
            "m2contratados",
            "preciocontratados",
            "standslibres",
            "m2libres",
            "preciolibres"
        );
        $result = $this->SQLModel->selectFromTable("vw_sas_ReportePabellones", $fields, $params);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getMontaje($params) {
        $fields = array(
            "CodigoCliente",
            "EMSTDListadoStand",
            "DC_NombreComercial",
            "contactoempresa",
            "contactocargo",
            "contactoemail",
            "contactocelular",
            "Opcion_ES",
//            "rotulo",
            "mampara",
            "marquesina",
            "lampara",
            "contactoe",
            "bote",
            "contactov",
            "botev",
            "observaciones",
            "segundopiso",
            "MontajeActividad",
            "Empresa",
            "Responsable",
            "Cargo",
            "TelefonoCelular",
            "TelefonoOficina",
            "Email",
            "VehiculoES",
            "Cantidad"
        );
        $result = $this->SQLModel->selectFromTable("vw_sas_ReporteMontaje", $fields, $params);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getStandPabellon($params) {
        $qry .= 'SELECT ' . $this->getStandPabellonFields();
        $qry .= ' FROM "SAS"."vw_sas_ReporteStandPabellon" ';
        $qry .= ' WHERE ("EmpresaAdicional" = 0 OR "EmpresaAdicional" IS NULL) AND "idEdicion" = ' . $params['idEdicion'];
        $qry .= ' ORDER BY "DescripcionPabellon", "StandNumber"';
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }
    
    private function getStandPabellonFields(){
        $fields = '';
        $fields .= '"CodigoCliente",';
        $fields .= '"DC_NombreComercial",';
        $fields .= '"Stand_H",';
        $fields .= '"Stand_W",';
        $fields .= '"StandArea",';
        $fields .= '"StandNumber",';
        $fields .= '"StandStatus",';
        $fields .= '"DescripcionPabellon"';
        return $fields;
    }

    public function getCatalogoCategorias($params) {
        $qry .= 'SELECT c."NombreCategoriaES",';
        $qry .= 'c."idCategoria",';
        $qry .= 'c."idPadre"';
        $qry .= ' FROM "SAS"."Categoria" c';
        $qry .= ' WHERE c."idEdicion" = ' . $params['idEdicion'];
        $qry .= ' ORDER BY c."NombreCategoriaES"';
        
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getEmpresaCategoriaPDF($params, $lang) {
        $qry .= 'SELECT e."DC_NombreComercial",';
        $qry .= 'ed."EMSTDListadoStand",';
        $qry .= 'ec."idCategoria",';
        $qry .= 'c."NombreCategoriaES",';
        $qry .= 'c."idPadre"';
        $qry .= ' FROM "SAS"."EmpresaCategoria" ec';
        $qry .= ' INNER JOIN "SAS"."Categoria" c ';
        $qry .= ' ON ec."idCategoria" = c."idCategoria"';
        $qry .= ' INNER JOIN "SAS"."EmpresaEdicion" ed ';
        $qry .= ' ON ec."idEmpresa" = ed."idEmpresa"';
        $qry .= ' INNER JOIN "SAS"."Empresa" e ';
        $qry .= ' ON ed."idEmpresa" = e."idEmpresa"';
        $qry .= ' WHERE ec."idEdicion" = ' . $params['idEdicion'];
        #$qry .= ' ORDER BY c."idPadre",ec."idCategoria",e."DC_NombreComercial"';
        $qry .= ' ORDER BY c."NombreCategoriaES",e."DC_NombreComercial"';
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getExpositoresListadoStandPDF($params, $lang) {
        $fields = array(
            "DC_NombreComercial",
            "EMSTDListadoStand",
        );
        $result = $this->SQLModel->selectFromTable("vw_sas_ReporteExpositores", $fields, $params, array('DC_NombreComercial' => 'ASC'));
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getExpositoresPaisPDF($params, $lang) {
        $fields = array(
            "DC_NombreComercial",
            "EMSTDListadoStand",
            "DC_Pais"
        );
        $result = $this->SQLModel->selectFromTable("vw_sas_ReporteExpositores", $fields, $params, array('DC_Pais' => 'ASC'));
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getExpositoresPabellonPDF($params, $lang) {
        $fields = array(
            "DC_NombreComercial",
            "EMSTDListadoStand",
            "cadenapabellon"
        );
        $result = $this->SQLModel->selectFromTable("vw_sas_ReporteExpositores", $fields, $params, array('DC_NombreComercial' => 'ASC'));
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getCatalogoPabellones() {
        $qry .= 'SELECT p."NombreES",';
        $qry .= 'p."idPabellon"';
        $qry .= ' FROM "SAS"."Pabellon" p';
        $qry .= ' ORDER BY p."NombreES"';
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getTotalStands($params) {
        $qry = 'SELECT  ' . $this->getStandFields();
        $qry .= ' FROM "SAS"."vw_sas_ReporteStands" WHERE ';

        $length = count($params["editions"]);
        for ($i = 1; $i <= $length; $i++) {
            $qry .= '"idEdicion" = ' . $params["editions"][$i - 1];
            if ($i != $length)
                $qry .= ' OR ';
        }
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    protected function getStandFields() {
        $fields = '';
        $fields .= ' "idEdicion",';
        $fields .= ' "idStand",';
        $fields .= ' "StandNumber",';
        $fields .= ' "StandStatus", ';
        $fields .= ' "Stand_H",';
        $fields .= ' "Stand_W",';
        $fields .= ' "StandArea",';
        $fields .= ' "EtiquetaStand",';
        $fields .= ' "idEmpresa",';
        $fields .= ' "DC_NombreComercial"';
        return $fields;
    }

}
