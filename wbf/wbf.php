<?php

if (!defined('WABOOT_ENV')) {
    define('WABOOT_ENV', 'production');
}

if (!defined('LESS_LIVE_COMPILING')) {
    define('LESS_LIVE_COMPILING', false);
}

define("WBF_DIRECTORY", __DIR__);
define("WBF_URL", get_template_directory_uri() . "/wbf/");
define("WBF_ADMIN_DIRECTORY", __DIR__ . "/admin");
define("WBF_PUBLIC_DIRECTORY", __DIR__ . "/public");

require_once("wbf-autoloader.php");
require_once("backup-functions.php");
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

$md = WBF::get_mobile_detect();

add_action( "after_switch_theme", "WBF::activation" );
add_action( "switch_theme", "WBF::deactivation", 4 );

add_action( "after_setup_theme", "WBF::after_setup_theme" );
add_action( "init", "WBF::init" );
add_action( 'admin_menu', 'WBF::admin_menu' );
add_action( 'wbf_admin_submenu', 'WBF\admin\License_Manager::admin_license_menu_item', 30 );
add_action( 'admin_bar_menu', 'WBF::add_env_notice', 980 );
add_action( 'admin_bar_menu', 'WBF::add_admin_compile_button', 990 );
add_action( 'wp_enqueue_scripts', 'WBF::register_libs' );
add_action( 'admin_enqueue_scripts', 'WBF::register_libs' );
add_filter( 'options_framework_location','WBF::of_location_override' );
add_filter( 'site_transient_update_plugins', 'WBF::unset_unwanted_updates', 999 );

//add_filter( 'wbf_get_modules', 'WBF::do_not_load_pagebuilder', 999 ); //todo: finché non è stabile, escludiamolo dai moduli

class WBF {

	const version = "0.8.3";

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

    /*static function enable_default_components(){
        if(class_exists('\WBF\modules\components\ComponentsManager')){
            $theme = wp_get_theme();
            $components_already_saved = (array) get_option( "wbf_components_saved_once", array() );
            if(!in_array($theme->get_stylesheet(),$components_already_saved)){
                $default_components = apply_filters("wbf_default_components",array());
                foreach($default_components as $c_name){
                    \WBF\modules\components\ComponentsManager::ensure_enabled($c_name);
                }
            }
        }
    }*/

    /*static function reset_components_state(){
        if(!class_exists('\WBF\modules\components\ComponentsManager')) return;
        $default_components = apply_filters("wbf_default_components",array());
        $registered_components = \WBF\modules\components\ComponentsManager::getAllComponents();
        foreach($registered_components as $c_name => $c_data){
            \WBF\modules\components\ComponentsManager::disable($c_name);
        }
        foreach($default_components as $c_name){
            \WBF\modules\components\ComponentsManager::ensure_enabled($c_name);
        }
    }*/

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

		$modules_dir = WBF_DIRECTORY."/modules";
		$dirs = array_filter(glob($modules_dir."/*"), 'is_dir');
		$dirs = apply_filters("wbf_get_modules", $dirs); //Allow developer to add\delete modules
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

	static function load_modules(){
		return self::get_modules(true);
	}

	static function load_modules_activation_hooks(){
		$modules = self::get_modules();
		foreach($modules as $m){
			if($m['activation']){
				require_once $m['activation'];
			}
		}
	}

