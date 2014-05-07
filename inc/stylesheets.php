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
	wp_enqueue_style( 'bootstrap', alienship_locate_template_uri( 'assets/css/bootstrap.min.css' ), array(), $waboot['Version'], 'all' );
	/* Load theme styles */
    wp_enqueue_style( 'font-awesome', alienship_locate_template_uri( 'assets/css/font-awesome.min.css' ), array( 'bootstrap' ), $waboot['Version'], 'all' );
	wp_enqueue_style( 'waboot-style', get_stylesheet_uri(), array( 'bootstrap','font-awesome' ), $waboot['Version'], 'all' );
}
add_action( 'wp_enqueue_scripts', 'waboot_theme_styles' );


// Load admin styles
function waboot_admin_styles($page) {
	wp_enqueue_style( 'alienship-admin-style', alienship_locate_template_uri( 'admin/css/admin.css' ), array(), '1.0.0', 'all' );
    if($page == "appearance_page_options-framework"){
        $stylesheet = alienship_locate_template_uri( 'admin/css/waboot-optionsframework.css' );
        if($stylesheet != "")
            wp_enqueue_style( 'waboot-theme-options-style', $stylesheet, array('optionsframework'), '1.0.0', 'all' );
    }
}
add_action( 'admin_enqueue_scripts', 'waboot_admin_styles' );