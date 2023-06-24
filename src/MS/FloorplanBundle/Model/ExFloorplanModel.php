<?php

namespace MS\FloorplanBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

/**
 *
 * @author Neto
 */
class ExFloorplanModel {

    private $pg_schema_sas = 'SAS', $pg_schema_ms_sl = 'MS_SL', $pg_schema_ae = 'AE';

    function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getEdicion($token) {
        $qry = 'SELECT';
        $qry .= ' "idEdicion"';
        $qry .= ', "idEvento"';
        $qry .= ', "idEdicion"';
        $qry .= ', "idEmpresa"';
        $qry .= ', "DD_NombreComercial"';
        $qry .= ' FROM "' . $this->pg_schema_sas . '"."EmpresaEdicion"';
        $qry .= ' WHERE';
        $qry .= ' "TokenMS"=\'' . $token . '\'';
        $result_query = $this->SQLModel->executeQuery($qry);
        if ($result_query['status'] == 1) {
            $data = $result_query['data']['0']['idEdicion'] ? $result_query['data']['0'] : '404';
            return $data;
        } else {
            return array('status' => false, 'data' => '206');
        }
    }

    public function updateToken($args) {
        $qry = 'SELECT "' . $this->pg_schema_sas . '"."fn_sas_actualizarTokenMS"(' . $args['idEvento'] . ',' . $args['idEdicion'] . ',' . $args['idEmpresa'] . ')';
        return $this->SQLModel->executeQuery($qry);
    }

    /*    QUERIES PARA PANTALLA DE EXPOSITOR      */

    public function getExData($args) {
        $qry = 'SELECT "Upgrade" as upgrade, "idExpositor", "Nombre"';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_Expositor"';
        $qry .= ' WHERE "idExpositor"= ' . $args["idExhibitor"];
        $result_pg = $this->SQLModel->executeQuery($qry);

        $data = ($result_pg['status'] && count($result_pg['data']) > 0) ? $result_pg['data'] : array(0 => array('Nombre' => '', 'upgrade' => 0));
        return $data;
    }

    public function getEdExhibitorDetails($lang, $args) {
        $qry = 'SELECT';
        $qry .= ' ex."idPaquete" AS "upgrade", "idEmpresa" AS "idExpositor", pack."Paquete' . $lang . '",';
        $qry .= ' (CASE WHEN "DD_NombreComercial" IS NULL OR "DD_NombreComercial"=\'\' ';
        $qry .= ' THEN "DC_NombreComercial"';
        $qry .= ' ELSE "DD_NombreComercial" END) AS "Nombre",';
        $qry .= ' SUM(COALESCE("1",0)) AS "booth",';
        $qry .= ' SUM(COALESCE("2",0)) AS "list",';
        $qry .= ' SUM(COALESCE("3",0)) AS "product",';
        $qry .= ' SUM(COALESCE("4",0)) AS "webpage",';
        $qry .= ' SUM(COALESCE("5",0)) AS "video",';
        $qry .= ' SUM(COALESCE("6",0)) AS "location",';
        $qry .= ' SUM(COALESCE("7",0)) AS "views",';
        $qry .= ' SUM(COALESCE("8",0)) AS "photo",';
        $qry .= ' SUM(COALESCE("9",0)) AS "product directory",';
        $qry .= ' SUM(COALESCE("10",0)) AS "retrieval",';
        $qry .= ' SUM(COALESCE("11",0)) AS "tour"';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_GlobalSummary" ard ';
        $qry .= ' INNER JOIN "' . $this->pg_schema_sas . '"."CacheEmpresa" ex ';
        $qry .= ' ON ard."idExpositor"=ex."idEmpresa"';
        $qry .= ' INNER JOIN "' . $this->pg_schema_sas . '"."Paquete" pack ';
        $qry .= ' ON ex."idPaquete"=pack."idPaquete"';
        $qry .= ' WHERE MAKE_DATE(year,month,day) BETWEEN \'' . $args["iniTimestamp"] . '\' AND \'' . $args["endTimestamp"] . '\' ';
        $qry .= ' AND ard."idEvento"=' . $args['idEvento'];
        $qry .= ' AND ard."idEdicion"=' . $args['idEdicion'];
        $qry .= ' AND ard."idExpositor"=' . $args['idExhibitor'];
        $qry .= ' GROUP BY ex."idEmpresa",';
        $qry .= ' ex."DD_NombreComercial",';
        $qry .= ' ex."DC_NombreComercial",';
        $qry .= ' ex."idPaquete",';
        $qry .= ' pack."PaqueteES"';
        $qry .= ' ORDER BY ex."idEmpresa" ASC';
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg['data'];
    }

    function getDetailFields() {
        $fields = '';
        //$fields.=' ard."idModuloEdicion",';
        //$fields.=' ex."Upgrade" as upgrade,';
        $fields .= ' ard."idExpositor",';
        //$fields.=' ex."Nombre" as "Nombre",';
        $fields .= ' o."Nombre" as "objeto",';
        $fields .= ' SUM(ard."Cantidad") as "cantidad",';
        $fields .= ' ard."idObjeto" ';
        return $fields;
    }

//http://www.craiglotter.co.za/2010/05/07/php-how-to-get-all-the-numbers-out-of-an-alphanumeric-string/
    public function createVisitorsArray($visitorsId, $key) {

        $query = "( ";
        $index = 0;
        foreach ($visitorsId as &$value) {
            $nkey = preg_replace('/[a-zA-Z*]/', '', $value[$key]);
            if ($nkey != 0) {
                $query .= ($index == 0) ? ' ' . $nkey . '' : ' , ' . $nkey . ' ';
                $index++;
            }
        }
        return $query . " )";
    }

