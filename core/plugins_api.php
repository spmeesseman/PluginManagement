<?php
use splitbrain\PHPArchive\Tar;

function plugins_get_button_clear($p_tab, $p_action, $p_param = '')
{
    return '<span class="pull-right">
                <form method="post" action="' . plugin_page('plugins_edit') . '" title= "' . plugin_lang_get('management_log_clear') . '" class="form-inline">
                    ' . form_security_field('plugin_Plugins_plugins_edit') . '
                    <input type="hidden" name="action" value="' . $p_action . '" />
                    <input type="hidden" name="param" value="' . $p_param . '" />
                    <input type="hidden" name="tab" value="' . $p_tab . '" />
                    <input type="hidden" name="id" value="0" />
                    <input type="submit" name="submit" class="btn btn-primary btn-sm btn-white btn-round plugins-clear" value="' . plugin_lang_get('management_log_clear') . '" />
                </form>
            </span>';
}


function plugins_get_button_delete($p_tab, $p_action, $p_id = 0, $p_param = '')
{
    return '<span class="pull-right padding-right-8">
                <form method="post" action="' . plugin_page('plugins_edit') . '" title= "' . lang_get('delete_link') . '"  class="form-inline">
                    ' . form_security_field('plugin_Plugins_plugins_edit') . '
                    <input type="hidden" name="action" value="' . $p_action . '" />
                    <input type="hidden" name="param" value="' . $p_param . '" />
                    <input type="hidden" name="tab" value="' . $p_tab . '" />
                    <input type="hidden" name="id" value="' . $p_id . '" />
                    <input type="submit" name="submit" class="btn btn-primary btn-sm btn-white btn-round plugins-delete" value="' . lang_get('delete_link') . '" />
                </form>
            </span>';
}


function plugins_get_button_add_email()
{
    return '<span class="pull-right" style="padding-right:30px">
                <form method="post" action="' . plugin_page('plugins_edit') . '" class="form-inline">
                    ' . form_security_field('plugin_Plugins_plugins_edit') . '
                    <input type="hidden" name="action" value="add_account_blocked_email" />
                    <input type="hidden" name="tab" value="Account Block" />
                    <input type="hidden" name="id" value="0" />
                    <input type="submit" name="submit" class="btn btn-primary btn-sm btn-white btn-round" value="' . lang_get('add_user_to_monitor') . ':" /> 
                    <input type="text" name="param" class="input-sm" style="width:250px !important" />
                </form>
            </span>';
}


function plugins_print_button_download($p_plugin_name, $p_text, $p_download_url)
{
    echo '<form method="post" action="' . plugin_page('download') . '" title= "' . plugin_lang_get('management_update_download_tooltip') . '"  class="form-inline">
            ' . form_security_field('plugin_Plugins_download') . '
            <input type="hidden" name="plugin_name" value="' . $p_plugin_name . '" />
            <input type="hidden" name="download_url" value="' . $p_download_url . '" />
            <input type="submit" name="submit" class="btn btn-primary btn-xs btn-white btn-round" value="' . $p_text . '" />
        </form>';
}


function plugins_recursive_copy($p_src, $p_dst) 
{
	$dir = opendir($p_src);
    @mkdir($p_dst);
    
    while(( $file = readdir($dir)) ) 
    {
        if (( $file != '.' ) && ( $file != '..' )) 
        {
			if (is_dir($p_src . '/' . $file)) {
				$this->recursive_copy($p_src .'/'. $file, $p_dst .'/'. $file);
			}
			else {
				copy($p_src .'/'. $file, $p_dst .'/'. $file);
			}
		}
    }
    
	closedir($dir);
}


/**
 * Determine if an installed plugin has a new version available.
 * @param MantisPlugin $p_plugin Plugin basename.
 * @return boolean True if plugin has a new version available
 */
