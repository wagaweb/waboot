<?php

namespace Waboot\inc\cli\feeds;

use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\woocommerce\WabootProduct;
use Waboot\inc\core\woocommerce\WabootProductVariation;
use function Waboot\inc\getHierarchicalCustomFieldFromProduct;

require_once __DIR__.'/feed-utils.php';

class GenerateGShoppingFeed extends AbstractCommand
{
    /**
     * @var string
     */
    protected $logDirName = 'wb-feed-gshopping-gen';
    /**
     * @var string
     */
    protected $logFileName = 'wb-feed-gshopping-gen';
    /**
     * @var int[]
     */
    protected $productIds;
    /**
     * @var int[]
     */
    protected $providedIds = [];
    /**
     * @var bool
     */
    protected $variableProductsOnly;
    /**
     * @var array
     */
    protected $records;

    /**
     * Generate Google Shopping feed
     *
     * ## OPTIONS
     *
     * [--products]
     * : Comma separated products ids to parse.
     *
     * [--variable-products-only]
     * : Parse the variable products only
     *
     * ## EXAMPLES
     *
     *      wp wawoo:feeds:generate-gshopping
     *
     *      wp wawoo:feeds:generate-gshopping --products=30,33,34
     *
     * @param $args
     * @param $assoc_args
     * @return int
     */
    public function __invoke($args, $assoc_args): int
    {
        try{
            parent::__invoke($args,$assoc_args);
            if(isset($assoc_args['products'])){
                $providedIds = explode(',',$assoc_args['products']);
                if(\is_array($providedIds)){
                    $this->providedIds = $providedIds;
                }
            }
            $this->variableProductsOnly = isset($assoc_args['variable-products-only']);
            $this->populateProducts();
            $this->populateRecords();
            $this->generateXML();
            $this->success('Operation completed');
            return 0;
        }catch (\RuntimeException $e){
            $this->error($e->getMessage(), false);
            return 1;
        }
    }

    /**
     * Fetch products for the feed
     */
    public function populateProducts(): void
    {
        $this->log('Retrieving products...');
        if(isset($this->providedIds) && !empty($this->providedIds)){
            $ids = array_map('intval',$this->providedIds);
            $this->productIds = $ids;
            $this->log('...Done');
            return;
        }
        $ids = get_posts([
            //'post_type' => ['product','product_variation'],
            'post_type' => ['product'],
            'meta_query' => [
                [
                    'key' => '_stock_status',
                    'value' => 'instock'
                ]
            ],
            'fields' => 'ids',
            'posts_per_page' => -1
        ]);
        if(\is_array($ids) && count($ids) > 0){
            $this->productIds = $ids;
        }else{
            $this->productIds = [];
            throw new \RuntimeException('No products found');
        }
        $this->log('...Done');
    }

    /**
     * Create the records for the XML file
     */
    public function populateRecords(): void
    {
        $this->log('Generating records for '.count($this->productIds).' products', !$this->showProgressBar);
        if($this->showProgressBar){
            $progress = $this->makeProgressBar('Generating records', count($this->productIds));
        }else{
            $progress = false;
        }
        foreach ($this->productIds as $productId) {
            try {
                $product = wc_get_product($productId);
                if($product instanceof \WC_Product_Variable){
                    $variationsData = $product->get_available_variations();
                    if(count($variationsData) === 0){
                        continue;
                    }
                    foreach ($variationsData as $variationData){
                        $variationId = $variationData['variation_id'];
                        $variation = wc_get_product($variationId);
                        if(!$variation instanceof \WC_Product_Variation){
                            continue;
                        }
                        $newRecord = $this->generateRecord($variation,$product);
                        $this->records[] = $newRecord;
                    }
                }else{
                    if($this->variableProductsOnly){
                        continue;
                    }
                    $newRecord = $this->generateRecord($product);
                    $this->records[] = $newRecord;
                }
                $this->tickProgressBar($progress);
            } catch (\RuntimeException $e) {
                $this->error($e, false);
                continue;
            }
        }
        $this->completeProgressBar($progress);
    }

