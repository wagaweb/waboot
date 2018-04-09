<?php

namespace Waboot\hooks\components_updater;

use function Waboot\functions\components\get_components_to_update;
use function Waboot\functions\components\setup_components_update_cache;

add_action('admin_init', __NAMESPACE__ . '\\build_update_cache');
add_filter('wp_get_update_data', __NAMESPACE__.'\\notify_updates',11,2);
add_action('core_upgrade_preamble', __NAMESPACE__.'\\display_components_updates');

/**
 * Setup the components updates cache
 *
 * @hooked 'admin_init'
 *
 * @throws \Exception
 */
function build_update_cache(){
	setup_components_update_cache(true);
}

/**
 * Alter WP Update data
 *
 * @hooked 'wp_get_update_data'
 */
function notify_updates($update_data, $titles){
	$components_to_update = get_components_to_update();
	if(count($components_to_update) > 0){
		$update_data['theme-components'] = count($components_to_update);
		$update_data['counts']['total'] = $update_data['counts']['total'] + $update_data['theme-components'];
		$update_data['title'] = $titles ? esc_attr( implode( ', ', $titles ) ) : '';
		$update_data['title'] = $update_data['title'].' , '.sprintf( _n( '%d Component Update', '%d Component Updates', $update_data['theme-components'] ), $update_data['theme-components'] );
	}
	return $update_data;
}

/**
 * Displays components updates in update-core.php
 *
 * @hooked 'core_upgrade_preamble'
 */
function display_components_updates(){
	$components = get_components_to_update();
}