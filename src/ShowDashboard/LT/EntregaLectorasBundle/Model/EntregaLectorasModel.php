<?php

namespace ShowDashboard\LT\EntregaLectorasBundle\Model;

use ShowDashboard\LT\LectorasBundle\Model\LectorasModel;
/**
 *
 * @author Eduardo Cervantes <eduardoc@infoexpo.com.mx>
 */
use Utilerias\SQLBundle\Model\SQLModel;

class EntregaLectorasModel extends LectorasModel {

    protected $SQLModel;

    public function __construct() {
        $this->SQLModel = new SQLModel();
        $this->SQLModel->setSchema("SAS");
    }

    public function getProductosLectoras($lang, $where) {
        $fields = $this->getCamposProductos($lang);
        $result = $this->SQLModel->selectFromTable("EquipoAdicional", $fields, $where);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $equipos = Array();
        foreach ($result['data'] as $value) {
            $equipos[$value['idEquiposAdicionales']] = $value;
        }
    }

    private function getCamposProductos($lang) {
        return Array(
            "idEquipoAdicional"
        );
    }

    public function getSolicitudesLectoras($where) {
        $fields = $this->getCamposSolicitudes();
        $result = $this->SQLModel->selectFromTable("DetalleServicio", $fields);
    }

    private function getCamposSolicitudes($where) {
        return Array();
    }

    public function getEmpresaForma($where, $idContacto) {
        $fields = $this->getCamposEmpresaForma();
        /* ---  consulta empresa forma y regresa el resultado  --- */
        $result = $this->EmpresaForma($fields, $where);
        /* ---  en caso de no traer empresa forma inserta una nueva empresaForma  --- */
        if (COUNT($result['data']) == 0) {
            /* ---  agregamos una variable para insertar los valores es importante poner el idContacto y el status de la forma  --- */
            $values = $where;
            $values['idContacto'] = $idContacto;
            $values['StatusForma'] = 0;
            $values['Bloqueado'] = $this->getFormaStatus($where); /* ---  consiltamos la forma para saber en que estatus de bloqueado debe de estar  --- */
            $insert = $this->SQLModel->insertIntoTable("EmpresaForma", $values, "idEmpresaForma");
            if (!$insert['status']) {
                throw new \Exception($result['data'], 409);
            }
            /* ---  agregamos el idEmpresaForma y consultamos la EmpresaForma para regresar los detalles y así poder actualizarlos  --- */
            $where['idEmpresaForma'] = $insert['data'][0]['idEmpresaForma'];
            $result = $this->EmpresaForma($fields, $where);
        }
        return $result['data'][0];
    }

    private function getCamposEmpresaForma() {
        return Array(
            "DetallePagoJSON",
            "DetalleServicioJSON",
            "DetalleEntregaScannerJSON",
            "IVA",
            "Lang",
            "StatusPago",
            "Subtotal",
            "Total"
        );
    }

    private function EmpresaForma($fields, $where) {
        // print_r($fields); die();
        $result = $this->SQLModel->selectFromTable("EmpresaForma", $fields, $where);

        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        return $result;
    }

    private function getFormaStatus($args) {
        $where = Array(
            "idForma" => $args['idForma'],
            "idEvento" => $args['idEvento'],
            "idEdicion" => $args['idEdicion']
        );
        $result = $this->SQLModel->selectFromTable("Forma", Array("Bloqueado"), $where, $order);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        return $result['data'][0]['Bloqueado'];
    }

