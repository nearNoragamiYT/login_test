<?php

namespace Empresa\EmpresaFichaMontajeBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class FichaMontajeModel extends DashboardModel {

    protected $SQLModel, $PGSQLModel;
    protected $path_cache_ed = "../app/cache/ed/";

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getSellers($args) {
        $qry = 'SELECT DISTINCT usu."idUsuario", usu."Nombre", usu."Email"';
        $qry .= 'FROM "SAS"."Usuario" usu ';
        $qry .= 'INNER JOIN "SAS"."EmpresaUsuario" empu ';
        $qry .= 'ON usu."idUsuario" = empu."idUsuario"';
        $qry .= 'INNER JOIN "SAS"."Contrato" con ';
        $qry .= 'ON empu."idEmpresa" = con."idEmpresa" ';
        $qry .= 'WHERE con."idEvento"=' . $args['idEvento'] . ' AND con."idEdicion" = ' . $args['idEdicion'] .'AND con."idStatusContrato"= 4' ;
        $qry .= ' ORDER BY usu."idUsuario"';
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idUsuario']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    function getContactoPrincipal($args) {
        $qry = 'SELECT em."idEmpresa", con."idContacto", cone."Principal", con."Nombre", con."ApellidoMaterno", con."ApellidoPaterno", con."Email"';
        $qry .= ' FROM "SAS". "Empresa" em';
        $qry .= ' INNER JOIN "SAS". "Contacto" con ';
        $qry .= ' ON em."idEmpresa"= con."idEmpresa"';
        $qry .= ' INNER JOIN "SAS". "ContactoEdicion"cone';
        $qry .= ' ON con."idContacto"= cone."idContacto" AND cone."Principal"= TRUE';
        $qry .= ' WHERE em."idEmpresa"='. $args['idEmpresa'] . ' AND cone."idEdicion" =' . $args['idEdicion'];
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        return $result['data'];
    }

