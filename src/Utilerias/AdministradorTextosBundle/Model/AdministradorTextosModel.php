<?php

namespace Utilerias\AdministradorTextosBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use ShowDashboard\DashboardBundle\Model\DashboardModel;

/**
 * @author Eduardo
 * @since 26/07/2016
 * @version 2.0
 */
class AdministradorTextosModel extends DashboardModel {

    protected $SQLModel;

    const APP = 'SAS', PLATFORM = 1;

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getCommittee() {
        $fiels = array('idComiteOrganizador', 'ComiteOrganizador');
        $result = $this->SQLModel->selectFromTable("ComiteOrganizador", $fiels);
        if ($result['status'] == 1) {
            return $result['data'];
        }
        return $result['error'];
    }

    public function getEvent($idComite, $lang) {
        $qry = 'SELECT "idEvento", "Evento_' . strtoupper($lang) . '" AS Evento FROM "SAS"."Evento" WHERE "idComiteOrganizador" = ' . $idComite;
        $result = $this->SQLModel->executeQuery($qry);
        if ($result['status'] == 1) {
            return $result['data'];
        }
        return $result['error'];
    }

    public function getEdition($idEvento, $lang) {
        $qry = 'SELECT "idEvento", "Evento_' . strtoupper($lang) . '" AS Evento FROM "SAS"."Evento" WHERE "idEvento" = ' . $idEvento;
        $result = $this->SQLModel->executeQuery($qry);
        if ($result['status'] == 1) {
            return $result['data'];
        }
        return $result['error'];
    }

    public function getTexts($args) {
        $fields = array('idTexto', 'Seccion', 'Etiqueta', 'Texto_EN', 'Texto_ES', 'Texto_FR', 'Texto_PT');
        $result = $this->SQLModel->selectFromTable("Texto", $fields, $args, array('"idTexto"' => 'ASC'));
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $data = array();
        foreach ($result['data'] as $key => $value) {
            $data[$value['idTexto']] = $value;
        }
        $result['data'] = $data;
        return $result;
    }

    public function addText($data) {
        $values = array(
            'idPlataformaIxpo' => self::PLATFORM,
            'idEvento' => $data['idEvento'],
            'Seccion' => $data['idSeccion'],
            'Etiqueta' => "'" . str_replace("'", "''", $data['Etiqueta']) . "'",
            'Texto_EN' => "'" . str_replace("'", "''", $data['Texto_EN']) . "'",
            'Texto_ES' => "'" . str_replace("'", "''", $data['Texto_ES']) . "'",
            'Texto_FR' => "'" . str_replace("'", "''", $data['Texto_FR']) . "'",
            'Texto_PT' => "'" . str_replace("'", "''", $data['Texto_PT']) . "'"
        );
        $result = $this->SQLModel->insertIntoTable("Texto", $values, "idTexto");
        if (!($result['status'] && count($result['data']) > 0)) {
            die($result['data']);
        }
        $this->deleteTextJson($data['idSeccion']);
        return $result;
    }

    public function editText($data, $id) {
        $values = array(
            'Seccion' => $data['idSeccion'],
            'Etiqueta' => "'" . str_replace("'", "''", $data['Etiqueta']) . "'",
            'Texto_EN' => "'" . str_replace("'", "''", $data['Texto_EN']) . "'",
            'Texto_ES' => "'" . str_replace("'", "''", $data['Texto_ES']) . "'",
            'Texto_FR' => "'" . str_replace("'", "''", $data['Texto_FR']) . "'",
            'Texto_PT' => "'" . str_replace("'", "''", $data['Texto_PT']) . "'"
        );
        $result = $this->SQLModel->updateFromTable("Texto", $values, array("idTexto" => $id), "idTexto");
        if (!($result['status'] && count($result['data']) > 0)) {
            die($result['data']);
        }
        $this->deleteTextJson($data['idSeccion']);
        return $result;
    }

    public function deleteText($section, $id) {
        $result = $this->SQLModel->deleteFromTable("Texto", array("idTexto" => $id));
        if (!($result['status'] && count($result['data']) > 0)) {
            die($result['data']);
        }
        $this->deleteTextJson($section);
        return $result;
    }

    public function deleteTextJson($section) {
        $idioms = array("EN", "ES", "FR", "PT");
        foreach ($idioms as $lang) {
            $cache = self::PLATFORM . '_' . $section . '_' . strtoupper($lang) . '.json';
            $path = '../var/cache/textos/' . $cache;
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

}
