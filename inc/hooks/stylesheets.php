<?php

namespace Waboot\hooks\styles;

/**
 * Loads bootstrap
 */
function register_bootstrap(){
	$file = get_template_directory()."/assets/dist/css/bootstrap.min.css";
	if(is_readable($file)){
		$version = filemtime($file);
		wp_enqueue_style('bootstrap-css', get_template_directory_uri()."/assets/dist/css/bootstrap.min.css", [], $version, 'all' );
	}
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__.'\\register_bootstrap' );

/**
 * Loads font-awesome
 */
function register_fa(){
	$file = get_template_directory()."/assets/dist/css/font-awesome.min.css";
	if(is_readable($file)){
		$version = filemtime($file);
		wp_enqueue_style('font-awesome', get_template_directory_uri()."/assets/dist/css/font-awesome.min.css", [], $version, 'all' );
	}
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__.'\\register_fa' );

/**
 * Loads frontend styles
 */
function theme_styles() {
	$file = get_template_directory()."/assets/dist/css/waboot.min.css";
	if(is_readable($file)){
		$version = filemtime($file);
		wp_register_style('waboot-style', get_template_directory_uri()."/assets/dist/css/waboot.min.css", ['bootstrap-css'], $version, 'all' );
	}
	wp_enqueue_style('core-style', get_stylesheet_uri(), ['waboot-style'], false, 'all' ); //enqueue style.css
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__.'\\theme_styles' );

/**
 * Loads backend styles
 */
function admin_styles(){
	$file = get_template_directory()."/assets/dist/css/waboot-admin.min.css";
	if(is_readable($file)){
		$version = filemtime($file);
		wp_register_style('waboot-admin-style', get_template_directory_uri()."/assets/dist/css/waboot-admin.min.css", [], $version, 'all' );
		wp_enqueue_style('waboot-admin-style');
	}
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__.'\\admin_styles' );

/**
 * Apply custom stylesheet to the wordpress visual editor.
 *
 * @uses add_editor_style()
 */
function editor_style() {
	add_editor_style(get_template_directory_uri()."/assets/dist/css/waboot-admin-tinymce.min.css");
}
add_action('init', __NAMESPACE__.'\\editor_style');