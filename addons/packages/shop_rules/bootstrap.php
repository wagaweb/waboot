<?php

namespace Waboot\addons\packages\shop_rules;

use Waboot\addons\packages\shop_rules\cli\CalculateSales;
use Waboot\addons\packages\shop_rules\cli\GenerateShopRules;
use Waboot\addons\packages\shop_rules\cli\JoinTaxonomy;

use function Waboot\addons\getAddonDirectory;

require_once getAddonDirectory('shop_rules').'/rule_params/BuyXGetY.php';
require_once getAddonDirectory('shop_rules').'/rule_params/BuyXGetYItem.php';
require_once getAddonDirectory('shop_rules').'/rule_params/JoinTaxonomy.php';
require_once getAddonDirectory('shop_rules').'/rule_params/Sale.php';
require_once getAddonDirectory('shop_rules').'/rule_params/Discount.php';
require_once getAddonDirectory('shop_rules').'/ShopRule.php';
require_once getAddonDirectory('shop_rules').'/ShopRuleException.php';
require_once getAddonDirectory('shop_rules').'/ShopRuleRepository.php';
require_once getAddonDirectory('shop_rules').'/ShopRuleRepositoryException.php';
require_once getAddonDirectory('shop_rules').'/ShopRuleTaxFilter.php';
require_once getAddonDirectory('shop_rules').'/cli/GenerateShopRules.php';
require_once getAddonDirectory('shop_rules').'/cli/JoinTaxonomy.php';
require_once getAddonDirectory('shop_rules').'/cli/CalculateSales.php';
require_once getAddonDirectory('shop_rules').'/functions.php';
require_once getAddonDirectory('shop_rules').'/buy-x-get-y-hooks.php';
require_once getAddonDirectory('shop_rules').'/cart-adjustment-hooks.php';

/*
 * Backend hooks
 */
require_once getAddonDirectory('shop_rules').'/backend.php';
require_once getAddonDirectory('shop_rules').'/backend_api.php';

/*add_action('init', function(){
    ajaxGetEditData();
});*/

/*
 * CLI
 */

/*add_action('init', function (){
    //(new GenerateShopRules())->__invoke([],[]);
});*/

/*
add_action('wp_enqueue_scripts', function () {
    $assets = [
        'shoprules-script' => [
            'uri' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ?
                getAddonDirectoryURI('shop_rules') . '/assets/dist/shop-rules.js' :
                getAddonDirectoryURI('shop_rules') . '/assets/dist/shop-rules.min.js',
            'path' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ?
                getAddonDirectory('shop_rules') . '/assets/dist/shop-rules.js' :
                getAddonDirectory('shop_rules') . '/assets/dist/shop-rules.min.js',
            'type' => 'js',
            'deps' => ['jquery'],
            'in_footer' => true
        ],
        'shoprules-style' => [
            'uri' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ?
                getAddonDirectoryURI('shop_rules') . '/assets/dist/shop-rules.css' :
                getAddonDirectoryURI('shop_rules') . '/assets/dist/shop-rules.min.css',
            'path' => defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ?
                getAddonDirectory('shop_rules') . '/assets/dist/shop-rules.js' :
                getAddonDirectory('shop_rules') . '/assets/dist/shop-rules.min.js',
            'type' => 'css'
        ],
    ];
    $am = new AssetsManager(apply_filters('catalog_addon_assets', $assets));
    try {
        $am->enqueue();
    } catch (\Exception $e) {
        trigger_error($e->getMessage(), E_USER_WARNING);
    }
});
*/

if (!defined('WP_CLI')) {
    return;
}

\WP_CLI::add_command('wawoo:generate-shop-rules', GenerateShopRules::class);
\WP_CLI::add_command('wawoo:join-taxonomy', JoinTaxonomy::class, [
    'shortdesc' => 'Join queried products into taxonomy',
    'synopsis' => [
        [
            'type' => 'flag',
            'name' => 'reset',
            'description' => 'Reset all old joined taxonomies',
            'optional' => true,
        ],
    ],
]);
\WP_CLI::add_command('wawoo:calculate-sales', CalculateSales::class, [
    'shortdesc' => 'Calculate product sale price based on shop rules',
    'synopsis' => [
        [
            'type' => 'flag',
            'name' => 'reset',
            'description' => 'Reset all old sale prices',
            'optional' => true,
        ],
    ],
]);
