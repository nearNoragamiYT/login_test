<?php

namespace LoginBundle\Model;

/**
 * Description of LoginModel
 *
 * @author Javier
 */

use Utilerias\SQLBundle\Model\SQLModel;

class LoginModel
{

    protected $SQLModel;

    public function __construct()
    {
        $this->SQLModel = new SQLModel();
    }

    public function getUsuario($args = array())
    {
        $qry = 'SELECT';
        $qry .= ' "idUsuario",';
        $qry .= ' "Email",';
        $qry .= ' "Nombre",';
        $qry .= ' "Puesto",';
        $qry .= ' "idTipoUsuario",';
        $qry .= ' "TipoUsuario",';
        $qry .= ' "idComiteOrganizador",';
        $qry .= ' "Rol",';
        $qry .= ' "TokenPassword",';
        $qry .= ' "Activo",';
        $qry .= ' "UsuarioEdicion"';
        $qry .= ' FROM "SAS"."vw_sas_Usuarios"';
        $qry .= $this->SQLModel->buildWhere($args);
        $result = $this->SQLModel->executeQuery($qry);

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }

        foreach ($result['data'] as $key => $usuario) {
            $usuario['Ediciones'] = $this->formatUsuarioEdicion($usuario['UsuarioEdicion']);
            unset($usuario['UsuarioEdicion']);
            $result['data'][$key] = $usuario;
        }
        return $result;
    }

    private function formatUsuarioEdicion($usuarioEdicion)
    {
        $usuarioEdicion = json_decode($usuarioEdicion, TRUE);
        $data = NULL;
        if (count($usuarioEdicion) == 0) {
            return $data;
        }

        foreach ($usuarioEdicion as $edicion) {
            $data[$edicion['idEdicion']] = NULL;
            if (count($edicion['Permisos'])) {
                foreach ($edicion['Permisos'] as $permiso) {
                    $data[$edicion['idEdicion']][$permiso['idModulo']] = $permiso;
                }
            }
        }
        return $data;
    }

    public function findUser($args)
    {
        $qry = 'SELECT';
        $qry .= ' u."idUsuario",';
        $qry .= ' u."idContactoComiteOrganizador",';
        $qry .= ' u."idPlantillaAcceso",';
        $qry .= ' u."Email",';
        $qry .= ' u."TokenPassword",';
        $qry .= ' u."TipoUsuario",';
        $qry .= ' u."idTipoUsuario",';
        $qry .= ' u."idComiteOrganizador",';
        $qry .= ' u."ListaAcceso",';
        $qry .= ' (SELECT json_agg("idEdicion"::text)';
        $qry .= ' FROM "SAS"."UsuarioEdicion" ue';
        $qry .= ' WHERE ue."idUsuario" = u."idUsuario")';
        $qry .= ' as "Ediciones"';
        $qry .= ' FROM "SAS"."Usuario" u';
        $qry .= $this->SQLModel->buildWhere($args);
        return $this->SQLModel->executeQuery($qry);
    }

    public function getComiteOrganizador($args = array())
    {
        $fields = array('idComiteOrganizador', 'ComiteOrganizador', 'Logo', 'Licencias');
        return $this->SQLModel->selectFromTable("ComiteOrganizador", $fields, $args, array('"idComiteOrganizador"' => 'ASC'));
    }

    public function getModuloIxpo()
    {
        $qry = 'SELECT';
        $qry .= ' mi."idModuloIxpo",';
        $qry .= ' mi."idPlataformaIxpo",';
        $qry .= ' pi."PlataformaIxpo",';
        $qry .= ' pi."Prefijo",';
        $qry .= ' pi."Ruta" as "RutaPlataforma",';
        //$qry .= ' pi."RutaConfiguracion" as "RutaConfiguracionPlataforma",';
        $qry .= ' mi."Nivel",';
        $qry .= ' mi."idPadre",';
        $qry .= ' mi."Modulo_ES",';
        $qry .= ' mi."Modulo_EN",';
        $qry .= ' mi."Ruta",';
        $qry .= ' mi."Icono",';
        $qry .= ' mi."Publicado",';
        $qry .= ' mi."Orden"';
        $qry .= ' FROM "SAS"."ModuloIxpo" mi';
        $qry .= ' JOIN "SAS"."PlataformaIxpo" pi';
        $qry .= ' ON mi."idPlataformaIxpo" = pi."idPlataformaIxpo"';
        $qry .= ' ORDER BY mi."idPlataformaIxpo", mi."Orden"';
        return $this->SQLModel->executeQuery($qry);
    }

    public function getModulosUsuario($user)
    {
        $listaAccesos = json_decode($user['ListaAcceso'], TRUE);

        $result_modulo_ixpo = $this->getModuloIxpo();
        if (!($result_modulo_ixpo['status'] && count($result_modulo_ixpo['data']) > 0)) {
            return $result_modulo_ixpo;
        }

        $data = array();
        $plataformas = NULL;
        foreach ($result_modulo_ixpo['data'] as $key => $modulo) {
            /* Si no es super admin y no tiene permiso sobre el modulo, lo omitimos */
            if ($user['TipoUsuario'] != "1" && !array_key_exists($modulo['idModuloIxpo'], $listaAccesos)) {
                continue;
            }
            if ($modulo['Nivel'] == "1") {
                $modulo["SubModulos"] = NULL;
                $data[$modulo['idPlataformaIxpo']][$modulo['idModuloIxpo']] = $modulo;

                $plataforma = array();
                $plataforma["idModuloIxpo"] = $modulo['idModuloIxpo'];
                $plataforma["idPlataformaIxpo"] = $modulo['idPlataformaIxpo'];
                $plataforma["PlataformaIxpo"] = $modulo['PlataformaIxpo'];
                $plataforma["Prefijo"] = $modulo['Prefijo'];
                $plataforma["Ruta"] = $modulo['RutaPlataforma'];
                $plataforma["Icono"] = $modulo['IconoPlataforma'];
                $plataformas[$modulo['idPlataformaIxpo']] = $plataforma;
                unset($result_modulo_ixpo['data'][$key]);
            }
        }

        foreach ($result_modulo_ixpo['data'] as $key => $modulo) {
            if ($modulo['Nivel'] == "2" && isset($data[$modulo['idPlataformaIxpo']][$modulo['idPadre']])) {
                unset($modulo['PlataformaIxpo']);
                unset($modulo['Prefijo']);
                unset($modulo['RutaPlataforma']);
                unset($modulo['IconoPlataforma']);
                $data[$modulo['idPlataformaIxpo']][$modulo['idPadre']]["SubModulos"][$modulo['idModuloIxpo']] = $modulo;
                unset($result_modulo_ixpo['data'][$key]);
            }
        }
        $result_modulo_ixpo["data"] = $data;
        $result_modulo_ixpo["plataformas"] = $plataformas;
        return $result_modulo_ixpo;
    }

    /**
     * Obtenemos los Modulos activos por Edicion del usuario
     * @param type $user
     * @return type
     */
    public function getModulosEdicionUsuario($user)
    {
        $ediciones = $user['Ediciones'];
        /* Si no tiene ediciones activas el usuario, regresamos el arreglo vacio */
        if (count($ediciones) == 0) {
            return array("status" => TRUE, 'data' => NULL, "plataformas" => NULL);
        }

        $result_modulo_ixpo = $this->getModuloIxpo();
        if (!($result_modulo_ixpo['status'] && count($result_modulo_ixpo['data']) > 0)) {
            return $result_modulo_ixpo;
        }
        $modulo_ixpo = $result_modulo_ixpo['data'];

        /* Filtramos las plataformas disponibles para el usuario */
        $plataformas = NULL;
        foreach ($ediciones as $idEdicion => $modulos) {
            $plataformas[$idEdicion] = $this->filtrarPlataformasModuloDisponibles($modulos, $modulo_ixpo);
        }

        /* Filtramos los modulos disponibles para el usuario */
        $data = NULL;
        foreach ($ediciones as $idEdicion => $modulos) {
            $data[$idEdicion] = $this->filtrarModulosDisponibles($modulos, $modulo_ixpo);
        }
        $result_modulo_ixpo["data"] = $data;
        $result_modulo_ixpo["plataformas"] = $plataformas;
        return $result_modulo_ixpo;
    }

    /**
     * Filtra las plataformas disponibles para el usuario
     * @param type $modulos Arreglo Modulos disponibles del usuario
     * @param type $modulo_ixpo Arreglo Modulos Ixpo
     * @return type Arreglo Plataformas disponibles para el usuario
     */
    private function filtrarPlataformasModuloDisponibles($modulos, $modulo_ixpo)
    {
        $plataformas = NULL;
        /* Si no hay modulos disponibles para la edicion regresamos vacio */
        if (count($modulos) == 0) {
            return $plataformas;
        }

        /* Buscamos las plataformas disponibles para el usuario */
        foreach ($modulos as $idModulo => $permisos) {
            /* multidimensional array search by value 
             * Buscamos la informacion de los modulos disponibles para el usuario
             */
            $key = array_search($idModulo, array_column($modulo_ixpo, 'idModuloIxpo'));
            $modulo = $modulo_ixpo[$key];

            if (count($modulo) > 0 && $modulo['Nivel'] == "1" && !isset($plataformas[$modulo['idPlataformaIxpo']])) {
                $plataforma = array();
                $plataforma["idModuloIxpo"] = $modulo['idModuloIxpo'];
                $plataforma["idPlataformaIxpo"] = $modulo['idPlataformaIxpo'];
                $plataforma["PlataformaIxpo"] = $modulo['PlataformaIxpo'];
                $plataforma["Prefijo"] = $modulo['Prefijo'];
                $plataforma["Ruta"] = $modulo['RutaPlataforma'];
                $plataforma["RutaConfiguracion"] = $modulo['RutaConfiguracionPlataforma'];
                $plataformas[$modulo['idPlataformaIxpo']] = $plataforma;
                unset($modulos[$idModulo]);
            }
        } //fin for modulos
        return $plataformas;
    }

    /**
     * Filtra los modulos disponibles para el usuario
     * @param type $modulos Arreglo Modulos disponibles del usuario
     * @param type $modulo_ixpo Arreglo Modulos Ixpo
     * @return type Arreglo Modulos disponibles para el usuario
     */
    private function filtrarModulosDisponibles($modulos, $modulo_ixpo)
    {
        $data = NULL;
        /* Si no hay modulos disponibles para la edicion regresamos vacio */
        if (count($modulos) == 0) {
            return $data;
        }

        $modulo_nivel = NULL;
        /* Filtramos los modulos disponibles para el usuario organizados por nivel */
        foreach ($modulos as $idModulo => $permisos) {
            /* Buscamos la informacion de los modulos disponibles para el usuario */
            $key = array_search($idModulo, array_column($modulo_ixpo, 'idModuloIxpo'));
            $modulo = $modulo_ixpo[$key];
            if (count($modulo) > 0) {
                unset($permisos["idModulo"]);
                unset($permisos["Ruta"]);
                $modulo["Permisos"] = $permisos;
                $modulo_nivel[$modulo["Nivel"]][] = $modulo;
            }
        }

        // Si no coinciden modulos encontrados, regresamos null
        if ($modulo_nivel == NULL) {
            return $modulo_nivel;
        }

        /* Comenzamos con los del nivel 1 */
        if (isset($modulo_nivel[1])) {
            foreach ($modulo_nivel[1] as $modulo) {
                $modulo["SubModulos"] = NULL;
                $data[$modulo["idPlataformaIxpo"]][$modulo['idModuloIxpo']] = $modulo;
            }
        }
        /* Asignamos los modulos de nivel 2 */
        if (isset($modulo_nivel[2])) {
            foreach ($modulo_nivel[2] as $modulo) {
                if (isset($data[$modulo["idPlataformaIxpo"]][$modulo['idPadre']])) {
                    $modulo["SubModulos"] = NULL;
                    $data[$modulo["idPlataformaIxpo"]][$modulo['idPadre']]["SubModulos"][$modulo['idModuloIxpo']] = $modulo;
                }
            }
        }

        return $data;
    }

    public function trimValues(&$post)
    {
        if (count($post) == 0) {
            return $post;
        }

        foreach ($post as $key => $value) {
            $post[$key] = trim($value);
        }
        return $post;
    }

    public function is_defined($value)
    {
        if (isset($value) && !empty($value) && $value != NULL && $value != "") {
            return TRUE;
        }
        return FALSE;
    }

    public function updateUserData($data, $args)
    {
        return $this->SQLModel->updateFromTable("Usuario", $data, $args);
    }

    public function is_ssl()
    {
        if (isset($_SERVER['HTTPS'])) {
            if ('on' == strtolower($_SERVER['HTTPS']))
                return true;
            if ('1' == $_SERVER['HTTPS'])
                return true;
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }
        return false;
    }

    public function getComiteOrganizadorConfiguracion()
    {
        $qry = 'SELECT';
        $qry .= ' co."idComiteOrganizador",';
        $qry .= ' co."ComiteOrganizador",';
        $qry .= ' co."Logo"';
        $qry .= ' FROM';
        $qry .= ' "SAS"."ComiteOrganizador" co ';
        $qry .= ' JOIN "SAS"."ConfiguracionInicial" ci ';
        $qry .= ' ON co."idComiteOrganizador"=ci."idComiteOrganizador"';
        $qry .= ' ORDER BY ci."idConfiguracionInicial" ASC';
        return $this->SQLModel->executeQuery($qry);
    }

    public function getEvento($args = array())
    {
        $fields = array('idEvento', 'Evento_ES', 'Evento_EN');
        return $this->SQLModel->selectFromTable("Evento", $fields, $args, array('"idEvento"' => 'ASC'));
    }
}
