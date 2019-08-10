<?php

require_once('plugins_api.php');

form_security_validate('plugin_Plugins_download');
auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));

$f_plugin_name = gpc_get_string('plugin_name');
$f_download_url = gpc_get_string('download_url');

$t_success = plugins_update_plugin($f_plugin_name, $f_download_url);

form_security_purge('plugin_Plugins_download');

$t_redirect_url = plugin_page('plugins', true) . '&tab=' . plugin_lang_get('update_title');

if (!$t_success) {
	plugins_print_failure_and_redirect($t_redirect_url, 'Could not update the\'' . $f_plugin_name . '\' plugin', true);
}
plugins_print_success_and_redirect($t_redirect_url);