    public function getAmountOfClicksByEx($args) {
        $qry = 'SELECT';
        $qry .= ' cf.day,cf.month,cf.year,';
        $qry .= ' CASE WHEN gs.amount IS NULL THEN 0';
        $qry .= ' ELSE gs.amount END';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_CalendarioFechasInteracciones" AS cf';
        $qry .= ' LEFT JOIN';
        //Start Global Summary
        $qry .= ' (SELECT gs.day,gs.month,gs.year,';
        $qry .= ' SUM(';
        $qry .= ' COALESCE(gs."1",0)+';
        $qry .= ' COALESCE(gs."2",0)+';
        $qry .= ' COALESCE(gs."3",0)+';
        $qry .= ' COALESCE(gs."4",0)+';
        $qry .= ' COALESCE(gs."5",0)+';
        $qry .= ' COALESCE(gs."6",0)+';
        $qry .= ' COALESCE(gs."8",0)+';
        $qry .= ' COALESCE(gs."9",0)';
        $qry .= ') AS amount';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_GlobalSummary" AS gs';
        $qry .= ' WHERE gs."idEvento"=' . $args['idEvento'];
        $qry .= ' AND gs."idEdicion"=' . $args['idEdicion'];
        $qry .= ' AND gs."idExpositor"=' . $args['idExhibitor'];
        $qry .= ' AND MAKE_DATE(gs.year,gs.month,gs.day)';
        $qry .= ' BETWEEN \'' . $args["iniTimestamp"] . '\' AND \'' . $args["endTimestamp"] . '\'';
        $qry .= ' GROUP BY gs.day,gs.month,gs.year';
        $qry .= ' ORDER BY gs.year,gs.month,gs.day ASC) AS gs';
        //End Global Summary
        $qry .= ' ON cf.day=gs.day AND cf.month=gs.month AND cf.year=gs.year';
        $qry .= ' WHERE MAKE_DATE(cf.year,cf.month,cf.day)';
        $qry .= ' BETWEEN \'' . $args["iniTimestamp"] . '\' AND \'' . $args["endTimestamp"] . '\'';
        $qry .= ' GROUP BY cf.day,cf.month,cf.year,amount';
        $qry .= ' ORDER BY cf.year,cf.month,cf.day ASC';

        $result_pg = $this->SQLModel->executeQuery($qry);
        $data = ($result_pg['status']) ? $result_pg['data'] : array();

        return $data;
    }

    public function getAmountOfViewsByEx($args) {
        $qry = 'SELECT';
        $qry .= ' cf.day,cf.month,cf.year,';
        $qry .= ' CASE WHEN gs.amount IS NULL THEN 0';
        $qry .= ' ELSE gs.amount END';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_CalendarioFechasInteracciones" AS cf';
        $qry .= ' LEFT JOIN';
        // Start Global Summary
        $qry .= ' (SELECT gs.day,gs.month,gs.year,';
        $qry .= ' SUM(gs."7") AS amount';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_GlobalSummary" AS gs';
        $qry .= ' WHERE gs."idEvento"=' . $args['idEvento'];
        $qry .= ' AND gs."idEdicion"=' . $args['idEdicion'];
        $qry .= ' AND gs."idExpositor"=' . $args['idExhibitor'];
        $qry .= ' AND MAKE_DATE(gs.year,gs.month,gs.day)';
        $qry .= ' BETWEEN \'' . $args["iniTimestamp"] . '\' AND \'' . $args["endTimestamp"] . '\'';
        $qry .= ' GROUP BY gs.day,gs.month,gs.year';
        $qry .= ' ORDER BY gs.year,gs.month,gs.day ASC) AS gs';
        //End Global Summary
        $qry .= ' ON cf.day=gs.day AND cf.month=gs.month AND cf.year=gs.year';
        $qry .= ' WHERE MAKE_DATE(cf.year,cf.month,cf.day)';
        $qry .= ' BETWEEN \'' . $args["iniTimestamp"] . '\' AND \'' . $args["endTimestamp"] . '\'';
        $qry .= ' GROUP BY cf.day,cf.month,cf.year,amount';
        $qry .= ' ORDER BY cf.year,cf.month,cf.day ASC';

        $result_pg = $this->SQLModel->executeQuery($qry);
        $data = ($result_pg['status']) ? $result_pg['data'] : array();
        return $data;
    }

