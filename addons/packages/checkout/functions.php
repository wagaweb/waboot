<?php

namespace Waboot\addons\packages\checkout;

/**
 * Adds coupon template into order review and hide the default one
 * The default coupon is displayed at "woocommerce_before_checkout_form"
 * We want a different template for coupon, but don't want to edit the original one,
 * so we add this hook in which we display another coupon and hide the original one.
 */
function printCustomCouponWrapper(string $wrapperClass = 'woocommerce-form-coupon__wrapper'): void {
    ?>
    <div class="<?php echo $wrapperClass ?>">
        <!-- BEGIN - mutuated from: wp-content/plugins/woocommerce/templates/checkout/form-coupon.php -->
        <p><?php esc_html_e( 'If you have a coupon code, please apply it below.', 'woocommerce' ); ?></p>
        <p class="form-row form-row-first">
            <label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
            <input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" id="coupon_code" value="" />
        </p>
        <p class="form-row form-row-last">
            <button type="submit" class="button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
        </p>
        <div class="clear"></div>
        <!-- END mutuated from: wp-content/plugins/woocommerce/templates/checkout/form-coupon.php -->
    </div>
    <?php
}

function printCustomCouponWrapperJS(): void {
    ?>
    <script>
        // Hide original coupon
        jQuery('.woocommerce-form-coupon-toggle').hide();
        var $customCouponWrapper = jQuery('.woocommerce-form-coupon__wrapper');
        // When our "apply_coupon" is clicked...
        $customCouponWrapper.find('button[name="apply_coupon"]').on('click', function (e) {
            console.log('[waboot] Custom coupon button clicked');
            e.preventDefault();
            // Grab the coupon form
            var $checkoutCouponForm = jQuery('form.checkout_coupon');
            console.log($checkoutCouponForm);
            if($checkoutCouponForm.length > 0){
                console.log('[waboot] -> propagate to original');
                // Grab our coupon value
                var currentCoupon = jQuery(this).parents('.woocommerce-form-coupon__wrapper').find('input[name="coupon_code"]').val();
                if(currentCoupon !== ''){
                    // Put it inside the original coupon
                    $checkoutCouponForm.find('input[name="coupon_code"]').val(currentCoupon);
                    // Submit the original coupon
                    $checkoutCouponForm.submit();
                }
            }
        });
    </script>
    <?php
}