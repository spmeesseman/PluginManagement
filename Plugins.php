<?php

# Copyright (c) 2019 Scott Meesseman
# Licensed under GPL3 

require_once('core/plugins_api.php');


class PluginsPlugin extends MantisPlugin
{

    function register() 
    {
		$this->name = plugin_lang_get("title");
        $this->description = plugin_lang_get("description");
        $this->page = 'config';

        $this->version = "0.0.1";
        $this->requires = array(
            "MantisCore" => "2.0.0",
        );

        $this->author = "Scott Meesseman";
        $this->contact = "spmeesseman@gmail.com";
        $this->url = "https://github.com/mantisbt-plugins/Plugins";
    }
    

    function init() 
    {
        $t_inc = get_include_path();
        $t_core = config_get_global('core_path');
        $t_path = config_get_global('plugin_path'). plugin_get_current() . DIRECTORY_SEPARATOR . 'core'. DIRECTORY_SEPARATOR;
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
            'github_api_token' => ''
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
        http_csp_add('img-src', $t_protocol + 'img.shields.io/');
    }
    
    
    function plugins_menu() 
    {
        if (access_has_global_level(plugin_config_get('view_threshold_level'))) {
            return array(
                '<a href="' . plugin_page( 'plugins' ) . '">' . plugin_lang_get( 'management_title' ) . '</a>',
            );
        }

        return array();
    }

}
