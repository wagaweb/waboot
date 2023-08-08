<?php

namespace Waboot\addons;

/**
 * @param array $cartItem
 * @param string $cartItemKey
 *
 * @usedby 'cart'
 * @usedby 'mini-cart'
 *
 * @return \WC_Product|FALSE
 */
function getWCProductFromCartData(array $cartItem, $cartItemKey){
    $product = apply_filters('woocommerce_cart_item_product', $cartItem['data'], $cartItem, $cartItemKey);
    if($product instanceof \WC_Product){
        return $product;
    }
    return false;
}