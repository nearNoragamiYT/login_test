<?php

namespace ShowDashboard\FT\ConfiguracionBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;
use Utilerias\SQLBundle\Model\SQLModelFactura;

class ConfiguracionModel
{

    protected $SQLModel, $SQLModelFactura, $mb = 1048576, $path_file = 'resources/images/';

    public function __construct()
    {
        $this->SQLModel = new SQLModel();
        $this->SQLModelFactura = new SQLModelFactura();
    }

    public function getfacturas()
    {
        $query = 'SELECT * FROM "AE"."Factura"';
        return $this->SQLModel->executeQuery($query);
    }
    public function getConexion()
    {
        $query = 'SELECT * FROM "SAS"."Conexion"';
        return $this->SQLModelFactura->executeQuery($query);
    }
    public function getConfiguracion($idConfiguracion){
        $query = 'SELECT * FROM "SAS"."Configuracion"';
        $query .= ' WHERE "idConfiguracion" = '.$idConfiguracion;
        return $this->SQLModelFactura->executeQuery($query);
    }
    public function getInsertConfiguracion($params,$idconfiguracion){
        $qry = 'UPDATE "SAS"."Configuracion" SET';
        $qry .= ' "ColorPortal" = '."'".$params['colorPortal']."'".',"colorHeader" = '."'".$params['colorHeader']."'".',"idTipoUsuario" = '.$params['idTipoUsuario'];
        $qry .= '  WHERE "idConfiguracion" = '.$idconfiguracion;
        return $this->SQLModelFactura->executeQuery($qry);
    }

    public function uploadFiles($files, $general_text, $id) {
        $result = array("status" => TRUE, "data" => "");
        if (count($files) == 0) {
            return $result;
        }

        $files_tmp = array();
        foreach ($files as $key => $file) {
            if (isset($file['name']) && $file['name'] != "") {
                $error = FALSE;
                /* verificamos si se puede abrir el archivo */
                if ($file["error"] > 0) {
                    $error = $general_text['sas_errorArchivo'] . ' "' . $file['name'] . '"';
                }            

                /* verificamos el tamaño del archivo menor a 3 MB */
                if ($file["size"] > 3 * $this->mb) {
                    $find = array('{0}', '%file%');
                    $replace = array('2', ' "' . $file['name'] . '"');
                    $error = str_replace($find, $replace, $general_text['sas_tamanoInvalido']);
                }

                /* guardamos el archivo en nuestro servidor */
                
            /* print_r($this->path_file . $key . '/' . basename($file['name'])); 
            die(); */
                if (!move_uploaded_file($file['tmp_name'], $this->path_file .  '/logo-evento//' . basename($file['name']))) {
                    $error = $general_text['sas_errorSubirArchivo'] . ' "' . $file['name'] . '"';
                }

                /* Si hubo algun error lo regresamos */
                if ($error) {
                    $result['status'] = FALSE;
                    $result['data'] = $error;
                    return $result;
                }
                $file = Array(
                    'name' => $file['name'],
                    'tmp_name' => $file['tmp_name'],
                    'type' => $file['type'],
                    'field' => $key,
                );
                $files_tmp[] = $file;
            }
        }
        
        $result['data'] = $files_tmp;
        return $result;
    }

    public function updateLogo($nameLogo, $idConfiguracion)
    {
        $qry = 'UPDATE "SAS"."Configuracion" SET';
        $qry .= ' "LogoTipo" = '."'".$nameLogo."'";
        $qry .= '  WHERE "idConfiguracion" = '.$idConfiguracion;
        return $this->SQLModelFactura->executeQuery($qry);
    }
    public function getEdicion($idEvento, $idEdicion)
    {
        $query = 'SELECT * FROM "SAS"."Edicion"';
        $query .= '  WHERE "idEvento" = '.$idEvento;
        $query .= '  AND "idEdicion" = '.$idEdicion;
        return $this->SQLModel->executeQuery($query);
    }
}
