<?php

namespace Visitante\PrensaBundle\Model;

use Visitante\PrensaBundle\Model\PrensaModel;

class PrensaConfiguration {

    protected $PrensaModel;

    public function __construct() {
        $this->PrensaModel = new PrensaModel();
    }

    public function getVisitantesFilters() {
        return Array(
            "idVisitanteNoAutorizado" => Array(
                'table' => "",
                'field' => '"idVisitanteNoAutorizado"',
                'text' => "idVisitanteNoAutorizado",
                'help-lb' => "",
                'filter_options' => Array(
                    'search_operator' => '=',
                ),
            ),
            "idVisitante" => Array(
                'table' => "",
                'field' => '"idVisitante"',
                'text' => "Identificador",
                'help-lb' => "",
                'text_placeholder' => "ID",
                'filter_options' => Array(
                    'type' => "input",
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
                    'search_operator' => 'ilike',
                ),
            ),
            "NombreComercial" => Array(
                'table' => "",
                'field' => '"NombreComercial"',
                'text' => "Nombre Comercial",
                'help-lb' => "",
                'text_placeholder' => "Nombre Comercial",
                'filter_options' => Array(
                    'type' => "input",
                    'search_operator' => 'ilike',
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
                    'values' => $this->PrensaModel->getCargo(),
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
                    'search_operator' => '=',
                ),
            ),
            "NombreStatus" => Array(
                'table' => "",
                'field' => '"idStatus"',
                'text' => "NombreStatus",
                'help-lb' => "",
                'text_placeholder' => "Estatus Registro",
                'filter_options' => Array(
                    'type' => "select",
                    'values' => $this->PrensaModel->getStatus(),
                    'search_operator' => '=',
                ),
            ),
        );
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
