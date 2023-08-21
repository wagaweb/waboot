<?php

namespace Waboot\inc\woocommerce;

if(!\function_exists('is_woocommerce')){
    return; //Do not load any of the following if WooCommerce is not enabled
}

// Hides ALL shipping rates in ALL zones when Free Shipping is available
add_filter( 'woocommerce_package_rates', function( $rates ) {
    $free = array();
    foreach ( $rates as $rate_id => $rate ) {
        if ( 'free_shipping' === $rate->method_id ) {
            $free[ $rate_id ] = $rate;
            break;
        }
    }
    return ! empty( $free ) ? $free : $rates;
}, 100 );

// Adds Payment Request button (Apple Pay) on the Checkout page
add_filter('wc_stripe_show_payment_request_on_checkout', '__return_true');
