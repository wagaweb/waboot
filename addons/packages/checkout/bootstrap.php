<?php

namespace Waboot\addons\packages\checkout;

use function Waboot\addons\getAddonDirectory;

/*
 * This addons requires a page with the
 * [woocommerce_checkout] shortcode
 *
 * On new installs, the default checkout page is a block page, this
 * function checks that: CartCheckoutUtils::is_checkout_block_default()
 * This block page controls the visibility of phone, company and address_2 field
 * with options:
 * woocommerce_checkout_company_field
 * woocommerce_checkout_address_2_field
 * woocommerce_checkout_phone_field
 * in wp-content/plugins/woocommerce/src/Blocks/Utils/CartCheckoutUtils.php
 */

/*
 * WooCommerce manage fields on country basis:
 * wp-content/plugins/woocommerce/includes/class-wc-checkout.php -> get_checkout_fields()
 * -> initialize_checkout_fields()
 * -> WC()->countries->get_address_fields($country)
 * wp-content/plugins/woocommerce/includes/class-wc-countries.php -> get_address_fields($country)
 * -> get_default_address_fields()
 * -> get_country_locale() // This contains local modifications to default addresses
 * -> apply_filters( 'woocommerce_' . $type . 'fields', $address_fields, $country );
 */

$deps = [
    'functions.php',
    'hooks/fields.php',
    'hooks/layout.php',
    'hiphop/subscriptions.php',
    //'step-checkout-base.php', // Use either base or 'step-checkout.php'
    'step-checkout.php'
];

$deps = array_map(static function($file){
    $file = getAddonDirectory('checkout').'/'.$file;
    return str_replace(get_template_directory(),'',$file);
}, $deps);

\Waboot\inc\core\safeRequireFiles($deps);