    function getEmpresas($where, $lang) {
        $qry = 'SELECT DISTINCT co."idEmpresa", e."DC_NombreComercial", usu."idUsuario", usu."Nombre" ';
        $qry .= 'FROM "SAS"."Usuario" AS usu ';
        $qry .= 'INNER JOIN "SAS"."EmpresaUsuario" AS empu ';
        $qry .= 'ON usu."idUsuario" = empu."idUsuario" ';
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

    public function getFichasMontaje($condition, $idEmpresa = NULL) {
        $qry .= 'SELECT DISTINCT ON (e."idEmpresa") e."idEmpresa",e."DC_NombreComercial",e."idEmpresaTipo",e."CodigoCliente",ef."DetalleForma",';
        $qry .= 'p."idPabellon", p."NombreEN", p."NombreES",';
        $qry .= 'empu."idUsuario",c."idContrato",c."ListadoStand",c."idOpcionPago",c."idEmpresaEntidadFiscal",';
        $qry .= 'ee."MontajeAndenEntrada",ee."MontajeSalaEntrada",ee."MontajeDiaEntrada",ee."MontajeHorarioEntrada",ee."MontajeAndenSalida",ee."MontajeSalaSalida",ee."MontajeDiaSalida",ee."MontajeHorarioSalida"';
        $qry .= 'FROM "SAS"."Empresa" e ';
        $qry .= 'INNER JOIN "SAS"."EmpresaUsuario" empu ON e."idEmpresa" = empu."idEmpresa" ';
        $qry .= 'INNER JOIN "SAS"."EmpresaStand" es ON e."idEmpresa" = es."idEmpresaContrato" ';
        $qry .= 'INNER JOIN "SAS"."Stand" s ON es."idStand" = s."idStand" ';
        $qry .= 'INNER JOIN "SAS"."Pabellon" P ON s."idPabellon" = P ."idPabellon" ';
        $qry .= 'LEFT JOIN "SAS"."EmpresaForma" ef ON e."idEmpresa" = ef."idEmpresa" ';
        $qry .= 'INNER JOIN "SAS"."Contrato" c ON e."idEmpresa" = c."idEmpresa" ';
        $qry .= 'INNER JOIN "SAS"."EmpresaEdicion" ee ON c."idEmpresa" = ee."idEmpresa" ';
        $qry .= 'WHERE ee."idEdicion" = ' . $condition['idEdicion'] . ' AND ';
        $qry .= ' es."idEdicion" = ' . $condition['idEdicion'] . ' AND ';
        $qry .= ' c."idEvento" = ' . $condition['idEvento'] . ' AND ';
        $qry .= ' c."idEdicion" = ' . $condition['idEdicion'] . ' AND ';
        $qry .= ' ee."idEvento" = ' . $condition['idEvento'] . ' AND ';
        $qry .= ' ee."idEtapa" = ' . $condition['idEtapa'] . ' AND ';
        /* $qry .= ' ef."idEvento" = ' . $condition['idEvento'] . ' AND ';
          $qry .= ' ef."idEdicion" = ' . $condition['idEdicion'] . ' AND ';
          $qry .= ' ef."idForma" = ' . $condition['idForma'] . ' AND '; */
        if ($idEmpresa != NULL) {
            $qry .= ' e."idEmpresa" = ' . $idEmpresa . ' AND ';
        }
        $qry .= ' empu."idUsuario" = ' . $condition['idUsuario'] . ' AND ';
        $qry .= ' c."idStatusContrato" = ' . $condition['idContratoStatus'];
        $qry .= ' ORDER BY e."idEmpresa";';
        
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        return $result['data'];
    }

    public function getFichasMontajeAll($condition) {
        $qry .= 'SELECT DISTINCT ON (e."idEmpresa") e."idEmpresa",e."DC_NombreComercial",e."idEmpresaTipo",e."CodigoCliente",ef."DetalleForma",';
        $qry .= 'p."idPabellon", p."NombreEN", p."NombreES",';
        $qry .= 'empu."idUsuario",c."idContrato",c."ListadoStand",c."idOpcionPago",c."idEmpresaEntidadFiscal",';
        $qry .= 'ee."MontajeAndenEntrada",ee."MontajeSalaEntrada",ee."MontajeDiaEntrada",ee."MontajeHorarioEntrada",ee."MontajeAndenSalida",ee."MontajeSalaSalida",ee."MontajeDiaSalida",ee."MontajeHorarioSalida"';
        $qry .= 'FROM "SAS"."Empresa" e ';
        $qry .= 'INNER JOIN "SAS"."EmpresaUsuario" empu ON e."idEmpresa" = empu."idEmpresa" ';
        $qry .= 'INNER JOIN "SAS"."EmpresaStand" es ON e."idEmpresa" = es."idEmpresaContrato"';
        $qry .= 'INNER JOIN "SAS"."Stand" s ON es."idStand" = s."idStand"';
        $qry .= 'INNER JOIN "SAS"."Pabellon" P ON s."idPabellon" = P ."idPabellon"';
        $qry .= 'INNER JOIN "SAS"."EmpresaForma" ef ON e."idEmpresa" = ef."idEmpresa"';
        $qry .= 'INNER JOIN "SAS"."Contrato" c ON ef."idEmpresa" = c."idEmpresa"';
        $qry .= 'INNER JOIN "SAS"."EmpresaEdicion" ee ON c."idEmpresa" = ee."idEmpresa"';
        $qry .= 'AND ee."idEdicion" = ' . $condition['idEdicion'] . ' AND ';
        $qry .= ' ee."idEvento" = ' . $condition['idEvento'] . ' AND ';
        $qry .= ' ee."idEtapa" = ' . $condition['idEtapa'] . ' AND ';
        $qry .= ' c."idEvento" = ' . $condition['idEvento'] . ' AND ';
        $qry .= ' c."idEdicion" = ' . $condition['idEdicion'] . ' AND ';
        /* $qry .= ' ef."idEvento" = ' . $condition['idEvento'] . ' AND ';
          $qry .= ' ef."idEdicion" = ' . $condition['idEdicion'] . ' AND ';
          $qry .= ' ef."idForma" = ' . $condition['idForma'] . ' AND '; */
        $qry .= ' c."idStatusContrato" = ' . $condition['idContratoStatus'];
        $qry .= ' ORDER BY e."idEmpresa";';

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        return $result['data'];
    }

    //FUNCIONES DEL SNIPPET

    public function getEmpresaForma($condition, $idEmpresa = NULL) {
        $qry = 'SELECT e."idEmpresa",e."DC_NombreComercial",e."idEmpresaTipo",e."CodigoCliente", ee."DetalleForma" ';
        $qry .= 'FROM "SAS"."EmpresaForma" ee ';
        $qry .= 'INNER JOIN "SAS"."Empresa" e ';
        $qry .= 'ON ee."idEmpresa" = e."idEmpresa" AND ';
        if ($idEmpresa != NULL) {
            $qry .= ' e."idEmpresa" = ' . $idEmpresa . ' AND ';
        }
        $qry .= ' ee."idEdicion" = ' . $condition['idEdicion'] . ' AND ';
        $qry .= ' ee."idEvento" = ' . $condition['idEvento'] . ' AND ';
        $qry .= ' ee."idForma" = ' . $condition['idForma'];
        $qry .= ' ORDER BY e."idEmpresa";';

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }

        $data = Array();
        if (COUNT($result['data']) > 0) {
            foreach ($result['data'] as $value) {
                $data[$value['idEmpresa']] = $value;
            }
        }
        return $data;
    }

