<?php

# Copyright (c) 2019 Scott Meesseman
# Licensed under GPL3 

require_once('core/plugins_api.php');
require_once( 'classes/ZipArchiveEx.class.php' );


class PluginManagementPlugin extends MantisPlugin
{

    function register() 
    {
		$this->name = plugin_lang_get( "title" );
        $this->description = plugin_lang_get( "description" );
        $this->page = "config";

        $this->version = "1.0.0";
        $this->requires = array(
            "MantisCore" => "2.0.0",
        );

        $this->author = "Scott Meesseman, MantisBT Team";
        $this->contact = "spmeesseman@gmail.com";
        $this->url = "https://github.com/mantisbt-plugins/Plugins";
    }
    

    function init() 
    {
        $t_inc = get_include_path();
        $t_core = config_get_global( 'core_path' );
        $t_path = config_get_global( 'plugin_path' ). plugin_get_current() . DIRECTORY_SEPARATOR . 'core'. DIRECTORY_SEPARATOR;
        if (strstr($t_inc, $t_core) == false) {
            set_include_path($t_inc . PATH_SEPARATOR . $t_core . PATH_SEPARATOR . $t_path);
        }
        else {
            set_include_path($t_inc .  PATH_SEPARATOR . $t_path);
        }
    }


    function config() 
    {
		return array(
			'edit_threshold_level'	=> ADMINISTRATOR ,
            'view_threshold_level'	=> MANAGER,
            'github_api_token' => '',
            'plugin_path_backup' => 'backup'
		);
	}


    function hooks() 
    {
		return array(
            'EVENT_MENU_MANAGE' => 'plugins_menu',
            'EVENT_CORE_HEADERS' => 'csp_headers'
		);
    }


    function csp_headers() 
    {
        $t_protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0 ? 'https://' : 'http://';
        http_csp_add('img-src', $t_protocol . 'img.shields.io/');
    }
    

    function resources($event) # this doesnt get called anymore??
    {
        return '<link rel="stylesheet" type="text/css" href="'.plugin_file("plugins.css").'"/>';
    }


    function plugins_menu() 
    {
        if (access_has_global_level(plugin_config_get('view_threshold_level'))) {
            return array(
                '<a href="' . plugin_page( 'plugin_page' ) . '">' . plugin_lang_get( 'title' ) . '</a>',
            );
        }

        return array();
    }

}
