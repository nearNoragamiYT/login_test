<?php

namespace ShowDashboard\RS\ReporteComprasBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

class ReporteComprasModel {

    protected $SQLModel, $schema = "AE";

    public function __construct() {//se crea la conexion
        $this->SQLModel = new SQLModel();
        $this->SQLModel->setSchema($this->schema);
    }

    public function getFiltro() {
        $qry = ' SELECT ';
        $qry .= ' nodo."idNodo",';
        $qry .= ' nodo."NombreNodo", ';
        $qry .= ' conf."Tienda" ';
        $qry .= ' FROM ';
        $qry .= ' "AE"."Nodo" nodo ';
        $qry .= ' JOIN "AE"."ConfiguracionNodo" conf ON nodo."idNodo" = conf."idNodo" ';
        $qry .= ' WHERE conf."Tienda" = true ';
        $qry .= ' ORDER BY ';
        $qry .= ' nodo."idNodo"';

        $result = $this->SQLModel->executeQuery($qry);

        if (($result['status'] && count($result['data']) > 0)) {
            foreach ($result['data'] as $value) {
                $data[$value['idNodo']] = $value;
            }
            $result['data'] = $data;
        }

        return $result;
    }

    public function getReporteCaja($date_start, $date_end) {
        $qry = ' SELECT' . $this->getCajasFields();
        $qry .= ' FROM "AE"."vw_rs_ReporteCompra" ';

        if ($date_start != "" && $date_end != "") {
            $qry .= ' WHERE ';
            $qry .= ' "FechaPagado" BETWEEN  ' . "'" . $date_start . "'" . ' AND ' . "'" . $date_end . "' ";
        }
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            return $result;
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getReporte($date_start, $date_end, $idTarjeta) {
        $qry = ' SELECT' . $this->getCajasFields1();
        $qry .= ' FROM "AE"."vw_rs_ReporteCompra" ';

        if ($date_start != "" && $date_end != "" && $idTarjeta != "") {
            $qry .= ' WHERE ';
            $qry .= ' "FechaPagado" BETWEEN  ' . "'" . $date_start . "'" . ' AND ' . "'" . $date_end . "' " . ' AND  "idFormaPago" = ' . "" . $idTarjeta . "";
        }
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            return $result;
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getReporteNodo($date_start, $date_end) {
        $qry = ' SELECT' . $this->getCajasFields2();
        $qry .= ' FROM "AE"."vw_rs_ReporteCompra" ';

        if ($date_start != "" && $date_end != "") {
            $qry .= ' WHERE ';
            $qry .= ' "FechaPagado" BETWEEN  ' . "'" . $date_start . "'" . ' AND ' . "'" . $date_end . "' " . 'AND "idNodo" IS NOT NULL';
        }

        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            return $result;
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getupdateGeneral($args) {
        $qry = ' SELECT' . $this->getCajasFields2();
        $qry .= ' FROM "AE"."vw_rs_ReporteCompra" ';

        if ($args['fechaInicial'] != "" && $args['fechaFinal'] != "" && $args['idNodo'] != "") {
            $qry .= ' WHERE ';
            $qry .= ' "FechaPagado" BETWEEN  ' . "'" . $args['fechaInicial'] . "'" . ' AND ' . "'" . $args['fechaFinal'] . "' " . 'AND "idNodo" = ' . "" . $args['idNodo'] . "";
        }
        $result = $this->SQLModel->executeQuery($qry);

        if (isset($result['status']) && $result['status'] == 1) {
            return $result;
        } else
            return Array("status" => FALSE, "data" => $result['status']);
    }

    public function getCajasFields() {
        $fields = '';
        $fields .= ' "idCompra",';
        $fields .= ' "FechaPagado",';
        $fields .= ' "NombreCompleto",';
        $fields .= ' "Email",';
        $fields .= ' "ProductoES",';
        $fields .= ' "Total"';

        return $fields;
    }

    public function getCajasFields1() {
        $fields = '';
        $fields .= ' "idCompra",';
        $fields .= ' "idFormaPago",';
        $fields .= ' "FechaPagado",';
        $fields .= ' "NombreCompleto",';
        $fields .= ' "Email",';
        $fields .= ' "ProductoES",';
        $fields .= ' "Total"';

        return $fields;
    }

    public function getCajasFields2() {
        $fields = '';
        $fields .= ' "idCompra",';
        $fields .= ' "idNodo",';
        $fields .= ' "FechaPagado",';
        $fields .= ' "NombreCompleto",';
        $fields .= ' "Email",';
        $fields .= ' "ProductoES",';
        $fields .= ' "Total"';

        return $fields;
    }

    public function getCompraStatus() {
        $qry = ' SELECT ';
        $qry .= 'COUNT(compra."idCompraStatus"), ';
        $qry .= 'status."StatusES" ';
        $qry .= ' FROM "AE"."CompraRS" compra ';
        $qry .= ' JOIN "AE"."CompraStatus" status';
        $qry .= ' ON compra."idCompraStatus" = status."idCompraStatus"';
        $qry .= ' WHERE compra."CompraRS"= 1';
        $qry .= ' GROUP BY (status."StatusES")';

        $result = $this->SQLModel->executeQuery($qry);

        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }

        return $result;
    }

    public function getimpresionTicket($id) {

        $qry = ' SELECT ';
        $qry .= ' comp."idCompra", ';
        $qry .= ' comp."idEvento", ';
        $qry .= ' comp."idEdicion", ';
        $qry .= ' comp."idFormaPago", ';
        $qry .= ' comp."idMonedaTipo", ';
        $qry .= ' comp."TicketFacturacion", ';
        $qry .= ' comp."SubTotal",';
        $qry .= ' comp."IVA", ';
        $qry .= ' comp."Total", ';
        $qry .= ' comp."MonedaTipo", ';
        $qry .= ' comp."FechaCreacion", ';
        $qry .= ' compDe."Cantidad", ';
        $qry .= ' compDe."Precio", ';
        $qry .= ' compDe."idProducto", ';
        $qry .= ' compDe."ProductoES", ';
        $qry .= ' compDe."ProductoEN" ';
        $qry .= ' FROM ';
        $qry .= ' "AE"."CompraRS" comp ';
        $qry .= ' JOIN "AE"."CompraDetalleRS" compDe ON comp."idCompra" = compDe."idCompra" ';
        $qry .= ' WHERE ';
        $qry .= ' comp."idCompra" = ' . $id;

        $result = $this->SQLModel->executeQuery($qry);

        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }

        return $result;
    }

}
