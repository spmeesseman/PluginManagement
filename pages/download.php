<?php

require_once('plugins_api.php');

form_security_validate('plugin_PluginManagement_download');
auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));
access_ensure_global_level( plugin_config_get( 'edit_threshold_level' ) );

$f_plugin_name = gpc_get_string('plugin_name');
$f_plugin_current_version = gpc_get_string('plugin_current_version');
$f_plugin_latest_version = gpc_get_string('plugin_latest_version');
$f_download_url = gpc_get_string('download_url');

$t_success = plugins_update_plugin($f_plugin_name, $f_plugin_current_version, $f_plugin_latest_version, $f_download_url);

form_security_purge('plugin_PluginManagement_download');

$t_redirect_url = plugin_page('plugin_page', true);

if (!$t_success) {
	plugins_print_failure_and_redirect($t_redirect_url, plugin_lang_get( 'download_failure' ) . ' \'' . $f_plugin_name . '\'', true);
}

plugins_print_success_and_redirect( $t_redirect_url, plugin_lang_get( 'download_success' ) . ' \'' . $f_plugin_name . '\'');
