<?php

namespace Waboot\woocommerce;

global $woocommerce;

if(!isset($woocommerce)) return;

/*
 * DECLARING HOOKS
 */

//Declare WooCommerce support
add_action( 'after_setup_theme', __NAMESPACE__.'\\add_woocommerce_support' );

//Setup the wrapper
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
add_action('woocommerce_before_main_content', __NAMESPACE__.'\\wrapper_start', 10);
add_action('woocommerce_after_main_content', __NAMESPACE__.'\\wrapper_end', 10);

//Disable the default Woocommerce stylesheet
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

//Disabling actions
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

//Enable the modification of woocommerce product x page
add_filter("loop_shop_per_page",function($cols){
	$n = apply_filters("waboot/woocommerce/loop_shop_per_page/cols",of_get_option("woocommerce_products_per_page",$cols));
	return (int) $n;
}, 20);

//Layout altering:
add_filter("waboot_woocommerce_mainwrap_container_class", "wabppt_set_mainwrap_container_classes"); //todo: questa è una funzione generale che adesso è stata spostata
add_filter("waboot/layout/body_layout/get", __NAMESPACE__."\\alter_body_layout", 90);
add_filter("waboot/layout/get_cols_sizes", __NAMESPACE__."\\alter_col_sizes", 90);
add_filter("wbf/modules/behaviors/get/primary-sidebar-size", __NAMESPACE__."\\primary_sidebar_size_behavior", 999);
add_filter("wbf/modules/behaviors/get/secondary-sidebar-size", __NAMESPACE__."\\secondary_sidebar_size_behavior", 999);

// Theme Options
add_action('init',__NAMESPACE__.'\\hidePriceAndCart');

/*
 * HOOKED FUNCTIONS
 */

function hidePriceAndCart(){
	if((function_exists('is_woocommerce'))) {
		if (of_get_option("waboot_woocommerce_hide_price") == 1) {
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
			remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
		}
		if (of_get_option("waboot_woocommerce_catalog") == 1) {
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
			remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
		}
	}
}

function add_woocommerce_support() {
	add_theme_support( 'woocommerce' );
}

function wrapper_start() {
	?>
	<div id="main-wrap" class="<?php echo apply_filters( 'waboot_woocommerce_mainwrap_container_class', 'content-area col-sm-8' ); ?>">
	<main id="main" class="site-main" role="main">
	<?php
}

function wrapper_end() {
	?>
	</main>
	</div><!-- #main-wrap -->
	<?php
}

function alter_body_layout($layout){
	if(function_exists('is_product_category') && is_product_category()){
		$layout = of_get_option('waboot_woocommerce_sidebar_layout');
	}elseif(function_exists('is_shop') && is_shop()) {
		$layout = of_get_option('waboot_woocommerce_shop_sidebar_layout');
	}
	return $layout;
}

function alter_col_sizes($sizes){
	if((function_exists('is_woocommerce') && is_woocommerce())) {
		global $post;
		$do_calc = false;
		if(is_shop()){
			$sizes = array("main"=>12);
			//Primary size
			$primary_sidebar_width = of_get_option('woocommerce_shop_primary_sidebar_size');
			if(!$primary_sidebar_width) $primary_sidebar_width = 0;
			//Secondary size
			$secondary_sidebar_width = of_get_option('woocommerce_shop_secondary_sidebar_size');
			if(!$secondary_sidebar_width) $secondary_sidebar_width = 0;
			$do_calc = true;
		}elseif(is_product_category()){
			$sizes = array("main"=>12);
			//Primary size
			$primary_sidebar_width = of_get_option('waboot_woocommerce_primary_sidebar_size');
			if(!$primary_sidebar_width) $primary_sidebar_width = 0;
			//Secondary size
			$secondary_sidebar_width = of_get_option('waboot_woocommerce_secondary_sidebar_size');
			if(!$secondary_sidebar_width) $secondary_sidebar_width = 0;
			$do_calc = true;
		}

		if($do_calc){
			if (waboot_body_layout_has_two_sidebars()) {
				//Main size
				$mainwrap_size = 12 - _layout_width_to_int($primary_sidebar_width) - _layout_width_to_int($secondary_sidebar_width);
				$sizes = array("main"=>$mainwrap_size,"primary"=>_layout_width_to_int($primary_sidebar_width),"secondary"=>_layout_width_to_int($secondary_sidebar_width));
			}else{
				if(waboot_get_body_layout() != "full-width"){
					$mainwrap_size = 12 - _layout_width_to_int($primary_sidebar_width);
					$sizes = array("main"=>$mainwrap_size,"primary"=>_layout_width_to_int($primary_sidebar_width));
				}
			}
		}
	}

	return $sizes;
}

function primary_sidebar_size_behavior(\WBF\modules\behaviors\Behavior $b){
	if((function_exists('is_woocommerce') && is_woocommerce())) {
		if(is_shop()){
			$primary_sidebar_width = of_get_option('woocommerce_shop_primary_sidebar_size');
			if(!$primary_sidebar_width) $primary_sidebar_width = 0;
			$b->value = $primary_sidebar_width;
		}elseif(is_product_category()){
			$primary_sidebar_width = of_get_option('waboot_woocommerce_primary_sidebar_size');
			if(!$primary_sidebar_width) $primary_sidebar_width = 0;
			$b->value = $primary_sidebar_width;
		}
	}

	return $b;
}

function secondary_sidebar_size_behavior(\WBF\modules\behaviors\Behavior $b){
	if((function_exists('is_woocommerce') && is_woocommerce())) {
		if(is_shop()){
			$secondary_sidebar_width = of_get_option('woocommerce_shop_secondary_sidebar_size');
			if(!$secondary_sidebar_width) $secondary_sidebar_width = 0;
			$b->value = $secondary_sidebar_width;
		}elseif(is_product_category()){
			$secondary_sidebar_width = of_get_option('waboot_woocommerce_secondary_sidebar_size');
			if(!$secondary_sidebar_width) $secondary_sidebar_width = 0;
			$b->value = $secondary_sidebar_width;
		}
	}

	return $b;
}