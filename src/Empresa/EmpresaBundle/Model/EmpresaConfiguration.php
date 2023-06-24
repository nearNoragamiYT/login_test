<?php

namespace Empresa\EmpresaBundle\Model;

use Empresa\EmpresaBundle\Model\EmpresaModel;

class EmpresaConfiguration {

    protected $EmpresaModel;

    public function __construct() {
        $this->EmpresaModel = new EmpresaModel();
    }

    public function getColumnCategories($texts = Array()) {
        return Array(
            Array(
                "category_id" => 1,
                "text" => $texts['data']['sas_empresas'],
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
            "idEtapa" => Array(
                'category_id' => 2,
                'text' => $texts['data']["sas_participacion"],
                'help-lb' => "",
                'data-hide' => "phone,tablet,small_screen",
                'filter_options' => Array(
                    'is_select' => TRUE,
                    'values' => Array(
                        '1' => $texts['data']["sas_solicitud"],
                        '2' => $texts['data']["sas_expositor"],
                        '3' => $texts['data']["sas_adicional"]
                    ),
                    'is_optional_column' => FALSE,
                    'search_operator' => '=',
                ),
                'is_visible' => TRUE,
            ),
            "idEmpresaTipo" => Array(
                'category_id' => 2,
                'text' => $texts['data']["sas_empresaTipo"],
                'help-lb' => "",
                'data-hide' => "phone,tablet,small_screen",
                'filter_options' => Array(
                    'is_select' => TRUE,
                    'values' => $this->EmpresaModel->getEmpresaTipo($idEdicion, $lang),
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
            "Nombre" => Array(
                'category_id' => 3,
                'text' => $texts['data']["sas_nombreContacto"] . " " . $texts['data']["sas_contacto"],
                'help-lb' => "",
                'data-hide' => "phone,tablet",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Email" => Array(
                'category_id' => 3,
                'text' => $texts['data']["sas_email"],
                'help-lb' => "",
                'data-hide' => "phone,tablet",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Password" => Array(
                'category_id' => 3,
                'text' => $texts['data']["sas_passwordContac"],
                'help-lb' => "",
                'data-hide' => "phone,tablet",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "Gafetes" => Array(
                'category_id' => 3,
                'text' => "Gafetes",
                'help-lb' => "",
                'data-hide' => "phone,tablet",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => FALSE,
            ),
//            "EMSTDListadoStand" => Array(
//                'category_id' => 2,
//                'text' => $texts['data']["sas_listadoStands"],
//                'help-lb' => "",
//                'data-hide' => "phone,tablet,small_screen",
//                'filter_options' => Array(
//                    'is_optional_column' => FALSE,
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
//                    'class' => 'only-numbers',
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
//            "ExpositorNuevo" => Array(
//                'category_id' => 3,
//                'text' => "Tipo Empresa",
//                'help-lb' => "",
//                'data-hide' => "phone,tablet",
//                'filter_options' => Array(
//                    'is_select' => TRUE,
//                    'values' => Array(
//                        '1' => "Nuevo",
//                        '0' => "Renovacion"
//                    ),
//                    'is_optional_column' => FALSE,
//                    'class' => 'only-numbers',
//                ),
//                'is_visible' => TRUE,
//            ),
        );
    }

}
