<?php

namespace Wizard\EdicionBundle\Model;

use Wizard\WizardBundle\Model\WizardModel;

/**
 * Description of WizardModel
 *
 * @author Javier
 */
class EdicionModel extends WizardModel {

    public function __construct() {
        parent::__construct();
    }

    public function getEdicion($args = array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("Edicion", $fields, $args, array('"idEdicion"' => 'ASC'));
    }

    public function insertEditEdicion($data) {
        /* Si trae el id editamos, de lo contrario insertamos */
        if ($this->is_defined($data['idEdicion'])) {
            $where = array('idEdicion' => $data['idEdicion']);
            unset($data['idEdicion']);
            return $this->SQLModel->updateFromTable('Edicion', $data, $where, 'idEdicion');
        }
        unset($data['idEdicion']);
        return $this->SQLModel->insertIntoTable('Edicion', $data, 'idEdicion');
    }

    public function getEdicionProductoIxpo($edicion = "") {
        $qry = 'SELECT';
        $qry .= ' "idEdicion",';
        $qry .= ' "idProductoIxpo",';
        $qry .= ' "Url",';
        $qry .= ' "Comentario",';
        $qry .= ' "Activa"';
        $qry .= ' FROM "' . $this->schema . '"."EdicionProductoIxpo"';
        if (is_array($edicion) && count($edicion) > 0) {
            $qry .= ' WHERE';
            foreach ($edicion as $value) {
                $qry .= ' "idEdicion"=' . $value;
                if (next($edicion)) {
                    $qry .= ' OR';
                }
            }
            $qry .= ' AND "Activa"=true';
        } else if ($edicion != "") {
            $qry .= ' WHERE "idEdicion"=' . $edicion;
            $qry .= ' AND "Activa"=true';
        }
        return $this->SQLModel->executeQuery($qry);
    }

    public function getModuloProductoIxpo($args = array()) {
        $qry = 'SELECT';
        $qry .= ' mi."idPlataformaIxpo",';
        $qry .= ' p."PlataformaIxpo",';
        $qry .= ' mpi."idModuloIxpo",';
        $qry .= ' mi."Modulo_ES",';
        $qry .= ' mi."Modulo_EN",';
        $qry .= ' mi."Modulo_FR",';
        $qry .= ' mi."Modulo_PT",';
        $qry .= ' mpi."idProductoIxpo",';
        $qry .= ' pi."ProductoIxpo",';
        $qry .= ' pi."EstandarIxpo"';
        $qry .= ' FROM "' . $this->schema . '"."ModuloProductoIxpo" mpi';
        $qry .= ' JOIN "' . $this->schema . '"."ProductoIxpo" pi';
        $qry .= ' ON mpi."idProductoIxpo"=pi."idProductoIxpo"';
        $qry .= ' JOIN "' . $this->schema . '"."ModuloIxpo" mi';
        $qry .= ' ON mpi."idModuloIxpo"=mi."idModuloIxpo"';
        $qry .= ' JOIN "' . $this->schema . '"."PlataformaIxpo" p';
        $qry .= ' ON mi."idPlataformaIxpo"=p."idPlataformaIxpo"';
        $qry .= ' ORDER BY mi."idPlataformaIxpo" ASC,';
        $qry .= ' mpi."idProductoIxpo" ASC,';
        $qry .= ' mi."Orden" ASC';
        return $this->SQLModel->executeQuery($qry);
    }

    public function formatModuloProductoIxpo($modulos) {
        $moduloProductoIxpo = array();
        if (!(is_array($modulos) && count($modulos) > 0)) {
            return array();
        }

        foreach ($modulos as $key => $value) {
            if (!isset($moduloProductoIxpo[$value['idPlataformaIxpo']])) {
                $plataforma = array(
                    'idPlataformaIxpo' => $value['idPlataformaIxpo'],
                    'PlataformaIxpo' => $value['PlataformaIxpo'],
                    'Productos' => array()
                );
                $moduloProductoIxpo[$value['idPlataformaIxpo']] = $plataforma;
            }

            if (!isset($moduloProductoIxpo[$value['idPlataformaIxpo']]['Productos'][$value['idProductoIxpo']])) {
                $producto = array(
                    'idProductoIxpo' => $value['idProductoIxpo'],
                    'ProductoIxpo' => $value['ProductoIxpo'],
                    'EstandarIxpo' => $value['EstandarIxpo'],
                    'Modulos' => array()
                );
                $moduloProductoIxpo[$value['idPlataformaIxpo']]['Productos'][$value['idProductoIxpo']] = $producto;
            }

            $modulo = array(
                'idModuloIxpo' => $value['idModuloIxpo'],
                'Modulo_ES' => $value['Modulo_ES'],
                'Modulo_EN' => $value['Modulo_EN'],
                'Modulo_FR' => $value['Modulo_FR'],
                'Modulo_PT' => $value['Modulo_PT']
            );
            $moduloProductoIxpo[$value['idPlataformaIxpo']]['Productos'][$value['idProductoIxpo']]['Modulos'][$value['idModuloIxpo']] = $modulo;
        }
        return $moduloProductoIxpo;
    }

    public function deleteEdicion($args) {
        return $this->SQLModel->deleteFromTable('Edicion', $args);
    }

    public function insertUsuarioEdicion($data) {
        return $this->SQLModel->insertIntoTable('UsuarioEdicion', $data, 'idEdicion');
    }

    public function getModuloIxpo($args = array()) {
        return $this->SQLModel->selectFromTable('ModuloIxpo', array('idModuloIxpo'), $args, array("idPlataformaIxpo" => "ASC", "Orden" => "ASC"));
    }

    public function insertPermisosEdicion($data) {
        $qry = 'INSERT INTO';
        $qry .= ' "' . $this->schema . '"."UsuarioEdicionModuloPermisos"';
        $qry .= ' (';
        $qry .= '"idUsuario",';
        $qry .= ' "idEvento",';
        $qry .= ' "idEdicion",';
        $qry .= ' "idModulo",';
        $qry .= ' "Ver",';
        $qry .= ' "Editar",';
        $qry .= ' "Borrar"';
        $qry .= ')';
        $qry .= ' VALUES ';
        foreach ($data as $k => $value) {
            $qry .= '(';
            $qry .= $value['idUsuario'] . ',';
            $qry .= $value['idEvento'] . ',';
            $qry .= $value['idEdicion'] . ',';
            $qry .= $value['idModulo'] . ',';
            $qry .= $value['Ver'] . ',';
            $qry .= $value['Editar'] . ',';
            $qry .= $value['Borrar'];
            $qry .= '),';
        }
        $qry = substr($qry, 0, -1);
        return $this->SQLModel->executeQuery($qry);
    }

    public function deleteUsuarioEdicion($args) {
        return $this->SQLModel->deleteFromTable('UsuarioEdicion', $args);
    }

}
