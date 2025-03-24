<?php

namespace Waboot\addons\packages\checkout;

/*
 * Funzionamento:
 * - Il form originale viene wrappato in #original-form-wrapper
 * - L'app VUE viene renderizzata in #woocommerce-checkout-steps-app
 * - L'app VUE compila il form originale (tramite i watch() nello store checkoutData)
 * - Arrivati allo step di pagamento (gestito dal componente Pay), il form originale viene spostato all'interno
 *   dell'app VUE. L'unica parte visibile del form originale è la parte del pagamento.
 */

use function Waboot\inc\core\AssetsManager;
use function Waboot\inc\getCurrentLanguage;

require_once 'step_chekout/backend-hooks.php';

add_action('wp_enqueue_scripts', static function(){
    try{
        $assets = [];
        $assetsDir = get_template_directory() . '/addons/packages/checkout/js/dist/assets/';
        $jsFiles = glob($assetsDir.'/index-*.js');
        if(!\is_array($jsFiles) || empty($jsFiles)){
            return;
        }
        $mainJsFilePath = array_shift($jsFiles);
        $mainJsFileName = basename($mainJsFilePath);
        $assets['step-checkout-main-js'] = [
            'uri' => get_template_directory_uri() . '/addons/packages/checkout/js/dist/assets/'.$mainJsFileName,
            'path' => $assetsDir.'/'.$mainJsFileName,
            'type' => 'js',
            'i10n' => [
                'name' => 'stepCheckoutBackendData',
                'params' => [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'api_url' => get_bloginfo('wpurl').'/wp-json/npk/v1',
                    'nonce' => wp_create_nonce('step-checkout-request-data'),
                    'locale' => get_locale(),
                    'current_language' => getCurrentLanguage()
                ]
            ],
            'in_footer' => true
        ];
        $cssFiles = glob($assetsDir.'/index-*.css');
        if(\is_array($cssFiles) && !empty($cssFiles)){
            $mainCssFilePath = array_shift($cssFiles);
            $mainCssFileName = basename($mainCssFilePath);
            $assets['step-checkout-main-css'] = [
                'uri' => get_template_directory_uri() . '/addons/packages/checkout/js/dist/assets/'.$mainCssFileName,
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