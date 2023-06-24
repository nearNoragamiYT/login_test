<?php

namespace Empresa\EmpresaFiscalBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class EmpresaFiscalModel extends DashboardModel {

    protected $SQLModel, $PGSQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->PGSQLModel = new PGSQLClient();
    }

    public function getFinancialCompanies($args = "") {
        $qry = ' SELECT ' . $this->getFinancialFields();
        $qry .= ' FROM "SAS"."EmpresaEntidadFiscal" eef';
        $qry .= ' WHERE eef."idEmpresa" = ' . $args['eef."idEmpresa"'];
        $qry .= ' ORDER BY eef."idEmpresaEntidadFiscal"';
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idEmpresaEntidadFiscal']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getFinancialFields() {
        $fields = '';
        $fields .= 'eef."idEmpresaEntidadFiscal",';
        $fields .= ' eef."idEmpresa",';
        $fields .= ' eef."DF_RazonSocial",';
        $fields .= ' eef."DF_RFC",';
        $fields .= ' eef."DF_idPais",';
        $fields .= ' eef."DF_Pais",';
        $fields .= ' eef."DF_idEstado",';
        $fields .= ' eef."DF_CodigoPostal",';
        $fields .= ' eef."DF_Ciudad",';
        $fields .= ' eef."DF_Colonia",';
        $fields .= ' eef."DF_Calle",';
        /* $fields .= ' eef."DF_NumeroExterior",';
          $fields .= ' eef."DF_RepresentanteLegal",';
          $fields .= ' eef."DF_Email",';
          $fields .= ' eef."DF_NumeroInterior", '; */
        $fields .= ' eef."Principal" ';
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

    public function insertFinancialCompany($args) {
        $result = $this->SQLModel->insertIntoTable("EmpresaEntidadFiscal", $args, "idEmpresaEntidadFiscal");
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function updateFinancialCompany($args, $id) {
        $result = $this->SQLModel->updateFromTable("EmpresaEntidadFiscal", $args, array("idEmpresaEntidadFiscal" => $id), "idEmpresaEntidadFiscal");
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function deleteFinancialCompany($id) {
        $result = $this->SQLModel->deleteFromTable("EmpresaEntidadFiscal", array("idEmpresaEntidadFiscal" => $id));
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function changeFinancialCompany($data) {
        $conditions = Array("idEmpresaEntidadFiscal" => $data["idActual"], "idEmpresa" => $data["idEmpresa"]);
        $result = $this->SQLModel->updateFromTable("EmpresaEntidadFiscal", Array("Principal" => "false"), $conditions);

        $conditions = Array("idEmpresaEntidadFiscal" => $data["idNuevo"], "idEmpresa" => $data["idEmpresa"]);
        $result = $this->SQLModel->updateFromTable("EmpresaEntidadFiscal", Array("Principal" => "true"), $conditions);

        return $result;
    }

    public function getCompanyHeader($args = "") {
        $qry = ' SELECT e."idEmpresa", e."DC_NombreComercial", e."CodigoCliente"';
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

}
