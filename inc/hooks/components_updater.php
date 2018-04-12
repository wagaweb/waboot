<?php

namespace Waboot\hooks\components_updater;

use function Waboot\functions\components\get_components_to_update;
use function Waboot\functions\components\install_remote_component;
use function Waboot\functions\components\set_component_update_cache;
use function Waboot\functions\components\set_component_update_cache_as_updated;
use function Waboot\functions\components\setup_components_update_cache;
use WBF\components\mvc\HTMLView;
use WBF\components\utils\WordPress;
use WBF\modules\components\Component;
use WBF\modules\components\ComponentFactory;
use WBF\modules\components\ComponentsManager;

add_action('admin_init', __NAMESPACE__ . '\\build_update_cache');
add_filter('wp_get_update_data', __NAMESPACE__.'\\notify_updates',11,2);
add_action('core_upgrade_preamble', __NAMESPACE__.'\\force_check_for_updates');
add_action('core_upgrade_preamble', __NAMESPACE__.'\\display_components_updates',11);
add_action('update-core-custom_'.'do-component-upgrade', __NAMESPACE__.'\\do_component_upgrade');

/**
 * Setup the components updates cache
 *
 * @uses setup_components_update_cache()
 *
 * @hooked 'admin_init'
 *
 * @throws \Exception
 */
function build_update_cache(){
	if(is_admin() && isset($_GET['waboot_force_components_update_check'])){
		if($_GET['waboot_force_components_update_check'] === '1'){
			setup_components_update_cache(true);
		}elseif($_GET['waboot_force_components_update_check'] === '2'){
			setup_components_update_cache(true,true);
		}
	}else{
		setup_components_update_cache();
	}
}

/**
 * Alter WP Update data
 *
 * @hooked 'wp_get_update_data'
 */
function notify_updates($update_data, $titles){
	$components_to_update = get_components_to_update();
	if(\count($components_to_update) > 0){
		$update_data['theme-components'] = \count($components_to_update);
		$update_data['counts']['total'] = $update_data['counts']['total'] + $update_data['theme-components'];
		$update_data['title'] = $titles ? esc_attr( implode( ', ', $titles ) ) : '';
		$update_data['title'] = $update_data['title'].' , '.sprintf( _n( '%d Component Update', '%d Component Updates', $update_data['theme-components'], 'waboot' ), $update_data['theme-components'] );
	}
	return $update_data;
}

/**
 * Force check for updates in update-core.php page
 *
 * @hooked 'core_upgrade_preamble'
 *
 * @throws \Exception
 */
function force_check_for_updates(){
	if(!isset($_GET['force-check']) || $_GET['force-check'] !== '1') return;
	setup_components_update_cache(true);
}

/**
 * Displays components updates in update-core.php
 *
 * @hooked 'core_upgrade_preamble', 11
 */
function display_components_updates(){
	$components = get_components_to_update();
    (new HTMLView('templates/admin/components-updates-list.php'))->display([
        'all_updated' => \count($components) === 0,
        'no_components' => \count(ComponentsManager::getAllComponents()) === 0,
        'components_to_update' => $components,
	    'update_form_action' => admin_url('update-core.php?action=do-component-upgrade')
    ]);
}

/**
 * Update single or multiple (in future) component
 *
 * @hooked update-core-custom_{$action}
 */
function do_component_upgrade(){
	$component_slug = isset($_GET['component']) ? $_GET['component'] : false;
	$component_nicename = isset($_GET['nicename']) ? $_GET['nicename'] : $component_slug;
	WordPress::maintenance_mode(true);
	if(\is_string($component_slug) && $component_slug !== ''){
		try{
			$r = install_remote_component($component_slug, true);
			//Set as updated
			$component = ComponentFactory::create_from_slug($component_slug);
			if($component instanceof Component){
				set_component_update_cache_as_updated($component);
			}
		}catch (\Exception $e){
			$error = $e->getMessage();
		}
	}else{
		$error = _x('Invalid component provided','Component Update Landing page','waboot');
	}

	$update_actions = [
		'components_page' => '<a href="' . add_query_arg(['page' => 'wbf_components'],self_admin_url('admin.php')) . '" target="_parent">' . __( 'Return to Components page', 'waboot' ) . '</a>',
		'updates_page' => '<a href="' . self_admin_url( 'update-core.php' ) . '" target="_parent">' . __( 'Return to WordPress Updates page' ) . '</a>'
	];

	WordPress::maintenance_mode(false);
	(new HTMLView('templates/admin/components-update-landing-page.php'))->display([
		'component_nicename' => $component_nicename,
		'error_occurred' => isset($error),
		'error' => isset($error) ? $error : false,
		'update_actions' => $update_actions
	]);
}