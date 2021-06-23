<?php

namespace Waboot\inc\woocommerce\endpoints;

if(!\function_exists('is_woocommerce')){
    return; //Do not load any of the following if WooCommerce is not enabled
}

function getMiniCartData(){
    $miniCartData = \Waboot\inc\getMiniCartData();
    wp_send_json_success($miniCartData);
}

add_action('wp_ajax_get_mini_cart_data', __NAMESPACE__.'\getMiniCartData');
add_action('wp_ajax_nopriv_get_mini_cart_data',  __NAMESPACE__.'\getMiniCartData');