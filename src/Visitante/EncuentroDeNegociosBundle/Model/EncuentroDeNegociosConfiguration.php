<?php

namespace Visitante\EncuentroDeNegociosBundle\Model;

use Visitante\EncuentroDeNegociosBundle\Model\EncuentroDeNegociosModel;

class EncuentroDeNegociosConfiguration {

    protected $EncuentroDeNegociosModel;

    public function __construct() {
        $this->EncuentroDeNegociosModel = new EncuentroDeNegociosModel();
    }

    public function getVisitantesFilters() {
        return Array(
            "idVisitante" => Array(
                'table' => "",
                'field' => '"idVisitante"',
                'text' => "Identificador",
                'help-lb' => "",
                'text_placeholder' => "ID",
                'filter_options' => Array(
                    'type' => "input",
                    'show_filter' => true,
                    'search_operator' => '=',
                ),
            ),
            "NombreCompleto" => Array(
                'table' => "",
                'field' => '"NombreCompleto"',
                'text' => "Nombre Completo",
                'text_placeholder' => "Nombre Completo",
                'help-lb' => "",
                'filter_options' => Array(
                    'type' => "input",
                    'show_filter' => true,
                    'search_operator' => 'ilike',
                ),
            ),
            "Email" => Array(
                'table' => "",
                'field' => '"Email"',
                'text' => "Email",
                'help-lb' => "",
                'text_placeholder' => "Email",
                'filter_options' => Array(
                    'type' => "input",
                    'show_filter' => true,
                    'search_operator' => 'ilike',
                ),
            ),
            "NombreComercial" => Array(
                'table' => "",
                'field' => '"idNombreComercial"',
                'text' => "Nombre Comercial",
                'help-lb' => "",
                'text_placeholder' => "Nombre Comercial",
                'filter_options' => Array(
                    'type' => "select",
                    'show_filter' => true,
                    'values' => $this->EncuentroDeNegociosModel->getNombreComercial(),
                    'search_operator' => '=',
                ),
            ),
            "Cargo" => Array(
                'table' => "",
                'field' => '"DE_idCargo"',
                'text' => "Cargo",
                'help-lb' => "",
                'text_placeholder' => "Cargo",
                'filter_options' => Array(
                    'type' => "select",
                    'show_filter' => true,
                    'values' => $this->EncuentroDeNegociosModel->getCargo(),
                    'search_operator' => '=',
                ),
            ),
            "FechaPreregistro" => Array(
                'table' => "",
                'field' => '"FechaPreregistro"',
                'text' => "FechaPreregistro",
                'help-lb' => "",
                'text_placeholder' => "Fecha Preregistro",
                'filter_options' => Array(
                    'type' => "date",
                    'show_filter' => true,
                    'search_operator' => '=',
                ),
            ),
        );
    }

    public function getEncuentroDeNegocios() {
        $select = array(
            '1' => array(
                "idAsociado" => "1",
                "DescripcionAsociado" => "Si"
            ),
            '0' => array(
                "idAsociado" => "0",
                "DescripcionAsociado" => "No"
            )
        );
        return $select;
    }

    public function getPreregistrado() {
        $select = array(
            '0' => array(
                "DescripcionPreregistrado" => "Si",
                "idPreregistrado" => "Si"
            ),
            '1' => array(
                "DescripcionPreregistrado" => "No",
                "idPreregistrado" => "No"
            )
        );
        return $select;
    }

    public function getEncuentroNegocio() {
        $select = array(
            '0' => array(
                "idEncuentroNegocios" => "Si",
                "EncuentroNegocios" => "Si"
            ),
            '1' => array(
                "idEncuentroNegocios" => "No",
                "EncuentroNegocios" => "No"
            )
        );
        return $select;
    }

}
