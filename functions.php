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
	'inc/multilanguage-functions.php',
	'inc/hooks/gravityform/hooks.php',
    //'inc/hooks/woocommerce.php',
	'inc/cli.php',
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
\Waboot\inc\loadAddons();
*/