    public function getAmountUniqueVisitorsByEx($args) {

        /* Esta consulta es unicamente para los usuarios que no estan logueados en el sitio */
        $obj = array("booth" => array("idObjeto" => 1, "amount" => 0), "list" => array("idObjeto" => 2, "amount" => 0), "product" => array("idObjeto" => 3, "amount" => 0),
            "webpage" => array("idObjeto" => 4, "amount" => 0), "video" => array("idObjeto" => 5, "amount" => 0), "location" => array("idObjeto" => 6, "amount" => 0));

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
        $qry .= ' WHERE "idExpositor" = ' . $args["idExhibitor"];
        $qry .= ' AND "idVisitante" = 0 ';
        $qry .= ' GROUP BY';
        $qry .= ' "Ip",';
        $qry .= ' "idVisitante",';
        $qry .= ' "idObjeto") AS NoRegistrados';
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
        $noRegisterVisitors = ($result_pg['status']) ? $result_pg['data'] : array();

        foreach ($noRegisterVisitors as $key => $value) {
            foreach ($obj as $key2 => &$value2) {
                if ($value[$key2] > 0) {
                    $value2["amount"] ++;
                }
            }
        }

        $noRegisterVisitors = $obj;

        $qry = 'SELECT COALESCE("Ip",0), ';
        $qry .= ' COALESCE ("idVisitante",1), ';
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
        $qry .= ' COALESCE("Ip",0),';
        $qry .= ' COALESCE ("idVisitante",1),';
        $qry .= ' CASE WHEN "idObjeto" = 1 THEN COUNT("idAccionRawData") ELSE 0 END AS "booth",';
        $qry .= ' CASE WHEN "idObjeto" = 2 THEN COUNT("idAccionRawData") ELSE 0 END AS "list",';
        $qry .= ' CASE WHEN "idObjeto" = 3 THEN COUNT("idAccionRawData") ELSE 0 END AS "product",';
        $qry .= ' CASE WHEN "idObjeto" = 4 THEN COUNT("idAccionRawData") ELSE 0 END AS "webpage",';
        $qry .= ' CASE WHEN "idObjeto" = 5 THEN COUNT("idAccionRawData") ELSE 0 END AS "video",';
        $qry .= ' CASE WHEN "idObjeto" = 11 THEN COUNT("idAccionRawData") ELSE 0 END AS "tour",';
        $qry .= ' CASE WHEN "idObjeto" = 10 THEN COUNT("idAccionRawData") ELSE 0 END AS "retrieval",';
        $qry .= ' CASE WHEN "idObjeto" = 6 THEN COUNT("idAccionRawData") ELSE 0 END AS "location"';
        $qry .= ' FROM "MS_SL"."ms_AccionRawData" ard ';
        $qry .= ' WHERE "idExpositor" = ' . $args["idExhibitor"];
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
        $registeredVisitors = ($result_pg['status']) ? $result_pg['data'] : array();

        $obj2 = array("booth" => array("idObjeto" => 1, "amount" => 0), "list" => array("idObjeto" => 2, "amount" => 0), "product" => array("idObjeto" => 3, "amount" => 0),
            "webpage" => array("idObjeto" => 4, "amount" => 0), "video" => array("idObjeto" => 5, "amount" => 0), "location" => array("idObjeto" => 6, "amount" => 0));

        foreach ($registeredVisitors as $key => $value) {
            foreach ($obj2 as $key2 => &$value2) {
                if ($value[$key2] > 0) {
                    $value2["amount"] ++;
                }
            }
        }

        $registeredVisitors = $obj2;
        $result = array();
        foreach ($noRegisterVisitors as $key => $value) {
            $result[$value["idObjeto"]]["total"] = $value["amount"];
            $result[$value["idObjeto"]]["anonymous"] = $value["amount"];
        }

        foreach ($registeredVisitors as $key => $value) {
            $result[$value["idObjeto"]]["registered"] = $value["amount"];
            if (!empty($result[$value["idObjeto"]])) {
                if (empty($result[$value["idObjeto"]]["total"]))
                    $result[$value["idObjeto"]]["total"] = 0;
                $result[$value["idObjeto"]]["total"] += $value["amount"];
            } else
                $result[$value["idObjeto"]]["total"] = $value["amount"];
            $result[$value["idObjeto"]]["key"] = $key;
        }
        $result2 = array();
        $index = 0;
        foreach ($result as $key => $value) {
            $anonymous = (!empty($value["anonymous"])) ? $value["anonymous"] : 0;
            $registered = (!empty($value["registered"]) ? $value["registered"] : 0);
            $result2[$index] = array("idObjeto" => $key, "amount" => $value["total"], "anonymous" => $anonymous, "registered" => $registered, "key" => $value["key"]);
            $index++;
        }
        return $result2;
    }

    public function getTotalUniqueVisitorsByEx($args) {
        $qry = 'SELECT';
        $qry .= ' COUNT("idVisitante") AS amount';
        $qry .= ' FROM(';
        $qry .= ' SELECT "idVisitante"';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_GlobalSummary" AS ard';
        $qry .= ' WHERE "idExpositor"=' . $args['idExhibitor'];
        $qry .= ' AND "idVisitante"<>0';
        $qry .= ' AND(';
        $qry .= ' "1">0'; //Booth
        $qry .= ' OR "2">0'; //List
        $qry .= ' OR "3">0'; //
        $qry .= ' OR "4">0'; //
        $qry .= ' OR "5">0'; //
        $qry .= ' OR "6">0'; //
        $qry .= ' OR "7">0'; //Views
        $qry .= ' OR "8">0'; //
        $qry .= ' OR "9">0'; //
        $qry .= ' OR "10">0'; //Leads
        $qry .= ' OR "11">0'; //Tours
        $qry .= ' )';
        $qry .= ' GROUP BY "idVisitante"';
        $qry .= ' )AS TEMP';

        $result_pg = $this->SQLModel->executeQuery($qry);
        $registered = ($result_pg['status']) ? $result_pg['data'] : array();

        $qry = 'SELECT';
        $qry .= ' COUNT (DISTINCT("Ip")) AS amount';
        $qry .= ' FROM(';
        $qry .= ' SELECT';
        $qry .= ' "Ip", "idObjeto",count("idAccionRawData") AS cant';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_AccionRawData" AS ard';
        $qry .= ' WHERE "idExpositor"=' . $args['idExhibitor'];
        $qry .= ' AND "idVisitante"=0';
        $qry .= ' GROUP BY "Ip", "idObjeto"';
        $qry .= ' HAVING count("idAccionRawData")>0';
        $qry .= ' ORDER BY "Ip", "idObjeto" ASC';
        $qry .= ' ) AS TEMP';
        $result_pg = $this->SQLModel->executeQuery($qry);
        $noRegistered = ($result_pg['status']) ? $result_pg['data'] : array();

        $sum = 0;
        if (!empty($registered)) {
            $sum += $registered[0]["amount"];
        }
        if (!empty($noRegistered)) {
            $sum += $noRegistered[0]["amount"];
        }
        return $sum;
    }

    public function getProductsResult($lang, $args) {
        $qry = 'SELECT '
                . '"idProducto","Producto","idExpositor","NombreCat"' . $lang . ' as Categoria, '
                . '"NombreExpositor", SUM("Cantidad") as cantidad ';
        $qry .= 'FROM "' . $this->pg_schema_ms_sl . '"."ms_SummaryProducto" ard '
                . 'WHERE "Fecha" BETWEEN \'' . $args["iniTimestamp"] . '\' AND \'' . $args["endTimestamp"] . '\'';
        $qry .= ' GROUP BY "idProducto"';
        $qry .= ' ORDER BY "Cantidad" DESC, "Producto" ASC';
        $result_pg = $this->SQLModel->executeQuery($qry);
        $data = ($result_pg['status']) ? $result_pg['data'] : array();
        return $data;
    }

