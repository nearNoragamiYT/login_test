<?php

namespace ShowDashboard\ED\Formas\AdministradorFormasBundle\Model;

use ShowDashboard\DashboardBundle\Model\DashboardModel;
/**
 *
 * @author Edardo Cervantes <eduardoc@infoexpo.com.mx>
 */
use Utilerias\SQLBundle\Model\SQLModel;

class AdministradorFormasModel extends DashboardModel {

    protected $SQLModel;

    const rutaImagen = "administrador/secciones/", rutaPDF = "administrador/formas/";

    public function __construct() {
        $this->SQLModel = new SQLModel();
    }

    public function getSectionForms($idioms, $idEdicion) {
        $qry = ' SELECT ' . $this->getSectionFormsFields($idioms);
        $qry .= ' FROM "SAS"."SeccionFormatos" WHERE "idEdicion" = ' . $idEdicion . ' AND "idSeccionFormatos" > 0 ORDER BY "Orden"';

        $result = $this->SQLModel->executeQuery($qry);

        if ($result['status'] == 1) {
            $data = array();
            foreach ($result['data'] as $value) {
                $data[$value['idSeccionFormatos']] = $value;
            }
            return Array("status" => TRUE, "data" => $data);
        } else {
            return Array("status" => FALSE, "data" => $result['data']);
        }
    }

    private function getSectionFormsFields($idioms) {
        $fields = null;
        $fields .= '"idSeccionFormatos", ';
        foreach ($idioms as $key => $value) {
            $fields .= '"Nombre' . $value . '", ';
        }
        $fields .= '"ColorFondo", ';
        $fields .= '"ColorLetra", ';
        $fields .= '"CreacionSAS", ';
        $fields .= '"VisibleWeb", ';
        $fields .= '"HabilitarSeccion", ';
        $fields .= '"Imagen", ';
        $fields .= '"Orden" ';
        return $fields;
    }

    public function getFormas($idioms, $idEdicion) {
        $qry = ' SELECT ' . $this->getCamposFormas($idioms);
        $qry .= ' FROM "SAS"."Forma" AS f ';
        $qry .= ' JOIN "SAS"."SeccionFormatos" AS sf ON f."idSeccionFormatos" = sf."idSeccionFormatos"';
        $qry .= ' WHERE f."idSeccionFormatos" IS NOT NULL AND f."idSeccionFormatos" > 0 AND f."idEdicion" = ' . $idEdicion . ' AND sf."idEdicion" = ' . $idEdicion;
        $qry .= ' ORDER BY sf."Orden" ASC, f."OrdenDespliegue" ASC';
        $result_query = $this->SQLModel->executeQuery($qry);

        $result = Array("status" => FALSE, "data" => "");
        if (isset($result_query['status']) && $result_query['status'] == 1) {
            $forms = Array();
            if (COUNT($result_query["data"]) > 0) {
                /* ---  EmpresaForma completas  --- */
                $result_completed_forms = $this->getGrupoPorEMFO(Array('ef."StatusForma"' => 1, 'ee."idEdicion"' => $idEdicion, 'ef."idEdicion"' => $idEdicion));
                if (!$result_completed_forms["status"]) {
                    $result["data"] = $result_completed_forms['data'];
                    return $result;
                }
                $completed_forms = array();
                foreach ($result_completed_forms['data'] as $value) {
                    $completed_forms[$value['idForma']] = $value['count'];
                }
                /* ---  EmpresaForma incompletas   --- */
                $result_incompleted_forms = $this->getNumeroExpositores($idEdicion);
                if (!$result_incompleted_forms["status"]) {
                    $result["data"] = $result_incompleted_forms['data'];
                    return $result;
                }
                $incompleted_forms = $result_incompleted_forms["data"];
                /* ---  formas de interés  --- */
                $result_no_interest_forms = $this->getGrupoPorEMFO(Array('ef."Interes"' => 0, 'ee."idEdicion"' => $idEdicion, 'ef."idEdicion"' => $idEdicion));
                if (!$result_no_interest_forms["status"]) {
                    $result["data"] = $result_no_interest_forms['data'];
                    return $result;
                }
                if (COUNT($result_no_interest_forms['data']) > 0) {
                    foreach ($result_no_interest_forms['data'] as $value) {
                        $no_interest_forms[$value['idForma']] = $value['count'];
                    }
                }
                foreach ($result_query["data"] as $key => $form) {
                    $form["incompleted"] = $incompleted_forms;
                    $form["completed"] = 0;
                    $form["no_interest"] = 0;
                    foreach ($no_interest_forms as $id => $completed_form) {
                        if ($form["idForma"] == $id) {
                            $form["no_interest"] = $no_interest_forms[$id];
                        }
                    }
                    foreach ($completed_forms as $id => $completed_form) {
                        if ($form["idForma"] == $id) {
                            $form["completed"] = $completed_form;
                            $form["incompleted"] = $incompleted_forms - $completed_form;
                            break;
                        }
                    }
                    $forms[$key] = $form;
                }
            }
            $result["status"] = TRUE;
            $result ["data"] = $forms;
        } else {
            $result["data"] = $result_query['data'];
        }
        return $result;
    }

