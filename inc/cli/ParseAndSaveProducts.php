<?php

namespace Waboot\inc\cli;

use Waboot\inc\core\cli\AbstractCommand;
use function Waboot\inc\adjustPriceMeta;
use function Waboot\inc\adjustProductStockStatus;
use function Waboot\inc\getAllProductVariationIds;
use function Waboot\inc\syncVariableProductData;

class ParseAndSaveProducts extends AbstractCommand
{
    /**
     * @var string
     */
    protected $logDirName = 'parse-and-save-products';
    /**
     * @var string
     */
    protected $logFileName = 'parse-and-save-products';
    /**
     * @var bool
     */
    protected $parseOnlyPublished;
    /**
     * @var int[]
     */
    protected $providedIds;
    /**
     * @var string[]
     */
    protected $providedStatuses;
    /**
     * @var int[]
     */
    protected $productIds;

    /**
     * Parse and re-save all products
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
     *      wp wawoo:parse-and-save-products
     */
    public function __invoke($args, $assoc_args): int
    {
        $this->log('ParseAndSaveProducts invoked');
        if(isset($assoc_args['products'])){
            $productIds = explode(',',$assoc_args['products']);
            if(\is_array($productIds) && count($productIds) > 0){
                $this->log('[INPUTS] Provided IDS: '.$assoc_args['products']);
                $productIds = array_map('intval',$productIds);
                $productIds = array_filter($productIds, static function ($id) { return $id > 0; });
                $this->providedIds = $productIds;
                $this->log('[INPUTS] Provided IDS (validated): '.implode(', ',$this->providedIds));
            }
        }
        if(isset($assoc_args['only-with-statuses'])){
            $statuses = explode(',',$assoc_args['only-with-statuses']);
            if(\is_array($statuses)){
                $this->log('[INPUTS] Statuses: '.$statuses);
                $this->providedStatuses = $statuses;
            }
        }
        if(!isset($this->providedStatuses)){
            $this->parseOnlyPublished = isset($assoc_args['only-published']) && !isset($assoc_args['only-with-statuses']);
            $this->log('[INPUTS] Only Published? '.(int) $this->parseOnlyPublished);
        }
        try{
            $this->parse();
            $this->success('Operation completed');
            return 0;
        }catch (\RuntimeException $e){
            $this->error($e->getMessage());
            return -1;
        }
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
        foreach ($productIds as $productId)
        {
            try{
                do_action('wawoo/cli/parse-and-save-products/pre-save', $productId, $this);
                $product = wc_get_product($productId);
                if($product instanceof \WC_Product_Variation){
                    $parentId = $product->get_parent_id();
                    $parentProduct = wc_get_product($parentId);
                    if(!$parentProduct instanceof \WC_Product_Variable){
                        $this->log('ERROR: Variation #'.$parentId.' has an invalid parent');
                        $this->saveProduct($product);
                    }else{
                        $this->saveProduct($product);
                        $this->saveProduct($parentProduct);
                    }
                }else{
                    $this->saveProduct($product);
                }
                do_action('wawoo/cli/parse-and-save-products/post-save', $productId, $this);
            }catch (\RuntimeException $e){
                $this->log('ERROR: '.$e->getMessage());
                continue;
            }
        }
    }

    /**
     * @param \WC_Product $product
     */
    protected function saveProduct(\WC_Product $product): void
    {
        $this->logProductSaving($product);
        do_action('wawoo/cli/parse-and-save-products/pre-save/single', $product, $this);
        $wpPost = get_post($product->get_id());
        $wpPostId = $wpPost->ID;
        $product->set_date_modified(current_time( 'mysql' ));
        @$product->save();
        adjustPriceMeta($product->get_id());
        adjustProductStockStatus($product->get_id());
        @do_action('save_post', $wpPostId, $wpPost, true);
        if($product instanceof \WC_Product_Variable){
            $variations = $this->getProductVariations($wpPostId);
            $dataStore = $product->get_data_store();
            $dataStore->sort_all_product_variations($wpPostId);
            foreach ($variations as $i => $variationId){
                $variation = new \WC_Product_Variation($variationId);
                $variation->set_date_modified(current_time( 'mysql' ));
                @$variation->save();
                @do_action( 'woocommerce_save_product_variation', $variationId, $i);
            }
            @do_action( 'woocommerce_ajax_save_product_variations', $wpPostId);
            syncVariableProductData($product->get_id());
        }
        do_action('wawoo/cli/parse-and-save-products/post-save/single', $product, $this);
    }

    /**
     * @param \WC_Product $product
     */
    protected function logProductSaving(\WC_Product $product): void
    {
        $sku = $product->get_sku();
        $id = $product->get_id();
        $type = $product->get_type();
        $message = sprintf('Saving %s product #%s (sku: %s)',$type,$id,$sku);
        $this->log($message);
    }

    /**
     * @return int[]
     */
    protected function fetchProductIds(): array
    {
        if(isset($this->providedIds)){
            return $this->providedIds;
        }

        $qArgs = [
            'post_type' => ['product'],
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
        ];
        if($this->parseOnlyPublished){
            $qArgs['post_status'] = ['publish'];
        }else{
            $qArgs['post_status'] = ['any'];
        }
        $qArgs['fields'] = 'ids';

        $qArgs = apply_filters('wawoo/cli/parse-and-save-products/fetch-products-ids-query-args',$qArgs);

        $r = get_posts($qArgs);
        if(!\is_array($r) || count($r) === 0){
            return [];
        }
        return $r;
    }

    /**
     * @param $parentId
     * @param string $returnType ('id' or 'object')
     * @return int[]|\WC_Product_Variation[]
     */
    protected function getProductVariations($parentId, $returnType = 'id'): array
    {
        $variationsIds = getAllProductVariationIds($parentId);
        if(\is_array($variationsIds) && !empty($variationsIds)){
            if($returnType === 'object'){
                return array_map(static function($id){ return wc_get_product($id); },$variationsIds);
            }
            return $variationsIds;
        }
        return [];
    }
}