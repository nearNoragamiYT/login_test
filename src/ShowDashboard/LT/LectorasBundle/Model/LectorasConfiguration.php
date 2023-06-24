<?php

namespace ShowDashboard\LT\LectorasBundle\Model;

class LectorasConfiguration {

    public function getExhibitorsMetaData($textos) {
        return Array(
            "fields" => Array(
                "idEmpresa" => Array(
                    'text' => $textos['sas_idEmpresa'],
                    'help-lb' => "",
                    'is_visible' => TRUE,
                    'is_filter' => TRUE,
                    'data-type' => "numeric",
                    'data' => Array()
                ),
                "CodigoCliente" => Array(
                    'text' => $textos['sas_codigoCliente'],
                    'help-lb' => "",
                    'is_visible' => TRUE,
                    'is_filter' => TRUE,
                    'data-type' => "text",
                    'data' => Array()
                ),
                "DC_NombreComercial" => Array(
                    'text' => $textos["sas_nombreComercial"],
                    'help-lb' => "",
                    'is_visible' => TRUE,
                    'is_filter' => TRUE,
                    'data-type' => "text",
                    'data' => Array()
                ),
                // "ContratoEntidadFiscal" => Array(
                //     'text' => $textos["sas_razonSocial"],
                //     'help-lb' => "",
                //     'is_visible' => TRUE,
                //     'is_filter' => TRUE,
                //     'data-type' => "text",
                //     'data' => Array()
                // ),
                "NombreContacto" => Array(
                    'text' => $textos["sas_contacto"],
                    'help-lb' => "",
                    'is_visible' => TRUE,
                    'is_filter' => TRUE,
                    'data-type' => "text",
                    'data' => Array()
                ),
                "Email" => Array(
                    'text' => $textos["sas_email"],
                    'help-lb' => "",
                    'is_visible' => TRUE,
                    'is_filter' => TRUE,
                    'data-type' => "text",
                    'data' => Array()
                ),
                "EMSTDListadoStand" => Array(
                    'text' => $textos["sas_listadoStands"],
                    'help-lb' => "",
                    'is_visible' => TRUE,
                    'is_filter' => TRUE,
                    'data-type' => "numeric",
                    'data' => Array()
                ),
                "LectorasSolicitadas" => Array(
                    'text' => $textos["sas_lectorasSolicitadas"],
                    'help-lb' => "",
                    'is_visible' => TRUE,
                    'is_filter' => TRUE,
                    'data-type' => "numeric",
                    'data' => Array()
                ),
                "LectorasAsignadas" => Array(
                    'text' => $textos["sas_lectorasAsignadas"],
                    'help-lb' => "",
                    'is_visible' => TRUE,
                    'is_filter' => TRUE,
                    'data-type' => "numeric",
                    'data' => Array()
                ),
                "LectorasDevueltas" => Array(
                    'text' => $textos["sas_lectorasDevueltas"],
                    'help-lb' => "",
                    'is_visible' => TRUE,
                    'is_filter' => TRUE,
                    'data-type' => "numeric",
                    'data' => Array()
                ),
            )
        );
    }

