<?php

namespace Waboot\inc\cli\feeds;

use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\multilanguage\helpers\Polylang;
use Waboot\inc\core\woocommerce\ProductException;
use Waboot\inc\core\woocommerce\ProductFactory;
use Waboot\inc\core\woocommerce\ProductFactoryException;
use function Waboot\inc\getAllProductVariationIds;
use function Waboot\inc\getHierarchicalCustomFieldFromProduct;

require_once __DIR__.'/feed-utils.php';

class GenerateGShoppingFeed extends AbstractCommand
{
    public const EXCLUDED_BY_ID = 'id';
    public const EXCLUDED_BY_SKU = 'sku';
    public const EXCLUDED_BY_CALLBACK = 'callback';
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
     * @var int[]
     */
    protected $excludedIds = [];
    /**
     * @var bool
     */
    protected $variableProductsOnly;
    /**
     * @var array
     */
    protected $records;
    /**
     * @var string
     */
    protected $language;
    /**
     * @var string
     */
    protected $customOutputPath;
    /**
     * @var string
     */
    protected $customOutputFilename;
    /**
     * @var string
     */
    protected $defaultProductCategory;
    /**
     * @var string
     */
    protected $defaultProductShippingLabel;
    /**
     * @var array
     */
    protected $productsSkuWithNoEan = [];
    /**
     * @var array
     */
    protected $skippedDuplicatedSku = [];

    /**
     * Generate Google Shopping feed
     *
     * ## OPTIONS
     *
     * [--progress]
     * : Show the progress bar
     *
     * [--products]
     * : Comma separated products ids to parse.
     *
     * [--excluded-products]
     * : Comma separated products ids or sku to exclude.
     *
     * [--excluded-by]
     * : Tells whether products are excluded by "sku", "id" or "callback"
     *
     * [--excluded-callback]
     * : Specify the exclusion callback (must return an array of product id)
     *
     * [--lang]
     * : Set the language to use
     *
     * [--variable-products-only]
     * : Parse the variable products only
     *
     * [--default-google-product-category]
     * : Specify the default product category (otherwise obtained from "_gshopping_product_category" meta)
     *
     * [--default-google-product-shipping-label]
     * : Specify the default product shipping label (otherwise obtained from "_gshopping_shipping_label" meta)
     *
     * [--output-dir-path]
     * : Set the output path
     *
     * [--output-file-name]
     * : Set the output file name
     *
     * ## EXAMPLES
     *
     *      wp wawoo:feeds:generate-gshopping
     *
     *      wp wawoo:feeds:generate-gshopping --lang=it
     *
     *      wp wawoo:feeds:generate-gshopping --output-file-name=test_it --lang=it
     *
     *      wp wawoo:feeds:generate-gshopping --products=30,33,34
     *
     * @param $args
     * @param $assoc_args
     * @return int
     */
    public function __invoke($args, $assoc_args): int
    {
        return parent::__invoke($args,$assoc_args);
    }

