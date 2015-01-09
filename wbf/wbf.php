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
//locate_template( '/wbf/includes/compiler/less-php/compiler.php', true );

$md = WBF::get_mobile_detect();

add_action( "after_switch_theme", "WBF::activation" );
add_action( "switch_theme", "WBF::deactivation" );
add_action( "after_setup_theme", "WBF::after_setup_theme" );
add_action( "init", "WBF::init" );
add_action( "updated_option", "WBF::of_style_options_save", 11, 3 );
add_action( "updated_option", "WBF::compile_less_on_theme_options_save", 9999, 3 );
add_action( 'admin_menu', 'WBF::admin_menu' );
add_action( 'admin_bar_menu', 'WBF::add_env_notice', 980 );
add_action( 'admin_bar_menu', 'WBF::add_admin_compile_button', 990 );
add_action( 'wp_enqueue_scripts', 'WBF::register_libs' );
add_filter('options_framework_location','WBF::of_location_override');

class WBF {
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
		if(class_exists("Waboot_ComponentsManager") && array_key_exists($name,$GLOBALS['loaded_components'])) {
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

        //Global Customization
	    locate_template( '/wbf/public/theme-customs.php', true );

        //Utility
	    locate_template( '/wbf/public/utilities.php', true );
        locate_template('/wbf/vendor/lostpress-utils.php', true);

        // Email encoder
        locate_template('/wbf/public/email-encoder.php', true);

        // Load waboot textdomain
        load_theme_textdomain('waboot', get_template_directory() . '/languages');

        // Load the CSS
	    locate_template( '/wbf/public/public-styles.php', true );
	    locate_template( '/wbf/admin/adm-styles.php', true );

        // Load scripts
        //locate_template( '/wbf/public/scripts.php', true );
        //locate_template( '/wbf/admin/scripts.php', true );

	    //ACF INTEGRATION
        if(!is_plugin_active("advanced-custom-fields-pro/acf.php") && !is_plugin_active("advanced-custom-fields/acf.php")){
            locate_template( '/wbf/vendor/acf/acf.php', true );
            locate_template( '/wbf/admin/acf-integration.php', true );
        }

        // Google Fonts
        locate_template('/wbf/includes/google-fonts-retriever.php', true);
        $GLOBALS['wbf_gfont_fetcher'] = WBF\GoogleFontsRetriever::getInstance();

        // Load behaviors extension
	    locate_template( '/wbf/admin/behaviors-framework.php', true );
        locate_template('/inc/behaviors.php', true);

        // Load theme options framework
        locate_template('/wbf/admin/options-panel.php', true);

        // Load components framework
	    locate_template( '/wbf/admin/components-framework.php', true );
	    locate_template( '/wbf/admin/components-hooks.php', true ); //Components hooks

        // Breadcrumbs
        if (of_get_option('waboot_breadcrumbs', 1)) {
            locate_template('/wbf/vendor/breadcrumb-trail.php', true);
	        locate_template( '/wbf/public/breadcrumb-trail.php', true );
        }

        //Loads components
        Waboot_ComponentsManager::toggle_components(); //enable or disable components if necessary
        Waboot_ComponentsManager::init();
        Waboot_ComponentsManager::setupRegisteredComponents();
    }

	function init() {
		//The debugger
		locate_template( '/wbf/public/debug.php', true );
		//waboot_debug_init();
	}

	function register_libs(){
		wp_register_script("owlcarousel-js",WBF_URL."/vendor/owlcarousel/owl.carousel.min.js",array("jquery"),null,true);
		wp_register_style("owlcarousel-css",WBF_URL."/vendor/owlcarousel/assets/owl.carousel.css");
	}

