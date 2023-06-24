<?php

namespace Empresa\ContratoBundle\Model;

/**
 * Description of LoginModel
 *
 * @author Juan
 */
use ShowDashboard\DashboardBundle\Model\DashboardModel;
use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;

class ContratoModel extends DashboardModel {

    protected $SQLModel, $PGSQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->PGSQLModel = new PGSQLClient();
    }

    public function getCompany($args = Array()) {
        $fields = array(/* 'idEmpresa', 'idEmpresaTipo', 'DC_NombreComercial', "CodigoCliente" */);
        return $this->SQLModel->selectFromTable("Empresa", $fields, $args);
    }

    public function getEmpresaPadre($args = Array()) {
        $fields = array('idEmpresaPadre');
        return $this->SQLModel->selectFromTable("EmpresaEdicion", $fields, $args);
    }

    public function getStands($args = Array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("Stand", $fields, $args);
    }

    public function getStandsPavellon($args = Array(), $lang) {
        $qry = 'SELECT ' . $this->camposStand($lang) . ' FROM "SAS"."Stand" AS std';
        $qry .= ' INNER JOIN "SAS"."Pabellon" AS pbn ON std."idPabellon" = pbn."idPabellon"';
        $qry .= ' WHERE std."StandStatus" = ' . $args['StandStatus'] . ' AND std."idEvento" = ' . $args['idEvento'] . ' AND std."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' AND pbn."idEvento" = ' . $args['idEvento'] . ' AND pbn."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' ORDER BY std."StandNumber" ASC;';
        return $this->SQLModel->executeQuery($qry);
    }

    private function camposStand($lang) {
        $fields = 'std."idStand", ';
        $fields .= 'std."idTipoStand", ';
        $fields .= 'std."EtiquetaStand", ';
        $fields .= 'std."FechaReserva", ';
        $fields .= 'std."StandArea", ';
        $fields .= 'std."StandNumber", ';
        $fields .= 'std."StandStatus", ';
        $fields .= 'std."Stand_H", ';
        $fields .= 'std."Stand_W", ';
        $fields .= 'pbn."Nombre' . strtoupper($lang) . '" AS "NombrePabellon" ';
        return $fields;
    }

    public function getPabellones($args = Array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("Pabellon", $fields, $args);
    }

    public function getTipoStand($args = Array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("TipoStand", $fields, $args);
    }

    public function getContactos($args = Array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("Contacto", $fields, $args);
    }

    public function getEmpresaTipo($args = Array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("EmpresaTipo", $fields, $args, Array('TipoES' => 'ASC'));
    }

    public function getEmpresaEntidadFiscal($args = Array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("EmpresaEntidadFiscal", $fields, $args);
    }

    public function getVendedores($args = Array()) {
        $qry = 'SELECT usu."idUsuario", usu."Nombre", usu."Email" ';
        $qry .= 'FROM "SAS"."Usuario" usu ';
        $qry .= 'INNER JOIN "SAS"."UsuarioEdicion" use ON usu."idUsuario" = use."idUsuario" ';
        $qry .= 'WHERE ';
        $qry .= ' use."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' AND use."idEvento" = ' . $args['idEvento'];
        $qry .= ' AND usu."idTipoUsuario" = 6';
        return $this->SQLModel->executeQuery($qry);
    }

    public function getOpcionPago($args = Array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("OpcionPago", $fields, $args, Array('idOpcionPago' => 'ASC'));
    }

    public function getStatusContrato($args = Array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("StatusContrato", $fields, $args);
    }

    public function getSocioTipo($args = Array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("SocioTipo", $fields, $args);
    }

    public function getEmpresaCategoria($args = Array()) {
        $qry = 'SELECT cat."NombreCategoriaES", cat."NombreCategoriaEN", empcat."DC_TextoCategoria"';
        $qry .= ' FROM "SAS"."Categoria" as cat';
        $qry .= ' INNER JOIN  "SAS"."EmpresaCategoria" as empcat ON empcat."idCategoria" = cat."idCategoria" ';
        $qry .= ' WHERE cat."idEvento" = ' . $args['idEvento'] . ' AND cat."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' AND empcat."idEvento" = ' . $args['idEvento'] . ' AND empcat."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' AND empcat."idEmpresa" = ' . $args['idEmpresa'] . ' AND empcat."CategoriaComite" = 1;';
        return $this->SQLModel->executeQuery($qry);
    }

    public function getEmpresaEdicion($args = Array()) {
        $fields = array('idEmpresaPadre');
        return $this->SQLModel->selectFromTable("EmpresaEdicion", $fields, $args);
    }

    public function getEmpresaStand($args = Array()) {
        $qry = 'SELECT '
                . 'pb."NombreES" AS "Zona", '
                . 'ts."TipoStand", '
                . 's."idStand", '
                . 's."idTipoStand", '
                . 's."EtiquetaStand", '
                . 's."StandNumber", '
                . 's."StandArea", '
                . 's."Stand_W", '
                . 's."Stand_H", '
                . 'es."idTipoPrecioStand", '
                . 'es."PrecioModificado", '
                . 'es."idEmpresaContrato", '
                . 'es."idEmpresaAsignada", '
                . 'es."Precio", '
                . 'tps."TipoPrecioStand" '
                . 'FROM "SAS"."EmpresaStand" AS es ';
        $qry .= 'INNER JOIN "SAS"."Stand" AS s ON es."idStand" = s."idStand" ';
        $qry .= 'INNER JOIN "SAS"."TipoStand" AS ts ON ts."idTipoStand" = s."idTipoStand" ';
        $qry .= 'INNER JOIN "SAS"."TipoPrecioStand" AS tps ON tps."idTipoPrecioStand" = es."idTipoPrecioStand" ';
        $qry .= 'INNER JOIN "SAS"."Pabellon" AS pb ON pb."idPabellon" = s."idPabellon" ';
        $qry .= 'WHERE es."idEvento" = ' . $args['idEvento'] . ' AND es."idEdicion" = ' . $args['idEdicion'] . ' AND es."idContrato" = ' . $args['idContrato'] . ' AND es."idEmpresaAsignada" = ' . $args['idEmpresa'];
        return $this->SQLModel->executeQuery($qry);
    }

    public function getEmpresaUsuario($idEmpresa) {
        return $this->SQLModel->selectFromTable("EmpresaUsuario", Array('idUsuario'), Array('idEmpresa' => $idEmpresa));
    }

    public function getContrato($args = Array(), $principal = FALSE) {
        $fields = array();
        $result = $this->SQLModel->selectFromTable("Contrato", $fields, $args);
        /* ---  Regresa el contrato a borrador solo cuando entra a la liga que lo redirige al paso que le corresponde  --- */
        /* if ($result['data'][0]['idStatusContrato'] != 1 && $principal) {
          $update = $this->SQLModel->updateFromTable("Contrato", Array('idStatusContrato' => 1), $args);
          if (!$update['status']) {
          throw new \Exception($update['data'], 409);
          }
          } */
        return $result;
    }

    public function insertEntidad($args = Array()) {
        $data = Array(
            'idEmpresa' => $args['idEmpresa'],
            'DF_RazonSocial' => "'" . $args['RazonSocial'] . "'",
            'DF_RFC' => "'" . $args['RFC'] . "'",
            'DF_RepresentanteLegal' => "'" . $args['RepresentanteLegal'] . "'",
            'DF_Email' => "'" . $args['Email'] . "'",
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

    public function insertContacto($args = Array()) {
        $data = Array(
            'idEmpresa' => $args['idEmpresa'],
            'Nombre' => "'" . $args['Nombre'] . "'",
            'ApellidoPaterno' => "'" . $args['ApellidoPaterno'] . "'",
            'ApellidoMaterno' => "'" . $args['ApellidoMaterno'] . "'",
            'Email' => "'" . $args['Email'] . "'",
            'Puesto' => "'" . $args['Puesto'] . "'",
            'Telefono' => "'" . $args['Telefono'] . "'",
        );
        $res_pg = $this->SQLModel->insertIntoTable("Contacto", $data, "idContacto");
        return $res_pg;
    }

    public function saveInformation($args = Array()) {
        $qry = 'SELECT * FROM "SAS"."fn_sas_ContratoInformacion"(' . $args['idContrato'] . ',' . $args['idEvento'] . ',' . $args['idEdicion'] . ',' . $args['idEmpresa'] . ',' . $args['idEmpresaPadre'] . ',' . $args['idEmpresaEntidadFiscal'] . ',' . $args['EmpresaTipo'] . ',' . "'" . $args['Contactos'] . "'" . ');';
        $res_pg = $this->SQLModel->executeQuery($qry);
       
        $this->regresarContratoBorrador(Array("idContrato" => $args['idContrato'], "idEvento" => $args['idEvento'], "idEdicion" => $args['idEdicion'], "idEmpresa" => $args['idEmpresa']));
        return $res_pg;
    }

    public function saveEspacio($args = Array()) {
        $qry = 'SELECT * FROM "SAS"."fn_sas_ContratoEspacio"(' . $args['idContrato'] . ', ' . $args['idEvento'] . ', ' . $args['idEdicion'] . ', ' . $args['idEmpresa'] . ', ' . $args['idVendedor'] . ', ' . $args['idOpcionPago'] . ', ' . "'" . $args['Moneda'] . "'" . ', ' . $args['SubTotal'] . ', ' . $args['IVA'] . ', ' . $args['Total'] . ', ' . "'" . $args['ListadoStand'] . "'" . ', ' . $args['DescuentoCantidad'] . ', ' . $args['DecoracionCantidad'] . ', ' . $args['SubtotalStand'] . ", '" . $args['OtrosConceptos'] . "'" . ', ' . $args['OtrosConceptosCantidad'] . ');';
        $res_pg = $this->SQLModel->executeQuery($qry);
        $this->regresarContratoBorrador(Array("idContrato" => $args['idContrato'], "idEvento" => $args['idEvento'], "idEdicion" => $args['idEdicion'], "idEmpresa" => $args['idEmpresa']));
        return $res_pg;
    }

    public function deleteEspacio($args = Array()) {
        $qry = 'SELECT * FROM "SAS"."fn_sas_DesasignarStandContrato"(' . $args['idContrato'] . ', ' . $args['ListadoStand']['contract'] . ', ' . $args['ListadoStand']['asign'] . ', ' . $args['idEvento'] . ', ' . $args['idEdicion'] . ', ' . "'" . $args['ListadoStand']['record'] . "'" . ');';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function getCostoAdicional($args = Array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("CostoAdicional", $fields, $args, Array('Orden' => 'ASC'));
    }

    public function getContratoCostoAdicional($args = Array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("ContratoCostoAdicional", $fields, $args);
    }

    public function saveProducts($args = Array()) {
        $args['Premios'] = ($args['Premios'] == "{}") ? "null" : "'" . $args['Premios'] . "'";
        $qry = 'SELECT * FROM "SAS"."fn_sas_ContratoProductos"(' . $args['idContrato'] . ', ' . $args['SubTotal'] . ', ' . $args['IVA'] . ', ' . $args['Total'] . ", '" . $args['ListaCostos'] . "', " . $args['Premios'] . ');';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function getEdicion($args = Array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("Edicion", $fields, $args);
    }

    public function setContrato($where, $set) {
        return $this->SQLModel->updateFromTable("Contrato", $set, $where, "idContrato");
    }

    public function autContract($args = Array()) {
        $qry = 'SELECT * FROM "SAS"."fn_sas_AutorizarContrato"(' . $args['idContrato'] . ', ' . $args['idEvento'] . ', ' . $args['idEdicion'] . ', ' . $args['idEmpresa'] . ');';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function canContract($args = Array()) {
        $qry = 'SELECT * FROM "SAS"."fn_sas_CancelarContrato"(' . $args['idContrato'] . ', ' . $args['idEvento'] . ', ' . $args['idEdicion'] . ', ' . $args['idEmpresa'] . ');';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function getPremio($args = Array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("Premio", $fields, $args);
    }

    public function getTipoPrecioStand($args = Array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("TipoPrecioStand", $fields, $args);
    }

    public function getTipoPrecioTipoStand($args = Array()) {
        $qry = 'SELECT ' . $this->camposTipoPrecioTipoStand() . ' FROM "SAS"."TipoStand" AS ts';
        $qry .= ' INNER JOIN "SAS"."TipoPrecioTipoStand" AS tpts ON ts."idTipoStand" = tpts."idTipoStand"';
        $qry .= ' INNER JOIN "SAS"."TipoPrecioStand" AS tp ON tpts."idTipoPrecioStand" = tp."idTipoPrecioStand"';
        $qry .= ' WHERE ts."idEvento" = ' . $args['idEvento'] . ' AND  ts."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' AND tp."idEvento" = ' . $args['idEvento'] . ' AND  tp."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' ORDER BY tpts."idTipoPrecioStand";';
        return $this->SQLModel->executeQuery($qry);
    }

    private function camposTipoPrecioTipoStand() {
        $fields = 'tpts."idTipoPrecioTipoStand", ';
        $fields .= 'ts."idTipoStand", ';
        $fields .= 'tp."idTipoPrecioStand", ';
        $fields .= 'ts."TipoStand", ';
        $fields .= 'ts."AnchoStand", ';
        $fields .= 'ts."AltoStand", ';
        $fields .= 'tp."TipoPrecioStand", ';
        $fields .= 'tpts."PrecioES", ';
        $fields .= 'tpts."PrecioEN" ';
        return $fields;
    }

    public function getImportacionContratos($args) {
        $fields = array();
        return $this->SQLModel->selectFromTable("ImportacionContratos", $fields, $args);
    }

    public function updateContactos($args = Array()) {
        return $this->SQLModel->updateFromTable("ImportacionContratos", Array('Contactos' => "'" . $args['json'] . "'"), Array('idEmpresa' => $args['idEmpresa']), "idEmpresa");
    }

    public function updateStands($args = Array()) {
        return $this->SQLModel->updateFromTable("ImportacionContratos", Array('Stands' => "'" . $args['json'] . "'"), Array('idEmpresa' => $args['idEmpresa']), "idEmpresa");
    }

    public function getContratoCustom($columns = Array(), $params = Array(), $order = Array(), $limit = -1, $offset = -1) {
        $columns_str = '';
        $group_by = ' ';
        if (is_array($columns) && COUNT($columns) > 0) {
            $group_by .= 'GROUP BY';
            foreach ($columns as $column) {
                $columns_str .= ' ' . $column . ',';

                $column_name = $column;
                if (strpos(strtoupper($column), " AS ")) {
                    $column_name = substr($column, 0, strpos(strtoupper($column), " AS "));
                }
                $group_by .= ' ' . $column_name . ',';
            }
            $columns_str = substr($columns_str, 0, -1);
            $group_by = substr($group_by, 0, -1);
        }

        $order_by = ' ';
        if (is_array($order) && COUNT($order) > 0) {
            $order_by .= 'ORDER BY';
            foreach ($order as $order_column) {
                $order_by .= ' ' . $order_column["name"] . ' ' . $order_column["dir"] . ',';
            }
            $order_by = substr($order_by, 0, -1);
        }

        $qry = ' SELECT ' . $columns_str;
        $qry .= 'FROM "SAS"."vw_sas_obtenerContratos"';
        $qry .= ' {where}';
        $qry .= $group_by;
        $qry .= $order_by;
        $q = $qry;
        $q .= ' LIMIT 0';
        $q .= ' OFFSET 100';
        if ($limit != -1 && is_numeric($limit)) {
            $qry .= ' LIMIT ' . $limit;
        }
        if ($offset != -1 && is_numeric($offset)) {
            $qry .= ' OFFSET ' . $offset;
        }
        $result_query = $this->PGSQLModel->execQueryString($qry, $params);

        if ($result_query["status"]) {
            return Array("status" => TRUE, "data" => $result_query['data'], "data_qry" => array("qry" => $q, "params" => $params));
        } else {
            return Array("status" => FALSE, "error" => $result_query["error"]["string"]);
        }
    }

    public function getCountContrato($columns = Array(), $params = Array(), $query = "") {
        if ($query != "") {
            $Result = $this->PGSQLModel->execQueryString($query, $params);
            $Result['count']['qry'] = $query;
            $Result['count']['params'] = $params;
        } else {
            if (is_array($columns) && COUNT($columns) > 0) {
                $group_by .= 'GROUP BY';
                foreach ($columns as $column) {
                    $columns_str .= ' ' . $column . ',';

                    $column_name = $column;
                    if (strpos(strtoupper($column), " AS ")) {
                        $column_name = substr($column, 0, strpos(strtoupper($column), " AS "));
                    }
                    $group_by .= ' ' . $column_name . ',';
                }
                $columns_str = substr($columns_str, 0, -1);
                $group_by = substr($group_by, 0, -1);
            }

            $qry = ' select COUNT(*) as "total"';
            $qry .= ' FROM (';
            $qry .= '     SELECT ' . $columns_str;
            $qry .= '     FROM "SAS"."vw_sas_obtenerContratos"';
            $qry .= '     {where}';
            $qry .= '     ' . $group_by;
            $qry .= ') sq';

            $Result = $this->PGSQLModel->execQueryString($qry, $params);
            $Result['count']['qry'] = $qry;
            $Result['count']['params'] = $params;
        }
        return $Result;
    }

    public function regresarContratoBorrador($args) {
        $update = $this->SQLModel->updateFromTable("Contrato", Array('idStatusContrato' => 1), $args);
        if (!$update['status']) {
            throw new \Exception($update['data'], 409);
        }
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

}
