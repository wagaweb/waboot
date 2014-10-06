<?php

if(!defined('WABOOT_ENV')){
    define('WABOOT_ENV','production');
}

if(!defined('LESS_LIVE_COMPILING')){
    define('LESS_LIVE_COMPILING',false);
}

spl_autoload_register('waboot_autoloader');

if ( ! function_exists( 'waboot_setup' ) ):
    function waboot_setup() {

	    //Global Utilities
	    locate_template( '/inc/vendor/lostpress-utils.php', true );

        //Global Customization
        locate_template( '/inc/global-customizations.php', true );

        //Utility
        locate_template( '/inc/utility.php', true );

        // Custom template tags for this theme.
        locate_template( '/inc/hooks.php', true );
        locate_template( '/inc/template-tags.php', true );

        // Register the navigation menus.
        locate_template( '/inc/menus.php', true );
        locate_template( '/inc/vendor/BootstrapNavMenuWalker.php', true );
        locate_template( '/inc/vendor/wp_bootstrap_navwalker.php', true );
        locate_template( '/inc/waboot-menu-navwalker.php', true );

        // Register sidebars
        locate_template( '/inc/widgets.php', true );

        // Header image
        locate_template( '/inc/custom-header.php', true );

        // Load behaviors extension
        locate_template( '/admin/behaviors.php', true );

        // Load theme options framework
        locate_template( '/admin/options-panel.php', true );

        // Load components framework
        locate_template( '/admin/waboot-components-framework.php', true );

        // Customizer
        locate_template( '/inc/customizer.php', true );

        // Breadcrumbs
        if ( of_get_option( 'waboot_breadcrumbs',1) ) {
            locate_template( '/inc/vendor/breadcrumb-trail.php', true );
            locate_template( '/inc/waboot-breadcrumb-trail.php', true );
        }

        // Email encoder
        locate_template( '/inc/email-encoder.php', true );

        // Load the CSS
        locate_template( '/inc/stylesheets.php', true );

        // Load scripts
        locate_template( '/inc/scripts.php', true );

        /**
         * Make theme available for translation
         * Translations can be filed in the /languages/ directory
         * If you're building a theme based on waboot, use a find and replace
         * to change 'waboot' to the name of your theme in all the template files
         */
        load_theme_textdomain( 'waboot', get_template_directory() . '/languages' );

        // Add default posts and comments RSS feed links to head
        add_theme_support( 'automatic-feed-links' );

        // Add support for custom backgrounds
        add_theme_support( 'custom-background', array(
            'default-color' => 'ffffff',
        ) );

        // Add support for post-thumbnails
        add_theme_support( 'post-thumbnails' );

        // Add support for post formats. To be styled in later release.
        add_theme_support( 'post-formats', array( 'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat' ) );

        // Load Jetpack related support if needed.
        if ( class_exists( 'Jetpack' ) )
            locate_template( '/inc/jetpack.php', true );

        //Loads components
        Waboot_ComponentsManager::init();
        Waboot_ComponentsManager::setupRegisteredComponents();
    }
endif;
add_action( 'after_setup_theme', 'waboot_setup' );

//Components hooks
locate_template( '/admin/waboot-components-hooks.php', true );

/**
 * Less compiling
 */
if(isset($_GET['compile']) && $_GET['compile'] == true){
    if ( current_user_can( 'manage_options' ) ) {
        //old method: add_action("waboot_head","waboot_compile_less");
        locate_template( '/inc/compiler/less-php/compiler.php', true );
        waboot_compile_less();
    }
}

/**
 * Less compiling (ajax)
 */
/*if( (is_child_theme() || CURRENT_ENV == ENV_DEV)  && LESS_LIVE_COMPILING){
    add_action('wp_ajax_waboot_needs_to_compile', 'checkCompile');
    add_action('wp_ajax_nopriv_waboot_needs_to_compile', 'checkCompile');

    add_action('wp_ajax_waboot_compile', 'compileLess');
    add_action('wp_ajax_nopriv_waboot_compile', 'compileLess');
}

function checkCompile(){
    require_once("inc/Waboot_Less_Compiler.php");

    $compile_sets = apply_filters('waboot_compile_sets',array());
    $waboot_less_compiler = new Waboot_Less_Compiler($compile_sets);
    echo $waboot_less_compiler->needs_to_compile("theme_frontend");
    die();
}

function compileLess(){
    require_once("inc/Waboot_Less_Compiler.php");

    $compile_sets = apply_filters('waboot_compile_sets',array());
    $waboot_less_compiler = new Waboot_Less_Compiler($compile_sets);
    echo $waboot_less_compiler->compile();
    die();
}*/

// WP Update Server
$WabootThemeUpdateChecker = new ThemeUpdateChecker(
    'waboot', //Theme slug. Usually the same as the name of its directory.
    'http://wpserver.wagahost.com/?action=get_metadata&slug=waboot' //Metadata URL.
);

/**
 * Waboot autoloader
 * @param $class
 * @since 0.1.4
 */
function waboot_autoloader($class) {
    switch($class){
        case "Waboot_Cache":
            locate_template('inc/compiler/less-php/Waboot_Cache.php',true);
            break;
        case "Waboot_Less_Compiler":
            locate_template('inc/compiler/less-php/Waboot_Less_Compiler.php',true);
            break;
        case "Less_Cache":
            locate_template('inc/compiler/less-php/vendor/Lessphp/Cache.php',true);
            break;
        case "Less_Parser":
            locate_template('inc/compiler/less-php/vendor/Lessphp/Less.php',true);
            break;
        case "lessc":
            locate_template('inc/compiler/less-php/vendor/Lessphp/lessc.inc.php',true);
            break;
        case "Less_Version":
            locate_template('inc/compiler/less-php/vendor/Lessphp/Version.php',true);
            break;
        case "BootstrapNavMenuWalker":
            locate_template('inc/vendor/BootstrapNavMenuWalker.php',true);
            break;
        case "ThemeUpdateChecker":
            require_once 'inc/theme-updates/theme-update-checker.php';
            break;
    }
}