<?php

namespace MS\FloorplanBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

/**
 *
 * @author Ernesto L <ernestol@infoexpo.com.mx>
 */
class FloorplanExhibitorDevModel {

    private $pg_schema_sas = 'SAS', $pg_schema_ms_sl = 'MS_SL', $pg_schema_ae = 'AE', $pg_schema_lectoras = 'LECTORAS';

    function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getExhibitorName($args) {
        $qry = 'SELECT "DD_NombreComercial"';
        $qry .= ' FROM "' . $this->pg_schema_sas . '"."EmpresaEdicion"';
        $qry .= ' WHERE "idEmpresa"=' . $args['idExpositor'];
        $qry .= ' AND "idEdicion"=' . $args['idEdicion'];
        $result_pg = $this->SQLModel->executeQuery($qry);
        if ($result_pg['status']) {
            return $result_pg['data'][0]['DD_NombreComercial'];
        } else {
            return "No Disponible";
        }
    }

    public function getExhibitorGraphic($args) {
        $qry = 'SELECT concat(cf.year,\'-\',cf.month,\'-\',cf.day) AS "Fecha",';
        $qry .= ' CASE WHEN gs.v_amount IS NULL THEN 0';
        $qry .= ' ELSE gs.v_amount';
        $qry .= ' END AS v_amount,';
        $qry .= ' CASE WHEN gs.c_amount IS NULL THEN 0';
        $qry .= ' ELSE gs.c_amount';
        $qry .= ' END AS c_amount';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_CalendarioFechasInteracciones" as cf ';
        $qry .= ' LEFT JOIN(';
        //Start of Global Summary
        $qry .= ' SELECT gs.day, gs.month, gs.year,';
        $qry .= ' SUM(gs."7") as v_amount,';
        $qry .= 'SUM(';
        $qry .= ' COALESCE("1",0)+';
        $qry .= ' COALESCE("2",0)+';
        $qry .= ' COALESCE("3",0)+';
        $qry .= ' COALESCE("4",0)+';
        $qry .= ' COALESCE("5",0)+';
        $qry .= ' COALESCE("6",0)+';
        $qry .= ' COALESCE("8",0)+';
        $qry .= ' COALESCE("9",0)';
        $qry .= ') as c_amount';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_GlobalSummary" as gs ';
        $qry .= 'WHERE MAKE_DATE(gs.year,gs.month,gs.day) BETWEEN \'' . $args["FechaIni"] . '\' AND \'' . $args["FechaFin"] . '\' ';
        $qry .= ' AND gs."idEvento"=' . $args['idEvento'];
        $qry .= ' AND gs."idEdicion"=' . $args['idEdicion'];
        $qry .= ' AND gs."idExpositor"=' . $args['idExpositor'];
        $qry .= ' GROUP BY gs.day,gs.month, gs.year ';
        $qry .= 'ORDER BY gs.year,gs.month,gs.day asc) as gs';
        //End of Global Summary
        $qry .= ' ON cf.day=gs.day AND cf.month=gs.month AND cf.year=gs.year';
        $qry .= ' WHERE MAKE_DATE(cf.year,cf.month,cf.day) BETWEEN \'' . $args["FechaIni"] . '\' AND \'' . $args["FechaFin"] . '\' ';
        $qry .= ' GROUP BY cf.day,cf.month,cf.year,v_amount,c_amount';
        $qry .= ' ORDER BY cf.year, cf.month,cf.day asc';
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg;
    }

    public function getExhibitorTour($args) {
        $qry = 'SELECT';
        $qry .= ' SUM(COALESCE("11",0)) AS "Recorrido"';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_GlobalSummary" ard ';
        $qry .= ' WHERE MAKE_DATE(year,month,day) BETWEEN \'' . $args["FechaIni"] . '\' AND \'' . $args["FechaFin"] . '\' ';
        $qry .= ' AND ard."idEvento"=' . $args['idEvento'];
        $qry .= ' AND ard."idEdicion"=' . $args['idEdicion'];
        $qry .= ' AND ard."idExpositor"=' . $args['idExpositor'];
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg;
    }

    public function getExhibitorScannerApp($args) {
        $qry = 'SELECT ';
        $qry .= ' COUNT(LC."idLecturaContacto") AS "ScannerApp"';
        $qry .= ' FROM "' . $this->pg_schema_lectoras . '"."LecturaContacto" LC';
        $qry .= ' INNER JOIN "' . $this->pg_schema_lectoras . '"."EmpresaScanner" AS ES ON LC."idEmpresaScanner" = ES."idEmpresaScanner"';
        $qry .= ' WHERE ES."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' AND ES."idEmpresa"=' . $args['idExpositor'];
        $qry .= ' GROUP BY';
        $qry .= ' ES."idEmpresa"';
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg;
    }

