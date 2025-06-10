<?php

namespace Waboot\addons\packages\checkout\step_checkout_base;

use function Waboot\addons\getAddonDirectory;

require_once 'hooks/coupons.php';
require_once '../hiphop/step-checkout-base-hooks.php';

remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );

add_action('woocommerce_before_checkout_form', function($checkout){
    if ( $checkout->enable_signup && !is_user_logged_in() ) {
        include getAddonDirectory('checkout').'/templates/login-step.php';
    }
},20,1);

add_action('woocommerce_checkout_before_order_review_heading', function () {
    echo '<div class="order-review__wrapper">';
}, 20);

add_action('woocommerce_checkout_after_order_review_heading', function () {
    echo '</div><!-- /.order-review-wrapper -->';
}, 20);