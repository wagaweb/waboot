<?php

namespace Waboot\hooks\generators;

use Waboot\Theme;
use WBF\components\mvc\HTMLView;

add_action("wp_ajax_handle_generator", function(){
	$selected_generator = isset($_POST['params']) && isset($_POST['params']['generator']) ? sanitize_text_field($_POST['params']['generator']) : false;
	$step = isset($_POST['params']) && isset($_POST['params']['step']) ? sanitize_text_field($_POST['params']['step']) : Theme::GENERATOR_STEP_ALL;
	$action = isset($_POST['params']) && isset($_POST['params']['action']) ? sanitize_text_field($_POST['params']['action']) : Theme::GENERATOR_ACTION_ALL;

	if($selected_generator){
		$r = Theme::getInstance()->handle_generator($selected_generator,$step,$action);
		if($r['status'] == 'success'){
			if($r['complete']){
				$r['status'] = "complete";
			}else{
				$r['status'] = "run";
			}
			wp_send_json_success($r);
		}else{
			$r['status'] = "complete";
			wp_send_json_error($r);
		}
	}else{
		$r['status'] = "complete";
		wp_send_json_error();
	}
});

/**
 * Handle wizard submit
 *
 * @hooked 'admin_init'
 *
 */
function handle_wizard(){
	if(!isset($_POST['waboot_wizard_nonce'])) return;

	$r = true;

	//Check generators
	$selected_generator = isset($_POST['generator']) ? sanitize_text_field($_POST['generator']) : false;
	if($selected_generator){
		$r = Theme::getInstance()->handle_generator($selected_generator);
	}

	if($r){
		WBF()->notice_manager->add_notice("waboot_wizard_completed",__("Wizard completed successfully","waboot"),"updated","_flash_");
		update_option("waboot-done-wizard",true);
	}else{
		WBF()->notice_manager->add_notice("waboot_wizard_completed",__("Wizard encountered some errors","waboot"),"error","_flash_");
	}
}
add_action('admin_init',__NAMESPACE__."\\handle_wizard",10);

/**
 * Adds and display Waboot Wizard page
 *
 * @hooked 'wbf_admin_submenu'
 *
 * @param $menu_slug
 */
function add_wizard_page($menu_slug){
	$menu = [
		'page_title' => __("Setup Wizard","waboot"),
		'menu_title' => __("Setup Wizard","waboot"),
		'capability' => "manage_options",
		'menu_slug'  => "waboot_setup_wizard"
	];

	add_submenu_page( $menu_slug, $menu['page_title'], $menu['menu_title'], $menu['capability'], $menu['menu_slug'], function(){

		$v = new HTMLView("templates/admin/wizard.php");

		$generators = Theme::get_generators();

		$v->for_dashboard()->display([
			"page_title" => __("Setup Wizard","waboot"),
			"generators" => $generators,
			"nonce_action" => "waboot_submit_wizard",
			"nonce_name" => "waboot_wizard_nonce"
		]);
	});
}
add_action("wbf_admin_submenu",__NAMESPACE__."\\add_wizard_page");