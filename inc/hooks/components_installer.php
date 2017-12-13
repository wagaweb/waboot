<?php

namespace Waboot\hooks\components_installer;

add_action('wp_ajax_get_available_components', __NAMESPACE__.'\\ajax_get_available_components');
add_action('wp_ajax_nopriv_get_available_components', __NAMESPACE__.'\\ajax_get_available_components');
add_action('wp_ajax_install_remote_component', __NAMESPACE__.'\\ajax_install_remote_component');
add_action('wp_ajax_nopriv_install_remote_component', __NAMESPACE__.'\\ajax_install_remote_component');

/**
 * Request all available remote components
 *
 * @return array
 */
function request_components(){
	$components = [
		[
			'slug' => 'component-a',
			'title' => 'Component a',
			'thumbnail' => 'http://via.placeholder.com/128x128',
			'description' => 'Component A Desc',
			'tags' => ['tag-a','tab-b'],
			'download_url' => '#',
			'author' => 'WAGA'
		],
		[
			'slug' => 'component-b',
			'title' => 'Component b',
			'thumbnail' => 'http://via.placeholder.com/128x128',
			'description' => 'Component B Desc',
			'tags' => ['tag-a','tab-c'],
			'download_url' => '#',
			'author' => 'WAGA'
		],
		[
			'slug' => 'component-c',
			'title' => 'Component C',
			'thumbnail' => 'http://via.placeholder.com/128x128',
			'description' => 'Component C Desc',
			'tags' => ['tag-b','tab-c'],
			'download_url' => '#',
			'author' => 'WAGA'
		]
	];
	return $components;
}

/**
 * Request single remote component data
 *
 * @return array
 */
function request_single_component($slug){
	$component = [
		'slug' => 'component-a',
		'title' => 'Component a',
		'thumbnail' => 'http://via.placeholder.com/128x128',
		'description' => 'Component A Desc',
		'tags' => ['tag-a','tab-b'],
		'download_url' => '#',
		'author' => 'WAGA'
	];
	return $component;
}

/**
 * Ajax callback to get alla available components.
 */
function ajax_get_available_components(){
	if(!defined('DOING_AJAX') || !DOING_AJAX){
		return;
	}

	$components = request_components();
	wp_send_json_success($components);
}

/**
 * Ajax callback to download a component.
 */
function ajax_install_remote_component(){
	if(!defined('DOING_AJAX') || !DOING_AJAX){
		return;
	}

	if(!isset($_POST['slug'])){
		wp_send_json_error('No slug provided');
	}

	$slug = sanitize_text_field($_POST['slug']);
	$component = request_single_component($slug);

	if(!is_array($component)){
		wp_send_json_error('Unable to get info of component: '.$slug);
	}

	//Download the component file:
	//...

	//Install the component:
	//...
}

