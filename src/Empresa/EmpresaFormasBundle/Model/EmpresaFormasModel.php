<?php

namespace Empresa\EmpresaFormasBundle\Model;

/**
 *
 * @author Eduardo Cervantes <eduardoc@infoexpo.com.mx>
 */
use ShowDashboard\DashboardBundle\Model\DashboardModel;

class EmpresaFormasModel extends DashboardModel {

    public function getFormas($where, $lang) {
        $fields = Array(
            "idForma",
            "idSeccionFormatos",
            "NombreForma" . strtoupper($lang),
            "FormaPago" . strtoupper($lang),
            "Bloqueado",
            "FechaLimite",
            "LinkEDForma"
        );
        $where['TipoLink'] = 1;
        $result = $this->SQLModel->selectFromTable("Forma", $fields, $where, Array("idForma" => "ASC"));
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = Array();
        foreach ($result['data'] as $forma) {
            $data[$forma['idForma']] = $forma;
        }
        return $data;
    }

    public function getEmpresaFormas($where) {
        $qry = 'SELECT ef."idForma", ef."Bloqueado", ef."FechaModificacionWeb", ef."FechaPrimerGuardado",';
        $qry .= ' ef."Lang", ef."StatusForma" FROM "SAS"."EmpresaForma" AS ef';
        $qry .= ' INNER JOIN "SAS"."Forma" as f ON ef."idForma" = f."idForma"';
        $qry .= ' WHERE f."TipoLink" = 1 AND ef."idEdicion" = ' . $where['idEdicion'];
        $qry .= ' AND ef."idEdicion" = f."idEdicion" AND ef."idEmpresa" = ' . $where['idEmpresa'];
        $qry .= ' ORDER BY ef."idForma" ASC';
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = Array();
        foreach ($result['data'] as $forma) {
            $data[$forma['idForma']] = $forma;
        }
        return $data;
    }

    public function getToken($where) {
        $result = $this->SQLModel->selectFromTable("EmpresaEdicion", Array('Token'), $where);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        if ($result['data'][0]["Token"] == null || $result['data'][0]["Token"] == "") {
            throw new \Exception("Error! Not get token form exhibitor " . $where['idEmpresa'], 409);
        }
        return $result['data'][0]["Token"];
    }

    public function getCompanyHeader($args = "") {
        $qry = ' SELECT e."idEmpresa", e."DC_NombreComercial", e."CodigoCliente" ';
        $qry .= ' FROM "SAS"."Empresa" e';
        $qry .= ' WHERE e."idEmpresa" = ' . $args['e."idEmpresa"'];
        $result = $this->SQLModel->executeQuery($qry);
        if (isset($result['status']) && $result['status'] == 1) {
            $qry = ' SELECT ee."idEtapa", ee."EMSTDListadoStand", ee."idPaquete", ee."Nombre", ee."Email", ee."Password"';
            $qry .= ' FROM "SAS"."vw_sas_ObtenerEmpresas" ee';
            $qry .= ' WHERE ee."idEmpresa" = ' . $args['e."idEmpresa"'] . ' AND ee."idEdicion" = ' . $args['ee."idEdicion"'];
            $result2 = $this->SQLModel->executeQuery($qry);
            if (isset($result2['status']) && $result2['status'] == 1 && isset($result2['data'][0])) {
                $data = array_merge($result["data"][0], $result2["data"][0]);
                return $data;
            } else {
                return $result["data"][0];
            }
        } else
            return Array("status" => FALSE, "data" => $result_pg['status']);
    }

    public function getPackages($args, $lang) {
        $qry = ' SELECT "idPaquete", "Paquete' . strtoupper($lang) . '" ';
        $qry .= ' FROM "SAS"."Paquete" ';
        $qry .= ' WHERE "idEdicion" = ' . $args['idEdicion'];
        $qry .= ' ORDER BY "idPaquete"';
        $result = $this->SQLModel->executeQuery($qry);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        $data = Array();
        if (COUNT($result['data']) > 0) {
            foreach ($result['data'] as $value) {
                $data[$value['idPaquete']] = $value;
            }
        }
        return $data;
    }

    public function getFormasPaquete($data) {
        $where = Array(
            "idEvento" => $data['idEvento'],
            "idEdicion" => $data['idEdicion']
        );
        $result = $this->SQLModel->selectFromTable("FormaPaquete", Array(), $where);
        if (!$result['status']) {
            die($result['data']);
        }
        $fopq = Array();
        foreach ($result['data'] as $key => $value) {
            $fopq[$value['idPaquete']][$key] = $value['idForma'];
        }
        return $fopq;
    }

    public function getAditionalDetail($where) {
        $result = $this->SQLModel->selectFromTable("EmpresaEdicion", Array("EmpresaAdicional"), $where);
        if (!$result['status']) {
            throw new \Exception($result['data'], 409);
        }
        if ($result['data'][0]["EmpresaAdicional"] > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function actualizarBloqueo($data) {
        $qry = 'SELECT * FROM "SAS"."fn_sas_BloqueaEmpresaForma"(' . $data['idEmpresa'] . ', ' . $data['idForma'] . ', ' . $data['Bloqueado'] . ', ' . $data['idEvento'] . ', ' . $data['idEdicion'] . ');';
        $result = $this->SQLModel->executeQuery($qry);

        if (!$result['status']) {
            die($result['data']);
        }
    }

}
