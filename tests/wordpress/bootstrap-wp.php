<?php

error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );

define("WBTEST_CURRENT_PATH",dirname(__FILE__));
define("WBTEST_WORDPRESS_PATH",dirname(dirname(dirname(dirname(dirname(WBTEST_CURRENT_PATH))))));
define("WBTEST_WP_CONTENT_PATH",dirname(dirname(dirname(dirname(WBTEST_CURRENT_PATH)))));
define("WBTEST_CONFIG_PATH",WBTEST_CURRENT_PATH.'/configs/wp-tests-config.php');

define("WBF_PREVENT_STARTUP", true);

/*
 * Globalize some WordPress variables, because PHPUnit loads this file inside a function
 * See: https://github.com/sebastianbergmann/phpunit/issues/325
 */
global $wpdb, $current_site, $current_blog, $wp_rewrite, $shortcode_tags, $wp;

//Load configs
if(!is_readable(WBTEST_CONFIG_PATH)){
	die( "ERROR: wp-tests-config.php is missing! Please use wp-tests-config-sample.php to create a config file.\n" );
}

require_once WBTEST_CONFIG_PATH;

//Load utility functions
require_once WBTEST_CURRENT_PATH . '/includes/functions.php';

tests_reset__SERVER();

define('WP_TESTS_TABLE_PREFIX', $table_prefix); //ATTENTION: Do not use "global $table_prefix;"
define('DIR_TESTDATA', WBTEST_CURRENT_PATH . '/data');

define('WP_LANG_DIR', DIR_TESTDATA . '/languages');

if(!defined('WP_TESTS_FORCE_KNOWN_BUGS')){
	define('WP_TESTS_FORCE_KNOWN_BUGS',false);
}

define( 'DISABLE_WP_CRON', true ); // Cron tries to make an HTTP request to the blog, which always fails, because tests are run in CLI mode only

define('WP_MEMORY_LIMIT', -1);
define('WP_MAX_MEMORY_LIMIT', -1);

$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['HTTP_HOST'] = WP_TESTS_DOMAIN;
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

//Should we run in multisite mode?
$multisite = '1' == getenv('WP_MULTISITE');
$multisite = $multisite || ( defined('WP_TESTS_MULTISITE') && WP_TESTS_MULTISITE);
$multisite = $multisite || ( defined('MULTISITE') && MULTISITE);

//Override the PHPMailer
require_once( WBTEST_CURRENT_PATH . '/includes/mock-mailer.php' );
$phpmailer = new MockPHPMailer();

//Install WP
system( WP_PHP_BINARY . ' ' . escapeshellarg( WBTEST_CURRENT_PATH . '/includes/install.php' ) . ' ' . escapeshellarg( WBTEST_CONFIG_PATH ) . ' ' . $multisite );

if($multisite){
	//echo "Running as multisite..." . PHP_EOL;
	defined( 'MULTISITE' ) or define( 'MULTISITE', true );
	defined( 'SUBDOMAIN_INSTALL' ) or define( 'SUBDOMAIN_INSTALL', false );
	$GLOBALS['base'] = '/';
}else{
	//echo "Running as single site... To run multisite, use -c tests/phpunit/multisite.xml" . PHP_EOL;
}
unset( $multisite );

$GLOBALS['_wp_die_disabled'] = false;
//Allow tests to override wp_die
tests_add_filter( 'wp_die_handler', '_wp_die_handler_filter' );

/*
 * Preset WordPress options defined in bootstrap file.
 * Used to activate themes, plugins, as well as other settings.
 */
if(isset($GLOBALS['wp_tests_options'])){
	function wp_tests_options($value){
		$key = substr( current_filter(), strlen( 'pre_option_' ));
		return $GLOBALS['wp_tests_options'][$key];
	}
	foreach(array_keys($GLOBALS['wp_tests_options']) as $key){
		tests_add_filter('pre_option_'.$key, 'wp_tests_options');
	}
}

//Load environment
function _manually_load_environment() {
	switch_theme('waboot');
}
tests_add_filter('muplugins_loaded', '_manually_load_environment');

//Load WordPress
require_once ABSPATH . '/wp-settings.php';

// Delete any default posts & related data
//_delete_all_posts();

//Requirements
require WBTEST_CURRENT_PATH . '/includes/WP_UnitTestCase.php';
require WBTEST_CURRENT_PATH . '/includes/exceptions.php';
require WBTEST_CURRENT_PATH . '/includes/utils.php';
require WBTEST_CURRENT_PATH . '/includes/spy-rest-server.php';

require_once WBTEST_CURRENT_PATH . '/includes/WP_PHPUnit_Util_Getopt.php';
new WP_PHPUnit_Util_Getopt( $_SERVER['argv'] );