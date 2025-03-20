<?php

namespace Waboot\addons\packages\checkout\base_mods;

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