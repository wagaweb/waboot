<?php

namespace Waboot\functions\components;
use WBF\components\utils\Paths;
use WBF\modules\components\Component;
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
				if( \strlen($current_version) < \strlen($pkg_version) ){
					/**
					 * Modified version of version_compare to address non-standardized comparing (http://php.net/manual/en/function.version-compare.php)
					 * @param string $version1
					 * @param string $version2
					 * @param string $operator
					 *
					 * @return int|bool
					 */
					$mod_version_compare = function($version1, $version2, $operator = null) {
						$_fv = (int) trim( str_replace( '.', '', $version1 ) );
						$_sv = (int) trim( str_replace( '.', '', $version2 ) );
						if( \strlen ( $_fv ) > \strlen ( $_sv ) ){
							$_sv = str_pad ( $_sv, \strlen ( $_fv ), 0 );
						}
						if(\strlen ( $_fv ) < \strlen ( $_sv ) ){
							$_fv = str_pad ( $_fv, \strlen ( $_sv ), 0 );
						}
						return version_compare ( ( string ) $_fv, ( string ) $_sv, $operator );
					};
					$has_update = $mod_version_compare( $current_version, $pkg_version, '<' );
				}else{
					$has_update = version_compare( $current_version, $pkg_version, '<' );
				}
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
 *
 * @throws \Exception
 */
function setup_components_update_cache($force = false){
	$components = ComponentsManager::getAllComponents();
	foreach ($components as $component){
		try{
			setup_single_component_update_cache($component, $force);
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
 * @param Component $component
 * @param bool $force force the update retrieval for cached components
 *
 * @throws \Exception
 */
function setup_single_component_update_cache($component, $force = false){
	if(!$force){
		$package = \get_transient('waboot_component_'.$component->name.'_updated_package');
		if(\is_array($package)) return;
	}
	$needs_update = has_update($component);
	$update_interval = (int) apply_filters('waboot/components/update_check_time_interval', 60*60*24);
	if($needs_update){
		$package = request_single_component($component->name, get_update_uri($component));
		\set_transient('waboot_component_'.$component->name.'_updated_package',$package,$update_interval);
	}else{
		\set_transient('waboot_component_'.$component->name.'_updated_package',[],$update_interval);
	}
}

/**
 * Get the component update uri
 *
 * @param Component $component
 *
 * @return string
 */
function get_update_uri($component){
	$component_data = get_file_data( $component->file, ['UpdateURI' => 'Update URI', 'Author' => 'Author', 'AuthorURI' => 'Author URI'] );
	if(!isset($component_data['UpdateURI']) || $component_data['UpdateURI'] === ''){
		if(isset($component_data['Author']) && strpos($component_data['Author'],'waga') !== false){
			return get_api_single_component_endpoint($component->name);
		}
	}else{
		return $component_data['UpdateURI'];
	}
	return '';
}

