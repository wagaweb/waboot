<?php

namespace Waboot\addons\packages\checkout\hooks;

use function Waboot\addons\packages\checkout\printCustomCouponWrapper;
use function Waboot\addons\packages\checkout\printCustomCouponWrapperJS;

/*
 * rename the coupon field on the checkout page
 */
add_filter( 'gettext', function($translated_text, $text, $domain){
    switch ( $translated_text ) {
        case 'Apply coupon' :
            $translated_text = __( 'Apply', 'woocommerce' );
            break;
        case 'Applica codice promozionale' :
            $translated_text = __( 'Applica', 'woocommerce' );
            break;
    }
    return $translated_text;
}, 20, 3 );

add_action('woocommerce_checkout_before_terms_and_conditions', function () {
    printCustomCouponWrapper();
    printCustomCouponWrapperJS();
}, 20);