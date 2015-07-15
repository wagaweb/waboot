<?php
/**
 * Register and enqueue the front end and back end CSS
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
    $compiled_stylesheet = waboot_get_compiled_stylesheet_name();

	/* Load theme styles */
    wp_enqueue_style( 'font-awesome', wbf_locate_template_uri( 'assets/css/font-awesome.min.css' ), $theme['Version'], 'all' );
    wp_enqueue_style( 'main-style', wbf_locate_template_uri( "assets/css/{$compiled_stylesheet}.css" ), array( 'font-awesome' ), $theme['Version'], 'all' );
	wp_enqueue_style( 'core-style', get_stylesheet_uri(), array( 'font-awesome' ), $theme['Version'], 'all' ); //style.css
}
add_action( 'wp_enqueue_scripts', 'waboot_theme_styles' );

// Load backend styles
function waboot_admin_styles(){
	wp_enqueue_style( 'main-admin-style', wbf_locate_template_uri( "assets/css/admin.css" ));
}
add_action( 'admin_enqueue_scripts', 'waboot_admin_styles' );

/**
 * Apply custom stylesheet to the wordpress visual editor.
 *
 * @since 0.1.0
 * @uses add_editor_style()
 * @uses waboot_get_compiled_stylesheet_name()
 */
function waboot_editor_styles() {
	$theme_name = waboot_get_compiled_stylesheet_name();
	add_editor_style(wbf_locate_template_uri("assets/css/{$theme_name}.css"));
}
add_action('init', 'waboot_editor_styles');

/**
 * Apply "post-type relative" custom stylesheet to visual editor
 * @since 0.1.0
 * @uses add_editor_style()
 * @uses get_post_type()
 */
function waboot_post_type_editor_styles() {
	global $post;
	if (isset($post->ID)) {
		$post_type = get_post_type($post->ID);
		$editor_style = 'tinymce-' . $post_type . '.css'; //Es: tinymce-post.css
		if(function_exists("wbf_locate_template_uri")) :
			$style_uri = wbf_locate_template_uri("assets/css/{$editor_style}");
			if(!empty($style_uri)){
				add_editor_style(wbf_locate_template_uri("assets/css/{$editor_style}"));
			}
		endif;
	}
}
add_action('pre_get_posts', 'waboot_post_type_editor_styles');