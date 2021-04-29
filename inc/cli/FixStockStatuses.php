<?php

namespace Waboot\inc\cli;

use Waboot\inc\core\cli\AbstractCommand;

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
        add_action('wawoo/cli/parse-and-save-products/post-save/single', static function($product, ParseAndSaveProducts $class){
            $qtyMeta = get_post_meta($product->get_id(),'_stock', true);
            $statusMeta = get_post_meta($product->get_id(),'_stock_status', true);
            $visibilityTerms = wp_get_post_terms($product->get_id(),'product_visibility');
            $visibilityTermsSlugs = wp_list_pluck($visibilityTerms,'slug');
            $managingStock = $product->managing_stock();
            if($qtyMeta !== '' && $managingStock){
                $qty = (int) $qtyMeta;
                $realStatus = $qty > 0 ? 'instock' : 'outofstock';
                if($realStatus !== $statusMeta){
                    $class->log('Set "_stock_status" to: '.$realStatus);
                    update_post_meta($product->get_id(),'_stock_status',$realStatus);
                }
                if($realStatus === 'instock' && \in_array('outofstock',$visibilityTermsSlugs,true)){
                    $class->log('Removing "outofstock" product_visibility term');
                    wp_remove_object_terms($product->get_id(),'outofstock','product_visibility');
                }elseif($realStatus === 'outofstock' && !\in_array('outofstock',$visibilityTermsSlugs,true)){
                    $class->log('Adding "outofstock" product_visibility term');
                    wp_add_object_terms($product->get_id(),'outofstock','product_visibility');
                }
            }
        },10,2);
        return parent::__invoke($args,$assoc_args);
    }
}