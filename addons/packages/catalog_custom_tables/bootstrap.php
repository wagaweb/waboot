<?php

namespace Waboot\addons\packages\catalog_custom_tables;

if(!class_exists('\Illuminate\Database\Capsule\Manager')){
   return;
}

define('WB_CUSTOM_PRODUCTS_TABLE','wc_wb_products');
define('WB_CUSTOM_CATEGORIES_TABLE', 'wc_wb_categories');
define('WB_CUSTOM_PRODUCTS_CATEGORIES_TABLE', 'wc_wb_products_categories');

if(defined('WP_CLI')){
    require_once __DIR__.'/vendor/cli.php';
}