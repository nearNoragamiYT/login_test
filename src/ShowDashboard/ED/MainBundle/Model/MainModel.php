<?php

namespace ShowDashboard\ED\MainBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

/**
 *
 * @author Eduardo Cervantes <eduardoc@infoexpo.com.mx>
 */
class MainModel {

    protected $SQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getExpositoresDetalle($where) {
        /* ---  total de clientes para el evento --- */
        $total = $this->getClientes($where);
        $totales = Array(
            "TotalClientes" => $total
        );
        /* ---  total del contratos  --- */
        $total = $this->getExpositores($where);
        $totales["TotalContratos"] = $total;
        /* ---  total de precontratos  --- */
        $totales["TotalPrecontratos"] = $totales["TotalClientes"] - $totales["TotalContratos"];
        $total = $this->getCoexpositores($where);
        $totales["TotalED"] = $totales["TotalContratos"];
        /* ---  total del coexpositores  --- */
        $totales["TotalCoexpositores"] = $total;
        /* ---  total del expositores  --- */
        $totales["TotalExpositores"] = $totales["TotalContratos"] - $total;
        /* ---  total de expositores que entraron al manual  --- */
        $total = $this->getExpositoresEntraron($where);
        /* ---  total del expositores entraron al ED  --- */
        $totales["TotalExpositoresEntraron"] = $total;
        /* ---  total del expositores  --- */
        $totales["TotalExpositoresNoEntraron"] = $totales["TotalED"] - $total;
        return $totales;
    }

    public function getExhibitorDashboardDetalles($where) {
        /* ---  total de formas del manual  --- */
        $total = $this->getFormasManual($where);
        $totales["TotalFormasManual"] = $total;
        /* ---  total de expositores--- */
        $total = $this->getExpositores($where);
        $totales["TotalExpositores"] = $total;
        /* ---  total de formas completas  --- */
        $total = $this->getFormasCompletas($where);
        $totales["TotalFormasCompletas"] = $total;
        /* ---  total de formas sin interes  --- */
        $total = $this->getFormasSinInteres($where);
        $totales["TotalFormasSinInteres"] = $total;
        /* ---  total de formas pendientes   --- */
        $totales["TotalFormasPendientes"] = ($totales["TotalExpositores"] * $totales["TotalFormasManual"]) - ($totales["TotalFormasCompletas"] + $totales["TotalFormasSinInteres"]);
        /* ---  total de gafetes ingresados desde el manual  --- */
        $total = $this->getGafetes($where);
        $totales['TotalGafetes'] = $total;
        /* ---  total de lectoras solicitadas desde el ED  --- */
        //$total = $this->getLectoras($where);
        //$totales['TotalLectoras'] = $total;
        /* ---  total de Formas obligatorias  --- */
        $total = $this->getFormasManual($where, TRUE);
        $totales['TotalFormasObligatorias'] = $total;
        /* ---  Total de formas obligarias llenas  --- */
        $total = $this->getFormasObligatoriasCompletas($where);
        $totales["TotalFormasObligatoriasCompletas"] = $total;
        $totales["TotalFormasObligatoriasPendientes"] = ($totales["TotalFormasObligatorias"] * $totales["TotalExpositores"]) - $totales["TotalFormasObligatoriasCompletas"];
        return $totales;
    }

    public function getMarketingferial($where, $lang) {
        /* --- total de paquetes por expositores  --- */
        $total = $this->getPaquetes($where, $lang);
        $totales['TotalPaquetes'] = $total;
        /* ---  total de solicitudes de paquetes  --- */
        $total = $this->getSolicitudesPaquetes($where);
        $totales['TotalPaquetesSolicitados'] = $total;
        return $totales;
    }

    public function getFloorPlan($where) {
        /* ---  total de area por estatus de stand  --- */
        $total = $this->getEstatusStands($where);
        $totales["TotalEstatusStands"] = $total;
        /* ---  total de solicitudes de stand  --- */
        $total = $this->getSolicitudesModificacion($where);
        $totales['TotalSolicitudesModificacion'] = $total;
        /* ---  total mi recorrido
          $qry = 'SELECT DISTINCT COUNT("idVisitanteExpositor") AS "Total" FROM "MS_SL"."ms_vVisitanteExpositor"' . $this->SQLModel->buildWhere($where);
          $qry .= ' AND "Recorrido" > 0;';
          $total = $this->SQLModel->executeQuery($qry);
          if (!$total['status']) {
          throw new \Exception($total['data'], 409);
          }
          $totales['TotalAreas'] = $total['data']; --- */
        return $totales;
    }

