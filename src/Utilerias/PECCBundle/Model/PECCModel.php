<?php

namespace Utilerias\PECCBundle\Model;

/**
 * Description of PECCModel
 *
 * @author Javier
 */
use Utilerias\SQLBundle\Model\SQLModel;

class PECCModel {

    protected $SQLModel, $path = '../var/cache/pecc/', $schema = "PECC";

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->SQLModel->setSchema($this->schema);
    }

    public function getPaises($lang = 'ES') {
        $lang = ($lang == "") ? 'ES' : strtoupper($lang);
        $result = $this->getPaisesPG();
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }
        $result['data'] = $this->array_sort($result['data'], 'Pais_' . $lang);

        return $result;
    }

    private function getPaisesPG() {
        $path = $this->path . 'paises.json';
        if (file_exists($path)) {
            $result_cache = file_get_contents($path);
            $data = json_decode($result_cache, TRUE);
            return Array("status" => TRUE, "data" => $data);
        }

        $fields = array('idPais', 'ISOCode2', 'ISOCode3', 'CodigoTelefonoInt', 'Pais_ES', 'Pais_EN');
        $result = $this->SQLModel->selectFromTable('Pais', $fields, array(), array('"idPais"' => 'ASC'));

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }

        $data = array();
        foreach ($result['data'] as $value) {
            $data[$value['idPais']] = $value;
        }
        $this->writeJSON($path, $data);
        clearstatcache();

        $result['data'] = $data;

        return $result;
    }

    public function getEstados($idPais) {
        $path = $this->path . "estados_$idPais.json";
        if (file_exists($path)) {
            $result_cache = file_get_contents($path);
            $data = json_decode($result_cache, TRUE);
            return Array("status" => TRUE, "data" => $data);
        }

        $fields = array('idEstado', 'Estado', 'ISOStateCode');
        $where = array('idPais' => $idPais);
        $result = $this->SQLModel->selectFromTable('Estado', $fields, $where, array('"Estado"' => 'ASC'));

        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }

        $data = array();
        foreach ($result['data'] as $value) {
            $data[$value['idEstado']] = $value;
        }
        $this->writeJSON($path, $data);
        clearstatcache();

        $result['data'] = $data;

        return $result;
    }

    public function getPECC($codigoPostal) {
        $path = $this->path . "pecc_$codigoPostal.json";
        if (file_exists($path)) {
            $result_cache = file_get_contents($path);
            $data = json_decode($result_cache, TRUE);
            return Array("status" => TRUE, "data" => $data);
        }

        $qry = 'SELECT';
        $qry .= ' col."CP" as "CodigoPostal",';
        $qry .= ' initcap(col."Colonia") as "Colonia",';
        $qry .= ' initcap(c."Ciudad") as "Ciudad",';
        $qry .= ' col."idEstado",';
        $qry .= ' col."idPais",';
        $qry .= ' col."CP" || \', \' ||';
        $qry .= ' initcap(col."Colonia") || \', \' ||';
        $qry .= ' initcap(c."Ciudad") || \', \' ||';
        $qry .= ' initcap(e."Estado")';
        $qry .= ' as label';
        $qry .= ' FROM "' . $this->schema . '"."Colonia" col';
        $qry .= ' JOIN "' . $this->schema . '"."Ciudad" c';
        $qry .= ' ON col."idCiudad"=c."idCiudad"';
        $qry .= ' JOIN "' . $this->schema . '"."Estado" e';
        $qry .= ' ON col."idEstado"=e."idEstado"';
        $qry .= ' WHERE col."CP" LIKE \'' . trim($codigoPostal) . '%\'';

        $result = $this->SQLModel->executeQuery($qry);
        if (!($result['status'] && count($result['data']) > 0)) {
            return $result;
        }

        $this->writeJSON($path, $result['data']);
        clearstatcache();

        return $result;
    }

    private function array_sort($array, $on, $order = "SORT_ASC") {
        $new_array = array();
        $sortable_array = array();

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case "SORT_ASC":
                    asort($sortable_array);
                    break;
                case "SORT_DESC":
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }

    private function writeJSON($filename, $array) {
        $json = json_encode($array);
        $fp = fopen($filename, "w");
        fwrite($fp, $json);
        fclose($fp);
    }

}
