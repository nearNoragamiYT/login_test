<?php

namespace ShowDashboard\FT\FacturacionBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\SQLBundle\Model\SQLModelFactura;

class FacturacionModel
{

    protected $SQLModel, $SQLModelFactura;

    public function __construct()
    {
        $this->SQLModel = new SQLModel();
        $this->SQLModelFactura = new SQLModelFactura();
    }

    public function getfacturasTimbradas($params)
    {
        $query = 'SELECT';
        $query .= ' com."idCompra",';
        $query .= ' vis."NombreCompleto",';
        $query .= 'vis."Email", ';
        $query .= ' ft."idFactura",';
        $query .= ' cs."StatusES",';
        $query .= ' com."CompraFacturada",';
        $query .= ' com."TicketFacturacion",';
        $query .= ' DATE(com."FechaPagado") as  "FechaPago",';
        $query .= ' CASE';
        $query .= ' WHEN com."CompraFacturada" = TRUE THEN ' . "'Facturada" . "'";
        $query .= ' ELSE ' . "'PorFacturar" . "'";
        $query .= ' END  as "Estatus Factura",';
        $query .= ' ft."FechaTimbrado",';
        $query .= ' ft."idFacturaStatus",';
        $query .= ' ft."idEdicion"';
        $query .= ' FROM';
        $query .= ' "AE"."Compra" com';
        $query .= ' INNER JOIN "AE"."Factura" ft ON com."idCompra" = ft."idCompra"';
        $query .= ' INNER JOIN "AE"."CompraStatus" cs ON cs."idCompraStatus" = com."idCompraStatus"';
        $query .= ' INNER JOIN "AE"."Visitante" vis ON vis."idVisitante" = com."idVisitante"';
        $query .= ' WHERE';
        $query .= ' com."CompraFacturada" = TRUE';
        $query .= ' AND ft."idEdicion" =' . $params['idEdicion'];
        $query .= ' AND ft."idEvento" =' . $params['idEvento'];
        $query .= 'ORDER BY';
        $query .= ' vis."NombreCompleto"';
        return $this->SQLModel->executeQuery($query);
    }

    public function getfacturasPendientes($params)
    {
        $query = 'SELECT';
        $query .= ' com."idCompra",';
        $query .= ' vis."NombreCompleto",';
        $query .= ' ft."idFactura",';
        $query .= ' cs."StatusES",';
        $query .= ' com."CompraFacturada",';
        $query .= ' com."TicketFacturacion",';
        $query .= ' DATE(com."FechaPagado") as  "FechaPago",';
        $query .= ' CASE';
        $query .= ' WHEN com."CompraFacturada" = TRUE THEN ' . "'Facturada" . "'";
        $query .= ' ELSE ' . "'Por Facturar" . "'";
        $query .= ' END  as "Estatus Factura",';
        $query .= ' ft."FechaTimbrado",';
        $query .= ' ft."idFacturaStatus",';
        $query .= ' ft."idEdicion"';
        $query .= ' FROM';
        $query .= ' "AE"."Compra" com';
        $query .= ' INNER JOIN "AE"."Factura" ft ON com."idCompra" = ft."idCompra"';
        $query .= ' INNER JOIN "AE"."CompraStatus" cs ON cs."idCompraStatus" = com."idCompraStatus"';
        $query .= ' INNER JOIN "AE"."Visitante" vis ON vis."idVisitante" = com."idVisitante"';
        $query .= ' WHERE';
        $query .= ' com."CompraFacturada" = FALSE';
        $query .= ' AND ft."idEdicion" =' . $params['idEdicion'];
        $query .= ' AND ft."idEvento" =' . $params['idEvento'];
        $query .= 'ORDER BY';
        $query .= ' vis."NombreCompleto"';
        return $this->SQLModel->executeQuery($query);
    }
    // cancelacion 
    public function getfacturasCanceladas($params)
    {
        $query = 'SELECT';
        $query .= ' com."idCompra",';
        $query .= ' vis."NombreCompleto",';
        $query .= ' ft."idFactura",';
        $query .= ' cs."StatusES",';
        $query .= ' com."CompraFacturada",';
        $query .= ' com."TicketFacturacion",';
        $query .= ' DATE(com."FechaPagado") as  "FechaPago",';
        $query .= ' CASE';
        $query .= ' WHEN com."CompraFacturada" = TRUE THEN ' . "'Facturada" . "'";
        $query .= ' ELSE ' . "'Por Facturar" . "'";
        $query .= ' END  as "Estatus Factura",';
        $query .= ' ft."FechaTimbrado",';
        $query .= ' ft."idFacturaStatus",';
        $query .= ' ft."idEdicion"';
        $query .= ' FROM';
        $query .= ' "AE"."Compra" com';
        $query .= ' INNER JOIN "AE"."Factura" ft ON com."idCompra" = ft."idCompra"';
        $query .= ' INNER JOIN "AE"."CompraStatus" cs ON cs."idCompraStatus" = com."idCompraStatus"';
        $query .= ' INNER JOIN "AE"."Visitante" vis ON vis."idVisitante" = com."idVisitante"';
        $query .= ' WHERE';
        $query .= ' com."CompraFacturada" = FALSE';
        $query .= ' AND ft."idEdicion" =' . $params['idEdicion'];
        $query .= ' AND ft."idEvento" =' . $params['idEvento'];
        $query .= 'ORDER BY';
        $query .= ' vis."NombreCompleto"';
        return $this->SQLModel->executeQuery($query);
    }

