<?php

require_once('plugins_api.php');

form_security_validate( 'plugin_PluginManagement_config_edit' );
auth_reauthenticate();

access_ensure_global_level(config_get('manage_plugin_threshold'));

$f_edit_threshold_level = gpc_get_int('edit_threshold_level');
$f_view_threshold_level = gpc_get_int('view_threshold_level');
$f_github_api_token = gpc_get_string('github_api_token');

plugin_config_set('edit_threshold', $f_edit_threshold_level);
plugin_config_set('view_threshold', $f_view_threshold_level);
plugin_config_set('github_api_token', $f_github_api_token);

form_security_purge( 'plugin_PluginManagement_config_edit' );

plugins_print_success_and_redirect(plugin_page('config', TRUE));
