<?php

namespace Waboot\hooks\generators;

use function Waboot\functions\get_start_wizard_link;
use function Waboot\functions\wbf_exists;
use Waboot\Theme;
use WBF\components\mvc\HTMLView;

/**
 * Redirect to Wizard page after the first theme switch
 */
function redirect_to_wizard(){
	$wizard_done = Theme::is_wizard_done();
	if(!$wizard_done){
		$start_wizard_link = get_start_wizard_link();
		wp_redirect($start_wizard_link);
	}
}
add_action("after_switch_theme", __NAMESPACE__."\\redirect_to_wizard");

/**
 * Adds the notice if the Wizard has never been done
 */
function add_wizard_notice(){
	if(wp_doing_ajax()) return;
	if(isset($_GET['page']) && $_GET['page'] === 'waboot_setup_wizard') return;
	$wizard_done = Theme::is_wizard_done();
	$wizard_skipped = Theme::is_wizard_skipped();
	if($wizard_done || $wizard_skipped) return;
	//Add the notice to wizard
	if(wbf_exists()){
		if(!WBF()->is_wbf_admin_page()){
			$start_wizard_link = get_start_wizard_link();
			$dismiss_wizard_link = add_query_arg(["waboot_dismiss_wizard"=>1],admin_url("themes.php"));
			$msg = sprintf(__("Thank you choosing Waboot! If you want, our wizard will help you to kickstart your theme with some initial settings: click <a href='%s'>here</a> to start or <a href='%s'>here</a> to dismiss this notice.","waboot"),$start_wizard_link,$dismiss_wizard_link);
			WBF()->services()->get_notice_manager()->add_notice("waboot-wizard",$msg,"nag","_flash_");
		}
	}else{
		$class = 'notice notice-error';
		$wizard_url = \Waboot\functions\get_start_wizard_link();
		$message = sprintf(
			__( "Waboot theme is missing some requirements to work properly. You can run the <a href='%s'>Wizard</a> to take care of them.", 'Waboot' ),
			$wizard_url
		);
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
	}
}
add_action("admin_init",__NAMESPACE__."\\add_wizard_notice",11);

/**
 * Handles the dismissing of the Wizard notice
 */
function dismiss_wizard_notice(){
	if(!isset($_GET['waboot_dismiss_wizard'])) return;
	if($_GET['waboot_dismiss_wizard'] == 1){
		WBF()->services()->get_notice_manager()->remove_notice("waboot-wizard");
		Theme::set_wizard_as_skipped();
	}
}
add_action("admin_init",__NAMESPACE__."\\dismiss_wizard_notice",11);

/**
 * Handles the resets of Wizard options
 */
function reset_wizard_status(){
	if(!isset($_GET['waboot_reset_wizard'])) return;
	if($_GET['waboot_reset_wizard'] == 1){
		WBF()->services()->get_notice_manager()->remove_notice("waboot-wizard");
		Theme::reset_wizard();
	}
}
add_action("admin_init",__NAMESPACE__."\\reset_wizard_status",11);

/**
 * Handles wizard submit via AJAX
 */
function handle_wizard_via_ajax(){
	$selected_generator = isset($_POST['params']) && isset($_POST['params']['generator']) ? sanitize_text_field($_POST['params']['generator']) : false;
	$step = isset($_POST['params']) && isset($_POST['params']['step']) ? sanitize_text_field($_POST['params']['step']) : Theme::GENERATOR_STEP_ALL;
	$action = isset($_POST['params']) && isset($_POST['params']['action']) ? sanitize_text_field($_POST['params']['action']) : Theme::GENERATOR_ACTION_ALL;

	if($selected_generator){
		$r = Waboot()->handle_generator($selected_generator,$step,$action);
		if($r['status'] === 'success'){
			if($r['complete']){
				$r['status'] = "complete";
				Theme::set_wizard_as_done();
			}else{
				$r['status'] = "run";
			}
			wp_send_json_success($r);
		}elseif($r['status'] === 'failed'){
			wp_send_json_error($r);
		}else{
			$r['status'] = "complete";
			wp_send_json_error($r);
		}
	}else{
		$r['status'] = 'failed';
		$r['message'] = 'No options selected';
		wp_send_json_error($r);
	}
}
add_action("wp_ajax_handle_generator", __NAMESPACE__."\\handle_wizard_via_ajax");

/**
 * Handle wizard submit via page refresh (not used anymore)
 *
 * @hooked 'admin_init'
 */
function handle_wizard(){
	if(!isset($_POST['waboot_wizard_nonce'])) return;

	$r = true;

	//Check generators
	$selected_generator = isset($_POST['generator']) ? sanitize_text_field($_POST['generator']) : false;
	if($selected_generator){
		$r = Waboot()->handle_generator($selected_generator);
	}

	if($r){
		WBF()->services()->get_notice_manager()->add_notice("waboot_wizard_completed",__("Wizard completed successfully","waboot"),"updated","_flash_");
		Theme::set_wizard_as_done();
	}else{
		WBF()->services()->get_notice_manager()->add_notice("waboot_wizard_completed",__("Wizard encountered some errors","waboot"),"error","_flash_");
	}
}
if(wbf_exists()) add_action('admin_init',__NAMESPACE__."\\handle_wizard",11);

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
		'menu_title' => Theme::is_wizard_done() ? __("Setup Wizard (Admin only)","waboot") : __("Setup Wizard","waboot"),
		'capability' => "manage_options",
		'menu_slug'  => "waboot_setup_wizard"
	];

	add_submenu_page( $menu_slug, $menu['page_title'], $menu['menu_title'], $menu['capability'], $menu['menu_slug'], __NAMESPACE__.'\display_wizard_page');
}

/**
 * Prints out the wizard page
 */
function display_wizard_page(){
	$generators = Theme::get_generators();

	if(class_exists('WBF\components\mvc\HTMLView')){
		$v = new HTMLView("templates/admin/wizard.php");
		$v->display([
			"page_title" => '',
			"generators" => $generators,
			"images_uri" => get_template_directory_uri().'/assets/images',
			"nonce_action" => "waboot_submit_wizard",
			"nonce_name" => "waboot_wizard_nonce"
		]);
	}else{
		$page_title = __("Setup Wizard","waboot");
		$nonce_action = "waboot_submit_wizard";
		$nonce_name = "waboot_wizard_nonce";
		$images_uri = get_template_directory_uri().'/assets/images';
		require_once get_template_directory().'/templates/admin/wizard.php';
	}
}

if(!wbf_exists()){
	add_action('admin_menu', function(){
		$menu = [
			'page_title' => __("Waboot Setup Wizard","waboot"),
			'menu_title' => __("Waboot Setup Wizard","waboot"),
			'capability' => "manage_options",
			'menu_slug'  => "waboot_setup_wizard"
		];
		\add_management_page( $menu['page_title'], $menu['menu_title'], $menu['capability'], $menu['menu_slug'], __NAMESPACE__.'\display_wizard_page');
	});
}else{
	if(!Theme::is_wizard_done()){
		add_action("wbf_admin_submenu",__NAMESPACE__."\\add_wizard_page",13);
	}
}

function print_debug_actions(){
	?>
	<li><a href="<?php echo add_query_arg('waboot_reset_wizard',1); ?>"><?php _e('Reset Waboot Wizard Status','waboot'); ?></a></li>
	<?php
}
add_action("wbf/admins/status_page/administration_console_table/actions_list",__NAMESPACE__."\\print_debug_actions");