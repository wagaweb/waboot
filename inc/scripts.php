<?php
/**
 * Load Bootstrap javascript modules
 *
 * @package Waboot
 * @since 1.0.0
 */
function waboot_bootstrap_js_loader() {

	// Bootstrap JS components - Drop a custom build in your child theme's 'js' folder to override this one.
	wp_enqueue_script( 'bootstrap.js', waboot_locate_template_uri( 'assets/js/bootstrap.min.js' ), array( 'jquery' ), '3.0.2', true );

	// Bootstrap helper script
	wp_enqueue_script( 'alienship-helper.js', waboot_locate_template_uri( 'sources/js/alienship-helper.js' ), array('jquery'),'1.0.0', true);
	wp_enqueue_script( 'dropdown-toggle.js', waboot_locate_template_uri( 'sources/js/dropdown-toggle.js' ), array('jquery'),'1.0.0', true);

	// Comment reply script
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );
}
add_action( 'wp_enqueue_scripts', 'waboot_bootstrap_js_loader' );