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
			'package' => 'http://cdn.wagahost.net/components/waboot/header_classic.zip',
			'author' => 'WAGA'
		],
		[
			'slug' => 'component-b',
			'title' => 'Component b',
			'thumbnail' => 'http://via.placeholder.com/128x128',
			'description' => 'Component B Desc',
			'tags' => ['tag-a','tab-c'],
			'package' => 'http://cdn.wagahost.net/components/waboot/header_classic.zip',
			'author' => 'WAGA'
		],
		[
			'slug' => 'component-c',
			'title' => 'Component C',
			'thumbnail' => 'http://via.placeholder.com/128x128',
			'description' => 'Component C Desc',
			'tags' => ['tag-b','tab-c'],
			'package' => 'http://cdn.wagahost.net/components/waboot/header_classic.zip',
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
		'package' => 'http://cdn.wagahost.net/components/waboot/header_classic.zip',
		'author' => 'WAGA'
	];
	return $component;
}

/**
 * Creates and return the tmp download directory
 *
 * @throws \Exception
 */
function get_tmp_download_directory(){
	$download_location = WBF()->get_working_directory().'/tmp/components-downloads';
	if(!is_dir($download_location)){
		$r = wp_mkdir_p($download_location);
		if(!$r){
			throw new \Exception('Unable to create download folder');
		}
	}
	return $download_location.'/';
}

/**
 * Creates and return the current component directory
 *
 * @return string
 * @throws \Exception
 */
function get_current_components_directory(){
	$dir = get_stylesheet_directory().'/components';
	if(!is_dir($dir)){
		$r = wp_mkdir_p($dir);
		if(!$r){
			throw new \Exception('Unable to create components folder');
		}
	}
	return $dir.'/';
}

/**
 * Download a component package
 *
 * @param string $package the package url
 * @param int $timeout
 *
 * @return string|\WP_Error
 * @throws \Exception
 */
function download_component_package($package, $timeout = 300){
	$url_filename = basename( parse_url( $package, PHP_URL_PATH ) );

	$tmpfname = wp_tempnam( $url_filename, get_tmp_download_directory() );

	$response = wp_safe_remote_get($package,['timeout'=>$timeout,'stream'=>true,'filename'=>$tmpfname]);

	if(is_wp_error($response)){
		unlink($tmpfname);
		return $response;
	}

	return $tmpfname;
}

/**
 * @param $origin
 * @param $to
 *
 * @return boolean
 * @throws \Exception
 */
function unzip_component_package($origin,$to){
	if(class_exists('ZipArchive', false)){
		$zip = new \ZipArchive();
		if($zip->open($origin) === TRUE){
			$zip->extractTo($to);
			$zip->close();
			return true;
		}else{
			throw new \Exception('Unable to unzip component file');
		}
	}
	throw new \Exception('ZipArchive class not found');
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
	if(!isset($component['package'])){
		wp_send_json_error('No package found for the component: '.$slug);
	}

	try{
		$download_file = download_component_package($component['package']);

		if(is_wp_error($download_file)){
			wp_send_json_error($download_file->get_error_message());
		}

		//Install the component:
		$install_directory = get_current_components_directory();
		if(is_dir($install_directory.$slug)){
			wp_send_json_error('Component directory already exists');
		}

		unzip_component_package($download_file,$install_directory);

		//Then delete the temp file
		unlink($download_file);

		wp_send_json_success();
	}catch(\Exception $e){
		wp_send_json_error($e->getMessage());
	}
}

