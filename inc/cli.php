<?php

namespace Waboot\inc;

use Waboot\inc\cli\FixPrices;
use Waboot\inc\cli\FixStockStatuses;
use Waboot\inc\cli\ImportPrices;
use Waboot\inc\cli\ImportProductImages;
use Waboot\inc\cli\order_export\ExportOrders;
use Waboot\inc\cli\order_export\ExportOrdersTest;
use Waboot\inc\cli\order_simulator\OrderSimulator;
use Waboot\inc\cli\ParseAndSaveProducts;
use Waboot\inc\cli\feeds\GenerateGShoppingFeed;
use Waboot\inc\cli\product_export\ExportProducts;
use Waboot\inc\cli\product_import\BuildTermsReferenceTable;
use Waboot\inc\cli\product_import\ImportProducts;
use Waboot\inc\cli\product_import\ImportTerms;
use Waboot\inc\cli\RemoveSalePrices;
use function Waboot\inc\core\helpers\registerCommand;

require_once get_stylesheet_directory().'/inc/cli/hooks.php';
require_once get_stylesheet_directory().'/inc/core/cli/CommandLoggerTrait.php';
require_once get_stylesheet_directory().'/inc/core/cli/AbstractCommand.php';
require_once get_stylesheet_directory().'/inc/cli/ParseAndSaveProducts.php';
require_once get_stylesheet_directory().'/inc/cli/FixStockStatuses.php';
require_once get_stylesheet_directory().'/inc/cli/FixPrices.php';
require_once get_stylesheet_directory().'/inc/cli/RemoveSalePrices.php';
require_once get_stylesheet_directory().'/inc/cli/ImportPrices.php';
require_once get_stylesheet_directory().'/inc/cli/feeds/GenerateGShoppingFeed.php';
//if(is_file(get_stylesheet_directory().'/inc/cli/product_import/waga-woocommerce-csv-cli-importer/src/index.php')){
//    require_once get_stylesheet_directory().'/inc/cli/product_import/waga-woocommerce-csv-cli-importer/src/index.php';
//}

add_action('init', static function(){
    /*
     * Test commands here
     */
    //(new BuildTermsReferenceTable())->__invoke([],['file' => 'terms.csv','delimiter' => ';']);
    //(new ImportTerms())->__invoke([],['taxonomies' => 'BraceletStrap']);
    //(new FixStockStatuses())->__invoke('',['products' => '12,35']);
    //(new GenerateGShoppingFeed())->__invoke([],['products'=>'34,31,23']);
    //(new ExportProducts())->__invoke([],['manifest' => '/var/www/html/waga/waboot/wp-content/themes/waboot/inc/cli/product_export/manifest-sample.json']);
    //(new ImportProducts())->__invoke([],['dry-run' => true, 'file' => 'aluser20221202.csv', 'manifest' => '/var/www/html/wp-content/import-products-manifest.json']);
    //(new ImportProducts())->__invoke([],['dry-run' => true, 'delimiter' => ';', 'file' => 'profumum.csv', 'manifest' => '/var/www/html/wp-content/eterea-products-manifest.json']);
});

if (!defined('WP_CLI')) {
    return;
}

try{
    /*
     * Add commands here
     */
    registerCommand('export-products', ExportProducts::class,'wawoo');
    registerCommand('import-products', ImportProducts::class,'wawoo');
    registerCommand('import-product-images', ImportProductImages::class,'wawoo');
    registerCommand('import-prices', ImportPrices::class, 'wawoo');
    registerCommand('build-term-reference-table', BuildTermsReferenceTable::class, 'wawoo');
    registerCommand('parse-and-save-products', ParseAndSaveProducts::class,'wawoo');
    registerCommand('fix-stock-statuses', FixStockStatuses::class,'wawoo');
    registerCommand('fix-prices', FixPrices::class,'wawoo');
    registerCommand('remove-sale-prices', RemoveSalePrices::class,'wawoo');
    registerCommand('export-orders', ExportOrders::class,'wawoo');
    registerCommand('test:export-orders', ExportOrdersTest::class,'wawoo');
    registerCommand('simulate-orders', OrderSimulator::class,'wawoo');
    registerCommand('feeds:generate-gshopping', GenerateGShoppingFeed::class,'wawoo');
}catch (\Exception $e){}