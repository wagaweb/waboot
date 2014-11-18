<?php
/**
 * Register and enqueue the front end CSS
 *
 * @package Waboot
 * @since 0.1.0
 */

// Load frontend theme styles
function waboot_theme_styles() {
	$theme = wp_get_theme(); //get current theme settings
    /**
     * Here by default $theme->stylesheet is the name of the theme directory.
     * We pass that name into the "waboot_compiled_stylesheet_name" filter which change its value according to one compiled from less.
     * See /inc/hooks.php at waboot_set_compiled_stylesheet_name($name)
     */
    $compiled_stylesheet = apply_filters("waboot_compiled_stylesheet_name",wp_get_theme()->stylesheet);

	/* Load theme styles */
    wp_enqueue_style( 'font-awesome', waboot_locate_template_uri( 'assets/css/font-awesome.min.css' ), $theme['Version'], 'all' );
    wp_enqueue_style( 'main-style', waboot_locate_template_uri( "assets/css/{$compiled_stylesheet}.css" ), array( 'font-awesome' ), $theme['Version'], 'all' );
	wp_enqueue_style( 'core-style', get_stylesheet_uri(), array( 'font-awesome' ), $theme['Version'], 'all' ); //style.css

	//Enqueue theme-options custom style
	$customcss = waboot_of_custom_css();
	if ( $customcss ) {
		wp_enqueue_style( 'custom-style', $customcss, array(
				'font-awesome',
				'main-style',
				'core-style'
			), $theme['Version'], 'all' );
	}
}
add_action( 'wp_enqueue_scripts', 'waboot_theme_styles' );


/**
 * Apply custom stylesheet to admin panel
 *
 * @param $page
 * @since 0.1.0
 * @uses waboot_locate_template_uri()
 */
function waboot_admin_styles($page) {
	wp_enqueue_style( 'waboot-admin-style', waboot_locate_template_uri( 'admin/css/admin.css' ), array(), '1.0.0', 'all' );
	if ( $page == "waboot_page_options-framework" ) {
        $stylesheet = waboot_locate_template_uri( 'admin/css/waboot-optionsframework.css' );
        if($stylesheet != "")
            wp_enqueue_style( 'waboot-theme-options-style', $stylesheet, array('optionsframework'), '1.0.0', 'all' ); //Custom Theme Options CSS
    }
}
add_action( 'admin_enqueue_scripts', 'waboot_admin_styles' );

/**
 * Apply custom stylesheet to the wordpress visual editor.
 *
 * @since 0.1.0
 * @uses add_editor_style()
 * @uses get_stylesheet_uri()
 */
function waboot_editor_styles() {
    $theme_name = apply_filters("waboot_compiled_stylesheet_name",wp_get_theme()->stylesheet);

    add_editor_style( waboot_locate_template_uri( "assets/css/{$theme_name}.css" ) );
    add_editor_style( 'admin/css/tinymce.css' );
}
add_action( 'init', 'waboot_editor_styles' );

/**
 * Apply "post-type relative" custom stylesheet to visual editor
 * @since 0.1.0
 * @uses add_editor_style()
 * @uses get_post_type()
 */
function waboot_post_type_editor_styles(){
    global $post;
    if(isset($post->ID)){
        $post_type = get_post_type( $post->ID );
        $editor_style = 'tinymce-' . $post_type . '.css'; //Es: tinymce-post.css
        add_editor_style( "admin/css/".$editor_style );
    }
}
add_action( 'pre_get_posts', 'waboot_post_type_editor_styles' );

/**
 * Apply custom in-line styles in header
 */
function waboot_theme_options_header_styles(){
    ?>
    <style type="text/css">
        body {
            background-color: <?php echo of_get_option( 'waboot_body_bgcolor' ); ?> !important;
            background-image: url(<?php echo of_get_option( 'waboot_body_bgimage' ); ?>);
            background-repeat: <?php echo of_get_option( 'waboot_body_bgrepeat' ); ?>;
            background-position: <?php echo of_get_option( 'waboot_body_bgpos' ); ?>;
            background-attachment: <?php echo of_get_option( 'waboot_body_bgattach' ); ?>;
        }
        #topnav-wrapper {
            background-color: <?php echo of_get_option( 'waboot_topnav_bgcolor' ); ?>;
        }
        #header-wrapper {
            background-color: <?php echo of_get_option( 'waboot_header_bgcolor' ); ?>;
        }
        #banner-wrapper {
            background-color: <?php echo of_get_option( 'waboot_banner_bgcolor' ); ?>;
        }
        #content-wrapper {
            background-color: <?php echo of_get_option( 'waboot_content_bgcolor' ); ?>;
        }
        #contentbottom-wrapper {
            background-color: <?php echo of_get_option( 'waboot_bottom_bgcolor' ); ?>;
        }
        #footer-wrapper {
            background-color: <?php echo of_get_option( 'waboot_footer_bgcolor' ); ?>;
        }
        #page {
            background-color: <?php echo of_get_option( 'waboot_page_bgcolor' ); ?>;
        }
        .navbar.main-navigation .navbar-collapse {
            background-color: <?php echo of_get_option( 'waboot_navbar_bgcolor' ); ?>;
        }

    </style>
    <?php
}
add_action("waboot_head",'waboot_theme_options_header_styles');