function plugins_update_plugin($p_plugin_name, $p_download_url) 
{
    $t_mantis_plugin_dir = dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'plugins'  . DIRECTORY_SEPARATOR . $p_plugin_name . DIRECTORY_SEPARATOR;
    $t_download_dir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR . $p_plugin_name . DIRECTORY_SEPARATOR;
    $t_backup_dir = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . $p_plugin_name . DIRECTORY_SEPARATOR;
    $t_release_filename = basename($p_download_url);
    $t_release_file = $t_download_dir .  $t_release_filename;

    if (!file_exists($t_mantis_plugin_dir)) {
        return false;
    }

	if (!file_exists($t_download_dir)) {
        @mkdir($t_download_dir, 0777, true);
    }

    if (!file_exists($t_backup_dir)) {
        @mkdir($t_backup_dir, 0777, true);
    }

    #
    # Use curl to send GiHub API request
    #
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $p_download_url,
        CURLOPT_HEADER => true, 
        CURLOPT_NOBODY => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => false,
        CURLOPT_BINARYTRANSFER => true
    ]);
    $t_response = curl_exec($curl);
    curl_close($curl);

    $t_file = fopen($t_release_file, "w") or die("Unable to open file for writing");
    if (false === $t_file) {
        trigger_error( ERROR_FILE_DISALLOWED, ERROR );
    }
    $t_bytes = fwrite($t_file, $t_response);
    fclose($t_file);

    #
    # Backup
    #
    plugins_recursive_copy($t_mantis_plugin_dir, $t_backup_dir);

    #
    # Update
    #

    #
    # Zip
    #
    if (strstr($t_release_file, '.zip') != false)
    {
        $t_zip = new ZipArchive;
        if ($t_zip->open($t_release_file) === true) 
        {
            $t_zip->extractTo($t_download_dir);
            $t_zip->close();
        } 
        else {
            return false;
        }
    }
    #
    # Tarball
    #
    else if (strstr($t_release_file, '.tgz') != false)
    {
        $t_tarball = new Tar;
        if ($t_tarball->open($t_release_file) === true) 
        {
            $t_tarball->extract($t_download_dir);
            $t_tarball->close();
        } 
        else {
            return false;
        }
    }
    else {
        return false;
    }

    unlink($t_release_file);

	return true;
}


/**
 * Determine if an installed plugin has a new version available.
 * @param MantisPlugin $p_plugin Plugin basename.
 * @return boolean True if plugin has a new version available
 */
function plugins_get_latest_release( MantisPlugin $p_plugin ) 
{
    $t_jso = null;
    $t_release_name = null;
    $t_zipball_url = null;
    $t_tarball_url = null;
    $t_changelog = null;

	if ($p_plugin->name == "MantisBT Core") {
        return $t_release_name;
    }

    #
    # Use curl to send GiHub API request
    #
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.github.com/repos/mantisbt-plugins/" . $p_plugin->name  . "/releases/latest",
        CURLOPT_HTTPHEADER => [
            "Accept: application/vnd.github.v3+json",
            "Content-Type: application/json",
            "User-Agent: mantisbt-plugins",
            "Authorization: token " . plugin_config_get('github_api_token')
        ],
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => false
    ]);
    $t_response = curl_exec($curl);
    curl_close($curl);

    #
    # Make sure an upload_url value exists on the response object to check for success
    #
    if ($t_response != null)
    {
        try
        {
            $t_jso = json_decode($t_response, true);
            if (!is_blank($t_jso['name']))
            {
                $t_release_name = $t_jso['name'];
                $t_zipball_url = $t_jso['zipball_url'];
                $t_tarball_url = $t_jso['tarball_url'];
                $t_changelog = $t_jso['body'];
            }
            
        }
        catch (Exception $e) {}
    }

    $t_version = null;
    preg_match_all("/[0-9].[0-9].[0-9]/", $t_release_name, $t_matches );
    foreach( $t_matches[0] as $t_substring ) {
        $t_version = $t_substring;
        break;
    }
    
	return array(
        "name" => $t_release_name,
        "version" => $t_version,
        "zipball_url" => $t_zipball_url,
        "tarball_url" => $t_tarball_url,
        "changelog" => $t_changelog
    );
}


function plugins_get_mantis_base_url()
{
    return sprintf(
      "%s://%s/",
      isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
      $_SERVER['SERVER_NAME']
    );
}


function plugins_log_event($p_user, $p_email, $p_action, $p_xdata1 = '', $p_xdata2 = '', $p_xdata3 = '')
{
    $t_db_table = plugin_table('log');
    $t_query = "INSERT INTO $t_db_table (user, email, date, action, xdata1, xdata2, xdata3) VALUES (?, ?, NOW(), ?, ?, ?, ?)";
    db_query($t_query, array($p_user, $p_email, $p_action, $p_xdata1, $p_xdata2, $p_xdata3));
}


function plugins_print_failure_and_redirect($p_redirect_url, $p_message = '', $p_die = true)
{
    layout_page_header(null, $p_redirect_url);
    layout_page_begin();
    html_operation_failure($p_redirect_url, $p_message);
    layout_page_end();
    if ($p_die) {
        die();
    }
}