    public function run(array $args, array $assoc_args): int
    {
        try{
            if(isset($assoc_args['products'])){
                $providedIds = explode(',',$assoc_args['products']);
                if(\is_array($providedIds)){
                    $this->providedIds = $providedIds;
                }
            }
            $excludedBy = $assoc_args['excluded-by'] ?? 'id';
            if(!\in_array($excludedBy,[self::EXCLUDED_BY_ID,self::EXCLUDED_BY_SKU,self::EXCLUDED_BY_CALLBACK],true)){
                $this->error('"excluded-by" non valido');
                return 1;
            }
            if(isset($assoc_args['excluded-products'])){
                $excludedProducts = explode(',',$assoc_args['excluded-products']);
                if(\is_array($excludedProducts)){
                    if($excludedBy === self::EXCLUDED_BY_SKU){
                        $this->excludedIds = array_map(static function($sku){
                            $pId = wc_get_product_id_by_sku($sku);
                            if(\is_int($pId) && $pId > 0){
                                return $pId;
                            }
                            return false;
                        },$excludedProducts);
                        $this->excludedIds = array_filter($this->excludedIds);
                    }elseif($excludedBy === self::EXCLUDED_BY_ID){
                        $this->excludedIds = $excludedProducts;
                    }
                }
            }elseif($excludedBy === self::EXCLUDED_BY_CALLBACK){
                $excludeCallback = $assoc_args['excluded-callback'] ?? null;
                if(!\is_callable($excludeCallback)){
                    $this->error('"excluded-callback" non valida');
                    return 1;
                }
                $excludedProducts = $excludeCallback();
                if(\is_array($excludedProducts)){
                    $this->excludedIds = $excludedProducts;
                }
            }
            $this->variableProductsOnly = isset($assoc_args['variable-products-only']);
            $this->language = isset($assoc_args['lang']) ? $assoc_args['lang'] : 'it';
            $this->setLanguage();
            if(isset($assoc_args['output-dir-path']) && \is_string($assoc_args['output-dir-path']) && $assoc_args['output-dir-path'] !== ''){
                $this->customOutputPath = $assoc_args['output-dir-path'];
            }
            if(isset($assoc_args['output-file-name']) && \is_string($assoc_args['output-file-name']) && $assoc_args['output-file-name'] !== ''){
                $this->customOutputFilename = $assoc_args['output-file-name'];
            }
            //https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt
            //https://support.google.com/merchants/answer/6324436?hl=it&ref_topic=6324338
            //https://www.google.com/basepages/producttype/taxonomy.en-US.txt
            //https://developers.facebook.com/docs/commerce-platform/catalog/categories/#google-prod-cat
            if(isset($assoc_args['default-google-product-category']) && \is_string($assoc_args['default-google-product-category']) && $assoc_args['default-google-product-category'] !== ''){
                $this->defaultProductCategory = $assoc_args['default-google-product-category'];
            }else{
                $this->defaultProductCategory = 'Apparel & Accessories';
            }
            if(isset($assoc_args['default-google-product-shipping-label']) && \is_string($assoc_args['default-google-product-shipping-label']) && $assoc_args['default-google-product-shipping-label'] !== ''){
                $this->defaultProductShippingLabel = $assoc_args['default-google-product-shipping-label'];
            }else{
                $this->defaultProductShippingLabel = 'italia';
            }
            $this->populateProducts();
            $this->populateRecords();
            $this->generateXML();
            if(!empty($this->productsSkuWithNoEan)){
                $this->log('Products with no EAN: '.implode(', ',$this->productsSkuWithNoEan));
            }
            if(!empty($this->skippedDuplicatedSku)){
                $this->log('Products skipped for duplicated SKU: ');
                foreach ($this->skippedDuplicatedSku as $skippedData){
                    $this->log('SKU: '.$skippedData['sku'].' | Parsed Record: '.$skippedData['parsed_record'].' | Skipped Record: '.$skippedData['duplicated_record']);
                }
            }
            $this->success('Operation completed');
            return 0;
        }catch (\RuntimeException $e){
            $this->error($e->getMessage());
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
        $qArgs = [
            //'post_type' => ['product','product_variation'],
            'post_type' => ['product'],
            'meta_query' => [
                [
                    'key' => '_stock_status',
                    'value' => 'instock'
                ]
            ],
            'fields' => 'ids',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ];
        if(!empty($this->excludedIds)){
            $qArgs['post__not_in'] = $this->excludedIds;
        }
        $ids = get_posts($qArgs);
        if(\is_array($ids) && count($ids) > 0){
            $this->log('Found '.count($ids).' ids');
            $productIdsToParse = [];
            $idsWithNoSku = [];
            foreach ($ids as $id){
                //dont check skus for variable products:
                if(!\in_array($id,$productIdsToParse,true) && WC()->product_factory::get_product_type($id) === 'variable'){
                    $productIdsToParse[] = $id;
                    continue;
                }
                //for everything else:
                if(get_post_meta($id,'_sku',true) === ''){
                    $idsWithNoSku[] = $id;
                }
                if(!\in_array($id,$productIdsToParse,true)){
                    $productIdsToParse[] = $id;
                }
            }
            $this->log('ID with no associated SKU: '.implode(', ',$idsWithNoSku));
            $this->productIds = $productIdsToParse;
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
        $parsedProductSkus = [];
        $parsedVariationSkus = [];
        foreach ($this->productIds as $productId) {
            try {
                $product = wc_get_product($productId);
                if($product instanceof \WC_Product_Variable){
                    //$variationsData = $product->get_available_variations();
                    $variationIds = getAllProductVariationIds($productId);
                    if(count($variationIds) === 0){
                        continue;
                    }
                    foreach ($variationIds as $variationId){
                        //$variationId = $variationData['variation_id'];
                        $variation = wc_get_product($variationId);
                        if(!$variation instanceof \WC_Product_Variation){
                            continue;
                        }
                        if(!$variation->is_in_stock()){
                            continue;
                        }
                        $stockMeta = get_post_meta($variationId,'_stock_status',true);
                        if($stockMeta === 'outofstock'){
                            continue; //double check
                        }
                        if($variation->get_sku() === ''){
                            try{
                                $newRecord = $this->generateRecord($variation,$product);
                                $this->records[] = $newRecord;
                            }catch (ProductFactoryException $e) {
                                $this->log('Error: '.$e->getMessage());
                            }
                        }else{
                            if(!array_key_exists($variation->get_sku(),$parsedVariationSkus)){
                                try{
                                    $newRecord = $this->generateRecord($variation,$product);
                                    $this->records[] = $newRecord;
                                    $parsedVariationSkus[$variation->get_sku()] = '{Variation: '.$variationId.', Product: '.$productId.'}';
                                }catch (ProductFactoryException $e) {
                                    $this->log('Error: '.$e->getMessage());
                                }
                            }else{
                                $this->skippedDuplicatedSku[] = [
                                    'sku' => $variation->get_sku(), //The SKU
                                    'parsed_record' => $parsedVariationSkus[$variation->get_sku()], //ID of the product previously parsed with the same SKU
                                    'duplicated_record' => '{Variation: '.$variationId.', Product: '.$productId.'}' //current product ID
                                ];
                            }
                        }
                    }
                }else{
                    if($this->variableProductsOnly){
                        continue;
                    }
                    if($product instanceof \WC_Product_Variation && \in_array($productId,$this->excludedIds)){
                        continue;
                    }
                    if($product->get_sku() === ''){
                        try {
                            $newRecord = $this->generateRecord($product);
                            $this->records[] = $newRecord;
                        } catch (ProductFactoryException $e) {
                            $this->log('Error: '.$e->getMessage());
                        }
                    }else{
                        if(!array_key_exists($product->get_sku(),$parsedProductSkus)){
                            try{
                                $newRecord = $this->generateRecord($product);
                                $this->records[] = $newRecord;
                                $parsedProductSkus[$product->get_sku()] = '{Product: '.$productId.'}';
                            } catch (ProductFactoryException $e) {
                                $this->log('Error: '.$e->getMessage());
                            }
                        }else{
                            $this->skippedDuplicatedSku[] = [
                                'sku' => $product->get_sku(), //The SKU
                                'parsed_record' => $parsedProductSkus[$product->get_sku()], //ID of the product previously parsed with the same SKU
                                'duplicated_record' => '{Product: '.$productId.'}' //current product ID
                            ];
                        }
                    }
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
     * @throws ProductFactoryException
     */
    public function generateRecord(\WC_Product $product, \WC_Product $parentProduct = null): array
    {
        $wbProduct = ProductFactory::create($product);
        $brand = $wbProduct->getBrand();
        $permalink = $wbProduct->getPermalink();
        $categories = $wbProduct->getCategories(false,true,' > ');
        $price = $wbProduct->getRegularPrice();
        $salePrice = $wbProduct->getSalePrice();
        /*
         * BEGIN: Exclude zero priced products
         */
        if($product->is_on_sale()){
            if(
                ( is_numeric($salePrice) && $salePrice <= 0 ) ||
                ( \is_string($salePrice) && ($salePrice === '' || $salePrice === '0') )
            ){
                return [];
            }
        }elseif(
            ( is_numeric($price) && $price <= 0 ) ||
            ( \is_string($price) && ($price === '' || $price === '0') )
        ){
            return [];
        }
        /*
         * END: Exclude zero priced products
         */
        $size = $product->get_attribute('size');
        $gtin = getHierarchicalCustomFieldFromProduct($product,'_ean','');
        if(isset($brand) && $brand instanceof \WP_Term){
            $brand = $brand->name;
        }else{
            $brand = '';
        }
        //https://support.google.com/merchants/topic/6324338?hl=it&ref_topic=7294998
        //https://support.google.com/merchants/answer/7052112?hl=it&ref_topic=6324338&sjid=15059762771109391205-EU
        //https://developers.facebook.com/docs/commerce-platform/catalog/fields
        $newRecord = [
            'id' => $product->get_sku(),
            'description' => [
                '_cdata' => isset($parentProduct) ? getGShoppingDescription($parentProduct) : getGShoppingDescription($product)
            ],
            'condition' => 'new',
            'mpn' => $product->get_sku(),
            'identifier_exists' => 'yes',
            'title' => $product->get_title(),
            'availability' => 'in stock',
            'price' => str_replace(',','.',$price).' EUR',
            'link' => $permalink,
            'brand' => $brand,
            'google_product_category' => htmlentities(getHierarchicalCustomFieldFromProduct($product,'_gshopping_product_category',$this->defaultProductCategory)),
            'product_type' => htmlentities($categories),
            'shipping_label' => getHierarchicalCustomFieldFromProduct($product,'_gshopping_shipping_label',$this->defaultProductShippingLabel),
            'imgs' => getProductImagesSrc($product),
        ];
        if($product instanceof \WC_Product_Variation){
            $newRecord['item_group_id'] = $parentProduct->get_sku();
        }
        if($size !== ''){
            $newRecord['size'] = $size; //It can be used 'one size' as default
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
        if(isset($this->customOutputPath)){
            $xmlDirPath = rtrim($this->customOutputPath,'/');
        }else{
            $xmlDirPath = WP_CONTENT_DIR . '/wb-feeds';
        }
        if(isset($this->customOutputFilename)){
            $xmlFileName = $this->customOutputFilename.'.xml';
        }elseif($this->language !== null && \is_string($this->language) && $this->language !== ''){
            $xmlFileName = 'google-products-feed-'.$this->language.'.xml';
        }else{
            $xmlFileName = 'google-products-feed.xml';
        }
        $xmlFilePath = $xmlDirPath . '/'. $xmlFileName;
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
        if(!\class_exists('\Spatie\ArrayToXml\ArrayToXml')){
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

    /**
     * @return void
     */
    private function setLanguage(): void
    {
        if(Polylang::isPolylang()){
            Polylang::setCurrentLanguage($this->language);
        }
    }
}