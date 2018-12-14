<?php

namespace Waboot\hooks\styles;
use function Waboot\functions\get_theme_options_css_dest_path;
use function Waboot\functions\get_theme_options_css_dest_uri;
use function Waboot\functions\wbf_exists;
use WBF\components\assets\AssetsManager;

/**
 * @hooked 'wp_enqueue_scripts'
 * @throws \Exception
 */
function waboot_style(){
	if(defined('WABOOT_EXCLUDE_STYLES') && WABOOT_EXCLUDE_STYLES) {
		return;
	}

	$loadWabootStylesOptions = \Waboot\functions\get_option('load_waboot_styles');
	if(!$loadWabootStylesOptions || $loadWabootStylesOptions !== '1'){
		return;
	}

	if( wbf_exists() ){
		$assets['waboot-style'] = [
			'uri' => get_template_directory_uri() . '/assets/dist/css/waboot.min.css',
			'path' => get_template_directory() . '/assets/dist/css/waboot.min.css',
			'type' => 'css'
		];
		$am = new AssetsManager($assets);
		$am->enqueue();
	}else{
		wp_enqueue_style('waboot-style', get_template_directory_uri() . '/assets/dist/css/waboot.min.css' );
	}
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__.'\\waboot_style', 8 );

/**
 * @hooked 'wp_enqueue_scripts'
 * @throws \Exception
 */
function theme_options_style(){
	//Theme options style
	$file = get_theme_options_css_dest_path();
	if(is_readable($file)){
		$assets['waboot-theme-options-style'] = [
			'uri' => get_theme_options_css_dest_uri(),
			'path' => $file,
			'type' => 'css'
		];
		$am = new AssetsManager($assets);
		$am->enqueue();
	}
}
if( wbf_exists() ){
	add_action( 'wp_enqueue_scripts', __NAMESPACE__.'\\theme_options_style', 9 );
}

/**
 * Loads frontend styles
 */
function theme_styles(){
	//Common styles
	$assets = [
		'fontawesome-4' => [
			'uri' => get_template_directory_uri() . '/assets/dist/css/font-awesome-4.7.0.min.css',
			'path' => get_template_directory() . '/assets/dist/css/font-awesome-4.7.0.min.css',
			'type' => 'css',
			'enqueue' => \Waboot\functions\get_option('fa_version') === 'legacy'
		],
		'fontawesome-regular' => [
			'uri' => get_template_directory_uri() . '/assets/dist/css/fa5/regular.min.css',
			'path' => get_template_directory() . '/assets/dist/css/fa5/regular.min.css',
			'type' => 'css',
			'enqueue' => \Waboot\functions\get_option('fa_version') === 'latest'
		],
		'fontawesome-solid' => [
			'uri' => get_template_directory_uri() . '/assets/dist/css/fa5/solid.min.css',
			'path' => get_template_directory() . '/assets/dist/css/fa5/solid.min.css',
			'type' => 'css',
			'enqueue' => \Waboot\functions\get_option('fa_version') === 'latest'
		],
		'fontawesome-brands' => [
			'uri' => get_template_directory_uri() . '/assets/dist/css/fa5/brands.min.css',
			'path' => get_template_directory() . '/assets/dist/css/fa5/brands.min.css',
			'type' => 'css',
			'enqueue' => \Waboot\functions\get_option('fa_version') === 'latest'
		],
		'fontawesome' => [
			'uri' => get_template_directory_uri() . '/assets/dist/css/fa5/fontawesome.min.css',
			'path' => get_template_directory() . '/assets/dist/css/fa5/fontawesome.min.css',
			'type' => 'css',
			'deps' => ['fontawesome-regular','fontawesome-solid','fontawesome-brands'],
			'enqueue' => \Waboot\functions\get_option('fa_version') === 'latest'
		]
	];
	$am = new AssetsManager($assets);
	$am->enqueue();
}
if( wbf_exists() ) {
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\theme_styles' );
}

/**
 * Loads backend styles
 */
function admin_styles(){
	if(class_exists( AssetsManager::class )){
		$assets = [
			'waboot-admin-style' => [
				'uri' => get_template_directory_uri() . '/assets/dist/css/waboot-admin.min.css',
				'path' => get_template_directory() . '/assets/dist/css/waboot-admin.min.css',
				'type' => 'css'
			]
		];
		$am = new AssetsManager($assets);
		$am->enqueue();
	}else{
		wp_enqueue_style('waboot-admin-style', get_template_directory_uri() . '/assets/dist/css/waboot-admin.min.css' );
	}
}
add_action( 'admin_enqueue_scripts', __NAMESPACE__.'\\admin_styles', 100 );

/**
 * Apply custom stylesheet to the wordpress visual editor(s).
 *
 * @uses add_editor_style()
 */
function editor_style() {
	add_editor_style( get_template_directory_uri() . '/assets/dist/css/waboot-admin-tinymce.min.css' );
	add_editor_style( get_template_directory_uri() . '/assets/dist/css/waboot-admin-gutenberg.min.css' );
}
add_action('init', __NAMESPACE__.'\\editor_style');