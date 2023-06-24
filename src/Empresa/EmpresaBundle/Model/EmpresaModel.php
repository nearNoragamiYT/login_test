<?php

namespace Empresa\EmpresaBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class EmpresaModel extends DashboardModel {

    protected $SQLModel, $PGSQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->PGSQLModel = new PGSQLClient();
    }

    public function getEmpresaCustom($columns = Array(), $params = Array(), $order = Array(), $limit = -1, $offset = -1) {
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
        $qry .= 'FROM "SAS"."vw_sas_ObtenerEmpresas201"';
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

    public function getCountEmpresa($columns = Array(), $params = Array(), $query = "") {
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
            $qry .= '     FROM "SAS"."vw_sas_ObtenerEmpresas201"';
            $qry .= '     {where}';
            $qry .= '     ' . $group_by;
            $qry .= ') sq';

            $Result = $this->PGSQLModel->execQueryString($qry, $params);
            $Result['count']['qry'] = $qry;
            $Result['count']['params'] = $params;
        }
        return $Result;
    }

    public function getCompanies($args = "", $idEdicion = 0) {
        $qry = ' SELECT ' . $this->getCompaniesFields();
        $qry .= ' FROM "SAS"."Empresa" e';
        $qry .= ' LEFT JOIN "SAS"."Contacto" c';
        $qry .= ' ON e."idEmpresa" = c."idEmpresa"';
        $qry .= ' LEFT JOIN "SAS"."ContactoEdicion" ce';
        $qry .= ' ON c."idContacto" = ce."idContacto"';
        $qry .= ' LEFT OUTER JOIN "SAS"."EmpresaEdicion" ed';
        $qry .= ' ON e."idEmpresa" = ed."idEmpresa"';
        $qry .= ' WHERE ce."Principal" = ' . $args['c."Principal"'];
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            foreach ($result['data'] as $value) {
                $data['data'][$value['idEmpresa']] = $value;
            }
            return $data['data'];
        } else {
            return NULL;
        }
    }

    private function getCompaniesFields() {
        $fields = '';
        $fields .= ' e."idEmpresa", ';
        $fields .= ' e."CodigoCliente", ';
        $fields .= ' ed."idEvento", ';
        $fields .= ' ed."idEdicion", ';
        $fields .= ' ed."idEtapa", ';
        $fields .= ' e."DC_NombreComercial", ';
        $fields .= ' (c."Nombre" ';
        $fields .= " || ' ' || ";
        $fields .= 'c."ApellidoPaterno") AS "NombreCompleto", ';
        $fields .= ' c."Email", ';
        $fields .= ' ce."Password", ';
        $fields .= ' ed."EMSTDListadoStand", ';
        $fields .= ' ed."EMSTDMetrosCuadrados", ';
        $fields .= ' ed."idPaquete", ';

        $fields .= ' e."idEmpresaTipo", ';
        $fields .= ' ed."Coexpositor", ';
        $fields .= ' e."FechaCreacion", ';
        $fields .= ' e."FechaModificacion", ';
        $fields .= ' e."ED_Acceso", ';
        $fields .= ' e."ED_FechaUltimoAcceso" ';

        return $fields;
    }

    public function getPaquetes($idEdicion, $lang) {
        $fields = array("idPaquete", "Paquete" . strtoupper($lang));
        $args = array('idEdicion' => $idEdicion);
        $result = $this->SQLModel->selectFromTable("Paquete", $fields, $args, array('"idPaquete"' => 'ASC'));
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idPaquete']] = $value['Paquete' . strtoupper($lang)];
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getExpositores($params) { 
        $fields = array("DC_NombreComercial", "Email", "Password", "idEdicion", "DC_idPais", "CodigoCliente", "Vendedores", "Nombre");
        $result = $this->SQLModel->selectFromTable("vw_sas_ObtenerEmpresas201", $fields, $params);

        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }
    
    public function getDetalleGafete($params) {
        $qry = ' SELECT dg."idForma", dg."idDetalleGafete", dg."idEmpresa", dg."idEdicion", dg."DGNombre", dg."DGApellidoPaterno", dg."DGEmpresa", dg."DGPuesto", ca."idCargo", ca."DescripcionES"';
        $qry .= ' FROM "SAS"."DetalleGafete" dg';
        $qry .= ' JOIN "SAS"."CargoSAS" ca ON CAST ( dg."DGPuesto" AS INTEGER ) = ca."idCargo"';
        $qry .= ' WHERE "idEdicion" = ' . $params['idEdicion'];
        $qry .= ' AND "idEmpresa" = ' . $params['idEmpresa'];
        $qry .= ' AND "idForma" = 201';
        
        $result = $this->SQLModel->executeQuery($qry);

         if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getExpositoresMasivos($params) {
        $fields = array("idEmpresa", "DC_NombreComercial", "Email", "Password", "idEdicion", "DC_idPais", "CodigoCliente", "Vendedores");
        $result = $this->SQLModel->selectFromTable("vw_sas_ObtenerEmpresas201", $fields, $params);
        
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    ///////////////////////////////// sin uso ////////////////////////////////

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
        $fields .= ' e."Edicion_ES",';
        $fields .= ' e."Edicion_EN",';
        $fields .= ' e."Edicion_PT",';
        $fields .= ' e."Edicion_FR", ';
        $fields .= ' e."Header_Contrato_ES",';
        $fields .= ' e."Header_Contrato_EN",';
        $fields .= ' e."Header_Contrato_PT",';
        $fields .= ' e."Header_Contrato_FR", ';
        $fields .= ' e."LinkED" ';
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

    public function getEmpresaTipo($idEdicion, $lang) {
        $fields = array("idEmpresaTipo", "Tipo" . strtoupper($lang));
        $args = array('idEdicion' => $idEdicion);
        $result = $this->SQLModel->selectFromTable("EmpresaTipo", $fields, $args, array('"idEmpresaTipo"' => 'ASC'));
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idEmpresaTipo']] = $value['Tipo' . strtoupper($lang)];
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

}
