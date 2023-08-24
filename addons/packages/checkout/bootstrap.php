<?php

namespace Waboot\addons\packages\checkout;

use function Waboot\addons\getAddonDirectory;
use function Waboot\inc\core\AssetsManager;

require_once 'backend-hooks.php';

//remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );

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
    //echo '<div class="woocommerce-form-coupon__wrapper">';
    //wc_get_template_part('/checkout/form','coupon');
    ?>
<!--    <script>-->
<!--        jQuery('.woocommerce-form-coupon-toggle').hide();-->
<!--        jQuery('button[name="apply_coupon"]').on('click', function (e) {-->
<!--            e.preventDefault();-->
<!--            var $checkoutCouponForm = jQuery('form.checkout_coupon');-->
<!--            if($checkoutCouponForm.length > 0){-->
<!--                var currentCoupon = jQuery(this).parents('.woocommerce-form-coupon__wrapper').find('input[name="coupon_code"]').val();-->
<!--                $checkoutCouponForm.find('input[name="coupon_code"]').val(currentCoupon);-->
<!--                $checkoutCouponForm.submit();-->
<!--            }-->
<!--        });-->
<!--    </script>-->
    <?php
    //echo '</div>';
} , 20 );

add_action('woocommerce_checkout_before_order_review_heading', function () {
    //echo '<div class="order-review__wrapper">';
}, 20);

add_action('woocommerce_checkout_after_order_review_heading', function () {
    //echo '</div><!-- /.order-review-wrapper -->';
}, 20);


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
    include getAddonDirectory('checkout').'/templates/thankyou-order-buttons.php';
}, 10 );

/*
 * Step Checkout
 */

add_action( 'woocommerce_checkout_init', function() {
    //remove_action( 'woocommerce_checkout_billing', array( WC()->checkout(), 'checkout_form_billing' ) );
});

add_action('wp_enqueue_scripts', static function(){
    try{
        $assets = [];
        $assetsDir = get_template_directory() . '/addons/packages/checkout/assets/dist/';
        $jsFiles = glob($assetsDir.'/index-*.js');
        if(!\is_array($jsFiles) || empty($jsFiles)){
            return;
        }
        $mainJsFilePath = array_shift($jsFiles);
        $mainJsFileName = basename($mainJsFilePath);
        $assets['step-checkout-main-js'] = [
            'uri' => get_template_directory_uri() . '/addons/packages/checkout/assets/dist/'.$mainJsFileName,
            'path' => $assetsDir.'/'.$mainJsFileName,
            'type' => 'js',
            'i10n' => [
                'name' => 'stepCheckoutBackendData',
                'params' => [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('step-checkout-request-data'),
                ]
            ],
            'in_footer' => true
        ];
        $cssFiles = glob($assetsDir.'/index-*.css');
        if(\is_array($cssFiles) && !empty($cssFiles)){
            $mainCssFilePath = array_shift($cssFiles);
            $mainCssFileName = basename($mainCssFilePath);
            $assets['step-checkout-main-css'] = [
                'uri' => get_template_directory_uri() . '/addons/packages/checkout/assets/dist/'.$mainCssFileName,
                'path' => $assetsDir.'/'.$mainCssFileName,
                'type' => 'css'
            ];
        }
        AssetsManager()->addAssets($assets);
        AssetsManager()->enqueue();
    }catch (\Exception $e){
        trigger_error($e->getMessage(),E_USER_WARNING);
    }
});

add_filter('script_loader_tag', static function($tag, $handle, $src){
    if('step-checkout-main-js' !== $handle){
        return $tag;
    }
    $tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
    return $tag;
},10,3);

add_action('woocommerce_before_checkout_form', function($checkout){
    ?>
    <div id="woocommerce-checkout-steps-app">
    </div>
    <?php
},20,1);

add_action('woocommerce_before_checkout_form', function($checkout){
    ?>
    <div id="original-form-wrapper" style="margin-top: 60px;">
    <?php
},20,1);

add_action('woocommerce_after_checkout_form', function($checkout){
    ?>
    </div><!-- #original-form-wrapper -->
    <?php
},99,1);