function plugins_print_section($p_section_name, $p_content, $p_fa_icon = 'fa-bug')
{
    $t_block_id = 'plugin_Plugins_'.$p_section_name;
    $t_collapse_block = is_collapsed($t_block_id);
    $t_block_css = $t_collapse_block ? 'collapsed' : '';
    $t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';

    echo '
    <div id="' . $t_block_id . '" class="widget-box widget-color-blue2  no-border ' . $t_block_css . '">

        <div class="widget-header widget-header-small">
            <h4 class="widget-title lighter">
                <i class="ace-icon fa ' . $p_fa_icon . '"></i>
                ' . plugin_lang_get('management_'.$p_section_name.'_label') . '
            </h4>
            <div class="widget-toolbar">
                <a data-action="collapse" href="#">
                    <i class="ace-icon fa ' . $t_block_icon . ' bigger-125"></i>
                </a>
            </div>
        </div>

        <div class="widget-toolbox padding-8 clearfix">
            ' . plugin_lang_get('management_'.$p_section_name.'_description') . '
        </div>

        <div class="widget-body">
            <div class="widget-main no-padding">
                <div class="form-container">
                    <div class="table-responsive">
                        <table class="table table-bordered table-condensed">
                            <fieldset>
                                <tr>
                                    <td>
                                        ' . $p_content . '
                                    </td>
                                </tr>
                            </fieldset>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
    </div>';
}


function plugins_print_success_and_redirect($p_redirect_url, $p_message = '', $p_die = false)
{
    layout_page_header(null, $p_redirect_url);
    layout_page_begin();
    html_operation_successful($p_redirect_url, $p_message);
    layout_page_end();
    if ($p_die) {
        die();
    }
}


function plugins_print_tab($p_tab_title, $p_current_tab_title)
{
    $t_tab_title = '';
    if ($p_tab_title == 'Info') {
        $t_tab_title = '<i class="blue ace-icon fa fa-info-circle"></i>';
    }
    else {
        $t_tab_title = $p_tab_title;
    }
    $menu_item = '<a href="' . plugin_page('plugins') . '&tab=' . urlencode($p_tab_title) . '">' . $t_tab_title . '</a>';
    $active = $p_current_tab_title === $p_tab_title ? ' class="active"' : '';
    echo "<li{$active}>" . $menu_item . '</li>';
}


function plugins_print_tab_bar()
{
    $t_first_tab_title = plugin_lang_get('management_info_title');
    $t_current_tab = gpc_get_string('tab', null);
    $t_is_first_page = ($t_current_tab === null);
    if ($t_is_first_page) {
        $t_current_tab = $t_first_tab_title; 
    }

    echo '<ul class="nav nav-tabs padding-18" style="margin-top:5px;margin-left:5px;">' . "\n";
    
    plugins_print_tab($t_first_tab_title, $t_current_tab);
    plugins_print_tab(plugin_lang_get('management_update_title'), $t_current_tab);

    echo '</ul>' . "\n<br />";

    return $t_current_tab;
}


