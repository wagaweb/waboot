<?php

namespace Waboot\hooks\init;

use Waboot\Theme;
use WBF\components\mvc\HTMLView;
use WBF\components\notices\Notice_Manager;

function check_for_wizard(){
	$wizard_done = get_option("waboot-done-wizard",false);
	if(!$wizard_done){
		//Add the notice to wizard
	}
}
add_action("after_switch_theme", __NAMESPACE__."\\check_for_wizard");

function setup() {
	//Make theme available for translation.
	load_theme_textdomain( 'waboot', get_template_directory() . '/languages' );

	// Switch default core markup for search form, comment form, and comments to output valid HTML5.
	add_theme_support( 'html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption') );

	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// Add support for custom backgrounds
	add_theme_support( 'custom-background', array('default-color' => 'ffffff') );

	// Add support for post-thumbnails
	add_theme_support( 'post-thumbnails' );

	// Add support for post formats. To be styled in later release.
	add_theme_support( 'post-formats', array( 'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat' ) );
}
add_action('after_setup_theme', __NAMESPACE__."\\setup", 11);

/**
 * Register the navigation menus. This theme uses wp_nav_menu() in three locations.
 * todo: valutare se farli creare solo ai componenti interessati
 */
function register_menus(){
	register_nav_menus([
		'top'           => __( 'Top Menu', 'waboot' ),
		'main'          => __( 'Main Menu', 'waboot' ),
		'bottom'        => __( 'Bottom Menu', 'waboot' )
	]);
}
add_action("after_setup_theme",__NAMESPACE__."\\register_menus",11);

/**
 * Rename the default label of admin menu
 *
 * @param $label
 *
 * @hooked 'wbf/admin_menu/label'
 *
 * @return string
 */
function set_wbf_admin_menu_label($label){
	return "Waboot";
}
add_filter("wbf/admin_menu/label",__NAMESPACE__."\\set_wbf_admin_menu_label");

/**
 * Set the icon of admin menu
 *
 * @param $icon
 *
 * @hooked 'wbf/admin_menu/icon'
 *
 * @return string
 */
function set_wbf_admin_menu_icon($icon){
	$icon = get_template_directory_uri()."/assets/images/options/icons/waboot-icon-20x20.svg";
	return $icon;
}
add_filter("wbf/admin_menu/icon",__NAMESPACE__."\\set_wbf_admin_menu_icon");

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