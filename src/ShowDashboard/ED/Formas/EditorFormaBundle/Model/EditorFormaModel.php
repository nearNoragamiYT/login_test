<?php

namespace ShowDashboard\ED\Formas\EditorFormaBundle\Model;

/**
 * Description of EditorFormaModel
 *
 * @author Javier
 */
use ShowDashboard\DashboardBundle\Model\DashboardModel;

//use Utilerias\SQLBundle\Model\SQLModel;

class EditorFormaModel extends DashboardModel {

    protected $SQLModel, $schema = "SAS";

    public function __construct() {
        parent::__construct();
//$this->SQLModel = new SQLModel();
    }

    public function getForma($where = array()) {
        $fields = array('idForma', 'idEvento', 'idEdicion', 'NombreFormaES', 'NombreFormaEN', 'FechaLimite', 'idTipoForma');
        return $this->SQLModel->selectFromTable('Forma', $fields, $where);
    }

    public function insertEditTextosForma($data) {
        $qry = 'SELECT "SAS"."fn_sas_InsertaActualizaTextos"';
        $qry .= ' (';
        $qry .= $data['idPlataformaIxpo'] . ',';
        $qry .= $data['idEdicion'] . ',';
        $qry .= $data['Seccion'] . ',';
        $qry .= "'" . $data['Etiqueta'] . "',";
        $qry .= "$" . "chv$" . $data['Texto_ES'] . "$" . "chv$,";
        $qry .= "$" . "chv$" . $data['Texto_EN'] . "$" . "chv$";
        $qry .= ");";
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getTextosForma($where) {
        $fields = array('Etiqueta', 'Texto_ES', 'Texto_EN');
        $result = $this->SQLModel->selectFromTable('Texto', $fields, $where);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = array();

        foreach ($result['data'] as $key => $value) {
            $etiqueta = $value['Etiqueta'];
            unset($value['Etiqueta']);
            $data[$etiqueta] = $value;
        }

        $result['data'] = $data;
        return $result;
    }

    public function getServiciosForma($args) {
        $fields = array(
            'idServicio',
            'idForma',
            'idEvento',
            'idEdicion',
            'TituloES',
            'TituloEN',
            'MonedaES',
            'MonedaEN',
            'DescripcionES',
            'DescripcionEN',
            'PrecioAntesFechaES',
            'PrecioAntesFechaEN',
            'FechaLimite',
            'PrecioDespuesFechaES',
            'PrecioDespuesFechaEN',
            'Orden',
        );
        $result = $this->SQLModel->selectFromTable('Servicio', $fields, $args, array("Orden" => "ASC"));
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idServicio']] = $value;
        }

        $result['data'] = $data;
        return $result;
    }

    public function insertEditServicio($data) {
        /* Si trae el id editamos, de lo contrario insertamos */
        if ($this->is_defined($data['idServicio'])) {
            $where = array('idServicio' => $data['idServicio']);
            unset($data['idServicio']);
            return $this->SQLModel->updateFromTable('Servicio', $data, $where, 'idServicio');
        }
        unset($data['idServicio']);
        return $this->SQLModel->insertIntoTable('Servicio', $data, 'idServicio');
    }

    public function deleteServicio($args) {
        return $this->SQLModel->deleteFromTable('Servicio', $args);
    }

    public function formatQuoteValue($args) {
        $args_tmp = array();
        foreach ($args as $key => $value) {
            /* Si el valor tiene operadores relacionales, construimos la condicion */
            if ((is_array($value) && count($value) > 0)) {
                if (substr($value['value'], 0, 1) == "'" && substr($value['value'], -1) == "'") {
                    $value['value'] = substr($value['value'], 1, -1);
                }
                $value['value'] = (empty($value['value'])) ? "" : "'" . $value['value'] . "'";
            } else {
                if (substr($value, 0, 1) == "'" && substr($value, -1) == "'") {
                    $value = substr($value, 1, -1);
                }
                $value = (empty($value)) ? "" : "'" . $value . "'";
            }
            $args_tmp[$key] = $value;
        }
        return $args_tmp;
    }

    public function trimValues(&$post) {
        if (count($post) == 0) {
            return $post;
        }

        foreach ($post as $key => $value) {
            $post[$key] = trim($value);
        }
        return $post;
    }

    public function is_defined($value) {
        if (isset($value) && !empty($value) && $value != NULL && $value != "") {
            return TRUE;
        }
        return FALSE;
    }

}
