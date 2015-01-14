<?php

/**
 * Allow sanitization of csseditor
 */
add_filter( 'of_sanitize_csseditor', 'of_sanitize_textarea' );
add_filter( 'of_sanitize_typography', 'of_sanitize_typography' );

/**
 * Allow "a", "embed" and "script" tags in theme options text boxes
 */
remove_filter( 'of_sanitize_text', 'sanitize_text_field' );
add_filter( 'of_sanitize_text', 'custom_sanitize_text' );

function custom_sanitize_text( $input ) {
	global $allowedposttags;

	$custom_allowedtags["a"] = array(
		"href"   => array(),
		"target" => array(),
		"id"     => array(),
		"class"  => array()
	);

	$custom_allowedtags = array_merge( $custom_allowedtags, $allowedposttags );
	$output             = wp_kses( $input, $custom_allowedtags );

	return $output;
}

function of_sanitize_typography( $input ) {

	$output = wp_parse_args( $input, array(
		'family'  => '',
		'style'  => array(),
		'charset' => array(),
		'color' => ''
	) );

	/*$output['family'] = apply_filters( 'of_sanitize_text', $output['family'] );
	$output['style'] = apply_filters( 'of_sanitize_text', $output['style'] );
	$output['charset'] = apply_filters( 'of_sanitize_text', $output['charset'] );*/
	$output['color'] = apply_filters( 'of_sanitize_color', $output['color'] );

	return $output;
}