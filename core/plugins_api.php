<?php
use splitbrain\PHPArchive\Tar;

function plugins_get_button_clear( $p_tab, $p_action, $p_param = '' )
{
    return '<span class="pull-right">
                <form method="post" action="' . plugin_page( 'plugins_edit' ) . '" title= "' . plugin_lang_get( 'log_clear' ) . '" class="form-inline">
                    ' . form_security_field( 'plugin_Plugins_plugins_edit' ) . '
                    <input type="hidden" name="action" value="' . $p_action . '" />
                    <input type="hidden" name="param" value="' . $p_param . '" />
                    <input type="hidden" name="tab" value="' . $p_tab . '" />
                    <input type="hidden" name="id" value="0" />
                    <input type="submit" name="submit" class="btn btn-primary btn-sm btn-white btn-round plugins-clear" value="' . plugin_lang_get( 'log_clear' ) . '" />
                </form>
            </span>';
}


function plugins_get_button_delete( $p_tab, $p_action, $p_id = 0, $p_param = '' )
{
    return '<span class="pull-right padding-right-8">
                <form method="post" action="' . plugin_page( 'plugins_edit' ) . '" title= "' . lang_get( 'delete_link' ) . '"  class="form-inline">
                    ' . form_security_field( 'plugin_Plugins_plugins_edit' ) . '
                    <input type="hidden" name="action" value="' . $p_action . '" />
                    <input type="hidden" name="param" value="' . $p_param . '" />
                    <input type="hidden" name="tab" value="' . $p_tab . '" />
                    <input type="hidden" name="id" value="' . $p_id . '" />
                    <input type="submit" name="submit" class="btn btn-primary btn-sm btn-white btn-round plugins-delete" value="' . lang_get( 'delete_link' ) . '" />
                </form>
            </span>';
}


function plugins_get_button_add_email()
{
    return '<span class="pull-right" style="padding-right:30px">
                <form method="post" action="' . plugin_page( 'plugins_edit ') . '" class="form-inline">
                    ' . form_security_field( 'plugin_Plugins_plugins_edit' ) . '
                    <input type="hidden" name="action" value="add_account_blocked_email" />
                    <input type="hidden" name="tab" value="Account Block" />
                    <input type="hidden" name="id" value="0" />
                    <input type="submit" name="submit" class="btn btn-primary btn-sm btn-white btn-round" value="' . lang_get( 'add_user_to_monitor' ) . ':" /> 
                    <input type="text" name="param" class="input-sm" style="width:250px !important" />
                </form>
            </span>';
}


function plugins_print_button_download( $p_plugin_name, $p_text, $p_download_url )
{
    echo '<form method="post" action="' . plugin_page( 'download' ) . '" title= "' . plugin_lang_get( 'update_download_tooltip' ) . '"  class="form-inline">
            ' . form_security_field( 'plugin_Plugins_download' ) . '
            <input type="hidden" name="plugin_name" value="' . $p_plugin_name . '" />
            <input type="hidden" name="download_url" value="' . $p_download_url . '" />
            <input type="submit" name="submit" class="btn btn-primary btn-xs btn-white btn-round" value="' . $p_text . '" />
        </form>';
}


