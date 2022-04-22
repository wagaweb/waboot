<?php

define('LANG_TEXTDOMAIN', 'waboot');
define('TAX_MAP', [
    'categoria' => 'product_cat',
    //'collezione' => 'product_collection',
    //'selezione' => 'product_selection',
]);

require_once get_template_directory().'/vendor/autoload.php';
require_once get_template_directory().'/inc/bootstrap.php';

\Waboot\inc\initWaboot();

/*
 * Additional files
 */
//Loads additional files:
$additionalDeps = [
    'inc/woocommerce-helpers.php',
    'inc/catalog-functions.php',
    'inc/hooks/woocommerce.php',
    'inc/hooks/woocommerce-endpoints.php',
    'inc/hooks/catalog.php',
    'inc/cli.php',
];
\Waboot\inc\core\safeRequireFiles($additionalDeps);

/*
 * Addons
 */
add_filter('waboot/addons/disabled', function(){
    return [
        //'star_rating'
    ];
});
\Waboot\inc\loadAddons();