    public function getClientes($where) {
        $qry = 'SELECT DISTINCT COUNT("idEmpresa") AS "Total" FROM "SAS"."EmpresaEdicion"' . $this->SQLModel->buildWhere($where);
        $qry .= ' AND "idEtapa" = 1 OR "idEtapa" = 2';
        $total = $this->SQLModel->executeQuery($qry);
        if (!$total['status']) {
            throw new \Exception($total['data'], 409);
        }
        return $total['data'][0]["Total"];
    }

    public function getExpositores($where) {
        $qry = 'SELECT DISTINCT COUNT("idEmpresa") AS "Total" FROM "SAS"."EmpresaEdicion"' . $this->SQLModel->buildWhere($where);
        $qry .= ' AND "idEtapa" = 2';
        $total = $this->SQLModel->executeQuery($qry);
        if (!$total['status']) {
            throw new \Exception($total['data'], 409);
        }
        return $total['data'][0]["Total"];
    }

    public function getCoexpositores($where) {
        $qry = 'SELECT DISTINCT COUNT("idEmpresa") AS "Total" FROM "SAS"."EmpresaEdicion"' . $this->SQLModel->buildWhere($where);
        $qry .= ' AND "EmpresaAdicional" = 1';
        $total = $this->SQLModel->executeQuery($qry);
        if (!$total['status']) {
            throw new \Exception($total['data'], 409);
        }
        return $total['data'][0]["Total"];
    }

    public function getExpositoresEntraron($where) {
        $qry = 'SELECT DISTINCT COUNT("idEmpresa") AS "Total" FROM "SAS"."EmpresaEdicion"' . $this->SQLModel->buildWhere($where);
        $qry .= ' AND "idEtapa" = 2 AND "PrimerAccesoED" = ' . "'t'";
        $total = $this->SQLModel->executeQuery($qry);
        if (!$total['status']) {
            throw new \Exception($total['data'], 409);
        }
        $totales = Array(
            "Total" => $total['data'][0]['Total']
        );
        return $total['data'][0]["Total"];
    }

    public function getFormasManual($where, $obligatorias = FALSE) {
        $qry = 'SELECT DISTINCT COUNT("idForma") AS "Total" FROM "SAS"."Forma"' . $this->SQLModel->buildWhere($where);
        if ($obligatorias) {
            $qry .= 'AND "ObligatorioOpcional" = 1;';
        }
        $total = $this->SQLModel->executeQuery($qry);
        if (!$total['status']) {
            throw new \Exception($total['data'], 409);
        }
        return $total['data'][0]["Total"];
    }

    public function getFormasCompletas($where) {
        $qry = 'SELECT DISTINCT COUNT("idEmpresaForma") AS "Total" FROM "SAS"."EmpresaForma"' . $this->SQLModel->buildWhere($where);
        $qry .= ' AND "StatusForma" = 1 AND "Interes" = 1';
        $total = $this->SQLModel->executeQuery($qry);
        if (!$total['status']) {
            throw new \Exception($total['data'], 409);
        }
        return $total['data'][0]["Total"];
    }

    public function getFormasSinInteres($where) {
        $qry = 'SELECT DISTINCT COUNT("idEmpresaForma") AS "Total" FROM "SAS"."EmpresaForma"' . $this->SQLModel->buildWhere($where);
        $qry .= ' AND "Interes" = 0';
        $total = $this->SQLModel->executeQuery($qry);
        if (!$total['status']) {
            throw new \Exception($total['data'], 409);
        }
        return $total['data'][0]["Total"];
    }

    public function getGafetes($where) {
        $qry = 'SELECT DISTINCT COUNT("idDetalleGafete") AS "Total" FROM "SAS"."DetalleGafete"' . $this->SQLModel->buildWhere($where);
        $total = $this->SQLModel->executeQuery($qry);
        if (!$total['status']) {
            throw new \Exception($total['data'], 409);
        }
        return $total['data'][0]["Total"];
    }

