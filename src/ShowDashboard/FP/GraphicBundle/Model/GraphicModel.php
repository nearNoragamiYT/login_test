<?php

namespace ShowDashboard\FP\GraphicBundle\Model;

use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use Utilerias\SQLBundle\Model\SQLModel;

/**
 * Description of TypeStandsModel
 *
 * @author Neto
 */
class GraphicModel {

    protected $metadata_type = Array(
        'floorplan' => Array(
            '"idEvento"' => 'integer',
            '"idEdicion"' => 'integer',
            '"idStand"' => 'integer',
            '"StandNumber"' => 'character',
            '"Stand_X"' => 'integer',
            '"Stand_Y"' => 'integer',
            '"Stand_W"' => 'double',
            '"Stand_H"' => 'double',
            '"idTipoStand"' => 'integer'
        ),
    );
    protected
            $pg_schema = '"SAS"',
            $pg_sta = '"Stand"',
            $pg_stt = '"TipoStand"',
            $pg_edi = '"Edicion"';

    public function __construct() {
        $this->PGSQL_Client = new PGSQLClient();
        $this->SQLModel = new SQLModel();
    }

    public function getModulo($args = array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("ModuloIxpo", $fields, $args, array('"idModuloIxpo"' => 'ASC'));
    }

    public function breadcrumb($route, $lang) {
        /* Modulos */
        $modulos = Array();
        $breadcrumb = Array();
        $result_modulos = $this->getModulo();
        foreach ($result_modulos['data'] as $key => $value) {
            $modulos[$value['idModuloIxpo']] = $value;
            if ($value['Ruta'] === $route) {
                $id_modulo = $value['idModuloIxpo'];
                $id_padre = $value['idPadre'];
                $ruta = $value['Ruta'];
            }
        }
        $this->findBreadcrumbParent($id_modulo, $modulos, $lang, $breadcrumb, $ruta);
        $result = array(array("breadcrumb" => $breadcrumb[0], "route" => $ruta));
        return $result;
    }

    private function findBreadcrumbParent($id_modulo, $modulos, $lang, &$breadcrumb, $ruta) {
        array_unshift($breadcrumb, $modulos[$id_modulo]['Modulo_' . strtoupper($lang)]);
        if ($modulos[$id_modulo]['idPadre'] != 0) {
            $this->findBreadcrumbParent($modulos[$id_modulo]['idPadre'], $modulos, $lang, $breadcrumb);
        }
        return $breadcrumb;
    }

