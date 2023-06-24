<?php

namespace CuentaBundle\Model;

/**
 * Description of LoginModel
 *
 * @author Javier
 */
use Utilerias\SQLBundle\Model\SQLModel;

class CuentaModel {

    protected $SQLModel, $schema = "SAS";

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getUsuario($args) {
        $qry = 'SELECT';
        $qry .= ' c."idContactoComiteOrganizador",';
        $qry .= ' c."idComiteOrganizador",';
        $qry .= ' c."Nombre",';
        $qry .= ' c."Puesto",';
        $qry .= ' c."Telefono",';
        $qry .= ' c."RedSocial",';
        $qry .= ' c."Email" as "EmailContacto",';
        $qry .= ' u."idUsuario",';
        $qry .= ' u."Email",';
        $qry .= ' u."Password",';
        $qry .= ' u."ListaAcceso"';
        $qry .= ' FROM "' . $this->schema . '"."ContactoComiteOrganizador" c';
        $qry .= ' LEFT JOIN "' . $this->schema . '"."Usuario" u';
        $qry .= ' ON c."idContactoComiteOrganizador"=u."idContactoComiteOrganizador"';
        $qry .= $this->buildWhere($args);
        return $this->SQLModel->executeQuery($qry);
    }

    public function getModuloIxpo() {
        $qry = 'SELECT';
        $qry .= ' mi."idModuloIxpo",';
        $qry .= ' mi."idPlataformaIxpo",';
        $qry .= ' pi."PlataformaIxpo",';
        $qry .= ' pi."Prefijo",';
        $qry .= ' pi."Ruta" as "RutaPlataforma",';
        $qry .= ' pi."Icono" as "IconoPlataforma",';
        $qry .= ' mi."idPadre",';
        $qry .= ' mi."Modulo_ES",';
        $qry .= ' mi."Modulo_EN",';
        $qry .= ' mi."Ruta",';
        $qry .= ' mi."Icono",';
        $qry .= ' mi."Orden"';
        $qry .= ' FROM "SAS"."ModuloIxpo" mi';
        $qry .= ' JOIN "SAS"."PlataformaIxpo" pi';
        $qry .= ' ON mi."idPlataformaIxpo" = pi."idPlataformaIxpo"';
        $qry .= ' ORDER BY mi."idPlataformaIxpo", mi."Orden"';
        return $this->SQLModel->executeQuery($qry);
    }

    public function getModulos($usuario) {
        $ListaPermisos = json_decode($usuario['ListaAcceso'], TRUE);
        $result_modulo_ixpo = $this->getModuloIxpo();
        if (!($result_modulo_ixpo['status'] && count($result_modulo_ixpo['data']) > 0)) {
            return $result_modulo_ixpo;
        }

        $data = array();
        $plataformas = NULL;
        foreach ($result_modulo_ixpo['data'] as $key => $modulo) {
            if (!array_key_exists($modulo['idModuloIxpo'], $ListaPermisos)) {
                continue;
            }
            if ($modulo['idPadre'] == "0") {
                if (!isset($data[$modulo['idPlataformaIxpo']][$modulo['idModuloIxpo']])) {
                    $modulo["SubModulos"] = NULL;
                    $data[$modulo['idPlataformaIxpo']][$modulo['idModuloIxpo']] = $modulo;
                    $plataformas[$modulo['idPlataformaIxpo']] = array(
                        "idPlataformaIxpo" => $modulo['idPlataformaIxpo'],
                        "PlataformaIxpo" => $modulo['PlataformaIxpo'],
                        "Prefijo" => $modulo['Prefijo'],
                        "Ruta" => $modulo['RutaPlataforma'],
                        "Icono" => $modulo['IconoPlataforma'],
                    );
                }
            } else {
                unset($modulo['PlataformaIxpo']);
                unset($modulo['Prefijo']);
                unset($modulo['RutaPlataforma']);
                unset($modulo['IconoPlataforma']);
                if (isset($data[$modulo['idPlataformaIxpo']][$modulo['idPadre']])) {
                    $data[$modulo['idPlataformaIxpo']][$modulo['idPadre']]["SubModulos"][$modulo['idModuloIxpo']] = $modulo;
                }
            }
        }

        $result_modulo_ixpo["data"] = $data;
        $result_modulo_ixpo["plataformas"] = $plataformas;
        return $result_modulo_ixpo;
    }

    private function buildWhere($where) {
        if (!(is_array($where) && count($where) > 0)) {
            return "";
        }
        $qry = " WHERE";
        $qry.= $this->buildParameters($where);
        return $qry;
    }

    private function buildParameters($param) {
        if (!(is_array($param) && count($param) > 0)) {
            return "";
        }

        $qry_param = " ";
        foreach ($param as $key => $value) {
            /* Si tiene parentesis el key, lo dejamos como viene */
            $qry_param.= $key;
            /* Si el valor tiene operadores relacionales, construimos la condicion */
            $operator = "=";
            if ((is_array($value) && count($value) > 0)) {
                $operator = $value['operator'];
                $value = $value['value'];
            }
            if (substr($value, 0, 1) == "'" && substr($value, -1) == "'") {
                $value = "'" . substr($value, 1, -1) . "'";
            }
            $qry_param.= ($value == "") ? " IS NULL " : $operator . $value;

            if (next($param)) {
                $qry_param.= ' AND ';
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

    public function editContacto($data) {
        $where = array('idContactoComiteOrganizador' => $data['idContactoComiteOrganizador']);
        unset($data['idContactoComiteOrganizador']);
        return $this->SQLModel->updateFromTable('ContactoComiteOrganizador', $data, $where, 'idContactoComiteOrganizador');
    }

    public function editUsuario($data) {
        $where = array('idUsuario' => $data['idUsuario']);
        unset($data['idUsuario']);
        return $this->SQLModel->updateFromTable('Usuario', $data, $where, 'idUsuario');
    }

}
