<?php
/**
 * Register and enqueue the front end CSS
 *
 * @package Waboot
 * @since 1.0.0
 */

// Load frontend theme styles
function waboot_theme_styles() {
	$waboot = wp_get_theme();

	// Load core Bootstrap CSS
	//wp_enqueue_style( 'bootstrap', waboot_locate_template_uri( 'assets/css/bootstrap.min.css' ), array(), $waboot['Version'], 'all' );
	/* Load theme styles */
    wp_enqueue_style( 'font-awesome', waboot_locate_template_uri( 'assets/css/font-awesome.min.css' ), $waboot['Version'], 'all' );
	wp_enqueue_style( 'waboot-style', get_stylesheet_uri(), array( 'font-awesome' ), $waboot['Version'], 'all' );
}
add_action( 'wp_enqueue_scripts', 'waboot_theme_styles' );


// Load admin styles
function waboot_admin_styles($page) {
	wp_enqueue_style( 'waboot-admin-style', waboot_locate_template_uri( 'admin/css/admin.css' ), array(), '1.0.0', 'all' );
    if($page == "appearance_page_options-framework"){
        $stylesheet = waboot_locate_template_uri( 'admin/css/waboot-optionsframework.css' );
        if($stylesheet != "")
            wp_enqueue_style( 'waboot-theme-options-style', $stylesheet, array('optionsframework'), '1.0.0', 'all' ); //Custom Theme Options CSS
    }
}
add_action( 'admin_enqueue_scripts', 'waboot_admin_styles' );

/**
 * Apply custom stylesheet to the visual editor.
 *
 * @since 1.0
 * @uses add_editor_style()
 * @uses get_stylesheet_uri()
 */
function waboot_editor_styles() {
    add_editor_style( get_stylesheet_uri() );
    //add_editor_style( 'css/bootstrap.min.css' ); LostCore: now bootstrap is in style.css
    add_editor_style( 'admin/css/tinymce.css' ); //Overwrite some bootstrap stylesheet
}
add_action( 'init', 'waboot_editor_styles' );

/**
 * Apply "post-type relative" custom stylesheet to visual editor
 * @since 1.0
 * @uses add_editor_style()
 * @uses get_post_type()
 */
function waboot_post_type_editor_styles(){
    global $post;
    $post_type = get_post_type( $post->ID );
    $editor_style = 'tinymce-' . $post_type . '.css'; //Es: tinymce-post.css
    add_editor_style( "admin/css/".$editor_style );
}
add_action( 'pre_get_posts', 'waboot_post_type_editor_styles' );

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
        #logo {
            text-align: <?php echo of_get_option( 'waboot_logo_align' ); ?>;
            float: <?php echo of_get_option( 'waboot_logo_align', 'left,right' ); ?>;
            <?php if ( of_get_option( 'waboot_float_navbar', 1 ) ) {?> display: inline-block; <?php } ?>
        }
        #page {
            background-color: <?php echo of_get_option( 'waboot_page_bgcolor' ); ?>;
        }
        #header-wrapper .navbar-collapse {
            background-color: <?php echo of_get_option( 'waboot_navbar_bgcolor' ); ?>;
        }

    </style>
    <?php
}
add_action("waboot_head",'waboot_theme_options_header_styles');