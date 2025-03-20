<?php

namespace Waboot\inc\core\woocommerce\utils;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

function isCustomOrderTableEnabled(): bool {
    try{
        if(!class_exists('\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController')){
            return false;
        }
        $c = wc_get_container()->get(CustomOrdersTableController::class);
        return $c->custom_orders_table_usage_is_enabled();
    }catch (\Exception|\Throwable $e){
        return false;
    }
}