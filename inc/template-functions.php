<?php

namespace Waboot\functions;

/**
 * Wrapper for \WBF\modules\options\of_get_option
 *
 * @param $name
 * @param bool $default
 *
 * @return bool|mixed
 */
function get_option($name, $default = false){
	if(class_exists("WBF")){
		return \WBF\modules\options\of_get_option($name,$default);
	}else{
		return $default;
	}
}

/**
 * Checks if at least one widget area with $prefix is active (eg: footer-1, footer-2, footer-3...)
 *
 * @param $prefix
 *
 * @param int $limit (default: 4)
 *
 * @return bool
 */
function count_widgets_in_area($prefix,$limit = 4){
	$count = 0;
	for($i = 1; $i <= $limit; $i++) {
		if(is_active_sidebar($prefix . "-" . $i)) {
			$count++;
		}
	}
	return $count;
}

/**
 * Prints out a waboot-type widget area
 *
 * @param $prefix
 * @param int $limit
 */
function print_widgets_in_area($prefix,$limit = 4){
	$count = count_widgets_in_area($prefix,$limit);
	if($count === 0) return;
}

/**
 * Get the correct CSS class to align $count containers
 *
 * @param int $count
 *
 * @return string
 *
 */
function get_grid_class_for_alignment($count = 4){
	$class = '';
	switch($count ) {
		case '1':
			$class = ' col-sm-12';
			break;
		case '2':
			$class = ' col-sm-6';
			break;
		case '3':
			$class = ' col-sm-4';
			break;
		case '4':
			$class = ' col-sm-3';
			break;
	}

	return $class;
}
function get_widget_areas(){
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

	return $areas;
}