<?php

namespace MS\FloorplanBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

/**
 *
 * @author Ernesto L <ernestol@infoexpo.com.mx>
 */
class FloorplanDevModel {

    private $pg_schema_sas = 'SAS', $pg_schema_ms_sl = 'MS_SL', $pg_schema_ae = 'AE', $pg_schema_lectoras = 'LECTORAS';

    function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getDetailsExhibitor($args) {        
        $qry = 'SELECT';
        $qry .= ' ex."idPaquete" AS upgrade, "idEmpresa" AS "idExpositor", pack."PaqueteES",';
        $qry .= ' (CASE WHEN "DD_NombreComercial" IS NULL OR "DD_NombreComercial"=\'\' ';
        $qry .= ' THEN "DC_NombreComercial"';
        $qry .= ' ELSE "DD_NombreComercial" END) AS "Nombre",';
        $qry .= ' SUM(COALESCE("1",0)) AS "Booth",';
        $qry .= ' SUM(COALESCE("2",0)) AS "List",';
        $qry .= ' SUM(COALESCE("3",0)) AS "Product",';
        $qry .= ' SUM(COALESCE("4",0)) AS "WebPage",';
        $qry .= ' SUM(COALESCE("5",0)) AS "Video",';
        $qry .= ' SUM(COALESCE("6",0)) AS "Location",';
        $qry .= ' SUM(COALESCE("7",0)) AS "Views",';
        $qry .= ' SUM(COALESCE("8",0)) AS "Photo",';
        $qry .= ' SUM(COALESCE("9",0)) AS "Product Directory",';
        $qry .= ' SUM(COALESCE("10",0)) AS "Lectura",';
        $qry .= ' SUM(COALESCE("11",0)) AS "Recorrido"';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_GlobalSummary" ard ';
        $qry .= ' INNER JOIN "' . $this->pg_schema_sas . '"."CacheEmpresa" ex ';
        $qry .= ' ON ex."idEmpresa"=ard."idExpositor"';
        $qry .= ' INNER JOIN "' . $this->pg_schema_sas . '"."Paquete" pack ';
        $qry .= ' ON ex."idPaquete"=pack."idPaquete"';
        $qry .= ' WHERE MAKE_DATE(year,month,day) BETWEEN \'' . $args["FechaIni"] . '\' AND \'' . $args["FechaFin"] . '\' ';
        $qry .= ' AND ard."idEvento"=' . $args['idEvento'];
        $qry .= ' AND ard."idEdicion"=' . $args['idEdicion'];
        $qry .= ' AND ard."idExpositor">0';
        $qry .= ' GROUP BY ex."idEmpresa",';
        $qry .= ' ex."DD_NombreComercial",';
        $qry .= ' ex."DC_NombreComercial",';
        $qry .= ' ex."idPaquete",';
        $qry .= ' pack."PaqueteES"';
        $qry .= ' ORDER BY ex."idEmpresa" ASC';
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg;
    }
    
    public function getDetailsAppExhibitor($args) { 
        $qry .= 'SELECT';
        $qry .= ' ES."idEmpresa" AS "idExpositor",';
        $qry .= ' COUNT(LC."idLecturaContacto") AS "ScannerApp"';
        $qry .= ' FROM "' . $this->pg_schema_lectoras . '"."LecturaContacto" LC';
        $qry .= ' INNER JOIN "' . $this->pg_schema_lectoras . '"."EmpresaScanner" AS ES ON LC."idEmpresaScanner" = ES."idEmpresaScanner"';
        $qry .= ' WHERE ES."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' GROUP BY';
        $qry .= ' ES."idEmpresa"';
        $qry .= ' ORDER BY ES."idEmpresa" ASC';
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg;
    }
    
    public function getDetailsSearchedText($args){
        $qry = ' SELECT "Query" AS first_word, SUM ("Cantidad") AS amount';
        $qry .= ' FROM "'. $this->pg_schema_ms_sl . '"."ms_SummaryBRD" ard';
        $qry .= ' WHERE "Fecha" BETWEEN \'' . $args["FechaIni"] . '\' ';
        $qry .= ' AND \'' . $args["FechaFin"] . '\'';
        $qry .= ' AND "idEdicion"=' . $args['idEdicion'];
        $qry .= ' AND "idEvento"=' . $args['idEvento'];
        $qry .= ' AND "Type" = 0';
        $qry .= ' GROUP BY first_word';
        $qry .= ' ORDER BY amount DESC';
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg;
    }
    
