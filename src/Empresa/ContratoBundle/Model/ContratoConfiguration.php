<?php

namespace Empresa\ContratoBundle\Model;

class ContratoConfiguration {

    public function getColumnCategories($textos) {
        return Array(
            Array(
                "category_id" => 1,
                "text" => $textos['sas_contrato'],
            ),
            Array(
                "category_id" => 2,
                "text" => "-",
            ),
            Array(
                "category_id" => 3,
                "text" => "-",
            )
        );
    }

    public function getContractMetaData($textos) {
        return Array(
            "NoFolio" => Array(
                'category_id' => 1,
                'text' => $textos["sas_numeroContrato"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "CodigoCliente" => Array(
                'category_id' => 1,
                'text' => $textos["sas_CodigoCliente"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "DC_NombreComercial" => Array(
                'category_id' => 1,
                'text' => $textos["sas_nombreComercial"],
                'help-lb' => "",
                'data-class' => "sorting_asc",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "ListadoStand" => Array(
                'category_id' => 2,
                'text' => $textos["sas_listadoStands"],
                'help-lb' => "",
                'data-class' => "sorting_asc",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "AreaContratada" => Array(
                'category_id' => 2,
                'text' => $textos["sas_TotalAreaM2"],
                'help-lb' => "",
                'data-class' => "sorting_asc",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "idStatusContrato" => Array(
                'category_id' => 2,
                'text' => $textos["sas_estatus"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_select' => TRUE,
                    'values' => Array(
                        '1' => "Borrador",
                        '4' => "Autorizado",
                        '5' => "Cancelado"
                    ),
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "idEmpresa" => Array(
                'category_id' => 3,
                'text' => $textos["sas_idEmpresa"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => FALSE,
            ),
            "idContrato" => Array(
                'category_id' => 3,
                'text' => $textos["sas_idContrato"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => FALSE,
            )
        );
    }

}
