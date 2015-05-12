<?php

/**
 * Display the contact form
 * @use wbft_the_contact_form
 */
add_shortcode( 'wb_contact_form', function($atts){
	ob_start();
	wbft_the_contact_form();
	$return_string = trim( preg_replace( "|[\r\n\t]|", "", ob_get_clean() ) );
	return $return_string;
});