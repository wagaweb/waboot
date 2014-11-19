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

add_action("after_setup_theme", "WBF::after_setup_theme");
add_action("init", "WBF::init");

class WBF
{
    static function get_mobile_detect()
    {
        global $md;
        if (!$md instanceof Mobile_Detect) {
            $md = new Mobile_Detect();
            $md->setDetectionType('extended');
        }
        return $md;
    }

    function after_setup_theme()
    {
        //Global Customization
        locate_template('/wbf/public/global-customizations.php', true);

        //Utility
        locate_template('/wbf/public/utility.php', true);

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

    function init()
    {
        //The debugger
        locate_template('/wbf/public/waboot-debug.php', true);
        //waboot_debug_init();
    }
}

// WP Update Server
$WabootThemeUpdateChecker = new ThemeUpdateChecker(
    'waboot', //Theme slug. Usually the same as the name of its directory.
    'http://wpserver.wagahost.com/?action=get_metadata&slug=waboot' //Metadata URL.
);

/**
 * Less compiling
 */
if (isset($_GET['compile']) && $_GET['compile'] == true) {
    if (current_user_can('manage_options')) {
        locate_template('/inc/compiler/less-php/compiler.php', true);
        waboot_compile_less();
    }
}