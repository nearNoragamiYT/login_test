<?php

namespace ShowDashboard\LT\LectorasBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use ShowDashboard\DashboardBundle\Model\DashboardModel;
use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;

class LectorasModel extends DashboardModel {

    protected $SQLModel, $PGSQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->SQLModel->setSchema("LECTORAS");
        $this->PGSQLModel = new PGSQLClient();
    }

    public function getCountExhibtors($args = Array(), $conditions = Array()) {
        $qry = ' select COUNT(*) as "Total"';
        $qry .= ' FROM (';
        $qry .= '     SELECT ' . $this->getExhibitorsDateFields();
        $qry .= '     FROM "LECTORAS"."vw_sas_ObtenerExpositores_nuevo"';
        $qry .= '     WHERE "idEvento"=' . $args["idEvento"] . ' AND "idEdicion" = ' . $args["idEdicion"];
        foreach ($conditions as $field) {
            $qry .= " AND ";
            $qry .= $field;
        }
        $qry .= ') sq';
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"][0]["Total"];
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    public function getExhibitors($args = Array(), $conditions = Array()) {
        $qry .= '     SELECT ' . $this->getExhibitorsDateFields();
        $qry .= '     FROM "LECTORAS"."vw_sas_ObtenerExpositores_nuevo"';
        $qry .= '     WHERE "idEvento"=' . $args["idEvento"] . ' AND "idEdicion" = ' . $args["idEdicion"];
        foreach ($conditions as $field) {
            $qry .= " AND ";
            $qry .= $field;
        }
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    public function getTypes($idEdicion) {
        $qry = ' SELECT ' . $this->getTypeFields();
        $qry .= ' FROM "SAS"."EmpresaTipo" et';
        $qry .= ' WHERE et."idEdicion" = ' . $idEdicion;
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idEmpresaTipo']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getTypeFields() {
        $fields = '';
        $fields .= ' et."idEmpresaTipo",';
        $fields .= ' et."TipoEN",';
        $fields .= ' et."TipoES",';
        $fields .= ' et."TipoFR",';
        $fields .= ' et."TipoPT" ';
        return $fields;
    }

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

    public function getCompanyHeader($args = "") {
        $qry = ' SELECT e."idEmpresa", e."DC_NombreComercial"';
        $qry .= ' FROM "SAS"."Empresa" e';
        $qry .= ' WHERE e."idEmpresa" = ' . $args['e."idEmpresa"'];
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $qry = ' SELECT ee."idEtapa", ee."EMSTDListadoStand", ee."idPaquete", ce."idContacto"';
            $qry .= ' FROM "SAS"."EmpresaEdicion" AS ee';
            $qry .= ' INNER JOIN "SAS"."ContactoEdicion" AS ce ON ee."idEmpresa" = ce."idEmpresa"';
            $qry .= ' WHERE ee."idEmpresa" = ' . $args['e."idEmpresa"'] . ' AND ee."idEdicion" = ' . $args['ee."idEdicion"'];
            $qry .= ' AND ce."idEmpresa" = ' . $args['e."idEmpresa"'] . ' AND ce."idEdicion" = ' . $args['ee."idEdicion"'] . ' AND ce."Principal" = ' . "'t'";
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

    public function getExhibitorsDateFields() {
        $fields = '';
        $fields .= ' "idEmpresa",';
        $fields .= ' "CodigoCliente",';
        $fields .= ' "DC_NombreComercial",';
        // $fields .= ' "ContratoEntidadFiscal",';
        $fields .= ' "NombreContacto",';
        $fields .= ' "Email",';
        $fields .= ' "EMSTDListadoStand",';
        $fields .= ' "LectorasSolicitadas",';
        $fields .= ' "LectorasAsignadas",';
        $fields .= ' "LectorasDevueltas"';
        return $fields;
    }

    public function getLectorasEmpresa($params) {
        $fields = array(
            "idEmpresaScanner",
            "idEmpresa",
            "CodigoScanner",
            "EtiquetaApp",
            "idScannerTipo",
            "ScannerTipo",
            "idStatusScanner",
            "Status",
            "Cortesia",
            "idScanner",
            "AppIxpo",
            "EstadoDisponibilidad",
            "idStatusAsignacion"
        );
        $result = $this->SQLModel->selectFromTable("vw_sas_ObtenerLectorasEmpresa", $fields, $params);
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value["idEmpresaScanner"]] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getLectorasEmpresaReporte($params) {
        $fields = array(
            "idEmpresaScanner",
            "idEmpresa",
            "CodigoCliente",
            "DC_NombreComercial",
            "DF_RazonSocial",
            "ScannerTipo",
            "CodigoScanner",
            "Status",
            "StatusPago"
        );
        $result = $this->SQLModel->selectFromTable("vw_sas_ReporteLectorasEmpresa", $fields, $params);
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value["idEmpresaScanner"]] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getScannerTipo($args) {
        $fields = array(
            "idScannerTipo",
            "ScannerTipo",
            "RequierePassport",
            "Duracion",
            "AppIxpo"
        );
        $result = $this->SQLModel->selectFromTable("ScannerTipo", $fields, $args);
        if (($result['status'] && count($result['data']) > 0)) {
            $data = Array();
            foreach ($result['data'] as $value) {
                $data[$value['idScannerTipo']] = $value;
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getStatusScanner() {
        $fields = array(
            "idStatusScanner",
            "Status",
            "Descripcion",
        );
        $result = $this->SQLModel->selectFromTable("StatusScanner", $fields);
        if (($result['status'] && count($result['data']) > 0)) {
            $data = Array();
            foreach ($result['data'] as $value) {
                $data[$value['idStatusScanner']] = $value;
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getLectoras($args) {
        $fields = array(
            "idScanner",
            "CodigoScanner",
            "idScannerTipo",
            "ScannerActivo",
            "idEvento",
            "idEdicion"
        );
        $result = $this->SQLModel->selectFromTable("Scanner", $fields, $args);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"][0];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function insertLectora($fields) {
        $result = $this->SQLModel->insertIntoTable("Scanner", $fields, "idScanner");
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function updateLectora($fields, $args) {
        $result = $this->SQLModel->updateFromTable("Scanner", $fields, $args, "idScanner");
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function insertEmpresaScanner($fields) {
        $result = $this->SQLModel->insertIntoTable("EmpresaScanner", $fields, "idEmpresaScanner");
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function updateEmpresaScanner($fields, $args) {
        $result = $this->SQLModel->updateFromTable("EmpresaScanner", $fields, $args);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function deleteEmpresaScanner($args) {
        $result = $this->SQLModel->deleteFromTable("EmpresaScanner", $args);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function getContactoPago($idEdicion, $idEmpresa, $idContacto) {
        $qry = 'SELECT CONCAT(co."Nombre",' . "' '" . ', co."ApellidoPaterno", ' . " ' '" . ', co."ApellidoMaterno") AS "NombreCompleto", co."Email", co."Celular", co."Puesto", ';
        $qry .= 'CONCAT(co."TelefonoCodigoPais",' . "' '" . ', co."TelefonoArea", ' . "' '" . ', co."Telefono",' . " ' EXT: '" . ', co."TelefonoExtension") AS "TelefonoCompleto" ';
        $qry .= 'FROM "SAS"."Contacto" AS co ';
        $qry .= 'INNER JOIN "SAS"."ContactoEdicion" as ce ON co."idContacto" = ce."idContacto" ';
        $qry .= 'WHERE ce."idEdicion" = ' . $idEdicion . ' AND ce."idEmpresa" = ' . $idEmpresa;
        if ($idContacto != null && $idContacto != "") {
            $qry .= ' AND ce."idContacto" = ' . $idContacto;
        } else {
            $qry .= ' AND ce."Principal" = ' . "'t'";
        }
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        return $result['data'][0];
    }

    public function getSoliciudLectorasReporte($params) {
        $fields = array(
            "idEmpresa",
            "CodigoCliente",
            "DC_NombreComercial",
            // "ContratoEntidadFiscal",
            "NombreContacto",
            "Email",
            "EMSTDListadoStand",
            "LectorasSolicitadas",
            "RentasSitio",
            "NumeroLectorasCortesia",
            "SustitucionEquipo",
            "LectorasAsignadas",
            "LectorasRecibidas"
        );

        $result = $this->SQLModel->selectFromTable("vw_sas_ObtenerSolicitudLectoras_nuevo", $fields, $params);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result['data'];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getEmpresaEntidadFiscal($args = Array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("EmpresaEntidadFiscal", $fields, $args);
    }

    public function getStatusPago() {
        $this->SQLModel->setSchema("SAS");
        $fields = array();
        return $this->SQLModel->selectFromTable("StatusPago", $fields, array(), array('"idStatusPago"' => 'ASC'));
    }

    public function updateStatusPago($fields, $args) {
        return $result = $this->SQLModel->updateFromTable("EmpresaForma", $fields, $args);
    }

    public function getFormaPago() {
        $fields = array();
        return $this->SQLModel->selectFromTable("FormaPago", $fields);
    }

    public function updateFormaPago($fields, $args) {
        return $result = $this->SQLModel->updateFromTable("EmpresaForma", $fields, $args);
    }

    public function updateFechaPago($fields, $args) {
        return $result = $this->SQLModel->updateFromTable("EmpresaForma", $fields, $args);
    }

    public function updateDetallePago($fields, $args) {
        return $result = $this->SQLModel->updateFromTable("EmpresaForma", $fields, $args);
    }

    public function getReporteSeguimiento($params) {
        $qry = ' SELECT ' . $this->getLectorasFields();
        $qry .= ' FROM "SAS"."vw_sas_ReporteSeguimientoLectoras" ';
        $qry .= ' WHERE "idEdicion" = ' . $params["idEdicion"];
        $qry .= 'ORDER BY "idEmpresa"';
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    protected function getLectorasFields() {
        $fields = '';
        $fields .= ' "idEmpresa",';
        $fields .= ' "CodigoCliente",';
        $fields .= ' "NombreComercial",';
        $fields .= ' "ListadoStand",';
        $fields .= ' ("Contacto_Contratacion_Nombre" ';
        $fields .= " || ' ' || ";
        $fields .= '"Contacto_Contratacion_ApellidoPaterno"';
        $fields .= ') AS NombreContactoContratacion,';
        $fields .= ' "Contacto_Contratacion_Email",';
        $fields .= ' "Contacto_Contratacion_Telefono",';
        $fields .= ' ("Contacto_Forma_Nombre" ';
        $fields .= " || ' ' || ";
        $fields .= '"Contacto_Forma_Apellido_Paterno"';
        $fields .= ') AS NombreContactoForma,';
        $fields .= ' "Contacto_Forma_Email",';
        $fields .= ' "Contacto_Forma_Telefono_Completo",';
        $fields .= ' "Contacto_Forma_Celular",';
        $fields .= ' "Detalle_Servicio",';
        $fields .= ' "TipoLectora",';
        $fields .= ' "StatusPago",';
        $fields .= ' "NoFactura"';

        return $fields;
    }

    public function generateEMFO($fields) {
        return $result = $this->SQLModel->insertIntoTable("EmpresaForma", $fields);
    }

    public function getMailContactoPrincipal($args) {
        $qry = ' SELECT c."Email"';
        $qry .= ' FROM "SAS"."Contacto" c ';
        $qry .= ' JOIN "SAS"."ContactoEdicion" ce ';
        $qry .= ' ON c."idContacto" = ce."idContacto" ';
        $qry .= ' WHERE ce."idEdicion" = ' . $args["idEdicion"];
        $qry .= ' AND ce."idEvento" = ' . $args["idEvento"];
        $qry .= ' AND ce."Principal" = TRUE';
        $qry .= ' AND ce."idEmpresa" =' . $args['idEmpresa'];
        $qry .= ' AND ce."idContacto" =' . $args['idContacto'];
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"][0];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getLecturas($args) {
        $fields = array(
            "idEmpresaScanner",
            "idLectura"
        );
        $result = $this->SQLModel->selectFromTable("Lecturas", $fields, $args);
        if (isset($result['status']) && $result['status'] == 1) {
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getSolicitudLectoras($args) {
        $qry = 'SELECT cd."idServicio", sum(cd."Cantidad") as "Cantidad"';
        $qry .= ' FROM "SAS"."Compra_SAS" c ';
        $qry .= ' JOIN "SAS"."CompraDetalle_SAS" cd';
        $qry .= ' ON c."idCompra" = cd."idCompra"';
        $qry .= ' JOIN "SAS"."Servicio" s';
        $qry .= ' ON s."idServicio" = cd."idServicio"';
        $qry .= ' WHERE c."idEmpresa" = '. $args['"idEmpresa"'];
        $qry .= ' AND c."idEdicion" = '. $args['"idEdicion"'];
        $qry .= ' AND s."idEdicion" = '. $args['"idEdicion"'];
        $qry .= ' AND c."idForma" = '. $args['"idForma"'];
        $qry .= ' GROUP BY cd."idServicio"';
        $result = $this->SQLModel->executeQuery($qry);
        // print_r($result); die();
        // $fields = array(
        //     "DetalleServicioJSON",
        //     "DetallePagoJSON",
        //     "StatusPago",
        //     "Lang",
        //     "idFormaPago",
        //     "ModificacionComite",
        //     "FechaActualizacionStatusPago",
        //     "Subtotal",
        //     "IVA",
        //     "Total",
        //     "StatusForma",
        //     "FechaPrimerGuardado"
        // );
        // $this->SQLModel->setSchema("SAS");
        // $result = $this->SQLModel->selectFromTable("EmpresaForma", $fields, $args);
        // $this->SQLModel->setSchema("LECTORAS");
        // print_r($result); die();
        if (isset($result['status']) && $result['status'] == 1) {
            // return $result["data"][0];
            return $result["data"];
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getServicios($args) {
        $this->SQLModel->setSchema("SAS");
        $result = $this->SQLModel->selectFromTable("Servicio", $this->getCamposServiciosForma(), $args, array('Orden' => 'ASC'));
        $this->SQLModel->setSchema("LECTORAS");
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
            'TituloEN',
            'TituloES',
        );
        return $fields;
    }

}