    public function getExhibitorScannerThird($args) {
        $qry = 'SELECT';
        $qry .= ' SUM(COALESCE("10",0)) AS "ScannerThird"';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_GlobalSummary" ard ';
        $qry .= ' WHERE MAKE_DATE(year,month,day) BETWEEN \'' . $args["FechaIni"] . '\' AND \'' . $args["FechaFin"] . '\' ';
        $qry .= ' AND ard."idEvento"=' . $args['idEvento'];
        $qry .= ' AND ard."idEdicion"=' . $args['idEdicion'];
        $qry .= ' AND ard."idExpositor"=' . $args['idExpositor'];
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg;
    }

    public function getExhibitorInteractions($args) {
        $countStatus=0;
        $amount = array(
            'booth' => 0,
            'list' => 0,
            'product' => 0,
            'webpage' => 0,
            'video' => 0,
            'tour' => 0,
            'retrieval' => 0,
            'location' => 0
        );
        
        $person = array(
            'booth' => 0,
            'list' => 0,
            'product' => 0,
            'webpage' => 0,
            'video' => 0,
            'tour' => 0,
            'retrieval' => 0,
            'location' => 0
        );
        
        $anonymous = array(
            'booth' => 0,
            'list' => 0,
            'product' => 0,
            'webpage' => 0,
            'video' => 0,
            'tour' => 0,
            'retrieval' => 0,
            'location' => 0
        );
        
        $registered = array(
            'booth' => 0,
            'list' => 0,
            'product' => 0,
            'webpage' => 0,
            'video' => 0,
            'tour' => 0,
            'retrieval' => 0,
            'location' => 0
        );
        
        $qry = 'SELECT "Ip", ';
        $qry .= ' SUM("booth") as "booth", ';
        $qry .= ' SUM("list") as "list", ';
        $qry .= ' SUM("product") as "product", ';
        $qry .= ' SUM("webpage") as "webpage", ';
        $qry .= ' SUM("video") as "video", ';
        $qry .= ' SUM("tour") as "tour", ';
        $qry .= ' SUM("retrieval") as "retrieval", ';
        $qry .= ' SUM("location") as "location" ';
        $qry .= ' FROM (';
        $qry .= ' SELECT';
        $qry .= ' "Ip",';
        $qry .= ' CASE WHEN "idObjeto" = 1 THEN COUNT("idAccionRawData") ELSE 0 END AS "booth",';
        $qry .= ' CASE WHEN "idObjeto" = 2 THEN COUNT("idAccionRawData") ELSE 0 END AS "list",';
        $qry .= ' CASE WHEN "idObjeto" = 3 THEN COUNT("idAccionRawData") ELSE 0 END AS "product",';
        $qry .= ' CASE WHEN "idObjeto" = 4 THEN COUNT("idAccionRawData") ELSE 0 END AS "webpage",';
        $qry .= ' CASE WHEN "idObjeto" = 5 THEN COUNT("idAccionRawData") ELSE 0 END AS "video",';
        $qry .= ' CASE WHEN "idObjeto" = 11 THEN COUNT("idAccionRawData") ELSE 0 END AS "tour",';
        $qry .= ' CASE WHEN "idObjeto" = 10 THEN COUNT("idAccionRawData") ELSE 0 END AS "retrieval",';
        $qry .= ' CASE WHEN "idObjeto" = 6 THEN COUNT("idAccionRawData") ELSE 0 END AS "location"';
        $qry .= ' FROM "MS_SL"."ms_AccionRawData" ard ';
        $qry .= ' WHERE "idExpositor" = ' . $args["idExpositor"];
        $qry .= ' AND "idVisitante" = 0 ';
        $qry .= ' GROUP BY';
        $qry .= ' "Ip",';
        $qry .= ' "idObjeto") AS NoRegistrados';
        $qry .= ' WHERE';
        $qry .= ' "booth">0';
        $qry .= ' OR "list">0';
        $qry .= ' OR "product">0';
        $qry .= ' OR "webpage">0';
        $qry .= ' OR "video">0';
        $qry .= ' OR "location">0';
        $qry .= ' GROUP BY "Ip"';
        $qry .= ' ORDER BY "Ip" ASC';
        $result_pg = $this->SQLModel->executeQuery($qry);
        if($result_pg['status']){
            $countStatus++;
        }

        $noRegisterVisitors = ($result_pg['status']) ? $result_pg['data'] : array('data' => array(
                '0' => array(
                    'Ip' => 0,
                    'booth' => 0,
                    'list' => 0,
                    'product' => 0,
                    'webpage' => 0,
                    'video' => 0,
                    'tour' => 0,
                    'retrieval' => 0,
                    'location' => 0
                )
            )
        );
        foreach ($noRegisterVisitors as $key => $value) {
            foreach ($value as $key2 => &$value2) {
                if($key2!='Ip' && $key2!='idVisitante'){
                    $amount[$key2]+=$value2;
                    $anonymous[$key2]+=$value2==0?0:1;
                    $person[$key2]+=$value2==0?0:1;
                }
            }
        }

        $qry = 'SELECT "Ip", ';
        $qry .= ' "idVisitante", ';
        $qry .= ' SUM("booth") as "booth", ';
        $qry .= ' SUM("list") as "list", ';
        $qry .= ' SUM("product") as "product", ';
        $qry .= ' SUM("webpage") as "webpage", ';
        $qry .= ' SUM("video") as "video", ';
        $qry .= ' SUM("tour") as "tour", ';
        $qry .= ' SUM("retrieval") as "retrieval", ';
        $qry .= ' SUM("location") as "location" ';
        $qry .= ' FROM (';
        $qry .= ' SELECT';
        $qry .= ' "Ip",';
        $qry .= ' "idVisitante",';
        $qry .= ' CASE WHEN "idObjeto" = 1 THEN COUNT("idAccionRawData") ELSE 0 END AS "booth",';
        $qry .= ' CASE WHEN "idObjeto" = 2 THEN COUNT("idAccionRawData") ELSE 0 END AS "list",';
        $qry .= ' CASE WHEN "idObjeto" = 3 THEN COUNT("idAccionRawData") ELSE 0 END AS "product",';
        $qry .= ' CASE WHEN "idObjeto" = 4 THEN COUNT("idAccionRawData") ELSE 0 END AS "webpage",';
        $qry .= ' CASE WHEN "idObjeto" = 5 THEN COUNT("idAccionRawData") ELSE 0 END AS "video",';
        $qry .= ' CASE WHEN "idObjeto" = 11 THEN COUNT("idAccionRawData") ELSE 0 END AS "tour",';
        $qry .= ' CASE WHEN "idObjeto" = 10 THEN COUNT("idAccionRawData") ELSE 0 END AS "retrieval",';
        $qry .= ' CASE WHEN "idObjeto" = 6 THEN COUNT("idAccionRawData") ELSE 0 END AS "location"';
        $qry .= ' FROM "MS_SL"."ms_AccionRawData" ard ';
        $qry .= ' WHERE "idExpositor" = ' . $args["idExpositor"];
        $qry .= ' AND "idVisitante" <> 0 ';
        $qry .= ' GROUP BY';
        $qry .= ' "Ip",';
        $qry .= ' "idVisitante",';
        $qry .= ' "idObjeto") AS Registrados';
        $qry .= ' WHERE';
        $qry .= ' "booth">0';
        $qry .= ' OR "list">0';
        $qry .= ' OR "product">0';
        $qry .= ' OR "webpage">0';
        $qry .= ' OR "video">0';
        $qry .= ' OR "location">0';
        $qry .= ' GROUP BY "Ip", "idVisitante"';
        $qry .= ' ORDER BY "Ip" ASC';
        $result_pg = $this->SQLModel->executeQuery($qry);
        if($result_pg['status']){
            $countStatus++;
        }

        $registeredVisitors = ($result_pg['status']) ? $result_pg['data'] : array('data' => array(
                '0' => array(
                    'Ip' => 0,
                    'booth' => 0,
                    'list' => 0,
                    'product' => 0,
                    'webpage' => 0,
                    'video' => 0,
                    'tour' => 0,
                    'retrieval' => 0,
                    'location' => 0
                )
            )
        );
        foreach ($registeredVisitors as $key => $value) {
            foreach ($value as $key2 => &$value2) {
                if($key2!='Ip' && $key2!='idVisitante'){
                    $amount[$key2]+=$value2;
                    $registered[$key2]+=$value2==0?0:1;
                    $person[$key2]+=$value2==0?0:1;
                }
            }
        }
        if($countStatus==2){
            $response['status']=true;
        }
        else{
            $response['status']=false;
        }
        $response['data']['amount']=$amount;
        $response['data']['person']=$person;
        $response['data']['anonymous']=$anonymous;
        $response['data']['registered']=$registered;
        return $response;
    }

}
