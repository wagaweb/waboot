<?php
/**
 * Jetpack Related Features
 */

if(!function_exists("wbft_add_jetpack_support")):
	function wbft_add_jetpack_support(){
		/**
		 * Add theme support for Infinite Scroll.
		 * @see http://jetpack.me/support/infinite-scroll/
		 */
		add_theme_support( 'infinite-scroll', array(
			'container' => 'content',
			'footer'    => false,
			'render'    => 'wbft_infinite_scroll_init',
		) );
	}
	add_action('after_setup_theme', 'wbft_add_jetpack_support', 11);
endif;

/**
 * Loop for Infinite Scroll
 */
function wbft_infinite_scroll_init() {
	while ( have_posts() ) :
		the_post();
		get_template_part( '/templates/parts/content', get_post_format() );
	endwhile;
}