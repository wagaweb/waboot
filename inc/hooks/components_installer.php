<?php

namespace Waboot\hooks\components_installer;

add_action('wp_ajax_get_available_components', __NAMESPACE__.'\\ajax_get_available_components');
add_action('wp_ajax_nopriv_get_available_components', __NAMESPACE__.'\\ajax_get_available_components');
add_action('wp_ajax_download_component', __NAMESPACE__.'\\ajax_download_component');
add_action('wp_ajax_nopriv_download_component', __NAMESPACE__.'\\ajax_download_component');

/**
 * Ajax callback to get alla available components.
 */
function ajax_get_available_components(){
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

	wp_send_json_success($components);
}

/**
 * Ajax callback to download a component.
 */
function ajax_download_component(){

}

