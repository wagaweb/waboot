<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.waga.it
 * @since             0.13.4
 * @package           WBF
 *
 * @wordpress-plugin
 * Plugin Name:       Waboot Framework
 * Plugin URI:        http://www.waga.it
 * Description:       WordPress Extension Framework
 * Version:           0.13.4
 * Author:            WAGA
 * Author URI:        http://www.waga.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wbf
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if( ! class_exists('WBF') ) :

	if (!defined('WBF_ENV')) {
		define('WBF_ENV', 'production');
	}

	require_once('includes/utilities.php'); // Utility

	define("WBF_DIRECTORY", __DIR__);
	if(preg_match("/wp-content\/themes/", __DIR__ )){
		$url = rtrim(path_to_url(dirname(__FILE__)),"/")."/"; //ensure trailing slash
		define("WBF_URL", $url);
	}else{
		define("WBF_URL", get_bloginfo("url") . "/wp-content/plugins/wbf/");
	}
	define("WBF_ADMIN_DIRECTORY", __DIR__ . "/admin");
	define("WBF_PUBLIC_DIRECTORY", __DIR__ . "/public");

	require_once("wbf-autoloader.php");
	require_once("backup-functions.php");
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

	class WBF {

		var $options;
		var $update_instance;
		var $modules;
		var $url;
		var $path;

		const version = "0.13.4";

		public static function getInstance($args = []){
			static $instance = null;
			if (null === $instance) {
				$instance = new static($args = []);
			}

			return $instance;
		}

		protected function __construct($args = []){
			$args = wp_parse_args($args,[
				'do_global_theme_customizations' => true
			]);
			$this->options = $args;
			$this->startup();
		}

		static function handle_errors($errno,$errstr,$errfile,$errline,$errcontext){
			global $wbf_notice_manager;
			if($wbf_notice_manager && is_admin() && current_user_can("manage_options")){
				$str = sprintf('[Admin Only] There was an USER_WARNING error generated at %s:%s: <strong>%s</strong>',basename($errfile),$errline,$errstr);
				$wbf_notice_manager->add_notice($errline,$str,"error","_flash_");
			}
		}

		function startup(){

			set_error_handler('\WBF::handle_errors',E_USER_WARNING);

			$this->maybe_run_activation();

			$this->url = self::get_url();
			$this->path = self::get_path();

			if($this->options['do_global_theme_customizations']){
				add_action('wbf_after_setup_theme',[$this,'do_global_theme_customizations']);
			}

			$GLOBALS['md'] = $this->get_mobile_detect();
			$this->init_styles_compiler();

			if($this->is_plugin()){
				add_action('activate_' . plugin_basename(__FILE__), [$this,"maybe_run_activation"]);
				add_action('deactivate_' . plugin_basename(__FILE__), [$this,"deactivation"]);
			}else{
				add_action( "after_switch_theme", [$this,"activation"] );
				add_action( "switch_theme", [$this,"deactivation"], 4 );
			}

			if($this->is_plugin()) {
				add_action( "plugins_loaded", [$this,"plugins_loaded"] );
			}
			add_action( "after_setup_theme", [$this,"after_setup_theme"] );
			add_action( "init", [$this,"init"] );

			add_action( 'admin_menu', [$this,"admin_menu"] );
			add_action( 'admin_bar_menu', [$this,"add_env_notice"], 1000 );
			add_action( 'admin_bar_menu', [$this,"add_admin_compile_button"], 990 );

			if(class_exists('\WBF\admin\License_Manager')){
				\WBF\admin\License_Manager::init();
			}

			add_action( 'wp_enqueue_scripts', [$this,"register_libs"] );
			add_action( 'admin_enqueue_scripts', [$this,"register_libs"] );

			add_filter( 'options_framework_location',[$this,"of_location_override"] );
			add_filter( 'site_transient_update_plugins', [$this,"unset_unwanted_updates"], 999 );

			add_filter( 'wbf/modules/available', [$this,"do_not_load_pagebuilder"], 999 ); //todo: finché non è stabile, escludiamolo dai moduli

			//Set update server
			if(self::is_plugin()){
				$this->update_instance = new \WBF\includes\Plugin_Update_Checker("http://update.waboot.org/?action=get_metadata&slug=wbf&type=plugin",self::get_path(),"wbf",null,false,12,'wbf_updates');
			}
		}

		/**
		 *
		 *
		 * UTILITY
		 *
		 *
		 */

		static function get_mobile_detect() {
			global $md;
			if ( ! $md instanceof Mobile_Detect ) {
				$md = new Mobile_Detect();
				$md->setDetectionType( 'extended' );
			}

			return $md;
		}

		private function init_styles_compiler(){
			$GLOBALS['wbf_styles_compiler'] = false;
		}

		static function set_styles_compiler($args,$base_compiler = null){
			$GLOBALS['wbf_styles_compiler'] = new \WBF\includes\compiler\Styles_Compiler($args,$base_compiler);
		}

		/**
		 * Checks if current admin page is part of WBF
		 * @return bool
		 */
		static function is_wbf_admin_page(){
			global $plugin_page;
			$valid_pages = array(
				'waboot_options',
				'waboot_components',
				'themeoptions_manager',
				'waboot_license'
			);

			if(in_array($plugin_page,$valid_pages)){
				return true;
			}

			return false;
		}

		static function print_copyright(){
			$theme = wp_get_theme();
			if($theme->stylesheet == "waboot"){
				$version = $theme->version;
			}
			if($theme->stylesheet != "waboot" && $theme->template == "waboot"){
				$theme = wp_get_theme("waboot");
				$version = $theme->version;
			}
			if($theme->stylesheet != "waboot" && $theme->template != "waboot"){
				$version = self::version;
			}
			$output = "<div class=\"wbf-copy\"><span><em>Waboot ".$version."</em>";
			$output .= "</span></div>";
			echo $output;
		}

		static function module_is_loaded($module_name){
			$modules = self::get_modules();
			foreach($modules as $name => $params){
				if($name == $module_name) return true;
			}

			return false;
		}

		static function get_modules($include = false){
			static $modules = array();
			if(!empty($modules)){
				return $modules;
			}

			$modules_dir = self::get_path()."modules";
			$dirs = array_filter(glob($modules_dir."/*"), 'is_dir');
			$dirs = apply_filters("wbf/modules/available", $dirs); //Allow developer to add\delete modules
			foreach($dirs as $d){
				$current_module_dir = $d;
				if(is_file($current_module_dir."/bootstrap.php")){
					$modules[basename($d)] = array(
						'path' => $current_module_dir,
						'bootstrap' => $current_module_dir."/bootstrap.php",
						'activation' => is_file($current_module_dir."/activation.php") ? $current_module_dir."/activation.php" : false,
						'deactivation' => is_file($current_module_dir."/deactivation.php") ? $current_module_dir."/deactivation.php" : false,
					);
					if($include) require_once $modules[basename($d)]['bootstrap'];
				}
			}
			return $modules;
		}

		function load_modules(){
			return $this->get_modules(true);
		}

		function load_modules_activation_hooks(){
			$modules = $this->get_modules();
			foreach($modules as $m){
				if($m['activation']){
					require_once $m['activation'];
				}
			}
		}

		function load_modules_deactivation_hooks(){
			$modules = $this->get_modules();
			foreach($modules as $m){
				if($m['deactivation']){
					require_once $m['deactivation'];
				}
			}
		}

		/**
		 * Checks if WBF is in the plugins directory
		 * @return bool
		 */
		static function is_plugin(){
			$path = self::get_path();
			if(preg_match("/plugins/",$path)){
				$is_plugin = true;
			}else{
				$is_plugin = false;
			}
			return apply_filters("wbf/is_plugin",$is_plugin);
		}

		/**
		 * Returns WBF url or FALSE
		 * @return bool|string
		 */
		static function get_url(){
			static $url;

			if(isset($url)) return $url;

			$url = get_option("wbf_url");
			if($url && is_string($url) && !empty($url)){
				$url = rtrim($url,"/")."/";
				return $url;
			}elseif(defined("WBF_URL")){
				$url = rtrim(WBF_URL,"/")."/";
				return $url;
			}
			return false;
		}

		/**
		 * Returns WBF path or FALSE
		 * @return bool|string
		 */
		static function get_path(){
			static $path;

			if(isset($path)) return $path;

			$path = get_option("wbf_path");
			if($path && is_string($path) && !empty($path)){
				$path = rtrim($path,"/")."/";
				return $path;
			}elseif(defined("WBF_DIRECTORY")){
				$path = rtrim(WBF_DIRECTORY,"/")."/";
				return $path;
			}
			return false;
		}

		/**
		 * Prefix $to with the WBF URL
		 * @param $to
		 *
		 * @return bool|string
		 */
		static function prefix_url($to){
			$url = trim(self::get_url());
			$to = trim($to);
			if($url){
				return rtrim($url,"/")."/".ltrim($to,"/");
			}else{
				return false;
			}
		}

		/**
		 * Prefix $to with the WBF PATH
		 * @param $to
		 *
		 * @return bool|string
		 */
		static function prefix_path($to){
			$path = trim(self::get_url());
			$to = trim($to);
			if($path){
				return rtrim($path,"/")."/".ltrim($to,"/");
			}else{
				return false;
			}
		}

		/**
		 *
		 *
		 * BACKUP FUNCTIONS
		 *
		 *
		 */

		static function get_behavior( $name, $post_id = 0, $return = "value" ) {
			if ( $post_id == 0 ) {
				global $post;
				$post_id = $post->ID;
			}

			$b = get_post_meta( "_behavior_" . $post_id, $name, true );

			if(!isset($b) || (is_bool($b) && $b == false)){
				$config = get_option( 'optionsframework' );
				$b = of_get_option( $config['id'] . "_behavior_" . $name );
			}

			$b = apply_filters("wbf/modules/behaviors/get",$b);
			$b = apply_filters("wbf/modules/behaviors/get/".$name,$b);

			return $b;
		}

		/**
		 *
		 *
		 * HOOKS
		 *
		 *
		 */

		function do_global_theme_customizations(){
			// Global Customization
			wbf_locate_file( '/public/theme-customs.php', true );

			// Email encoder
			wbf_locate_file( '/public/email-encoder.php', true );
		}

		/**
		 * Wordpress "plugins_loaded" callback
		 */
		function plugins_loaded(){
			// ACF INTEGRATION
			if(!is_plugin_active("advanced-custom-fields-pro/acf.php") && !is_plugin_active("advanced-custom-fields/acf.php")){
				require_once self::get_path().'vendor/acf/acf.php';
				require_once self::get_path().'admin/acf-integration.php';
			}
		}

		/**
		 * Wordpress "after_setup_theme" callback
		 */
		function after_setup_theme() {
			global $wbf_notice_manager;

			$this->maybe_add_option();

			$this->modules = $this->load_modules();

			// Make framework available for translation.
			load_textdomain( 'wbf', self::get_path() . 'languages/wbf-'.get_locale().".mo");

			if(!isset($wbf_notice_manager)){
				$GLOBALS['wbf_notice_manager'] = new \WBF\admin\Notice_Manager(); // Loads notice manager. The notice manager can be already loaded by plugins constructor prior this point.
			}

			// Load the CSS
			wbf_locate_file( '/public/public-styles.php', true );
			wbf_locate_file( '/admin/adm-styles.php', true );

			// Load scripts
			//locate_template( '/wbf/public/scripts.php', true );
			wbf_locate_file( '/admin/adm-scripts.php', true );

			do_action("wbf_after_setup_theme");

			// ACF INTEGRATION
			if(!self::is_plugin()){
				if(!is_plugin_active("advanced-custom-fields-pro/acf.php") && !is_plugin_active("advanced-custom-fields/acf.php")){
					wbf_locate_file( '/vendor/acf/acf.php', true );
					wbf_locate_file( '/admin/acf-integration.php', true );
				}
			}

			// Google Fonts
			wbf_locate_file('/includes/google-fonts-retriever.php', true);
			if(class_exists("WBF\GoogleFontsRetriever")) $GLOBALS['wbf_gfont_fetcher'] = WBF\GoogleFontsRetriever::getInstance();
		}

		/**
		 * Wordpress "init" callback
		 */
		function init() {
			do_action("wbf_init");

			// Breadcrumbs
			if(function_exists("of_get_option")) {
				if(of_get_option('waboot_breadcrumbs', 1)){
					wbf_locate_file( '/vendor/breadcrumb-trail.php', true );
					wbf_locate_file( '/public/breadcrumb-trail.php', true );
				}
			}else{
				wbf_locate_file( '/vendor/breadcrumb-trail.php', true);
				wbf_locate_file( '/public/breadcrumb-trail.php', true );
			}

			if(function_exists('\WBF\modules\options\of_check_options_deps')) \WBF\modules\options\of_check_options_deps(); //Check if theme options dependencies are met
			$GLOBALS['wbf_notice_manager']->enqueue_notices(); //Display notices
		}

		function register_libs(){
			/*
			 * STYLES
			 */
			wp_register_style("jquery-ui-style","//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css",array(),false,"all");
			wp_register_style("owlcarousel-css",WBF_URL."/vendor/owlcarousel/assets/owl.carousel.css");
			/*
			 * SCRIPTS
			 */
			wp_register_script('gmapapi', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places', array('jquery'), false, false );
			if(WBF_ENV == "dev"){
				wp_register_script("wbfgmapmc",WBF_URL."/sources/js/includes/wbfgmap/markerclusterer.js",array("jquery","gmapapi"),false,true);
				wp_register_script("wbfgmap",WBF_URL."/sources/js/includes/wbfgmap/acfmap.js",array("jquery","gmapapi","wbfgmapmc"),false,true);
			}else{
				wp_register_script("wbfgmap",WBF_URL."/includes/scripts/wbfgmap.min.js",array("jquery","gmapapi"),false,true);
			}
			wp_register_script("imagesLoaded-js",WBF_URL."/vendor/imagesloaded/imagesloaded.pkgd.min.js",[],false,true);
			wp_register_script("owlcarousel-js",WBF_URL."/vendor/owlcarousel/owl.carousel.min.js",array("jquery"),false,true);
		}

		function admin_menu(){
			global $menu,$options_framework_admin,$WBFThemeUpdateChecker;

			//Check if must display the bubble warning
			if(isset($WBFThemeUpdateChecker))
				$updates_state = get_option($WBFThemeUpdateChecker->optionName,null);

			if(isset($updates_state) && !is_null($updates_state->update))
				$warning_count = 1;
			else
				$warning_count = 0;

			$menu_label = sprintf( __( 'Waboot %s' ), "<span class='update-plugins count-$warning_count' title='".__("Update available","wbf")."'><span class='update-count'>" . number_format_i18n($warning_count) . "</span></span>" );

			$menu['58']     = $menu['59']; //move the separator before "Appearance" one position up
			$waboot_menu    = add_menu_page( "Waboot", $menu_label, "edit_theme_options", "waboot_options", "WBF::options_page", "dashicons-text", 59 );
			//$waboot_options = add_submenu_page( "waboot_options", __( "Theme options", "waboot" ), __( "Theme Options", "waboot" ), "edit_theme_options", "waboot_options", array($options_framework_admin,"options_page") );
			do_action("wbf_admin_submenu","waboot_options");
		}

		function unset_unwanted_updates($value){
			$acf_update_path = preg_replace("/^\//","",self::get_path().'vendor/acf/acf.php');

			if(isset($value->response[$acf_update_path])){
				unset($value->response[$acf_update_path]);
			}

			return $value;
		}

		function do_not_load_pagebuilder($module_dirs){
			foreach($module_dirs as $k => $dir){
				$module_name = basename($dir);
				if($module_name == "pagebuilder"){
					unset($module_dirs[$k]);
				}
			}

			return $module_dirs;
		}

		/**
		 * Add env notice to the admin bar
		 * @param $wp_admin_bar
		 * @since 0.2.0
		 */
		function add_env_notice($wp_admin_bar){
			global $post;

			if ( current_user_can( 'manage_options' ) ) {
				$args = array(
					'id'    => 'wbf_env_notice',
					'title' => _x("ENV","WBF Admin Bar","wbf").': '.WBF_ENV,
					'href'  => "#",
					'meta'  => array( 'class' => 'wbf-toolbar-env-notice' )
				);
				$wp_admin_bar->add_node( $args );
			}
		}

		/**
		 * Add a "Compile Less" button to the toolbar
		 * @param $wp_admin_bar
		 * @since 0.1.1
		 */
		function add_admin_compile_button($wp_admin_bar){
			global $post;

			if ( current_user_can( 'manage_options' ) ) {
				$args = array(
					'id'    => 'wbf_compile_styles',
					'title' => 'Compile CSS',
					'href'  => add_query_arg('compile','true'),
					'meta'  => array( 'class' => 'wbf-toolbar-compile-theme-styles-button' )
				);
				$wp_admin_bar->add_node( $args );
			}
		}

		function of_location_override(){
			return array("inc/options.php");
		}

		/**
		 *
		 *
		 * ACTIVATION \ DEACTIVATION
		 *
		 *
		 */

		function maybe_run_activation($force = false){
			if($force){
				$this->activation();
			}else{
				$opt = get_option( "wbf_installed" );
				if ( ! $opt ) {
					$this->activation();
				}
			}
		}

		function maybe_add_option() {
			$opt = get_option( "wbf_installed" );
			if( ! $opt || !self::has_valid_wbf_path()) {
				$this->add_wbf_options();
			}
		}

		function add_wbf_options(){
			update_option( "wbf_installed", true ); //Set a flag to make other component able to check if framework is installed
			update_option( "wbf_path", WBF_DIRECTORY );
			update_option( "wbf_url", WBF_URL );
			update_option( "wbf_components_saved_once", false );
		}

		function has_valid_wbf_path(){
			$path = get_option("wbf_path");
			if(!$path || empty($path) || !is_string($path)){
				return false;
			}
			if(file_exists($path."/wbf.php")){
				return true;
			}
			return false;
		}

		function activation() {
			$this->load_modules_activation_hooks();

			$this->add_wbf_options();
			do_action("wbf_activated");
			//self::enable_default_components();
		}

		function deactivation($template = null) {
			$this->load_modules_deactivation_hooks();
			delete_option( "wbf_installed" );
			delete_option( "wbf_path" );
			delete_option( "wbf_url" );
			if($template){
				$theme_switched = get_option( 'theme_switched', "" );
				do_action("wbf_deactivated", $theme_switched);
			}else{
				do_action("wbf_deactivated", "plugin");
			}
			/*if(!empty($theme_switched)){
				$wbf_components_saved_once = (array) get_option("wbf_components_saved_once", array());
				if(($key = array_search($theme_switched, $wbf_components_saved_once)) !== false) {
					unset($wbf_components_saved_once[$key]);
				}
				if(empty($wbf_components_saved_once)){
					delete_option( "wbf_components_saved_once" );
				}else{
					update_option( "wbf_components_saved_once", $wbf_components_saved_once );
				}
			}*/
		}

		/**
		 * Waboot options page for further uses
		 */
		static function options_page() {
			/*$options_framework_admin = new Waboot_Options_Framework_Admin;
			$options_framework_admin->options_page();*/
			return true;
			?>
			<div class="wrap">
				<h2><?php _e( "Waboot Options", "wbf" ); ?></h2>

				<p>
					--- Placeholder ---
				</p>
			</div>
			<?php
		}
	}

	$GLOBALS['wbf'] = WBF::getInstance();

else:
	//HERE WBF IS ALREADY DEFINED. We can't tell if by a plugin or via theme... So...

	//If this is a plugin, then force the options to point over the plugin.
	if(preg_match("/plugins/",__FILE__) && preg_match("/themes/",get_option("wbf_path"))){
		update_option( "wbf_path", __DIR__ );
		update_option( "wbf_url", get_bloginfo("url") . "/wp-content/plugins/wbf/" );
		define("WBF_DIRECTORY", __DIR__);
		define("WBF_URL", get_bloginfo("url") . "/wp-content/plugins/wbf/");
		define("WBF_ADMIN_DIRECTORY", __DIR__ . "/admin");
		define("WBF_PUBLIC_DIRECTORY", __DIR__ . "/public");
	}

endif; // class_exists check