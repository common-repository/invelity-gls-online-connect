<?php
/*
Plugin Name: Invelity GLS online connect
Plugin URI: https://www.invelity.com/sk/sluzby
Description:Plugin Invelity GLS online connect je vytvorený pre obchodníkov na platforme Woocommerce ktorý potrebuju automaticky exportovat údaje o objednávkach do systému GLS online za účelom vytlačenia doručovacích lístkov.
Author: Invelity
Author URI: https://www.invelity.com
Version: 1.2.4
*/
defined('ABSPATH') or die('No script kiddies please!');

require_once('classes/class.invelityGlsOnlineConnectAdmin.php');
require_once('classes/class.invelityGlsOnlineConnectProcess.php');
if (!class_exists('InvelityPluginsAdmin')) {
    require_once('classes/class.invelityPluginsAdmin.php');
}

class InvelityGlsOnlineConnect
{
    public $settings = [];

    public function __construct()
    {
        $this->settings['plugin-slug'] = 'invelity-gls-online-connect';
        $this->settings['old-plugin-slug'] = 'finest-online-connect-export';
        $this->settings['plugin-path'] = plugin_dir_path(__FILE__);
        $this->settings['plugin-url'] = plugin_dir_url(__FILE__);
        $this->settings['plugin-name'] = 'Invelity Gls online connect';
        $this->settings['plugin-license-version'] = '1.x.x';
        $this->initialize();
    }

    private function initialize()
    {
        $this->updateOldVersionData();
        new InvelityPluginsAdmin($this);
        new InvelityGlsOnlineConnectAdmin($this);
        new InvelityGlsOnlineConnectProcess($this);
    }

    public function updateOldVersionData(){
        $options = get_option('invelity_gls_export_options');
        if(!isset($options['country_version']) || !$options['country_version']){
            $options['country_version'] = 'sk';
            update_option('invelity_gls_export_options', $options);
        }
    }

    public function getPluginSlug()
    {
        return $this->settings['plugin-slug'];
    }

    public function getPluginPath()
    {
        return $this->settings['plugin-path'];
    }

    public function getPluginUrl()
    {
        return $this->settings['plugin-url'];
    }

    public function getPluginName()
    {
        return $this->settings['plugin-name'];
    }

    public function getOldPluginSlug()
    {
        return $this->settings['old-plugin-slug'];
    }

}

new InvelityGlsOnlineConnect();




