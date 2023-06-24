<?php

namespace AdministracionGlobal\ProductoBundle\Model;

/**
 * Description of Plataforma
 *
 * @author Juan
 */
use Utilerias\SQLBundle\Model\SQLModel;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class ProductoModel extends DashboardModel{

    protected $SQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getProducto($args = array()) {
        $fields = array('idProductoIxpo', 'ProductoIxpo', 'EstandarIxpo');
        $result = $this->SQLModel->selectFromTable("ProductoIxpo", $fields, $args, array('"idProductoIxpo"' => 'ASC'));
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idProductoIxpo']] = $value;
        }
        $result['data'] = $data;
        return $result;
    }

    public function getModulo($args = array()) {
        $fields = array('idModuloIxpo', 'idPadre', 'Nivel', 'Modulo_ES', 'Modulo_EN', 'Modulo_PT', 'Modulo_FR', 'Icono');
        $result = $this->SQLModel->selectFromTable("ModuloIxpo", $fields, $args, array('"Orden"' => 'ASC'));
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idModuloIxpo']] = $value;
        }
        $result['data'] = $data;
        return $result;
    }

    public function getModuloProducto($args = array()) {
        $fields = array('idModuloIxpo', 'idProductoIxpo');
        $result = $this->SQLModel->selectFromTable("ModuloProductoIxpo", $fields, $args, array());
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function insertProducto($args = array()) {
        $qry = 'SELECT * FROM "SAS"."fn_InsertarProductoModulosIxpo"(' . "'" . $args['Nombre'] . "'" . ', ARRAY[' . $args['modulos'] . ']);';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function updateProducto($args = array()) {
        $qry = 'SELECT * FROM "SAS"."fn_ActualizarProductoModulosIxpo"(' . $args['idProducto'] . ', ' . "'" . $args['Nombre'] . "'" . ', ARRAY[' . $args['modulos'] . ']);';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

    public function duplicateProducto($args = array()) {
        $qry = 'SELECT * FROM "SAS"."fn_DuplicarProductoModulosIxpo"(' . $args['id'] . ');';
        $res_pg = $this->SQLModel->executeQuery($qry);
        return $res_pg;
    }

}
