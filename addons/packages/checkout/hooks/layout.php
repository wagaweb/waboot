<?php

namespace Waboot\addons\packages\checkout\hooks;

use function Waboot\addons\getAddonDirectory;

remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );

add_action('woocommerce_before_checkout_form', function($checkout){
    ?>
    <?php do_action('wawoo/addons/checkout/before_checkout_wrapper'); ?>
    <div class="checkout-addon-wrapper">

    <div id="order-review__wrapper" class="woocommerce-checkout-steps__order-review">
        <div class="woocommerce-checkout-steps__order-review-top">
            <h3><?php _e('Order review') ?></h3>
            <button><i class="icon icon-chevron-down"></i></button>

            <strong data-cart-total></strong>
        </div>

        <div class="woocommerce-checkout-steps__loader"><span class="loader"></span></div>

        <div data-order-review-wrapper></div>
    </div>

    <div class="checkout-addon-steps">
    <div id="woocommerce-checkout-steps-app" class="woocommerce-checkout-steps">
    </div>
    <?php do_action('wawoo/addons/checkout/after_checkout_wrapper'); ?>
    <?php
},20,1);

add_action('woocommerce_before_checkout_form', function($checkout){
    ?>
    <div id="original-form-wrapper" class="original-form-wrapper">
    <?php
},20,1);

add_action('woocommerce_after_checkout_form', function($checkout){
    ?>
    </div><!-- #original-form-wrapper -->
    </div><!-- .checkout-addon-steps -->
    </div><!-- .checkout-addon-wrapper -->
    <?php
},99,1);

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