    public function getColumnSLDefs($texts = Array(), $lang, $idEdicion) {
        return Array(
            "idEmpresa" => Array(
                'category_id' => 1,
                'text' => $texts['data']['sas_idEmpresa'],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => FALSE,
            ),
            "CodigoCliente" => Array(
                'category_id' => 1,
                'text' => $texts['data']['sas_codigoCliente'],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "DC_NombreComercial" => Array(
                'category_id' => 2,
                'text' => $texts['data']["sas_nombreComercial"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            // "ContratoEntidadFiscal" => Array(
            //     'category_id' => 2,
            //     'text' => $texts['data']["sas_razonSocial"],
            //     'help-lb' => "",
            //     'filter_options' => Array(
            //         'is_optional_column' => FALSE,
            //         'search_operator' => 'ilike',
            //     ),
            //     'is_visible' => TRUE,
            // ),
            "NombreContacto" => Array(
                'category_id' => 2,
                'text' => $texts['data']["sas_contacto"], #.' -- '. $texts['data']["sas_email"],
                'help-lb' => "",
                'data-hide' => "phone",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Email" => Array(
                'category_id' => 1,
                'text' => $texts['data']["sas_email"],
                'help-lb' => "",
                'data-class' => "expand",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "EMSTDListadoStand" => Array(
                'category_id' => 3,
                'text' => $texts['data']["sas_listadoStands"],
                'help-lb' => "",
                'data-hide' => "phone,tablet,small_screen",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "LectorasSolicitadas" => Array(
                'category_id' => 3,
                'text' => $texts['data']["sas_lectorasSolicitadasSiglas"],
                'help-lb' => "Lectoras Solicitadas",
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "RentasSitio" => Array(
                'category_id' => 3,
                'text' => "RS",
                'help-lb' => $texts['data']["sas_rentasEnStio"],
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "NumeroLectorasCortesia" => Array(
                'category_id' => 3,
                'text' => $texts['data']["sas_lectorasCortesiaSiglas"],
                'help-lb' => $texts['data']["sas_lectorasCortesia"],
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "SustitucionEquipo" => Array(
                'category_id' => 3,
                'text' => $texts['data']["sas_sustitucionLectorasSiglas"],
                'help-lb' => $texts['data']["sas_sustitucionLectoras"],
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "LectorasAsignadas" => Array(
                'category_id' => 3,
                'text' => $texts['data']["sas_lectorasAsignadasSiglas"],
                'help-lb' => $texts['data']["sas_lectorasAsignadas"],
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "LectorasRecibidas" => Array(
                'category_id' => 3,
                'text' => $texts['data']["sas_lectorasDevueltasSiglas"],
                'help-lb' => $texts['data']["sas_lectorasDevueltas"],
                'data-hide' => "always",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            // "FechaPrimerGuardado" => Array(
            //     'category_id' => 3,
            //     'text' => $texts['data']["sas_fechaSolicitud"],
            //     'help-lb' => "",
            //     'data-hide' => "always",
            //     'filter_options' => Array(
            //         'is_optional_column' => FALSE,
            //         'class' => 'only-numbers',
            //     ),
            //     'is_visible' => TRUE,
            // ),
            // "idFormaPago" => Array(
            //     'category_id' => 3,
            //     'text' => $texts['data']["sas_formaPago"],
            //     'help-lb' => "",
            //     'data-hide' => "always",
            //     'filter_options' => Array(
            //         'is_optional_column' => FALSE,
            //         'class' => 'only-numbers',
            //     ),
            //     'is_visible' => TRUE,
            // ),
            // "StatusPago" => Array(
            //     'category_id' => 3,
            //     'text' => $texts['data']["sas_statusPago"],
            //     'help-lb' => "",
            //     'data-hide' => "always",
            //     'filter_options' => Array(
            //         'is_optional_column' => FALSE,
            //         'class' => 'only-numbers',
            //     ),
            //     'is_visible' => TRUE,
            // ),
            // "FechaActualizacionStatusPago" => Array(
            //     'category_id' => 3,
            //     'text' => $texts['data']["sas_fechaPago"],
            //     'help-lb' => "",
            //     'data-hide' => "always",
            //     'filter_options' => Array(
            //         'is_optional_column' => FALSE,
            //         'class' => 'only-numbers',
            //     ),
            //     'is_visible' => TRUE,
            // ),
            // "Observaciones" => Array(
            //     'category_id' => 2,
            //     'text' => $texts['data']["sas_observaciones"],
            //     'help-lb' => "",
            //     'data-hide' => "always",
            //     'filter_options' => Array(
            //         'is_optional_column' => FALSE,
            //         'class' => 'only-numbers',
            //     ),
            //     'is_visible' => FALSE,
            // ),
        );
    }

    public function getEmpresaScannersMetaData($textos) {
        return Array(
            "idEmpresaScanner" => Array(
                'category_id' => 1,
                'text' => $textos['sas_idLectora'],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => FALSE,
            ),
            "CodigoScanner" => Array(
                'category_id' => 1,
                'text' => $textos["sas_codigoLectora"],
                'help-lb' => "",
                'data-class' => "expand",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "EtiquetaApp" => Array(
                'category_id' => 1,
                'text' => $textos["sas_correoEtiqueta"],
                'help-lb' => "",
                'data-class' => "expand",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "ScannerTipo" => Array(
                'category_id' => 2,
                'text' => $textos["sas_tipoLectora"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Status" => Array(
                'category_id' => 1,
                'text' => $textos["sas_statusLectora"],
                'help-lb' => "",
                'data-hide' => "phone",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Cortesia" => Array(
                'category_id' => 1,
                'text' => "Cortesía",
                'help-lb' => "",
                'data-hide' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
        );
    }

    public function getEmpresaScannersReporteMetaData($textos) {
        return Array(
            "idEmpresaScanner" => Array(
                'category_id' => 1,
                'text' => $textos['sas_idLectora'],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => FALSE,
            ),
            "idEmpresa" => Array(
                'category_id' => 1,
                'text' => $textos['sas_idEmpresa'],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "CodigoCliente" => Array(
                'category_id' => 1,
                'text' => $textos['sas_codigoCliente'],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "DC_NombreComercial" => Array(
                'category_id' => 2,
                'text' => $textos["sas_nombreComercial"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "DF_RazonSocial" => Array(
                'category_id' => 2,
                'text' => $textos["sas_razonSocial"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "ScannerTipo" => Array(
                'category_id' => 2,
                'text' => $textos["sas_tipoLectora"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "CodigoScanner" => Array(
                'category_id' => 2,
                'text' => $textos["sas_codigoLectora"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Status" => Array(
                'category_id' => 1,
                'text' => $textos["sas_statusLectora"],
                'help-lb' => "",
                'data-hide' => "phone",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "StatusPago" => Array(
                'category_id' => 1,
                'text' => $textos["sas_estatusPago"],
                'help-lb' => "",
                'data-hide' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
        );
    }

}
