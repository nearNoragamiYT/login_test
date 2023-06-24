<?php

namespace Visitante\CompradorBundle\Model;

use Visitante\CompradorBundle\Model\CompradorModel;

class CompradorConfiguration {

    protected $CompradorModel;

    public function __construct() {
        $this->CompradorModel = new CompradorModel();
    }

   

    public function getCompradoresFilters() {
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
                'field' => '"NombreComercial"',
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
                    'values' => $this->CompradorModel->getCargo(),
                    'search_operator' => '=',
                ),
            ),
            /* "Comprador" => Array(
                'table' => "",
                'field' => '"Comprador"',
                'text' => "Comprador",
                'help-lb' => "",
                'text_placeholder' => "Comprador",
                'filter_options' => Array(
                    'type' => "select",
                    'show_filter' => false,
                    'values' => $this->getComprador(),
                    'search_operator' => '=',
                ),
            ), */
            /* "Preregistrado" => Array(
                'table' => "",
                'field' => '"Preregistrado"',
                'text' => "Preregistrado",
                'help-lb' => "",
                'text_placeholder' => "Preregistrado",
                'filter_options' => Array(
                    'type' => "select",
                    'show_filter' => false,
                    'values' => $this->getPreregistrado(),
                    'search_operator' => 'ilike',
                ),
            ), */
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
            "NombreStatus" => Array(
                'table' => "",
                'field' => '"idStatus"',
                'text' => "NombreStatus",
                'help-lb' => "",
                'text_placeholder' => "Estatus Registro",
                'filter_options' => Array(
                    'type' => "select",
                    'show_filter' => true,
                    'values' => $this->CompradorModel->getStatus(),
                    'search_operator' => '=',
                ),
            ),
        );
    }

    public function getComprador() {
        $select = array(
            '1' => array(
                "idComprador" => "1",
                "DescripcionComprador" => "Si"
            ),
            '0' => array(
                "idComprador" => "0",
                "DescripcionComprador" => "No"
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

    

}
