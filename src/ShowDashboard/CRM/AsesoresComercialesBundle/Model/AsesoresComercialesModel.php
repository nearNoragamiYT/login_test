<?php

namespace ShowDashboard\CRM\AsesoresComercialesBundle\Model;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use ShowDashboard\DashboardBundle\Model\DashboardModel;

/**
 * Description of AsesoresComercialesModel
 *
 * @author Eduardo
 */
class AsesoresComercialesModel extends DashboardModel {

    public function getAsesoresComerciales($where) {
        $qry = 'SELECT ' . $this->getCamposAsesores();
        $qry .= 'FROM "SAS"."Usuario" usu ';
        $qry .= 'LEFT JOIN "SAS"."UsuarioEdicion" use ON usu."idUsuario" = use."idUsuario" ';
        $qry .= 'INNER JOIN "SAS"."ContactoComiteOrganizador" cco ON usu."idContactoComiteOrganizador" = cco."idContactoComiteOrganizador" ';
        $qry .= 'INNER JOIN "SAS"."TipoUsuario" tus ON usu."idTipoUsuario" = tus."idTipoUsuario" ';
        $qry .= 'WHERE use."idEdicion" = ' . $where['idEdicion'] . ' AND  usu."idTipoUsuario" = ' . $where['idTipoUsuario'];

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        //$i = 1;
        $vendedores = Array();
        foreach ($result['data'] as $value) {
            $vendedores[$value['idUsuario']] = $value;
            $vendedores[$value['idUsuario']]["CantidadEmpresas"] = $this->getEmpresaUsuario($value['idUsuario']);
            //$vendedores[$value['idUsuario']]["CantidadEmpresas"] = $i++;
        }
        return $vendedores;
    }

    public function getCamposAsesores() {
        return 'usu."idUsuario", '
                . 'cco."Nombre", '
                . 'cco."Telefono", '
                . 'cco."Puesto", '
                . 'usu."Activo", '
                . 'usu."Email"';
    }

    public function getEmpresaUsuario($idUsuario) {
        $qry = 'SELECT COUNT("idEmpresa") AS "Total" ';
        $qry .= 'FROM "SAS"."EmpresaUsuario" ';
        $qry .= 'WHERE "idUsuario" = ' . $idUsuario;

        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        return $result['data'][0]['Total'];
    }

    public function agregarEdiarAsesor($idEvento, $idEdicion, $idComiteOrganizador, $idTipoUsuario, &$data) {
        $qry = 'SELECT * FROM "SAS"."fn_sas_InsertEditUsuario"('
                . $idEdicion . ", "
                . $idEvento . ", "
                . $data['idUsuario'] . ", "
                . $idTipoUsuario . ", '"
                . $data['Nombre'] . "', '"
                . $data['Password'] . "', '"
                . $data['Email'] . "', '"
                . $data['Telefono'] . "', '"
                . $data['Puesto'] . "', '"
                . $data['Activo'] . "', "
                . $idComiteOrganizador
                . ')';
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data['idUsuario'] = $result['data'][0]['fn_sas_InsertEditUsuario'];
        unset($data['Password']);
    }

    public function activarAsesor($data) {
        $result = $this->SQLModel->updateFromTable("Usuario", Array("Activo" => $data['Activo']), Array("idUsuario" => $data['idUsuario']));
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
    }

}
