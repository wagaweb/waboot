<?php

abstract class WabootGenerator{
	/**
	 * Checks if WBF is active
	 *
	 * @return bool
	 */
	protected function is_wbf_active(){
		return function_exists('WBF');
	}

	/**
	 * Checks if WBF is installed
	 *
	 * @return boolean
	 */
	protected function is_wbf_installed(){
		$is_installed = get_option('wbf_installed',false);
		if($is_installed){
			$path = $this->get_wbf_path();
			return is_dir($path);
		}
		return false;
	}

	/**
	 * @return string|boolean
	 */
	protected function get_wbf_path(){
		return get_option('wbf_path',false);
	}

	/**
	 * Clear WBF installation
	 */
	protected function clear_wbf_directory(){
		$path = $this->get_wbf_path();
		if($path && is_dir($path)){
			return rmdir($path);
		}
		return true;
	}

	/**
	 * Activate WBF
	 *
	 * @return null
	 */
	protected function activate_wbf(){
		$plugin = 'wbf/wbf.php';
		$current = get_option( 'active_plugins' );
		$plugin = plugin_basename( trim( $plugin ) );

		if ( !in_array( $plugin, $current ) ) {
			$current[] = $plugin;
			sort( $current );
			do_action( 'activate_plugin', trim( $plugin ) );
			update_option( 'active_plugins', $current );
			do_action( 'activate_' . trim( $plugin ) );
			do_action( 'activated_plugin', trim( $plugin) );
		}

		return null;
	}
}