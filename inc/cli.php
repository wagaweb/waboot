<?php

namespace Waboot\inc;

use Waboot\inc\cli\ParseAndSaveProducts;

require_once get_stylesheet_directory().'/inc/core/cli/CommandLogger.php';
require_once get_stylesheet_directory().'/inc/core/cli/AbstractCommand.php';
require_once get_stylesheet_directory().'/inc/cli/ParseAndSaveProducts.php';

add_action('init', function(){
    /*
     * Test commands here
     */
    //(new ParseAndSaveProducts())->__invoke('',[]);
});

if (!defined('WP_CLI')) {
    return;
}

try{
    /*
     * Add commands here
     */
    \WP_CLI::add_command('wawoo:parse-and-save-products', ParseAndSaveProducts::class);
}catch (\Exception $e){}