<?php

require_once("vendor/autoload.php");

spl_autoload_register('wbf_autoloader');

/**
 * Waboot autoloader
 * @param $class
 * @since 0.1.4
 */
function wbf_autoloader($class)
{
    //Load Options Framework Classes
    if (preg_match("/^Options_Framework_/", $class)) {
        $filename = "class-" . strtolower(preg_replace("/_/", "-", $class)) . ".php";
        if ($class == "Options_Framework_Admin") {
            locate_template('wbf/vendor/options-framework/' . $filename, true);
        } else {
            $filename = preg_replace("/-framework/", "", $filename);
            locate_template('wbf/vendor/options-framework/' . $filename, true);
        }
    }

    /*if (preg_match("/^Waboot_Options_/", $class)) {
        $filename = "class-" . strtolower(preg_replace("/_/", "-", $class)) . ".php";
        locate_template('wbf/admin/' . $filename, true);
    }*/

    if (preg_match("/conditions/", $class)) {
        $childclass = explode('\\', $class);
        $name = end($childclass);
        locate_template( 'wbf/admin/conditions/'.$name.'.php', true );
    }

    if (preg_match("/modules/", $class)) {
        $childclass = explode('\\', $class);
        $name = end($childclass);
        $module = $childclass[2];
        locate_template( 'wbf/modules/'.$module.'/'.$name.'.php', true );
    }

    switch ($class) {
	    case "WBF\includes\License_Interface":
		    locate_template( 'wbf/includes/license-interface.php', true );
		    break;
        case "WBF\admin\License_Manager":
            locate_template( 'wbf/admin/license-manager.php', true );
            break;
        case "WBF\admin\Notice_Manager":
            locate_template( 'wbf/admin/notice-manager.php', true );
            break;
        case "WBF\includes\Plugin_Update_Checker":
            locate_template( 'wbf/includes/plugin-update-checker.php', true );
            break;
	    case "WBF\includes\Theme_Update_Checker":
		    locate_template( 'wbf/includes/theme-update-checker.php', true );
		    break;
	    case 'WBF\includes\compiler\Styles_Compiler':
		    locate_template( 'wbf/includes/compiler/class-styles-compiler.php', true );
		    break;
	    case 'WBF\includes\compiler\Base_Compiler':
		    locate_template( 'wbf/includes/compiler/interface-base-compiler.php', true );
		    break;
        case 'WBF\includes\compiler\less\Less_Cache':
	        locate_template( 'wbf/includes/compiler/less/Less_Cache.php', true );
            break;
        case 'WBF\includes\compiler\less\Less_Compiler':
	        locate_template( 'wbf/includes/compiler/less/Less_Compiler.php', true );
            break;
        case "Less_Cache":
	        locate_template( 'wbf/includes/compiler/less/vendor/Lessphp/Cache.php', true );
            break;
        case "Less_Parser":
	        locate_template( 'wbf/includes/compiler/less/vendor/Lessphp/Less.php', true );
            break;
        case "lessc":
	        locate_template( 'wbf/includes/compiler/less/vendor/Lessphp/lessc.inc.php', true );
            break;
        case "Less_Version":
	        locate_template( 'wbf/includes/compiler/less/vendor/Lessphp/Version.php', true );
            break;
        case "BootstrapNavMenuWalker":
            locate_template('wbf/vendor/BootstrapNavMenuWalker.php', true);
            break;
        case "WabootNavMenuWalker":
	        locate_template( 'wbf/public/menu-navwalker.php', true );
            break;
        case "ThemeUpdate":
        case "ThemeUpdateChecker":
            locate_template('wbf/vendor/theme-updates/theme-update-checker.php', true);
            break;
        /*case "PluginUpdateChecker":
        case "PluginUpdate":
        case "PluginInfo":
        case "PluginUpdateChecker_1_6":
        case "PluginInfo_1_6":
        case "PluginUpdate_1_6":
        case "PucFactory":
            locate_template('wbf/vendor/plugin-updates/plugin-update-checker.php', true);
            break;*/
        /*case "Mobile_Detect":
            locate_template('wbf/vendor/Mobile_Detect.php', true);
            break;*/
        case "Options_Framework":
            locate_template('wbf/vendor/options-framework/class-options-framework.php', true);
            break;
    }
}