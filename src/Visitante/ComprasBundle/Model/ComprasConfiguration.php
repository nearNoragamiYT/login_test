<?php

namespace Visitante\ComprasBundle\Model;

class ComprasConfiguration {

    public function getColumnCategories($text = Array()) {
        $text = $text['data'];
        return Array(
            Array(
                "category_id" => 1,
                "text" => 'Compra',
            ),
            Array(
                "category_id" => 2,
                "text" => "-",
            ),
            Array(
                "category_id" => 3,
                "text" => "Visitante",
            ),
        );
    }

    public function getColumnDefs($text = Array(), $lang = "es", $idEdicion) {
        $text = $text['data'];
        return Array(
            "idCompra" => Array(
                'table' => "comp",
                'category_id' => 1,
                'text' => 'ID Compra',
                'help-lb' => "",
                'data-class' => "expand",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "idCompraStatus" => Array(
                'table' => "comp",
                'category_id' => 1,
                'text' => 'Estatus Compra',
                'help-lb' => "",
                'filter_options' => Array(
                    'is_select' => TRUE,
                    'values' => Array(
                        '1' => 'Pendiente',
                        '2' => 'Pagada',
                        '3' => 'Cancelada',
                        '4' => 'Cortesía',
                    ),
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "Total" => Array(
                'table' => "comp",
                'category_id' => 1,
                'text' => 'Monto Compra',
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "idFormaPago" => Array(
                'table' => "comp",
                'category_id' => 1,
                'text' => 'Forma Pago',
                'help-lb' => "",
                'data-hide' => "phone",
                'filter_options' => Array(
                    'is_select' => TRUE,
                    'values' => Array(
                        '1' => 'Pago en Línea',
                        '4' => 'Cortesía',
//                        '2' => 'Depósito/Trasferencia',
//                        '3' => 'Pago en Sitio',
//                        '4' => 'Pago en OXXO',
                    ),
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "FechaCreacion" => Array(
                'table' => "comp",
                'category_id' => 2,
                'text' => 'Fecha Compra',
                'help-lb' => "",
                'data-hide' => "phone",
                'filter_options' => Array(
                    'is_date' => TRUE,
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "ReqFactura" => Array(
                'table' => "comp",
                'category_id' => 2,
                'text' => 'Requiere Factura',
                'help-lb' => "",
                'data-hide' => "phone,tablet",
                'filter_options' => Array(
                    'is_select' => TRUE,
                    'values' => Array(
                        '1' => 'Si',
                        '0' => 'No',
                    ),
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "Facturada" => Array(
                'table' => "comp",
                'category_id' => 2,
                'text' => 'Compra Facturada',
                'help-lb' => "",
                'data-hide' => "phone,tablet",
                'filter_options' => Array(
                    'is_select' => TRUE,
                    'values' => Array(
                        '1' => 'Si',
                        '0' => 'No',
                    ),
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "idVisitante" => Array(
                'table' => "vis",
                'category_id' => 3,
                'text' => 'ID Visitante',
                'help-lb' => "",
                'data-hide' => "phone",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "NombreCompleto" => Array(
                'table' => "vis",
                'category_id' => 3,
                'text' => $text['sas_nombreVisitante'],
                'help-lb' => "",
                'data-hide' => "phone",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Email" => Array(
                'table' => "vis",
                'category_id' => 3,
                'text' => $text['sas_emailVisitante'],
                'help-lb' => "",
                'data-hide' => "phone,tablet",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "DE_AreaPais" => Array(
                'table' => "vis",
                'category_id' => 3,
                'text' => 'Lada País',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'oculto',
                ),
                'is_visible' => TRUE,
            ),
            "DE_AreaCiudad" => Array(
                'table' => "vis",
                'category_id' => 3,
                'text' => 'Lada',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'oculto',
                ),
                'is_visible' => TRUE,
            ),
            "DE_Telefono" => Array(
                'table' => "vis",
                'category_id' => 3,
                'text' => 'Teléfono',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'only-numbers oculto',
                ),
                'is_visible' => TRUE,
            ),
            "RFC" => Array(
                'table' => "comp",
                'category_id' => 3,
                'text' => 'RFC',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'oculto',
                ),
                'is_visible' => FALSE,
            ),
            "RazonSocial" => Array(
                'table' => "comp",
                'category_id' => 3,
                'text' => 'Razón Social',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'oculto',
                ),
                'is_visible' => FALSE,
            ),
            "EmailFacturacion" => Array(
                'table' => "comp",
                'category_id' => 3,
                'text' => 'Email Facturación',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'oculto',
                ),
                'is_visible' => FALSE,
            ),
            "AreaCiudad" => Array(
                'table' => "comp",
                'category_id' => 3,
                'text' => 'Lada Facturación',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'oculto',
                ),
                'is_visible' => FALSE,
            ),
            "Telefono" => Array(
                'table' => "comp",
                'category_id' => 3,
                'text' => 'Teléfono Facturación',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'oculto',
                ),
                'is_visible' => FALSE,
            ),
            "Pais" => Array(
                'table' => "comp",
                'category_id' => 3,
                'text' => 'País',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'oculto',
                ),
                'is_visible' => FALSE,
            ),
            "Estado" => Array(
                'table' => "comp",
                'category_id' => 3,
                'text' => 'Estado',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'oculto',
                ),
                'is_visible' => FALSE,
            ),
            "Ciudad" => Array(
                'table' => "comp",
                'category_id' => 3,
                'text' => 'Ciudad',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'oculto',
                ),
                'is_visible' => FALSE,
            ),
            "Colonia" => Array(
                'table' => "comp",
                'category_id' => 3,
                'text' => 'Colonia',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'oculto',
                ),
                'is_visible' => FALSE,
            ),
            "Calle" => Array(
                'table' => "comp",
                'category_id' => 3,
                'text' => 'Calle',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'oculto',
                ),
                'is_visible' => FALSE,
            ),
            "NumeroExterior" => Array(
                'table' => "comp",
                'category_id' => 3,
                'text' => 'No Exterior',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'oculto',
                ),
                'is_visible' => FALSE,
            ),
            "NumeroInterior" => Array(
                'table' => "comp",
                'category_id' => 3,
                'text' => 'No Interior',
                'help-lb' => "",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'oculto',
                ),
                'is_visible' => FALSE,
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

}
