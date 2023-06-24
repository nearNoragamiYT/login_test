<?php

namespace Empresa\EMarketingBundle\Model;

/**
 * Description of EMarketingModel
 *
 * @author Juan
 */
use Utilerias\SQLBundle\Model\SQLModel;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class EMarketingModel extends DashboardModel {

    protected $SQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getEMarketing($args) {
        $fields = array();
        return $this->SQLModel->selectFromTable("EMarketing", $fields, $args);
    }

    public function getDetalleEMarketing($args) {
        $fields = array();
        return $this->SQLModel->selectFromTable("DetalleEMarketing", $fields, $args);
    }

    public function insertEmarketing($args) {
        $data = Array();
        $data['Nombre'] = "'" . $args['NombreEmarketing'] . "'";
        $data['Cuerpo'] = "'" . '<p><img src="http://expoantad.infoexpo.com.mx/2019/ed/web/public/images/ED/logo_es.png" /></p>' . "'";
        $data['idEvento'] = $args['idEvento'];
        $data['idEdicion'] = $args['idEdicion'];
        $data['idUsuario'] = $args['idUsuario'];        
        $res_pg = $this->SQLModel->insertIntoTable("EMarketing", $data, "idEMarketing");        
        return $res_pg;
    }

    public function updateEmarketing($args = array()) {
        $data = Array();
        $data['Asunto'] = "'" . $args['Asunto'] . "'";
        $data['Cuerpo'] = "'" . $args['Cuerpo'] . "'";
        $where = Array('idEMarketing' => $args['idEMarketing']);
        $res_pg = $this->SQLModel->updateFromTable("EMarketing", $data, $where, "idEMarketing");
        return $res_pg;
    }

    public function getEmpresas($args) {
        $qry = '';
        $qry .= 'SELECT e."idEmpresa", e."idEmpresaTipo", e."CodigoCliente", e."DC_NombreComercial", e."DC_Pais", c."Nombre", c."ApellidoPaterno", c."ApellidoMaterno", c."Email", cc."Password", e."idEmpresaTipo", con."idVendedor", ee."UsuarioInvitaciones", ee."PasswordInvitaciones", ';
        $qry .= ' ee."MontajeAndenEntrada",ee."MontajeSalaEntrada",ee."MontajeDiaEntrada",ee."MontajeHorarioEntrada",ee."MontajeAndenSalida",ee."MontajeSalaSalida",ee."MontajeDiaSalida",ee."MontajeHorarioSalida", ';
        $qry .= ' con."idVendedor",con."idContrato",con."ListadoStand",con."idOpcionPago",con."idEmpresaEntidadFiscal" ';
        $qry .= 'FROM "SAS"."Empresa" AS e INNER JOIN "SAS"."EmpresaEdicion" AS ee ON e."idEmpresa" = ee."idEmpresa" ';
        $qry .= 'INNER JOIN "SAS"."Contacto" AS c ON e."idEmpresa" = c."idEmpresa" ';
        $qry .= 'INNER JOIN "SAS"."ContactoEdicion" AS cc ON c."idContacto" = cc."idContacto" ';
        $qry .= 'INNER JOIN "SAS"."Contrato" AS con ON e."idEmpresa" = con."idEmpresa" ';
        $qry .= 'WHERE ';
        $qry .= ' ee."idEvento" = ' . $args['idEvento'] . ' AND ';
        $qry .= ' ee."idEdicion" = ' . $args['idEdicion'] . ' AND ';
        $qry .= ' ee."idEtapa" = 2' . ' AND ';
        $qry .= ' cc."idEvento" = ' . $args['idEvento'] . ' AND ';
        $qry .= ' cc."idEdicion" = ' . $args['idEdicion'] . ' AND ';
        $qry .= ' con."idEvento" = ' . $args['idEvento'] . ' AND ';
        $qry .= ' con."idEdicion" = ' . $args['idEdicion'] . ' AND ';
        $qry .= ' con."idStatusContrato" = 4 AND';
        $qry .= ' cc."Principal" = true';
        $qry .= ' ORDER BY e."DC_NombreComercial" ASC ';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function getEmpresaTipo($args = array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("EmpresaTipo", $fields, $args);
    }

    public function getVendedor($args = array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("Usuario", $fields, $args);#Antes Vendedor
    }

    public function insertDetalleEMarketing($args = array()) {
        $res_pg = $this->SQLModel->insertIntoTable("DetalleEMarketing", $args, "idDetalleEMarketing");
        return $res_pg;
    }

    public function updateDetalleEMarketing($args = array()) {
        $data = Array('Estatus' => $args['Estatus']);
        $where = Array('idDetalleEMarketing' => $args['idDetalleEMarketing']);
        $res_pg = $this->SQLModel->updateFromTable("DetalleEMarketing", $data, $where, "FechaEnvio");
        return $res_pg;
    }

    public function updateTrackingDetalleEMarketing($args = array()) {
        $qry = 'UPDATE "SAS"."DetalleEMarketing" SET "Vista" = TRUE, "FechaUltimaVista" = now(),"NumeroVistas" = (SELECT"NumeroVistas" :: INT FROM "SAS"."DetalleEMarketing" WHERE "idDetalleEMarketing" = ' . $args['idDetalleEMarketing'] . ') + 1,';
        $qry .= '"Pais" = ' . "'" . $args['Pais'] . "'" . ',';
        $qry .= '"Region" = ' . "'" . $args['Region'] . "'" . ',';
        $qry .= '"Ciudad" = ' . "'" . $args['Ciudad'] . "'" . ',';
        $qry .= '"Plataforma" = ' . "'" . $args['Plataforma'] . "'" . ',';
        $qry .= '"UserAgent" = ' . "'" . $args['UserAgent'] . "'" . ',';
        $qry .= '"IP" = ' . "'" . $args['IP'] . "'" . ',';
        $qry .= '"Navegador" = ' . "'" . $args['Navegador'] . "'" . '';
        $qry .= 'WHERE "idDetalleEMarketing" = ' . $args['idDetalleEMarketing'];
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function updateNumerosEmarketing($args = array()) {
        $data = Array();
        $data['NumeroEnvio'] = $args['NumeroEnvio'];
        $data['TotalEnvios'] = $args['TotalEnvios'];
        $where = Array('idEMarketing' => $args['idEMarketing']);
        $res_pg = $this->SQLModel->updateFromTable("EMarketing", $data, $where, "idEMarketing");
        return $res_pg;
    }

    public function getNumeroVistas($args = array()) {
        $qry = 'SELECT SUM("NumeroVistas") as Vistas FROM "SAS"."DetalleEMarketing" WHERE "idEMarketing" = ' . $args['idEMarketing'];
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function getDataEmpresa($args) {
        $qry .= 'SELECT DISTINCT ON (ef."idEmpresa") ef."idEmpresa", ef."DetalleForma",';
        $qry .= 'p."idPabellon", p."NombreEN", p."NombreES" ';
        $qry .= 'FROM "SAS"."EmpresaForma" ef ';
        $qry .= 'INNER JOIN "SAS"."EmpresaStand" es ON ef."idEmpresa" = es."idEmpresaContrato"';
        $qry .= 'INNER JOIN "SAS"."Stand" s ON es."idStand" = s."idStand"';
        $qry .= 'INNER JOIN "SAS"."Pabellon" P ON s."idPabellon" = P ."idPabellon"';
        $qry .= 'AND ef."idEmpresa" = ' . $args['idEmpresa'] . ' AND ';
        $qry .= ' ef."idForma" = 217 AND ';
        $qry .= ' ef."idEvento" = ' . $args['idEvento'] . ' AND ';
        $qry .= ' ef."idEdicion" = ' . $args['idEdicion'] . ' AND ';
        $qry .= ' es."idEvento" = ' . $args['idEvento'] . ' AND ';
        $qry .= ' es."idEdicion" = ' . $args['idEdicion'] . ' ';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function getMontajeActividad($args = array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("MontajeActividad", $fields, $args);
    }

    public function getMontajeVehiculo($args = array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("MontajeVehiculo", $fields, $args);
    }

    public function getEmpresaMontaje($args = array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("EmpresaMontaje", $fields, $args);
    }

    public function getOpcionPago($args = array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("OpcionPago", $fields, $args);
    }

    public function getEmpresaEntidadFiscal($args = array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("EmpresaEntidadFiscal", $fields, $args);
    }

}
