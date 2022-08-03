<?php

namespace Waboot\inc\cli;

class RemoveSalePrices extends ParseAndSaveProducts
{
    /**
     * @var string
     */
    protected $logFileName = 'remove-sale-prices';
    /**
     * @var string
     */
    protected $logDirName = 'remove-sale-prices';

    /**
     * Parse and tries to remove all sale prices from all products
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
     *      wp wawoo:remove-sale-prices
     */
    public function __invoke($args, $assoc_args): int
    {
        return parent::__invoke($args,$assoc_args);
    }

    /**
     * Parse and save the products
     */
    protected function parse(): void
    {
        $productIds = $this->fetchProductIds();
        if(!\is_array($productIds) || count($productIds) === 0){
            $this->log('No products found');
            return;
        }
        $parsedVariableProducts = [];
        foreach ($productIds as $productId)
        {
            try{
                $this->log('- Parsing product #'.$productId);
                $product = wc_get_product($productId);
                if($product instanceof \WC_Product_Variable){
                    $this->log('-- It\'s a variable product');
                    $variations = $this->getProductVariations($productId, 'object');
                    foreach ($variations as $variation){
                        if(!$variation instanceof \WC_Product_Variation){
                            continue;
                        }
                        $this->log('--- Parsing variation #'.$variation->get_id());
                        $this->removeSalePrice($variation);
                        $variation->save();
                    }
                    $this->log('--- Syncing product');
                    \WC_Product_Variable::sync($productId, true);
                }elseif($product instanceof \WC_Product_Variation){
                    $parentId = $product->get_parent_id();
                    if(is_int($parentId) && $parentId !== 0){
                        $this->removeSalePrice($product);
                        $product->save();
                        if(!\in_array($productId,$parsedVariableProducts,true)){
                            $parsedVariableProducts[] = $parentId;
                        }
                    }
                }else{
                    $this->removeSalePrice($product);
                    $this->log('-- Salvo il prodotto');
                    $product->save();
                }
            }catch (\RuntimeException $e){
                $this->log('ERROR: '.$e->getMessage());
                continue;
            }
        }
        if(!empty($parsedVariableProducts)){
            foreach ($parsedVariableProducts as $parsedVariableProductId){
                \WC_Product_Variable::sync($parsedVariableProductId, true);
            }
        }
    }

    /**
     * @param \WC_Product $product
     * @return void
     */
    private function removeSalePrice(\WC_Product $product): void
    {
        $salePrice = get_post_meta($product->get_id(),'_sale_price',true);
        $regularPrice = get_post_meta($product->get_id(),'_regular_price',true);
        $price = get_post_meta($product->get_id(),'_price',true);
        $this->log('-- Removing _sale_price');
        if(!$this->isDryRun()) {
            $product->set_sale_price('');
        }
        if($price !== '' && $regularPrice === ''){
            $this->log('-- Product _price is: '.$price.' and _regular_price is: '.$regularPrice);
            $this->log('-- ... Updating _regular_price to: '.$price);
            if(!$this->isDryRun()) {
                $product->set_regular_price($price);
            }
        }
    }
}