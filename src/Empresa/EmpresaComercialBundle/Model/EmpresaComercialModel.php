<?php

namespace Empresa\EmpresaComercialBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class EmpresaComercialModel extends DashboardModel {

    protected $SQLModel, $PGSQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->PGSQLModel = new PGSQLClient();
    }

    public function getComercialCompany($args = "") {
        $qry = ' SELECT ' . $this->getComercialFields();
        $qry .= ' FROM "SAS"."Empresa" e';
        $qry .= ' LEFT OUTER JOIN "SAS"."EmpresaEdicion" ed';
        $qry .= ' ON e."idEmpresa" = ed."idEmpresa"';
        $qry .= ' WHERE e."idEmpresa" = ' . $args['e."idEmpresa"'];
        $qry .= ' ORDER BY e."idEmpresa"';
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"][0];
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getComercialFields() {
        $fields = '';
        $fields .= ' e."idEmpresa",';
        $fields .= ' e."idEmpresaTipo",';
        $fields .= ' e."idEmpresaUUID",';
        $fields .= ' e."CodigoCliente",';
        $fields .= ' e."DC_NombreComercial",';
        $fields .= ' e."DC_idPais",';
        $fields .= ' e."DC_Pais",';
        $fields .= ' e."DC_idEstado",';
        $fields .= ' e."DC_Estado",';
        $fields .= ' e."DC_Ciudad",';
        $fields .= ' e."DC_CodigoPostal",';
        $fields .= ' e."DC_Colonia",';
        $fields .= ' e."DC_CalleNum",';
        $fields .= ' e."DC_TelefonoAreaPais",';
        $fields .= ' e."DC_TelefonoAreaCiudad",';
        $fields .= ' e."DC_Telefono",';
        $fields .= ' e."DC_TelefonoExtension",';
        $fields .= ' e."DC_PaginaWeb",';
        $fields .= ' e."DC_DescripcionES",';
        $fields .= ' e."DC_DescripcionEN",';
        $fields .= ' e."idPabellon",';
        $fields .= ' ed."VisibleDirectorio",';
        $fields .= ' ed."idEmpresaPadre"';

        return $fields;
    }

    public function getTypes($idEdicion, $lang) {
        $qry = ' SELECT ' . $this->getTypeFields();
        $qry .= ' FROM "SAS"."EmpresaTipo" et';
        $qry .= ' WHERE et."idEdicion" = ' . $idEdicion;
        $qry .= ' ORDER BY et."Tipo' . strtoupper($lang) . '"';
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idEmpresaTipo']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getTypeFields() {
        $fields = '';
        $fields .= ' et."idEmpresaTipo",';
        $fields .= ' et."TipoEN",';
        $fields .= ' et."TipoES",';
        $fields .= ' et."TipoFR",';
        $fields .= ' et."Coexpositor" ';
        return $fields;
    }

    public function getSellers() {
        $qry = ' SELECT ' . $this->getSellerFields();
        $qry .= ' FROM "SAS"."Vendedor" v';
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idVendedor']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getSellerFields() {
        $fields = '';
        $fields .= ' v."idVendedor",';
        $fields .= ' v."Email",';
        $fields .= ' v."NombreCompletoES",';
        $fields .= ' v."NombreCompletoEN" ';
        return $fields;
    }

    public function getPavilions($idEdicion) {
        $qry = ' SELECT ' . $this->getPavilionFields();
        $qry .= ' FROM "SAS"."Pabellon" p';
        $qry .= ' WHERE p."idEdicion" = ' . $idEdicion;
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idPabellon']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getPavilionFields() {
        $fields = '';
        $fields .= ' p."idPabellon",';
        $fields .= ' p."NombreEN",';
        $fields .= ' p."NombreES"';
        return $fields;
    }

    public function saveComercialCompany($args, $id) {
        $result = $this->SQLModel->updateFromTable("Empresa", $args, array("idEmpresa" => $id), "idEmpresa");
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function changeVisible($args, $id) {
        $result = $this->SQLModel->updateFromTable("EmpresaEdicion", $args, array("idEmpresa" => $id));
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function getEvents() {
        $qry = ' SELECT ' . $this->getEventFields();
        $qry .= ' FROM "SAS"."Evento" e';
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idEvento']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getEventFields() {
        $fields = '';
        $fields .= ' e."idEvento",';
        $fields .= ' e."idComiteOrganizador",';
        $fields .= ' e."Evento_ES",';
        $fields .= ' e."Evento_EN",';
        $fields .= ' e."Evento_PT",';
        $fields .= ' e."Evento_FR" ';
        return $fields;
    }

    public function getPackages($args) {
        $qry = ' SELECT ' . $this->getPackagesFields();
        $qry .= ' FROM "SAS"."Paquete" p';
        $qry .= ' WHERE p."idEdicion" = ' . $args['p."idEdicion"'];
        $qri .= ' ORDER BY p."idPaquete"';
        $result = $this->SQLModel->executeQuery($qry);
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

    private function getPackagesFields() {
        $fields = '';
        $fields .= ' p."idPaquete",';
        $fields .= ' p."PaqueteES",';
        $fields .= ' p."PaqueteEN",';
        $fields .= ' p."PaquetePT",';
        $fields .= ' p."PaqueteFR" ';
        return $fields;
    }

    public function getCompanyHeader($args = "") {
        $qry = ' SELECT e."idEmpresa", e."DC_NombreComercial", e."CodigoCliente" ';
        $qry .= ' FROM "SAS"."Empresa" e';
        $qry .= ' WHERE e."idEmpresa" = ' . $args['e."idEmpresa"'];
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $qry = ' SELECT ee."idEtapa", ee."EMSTDListadoStand", ee."idPaquete", ee."Nombre", ee."Email", ee."Password"';
            $qry .= ' FROM "SAS"."vw_sas_ObtenerEmpresas" ee';
            $qry .= ' WHERE ee."idEmpresa" = ' . $args['e."idEmpresa"'] . ' AND ee."idEdicion" = ' . $args['ee."idEdicion"'];
            $result2 = $this->SQLModel->executeQuery($qry);

            if (isset($result2['status']) && $result2['status'] == 1 && isset($result2['data'][0])) {
                $data = array_merge($result["data"][0], $result2["data"][0]);
                return $data;
            } else {
                return $result["data"][0];
            }
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    public function getCategory($args) {
        $fields = array();
        return $this->SQLModel->selectFromTable("Categoria", $fields, $args, Array('NombreCategoriaES' => 'ASC'));
    }

    public function getCompanyCategory($args) {
        $fields = array();
        return $this->SQLModel->selectFromTable("EmpresaCategoria", $fields, $args);
    }

    public function saveCategories($args) {
        $qry = 'SELECT * FROM "SAS"."fn_sas_InsertaCategorias"(' . $args['idEmpresa'] . ', ' . $args['idEvento'] . ', ' . $args['idEdicion'] . ', ' . "'" . $args['ListadoCategorias'] . "'" . ');';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function getParents($args) {
        $qry = 'SELECT e."idEmpresa",';
        $qry .= ' e."DC_NombreComercial"';
        $qry .= 'FROM "SAS"."Empresa" AS e ';
        $qry .= 'INNER JOIN "SAS"."EmpresaEdicion" AS ee ON e."idEmpresa" = ee."idEmpresa" ';
        $qry .= 'WHERE ee."idEvento" = ' . $args['idEvento'] . ' AND ee."idEdicion" =' . $args['idEdicion'];
        $qry .= ' AND ee."Coexpositor" = 0 AND ee."EmpresaAdicional" = 0 ';
        $qry .= ' ORDER BY e."DC_NombreComercial" ASC';
        return $this->SQLModel->executeQuery($qry);
    }

    public function getAditionalDetail($where) {
        $result = $this->SQLModel->selectFromTable("EmpresaEdicion", Array("EmpresaAdicional"), $where);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        if ($result['data'][0]["EmpresaAdicional"] > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

}