    public function getEmpresaTipo($condition) {
        $qry = 'SELECT "idEmpresaTipo","TipoEN","TipoES" ';
        $qry .= 'FROM "SAS"."EmpresaTipo" ';
        $qry .= 'WHERE "idEdicion" = ' . $condition['idEdicion'] . ' AND ' . '"idEvento" = ' . $condition['idEvento'];
        $qry .= ' ORDER BY "idEmpresaTipo" ;';

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = Array();
        if (COUNT($result['data']) > 0) {
            foreach ($result['data'] as $value) {
                $data[$value['idEmpresaTipo']] = $value;
            }
        }
        return $data;
    }

    public function getMontajeActividad($condition) {
        $qry = ' SELECT DISTINCT ma."idMontajeActividad",ma."MontajeActividadES",ma."MontajeActividadEN" ';
        $qry .= ' FROM "SAS"."EmpresaMontaje" em';
        $qry .= ' INNER JOIN "SAS"."MontajeActividad" ma ';
        $qry .= ' ON em."idEdicion" = ' . $condition['idEdicion'] . ' AND  em."idEvento" = ' . $condition['idEvento'];
        $qry .= ' ORDER BY ma."idMontajeActividad" ';

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = Array();
        if (COUNT($result['data']) > 0) {
            foreach ($result['data'] as $value) {
                $data[$value['idMontajeActividad']] = $value;
            }
        }
        return $data;
    }

    public function getMontajeVehiculo($condition) {
        $qry = ' SELECT DISTINCT mv."idMontajeVehiculo",mv."VehiculoES",mv."VehiculoEN" ';
        $qry .= ' FROM "SAS"."EmpresaMontaje" em';
        $qry .= ' INNER JOIN "SAS"."MontajeVehiculo" mv ';
        $qry .= ' ON em."idEdicion" = ' . $condition['idEdicion'] . ' AND  em."idEvento" = ' . $condition['idEvento'];
        $qry .= ' ORDER BY mv."idMontajeVehiculo" ';

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = Array();
        if (COUNT($result['data']) > 0) {
            foreach ($result['data'] as $value) {
                $data[$value['idMontajeVehiculo']] = $value;
            }
        }
        return $data;
    }

    public function getEmpresaMontaje($condition) {
        $qry = ' SELECT * ';
        $qry .= ' FROM "SAS"."EmpresaMontaje" em';
        $qry .= ' INNER JOIN "SAS"."Empresa" e ';
        $qry .= ' ON em."idEdicion" = ' . $condition['idEdicion'] . ' AND  em."idEvento" = ' . $condition['idEvento'];
        $qry .= ' AND em."idEmpresa" = e."idEmpresa" ';
        $qry .= ' ORDER BY e."idEmpresa" ';

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = Array();
        if (COUNT($result['data']) > 0) {
            foreach ($result['data'] as $value) {
                $data[$value['idEmpresa']] = $value;
            }
        }
        return $data;
    }

