<?php

namespace ShowDashboard\RS\VisitanteBundle\Model;

class VisitanteConfiguration {

    public function getColumnCategories($text = Array()) {
        $text = $text['data'];
        return Array(
            Array(
                "category_id" => 1,
                "text" => $text['sas_visitantes'],
            ),
            Array(
                "category_id" => 2,
                "text" => "-",
            ),
            Array(
                "category_id" => 3,
                "text" => "-",
            ),
            Array(
                "category_id" => 4,
                "text" => 'Invisible',
            ),
        );
    }

    public function getColumnDefs($text = Array(), $lang = "es", $idEdicion) {
        $text = $text['data'];
        return Array(
            "idVisitante" => Array(
                'table' => "vis",
                'category_id' => 1,
                'text' => $text['sas_idVisitante'],
                'help-lb' => "",
                'data-class' => "expand",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "NombreCompleto" => Array(
                'table' => "vis",
                'category_id' => 1,
                'text' => $text['sas_nombreVisitante'],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Email" => Array(
                'table' => "vis",
                'category_id' => 1,
                'text' => $text['sas_emailVisitante'],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "DE_RazonSocial" => Array(
                'table' => "vis",
                'category_id' => 2,
                'text' => $text['sas_empresaVisitante'],
                'help-lb' => "",
                'data-hide' => "phone",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "DE_Cargo" => Array(
                'table' => "vis",
                'category_id' => 2,
                'text' => $text['sas_cargoVisitante'],
                'help-lb' => "",
                'data-hide' => "phone",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "ClubElite" => Array(
                'table' => "vise",
                'category_id' => 2,
                'text' => 'Tipo de Visitante',
                'help-lb' => "",
                'data-hide' => "phone,tablet",
                'filter_options' => Array(
                    'is_select' => TRUE,
                    'values' => array(
                        '1' => 'VIP',
                        '0' => 'Visitante General'
                    ),
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "Preregistrado" => Array(
                'table' => "vise",
                'category_id' => 3,
                'text' => 'Preregistrado',
                'help-lb' => "",
                'data-hide' => "phone,tablet,small_screen",
                'filter_options' => Array(
                    'is_select' => TRUE,
                    'values' => array(
                        '1' => 'Si',
                        '0' => 'No'
                    ),
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "FechaAlta_AE" => Array(
                'table' => "vise",
                'category_id' => 3,
                'text' => 'Fecha Registro',
                'help-lb' => "",
                'data-hide' => "phone,tablet",
                'filter_options' => Array(
                    'is_date' => TRUE,
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "DE_Telefono" => Array(
                'table' => "vis",
                'category_id' => 3,
                'text' => 'Telefono',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "DE_AreaPais" => Array(
                'table' => "vis",
                'category_id' => 4,
                'text' => 'Lada Pais',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "DE_AreaCiudad" => Array(
                'table' => "vis",
                'category_id' => 4,
                'text' => 'Lada',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
        );
    }

    public function getTitulos() {
        return array(
            'Sr.' => array(
                'es' => 'Sr.',
                'en' => 'Mr.',
            ),
            'Sra.' => array(
                'es' => 'Sra.',
                'en' => 'Mrs.',
            ),
            'Srita.' => array(
                'es' => 'Srita.',
                'en' => 'Miss',
            ),
            'Lic.' => array(
                'es' => 'Lic.',
                'en' => 'Lic.',
            ),
            'Ing.' => array(
                'es' => 'Ing.',
                'en' => 'Engineer',
            ),
            'Dr.' => array(
                'es' => 'Dr.',
                'en' => 'Doctor',
            ),
            'Prof.' => array(
                'es' => 'Prof.',
                'en' => 'Profesor',
            ),
        );
    }

    public function getVisitanteTipo($lang) {
        $select = array(
            '0' => array(
                'es' => 'Sin Tipo',
                'en' => 'No type',
            ),
            '1' => array(
                'es' => 'Visitante General',
                'en' => 'General Attendee',
            ),
            '2' => array(
                'es' => 'Visitante',
                'en' => 'Attendee',
            ),
            '4' => array(
                'es' => 'VIP',
                'en' => 'VIP',
            ),
        );
        foreach ($select as $key => $value) {
            $select[$key] = $value[$lang];
        }
        return $select;
    }
    
}
