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
add_filter( 'options_framework_location','WBF::of_location_override' );
add_filter( 'site_transient_update_plugins', 'WBF::unset_unwanted_updates', 999 );

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

    static function enable_default_components(){
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
    }

    static function reset_components_state(){
        if(!class_exists('\WBF\modules\components\ComponentsManager')) return;
        $default_components = apply_filters("wbf_default_components",array());
        $registered_components = \WBF\modules\components\ComponentsManager::getAllComponents();
        foreach($registered_components as $c_name => $c_data){
            \WBF\modules\components\ComponentsManager::disable($c_name);
        }
        foreach($default_components as $c_name){
            \WBF\modules\components\ComponentsManager::ensure_enabled($c_name);
        }
    }

	static function get_modules($include = false){
		$modules_dir = WBF_DIRECTORY."/modules";
		$modules = array();
		$dirs = array_filter(glob($modules_dir."/*"), 'is_dir');
		foreach($dirs as $d){
			$current_module_dir = $d;
			if(is_file($current_module_dir."/bootstrap.php")){
				$modules[basename($d)] = array(
					'path' => $current_module_dir,
					'bootstrap' => $current_module_dir."/bootstrap.php",
				);
				if($include) require_once $modules[basename($d)]['bootstrap'];
			}
		}
		return $modules;
	}

	static function load_modules(){
		return self::get_modules(true);
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

	static function component_is_loaded($name){
		if(class_exists('\WBF\modules\components\ComponentsManager') && array_key_exists($name,$GLOBALS['loaded_components'])) {
			return true;
		}

		return false;
	}

	/**
	 *
	 *
	 * HOOKS
	 *
	 *
	 */

    function after_setup_theme()
    {
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

	function init() {
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

	function register_libs(){
        wp_register_script("imagesLoaded-js",WBF_URL."/vendor/imagesLoaded/imagesloaded.pkgd.min.js",array(),null,true);
		wp_register_script("owlcarousel-js",WBF_URL."/vendor/owlcarousel/owl.carousel.min.js",array("jquery"),null,true);
		wp_register_style("owlcarousel-css",WBF_URL."/vendor/owlcarousel/assets/owl.carousel.css");
	}

	function admin_menu(){
		global $menu,$options_framework_admin,$WabootThemeUpdateChecker;

		//Check if must display the bubble warning
		if(isset($WabootThemeUpdateChecker))
			$updates_state = get_option($WabootThemeUpdateChecker->optionName,null);

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

	function unset_unwanted_updates($value){
		$acf_update_path = preg_replace("/^\//","",WBF_DIRECTORY.'/vendor/acf/acf.php');

		if(isset($value->response[$acf_update_path])){
			unset($value->response[$acf_update_path]);
		}

		return $value;
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
	function add_admin_compile_button($wp_admin_bar){
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

	function maybe_add_option() {
		$opt = get_option( "wbf_installed" );
		if ( ! $opt ) {
			self::activation();
		}
	}

	function activation() {
		//Set a flag to make other component able to check if framework is installed
		update_option( "wbf_installed", true );
		update_option( "wbf_path", WBF_DIRECTORY );
		update_option( "wbf_url", WBF_URL );
		update_option( "wbf_components_saved_once", false );
        self::enable_default_components();
	}

	function deactivation($template) {
		delete_option( "wbf_installed" );
		delete_option( "wbf_path" );
		delete_option( "wbf_url" );
        $theme_switched = get_option( 'theme_switched', "" );
        if(!empty($theme_switched)){
            $wbf_components_saved_once = (array) get_option("wbf_components_saved_once", array());
            if(($key = array_search($theme_switched, $wbf_components_saved_once)) !== false) {
                unset($wbf_components_saved_once[$key]);
            }
            if(empty($wbf_components_saved_once)){
                delete_option( "wbf_components_saved_once" );
            }else{
                update_option( "wbf_components_saved_once", $wbf_components_saved_once );
            }
        }
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

/**
 * Behaviors framework backup functions; handles the case in which the Behaviors are not loaded
 *
 * @param $name
 * @param int $post_id
 * @param string $return
 *
 * @return array|bool|mixed|string
 */
function get_behavior( $name, $post_id = 0, $return = "value" ) {
    if (class_exists('\WBF\modules\behaviors\BehaviorsManager')) {
        return \WBF\modules\behaviors\get_behavior( $name, $post_id = 0, $return = "value" ); //call the behavior framework function
    } else {
        return WBF::get_behavior( $name, $post_id = 0, $return = "value" ); //call the backup function
    }
}

/**
 * \WBF\modules\options\of_get_option wrapper function
 * @param $name
 * @param bool $default
 * @return \WBF\modules\options\of_get_option output
 */
function of_get_option($name, $default = false){
    if(function_exists('\WBF\modules\options\of_get_option'))
        return \WBF\modules\options\of_get_option($name,$default);
    else
        return $default;
}

/**
 * WP UPDATE SERVER
 */
$GLOBALS['WabootThemeUpdateChecker'] = new \WBF\includes\Theme_Update_Checker(
	'waboot', //Theme slug. Usually the same as the name of its directory.
	'http://update.waboot.org/?action=get_metadata&slug=waboot' //Metadata URL.
);