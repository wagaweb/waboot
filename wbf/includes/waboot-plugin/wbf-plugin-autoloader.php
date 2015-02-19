<?php

spl_autoload_register( "wbf_plugin_autoload" );
function wbf_plugin_autoload( $class ) {
	$wbf_path = get_option( "wbf_path" );

	if ( $wbf_path ) {
		$plugin_main_class_dir = $wbf_path . "/includes/waboot-plugin";

		if ( preg_match( "/^Waboot_Plugin|Template_/", $class ) ) {
			$filename = "class-" . strtolower( preg_replace( "/_/", "-", $class ) ) . ".php";
			if ( is_file( $plugin_main_class_dir . "/" . $filename ) ) {
				require_once( $plugin_main_class_dir . "/" . $filename );
			}
		}

		switch($class){
			case "WBF\admin\License_Manager":
				require_once($wbf_path . "/admin/license-manager.php");
				break;
			case "WBF\includes\Plugin_Update_Checker":
				require_once($wbf_path . "/includes/plugin-update-checker.php");
				break;
			case "PluginUpdateChecker":
			case "PluginUpdate":
			case "PluginInfo":
			case "PluginUpdateChecker_1_6":
			case "PluginInfo_1_6":
			case "PluginUpdate_1_6":
			case "PucFactory":
				require_once($wbf_path . "/vendor/plugin-updates/plugin-update-checker.php");
				break;
		}
	}
}