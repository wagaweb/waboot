<?php

namespace Waboot\inc;

use waboot\inc\cli\feeds\GenerateFacebookFeed;
use Waboot\inc\cli\feeds\GeneratePinterestFeed;
use waboot\inc\cli\feeds\GenerateTikTokFeed;
use Waboot\inc\cli\FixPrices;
use Waboot\inc\cli\FixStockStatuses;
use Waboot\inc\cli\GenerateOrderStatsTable;
use Waboot\inc\cli\GenerateSiteStatFile;
use Waboot\inc\cli\ImportPrices;
use Waboot\inc\cli\ImportProductImages;
use Waboot\inc\cli\order_export\ExportOrders;
use Waboot\inc\cli\order_export\ExportOrdersTest;
use Waboot\inc\cli\order_simulator\OrderSimulator;
use Waboot\inc\cli\ParseAndSaveProducts;
use Waboot\inc\cli\feeds\GenerateGShoppingFeed;
use Waboot\inc\cli\product_export\ExportProducts;
use Waboot\inc\cli\product_import\ImportProducts;
use Waboot\inc\cli\PublishMissingArticles;
use Waboot\inc\cli\RemoveSalePrices;
use function Waboot\inc\core\helpers\registerCommand;

require_once get_stylesheet_directory().'/inc/cli/hooks.php';
require_once get_stylesheet_directory().'/inc/cli/feeds/GenerateGShoppingFeed.php';
require_once get_stylesheet_directory().'/inc/cli/feeds/GenerateFacebookFeed.php';
require_once get_stylesheet_directory().'/inc/cli/feeds/GeneratePinterestFeed.php';
require_once get_stylesheet_directory().'/inc/cli/feeds/GenerateTikTokFeed.php';
//if(is_file(get_stylesheet_directory().'/inc/cli/product_import/waga-woocommerce-csv-cli-importer/src/index.php')){
//    require_once get_stylesheet_directory().'/inc/cli/product_import/waga-woocommerce-csv-cli-importer/src/index.php';
//}

/*
 * Filter out unwanted taxonomies
 */
add_filter('wawoo/cli/gen-stat-table/taxonomies', static function (array $taxonomies) {
    return array_filter($taxonomies, static function (string $taxonomy){
        return !\in_array($taxonomy,['post_translations','product_visibility','product_shipping_class']);
    });
},10,1);

add_action('init', static function(){
    /*
     * Test commands here
     */
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
    // Stats
    registerCommand('generate-site-stat-file', GenerateSiteStatFile::class, 'waboot');
    // Products
    registerCommand('export-products', ExportProducts::class,'wawoo');
    registerCommand('import-products', ImportProducts::class,'wawoo');
    registerCommand('import-product-images', ImportProductImages::class,'wawoo');
    registerCommand('import-prices', ImportPrices::class, 'wawoo');
    // Orders
    registerCommand('export-orders', ExportOrders::class,'wawoo');
    registerCommand('test:export-orders', ExportOrdersTest::class,'wawoo');
    registerCommand('simulate-orders', OrderSimulator::class,'wawoo');
    registerCommand('gen-order-stats-table', GenerateOrderStatsTable::class, 'wawoo');
    // Feeds
    registerCommand('feeds:generate-gshopping', GenerateGShoppingFeed::class,'wawoo');
    registerCommand('feeds:generate-pinterest', GeneratePinterestFeed::class,'wawoo');
    registerCommand('feeds:generate-facebook', GenerateFacebookFeed::class,'wawoo');
    registerCommand('feeds:generate-tiktok', GenerateTikTokFeed::class,'wawoo');
    // Fixes
    registerCommand('publish-missed-posts', PublishMissingArticles::class,'waboot');
    registerCommand('parse-and-save-products', ParseAndSaveProducts::class,'wawoo');
    registerCommand('fix-stock-statuses', FixStockStatuses::class,'wawoo');
    registerCommand('fix-prices', FixPrices::class,'wawoo');
    registerCommand('remove-sale-prices', RemoveSalePrices::class,'wawoo');
}catch (\Exception $e){}