    /**
     * @param \WC_Product $product
     * @param \WC_Product|null $parentProduct
     * @return array
     */
    public function generateRecord(\WC_Product $product, \WC_Product $parentProduct = null): array
    {
        if($product instanceof \WC_Product_Variation){
            $wbVariation = new WabootProductVariation($product, $parentProduct);
            $brand = $wbVariation->getBrand();
            $permalink = $wbVariation->getPermalink();
            $categories = $wbVariation->getParent()->getCategories(false,true,' > ');
        }else{
            $wbProduct = new WabootProduct($product);
            $brand = $wbProduct->getBrand();
            $permalink = $wbProduct->getPermalink();
            $categories = $wbProduct->getCategories(false,true,' > ');
        }
        $price = $product->get_regular_price();
        $salePrice = $product->get_price();
        $size = $product->get_attribute('size');
        $gtin = getHierarchicalCustomFieldFromProduct($product,'_gtin','');
        if(!\is_string($brand)){
            $brand = '';
        }
        $newRecord = [
            'id' => $product->get_sku(),
            'description' => [
                '_cdata' => getGShoppingDescription($product)
            ],
            'condition' => 'new',
            'mpn' => $product->get_sku(),
            'identifier_exists' => 'yes',
            'title' => $product->get_title(),
            'availability' => 'in stock',
            'price' => str_replace(',','.',$price).' EUR',
            'link' => $permalink,
            'brand' => $brand,
            'google_product_category' => htmlentities(getHierarchicalCustomFieldFromProduct($product,'_gshopping_product_category','Apparel & Accessories > Shoes')),
            'product_type' => htmlentities($categories),
            'shipping_label' => getHierarchicalCustomFieldFromProduct($product,'_gshopping_shipping_label','italia'),
            'imgs' => getProductImagesSrc($product),
        ];
        if($product instanceof \WC_Product_Variation){
            $newRecord['item_group_id'] = $product->get_sku();
        }
        if($size !== ''){
            $newRecord['size'] = $size;
        }
        if($gtin !== ''){
            $newRecord['gtin'] = $gtin;
        }
        if($product->is_on_sale()){
            $newRecord['sale_price'] = str_replace(',','.',$salePrice).' EUR';
        }
        return $newRecord;
    }

    /**
     * Generate the XML file
     */
    public function generateXML(): void
    {
        if (!\is_array($this->records) || count($this->records) === 0) {
            throw new \RuntimeException('No records found');
        }
        $xmlDirPath = WP_CONTENT_DIR . '/wb-feeds';
        $xmlFilePath = $xmlDirPath . '/google-products-feed.xml';
        if (!wp_mkdir_p($xmlDirPath)) {
            throw new \RuntimeException('Unable to create directory: ' . $xmlDirPath);
        }
        $this->log('Manipulating records...');
        $xmlRecords = [
            'channel' => [
                'title' => [
                    '_cdata' => 'Waboot ® Products'
                ],
                'link' => [
                    '_cdata' => 'https://www.waboot.io/'
                ],
                'description' => [
                    '_cdata' => 'WooCommerce Product List RSS feed'
                ],
                'item' => []
            ],
        ];
        foreach ($this->records as $record){
            $xmlRecord = [];
            foreach ($record as $fieldName => $fieldValue){
                if($fieldName === 'imgs'){
                    if(!\is_array($fieldValue) || count($fieldValue) === 0){
                        continue;
                    }
                    $featured = array_shift($fieldValue);
                    $xmlRecord['g:image_link'] = [
                        '_cdata' => $featured
                    ];
                    if(count($fieldValue) > 0){
                        foreach ($fieldValue as $imgSrc){
                            $xmlRecord['g:additional_image_link'][] = [
                                '_cdata' => $imgSrc
                            ];
                        }
                    }
                }else{
                    $xmlRecord['g:'.$fieldName] = $fieldValue;
                }
            }
            $xmlRecords['channel']['item'][] = $xmlRecord;
        }
        $this->log('Writing xml...');
        if(\class_exists('\Spatie\ArrayToXml\ArrayToXml')){
            throw new \RuntimeException('Missing \Spatie\ArrayToXml\ArrayToXml');
        }
        $xmlContent = \Spatie\ArrayToXml\ArrayToXml::convert($xmlRecords,[
            'rootElementName' => 'rss',
            '_attributes' => [
                'xmlns:g' => 'http://base.google.com/ns/1.0',
                'version' => '2.0'
            ],
        ], true, 'UTF-8', '1.0', [
            'formatOutput' => true
        ]);
        if(\is_string($xmlContent) && $xmlContent !== ''){
            if(\is_file($xmlFilePath)){
                unlink($xmlFilePath);
            }
            $r = file_put_contents($xmlFilePath,$xmlContent);
            if($r === false){
                throw new \RuntimeException('Unable write to file: '.$xmlFilePath);
            }
            $this->log('XML written: '.$xmlFilePath);
        }else{
            throw new \RuntimeException('Unable to write XML');
        }
    }
}