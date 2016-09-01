<?php

namespace Waboot\woocommerce;

use WBF\modules\options\Organizer;

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