    private function getCamposFormas($idioms) {
        $fields = 'f."idForma", ';
        $fields .= 'sf."idSeccionFormatos", ';
        foreach ($idioms as $key => $value) {
            $fields .= 'f."Link' . $value . '", ';
            $fields .= 'f."Descripcion' . $value . '", ';
            $fields .= 'f."FechaActualizacion' . $value . '", ';
            $fields .= 'f."FormaPago' . $value . '", ';
            $fields .= 'f."NombreForma' . $value . '", ';
            $fields .= 'sf."Nombre' . $value . '", ';
        }
        $fields .= 'f."OrdenDespliegue", ';
        $fields .= 'f."ObligatorioOpcional", ';
        $fields .= 'f."FechaLimite", ';
        $fields .= 'f."LinkEDForma", ';
        $fields .= 'f."FechaModificacion", ';
        $fields .= 'f."Bloqueado", ';
        $fields .= 'f."Habilitado", ';
        $fields .= 'f."FormaVisibleWeb", ';
        $fields .= 'f."TipoLink", ';
        $fields .= 'f."CreacionSAS", ';
        $fields .= 'f."LinkReporte", ';
        $fields .= 'f."Identificador" ';
        return $fields;
    }

    public function getGrupoPorEMFO($where) {
        $qry = ' SELECT ' . $this->getCamposEMFO();
        $qry .= ' FROM "SAS"."EmpresaForma" AS ef';
        $qry .= ' INNER JOIN "SAS"."EmpresaEdicion" AS ee ON ef."idEmpresa" = ee."idEmpresa" ';
        $qry .= $this->buildCondition($where);
        $qry .= ' GROUP BY "idForma"';

        $result_query = $this->SQLModel->executeQuery($qry);

        if ($result_query['status'] == 1) {
            return Array("status" => TRUE, "data" => $result_query['data']);
        } else {
            return Array("status" => FALSE, "error" => $result_query['data']);
        }
    }

    private function getCamposEMFO() {
        $fields = 'ef."idForma", ';
        $fields .= 'COUNT(ef."idEmpresaForma") AS "count" ';
        return $fields;
    }

    public function getNumeroExpositores($idEdicion) {
        $qry = 'SELECT COUNT("idEmpresa") AS "count" FROM "SAS"."EmpresaEdicion" WHERE "idEtapa" = 2 AND "idEdicion" = ' . $idEdicion;

        $result = $this->SQLModel->executeQuery($qry);

        if ($result['status'] == 1) {
            return Array("status" => TRUE, "data" => $result['data'][0]['count']);
        } else {
            return Array("status" => FALSE, "error" => $result['data']);
        }
    }

    public function acualizarOrden($data, $idEdicion) {
        $idSection = $data['idSeccionFormatos'];
        unset($data['idSeccionFormatos']);
        foreach ($data as $orden => $idForma) {
            $result = $this->SQLModel->updateFromTable("Forma", array('OrdenDespliegue' => $orden), array('idSeccionFormatos' => $idSection, 'idForma' => $idForma, 'idEdicion' => $idEdicion));
            if (!$result['status']) {
                die($result['data']);
            }
        }
        return $result;
    }