    public function getLectoras($where) {
        /* ---  consultamos todas las las lectoras que tenga la empresa aunque no tengan un equipo adicional por ejemplo la app movil  --- */
        $qry = 'SELECT DISTINCT ' . $this->getCamposLectoras(FALSE) . ' FROM "LECTORAS"."EmpresaScanner" AS el ';
        $qry .= 'INNER JOIN "LECTORAS"."Scanner" AS l ON el."idScanner" = l."idScanner" ';
        $qry .= 'INNER JOIN "LECTORAS"."ScannerTipo" AS lt ON l."idScannerTipo" = lt."idScannerTipo" ';
        $qry .= 'WHERE el."idEvento" = ' . $where['idEvento'] . ' AND el."idEdicion" = ' . $where['idEdicion'] . ' AND el."idEmpresa" = ' . $where['idEmpresa'];
        $qry .= ' AND lt."idEvento" = ' . $where['idEvento'] . ' AND lt."idEdicion" = ' . $where['idEdicion'];
        $qry .= ' ORDER BY lt."idScannerTipo" ASC;';
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        /* ---  obtenemos las diferentes lectoras por empresa pero sin cantidad pero que tengan un servicio   --- */
        $qry = 'SELECT DISTINCT ' . $this->getCamposLectoras(TRUE) . ' FROM "LECTORAS"."EmpresaScanner" AS el ';
        $qry .= 'INNER JOIN "LECTORAS"."Scanner" AS l ON el."idScanner" = l."idScanner" ';
        $qry .= 'INNER JOIN "LECTORAS"."ScannerTipo" AS lt ON l."idScannerTipo" = lt."idScannerTipo" ';
        $qry .= 'INNER JOIN "LECTORAS"."EquipoAdicional" AS ea ON lt."idScannerTipo" = ea."idScannerTipo" ';
        $qry .= 'WHERE el."idEvento" = ' . $where['idEvento'] . ' AND el."idEdicion" = ' . $where['idEdicion'] . ' AND el."idEmpresa" = ' . $where['idEmpresa'];
        $qry .= ' AND lt."idEvento" = ' . $where['idEvento'] . ' AND lt."idEdicion" = ' . $where['idEdicion'];
        $qry .= ' AND el."idEdicion" = ea."idEdicion" ORDER BY lt."idScannerTipo" ASC, ea."idEquipoAdicional" ASC;';
        $resultEquios = $this->SQLModel->executeQuery($qry);
        if (!$resultEquios['status']) {
            throw new \Exception($resultEquios['data'], 409);
        }
        $equiposLectoras = Array();
        foreach ($resultEquios['data'] as $equipos) {
            $equiposLectoras[$equipos["idScannerTipo"]][$equipos["idEquipoAdicional"]] = $equipos;
        }
        /* ---  obtenemos la cantidad de tipo de lectora por expositor  --- */
        $qry = 'SELECT lt."idScannerTipo", COUNT(el."idEmpresaScanner") AS "CantidadScanners" ';
        $qry .= 'FROM "LECTORAS"."EmpresaScanner" AS el ';
        $qry .= 'INNER JOIN "LECTORAS"."Scanner" AS l ON el."idScanner" = l."idScanner" ';
        $qry .= 'INNER JOIN "LECTORAS"."ScannerTipo" AS lt ON l."idScannerTipo" = lt."idScannerTipo" ';
        $qry .= 'WHERE el."idEvento" = ' . $where['idEvento'] . ' AND el."idEdicion" = ' . $where['idEdicion'] . ' AND el."idEmpresa" = ' . $where['idEmpresa'];
        $qry .= ' AND lt."idEvento" = ' . $where['idEvento'] . ' AND lt."idEdicion" = ' . $where['idEdicion'];
        $qry .= ' GROUP BY lt."idScannerTipo";';
        $resultCantidad = $this->SQLModel->executeQuery($qry);
        if (!$resultCantidad['status']) {
            throw new \Exception($resultCantidad['data'], 409);
        }
        $cantidadLectoras = Array();
        foreach ($resultCantidad['data'] as $qty) {
            $cantidadLectoras[$qty["idScannerTipo"]] = $qty['CantidadScanners'];
        }

        $empresaLecotora = Array();
        foreach ($result['data'] as $value) {
            $empresaLecotora[$value["idScannerTipo"]]['ScannerDetalle'][$value["idEmpresaScanner"]]['CodigoScanner'] = $value['CodigoScanner'];
            $empresaLecotora[$value["idScannerTipo"]]['CantidadScanners'] = $cantidadLectoras[$value["idScannerTipo"]];
            $empresaLecotora[$value["idScannerTipo"]]['ScannerTipo'] = $value['ScannerTipo'];
            $empresaLecotora[$value["idScannerTipo"]]['PrecioScanner'] = $value['PrecioScanner'];
            $empresaLecotora[$value["idScannerTipo"]]['MonedaScanner'] = $value['MonedaScanner'];
            if (isset($equiposLectoras[$value["idScannerTipo"]])) {
                foreach ($equiposLectoras[$value["idScannerTipo"]] as $equipo) {
                    $empresaLecotora[$value["idScannerTipo"]]["Equipos"][$equipo['idEquipoAdicional']]['EquipoAdicional'] = $equipo['EquipoAdicional'];
                    $empresaLecotora[$value["idScannerTipo"]]["Equipos"][$equipo['idEquipoAdicional']]['Descripcion'] = $equipo['Descripcion'];
                    $empresaLecotora[$value["idScannerTipo"]]["Equipos"][$equipo['idEquipoAdicional']]['Moneda'] = $equipo['Moneda'];
                    $empresaLecotora[$value["idScannerTipo"]]["Equipos"][$equipo['idEquipoAdicional']]['Precio'] = $equipo['Precio'];
                }
            }
        }
        return $empresaLecotora;
    }

