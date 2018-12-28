<?php

namespace Waboot\functions\components;
use WBF\components\utils\Paths;
use WBF\modules\components\Component;
use WBF\modules\components\ComponentsManager;
use function WBF\modules\components\get_child_components_directory;
use function WBF\modules\components\get_root_components_directory;


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
	static $remote_components;

	if(isset($remote_components[$slug]) && \is_array($remote_components[$slug])) return $remote_components[$slug];

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

	$remote_components[$slug] = $remote_component;

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
	$dir = is_child_theme()? get_child_components_directory() : get_root_components_directory();
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
 * @uses request_single_component()
 * @uses download_component_package()
 * @uses unzip_component_package()
 *
 * @param $slug
 * @param bool $overwrite_existing
 *
 * @return bool
 * @throws \Exception
 */
function install_remote_component($slug, $overwrite_existing = false){
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
	$unzipped = unzip_component_package($download_file,$slug,$overwrite_existing);
	//Then delete the temp file
	unlink($download_file);
	//Finally, perform a new components detection
	//ComponentsManager::detect_components();
	return $unzipped;
}

/**
 * Get the components to update
 *
 * @return array
 */
function get_components_to_update(){
	$components = ComponentsManager::getAllComponents();
	$components_to_update = [];
	foreach ($components as $component){
		if(!$component instanceof Component) continue;
		$pkg = \get_transient('waboot_component_'.$component->name.'_updated_package');
		if(\is_array($pkg) && !empty($pkg)){
			$components_to_update[$component->name] = $pkg;
			$components_to_update[$component->name]['current_version'] = $component->get_version();
		}
	}
	return $components_to_update;
}

/**
 * Check if a component has an update
 *
 * @param Component $component
 *
 * @throws \Exception
 *
 * @return bool;
 */
function has_update($component){
	$update_uri = get_update_uri($component);
	$has_update = false;

	if ( $update_uri !== '' ) {
		try {
			$data = request_single_component( $component->name, $update_uri );
			if ( isset( $data['version'] ) ) {
				$current_version = $component->get_version();
				$pkg_version = $data['version'];
				$has_update = version_compare( $current_version, $pkg_version, '<' );
			}
		} catch ( \Exception $e ) {
			throw new \Exception($e->getMessage());
		}
	}
	return $has_update;
}

/**
 * Setup the components updates cache
 *
 * @uses setup_single_component_update_cache()
 *
 * @hooked 'admin_init'
 *
 * @param bool $force force the update retrieval for cached components
 * @param bool $always_get_update if TRUE bypass the has_update() result
 *
 * @throws \Exception
 */
function setup_components_update_cache($force = false, $always_get_update = false){
	$components = ComponentsManager::getAllComponents();
	foreach ($components as $component){
		try{
			setup_single_component_update_cache($component, $force, $always_get_update);
		}catch(\Exception $e){
			WBF()->get_service_manager()->get_notice_manager()->add_notice(
				'unable_to_update_component_' . $component->name,
				sprintf( __( 'Unable to check for updates of component: %s because of the error: %s' ), $component->name, $e->getMessage() ),
				'error',
				'_flash_'
			);
		}
	}
}

/**
 * Setup single component updates cache
 *
 * @uses has_update()
 * @uses request_single_component()
 * @uses get_component_update_cache()
 * @uses set_component_update_cache()
 *
 * @param Component $component
 * @param bool $force force the update retrieval for cached components
 * @param bool $always_get_update if TRUE bypass the has_update() result
 *
 * @throws \Exception
 */
function setup_single_component_update_cache($component, $force = false, $always_get_update = false){
	if(!$force){
		$package = get_component_update_cache($component);
		if(\is_array($package)) return;
	}
	$needs_update = has_update($component) || $always_get_update;
	if($needs_update){
		$package = request_single_component($component->name, get_update_uri($component));
		set_component_update_cache($component,$package);
	}else{
		set_component_update_cache_as_updated($component);
	}
}

/**
 * @param Component $component
 *
 * @return bool|array
 */
function get_component_update_cache($component){
	return \get_transient('waboot_component_'.$component->name.'_updated_package');
}

/**
 * @param Component $component
 * @param array $package
 */
function set_component_update_cache($component, $package){
	$update_interval = (int) apply_filters('waboot/components/update_check_time_interval', 60*60*24);
	\set_transient('waboot_component_'.$component->name.'_updated_package',$package, $update_interval);
}

/**
 * @param Component $component
 */
function set_component_update_cache_as_updated($component){
	$update_interval = (int) apply_filters('waboot/components/update_check_time_interval', 60*60*24);
	\set_transient('waboot_component_'.$component->name.'_updated_package',[], $update_interval);
}

/**
 * @param Component $component
 */
function delete_component_update_cache($component){
	\delete_transient('waboot_component_'.$component->name.'_updated_package');
}

/**
 * Get the component update uri
 *
 * @uses get_api_single_component_endpoint()
 *
 * @param Component $component
 *
 * @return string
 */
function get_update_uri($component){
	$component_data = get_file_data( $component->file, ['UpdateURI' => 'Update URI', 'Author' => 'Author', 'AuthorURI' => 'Author URI'] );
	if(!isset($component_data['UpdateURI']) || $component_data['UpdateURI'] === ''){
		if(isset($component_data['Author']) && strpos($component_data['Author'],'waboot') !== false){
			return get_api_single_component_endpoint($component->name);
		}
	}else{
		return $component_data['UpdateURI'];
	}
	return '';
}

/**
 * @param Component $component
 *
 * @return bool|string
 */
function get_preview_image(Component $component){
	$thumbnail = $component->directory.'/screenshot.png';
	if(file_exists($thumbnail)){
		return $component->directory_uri.'/screenshot.png';
	}
	$defaultThumbnail = get_template_directory().'/assets/images/components-default.png';
	static $defaultThumbnailExists;
	if($defaultThumbnailExists === null){
		$defaultThumbnailExists = file_exists($defaultThumbnail);
	}
	if($defaultThumbnailExists){
		return get_template_directory_uri().'/assets/images/components-default.png';
	}
	return false;
}

