<?php

namespace Visitante\ComprasBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Visitante\VisitanteBundle\Model\MainModel;

class ComprasModel extends MainModel {

    public function __construct(ContainerInterface $container = null) {
        parent::__construct($container);
    }

    public function getVisitanteCustom($columns = Array(), $params = Array(), $order = Array(), $limit = -1, $offset = -1) {
        $columns_str = '';
        $group_by = ' ';
        if (is_array($columns) && COUNT($columns) > 0) {
            $group_by .= 'GROUP BY';
            foreach ($columns as $column) {
                $columns_str .= ' ' . $column . ',';

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

        $qry = ' SELECT ' . $columns_str;
        $qry .= ' FROM "AE"."Compra" comp INNER JOIN "AE"."VisitanteEdicion" vise ON comp."idVisitante" = vise."idVisitante"'
                . ' AND comp."idEvento" = vise."idEvento" AND comp."idEdicion" = vise."idEdicion"'
                . ' INNER JOIN "AE"."Visitante" vis ON vise."idVisitante" = vis."idVisitante"';
        $qry .= ' {where}';
        $qry .= $group_by;
        $qry .= $order_by;
        $q = $qry;
        $q .= ' LIMIT 0';
        $q .= ' OFFSET 100';
        if ($limit != -1 && is_numeric($limit)) {
            $qry .= ' LIMIT ' . $limit;
        }
        if ($offset != -1 && is_numeric($offset)) {
            $qry .= ' OFFSET ' . $offset;
        }

        foreach ($params['where'] as $key => $value) {
            if (in_array('comp."FechaCreacion"', $value))
                $params['where'][$key]['timestamp'] = TRUE;
        }

        $result_query = $this->SQLModel->executeQueryWhitWhere($qry, $params);

        if ($result_query["status"]) {
            return Array("status" => TRUE, "data" => $result_query['data'], "data_qry" => array("qry" => $q, "params" => $params));
        } else {
            return Array("status" => FALSE, "error" => $result_query["error"]["string"]);
        }
    }

    public function getCountVisitante($columns = Array(), $params = Array(), $query = "") {
        if ($query != "") {
            $Result = $this->SQLModel->executeQueryWhitWhere($query, $params);
            $Result['count']['qry'] = $query;
            $Result['count']['params'] = $params;
        } else {
            if (is_array($columns) && COUNT($columns) > 0) {
                $group_by .= 'GROUP BY';
                foreach ($columns as $column) {
                    $columns_str .= ' ' . $column . ',';

                    $column_name = $column;
                    if (strpos(strtoupper($column), " AS ")) {
                        $column_name = substr($column, 0, strpos(strtoupper($column), " AS "));
                    }
                    $group_by .= ' ' . $column_name . ',';
                }
                $columns_str = substr($columns_str, 0, -1);
                $group_by = substr($group_by, 0, -1);
            }

            $qry = ' select COUNT(*) as "total"';
            $qry .= ' FROM (';
            $qry .= '     SELECT ' . $columns_str;
            $qry .= '     FROM "AE"."Compra" comp INNER JOIN "AE"."VisitanteEdicion" vise ON comp."idVisitante" = vise."idVisitante"'
                    . ' AND comp."idEvento" = vise."idEvento" AND comp."idEdicion" = vise."idEdicion"'
                    . ' INNER JOIN "AE"."Visitante" vis ON vise."idVisitante" = vis."idVisitante"';
            $qry .= '     {where}';
            $qry .= '     ' . $group_by;
            $qry .= ') sq';

            foreach ($params['where'] as $key => $value) {
                if (in_array('comp."FechaCreacion"', $value))
                    $params['where'][$key]['timestamp'] = TRUE;
            }

            $Result = $this->SQLModel->executeQueryWhitWhere($qry, $params);
            $Result['count']['qry'] = $qry;
            $Result['count']['params'] = $params;
        }
        return $Result;
    }

    public function getComprasVisitante($args) {
        $qry = 'SELECT';
        $qry .= ' c."idCompra",';
        $qry .= ' c."idCompraStatus",';
        $qry .= ' cs."StatusES",';
        $qry .= ' cs."StatusEN",';
        $qry .= ' c."idFormaPago",';
        $qry .= ' fp."FormaPagoES",';
        $qry .= ' fp."FormaPagoEN",';
        $qry .= ' c."SubTotal",';
        $qry .= ' c."IVA",';
        $qry .= ' c."Total",';
        $qry .= ' c."Descuento",';
        $qry .= ' cup."Cupon",';
        $qry .= ' cup."DescripcionES" as "DescuentoDescripcionES",';
        $qry .= ' cup."DescripcionEN" as "DescuentoDescripcionEN",';
        $qry .= ' c."idCuponDescuento",';
        $qry .= ' c."ReqFactura",';
        $qry .= ' c."Facturada",';
        $qry .= ' c."RFC",';
        $qry .= ' c."RazonSocial",';
        $qry .= ' c."EmailFacturacion",';
        $qry .= ' c."Pais",';
        $qry .= ' c."CodigoPostal",';
        $qry .= ' c."Estado",';
        $qry .= ' c."Ciudad",';
        $qry .= ' c."Colonia",';
        $qry .= ' c."Calle",';
        $qry .= ' c."NumeroExterior",';
        $qry .= ' c."NumeroInterior",';
        $qry .= ' c."FechaCreacion",';
        $qry .= ' c."FechaPagado",';
        $qry .= ' c."FechaCancelado"';
        $qry .= ' FROM "AE"."Compra" c';
        $qry .= ' LEFT JOIN "AE"."CompraStatus" cs';
        $qry .= ' ON c."idCompraStatus" = cs."idCompraStatus"';
        $qry .= ' LEFT JOIN "AE"."FormaPago" fp';
        $qry .= ' ON c."idFormaPago" = fp."idFormaPago"';
        $qry .= ' LEFT JOIN "AE"."CuponDescuento" cd';
        $qry .= ' ON c."idCuponDescuento" = cd."idCuponDescuento"';
        $qry .= ' LEFT JOIN "AE"."Cupon" cup';
        $qry .= ' ON cup."idCupon" = cd."idCupon"';
        $qry .= $this->SQLModel->buildWhere($args);
        $qry .= ' ORDER BY c."idCompraStatus",';
        $qry .= ' c."FechaCreacion"';
        $result = $this->SQLModel->executeQuery($qry);
        
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }

        foreach ($result['data'] as $key => $compra) {
            $args = array('idCompra' => $compra['idCompra']);
            $result_compraDetalle = $this->getCompraDetalle($args);
            if (!$result_compraDetalle['status']) {
                return $result_compraDetalle;
            }
            $compraDetalle = $result_compraDetalle['data'];
            $result['data'][$key]['CompraDetalle'] = $compraDetalle;
        }

        return $result;
    }

    private function getCompraDetalle($args) {
        $qry = 'SELECT';
        $qry .= ' "idCompraDetalle",';
        $qry .= ' "idCuponProducto",';
        $qry .= ' "Descuento",';
        $qry .= ' "Cantidad",';
        $qry .= ' "Precio",';
        $qry .= ' "PrecioUnitario",';
        $qry .= ' "ProductoES",';
        $qry .= ' "ProductoEN",';
        $qry .= ' "ProductoDescripcionES",';
        $qry .= ' "ProductoDescripcionEN",';
        $qry .= ' "idProducto"';
        $qry .= ' FROM "AE"."CompraDetalle"';
        $qry .= $this->SQLModel->buildWhere($args);
        $qry .= ' ORDER BY "idCompraDetalle"';
        return $this->SQLModel->executeQuery($qry);
    }

    private function getCompraPaqueteProducto($args) {
        $qry = 'SELECT';
        $qry .= ' cpp."idCompraDetalle",';
        $qry .= ' cpp."idProducto",';
        $qry .= ' cpp."ProductoES",';
        $qry .= ' cpp."ProductoEN",';
        $qry .= ' cpp."ProductoDescripcionES",';
        $qry .= ' cpp."ProductoDescripcionEN",';
        $qry .= ' cpp."Descuento",';
        $qry .= ' cpp."Cantidad",';
        $qry .= ' cpp."Precio",';
        $qry .= ' cpp."PrecioUnitario"';
        $qry .= ' FROM "AE"."CompraPaqueteProducto" cpp';
        $qry .= ' JOIN "AE"."CompraDetalle" cd';
        $qry .= ' ON cpp."idCompraDetalle" = cd."idCompraDetalle"';
        $qry .= $this->SQLModel->buildWhere($args);
        $qry .= ' ORDER BY cpp."idCompraDetalle",';
        $qry .= ' cpp."idCompraPaqueteProducto"';
        $result = $this->SQLModel->executeQuery($qry);

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            if (!isset($data[$value['idCompraDetalle']])) {
                $data[$value['idCompraDetalle']] = array();
            }
            $data[$value['idCompraDetalle']][] = $value;
        }
        $result['data'] = $data;
        return $result;
    }

