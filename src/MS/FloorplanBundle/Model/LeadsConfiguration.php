<?php

namespace MS\FloorplanBundle\Model;

class LeadsConfiguration {

    public function getColumnCategories($lang) {
        return Array(
            Array(
                "category_id" => 1,
                "text" => $lang['ms_textoFiltros'],
            ),
            Array(
                "category_id" => 2,
                "text" => "-",
            ),
            Array(
                "category_id" => 3,
                "text" => "-",
            ),
//            Array(
//                "category_id" => 4,
//                "text" => '-',
//            ),
        );
    }

    public function getColumnDefs($lang) {
        return Array(
            "NombreCompleto" => Array(
                'category_id' => 1,
                'text' => $lang['ms_textoNombre'], //texto general
                'help-lb' => "",
                'data-class' => "expand",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                //'json_column' => "jp",
                ),
                'is_visible' => TRUE,
            ),
            "Email" => Array(
                'category_id' => 1,
                'text' => "Email", //texto
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                //'json_column' => "jp",
                ),
                'is_visible' => TRUE,
            ),
            "DE_Cargo" => Array(
                'category_id' => 1,
                'text' => $lang['ms_textoCargo'], //texto
                'help-lb' => "",
                'data-hide' => "tablet,small_screen",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                //'json_column' => "jp",
                ),
                'is_visible' => TRUE,
            ),
            "DE_RazonSocial" => Array(
                'category_id' => 1,
                'text' => $lang['ms_textoEmpresa'], //texto
                'help-lb' => "",
                'data-hide' => "tablet,small_screen",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                //'json_column' => "jp",
                ),
                'is_visible' => TRUE,
            ),
            "DE_Ciudad" => Array(
                'category_id' => 1,
                'text' => $lang['ms_textoCiudad'], //texto
                'help-lb' => "",
                'data-hide' => "tablet,small_screen",
                'filter_options' => Array(
                    'is_select' => FALSE,
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                //'json_column' => "jp",
                ),
                'is_visible' => TRUE,
            ),
            "DE_Estado" => Array(
                'category_id' => 1,
                'text' => $lang['ms_textoEstado'], //texto
                'help-lb' => "",
                'data-hide' => "tablet,small_screen",
                'filter_options' => Array(
                    'is_select' => FALSE,
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                //'json_column' => "jp",
                ),
                'is_visible' => TRUE,
            ),
            "DE_Pais" => Array(
                'category_id' => 2,
                'text' => $lang['ms_textoPais'], //texto
                'help-lb' => "",
                'data-hide' => "tablet,small_screen",
                'filter_options' => Array(
                    'is_select' => FALSE,
                    'is_check' => FALSE,
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                //'json_column' => "jp",
                ),
                'is_visible' => TRUE,
            ),
            "DireccionCompleta" => Array(
                'category_id' => 2,
                'text' => $lang['ms_textoDireccion'], //texto
                'help-lb' => "",
                'data-hide' => "tablet,small_screen",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                //'json_column' => "jp",
                ),
                'is_visible' => TRUE,
            ),
            "DE_Telefono" => Array(
                'category_id' => 2,
                'text' => $lang['ms_textoTelefono'], //texto
                'help-lb' => "",
                'data-hide' => "tablet,small_screen",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                //'json_column' => "jp",
                ),
                'is_visible' => TRUE,
            ),
//            "Fecha" => Array(
//                'category_id' => 2,
//                'text' => 'Fecha de escaneo', //texto
//                'help-lb' => "",
//                'data-hide' => "tablet,small_screen",
//                'filter_options' => Array(
//                    'is_optional_column' => TRUE,
//                    'search_operator' => '=',
//                ),
//                'is_visible' => TRUE,
//            ),
            "Recorrido" => Array(
                'category_id' => 2,
                'text' => $lang['ms_textoRecorrido'],//texto
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_select' => TRUE,
                    'is_check' => FALSE,
                    'is_optional_column' => TRUE,
                    'search_operator' => '=',
                    'values' => Array(0 => "No", 1 => $lang['ms_textoSi']=='Yes'?"Yes":"Si"),
                ),
                'is_visible' => TRUE,
            ),
            "Lectura" => Array(
                'category_id' => 3,
                'text' => 'Lecturas',//texto
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_select' => FALSE,
                    'is_check' => FALSE,
                    'is_optional_column' => TRUE,
                    'filter_operators' => Array(">", ">=", "=", "<=", "<"),
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "List" => Array(
                'category_id' => 3,
                'text' => $lang['ms_textoInfoBasica'],//texto
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_select' => FALSE,
                    'is_check' => FALSE,
                    'is_optional_column' => TRUE,
                    'filter_operators' => Array(">", ">=", "=", "<=", "<"),
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "Booth" => Array(
                //'category_id' => 4,
                'category_id' => 3,
                'text' => $lang['ms_textoStand'],//texto
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_select' => FALSE,
                    'is_check' => FALSE,
                    'is_optional_column' => TRUE,
                    'filter_operators' => Array(">", ">=", "=", "<=", "<"),
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "Video" => Array(
                'category_id' => 3,
                'text' => $lang['ms_textoVideo'],//textos
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_select' => FALSE,
                    'is_check' => FALSE,
                    'is_optional_column' => TRUE,
                    'filter_operators' => Array(">", ">=", "=", "<=", "<"),
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "Location" => Array(
                'category_id' => 3,
                'text' => $lang['ms_textoUbicacion'],//textos
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                   'is_select' => FALSE,
                    'is_check' => FALSE,
                    'is_optional_column' => TRUE,
                    'filter_operators' => Array(">", ">=", "=", "<=", "<"),
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "Product" => Array(
                'category_id' => 3,
                'text' => $lang['ms_textoProductos'],//textos
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_select' => FALSE,
                    'is_check' => FALSE,
                    'is_optional_column' => TRUE,
                    'filter_operators' => Array(">", ">=", "=", "<=", "<"),
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            
            "WebPage" => Array(
                'category_id' => 3,
                'text' => $lang['ms_textoPagWeb'],//textos
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_select' => FALSE,
                    'is_check' => FALSE,
                    'is_optional_column' => TRUE,
                    'filter_operators' => Array(">", ">=", "=", "<=", "<"),
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
        );
    }

}
