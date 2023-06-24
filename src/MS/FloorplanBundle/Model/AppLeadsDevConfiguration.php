<?php

namespace MS\FloorplanBundle\Model;

/**
 * Description of LeadsDevConfiguration
 *
 * @author Ernesto L <ernestol@infoexpo.com.mx>
 */
class AppLeadsDevConfiguration {

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
                'category_id' =>2,
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
                'category_id' => 2,
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
                'category_id' => 2,
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
                'category_id' => 3,
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
                'category_id' => 3,
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
                'category_id' => 3,
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
        );
    }

}
