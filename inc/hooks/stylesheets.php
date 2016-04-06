<?php

namespace Waboot\hooks\styles;

/**
 * Loads front end styles
 */
function theme_styles() {
	wp_enqueue_style( 'core-style', get_stylesheet_uri(), array( 'font-awesome' ), false, 'all' ); //style.css
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__.'\\theme_styles' );