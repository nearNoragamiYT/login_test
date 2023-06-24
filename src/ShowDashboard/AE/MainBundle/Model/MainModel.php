<?php

namespace ShowDashboard\AE\MainBundle\Model;

/**
 * Description of EditorFormaModel
 *
 * @author Javier
 */
use ShowDashboard\DashboardBundle\Model\DashboardModel;
//use Utilerias\SQLBundle\Model\SQLModel;
//use Symfony\Component\HttpFoundation\Session\Session;

class MainModel extends DashboardModel {

    protected $SQLModelAE, $schemaAE = "AE";

    public function issetHandlerAE() {
        if ($this->SQLModelAE == NULL) {
            return FALSE;
        }
        return TRUE;
    }

    public function getConfiguracion($fields = array(), $args = array()) {
        if ($this->SQLModelAE == NULL) {
            return array('status' => TRUE, 'data' => NULL);
        }
        return $this->SQLModelAE->selectFromTable('Configuracion', $fields, $args);
    }

    public function deleteCacheAE($url, $folder) {
        /* Si tiene la ruta del AE, eliminamos su cache */
        if ($url != "") {
            $url .= "utilerias/borrar-cache";
            if ($folder != "") {
                $url .= "/" . $folder;
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);
        }
    }

}
