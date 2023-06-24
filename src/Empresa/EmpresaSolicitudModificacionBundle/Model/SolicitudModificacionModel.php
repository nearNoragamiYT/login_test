<?php

namespace Empresa\EmpresaSolicitudModificacionBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\PostgreSQLBundle\v9\PGSQLClient;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class SolicitudModificacionModel extends DashboardModel {

    protected $SQLModel, $PGSQLModel;
    protected $path_cache_ed = "../app/cache/ed/";

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getCompanyHeader($args = "") {
        $qry = ' SELECT e."idEmpresa", e."DC_NombreComercial", e."CodigoCliente" ';
        $qry .= ' FROM "SAS"."Empresa" e';
        $qry .= ' WHERE e."idEmpresa" = ' . $args['e."idEmpresa"'];
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $qry = ' SELECT ee."idEtapa", ee."EMSTDListadoStand", ee."idPaquete", ee."Nombre", ee."Email", ee."Password"';
            $qry .= ' FROM "SAS"."vw_sas_ObtenerEmpresas" ee';
            $qry .= ' WHERE ee."idEmpresa" = ' . $args['e."idEmpresa"'] . ' AND ee."idEdicion" = ' . $args['ee."idEdicion"'];
            $result2 = $this->SQLModel->executeQuery($qry);

            if (isset($result2['status']) && $result2['status'] == 1 && isset($result2['data'][0])) {
                $data = array_merge($result["data"][0], $result2["data"][0]);
                return $data;
            } else {
                return $result["data"][0];
            }
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    public function getEmpresaToken($args = "") {
        $qry = ' SELECT "Token"';
        $qry .= ' FROM "SAS"."EmpresaEdicion"';
        $qry .= ' WHERE "idEmpresa" = ' . $args['idEmpresa'] . ' AND "idEdicion" = ' . $args['idEdicion'] . ' AND "idEmpresa" = ' . $args['idEmpresa'];

        $result = $this->SQLModel->executeQuery($qry);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = $result['data'];
        $token = $data[0];

        return $token;
    }

    public function getPackages($args = "") {
        $qry = ' SELECT ' . $this->getPackagesFields();
        $qry .= ' FROM "SAS"."Paquete" p';
        $qry .= ' WHERE p."idEdicion" = ' . $args['p."idEdicion"'];
        $qri .= ' ORDER BY p."idPaquete"';
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $data = Array();
            if (COUNT($result['data']) > 0) {
                foreach ($result['data'] as $value) {
                    $data[$value['idPaquete']] = $value;
                }
            }
            return $data;
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    private function getPackagesFields() {
        $fields = '';
        $fields .= ' p."idPaquete",';
        $fields .= ' p."PaqueteES",';
        $fields .= ' p."PaqueteEN",';
        $fields .= ' p."PaquetePT",';
        $fields .= ' p."PaqueteFR" ';
        return $fields;
    }

    public function getCategorias($args = "") {
        $qry = 'SELECT "idCategoria", "NombreCategoriaES" ';
        $qry .= 'FROM "SAS"."Categoria" as "CAT" ';
        $qry .= 'WHERE ';
        $qry .= '"CAT"."idEvento" = ' . $args['idEvento'] . ' AND ';
        $qry .= '"CAT"."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' ORDER BY "Orden" ASC;';
        $result = $this->SQLModel->executeQuery($qry);

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idCategoria']] = $value;
        }
        $result['data'] = $data;
        return $result['data'];
    }

    public function getCamposForma($args = "") {
        $qry = 'SELECT "CamposJSON" ';
        $qry .= 'FROM "SAS"."Forma" ';
        $qry .= 'WHERE "idForma" = ' . $args["idForma"] . ' AND ';
        $qry .= '"idEvento" = ' . $args['idEvento'] . ' AND ';
        $qry .= '"idEdicion" = ' . $args['idEdicion'];
        $qry .= ';';
        $result = $this->SQLModel->executeQuery($qry);

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $result['data'];
    }

    public function getRegistrosEmpresas($where) {
        $qry = ' SELECT e."idEmpresa", e."DC_NombreComercial", ee."Token"';
        $qry .= ' FROM "SAS"."Empresa" e';
        $qry .= ' INNER JOIN "SAS"."EmpresaEdicion" ee ON e."idEmpresa" = ee."idEmpresa"';
        $qry .= ' WHERE ee."idEtapa" = 2 AND ee."idEdicion" = ' . $where['idEdicion'] . ' AND ee."idEvento" = ' . $where['idEvento'];
        $result = $this->SQLModel->executeQuery($qry);

        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idEmpresa']] = $value;
        }
        $result['data'] = $data;
        return $result['data'];
    }

