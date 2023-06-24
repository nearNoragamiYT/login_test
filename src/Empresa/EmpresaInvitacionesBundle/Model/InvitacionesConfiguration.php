<?php

namespace Empresa\EmpresaInvitacionesBundle\Model;

class InvitacionesConfiguration{
    
    public function getInvitationsMetadata($section_texts, $general_texts){
        return Array(
            "Numero" => Array(
                'category_id' => 1,
                'text' => "#",
                //'values' => $empresas2, 
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "idCupon" => Array(
                'category_id' => 1,
                'text' => $section_texts["sas_idInvitacion"],                
                //'values' => $campos2,                    
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),                       
            "Cupon" => Array(
                'category_id' => 1,
                'text' => $section_texts["sas_invitacion"],
                //'values' => $categorias2,                 
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,            
            ),
            "idVisitante" => Array(
                'category_id' => 1,
                'text' => $section_texts["sas_invitado"],                
                //'values' => $campos2,                    
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ), 
            "StatusCupon" => Array(
                'category_id' => 1,
                'text' => $section_texts["sas_estatus"],
                'values' => Array("0" => $textos["sas_solicitudRechazada"], "1" => $textos["sas_solicitudCompletada"], "2" => $textos["sas_solicitudPendiente"]),
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "FechaGeneracion" => Array(
                'category_id' => 1,
                'text' => $section_texts["sas_fecha_generacion"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
//            "Cancelar" => Array(
//                'category_id' => 1,
//                'text' => $general_texts["sas_cancelar"],
//                //'values' => Array(""=>"Nada"),
//                'help-lb' => "",
//                'filter_options' => Array(
//                    'is_optional_column' => TRUE,
//                    'search_operator' => 'ilike',
//                ),
//                'is_visible' => TRUE,
//            ),                                    
        );
    }
}

