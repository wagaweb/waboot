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
}
add_action( 'wp_enqueue_scripts', 'waboot_theme_styles' );