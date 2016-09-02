<?php

namespace Waboot\woocommerce;

use WBF\modules\options\Organizer;

//Declare WooCommerce support
add_action('init', function(){
	add_theme_support( 'woocommerce' );
},20);

//Setup the wrapper
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
add_action('woocommerce_before_main_content', __NAMESPACE__."\\wrapper_start", 10);
add_action('woocommerce_after_main_content', __NAMESPACE__."\\wrapper_end", 10);

//Layout altering:
add_filter("waboot/layout/main_wrapper/classes", __NAMESPACE__."\\set_main_wrapper_classes");

/**
 * Set WooCommerce wrapper start tags
 *
 * @hooked 'woocommerce_before_main_content'
 */
function wrapper_start() {
	$main_wrapper_vars = \Waboot\functions\get_main_wrapper_template_vars();
	?>
	<div id="main-wrapper" class="<?php echo $main_wrapper_vars['classes']; ?>">
		<div class="main-inner">
	<?php
}

/**
 * Set WooCommerce wrapper end tags
 *
 * @hooked 'woocommerce_after_main_content'
 */
function wrapper_end() {
	?>
		</div><!-- .main-inner -->
	</div><!-- #main-wrapper -->
	<?php
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
		$new_classes = apply_filters( 'waboot/woocommerce/layout/main/classes', ['content-area','col-sm-8']);
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

function alter_entry_title_visibility($is_visible, $current_title_position){
	if(\is_product_category()){
		if($current_title_position == "top"){
			$is_visible = \Waboot\functions\get_option("woocommerce_title_position") == "top" && (bool) \Waboot\functions\get_option("woocommerce_display_title");
		}
	}elseif(\is_shop()){
		if($current_title_position == "top"){
			$is_visible = \Waboot\functions\get_option("woocommerce_title_position") == "top" && (bool) \Waboot\functions\get_option("blog_display_title");
		}
	}
	return $is_visible;
}
add_filter("waboot/entry/title", __NAMESPACE__."\\alter_entry_title", 10, 2);

/**
 * Register WooCommerce Theme Options
 *
 * @param Organizer $orgzr
 */
function register_options($orgzr){
	//It is handled by "Woocommerce Standard" Component
}
add_action("wbf/theme_options/register", __NAMESPACE__.'\\register_options', 14);