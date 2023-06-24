<?php

namespace ShowDashboard\CRM\EmpresasAsignadasBundle\Model;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

/**
 * Description of EmpresasAsignadasModel
 *
 * @author Eduardo
 */
class EmpresasAsignadasModel extends DashboardModel {

    protected $SQLModel, $PGSQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->PGSQLModel = new PGSQLClient();
    }

    public function getVendedores($idTipoUsuario1, $idTipoUsuario2) {
        $qry = 'SELECT "Nombre", "idUsuario" ';
        $qry .= 'FROM "SAS"."Usuario"';
        $qry .= 'WHERE "idTipoUsuario" BETWEEN ' . $idTipoUsuario1 . ' AND ' . $idTipoUsuario2;

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $vendedores = Array();
        foreach ($result['data'] as $key => $value) {
            $vendedores[$value['idUsuario']] = $value["Nombre"];
        }
        return $vendedores;
    }

    public function getEmpresaTipo($where, $lang) {
        $fields = Array(
            "idEmpresaTipo",
            "Tipo" . strtoupper($lang)
        );
        $result = $this->SQLModel->selectFromTable("EmpresaTipo", $fields, $where);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $empresaTipo = Array();
        foreach ($result['data'] as $key => $value) {
            $empresaTipo[$value['idEmpresaTipo']] = $value["Tipo" . strtoupper($lang)];
        }
        return $empresaTipo;
    }

    public function getEmpresasAsignadas($where, $conditions, $config) {
        $qry = 'SELECT ' . $this->getCamposEmpresasAsignadas();
        $qry .= 'FROM "SAS"."Empresa" emp ';
        $qry .= 'LEFT JOIN "SAS"."EmpresaUsuario" emus ON emp."idEmpresa" = emus."idEmpresa" ';
        $qry .= 'LEFT JOIN "SAS"."Usuario" usu ON usu."idUsuario" = emus."idUsuario" ';

        $i = 0;
        foreach ($conditions as $key => $field) {
            if ($i == 0) {
                $qry .= " WHERE ";
                $qry .= $field;
            } else {
                $qry .= " AND ";
                $qry .= $field;
            }
            $i++;
        }

        /* if (isset($where['idUsuario'])) {
          if ($conditions != null) {
          $qry .= ' AND usu."idUsuario" = ' . $where['idUsuario'];
          } else {
          $qry .= ' WHERE usu."idUsuario" = ' . $where['idUsuario'];
          }
          } */

        if (COUNT($config) > 0) {
            $qry .= $this->SQLModel->prepareQueryConfig($config);
        }
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }

        return $result['data'];
    }

    public function getEmpresasAsignadasCount($where, $conditions) {
        $qry = 'SELECT COUNT(emp."idEmpresa") AS "Total" ';
        $qry .= 'FROM "SAS"."Empresa" emp ';
        $qry .= 'LEFT JOIN "SAS"."EmpresaUsuario" emus ON emp."idEmpresa" = emus."idEmpresa" ';
        $qry .= 'LEFT JOIN "SAS"."Usuario" usu ON usu."idUsuario" = emus."idUsuario" ';

        $i = 0;
        foreach ($conditions as $key => $field) {
            if ($i == 0) {
                $qry .= " WHERE ";
                $qry .= $field;
            } else {
                $qry .= " AND ";
                $qry .= $field;
            }
            $i++;
        }

        /* if (isset($where['idUsuario'])) {
          if ($conditions != null) {
          $qry .= ' AND usu."idUsuario" = ' . $where['idUsuario'];
          } else {
          $qry .= ' WHERE usu."idUsuario" = ' . $where['idUsuario'];
          }
          } */

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        return $result['data'][0]["Total"];
    }

    public function getCamposEmpresasAsignadas() {
        return 'emp."idEmpresa", '
                . 'emp."CodigoCliente", '
                . 'emp."DC_NombreComercial", '
                . 'emp."idEmpresaTipo", '
                . 'emus."idUsuario" ';
    }

    public function agregarAsesor($data) {
        $values = Array("idUsuario" => $data['idUsuario'], "idEmpresa" => $data['idEmpresa']);
        $result = $this->SQLModel->insertIntoTable("EmpresaUsuario", $values);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
    }

    public function validarEmpresaUsuario($data) {
        $where = Array("idUsuario" => $data['idUsuario'], "idEmpresa" => $data['idEmpresa']);
        $result = $this->SQLModel->selectFromTable("EmpresaUsuario", Array("idUsuario"), $where);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        return (isset($result['data'][0]['idUsuario'])) ? true : false;
    }

    public function cambiarAsesor($data) {
        $values = Array("idUsuario" => $data['idUsuario']);
        $where = Array("idUsuario" => $data['idOriginal'], "idEmpresa" => $data['idEmpresa']);
        $result = $this->SQLModel->updateFromTable("EmpresaUsuario", $values, $where);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
    }

}
