<?php

namespace ShowDashboard\DashboardBundle\Model;

use Symfony\Component\HttpFoundation\Request;
/**
 * Description of LoginModel
 *
 * @author Juan
 */
use Utilerias\SQLBundle\Model\SQLModel;

class DashboardModel {

    protected $SQLModel, $schema = "SAS", $allowedExts = array('jpg', 'jpeg', 'png', 'gif'), $mb = 1048576, $path_file = 'images/logos-co/';

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getConfiguracionInicial($args = array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("ConfiguracionInicial", $fields, $args);
    }

    public function getEvento($args = array()) {
        $fields = array('idEvento', 'idComiteOrganizador', 'Evento_ES', 'Evento_EN', 'Evento_FR', 'Evento_PT');
        return $this->SQLModel->selectFromTable("Evento", $fields, $args, array('"idEvento"' => 'ASC'));
    }

    public function getModulo($args = array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("ModuloIxpo", $fields, $args, array('"idModuloIxpo"' => 'ASC'));
    }

    public function getRecinto($args = array()) {
        $fields = array();
        return $this->SQLModel->selectFromTable("Recinto", $fields, $args, array('"idRecinto"' => 'ASC'));
    }

    /**
     * Trae la informacion de Ediciones disponibles para el usuario, consulta en UsuarioEdicion
     * @param type $args Generalmente "idUsuario"
     * @return type
     */
    public function getEventoEdicionUsuario($args = array()) {
        $qry = 'SELECT DISTINCT ';
        $qry .= ' ev."idEvento",';
        $qry .= ' ev."Evento_ES",';
        $qry .= ' ev."Evento_EN",';
        $qry .= ' ed."idEdicion",';
        $qry .= ' ed."Abreviatura",';
        $qry .= ' ed."Logo_ES_1",';
        $qry .= ' ed."Logo_EN_1",';
        $qry .= ' ed."Edicion_ES",';
        $qry .= ' ed."Edicion_EN",';
        $qry .= ' ed."FechaInicio",';
        $qry .= ' ed."FechaFin",';
        $qry .= ' ed."Ciudad",';
        $qry .= ' ed."LinkED",';
        $qry .= ' ed."LinkAE"';
        $qry .= ' FROM';
        $qry .= ' "SAS"."Evento" AS ev';
        $qry .= ' INNER JOIN "SAS"."Edicion" AS ed ON ev."idEvento" = ed."idEvento"';
        $qry .= ' INNER JOIN "SAS"."UsuarioEdicion" AS ue ON ed."idEdicion" = ue."idEdicion"';
        $qry .= $this->buildWhere($args);
        $qry .= ' ORDER BY ev."idEvento" ASC,';
        $qry .= ' ed."idEdicion" ASC';
        return $this->SQLModel->executeQuery($qry);
    }

    public function getEventoEdicion($args = array()) {
        $fields = array();
        $qry = 'SELECT ';
        $qry .= 'ev."idEvento", ev."Evento_ES", ev."Evento_EN", ev."Evento_FR", ev."Evento_PT", ';
        $qry .= 'ed."idEdicion", ed."idRecinto", ed."Logo_ES_1", ed."Logo_EN_1", ed."Logo_FR_1", ed."Logo_PT_1", ed."Edicion_ES", ed."Edicion_EN", ed."Edicion_FR", ed."Edicion_PT", ed."FechaInicio", ed."FechaFin", ed."Ciudad", ed."LinkED"  ';
        $qry .= 'FROM "SAS"."Evento" AS ev INNER JOIN "SAS"."Edicion" AS ed ';
        $qry .= 'ON ev."idEvento" = ed."idEvento" ';
        $qry .= 'INNER JOIN "SAS"."UsuarioEdicion" AS ue ON ed."idEdicion" = ue."idEdicion" ';
        $qry .= 'INNER JOIN "SAS"."Usuario" AS u ON ue."idUsuario" = u."idUsuario" ';
        $qry .= $this->buildWhere($args);
        //$qry .= 'WHERE u."idUsuario" = ' . $args['idUsuario'] . ' AND ev."idComiteOrganizador" = ' . $args['idComiteOrganizador'] . ' AND ed."idComiteOrganizador" = ' . $args['idComiteOrganizador'] . ' ';
        return $this->SQLModel->executeQuery($qry);
    }
    
    public function tabsPermission($user){
        $idUserType = $user["idTipoUsuario"];
        $qry = 'SELECT "TabsPermisos" FROM "SAS"."TipoUsuario" WHERE "idTipoUsuario" = ' . $idUserType;
        $result = $this->SQLModel->executeQuery($qry);
        
        return $result["data"][0]["TabsPermisos"];
    }
    
