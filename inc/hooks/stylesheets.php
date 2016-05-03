<?php

namespace Waboot\hooks\styles;

/**
 * Loads font-awesome
 */
function register_fa(){
	$file = get_stylesheet_directory()."/assets/dist/css/font-awesome.min.css";
	if(is_readable($file)){
		$version = filemtime($file);
		wp_enqueue_style('font-awesome', get_stylesheet_directory_uri()."/assets/dist/css/font-awesome.min.css", [], $version, 'all' );
	}
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__.'\\register_fa' );

/**
 * Loads frontend styles
 */
function theme_styles() {
	//Main style
	$file = get_stylesheet_directory()."/assets/dist/css/waboot.css";
	if(is_readable($file)){
		$version = filemtime($file);
		wp_register_style('waboot-style', get_stylesheet_directory_uri()."/assets/dist/css/waboot.css", [], $version, 'all' ); //waboot.css
	}
	wp_enqueue_style('core-style', get_stylesheet_uri(), ['waboot-style'], false, 'all' ); //style.css
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__.'\\theme_styles' );