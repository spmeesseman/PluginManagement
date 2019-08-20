<?php

use splitbrain\PHPArchive\Tar;



function plugins_archive_plugin( $p_plugin_name, $p_plugin_version )
{
    $t_zip = new ZipArchiveEx();
    $t_path = config_get_global( 'plugin_path' ) . DIRECTORY_SEPARATOR . $p_plugin_name;
    log_event( LOG_PLUGIN, "PluginManagement: Archive plugin %s v%s path = %s", $p_plugin_name, $p_plugin_version, $t_path );
    $t_filename = plugins_get_backup_dir() . $p_plugin_name . '--' . $p_plugin_version . '.zip';
    if ( $t_zip->open( $t_filename, ZipArchive::CREATE | ZipArchive::OVERWRITE ) !== true ) {
        trigger_error( plugin_lang_get( 'backup failed' ) );
    }
    $t_zip->addDir($t_path, $p_plugin_name);
    $t_zip->close();
    return $t_filename;
}


function plugins_get_button_clear( $p_tab, $p_action, $p_param = '' )
{
    return '<span class="pull-right">
                <form method="post" action="' . plugin_page( 'plugins_edit' ) . '" title= "' . plugin_lang_get( 'log_clear' ) . '" class="form-inline">
                    ' . form_security_field( 'plugin_PluginManagement_plugins_edit' ) . '
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
                    ' . form_security_field( 'plugin_PluginManagement_plugins_edit' ) . '
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
                    ' . form_security_field( 'plugin_PluginManagement_plugins_edit' ) . '
                    <input type="hidden" name="action" value="add_account_blocked_email" />
                    <input type="hidden" name="tab" value="Account Block" />
                    <input type="hidden" name="id" value="0" />
                    <input type="submit" name="submit" class="btn btn-primary btn-sm btn-white btn-round" value="' . lang_get( 'add_user_to_monitor' ) . ':" /> 
                    <input type="text" name="param" class="input-sm" style="width:250px !important" />
                </form>
            </span>';
}


function plugins_print_button_download( $p_plugin_name, $p_plugin_current_version, $p_plugin_lastest_version, $p_text, $p_download_url )
{
    echo '<form method="post" action="' . plugin_page( 'download' ) . '" title= "' . plugin_lang_get( 'update_download_tooltip' ) . '"  class="form-inline">
            ' . form_security_field( 'plugin_PluginManagement_download' ) . '
            <input type="hidden" name="plugin_name" value="' . $p_plugin_name . '" />
            <input type="hidden" name="plugin_current_version" value="' . $p_plugin_current_version . '" />
            <input type="hidden" name="plugin_latest_version" value="' . $p_plugin_lastest_version . '" />
            <input type="hidden" name="download_url" value="' . $p_download_url . '" />
            <input type="submit" name="submit" class="btn btn-primary btn-xs btn-white btn-round" value="' . $p_text . '" />
        </form>';
}


function plugins_copy_recursive( $p_src, $p_dst ) 
{
    if (! is_dir($p_src)) {
        trigger_error( "$p_src must be a directory" );
    }

    if ( substr($p_src, strlen($p_src) - 1, 1) != DIRECTORY_SEPARATOR ) {
        $p_src .= DIRECTORY_SEPARATOR;
    }

    if ( substr($p_dst, strlen($p_dst) - 1, 1) != DIRECTORY_SEPARATOR ) {
        $p_dst .= DIRECTORY_SEPARATOR;
    }

    $t_dir = opendir( $p_src );
    while ( ( $t_file = readdir($t_dir)) ) {
        if ( ( $t_file != '.' ) && ( $t_file != '..' ) ) {
			if ( is_dir( $p_src . $t_file ) ) {
				plugins_copy_recursive( $p_src . $t_file . DIRECTORY_SEPARATOR, $p_dst . $t_file . DIRECTORY_SEPARATOR );
			}
			else {
                copy( $p_src . $t_file, $p_dst . $t_file );
			}
		}
    }
	closedir($t_dir);
}


