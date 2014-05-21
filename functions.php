<?php

//rename_theme_options("waboot");

define("ENV_DEV",1);
define("ENV_PRODUCTION",2);
define("LESS_LIVE_COMPILING",true);

if(!defined("CURRENT_ENV")){
    define("CURRENT_ENV",ENV_DEV);
}

if ( ! function_exists( 'waboot_setup' ) ):
    function waboot_setup() {

        //Global Customization
        locate_template( '/inc/global_customizations.php', true );

        //Utility
        locate_template( '/inc/utility.php', true );

        // Custom template tags for this theme.
        locate_template( '/core/inc/template-tags.php', true );
        locate_template( '/inc/hooks.php', true );
        locate_template( '/inc/template-tags.php', true );

        // Register the navigation menus.
        locate_template( '/core/inc/menus.php', true );
        locate_template( '/core/inc/wp_bootstrap_navwalker.php', true );

        // Register sidebars
        locate_template( '/core/inc/sidebars.php', true );

        // Header image
        locate_template( '/inc/header-image.php', true );

        // Load behaviors extension
        locate_template( '/admin/behaviors.php', true );

        // Load theme options framework
        locate_template( '/inc/options-panel.php', true );

        // Customizer
        locate_template( '/core/inc/customizer.php', true );

        // Breadcrumbs
        if ( of_get_option( 'alienship_breadcrumbs',1) ) {
            locate_template( '/core/inc/breadcrumb-trail.php', true );
        }

        // Custom functions that act independently of the theme templates
        //locate_template( '/core/inc/tweaks.php', true ); //LostCore: tweaks utili integrati in global_customization

        // Email encoder
        locate_template( '/inc/email_encoder.php', true );

        // Load the CSS
        locate_template( '/inc/stylesheets.php', true );

        // Load scripts
        locate_template( '/inc/scripts.php', true );

        /**
         * Make theme available for translation
         * Translations can be filed in the /languages/ directory
         * If you're building a theme based on alienship, use a find and replace
         * to change 'alienship' to the name of your theme in all the template files
         */
        load_theme_textdomain( 'alienship', get_template_directory() . '/languages' );


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
            locate_template( '/core/inc/jetpack.php', true );
    }
endif;
add_action( 'after_setup_theme', 'waboot_setup' );

/**
 * Autocompile less if it is a child theme
 */
if( (is_child_theme() || CURRENT_ENV == ENV_DEV)  && LESS_LIVE_COMPILING){
    waboot_compile_less();
}

/**
 * Theme Options: allow "a", "embed" and "script" tags in theme options text boxes
 */
function optionscheck_change_sanitize() {
    remove_filter( 'of_sanitize_text', 'sanitize_text_field' );
    add_filter( 'of_sanitize_text', 'custom_sanitize_text' );
}
add_action( 'admin_init','optionscheck_change_sanitize', 100 );

function custom_sanitize_text( $input ) {
    global $allowedposttags;

    $custom_allowedtags["a"] = array(
        "href"   => array(),
        "target" => array(),
        "id"     => array(),
        "class"  => array()
    );

    $custom_allowedtags = array_merge( $custom_allowedtags, $allowedposttags );
    $output = wp_kses( $input, $custom_allowedtags );
    return $output;
}

/**
 * Theme Options: relocate options.php for a cleaner structure
 * @return array
 */
function waboot_options_framework_location_override() {
    return array("inc/options.php");
}
add_filter('options_framework_location','waboot_options_framework_location_override');

/*-----------------------------------------------------------------------------------*/
/* Start Custom Functions - Please refrain from editing this section */
/*-----------------------------------------------------------------------------------*/

// Register our sidebars and widgetized areas
function arphabet_widgets_init() {

	register_sidebar( array(
		'name' => 'Banner',
		'id' => 'banner',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="rounded">',
		'after_title' => '</h2>',
	) );
	register_sidebar( array(
		'name' => 'Content Bottom',
		'id' => 'contentbottom',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="rounded">',
		'after_title' => '</h2>',
	) );
	register_sidebar( array(
		'name' => 'Header Left',
		'id' => 'header-left',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="rounded">',
		'after_title' => '</h2>',
	) );
	register_sidebar( array(
		'name' => 'Header Right',
		'id' => 'header-right',
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h2 class="rounded">',
		'after_title' => '</h2>',
	) );
}
add_action( 'widgets_init', 'arphabet_widgets_init' );

// Add WP Better email support for gravity form
function change_notification_format( $notification, $form, $entry ) {
	// is_plugin_active is not availble on front end
	if( !is_admin() )
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	// does WP Better Emails exists and activated ?
	if( !is_plugin_active('wp-better-emails/wpbe.php') )
		return $notification;

	// change notification format to text from the default html
    $notification['message_format'] = "text";
	// disable auto formatting so you don't get double line breaks
	$notification['disableAutoformat'] = true;

    return $notification;
}
add_filter('gform_notification', 'change_notification_format', 10, 3);

function waboot_compile_less(){
    require_once("inc/vendor/Less.php");
    try{
        $theme = wp_get_theme()->stylesheet;
        $inputFile = get_stylesheet_directory()."/sources/less/{$theme}.less";
        $cachedir = get_stylesheet_directory()."/assets/css/cache";
        $outputDir = get_stylesheet_directory()."/assets/css";
        $outputFile = get_stylesheet_directory()."/assets/css/style.css";
        $parser_options = array(
            'cache_dir'         => $cachedir,
            'compress'          => false,
            'sourceMap'         => true,
            'sourceMapWriteTo'  => $outputDir.'/style.css.map',
            'sourceMapURL'      => get_stylesheet_directory_uri().'/assets/css/style.css.map',
        );
        $css_file_name = Less_Cache::Get(
            array(
                $inputFile => get_stylesheet_directory_uri(),
            ),
            $parser_options
        );
        $css = file_get_contents( $cachedir.'/'.$css_file_name );
        file_put_contents($outputFile, $css);
    }catch (exception $e) {
        echo "Less Compiler Fatal Error: " . $e->getMessage();
    }
}

/*function old_waboot_compile_less(){
    require_once("vendor/Less.php");
    require_once("vendor/lessc.inc.php");
    try{
        $inputFile = get_stylesheet_directory()."/sources/overrides/waboot.less";
        $outputFile = get_stylesheet_directory()."/assets/css/style.css";
        $less = new lessc;
        $less->setFormatter("compressed");

        $cache = get_option("less_cache");
        if(!$cache){
            $cache = $less->cachedCompile($inputFile);
            add_option("less_cache",$cache);
            file_put_contents($outputFile, $cache["compiled"]);
        }else{
            $last_updated = $cache["updated"];
            $cache = $less->cachedCompile($inputFile);
            if ($cache["updated"] > $last_updated) {
                file_put_contents($outputFile, $cache["compiled"]);
            }
        }
    }catch (exception $e) {
        echo "Less Compiler Fatal Error: " . $e->getMessage();
    }
}*/