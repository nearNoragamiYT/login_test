<?php

namespace Wizard\ProductoBundle\Model;

use Wizard\WizardBundle\Model\WizardModel;

/**
 * Description of WizardModel
 *
 * @author Javier
 */
class ProductoModel extends WizardModel {

    public function __construct() {
        parent::__construct();
    }

    public function insertEdicionProducto($data) {
        return $this->SQLModel->insertIntoTable('EdicionProductoIxpo', $data, 'idProductoIxpo');
    }

    public function getPlataformaIxpo($args = array()) {
        $fields = array('idPlataformaIxpo', 'Nombre', 'Descripcion');
        return $this->SQLModel->selectFromTable("PlataformaIxpo", $fields, $args);
    }

    public function getModuloIxpo($args = array()) {
        $fields = array('idModuloIxpo', 'idPlataformaIxpo', 'Modulo_ES', 'Modulo_EN', 'Modulo_FR', 'Modulo_PT');
        $orderBy = array('"idPlataformaIxpo"' => 'ASC', '"Orden"' => 'ASC');
        return $this->SQLModel->selectFromTable("ModuloIxpo", $fields, $args, $orderBy);
    }

    public function getProductoIxpo($args = array()) {
        $fields = array('idProductoIxpo', 'Nombre', 'EstandarIxpo');
        return $this->SQLModel->selectFromTable("ModuloIxpo", $fields, $args);
    }

    public function disableEdicionProducto($data, $where = array()) {
        return $this->SQLModel->updateFromTable('EdicionProductoIxpo', $data, $where);
    }

    public function deleteEdicionProducto($where = array()) {
        return $this->SQLModel->deleteFromTable('EdicionProductoIxpo', $where);
    }

    public function getEventoEdicion($idComiteOrganizador = NULL) {
        $qry = 'SELECT';
        $qry .= ' e."idEvento",';
        $qry .= ' e."Evento_ES",';
        $qry .= ' ed."idEdicion",';
        $qry .= ' ed."Edicion_ES"';

        $qry .= ' FROM "' . $this->schema . '"."Evento" e';
        $qry .= ' JOIN "' . $this->schema . '"."Edicion" ed';
        $qry .= ' ON ed."idEvento"=e."idEvento"';
        if ($idComiteOrganizador) {
            $qry .= ' WHERE e."idComiteOrganizador"=' . $idComiteOrganizador;
        }
        $qry .= ' ORDER BY e."idEvento" ASC,';
        $qry .= ' ed."idEdicion" ASC';
        return $this->SQLModel->executeQuery($qry);
    }

}
