<?php

namespace ShowDashboard\ED\Formas\ConfiguracionEDBundle\Model;

/**
 * @author Eduardo Cervantes <eduardoc@infoexpo.com.mx>
 */
class ConfiguracionEDModel {

    protected function App($textos) {
        $edApp = array(
            /* --- Es de suma importancia poner los idiomas por el orden del indice de ASC   --- */
            'Idioms' => array(/* 28 => "PT",35 => "FR", */134 => "ES", 221 => "EN"), // la abreviatura de los lenguajes
            'link_ed' => 'http://dev.infoexpo.com.mx/2016/ae/web/',
            "setFormsTableMetaData" => array(
                "idForma" => array(
                    'text' => "ID",
                    'help-lb' => "",
                    'values' => array(),
                    'visible' => TRUE,
                    'is_filter' => FALSE,
                    'className' => "td-id"
                ),
                "FO_NombreForma_ES" => array(
                    'text' => "Forma",
                    'help-lb' => "",
                    'values' => array(),
                    'visible' => TRUE,
                    'is_filter' => FALSE,
                    'className' => "td-name"
                ),
                "FO_FechaLimite" => array(
                    'text' => "Fecha Límite",
                    'help-lb' => "",
                    'values' => array(),
                    'visible' => TRUE,
                    'is_filter' => FALSE
                ),
                "completed" => array(
                    'text' => "Han llenado",
                    'help-lb' => "",
                    'values' => array(),
                    'visible' => TRUE,
                    'is_filter' => FALSE,
                    'className' => "td-details completed"
                ),
                "incompleted" => array(
                    'text' => "No han llenado",
                    'help-lb' => "",
                    'values' => array(),
                    'visible' => TRUE,
                    'is_filter' => FALSE,
                    'className' => "td-details incompleted"
                ),
                "FO_TipoLink" => array(
                    'text' => "Subir Archivo",
                    'help-lb' => "",
                    'values' => array(),
                    'visible' => TRUE,
                    'is_filter' => FALSE,
                    'className' => "td-tipolink"
                )
            ),
            "getExhibitorTableMetaData" =>
            Array(
                "idEmpresa" => Array(
                    'text' => "ID",
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => TRUE
                ),
                "idEtapa" => Array(
                    'text' => $textos["sas_participacion"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => TRUE
                ),
                "DC_NombreComercial" => Array(
                    'text' => $textos["sas_nombreEmpresa"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => TRUE
                ),
                "DC_Telefono" => Array(
                    'text' => $textos["sas_telefono"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => TRUE
                ),
                "NombreCompleto" => Array(
                    'text' => "Contacto Empresa(Nombre)",
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DC_Email" => Array(
                    'text' => "Contacto Empresa(Email)",
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DC_Celular" => Array(
                    'text' => "Contacto Empresa(Celular)",
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DC_Pais" => Array(
                    'text' => $textos["sas_pais"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => TRUE
                ),
                "DC_Estado" => Array(
                    'text' => $textos["sas_estado"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DC_CodigoPostal" => Array(
                    'text' => $textos["sas_codigoPostal"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DC_Ciudad" => Array(
                    'text' => $textos["sas_ciudad"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DC_Colonia" => Array(
                    'text' => $textos["sas_colonia"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DC_CalleNum" => Array(
                    'text' => $textos["sas_direccion"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DC_DescripcionEN" => Array(
                    'text' => $textos["sas_descripcionEN"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DC_DescripcionES" => Array(
                    'text' => $textos["sas_descripcionES"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DC_DescripcionFR" => Array(
                    'text' => $textos["sas_descripcionFR"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DC_DescripcionPT" => Array(
                    'text' => $textos["sas_descripcionPT"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DC_PaginaWeb" => Array(
                    'text' => $textos["sas_paginaWeb"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "EMSTD_ListadoStand" => Array(
                    'text' => $textos["sas_listadoStands"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => TRUE
                ),
                /* "NumeroGafetes" => Array(
                  'text'    =>  "Gafetes Asignados",
                  'help-lb' =>  "",
                  'values'  =>  Array(),
                  'visible' =>  TRUE
                  ), */
                "FechaCreacion" => Array(
                    'text' => $textos["sas_agregarFecha"],
                    'help-lb' => $textos["sas_formatoFecha"],
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "NombreCompleto" => Array(
                    'text' => "Contacto Manual (Nombre)",
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => TRUE
                ),
                "Email" => Array(
                    'text' => "Contacto Manual (Email)",
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => TRUE
                ),
                "Password" => Array(
                    'text' => "Contacto Manual (Password)",
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => TRUE
                ),
                "EmailEnviado" => Array(
                    'text' => $textos["sas_evioAcceso"],
                    'help-lb' => "",
                    'values' => Array(
                        Array(
                            'value' => '0',
                            'text' => $textos["sas_no"]
                        ),
                        Array(
                            'value' => '1',
                            'text' => $textos["sas_si"]
                        )
                    ),
                    'visible' => FALSE
                ),
                "FechaUltimoEnvio" => Array(
                    'text' => "Último envío",
                    'help-lb' => $textos["sas_formatoFecha"],
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "ED_Acceso" => Array(
                    'text' => $textos["sas_accesoManual"],
                    'help-lb' => "",
                    'values' => Array(
                        Array(
                            'value' => '0',
                            'text' => $textos["sas_no"]
                        ),
                        Array(
                            'value' => '1',
                            'text' => $textos["sas_si"]
                        )
                    ),
                    'visible' => FALSE
                ),
                "ED_FechaUltimoAcceso" => Array(
                    'text' => $textos["sas_ultimoAcceso"],
                    'help-lb' => $textos["sas_formatoFecha"],
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DF_RazonSocial" => Array(
                    'text' => "Facturación - " . $textos["sas_razonSocial"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DF_Representante_Legal" => Array(
                    'text' => "Contacto Facturación(Nombre)",
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DF_Email" => Array(
                    'text' => "Contacto Facturación(Email)",
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DF_Pais" => Array(
                    'text' => "Facturación - " . $textos["sas_pais"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DF_Estado" => Array(
                    'text' => "Facturación - " . $textos["sas_estado"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DF_Codigo_Postal" => Array(
                    'text' => "Facturación - " . $textos["sas_codigoPostal"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DF_Ciudad" => Array(
                    'text' => "Facturación - " . $textos["sas_ciudad"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DF_Colonia" => Array(
                    'text' => "Facturación - " . $textos["sas_colonia"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DF_Calle" => Array(
                    'text' => "Facturación - " . $textos["sas_direccion"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "DF_Telefono" => Array(
                    'text' => "Facturación - " . $textos["sas_telefono"],
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                ),
                "idPaquete" => Array(
                    'text' => "Paquete",
                    'help-lb' => "",
                    'values' => Array(),
                    'visible' => FALSE
                )
            )
        );
        return $edApp;
    }

    public function EDApp($text) {
        $app = $this->App($text);
        return $app;
    }

}
