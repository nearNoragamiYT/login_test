<?php

namespace ShowDashboard\RS\AdminProdRSBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

class ProductosModel {

    protected $SQLModel, $schema = "AE";

    public function __construct() {//se crea la conexion
        $this->SQLModel = new SQLModel();
        $this->SQLModel->setSchema($this->schema);
    }

    public function getProductos() {
        $qry = 'SELECT';
        $qry .= ' "idProducto",';
        $qry .= ' "ProductoES",';
        $qry .= ' "ProductoEN",';
        $qry .= ' "DescripcionES",';
        $qry .= ' "DescripcionEN",';
        $qry .= ' "PrecioES",';
        $qry .= ' "PrecioSitio",';
        $qry .= ' "Evento_ES",';
        $qry .= ' "Activo",';
        $qry .= ' "Evento_EN"';
        $qry .= ' FROM';
        $qry .= ' "AE"."Producto" prod';
        $qry .= ' INNER JOIN "SAS"."Evento" eve';
        $qry .= ' ON prod."idEvento" = eve."idEvento"';

        $result = $this->SQLModel->executeQuery($qry);

        if (($result['status'] && count($result['data']) > 0)) {
            foreach ($result['data'] as $value) {
                $data[$value['idProducto']] = $value;
            }
            $result['data'] = $data;
        }
        return $result;
    }

    public function insertProducto($data) {
        $result = $this->SQLModel->insertIntoTable("Producto", $data, "idProducto");
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        unset($result['query']);
        
        return $result;
    }
    
    public function deleteProducto($data){
        $result = $this->SQLModel->deleteFromTable("Producto", $data);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }
    
    public function updateProducto ($data, $where){
        $result = $this->SQLModel->updateFromTable("Producto", $data, $where);
        
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }
    
    public function getProducto($data_prod){
        $qry = 'SELECT';
        $qry .= ' "idProducto",';
        $qry .= ' "ProductoES",';
        $qry .= ' "DescripcionES",';
        $qry .= ' "Precio",';
        $qry .= ' "PrecioSitio",';
        $qry .= ' "Activo" ';
        $qry .= ' FROM';
        $qry .= ' "AE"."Producto"';
        $qry .= ' WHERE "idProducto" =' . $data_prod['idProducto'];
        
        $result = $this->SQLModel->executeQuery($qry);

//        if (($result['status'] && count($result['data']) > 0)) {
//            foreach ($result['data'] as $value) {
//                $data[$value['idProducto']] = $value;
//            }
//            $result['data'] = $data;
//        }
        return $result;
    }

}