    public function getEmpresaForma($args, $data) {
        if ($data['StatusForma']) {
            /* ---  obtiene las empresas forma en status 1  --- */
            $qry = ' SELECT ' . $this->getCamposEmpresaForma(TRUE, TRUE);
            $qry .= ' FROM "SAS"."Empresa" AS e';
            $qry .= ' INNER JOIN "SAS"."Contrato" AS ct ON e."idEmpresa" = ct."idEmpresa" AND ct."idStatusContrato" < 5';
            $qry .= ' INNER JOIN "SAS"."Usuario" AS ven ON ct."idVendedor" = ven."idUsuario"';#antes ven."idVendedor" y "SAS"."Vendedor"
            $qry .= ' INNER JOIN "SAS"."EmpresaEntidadFiscal" AS eef ON ct."idEmpresaEntidadFiscal" = eef."idEmpresaEntidadFiscal"';
            $qry .= ' INNER JOIN "SAS"."EmpresaEdicion" AS ee ON e."idEmpresa" = ee."idEmpresa"';
            $qry .= ' INNER JOIN "SAS"."EmpresaForma" AS ef ON e."idEmpresa" = ef."idEmpresa"';
            //$qry .= ' INNER JOIN (SELECT "idContacto" FROM  "SAS"."ContactoEdicion"';
            //$qry .= ' GROUP BY "idContacto")contacto ON contacto."idContacto" = ef."idContacto"';
            $qry .= ' INNER JOIN "SAS"."Contacto" co ON ef."idContacto" = co."idContacto" ' . $this->buildCondition($args);
           #print_r($qry);die(' <= $qry');
            $result = $this->SQLModel->executeQuery($qry);
        } else {
            /* ---  Selecciona los registros de EmpresaEdicion sin EmpresaForma(formas sin llenar)  --- */
            $qry = ' SELECT ' . $this->getCamposEmpresaForma(FALSE, TRUE);
            $qry .= ' FROM "SAS"."Empresa" AS e';
            $qry .= ' INNER JOIN "SAS"."Contrato" AS ct ON e."idEmpresa" = ct."idEmpresa" AND ct."idStatusContrato" < 5';
            $qry .= ' INNER JOIN "SAS"."Usuario" AS ven ON ct."idVendedor" = ven."idUsuario"';
            $qry .= ' INNER JOIN "SAS"."EmpresaEntidadFiscal" AS eef ON ct."idEmpresaEntidadFiscal" = eef."idEmpresaEntidadFiscal"';
            $qry .= ' INNER JOIN "SAS"."EmpresaEdicion" AS ee ON e."idEmpresa" = ee."idEmpresa"';
            $qry .= ' INNER JOIN "SAS"."Contacto" AS co ON e."idEmpresa" = co."idEmpresa"';
            $qry .= ' INNER JOIN "SAS"."ContactoEdicion" AS ce ON co."idContacto" = ce."idContacto" AND  ee."idEdicion" = ce."idEdicion" ' . $this->buildCondition($args);
            $qry .= ' AND e."idEmpresa" NOT IN( SELECT "idEmpresa" FROM "SAS"."EmpresaForma" WHERE "idEdicion" = ' . $data['idEdicion'] . ' AND  "idForma" = ' . $data['idForma'] . ' AND "StatusForma" = 1)';            
            $resultEmpresaEdicion = $this->SQLModel->executeQuery($qry);
            
            if (!$resultEmpresaEdicion['status']) {
                die('Error! Not consult ->EmpresaEdicion<- ' . $resultEmpresaEdicion['data']);
            }
            /* ---  hace un array asociativo con el id de la empresa para hacer un merge más adelante   --- */
            $EMED = Array();
            foreach ($resultEmpresaEdicion['data'] as $value) {
                $EMED[$value['idEmpresa']] = $value;
            }
            /* ---  Selecciona los registros de EmpresaEdicion con EmpresaForma(En status de forma en 0) --- */
            $args['ef."StatusForma"'] = 0;
            $args['ef."idForma"'] = $data['idForma'];
            $args['ce."Principal"'] = "'TRUE'";
            $qry2 = ' SELECT ' . $this->getCamposEmpresaForma(TRUE, FALSE);
            $qry2 .= ' FROM "SAS"."Empresa" AS e';
            $qry2 .= ' INNER JOIN "SAS"."EmpresaEdicion" AS ee ON e."idEmpresa" = ee."idEmpresa"';
            $qry2 .= ' INNER JOIN "SAS"."EmpresaForma" AS ef ON e."idEmpresa" = ef."idEmpresa"';
            $qry2 .= ' INNER JOIN "SAS"."Contacto" AS co ON e."idEmpresa" = co."idEmpresa"';
            $qry2 .= ' INNER JOIN "SAS"."ContactoEdicion" AS ce ON co."idContacto" = ce."idContacto" ' . $this->buildCondition($args);
            
            $resultEmpresaForma = $this->SQLModel->executeQuery($qry2);
            
            if (!$resultEmpresaForma['status']) {
                die('Error! Not consult ->EmpresaForma<- ' . $resultEmpresaForma['data']);
            }
            /* ---  hace un array asociativo con el id de la empresa para hacer un merge con EMED --- */
            $EMFO = Array();
            foreach ($resultEmpresaForma['data'] as $value) {
                $EMFO[$value['idEmpresa']] = $value;
            }
            /* ---  hace la variable para devolver la respuesta con el status y también un merge de EMED con EMFO donde EMFO sobre escribe EMED  --- */
            $result = Array('status' => TRUE);
            foreach ($EMFO as $value) {
                $EMED[$value['idEmpresa']] = $value;
            }
            $result['data'] = array_values($EMED); //regresa el array reseteando el indice de cada elemento del array comenzando en 0,1,2...
        }
        if (!$result['status']) {
            die('Error! consult ->EmpresaForma<-' . $result['data']);
        }
        return $result;
    }