    public function getUserType($id){
        
    }

    public function breadcrumb($route, $lang) {
        /* Modulos */
        $modulos = Array();
        $breadcrumb = Array();
        $result_modulos = $this->getModulo();
        foreach ($result_modulos['data'] as $key => $value) {
            $modulos[$value['idModuloIxpo']] = $value;
            if ($value['Ruta'] === $route) {
                $id_modulo = $value['idModuloIxpo'];
                $id_padre = $value['idPadre'];
                $ruta = $value['Ruta'];
            }
        }
        $this->findBreadcrumbParent($id_modulo, $modulos, $lang, $breadcrumb, $ruta);
        $result = array(array("breadcrumb" => $breadcrumb[0], "route" => $ruta));
        return $result;
    }

    private function findBreadcrumbParent($id_modulo, $modulos, $lang, &$breadcrumb, $ruta) {
        array_unshift($breadcrumb, $modulos[$id_modulo]['Modulo_' . strtoupper($lang)]);
        if ($modulos[$id_modulo]['idPadre'] != 0) {
            $this->findBreadcrumbParent($modulos[$id_modulo]['idPadre'], $modulos, $lang, $breadcrumb);
        }
        return $breadcrumb;
    }

    public function buildWhere($where) {
        if (!(is_array($where) && count($where) > 0)) {
            return "";
        }
        $qry = " WHERE";
        $qry .= $this->buildParameters($where);
        return $qry;
    }

    private function buildParameters($param) {
        if (!(is_array($param) && count($param) > 0)) {
            return "";
        }

        $qry_param = " ";
        foreach ($param as $key => $value) {
            /* Si tiene parentesis el key, lo dejamos como viene */
            $qry_param .= $key;
            /* Si el valor tiene operadores relacionales, construimos la condicion */
            $operator = "=";
            if ((is_array($value) && count($value) > 0)) {
                $operator = $value['operator'];
                $value = $value['value'];
            }
            if (substr($value, 0, 1) == "'" && substr($value, -1) == "'") {
                $value = "'" . substr($value, 1, -1) . "'";
            }
            $qry_param .= ($value == "") ? " IS NULL " : $operator . $value;

            if (next($param)) {
                $qry_param .= ' AND ';
            }
        }
        return $qry_param;
    }

    public function trimValues(&$post) {
        if (count($post) == 0) {
            return $post;
        }

        foreach ($post as $key => $value) {
            $post[$key] = trim($value);
        }
        return $post;
    }

    public function is_defined($value) {
        if (isset($value) && !empty($value) && $value != NULL && $value != "") {
            return TRUE;
        }
        return FALSE;
    }

    public function uploadFiles($files, $general_text, $path = "") {
        $result = array("status" => TRUE, "data" => "");
        if (count($files) == 0) {
            return $result;
        }

        $files_tmp = array();
        foreach ($files as $key => $file) {
            if (isset($file['name']) && $file['name'] != "") {
                $error = FALSE;
                /* verificamos si se puede abrir el archivo */
                if ($file["error"] > 0) {
                    $error = $general_text['sas_errorArchivo'] . ' "' . $file['name'] . '"';
                }

                /* verificamos si es válida la extension */
                $temp = explode(".", $file['name']);
                $extension = strtolower(end($temp));

                if (!in_array(strtolower($extension), $this->allowedExts)) {
                    $error = '"' . $file['name'] . '" ' . $general_text['sas_archivoInvalido'];
                }

                /* verificamos el tamaño del archivo menor a 3 MB */
                if ($file["size"] > 3 * $this->mb) {
                    $find = array('{0}', '%file%');
                    $replace = array('3', ' "' . $file['name'] . '"');
                    $error = str_replace($find, $replace, $general_text['sas_tamanoInvalido']);
                }

                /* guardamos el archivo en nuestro servidor */
                if (!move_uploaded_file($file['tmp_name'], $this->path_file . $path . basename($file['name']))) {
                    $error = $general_text['sas_errorSubirArchivo'] . ' "' . $file['name'] . '"';
                }

                /* Si hubo algun error lo regresamos */
                if ($error) {
                    $result['status'] = FALSE;
                    $result['data'] = $error;
                    return $result;
                }
                $file = Array(
                    'name' => $file['name'],
                    'tmp_name' => $file['tmp_name'],
                    'type' => $extension,
                    'field' => $key,
                );

                $files_tmp[] = $file;
            }
        }

        $result['data'] = $files_tmp;
        return $result;
    }