	static function load_modules_deactivation_hooks(){
		$modules = self::get_modules();
		foreach($modules as $m){
			if($m['deactivation']){
				require_once $m['deactivation'];
			}
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

		if ( $b ) {
			return $b;
		} else {
			$config = get_option( 'optionsframework' );
			$b      = of_get_option( $config['id'] . "_behavior_" . $name );

			return $b;
		}
	}

	/**
	 *
	 *
	 * HOOKS
	 *
	 *
	 */

    static function after_setup_theme() {
	    self::maybe_add_option();

	    $modules = self::load_modules();

	    // Make framework available for translation.
        load_textdomain( 'wbf', WBF_DIRECTORY . '/languages/wbf-'.get_locale().".mo");

        $GLOBALS['wbf_notice_manager'] = new WBF\admin\Notice_Manager(); // Loads notice manager

        // Global Customization
	    locate_template( '/wbf/public/theme-customs.php', true );

        // Utility
	    locate_template( '/wbf/includes/utilities.php', true );
        locate_template('/wbf/vendor/lostpress-utils.php', true);

        // Email encoder
        locate_template('/wbf/public/email-encoder.php', true);

        // Load the CSS
	    locate_template( '/wbf/public/public-styles.php', true );
	    locate_template( '/wbf/admin/adm-styles.php', true );

        // Load scripts
        //locate_template( '/wbf/public/scripts.php', true );
        locate_template( '/wbf/admin/adm-scripts.php', true );

	    do_action("wbf_after_setup_theme");

	    // ACF INTEGRATION
        if(!is_plugin_active("advanced-custom-fields-pro/acf.php") && !is_plugin_active("advanced-custom-fields/acf.php")){
            locate_template( '/wbf/vendor/acf/acf.php', true );
            locate_template( '/wbf/admin/acf-integration.php', true );
        }

        // Google Fonts
        locate_template('/wbf/includes/google-fonts-retriever.php', true);
        if(class_exists("WBF\GoogleFontsRetriever")) $GLOBALS['wbf_gfont_fetcher'] = WBF\GoogleFontsRetriever::getInstance();
    }

	static function init() {
		do_action("wbf_init");

		// Breadcrumbs
		if(function_exists("of_get_option")) {
			if(of_get_option('waboot_breadcrumbs', 1)){
				locate_template('/wbf/vendor/breadcrumb-trail.php', true);
				locate_template( '/wbf/public/breadcrumb-trail.php', true );
			}
		}else{
			locate_template('/wbf/vendor/breadcrumb-trail.php', true);
			locate_template( '/wbf/public/breadcrumb-trail.php', true );
		}

		if(function_exists('\WBF\modules\options\of_check_options_deps')) \WBF\modules\options\of_check_options_deps(); //Check if theme options dependencies are met
		$GLOBALS['wbf_notice_manager']->enqueue_notices(); //Display notices

		//The debugger
		locate_template( '/wbf/public/debug.php', true );
		//waboot_debug_init();
	}

	static function register_libs(){
		/*
		 * STYLES
		 */
		wp_register_style("jquery-ui-style","//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css",array(),false,"all");
		wp_register_style("owlcarousel-css",WBF_URL."/vendor/owlcarousel/assets/owl.carousel.css");
		/*
		 * SCRIPTS
		 */
		wp_register_script('gmapapi', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', array('jquery'), false, false );
		if(WBF_ENV == "dev"){
			wp_register_script("wbfgmapmc",WBF_URL."/sources/js/includes/wbfgmap/markerclusterer.js",array("jquery","gmapapi"),false,true);
			wp_register_script("wbfgmap",WBF_URL."/sources/js/includes/wbfgmap/acfmap.js",array("jquery","gmapapi","wbfgmapmc"),false,true);
		}else{
			wp_register_script("wbfgmap",WBF_URL."/includes/scripts/wbfgmap.min.js",array("jquery","gmapapi"),false,true);
		}
		wp_register_script("imagesLoaded-js",WBF_URL."/vendor/imagesLoaded/imagesloaded.pkgd.min.js",array(),false,true);
		wp_register_script("owlcarousel-js",WBF_URL."/vendor/owlcarousel/owl.carousel.min.js",array("jquery"),false,true);
	}

	static function admin_menu(){
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
		$waboot_menu    = add_menu_page( "Waboot", $menu_label, "edit_theme_options", "waboot_options", "waboot_options_page", "dashicons-text", 59 );
		//$waboot_options = add_submenu_page( "waboot_options", __( "Theme options", "waboot" ), __( "Theme Options", "waboot" ), "edit_theme_options", "waboot_options", array($options_framework_admin,"options_page") );
		do_action("wbf_admin_submenu","waboot_options");
	}

	static function unset_unwanted_updates($value){
		$acf_update_path = preg_replace("/^\//","",WBF_DIRECTORY.'/vendor/acf/acf.php');

		if(isset($value->response[$acf_update_path])){
			unset($value->response[$acf_update_path]);
		}

		return $value;
	}

	static function do_not_load_pagebuilder($module_dirs){
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
	static function add_env_notice($wp_admin_bar){
		global $post;

		if ( current_user_can( 'manage_options' ) ) {
			$args = array(
				'id'    => 'env_notice',
				'title' => '['.WABOOT_ENV."]",
				'href'  => "#",
				'meta'  => array( 'class' => 'toolbar-env-notice' )
			);
			$wp_admin_bar->add_node( $args );
		}
	}

	/**
	 * Add a "Compile Less" button to the toolbar
	 * @param $wp_admin_bar
	 * @since 0.1.1
	 */
	static function add_admin_compile_button($wp_admin_bar){
		global $post;

		if ( current_user_can( 'manage_options' ) ) {
			$args = array(
				'id'    => 'waboot_compile',
				'title' => 'Compile Less',
				'href'  => add_query_arg('compile','true'),
				'meta'  => array( 'class' => 'toolbar-compile-less-button' )
			);
			$wp_admin_bar->add_node( $args );
		}
	}

	static function of_location_override(){
		return array("inc/options.php");
	}

	/**
	 *
	 *
	 * ACTIVATION \ DEACTIVATION
	 *
	 *
	 */

	static function maybe_add_option() {
		$opt = get_option( "wbf_installed" );
		if ( ! $opt ) {
			self::add_wbf_options();
		}
	}

	private static function add_wbf_options(){
		update_option( "wbf_installed", true ); //Set a flag to make other component able to check if framework is installed
		update_option( "wbf_path", WBF_DIRECTORY );
		update_option( "wbf_url", WBF_URL );
		update_option( "wbf_components_saved_once", false );
	}

	static function activation() {
		self::load_modules_activation_hooks();

		self::add_wbf_options();
		do_action("wbf_activated");
        //self::enable_default_components();
	}

	static function deactivation($template) {
		self::load_modules_deactivation_hooks();
		$theme_switched = get_option( 'theme_switched', "" );

		delete_option( "wbf_installed" );
		delete_option( "wbf_path" );
		delete_option( "wbf_url" );
		do_action("wbf_deactivated", $theme_switched);
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
}

/**
 * Waboot options page for further uses
 */
function waboot_options_page() {
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

if(!is_admin() && !function_exists("waboot_mobile_body_class")):
	/**
	 * Adds mobile classes to body
	 */
	function waboot_mobile_body_class($classes){
		$md = WBF::get_mobile_detect();
		if($md->isMobile()){
			$classes[] = "mobile";
			if($md->is_ios()) $classes[] = "mobile-ios";
			if($md->is_android()){
				$classes[] = "mobile-android";
				$classes[] = "mobile-android-".$md->version('Android');
			}
			if($md->is_windows_mobile()) $classes[] = "mobile-windows";
			if($md->isTablet()) $classes[] = "mobile-tablet";
			if($md->isIphone()){
				$classes[] = "mobile-iphone";
				$classes[] = "mobile-iphone-".$md->version('IPhone');
			}
			if($md->isIpad()){
				$classes[] = "mobile-ipad";
				$classes[] = "mobile-ipad-".$md->version('IPad');
			}
			if($md->is('Kindle')) $classes[] = "mobile-kindle";
			if($md->is('Samsung')) $classes[] = "mobile-samsung";
			if($md->is('SamsungTablet')) $classes[] = "mobile-samsungtablet";
		}else{
			$classes[] = "desktop";
		}
		return $classes;
	}
	add_filter('body_class','waboot_mobile_body_class');
endif;