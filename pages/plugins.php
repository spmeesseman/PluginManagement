<?php

require_once('core.php');
require_once('plugins_api.php');
require_once('parsedown.php');
require_once('parsedown-toc.php');

auth_reauthenticate();
access_ensure_project_level(plugin_config_get('view_threshold_level'));

layout_page_header_begin(plugin_lang_get('management_title'));
echo "\t" . '<script type="text/javascript" src="' . plugin_file('plugins.js') . '"></script>' . "\n";
layout_page_header_end( $p_page_id );

layout_page_begin(__FILE__);
print_manage_menu('Plugins/plugins');

$keywords_block_bug = '';
$keywords_block_bugnote = '';

$t_current_tab = plugins_print_tab_bar();

echo '<div hidden title="' . plugin_lang_get('management_confirm_clear') . '" id="plugins_confirm_clear"></div>';

?>

<div class="col-xs-12">
    <div id="config-div" class="form-container">

<?php
        #
        # 'Info' tab
        #
        if ($t_current_tab === plugin_lang_get('management_info_title'))
        {
            $t_file_path = config_get_global( 'plugin_path' ) . 'Plugins' . DIRECTORY_SEPARATOR . 'README.md';
            $t_file = fopen($t_file_path , "r") or die("<br><br> &nbsp;&nbsp&nbsp;&nbsp;&nbsp&nbsp;<b>" . plugin_lang_get('cannot_open') . "</b>");
            $t_content = fread($t_file, filesize($t_file_path));
            fclose($t_file);

            $ParsedownToc = new ParsedownToc();
            $ParsedownToc->setSafeMode(false);

			$t_html = $ParsedownToc->text($t_content);
			// Bug in parsedown example '>' gets converted to &amp;gt; when it should be just &gt;
			$t_html = str_replace("&amp;gt;", "&gt;", $t_html);
			$t_html = str_replace("&amp;lt;", "&lt;", $t_html);
			$t_html = str_replace("&amp;quot;", "&quot;", $t_html);
            $t_html = str_replace('="res/', '="' . helper_mantis_url('plugins/Plugins/res/'), $t_html);

            plugins_print_section('info', $t_html, 'fa-book');
            echo '<div class="space-10"></div>';
            
            $Parsedown = new ParsedownEx();

            $t_file_path = config_get_global( 'plugin_path' ) . 'Plugins' . DIRECTORY_SEPARATOR . 'CHANGELOG.md';
            $t_file = fopen($t_file_path , "r") or die("<br><br> &nbsp;&nbsp&nbsp;&nbsp;&nbsp&nbsp;<b>" . plugin_lang_get('cannot_open') . "</b>");
            $t_content = fread($t_file, filesize($t_file_path));
            fclose($t_file);

            $t_html = $Parsedown->text($t_content);
			// Bug in parsedown example '>' gets converted to &amp;gt; when it should be just &gt;
			$t_html = str_replace("&amp;gt;", "&gt;", $t_html);
			$t_html = str_replace("&amp;lt;", "&lt;", $t_html);
            $t_html = str_replace("&amp;quot;", "&quot;", $t_html);
            
            plugins_print_section('info_changelog', $t_html, 'fa-book');
        }
        #
        # 'Update' tab
        #
        else if ($t_current_tab === plugin_lang_get('management_update_title'))
        {
            plugins_print_update_section();
        }
?>
    </div>
    <div class="space-10"></div>
</div>

<?php
layout_page_end();
