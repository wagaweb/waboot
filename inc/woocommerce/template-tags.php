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