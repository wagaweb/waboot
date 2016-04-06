<?php

namespace Waboot\hooks\init;

if(!function_exists( 'waboot_setup')):
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

		/**
		 * Register the navigation menus. This theme uses wp_nav_menu() in three locations.
		 */
		register_nav_menus([
			'top'           => __( 'Top Menu', 'waboot' ),
			'main'          => __( 'Main Menu', 'waboot' ),
			'bottom'        => __( 'Bottom Menu', 'waboot' )
		]);
	}
	add_action('after_setup_theme', __NAMESPACE__."\\setup", 11);
endif;

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
add_filter("wbf/admin_menu/label",__NAMESPACE__."set_wbf_admin_menu_label");