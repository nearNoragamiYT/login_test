<?php

namespace Empresa\EmpresaContratoBundle\Model;

class EmpresaContratoConfiguration {
    
public function getContractMetaData($textos) {  
        return Array(
            "idContrato" => Array(
                'category_id' => 1,
                'text' => $textos["sas_idContrato"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => FALSE,
            ),
            "NoFolio" => Array(
                'category_id' => 1,
                'text' => $textos["sas_numeroContrato"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "idEdicion" => Array(
                'category_id' => 1,
                'text' => $textos["sas_edicion"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
                        "ListadoStand" => Array(
                'category_id' => 1,
                'text' => $textos["sas_standsContrato"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "AreaContratada" => Array(
                'category_id' => 1,
                'text' => $textos["sas_areaTotal"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "ContratoPDF" => Array(
                'category_id' => 1,
                'text' => $textos["sas_pdf"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => FALSE,
            ),
            "idStatusContrato" => Array(
                'category_id' => 1,
                'text' => $textos["sas_estatus"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            )
        );
    }
}