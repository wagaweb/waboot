<?php

namespace Waboot\inc\woocommerce;

if(!\function_exists('is_woocommerce')){
    return; //Do not load any of the following if WooCommerce is not enabled
}

// Add Quantity Badge in Cart Icon
add_filter( 'woocommerce_widget_cart_item_quantity', function($html, $cart_item, $cart_item_key) {
    //var_dump($cart_item);
    $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
    $product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
    return '<span class="quantity" data-cart-item-quantity="' . $cart_item['quantity'] . '">' . sprintf( '%s &times; %s', $cart_item['quantity'], $product_price ) . '</span>';
}, 10, 3 );
