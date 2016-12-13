<?php

namespace Waboot\woocommerce;

/**
 * waboot_woocommerce_is_shop - Returns true when the specified page is the shop page.
 *
 * @param $id
 *
 * @return bool
 */
function is_shop($id){
	return $id == wc_get_page_id( 'shop' );
}

/**
 * Prints out the shop title
 *
 * @param string $prefix
 * @param string $suffix
 * @param bool $display
 *
 * @return string
 * 
 * @not-used-yet
 */
function shop_title($prefix = "", $suffix = "", $display = true) {
	if (of_get_option('blogpage_displaytitle') == "1") { //todo: add this
		$title = $prefix . apply_filters('waboot_index_title_text', get_shop_page_title()) . $suffix;
	} else {
		$title = "";
	}

	if ($display) {
		echo $title;
	}
	return $title;
}

/**
 * Format WC archives page title
 *
 * @param string $prefix
 * @param string $suffix
 * @param bool|true $display
 *
 * @return string|void
 *
 * @not-used-yet
 */
function archive_page_title($prefix = "", $suffix = "", $display = true){
	if (of_get_option('woocommerce_displaytitle') == "1") { //todo: add this
		$output = \Waboot\functions\get_archive_page_title();
		$output = $prefix.$output.$suffix;
	}else{
		$output = "";
	}
	if($display){
		echo $output;
	}
	return $output;
}