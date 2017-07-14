<?php

namespace Waboot\hooks\init;

use Waboot\Theme;
use WBF\components\mvc\HTMLView;
use WBF\components\notices\Notice_Manager;

function check_for_wizard(){
	$wizard_done = Theme::is_wizard_done();
	if(!$wizard_done){
		//Add the notice to wizard
		$start_wizard_link = admin_url("admin.php?page=waboot_setup_wizard");
		$dismiss_wizard_link = add_query_arg(["waboot_dismiss_wizard"=>1],admin_url("themes.php"));
		$msg = sprintf(__("Thank you to have chosen Waboot! If you want, our wizard will help you to kickstart your theme with some initiali settings: click <a href='%s'>here</a> to start or <a href='%s'>here</a> to dismiss this notice.","waboot"),$start_wizard_link,$dismiss_wizard_link);
		WBF()->notice_manager->add_notice("waboot-wizard",$msg,"nag","base","\\Waboot\\DoneWizardCondition",["_file"=>get_template_directory()."/inc/DoneWizardCondition.php"]);
	}
}
add_action("after_switch_theme", __NAMESPACE__."\\check_for_wizard");

function dismiss_wizard_notice(){
	if(!isset($_GET['waboot_dismiss_wizard'])) return;
	if($_GET['waboot_dismiss_wizard'] == 1){
		WBF()->notice_manager->remove_notice("waboot-wizard");
	}
}
add_action("admin_init",__NAMESPACE__."\\dismiss_wizard_notice");

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