    private function getCamposEmpresaForma($EMFO, $Contrato) {
        $fields = '';
        $fields .= ' e."idEmpresa", ';
        $fields .= ' e."DC_NombreComercial",';
        $fields .= ' e."DC_idPais" AS "EmpresaIdPais", ';
        $fields .= ' e."CodigoCliente", ';
        $fields .= ' e."DC_Pais" AS "EmpresaPais", ';
        $fields .= ' e."DC_Estado" AS "EmpresaEstado", ';
        $fields .= ' ee."idEdicion", ';
        $fields .= ' co."Email", ';
        $fields .= ' co."Telefono", ';
        if ($Contrato) {
            $fields .= ' eef."DF_RazonSocial", ';
            $fields .= ' ven."Nombre" AS "Vendedor", ';
        }
        $fields .= ' CONCAT(co."Nombre", ' . "' '," . 'co."ApellidoPaterno", ' . "' '," . 'co."ApellidoMaterno") AS "NombreCompleto", ';
        if ($EMFO) {
            $fields .= ' ef."Lang", ';
            $fields .= ' ef."StatusPago", ';
            $fields .= ' ef."Bloqueado", ';
        } else {
            $fields .= ' ce."Password", ';
        }
        $fields .= ' ee."idEtapa" AS "EmpresaEtapa", ';
        $fields .= ' ee."Token" ';
        return $fields;
    }

    public function editarHTML($post) {
        $texto = preg_replace("/\r\n+|\r+|\n+|\t+/i", " ", $post['correo']);
        $qry = ' UPDATE "SAS"."Texto" ';
        #$qry .= ' SET "Texto_' . strtoupper($post['lang']) . '" =' . " '" . $texto . "'" . ' WHERE "Etiqueta" = ' . "'sas_emailFormaPendiente' AND" . ' "idPlataformaIxpo" = 1 AND "Seccion" = 4 AND "idEdicion" = ' . $post['idEdicion'];
        $qry .= ' SET "Texto_' . strtoupper($post['lang']) . '" =' . " '" . $texto . "'" . ' WHERE "Etiqueta" = ' . "'sas_emailFormaPendiente' AND" . ' "idPlataformaIxpo" = 1 AND "Seccion" = 4';

        $result = $this->SQLModel->executeQuery($qry);

        if (!$result['status']) {
            die("Error! Not update ->Texto<- " . $result['data']);
        }
        return array('status' => TRUE);
    }

    public function desbloquarBloquearForma($data) {
        $qry = 'SELECT * FROM "SAS"."fn_sas_BloqueaEmpresaForma"(' . $data['idEmpresa'] . ', ' . $data['idForma'] . ', ' . $data['Bloqueado'] . ', ' . $data['idEvento'] . ', ' . $data['idEdicion'] . ');';

        $result = $this->SQLModel->executeQuery($qry);

        if (!$result['status']) {
            die('Error! not execute ->fn_sas_BloqueaEmpresaForma<- ' . $result['data']);
        }
        return $result;
    }

