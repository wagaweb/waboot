<?php

namespace Waboot\inc;

use Waboot\inc\cli\FixPrices;
use Waboot\inc\cli\FixStockStatuses;
use Waboot\inc\cli\GenerateAttributeListMeta;
use Waboot\inc\cli\ImportPrices;
use Waboot\inc\cli\order_export\ExportOrders;
use Waboot\inc\cli\order_export\ExportOrdersTest;
use Waboot\inc\cli\order_simulator\OrderSimulator;
use Waboot\inc\cli\ParseAndSaveProducts;
use Waboot\inc\cli\feeds\GenerateGShoppingFeed;
use Waboot\inc\cli\product_export\ExportProducts;
use Waboot\inc\cli\RemoveSalePrices;

require_once get_stylesheet_directory().'/inc/core/cli/CommandLoggerTrait.php';
require_once get_stylesheet_directory().'/inc/core/cli/AbstractCommand.php';
require_once get_stylesheet_directory().'/inc/cli/ParseAndSaveProducts.php';
require_once get_stylesheet_directory().'/inc/cli/FixStockStatuses.php';
require_once get_stylesheet_directory().'/inc/cli/FixPrices.php';
require_once get_stylesheet_directory().'/inc/cli/RemoveSalePrices.php';
require_once get_stylesheet_directory().'/inc/cli/GenerateAttributeListMeta.php';
require_once get_stylesheet_directory().'/inc/cli/ImportPrices.php';
require_once get_stylesheet_directory().'/inc/cli/feeds/GenerateGShoppingFeed.php';
if(is_file(get_stylesheet_directory().'/inc/cli/product_import/waga-woocommerce-csv-cli-importer/src/index.php')){
    require_once get_stylesheet_directory().'/inc/cli/product_import/waga-woocommerce-csv-cli-importer/src/index.php';
}

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
    \WP_CLI::add_command('wawoo:fix-prices', FixPrices::class);
    \WP_CLI::add_command('wawoo:remove-sale-prices', RemoveSalePrices::class);
    \WP_CLI::add_command('wawoo:export-orders', ExportOrders::class);
    \WP_CLI::add_command('wawoo:simulate-orders', OrderSimulator::class);
    \WP_CLI::add_command('wawoo:export-products', ExportProducts::class);
    \WP_CLI::add_command('wawoo:gen-attr-list-meta', GenerateAttributeListMeta::class, [
        'shortdesc' => 'Generate `_attribute_list` metadata for each variable product',
        'synopsis' => [
            [
                'type' => 'positional',
                'name' => 'ids',
                'description' => 'The ID of the product to process',
                'optional' => true,
                'repeating' => true,
            ],
        ],
    ]);
    \WP_CLI::add_command('wawoo:test:export-orders', ExportOrdersTest::class);
    \WP_CLI::add_command('wawoo:feeds:generate-gshopping', GenerateGShoppingFeed::class);
    \WP_CLI::add_command('wawoo:import-prices', ImportPrices::class, [
        'shortdesc' => 'Import prices',
        'synopsis' => [
            [
                'type' => 'positional',
                'name' => 'filename',
                'description' => 'The path to the file to import',
                'optional' => false,
                'repeating' => false,
            ],
            [
                'type' => 'positional',
                'name' => 'key',
                'description' => 'The type of key that identify the product (`id`/`sku`)',
                'optional' => false,
                'repeating' => false,
            ],
            [
                'type' => 'positional',
                'name' => 'price',
                'description' => 'The type of price to import (`regular`/`sale`)',
                'optional' => false,
                'repeating' => false,
            ],
            [
                'type' => 'flag',
                'name' => 'dry',
                'description' => 'Dry run',
                'optional' => true,
                'repeating' => false,
            ],
        ],
    ]);
}catch (\Exception $e){}