    public function getContrato($condition) {
        $qry = ' SELECT "idContrato","idEmpresa","idUsuario" ';
        $qry .= ' FROM "SAS"."EmpresaStand" es';
        $qry .= ' WHERE "idEdicion" = ' . $condition['idEdicion'] . ' AND ' . '"idEvento" = ' . $condition['idEvento'] . ' AND ' . '"idUsuario" = ' . $condition['idUsuario'];
        $qry .= ' ORDER BY "idContrato" ;';

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = Array();
        if (COUNT($result['data']) > 0) {
            foreach ($result['data'] as $value) {
                $data[$value['idContrato']] = $value;
            }
        }
        return $data;
    }

    public function getContratoStands($condition) {
        $qry = ' SELECT c."idContrato",c."idEmpresa",c."idUsuario",es."idEmpresaContrato",es."idEmpresaAsignada",es."idStand",p."idPabellon",p."NombreEN",p."NombreES"';
        $qry .= ' FROM "SAS"."EmpresaStand" es';
        $qry .= ' INNER JOIN "SAS"."Stand" s';
        $qry .= ' ON es."idStand" = s."idStand"';
        $qry .= ' INNER JOIN "SAS"."Pabellon" p';
        $qry .= ' ON s."idPabellon" = p."idPabellon"';
        $qry .= ' INNER JOIN "SAS"."Contrato" c';
        $qry .= ' ON es."idContrato" = c."idContrato"';
        $qry .= ' AND es."idEdicion" = ' . $condition['idEdicion'] . ' AND ' . 'es."idEvento" = ' . $condition['idEvento'] . ' AND ' . 'c."idUsuario" = ' . $condition['idUsuario'];
        $qry .= ' ORDER BY es."idStand" ;';

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = Array();
        if (COUNT($result['data']) > 0) {
            foreach ($result['data'] as $value) {
                $data[$value['idContrato']] = $value;
            }
        }
        return $data;
    }

    public function getOpcionesPago($condition) {
        $qry = ' SELECT "idOpcionPago","Opcion_EN","Opcion_ES" ';
        $qry .= ' FROM "SAS"."OpcionPago"';
        $qry .= ' WHERE "idEvento" = ' . $condition['idEvento'];
        $qry .= ' AND "idEdicion" = ' . $condition['idEdicion'];

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = Array();
        if (COUNT($result['data']) > 0) {
            foreach ($result['data'] as $value) {
                $data[$value['idOpcionPago']] = $value;
            }
        }
        return $data;
    }

    public function getEntidadFiscal($condition) {
        $qry = ' SELECT "idEmpresaEntidadFiscal","idEmpresa","DF_RazonSocial" ';
        $qry .= ' FROM "SAS"."EmpresaEntidadFiscal"';
        $qry .= ' ORDER BY "idEmpresaEntidadFiscal" ;';

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = Array();
        if (COUNT($result['data']) > 0) {
            foreach ($result['data'] as $value) {
                $data[$value['idEmpresaEntidadFiscal']] = $value;
            }
        }
        return $data;
    }

    public function getPabellones($condition) {
        $qry = ' SELECT es."idEmpresaEntidadFiscal","idEmpresa","DF_RazonSocial" ';
        $qry .= ' FROM "SAS"."EmpresaEntidadFiscal"';
        $qry .= ' ORDER BY "idEmpresaEntidadFiscal" ;';

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = Array();
        if (COUNT($result['data']) > 0) {
            foreach ($result['data'] as $value) {
                $data[$value['idEmpresaEntidadFiscal']] = $value;
            }
        }
        return $data;
    }
    
    public function getSeller($args) {
        $qry = 'SELECT usu."idUsuario", usu."Email" ';
        $qry .= 'FROM "SAS"."Usuario" usu ';        
        $qry .= 'WHERE usu."idUsuario" = ' . $args['idVendedor'] ;        
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {                        
            return $result['data'][0];
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

}
