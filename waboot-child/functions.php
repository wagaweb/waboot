<?php

/**
 * Theme init function. PLEASE EDIT "mytheme" SUFFIX both here and in add_action below.
 */
add_action("after_setup_theme", function(){
	load_child_theme_textdomain( 'waboot-child', get_stylesheet_directory() . '/languages' ); //Make theme available for translation
});

/**
 * Enqueue scripts and styles
 */
add_action("wp_enqueue_scripts", function(){
	$theme = wp_get_theme();
	$assets = [
		'wp-style' => [
			'uri' => get_stylesheet_directory_uri()."/style.css", //A valid uri
			'path' => get_stylesheet_directory()."/style.css", //A valid path
			'version' => false, //If FALSE, the filemtime will be used (if path is set)
			'deps' => [], //Dependencies
			'i10n' => [], //the Localication array for wp_localize_script
			'type' => 'css', //js or css
			'enqueue_callback' => false, //A valid callable that must be return true or false
			'in_footer' => false, //Used for scripts
			'enqueue' => true //If FALSE the script\css will only be registered
		],
		//Decomment following lines if you use the assets:
		/*'theme-style' => [
			'uri' => get_stylesheet_directory_uri()."/assets/dist/css/main.min.css",
			'path' => get_stylesheet_directory()."/assets/dist/css/main.min.css",
			'type' => 'css'
		],*/
		/*'theme-scripts' => [
			'uri' => get_stylesheet_directory_uri()."/assets/dist/js/main.min.js",
			'path' => get_stylesheet_directory()."/assets/dist/js/main.min.js",
			'type' => 'js'
		],*/
		/*'snippets-script' => [
			'uri' => get_stylesheet_directory_uri()."/assets/src/js/snippets.js",
			'path' => get_stylesheet_directory()."/assets/src/js/snippets.js",
			'type' => 'js'
		]*/
	];

	$am = new \WBF\components\assets\AssetsManager($assets);
	$am->enqueue();
});