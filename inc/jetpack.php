<?php
/**
 * Jetpack Related Features
 *
 * @package Waboot
 * @since 0.1.0
 */

/**
 * Add theme support for Infinite Scroll.
 * @see http://jetpack.me/support/infinite-scroll/
 */

add_theme_support( 'infinite-scroll', array(
	'container' => 'content',
	'footer'    => false,
	'render'    => 'waboot_infinite_scroll_init',
) );

/**
 * Loop for Infinite Scroll
 */
function waboot_infinite_scroll_init() {
	while ( have_posts() ) :
		the_post();
		get_template_part( '/templates/parts/content', get_post_format() );
	endwhile;
}