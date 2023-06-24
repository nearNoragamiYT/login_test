<?php

namespace Empresa\EmpresaContactoBundle\Model;

class EmpresaContactoConfiguration {

    public function getGeneralContactMetaData($textos) {
        return Array(
            "nombrecompleto" => Array(
                'category_id' => 1,
                'text' => $textos["sas_nombreContacto"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Email" => Array(
                'category_id' => 1,
                'text' => $textos["sas_emailContacto"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Password" => Array(
                'category_id' => 1,
                'text' => $textos["sas_passwordContacto"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Telefono" => Array(
                'category_id' => 1,
                'text' => $textos["sas_telefono"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => FALSE,
            ),
            "Celular" => Array(
                'category_id' => 1,
                'text' => $textos["sas_celular"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => FALSE,
            ),
        );
    }

    public function getEditionContactMetaData($textos) {
        return Array(
            "idContactoTipo" => Array(
                'category_id' => 1,
                'text' => $textos["sas_tipoContacto"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "nombrecompleto" => Array(
                'category_id' => 1,
                'text' => $textos["sas_nombreContacto"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Email" => Array(
                'category_id' => 1,
                'text' => $textos["sas_emailContacto"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Password" => Array(
                'category_id' => 1,
                'text' => $textos["sas_passwordContacto"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Puesto" => Array(
                'category_id' => 1,
                'text' => $textos["sas_puestoContacto"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Telefono" => Array(
                'category_id' => 1,
                'text' => $textos["sas_telefono"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => FALSE,
            ),
            "Celular" => Array(
                'category_id' => 1,
                'text' => $textos["sas_celular"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => FALSE,
            ),
            "Principal" => Array(
                'category_id' => 1,
                'text' => $textos["sas_contactoPrincipal"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'values' => Array(
                    "" => "No",
                    false => "No",
                    true => "Si"
                ),
                'is_visible' => TRUE,
            )
        );
    }

}
