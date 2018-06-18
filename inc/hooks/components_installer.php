<?php

namespace Waboot\hooks\components_installer;

use function Waboot\functions\components\request_components;
use function Waboot\functions\components\request_single_component;
use function Waboot\functions\components\get_tmp_download_directory;
use function Waboot\functions\components\get_current_components_directory;
use function Waboot\functions\components\download_component_package;
use function Waboot\functions\components\unzip_component_package;
use WBF\components\utils\Paths;
use WBF\modules\components\ComponentsManager;

add_action('wp_ajax_get_available_components', __NAMESPACE__ . '\\ajax_get_available_components');
add_action('wp_ajax_nopriv_get_available_components', __NAMESPACE__.'\\ajax_get_available_components');

add_action('wp_ajax_install_remote_component', __NAMESPACE__.'\\ajax_install_remote_component');
add_action('wp_ajax_nopriv_install_remote_component', __NAMESPACE__.'\\ajax_install_remote_component');

add_action('wp_ajax_activate_component_from_installer', __NAMESPACE__.'\\ajax_activate_component_from_installer');
add_action('wp_ajax_nopriv_activate_component_from_installer', __NAMESPACE__.'\\ajax_activate_component_from_installer');

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

	try{
		if(!isset($_POST['slug'])){
			throw new \Exception('No slug provided');
		}

		$slug = sanitize_text_field($_POST['slug']);
		$component = request_single_component($slug);

		if(!is_array($component)){
			throw new \Exception('Unable to get info of component: '.$slug);
		}

		//Download the component file:
		if(!isset($component['package'])){
			throw new \Exception('No package found for the component: '.$slug);
		}

		$download_file = download_component_package($component['package']);

		if(is_wp_error($download_file)){
			throw new \Exception($download_file->get_error_message());
		}

		//Install the component:
		unzip_component_package($download_file,$slug,true);

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

	try{
		if(!isset($_POST['slug'])){
			throw new \Exception('No slug provided');
		}

		$slug = sanitize_text_field($_POST['slug']);

		ComponentsManager::ensure_enabled($slug);
		wp_send_json_success();
	}catch(\Exception $e){
		wp_send_json_error($e->getMessage());
	}
}

