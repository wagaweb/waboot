<?php

namespace Waboot\addons\packages\checkout_multistep;

use function Waboot\addons\getAddonDirectory;

require_once __DIR__ . '/subscriptions.php';

remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );

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

//add_action( 'woocommerce_review_order_before_payment' , 'woocommerce_checkout_coupon_form' , 10 );
/*
 * Adds coupon template into order review and hide the default one
 */
add_action( 'woocommerce_review_order_before_payment' , function(){
    echo '<div class="woocommerce-form-coupon__wrapper">';
    wc_get_template_part('/checkout/form','coupon');
    ?>
    <script>
        jQuery('.woocommerce-form-coupon-toggle').hide();
        jQuery('button[name="apply_coupon"]').on('click', function (e) {
            e.preventDefault();
            var $checkoutCouponForm = jQuery('form.checkout_coupon');
            if($checkoutCouponForm.length > 0){
                var currentCoupon = jQuery(this).parents('.woocommerce-form-coupon__wrapper').find('input[name="coupon_code"]').val();
                $checkoutCouponForm.find('input[name="coupon_code"]').val(currentCoupon);
                $checkoutCouponForm.submit();
            }
        });
    </script>
    <?php
    echo '</div>';
} , 20 );

add_action('woocommerce_checkout_before_order_review_heading', function () {
    echo '<div class="order-review__wrapper">';
}, 20);

add_action('woocommerce_checkout_after_order_review_heading', function () {
    echo '</div><!-- /.order-review-wrapper -->';
}, 20);

add_action('woocommerce_before_checkout_form', function($checkout){
    if ( $checkout->enable_signup && !is_user_logged_in() ) {
        include getAddonDirectory('checkout_multistep').'/templates/login-step.php';
    }
},20,1);

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

remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
add_action( 'woocommerce_thankyou', function(){
    include getAddonDirectory('checkout_multistep').'/templates/thankyou-order-buttons.php';
}, 10 );

add_action('after_switch_theme', function () {
    createSubscriptionTable();
});

add_action(
    'wp',
    function (): void {
        if (!is_checkout()) {
            return;
        }

        if (is_user_logged_in()) {
            return;
        }

        if(isset($_POST['c-email'])){
            if (wp_verify_nonce($_REQUEST['login-step-nonce'], 'login-step')) {
                $email = $_POST['c-email'] ?? '';
                if (is_email($email)) {
                    $user = get_user_by('email', $email);
                    if ($user === false) {
                        return;
                    }
                    wc_add_notice(__('Esiste già un utente registrato con questa email, fai il login per procedere.', LANG_TEXTDOMAIN), 'error');
                } else {
                    wc_add_notice(__('Esegui il login o inserisci una e-mail corretta per procedere', LANG_TEXTDOMAIN), 'error');
                }
            }

            wc_add_notice(__('Invalid nonce', LANG_TEXTDOMAIN), 'error');
        }

        add_filter('woocommerce_checkout_registration_required', function (): bool {
            return true;
        });

        add_filter('woocommerce_checkout_registration_enabled', function (): bool {
            return false;
        });

        add_filter('woocommerce_checkout_must_be_logged_in_message', function (): string {
            return '';
        });

        add_action(
            'woocommerce_before_checkout_form',
            function (): void {
                include getAddonDirectory('checkout_multistep') . '/templates/login-step.php';
            },
            20
        );
    },
    20,
    1
);

add_action(
    'woocommerce_checkout_get_value',
    function ($value, $field) {

        if (!wp_verify_nonce($_REQUEST['login-step-nonce'] ?? '', 'login-step')) {
            return $value;
        }

        if ($field === 'billing_email' && is_email($_POST['c-email']??'')) {
            return $_POST['c-email'];
        }

        return $value;
    },
    10,
    2
);