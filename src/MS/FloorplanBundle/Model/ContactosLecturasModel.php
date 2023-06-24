<?php

namespace MS\FloorplanBundle\Model;

use Utilerias\SQLBundle\Model\SQLModel;

/**
 * Description of ContactosLecturasModel
 *
 * @author Ernesto L <ernestol@infoexpo.com.mx>
 */
class ContactosLecturasModel {

    private $pg_schema_sas = 'SAS', $pg_schema_ms_sl = 'MS_SL', $pg_schema_ae = 'AE', $pg_schema_lectoras = 'LECTORAS',$SQLModel;
    private $tipo_opn_uno = 3, $tipo_opn_dos = 3;
    private $tipo_MI_uno = 7, $tipo_MI_dos = 10;

    function __construct() {
        $this->SQLModel = new SQLModel();
    }

    function insertGridDownload($args) {
        $qry = ' INSERT INTO "' . $this->pg_schema_sas . '"."EDLog"("idContacto","idEvento","idEdicion","idEmpresa","idAccion")';
        $qry .= ' VALUES(' . $args['idContacto'] . ',' . $args['idEvento'] . ',' . $args['idEdicion'] . ',' . $args['idEmpresa'] . ',1)';
        return $this->SQLModel->executeQuery($qry);
    }

    public function getLeadsApp($args) {
        $qry = 'SELECT DISTINCT ON (LC."BadgeId") ';
        $qry .= 'COALESCE(ES."EtiquetaApp",\'\') AS "usuario",';
        $qry .= 'to_char(LC."FechaCaptura",\'YYYY-MM-DD\') AS "fecha",';
        $qry .= 'to_char(LC."FechaCaptura",\'HH:MI\') AS "hora",';
        $qry .= 'COALESCE(LC."Nombre",\'\') AS "nombre",';
        $qry .= 'COALESCE(LC."ApellidoPaterno",\'\') AS "apellidoPaterno",';
        $qry .= 'COALESCE(LC."ApellidoMaterno",\'\') AS "apellidoMaterno",';
        $qry .= 'CASE WHEN LC."idVisitante" IS NULL OR LC."idVisitante"=0 THEN ';
        $qry .= 'DG."DGEmpresa"';
        $qry .= 'ELSE V."DE_RazonSocial"';
        $qry .= 'END AS "empresa",';

        $qry .= 'LC."Cargo" AS "puesto",';
        //TODO: Revisar cargo
        // $qry .= ' CASE WHEN LC."Cargo"=concat(\' | \',DG."DGEmpresa") THEN ';
        // $qry .= ' \'' . $args["textPosition"] . '\'';
        // $qry .= ' WHEN LC."Cargo"=concat(\' | \',V."DE_RazonSocial") THEN ';
        // $qry .= ' \'' . $args["textPosition"] . '\'';
        // $qry .= ' ELSE';
        // // $qry .= ' LC."Cargo"';
        // $qry .= ' V."DE_Cargo"';
        // $qry .= ' END AS "puesto",';

        
        $qry .= 'LC."Email" AS "email",';
        $qry .= '0 AS "tipoLectora",';
        $qry .= 'CASE WHEN LC."idVisitante" IS NULL OR LC."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_CalleNum"';
        $qry .= 'ELSE V."DE_Direccion"';
        $qry .= 'END AS "direccion",';
        $qry .= 'CASE WHEN LC."idVisitante" IS NULL OR LC."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Colonia"';
        $qry .= 'ELSE V."DE_Colonia"';
        $qry .= 'END AS "colonia",';
        $qry .= 'CASE WHEN LC."idVisitante" IS NULL OR LC."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_CodigoPostal"';
        $qry .= 'ELSE V."DE_CP"';
        $qry .= 'END AS "cp",';
        $qry .= 'CASE WHEN LC."idVisitante" IS NULL OR LC."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Ciudad"';
        $qry .= 'ELSE V."DE_Ciudad"';
        $qry .= 'END AS "ciudad",';
        $qry .= 'CASE WHEN LC."idVisitante" IS NULL OR LC."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Estado"';
        $qry .= 'ELSE V."DE_Estado"';
        $qry .= 'END AS "estado",';
        $qry .= 'CASE WHEN LC."idVisitante" IS NULL OR LC."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Pais"';
        $qry .= 'ELSE V."DE_Pais"';
        $qry .= 'END AS "pais",';
        $qry .= 'LC."TagsContacto" AS "tags",';
        $qry .= '"Ranking" AS "ranking",';
        $qry .= 'CASE WHEN LC."idVisitante" IS NULL OR LC."idVisitante" = 0 THEN ';
        $qry .= 'concat (COALESCE (EMPED."DD_TelefonoAreaPais",\'\'),';
        $qry .= '\' \'||COALESCE (EMPED."DD_TelefonoAreaCiudad", \'\'),';
        $qry .= '\'-\'||COALESCE (EMPED."DD_Telefono",\'\')';
        $qry .= ') ';
        $qry .= 'ELSE ';
        $qry .= 'concat (';
        $qry .= 'COALESCE (V."DE_AreaPais",\'\'),';
        $qry .= '\' \'||COALESCE (V."DE_AreaCiudad", \'\'),';
        $qry .= '\'-\'||COALESCE (V."DE_Telefono", \'\')';
        $qry .= ' ) ';
        $qry .= 'END AS "telefono"';
        $qry .= ' FROM "' . $this->pg_schema_lectoras . '"."LecturaContacto" LC';
        $qry .= ' INNER JOIN "' . $this->pg_schema_lectoras . '"."EmpresaScanner" AS ES ON LC."idEmpresaScanner" = ES."idEmpresaScanner"';
        $qry .= ' AND (ES."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' OR ES."idEdicion" = ' . $args['idEdicionSecond'];
        $qry .= ') AND (ES."idEvento" = ' . $args['idEvento'];
        $qry .= ' OR ES."idEvento" = ' . $args['idEventoSecond'];
        $qry .= ') AND ES."idEmpresa"= ' . $args['idEmpresa'];
        $qry .= ' LEFT JOIN "' . $this->pg_schema_ae . '"."Visitante" V ON LC."idVisitante" = V."idVisitante"';
        $qry .= ' LEFT JOIN "' . $this->pg_schema_sas . '"."DetalleGafete" DG ON LC."idExpositor" = DG."idDetalleGafete"';
        $qry .= ' LEFT JOIN "' . $this->pg_schema_sas . '"."EmpresaEdicion" EMPED  on EMPED."idEmpresa" = DG."idEmpresa"';
        $qry .= ' AND (EMPED."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' OR EMPED."idEdicion" = ' . $args['idEdicionSecond'];
        $qry .= ') AND (EMPED."idEvento" = ' . $args['idEvento'];
        $qry .= ' OR EMPED."idEvento" = ' . $args['idEventoSecond'];
        $qry .= ')  ';
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg;
    }

