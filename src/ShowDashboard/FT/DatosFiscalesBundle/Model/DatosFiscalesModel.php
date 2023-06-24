<?php

namespace ShowDashboard\FT\DatosFiscalesBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\SQLBundle\Model\SQLModelFactura;

class DatosFiscalesModel
{

    protected $SQLModel, $SQLModelFactura;

    public function __construct()
    {
        $this->SQLModel = new SQLModel();
        $this->SQLModelFactura = new SQLModelFactura();
    }

    public function getfacturas()
    {
        $query = 'SELECT * FROM "AE"."Factura"';
        return $this->SQLModel->executeQuery($query);
    }
    public function getConexion()
    {
        $query = 'SELECT * FROM "SAS"."Conexion"';
        return $this->SQLModelFactura->executeQuery($query);
    }
}
