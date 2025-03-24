<?php

namespace Waboot\addons\packages\checkout;

use function Waboot\addons\getAddonDirectory;

$deps = [
    'base_mods/coupons.php',
    'base_mods/fields.php',
    'base_mods/layout.php',
    // 'step-checkout-base.php', // Use either base or 'step-checkout.php'
    'step-checkout.php'
];

$deps = array_map(static function($file){
    $file = getAddonDirectory('checkout').'/'.$file;
    return str_replace(get_template_directory(),'',$file);
}, $deps);

\Waboot\inc\core\safeRequireFiles($deps);