    public function getLeads($args) {
        $qry = 'SELECT ';
        $qry .= 'COALESCE(ES."EtiquetaApp",\'\') AS "usuario",';
        $qry .= 'to_char(L."Fecha",\'YYYY-MM-DD\') AS "fecha",';
        $qry .= 'to_char(L."Hora",\'HH:MI\') AS "hora",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL THEN ';
        $qry .= 'COALESCE(DG."DGNombre",\'\')';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL  THEN ';
        $qry .= ' COALESCE(V."Nombre",\'\')';
        $qry .= 'END AS "nombre",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'COALESCE(DG."DGApellidoPaterno",\'\')';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL OR L."idDetalleGafete"=0  THEN ';
        $qry .= ' COALESCE(V."ApellidoPaterno",\'\')';
        $qry .= 'END AS "apellidoPaterno",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'COALESCE(DG."DGApellidoMaterno",\'\')';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL OR L."idDetalleGafete"=0  THEN ';
        $qry .= ' COALESCE(V."ApellidoMaterno",\'\')';
        $qry .= 'END AS "apellidoMaterno",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'DG."DGEmpresa"';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL OR L."idDetalleGafete"=0  THEN ';
        $qry .= 'V."DE_RazonSocial"';
        $qry .= 'END AS "empresa",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'DG."DGPuesto"';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL OR L."idDetalleGafete"=0  THEN ';
        $qry .= 'V."DE_Cargo"';
        $qry .= 'END AS "puesto",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'DG."DGEmail"';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL OR L."idDetalleGafete"=0  THEN ';
        $qry .= 'V."Email"';
        $qry .= 'END AS "email",';
        $qry .= 'CASE WHEN ST."idScannerTipo" = 3 THEN ';
        $qry .= '2';
        $qry .= ' END AS "tipoLectora",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_CalleNum"';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL OR L."idDetalleGafete"=0  THEN ';
        $qry .= 'V."DE_Direccion"';
        $qry .= 'END AS "direccion",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Colonia"';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL OR L."idDetalleGafete"=0  THEN ';
        $qry .= ' V."DE_Colonia"';
        $qry .= 'END AS "colonia",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_CodigoPostal"';
        $qry .= 'ELSE V."DE_CP"';
        $qry .= 'END AS "cp",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Ciudad"';
        $qry .= 'ELSE V."DE_Ciudad"';
        $qry .= 'END AS "ciudad",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Estado"';
        $qry .= 'ELSE V."DE_Estado"';
        $qry .= 'END AS "estado",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Pais"';
        $qry .= 'ELSE V."DE_Pais"';
        $qry .= 'END AS "pais",';
        $qry .= ' \'No Tags\' AS "tags",';
        $qry .= ' 0 AS "ranking",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL THEN ';
        $qry .= 'concat (COALESCE (EMPED."DD_TelefonoAreaPais",\'\'),';
        $qry .= '\' \'||COALESCE (EMPED."DD_TelefonoAreaCiudad", \'\'),';
        $qry .= '\'-\'||COALESCE (EMPED."DD_Telefono",\'\')';
        $qry .= ') ';
        $qry .= 'ELSE ';
        $qry .= 'concat (';
        $qry .= 'COALESCE (V."DE_AreaPais",\'\'),';
        $qry .= '\' \'||COALESCE (V."DE_AreaCiudad", \'\'),';
        $qry .= '\'-\'||COALESCE (V."DE_Telefono", \'\')';
        $qry .= ' ) ';
        $qry .= 'END AS "telefono"';
        $qry .= ' FROM "' . $this->pg_schema_lectoras . '"."Lecturas" L';
        $qry .= ' JOIN "' . $this->pg_schema_lectoras . '"."EmpresaScanner" AS ES ON L."idEmpresaScanner" = ES."idEmpresaScanner"';
        $qry .= ' AND (ES."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' OR ES."idEdicion" = ' . $args['idEdicionSecond'];
        $qry .= ') AND (ES."idEvento" = ' . $args['idEvento'];
        $qry .= ' OR ES."idEvento" = ' . $args['idEventoSecond'];
        $qry .= ') AND ES."idEmpresa"= ' . $args['idEmpresa'];
        $qry .= ' JOIN "' . $this->pg_schema_lectoras . '"."Scanner" SC  on ES."idScanner" = SC."idScanner"';
        $qry .= ' JOIN "' . $this->pg_schema_lectoras . '"."ScannerTipo" ST  on SC."idScannerTipo" = ST."idScannerTipo" AND (ST."idScannerTipo"='.$this->tipo_opn_uno.' OR ST."idScannerTipo" = '.$this->tipo_opn_dos.')';
        $qry .= ' LEFT JOIN "' . $this->pg_schema_ae . '"."Visitante" V ON L."idVisitante" = V."idVisitante"';
        $qry .= ' LEFT JOIN "' . $this->pg_schema_sas . '"."DetalleGafete" DG ON L."idDetalleGafete" = DG."idDetalleGafete"';
        $qry .= ' LEFT JOIN "' . $this->pg_schema_sas . '"."EmpresaEdicion" EMPED  on EMPED."idEmpresa" = DG."idEmpresa"';
        $qry .= ' AND (EMPED."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' OR EMPED."idEdicion" = ' . $args['idEdicionSecond'];
        $qry .= ') AND (EMPED."idEvento" = ' . $args['idEvento'];
        $qry .= ' OR EMPED."idEvento" = ' . $args['idEventoSecond'];
        $qry .= ') WHERE ';
        $qry .= ' L."idVisitante">0 ';
        $qry .= ' OR L."idDetalleGafete">0 ';
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg;
    }

    public function getAppsReport($args) {
        $qry = 'SELECT DISTINCT ON (LC."BadgeId") ';
        $qry .= 'COALESCE(ES."EtiquetaApp",\'\') AS "usuario",';
        $qry .= 'to_char(LC."FechaCaptura",\'YYYY-MM-DD\') AS "fecha",';
        $qry .= 'to_char(LC."FechaCaptura",\'HH:MI\') AS "hora",';
        $qry .= 'COALESCE(LC."Nombre",\'\') AS "nombre",';
        $qry .= 'COALESCE(LC."ApellidoPaterno",\'\') AS "apellidoPaterno",';
        $qry .= 'COALESCE(LC."ApellidoMaterno",\'\') AS "apellidoMaterno",';
        $qry .= 'CASE WHEN LC."idVisitante" = 0 THEN ';
        $qry .= 'DG."DGEmpresa"';
        $qry .= 'ELSE V."DE_RazonSocial"';
        $qry .= 'END AS "empresa",';

       /*  $qry .= ' CASE WHEN LC."Cargo"=concat(\' | \',DG."DGEmpresa") THEN ';
        $qry .= ' \'' . $args["textPosition"] . '\'';
        $qry .= ' WHEN LC."Cargo"=concat(\' | \',V."DE_RazonSocial") THEN ';
        $qry .= ' \'' . $args["textPosition"] . '\'';
        $qry .= ' ELSE';
        //$qry .= ' LC."Puesto"';
        $qry .= ' V."DE_Cargo"';
        $qry .= ' END AS "puesto",'; */

        $qry .= 'LC."Cargo" AS "puesto",';
        $qry .= 'LC."Email" AS "email",';
        $qry .= '\'App\' AS "tipoLectora",';
        $qry .= 'CASE WHEN LC."idVisitante" IS NULL OR LC."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_CalleNum"';
        $qry .= 'ELSE V."DE_Direccion"';
        $qry .= 'END AS "direccion",';
        $qry .= 'CASE WHEN LC."idVisitante" IS NULL OR LC."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Colonia"';
        $qry .= 'ELSE V."DE_Colonia"';
        $qry .= 'END AS "colonia",';
        $qry .= 'CASE WHEN LC."idVisitante" IS NULL OR LC."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_CodigoPostal"';
        $qry .= 'ELSE V."DE_CP"';
        $qry .= 'END AS "cp",';
        $qry .= 'CASE WHEN LC."idVisitante" IS NULL OR LC."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Ciudad"';
        $qry .= 'ELSE V."DE_Ciudad"';
        $qry .= 'END AS "ciudad",';
        $qry .= 'CASE WHEN LC."idVisitante" IS NULL OR LC."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Estado"';
        $qry .= 'ELSE V."DE_Estado"';
        $qry .= 'END AS "estado",';
        $qry .= 'CASE WHEN LC."idVisitante" IS NULL OR LC."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Pais"';
        $qry .= 'ELSE V."DE_Pais"';
        $qry .= 'END AS "pais",';
        $qry .= 'LC."TagsContacto" AS "tags",';
        $qry .= 'LC."Notas" AS "comments",';
        $qry .= 'CASE WHEN LC."Ranking" = 0 THEN ';
        $qry .= '\'No Ranking\' ';
        $qry .= 'WHEN LC."Ranking" = 1 THEN';
        $qry .= '\'Hot\' ';
        $qry .= 'WHEN LC."Ranking" = 2 THEN';
        $qry .= '\'Medium\' ';
        $qry .= 'ELSE \'Cold\' ';
        $qry .= 'END AS "ranking",';
        $qry .= 'CASE WHEN LC."idVisitante" IS NULL OR LC."idVisitante" = 0 THEN ';
        $qry .= 'concat (COALESCE (EMPED."DD_TelefonoAreaPais",\'\'),';
        $qry .= '\' \'||COALESCE (EMPED."DD_TelefonoAreaCiudad", \'\'),';
        $qry .= '\'-\'||COALESCE (EMPED."DD_Telefono",\'\')';
        $qry .= ') ';
        $qry .= 'ELSE ';
        $qry .= 'concat (';
        $qry .= 'COALESCE (V."DE_AreaPais",\'\'),';
        $qry .= '\' \'||COALESCE (V."DE_AreaCiudad", \'\'),';
        $qry .= '\'-\'||COALESCE (V."DE_Telefono", \'\')';
        $qry .= ' ) ';
        $qry .= 'END AS "telefono",';
        $qry .= 'LC."Encuesta" AS "encuesta"';
        $qry .= ' FROM "' . $this->pg_schema_lectoras . '"."LecturaContacto" LC';
        $qry .= ' INNER JOIN "' . $this->pg_schema_lectoras . '"."EmpresaScanner" AS ES ON LC."idEmpresaScanner" = ES."idEmpresaScanner"';
        $qry .= ' AND (ES."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' OR ES."idEdicion" = ' . $args['idEdicionSecond'];
        $qry .= ') AND (ES."idEvento" = ' . $args['idEvento'];
        $qry .= ' OR ES."idEvento" = ' . $args['idEventoSecond'];
        $qry .= ') AND ES."idEmpresa"= ' . $args['idEmpresa'];
        $qry .= ' LEFT JOIN "' . $this->pg_schema_ae . '"."Visitante" V ON LC."idVisitante" = V."idVisitante"';
        $qry .= ' LEFT JOIN "' . $this->pg_schema_sas . '"."DetalleGafete" DG ON LC."idExpositor" = DG."idDetalleGafete"';
        $qry .= ' LEFT JOIN "' . $this->pg_schema_sas . '"."EmpresaEdicion" EMPED  on EMPED."idEmpresa" = DG."idEmpresa"';
        $qry .= ' AND (EMPED."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' OR EMPED."idEdicion" = ' . $args['idEdicionSecond'];
        $qry .= ') AND (EMPED."idEvento" = ' . $args['idEvento'];
        $qry .= ' OR EMPED."idEvento" = ' . $args['idEventoSecond'];
        $qry .= ')  ';
        $result_pg = $this->SQLModel->executeQuery($qry);
        // conseguimos la encuesta
        $encuestaCompleta=$this->getEncuesta($args['idEmpresa']);
        // convertirmos a arreglo el json de la encuesta
        $encuestaCompleta=json_decode($encuestaCompleta['data'][0]['encuesta'],True);
        //creamos un campo en el arreglo guardando la encuesta completa
        $result_pg['data']['encuestaCompleta'] = $encuestaCompleta;
        //recorremos a todos los usuarios del arreglo 
        foreach($result_pg['data'] as $clave=>$key ){        
            $result_pg['data'][$clave]['comments']=str_replace("{}","",$key['comments']);
            $concatenar='';
            foreach(json_decode($key['comments'], True) as $claveComentario=>$llaveComentario){
                    //  $result_pg['data'][$clave]['comments'].=$llaveComentario['Descripcion'];
                    $concatenar .=$llaveComentario['Descripcion'].'|';
                }
                $result_pg['data'][$clave]['comments']=trim($concatenar, '|');
        // si existe la posicion en el arreglo entra
                if($key['encuesta']){
        // si la encuesta esta vacia no entra 
                    if($key['encuesta'] !=="{}"){
        //recorremos todas las preguntas de la encuesta
                        foreach($result_pg['data']['encuestaCompleta'] as $valor=>$llave ){        
        //creamos la posicion en el arreglo con el nombre de la pregunta 
                            $result_pg['data'][$clave][$llave['Descripcion']]="";
        //ahora se recorre el json con la encuesta que contesto la persona 
                            foreach(json_decode($key['encuesta'], True) as $claveEncuesta=>$llaveEncuesta){
        //Si las llaves coincide con las llaves encuesta completa se guarda la respuesta de la pregunta de lo contrario sera igual a vacio
                                    if($llaveEncuesta['Pregunta']==$llave['Descripcion']){
                                        $result_pg['data'][$clave][$llave['Descripcion']]=str_replace(",","|",$llaveEncuesta['Respuestas']);
                                    }
                            }
                        }
                    }
                }
        //elimina la posicion encuesta del arreglo para imprimir el reporte bien
                unset($result_pg['data'][$clave]['encuesta'] );
            }
        //elimina la posicion encuestaCompleta del arreglo para imprimir el reporte bien
        unset($result_pg['data']['encuestaCompleta'] );
        return $result_pg;
    }

    public function getLeadsReport($args) {
        $qry = 'SELECT ';
        $qry .= 'COALESCE(ES."EtiquetaApp",\'\') AS "usuario",';
        $qry .= 'to_char(L."Fecha",\'YYYY-MM-DD\') AS "fecha",';
        $qry .= 'to_char(L."Hora",\'HH:MI\') AS "hora",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'COALESCE(DG."DGNombre",\'\')';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL OR L."idDetalleGafete"=0  THEN ';
        $qry .= ' COALESCE(V."Nombre",\'\')';
        $qry .= 'END AS "nombre",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'COALESCE(DG."DGApellidoPaterno",\'\')';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL OR L."idDetalleGafete"=0  THEN ';
        $qry .= ' COALESCE(V."ApellidoPaterno",\'\')';
        $qry .= 'END AS "apellidoPaterno",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'COALESCE(DG."DGApellidoMaterno",\'\')';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL OR L."idDetalleGafete"=0  THEN ';
        $qry .= ' COALESCE(V."ApellidoMaterno",\'\')';
        $qry .= 'END AS "apellidoMaterno",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'DG."DGEmpresa"';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL OR L."idDetalleGafete"=0  THEN ';
        $qry .= 'V."DE_RazonSocial"';
        $qry .= 'END AS "empresa",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'DG."DGPuesto"';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL OR L."idDetalleGafete"=0  THEN ';
        $qry .= 'V."DE_Cargo"';
        $qry .= 'END AS "puesto",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'DG."DGEmail"';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL OR L."idDetalleGafete"=0  THEN ';
        $qry .= 'V."Email"';
        $qry .= 'END AS "email",';
        $qry .= 'CASE WHEN ST."idScannerTipo" = 3 THEN ';
        $qry .= ' \'Mini Scan Wireless (OPN)\' ';
        //$qry .= ' \'MI Tracker\' ';
        $qry .= 'END AS "tipoLectora",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_CalleNum"';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL OR L."idDetalleGafete"=0  THEN ';
        $qry .= 'V."DE_Direccion"';
        $qry .= 'END AS "direccion",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Colonia"';
        $qry .= 'WHEN L."idDetalleGafete" IS NULL OR L."idDetalleGafete"=0  THEN ';
        $qry .= ' V."DE_Colonia"';
        $qry .= 'END AS "colonia",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_CodigoPostal"';
        $qry .= 'ELSE V."DE_CP"';
        $qry .= 'END AS "cp",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Ciudad"';
        $qry .= 'ELSE V."DE_Ciudad"';
        $qry .= 'END AS "ciudad",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Estado"';
        $qry .= 'ELSE V."DE_Estado"';
        $qry .= 'END AS "estado",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL OR L."idVisitante"=0 THEN ';
        $qry .= 'EMPED."DD_Pais"';
        $qry .= 'ELSE V."DE_Pais"';
        $qry .= 'END AS "pais",';
        $qry .= ' \'No Tags\' AS "tags",';
        $qry .= ' L."Comments" AS "comments",';
        $qry .= ' \'No Ranking\' AS "ranking",';
        $qry .= 'CASE WHEN L."idVisitante" IS NULL THEN ';
        $qry .= 'concat (COALESCE (EMPED."DD_TelefonoAreaPais",\'\'),';
        $qry .= '\' \'||COALESCE (EMPED."DD_TelefonoAreaCiudad", \'\'),';
        $qry .= '\'-\'||COALESCE (EMPED."DD_Telefono",\'\')';
        $qry .= ') ';
        $qry .= 'ELSE ';
        $qry .= 'concat (';
        $qry .= 'COALESCE (V."DE_AreaPais",\'\'),';
        $qry .= '\' \'||COALESCE (V."DE_AreaCiudad", \'\'),';
        $qry .= '\'-\'||COALESCE (V."DE_Telefono", \'\')';
        $qry .= ' ) ';
        $qry .= 'END AS "telefono"';
        $qry .= ' FROM "' . $this->pg_schema_lectoras . '"."Lecturas" L';
        $qry .= ' JOIN "' . $this->pg_schema_lectoras . '"."EmpresaScanner" AS ES ON L."idEmpresaScanner" = ES."idEmpresaScanner"';
        $qry .= ' AND (ES."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' OR ES."idEdicion" = ' . $args['idEdicionSecond'];
        $qry .= ') AND (ES."idEvento" = ' . $args['idEvento'];
        $qry .= ' OR ES."idEvento" = ' . $args['idEventoSecond'];
        $qry .= ') AND ES."idEmpresa"= ' . $args['idEmpresa'];
        $qry .= ' JOIN "' . $this->pg_schema_lectoras . '"."Scanner" SC  on ES."idScanner" = SC."idScanner"';
        $qry .= ' JOIN "' . $this->pg_schema_lectoras . '"."ScannerTipo" ST  on SC."idScannerTipo" = ST."idScannerTipo" AND (ST."idScannerTipo"=3)';
        $qry .= ' LEFT JOIN "' . $this->pg_schema_ae . '"."Visitante" V ON L."idVisitante" = V."idVisitante"';
        $qry .= ' LEFT JOIN "' . $this->pg_schema_sas . '"."DetalleGafete" DG ON L."idDetalleGafete" = DG."idDetalleGafete"';
        $qry .= ' LEFT JOIN "' . $this->pg_schema_sas . '"."EmpresaEdicion" EMPED  on EMPED."idEmpresa" = DG."idEmpresa"';
        $qry .= ' AND (EMPED."idEdicion" = ' . $args['idEdicion'];
        $qry .= ' OR EMPED."idEdicion" = ' . $args['idEdicionSecond'];
        $qry .= ') AND (EMPED."idEvento" = ' . $args['idEvento'];
        $qry .= ' OR EMPED."idEvento" = ' . $args['idEventoSecond'];
        $qry .= ') WHERE ';
        $qry .= ' L."idVisitante">0 ';
        $qry .= ' OR L."idDetalleGafete">0 ';
        
        $result_pg = $this->SQLModel->executeQuery($qry);
        return $result_pg;
    }

    public function getEncuesta($args){
        $qry = 'SELECT "Encuesta" AS encuesta ';
        $qry .= ' FROM "' . $this->pg_schema_lectoras . '"."Encuesta"';
        $qry .= ' WHERE "idEmpresa" ='. $args ;
        $result = $this->SQLModel->executeQuery($qry);
        return $result;
    }

}