    public function getDetailsSearchedCategory($args){
        $qry = 'SELECT';
        $qry .= ' cat."NombreCategoria' . strtoupper($args['lang']) . '" as first_word';
        $qry .= ' ,SUM(summ."Cantidad") as amount';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_SummaryBRD" summ';
        $qry .= ' INNER JOIN "' . $this->pg_schema_sas . '"."Categoria" cat ON summ."idRef"=cat."idCategoria"';
        $qry .= ' WHERE summ."Fecha" BETWEEN \'' . $args["FechaIni"] . '\' ';
        $qry .= ' AND \'' . $args["FechaFin"] . '\'';
        $qry .= ' AND summ."idEdicion"=' . $args['idEdicion'];
        $qry .= ' AND summ."idEvento"=' . $args['idEvento'];
        $qry .= ' AND summ."Type"= 1';
        $qry .= ' GROUP BY summ."idRef"';
        $qry .= ' ,cat."NombreCategoria' . strtoupper($args['lang']) . '"';
        $qry .= ' ORDER BY amount DESC';
        $result_pg = $this->SQLModel->executeQuery($qry);     
        return $result_pg;
    }
    
    public function getTotalClicks($args){
        $qry = 'SELECT';
        $qry .= ' SUM("1")AS "sumBooth"';
        $qry .= ' ,SUM("2") AS "sumList"';
        $qry .= ' ,SUM("3") AS "sumProduct"';
        $qry .= ' ,SUM("4") AS "sumWebpage"';
        $qry .= ' ,SUM("5") AS "sumVideo"';
        $qry .= ' ,SUM("6") AS "sumLocation"';
        $qry .= ' ,SUM("8") AS "sumPhoto"';
        $qry .= ' ,SUM("9") AS "sumProductDirectory"';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_GlobalSummary" ard ';
        $qry .= ' WHERE MAKE_DATE(year,month,day) BETWEEN \'' . $args["FechaIni"] . '\' AND \'' . $args["FechaFin"] . '\' ';
        $qry .= ' AND ard."idEvento"=' . $args['idEvento'];
        $qry .= ' AND ard."idEdicion"=' . $args['idEdicion'];
        $qry .= ' AND ard."idExpositor">0';
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg;
    }
    
    public function getTotalViews($args){
        $qry = 'SELECT';
        $qry .= ' SUM("7")AS "sumViews"';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_GlobalSummary" ard ';
        $qry .= ' WHERE MAKE_DATE(year,month,day) BETWEEN \'' . $args["FechaIni"] . '\' AND \'' . $args["FechaFin"] . '\' ';
        $qry .= ' AND ard."idEvento"=' . $args['idEvento'];
        $qry .= ' AND ard."idEdicion"=' . $args['idEdicion'];
        $qry .= ' AND ard."idExpositor">0';
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg;
    }
    
    public function getTotalVisits($args){
        $qry = 'SELECT';
        $qry .= ' SUM("Cantidad") as "sumVisits"';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_SummaryVRD" ';
        $qry .= 'WHERE "Fecha" BETWEEN \'' . $args["FechaIni"] . '\'';
        $qry .= ' AND \'' . $args["FechaFin"] . '\'';
        $qry .= ' AND "idEdicion"=' . $args['idEdicion'];
        $qry .= ' AND "idEvento"=' . $args['idEvento'];
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg;
    }
    
    public function getTotalOneTimeVisitors($args){
        $qry = 'SELECT';
        $qry .= ' COUNT(DISTINCT(ved."idVisitante")) as "sumOneTimeVisits"';
        $qry .= ' FROM "AE"."VisitanteEdicion" AS ved ';
        $qry .= ' JOIN "MS_SL"."ms_GlobalSummary" AS gs';
        $qry .= ' ON ved."idVisitante"=gs."idVisitante"';
        $qry .= ' WHERE';
        $qry .= ' gs."idEdicion"=' . $args['idEdicion'];
        $qry .= ' AND gs."idEvento"=' . $args['idEvento'];
        $qry .= ' AND MAKE_DATE(gs."year",gs."month",gs."day")';
        $qry .= ' BETWEEN \'' . $args["FechaIni"] . '\'';
        $qry .= ' AND \'' . $args["FechaFin"] . '\'';
        $qry .= ' AND gs."idVisitante"<>0';
        $qry .= ' AND gs."idExpositor"<>0';
        $qry .= ' AND gs."11">0';
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg;
    }
    
    public function getTotalComebackVisitors($args){
        $qry = 'SELECT';
        $qry .= ' COUNT(ard."Ip") "sumComebackVisits"';
        $qry .= ' FROM (SELECT "Ip", SUM("Cantidad") as cantidad';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_SummaryVRD"';
        $qry .= ' WHERE "Fecha" BETWEEN \'' . $args["FechaIni"] . '\'';
        $qry .= ' AND \'' . $args["FechaFin"] . '\'';
        $qry .= ' AND "idEdicion"=' . $args['idEdicion'];
        $qry .= ' AND "idEvento"=' . $args['idEvento'];
        $qry .= ' AND "idVisitante"=0 GROUP BY "Ip") as ard';
        $qry .= ' WHERE ard.cantidad>1';
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg;
    }

}
