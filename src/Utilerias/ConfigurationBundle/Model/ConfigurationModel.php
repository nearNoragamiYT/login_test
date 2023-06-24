<?php

namespace Utilerias\ConfigurationBundle\Model;

/**
 * Description of ConfigurationModel
 *
 * @author Javier
 */
class ConfigurationModel {

    protected $App = array(
        'open' => TRUE,
        'mantenimiento' => FALSE,
        'notity_error_server' => FALSE,
        'ga' => FALSE,
        'salt' => '*;7/SjqjVjIsI*',
        'Cliente_es' => 'Expo Antad & Alimentaria',
        'Cliente_en' => 'Expo Antad & Alimentaria',
        'IdConfiguracion' => 2,
        'url_pdfs' => 'https://antad.infoexpo.com.mx/facturacion/2022/web/docs/facturas/',
        'url_xmls' => 'https://antad.infoexpo.com.mx/facturacion/2022/web/docs/comprobantes/',
        'mail_list' => array(
            'MailContacto' => 'antad@infoexpo.com.mx',
            'MailDebug' => 'mail.debug@ixpo.mx',
            'MailNotify' => 'mail.debug@ixpo.mx',
        ),
        'roles' => array(
            1 => "ROLE_ADMIN",
            2 => "ROLE_IXPO",
            3 => "ROLE_USER",
        ),
    );

    public function getApp() {
        return $this->App;
    }

    protected $parameters_gmail_notify = array(
        'mailer_host' => 'smtp.gmail.com',
        'mailer_user' => 'mail.debug@ixpo.mx',
        'mailer_password' => '754**iFx_N_268_jkl',
    );

    public function getParametersGmailNotify() {
        return $this->parameters_gmail_notify;
    }

}
