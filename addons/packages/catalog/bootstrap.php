<?php

namespace Waboot\addons\packages\catalog;

use Waboot\inc\core\AssetsManager;

use function Waboot\addons\getAddonDirectory;
use function Waboot\addons\getAddonDirectoryURI;

require_once getAddonDirectory('catalog') . '/functions.php';
require_once getAddonDirectory('catalog') . '/hooks.php';

define('TAX_MAP', [
    'categoria' => 'product_cat',
    //'collezione' => 'product_collection',
    //'selezione' => 'product_selection',
]);

add_action('wp_enqueue_scripts', function () {
    $assets = [
        'catalog-script' => [
            'uri' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ?
                getAddonDirectoryURI('catalog') . '/assets/dist/catalog.js' :
                getAddonDirectoryURI('catalog') . '/assets/dist/catalog.min.js',
            'path' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ?
                getAddonDirectory('catalog') . '/assets/dist/catalog.js' :
                getAddonDirectory('catalog') . '/assets/dist/catalog.min.js',
            'type' => 'js',
            'deps' => ['jquery'],
            'in_footer' => true
        ],
        'catalog-style' => [
            'uri' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ?
                getAddonDirectoryURI('catalog') . '/assets/dist/catalog.css' :
                getAddonDirectoryURI('catalog') . '/assets/dist/catalog.min.css',
            'path' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ?
                getAddonDirectory('catalog') . '/assets/dist/catalog.js' :
                getAddonDirectory('catalog') . '/assets/dist/catalog.min.js',
            'type' => 'css'
        ],
    ];
    $am = new AssetsManager(apply_filters('catalog_addon_assets', $assets));
    try {
        $am->enqueue();
    } catch (\Exception $e) {
        trigger_error($e->getMessage(), E_USER_WARNING);
    }
});
