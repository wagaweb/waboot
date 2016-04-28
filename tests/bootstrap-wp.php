<?php
error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );

define("WBTEST_CURRENT_PATH",dirname(__FILE__));
define("WBTEST_WP_CONTENT_PATH",dirname(dirname(dirname(WBTEST_CURRENT_PATH))));
define("WBTEST_WORDPRESS_PATH",dirname(dirname(dirname(dirname(WBTEST_CURRENT_PATH)))));

require_once dirname(WBTEST_CURRENT_PATH)."/vendor/autoload.php";

$config_file_path = WBTEST_CURRENT_PATH . '/configs/wp-tests-config.php';

/*
 * Globalize some WordPress variables, because PHPUnit loads this file inside a function
 * See: https://github.com/sebastianbergmann/phpunit/issues/325
 */
global $wpdb, $current_site, $current_blog, $wp_rewrite, $shortcode_tags, $wp;

if(!is_readable( $config_file_path)){
	die( "ERROR: wp-tests-config.php is missing! Please use wp-tests-config-sample.php to create a config file.\n" );
}
require_once $config_file_path;

if(!defined('WP_TESTS_FORCE_KNOWN_BUGS')){
	define('WP_TESTS_FORCE_KNOWN_BUGS',false);
}

// Cron tries to make an HTTP request to the blog, which always fails, because tests are run in CLI mode only
define( 'DISABLE_WP_CRON', true );

define('WP_MEMORY_LIMIT', -1);
define('WP_MAX_MEMORY_LIMIT', -1);

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['HTTP_HOST'] = WP_TESTS_DOMAIN;
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

// Load WordPress
require_once ABSPATH . '/wp-settings.php';