function plugins_recursive_copy( $p_src, $p_dst ) 
{
	$dir = opendir( $p_src );
    @mkdir( $p_dst );
    
    while(( $file = readdir($dir)) ) 
    {
        if ( ( $file != '.' ) && ( $file != '..' ) ) 
        {
			if ( is_dir( $p_src . '/' . $file ) ) {
				$this->recursive_copy( $p_src .'/'. $file, $p_dst .'/'. $file );
			}
			else {
				copy( $p_src .'/'. $file, $p_dst .'/'. $file );
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
function plugins_update_plugin( $p_plugin_name, $p_download_url ) 
{
    $t_mantis_plugin_dir = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . DIRECTORY_SEPARATOR . 'plugins'  . DIRECTORY_SEPARATOR . $p_plugin_name . DIRECTORY_SEPARATOR;
    $t_download_dir = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR . $p_plugin_name . DIRECTORY_SEPARATOR;
    $t_backup_dir = dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . $p_plugin_name . DIRECTORY_SEPARATOR;
    $t_release_filename = basename( $p_download_url);
    $t_release_file = $t_download_dir .  $t_release_filename;

    if ( !file_exists( $t_mantis_plugin_dir ) ) {
        return false;
    }

	if ( !file_exists( $t_download_dir ) ) {
        @mkdir($t_download_dir, 0777, true);
    }

    if ( !file_exists($t_backup_dir)) {
        @mkdir($t_backup_dir, 0777, true);
    }

    #
    # Use curl to send GiHub API request
    #
    $curl = curl_init();
    curl_setopt_array( $curl, [
        CURLOPT_URL => $p_download_url,
        CURLOPT_HEADER => true, 
        CURLOPT_NOBODY => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => false,
        CURLOPT_BINARYTRANSFER => true
    ] );
    $t_response = curl_exec( $curl );
    curl_close( $curl );

    $t_file = fopen( $t_release_file, "w" ) or die( "Unable to open file for writing" );
    if (false === $t_file) {
        trigger_error( ERROR_FILE_DISALLOWED, ERROR );
    }
    $t_bytes = fwrite($t_file, $t_response);
    fclose($t_file);

    #
    # Backup
    #
    plugins_recursive_copy( $t_mantis_plugin_dir, $t_backup_dir );

    #
    # Update
    #

    #
    # Zip
    #
    if ( strstr( $t_release_file, '.zip' ) != false )
    {
        $t_zip = new ZipArchive;
        if ( $t_zip->open( $t_release_file ) === true ) 
        {
            $t_zip->extractTo( $t_download_dir );
            $t_zip->close();
        } 
        else {
            return false;
        }
    }
    #
    # Tarball
    #
    else if ( strstr($t_release_file, '.tgz' ) != false)
    {
        $t_tarball = new Tar;
        if ($t_tarball->open( $t_release_file ) === true) 
        {
            $t_tarball->extract( $t_download_dir );
            $t_tarball->close();
        } 
        else {
            return false;
        }
    }
    else {
        return false;
    }

    unlink( $t_release_file );

	return true;
}


/**
 * Determine if an installed plugin has a new version available.
 * @param MantisPlugin $p_plugin Plugin basename.
 * @return boolean True if plugin has a new version available
 */
function plugins_get_latest_release( $p_plugin_basename, $p_plugin_name ) 
{
    $t_jso = null;
    $t_release_name = null;
    $t_zipball_url = null;
    $t_tarball_url = null;
    $t_changelog = null;
    $t_version = null;

	if ( strstr($p_plugin_name, "MantisBT ") != false ) {
        return null;
    }

    log_event( LOG_PLUGIN, "PluginManagement: Check latest release for %s", $p_plugin_name );

    #
    # Use curl to send GiHub API request
    #
    $curl = curl_init();
    if ( !is_blank( plugin_config_get( 'github_api_token', '' ) ) )
    {
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.github.com/repos/mantisbt-plugins/" . $p_plugin_basename  . "/releases/latest",
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
    }
    else {
        curl_setopt_array( $curl, [
            CURLOPT_URL => "https://api.github.com/repos/mantisbt-plugins/" . $p_plugin_basename  . "/releases/latest",
            CURLOPT_HTTPHEADER => [
                "Accept: application/vnd.github.v3+json",
                "Content-Type: application/json",
                "User-Agent: mantisbt-plugins"
            ],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => false
        ] );
    }
    $t_response = curl_exec($curl);
    curl_close($curl);

    #
    # Make sure an upload_url value exists on the response object to check for success
    #
    if ( $t_response != null )
    {
        try
        {
            log_event(LOG_PLUGIN, "PluginManagement: Response 200 - %s", $t_response);
            $t_jso = json_decode($t_response, true);
            #
            # Check response
            #
            # Example rate limit exceeded:
            #
            # Response 200 - {
            #   "message":"API rate limit exceeded for 40.85.161.174.",
            #    "documentation_url":"https://developer.github.com/v3/#rate-limiting"
            # }
            #
            if ( !is_blank( $t_jso['message'] ) )
            {
                return array(
                    "success" => false,
                    "error_message" => $t_jso['message']
                );
            }
            else 
            {
                if ( !is_blank( $t_jso['name'] ) )
                {
                    $t_release_name = $t_jso['name'];
                    $t_zipball_url = $t_jso['zipball_url'];
                    $t_tarball_url = $t_jso['tarball_url'];
                    $t_changelog = $t_jso['body'];
                }
                else {
                    return array(
                        "success" => false,
                        "error_message" => plugin_lang_get( 'ghapi_missing_release_name' )
                    );
                }

                preg_match_all( "/[0-9].[0-9].[0-9]/", $t_release_name, $t_matches );
                foreach( $t_matches[0] as $t_substring ) {
                    $t_version = $t_substring;
                    break;
                }

                log_event( LOG_PLUGIN, "PluginManagement: Version: %s", $t_version );
                log_event( LOG_PLUGIN, "PluginManagement: Release name: %s", $t_release_name );
                log_event( LOG_PLUGIN, "PluginManagement: Zipball URL: %s", $t_zipball_url );
                log_event( LOG_PLUGIN, "PluginManagement: Tarball URL: %s", $t_tarball_url );
                log_event( LOG_PLUGIN, "PluginManagement: Changelog: %s", $t_changelog );
            }
        }
        catch (Exception $e) 
        {
            log_event( LOG_PLUGIN, "PluginManagement: Exception decoding response" );
        }
    }
    else {
        log_event( LOG_PLUGIN, "PluginManagement: Invalid/no response from github" );
    }
    
	return array(
        "success" => true,
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


function plugins_print_alert($p_message)
{
    echo '<div class="container-fluid">';
	echo '<div class="col-md-12 col-xs-12">';
	echo '<div class="space-0"></div>';
	echo '<div class="alert alert-warning center">';
    $t_message = 'operation_warnings';
	if( is_blank( $p_message ) ) {
		$t_message = lang_get( $t_message );
    } 
    else {
		$t_message = $p_message;
	}
	echo '<p class="bold bigger-110">' . $t_message  . '</p><br />';
	echo '</div></div></div>', PHP_EOL;
}


function plugins_print_failure_and_redirect( $p_redirect_url, $p_message = '', $p_die = true )
{
    layout_page_header( null, $p_redirect_url );
    layout_page_begin();
    html_operation_failure( $p_redirect_url, $p_message );
    layout_page_end();
    if ( $p_die ) {
        die();
    }
}


function plugins_print_success_and_redirect($p_redirect_url, $p_message = '', $p_die = false)
{
    layout_page_header( null, $p_redirect_url );
    layout_page_begin();
    html_operation_successful( $p_redirect_url, $p_message );
    layout_page_end();
    if ( $p_die ) {
        die();
    }
}
