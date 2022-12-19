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
use Waboot\inc\cli\product_import\ImportProducts;
use Waboot\inc\cli\RemoveSalePrices;
use Waboot\inc\cli\SimpleCommand;
use function Waboot\inc\core\Waboot;

require_once get_stylesheet_directory().'/inc/core/cli/CommandLoggerTrait.php';
require_once get_stylesheet_directory().'/inc/core/cli/AbstractCommand.php';
require_once get_stylesheet_directory().'/inc/cli/ParseAndSaveProducts.php';
require_once get_stylesheet_directory().'/inc/cli/FixStockStatuses.php';
require_once get_stylesheet_directory().'/inc/cli/FixPrices.php';
require_once get_stylesheet_directory().'/inc/cli/RemoveSalePrices.php';
require_once get_stylesheet_directory().'/inc/cli/GenerateAttributeListMeta.php';
require_once get_stylesheet_directory().'/inc/cli/ImportPrices.php';
require_once get_stylesheet_directory().'/inc/cli/feeds/GenerateGShoppingFeed.php';
//if(is_file(get_stylesheet_directory().'/inc/cli/product_import/waga-woocommerce-csv-cli-importer/src/index.php')){
//    require_once get_stylesheet_directory().'/inc/cli/product_import/waga-woocommerce-csv-cli-importer/src/index.php';
//}

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
    //Waboot()->registerCommand('simple', SimpleCommand::class,'sm');
    Waboot()->registerCommand('export-products', ExportProducts::class,'wawoo');
    Waboot()->registerCommand('import-products', ImportProducts::class,'wawoo');
    Waboot()->registerCommand('import-prices', ImportPrices::class, 'wawoo');
    Waboot()->registerCommand('parse-and-save-products', ParseAndSaveProducts::class,'wawoo');
    Waboot()->registerCommand('fix-stock-statuses', FixStockStatuses::class,'wawoo');
    Waboot()->registerCommand('fix-prices', FixPrices::class,'wawoo');
    Waboot()->registerCommand('remove-sale-prices', RemoveSalePrices::class,'wawoo');
    Waboot()->registerCommand('gen-attr-list-meta', GenerateAttributeListMeta::class,'wawoo');
    Waboot()->registerCommand('export-orders', ExportOrders::class,'wawoo');
    Waboot()->registerCommand('test:export-orders', ExportOrdersTest::class,'wawoo');
    Waboot()->registerCommand('simulate-orders', OrderSimulator::class,'wawoo');
    Waboot()->registerCommand('feeds:generate-gshopping', GenerateGShoppingFeed::class,'wawoo');
}catch (\Exception $e){}