function plugins_delete_dir($p_path) 
{
    if (! is_dir($p_path)) {
        trigger_error( "$p_path must be a directory" );
    }
    log_event( LOG_PLUGIN, "PluginManagement: delete dir = %s", $p_path  );

    if ( substr($p_path, strlen($p_path) - 1, 1) != DIRECTORY_SEPARATOR ) {
        $p_path .= DIRECTORY_SEPARATOR;
    }

    $t_files = array_diff(scandir($p_path), array('.', '..')); 
    foreach ($t_files as $t_file) { 
        $t_file_path = $p_path . $t_file;
        (is_dir( $t_file_path ) ) ? plugins_delete_dir( $t_file_path ) : unlink( $t_file_path ); 
    }

    rmdir($p_path); 
}


function plugins_find_all_backups() 
{
	$t_plugin_path = plugins_get_backup_dir();
	$t_plugins = array();
    log_event( LOG_PLUGIN, "find_all" );
	if( $t_dir = opendir( $t_plugin_path ) ) {
		while( ( $t_file = readdir( $t_dir ) ) !== false ) {
            log_event( LOG_PLUGIN, "find_all: file %s", $t_file  );
			if ( '.' == $t_file || '..' == $t_file ) {
				continue;
			}
			if ( is_file( $t_plugin_path . $t_file ) ) {
				$t_plugin_basename = substr( $t_file, 0, strpos( $t_file, '--') );
                $t_plugin_version = str_replace( '.zip', '', substr( $t_file, strpos( $t_file, '--' ) + 2 ));
                $t_plugin = plugin_register( $t_plugin_basename, true );

                $t_classname = $t_plugin_basename . 'Plugin';
                $t_child = null;

                # Include the plugin script if the class is not already declared.
                if( !class_exists( $t_classname ) ) {
                    if( !plugin_include( $t_plugin_basename, $t_child ) ) {
                        return null;
                    }
                }

                # Make sure the class exists and that it's of the right type.
                if( class_exists( $t_classname ) && is_subclass_of( $t_classname, 'MantisPlugin' ) ) {
                    plugin_push_current( is_null( $t_child ) ? $t_plugin_basename : $t_child );

                    $t_plugin = new $t_classname( is_null( $t_child ) ? $t_plugin_basename : $t_child );

                    plugin_pop_current();

                    # Final check on the class
                    if( is_null( $t_plugin->name ) || is_null( $t_plugin->version ) ) {
                        return null;
                    }

                    if ( $t_plugin ) {
                        $t_plugin->version = $t_plugin_version;
                        $t_plugins[$t_plugin_basename] = $t_plugin;
                    }
                } else {
                    error_parameters( $t_plugin_basename, $t_classname );
                    trigger_error( ERROR_PLUGIN_CLASS_NOT_FOUND, ERROR );
                } 
			}
		}
        closedir( $t_dir );
	}
	return $t_plugins;
}


function plugins_get_download_dir()
{
    $t_mantis_plugin_dir = config_get_global( 'plugin_path' )  . DIRECTORY_SEPARATOR ;
    $t_download_dir = $t_mantis_plugin_dir . 'PluginManagement' . DIRECTORY_SEPARATOR . 'download' . DIRECTORY_SEPARATOR;
    if ( !file_exists( $t_download_dir ) ) {
        @mkdir($t_download_dir, 0755, true);
    }
    return $t_download_dir;
}


function plugins_get_backup_dir()
{
    $t_mantis_plugin_dir = config_get_global( 'plugin_path' )  . DIRECTORY_SEPARATOR ;
    $t_backup_dir = $t_mantis_plugin_dir . 'PluginManagement' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR;
    if ( !file_exists( $t_backup_dir ) ) {
        @mkdir($t_backup_dir, 0755, true);
    }
    return $t_backup_dir;
}


/**
 * Unzip the source_file in the destination dir
 *
 * @param   string      The path to the ZIP-file.
 * @param   string      The path where the zipfile should be unpacked, if false the directory of the zip-file is used
 * @param   boolean     Indicates if the files will be unpacked in a directory with the name of the zip-file (true) or not (false) (only if the destination directory is set to false!)
 * @param   boolean     Overwrite existing files (true) or not (false)
 *  
 * @return  boolean     Succesful or not
 */
