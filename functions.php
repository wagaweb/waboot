<?php

define('LANG_TEXTDOMAIN', 'waboot');

require_once get_template_directory().'/vendor/autoload.php';
require_once get_template_directory().'/inc/bootstrap.php';

\Waboot\inc\initWaboot();

/*
 * Additional files
 */
//Loads additional files:
$additionalDeps = [
    'inc/woocommerce-helpers.php',
    'inc/hooks/woocommerce/woocommerce.php',
    'inc/hooks/woocommerce/woocommerce-endpoints.php',
    'inc/hooks/woocommerce/woocommerce-listing.php',
    'inc/hooks/woocommerce/woocommerce-product.php',
    'inc/hooks/woocommerce/woocommerce-cart.php',
    'inc/hooks/woocommerce/woocommerce-checkout.php',
    'inc/hooks/woocommerce/woocommerce-search.php',
    'inc/hooks/woocommerce/woocommerce-wishlist.php',
    'inc/cli.php',
];
\Waboot\inc\core\safeRequireFiles($additionalDeps);

/*
 * Addons
 */
add_filter('waboot/addons/disabled', function(){
    return [
        //'star_rating'
        'shop_rules' //{SHOP RULES}
    ];
});
\Waboot\inc\loadAddons();