    public function getProductsByEx($lang, $args) {

        $qry = 'SELECT ' . $this->getProductFields($lang) . ' FROM "' . $this->pg_schema_ms_sl . '"."ms_SummaryProducto" WHERE "Fecha" BETWEEN \'' . $args["iniTimestamp"] . '\' AND \'' . $args["endTimestamp"] . '\' AND idExpositor= ' . $args["idExpositor"];
        $qry .= ' GROUP BY "idProducto"';
        $qry .= ' ORDER BY cantidad DESC, "Producto" ASC';
        $result_pg = $this->SQLModel->executeQuery($qry);
        $data = ($result_pg['status']) ? $result_pg['data'] : array();
        return $data;
    }

    function getProductFields($lang) {

        $fields = '';
        $fields .= ' "idProducto", ';
        $fields .= ' "Producto", ';
        $fields .= ' "NombreCat"' . $lang . ' as Categoria, ';
        $fields .= ' "SUM("Cantidad") as cantidad ';
        return $fields;
    }

    public function getKioskDates($args) {


        $qry = 'SELECT DISTINCT("Fecha") as fecha FROM "' . $this->pg_schema_ms_sl . '"."ms_SummaryARD" WHERE kiosko!=0';
        $qry .= ' ORDER BY fecha ASC';
        $result_pg = $this->SQLModel->executeQuery($qry);
        $data = ($result_pg['status']) ? $result_pg['data'] : array();
        return $data;
    }

    function ordenar_array($datos, $campo, $orden) {
        $sort = array();
        foreach ($datos as $k => $v) {
            $sort[$campo][$k] = $v[$campo];
        }
//orden SORT_DESC, SORT_ASC
        array_multisort($sort[$campo], $orden, $datos);
        return $datos;
    }

//    public function getCaseFields($args) {
//        $qry = 'SELECT * ';
//        $qry .= 'FROM "' . $this->pg_schema . '"."ms_SummaryARD" ';
//        $qry .= 'WHERE "idSummaryARD"=(SELECT MIN("idSummaryARD") ';
//        $qry .= 'FROM "' . $this->pg_schema . '"."ms_SummaryARD")';
//        $result_pg = $this->SQLModel->executeQuery($qry);
//        return $result_pg;
//    }

    public function getPackages($args) {
        $qry = 'SELECT';
        $qry .= ' "idPaquete"';
        $qry .= ', "PaqueteES"';
        $qry .= ', "PaqueteEN"';
        $qry .= ', "PaquetePT"';
        $qry .= ', "PaqueteFR"';
        $qry .= ' FROM "' . $this->pg_schema_sas . '"."Paquete"';
        $qry .= ' WHERE';
        $qry .= ' "idEdicion"=' . $args['idEdicion'];
        $result_pg = $this->SQLModel->executeQuery($qry);
        if ($result_pg['status']) {
            $response = array();
            foreach ($result_pg['data'] as $key => $paquete) {
                $response[$key] = $paquete;
                $response[$key]['idModuloEdicion'] = $args['idModuloEdicion'];
            }
        } else {
            $response = NULL;
        }
        return $response;
    }