    public function getFacturaID($idFactura, $idEdicion)
    {
        $query = 'SELECT ';
        $query .= 'ft."idFactura", ';
        $query .= 'ft."idCompra",';
        $query .= 'ft."Folio", ';
        $query .= 'ft."UUID", ';
        $query .= 'com."FolioSustitucion", ';
        $query .= 'com."CompraFacturada",';
        $query .= 'com."RFC",';
        $query .= 'vis."Nombre", ';
        $query .= 'vis."ApellidoPaterno", ';
        $query .= 'vis."ApellidoMaterno", ';
        $query .= 'vis."Email", ';
        $query .= 'vis."DE_Telefono", ';
        $query .= 'vis."DE_Pais",';
        $query .= 'vis."DE_Estado", ';
        $query .= 'vis."DE_Ciudad", ';
        $query .= 'vis."DE_RazonSocial", ';
        $query .= 'com."TicketFacturacion", ';
        $query .= 'DATE(com."FechaPagado") AS "FechaPagado" ';
        $query .= 'FROM "AE"."Compra" com ';
        $query .= 'INNER JOIN "AE"."Visitante" vis ON com."idVisitante" = vis."idVisitante"';
        $query .= 'INNER JOIN "AE"."Factura" ft ON ft."idCompra" = com."idCompra" AND ft."idEdicion" = com."idEdicion"';
        // $query .= 'INNER JOIN "AE"."MotivoCancelacion" MC ON MC."idMotivoCancelacion" = ft."idMotivoCancelacion"';
        $query .= 'WHERE ft."idFactura" = ' . $idFactura . ' AND com."idEdicion" =' . $idEdicion;
        return $this->SQLModel->executeQuery($query);
    }
    public function getFacturaInfo($params, $idEdicion)
    {
        $query = ' SELECT ';
        $query .= 'ft."idFactura", ';
        $query .= 'ft."idCompra",';
        $query .= 'ft."Folio", ';
        $query .= 'ft."UUID", ';
        $query .= 'vis."Nombre", ';
        $query .= 'vis."ApellidoPaterno", ';
        $query .= 'vis."ApellidoMaterno", ';
        $query .= 'vis."Email", ';
        $query .= 'vis."DE_Telefono", ';
        $query .= 'vis."DE_Pais",';
        $query .= 'vis."DE_Estado", ';
        $query .= 'vis."DE_Ciudad", ';
        $query .= 'vis."DE_RazonSocial", ';
        $query .= 'com."TicketFacturacion", ';
        $query .= 'DATE(com."FechaPasado") AS "FechaPagado" ';
        $query .= 'FROM "AE"."Compra" com ';
        $query .= 'INNER JOIN "AE"."Visitante" vis ON com."idVisitante" = vis."idVisitante"';
        $query .= 'INNER JOIN "AE"."Factura" ft ON ft."idCompra" = com."idCompra" AND ft."idEdicion" = com."idEdicion"';
        $query .= 'WHERE ft."idFactura" = ' . $params . ' AND com."idEdicion" =' . $idEdicion;
        return $this->SQLModel->executeQuery($query);
    }
    public function getEdicion($idEvento, $idEdicion)
    {
        $query = 'SELECT * FROM "SAS"."Edicion"';
        $query .= '  WHERE "idEvento" = ' . $idEvento;
        $query .= '  AND "idEdicion" = ' . $idEdicion;
        return $this->SQLModel->executeQuery($query);
    }
    public function getMotivoCancelacion()
    {
        $query = ' SELECT * from';
        $query .= ' "AE"."MotivoCancelacion"';
        return $this->SQLModel->executeQuery($query);
    }
    public function getCompraDatos()
    {
        $query = ' SELECT * from';
        $query .= ' "AE"."Compra"';
        // $query .= '  WHERE "idCompra" = ' . $idCompra;
        return $this->SQLModel->executeQuery($query);
    }
    public function getConfiguracionPortal($idConfifuracion)
    {
        $qry = ' SELECT ';
        $qry .= 'confi."idConfiguracion", ';
        $qry .= 'confi."ColorPortal",';
        $qry .= 'confi."LogoTipo",';
        $qry .= 'confi."EventoUrl", ';
        $qry .= 'confi."colorHeader", ';


        $qry .= 'TS."RFC", ';
        $qry .= 'TS."Email", ';
        $qry .= 'TS."Password", ';
        $qry .= 'TS."URL", ';
        $qry .= 'TS."Nombre", ';
        $qry .= 'TS."RegimenFiscal", ';
        $qry .= 'TS."URLV4", ';
        $qry .= 'TS."idTipoUsuario" ';


        $qry .= ' FROM "SAS"."Configuracion" confi';
        $qry .= ' INNER JOIN "SAS"."TipoUsuario" TS ';
        $qry .= ' ON confi."idTipoUsuario" = TS."idTipoUsuario"';


        $qry .= ' WHERE "idConfiguracion" = ' . $idConfifuracion;
        $result = $this->SQLModelFactura->executeQuery($qry);
        return $result;
    }

    public function getInsertCancelacionMotivo($idFctura,$idMoTivoC,$motivo,$idEdicion,$idEvento,$respuestaSATCancelacios,$fechaCancelada)
    {
        $qry = 'UPDATE "AE"."Factura" SET';
        $qry .= ' "MotivoCancelacion" = ' . $motivo;
        $qry .= ', "idMotivoCancelacion" = ' . $idMoTivoC;
        $qry .= ', "RespuestaSATCancelacion" = ' . "'".$respuestaSATCancelacios."'";
        $qry .= ', "FechaCancelado" = ' . "'". $fechaCancelada. "'";
        $qry .= '  WHERE "idFactura" = ' . $idFctura;
        $qry .= ' AND "idEdicion" =' . $idEdicion;
        $qry .= ' AND "idEvento" =' . $idEvento;
        return $this->SQLModel->executeQuery($qry);
    }
}
