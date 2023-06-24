<?php

namespace ShowDashboard\RS\AsistenteRSBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

class AsistenteRSModel {

    protected $SQLModel, $schema = "AE";

    public function __construct() {//se crea la conexion
        $this->SQLModel = new SQLModel();
        $this->SQLModel->setSchema($this->schema);
    }

    public function getEdicion() {
        $qry = 'SELECT';
        $qry .= ' "idEdicion", ';
        $qry .= ' "Edicion_ES"';
        $qry .= ' FROM';
        $qry .= ' "SAS"."Edicion"';

        $result = $this->SQLModel->executeQuery($qry);
        if (($result['status'] && count($result['data']) > 0)) {
            foreach ($result['data'] as $value) {
                $data[$value['idEdicion']] = $value;
            }
            $result['data'] = $data;
        }
        return $result;
    }

    public function getConfiguracionEdicion($lang, $idEdicion) {
        $qry = 'SELECT ';
        $qry .= ' "idEdicion",';
        $qry .= ' "Edicion_ES",';
        $qry .= ' "Edicion_EN",';
        $qry .= ' "Edicion_FR",';
        $qry .= ' "Edicion_PT",';
        $qry .= ' "FechaFin",';
        $qry .= ' "Descripcion",';
        $qry .= ' "Abreviatura",';
        $qry .= ' "FechaInicio",';
        $qry .= ' "KeyEncriptacion",';
        $qry .= ' "Logo_ES_1",';
        $qry .= ' "Logo_EN_1",';
        $qry .= ' "Logo_FR_1",';
        $qry .= ' "Logo_PT_1",';
        $qry .= ' "Slogan_ES",';
        $qry .= ' "Slogan_EN",';
        $qry .= ' "Slogan_FR",';
        $qry .= ' "Slogan_PT"';
        $qry .= 'FROM';
        $qry .= ' "SAS"."Edicion"';
        $qry .= 'WHERE "idEdicion" =' . "" . $idEdicion . "";

        $result = $this->SQLModel->executeQuery($qry);
        if (($result['status'] && count($result['data']) > 0)) {
            foreach ($result['data'] as $value) {
                $data[$value['idEdicion']] = $value;
            }
            $result['data'] = $data;
        }
        return $result;
    }

    public function getConfiguracionRS($lang, $idEdicion) {
        $qry = 'SELECT ';
        $qry .= ' "idConfiguracion", ';
        $qry .= ' "idEdicion",';
        $qry .= ' "Edicion_ES",';
        $qry .= ' "Edicion_EN",';
        $qry .= ' "FechaFin",';
        $qry .= ' "Descripcion",';
        $qry .= ' "Abreviatura",';
        $qry .= ' "FechaInicio",';
        $qry .= ' "LlaveEncriptacion",';
        $qry .= ' "Logo_ES_1",';
        $qry .= ' "Logo_ES_2",';
        $qry .= ' "Logo_ES_3",';
        $qry .= ' "Logo_EN_1",';
        $qry .= ' "Logo_EN_2",';
        $qry .= ' "Logo_EN_3",';
        $qry .= ' "ColorHeader",';
        $qry .= ' "ColorButton"';
        $qry .= 'FROM';
        $qry .= ' "AE"."ConfiguracionRS"';
        $qry .= 'WHERE "idEdicion" =' . "" . $idEdicion . "";
        $qry .= 'AND "Edicion_ES" IS NOT NULL';
        
        $result = $this->SQLModel->executeQuery($qry);
        if (($result['status'] && count($result['data']) > 0)) {
            foreach ($result['data'] as $value) {
                $data[$value['idEdicion']] = $value;
            }
            $result['data'] = $data;
        }        
        return $result;
    }

    public function getNombreEdicion($lang, $idEdicion) {
        $qry = 'SELECT';
        $qry .= ' "idEdicion", ';
        $qry .= ' "Edicion_ES",';
        $qry .= ' "Edicion_EN"';
        $qry .= ' FROM';
        $qry .= ' "SAS"."Edicion"';
        $qry .= 'WHERE "idEdicion" =' . "" . $idEdicion . "";

        $result = $this->SQLModel->executeQuery($qry);
        if (($result['status'] && count($result['data']) > 0)) {
            foreach ($result['data'] as $value) {
                $data[$value['idEdicion']] = $value;
            }
            $result['data'] = $data;
        }        
        return $result;
    }

    public function insertConfig($args) {
        
        $result = $this->SQLModel->insertIntoTable("ConfiguracionRS", $args, "idConfiguracion");

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        unset($result['query']);
        return $result;
    }
    
     public function updateConfig($data, $where) {
        $result = $this->SQLModel->updateFromTable("ConfiguracionRS", $data, $where);
        
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }
}