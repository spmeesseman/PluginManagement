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
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses plugin_api.php
 * @uses print_api.php
 * @uses string_api.php
 * @uses utility_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'config_api.php' );
require_api( 'form_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'plugin_api.php' );
require_api( 'print_api.php' );
require_api( 'string_api.php' );
require_api( 'utility_api.php' );

require_once( 'plugins_api.php' );

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

layout_page_header_begin(lang_get( 'manage_plugin_link' ) );
echo "\t" . '<link rel="stylesheet" type="text/css" href="'.plugin_file("plugins.css").'" />' . "\n";
echo "\t" . '<script type="text/javascript" src="'.plugin_file("plugins.js").'" />' . "\n";
layout_page_header_end();

layout_page_begin( 'manage_overview_page.php' );

print_manage_menu( 'PluginManagement/plugin_page' );

$f_check_for_updates = gpc_get_bool( 'chkupdates', false );
$g_plugins_cache = array();
$t_plugin_cache_exists = false;
$t_plugins_cache = gpc_get_string( 'pcache', '' );
if ( !is_blank( $t_plugins_cache ) ) 
{
	$f_check_for_updates = true;
	$g_plugins_cache = unserialize( $t_plugins_cache );
}

$t_plugins_ignore = array( 'Import/Export issues', 'MantisBT Core', 'MantisBT Formatting',  'Mantis Graphs', 
						   'Avatars via Gravatar', 'Source GitHub Integration', 'Source Subversion Integration',
						   'Source Subversion / WebSVN Integration', 'Source GitLab Integration' );

$t_plugins = plugin_find_all();
uasort( $t_plugins,
	function ( $p_p1, $p_p2 ) {
		return strcasecmp( $p_p1->name, $p_p2->name );
	}
);

$t_plugins_backups = plugins_find_all_backups();
log_event( LOG_PLUGIN,count($t_plugins_backups ));

uasort( $t_plugins_backups,
	function ( $p_p1, $p_p2 ) {
		return strcasecmp( $p_p1->name, $p_p2->name );
	}
);

$t_plugins_installed = array();
$t_plugins_available = array();

foreach( $t_plugins as $t_basename => $t_plugin ) {
	if( plugin_is_registered( $t_basename ) ) {
		$t_plugins_installed[$t_basename] = $t_plugin;
	} else {
		$t_plugins_available[$t_basename] = $t_plugin;
	}
}

if( 0 < count( $t_plugins_installed ) ) {
?>
<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container">

	<form action="<?php echo plugin_page( 'update' ) ?>" method="post">
		<fieldset>
		<?php echo form_security_field( 'manage_plugin_update' ) ?>

<div class="widget-box widget-color-blue2">
	<div class="widget-header widget-header-small">
		<h4 class="widget-title lighter">
			<i class="ace-icon fa fa-cubes"></i>
				<?php echo lang_get('plugins_installed') ?>
		</h4>
	</div>
<div class="widget-body">
<div class="widget-main no-padding">
	<div class="table-responsive">
		<table class="table table-striped table-bordered table-condensed table-hover">

			<colgroup>
				<col style="width:20%" />
				<col style="width:35%" />
				<col style="width:20%" />
				<col style="width:7%" />
				<col style="width:8%" />
				<col style="width:10%" />
			</colgroup>
			<thead>
				<!-- Info -->
				<tr>
					<th><?php echo lang_get( 'plugin' ) ?></th>
					<th><?php echo lang_get( 'plugin_description' ) ?></th>
					<th><?php echo lang_get( 'plugin_depends' ) ?></th>
					<th><?php echo lang_get( 'plugin_priority' ) ?></th>
					<th><?php echo lang_get( 'plugin_protected' ) ?></th>
					<th><?php echo lang_get( 'plugin_actions' ) ?></th>
				</tr>
			</thead>

			<tbody>
<?php

$t_github_api_exhausted = false;

# hack, for whatever reason the first embedded form is taking the main <form> action/url, win10 chrome
echo '<form class="form-inline"></form>';

foreach ( $t_plugins_installed as $t_basename => $t_plugin ) 
{
	$t_description = string_display_line_links( $t_plugin->description );
	$t_author = $t_plugin->author;
	$t_contact = $t_plugin->contact;
	$t_page = $t_plugin->page;
	$t_url = $t_plugin->url;
	$t_requires = $t_plugin->requires;
	$t_depends = array();
	$t_priority = plugin_priority( $t_basename );
	$t_protected = plugin_protected( $t_basename );
	
	$t_name = string_display_line( $t_plugin->name );
	if( !is_blank( $t_page ) ) {
		$t_name = '<a href="' . string_attribute( plugin_page( $t_page, false, $t_basename ) ) . '">' . $t_name . '</a>';
	}

	if( !empty( $t_author ) ) {
		if( is_array( $t_author ) ) {
			$t_author = implode( ', ', $t_author );
		}
		if( !is_blank( $t_contact ) ) {
			$t_author = '<br />' . sprintf( lang_get( 'plugin_author' ),
				'<a href="mailto:' . string_attribute( $t_contact ) . '">' . string_display_line( $t_author ) . '</a>' );
		} else {
			$t_author = '<br />' . string_display_line( sprintf( lang_get( 'plugin_author' ), $t_author ) );
		}
	}

	if( !is_blank( $t_url ) ) {
		$t_url = '<br />' . lang_get( 'plugin_url' ) . lang_get( 'word_separator' ) . '<a href="' . $t_url . '">' . $t_url . '</a>';
	}

	$t_upgrade = plugin_needs_upgrade( $t_plugin );

	$t_new_release = null;
	$t_github_release_found = false;

	$t_release_desc = '<br />' . plugin_lang_get( 'current_version' ) . ': &nbsp;' . $t_plugin->version;

	$t_ignored = preg_grep('/' . trim( $t_plugin->name ) . '[0-9A-z _-]*/i', $t_plugins_ignore);

	if ( count( $t_ignored ) == 0 ) 
	{
        if ( $f_check_for_updates ) {
			$t_release_desc .= '<br />' . plugin_lang_get( 'latest_version' ) . ': &nbsp;';
		}

		if ( $f_check_for_updates && !$t_github_api_exhausted ) {
			$t_new_release = plugins_get_latest_release( $t_basename, $t_name );
		}
		
		if ( $t_new_release != null ) {
			if ( !is_blank( $t_new_release['error_message'] ) ) {
				if ( stristr ( $t_new_release['error_message'], "limit exceeded" ) != false ) {
					$t_github_api_exhausted = true;
					$t_release_desc .= plugin_lang_get( 'api_requests_exhausted' );
				}
				else if ( stristr ( $t_new_release['error_message'], "not found" ) != false ) {
					$t_github_release_found = false;
					$t_release_desc .= plugin_lang_get( 'no_versions_found' );
				}
			}
			else if ( $t_new_release['version'] != null ) {  
				if (version_compare($t_new_release['version'], $t_plugin->version) === 1) {
					$t_release_desc = '<br /><span class="version_dated">' . plugin_lang_get( 'current_version' ) . ': &nbsp;' . $t_plugin->version . '</span>';
				}
				else {
					$t_release_desc = '<br />' . plugin_lang_get( 'current_version' ) . ': &nbsp;' . $t_plugin->version;
				}
				$t_release_desc .= '<br />' . plugin_lang_get( 'latest_version' ) . ': &nbsp;' . $t_new_release['version'];
				$t_github_release_found = true;
			}
			else {
				$t_release_desc .= plugin_lang_get( 'no_versions_found' );
			}
		}
		else {
			if ($f_check_for_updates )
			{
				if ( $t_github_api_exhausted ) {
					$t_release_desc .= plugin_lang_get( 'api_requests_exhausted' );
				}
				else {
					$t_release_desc .= plugin_lang_get( 'no_versions_found' );
				}
			}
		}
	}

	if ( is_array( $t_requires ) ) {
		foreach( $t_requires as $t_req_plugin => $t_version ) {
			$t_dependency = plugin_dependency( $t_req_plugin, $t_version );
			if( 1 == $t_dependency ) {
				if( is_blank( $t_upgrade ) ) {
					$t_depends[] = '<span class="small dependency_met">'.string_display_line( $t_plugins[$t_req_plugin]->name.' '.$t_version ).'</span>';
				} else {
					$t_depends[] = '<span class="small dependency_upgrade">'.string_display_line( $t_plugins[$t_req_plugin]->name.' '.$t_version ).'</span>';
				}
			} else if( -1 == $t_dependency ) {
				$t_depends[] = '<span class="small dependency_dated">'.string_display_line( $t_plugins[$t_req_plugin]->name.' '.$t_version ).'</span>';
			} else {
				$t_depends[] = '<span class="small dependency_unmet">'.string_display_line( $t_req_plugin.' '.$t_version ).'</span>';
			}
		}
	}

	if( 0 < count( $t_depends ) ) {
		$t_depends = implode( '<br />', $t_depends );
	} else {
		$t_depends = '<span class="small dependency_met">' . lang_get( 'plugin_no_depends' ) . '</span>';
	}

	echo '<tr>';
	echo '<td class="small">',$t_name,$t_release_desc,'<input type="hidden" name="change_',$t_basename,'" value="1"/></td>';
	echo '<td class="small">',$t_description,$t_author,$t_url,'</td>';
	echo '<td class="small center">',$t_depends,'</td>';
	if( 'MantisCore' == $t_basename ) {
		echo '<td>&#160;</td><td>&#160;</td>';
	} else {
		echo '<td class="center">',
			'<select name="priority_' . $t_basename . '"',
				' class="input-sm">',
				print_plugin_priority_list( $t_priority ),
			'</select>','</td>';
		echo '<td class="center">',
		'<label>',
			'<input type="checkbox" class="ace" name="protected_' . $t_basename . '"',
				check_checked( $t_protected ), ' />',
		'<span class="lbl"></span>',
		'</label>',
			'</select>','</td>';
	}
	echo '<td align="right" nowrap><table style="display:inline"><tr>';
	if( $t_upgrade ) {
		echo '<td><span class="pull-right padding-right-2">';
        print_link_button(
			plugin_page( 'upgrade' ) . '&name=' . $t_basename . form_security_param( 'manage_plugin_upgrade' ),
			lang_get( 'plugin_upgrade' ), 'btn-xs' );
		echo '</span></td>';
	}
	else if( $t_github_release_found ) 
	{
		echo '<td><span class="pull-right padding-right-2">';
		if ($t_new_release['version'] != null) {                                                 
			log_event( LOG_PLUGIN, "PluginManagement: Comparing version: Current '%s' New '%s'", $t_plugin->version, $t_new_release['version'] );
			if (version_compare($t_new_release['version'], $t_plugin->version) === 1) {              
				if ( $t_new_release['zipball_url'] != null ) {
					plugins_print_button_download( $t_basename, $t_plugin->version, $t_new_release['version'], plugin_lang_get( 'update_get' ) . ' v' . $t_new_release['version'], $t_new_release['zipball_url'] );
				}
				else if ( $t_new_release['tarball_url'] != null ) {
					plugins_print_button_download( $t_basename, $t_plugin->version, $t_new_release['version'], plugin_lang_get( 'update_get' ) . ' v' . $t_new_release['version'], $t_new_release['tarball_url'] );
				}
			}
		}
		echo '</span></td>';
	}
	if( !$t_protected ) {
		echo '<td><span class="pull-right padding-right-2">';
		print_link_button(
			plugin_page( 'uninstall' ) . '&name=' . $t_basename . form_security_param( 'manage_plugin_uninstall' ),
			lang_get( 'plugin_uninstall' ),  'btn-xs' );
		echo '</span></td>';
	}
	echo '</tr></table></td></tr>';
} ?>
			</tbody>
		</table>
		</div>
		<div class="widget-toolbox padding-8 clearfix">
			<input type="submit" class="btn btn-sm btn-primary btn-white btn-round" value="<?php echo plugin_lang_get('update_prio_protected') ?>"/>
			<a class="btn btn-sm btn-primary btn-white btn-round" href="<?php echo plugin_page( 'plugin_page' ) . '&chkupdates=true' ?>"><?php echo plugin_lang_get('updates_check') ?></a>;
		</div>
	</div>
</div>
</div>
</form>
</div>
<?php
}

echo '<script>pcache="' . str_replace( "\r", "", str_replace( "\n", "", str_replace( '"', '\\"', serialize( $g_plugins_cache ) ) ) ) . '";</script>';

layout_ex_section($t_plugins, $t_plugins_available);
layout_ex_section($t_plugins, $t_plugins_backups, true);

?>

<div class="center">
	<div class="space-10"></div>
	<div class="well well-sm">
		<i class="ace-icon fa fa-key"></i> &nbsp;
	<?php echo lang_get('plugin_key_label') ?> &nbsp;
	<span class='dependency_met'><?php echo lang_get( 'plugin_key_met' ) ?></span> |
	<span class='dependency_unmet'><?php echo lang_get( 'plugin_key_unmet' ) ?></span> |
	<span class='dependency_dated'><?php echo lang_get( 'plugin_key_dated' ) ?></span> |
	<span class='version_dated'><?php echo plugin_lang_get( 'version_dated' ) ?></span> |
	<span class='dependency_upgrade'><?php echo lang_get( 'plugin_key_upgrade' ) ?></span>
	</div>
</div>
<?php
echo '</div>';
layout_page_end();

function layout_ex_section($p_plugins, $p_plugins_group, $p_is_backup = false)
{
	if ( count( $p_plugins_group ) == 0 ) {
		return;
	}

	$t_title = ( !$p_is_backup ? lang_get('plugins_available') : plugin_lang_get('plugins_backed_up') );

	echo '
	<div class="space-10"></div>
	<div class="widget-box widget-color-blue2">
		<div class="widget-header widget-header-small">
			<h4 class="widget-title lighter">
				<i class="ace-icon fa fa-cube"></i>
				' . $t_title . '
			</h4>
		</div>

	<div class="widget-body">
		<div class="widget-main no-padding">
			<div class="table-responsive">
				<table class="table table-striped table-bordered table-condensed table-hover">
			<colgroup>
				<col style="width:25%" />
				<col style="width:45%" />
				<col style="width:20%" />
				<col style="width:10%" />
			</colgroup>
			<thead>
				<!-- Info -->
				<tr class="row-category">
					<td>' . lang_get( 'plugin' ) . '</td>
					<td>' . lang_get( 'plugin_description' ) . '</td>
					<td>' . lang_get( 'plugin_depends' ) . '</td>
					<td>' . lang_get( 'plugin_actions' ) . '</td>
				</tr>
			</thead>
			<tbody>';

	foreach ( $p_plugins_group as $t_basename => $t_plugin ) {
		$t_description = string_display_line_links( $t_plugin->description );
		$t_author = $t_plugin->author;
		$t_contact = $t_plugin->contact;
		$t_url = $t_plugin->url ;
		$t_requires = $t_plugin->requires;
		$t_depends = array();

		$t_name = string_display_line( $t_plugin->name.' '.$t_plugin->version );

		if( !empty( $t_author ) ) {
			if( is_array( $t_author ) ) {
				$t_author = implode( ', ', $t_author );
			}
			if( !is_blank( $t_contact ) ) {
				$t_author = '<br />' . sprintf( lang_get( 'plugin_author' ),
					'<a href="mailto:' . string_display_line( $t_contact ) . '">' . string_display_line( $t_author ) . '</a>' );
			} else {
				$t_author = '<br />' . string_display_line( sprintf( lang_get( 'plugin_author' ), $t_author ) );
			}
		}

		if( !is_blank( $t_url ) ) {
			$t_url = '<br />' . lang_get( 'plugin_url' ) . lang_get( 'word_separator' ) . '<a href="' . $t_url . '">' . $t_url . '</a>';
		}

		$t_ready = true;
		if( is_array( $t_requires ) ) {
			foreach( $t_requires as $t_plugin => $t_version ) {
				$t_dependency = plugin_dependency( $t_plugin, $t_version );
				if( 1 == $t_dependency ) {
					$t_depends[] = '<span class="small dependency_met">'.string_display_line( $p_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
				} else if( -1 == $t_dependency ) {
					$t_ready = false;
					$t_depends[] = '<span class="small dependency_dated">'.string_display_line( $p_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
				} else {
					$t_ready = false;
					$t_depends[] = '<span class="small dependency_unmet">'.string_display_line( $t_plugin.' '.$t_version ).'</span>';
				}
			}
		}

		if( 0 < count( $t_depends ) ) {
			$t_depends = implode( '<br />', $t_depends );
		} else {
			$t_depends = '<span class="small dependency_met">' . lang_get( 'plugin_no_depends' ) . '</span>';
		}

		echo '<tr>';
		echo '<td class="small center">',$t_name,'</td>';
		echo '<td class="small">',$t_description,$t_author,$t_url,'</td>';
		echo '<td class="center">',$t_depends,'</td>';
		echo '<td class="center">';
		if( $t_ready ) {
			print_small_button(
			plugin_page( 'install' ) . '&name=' . $t_basename . form_security_param( 'manage_plugin_install' ) . 
						 '&version=' . $t_plugin->version . '&restore=' . ($p_is_backup === true ? '1' : '0'), 
						 ($p_is_backup !== true ? lang_get( 'plugin_install' ) : plugin_lang_get( 'restore' ) ) );
		}
		echo '</td></tr>';
	}

	echo '
			</tbody>
		</table>
		</div>
	</div>
	</div>
	</div>';

}