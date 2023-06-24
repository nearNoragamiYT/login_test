<?php

namespace ShowDashboard\AE\Administracion\Configuracion\ConexionBDBundle\Model;

/**
 * Description of EditorFormaModel
 *
 * @author Javier
 */
use Utilerias\SQLBundle\Model\SQLModel;

class ConexionBDModel {

    protected $SQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
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
            'Soap',
            'FmConexion'
        );
        return $this->SQLModel->selectFromTable('Conexion', $fields, $args);
    }

    public function insertEditConexion($data) {
        /* Si trae el id editamos, de lo contrario insertamos */
        if ($data['idConexion'] != "") {
            $where = array('idConexion' => $data['idConexion']);
            unset($data['idConexion']);
            return $this->SQLModel->updateFromTable('Conexion', $data, $where, 'idConexion');
        }
        unset($data['idConexion']);
        return $this->SQLModel->insertIntoTable('Conexion', $data, 'idConexion');
    }

    public function deleteConexionAE($args) {
        return $this->SQLModel->deleteFromTable('Conexion', $args);
    }

}
