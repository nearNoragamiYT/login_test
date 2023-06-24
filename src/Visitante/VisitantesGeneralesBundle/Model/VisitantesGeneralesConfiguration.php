<?php

namespace Visitante\VisitantesGeneralesBundle\Model;

use Visitante\VisitantesGeneralesBundle\Model\VisitantesGeneralesModel;

class VisitantesGeneralesConfiguration {

    protected $VisitantesGeneralesModel;

    public function __construct() {
        $this->VisitantesGeneralesModel = new VisitantesGeneralesModel();
    }

    public function getVisitantesFilters() {
        return Array(
            "idVisitante" => Array(
                'table' => "vis",
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
                'table' => "vis",
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
                'table' => "vis",
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
            "DE_RazonSocial" => Array(
                'table' => "vis",
                'field' => '"DE_RazonSocial"',
                'text' => "Nombre Comercial",
                'help-lb' => "",
                'text_placeholder' => "Nombre Comercial",
                'filter_options' => Array(
                    'type' => "input",
                    'show_filter' => true,
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
                    'show_filter' => true,
                    'values' => $this->VisitantesGeneralesModel->getCargo(),
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
            "VisitanetTipo" => Array(
                'table' => "",
                'field' => '"VisitanetTipo"',
                'text' => "Tipo Visitante",
                'help-lb' => "",
                'text_placeholder' => "VisitanetTipo",
                'filter_options' => Array(
                    'type' => "input",
                    'show_filter' => true,
                    'search_operator' => 'ilike',
                ),
            ),
        );
    }

    public function getAsociado() {
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
