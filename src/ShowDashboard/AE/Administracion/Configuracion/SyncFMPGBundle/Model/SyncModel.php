<?php

namespace ShowDashboard\AE\Administracion\Configuracion\SyncFMPGBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use Utilerias\FileMakerBundle\API\ODBC\Client;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SyncModel
 *
 * @author Juls
 */
class SyncModel {

    public $SQLModel, $PGSQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->PGSQLModel = new PGSQLClient();
        $this->ODBCClient = new Client();
    }

    public function getTablasPG($schema) {
        $qry = ' SELECT ';
        $qry .= ' table_name ';
        $qry .= ' FROM information_schema.tables ';
        $qry .= ' WHERE ';
        $qry .= ' table_schema = \'' . $schema . '\'';
        $qry .= ' AND table_type = \'BASE TABLE\'';
        $qry .= ' AND table_name = \'Visitante\'';
        $qry .= ' OR table_name = \'VisitanteEdicion\'';
        $qry .= ' OR table_name = \'Compra\'';
        $qry .= ' OR table_name = \'CompraDetalle\'';
        $qry .= ' OR table_name = \'AgendaVisitante\'';
        $qry .= ' OR table_name = \'Invitacion\'';
        $qry .= ' OR table_name = \'VisitanteCupon\'';
        $qry .= ' ORDER BY ';
        $qry .= ' table_name ';

        return $this->SQLModel->executeQuery($qry);
    }

    public function getCamposPG($tabla) {

        $qry = ' SELECT ';
        $qry .= ' column_name, ';
        $qry .= ' data_type ';
        $qry .= ' FROM information_schema.columns ';
        $qry .= ' WHERE ';
        $qry .= ' table_name = \'' . $tabla . '\'';
        $qry .= ' ORDER BY ';
        $qry .= ' column_name ';
        return $this->SQLModel->executeQuery($qry);
    }

    public function getTablasFM() {
        $this->ODBCClient->setCache(0);
        $this->ODBCClient->setExpirationTime(3600);
        $qry = ' SELECT DISTINCT';
        $qry .= ' TableName AS BaseTableName ';
        $qry .= ' FROM FileMaker_Tables ';
        $qry .= ' WHERE ';
        $qry .= ' BaseTableName ';
        $qry .= ' IS NOT NULL ';
        $qry .= ' AND TableName = \'Visitante\'';
        $qry .= ' OR TableName = \'VisitanteEdicion\'';
        $qry .= ' OR TableName = \'Compra\'';
        $qry .= ' OR TableName = \'CompraDetalle\'';
        $qry .= ' OR TableName = \'AgendaVisitante\'';
        $qry .= ' OR TableName = \'Invitacion\'';
        $qry .= ' OR TableName = \'VisitanteCupon\'';
        $qry .= ' ORDER BY ';
        $qry .= ' TableName ';
        $this->ODBCClient->setQuery($qry);
        $this->ODBCClient->exec();
        return $this->ODBCClient->getResultAssoc();
    }

    public function getCamposFM($tabla) {
        $this->ODBCClient->setCache(0);
        $this->ODBCClient->setExpirationTime(3600);
        $qry = ' SELECT ';
        $qry .= ' FieldName, ';
        $qry .= ' FieldType ';
        $qry .= ' FROM FileMaker_Fields ';
        $qry .= ' WHERE ';
        $qry .= ' TableName = \'' . $tabla . '\'';
        $qry .= ' AND FieldType!= \' global\'';
        $qry .= ' ORDER BY ';
        $qry .= ' FieldName ';
        $this->ODBCClient->setQuery($qry);
        $this->ODBCClient->exec();
        return $this->ODBCClient->getResultAssoc();
    }

    public function updateJson($json, $idEvento, $idEdicion) {

        $qry = ' UPDATE ';
        $qry .= ' "AE"."Configuracion" ';
        $qry .= ' SET ';
        $qry .= ' "jsonSync" ';
        $qry .= ' = \'' . $json . '\'';
        $qry .= ' WHERE ';
        $qry .= ' "idEdicion" = ' . $idEdicion;
        $qry .= ' AND "idEvento" = ' . $idEvento ;
        return $this->SQLModel->executeQuery($qry);
    }
    
    public function getJson($idEvento, $idEdicion) {

        $qry = ' SELECT ';
        $qry .= ' "jsonSync" ';
        $qry .= ' FROM ';
        $qry .= ' "AE"."Configuracion" ';
        $qry .= ' WHERE ';
        $qry .= ' "idEdicion" = ' . $idEdicion;
        $qry .= ' AND "idEvento" = ' . $idEvento ;
        return $this->SQLModel->executeQuery($qry);
    }

}