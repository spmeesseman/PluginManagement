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
 * @uses database_api.php
 * @uses form_api.php
 * @uses gpc_api.php
 * @uses print_api.php
 */

require_once('plugins_api.php');

/** @ignore */
define('PLUGINS_DISABLED', true);

form_security_validate('plugin_Plugins_plugins_edit');
auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));

$f_action = gpc_get_string('action');

if ($f_action == 'update_priority_protected')
{
	$t_query = 'SELECT basename FROM {plugin}';
	$t_result = db_query($t_query);

	while($t_row = db_fetch_array($t_result)) {
		$t_basename = $t_row['basename'];

		$f_change = gpc_get_bool('change_'.$t_basename, 0);

		if(!$f_change) {
			continue;
		}

		$f_priority = gpc_get_int('priority_'.$t_basename, 3);
		$f_protected = gpc_get_bool('protected_'.$t_basename, 0);

		$t_query = 'UPDATE {plugin} SET priority=' . db_param() . ', protected=' . db_param() .
			' WHERE basename=' . db_param();

		db_query($t_query, array($f_priority, $f_protected, $t_basename));
	}
}

form_security_purge('plugin_Plugins_plugins_edit');

$t_redirect_url = plugin_page('plugins', true); # . '&tab=' . $f_tab;
#print_successful_redirect($t_redirect_url);
plugins_print_success_and_redirect($t_redirect_url);
