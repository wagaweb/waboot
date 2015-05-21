<?php

$wbf_path = get_option( "wbf_path" );
require_once $wbf_path."/includes/pluginsframework/autoloader.php";

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Waboot_Plugin
 */
class Waboot_Plugin_Loader extends WBF\includes\pluginsframework\Loader {}