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

$md = WBF::get_mobile_detect();

add_action( "after_switch_theme", "WBF::activation" );
add_action( "switch_theme", "WBF::deactivation" );
add_action("after_setup_theme", "WBF::after_setup_theme");
add_action("init", "WBF::init");

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
        locate_template('/wbf/public/global-customizations.php', true);

        //Utility
        locate_template('/wbf/public/utility.php', true);
        locate_template('/wbf/vendor/lostpress-utils.php', true);

        // Email encoder
        locate_template('/wbf/public/email-encoder.php', true);

        // Load waboot textdomain
        load_theme_textdomain('waboot', get_template_directory() . '/languages');

        // Load the CSS
        locate_template('/wbf/public/styles.php', true);
        locate_template('/wbf/admin/styles.php', true);

        // Load scripts
        //locate_template( '/wbf/public/scripts.php', true );
        //locate_template( '/wbf/admin/scripts.php', true );

	    //ACF INTEGRATION
	    locate_template( '/wbf/vendor/acf/acf.php', true );
	    locate_template( '/wbf/admin/acf-integration.php', true );

        // Load behaviors extension
        locate_template('/wbf/admin/waboot-behaviors-framework.php', true);
        locate_template('/inc/behaviors.php', true);

        // Load theme options framework
        locate_template('/wbf/admin/options-panel.php', true);

        // Load components framework
        locate_template('/wbf/admin/waboot-components-framework.php', true);
        locate_template('/wbf/admin/waboot-components-hooks.php', true); //Components hooks

        // Breadcrumbs
        if (of_get_option('waboot_breadcrumbs', 1)) {
            locate_template('/wbf/vendor/breadcrumb-trail.php', true);
            locate_template('/wbf/public/waboot-breadcrumb-trail.php', true);
        }

        //Loads components
        Waboot_ComponentsManager::init();
        Waboot_ComponentsManager::setupRegisteredComponents();
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

	function init() {
		//The debugger
		locate_template( '/wbf/public/waboot-debug.php', true );
		//waboot_debug_init();
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
 * WP UPDATE SERVER
 */
$WabootThemeUpdateChecker = new ThemeUpdateChecker(
    'waboot', //Theme slug. Usually the same as the name of its directory.
    'http://wpserver.wagahost.com/?action=get_metadata&slug=waboot' //Metadata URL.
);

/**
 * Less compiling
 */
if (isset($_GET['compile']) && $_GET['compile'] == true) {
    if (current_user_can('manage_options')) {
        locate_template('/wbf/compiler/less-php/compiler.php', true);
        waboot_compile_less();
    }
}