    function mostrarFormasSinInteres($args) {
        /* ---  obtiene las empresas forma que no les interesa de la forma --- */
        $qry = ' SELECT ' . $this->getCamposEmpresaForma(TRUE);
        $qry .= ' FROM "SAS"."Empresa" AS e';
        $qry .= ' INNER JOIN "SAS"."EmpresaEdicion" AS ee ON e."idEmpresa" = ee."idEmpresa"';
        $qry .= ' INNER JOIN "SAS"."EmpresaForma" AS ef ON e."idEmpresa" = ef."idEmpresa"';
        $qry .= ' INNER JOIN "SAS"."Contacto" AS co ON e."idEmpresa" = co."idEmpresa"';
        $qry .= ' INNER JOIN "SAS"."ContactoEdicion" AS ce ON co."idContacto" = ce."idContacto" ' . $this->buildCondition($args);

        $result = $this->SQLModel->executeQuery($qry);

        if (!$result['status']) {
            die("Error! Not consult ->EmpresaForma<- " . $result['data']);
        }

        return $result;
    }

    public function acualizarFecha($data, $condition) {
        $values = $this->setValores($data);
        $result = $this->SQLModel->updateFromTable("Forma", $values, $condition);
        if (!$result['status']) {
            die($result['data']);
        }
        return $result;
    }

    public function acualizarEstatus($data, $condition) {
        switch ($data['type']) {
            case "mandatory":
                $values['ObligatorioOpcional'] = "'" . $data['status'] . "'";
                break;
            case "lock":
                $values['Bloqueado'] = "'" . $data['status'] . "'";
                break;
            default:
                die('Type of change is undefined :(');
        }
        $result = $this->SQLModel->updateFromTable("Forma", $values, $condition);
        if (!$result['status']) {
            die($result['data']);
        }
        if ($data['type'] == "lock") {
            $resultLockEMFO = $this->SQLModel->updateFromTable("EmpresaForma", Array('Bloqueado' => $data['status']), $condition);
            if (!$resultLockEMFO['status']) {
                die("Error! not update status in ->EmpresaForma<- " . $resultLockEMFO['data']);
            }
        }
        return $result;
    }

    public function actualizarPDF($archivo, $data, $idEdicion) {
        $nombreArchivo = $this->limpiarTexto($data['NombreArchivo']);
        $update = $this->actualizarArchivo($archivo, $data['idForma'], $idEdicion, array('pdf', 'PDF'), $data['idioma'], $nombreArchivo);
        if (!$update['status']) {
            die('No upload file :(');
        }
        $data['url'] = $update['data']['url'];
        $result = $this->SQLModel->updateFromTable("Forma", array('Link' . $data['idioma'] => "'" . $data['url'] . "'", 'FechaActualizacion' . $data['idioma'] => "'" . $data['FechaActualizacion'] . "'"), array('idForma' => $data['idForma'], 'idEdicion' => $idEdicion));
        if (!$result['status']) {
            die($result['data']);
        }
        $result['data'] = $data;
        return $result;
    }

    public function actualizarLink($data, $idEdicion) {
        $result = $this->SQLModel->updateFromTable("Forma", array("Link" . $data['idioma'] => "'" . $data['Link'] . "'", 'FechaActualizacion' . $data['idioma'] => "'" . $data['FechaActualizacion'] . "'"), array('idForma' => $data['idForma'], 'idEdicion' => $idEdicion));
        if (!$result['status']) {
            die($result['data']);
        }
        $result['data'] = $data;
        return $result;
    }

    public function agregarImagen($archivo, $idSeccionFormatos, $idEdicion, $exts) {
        $update = $this->actualizarArchivo($archivo, $idSeccionFormatos, $idEdicion, $exts);
        if ($idSeccionFormatos != 0 && $update['status']) {
            $result = $this->SQLModel->updateFromTable("SeccionFormatos", array('Imagen' => "'" . $update['data']['url'] . "'"), array("idSeccionFormatos" => $idSeccionFormatos, "idEdicion" => $idEdicion));
            if (!$result['status']) {
                die($result['data']);
            }
        }
        return $update;
    }

