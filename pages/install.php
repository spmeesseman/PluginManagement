<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin Configuration
 *
 * @package MantisBT
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses config_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses plugin_api.php
 * @uses print_api.php
 */

/** @ignore */
define( 'PLUGINS_DISABLED', true );

require_once( 'core.php' );
require_once('plugins_api.php');
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'gpc_api.php' );
require_api( 'plugin_api.php' );
require_api( 'print_api.php' );

form_security_validate( 'manage_plugin_install' );

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );
access_ensure_global_level( plugin_config_get( 'edit_threshold_level' ) );

$f_restore = gpc_get_bool( 'restore' );
$f_basename = gpc_get_string( 'name' );
$f_version = gpc_get_string( 'version' );

$t_success = false;

if (!$f_restore) {
	$t_plugin = plugin_register( $f_basename, true );
	if( !is_null( $t_plugin ) ) {
		plugin_install( $t_plugin );
		$t_success = true;
	}
}
else {
	$t_success = plugins_restore_plugin($f_basename, $f_version);
}

form_security_purge( 'manage_plugin_install' );

$f_plugin_cache = gpc_get_string( 'pcache', '' );
$t_redirect_url = plugin_page( 'plugin_page', true ) . '&pcache=' . urlencode( $f_plugin_cache );

if (!$t_success) {
	if (!$f_restore) {
		plugins_print_failure_and_redirect($t_redirect_url, plugin_lang_get( 'install_failure' ) . ' \'' . $f_plugin_name . '\'', true);
	}
	else {
		plugins_print_failure_and_redirect($t_redirect_url, plugin_lang_get( 'restore_failure' ) . ' \'' . $f_plugin_name . '\'', true);
	}
}

if (!$f_restore) {
	plugins_print_success_and_redirect( $t_redirect_url, plugin_lang_get( 'install_success' ) );
}
else {
	plugins_print_success_and_redirect( $t_redirect_url, plugin_lang_get( 'restore_success' ) );
}
