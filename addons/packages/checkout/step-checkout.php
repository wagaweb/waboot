<?php

namespace Waboot\addons\packages\checkout;

use function Waboot\inc\core\AssetsManager;
use function Waboot\inc\getCurrentLanguage;

require_once 'step_checkout/backend-hooks.php';

add_action('wp_enqueue_scripts', static function(){
    try{
        $assets = [];
        $assetsDir = get_template_directory() . '/addons/packages/checkout/step_checkout/assets/dist/';
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
            'current_language' => getCurrentLanguage()
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
        $assets['step-checkout-main-js'] = [
            'uri' => get_template_directory_uri() . '/addons/packages/checkout/step_checkout/assets/dist/'.$mainJsFileName,
            'path' => $assetsDir.'/'.$mainJsFileName,
            'type' => 'js',
            'i10n' => [
                'name' => 'stepCheckoutBackendData',
                'params' => $mainJsI10nParams,
            ],
            'in_footer' => true
        ];
        $cssFiles = glob($assetsDir.'/index-*.css');
        if(\is_array($cssFiles) && !empty($cssFiles)){
            $mainCssFilePath = array_shift($cssFiles);
            $mainCssFileName = basename($mainCssFilePath);
            $assets['step-checkout-main-css'] = [
                'uri' => get_template_directory_uri() . '/addons/packages/checkout/step_checkout/assets/dist/'.$mainCssFileName,
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
    <div id="woocommerce-checkout-steps-app" class="woocommerce-checkout-steps">
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

add_filter('script_loader_tag', static function($tag, $handle, $src){
    if('step-checkout-main-js' !== $handle){
        return $tag;
    }
    $tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
    return $tag;
},10,3);