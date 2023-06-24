<?php

namespace Empresa\SolicitudPaqueteBundle\Model;

/**
 *
 * @author Eduardo Cervantes <eduardoc@infoexpo.com.mx>
 */
use ShowDashboard\DashboardBundle\Model\DashboardModel;
use Utilerias\SQLBundle\Model\SQLModel;

class SolicitudPaqueteModel extends DashboardModel {

    protected $SQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getSolicitudes($idEvento, $idEdicion) {
        $qry = 'SELECT ' . $this->getCamposSolicitudes();
        $qry .= 'FROM "SAS"."SolicitudPaquete" AS sp ';
        $qry .= 'INNER JOIN "SAS"."Empresa" AS em ON sp."idEmpresa" = em."idEmpresa" ';
        $qry .= 'INNER JOIN "SAS"."EmpresaEdicion" AS ee ON em."idEmpresa" = ee."idEmpresa" ';
        $qry .= 'INNER JOIN "SAS"."Contacto" AS co ON em."idEmpresa" = co."idEmpresa" ';
        $qry .= 'INNER JOIN "SAS"."ContactoEdicion" AS ce ON co."idContacto" = ce."idContacto" ';
        $qry .= 'WHERE sp."idEdicion" = ' . $idEdicion;
        $qry .= ' AND ee."idEdicion" = ' . $idEdicion;
        $qry .= ' AND ce."idEdicion" = ' . $idEdicion;
        $qry .= ' AND ce."idEvento" = ' . $idEvento;
        $qry .= ' AND ce."Principal" = ' . "'t';";
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $solicitudes = Array();
        foreach ($result['data'] as $sol) {
            $solicitudes[$sol['idSolicitudPaquete']] = $sol;
        }
        return $solicitudes;
    }

    private function getCamposSolicitudes() {
        $fields = 'sp."idSolicitudPaquete", ';
        $fields .= 'sp."idPaquete", ';
        $fields .= 'ee."idPaquete" AS "PaqueteActual", ';
        $fields .= 'sp."idEmpresa", ';
        $fields .= 'sp."Status", ';
        $fields .= 'sp."FechaSolicitud", ';
        $fields .= 'sp."FechaCancelacion", ';
        $fields .= 'sp."MotivoCancelacion", ';
        $fields .= 'em."DC_NombreComercial", ';
        $fields .= 'em."DC_Pais", ';
        $fields .= 'CONCAT(co."Nombre",' . "' '," . 'co."ApellidoPaterno",' . "' '," . 'co."ApellidoPaterno") AS "NombreCompleto", ';
        $fields .= 'co."Puesto", ';
        $fields .= 'CONCAT(co."TelefonoCodigoPais",' . "' '," . 'co."TelefonoArea",' . "' '," . 'co."Telefono",' . "' Ext: '," . 'co."TelefonoExtension") AS "TelefonoCompleto", ';
        $fields .= 'co."Puesto", ';
        $fields .= 'co."Email" ';
        return $fields;
    }

    public function getPaquetes($idEvento, $idEdicion, $lang) {
        $qry = 'SELECT "idPaquete", "Paquete' . strtoupper($lang) . '" ';
        $qry .= 'FROM "SAS"."Paquete" ';
        $qry .= 'WHERE "idEdicion" = ' . $idEdicion . ' AND "idEvento" = ' . $idEvento;
        $qry .= 'ORDER BY "Nivel" ASC;';

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $paquetes = Array();
        foreach ($result['data'] as $value) {
            $paquetes[$value['idPaquete']] = $value;
        }
        return $paquetes;
    }

    public function cancelarSolicitud($idEdicion, $idSolicitud, &$post) {
        $values = Array(
            "Status" => 4,
            "MotivoCancelacion" => "'" . str_replace("'", "''", $post['MotivoCancelacion']) . "'",
            "FechaCancelacion" => "now()"
        );
        $where = Array(
            "idEdicion" => $idEdicion,
            "idSolicitudPaquete" => $idSolicitud
        );
        $result = $this->SQLModel->updateFromTable("SolicitudPaquete", $values, $where, "FechaCancelacion");
        if (!$result['status']) {
            die($result['data']);
        }
        $post['Status'] = 4;
        $post['FechaCancelacion'] = $result['data'][0]['FechaCancelacion'];
    }

    public function aprobarSolicitud($idEdicion, $idSolicitud, &$post) {
        $values = Array(
            "Status" => 2,
            "idPaquete" => $post['idPaquete']
        );
        $where = Array(
            "idEdicion" => $idEdicion,
            "idSolicitudPaquete" => $idSolicitud
        );
        $result = $this->SQLModel->updateFromTable("SolicitudPaquete", $values, $where);
        if (!$result['status']) {
            die($result['data']);
        }
        $values = Array(
            "idPaquete" => $post['idPaquete']
        );
        $where = Array(
            "idEdicion" => $idEdicion,
            "idEmpresa" => $post['idEmpresa']
        );
        $result = $this->SQLModel->updateFromTable("EmpresaEdicion", $values, $where);
        if (!$result['status']) {
            die($result['data']);
        }
        $post["PaqueteActual"] = $post['idPaquete'];
        $post['Status'] = 2;
    }

}
