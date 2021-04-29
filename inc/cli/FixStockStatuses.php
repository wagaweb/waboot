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
        add_action('wawoo/cli/parse-and-save-products/post-save/single', static function(\WC_Product $product, ParseAndSaveProducts $class){
            $qtyMeta = get_post_meta($product->get_id(),'_stock', true);
            $statusMeta = get_post_meta($product->get_id(),'_stock_status', true);
            $class->log('- _stock: '.$qtyMeta);
            $class->log('- _stock_status: '.$statusMeta);
            $visibilityTerms = wp_get_post_terms($product->get_id(),'product_visibility');
            $visibilityTermsSlugs = wp_list_pluck($visibilityTerms,'slug');
            $managingStock = $product->managing_stock();
            $class->log('- Manage stock? '.(int) $managingStock);
            if(!$managingStock){
                return;
            }
            $qty = (int) $qtyMeta;
            $realStatus = $qty > 0 ? 'instock' : 'outofstock';
            if($product instanceof \WC_Product_Variable){
                /*
                 * If we have a variable product, its stock status must take into account
                 * the variations quantities
                 */
                $class->log('-- Checking variations');
                $variations = $class->getProductVariations($product->get_id(),'object');
                $totalVariationsQty = 0;
                $atLeastOneVariationIsStockManaging = false;
                foreach ($variations as $variation){
                    $vManagingQty = $variation->managing_stock();
                    if(!$vManagingQty){
                        continue;
                    }
                    $atLeastOneVariationIsStockManaging = true;
                    $vQty = (int) get_post_meta($variation->get_id(),'_stock',true);
                    $totalVariationsQty += $vQty;
                }
                if($atLeastOneVariationIsStockManaging){
                    $class->log('--- Variations with managing quantity flag total quantity: '.$totalVariationsQty);
                    $realStatus = $totalVariationsQty > 0 ? 'instock' : 'outofstock';
                }
            }
            if($realStatus !== $statusMeta){
                $class->log('-- Set "_stock_status" to: '.$realStatus);
                update_post_meta($product->get_id(),'_stock_status',$realStatus);
            }
            if(!$product instanceof \WC_Product_Variation){
                /*
                 * WooCommerce adds 'outofstock' term in 'product_visibility' taxonomy for
                 * out-of-stock products.
                 */
                if($realStatus === 'instock' && \in_array('outofstock',$visibilityTermsSlugs,true)){
                    $class->log('-- Removing "outofstock" product_visibility term');
                    wp_remove_object_terms($product->get_id(),'outofstock','product_visibility');
                }elseif($realStatus === 'outofstock' && !\in_array('outofstock',$visibilityTermsSlugs,true)){
                    $class->log('-- Adding "outofstock" product_visibility term');
                    wp_add_object_terms($product->get_id(),'outofstock','product_visibility');
                }
            }
        },10,2);
        return parent::__invoke($args,$assoc_args);
    }
}