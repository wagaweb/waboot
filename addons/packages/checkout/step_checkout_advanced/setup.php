<?php

namespace Waboot\addons\packages\checkout\step_checkout_advanced;

use Waboot\inc\core\woocommerce\addresses\ShippingAddress;
use function Waboot\addons\packages\checkout\hasCustomerCustomBillingFields;
use function Waboot\inc\core\AssetsManager;
use function Waboot\inc\getCurrentLanguage;

require_once 'hooks/coupons.php';
require_once 'hooks/backend.php';

add_action('wp_enqueue_scripts', static function(){
    try{
        $assets = [];
        $assetsDir = get_template_directory() . '/addons/packages/checkout/step_checkout_advanced/assets/dist/';
        $jsFiles = glob($assetsDir.'/index-*.js');
        if(!\is_array($jsFiles) || empty($jsFiles)){
            return;
        }
        $mainJsFilePath = array_shift($jsFiles);
        $mainJsFileName = basename($mainJsFilePath);
        $mainJsI10nParams = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('step-checkout-request-data'),
            'locale' => get_locale(),
            'current_language' => getCurrentLanguage(),
            /*
             * This controls which shipping address is used by default.
             * shipping = Default to customer shipping address (this enables "Ship to a different address?")
             * billing = Default to customer billing address (this enables "Ship to a different address?")
             * billing_only = Force shipping to the customer billing address (this HIDE "Ship to a different address?")
             */
            'woocommerce_ship_to_destination' => get_option('woocommerce_ship_to_destination')
        ];
        try{
            $wcRegistrationRequired = WC()->checkout()->is_registration_required();
            $wcRegistrationEnabled = WC()->checkout()->is_registration_enabled();
        }catch (\Exception|\Throwable $e){
            $wcRegistrationRequired = false;
            $wcRegistrationEnabled = false;
        }
        $mainJsI10nParams['wc_checkout_registration_required'] = $wcRegistrationRequired;
        $mainJsI10nParams['wc_checkout_registration_enabled'] = $wcRegistrationEnabled;
        $mainJsI10nParams['must_show_profile_step'] = hasCustomerCustomBillingFields();
        $assets['step-checkout-main-js'] = [
            'uri' => get_template_directory_uri() . '/addons/packages/checkout/step_checkout_advanced/assets/dist/'.$mainJsFileName,
            'path' => $assetsDir.'/'.$mainJsFileName,
            'type' => 'js',
            'i10n' => [
                'name' => 'stepCheckoutBackendData',
                'params' => $mainJsI10nParams,
            ],
            'in_footer' => true
        ];
        $assets['step-checkout-order-review'] = [
            'uri' => get_template_directory_uri() . '/addons/packages/checkout/step_checkout_advanced/assets/order-review-manager.js',
            'path' => get_template_directory() . '/addons/packages/checkout/step_checkout_advanced/assets/order-review-manager.js',
            'type' => 'js',
            'in_footer' => true
        ];
        $cssFiles = glob($assetsDir.'/index-*.css');
        if(\is_array($cssFiles) && !empty($cssFiles)){
            $mainCssFilePath = array_shift($cssFiles);
            $mainCssFileName = basename($mainCssFilePath);
            $assets['step-checkout-main-css'] = [
                'uri' => get_template_directory_uri() . '/addons/packages/checkout/step_checkout_advanced/assets/dist/'.$mainCssFileName,
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

add_filter('script_loader_tag', static function($tag, $handle, $src){
    if('step-checkout-main-js' !== $handle){
        return $tag;
    }
    $tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
    return $tag;
},10,3);

/*
 * Force password generation
 */
add_filter('pre_option_'.'woocommerce_registration_generate_password', static function ($value, string $option, $defaultValue) {
    return 'yes';
},10, 3);

/*
 * Force registration enabled
 */
add_filter('woocommerce_checkout_registration_enabled', '__return_true', 99);

/*
 * Save billing phone to shipping
 */
add_filter('wawoo/multiple_addresses/shipping_address_repository/create_from_posted_data', static function(ShippingAddress $address){
    if(!empty($_POST['billing_phone']) && !isset($_POST['shipping_phone'])){
        $address->setPhone($_POST['billing_phone']);
    }
    return $address;
});