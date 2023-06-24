<?php

namespace Utilerias\TextoBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TextoModel extends Controller {

    protected $App, $container, $SQLModel, $Platform = 1;

    //const APP = 'SAS';

    public function __construct(ContainerInterface $container = NULL) {
        $this->container = $container;
        $this->SQLModel = new SQLModel();
    }

    public function getTexts($lang = 'ES', $section = "'0'") {
        $lang = ($lang == "") ? 'ES' : strtoupper($lang);
        $texts = Array();
        $cache = /* self::APP . '_' . */$this->Platform . '_' . str_replace("'", "", $section) . '_' . strtoupper($lang) . '.json';
        $path = '../var/cache/textos/' . $cache;
        if (file_exists($path)) {
            $result_cache = file_get_contents($path);
            $texts = json_decode($result_cache, TRUE);
            return Array("status" => TRUE, "data" => $texts);
        }
        $texts = $this->getPGTexts($section, $lang);
        if (COUNT($texts) > 0) {
            $this->writeJSON($path, $texts);
            clearstatcache();
        }

        return Array("status" => TRUE, "data" => $texts);
    }

    protected function getPGTexts($section, $lang) {
        $data = Array();
        $fields = $this->getTextFields($lang);
        $where = Array('Seccion' => $section, 'idPlataformaIxpo' => $this->Platform);
        $result = $this->SQLModel->selectFromTable("Texto", $fields, $where);
        if ($result['status'] == 1) {
            foreach ($result['data'] as $key => $value) {
                $data[$value['Etiqueta']] = $value['Texto_' . $lang];
            }
            return $data;
        } else {
            return $result['data'];
        }
    }

    protected function getTextFields($lang) {
        return Array(
            'idTexto',
            'Etiqueta',
            'Texto_' . $lang,
        );
    }

    private function writeJSON($filename, $array) {
        $json = json_encode($array);
        $fp = fopen($filename, "w");
        fwrite($fp, $json);
        fclose($fp);
    }

    public function setPlatform($idPlataforma) {
        $this->Platform = $idPlataforma;
    }

}
