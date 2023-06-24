<?php

namespace ShowDashboard\RS\ArchivosRSBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

class ArchivoRSModel {

    protected $SQLModel, $schema = "AE";

    public function __construct() {//se crea la conexion
        $this->SQLModel = new SQLModel();
        $this->SQLModel->setSchema($this->schema);
    }

    public function insertVisitantes($json_dataComplete, $idEdicion, $idEvento) {
        $qry = 'SELECT * FROM "AE"."fn_rs_InsertaArchivos"(' . "'" . $json_dataComplete . "'" . ', ' . $idEdicion . ", " . $idEvento . ')';
        $result = $this->SQLModel->executeQuery($qry);

        return $result;
    }

    public function insertVisitanteTip($json_dataTipComplete, $idEdicion, $idEvento) {
        $qry = 'SELECT * FROM "AE"."fn_rs_InsertaArchivos"(' . "'" . $json_dataTipComplete . "'" . ', ' . $idEdicion . ", " . $idEvento . ')';
        $result = $this->SQLModel->executeQuery($qry);

        return $result;
    }

    public function insertLecturas($json_dataLecComplete, $idEdicion, $idEvento) {
        $qry = 'SELECT * FROM "AE"."fn_rs_SubirLecturas"(' . "'" . $json_dataLecComplete . "'" . ', ' . $idEdicion . ", " . $idEvento . ')';
        print_r($qry);
        die("here");
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

    public function getPuertas() {
        $qry = 'SELECT';
        $qry .= ' "idPuerta",';
        $qry .= ' "NombrePuerta" ';
        $qry .= ' FROM';
        $qry .= ' "LECTORAS"."Puerta"';

        $result = $this->SQLModel->executeQuery($qry);

        if (($result['status'] && count($result['data']) > 0)) {
            foreach ($result['data'] as $value) {
                $data[$value['idPuerta']] = $value;
            }
            $result['data'] = $data;
        }

        return $result;
    }

    public function getTipoScanners() {
        $qry = 'SELECT';
        $qry .= ' "idScannerTipo",';
        $qry .= ' "ScannerTipo" ';
        $qry .= ' FROM';
        $qry .= ' "LECTORAS"."ScannerTipo"';

        $result = $this->SQLModel->executeQuery($qry);

        if (($result['status'] && count($result['data']) > 0)) {
            foreach ($result['data'] as $value) {
                $data[$value['idScannerTipo']] = $value;
            }
            $result['data'] = $data;
        }

        return $result;
    }

    public function insertPuerta($puertaNueva) {

        $this->SQLModel->setSchema("LECTORAS");
        $result = $this->SQLModel->insertIntoTable("Puerta", $puertaNueva, "idPuerta");
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }

        unset($result['query']);
        return $result;
    }

}
