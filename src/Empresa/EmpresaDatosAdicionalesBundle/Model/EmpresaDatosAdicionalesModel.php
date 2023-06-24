<?php

namespace Empresa\EmpresaDatosAdicionalesBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class EmpresaDatosAdicionalesModel extends DashboardModel {

    protected $SQLModel, $PGSQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->PGSQLModel = new PGSQLClient();
    }

    public function getAditionalData($args = "") {
        $qry = ' SELECT ' . $this->getAditionalFields();
        $qry .= ' FROM "SAS"."EmpresaEdicion" ee';
        $qry .= ' WHERE ee."idEmpresa" = ' . $args['ee."idEmpresa"'];
        $qry .= ' AND ee."idEdicion" = ' . $args['ee."idEdicion"'];

        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"][0];
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getAditionalFields() {
        $fields = '';
        $fields .= ' ee."idEmpresa",';
        $fields .= ' ee."idTipoStandContratado",';
        $fields .= ' ee."idTipoPrecio",';
        $fields .= ' ee."ObservacionesFacturacion",';
        $fields .= ' ee."EmpresasAdicionales",';
        $fields .= ' ee."NumeroGafetes",';
        $fields .= ' ee."NumeroGafetesCompra",';
        $fields .= ' ee."GafetesPagados",';
        $fields .= ' ee."GafetesComentario",';
        $fields .= ' ee."NumeroVitrinas",';
        $fields .= ' ee."NumeroCatalogos",';
        $fields .= ' ee."NumeroInvitaciones",';
        $fields .= ' ee."UsuarioInvitaciones",';
        $fields .= ' ee."PasswordInvitaciones",';
        $fields .= ' ee."UsuarioEncuentroNegocios",';
        $fields .= ' ee."PasswordEncuentroNegocios",';
        $fields .= ' ee."Montaje",';
        $fields .= ' ee."MontajeAndenEntrada",';
        $fields .= ' ee."MontajeSalaEntrada",';
        $fields .= ' ee."MontajeDiaEntrada",';
        $fields .= ' ee."MontajeHorarioEntrada",';
        $fields .= ' ee."MontajeAndenSalida",';
        $fields .= ' ee."MontajeSalaSalida",';
        $fields .= ' ee."MontajeDiaSalida",';
        $fields .= ' ee."MontajeHorarioSalida"';

        return $fields;
    }

    public function saveAditionalData($args, $id) {
        $result = $this->SQLModel->updateFromTable("EmpresaEdicion", $args, array("idEmpresa" => $id));
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

    public function getTokens($params, $lang) {
        $qry = ' SELECT ' . $this->getTokensFields();
        $qry .= ' FROM "SAS"."Empresa" e';
        $qry .= ' JOIN "SAS"."EmpresaEdicion" ee';
        $qry .= ' ON e."idEmpresa" = ee."idEmpresa" AND ee."idEdicion" = ' . $params["idEdicion"];
        $qry .= ' WHERE e."idEmpresa" = ' . $params["idEmpresa"];
        $qry .= ' ORDER BY e."idEmpresa"';
        
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            return $result['data'];
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    protected function getTokensFields() {
        $fields = '';
        $fields .= ' e."idEmpresa",';
        $fields .= ' e."CodigoCliente",';
        $fields .= ' ee."idEtapa",';
        $fields .= ' e."DC_NombreComercial",';
        $fields .= ' ee."Token"';

        return $fields;
    }

}
