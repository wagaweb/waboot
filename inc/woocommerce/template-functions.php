<?php

namespace Waboot\woocommerce;

/**
 * Gets the shop page title
 *
 * @return bool|string
 */
function get_shop_page_title(){
	if(!function_exists("woocommerce_get_page_id")) return false;
	$shop_page_id = wc_get_page_id('shop');
	if($shop_page_id){
		$page_title = get_the_title( $shop_page_id );
		return $page_title;
	}else{
		return false;
	}
}