<?php

/*------------------------------------------------------------------------------------------*/
/* Start Custom Functions - Please edit this section only if you know what you'are doing :) */
/*------------------------------------------------------------------------------------------*/

/**
 * Theme init function. PLEASE EDIT "mytheme" SUFFIX both here and in add_action below.
 */
function mytheme_init(){
	/**
	 * Make theme available for translation
	 * Translations can be filed in the /languages/ directory
	 * If you're building a theme based on waboot, use a find and replace
	 * to change 'waboot' to the name of your theme in all the template files
	 */
	load_child_theme_textdomain( 'mytheme', get_stylesheet_directory() . '/languages' );
}
add_action("after_setup_theme","mytheme_init");

/*
 * BACKWARD COMPATIBILITY:
 * For themes from waboot < 0.15.0 and wbf < 0.13.12, un-comment the following functions:
 */

/*add_filter("wbft/compiler/output/directory",function($dir){
	$base_dir = get_stylesheet_directory()."/assets/css";
	return $base_dir;
});

add_filter("wbft/compiler/output/uri",function($uri){
	$base_uri = get_stylesheet_directory_uri()."/assets/css";
	return $base_uri;
});*/

/**
 * Override of waboot function for older themes
 */
/*function init_style_compiler(){
	$theme = waboot_get_compiled_stylesheet_name();
	$inputFileName = is_child_theme() ? "waboot-child" : "waboot";
	$output_dir = waboot_get_compiled_stylesheet_directory();
	$output_uri = waboot_get_compiled_stylesheet_uri();

	WBF::set_styles_compiler([
		"sets" => [
			"theme_frontend" => [
				"input" => get_stylesheet_directory()."/sources/less/{$inputFileName}.less",
				"output" => $output_dir."/{$theme}.css",
				"map" => $output_dir."/{$theme}.css.map",
				"map_url" => $output_uri."/{$theme}.css.map",
				"cache" => get_stylesheet_directory()."/assets/cache",
				"import_url" => get_stylesheet_directory_uri(),
				"primary" => true
			]
		],
		"sources_path" => get_stylesheet_directory()."/sources/less/"
	]);

	//Run a compilation if the styles file is not present
	global $wbf_styles_compiler;
	$sets = $wbf_styles_compiler->get_compile_sets();
	if(!is_file($sets['theme_frontend']['output'])){
		$wbf_styles_compiler->compile();
	}
}*/