<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ShowDashboard\CRM\EmpresasAsignadasBundle\Model;

/**
 * Description of EmpresasAsignadasCongfiguracion
 *
 * @author Eduardo
 */
class EmpresasAsignadasCongfiguracion {

    public function getEmpresasAsignadasCongfiguracion($vendedores, $empresasTipo, $textos) {
        $empresasTipo["-1"] = $textos['sas_todos'];
        $empresasTipo["NULL"] = $textos['sas_sinDefinir'];
        $vendedores["-1"] = $textos['sas_todos'];
        return Array(
            "fields" => Array(
                "idEmpresa" => Array(
                    'text' => $textos['sas_idEmpresa'],
                    'help-lb' => "",
                    'is_visible' => FALSE,
                    'is_filter' => TRUE,
                    'data-type' => "numeric",
                    'class' => "digits",
                    'data' => Array()
                ),
                "CodigoCliente" => Array(
                    'text' => $textos["sas_codigoCliente"],
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
                "idEmpresaTipo" => Array(
                    'text' => $textos["sas_tipoEmpresa"],
                    'help-lb' => "",
                    'is_visible' => TRUE,
                    'is_filter' => TRUE,
                    'data-type' => "select",
                    'data' => $empresasTipo
                ),
                "idUsuario" => Array(
                    'text' => $textos["sas_asesorAsignado"],
                    'help-lb' => "",
                    'is_visible' => TRUE,
                    'is_filter' => TRUE,
                    'order' => FALSE,
                    'data-type' => "select",
                    'data' => $vendedores
                )
            ),
            "config" => Array(
                "limit" => 200, //REQUERIDO límite de registros traidos por paginación
                "offset" => 0, //REQUERIDO inicio de donde se va a traer los registros
                "order_fields" => '"CodigoCliente"', //OPCIONAL si hay un orden de los campos si no lo hay poner null o ""
                "order" => "ASC", //OPCIONAL [ASC, DESC] Default ASC
                "idEmpresa" => "emp",
                "idUsuario" => "usu"
            )
        );
    }

}
