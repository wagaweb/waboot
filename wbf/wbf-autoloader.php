<?php

require_once("vendor/autoload.php");

spl_autoload_register('wbf_autoloader');

/**
 * Waboot autoloader
 * @param $class
 * @since 0.1.4
 */
function wbf_autoloader($class) {

    //Load Options Framework Classes
    if (preg_match("/^Options_Framework_/", $class)) {
        $filename = "class-" . strtolower(preg_replace("/_/", "-", $class)) . ".php";
        if ($class == "Options_Framework_Admin") {
	        wbf_locate_file('vendor/options-framework/' . $filename, true);
        } else {
            $filename = preg_replace("/-framework/", "", $filename);
	        wbf_locate_file('vendor/options-framework/' . $filename, true);
        }
    }

    if (preg_match("/conditions/", $class)) {
        $childclass = explode('\\', $class);
        $name = end($childclass);
	    wbf_locate_file('admin/conditions/'.$name.'.php', true);
    }

    if (preg_match("/modules/", $class)) {
        $childclass = explode('\\', $class);
        $name = end($childclass);
        $module = $childclass[2];
	    wbf_locate_file('modules/'.$module.'/'.$name.'.php', true);
    }

    switch ($class) {
	    case 'WBF\includes\License_Interface':
		    wbf_locate_file('includes/license-interface.php', true);
		    break;
	    case 'WBF\includes\License':
		    wbf_locate_file('includes/class-license.php', true);
		    break;
	    case 'WBF\includes\License_Exception':
		    wbf_locate_file('includes/class-license-exception.php', true);
		    break;
        case 'WBF\admin\License_Manager':
	        wbf_locate_file('admin/license-manager.php', true);
            break;
        case 'WBF\admin\Notice_Manager':
	        wbf_locate_file('admin/notice-manager.php', true);
            break;
        case 'WBF\includes\Plugin_Update_Checker':
	        wbf_locate_file('includes/plugin-update-checker.php', true);
            break;
	    case 'WBF\includes\Theme_Update_Checker':
		    wbf_locate_file('includes/theme-update-checker.php', true);
		    break;
	    case 'WBF\includes\compiler\Styles_Compiler':
		    wbf_locate_file('includes/compiler/class-styles-compiler.php', true);
		    break;
	    case 'WBF\includes\compiler\Base_Compiler':
		    wbf_locate_file('includes/compiler/interface-base-compiler.php', true);
		    break;
        case 'WBF\includes\compiler\less\Less_Cache':
	        wbf_locate_file('includes/compiler/less/Less_Cache.php', true);
            break;
        case 'WBF\includes\compiler\less\Less_Compiler':
	        wbf_locate_file('includes/compiler/less/Less_Compiler.php', true);
            break;
	    case 'Mobile_Detect':
		    wbf_locate_file('vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php', true);
		    break;
        case "Less_Cache":
	        wbf_locate_file('includes/compiler/less/vendor/Lessphp/Cache.php', true);
            break;
        case "Less_Parser":
	        wbf_locate_file('includes/compiler/less/vendor/Lessphp/Less.php', true);
            break;
        case "lessc":
	        wbf_locate_file('includes/compiler/less/vendor/Lessphp/lessc.inc.php', true);
            break;
        case "Less_Version":
	        wbf_locate_file('includes/compiler/less/vendor/Lessphp/Version.php', true);
            break;
        case "BootstrapNavMenuWalker":
	        wbf_locate_file('vendor/BootstrapNavMenuWalker.php', true);
            break;
        case "WabootNavMenuWalker":
	        wbf_locate_file('public/menu-navwalker.php', true);
            break;
        case "ThemeUpdate":
        case "ThemeUpdateChecker":
	        wbf_locate_file('vendor/theme-updates/theme-update-checker.php', true);
            break;
        case "Options_Framework":
	        wbf_locate_file('vendor/options-framework/class-options-framework.php', true);
            break;
    }
}