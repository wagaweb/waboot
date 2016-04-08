<?php

namespace Waboot\hooks\init;

function setup() {
	//Make theme available for translation.
	load_theme_textdomain( 'waboot', get_template_directory() . '/languages' );

	// Switch default core markup for search form, comment form, and comments to output valid HTML5.
	add_theme_support( 'html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption') );

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// Add support for custom backgrounds
	add_theme_support( 'custom-background', array('default-color' => 'ffffff') );

	// Add support for post-thumbnails
	add_theme_support( 'post-thumbnails' );

	// Add support for post formats. To be styled in later release.
	add_theme_support( 'post-formats', array( 'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat' ) );
}
add_action('after_setup_theme', __NAMESPACE__."\\setup", 11);

/**
 * Register widget areas
 */
function register_widget_areas(){
	$areas = [
		'sidebar-1' => [
			'name' =>  __('Secondary Sidebar', 'waboot'),
			'description' => __( 'The main widget area displayed in the sidebar.', 'waboot' )
		],
		'sidebar-2' => [
			'name' => __('Secondary Sidebar', 'waboot'),
			'description' => __( 'The main widget area displayed in the sidebar.', 'waboot' )
		],
		'footer-1' => [
			'name' => __('Footer 1', 'waboot'),
			'description' => __('The footer widget area displayed after all content.', 'waboot' )
		],
		'footer-2' => [
			'name' => __('Footer 2', 'waboot'),
			'description' => __('The second footer widget area, displayed below the Footer widget area.', 'waboot' )
		],
		'footer-3' => [
			'name' => __('Footer 3', 'waboot'),
			'description' => __('The third footer widget area, displayed below the Footer widget area.', 'waboot' )
		],
		'footer-4' => [
			'name' => __('Footer 4', 'waboot'),
			'description' => __('The fourth footer widget area, displayed below the Footer widget area.', 'waboot' )
		],
		'topbar' => [
			'name' => __('Top Bar', 'waboot'),
		],
		'banner' => [
			'name' => __('Banner', 'waboot')
		],
		'contentbottom' => [
			'name' => __('Content Bottom')
		],
		'header-left' => [
			'name' => __('Header Left')
		],
		'header-right' => [
			'name' => __('Header Right')
		],
	];

	foreach($areas as $area_id => $area_args){
		$args = [
			'name' => $area_args['name'],
			'description' => isset($area_args['description']) ? $area_args['description'] : "",
			'id' => $area_id,
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h2 class="rounded">',
			'after_title' => '</h2>',
		];
		register_sidebar($args);
	}
}
add_action("widgets_init",__NAMESPACE__."\\register_widget_areas");

/**
 * Register the navigation menus. This theme uses wp_nav_menu() in three locations.
 */
function register_menus(){
	register_nav_menus([
		'top'           => __( 'Top Menu', 'waboot' ),
		'main'          => __( 'Main Menu', 'waboot' ),
		'bottom'        => __( 'Bottom Menu', 'waboot' )
	]);
}
add_action("after_setup_theme",__NAMESPACE__."\\register_menus",11);

/**
 * Rename the default label of admin menu
 *
 * @param $label
 *
 * @hooked 'wbf/admin_menu/label'
 *
 * @return string
 */
function set_wbf_admin_menu_label($label){
	return "Waboot";
}
add_filter("wbf/admin_menu/label",__NAMESPACE__."set_wbf_admin_menu_label");