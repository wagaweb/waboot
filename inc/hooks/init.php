<?php

namespace Waboot\inc\hooks;

function setup() {
	//Make theme available for translation.
	load_theme_textdomain( LANG_TEXTDOMAIN, get_template_directory() . '/languages' );

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

	// Custom logo
	add_theme_support('custom-logo');

    // WooCommerce
    add_theme_support('woocommerce');
}
add_action('after_setup_theme', __NAMESPACE__."\\setup", 11);

/**
 * Register the navigation menus. This theme uses wp_nav_menu() in three locations.
 */
function registerMenus(){
	register_nav_menus([
		'main'          => __( 'Main Menu', LANG_TEXTDOMAIN ),
		'mobile'        => __( 'Mobile Menu', LANG_TEXTDOMAIN ),
		'bottom'        => __( 'Bottom Menu', LANG_TEXTDOMAIN )
	]);
}
add_action('after_setup_theme',__NAMESPACE__."\\registerMenus",11);

/**
 * Cleanup the head
 * @source http://geoffgraham.me/wordpress-how-to-clean-up-the-header/
 */
function headCleanup(): void {
    // EditURI link
    remove_action( 'wp_head', 'rsd_link' );
    // Category feed links
    remove_action( 'wp_head', 'feed_links_extra', 3 );
    // Post and comment feed links
    remove_action( 'wp_head', 'feed_links', 2 );
    // Windows Live Writer
    remove_action( 'wp_head', 'wlwmanifest_link' );
    // Index link
    remove_action( 'wp_head', 'index_rel_link' );
    // Previous link
    remove_action( 'wp_head', 'parent_post_rel_link', 10);
    // Start link
    remove_action( 'wp_head', 'start_post_rel_link', 10);
    // Canonical
    remove_action('wp_head', 'rel_canonical', 10);
    // Shortlink
    remove_action( 'wp_head', 'wp_shortlink_wp_head', 10);
    // Links for adjacent posts
    remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10);
    // WP version
    remove_action( 'wp_head', 'wp_generator' );
}
add_action('init',__NAMESPACE__.'\\headCleanup');

/**
 * @return void
 */
function addHealthCheckEndpoint() {
	add_filter("query_vars", static function($publicQueryVars){
		$publicQueryVars[] = "health_check";
		return $publicQueryVars;
	}, 10, 1);
	add_action("parse_request", static function($wp){
		if (isset($wp->query_vars["health_check"]) && "true" === $wp->query_vars["health_check"]) {
			// Use the global instance created by WordPress
			global $wpdb;
			// Check the connection:
			if (!$wpdb->check_connection(false)) {
				die(__("No DB connection"));
			}
			header("Content-length: 0");
			exit;
		}
	}, 10, 1);
	add_action("init", static function(){
		add_rewrite_rule("^wphealth$", "index.php?health_check=true", "top");
		flush_rewrite_rules();
	}, 10);
}
addHealthCheckEndpoint();

/*
 * Disable plugins auto-update
 */
add_filter( 'auto_update_plugin', '__return_false' );