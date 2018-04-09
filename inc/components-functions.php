<?php

namespace Waboot\functions\components;
use WBF\components\utils\Paths;
use WBF\modules\components\ComponentsManager;


/**
 * Get the API endpoint for Components listing
 *
 * @return string
 */
function get_api_list_endpoint(){
	return 'http://update.waboot.org/resource/list/components?basetheme=waboot';
}

/**
 * The the API endpoint for single component data
 *
 * @param $slug
 *
 * @return string
 */
function get_api_single_component_endpoint($slug){
	return 'http://update.waboot.org/resource/info/component/'.$slug.'/?basetheme=waboot';
}

/**
 * Request all available remote components
 *
 * @param string|null $endpoint
 *
 * @return array
 * @throws \Exception
 */
function request_components($endpoint = null){
	if(!isset($endpoint)){
		$endpoint = get_api_list_endpoint();
	}
	$remote_components_request = wp_remote_get($endpoint);

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
 * @param $slug
 * @param string|null $endpoint
 *
 * @return array
 * @throws \Exception
 */
function request_single_component($slug, $endpoint = null){
	if(!isset($endpoint)){
		$endpoint = get_api_single_component_endpoint($slug);
	}
	$remote_component_request = wp_remote_get($endpoint);

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
 * @param null $target_filename
 * @param int $timeout
 *
 * @return string|\WP_Error
 * @throws \Exception
 */
function download_component_package($package, $target_filename = null, $timeout = 300){
	if(!isset($target_filename)){
		$target_filename = basename( parse_url( $package, PHP_URL_PATH ) );
	}

	$tmpfname = wp_tempnam( $target_filename, get_tmp_download_directory() );

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
 * @param bool $delete_existing_directory
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
 * Install a remote component (do not check if the component is already available)
 *
 * @param $slug
 * @throws \Exception
 *
 * @return bool
 */
function install_remote_component($slug){
	$component = request_single_component($slug);

	if(!\is_array($component)){
		throw new \Exception('Unable to find the remote component: '.$slug);
	}

	//Download the component file:
	if(!isset($component['package'])){
		throw new \Exception('No package found for the component: '.$slug);
	}

	$download_file = download_component_package($component['package'],$component['slug'].'_'.$component['version']);

	if(is_wp_error($download_file)){
		throw new \Exception($download_file->get_error_message());
	}

	//Install the component:
	$unzipped = unzip_component_package($download_file,$slug);

	//Then delete the temp file
	unlink($download_file);

	//Finally, perform a new components detection
	ComponentsManager::detect_components();

	return $unzipped;
}