<?php

namespace Waboot\hooks\styles;
use WBF\components\assets\AssetsManager;

/**
 * Loads frontend styles
 */
function theme_styles(){
	//Common styles
	$assets = [
		'font-awesome' => [
			'uri' => get_template_directory_uri()."/assets/dist/css/font-awesome.min.css",
			'path' => get_template_directory()."/assets/dist/css/font-awesome.min.css",
			'type' => 'css'
		]
	];
	//Waboot style version
	if(defined('WABOOT_BOOTSTRAP_CLASSIC_STYLE') && WABOOT_BOOTSTRAP_CLASSIC_STYLE){
		$assets['waboot-style'] = [
			'uri' => get_template_directory_uri()."/assets/dist/css/waboot.min.css",
			'path' => get_template_directory()."/assets/dist/css/waboot.min.css",
			'type' => 'css'
		];
	}else{
		$assets['waboot-style'] = [
			'uri' => get_template_directory_uri()."/assets/dist/css/waboot-classic.min.css",
			'path' => get_template_directory()."/assets/dist/css/waboot-classic.min.css",
			'type' => 'css'
		];
	}
	//Theme options style
	$file = WBF()->resources->get_working_directory()."/theme-options.css";
	if(is_readable($file)){
		$assets['waboot-theme-options-style'] = [
			'uri' => WBF()->resources->get_working_directory_uri()."/theme-options.css",
			'path' => WBF()->resources->get_working_directory()."/theme-options.css",
			'type' => 'css'
		];
	}

	$am = new AssetsManager($assets);
	$am->enqueue();
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__.'\\theme_styles' );

/**
 * Loads backend styles
 */
function admin_styles(){
	$assets = [
		'waboot-admin-style' => [
			'uri' =>  get_template_directory_uri()."/assets/dist/css/waboot-admin.min.css",
			'path' => get_template_directory()."/assets/dist/css/waboot-admin.min.css",
			'type' => 'css'
		]
	];
	$am = new AssetsManager($assets);
	$am->enqueue();
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