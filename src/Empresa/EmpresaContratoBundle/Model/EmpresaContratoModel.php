<?php

namespace Empresa\EmpresaContratoBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class EmpresaContratoModel extends DashboardModel {

    protected $SQLModel, $PGSQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->PGSQLModel = new PGSQLClient();
    }

    public function getContractsByCompany($args = "") {
        $qry = ' SELECT ' . $this->getContractFields();
        $qry .= ' FROM "SAS"."Contrato" c';
        $qry .= ' WHERE c."idEmpresa" = ' . $args['c."idEmpresa"'];
        $qry .= ' AND c."idEdicion" = ' . $args['c."idEdicion"'];
        $qry .= ' ORDER BY c."idContrato"';
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idContrato']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getContractFields() {
        $fields = '';
        $fields .= ' c."idContrato",';
        $fields .= ' c."NoFolio",';
        $fields .= ' c."idEvento",';
        $fields .= ' c."idEdicion",';
        $fields .= ' c."idEmpresa",';
        $fields .= ' c."ListadoStand",';
        $fields .= ' c."AreaContratada",';
        $fields .= ' c."idStatusContrato",';
        $fields .= ' c."FechaEnvio",';
        $fields .= ' c."FechaLlenado",';
        $fields .= ' c."FechaSubida",';
        $fields .= ' c."FechaAutorizacion",';
        $fields .= ' c."FechaCancelacion",';
        $fields .= ' c."FechaCreacion",';
        $fields .= ' c."FechaModificacion",';
        $fields .= ' c."ContratoPDF"';

        return $fields;
    }

    public function getContactsTypes() {
        $qry = ' SELECT ' . $this->getTypesFields();
        $qry .= ' FROM "SAS"."ContactoTipo" ct';
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idStatusContrato']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getTypesFields() {
        $fields = '';
        $fields .= ' ct."idStatusContrato",';
        $fields .= ' ct."Status",';
        $fields .= ' ct."Descripcion"';
        return $fields;
    }

    public function getStatus() {
        $qry = ' SELECT ' . $this->getStatusFields();
        $qry .= ' FROM "SAS"."StatusContrato" s';
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idStatusContrato']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getStatusFields() {
        $fields = '';
        $fields .= ' s."idStatusContrato",';
        $fields .= ' s."Status",';
        $fields .= ' s."Descripcion"';
        return $fields;
    }

    public function getEditions() {
        $qry = ' SELECT ' . $this->getEditionFields();
        $qry .= ' FROM "SAS"."Edicion" e';
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idEdicion']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getEditionFields() {
        $fields = '';
        $fields .= ' e."idEdicion",';
        $fields .= ' e."idComiteOrganizador",';
        $fields .= ' e."Edicion_ES",';
        $fields .= ' e."Edicion_EN",';
        $fields .= ' e."Edicion_PT",';
        $fields .= ' e."Edicion_FR" ';
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

    public function cancelContract($args = array()) {
        $qry = 'SELECT * FROM "SAS"."fn_sas_CancelarContrato"(';
        $qry .= "'" . $args['idContrato'] . "',";
        $qry .= "'" . $args['idEvento'] . "',";
        $qry .= "'" . $args['idEdicion'] . "',";
        $qry .= "'" . $args['idEmpresa'] . "');";
        $result = $this->SQLModel->executeQuery($qry);

        return $result;
    }

    public function changeUpgrade($args = array()) {
        $conditions = Array("idEmpresa" => $args["idEmpresa"], "idEdicion" => $args["idEdicion"]);
        $result = $this->SQLModel->updateFromTable("EmpresaEdicion", array("idPaquete" => $args["idNuevoPaquete"]), $conditions, "");
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
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