function plugins_unzip( $p_src_file, $p_dest_dir = false, $p_create_zip_name_dir = true, $p_overwrite = true )
{
    log_event( LOG_PLUGIN, "PluginManagement: Unzip %s", $p_src_file );

    if ( $t_zip = zip_open( $p_src_file ) ) 
    {
        if ( $t_zip ) 
        {
            $splitter = ( $p_create_zip_name_dir === true ) ? "." : "/";
            if ( $p_dest_dir === false ) {
                $p_dest_dir = substr( $p_src_file, 0, strrpos( $p_src_file, $splitter ) ) . "/";
            }

            # Create the directories to the destination dir if they don't already exist
            plugins_create_dirs($p_dest_dir);

            # For every file in the zip-packet
            while ( $t_zip_entry = zip_read( $t_zip ) ) 
            {
                # Now we're going to create the directories in the destination directories
                # If the file is not in the root dir
                $pos_last_slash = strrpos( zip_entry_name( $t_zip_entry ), "/" );
                if  ( $pos_last_slash !== false ) {
                    # Create the directory where the zip-entry should be saved (with a "/" at the end)
                    plugins_create_dirs( $p_dest_dir . substr( zip_entry_name( $t_zip_entry ), 0, $pos_last_slash + 1 ) );
                }
                
                # Open the entry
                if ( zip_entry_open( $t_zip, $t_zip_entry, "r" ) ) {

                    # The name of the file to save on the disk
                    $t_file_name = $p_dest_dir . zip_entry_name($t_zip_entry);

                    # Check if the files should be overwritten or not
                    if ( $p_overwrite === true || $p_overwrite === false && !is_file( $t_file_name ) ) {
                        # Get the content of the zip entry
                        $t_fstream = zip_entry_read( $t_zip_entry, zip_entry_filesize( $t_zip_entry ) );
                        file_put_contents( $t_file_name, $t_fstream );
                        # Set the rights
                        if (is_dir($t_file_name)) {
                            chmod( $t_file_name, 0750 );
                        }
                        else {
                            chmod( $t_file_name, 0644 );
                        }
                    }
                    # Close the entry
                    zip_entry_close( $t_zip_entry );
                }
            }
            # Close the zip-file
            zip_close( $t_zip );
        }
    } else {
        return false;
    }

    return true;
}


/**
 * This function creates recursive directories if it doesn't already exist
 *
 * @param String  The path that should be created
 *  
 * @return  void
 */
function plugins_create_dirs( $p_path )
{
    if ( !is_dir( $p_path ) ) {
        $t_directory_path = "";
        $t_directories = explode( "/", $p_path );
        array_pop( $t_directories );
        foreach ( $t_directories as $t_directory ) {
            $t_directory_path .= $t_directory . "/";
            if ( !is_dir( $t_directory_path ) ) {
                mkdir( $t_directory_path );
                chmod( $t_directory_path, 0750 );
            }
        }
    }
}


function plugins_find_file( $p_folder, $p_pattern ) 
{
    $t_iti = new RecursiveDirectoryIterator($p_folder);
    foreach(new RecursiveIteratorIterator($t_iti) as $t_file){
         if(strpos($t_file , $p_pattern) !== false){
            return $t_file;
         }
    }
    return false;
}


function plugins_restore_plugin($p_plugin_name, $p_version_to_restore)
{
    $t_success = true;
    $t_plugin_dir = config_get_global( 'plugin_path' )  . DIRECTORY_SEPARATOR . $p_plugin_name . DIRECTORY_SEPARATOR;
    $t_restore_from_zip = plugins_get_backup_dir() . $p_plugin_name . '--' . $p_version_to_restore . 'zip';
    # Backup current
    plugins_archive_plugin( $p_plugin_name, plugin_get_version($p_plugin_name));
    # Restore requested version
    $t_success = plugins_unzip(  $t_restore_from_zip, $t_plugin_dir );
    return $t_success;
}


/**
 * Determine if an installed plugin has a new version available.
 * @param MantisPlugin $p_plugin Plugin basename.
 * @return boolean True if plugin has a new version available
 */
