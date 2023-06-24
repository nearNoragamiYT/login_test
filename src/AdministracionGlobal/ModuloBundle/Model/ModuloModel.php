<?php

namespace AdministracionGlobal\ModuloBundle\Model;

/**
 * Description of Modulo
 *
 * @author Jesus
 */

use Utilerias\SQLBundle\Model\SQLModel;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class ModuloModel extends DashboardModel
{

    protected $SQLModel;

    public function __construct()
    {
        $this->SQLModel = new SQLModel();
    }

    public function getModulo($args = array())
    {
        $fields = array(
            'idModuloIxpo',
            'idPlataformaIxpo',
            'idPadre',
            'Nivel',
            'Orden',
            'Modulo_ES',
            'Modulo_EN',
            'Ruta',
            'Publicado'
        );
        $orderBy = array(
            'idPlataformaIxpo' => 'ASC',
            'Nivel' => 'ASC',
            'Orden' => 'ASC',
        );
        $result = $this->SQLModel->selectFromTable("ModuloIxpo", $fields, $args, $orderBy);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idModuloIxpo']] = $value;
        }
        $result['data'] = $data;
        return $result;
    }

    public function insertEditModuloIxpo($data)
    {
        /* Si trae el id editamos, de lo contrario insertamos */
        if ($this->is_defined($data['idModuloIxpo'])) {
            $where = array('idModuloIxpo' => $data['idModuloIxpo']);
            unset($data['idModuloIxpo']);
            return $this->SQLModel->updateFromTable('ModuloIxpo', $data, $where, 'idModuloIxpo');
        }
        unset($data['idModuloIxpo']);
        return $this->SQLModel->insertIntoTable('ModuloIxpo', $data, 'idModuloIxpo');
    }

    public function insertModulo($args = array())
    {
        $data = array(
            'idPlataformaIxpo' => $args['idPlataformaIxpo'],
            'idPadre' => $args['idPadre'],
            'Nivel' => $args['Nivel'],
            'Orden' => $args['Orden'],
            'Modulo_ES' => "'" . $args['Modulo_ES'] . "'",
            'Modulo_EN' => "'" . $args['Modulo_EN'] . "'",
            'Modulo_FR' => "'" . $args['Modulo_FR'] . "'",
            'Modulo_PT' => "'" . $args['Modulo_PT'] . "'",
            'Ruta' => "'" . $args['Ruta'] . "'",
            'Icono' => "'" . $args['Icono'] . "'",
        );
        $res_pg = $this->SQLModel->insertIntoTable("ModuloIxpo", $data, "idModuloIxpo");
        return $res_pg;
    }

    public function updateModulo($args = array())
    {
        $data = array(
            'idPlataformaIxpo' => "'" . $args['idPlataformaIxpo'] . "'",
            'idPadre' => "'" . $args['idPadre'] . "'",
            'Nivel' => "'" . $args['Nivel'] . "'",
            'Orden' => "'" . $args['Orden'] . "'",
            'Modulo_ES' => "'" . $args['Modulo_ES'] . "'",
            'Modulo_EN' => "'" . $args['Modulo_EN'] . "'",
            'Modulo_FR' => "'" . $args['Modulo_FR'] . "'",
            'Modulo_PT' => "'" . $args['Modulo_PT'] . "'",
            'Ruta' => "'" . $args['Ruta'] . "'",
            'Icono' => "'" . $args['Icono'] . "'",
        );
        $where = array('idModuloIxpo' => $args['idModuloIxpo']);
        $res_pg = $this->SQLModel->updateFromTable("ModuloIxpo", $data, $where, "idModuloIxpo");
        return $res_pg;
    }

    public function deleteModulo($args = array())
    {
        $where = array('idModuloIxpo' => $args['idModuloIxpo']);
        $res_pg = $this->SQLModel->deleteFromTable("ModuloIxpo", $where);
        return $res_pg;
    }

    public function getPlataforma($args = array())
    {
        $fields = array('idPlataformaIxpo', 'PlataformaIxpo', 'Prefijo');
        $result = $this->SQLModel->selectFromTable("PlataformaIxpo", $fields, $args, array('"idPlataformaIxpo"' => 'ASC'));
        if (!($result['status']) && count($result['data']) > 0) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idPlataformaIxpo']] = $value;
        }
        $result['data'] = $data;
        return $result;
    }
}
