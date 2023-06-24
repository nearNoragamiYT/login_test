<?php

namespace Empresa\EmpresaGafetesBundle\Model;

/**
 *
 * @author Edardo Cervantes <eduardoc@infoexpo.com.mx>
 */
use Utilerias\SQLBundle\Model\SQLModel;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class EmpresaGafetesModel extends DashboardModel {

    protected $SQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getVendedores($where, $lang) {
        $qry = 'SELECT DISTINCT usu."idUsuario", usu."Nombre", usu."Email"';
        $qry .= 'FROM "SAS"."Usuario" usu ';
        $qry .= 'INNER JOIN "SAS"."EmpresaUsuario" empu ';
        $qry .= 'ON usu."idUsuario"= empu."idUsuario"';
        $qry .= 'INNER JOIN "SAS"."Contrato" c ';
        $qry .= 'ON empu."idEmpresa"= c."idEmpresa"';
        $qry .= 'AND c."idEvento"=' . $where['idEvento'] . ' AND c."idEdicion" = ' . $where['idEdicion'];
        $qry .= 'ORDER BY usu."idUsuario"';
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        return $result['data'];
    }

    function getEmpresas($where, $lang) {
        $qry = 'SELECT DISTINCT co."idEmpresa", e."DC_NombreComercial", usu."idUsuario", usu."Nombre"';
        $qry .= ' FROM "SAS"."Usuario" AS usu ';
        $qry .= 'INNER JOIN "SAS"."EmpresaUsuario" empu ';
        $qry .= 'ON usu."idUsuario"= empu."idUsuario"';
        $qry .= 'INNER JOIN "SAS"."Empresa" AS e ';
        $qry .= 'ON empu."idEmpresa" = e."idEmpresa" ';
        $qry .= 'INNER JOIN "SAS"."Contrato" AS co ';
        $qry .= 'ON e."idEmpresa" = co."idEmpresa" ';
        $qry .= 'AND co."idEvento" = ' . $where['idEvento'] . ' AND co."idEdicion" = ' . $where['idEdicion'];
        $qry .= ' AND co."idStatusContrato" = 4 ';
        $qry .= 'ORDER BY usu."idUsuario";';
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        return $result['data'];
    }

    function getGafetes($where) {
        $qry = 'SELECT ' . $this->getCamposDetalleGafetes() . ' FROM "SAS"."vw_sas_ReporteExpositores" WHERE';
        $qry .= ' "idEdicion" = ' . $where['idEdicion'] . ' AND "idStatusContrato" = 4 AND "EmpresaAdicional" = 0';
        if (array_key_exists("idUsuario", $where)) {
            $qry .= ' AND "idUsuario" = ' . $where['idUsuario'];
        }
        if (array_key_exists("idEmpresa", $where)) {
            $qry .= ' AND "idEmpresa" = ' . $where['idEmpresa'];
        }
        $qry .= ' ORDER BY "CodigoCliente" ASC;';
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        return $result['data'];
    }

    private function getCamposDetalleGafetes() {
        $fields = '"idEmpresa", ';
        $fields .= '"CodigoCliente" AS "CodigoAntad", ';
        $fields .= '"DF_RazonSocial" AS "RazonSocial", ';
        $fields .= '"DC_NombreComercial" AS "NombreComercial", ';
        $fields .= '"NombreCompletoTipo3" AS "Nombre", ';
        $fields .= '"NombreUsuario" AS "Vendedor", ';
        $fields .= '"EMSTDListadoStand" AS "ListadoStands", ';
        $fields .= '"GafetesPagados" AS "Pagados", ';
        $fields .= '"NumeroGafetes" AS "TotalGafetesAsignados", ';
        $fields .= '"totalgafetesutilizados" AS "GafetesCapturadosED", ';
        $fields .= '"gafetetotalsedecan" AS "EdecanesSolicitadasED", ';
        /* ---  están al reves en la consulta  --- */
        $fields .= '"gafetesutilizados" AS "GafetesCostoED", ';
        $fields .= '"gafetesedecan" AS "EdecanesCostoED", ';
        $fields .= '"gafetespagoutilizados" AS "GafetesED", ';
        $fields .= '"gafetespagoedecan" AS "EdecanesED", ';
        $fields .= '"gafetesrestantes" AS "TotalGafetesRestantes" ';

        return $fields;
    }

}
