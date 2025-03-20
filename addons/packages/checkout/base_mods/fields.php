<?php

namespace Waboot\addons\packages\checkout\base_mods;

/*
 * Remove fields from WooCommerce checkout page
 */
add_filter( 'woocommerce_checkout_fields' , function( $fields ) {
    // remove billing fields
    unset($fields['billing']['billing_address_2']);
    return $fields;
},20,1);

/**
 *  Move / ReOrder Fields @ Checkout Page, WooCommerce version 3.0+
 */
add_filter( 'woocommerce_default_address_fields', function($fields) {
    // default priorities:
    // 'first_name' - 10
    // 'last_name' - 20
    // 'company' - 30
    // 'country' - 40
    // 'address_1' - 50
    // 'address_2' - 60
    // 'postcode' - 65
    // 'city' - 70
    // 'state' - 80
    // 'phone' - 100
    // 'email' - 110
    $fields['company']['priority'] = 120;
    return $fields;
},20,1);