<?php

define("ENV_DEV",1);
define("ENV_PRODUCTION",2);
define("LESS_LIVE_COMPILING",true);

if(!defined("CURRENT_ENV")){
    define("CURRENT_ENV",ENV_DEV);
}

//Utility
locate_template( '/inc/utility.php', true );

if ( ! function_exists( 'waboot_setup' ) ):
    function waboot_setup() {

        //Global Customization
        locate_template( '/inc/global_customizations.php', true );

        // Custom template tags for this theme.
        locate_template( '/inc/hooks.php', true );
        locate_template( '/inc/template-tags.php', true );

        // Register the navigation menus.
        locate_template( '/inc/menus.php', true );
        locate_template( '/inc/vendor/BootstrapNavMenuWalker.php', true );
        locate_template( '/inc/vendor/wp_bootstrap_navwalker.php', true );
        locate_template( '/inc/waboot_bootstrap_navwalker.php', true );
        locate_template( '/inc/WabootNavMenuWalker.php', true );

        // Register sidebars
        locate_template( '/inc/widgets.php', true );

        // Header image
        locate_template( '/inc/custom-header.php', true );

        // Load behaviors extension
        locate_template( '/admin/behaviors.php', true );

        // Load theme options framework
        locate_template( '/admin/options-panel.php', true );

        // Customizer
        locate_template( '/inc/customizer.php', true );

        // Breadcrumbs
        if ( of_get_option( 'waboot_breadcrumbs',1) ) {
            locate_template( '/inc/vendor/breadcrumb-trail.php', true );
            locate_template( '/inc/waboot-breadcrumb-trail.php', true );
        }

        // Email encoder
        locate_template( '/inc/email_encoder.php', true );

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
    }
endif;
add_action( 'after_setup_theme', 'waboot_setup' );

/**
 * Autocompile less if it is a child theme
 */
if( (is_child_theme() || CURRENT_ENV == ENV_DEV)  && LESS_LIVE_COMPILING){
    add_action("waboot_head","waboot_compile_less");
    //waboot_compile_less();
}



