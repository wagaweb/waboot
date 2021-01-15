<?php

namespace Waboot\addons\packages\catalog_custom_tables;

if(!class_exists('\Illuminate\Database\Capsule\Manager')){
   return;
}

define('WB_CUSTOM_PRODUCTS_TABLE','woocommerce_wb_products');
define('WB_CUSTOM_CATEGORIES_TABLE', 'woocommerce_wb_categories');
define('WB_CUSTOM_PRODUCTS_CATEGORIES_TABLE', 'woocommerce_wb_products_categories');

require_once __DIR__.'/inc/functions.php';
require_once __DIR__.'/inc/hooks-api.php';

if(defined('WP_CLI')){
    require_once __DIR__.'/cli.php';
}

add_action('init', function (){
    //(new ImportWCProducts())->import();
});