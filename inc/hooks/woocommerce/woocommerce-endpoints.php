<?php

namespace Waboot\inc\woocommerce\endpoints;

if(!\function_exists('is_woocommerce')){
    return; //Do not load any of the following if WooCommerce is not enabled
}

/**
 * WP-Admin ajax endpoint to retrieve mini-cart data
 */
function getMiniCartData(){
    if(function_exists('\Waboot\inc\getMiniCartData')){
        $miniCartData = \Waboot\inc\getMiniCartData();
        wp_send_json_success($miniCartData);
    }
    wp_send_json_success([
        'total_items_qty' => 0,
        'total_different_items_qty' => 0
    ]);
}

add_action('wp_ajax_get_mini_cart_data', __NAMESPACE__.'\getMiniCartData');
add_action('wp_ajax_nopriv_get_mini_cart_data',  __NAMESPACE__.'\getMiniCartData');