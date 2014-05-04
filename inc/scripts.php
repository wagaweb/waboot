<?php
/**
 * Load Bootstrap javascript modules
 *
 * @package Waboot
 * @since 1.0.0
 */
function waboot_bootstrap_js_loader() {

	// Bootstrap JS components - Drop a custom build in your child theme's 'js' folder to override this one.
	wp_enqueue_script( 'bootstrap.js', alienship_locate_template_uri( 'js/bootstrap.min.js' ), array( 'jquery' ), '3.0.2', true );

	// Bootstrap helper script
	wp_enqueue_script( 'alienship-helper.js', alienship_locate_template_uri( 'core/js/alienship-helper.js' ), array('jquery'),'1.0.0', true);

	// Comment reply script
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );
}
add_action( 'wp_enqueue_scripts', 'waboot_bootstrap_js_loader' );