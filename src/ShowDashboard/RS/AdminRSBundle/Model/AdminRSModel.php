<?php

namespace ShowDashboard\RS\AdminRSBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

class AdminRSModel {

    protected $SQLModel, $schema = "AE";

    public function __construct() {//se crea la conexion
        $this->SQLModel = new SQLModel();
        $this->SQLModel->setSchema($this->schema);
    }

    public function getEstadoSistema() {
        $qry = 'SELECT';
        $qry .= ' conf."idConfiguracion",';
        $qry .= ' conf."idEdicion",';
        $qry .= ' conf."GafeteMultiple",';
        $qry .= ' conf."Preregistro",';
        $qry .= ' edi."Edicion_ES",';
        $qry .= ' eve."Evento_ES",';
        $qry .= ' eve."idEvento",';
        $qry .= ' cap."idCaptura",';
        $qry .= ' cap."TipoCaptura",';
        $qry .= ' conf."ClubElite", ';
        $qry .= ' conf."Tienda", ';
        $qry .= ' conf."AutoRegistro", ';
        $qry .= ' nodo."NombreNodo", ';
        $qry .= ' nodo."idNodo", ';
        $qry .= ' conf."ip" ';
        $qry .= ' FROM';
        $qry .= ' "AE"."ConfiguracionNodo" conf';
        $qry .= ' INNER JOIN "AE"."CapturaTipo" cap';
        $qry .= ' ON conf."idCaptura" = cap."idCaptura"';
        $qry .= ' INNER JOIN "AE"."Nodo" nodo';
        $qry .= ' ON conf."idNodo" = nodo."idNodo"';
        $qry .= ' INNER JOIN "SAS"."Evento" eve';
        $qry .= ' ON conf."idEvento" = eve."idEvento"';
        $qry .= ' INNER JOIN "SAS"."Edicion" edi';
        $qry .= ' ON conf."idEdicion" = edi."idEdicion"';

        $result = $this->SQLModel->executeQuery($qry);

        if (($result['status'] && count($result['data']) > 0)) {
            foreach ($result['data'] as $value) {
                $data[$value['idConfiguracion']] = $value;
            }
            $result['data'] = $data;
        }

        return $result;
    }

    public function getNodo($args = array()) {
        $qry = 'SELECT';
        $qry .= ' "idNodo",';
        $qry .= ' "NombreNodo" ';
        $qry .= ' FROM';
        $qry .= ' "AE"."Nodo"';

        $result = $this->SQLModel->executeQuery($qry);

        if (($result['status'] && count($result['data']) > 0)) {
            foreach ($result['data'] as $value) {
                $data[$value['idNodo']] = $value;
            }
            $result['data'] = $data;
        }
        return $result;
    }

    public function getEdicion($args = array()) {
        $qry = 'SELECT';
        $qry .= ' "idEdicion", ';
        $qry .= ' "Edicion_ES"';
        $qry .= ' FROM';
        $qry .= ' "SAS"."Edicion"';
        $qry .= 'WHERE "idEdicion" = 9';

        $result = $this->SQLModel->executeQuery($qry);

        if (($result['status'] && count($result['data']) > 0)) {
            foreach ($result['data'] as $value) {
                $data[$value['idEdicion']] = $value;
            }
            $result['data'] = $data;
        }
        return $result;
    }

    public function getEvento($idEvento) {
        $qry = 'SELECT';
        $qry .= ' "idEvento",';
        $qry .= ' "Evento_ES" ';
        $qry .= 'FROM';
        $qry .= ' "SAS"."Evento"';
        $qry .= 'WHERE "idEvento" =' . $idEvento;

        $result = $this->SQLModel->executeQuery($qry);

        if (($result['status'] && count($result['data']) > 0)) {
            foreach ($result['data'] as $value) {
                $data[$value['idEvento']] = $value;
            }
            $result['data'] = $data;
        }
        return $result;
    }

    public function getConfigRs($args = array()) {
        $result = $this->SQLModel->selectFromTable("ConfiguracionNodo");

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        foreach ($result['data'] as $value) {
            $data[$value['idConfiguracion']] = $value;
        }
        $result["data"] = array_pop($data);
        return $result;
    }

    public function insertNodo($args) {
        $result = $this->SQLModel->insertIntoTable("Nodo", $args, "idNodo");
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        unset($result['query']);
        return $result;
    }

    public function insertEstado($args) {
        $result = $this->SQLModel->insertIntoTable("ConfiguracionNodo", $args, "idConfiguracion");

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        unset($result['query']);
        return $result;
    }

    public function updateEstado($args, $where) {
        $result = $this->SQLModel->updateFromTable("ConfiguracionNodo", $args, $where);

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    public function deleteEstado($args) {
        $result = $this->SQLModel->deleteFromTable("ConfiguracionNodo", $args);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }

    /* REDIRECCION A SQL-MODEL */

    public function updateGeneral($args, $where) {
        $result = $this->SQLModel->updateFromTable("ConfiguracionNodo", $args, $where);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result;
    }
}
