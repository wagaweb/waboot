<?php

namespace Waboot\inc;

use Waboot\inc\cli\ExportOrders;
use Waboot\inc\cli\ExportOrdersTest;
use Waboot\inc\cli\ExportProducts;
use Waboot\inc\cli\FixStockStatuses;
use Waboot\inc\cli\OrderSimulator;
use Waboot\inc\cli\ParseAndSaveProducts;
use Waboot\inc\cli\feeds\GenerateGShoppingFeed;

require_once get_stylesheet_directory().'/inc/core/cli/CommandLoggerTrait.php';
require_once get_stylesheet_directory().'/inc/core/cli/AbstractCommand.php';
require_once get_stylesheet_directory().'/inc/cli/ParseAndSaveProducts.php';
require_once get_stylesheet_directory().'/inc/cli/FixStockStatuses.php';
require_once get_stylesheet_directory().'/inc/cli/ExportOrders.php';
require_once get_stylesheet_directory().'/inc/cli/ExportOrdersTest.php';
require_once get_stylesheet_directory().'/inc/cli/ExportProducts.php';
require_once get_stylesheet_directory().'/inc/cli/OrderSimulator.php';
require_once get_stylesheet_directory().'/inc/cli/feeds/GenerateGShoppingFeed.php';

add_action('init', static function(){
    /*
     * Test commands here
     */
    //(new FixStockStatuses())->__invoke('',['products' => '12,35']);
    //(new GenerateGShoppingFeed())->__invoke([],['products'=>'34,31,23']);
    //(new ExportProducts())->__invoke([],['manifest' => '/var/www/html/waga/waboot/wp-content/themes/waboot/inc/cli/product_export/manifest-sample.json']);
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
    \WP_CLI::add_command('wawoo:export-orders', ExportOrders::class);
    \WP_CLI::add_command('wawoo:simulate-orders', OrderSimulator::class);
    \WP_CLI::add_command('wawoo:export-products', ExportProducts::class);
    \WP_CLI::add_command('wawoo:test:export-orders', ExportOrdersTest::class);
    \WP_CLI::add_command('wawoo:feeds:generate-gshopping', GenerateGShoppingFeed::class);
}catch (\Exception $e){}