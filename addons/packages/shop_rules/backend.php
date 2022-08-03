<?php

namespace Waboot\addons\packages\shop_rules;

use Waboot\inc\core\AssetsManager;
use function Waboot\addons\getAddonDirectory;
use function Waboot\addons\getAddonDirectoryURI;

add_action('admin_enqueue_scripts', static function (){
    $am = new AssetsManager([
        'shop-rules-admin-js' => [
            'uri' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ?
                getAddonDirectoryURI('shop_rules') . '/assets/dist/js/shop-rules-admin.js' :
                getAddonDirectoryURI('shop_rules') . '/assets/dist/js/shop-rules-admin.min.js',
            'path' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ?
                getAddonDirectory('shop_rules') . '/assets/dist/js/shop-rules-admin.js' :
                getAddonDirectory('shop_rules') . '/assets/dist/js/shop-rules-admin.min.js',
            'type' => 'js',
            'i10n' => [
                'name' => 'shopRulesData',
                'params' => [
                    'ajax_url' => admin_url('admin-ajax.php')
                ],
            ],
            'deps' => ['jquery'],
            'in_footer' => true
        ],
        'shop-rules-admin-css' => [
            'uri' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ?
                getAddonDirectoryURI('shop_rules') . '/assets/dist/css/shop-rules-admin.css' :
                getAddonDirectoryURI('shop_rules') . '/assets/dist/css/shop-rules-admin.min.css',
            'path' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ?
                getAddonDirectory('shop_rules') . '/assets/dist/css/shop-rules-admin.css' :
                getAddonDirectory('shop_rules') . '/assets/dist/css/shop-rules-admin.min.css',
            'type' => 'css'
        ],
        'shop-rules-vselect-css' => [
            'uri' => getAddonDirectoryURI('shop_rules') . '/assets/vendor/vue-select/vue-select.css',
            'path' => getAddonDirectory('shop_rules') . '/assets/vendor/vue-select/vue-select.css',
            'type' => 'css'
        ],
        'shop-rules-datetime-css' => [
            'uri' => getAddonDirectoryURI('shop_rules') . '/assets/vendor/vue3-date-time-picker/main.css',
            'path' => getAddonDirectory('shop_rules') . '/assets/vendor/vue3-date-time-picker/main.css',
            'type' => 'css'
        ]
    ]);
    try{
        $am->enqueue();
    }catch (\Exception $e){
        trigger_error($e->getMessage(),E_USER_WARNING);
    }
});

add_action('admin_menu', static function(): void {
    if (!is_admin()) {
        return;
    }
    global $submenu;
    if(!isset($submenu['woocommerce'])) {
        return;
    }
    add_submenu_page(
        'woocommerce',
        __('WaWoo Shop Rules', 'waboot'),
        __('WaWoo Shop Rules', 'waboot'),
        'manage_woocommerce', 'wawoo_shop_rules',
        __NAMESPACE__.'\\renderAdminPage'
    );
});

function renderAdminPage(): void {
    ?>
    <div class="wrap">
        <div id="vue-shop-rules"></div>
    </div>
    <?php
}
