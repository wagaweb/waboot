<?php

namespace Waboot\woocommerce;

//Declare WooCommerce support
add_theme_support( 'woocommerce' );

//Setup the wrapper
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
add_action('woocommerce_before_main_content', __NAMESPACE__."\\wrapper_start", 10);
add_action('woocommerce_after_main_content', __NAMESPACE__."\\wrapper_end", 10);

/**
 * Set WooCommerce wrapper start tags
 *
 * @hooked 'woocommerce_before_main_content'
 */
function wrapper_start() {
    \get_template_part("templates/wrapper","start");
}

/**
 * Set WooCommerce wrapper end tags
 *
 * @hooked 'woocommerce_after_main_content'
 */
function wrapper_end() {
    \get_template_part("templates/wrapper","end");
}

//Layout altering:
add_filter("waboot/layout/main_wrapper/classes", __NAMESPACE__."\\set_main_wrapper_classes");
add_action("waboot/woocommerce/loop", __NAMESPACE__."\\loop_template");

/**
 * Set the main loop template
 *
 * @hooked 'waboot/woocommerce/loop'
 */
function loop_template(){
    \get_template_part('woocommerce/loop/waboot','loop');
}

/**
 * Set the main wrapper classes
 *
 * @hooked 'waboot/layout/main_wrapper/classes'
 *
 * @param $classes
 *
 * @return mixed|void
 */
function set_main_wrapper_classes($classes){
	if(\is_shop()){
		$new_classes = apply_filters( 'waboot/woocommerce/layout/main/classes', ['shop-content']);
		foreach ($new_classes as $c){
			$classes[] = $c;
		}
	}
	return $classes;
}

function alter_entry_title($title, $current_title_position){
	if(\is_product_category()){
		$title = \Waboot\functions\get_archive_page_title();
	}elseif(\is_shop()){
		$title = get_shop_page_title();
	}
	return $title;
}
add_filter("waboot/entry/title", __NAMESPACE__."\\alter_entry_title", 10, 2);