    public function getCountVisitors($args, $params = '', $test = false) {
        $qry = 'SELECT';
        #First Select for countig Visitors
        $qry .= ' (SELECT COUNT (DISTINCT(v."idVisitante")) AS "total"';
        $qry .= ' FROM "' . $this->pg_schema_ae . '"."Visitante" v';
        $qry .= ' INNER JOIN "' . $this->pg_schema_ae . '"."VisitanteEdicion" AS ve ON v."idVisitante" = ve."idVisitante"';
        $qry .= ' JOIN (SELECT';
        $qry .= ' "idVisitante", SUM("11") AS "Recorrido", SUM("10") AS "Lectura", SUM("2") AS "List", SUM("1") AS "Booth", SUM("5") AS "Video", SUM("6") AS "Location", SUM("3") AS "Product", SUM("4") AS "WebPage"';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_GlobalSummary" ';
        if ((isset($params['where']) && empty($params['where'])) || empty($params)) {
            $qry .= ' WHERE "idEdicion" = ' . $args['idEdicion'];
            $qry .= ' AND "idEvento" = ' . $args['idEvento'];
        } else {
            $qry .= ' WHERE "idEdicion" = ' . $args['idEdicion'];
            $qry .= ' AND "idEvento" = ' . $args['idEvento'];
            foreach ($params as $conditions) {
                foreach ($conditions as $colum) {
                    $qry .= $this->switchWhereMS($colum);
                }
            }
        }
        $qry .= ' AND "idExpositor"=' . $args['idExhibitor'];
        $qry .= ' AND "idVisitante" !=0';
        $qry .= ' AND MAKE_DATE("year","month","day")';
        $qry .= ' BETWEEN \'' . $args["iniTimestamp"] . '\' AND \'' . $args["endTimestamp"] . '\'';
        $qry .= ' AND ("1">0 OR "2">0 OR "3">0 OR "4">0 OR "5">0 OR "6">0 OR "11">0 OR "10">0) ';
        $qry .= ' GROUP BY "idVisitante", "idEvento","idEdicion","11", "10", "2", "1", "5", "6", "3", "4" ';
        $qry .= ' ORDER BY "idVisitante", "11", "10", "2", "1", "5", "6", "3", "4") as ard ';
        $qry .= ' ON v."idVisitante"=ard."idVisitante" ';
        if ((isset($params['where']) && empty($params['where'])) || empty($params)) {
            $qry .= ' WHERE ve."idEdicion" = ' . $args['idEdicion'];
            $qry .= ' AND ve."idEvento" = ' . $args['idEvento'];
        } else {
            $qry .= ' WHERE ve."idEdicion" = ' . $args['idEdicion'];
            $qry .= ' AND ve."idEvento" = ' . $args['idEvento'];
            foreach ($params as $conditions) {
                foreach ($conditions as $colum) {
                    $qry .= $this->switchWhere($colum);
                }
            }
        }
        $qry .= ')+(';
        #Second Select Count of Exhibitors
        $qry .= 'SELECT COUNT(DISTINCT(dg."idDetalleGafete")) AS "total"';
        $qry .= ' FROM "' . $this->pg_schema_sas . '"."DetalleGafete" dg';
        $qry .= ' INNER JOIN "' . $this->pg_schema_sas . '"."Empresa" AS emp ON dg."idEmpresa" = emp."idEmpresa"';
        $qry .= ' JOIN(';
        $qry .= ' SELECT "idGafeteExpositor", SUM("10") AS "Lectura"';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_GlobalSummary"';
        $qry .= ' WHERE "idEdicion" = ' . $args['idEdicion'];
        $qry .= ' AND "idEvento" = ' . $args['idEvento'];
        foreach ($params as $conditions) {
            foreach ($conditions as $colum) {
                $qry .= $this->switchWhereMS($colum);
            }
        }
        $qry .= ' AND "idExpositor" = ' . $args['idExhibitor'];
        $qry .= ' AND "idGafeteExpositor" IS NOT NULL';
        $qry .= ' AND MAKE_DATE("year","month","day")';
        $qry .= ' BETWEEN \'' . $args["iniTimestamp"] . '\' AND \'' . $args["endTimestamp"] . '\'';
        $qry .= ' GROUP BY';
        $qry .= ' "idGafeteExpositor", "idEvento","idEdicion","10"';
        $qry .= ' ORDER BY';
        $qry .= ' "idGafeteExpositor", "10"';
        $qry .= ' ) AS ard ON dg."idDetalleGafete" = ard."idGafeteExpositor"';
        if ((isset($params['where']) && empty($params['where'])) || empty($params)) {
            $qry .= ' WHERE dg."idEdicion" = ' . $args['idEdicion'];
        } else {
            $qry .= ' WHERE dg."idEdicion" = ' . $args['idEdicion'];
            foreach ($params as $conditions) {
                foreach ($conditions as $colum) {
                    $qry .= $this->switchWhere($colum, 1);
                }
            }
        }
        $qry .= ')+(';
        #Third Select Count of Scanner Infoexpo App
        $qry .= 'SELECT COUNT(DISTINCT(LC."idLecturaContacto")) AS "total"';
        $qry .= ' FROM "LECTORAS"."LecturaContacto" LC';
        $qry .= ' INNER JOIN "LECTORAS"."EmpresaScanner" AS ES ON LC."idEmpresaScanner" = ES."idEmpresaScanner"';
        if ((isset($params['where']) && empty($params['where'])) || empty($params)) {
            $qry .= ' WHERE ES."idEdicion" = ' . $args['idEdicion'];
            $qry .= ' AND "idEmpresa"=' . $args['idExhibitor'];
        } else {
            $qry .= ' WHERE ES."idEdicion" = ' . $args['idEdicion'];
            $qry .= ' AND "idEmpresa"=' . $args['idExhibitor'];
            foreach ($params as $conditions) {
                foreach ($conditions as $colum) {
                    $qry .= $this->switchWhere($colum, 2);
                }
            }
        }
        $qry .= ' ) AS "total"';
//        if($test){
//            print_r($qry);die();
//        }
//        else{
//            print_r($qry);die();
//        }
        return $this->SQLModel->executeQuery($qry);
    }

