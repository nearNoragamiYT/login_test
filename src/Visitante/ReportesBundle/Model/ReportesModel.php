<?php

namespace Visitante\ReportesBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class ReportesModel extends DashboardModel
{

    protected $SQLModel, $PGSQLModel;

    public function __construct()
    {
        $this->SQLModel = new SQLModel();
        $this->PGSQLModel = new PGSQLClient();
    }
    public function getReportes($where)
    {
        $qry = ' SELECT * ';
        $qry .= ' FROM "SAS"."ReportesPermisos" ';
        if ($where["idUsuario"] != "") {
            $qry .= ' WHERE "idPlataformaIxpo" = ' . $where["idPlataformaIxpo"];
            $qry .= ' AND "idUsuario" = ' . $where["idUsuario"];
            $qry .= ' AND "idEvento" = ' . $where["idEvento"];
            $qry .= ' AND "idEdicion" = ' . $where["idEdicion"];
            $qry .= ' AND  "Ver" = ' . $where["Ver"];
        }
        $qry .= ' ORDER BY "Orden" ASC';
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }



    public function getDetalleCompras($args)
    {
        $qry = ' SELECT "idCompra",';
        $qry .= ' "StatusES",';
        $qry .= ' "Total",';
        $qry .= ' "ProductoES",';
        $qry .= ' "FormaPagoES",';
        $qry .= ' "RequiereFactura",';
        $qry .= ' "TicketFacturacion",';
        $qry .= ' "FechaPagado",';
        $qry .= ' "CompraFacturada",';
        $qry .= ' "Folio",';
        $qry .= ' "Serie",';
        $qry .= ' "FechaTimbrado",';
        $qry .= ' "UUID",';
        $qry .= ' "FechaFactura",';
        $qry .= ' "RegimenFiscalES",';
        $qry .= ' "RazonSocialReceptor",';
        $qry .= ' "RFCReceptor",';
        $qry .= ' "DomicilioFiscalReceptor",';
        $qry .= ' "idVisitante",';
        $qry .= ' "NombreCompleto",';
        $qry .= ' "Email",';
        $qry .= ' "DE_AreaPais",';
        $qry .= ' "DE_AreaCiudad",';
        $qry .= ' "DE_Telefono",';
        $qry .= ' "DE_RazonSocial",';
        $qry .= ' "DE_CP",';
        $qry .= ' "DE_Pais",';
        $qry .= ' "DE_Estado"';
        if (isset($args["idProducto"])) {
            if (is_array($args["idProducto"])) {
                //Es un array, selecciono el 13
                $qry .= ', "Cantidad" AS "Citas Extras"';
            }
        }
        $qry .= ' FROM "AE"."vw_ae_VisitanteCompraDetalle" ';
        $qry .= ' WHERE "idEvento" = ' . $args["idEvento"];
        $qry .= ' AND "idEdicion" = ' . $args["idEdicion"];
        if (isset($args["idProducto"])) {
            if (is_array($args["idProducto"])) {
                $count = 0;
                $total = count($args["idProducto"]);
                $qry .= ' AND "idProducto" IN (';
                foreach ($args["idProducto"] as $value) {
                    if (++$count === $total) {
                        $qry .= $value . ') ';
                    } else {
                        $qry .= $value . ', ';
                    }
                }
            } else {
                $qry .= ' AND "idProducto" = ' . $args["idProducto"];
            }
        }
        if (isset($args["StatusES"])) {
            $qry .= ' AND "StatusES" = ' . $args["StatusES"];
        }
        if (isset($args["Total"])) {
            $qry .= ' AND "Total" > ' . $args["Total"];
        }
        //$qry .= $this->SQLModel->buildWhere($args);

        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getDetalleTransacciones($args)
    {
        $qry = ' SELECT "idVisitante",';
        $qry .= ' "idCompra",';
        $qry .= ' "NombreCompleto",';
        $qry .= ' "Email",';
        $qry .= ' "StatusES",';
        $qry .= ' "CodigoAutorizacion",';
        $qry .= ' "TipoTarjeta",';
        $qry .= ' "UltimosDigitosTarjeta",';
        $qry .= ' "CodigoError",';
        $qry .= ' "MensajeError",';
        $qry .= ' "FechaPagado",';
        $qry .= ' "FechaCancelado"';
        $qry .= ' FROM "AE"."ae_vw_VisitanteCompraTransaccion" ';
        $qry .= $this->SQLModel->buildWhere($args);
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getAsociadosProducto($args)
    {
        $qry = ' SELECT DISTINCT "idVisitante",';
        $qry .= ' "idVisitante",';
        $qry .= ' "NombreCompleto",';
        $qry .= ' "Email",';
        $qry .= ' "NombreComercial",';
        $qry .= ' "ProductoInteres"';
        $qry .= ' FROM "AE"."vw_ae_VisitantePreguntaCategoria" ';
        $qry .= $this->SQLModel->buildWhere($args);
        $result = $this->SQLModel->executeQuery($qry);
        //Remueve el Texto "Otros : "
        /* $data = array();
          foreach($result["data"] as $key => $value){
          if(strpos($value["NombreComercial"],'OTRO : ') !== false){
          $value["NombreComercial"] = str_replace('OTRO : ','',$value["NombreComercial"]);
          $data[$key] = $value;
          }
          else{
          $data[$key] = $value;
          }
          }
          $result["data"] = $data; */
        return $result;
    }

    public function getCompradoresInvitados()
    {
        $qry = 'SELECT ';
        $qry .= '"idVisitante", ';
        $qry .= '"Nombre", ';
        $qry .= '"ApellidoPaterno", ';
        $qry .= '"ApellidoMaterno", ';
        $qry .= '"Email", ';
        $qry .= '"DE_RazonSocial", ';
        $qry .= '"DE_Pais", ';
        $qry .= '"DE_Estado", ';
        $qry .= '"FechaPreregistro", ';
        $qry .= '"¿Participará en la Agenda de citas de negocio?", ';
        $qry .= '"Producto(s) de Interés", ';
        $qry .= '"Alimentos (Seleccione máximo 5 productos)", ';
        $qry .= '"Bebidas (Seleccione máximo 5 productos)", ';
        $qry .= '"Farmacias (Seleccione máximo 5 productos)", ';
        $qry .= '"Mercancias (Generales Seleccione máximo 5 productos)", ';
        $qry .= '"Mobiliario y Equipamiento (Seleccione máximo 5 productos)", ';
        $qry .= '"Organismos e Instituciones",';
        $qry .= '"Servicios (Seleccione máximo 5 productos)",';
        $qry .= '"Tecnología (Seleccione máximo 5 productos)",';
        $qry .= '"Transportación",';
        $qry .= '"Decisión de Compra", ';
        $qry .= '"¿Cómo se enteró del Evento?", ';
        $qry .= '"NombreStatus" ';
        $qry .= 'FROM "AE"."vw_ae_ReporteCompradores"';
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getGeneralAsociados()
    {
        $qry = 'SELECT ';
        $qry .= '"idVisitante", ';
        $qry .= '"Nombre", ';
        $qry .= '"ApellidoPaterno", ';
        $qry .= '"ApellidoMaterno", ';
        $qry .= '"Email", ';
        $qry .= '"DE_RazonSocial", ';
        $qry .= '"DE_Estado", ';
        $qry .= '"FechaPreregistro", ';
        $qry .= '"¿Participará en la Agenda de citas de negocio?", ';
        $qry .= '"Producto(s) de Interés", ';
        $qry .= '"Alimentos (Seleccione máximo 5 productos)", ';
        $qry .= '"Bebidas (Seleccione máximo 5 productos)", ';
        $qry .= '"Farmacias (Seleccione máximo 5 productos)", ';
        $qry .= '"Mercancias (Generales Seleccione máximo 5 productos)", ';
        $qry .= '"Mobiliario y Equipamiento (Seleccione máximo 5 productos)", ';
        $qry .= '"Organismos e Instituciones",';
        $qry .= '"Servicios (Seleccione máximo 5 productos)",';
        $qry .= '"Tecnología (Seleccione máximo 5 productos)",';
        $qry .= '"Transportación",';
        $qry .= '"Decisión de Compra", ';
        $qry .= '"¿Cómo se enteró del Evento?" ';
        $qry .= 'FROM "AE"."vw_ae_ReporteAsociados"';
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getSinCompras()
    {
        $qry = 'SELECT ';
        $qry .= '"idVisitante", ';
        $qry .= '"Nombre", ';
        $qry .= '"ApellidoPaterno", ';
        $qry .= '"ApellidoMaterno", ';
        $qry .= '"Email", ';
        $qry .= '"DE_RazonSocial", ';
        $qry .= '"DE_Estado", ';
        $qry .= '"FechaPreregistro", ';
        $qry .= '"¿Participará en la Agenda de citas de negocio?", ';
        $qry .= '"Producto(s) de Interés", ';
        $qry .= '"Alimentos (Seleccione máximo 5 productos)", ';
        $qry .= '"Bebidas (Seleccione máximo 5 productos)", ';
        $qry .= '"Farmacias (Seleccione máximo 5 productos)", ';
        $qry .= '"Mercancias (Generales Seleccione máximo 5 productos)", ';
        $qry .= '"Mobiliario y Equipamiento (Seleccione máximo 5 productos)", ';
        $qry .= '"Organismos e Instituciones",';
        $qry .= '"Servicios (Seleccione máximo 5 productos)",';
        $qry .= '"Tecnología (Seleccione máximo 5 productos)",';
        $qry .= '"Transportación",';
        $qry .= '"Decisión de Compra", ';
        $qry .= '"¿Cómo se enteró del Evento?", ';
        $qry .= '"Pagado", ';
        $qry .= '"Producto", ';
        $qry .= '"FechaCreacion" ';
        $qry .= 'FROM "AE"."vw_ae_ReporteVisitantesSinCompra" ';
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getAsociadosClicker()
    {
        $qry = 'SELECT ';
        $qry .= '"idVisitante",';
        $qry .= '"Nombre",';
        $qry .= '"ApellidoPaterno",';
        $qry .= '"ApellidoMaterno",';
        $qry .= '"Email",';
        $qry .= '"DE_Telefono",';
        $qry .= '"DE_CP",';
        $qry .= '"DE_Pais",';
        $qry .= '"DE_Estado",';
        $qry .= '"DE_Ciudad",';
        $qry .= '"¿Participará en la Agenda de citas de negocio?",';
        $qry .= '"FechaPreregistro" ';
        $qry .= 'FROM "AE"."vw_ae_ReporteAsociadosClicker" ';
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getVisitanteClicker()
    {
        $qry = 'SELECT ';
        $qry .= '"idVisitante",';
        $qry .= '"Nombre",';
        $qry .= '"ApellidoPaterno",';
        $qry .= '"ApellidoMaterno",';
        $qry .= '"Email",';
        $qry .= '"DE_Telefono",';
        $qry .= '"DE_CP",';
        $qry .= '"DE_Pais",';
        $qry .= '"DE_Estado",';
        $qry .= '"DE_Ciudad",';
        $qry .= '"¿Participará en la Agenda de citas de negocio?",';
        $qry .= '"Pagado",';
        $qry .= '"FechaPreregistro" ';
        $qry .= 'FROM "AE"."vw_ae_ReporteVisitantesClicker" ';
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getCompradoresClicker()
    {
        $qry = 'SELECT ';
        $qry .= '"idVisitante",';
        $qry .= '"Nombre",';
        $qry .= '"ApellidoPaterno",';
        $qry .= '"ApellidoMaterno",';
        $qry .= '"Email",';
        $qry .= '"DE_Telefono",';
        $qry .= '"DE_CP",';
        $qry .= '"DE_Pais",';
        $qry .= '"DE_Estado",';
        $qry .= '"DE_Ciudad",';
        $qry .= '"¿Participará en la Agenda de citas de negocio?",';
        $qry .= '"FechaPreregistro" ';
        $qry .= 'FROM "AE"."vw_ae_ReporteCompradoresClicker" ';
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }
}
