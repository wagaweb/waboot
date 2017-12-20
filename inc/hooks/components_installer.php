<?php

namespace Waboot\hooks\components_installer;

use WBF\components\utils\Paths;
use WBF\modules\components\ComponentsManager;

add_action('wp_ajax_get_available_components', __NAMESPACE__ . '\\ajax_get_available_components');
add_action('wp_ajax_nopriv_get_available_components', __NAMESPACE__.'\\ajax_get_available_components');

add_action('wp_ajax_install_remote_component', __NAMESPACE__.'\\ajax_install_remote_component');
add_action('wp_ajax_nopriv_install_remote_component', __NAMESPACE__.'\\ajax_install_remote_component');

add_action('wp_ajax_activate_component_from_installer', __NAMESPACE__.'\\ajax_activate_component_from_installer');
add_action('wp_ajax_nopriv_activate_component_from_installer', __NAMESPACE__.'\\ajax_activate_component_from_installer');

/**
 * Request all available remote components
 *
 * @return array
 * @throws \Exception
 */
function request_components(){
	$remote_components_request = wp_remote_get('http://update.waboot.org/resource/list/components?basetheme=waboot');

	if(is_wp_error($remote_components_request)){
		throw new \Exception($remote_components_request->get_error_message());
	}

	if($remote_components_request['response']['code'] !== 200){
		throw new \Exception('Unable to retrieve remote components');
	}

	$remote_components = json_decode($remote_components_request['body'],true);

	//Set the current status (0 = not installed, 1 = installed, 2 = active):
	foreach ($remote_components as $k => $component){
		if(ComponentsManager::is_active($component['slug'])){
			$remote_components[$k]['status'] = 2;
		}elseif(ComponentsManager::is_present($component['slug'])){
			$remote_components[$k]['status'] = 1;
		}else{
			$remote_components[$k]['status'] = 0;
		}
	}

	return $remote_components;
}

/**
 * Request single remote component data
 *
 * @return array
 * @throws \Exception
 */
function request_single_component($slug){
	$remote_component_request = wp_remote_get('http://update.waboot.org/resource/info/component/'.$slug.'/?basetheme=waboot');

	if(is_wp_error($remote_component_request)){
		throw new \Exception($remote_component_request->get_error_message());
	}

	if($remote_component_request['response']['code'] !== 200){
		throw new \Exception('Unable to retrieve remote component');
	}

	$remote_component = json_decode($remote_component_request['body'],true);

	return $remote_component;
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
 * Unzip the component package to che components directory
 *
 * @param $origin
 * @param $slug
 *
 * @return boolean
 * @throws \Exception
 */
function unzip_component_package($origin,$slug,$delete_existing_directory = false){
	if(class_exists('ZipArchive', false)){
		$base_install_directory = get_current_components_directory();
		$install_directory = rtrim($base_install_directory,'/').'/'.$slug;
		if(is_dir($install_directory)){
			if(!$delete_existing_directory){
				throw new \Exception('Component directory already exists');
			}else{
				Paths::deltree($install_directory);
				if(is_dir($install_directory)){
					throw new \Exception('Unable to remove already existing component directory');
				}
			}
		}

		$zip = new \ZipArchive();
		if($zip->open($origin) === TRUE){
			$zip->extractTo($base_install_directory);
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

	try{
		$components = request_components();
		wp_send_json_success($components);
	}catch (\Exception $e){
		wp_send_json_error($e->getMessage());
	}
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
			wp_send_json_error($download_file);
		}

		//Install the component:
		unzip_component_package($download_file,$slug);

		//Then delete the temp file
		unlink($download_file);

		wp_send_json_success();
	}catch(\Exception $e){
		wp_send_json_error($e->getMessage());
	}
}

/**
 * Ajax callback to activate a component.
 */
function ajax_activate_component_from_installer(){
	if(!defined('DOING_AJAX') || !DOING_AJAX){
		return;
	}

	if(!isset($_POST['slug'])){
		wp_send_json_error('No slug provided');
	}

	$slug = sanitize_text_field($_POST['slug']);

	try{
		ComponentsManager::ensure_enabled($slug);
		wp_send_json_success();
	}catch(\Exception $e){
		wp_send_json_error($e->getMessage());
	}
}

