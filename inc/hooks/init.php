<?php

namespace Waboot\hooks\init;

use Waboot\Theme;
use WBF\components\mvc\HTMLView;
use WBF\components\notices\Notice_Manager;

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
	// @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	add_theme_support( 'post-thumbnails' );

	// Add support for post formats. To be styled in later release.
	add_theme_support( 'post-formats', array( 'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat' ) );

	// Let WordPress decide document title
	add_theme_support( 'title-tag' );

	// Add support for responsive embeds.
	add_theme_support( 'responsive-embeds' );

	// Add theme support for selective refresh for widgets.
	// todo: https://make.wordpress.org/core/2016/03/22/implementing-selective-refresh-support-for-widgets/
	// add_theme_support( 'customize-selective-refresh-widgets' );

	// Add support for editor styles.
	add_theme_support( 'editor-styles' );

	// Adding support for core block visual styles.
	add_theme_support( 'wp-block-styles' );

	// Adding support for Gutemberg Wide Alignment
	add_theme_support('align-wide' );
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
 * Allows developers to edit the Layout grid classes
 */
function update_grid_classes(){
	WabootLayout()->update_grid_classes();
}
add_action("init",__NAMESPACE__."\\update_grid_classes",15);

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
 * Display the custom Waboot components repository page
 *
 * @param $view
 *
 * @return array
 */
function display_components_add_new_page($view){
	if(isset($_GET['section']) && $_GET['section'] === 'add_new'){
		$view['file'] = 'templates/admin/add-new-components.php';
		$view['plugin'] = null;
	}
	return $view;
}
add_filter('wbf/modules/components/views/components-page/file', __NAMESPACE__."\\display_components_add_new_page");

/**
 * Set the custom view params for the Waboot components repository page
 *
 * @param $args
 *
 * @return array
 */
function inject_components_add_new_page_args($args){
	return $args;
}
add_filter('wbf/modules/components/views/components-page/args', __NAMESPACE__."\\inject_components_add_new_page_args");