    public function getProgramas() {
        $qry = 'SELECT ';
        $qry .= ' "idPrograma",';
        $qry .= ' "ProgramaES",';
        $qry .= ' "ProgramaEN"';
        $qry .= ' FROM "AE"."Programa"';
        $qry .= $this->SQLModel->buildWhere($args);
        $result = $this->SQLModel->executeQuery($qry);

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }

        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idPrograma']] = $value;
        }

        $result['data'] = $data;
        return $result;
    }

    public function getVisitanteTipoSocio() {
        $cache_name = "visitante_tipo_socio";
        $ruta = $this->base_path_cache . $cache_name . '.json';
        if (file_exists($ruta)) {
            $result_cache = file_get_contents($ruta);
            $result = Array('status' => TRUE, 'data' => json_decode($result_cache, TRUE));
        }

        $qry = 'SELECT';
        $qry .= ' "idVisitanteTipoSocio",';
        $qry .= ' "VisitanteTipoSocioES",';
        $qry .= ' "VisitanteTipoSocioEN"';
        $qry .= ' FROM "AE"."VisitanteTipoSocio"';
        $qry .= ' ORDER BY "idVisitanteTipoSocio"';
        $result = $this->SQLModel->executeQuery($qry);

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idVisitanteTipoSocio']] = $value;
        }
        $this->writeJSON($ruta, $data);
        clearstatcache();
        $result['data'] = $data;
        return $result;
    }

    public function updateCompra($idCompra, $idStatus) {
        if ($idStatus == 3) {
            $args = array('idCompra' => $idCompra);
            $values = array(
                'idCompraStatus' => 3,
                'Cancelada' => "'1'",
                'FechaCancelado' => "now()"
            );
        }
        if ($idStatus == 2) {
            $args = array('idCompra' => $idCompra);
            $values = array(
                'idCompraStatus' => 2,
                'Pagada' => "'1'",
                'FechaPagado' => "now()"
            );
        }
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->updateFromTable("Compra", $values, $args, 'idCompra');
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function getComprasReport($params) {
        $fields = Array(
            'idCompra',
            'idCompraStatus',
            'Total',
            'FechaCreacion',
            'FechaPagado',
            'FechaCancelado',
            'ProductoES',
            'ProductoDescripcionES',
            'Cantidad',
            'PrecioUnitario',
            'Precio',
            'Descuento',
            'Cupon',
            'DescripcionES',
            'ReqFactura',
            'CompraFacturada',
            'idVisitante',
            'NombreCompleto',
            'Email',
            'Telefono'
        );

        $this->SQLModel->setSchema("AE");
        $result = $result = $this->SQLModel->selectFromTable("vw_ae_ReporteDetalleCompras", $fields, $params, array('"idCompra"' => 'ASC'));
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function getFormasPago($args) {
        $qry = 'SELECT "idFormaPago", "FormaPagoES","FormaPagoEN","DescripcionES","DescripcionEN" ';
        $qry .= 'FROM "AE"."FormaPago" as "FP" ';
        $qry .= ' ORDER BY "idFormaPago" ASC;';
        $result = $this->SQLModel->executeQuery($qry);

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idFormaPago']] = $value;
        }
        $result['data'] = $data;
        return $result['data'];
    }

    public function updateCompraFormaPago($idCompra, $idFormaPago) {
        if ($idFormaPago) {
            $args = array('idCompra' => $idCompra);
            $values = array(
                'idFormaPago' => $idFormaPago,
            );
        }
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->updateFromTable("Compra", $values, $args, 'idCompra');
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

    public function updateCompraFacturada($idCompra, $CompraFacturada, $FolioFactura) {
        $args = array('idCompra' => $idCompra);
        $values = array(
            'CompraFacturada' => "'" . $CompraFacturada . "'",
            'FolioFactura' => "'" . $FolioFactura . "'"
        );
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->updateFromTable("Compra", $values, $args, 'idCompra');
        $this->SQLModel->setSchema("SAS");
        return $result;
    }
    
    public function getTipoCambio($args) {
        $qry = 'SELECT';
        $qry .= ' tc."idTasaCambio",';
        $qry .= ' tc."idUsuario",';
        $qry .= ' tc."TasaCambioUSD" ';
        $qry .= ' FROM "AE"."TipoTasaCambio" tc ';
        $qry .= $this->SQLModel->buildWhere($args);
        $qry .= ' AND tc."idTasaCambio" = ( ';
        $qry .= ' SELECT  ';
        $qry .= ' MAX( "idTasaCambio" )   ';
        $qry .= 'FROM "AE"."TipoTasaCambio" )  ';
        $result = $this->SQLModel->executeQuery($qry);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }
    
    public function insertTasaCambio($values) {
        $this->SQLModel->setSchema("AE");
        $result = $this->SQLModel->insertIntoTable("TipoTasaCambio",$values,"idTasaCambio");
        $this->SQLModel->setSchema("SAS");
        return $result;
    }

}