    public function agregarSecccion($data, $idEdicion, $exts) {
        $idSeccionFormatos = $data['idSeccionFormatos'];
        unset($data['path']);
        unset($data['idSeccionFormatos']);
        $data['idEdicion'] = $idEdicion;
        $values = $this->setValores($data);
        if ($idSeccionFormatos == 0) {//seccion nueva
            $result = $this->SQLModel->insertIntoTable("SeccionFormatos", $values, "idSeccionFormatos");
            if (!$result['status']) {
                die($result['data']);
            }
            $idSeccionFormatos = $result['data'][0]['idSeccionFormatos'];
        } else {//actualiza seccion
            $result = $this->SQLModel->updateFromTable("SeccionFormatos", $values, array("idSeccionFormatos" => $idSeccionFormatos, "idEdicion" => $idEdicion));
            if (!$result['status']) {
                die($result['data']);
            }
        }
        $img = null;
        foreach ($exts as $ext) {// actualiza la imagen en el servidor y la base
            if (file_exists(self::rutaImagen . $idEdicion . "_sin_seccion." . $ext)) {
                $img = self::rutaImagen . $idEdicion . "_Seccion_" . $idSeccionFormatos . "." . strtolower($ext);
                rename(self::rutaImagen . $idEdicion . "_sin_seccion." . $ext, $img);
                $result = $this->SQLModel->updateFromTable("SeccionFormatos", array("Imagen" => "'" . $img . "'"), array("idSeccionFormatos" => $idSeccionFormatos, "idEdicion" => $idEdicion));
                if (!$result['status']) {
                    die($result['data']);
                }
            } elseif (file_exists(self::rutaImagen . $idEdicion . "_Seccion_" . $idSeccionFormatos . "." . strtolower($ext))) {
                $img = self::rutaImagen . $idEdicion . "_Seccion_" . $idSeccionFormatos . "." . strtolower($ext);
            }
        }
        return array("idSeccionFormatos" => $idSeccionFormatos, "Imagen" => $img);
    }

    public function elimiarSeccion($idSeccionFormatos, $idEdicion) {
        $result = $this->SQLModel->deleteFromTable("SeccionFormatos", Array('idSeccionFormatos' => $idSeccionFormatos, 'idEdicion' => $idEdicion));
        if (!$result['status']) {
            die($result['data']);
        }
        return;
    }

    public function agregarForma($data, $idEdicion, $idEvento) {
        foreach ($data as $key => $value) {
            if (substr($key, 0, 3) == "FO_") {
                $datos[substr($key, 3)] = $value;
            }
        }
        $datos['idEdicion'] = $idEdicion;
        $datos['idEvento'] = $idEvento;
        $datos['ActivoMKF'] = 0;
        $values = $this->setValores($datos);
        $result = $this->SQLModel->insertIntoTable("Forma", $values, 'idForma');
        if (!$result['status']) {
            die($result['data']);
        }

        $total = $this->getNumeroExpositores($idEdicion);
        if (!$total['status']) {
            die($total['data']);
        }
        $datos['idForma'] = $result['data'][0]['idForma'];
        $datos['incompleted'] = $total['data'];
        return $datos;
    }

    public function editarForma($data, $where) {
        unset($data['idForma']);
        $values = $this->setValores($data);
        $result = $this->SQLModel->updateFromTable("Forma", $values, $where);
        if (!$result['status']) {
            die("Error! The information has been no updated in Forma " . $result['data']);
        }
        return $result;
    }

    public function eliminarForma($idForma, $idEdicion) {
        $EMFO = $this->SQLModel->deleteFromTable("EmpresaForma", Array("idForma" => $idForma, "idEdicion" => $idEdicion));
        if (!$EMFO['status']) {
            die($EMFO['data']);
        }
        $forma = $this->SQLModel->deleteFromTable("Forma", Array("idForma" => $idForma, "idEdicion" => $idEdicion));
        if (!$forma['status']) {
            die($forma['data']);
        }
        return;
    }

