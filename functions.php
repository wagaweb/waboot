<?php

define('LANG_TEXTDOMAIN', 'waboot');

try{
    require_once get_template_directory().'/vendor/autoload.php';
    require_once get_template_directory().'/inc/bootstrap.php';

    \Waboot\inc\initWaboot();

    /*
     * Additional files
     */
    $additionalDeps = [
        'inc/multilanguage-functions.php',
        'inc/woocommerce-helpers.php',
        'inc/hooks/woocommerce/woocommerce.php',
        'inc/hooks/woocommerce/woocommerce-endpoints.php',
        'inc/hooks/woocommerce/woocommerce-listing.php',
        'inc/hooks/woocommerce/woocommerce-product.php',
        'inc/hooks/woocommerce/woocommerce-cart.php',
        'inc/hooks/woocommerce/woocommerce-checkout.php',
        'inc/hooks/woocommerce/woocommerce-search.php',
        'inc/hooks/woocommerce/woocommerce-wishlist.php',
        'inc/hooks/woocommerce/woocommerce-order.php',
        'inc/cli.php',
    ];
    \Waboot\inc\core\safeRequireFiles($additionalDeps);

    /*
     * Addons
     */
    add_filter('waboot/addons/disabled', function(){
        return [
            'checkout_multistep',
            //'star_rating'
        ];
    });
    \Waboot\inc\loadAddons();
}catch (\Throwable $e){
    echo $e->getMessage();
}
