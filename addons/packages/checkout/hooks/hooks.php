<?php

namespace Waboot\addons\packages\checkout\hooks;

use function Waboot\addons\packages\checkout\hasCustomerCustomBillingFields;
use function Waboot\inc\core\AssetsManager;
use function Waboot\inc\core\defaultShippingAddressNameIsMandatory;
use function Waboot\inc\core\mustDisplayDefaultShippingAddressName;
use function Waboot\inc\getCurrentLanguage;

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
        $mainJsI10nParams['wc_checkout_registration_required'] = $wcRegistrationRequired ? 'true' : 'false';
        $mainJsI10nParams['wc_checkout_registration_enabled'] = $wcRegistrationEnabled ? 'true' : 'false';
        $mainJsI10nParams['must_show_profile_step'] = hasCustomerCustomBillingFields() ? 'true': 'false';
        $mainJsI10nParams['use_proceed_as_guest'] = apply_filters('wawoo/addons/checkout/use_proceed_as_guest', false) ? 'true': 'false';
        $mainJsI10nParams['default_shipping_address_name_is_mandatory'] = defaultShippingAddressNameIsMandatory() ? 'true': 'false';
        $mainJsI10nParams['must_show_default_shipping_address_name'] = mustDisplayDefaultShippingAddressName() ? 'true': 'false';
        $assets['step-checkout-main-js'] = [
            'uri' => get_template_directory_uri() . '/addons/packages/checkout/assets/dist/'.$mainJsFileName,
            'path' => $assetsDir.'/'.$mainJsFileName,
            'type' => 'js',
            'i10n' => [
                'name' => 'stepCheckoutBackendData',
                'params' => $mainJsI10nParams,
            ],
            'in_footer' => true
        ];
        $assets['step-checkout-order-review'] = [
            'uri' => get_template_directory_uri() . '/addons/packages/checkout/assets/order-review-manager.js',
            'path' => get_template_directory() . '/addons/packages/checkout/assets/order-review-manager.js',
            'type' => 'js',
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

/*
 * Force registration enabled
 */
add_filter('woocommerce_checkout_registration_enabled', '__return_true', 99);

/*
 * Force password generation
 */
add_filter('pre_option_'.'woocommerce_registration_generate_password', static function ($value, string $option, $defaultValue) {
    return 'yes';
},10, 3);