<?php

/*
 * Loads text domain
 */
add_action("after_setup_theme", function(){
	load_child_theme_textdomain( 'waboot-child', get_stylesheet_directory() . '/languages' ); //Make theme available for translation
});

/**
 * Enqueue scripts and styles
 */
add_action("wp_enqueue_scripts", function(){
	if(!class_exists('\WBF\components\assets\AssetsManager')) return;
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
		//Decomment following lines if you build your assets with our gulpfile:
		/*'theme-style' => [
			'uri' => get_stylesheet_directory_uri()."/assets/dist/css/main.min.css",
			'path' => get_stylesheet_directory()."/assets/dist/css/main.min.css",
			'type' => 'css'
		],*/
		/*'theme-scripts' => [
			'uri' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? get_stylesheet_directory_uri()."/assets/dist/js/main.pkg.js" : get_stylesheet_directory_uri()."/assets/dist/js/main.min.js",
			'path' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? get_stylesheet_directory()."/assets/dist/js/main.pkg.js" : get_stylesheet_directory()."/assets/dist/js/main.min.js",
			'type' => 'js',
		    'deps' => ['jquery'],
		],*/
        //Decomment following lines if otherwise:
		/*'snippets-script' => [
			'uri' => get_stylesheet_directory_uri()."/assets/src/js/snippets.js",
			'path' => get_stylesheet_directory()."/assets/src/js/snippets.js",
			'type' => 'js'
		]*/
	];

	$am = new \WBF\components\assets\AssetsManager($assets);
	$am->enqueue();
});

//Loads dependencies
$theme_includes = [
	//Put here the dependencies files
	//@example: 'inc/theme_hooks.php'
];
foreach($theme_includes as $file){
	if (!$filepath = locate_template($file)) {
		trigger_error(sprintf('Error locating %s for inclusion', $file), E_USER_ERROR);
	}
	require_once $filepath;
}
