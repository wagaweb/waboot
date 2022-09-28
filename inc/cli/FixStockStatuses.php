<?php

namespace Waboot\inc\cli;

use Waboot\inc\core\cli\AbstractCommand;
use function Waboot\inc\adjustProductStockStatus;

class FixStockStatuses extends ParseAndSaveProducts
{
    /**
     * @var string
     */
    protected $logFileName = 'fix-stock-statuses';

    /**
     * Parse and tries to fix stock statuses of all products
     *
     * ## OPTIONS
     *
     * [--products]
     * : Comma separated products ids to parse. If a variation is provided, the parent will be parsed and saved too.
     *
     * [--only-published]
     * : Parse the published products only
     *
     * [--only-with-statuses]
     * : Comma separated list of post statuses
     *
     * ## EXAMPLES
     *
     *      wp wawoo:fix-stock-statuses
     */
    public function __invoke($args, $assoc_args): int
    {
        add_action('wawoo/cli/parse-and-save-products/post-save/single', static function(\WC_Product $product, ParseAndSaveProducts $class){
            $qtyMeta = get_post_meta($product->get_id(),'_stock', true);
            $statusMeta = get_post_meta($product->get_id(),'_stock_status', true);
            $class->log('- _stock: '.$qtyMeta);
            $class->log('- _stock_status: '.$statusMeta);
            $managingStock = $product->managing_stock();
            $class->log('- Manage stock? '.(int) $managingStock);
            $result = adjustProductStockStatus($product->get_id());
            if($result['old'] !== $result['new']){
                $class->log('-- Set "_stock_status" to: '.$result['new']);
            }else{
                $class->log('-- Kept "_stock_status" on: '.$result['old']);
            }
        },10,2);
        return parent::__invoke($args,$assoc_args);
    }
}