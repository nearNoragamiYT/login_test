<?php

namespace MS\FloorplanBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

/**
 *
 * @author Ernesto L
 */
class FloorplanExhibitorLeadsDevModel {
    private $pg_schema_sas = 'SAS', $pg_schema_ms_sl = 'MS_SL', $pg_schema_ae = 'AE', $pg_schema_lectoras = 'LECTORAS';

    function __construct() {
        $this->SQLModel = new SQLModel();
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
                case '"Fecha"':
                if ($vis_ex == 0 || $vis_ex == 1) {
                    $qry .= ' concat("day","") AS "Fecha",';
                } else {
                    $qry .= ' concat(EXTRACT(DAY FROM "FechaCaptura"),\'-\',EXTRACT(MONTH FROM "FechaCaptura"),\'-\',EXTRACT(YEAR FROM "FechaCaptura")) AS "Fecha",';
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
