<?php

namespace Empresa\EmpresaContactoBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class EmpresaContactoModel extends DashboardModel {

    protected $SQLModel, $PGSQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->PGSQLModel = new PGSQLClient();
    }

    public function getGeneralContacts($args = "") {
        $qry = ' SELECT ' . $this->getGeneralContactFields($args);
        $qry .= ' FROM "SAS"."Contacto" c';
        //$qry .= ' LEFT JOIN "SAS"."ContactoEdicion" AS ce ON c."idContacto" = ce."idContacto"';
        $qry .= ' WHERE c."idEmpresa" = ' . $args['c."idEmpresa"'];
        //$qry .= ' AND ce."idEdicion" = ' . $args['ce."idEdicion"'];
        $qry .= ' ORDER BY c."idContacto"';
        $result = $this->SQLModel->executeQuery($qry);

        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idContacto']] = $value;
                }
            }
            return $data;
        }
    }

    private function getGeneralContactFields($args) {
        $fields = '';
        $fields .= ' c."idContacto",';
        $fields .= ' c."idEmpresa",';
        $fields .= ' c."Nombre",';
        $fields .= ' c."ApellidoPaterno",';
        $fields .= ' c."ApellidoMaterno",';
        $fields .= ' (c."Nombre" ';
        $fields .= " || ' ' || ";
        $fields .= 'c."ApellidoPaterno"';
        $fields .= ') AS NombreCompleto,';
        $fields .= ' c."Email",';
        $fields .= ' c."EmailAlterno",';
        $fields .= ' c."Puesto",';
        $fields .= ' c."Telefono", ';
        $fields .= ' c."Celular" ';
        //$fields .= ' ce."Password" ';

        return $fields;
    }

    public function getEditionidContacts($args = "") {
        $qry = ' SELECT ' . $this->getEditionContactFields($args);
        $qry .= ' FROM "SAS"."Contacto" c';
        $qry .= ' LEFT JOIN "SAS"."ContactoEdicion" ce';
        $qry .= ' ON c."idContacto" = ce."idContacto"';
        $qry .= ' WHERE c."idEmpresa" = ' . $args['c."idEmpresa"'];
        $qry .= ' AND ce."idEdicion" = ' . $args['ce."idEdicion"'];
        $qry .= ' AND ce."idContactoTipo" <> 7 AND ce."idContactoTipo" <> 8';
        $qry .= ' ORDER BY ce."idContacto";';
        $result = $this->SQLModel->executeQuery($qry);

        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idContacto']] = $value;
                }
            }
            return $data;
        }
    }

    public function getEditionContacts($args = "") {
        $qry = ' SELECT DISTINCT ON(ce."idContactoTipo") ' . $this->getEditionContactFields($args);
        $qry .= ' FROM "SAS"."Contacto" c';
        $qry .= ' LEFT JOIN "SAS"."ContactoEdicion" ce';
        $qry .= ' ON c."idContacto" = ce."idContacto"';
        $qry .= ' WHERE c."idEmpresa" = ' . $args['c."idEmpresa"'];
        $qry .= ' AND ce."idEdicion" = ' . $args['ce."idEdicion"'];
        $qry .= ' AND ce."idContactoTipo" <> 7 AND ce."idContactoTipo" <> 8';
        $qry .= ' ORDER BY ce."idContactoTipo";';
        $result = $this->SQLModel->executeQuery($qry);

        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idContactoTipo']] = $value;
                }
            }
            return $data;
        }
    }

    private function getEditionContactFields($args) {
        $fields = '';
        $fields .= ' c."idContacto",';
        $fields .= ' c."idEmpresa",';
        $fields .= ' ce."idContactoTipo",';
        $fields .= ' c."Nombre",';
        $fields .= ' c."ApellidoPaterno",';
        $fields .= ' c."ApellidoMaterno",';
        $fields .= ' (c."Nombre" ';
        $fields .= " || ' ' || ";
        $fields .= 'c."ApellidoPaterno"';
        $fields .= ') AS NombreCompleto,';
        $fields .= ' c."Email",';
        $fields .= ' c."EmailAlterno",';
        $fields .= ' ce."Password",';
        $fields .= ' c."Puesto",';
        $fields .= ' c."Telefono", ';
        $fields .= ' c."Celular", ';
        $fields .= ' ce."Principal"';

        return $fields;
    }

    public function getPackages($args) {
        $qry = ' SELECT ' . $this->getPackagesFields();
        $qry .= ' FROM "SAS"."Paquete" p';
        $qry .= ' WHERE p."idEdicion" = ' . $args['p."idEdicion"'];
        $qry .= ' ORDER BY p."idPaquete"';
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

    public function getContactTypes($lang) {
        $qry = ' SELECT c."idContactoTipo", c."ContactoTipoES", c."ContactoTipoEN", c."ContactoTipoPT", c."ContactoTipoFR" ';
        $qry .= ' FROM "SAS"."ContactoTipo" c';
        $qry .= ' WHERE c."idContactoTipo" <> 7 AND c."idContactoTipo" <> 8 ';
        $qry .= ' ORDER BY c."ContactoTipo' . strtoupper($lang) . '"';
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idContactoTipo']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    public function insertContact($args) {
        $qry = 'SELECT * FROM "SAS"."fn_sas_ActualizarContactoEmpresa"(';
        $qry .= "'" . $args['idEmpresa'] . "',";
        $qry .= "'" . $args['idEvento'] . "',";
        $qry .= "'" . $args['idEdicion'] . "',";
        if ($args["idContactoTipo"] == "" || $args["idContactoTipo"] == null)
            $qry .= "null,";
        else
            $qry .= "'" . $args['idContactoTipo'] . "',";
        $qry .= "'" . $args['Nombre'] . "',";
        $qry .= "'" . $args['ApellidoPaterno'] . "',";
        $qry .= "'" . $args['ApellidoMaterno'] . "',";
        $qry .= "'" . $args['Email'] . "',";
        $qry .= "'" . $args['EmailAlterno'] . "',";
        $qry .= "'" . $args['Puesto'] . "',";
        $qry .= "'" . $args['Telefono'] . "',";
        $qry .= "'" . $args['Celular'] . "');";
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function updateContact($args1, $args2, $id) {
        $result = $this->SQLModel->updateFromTable("Contacto", $args1, array("idContacto" => $id), "idContacto");

        $qry = 'SELECT COUNT(ce."idContacto") FROM "SAS"."ContactoEdicion" ce';
        $qry .= ' WHERE ce."idEmpresa" = ' . $args2["idEmpresa"];
        $qry .= ' AND ce."idEdicion" = ' . $args2["idEdicion"];
        $qry .= ' AND ce."idContacto" = ' . $id;
        $qry .= ' AND ce."idContactoTipo" = ' . $args2["idContactoTipo"];
        $count = $this->SQLModel->executeQuery($qry)["data"][0]["count"];

        if ($count == 0) {
            $conditions = Array("idContactoTipo" => $args2["idContactoTipoActual"],
                "idEmpresa" => $args2["idEmpresa"],
                "idEdicion" => $args2["idEdicion"],
                "idContacto" => $id);
            $data = Array("idContactoTipo" => $args2["idContactoTipo"], "Password" => $args2["Password"]);
            $result2 = $this->SQLModel->updateFromTable("ContactoEdicion", $data, $conditions, "");

            return $result;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    public function deleteContact($args) {
        $qry = 'SELECT * FROM "SAS"."fn_sas_EliminaContacto"(';
        $qry .= "'" . $args['idContacto'] . "',";
        $qry .= "'" . $args['idEmpresa'] . "',";
        $qry .= "'" . $args['idEvento'] . "',";
        $qry .= "'" . $args['idEdicion'] . "');";
        $result = $this->SQLModel->executeQuery($qry);

        return $result;
    }

    public function changeContact($args = array()) {
        $qry = 'SELECT * FROM "SAS"."fn_sas_ContactoPrincipal"(';
        $qry .= "'" . $args['idEmpresa'] . "',";
        $qry .= "'" . $args['idEvento'] . "',";
        $qry .= "'" . $args['idEdicion'] . "',";
        $qry .= "'" . $args['idContactoTipo'] . "',";
        $qry .= "'" . $args['idNuevo'] . "');";
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
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

    public function getContact($args) {
        $fields = array();
        return $this->SQLModel->selectFromTable("Contacto", $fields, $args);
    }

    public function getEditionContact($args) {
        $qry = 'SELECT c."idContacto" ';
        $qry .= 'FROM "SAS"."Contacto" AS c  ';
        $qry .= 'INNER JOIN "SAS"."ContactoEdicion" AS ce  ';
        $qry .= ' ON  c."idContacto" = ce."idContacto" ';
        $qry .= ' WHERE c."idEmpresa" = ' . $args['idEmpresa'];
        $qry .= ' AND c."Email" = ' . $args['Email'];
        $qry .= ' AND c."Nombre" = ' . $args['Nombre'];
        $qry .= ' AND c."ApellidoPaterno" = ' . $args['ApellidoPaterno'];
        $qry .= ' AND ce."idContactoTipo" = ' . $args['idContactoTipo'];
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
