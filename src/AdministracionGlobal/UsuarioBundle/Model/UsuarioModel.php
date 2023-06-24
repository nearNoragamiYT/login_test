<?php

namespace AdministracionGlobal\UsuarioBundle\Model;

//use Utilerias\SQLBundle\Model\SQLModel;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class UsuarioModel extends DashboardModel {

    public function getUsuariosCO() {
        $qry = 'SELECT';
        $qry .= ' c."idContactoComiteOrganizador",';
        $qry .= ' c."idComiteOrganizador",';
        $qry .= ' c."Nombre",';
        $qry .= ' c."Puesto",';
        $qry .= ' c."Email" as "EmailContacto",';
        $qry .= ' co."Staff",';
        $qry .= ' co."ComiteOrganizador",';
        $qry .= ' u."idUsuario",';
        $qry .= ' u."Email",';
        $qry .= ' u."Password",';
        $qry .= ' u."TipoUsuario",';
        $qry .= ' u."ListaAcceso"';
        $qry .= ' FROM "' . $this->schema . '"."ContactoComiteOrganizador" c';
        $qry .= ' JOIN "' . $this->schema . '"."ComiteOrganizador" co';
        $qry .= ' ON c."idComiteOrganizador"=co."idComiteOrganizador"';
        $qry .= ' LEFT JOIN "' . $this->schema . '"."Usuario" u';
        $qry .= ' ON c."idContactoComiteOrganizador"=u."idContactoComiteOrganizador"';
        $qry .= ' ORDER BY c."idComiteOrganizador" ASC, c."idContactoComiteOrganizador" ASC';
        return $this->SQLModel->executeQuery($qry);
    }

    public function getComiteOrganizador($args = array()) {
        $fields = array('idComiteOrganizador', 'ComiteOrganizador', 'Licencias', 'Logo', 'Staff');
        $result = $this->SQLModel->selectFromTable("ComiteOrganizador", $fields, $args, array('"idComiteOrganizador"' => 'ASC'));
        $data = array();
        if (count($result['data']) > 0) {
            foreach ($result['data'] as $key => $value) {
                $data[$value['idComiteOrganizador']] = $value;
            }
        }
        $result['data'] = $data;
        return $result;
    }

    public function getEdicion($args = array()) {
        $fields = array('idEdicion', 'idEvento', 'idComiteOrganizador', 'Edicion_ES', 'Edicion_EN', 'Abreviatura');
        $result = $this->SQLModel->selectFromTable("Edicion", $fields, $args, array('"idEdicion"' => 'ASC'));
        if (!$result['status']) {
            return $result;
        }
        $data = array();
        if (count($result['data']) > 0) {
            foreach ($result['data'] as $key => $value) {
                $data[$value['idEdicion']] = $value;
            }
        }
        $result['data'] = $data;
        return $result;
    }

    public function getTipoUsuario($args = array()) {
        $fields = array('idTipoUsuario', 'TipoUsuario');
        $result = $this->SQLModel->selectFromTable("TipoUsuario", $fields, $args, array('"idTipoUsuario"' => 'ASC'));
        if (!$result['status']) {
            return $result;
        }
        $data = array();
        if (count($result['data']) > 0) {
            foreach ($result['data'] as $key => $value) {
                $data[$value['idTipoUsuario']] = $value;
            }
        }
        $result['data'] = $data;
        return $result;
    }

    public function getUsuario($args = array()) {
        $qry = 'SELECT *';
        $qry .= 'FROM "SAS"."vw_sas_Usuarios"';
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

    private function formatUsuarioEdicion($usuarioEdicion) {
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

    public function getUsuarioEdicion($args = array()) {
        $fields = array('idUsuario', 'idEdicion'/* , 'ListaAcceso' */);
        return $this->SQLModel->selectFromTable("UsuarioEdicion", $fields, $args, array('"idUsuario"' => 'ASC'));
    }

    public function insertEditUsuario($idUsuario = NULL, $stringData, $permisos = NULL) {
        $permisos_json = "null";
        if ($permisos) {
            $permisos_json = "'" . json_encode($permisos) . "'";
        }
        $qry = 'SELECT * FROM "SAS"."fn_sas_InsertaActualizaUsuarioEdicionModuloPermisos"(';
        if ($idUsuario) {
            $qry .= "$idUsuario,";
        } else {
            $qry .= "null,";
        }
        $qry .= "'$stringData',";
        $qry .= $permisos_json;
        $qry .= ')';
        return $this->SQLModel->executeQuery($qry);
    }

    public function formatearModuloPermisos($edicionesDisponibles, $modulos) {
        $permisos = NULL;
        if (count($edicionesDisponibles) == 0) {
            return $permisos;
        }

        $result_ediciones = $this->getEdicion();
        if (!$result_ediciones['status']) {
            throw new \Exception($result_ediciones['data'], 409);
        }
        $ediciones = $result_ediciones['data'];

        foreach ($edicionesDisponibles as $idEdicion) {
            if (isset($modulos[$idEdicion])) {
                foreach ($modulos[$idEdicion] as $idModulo => $modulo) {
                    $data = array();
                    $data['idEvento'] = $ediciones[$idEdicion]['idEvento'];
                    $data['idEdicion'] = $idEdicion;
                    $data['idModulo'] = $idModulo;
                    $data['Ver'] = $modulo['v'] ? "true" : "false";
                    $data['Editar'] = $modulo['e'] ? "true" : "false";
                    $data['Borrar'] = $modulo['b'] ? "true" : "false";
                    $permisos[] = $data;
                }
            }
        }
        return $permisos;
    }

    public function _insertEditUsuario($data) {
        $usuarioEdicion = $data['UsuarioEdicion'];
        unset($data['UsuarioEdicion']);
        /* Si trae el id editamos, de lo contrario insertamos */
        $idUsuario = $data['idUsuario'];
        unset($data['idUsuario']);
        if ($this->is_defined($idUsuario)) {
            $where = array('idUsuario' => $idUsuario);
            $result = $this->SQLModel->updateFromTable('Usuario', $data, $where, 'idUsuario');
        } else {
            $result = $this->SQLModel->insertIntoTable('Usuario', $data, 'idUsuario');
        }

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        return $this->insertEditUsuarioEdicion($result['data'][0]['idUsuario'], $usuarioEdicion);
    }

    public function insertEditUsuarioEdicion($idUsuario, $data) {
        /* Eliminamos los registros que tiene actualmente */
        $result_delete = $this->SQLModel->deleteFromTable('UsuarioEdicion', array('idUsuario' => $idUsuario));
        if (!$result_delete['status']) {
            return $result_delete;
        }

        $qry = 'INSERT INTO "SAS"."UsuarioEdicion"';
        $qry .= ' (';
        $qry .= ' "idUsuario",';
        $qry .= ' "idEdicion",';
        $qry .= ' "ListaAcceso"';
        $qry .= ' )';
        $qry .= ' VALUES';
        foreach ($data as $idEdicion => $modulos) {
            $qry .= ' ( ';
            $qry .= $idUsuario . ',';
            $qry .= $idEdicion . ',';
            $qry .= "'" . json_encode($modulos) . "'";
            $qry .= ' ),';
        }
        $qry = substr($qry, 0, -1);
        return $this->SQLModel->executeQuery($qry);
    }

    public function deleteUsuario($args) {
        return $this->SQLModel->deleteFromTable('Usuario', $args);
    }

    public function desactivarUsuario($args) {
        $values = array('Activo' => "false");
        return $this->SQLModel->updateFromTable('Usuario', $values, $args, "idUsuario");
    }

    public function reactivarUsuario($args) {
        $values = array('Activo' => "true");
        return $this->SQLModel->updateFromTable('Usuario', $values, $args, "idUsuario");
    }

    public function getModuloIxpo() {
        $qry = 'SELECT';
        $qry .= ' mi."idModuloIxpo",';
        $qry .= ' mi."idPlataformaIxpo",';
        $qry .= ' pi."PlataformaIxpo",';
        $qry .= ' pi."Prefijo",';
        $qry .= ' pi."Ruta" as "RutaPlataforma",';
        $qry .= ' pi."Icono" as "IconoPlataforma",';
        $qry .= ' mi."Nivel",';
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

    public function getModulos() {
        $result_modulo_ixpo = $this->getModuloIxpo();
        if (!($result_modulo_ixpo['status'] && count($result_modulo_ixpo['data']) > 0)) {
            return $result_modulo_ixpo;
        }

        $data = array();
        $plataformas = NULL;
        foreach ($result_modulo_ixpo['data'] as $key => $modulo) {
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

}