    public function getVisitorCustom($args, $columns = '', $params = '') {
        $columns_str = '';
        $columns_str_ex = '';
        $columns_str_ex_app = '';
        $group_by = ' ';
        $group_by_ex = ' ';
        $group_by_ex_app = '';
        #Assemble of Select for Visitors
        if (is_array($columns) && COUNT($columns) > 0) {
            $group_by .= 'GROUP BY';
            foreach ($columns as $column) {
                $columns_str .= $this->switchSelect($column, 0);
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
        #End of Assemble for Visitors
        $qry .= ' SELECT ' . $columns_str;
        $qry .= ' FROM	"' . $this->pg_schema_ae . '"."Visitante" v';
        $qry .= ' INNER JOIN "' . $this->pg_schema_ae . '"."VisitanteEdicion" AS ve ON v."idVisitante" = ve."idVisitante"';
        $qry .= ' JOIN (SELECT  "idVisitante", SUM("11") AS "Recorrido", SUM("10") AS "Lectura", SUM("2") AS "List", SUM("1") AS "Booth", SUM("5") AS "Video", SUM("6") AS "Location", SUM("3") AS "Product", SUM("4") AS "WebPage"';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_GlobalSummary" ';
        if ((isset($params['where']) && empty($params['where'])) || empty($params)) {
            $qry .= ' WHERE "idEdicion" = ' . $args['idEdicion'];
            $qry .= ' AND "idEvento"=' . $args['idEvento'];
        } else {
            $qry .= ' WHERE "idEdicion" = ' . $args['idEdicion'];
            $qry .= ' AND "idEvento"=' . $args['idEvento'];
            foreach ($params as $conditions) {
                foreach ($conditions as $colum) {
                    $qry .= $this->switchWhereMS($colum);
                }
            }
        }
        $qry .= ' AND "idExpositor"=' . $args['idExhibitor'];
        $qry .= ' AND "idVisitante" != 0';
        $qry .= ' AND MAKE_DATE("year","month","day")';
        $qry .= ' BETWEEN \'' . $args["iniTimestamp"] . '\' AND \'' . $args["endTimestamp"] . '\'';
        $qry .= ' AND ("1">0 OR "2">0 OR "3">0 OR "4">0 OR "5">0 OR "6">0 OR "11">0 OR "10">0) ';
        $qry .= ' GROUP BY "idVisitante"';
        $qry .= ' ORDER BY "idVisitante") as ard ';
        $qry .= ' ON v."idVisitante"=ard."idVisitante" ';
        if ((isset($params['where']) && empty($params['where'])) || empty($params)) {
            $qry .= ' WHERE ve."idEdicion" = ' . $args['idEdicion'];
            $qry .= ' AND ve."idEvento" = ' . $args['idEvento'];
        } else {
            $qry .= ' WHERE ve."idEdicion" = ' . $args['idEdicion'];
            $qry .= ' AND ve."idEvento" = ' . $args['idEvento'];
            foreach ($params as $conditions) {
                foreach ($conditions as $colum) {
                    $qry .= $this->switchWhere($colum);
                }
            }
        }
        $qry .= ' UNION ';
        #Assemble of Select for Exhibitors
        if (is_array($columns) && COUNT($columns) > 0) {
            $group_by_ex .= 'GROUP BY';
            foreach ($columns as $column) {
                $columns_str_ex .= $this->switchSelect($column, 1);
                $column_name = $column;
                if (strpos(strtoupper($column), " AS ")) {
                    $column_name = substr($column, 0, strpos(strtoupper($column), " AS "));
                }
                $group_by_ex .= ' ' . $column_name . ',';
            }
            $columns_str_ex = substr($columns_str_ex, 0, -1);
            $group_by = substr($group_by, 0, -1);
        }
        $order_by_ex = ' ';
        if (is_array($order) && COUNT($order) > 0) {
            $order_by .= 'ORDER BY';
            foreach ($order as $order_column) {
                $order_by .= ' ' . $order_column["name"] . ' ' . $order_column["dir"] . ',';
            }
            $order_by = substr($order_by, 0, -1);
        }
        #End of Assemble for Exhibitors
        $qry .= ' SELECT ' . $columns_str_ex;
        $qry .= ' FROM "' . $this->pg_schema_sas . '"."DetalleGafete" dg';
        $qry .= ' INNER JOIN "' . $this->pg_schema_sas . '"."Empresa" AS emp ON dg."idEmpresa" = emp."idEmpresa"';
        $qry .= ' JOIN (SELECT  "idGafeteExpositor", SUM("11") AS "Recorrido", SUM("10") AS "Lectura", SUM("2") AS "List", SUM("1") AS "Booth", SUM("5") AS "Video", SUM("6") AS "Location", SUM("3") AS "Product", SUM("4") AS "WebPage"';
        $qry .= ' FROM "' . $this->pg_schema_ms_sl . '"."ms_GlobalSummary" ';
        if ((isset($params['where']) && empty($params['where'])) || empty($params)) {
            $qry .= ' WHERE "idEdicion" = ' . $args['idEdicion'];
        } else {
            $qry .= ' WHERE "idEdicion" = ' . $args['idEdicion'];
            foreach ($params as $conditions) {
                foreach ($conditions as $colum) {
                    $qry .= $this->switchWhereMS($colum);
                }
            }
        }
        $qry .= ' AND "idExpositor"=' . $args['idExhibitor'];
        $qry .= ' AND "idGafeteExpositor" != 0';
        $qry .= ' AND MAKE_DATE("year","month","day")';
        $qry .= ' BETWEEN \'' . $args["iniTimestamp"] . '\' AND \'' . $args["endTimestamp"] . '\'';
        $qry .= ' AND ("1">0 OR "2">0 OR "3">0 OR "4">0 OR "5">0 OR "6">0 OR "11">0 OR "10">0) ';
        $qry .= ' GROUP BY "idGafeteExpositor"';
        $qry .= ' ORDER BY "idGafeteExpositor") as ard ';
        $qry .= ' ON ard."idGafeteExpositor"=dg."idDetalleGafete" ';
        if ((isset($params['where']) && empty($params['where'])) || empty($params)) {
            $qry .= ' WHERE dg."idEdicion" = ' . $args['idEdicion'];
            $qry .= ' AND dg."idEvento" = ' . $args['idEvento'];
        } else {
            $qry .= ' WHERE dg."idEdicion" = ' . $args['idEdicion'];
            $qry .= ' AND dg."idEvento" = ' . $args['idEvento'];
            foreach ($params as $conditions) {
                foreach ($conditions as $colum) {
                    $qry .= $this->switchWhere($colum, 1);
                }
            }
        }
        $qry .= ' UNION ';
        #Third Assemble of Select for Exhibitors with Scanner Infoexpo
        if (is_array($columns) && COUNT($columns) > 0) {
            $group_by_ex_app .= 'GROUP BY';
            foreach ($columns as $column) {
                $columns_str_ex_app .= $this->switchSelect($column, 2);
                $column_name = $column;
                if (strpos(strtoupper($column), " AS ")) {
                    $column_name = substr($column, 0, strpos(strtoupper($column), " AS "));
                }
                $group_by_ex_app .= ' ' . $column_name . ',';
            }
            $columns_str_ex_app = substr($columns_str_ex_app, 0, -1);
            $group_by = substr($group_by, 0, -1);
        }
        $order_by_ex_app = ' ';
        if (is_array($order) && COUNT($order) > 0) {
            $order_by .= 'ORDER BY';
            foreach ($order as $order_column) {
                $order_by_ex_app .= ' ' . $order_column["name"] . ' ' . $order_column["dir"] . ',';
            }
            $order_by_ex_app = substr($order_by_ex_app, 0, -1);
        }
        #End of Assemble for Exhibitors
        $qry .= ' SELECT ' . $columns_str_ex_app;
        $qry .= ' FROM "LECTORAS"."LecturaContacto" LC';
        $qry .= ' INNER JOIN "LECTORAS"."EmpresaScanner" AS ES ON LC."idEmpresaScanner" = ES."idEmpresaScanner"';
        if ((isset($params['where']) && empty($params['where'])) || empty($params)) {
            $qry .= ' WHERE ES."idEdicion" = ' . $args['idEdicion'];
            $qry .= ' AND ES."idEvento" = ' . $args['idEvento'];
            $qry .= ' AND "idEmpresa"=' . $args['idExhibitor'];
        } else {
            $qry .= ' WHERE ES."idEdicion" = ' . $args['idEdicion'];
            $qry .= ' AND ES."idEvento" = ' . $args['idEvento'];
            $qry .= ' AND "idEmpresa"=' . $args['idExhibitor'];
            foreach ($params as $conditions) {
                foreach ($conditions as $colum) {
                    $qry .= $this->switchWhere($colum, 2);
                }
            }
        }
        print_r($qry);die();
        $result_query = $this->SQLModel->executeQuery($qry);
        if ($result_query["status"]) {
            return Array("status" => TRUE, "data" => $result_query['data']);
        } else {
            return Array("status" => FALSE, "error" => $result_query["error"]["string"]);
        }
    }

    public function switchSelect($colum, $vis_ex = 0) {//0 is Visitor 1 is Exhibitor
        $qry = '';
        switch ($colum) {
            case '"NombreCompleto"':
                if ($vis_ex == 1) {
                    $qry .= ' concat("DGNombre", \'\',"DGApellidoPaterno",\'\',"DGApellidoMaterno") AS "NombreCompleto",';
                } elseif ($vis_ex == 0 || $vis_ex == 2) {
                    $qry .= 'concat(COALESCE("Nombre",\'\'),\' \',COALESCE("ApellidoPaterno",\'\'),\' \',COALESCE("ApellidoMaterno",\'\'))';
                    $qry .= ' AS "NombreCompleto",';
                } else {
                    
                }
                break;
            case '"Email"':
                if ($vis_ex == 1) {
                    $qry .= ' "DGEmail",';
                } else if ($vis_ex == 0 || $vis_ex == 2) {
                    $qry .= ' ' . $colum . ',';
                }
                break;
            case '"DE_Cargo"':
                if ($vis_ex == 1) {
                    $qry .= ' "DGPuesto",';
                } else if ($vis_ex == 0) {
                    $qry .= ' ' . $colum . ',';
                } else {
                    $qry .= ' "Puesto",';
                }
                break;
            case '"DE_RazonSocial"':
                if ($vis_ex == 1) {
                    $qry .= ' "DGEmpresa",';
                } else if ($vis_ex == 0) {
                    $qry .= ' ' . $colum . ',';
                } else {
                    $qry .= ' \'Infoexpo\' AS "DGEmpresa",';
                }
                break;
            case '"DE_Telefono"':
                if ($vis_ex == 1) {
                    $qry .= ' "DC_Telefono",';
                } else if ($vis_ex == 0) {
                    $qry .= ' ' . $colum . ',';
                } else {
                    $qry .= ' "Telefono",';
                }
                break;
            case '"DE_Ciudad"':
                if ($vis_ex == 1) {
                    $qry .= ' "DC_Ciudad",';
                } else if ($vis_ex == 0) {
                    $qry .= ' ' . $colum . ',';
                } else {
                    $qry .= ' \'MEXICO\' AS "Ciudad",';
                }
                break;
            case '"DE_Estado"':
                if ($vis_ex == 1) {
                    $qry .= ' "DC_Estado",';
                } else if ($vis_ex == 0) {
                    $qry .= ' ' . $colum . ',';
                } else {
                    $qry .= ' \'DISTRITO FEDERAL\' AS "Estado",';
                }
                break;
            case '"DE_Pais"':
                if ($vis_ex == 1) {
                    $qry .= ' "DC_Pais",';
                } else if ($vis_ex == 0) {
                    $qry .= ' ' . $colum . ',';
                } else {
                    $qry .= ' \'MEXICO\' AS "Pais",';
                }
                break;
            case '"DireccionCompleta"':
                if ($vis_ex == 1) {
                    $qry .= ' concat (' .
                            'COALESCE ("DC_CalleNum", \'\'),' .
                            'CASE WHEN COALESCE ("DC_CodigoPostal", \'\') = \'\' THEN \'\' ' .
                            'ELSE concat (\' C.P.\', "DC_CodigoPostal") END, ' .
                            'CASE WHEN COALESCE ("DC_Colonia", \'\') = \'\' THEN \'\' ' .
                            'ELSE concat (\' COL.\', "DC_Colonia") END' .
                            ') AS "DireccionCompleta",';
                } else if ($vis_ex == 0) {
                    $qry .= ' concat (';
                    $qry .= 'COALESCE ("DE_Direccion", \'\'),';
                    $qry .= 'CASE WHEN COALESCE ("DE_CP", \'\') = \'\' THEN \'\' ';
                    $qry .= 'ELSE concat (\' C.P.\', "DE_CP") END, ';
                    $qry .= 'CASE WHEN COALESCE ("DE_Colonia", \'\') = \'\' THEN \'\' ';
                    $qry .= 'ELSE concat (\' COL.\', "DE_Colonia") END';
                    $qry .= ') AS "DireccionCompleta",';
                } else {
                    $qry .= ' \'Test\' AS "Direccion",';
                }
                break;
            case '"Boletines"':
                if ($vis_ex == 1) {
                    $qry .= ' 0 AS' . $colum . ',';
                } else if ($vis_ex == 0) {
                    $qry .= ' COALESCE(' . $colum . ',0) AS' . $colum . ',';
                } else {
                    $qry .= ' 1 AS "Boletines",';
                }
                break;
            case '"Lectura"':
                if ($vis_ex == 2) {
                    $qry .= ' 0 AS ' . $colum . ',';
                } else {
                    $qry .= ' ' . $colum . ',';
                }
                break;
            default:
                if ($vis_ex == 2) {
                    $qry .= ' 0 AS ' . $colum . ',';
                } else {
                    $qry .= ' ' . $colum . ',';
                }
                break;
        }
        return $qry;
    }

    public function switchWhere($colum, $vis_ex = 0) {//0 is Visitor 1 is Exhibitor
        $qry = '';
        switch ($colum['name']) {
            case '"NombreCompleto"':
                if ($vis_ex == 1) {
                    $qry .= ' AND (';
                    $qry .= ' "DGNombre" ' . $colum['operator'] . ' \'' . $colum['value'] . '\'';
                    $qry .= ' OR "DGApellidoPaterno" ' . $colum['operator'] . ' \'' . $colum['value'] . '\'';
                    $qry .= ' OR "DGApellidoMaterno" ' . $colum['operator'] . ' \'' . $colum['value'] . '\')';
                } else {
                    $qry .= ' AND (';
                    $qry .= ' "Nombre" ' . $colum['operator'] . ' \'' . $colum['value'] . '\'';
                    $qry .= ' OR "ApellidoPaterno" ' . $colum['operator'] . ' \'' . $colum['value'] . '\'';
                    $qry .= ' OR "ApellidoMaterno" ' . $colum['operator'] . ' \'' . $colum['value'] . '\')';
                }
                break;
            case '"Email"':
                if ($vis_ex == 1) {
                    $qry .= ' AND "DGEmail" ' . $colum['operator'];
                } else {
                    $qry .= ' AND ' . $colum["name"] . ' ' . $colum['operator'];
                }
                break;
            case '"DE_Cargo"':
                if ($vis_ex == 1) {
                    $qry .= ' AND "DGPuesto" ' . $colum['operator'];
                } else if ($vis_ex == 0) {
                    $qry .= ' AND ' . $colum["name"] . ' ' . $colum['operator'];
                } else {
                    $qry .= ' AND "Puesto" ' . $colum['operator'];
                }
                break;
            case '"DE_RazonSocial"':
                if ($vis_ex == 1) {
                    $qry .= ' AND "DGEmpresa" ' . $colum['operator'];
                } else if ($vis_ex == 0) {
                    $qry .= ' AND ' . $colum["name"] . ' ' . $colum['operator'];
                }
                break;
            case '"DireccionCompleta"':
                if ($vis_ex == 1) {
                    $qry .= ' AND (';
                    $qry .= ' "DC_CalleNum" ' . $colum['operator'] . ' \'' . $colum['value'] . '\'';
                    $qry .= ' OR "DC_CodigoPostal" ' . $colum['operator'] . ' \'' . $colum['value'] . '\'';
                    $qry .= ' OR "DC_Colonia" ' . $colum['operator'] . ' \'' . $colum['value'] . '\')';
                } else if ($vis_ex == 0) {
                    $qry .= ' AND (';
                    $qry .= ' "DE_Direccion" ' . $colum['operator'] . ' \'' . $colum['value'] . '\'';
                    $qry .= ' OR "DE_CP" ' . $colum['operator'] . ' \'' . $colum['value'] . '\'';
                    $qry .= ' OR "DE_Colonia" ' . $colum['operator'] . ' \'' . $colum['value'] . '\')';
                }
                break;
            case '"DE_Telefono"':
                if ($vis_ex == 1) {
                    $qry .= ' AND "DC_Telefono" ' . $colum['operator'];
                } else if ($vis_ex == 0) {
                    $qry .= ' AND ' . $colum["name"] . ' ' . $colum['operator'];
                } else {
                    $qry .= ' AND "Telefono" ' . $colum['operator'];
                }
                break;
            case '"DE_Ciudad"':
                if ($vis_ex == 1) {
                    $qry .= ' AND "DGEmail" ' . $colum['operator'];
                } else if ($vis_ex == 0) {
                    $qry .= ' AND ' . $colum["name"] . ' ' . $colum['operator'];
                }
                break;
            case '"DE_Estado"':
                if ($vis_ex == 1) {
                    $qry .= ' AND "DC_Estado" ' . $colum['operator'];
                } else if ($vis_ex == 0) {
                    $qry .= ' AND ' . $colum["name"] . ' ' . $colum['operator'];
                }
                break;
            case '"DE_Pais"':
                if ($vis_ex == 1) {
                    $qry .= ' AND "DC_Pais" ' . $colum['operator'];
                } else if ($vis_ex == 0) {
                    $qry .= ' AND ' . $colum["name"] . ' ' . $colum['operator'];
                }
                break;
            case '"Boletines"':
                if ($vis_ex == 1) {
                    $qry = '';
                } else if ($vis_ex == 0) {
                    $qry .= ' AND ' . $colum["name"] . ' ' . $colum['operator'];
                }
                break;
        }
        if ($qry == '') {
            $qry = '';
        } else if ($colum['name'] !== '"NombreCompleto"' && $colum['name'] !== '"DireccionCompleta"') {
            $qry .= '\'' . gettype($colum['value']) . '\'' == 'string' ? ' \'' . $colum["value"] . '\' ' : '  \'' . $colum["value"] . '\' ';
        }
        return $qry;
    }

    public function switchWhereMS($colum) {//0 is Visitor 1 is Exhibitor
        $qry = '';
        switch ($colum['name']) {
            case '"Recorrido"':
                $qry .= ' AND "11" ' . $colum['operator'];
                break;
            case '"Lectura"':
                if ($colum['value'] == 0) {
                    $qry .= ' AND "10" <';
                } else if ($colum['value'] == 1) {
                    $qry .= ' AND  "10" >=';
                }
                break;
            case '"List"':
                $qry .= ' AND "2" ' . $colum['operator'];
                break;
            case '"Booth"':
                $qry .= ' AND "1" ' . $colum['operator'];
                break;
            case '"Video"':
                $qry .= ' AND "5" ' . $colum['operator'];
                break;
            case '"Location"':
                $qry .= ' AND "6" ' . $colum['operator'];
                break;
            case '"Product"':
                $qry .= ' AND "3" ' . $colum['operator'];
                break;
            case '"WebPage"':
                $qry .= ' AND "4" ' . $colum['operator'];
                break;
        }
        if ($qry == '') {
            $qry = '';
        } else {
            $qry .= '\'' . gettype($colum['value']) . '\'' == 'string' ? ' \'' . $colum["value"] . '\' ' : '  \'' . $colum["value"] . '\' ';
        }
        return $qry;
    }

}
