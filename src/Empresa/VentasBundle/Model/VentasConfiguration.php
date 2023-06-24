<?php

namespace Empresa\VentasBundle\Model;

use Empresa\VentasBundle\Model\VentasModel;

class VentasConfiguration {

    protected $EmpresaModel;

    public function __construct() {
        $this->EmpresaModel = new VentasModel();
    }

    public function getColumnCategories($texts = Array()) {
        return Array(
            Array(
                "category_id" => 1,
                "text" => $texts['data']['sas_ventas'],
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

    public function getColumnDefs($texts = Array(), $lang = "es", $idEdicion) {
        return Array(
            "CodigoCliente" => Array(
                'category_id' => 1,
                'text' => $texts['data']["sas_codigoCliente"],
                'help-lb' => "",
                'data-class' => "expand",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
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
//            "Eventos" => Array(
//                'category_id' => 2,
//                'text' => $texts['data']["sas_eventos"],
//                'help-lb' => "",
//                'filter_options' => Array(
//                    'is_optional_column' => FALSE,
//                    'search_operator' => 'ilike',
//                ),
//                'is_visible' => TRUE,
//            ),
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
            "idEtapa" => Array(
                'category_id' => 3,
                'text' => $texts['data']["sas_participacion"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_select' => TRUE,
                    'values' => Array(
                        '' => $texts['data']["sas_prospecto"],
                        '1' => $texts['data']["sas_precontrato"],
                        '2' => $texts['data']["sas_expositor"],
                        '3' => $texts['data']["sas_adicional"]
                    ),
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "idEmpresaTipo" => Array(
                'category_id' => 3,
                'text' => $texts['data']["sas_empresaTipo"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_select' => TRUE,
                    'values' => $this->EmpresaModel->getEmpresaTipo($lang),
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                    'class' => 'oculto',
                ),
                'is_visible' => TRUE,
            ),
//            "EMSTDListadoStand" => Array(
//                'category_id' => 3,
//                'text' => $texts['data']["sas_listadoStands"],
//                'help-lb' => "",
//                'data-hide' => "phone,tablet",
//                'filter_options' => Array(
//                    'is_optional_column' => FALSE,
//                    'search_operator' => 'ilike',
//                    'class' => 'only-numbers',
//                ),
//                'is_visible' => TRUE,
//            ),
//            "EMSTDMetrosCuadrados" => Array(
//                'category_id' => 3,
//                'text' => $texts['data']["sas_metrosContratados"],
//                'help-lb' => "",
//                'data-hide' => "phone,tablet",
//                'filter_options' => Array(
//                    'is_optional_column' => FALSE,
//                    'search_operator' => '=',
//                ),
//                'is_visible' => TRUE,
//            ),
//            "idPaquete" => Array(
//                'category_id' => 3,
//                'text' => $texts['data']["sas_paqueteMKF"],
//                'help-lb' => "",
//                'data-hide' => "phone,tablet",
//                'filter_options' => Array(
//                    'is_select' => TRUE,
//                    'values' => $this->EmpresaModel->getPaquetes($idEdicion, $lang),
//                    'is_optional_column' => FALSE,
//                    'class' => 'only-numbers',
//                ),
//                'is_visible' => TRUE,
//            ),
        );
    }

}