    public function getHallsTotal($idEdicion) {
        //Numero de Stands para la Edicion
        $qry = 'SELECT';
        $qry .= ' COUNT("idStand") as value';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_sta;
        $qry .= ' WHERE ' . $this->pg_schema . '.' . $this->pg_sta . '."idEdicion"=' . $idEdicion;
        $qry .= ' AND "Stand_W" > 0';
        $qry .= ' AND "Stand_H" > 0';
        $qry .= ' AND "Stand_X" > 0';
        $qry .= ' AND "Stand_Y" > 0';
        $stands = $this->PGSQL_Client->execQueryString($qry);
        
        //Numero de Stands Libres para la Edicion
        $qry = 'SELECT';
        $qry .= ' COUNT("idStand") as value';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_sta;
        $qry .= ' WHERE ' . $this->pg_schema . '.' . $this->pg_sta . '."StandStatus"=' . "'libre'";
        $qry .= ' AND "idEdicion"=' . $idEdicion;
        $qry .= ' AND "Stand_W" > 0';
        $qry .= ' AND "Stand_H" > 0';
        $qry .= ' AND "Stand_X" > 0';
        $qry .= ' AND "Stand_Y" > 0';
        $standsAvailable = $this->PGSQL_Client->execQueryString($qry);
        
        //Numero de Stands Reservados para la Edicion
        $qry = 'SELECT';
        $qry .= ' COUNT("idStand") as value';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_sta;
        $qry .= ' WHERE ' . $this->pg_schema . '.' . $this->pg_sta . '."StandStatus"=' . "'reservado'";
        $qry .= ' AND "idEdicion"=' . $idEdicion;
        $qry .= ' AND "Stand_W" > 0';
        $qry .= ' AND "Stand_H" > 0';
        $qry .= ' AND "Stand_X" > 0';
        $qry .= ' AND "Stand_Y" > 0';
        $standsReserved = $this->PGSQL_Client->execQueryString($qry);
        
        //Numero de Stands Contratados para la Edicion
        $qry = 'SELECT';
        $qry .= ' COUNT("idStand") as value';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_sta;
        $qry .= ' WHERE ' . $this->pg_schema . '.' . $this->pg_sta . '."StandStatus"=' . "'contratado'";
        $qry .= ' AND "idEdicion"=' . $idEdicion;
        $qry .= ' AND "Stand_W" > 0';
        $qry .= ' AND "Stand_H" > 0';
        $qry .= ' AND "Stand_X" > 0';
        $qry .= ' AND "Stand_Y" > 0';
        $standsOccupied = $this->PGSQL_Client->execQueryString($qry);

        //Total Libre 
        $qry = 'SELECT ';
        $qry .= 'SUM("StandArea") as libre';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_sta;
        $qry .= ' WHERE ' . $this->pg_schema . '.' . $this->pg_sta . '."StandStatus"=' . "'libre'";
        $qry .= ' AND "idEdicion"=' . $idEdicion;
        $qry .= ' AND "Stand_W" > 0';
        $qry .= ' AND "Stand_H" > 0';
        $qry .= ' AND "Stand_X" > 0';
        $qry .= ' AND "Stand_Y" > 0';
        $totalAvailable = $this->PGSQL_Client->execQueryString($qry);

        //Total Contratado
        $qry = 'SELECT ';
        $qry .= 'SUM("StandArea") as contratado';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_sta;
        $qry .= ' WHERE ' . $this->pg_schema . '.' . $this->pg_sta . '."StandStatus"=' . "'contratado'";
        $qry .= ' AND "idEdicion"=' . $idEdicion;
        $qry .= ' AND "Stand_W" > 0';
        $qry .= ' AND "Stand_H" > 0';
        $qry .= ' AND "Stand_X" > 0';
        $qry .= ' AND "Stand_Y" > 0';
        $totalHired = $this->PGSQL_Client->execQueryString($qry);

        //Total Reservado
        $qry = 'SELECT ';
        $qry .= 'SUM("StandArea") as reservado';
        $qry .= ' FROM ' . $this->pg_schema . '.' . $this->pg_sta;
        $qry .= ' WHERE ' . $this->pg_schema . '.' . $this->pg_sta . '."StandStatus"=' . "'reservado'";
        $qry .= ' AND "idEdicion"=' . $idEdicion;
        $qry .= ' AND "Stand_W" > 0';
        $qry .= ' AND "Stand_H" > 0';
        $qry .= ' AND "Stand_X" > 0';
        $qry .= ' AND "Stand_Y" > 0';
        $totalReserved = $this->PGSQL_Client->execQueryString($qry);

        //------------------------------------------------------------        
        $standValue = Array();
        $standValue['quantity'] = number_format($stands['data'][0]['value']);
        $standValue['quantityAvailable'] = number_format($standsAvailable['data'][0]['value']);
        $standValue['quantityReserved'] = number_format($standsReserved['data'][0]['value']);
        $standValue['quantityOccupied'] = number_format($standsOccupied['data'][0]['value']);
        $standValue['available'] = $totalAvailable['data'][0]['libre'] == '' ? 0 : round($totalAvailable['data'][0]['libre'],2);
        $standValue['occupied'] = $totalHired['data'][0]['contratado'] == '' ? 0 : round($totalHired['data'][0]['contratado'],2);
        $standValue['reserved'] = $totalReserved['data'][0]['reservado'] == '' ? 0 : round($totalReserved['data'][0]['reservado'],2);

        $standValue['total'] = number_format($standValue['available'] + $standValue['occupied'] + $standValue['reserved'], 2);
        $standValue['summary'] = $standValue['available'] + $standValue['occupied'] + $standValue['reserved'];

        if (array_key_exists("error", $standValue) || COUNT($standValue) == 0) {
            return Array("status" => FALSE, "data" => 'error');
        } else {
            return Array("status" => FALSE, "data" => $standValue);
        }
    }

}
