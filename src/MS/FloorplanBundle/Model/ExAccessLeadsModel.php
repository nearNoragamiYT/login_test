<?php

namespace MS\FloorplanBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

/**
 * Description of ExAccessLeadsModel
 *
 * @author Ernesto Lopez
 */
class ExAccessLeadsModel {

    private $pg_schema_sas = 'SAS', $pg_schema_ms_sl = 'MS_SL', $pg_schema_ae = 'AE',$SQLModel;

    function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getEdicion($token) {
        $qry = 'SELECT';
        $qry .= ' EMPED."idEdicion"';
        $qry .= ', EMPED."idEvento"';
        $qry .= ', EMPED."idEmpresa"';
        $qry .= ', EMP."DC_NombreComercial"';
        $qry .= ' FROM "' . $this->pg_schema_sas . '"."EmpresaEdicion" AS EMPED';
        $qry .= ' INNER JOIN "' . $this->pg_schema_sas . '"."Empresa" AS EMP ON EMPED."idEmpresa"=EMP."idEmpresa"';
        $qry .= ' WHERE';
        $qry .= ' EMPED."TokenMS"=\'' . $token . '\'';
        $result_query = $this->SQLModel->executeQuery($qry);
        if ($result_query['status']) {
            $data = $result_query['data']['0']['idEdicion'] ? $result_query['data']['0'] : '404';
            return $data;
        } else {
            return array('status' => false, 'data' => '206');
        }
    }

    public function getEditionName($idEdicion, $idEvento) {
        $qry = 'SELECT';
        $qry .= ' "Edicion_ES"';
        $qry .= ', "Edicion_EN"';
        $qry .= ' FROM "' . $this->pg_schema_sas . '"."Edicion"';
        $qry .= ' WHERE';
        $qry .= ' "idEdicion"=' . $idEdicion;
        $qry .= ' AND "idEvento"=' . $idEvento;
        return $this->SQLModel->executeQuery($qry);
    }

    public function updateToken($args) {
        $qry = 'SELECT "' . $this->pg_schema_sas . '"."fn_sas_actualizarTokenMS"(' . $args['idEvento'] . ',' . $args['idEdicion'] . ',' . $args['idEmpresa'] . ')';
        return $this->SQLModel->executeQuery($qry);
    }

    function insertGridView($args) {
        $qry = ' INSERT INTO "' . $this->pg_schema_sas . '"."EDLog"("idContacto","idEvento","idEdicion","idEmpresa","idAccion")';
        $qry .= ' VALUES(' . $args['idContacto'] . ',' . $args['idEvento'] . ',' . $args['idEdicion'] . ',' . $args['idEmpresa'] . ',2)';
        return $this->SQLModel->executeQuery($qry);
    }

}
