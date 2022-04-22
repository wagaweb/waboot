<?php

namespace Waboot\addons\packages\catalog;

use function Waboot\addons\getAddonDirectory;

require_once getAddonDirectory('catalog') . '/functions.php';
require_once getAddonDirectory('catalog') . '/hooks.php';

define('TAX_MAP', [
    'categoria' => 'product_cat',
    //'collezione' => 'product_collection',
    //'selezione' => 'product_selection',
]);
