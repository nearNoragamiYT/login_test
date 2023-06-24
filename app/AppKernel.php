<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{

    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new LoginBundle\LoginBundle(),
            new Utilerias\HandleErrorBundle\UtileriasHandleErrorBundle(),
            new AdministracionGlobal\ComiteOrganizadorBundle\AdministracionGlobalComiteOrganizadorBundle(),
            new AdministracionGlobal\PlataformaBundle\AdministracionGlobalPlataformaBundle(),
            new AdministracionGlobal\ComitePersonalBundle\AdministracionGlobalComitePersonalBundle(),
            new AdministracionGlobal\EdicionBundle\AdministracionGlobalEdicionBundle(),
            new AdministracionGlobal\EventoBundle\AdministracionGlobalEventoBundle(),
            new AdministracionGlobal\EntidadFiscalBundle\AdministracionGlobalEntidadFiscalBundle(),
            new AdministracionGlobal\LogBundle\AdministracionGlobalLogBundle(),
            new Utilerias\PECCBundle\UtileriasPECCBundle(),
            new AdministracionGlobal\ModuloBundle\AdministracionGlobalModuloBundle(),
            new Liuggio\ExcelBundle\LiuggioExcelBundle(),
            new Wizard\InformacionGeneralBundle\WizardInformacionGeneralBundle(),
            new Wizard\ComiteOrganizadorBundle\WizardComiteOrganizadorBundle(),
            new Wizard\EntidadFiscalBundle\WizardEntidadFiscalBundle(),
            new Wizard\ContactoBundle\WizardContactoBundle(),
            new Wizard\EventoBundle\WizardEventoBundle(),
            new Wizard\EdicionBundle\WizardEdicionBundle(),
            new Wizard\ProductoBundle\WizardProductoBundle(),
            new Wizard\UsuarioBundle\WizardUsuarioBundle(),
            new Wizard\WizardBundle\WizardWizardBundle(),
            new AdministracionGlobal\ProductoBundle\AdministracionGlobalProductoBundle(),
            new ShowDashboard\DashboardBundle\ShowDashboardDashboardBundle(),
            new ShowDashboard\ED\Formas\EditorFormaBundle\ShowDashboardEDFormasEditorFormaBundle(),
            new Empresa\EmpresaBundle\EmpresaEmpresaBundle(),
            new Empresa\EmpresaFiscalBundle\EmpresaEmpresaFiscalBundle(),
            new ShowDashboard\ED\Formas\AdministradorFormasBundle\ShowDashboardEDFormasAdministradorFormasBundle(),
            new Utilerias\AdministradorTextosBundle\UtileriasAdministradorTextosBundle(),
            new ShowDashboard\FP\FloorplanBundle\ShowDashboardFPFloorplanBundle(),
            new Empresa\ContratoBundle\EmpresaContratoBundle(),
            new Empresa\EmpresaComercialBundle\EmpresaEmpresaComercialBundle(),
            new Empresa\EmpresaContratoBundle\EmpresaEmpresaContratoBundle(),
            new WhiteOctober\TCPDFBundle\WhiteOctoberTCPDFBundle(),
            new ShowDashboard\ED\MainBundle\ShowDashboardEDMainBundle(),
            new ShowDashboard\FP\MainBundle\ShowDashboardFPMainBundle(),
            new ShowDashboard\FT\MainBundle\ShowDashboardFTMainBundle(),
            new ShowDashboard\AE\MainBundle\ShowDashboardAEMainBundle(),
            new ShowDashboard\ST\MainBundle\ShowDashboardSTMainBundle(),
            new AdministracionGlobal\MainBundle\AdministracionGlobalMainBundle(),
            new Empresa\EmpresaContactoBundle\EmpresaEmpresaContactoBundle(),
            new AdministracionGlobal\UsuarioBundle\AdministracionGlobalUsuarioBundle(),
            new CuentaBundle\CuentaBundle(),
            new Empresa\VentasBundle\EmpresaVentasBundle(),
            new ShowDashboard\AE\Administracion\LoginBundle\ShowDashboardAEAdministracionLoginBundle(),
            new ShowDashboard\AE\Administracion\DatosGeneralesBundle\ShowDashboardAEAdministracionDatosGeneralesBundle(),
            new ShowDashboard\AE\Administracion\ComprobanteBundle\ShowDashboardAEAdministracionComprobanteBundle(),
            new ShowDashboard\AE\Administracion\RedSocialBundle\ShowDashboardAEAdministracionRedSocialBundle(),
            new ShowDashboard\AE\Administracion\ConfiguracionBundle\ShowDashboardAEAdministracionConfiguracionBundle(),
            new ShowDashboard\AE\AdministradorTextos\TemplateBundle\ShowDashboardAEAdministradorTextosTemplateBundle(),
            new ShowDashboard\AE\AdministradorTextos\TemplateTextoBundle\ShowDashboardAEAdministradorTextosTemplateTextoBundle(),
            new ShowDashboard\FP\GraphicBundle\ShowDashboardFPGraphicBundle(),
            new Empresa\ReportesBundle\EmpresaReportesBundle(),
            new Empresa\EmpresaDatosAdicionalesBundle\EmpresaEmpresaDatosAdicionalesBundle(),
            new Empresa\EmpresaSolicitudModificacionBundle\EmpresaEmpresaSolicitudModificacionBundle(),
            new Empresa\EmpresaFichaMontajeBundle\EmpresaEmpresaFichaMontajeBundle(),
            new Empresa\EMarketingBundle\EmpresaEMarketingBundle(),
            new Empresa\EmpresaGafetesBundle\EmpresaEmpresaGafetesBundle(),
            new Empresa\EmpresaFormasBundle\EmpresaEmpresaFormasBundle(),
            new ShowDashboard\AE\Administracion\Encuesta\ExportEncuestaBundle\ShowDashboardAEAdministracionEncuestaExportEncuestaBundle(),
            new MS\FloorplanBundle\MSFloorplanBundle(),
            new StatLink\EstadisticaBundle\StatLinkEstadisticaBundle(),
            new Empresa\SolicitudPaqueteBundle\EmpresaSolicitudPaqueteBundle(),
            new ShowDashboard\AE\Administracion\Encuesta\Constructor\EncuestaBundle\ShowDashboardAEAdministracionEncuestaConstructorEncuestaBundle(),
            new ShowDashboard\AE\Administracion\Encuesta\Constructor\PreguntasBundle\ShowDashboardAEAdministracionEncuestaConstructorPreguntasBundle(),
            new ShowDashboard\AE\Administracion\Configuracion\SyncFMPGBundle\VisitanteSyncFMPGBundle(),
            new ShowDashboard\AE\Administracion\Configuracion\AjustesBundle\ShowDashboardAEAdministracionConfiguracionAjustesBundle(),
            new ShowDashboard\AE\Administracion\Configuracion\ConexionBDBundle\ShowDashboardAEAdministracionConfiguracionConexionBDBundle(),
            new ShowDashboard\AE\Administracion\Configuracion\EstilosBundle\ShowDashboardAEAdministracionConfiguracionEstilosBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new WebService\RestBundle\WebServiceRestBundle(),
            new ShowDashboard\CRM\EmpresasAsignadasBundle\ShowDashboardCRMEmpresasAsignadasBundle(),
            new ShowDashboard\CRM\MainBundle\ShowDashboardCRMMainBundle(),
            new ShowDashboard\CRM\AsesoresComercialesBundle\ShowDashboardCRMAsesoresComercialesBundle(),
            new MS\ApiBundle\MSApiBundle(),
            new Visitante\PrensaBundle\VisitantePrensaBundle(),
            new Visitante\RegistroMultipleBundle\VisitanteRegistroMultipleBundle(),
            new Visitante\EncuentroDeNegociosBundle\VisitanteEncuentroDeNegociosBundle(),
            new Visitante\VisitanteBundle\VisitanteVisitanteBundle(),
            new Visitante\VisitantesGeneralesBundle\VisitanteVisitantesGeneralesBundle(),
            new ShowDashboard\FP\AppBundle\ShowDashboardFPAppBundle(),
            new Visitante\DatosGeneralesBundle\VisitanteDatosGeneralesBundle(),
            new Visitante\PerfilBundle\VisitantePerfilBundle(),
            new Visitante\AsociadoBundle\VisitanteAsociadoBundle(),
            new Visitante\ComprasBundle\VisitanteComprasBundle(),
            new ShowDashboard\RS\AdminRSBundle\ShowDashboardRSAdminRSBundle(),
            new ShowDashboard\RS\AsistenteRSBundle\ShowDashboardRSAsistenteRSBundle(),
            new ShowDashboard\RS\ArchivosRSBundle\ShowDashboardRSArchivosRSBundle(),
            new ShowDashboard\RS\EstadisticasRSBundle\ShowDashboardRSEstadisticasRSBundle(),
            new ShowDashboard\RS\MainBundle\ShowDashboardRSMainBundle(),
            new ShowDashboard\RS\AdminProdRSBundle\ShowDashboardRSAdminProdRSBundle(),
            new ShowDashboard\RS\VisitanteBundle\ShowDashboardRSVisitanteBundle(),
            new ShowDashboard\RS\DatosGeneralesBundle\ShowDashboardRSDatosGeneralesBundle(),
            new ShowDashboard\RS\AdminEncuestaBundle\ShowDashboardRSAdminEncuestaBundle(),
            new ShowDashboard\RS\ReporteComprasBundle\ShowDashboardRSReporteComprasBundle(),
            new ShowDashboard\FT\FacturacionBundle\ShowDashboardFTFacturacionBundle(),
            new ShowDashboard\FT\ConfiguracionBundle\ShowDashboardFTConfiguracionBundle(),
            new ShowDashboard\FT\DatosFiscalesBundle\ShowDashboardFTDatosFiscalesBundle(),
            new Empresa\EmpresaInvitacionesBundle\EmpresaEmpresaInvitacionesBundle(),
            new ShowDashboard\LT\LectorasBundle\ShowDashboardLTLectorasBundle(),
            new ShowDashboard\LT\SolicitudLectorasBundle\ShowDashboardLTSolicitudLectorasBundle(),
            new ShowDashboard\LT\EntregaLectorasBundle\ShowDashboardLTEntregaLectorasBundle(),
            new ShowDashboard\LT\MainBundle\ShowDashboardLTMainBundle(),
            new Visitante\ReportesBundle\VisitanteReportesBundle(),
            new StatLink\DemographicsBundle\StatLinkDemographicsBundle(),
            new Visitante\CompradorBundle\VisitanteCompradorBundle(),
            new ShowDashboard\LT\ReportesBundle\ShowDashboardLTReportesBundle(),
            new Visitante\ComprasTorneoBundle\VisitanteComprasTorneoBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        $this->crearCarpetasPublicas($bundles);
        $this->crearCarpetasCache();
        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__) . '/var/cache/' . $this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__) . '/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
    }

    private function crearCarpetasPublicas($bundles)
    {
        /* Carpetas Publicas Assets Bundles */
        $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'web' . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR;
        if (count($bundles) > 0) {
            foreach ($bundles as $key => $bundle) {
                if (!in_array(explode("\\", get_class($bundle))[0], array('Symfony', "Sensio", "Doctrine", "Utilerias"))) {
                    $estructuraBundle = explode("\\", get_class($bundle));
                    unset($estructuraBundle[count($estructuraBundle) - 1]);
                    $directorio = $dir . join(DIRECTORY_SEPARATOR, $estructuraBundle) . DIRECTORY_SEPARATOR;
                    $carpeta = $directorio . DIRECTORY_SEPARATOR;
                    $this->crearCarpeta($carpeta . "css");
                    $this->crearCarpeta($carpeta . "js");
                }
            }
        }
    }

    private function crearCarpetasCache()
    {
        /* Carpetas temporales para el cache */
        $carpetasCache = array();

        $dir = dirname(__DIR__) . '/var/cache/';
        $carpetasCache[] = $dir . 'textos/';
        $carpetasCache[] = $dir . 'estadistica/';
        $carpetasCache[] = $dir . 'pecc/';
        $carpetasCache[] = $dir . 'web_service/';
        $carpetasCache[] = $dir . 'fp/';
        $carpetasCache[] = $dir . 'prod/';

        $dir = dirname(__DIR__) . '/web/';
        $carpetasCache[] = $dir . 'images/logos-co/';
        $carpetasCache[] = $dir . 'images/logos-co/header/';
        $carpetasCache[] = $dir . 'images/sponsor/';
        $carpetasCache[] = $dir . 'images/sponsor/ae/';
        $carpetasCache[] = $dir . 'administrador/secciones/';
        $carpetasCache[] = $dir . 'administrador/formas/';
        $carpetasCache[] = $dir . 'administrador/contratos/';
        $carpetasCache[] = $dir . 'administrador/fichas/';

        if (count($carpetasCache) > 0) {
            foreach ($carpetasCache as $key => $carpeta) {
                $this->crearCarpeta($carpeta);
            }
        }
    }

    private function crearCarpeta($carpeta)
    {
        if (!file_exists($carpeta)) {
            if (!mkdir($carpeta, 0777, true)) {
                throw new \Exception('Fallo al crear ' . $carpeta, 409);
            } else {
                chgrp($carpeta, 1002);
                chown($carpeta, 1002);
                chmod($carpeta, 0777);
            }
        }
    }
}
