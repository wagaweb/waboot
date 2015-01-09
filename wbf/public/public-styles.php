<?php
/**
 * Register and enqueue the front end CSS
 *
 * @package Waboot
 * @since 0.1.0
 */

add_action( 'wp_enqueue_scripts', 'wbf_add_client_custom_css', 99 );

/**
 * Adds client custom CSS
 */
function wbf_add_client_custom_css(){
	$client_custom_css = waboot_of_custom_css();

	if($client_custom_css){
		wp_enqueue_style('client-custom',$client_custom_css);
	}
}