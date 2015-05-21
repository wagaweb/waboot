<?php

$wbf_path = get_option( "wbf_path" );
require_once $wbf_path."/includes/pluginsframework/autoloader.php";

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Waboot_Plugin
 */
class Waboot_Plugin_i18n extends WBF\includes\pluginsframework\I18n {}