function plugins_print_update_section()
{
    auth_reauthenticate();
    access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

    $t_plugins = plugin_find_all();
    uasort( $t_plugins,
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

    if( 0 < count( $t_plugins_installed ) ) 
    {
        echo '
        <div class="col-md-12 col-xs-12">
        <div class="form-container">

            <form action="manage_plugin_update.php" method="post">
                <fieldset>
                ' . form_security_field( 'plugin_Plugins_plugins_edit' ) . '
                <input type="hidden" name="action" value="update_priority_protected" />

        <div class="widget-box widget-color-blue2">
            <div class="widget-header widget-header-small">
                <h4 class="widget-title lighter">
                    <i class="ace-icon fa fa-cubes"></i>
                        ' . lang_get('plugins_installed') . '
                </h4>
            </div>
        <div class="widget-body">
        <div class="widget-main no-padding">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-condensed table-hover">

                    <colgroup>
                        <col style="width:22%" />
                        <col style="width:38%" />
                        <col style="width:25%" />
                        <col style="width:15%" />
                    </colgroup>
                    <thead>
                        <!-- Info -->
                        <tr>
                            <th>' . lang_get( 'plugin' ) . '</th>
                            <th>' . lang_get( 'plugin_description' ) . '</th>
                            <th>' . lang_get( 'plugin_depends' ) . '</th>
                            <th>' . lang_get( 'plugin_priority' ) . '</th>
                            <th>' . lang_get( 'plugin_protected' ) . '</th>
                            <th nowrap>' . lang_get( 'plugin_actions' ) . '</th>
                        </tr>
                    </thead>

                    <tbody>';

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

            $t_name = string_display_line( $t_plugin->name.' '.$t_plugin->version );
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

            $t_new_release = plugins_get_latest_release( $t_plugin );
            
            if( is_array( $t_requires ) ) {
                foreach( $t_requires as $t_plugin => $t_version ) {
                    $t_dependency = plugin_dependency( $t_plugin, $t_version );
                    if( 1 == $t_dependency ) {
                        if( is_blank( $t_upgrade ) ) {
                            $t_depends[] = '<span class="small dependency_met">'.string_display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
                        } else {
                            $t_depends[] = '<span class="small dependency_upgrade">'.string_display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
                        }
                    } else if( -1 == $t_dependency ) {
                        $t_depends[] = '<span class="small dependency_dated">'.string_display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
                    } else {
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
            echo '<td class="small center">',$t_name,'<input type="hidden" name="change_',$t_basename,'" value="1"/></td>';
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
            //echo '<span class="pull-right padding-right-2">';
            if( $t_upgrade ) {
                echo '<td><span class="pull-right padding-right-2">';
                print_link_button(
                    'manage_plugin_upgrade.php?name=' . $t_basename . form_security_param( 'manage_plugin_upgrade' ),
                    lang_get( 'plugin_upgrade' ), 'btn-xs' );
                echo '</span></td>';
            }
            else if( $t_new_release != null && $t_new_release['name'] != null ) 
            {
                echo '<td><span class="pull-right padding-right-2">';
                if ($t_new_release['version'] != null) {
                    if (version_compare($t_new_release['version'], $t_plugin->version) > 0) {              
                        if ( $t_new_release['zipball_url'] != null ) {
                            plugins_print_button_download( $t_plugin->name, plugin_lang_get( 'management_update_get' ) . ' v' . $t_new_release['version'], $t_new_release['zipball_url'] );
                        }
                        else if ( $t_new_release['tarball_url'] != null ) {
                            plugins_print_button_download( $t_plugin->name, plugin_lang_get( 'management_update_get' ) . ' v' . $t_new_release['version'], $t_new_release['tarball_url'] );
                        }
                    }
                }
                echo '</span></td>';
            }
            if( !$t_protected ) 
            {
                echo '<td><span class="pull-right padding-right-2">';
                print_link_button(
                    'manage_plugin_uninstall.php?name=' . $t_basename . form_security_param( 'manage_plugin_uninstall' ),
                    lang_get( 'plugin_uninstall' ),  'btn-xs' );
                echo '</span></td>';
            }
            echo '</tr></table></td></tr>';
        }

        echo '      </tbody>
                </table>
            </div>
            <div class="widget-toolbox padding-8 clearfix">
                <input type="submit" class="btn btn-sm btn-primary btn-white btn-round" value="' . lang_get( 'plugin_update' ) . '"/>
            </div>
        </div>
        </div>
        </div>';
    }

    if( 0 < count( $t_plugins_available ) ) 
    {
        echo '
        <div class="space-10"></div>
        <div class="widget-box widget-color-blue2">
            <div class="widget-header widget-header-small">
                <h4 class="widget-title lighter">
                    <i class="ace-icon fa fa-cube"></i>
                    ' . lang_get('plugins_available'). '
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
                        <td>' . lang_get( 'plugin' ). '</td>
                        <td>' . lang_get( 'plugin_description' ). '</td>
                        <td>' . lang_get( 'plugin_depends' ). '</td>
                        <td>' . lang_get( 'plugin_actions' ). '</td>
                    </tr>
                </thead>
        
                <tbody>';

            foreach ( $t_plugins_available as $t_basename => $t_plugin ) 
            {
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
                            $t_depends[] = '<span class="small dependency_met">'.string_display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
                        } else if( -1 == $t_dependency ) {
                            $t_ready = false;
                            $t_depends[] = '<span class="small dependency_dated">'.string_display_line( $t_plugins[$t_plugin]->name.' '.$t_version ).'</span>';
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
                        'manage_plugin_install.php?name=' . $t_basename . form_security_param( 'manage_plugin_install' ),
                        lang_get( 'plugin_install' ) );
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
        

    } # available plugins

    echo '<div class="center">
            <div class="space-10"></div>
            <div class="well well-sm">
                <i class="ace-icon fa fa-key"></i>
            ' . lang_get('plugin_key_label'). '
            <span class="dependency_met">' . lang_get( 'plugin_key_met' ) . '</span>,
            <span class="dependency_unmet">' . lang_get( 'plugin_key_unmet' ) . '</span>,
            <span class="dependency_dated">' . lang_get( 'plugin_key_dated' ) . '</span>,
            <span class="dependency_upgrade">' . lang_get( 'plugin_key_upgrade' ) . '</span>.
            </div>
        </div>
        </div></div>';
}
