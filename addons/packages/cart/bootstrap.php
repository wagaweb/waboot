<?php

namespace Waboot\addons\packages\cart;

use function Waboot\addons\getAddonDirectory;

remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
//add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' );

/*
 * Display only the product title on cart (and not the variation title)
 */
add_filter('woocommerce_cart_item_name', function ($variation_name, $cart_item, $cart_item_key) {
    return get_the_title($cart_item['product_id']);
}, 10, 3);

/*
 * Append a query string to the "add-to-cart" button. We use this query string to open up the mini cart on page reload.
 */
add_filter('woocommerce_add_to_cart_form_action', function ($permalink) {
    return $permalink . '?addedProduct=true';
}, 10, 1);

/*
 * WooCommerce does not display attributes in cart and mini cart if the product name already contains the attribute.
 * We do not want that.
 * @see: wc_get_formatted_cart_item_data()
 */
add_filter('woocommerce_is_attribute_in_product_name', function($attribute, $name){
    return false;
},10,2);

/*
 * Display minicart
 */
add_action('waboot/layout/page-after', function(){
    require_once getAddonDirectory('cart').'/templates/minicart.php';
},14);
