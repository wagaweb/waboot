<?php

namespace Waboot\inc;

use Waboot\inc\cli\FixStockStatuses;
use Waboot\inc\cli\ParseAndSaveProducts;
use Waboot\inc\cli\feeds\GenerateGShoppingFeed;

require_once get_stylesheet_directory().'/inc/core/cli/CommandLogger.php';
require_once get_stylesheet_directory().'/inc/core/cli/AbstractCommand.php';
require_once get_stylesheet_directory().'/inc/cli/ParseAndSaveProducts.php';
require_once get_stylesheet_directory().'/inc/cli/FixStockStatuses.php';
require_once get_stylesheet_directory().'/inc/cli/feeds/GenerateGShoppingFeed.php';

add_action('init', function(){
    /*
     * Test commands here
     */
    //(new FixStockStatuses())->__invoke('',['products' => '12,35']);
    //(new GenerateGShoppingFeed())->__invoke([],['products'=>'34,31,23']);
});

if (!defined('WP_CLI')) {
    return;
}

try{
    /*
     * Add commands here
     */
    \WP_CLI::add_command('wawoo:parse-and-save-products', ParseAndSaveProducts::class);
    \WP_CLI::add_command('wawoo:fix-stock-statuses', FixStockStatuses::class);
    \WP_CLI::add_command('wawoo:feeds:generate-gshopping', GenerateGShoppingFeed::class);
}catch (\Exception $e){}