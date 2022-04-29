<?php

namespace Waboot\inc\woocommerce;

if(!\function_exists('is_woocommerce')){
    return; //Do not load any of the following if WooCommerce is not enabled
}

// JVM WooCommerce Wishlist
add_action('init', function () {
    if (function_exists('jvm_woocommerce_add_to_wishlist')) {
        remove_action('woocommerce_after_shop_loop_item', 'jvm_woocommerce_add_to_wishlist', 15);
        add_action('woocommerce_before_shop_loop_item_title', 'jvm_woocommerce_add_to_wishlist', 13);
        add_filter('jvm_add_to_wishlist_class', function () {
            if (is_singular('product')) {
                return  ' jvm_add_to_wishlist single-add-to-wishlist';
            }
            return ' jvm_add_to_wishlist add-to-wishlist';
        });
    }
});