    public function actualizarArchivo($archivo, $id, $idEdicion, $exts, $lang = false, $nombreArchivo = false) {
        $nombre = explode(".", $archivo['name']);
        $extension = strtolower(end($nombre));
        $rutaArchivo = "";
        if ($lang == false) {//es la imagen de la seccion
            if ($id == 0) {//subir una imagen nueva pero sin sección
                $rutaArchivo = self::rutaImagen . $idEdicion . "_sin_seccion.";
                foreach ($exts as $ext) {
                    if (file_exists($rutaArchivo . $ext)) {
                        unlink($rutaArchivo . $ext);
                    }
                }
                move_uploaded_file($archivo['tmp_name'], $rutaArchivo . strtolower($extension));
                if (file_exists($rutaArchivo . $extension)) {
                    return array('status' => true, 'data' => array('url' => $rutaArchivo . strtolower($extension)));
                }
                return array('status' => false);
            }
            /* ---  Actualizar imagen de la seccion  --- */
            $rutaArchivo = self::rutaImagen . $idEdicion . "_Seccion_" . $id . ".";
            foreach ($exts as $ext) {
                if (file_exists($rutaArchivo . $ext)) {
                    $date = date("Y-m-d _ H-i-s");
                    rename($rutaArchivo . $ext, self::rutaImagen . $idEdicion . "_Seccion_" . $id . "_" . $date . "." . $ext);
                }
            }
            move_uploaded_file($archivo['tmp_name'], $rutaArchivo . strtolower($extension));
            if (file_exists($rutaArchivo . $extension)) {
                return array('status' => true, 'data' => array('url' => $rutaArchivo . strtolower($extension)));
            }
            return array('status' => false);
        } else {//es un pdf de una sección en un idioma
            $rutaArchivo = self::rutaPDF . $idEdicion . "_" . $nombreArchivo . ".";
            foreach ($exts as $ext) {
                if (file_exists($rutaArchivo . $ext)) {
                    $date = date("Y-m-d _ H-i-s");
                    rename($rutaArchivo . $ext, self::rutaPDF . $idEdicion . "_" . $nombreArchivo . "_" . $date . "." . $ext);
                }
            }
            move_uploaded_file($archivo['tmp_name'], $rutaArchivo . strtolower($extension));
            if (file_exists($rutaArchivo . $extension)) {
                return array('status' => true, 'data' => array('url' => $rutaArchivo . strtolower($extension)));
            }
            return array('status' => false);
        }
    }

    private function checkImageExist($idEdicion, $idSeccionFormatos, $ruta) {
        $rutaArchivo = $ruta . $idEdicion . "_sin_seccion.";
        foreach ($exts as $ext) {
            if (file_exists($rutaArchivo . $ext)) {
                $rutaNueva = $ruta . "_Seccion_" . $idSeccionFormatos . "." . $ext;
                rename($rutaArchivo . $ext, $rutaNueva);
            }
        }
        move_uploaded_file($archivo['tmp_name'], $rutaArchivo . $extension);
        if (file_exists($rutaArchivo . $extension)) {
            return array('status' => true, 'data' => array('url' => $rutaArchivo . $extension));
        }
    }

    private function setValores($data) {
        $values = array();
        foreach ($data as $k => $v) {
            $values[$k] = "'" . str_replace("'", "''", $v) . "'";
        }
        return $values;
    }

    public function buildCondition($condition) {
        if (count($condition) == 0) {
            return "";
        }
        $length = count($condition);
        $i = 1;
        $qry = " WHERE ";
        foreach ($condition as $field => $value) {
            if ($i < $length) {
                $i ++;
                $and = " AND ";
            } else {
                $and = "";
            }
            if (is_numeric($field)) {
                $qry .= $value . " IS NULL" . $and;
            } else {
                $qry .= $field . " = " . $value . $and;
            }
        }
        return $qry;
    }

    /**
     * Limpia una cadena de caracteres raros
     * @param String $string text a limpiar
     * @return Stgring $string text limpio de caracteres raros
     */
    public function limpiarTexto($string) {
        $string = trim($string);
        $string = str_replace(array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'), array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'), $string);
        $string = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'), array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'), $string);
        $string = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'), array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'), $string);
        $string = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'), array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'), $string);
        $string = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'), array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'), $string);
        $string = str_replace(array('ç', 'Ç'), array('c', 'C',), $string);
        //Esta parte se encarga de eliminar cualquier caracter extrano
        $string = str_replace(array("¨", "º", "-", "~", "#", "@", "|", "!", "·", "$", "%", "/", "(", ")", "?", "'", "¡", "¿", "[", "^", "<code>", "]", "+", "}", "{", "¨", "´", ">", "< "), '', $string);
        $string = str_replace("&", 'and', $string);
        $string = str_replace(" ", '_', $string);
        return $string;
    }

}
