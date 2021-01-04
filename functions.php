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
    //'inc/hooks/woocommerce.php',
];
\Waboot\inc\core\safeRequireFiles($additionalDeps);

/*
 * Addons
 */
/*
add_filter('waboot/addons/disabled', function(){
    return [
        'star_rating'
    ];
});
*/
\Waboot\inc\loadAddons();