    public function formatQuoteValue($args) {
        $args_tmp = array();
        foreach ($args as $key => $value) {
            /* Si el valor tiene operadores relacionales, construimos la condicion */
            if ((is_array($value) && count($value) > 0)) {
                if (substr($value['value'], 0, 1) == "'" && substr($value['value'], -1) == "'") {
                    $value['value'] = substr($value['value'], 1, -1);
                }
                $value['value'] = (empty($value['value'])) ? "" : "'" . $value['value'] . "'";
            } else {
                if (substr($value, 0, 1) == "'" && substr($value, -1) == "'") {
                    $value = substr($value, 1, -1);
                }
                $value = (empty($value)) ? "" : "'" . $value . "'";
            }
            $args_tmp[$key] = $value;
        }
        return $args_tmp;
    }

    public function createString($post) {
        $SParr = "";
        if (count($post) == 0) {
            return $SParr;
        }

        foreach ($post as $key => $value) {
            $value = trim($value);
            $SParr .= "$key:=" . str_replace("'", "\'", $value) . "|||";
        }
        return substr($SParr, 0, -3);
    }

    /**
     * Obtenemos los datos de conexion a la base del AE
     * @param type $args
     * @return type
     */
    public function getConexionAE($args) {
        $fields = array(
            'idConexion',
            'idEdicion',
            'idEvento',
            'Nombre',
            'Servidor',
            'Base',
            'Usuario',
            'Puerto',
            'Password',
            'FmConexion'
        );
        return $this->SQLModel->selectFromTable('Conexion', $fields, $args);
    }

    /**
     * Verificamos si tiene permiso sobre la plataforma en la edicion seleccionada
     * @param Request $request Objeto Request
     * @param type $idPlataformaIxpo id de la plataforma
     * @return type TRUE | FALSE
     */
    public function verificarPermisoPlataforma(Request $request, $idPlataformaIxpo) {
        $session = $request->getSession();
        $edicion = $session->get('edicion');
        $plataformasUsuario = $session->get('plataformas_usuario');

        /* Verificamos si existe el id de la plataforma en el arreglo disponible del usuario */
        return isset($plataformasUsuario[$edicion['idEdicion']][$idPlataformaIxpo]);
    }

    /**
     * Verificamos si tiene permiso sobre el modulo
     * @param Request $request Objeto Request
     * @return type breadcrumbs | FALSE
     */
    public function rastrearBreadcrumbs(Request $request, $route = NULL) {
        if ($route == NULL) {
            $route = $request->get('_route');
        }
        $session = $request->getSession();
        $edicion = $session->get('edicion');
        $modulosUsuario = $session->get('modulos_usuario');
        $idPlataformaIxpo = $session->get('idPlataformaIxpo');
        $breadcrumb = FALSE;
        return array_reverse($this->buscarRutaModulo($route, $modulosUsuario[$edicion['idEdicion']][$idPlataformaIxpo], $session->get('lang'), $breadcrumb));
    }

    private function buscarRutaModulo($route, $modulos, $lang = "es", $breadcrumb) {
        if (count($modulos) == 0) {
            return FALSE;
        }

        foreach ($modulos as $modulo) {
            if ($route == $modulo['Ruta']) {
                $crumb = array(
                    'idModuloIxpo' => $modulo['idModuloIxpo'],
                    'Ruta' => $modulo['Ruta'],
                    'Modulo_ES' => $modulo['Modulo_ES'],
                    'Modulo_EN' => $modulo['Modulo_EN'],
                    'Permisos' => $modulo['Permisos'],
                );
                $breadcrumb[] = $crumb;
                return $breadcrumb;
            }

            if ($modulo['SubModulos'] && count($modulo['SubModulos']) > 0) {
                $match_breadcrumb = $this->buscarRutaModulo($route, $modulo['SubModulos'], $lang, $breadcrumb);
                if ($match_breadcrumb) {
                    $crumb = array(
                        'idModuloIxpo' => $modulo['idModuloIxpo'],
                        'Ruta' => $modulo['Ruta'],
                        'Modulo_ES' => $modulo['Modulo_ES'],
                        'Modulo_EN' => $modulo['Modulo_EN'],
                        'Permisos' => $modulo['Permisos'],
                    );
                    $match_breadcrumb[] = $crumb;
                    return $match_breadcrumb;
                }
            }
        }

        return FALSE;
    }

}
