<?php

namespace ShowDashboard\RS\EstadisticasRSBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

class EstadisticasModel {

    protected $SQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->SQLModel->setSchema("AE");
    }

    public function getAsistenciaDia($idEdicion) {
        $qry .= ' select * from "AE"."fn_rs_AsistenciaPorDia" WHERE "idEdicion" = ' . $idEdicion;
        $qry .= ' ORDER BY "Fecha"';

        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getComparacionAsistencia($idEdicion) {
        $qry .= ' select * from "AE"."vw_rs_AsistenciaEdicion" WHERE "Edicion" = ' . $idEdicion;

        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getAsistenciaHora($idEdicion) {
        $qry .= ' select * from "AE"."vw_rs_VisitanteHora" WHERE "_idedicion" = ' . $idEdicion . ' AND "HoraInicial" > \'06:59:59\' AND "HoraFinal" < \'20:59:59\'';
        $qry .= ' ORDER BY "HoraInicial", "Dia"';

        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    /* public function getClubElite($idEdicion) {
      $qry .= ' select * from "AE"."fn_rs_AsistenciaPorDiaClubElite" WHERE "idEdicion" = ' . $idEdicion;
      $qry .= ' ORDER BY "Fecha" ';

      $result = $this->SQLModel->executeQuery($qry);
      return $result;
      } */
}
