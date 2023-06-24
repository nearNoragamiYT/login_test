<?php

namespace ShowDashboard\RS\DatosGeneralesBundle\Model;

class VisitantePerfilRSConfiguration {
    
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

    public function getVisitorFilters($text = Array()) {
        $text = $text['data'];
        return Array(
            "Nombre" => Array(
                'table' => "vis",
                'text' => "Nombre",
                'help-lb' => "",
                'filter_options' => Array(
                    'search_operator' => 'ilike',
                ),
            ),
            "Email" => Array(
                'table' => "vis",
                'text' => "Email",
                'help-lb' => "",
                'filter_options' => Array(
                    'search_operator' => 'ilike',
                ),
            ),
            "DE_RazonSocial" => Array(
                'table' => "vis",
                'text' => "Empresa",
                'help-lb' => "",
                'filter_options' => Array(
                    'search_operator' => 'ilike',
                ),
            ),
            "DE_Cargo" => Array(
                'table' => "vis",
                'text' => "Cargo",
                'help-lb' => "",
                'filter_options' => Array(
                    'search_operator' => 'ilike',
                ),
            ),
            "Movil" => Array(
                'table' => "vis",
                'text' => "Telefono",
                'help-lb' => "",
                'filter_options' => Array(
                    'search_operator' => 'ilike',
                ),
            ),
            "DE_Pais" => Array(
                'table' => "vis",
                'text' => "Pais",
                'help-lb' => "",
                'filter_options' => Array(
                    'search_operator' => 'ilike',
                ),
            ),
            "DE_Estado" => Array(
                'table' => "vis",
                'text' => "Estado",
                'help-lb' => "",
                'filter_options' => Array(
                    'search_operator' => 'ilike',
                ),
            ),
            "DE_Ciudad" => Array(
                'table' => "vis",
                'text' => "Ciudad",
                'help-lb' => "",
                'filter_options' => Array(
                    'search_operator' => 'ilike',
                ),
            ),
            "DE_Colonia" => Array(
                'table' => "vis",
                'text' => "Colonia",
                'help-lb' => "",
                'filter_options' => Array(
                    'search_operator' => 'ilike',
                ),
            ),
            "DE_Direccion" => Array(
                'table' => "vis",
                'text' => "Direccion",
                'help-lb' => "",
                'filter_options' => Array(
                    'search_operator' => 'ilike',
                ),
            ),
            "DE_CP" => Array(
                'table' => "vis",
                'text' => "C.P.",
                'help-lb' => "",
                'filter_options' => Array(
                    'search_operator' => 'ilike',
                ),
            ),
            "Preregistrado" => Array(
                'table' => "vise",
                'text' => "Pre-Registrado",
                'help-lb' => "",
                'filter_options' => Array(
                    'search_operator' => '=',
                    'is_select' => TRUE,
                    'values' => array(
                        '1' => 'Si',
                        '0' => 'No'
                    ),
                ),
            ),
            "Asistencia" => Array(
                'table' => "vise",
                'text' => "Asistencia",
                'help-lb' => "",
                'filter_options' => Array(
                    'search_operator' => '=',
                    'is_select' => TRUE,
                    'values' => array(
                        '1' => 'Si',
                        '0' => 'No'
                    ),
                ),
            ),
        );
    }

}
