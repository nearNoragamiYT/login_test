<?php

namespace Visitante\VisitantesGeneralesBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Visitante\VisitanteBundle\Model\MainModel;
use Utilerias\SQLBundle\Model\SQLModel;

class VisitantesGeneralesModel extends MainModel {

    public $SQLModel, $PGSQLModel;

    public function __construct(ContainerInterface $container = null) {
        parent::__construct($container);
        $this->SQLModel = new SQLModel();
    }

    public function getCount($where = '', $idEdicion = '') {
        $qry = ' SELECT COUNT("Email") FROM "AE"."Visitante" ';
        if ($where != '') {
            $qry .= 'WHERE ' . $where;
        }

        $result = $this->SQLModel->executeQuery($qry);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function getListidVisitantes($where = '', $idEdicion = '') {
        $qry = ' SELECT  "idVisitante" ';
        $qry .= ' FROM';
        $qry .= ' "AE"."Visitante"';
        if ($where != '') {
            $qry .= 'WHERE ' . $where;
        }
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $flag = "";
                    $idVisitante = $value['idVisitante'];
                    if ($idVisitante == "") {

                        $flag = "0-" . $idVisitante;
                    } else if ($idVisitante != "") {
                        $flag = $idVisitante . "-0";
                    }
                    $data[$flag] = $value;
                }
            }
            $result['data'] = array();
            $result['data'] = $data;
        }
        return $result;
    }

    public function getVisitantes($where, $order, $param, $idEdicion = '') {

        if ($where != '') {
            $data = explode("AND ", $where);

            if (count($data) > 1) {
                $whereExplode = explode("WHERE ", $data[0]);
                $whereOK = "WHERE vis." . $whereExplode[1];
                unset($data[0]);

                foreach ($data as $key => $value) {
                    $whereOK .= 'AND vis.' . $value;
                }
            } else {
                $whereOK .= 'WHERE vis.' . $data[0];
            }
        } else {
            $whereOK = $where;
        }

        $qry = ' SELECT
	s."idVisitante",
	s."NombreCompleto",
	s."Email",
	s."DE_RazonSocial",
	s."FechaAlta_AE",
        s."VisitanteTipo"';
        $qry .= ' FROM
	(
	SELECT
		vis."idVisitante",
		vis."NombreCompleto",
		vis."Email",
		vis."DE_RazonSocial",
		vise."FechaAlta_AE",
	CASE ';
        $qry .= ' WHEN vis."RegistroMultiple" = 1 THEN
			\'Registro Multiple\' 
			WHEN vis."Prensa" = 1 THEN
			\'Prensa\' 
			WHEN vis."Asociado" = 1 
			AND vis."Comprador" = 1 THEN
				\'Asociado\' 
				WHEN vis."Comprador" = 1 THEN
				\'Comprador\' 
				WHEN vis."Asociado" = 1 THEN
				\'Asociado\' ELSE\'Visitante\' 
			END "VisitanteTipo" ';
        $qry .= ' FROM
	"AE"."Visitante" vis
	INNER JOIN "AE"."VisitanteEdicion" vise ON vise."idVisitante" = vis."idVisitante" ';
        $qry .= $whereOK;
        $qry .= ' GROUP BY
	vise."idVisitante",
	vis."idVisitante",
	vis."NombreCompleto",
	vis."Email",
	vis."DE_RazonSocial",
        vise."FechaAlta_AE"
        ORDER BY
	vis."idVisitante" ASC ';
        $qry .= ' LIMIT ' . $param['length'];
        $qry .= ' OFFSET ' . $param['start'];
        $qry .= ') s';

        $result = $this->SQLModel->executeQuery($qry);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function getVisitanteDG($idVisitante) {
        $qry = 'SELECT ';
        $qry .= 'vis."idVisitante", ';
        $qry .= 'vis."Nombre", ';
        $qry .= 'vis."ApellidoPaterno", ';
        $qry .= 'vis."ApellidoMaterno", ';
        $qry .= 'vis."NombreCompleto", ';
        $qry .= 'vis."FechaAlta", ';
        $qry .= 'vis."DE_Cargo", ';
        $qry .= 'vis."DE_RazonSocial", ';
        $qry .= 'vis."Email", ';
        $qry .= 'vis."RegistroMultiple", ';
        $qry .= 'vis."Prensa",';
        $qry .= 'vis."Asociado",';
        $qry .= 'vis."Comprador",';
        $qry .= 'vis."AreaOtro",';
        $qry .= 'vis."NombreComercialOtro",';
        $qry .= 'vis."CargoOtro"';
        $qry .= 'FROM "AE"."Visitante" vis ';
        $qry .= 'WHERE "idVisitante" = ' . $idVisitante;
        $result = $this->SQLModel->executeQuery($qry);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

}
