<?php

namespace Waboot\addons\packages\checkout\hooks;

use function Waboot\addons\getAddonDirectory;

remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
add_action( 'woocommerce_thankyou', function(){
    include getAddonDirectory('checkout').'/templates/thankyou-order-buttons.php';
}, 10 );

/*
 * Display product image in order review
 * @see: wp-content/plugins/woocommerce/templates/checkout/review-order.php
 */
add_filter('woocommerce_cart_item_name', function($name, $cart_item, $cart_item_key){
    if (!is_checkout()){
        return $name;
    }
    /**
     * @var \WC_Product $product
     */
    $product = $cart_item['data'];
    $thumbnail = $product->get_image(['50', '50'], ['class' => 'alignleft']);
    return $thumbnail . $name;
}, 11, 3);