    public function getLectoras($where) {
        $qry = 'SELECT "idEmpresaForma", "DetalleServicioJSON" FROM "SAS"."EmpresaForma"' . $this->SQLModel->buildWhere($where);
        $qry .= 'AND "idForma" = 401';
        $total = $this->SQLModel->executeQuery($qry);
        if (!$total['status']) {
            throw new \Exception($total['data'], 409);
        }
        $i = 0;
        foreach ($total['data'] as $ef) {
            if ($ef['DetalleServicioJSON'] != null || $ef['DetalleServicioJSON'] != '') {
                $servicios = json_decode($ef['DetalleServicioJSON'], true);
                foreach ($servicios as $lectora) {
                    $i = $i + $lectora['Cantidad'];
                }
            }
        }
        return $i;
    }

    public function getFormasObligatoriasCompletas($where) {
        $qry = 'SELECT DISTINCT COUNT(ef."idEmpresaForma") AS "Total" FROM "SAS"."Forma" AS f ';
        $qry .= 'INNER JOIN "SAS"."EmpresaForma" AS ef ON f."idForma" = ef."idForma" ';
        $qry .= 'WHERE ef."idEvento" = ' . $where['idEvento'] . ' AND ef."idEdicion" = ' . $where['idEdicion'];
        $qry .= ' AND ef."StatusForma" = 1 AND f."ObligatorioOpcional" = 1;';
        $total = $this->SQLModel->executeQuery($qry);
        if (!$total['status']) {
            throw new \Exception($total['data'], 409);
        }
        return $total['data'][0]["Total"];
    }

    public function getPaquetes($where, $lang) {
        $qry = 'SELECT DISTINCT pq."Paquete' . strtoupper($lang) . '" AS "Paquete", COUNT("idEmpresa") AS "Total" FROM "SAS"."EmpresaEdicion" AS ee ';
        $qry .= 'INNER JOIN "SAS"."Paquete" AS pq ON ee."idPaquete" = pq."idPaquete" ';
        $qry .= 'WHERE ee."idEtapa" = 2 AND ee."idEvento" = ' . $where['idEvento'] . ' AND ee."idEdicion" = ' . $where['idEdicion'];
        $qry .= ' GROUP BY pq."Paquete' . strtoupper($lang) . '"';
        $qry .= ' ORDER BY "Total" ASC;';
        $total = $this->SQLModel->executeQuery($qry);
        if (!$total['status']) {
            throw new \Exception($total['data'], 409);
        }
        return $total['data'];
    }

    public function getSolicitudesPaquetes($where) {
        $qry = 'SELECT DISTINCT "Status", COUNT("idSolicitudPaquete") AS "Total" FROM "SAS"."SolicitudPaquete" ';
        $qry .= 'WHERE "idEdicion" = ' . $where['idEdicion'];
        $qry .= ' GROUP BY "Status"';
        $qry .= ' ORDER BY "Total" ASC;';
        $total = $this->SQLModel->executeQuery($qry);
        if (!$total['status']) {
            throw new \Exception($total['data'], 409);
        }
        return $total['data'];
    }

    public function getEstatusStands($where) {
        $qry = 'SELECT DISTINCT "StandStatus", SUM("StandArea") AS "Total", COUNT ("StandArea") AS "NumerosStands" FROM "SAS"."Stand"' . $this->SQLModel->buildWhere($where);
        $qry .= ' AND "Stand_X" > 0 AND "Stand_Y" > 0 AND "Stand_H" > 0 AND "Stand_W" > 0';
        $qry .= ' GROUP BY "StandStatus"';
        $qry .= ' ORDER BY "Total" ASC;';
        $total = $this->SQLModel->executeQuery($qry);
        if (!$total['status']) {
            throw new \Exception($total['data'], 409);
        }
        return $total['data'];
    }

    public function getSolicitudesModificacion($where) {
        $where['idForma'] = 220;
        $qry = 'SELECT "DetalleForma" FROM "SAS"."EmpresaForma"' . $this->SQLModel->buildWhere($where);
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($total['data'], 409);
        }
        $totalAceptadas = 0;
        $totalRechazadas = 0;
        $totalPendientes = 0;
        foreach ($result['data'] as $value) {
            if (!is_null($value['DetalleForma'])) {
                $detalleForma = json_decode($value['DetalleForma'], 1);
                foreach ($detalleForma as $value) {
                    switch ((int) $value['StatusSolicitudCambio']) {
                        case 0:
                            $totalRechazadas++;
                            break;
                        case 1:
                            $totalAceptadas++;
                            break;
                        case 2:
                            $totalPendientes++;
                            break;
                    }
                }
            }
        }
        $total = Array(
            "aceptadas" => $totalAceptadas,
            "rechazadas" => $totalRechazadas,
            "pendientes" => $totalPendientes
        );
        return $total;
    }

}
