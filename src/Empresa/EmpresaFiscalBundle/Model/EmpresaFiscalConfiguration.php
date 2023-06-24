<?php

namespace Empresa\EmpresaFiscalBundle\Model;

class EmpresaFiscalConfiguration {

    public function getFinancialMetaData($textos) {
        return Array(
            "idEmpresaEntidadFiscal" => Array(
                'category_id' => 1,
                'text' => $textos["sas_idEmpresaFiscal"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "DF_RazonSocial" => Array(
                'category_id' => 1,
                'text' => $textos["sas_razonSocial"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "DF_RFC" => Array(
                'category_id' => 1,
                'text' => $textos["sas_rfc"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "DF_RepresentanteLegal" => Array(
                'category_id' => 1,
                'text' => $textos["sas_representanteLegal"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => false,
            ),
            "DF_Email" => Array(
                'category_id' => 1,
                'text' => $textos["sas_dfEmail"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => false,
            ),
            "Principal" => Array(
                'category_id' => 1,
                'text' => $textos["sas_contactoPrincipal"],
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
