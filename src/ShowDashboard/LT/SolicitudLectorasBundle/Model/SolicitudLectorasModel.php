<?php

namespace ShowDashboard\LT\SolicitudLectorasBundle\Model;

use ShowDashboard\LT\LectorasBundle\Model\LectorasModel;
/**
 *
 * @author Eduardo Cervantes <eduardoc@infoexpo.com.mx>
 */
use Utilerias\SQLBundle\Model\SQLModel;

class SolicitudLectorasModel extends LectorasModel {

    protected $SQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function solicitarLectoras($data, $where) {
        $values = $this->parseElements($data);
        $result = $this->SQLModel->updateFromTable("EmpresaForma", $values, $where);
        if (!$result['status']) {
            die($result['data']);
        }
    }

    public function insertarEmpresaFormaLog($user, $idEdicion, $idForma, $idEmpresa) {
        $result = $this->SQLModel->selectFromTable("ComiteOrganizador", Array("ComiteOrganizador"), Array("idComiteOrganizador" => $user['idComiteOrganizador']));
        if (!$result['status']) {
            die($result['data']);
        }
        $usuario = Array(
            "idUsuario" => $user['idUsuario'],
            "Email" => $user['Email'],
            "Nombre" => $user['Nombre'],
            "Puesto" => $user['Puesto'],
            "TipoUsuario" => $user['TipoUsuario'],
            "ComiteOrganizador" => $result['data'][0]['ComiteOrganizador']
        );
        $values = Array(
            "idEdicion" => $idEdicion,
            "idEmpresa" => $idEmpresa,
            "idForma" => $idForma,
            "Usuario" => "'" . json_encode($usuario) . "'",
            "Accion" => 1,
        );
        $result = $this->SQLModel->insertIntoTable("EmpresaFormaLog", $values);
        if (!$result['status']) {
            die($result['data']);
        }
    }

    public function insertEntidad($args = Array()) {
        $data = Array(
            'idEmpresa' => $args['idEmpresa'],
            'DF_RazonSocial' => "'" . $args['RazonSocial'] . "'",
            'DF_RFC' => "'" . $args['RFC'] . "'",
            'DF_RepresentanteLegal' => "'" . $args['RepresentanteLegal'] . "'",
            'DF_Email' => "'" . $args['Email'] . "'",
            //'DF_Telefono' => "'" . $args['Telefono'] . "'",
            //'DF_Puesto' => "'" . $args['Puesto'] . "'",
            'DF_Pais' => "'" . $args['Pais'] . "'",
            'DF_Estado' => "'" . $args['Estado'] . "'",
            'DF_idPais' => "'" . $args['idPais'] . "'",
            'DF_idEstado' => "'" . $args['idEstado'] . "'",
            'DF_Ciudad' => "'" . $args['Ciudad'] . "'",
            'DF_Colonia' => "'" . $args['Colonia'] . "'",
            'DF_Calle' => "'" . $args['Calle'] . "'",
            'DF_NumeroExterior' => "'" . $args['NumeroExterior'] . "'",
            'DF_NumeroInterior' => "'" . $args['NumeroInterior'] . "'",
            'DF_CodigoPostal' => "'" . $args['CodigoPostal'] . "'",
        );
        $res_pg = $this->SQLModel->insertIntoTable("EmpresaEntidadFiscal", $data, "idEmpresaEntidadFiscal");
        return $res_pg;
    }

    public function parseElements($data) {
        $values = Array();
        foreach ($data as $key => $value) {
            if (is_numeric($value)) {
                $values[$key] = $value;
            } else {
                $values[$key] = "'" . $value . "'";
            }
        }
        return $values;
    }

    public function getServicios($args) {
        $result = $this->SQLModel->selectFromTable("Servicio", $this->getCamposServiciosForma(), $args, array('Orden' => 'ASC'));
        if (isset($result['status']) && $result['status'] == 1) {
            $data = array();
            foreach ($result['data'] as $key => $value) {
                $data[$value['idServicio']] = $value;
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    private function getCamposServiciosForma() {
        $fields = array(
            'idServicio',
            'idForma',
            'DescripcionEN',
            'DescripcionES',
            'DescripcionFR',
            'DescripcionPT',
            'FechaLimite',
            'PrecioAntesFechaEN',
            'PrecioAntesFechaES',
            'PrecioAntesFechaFR',
            'PrecioAntesFechaPT',
            'PrecioDespuesFechaEN',
            'PrecioDespuesFechaES',
            'PrecioDespuesFechaFR',
            'PrecioDespuesFechaPT',
            'Orden',
            'TituloEN',
            'TituloES',
            'TituloFR',
            'TituloPT',
            'MonedaEN',
            'MonedaES',
            'MonedaFR',
            'MonedaPT'
        );
        return $fields;
    }

    public function getSolicitudLectoras($args) {
        $fields = array(
            "DetalleServicioJSON",
            "DetallePagoJSON",
            "StatusPago",
            "Lang",
            "idFormaPago",
            "ModificacionComite",
            "FechaActualizacionStatusPago",
            "Subtotal",
            "IVA",
            "Total",
            "StatusForma",
            "FechaPrimerGuardado"
        );
        $result = $this->SQLModel->selectFromTable("EmpresaForma", $fields, $args);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"][0];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getDataLink($idEdicion, $idEvento, $idForma, $idEmpresa){
        $qry = 'SELECT ee."Token", ef."Lang" ';
        $qry .= ' FROM "SAS"."EmpresaEdicion" ee ';
        $qry .= ' JOIN "SAS"."EmpresaForma" ef ';
        $qry .= ' ON ee."idEmpresa"  = ef."idEmpresa" ';
        $qry .= ' WHERE ee."idEdicion" = ' . $idEdicion;
        $qry .= ' AND ef."idEdicion" = ' . $idEdicion;
        $qry .= ' AND ee."idEvento" = ' . $idEvento;
        $qry .= ' AND ef."idEvento" = ' . $idEvento;
        $qry .= ' AND ef."idForma" = ' . $idForma;
        $qry .= ' AND ef."idEmpresa" = ' . $idEmpresa;
        $qry .= ' AND ee."idEmpresa" = ' . $idEmpresa;
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"][0];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

}
