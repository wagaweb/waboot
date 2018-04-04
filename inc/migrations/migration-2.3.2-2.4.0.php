<?php

namespace Waboot\migrations;

use function Waboot\functions\components\install_remote_component;

add_action('init', function(){
	$migrations = \get_option('waboot-migrations', []);
	if(in_array('2.3.2-2.4.0',$migrations) && isset($migrations['2.3.2-2.4.0']['status']) && $migrations['2.3.2-2.4.0']['status'] === 'done') return;

	if(!isset($migrations['2.3.2-2.4.0'])){
		$migrations['2.3.2-2.4.0'] = [
			'status' => 'incomplete'
		];
	}

	$current_migration = $migrations['2.3.2-2.4.0'];

	if(is_admin()){
		$backupped_components_states = \get_option('waboot_updates_backups_components');
		$current_theme = wp_get_theme();
		$hash = '2.3.2'.'_'.'2.4.0'.'_'.$current_theme->get_stylesheet();
		$last_backupped_components_states_update = array_key_exists($hash,$backupped_components_states) ? $backupped_components_states[$hash] : false;
		if($last_backupped_components_states_update && is_file($last_backupped_components_states_update['file'])){
			$states = file_get_contents($last_backupped_components_states_update['file']);
			$states = unserialize($states);
			if(is_array($states) && !empty($states)){
				foreach($states as $component_slug => $state){
					if($state === 1){
						$installed_component = in_array('installed_component_'.$component_slug,$current_migration);
						if(!$installed_component){
							$msg = sprintf(__('You must install and activate the component: %s. Please <a href="%s">click here</a> to do it'),$component_slug,add_query_arg(['waboot_perform_updates' => 'component','comp_slug' => $component_slug]));
							WBF()->get_service_manager()->get_notice_manager()->add_notice('must_install_component_'.$component_slug,$msg,'nag');
						}
					}
				}
			}
		}
	}
});

function mig_232_240_install_component($component){
	$migrations = \get_option('waboot-migrations', []);
	$current_migration = $migrations['2.3.2-2.4.0'];

	//Doing the update...
	install_remote_component($component);

	//Update the option
	//$current_migration['installed_component_'.$component] = true;
	//$migrations['2.3.2-2.4.0'] = $current_migration;
	//\update_option('waboot-migrations',$migrations);
}