function plugins_update_plugin( $p_plugin_name, $p_plugin_current_version, $p_plugin_lastest_version, $p_download_url ) 
{
    $t_success = true;
    $t_mantis_plugin_dir = config_get_global( 'plugin_path' )  . DIRECTORY_SEPARATOR ;
    $t_plugin_dir = $t_mantis_plugin_dir . $p_plugin_name . DIRECTORY_SEPARATOR;
    $t_release_filename = basename( $p_download_url);
    $t_download_dir = plugins_get_download_dir();
    $t_release_file = $t_download_dir .  $t_release_filename;

    log_event( LOG_PLUGIN, "PluginManagement: Update release for %s from v%s to v%s", $p_plugin_name, $p_plugin_current_version, $p_plugin_lastest_version );

    if ( !file_exists( $t_plugin_dir ) ) {
        return false;
    }

    #
    # Backup
    #
    $t_archive_path = plugins_archive_plugin( $p_plugin_name, $p_plugin_current_version );
    #$t_archive_path =  plugins_get_backup_dir() . $p_plugin_name . '--v' . $p_plugin_current_version;
    #plugins_copy_recursive($t_plugin_dir, $t_archive_path);

    log_event( LOG_PLUGIN, "PluginManagement: Download %s", $p_download_url );

    #
    # Use curl to send GiHub API request
    #
    $curl = curl_init();
    curl_setopt_array( $curl, [
        CURLOPT_URL => $p_download_url,
        CURLOPT_HTTPHEADER => [
            "User-Agent: mantisbt-plugins"
        ],
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
    # Update / overwrite existing plugin
    #

    $t_dest_dir = $t_download_dir . $p_plugin_name . '-' . $p_plugin_lastest_version . DIRECTORY_SEPARATOR;

    #
    # Zip
    #  
    if ( strstr( $p_download_url, 'zipball' ) != false || strstr( $p_download_url, '.zip' ) != false)
    {
        #if ( !plugins_unzip( $t_release_file, $t_plugin_dir, false ) ) {
        if ( plugins_unzip( $t_release_file, $t_dest_dir ) ) {
            # Examine the folder, find where the base plugin file is
            $t_main_file = plugins_find_file( $t_dest_dir,  $p_plugin_name . ".php" );
            $t_main_dir = dirname( $t_main_file ) . DIRECTORY_SEPARATOR;
            if ( !is_blank($t_main_dir) ) {
                log_event( LOG_PLUGIN, "PluginManagement: Found main plugin file dir = %s", $t_main_dir  );
                # update/overwrite the plugin files
                plugins_copy_recursive( $t_main_dir , $t_plugin_dir  );
            }
            else {
                log_event( LOG_PLUGIN, "PluginManagement: Could not find main plugin file %s", $t_main_file );
                $t_success = false;
            }
        }
        else {
            $t_success = false;
        }
    }
    #
    # TODO - Tarball - not sure will even need this
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
            $t_success = false;
        }
    }
    else {
        $t_success = false;
    }

    #
    # Cleanup
    #
    if ( is_file( $t_release_file ) ) {
        unlink( $t_release_file );
    }
    if ( is_dir( $t_dest_dir ) ) {
        plugins_delete_dir( $t_dest_dir );
    }

	return $t_success;
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

	if ( strstr($p_plugin_basename, "MantisBT ") != false ) {
        return null;
    }

    $t_plugin_basename = $p_plugin_basename;

    #
    # Special handling for source-integration
    #
    if ( $p_plugin_basename == "Source" ) {
        $t_plugin_basename = "source-integration";
    }
    else if ( strpos( $p_plugin_basename, "Source" ) === 0) {
        return null;
    }
    
    $t_release_url = "https://api.github.com/repos/mantisbt-plugins/" . $t_plugin_basename  . "/releases/latest";

    log_event( LOG_PLUGIN, "PluginManagement: Check latest release for %s", $t_plugin_basename );
    log_event( LOG_PLUGIN, "PluginManagement: URL", $t_release_url );

    #
    # Use curl to send GiHub API request
    #
    $curl = curl_init();
    if ( !is_blank( plugin_config_get( 'github_api_token', '' ) ) )
    {
        curl_setopt_array($curl, [
            CURLOPT_URL => $t_release_url,
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
            CURLOPT_URL => $t_release_url,
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
            log_event( LOG_PLUGIN, "PluginManagement: Exception decoding response %s", $e->getMessage() );
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
      "%s:#%s/",
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