    private function getCamposLectoras($all = false) {
        $fields = 'lt."idScannerTipo", ';
        $fields .= 'el."idEmpresaScanner", ';
        if ($all) {
            $fields .= 'ea."idEquipoAdicional", ';
            $fields .= 'ea."EquipoAdicional", ';
            $fields .= 'ea."Descripcion", ';
            $fields .= 'ea."Moneda", ';
            $fields .= 'ea."Precio" ';
        } else {
            $fields .= 'l."CodigoScanner", ';
            $fields .= 'el."idStatusScanner", ';
            $fields .= 'lt."ScannerTipo", ';
            $fields .= 'lt."PrecioScanner", ';
            $fields .= 'lt."MonedaScanner" ';
        }
        return $fields;
    }

    public function actualizaStatus($detalleEntrega, $where) {
        $this->SQLModel->setSchema("LECTORAS");
        if ($detalleEntrega['EquiposDevueltos'] != null && $detalleEntrega['EquiposDevueltos'] != "") {
            unset($where['idForma']);
            foreach ($detalleEntrega['EquiposDevueltos'] as $idLT => $lectoraTipo) {
                foreach ($lectoraTipo['ScannerDetalle'] as $idDT => $detalleLectora) {
                    $values = Array("idStatusScanner" => 2);
                    $where["idEmpresaScanner"] = $idDT;
                    $result = $this->SQLModel->updateFromTable("EmpresaScanner", $values, $where);
                    if (!$result['status']) {
                        die($result['data']);
                    }
                }
                foreach ($lectoraTipo['ScannersNoDevueltos'] as $idDT => $detalleLectora) {
                    $values = Array("idStatusScanner" => 1);
                    $where["idEmpresaScanner"] = $idDT;
                    $result = $this->SQLModel->updateFromTable("EmpresaScanner", $values, $where);
                    if (!$result['status']) {
                        die($result['data']);
                    }
                }
            }
        }
    }

    public function detalleEntrega($data, $where) {
        $this->SQLModel->setSchema("SAS");
        $values = $this->setValues($data);

        $result = $this->SQLModel->updateFromTable("EmpresaForma", $values, $where);
        if (!$result['status']) {
            die($result['data']);
        }
    }

    private function setValues($data) {
        $values = Array();
        foreach ($data as $key => $value) {
            $values[$key] = "'" . $value . "'";
        }
        return $values;
    }

}
