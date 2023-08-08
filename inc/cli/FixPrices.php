<?php

namespace Waboot\inc\cli;

use Waboot\inc\core\cli\AbstractCommand;
use function Waboot\inc\syncVariableProductData;

class FixPrices extends ParseAndSaveProducts
{
    /**
     * @var string
     */
    protected $logFileName = 'fix-prices';

    /**
     * Parse and tries to fix prices of all products
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
     *      wp wawoo:fix-prices
     */
    public function __invoke($args, $assoc_args): int
    {
        return parent::__invoke($args,$assoc_args);
    }

    function run(array $args, array $assoc_args): int
    {
        add_action('wawoo/cli/parse-and-save-products/post-save/single', function(\WC_Product $product, ParseAndSaveProducts $class){
            if($product instanceof \WC_Product_Variable){
                $class->log('-- Checking variations');
                $variations = $class->getProductVariations($product->get_id(),'object');
                foreach ($variations as $variation){
                    $class->log('-- Examining variation #'.$variation->get_id());
                    $this->adjustPrices($variation, $class);
                }
                $class->log('- Sync variable product: #'.$product->get_id());
                syncVariableProductData($product->get_id());
            }else{
                $class->log('-- Examining product #'.$product->get_id());
                $this->adjustPrices($product, $class);
            }
        },10,2);
        return parent::run($args,$assoc_args);
    }

    /**
     * @param \WC_Product $product
     * @param ParseAndSaveProducts $class
     * @return void
     */
    private function adjustPrices(\WC_Product $product, ParseAndSaveProducts $class): void
    {
        $priceMeta = get_post_meta($product->get_id(),'_price', true);
        $regularPriceMeta = get_post_meta($product->get_id(),'_regular_price', true);
        $salePriceMeta = get_post_meta($product->get_id(),'_sale_price', true);
        $class->log('- _price: '.$priceMeta);
        $class->log('- _regular_price: '.$regularPriceMeta);
        $class->log('- _sale_price: '.$salePriceMeta);
        if($priceMeta === '' || $priceMeta === '0' || $priceMeta === 0){
            if($salePriceMeta !== ''){
                $class->log('_sale_price -> _price');
                update_post_meta($product->get_id(),'_price',$salePriceMeta);
            }elseif($regularPriceMeta !== ''){
                $class->log('_regular_price -> _price');
                update_post_meta($product->get_id(),'_price',$regularPriceMeta);
            }
        }
    }
}