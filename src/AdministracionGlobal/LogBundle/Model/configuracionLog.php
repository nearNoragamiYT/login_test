<?php

namespace AdministracionGlobal\LogBundle\Model;

class configuracionLog {

    public function getColumnCategories($texts = Array()) {
        return Array(
            Array(
                "category_id" => 1,
                "text" => "Log",
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

    public function getColumnDefs($texts = Array(), $lang = "es") {
        return Array(
            "idLog" => Array(
                'category_id' => 1,
                'text' => $texts['data']['sas_idLog'],
                'help-lb' => "",
                'data-class' => "expand",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "idUsuario" => Array(
                'category_id' => 1,
                'text' => $texts['data']['sas_idUsuario'],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "idModuloIxpo" => Array(
                'category_id' => 1,
                'text' => $texts['data']['sas_idModulo'],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "Accion" => Array(
                'category_id' => 2,
                'text' => $texts['data']['sas_accion'],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "IP" => Array(
                'category_id' => 2,
                'text' => $texts['data']['sas_ip'],
                'help-lb' => "",
                'data-hide' => "phone,tablet,small_screen",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Navegador" => Array(
                'category_id' => 2,
                'text' => $texts['data']['sas_navegador'],
                'help-lb' => "",
                'data-hide' => "phone,tablet,small_screen",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "SistemaOperativo" => Array(
                'category_id' => 3,
                'text' => $texts['data']['sas_sistemaOp'],
                'help-lb' => "",
                'data-hide' => "phone,tablet,small_screen",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "FechaCreacion" => Array(
                'category_id' => 3,
                'text' => $texts['data']['sas_fechaCreacion'],
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "FechaModificacion" => Array(
                'category_id' => 3,
                'text' => $texts['data']['sas_fechaModificacion'],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
        );
    }

}