    public function getModificationRequestMetaData($textos = "", $categorias = "", $campos = "", $empresas = "") {

        //---------Creamos un arreglo de Empresas
        $empresas2 = Array();
        foreach ($empresas as $key => $value) {
            $empresas2[$value['idEmpresa']] = $value["DC_NombreComercial"];
        }
        //print_r($empresas2);die('X_x');
        //---------Creamos un arreglo de Campos
        $campos2 = Array();
        foreach ($campos as $key => $value) {
            $campos2[$value['idCampoModificacion']] = $value["NombreCampoES"];
        }

        /* ---------Creamos un arreglo de Categorias------------------ */
        //forma 1
        $categorias2 = Array();
        foreach ($categorias as $key => $value) {
            $categorias2[$value['idCategoria']] = $value["NombreCategoriaES"];
        }
        $categorias2['x'] = "No especificado";
        //$categorias2['No especificado'] = "No especificado";
        /* //forma2
          array_push($categorias, Array("" =>'',"NombreCategoriaES"=>"No lleno"));
          //print_r($categorias);die('X_x');

          $categorias2= Array();
          foreach ($categorias as $key => $value) {
          $categorias2[$value['idCategoria']] = $value["NombreCategoriaES"];
          }
         */
        return Array(
            "Solicitud" => Array(
                'category_id' => 1,
                'text' => $textos["sas_solicitud"],
                'values' => $empresas2,
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => TRUE,
            ),
            "idCampoModificacion" => Array(
                'category_id' => 1,
                'text' => $textos["sas_campoModificacion"],
                'values' => $campos2,
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "CategoriaPrincipal" => Array(
                'category_id' => 1,
                'text' => $textos["sas_categoriaPrincipal"],
                'values' => $categorias2,
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => FALSE,
            ),
            "CategoriaSecundaria" => Array(
                'category_id' => 1,
                'text' => $textos["sas_categoriaSecundaria"],
                'values' => $categorias2,
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => FALSE,
            ),
            "OtraCategoria" => Array(
                'category_id' => 1,
                'text' => $textos["sas_categoriaOtra"],
                //'values' => Array(""=>"Nada"),
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => FALSE,
            ),
            "Observacion" => Array(
                'category_id' => 1,
                'text' => $textos["sas_observacion"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "StatusSolicitudCambio" => Array(
                'category_id' => 1,
                'text' => $textos["sas_estatus"],
                'values' => Array("0" => $textos["sas_solicitudRechazada"], "1" => $textos["sas_solicitudCompletada"], "2" => $textos["sas_solicitudPendiente"]),
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => FALSE,
                    'class' => 'only-numbers',
                ),
                'is_visible' => true,
            ),
            "ObservacionComite" => Array(
                'category_id' => 1,
                'text' => $textos["sas_observacionComite"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            ),
            "FechaSolicitudCambio" => Array(
                'category_id' => 1,
                'text' => $textos["sas_fechaSolicitud"],
                'help-lb' => "",
                'filter_options' => Array(
                    'is_optional_column' => TRUE,
                    'search_operator' => 'ilike',
                ),
                'is_visible' => TRUE,
            )
        );
    }

    //FUNCIONES DEL SNIPPET

    public function getEmpresaForma($condition) {
        $qry = 'SELECT * ';
        $qry .= 'FROM "SAS"."EmpresaForma" ';
        $qry .= 'WHERE "idEmpresa" = ' . $condition["idEmpresa"] . ' AND ';
        if (key_exists("idForma", $condition) && $condition['idForma'] != null) {
            $qry .= '"idForma" = ' . $condition['idForma'] . ' AND ';
        }
        $qry .= '"idEvento" = ' . $condition['idEvento'] . ' AND ';
        $qry .= '"idEdicion" = ' . $condition['idEdicion'];
        $qry .= ';';
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception('Error! no get EmpresaForma ' . $result['error'], 409);
        }
        $EF = array();
        $EF['status'] = $result['status'];
        foreach ($result['data'] as $EMFO) {
            /* ---  Si un usuario del comite modifico la forma consulta el detalle en EmpresaFormaLog  --- */
            if ($EMFO['ModificacionComite'] == 1 && key_exists("idForma", $condition) && $condition['idForma'] != null) {
                $qry = 'SELECT "Usuario", "FechaCreacion", "Accion" FROM "SAS"."EmpresaFormaLog" WHERE ';
                $qry .= '"idEdicion" = ' . $condition['idEdicion'] . ' AND  "idEmpresa" = ' . $condition['idEmpresa'] . ' AND "idForma" = ' . $condition['idForma'] . ' ORDER BY "FechaCreacion" DESC LIMIT 1';

                $resultUser = $this->SQLModel->executeQuery($qry);

                if (!$resultUser['status']) {
                    throw new \Exception('Error! Not get user of committe ' . $resultUser['error']);
                }
                $EMFO['UsuarioComiteModifico'] = $resultUser['data'][0];
            }
            $EF[$EMFO['idForma']] = $EMFO;
        }
        return $EF;
    }

    //FUNCIONES DEL SNIPPET

    public function getDetalleForma($condition) {
        $qry = 'SELECT "idEmpresa", "DetalleForma" ';
        $qry .= 'FROM "SAS"."EmpresaForma" ';
        $qry .= 'WHERE "idEdicion" = ' . $condition["idEdicion"];
        if (key_exists("idForma", $condition) && $condition['idForma'] != null) {
            $qry .= ' AND "idForma" = ' . $condition['idForma'];
            $qry .= ' AND "DetalleForma"::VARCHAR <> ' . "'{}'";
        }
        $qry .= ' AND "idEvento" = ' . $condition['idEvento'];
        $qry .= ';';
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        foreach ($result['data'] as $EMFO) {
            $EF[$EMFO['idEmpresa']] = $EMFO;
            //$EF[$EMFO['DetalleForma']] = $EMFO;
        }
        return $EF;
    }

    //Actualiza la Solicitud de Modificacion
    public function updateEmpresaForma($args = "", $data) {
        $result = $this->SQLModel->updateFromTable("EmpresaForma", array('DetalleForma' => "'" . $data . "'"), $args);
        return $result;
    }

}