	function admin_menu(){
		global $menu,$options_framework_admin;
		$menu['58']     = $menu['59']; //move the separator before "Appearance" one position up
		$waboot_menu    = add_menu_page( "Waboot", "Waboot", "edit_theme_options", "waboot_options", "waboot_options_page", "dashicons-text", 59 );
		//$waboot_options = add_submenu_page( "waboot_options", __( "Theme options", "waboot" ), __( "Theme Options", "waboot" ), "edit_theme_options", "waboot_options", array($options_framework_admin,"options_page") );
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
     * Replace {of_get_option} tags in _theme-options-generated.less.cmp; It is called during "update_option" and only for of theme options.
     * @param string $option option name
     * @param $old_value old value of the option
     * @param $value new value of the option
     */
	function of_style_options_save($option, $old_value, $value){
		$config = get_option( 'optionsframework' );
		if($option == $config['id']){
			$tmpFile = new SplFileInfo(get_stylesheet_directory()."/sources/less/_theme-options-generated.less.cmp");
			if(!$tmpFile->isFile() || !$tmpFile->isWritable()){
				$tmpFile = new SplFileInfo(get_template_directory()."/sources/less/_theme-options-generated.less.cmp");
			}
			$parsedFile = new SplFileInfo(get_stylesheet_directory()."/sources/less/theme-options-generated.less");
			if($tmpFile->isFile() && $tmpFile->isWritable()){
				$genericOptionfindRegExp = "~//{of_get_option\('([a-zA-Z0-9\-_]+)'\)}~";
				$fontOptionfindRegExp = "~//{of_get_font\('([a-zA-Z0-9\-_]+)'\)}~";

				$tmpFileObj = $tmpFile->openFile("r");
				$parsedFileObj = $parsedFile->openFile("w+");

				while (!$tmpFileObj->eof()) {
					$line = $tmpFileObj->fgets();
					//Replace a generic of option
                    if(preg_match($genericOptionfindRegExp,$line,$matches)){
						if(array_key_exists($matches[1],$value)){
							if($value[$matches[1]] != ""){
								$line = preg_replace($genericOptionfindRegExp,$value[$matches[1]],$line);
							}else{
								$line = "//{$matches[1]} is empty\n";
							}
						}else{
							$line = "//{$matches[1]} not found\n";
						}
					}
                    //Replace a font option
                    if(preg_match($fontOptionfindRegExp,$line,$matches)){
                        $line = "//{$matches[1]} is empty\n";
                        if(array_key_exists($matches[1],$value)){
                            if($value[$matches[1]] != ""){
                                $attr = $value[$matches[1]];
                                $fontString = "font-family: '".$attr['family']."', ".$attr['category'].";";
                                if(preg_match("/([0-9]+)([a-z]+)/",$attr['style'],$style_matches)){
                                    if($style_matches[1] == 'regular') $style_matches[1] = "normal";
                                    $fontString .= "font-weight: ".$style_matches[1].";";
                                    $fontString .= "font-style: ".$style_matches[2].";";
                                }else{
                                    if($attr['style'] == 'regular') $attr['style'] = "normal";
                                    $fontString .= "font-weight: ".$attr['style'].";";
                                }
                                $fontString .= "color: ".$attr['color'].";";
                                $line = $fontString;
                            }else{
                                $line = "//{$matches[1]} is empty\n";
                            }
                        }else{
                            $line = "//{$matches[1]} not found\n";
                        }
                    }
					$parsedFileObj->fwrite($line);
				}
			}
		}
	}

	function compile_less_on_theme_options_save($option, $old_value, $value){
		$config = get_option( 'optionsframework' );
		if($option == $config['id']){
			if(isset($GLOBALS['waboot_styles_compiler'])){
				global $waboot_styles_compiler;
				$waboot_styles_compiler->compile();
			}
		}
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
	}

	function deactivation() {
		delete_option( "wbf_installed" );
		delete_option( "wbf_path" );
		delete_option( "wbf_url" );
	}
}

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
    if (class_exists("BehaviorsManager")) {
	    return wbf_get_behavior( $name, $post_id = 0, $return = "value" ); //call the behavior framework function
    } else {
	    return WBF::get_behavior( $name, $post_id = 0, $return = "value" ); //call the backup function
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
		<h2><?php _e( "Waboot Options", "waboot" ); ?></h2>

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
 * WP UPDATE SERVER
 */
$WabootThemeUpdateChecker = new ThemeUpdateChecker(
    'waboot', //Theme slug. Usually the same as the name of its directory.
    'http://wpserver.wagahost.com/?action=get_metadata&slug=waboot' //Metadata URL.
);