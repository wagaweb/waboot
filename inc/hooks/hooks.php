<?php

namespace Waboot\hooks;

/**
 * Add header metas
 */
function add_header_metas(){
	get_template_part("templates/parts/meta");
}
add_action("waboot/head/start",__NAMESPACE__."add_header_meta");

/**
 * Adds apple touch init to the document head meta
 */
function add_apple_touch_icon(){
	?>
	<link rel="apple-touch-icon" href="<?php apply_filters("waboot/assets/apple-touch-icon-path","apple-touch-icon.png"); ?>">
	<?php
}
add_action("waboot/head/meta",__NAMESPACE__."add_apple_touch_icon");

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