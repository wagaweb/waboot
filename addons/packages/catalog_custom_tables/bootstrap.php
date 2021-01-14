<?php

namespace Waboot\addons\packages\catalog_custom_tables;

use Waboot\addons\packages\catalog_custom_tables\cli\ImportWCProducts;
use Waboot\addons\packages\catalog_custom_tables\cli\SetupDB;

if(!class_exists('\Illuminate\Database\Capsule\Manager')){
   return;
}

define('WB_CUSTOM_PRODUCTS_TABLE','woocommerce_wb_products');
define('WB_CUSTOM_CATEGORIES_TABLE', 'woocommerce_wb_categories');
define('WB_CUSTOM_PRODUCTS_CATEGORIES_TABLE', 'woocommerce_wb_products_categories');

if(defined('WP_CLI')){
    require_once __DIR__.'/cli.php';
}

add_action('init', function (){
    (new ImportWCProducts())->import();
});