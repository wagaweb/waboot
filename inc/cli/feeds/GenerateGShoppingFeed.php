<?php

namespace Waboot\inc\cli\feeds;

use Automattic\WooCommerce\Enums\ProductType;
use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\cli\CLIRuntimeException;
use Waboot\inc\core\multilanguage\helpers\Polylang;
use Waboot\inc\core\woocommerce\ProductFactory;
use Waboot\inc\core\woocommerce\ProductFactoryException;
use Waboot\inc\enums\Feeds;
use function Waboot\inc\getAllProductVariationIds;
use function Waboot\inc\getHierarchicalCustomFieldFromProduct;
use function Waboot\inc\getProductType;

require_once __DIR__.'/feed-utils.php';

class GenerateGShoppingFeed extends AbstractCommand
{
    public const EXCLUDED_BY_ID = 'id';
    public const EXCLUDED_BY_SKU = 'sku';
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
    protected array $productIds;
    /**
     * @var int[]
     */
    protected array $providedIds = [];
    /**
     * @var string[]
     */
    protected array $providedTypes = [];
    /**
     * @var int[]
     */
    protected array $excludedIds = [];
    /**
     * @var bool
     */
    protected bool $excludeZeroPricedProducts = true;
    /**
     * @var array
     */
    protected array $records;
    protected ?string $language = null;
    protected ?string $defaultLanguage = 'it';
    protected string $customOutputPath;
    protected string $customOutputFilename;
    protected string $defaultProductCategory;
    protected string $defaultProductShippingLabel;
    protected array $productsSkuWithNoEan = [];
    protected array $skippedDuplicatedSku = [];
    protected array $parsedProductSkus = [];
    protected array $parsedVariationSkus = [];

    public static function getCommandDescription(): array
    {
        $description = parent::getCommandDescription();
        $description['shortdesc'] = 'Generate Google Shopping feed';
        $description['longdesc'] = '## EXAMPLES' . "\n\n" . 'wp wawoo:feeds:generate-gshopping';
        $description['synopsis'][] = [
            'type' => 'flag',
            'name' => 'progress',
            'description' => 'Show the progress bar',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'products',
            'description' => 'Comma separated products ids to parse',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'excluded-products',
            'description' => 'Comma separated products ids or sku to exclude',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'excluded-by',
            'description' => 'Tells whether products are excluded by "sku", "id"',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'lang',
            'description' => 'Set the language to use',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'types',
            'description' => 'Comma separated product types to include (simple,variable,grouped,external,variation). Default: simple,variable. See wp-content/plugins/woocommerce/src/Enums/ProductType.php',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'default-google-product-category',
            'description' => 'Specify the default product category (otherwise obtained from "_gshopping_product_category" meta)',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'default-google-product-shipping-label',
            'description' => 'Specify the default product shipping label (otherwise obtained from "_gshopping_shipping_label" meta)',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'output-dir-path',
            'description' => 'Set the output path',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'output-file-name',
            'description' => 'Set the output file name',
            'optional' => true,
        ];
        return $description;
    }

    public function run(array $args, array $assoc_args): int
    {
        try{
            if(isset($assoc_args['products'])){
                $providedIds = explode(',',$assoc_args['products']);
                if(\is_array($providedIds)){
                    $this->providedIds = $providedIds;
                    $this->log('Product IDS: '.implode(',',$this->providedIds));
                }
            }
            $this->providedTypes = [ProductType::SIMPLE, ProductType::VARIABLE];
            if(isset($assoc_args['types'])){
                $allowedTypes = apply_filters('wawoo/cli/genfeeds/allowed_types', [
                    ProductType::SIMPLE,
                    ProductType::VARIABLE,
                    ProductType::EXTERNAL,
                    ProductType::GROUPED,
                    ProductType::VARIATION,
                    'bundle' // WC Product Bundles built-in support
                ]);
                $providedTypes = explode(',',$assoc_args['types']);
                $providedTypes = array_filter($providedTypes, static function ($type) use($allowedTypes) {
                    return \in_array($type, $allowedTypes, true);
                });
                if(!empty($providedTypes)){
                    $this->providedTypes = $providedTypes;
                }else{
                    throw new CLIRuntimeException('No valid product types provided');
                }
            }
            $this->log('Product Types: '.implode(',',$this->providedTypes));
            if(isset($assoc_args['excluded-products'])){
                $excludedProducts = array_filter(explode(',', $assoc_args['excluded-products']));
                if(!empty($excludedProducts)){
                    $excludedBy = $assoc_args['excluded-by'] ?? 'id';
                    if(!\in_array($excludedBy,[self::EXCLUDED_BY_ID,self::EXCLUDED_BY_SKU],true)){
                        throw new CLIRuntimeException('"excluded-by" non valido');
                    }
                    if($excludedBy === self::EXCLUDED_BY_SKU){
                        $excludedIds = array_map(static function($sku){
                            $pId = wc_get_product_id_by_sku($sku);
                            if(\is_int($pId) && $pId > 0){
                                return $pId;
                            }
                            return false;
                        },$excludedProducts);
                        $excludedIds = array_filter($excludedIds);
                        $excludedIds = array_map('intval', $excludedIds);
                        $this->excludedIds = $excludedIds;
                    }else{
                        $this->excludedIds = array_map('intval', $excludedProducts);
                    }
                }
            }
            if(isset($assoc_args['lang']) || isset($this->defaultLanguage)){
                $this->language = $assoc_args['lang'] ?? $this->defaultLanguage;
                $this->setLanguage();
            }
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
     * @return array
     */
    protected function getProductQueryArgs(): array
    {
        /*$qArgs = [
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
        ];*/
        $qArgs = [
            'type' => $this->providedTypes,
            //'stock_status' => 'instock',
            'status' => 'publish',
            'return' => 'ids',
            'limit' => -1,
            'custom_meta_query' => [
                'key' => Feeds::EXCLUDE_FROM_FEEDS_META_KEY,
                'compare' => 'NOT EXISTS'
            ]
        ];
        if(!empty($this->excludedIds)){
            //$qArgs['post__not_in'] = $this->excludedIds;
            $qArgs['exclude'] = $this->excludedIds;
        }
        return $qArgs;
    }

    /**
     * @return array
     */
    protected function fetchProductsIds(): array
    {
        try{
            //$ids = get_posts($qArgs);
            $ids = wc_get_products($this->getProductQueryArgs());
            if(!\is_array($ids)){
                return [];
            }
            return $ids;
        }catch (\Exception|\Throwable $e){
            return [];
        }
    }

    /**
     * Fetch products for the feed
     */
    protected function populateProducts(): void
    {
        $this->log('Retrieving products...');
        if(isset($this->providedIds) && !empty($this->providedIds)){
            $ids = array_map('intval',$this->providedIds);
            $this->productIds = $ids;
            $this->log('...Done');
            return;
        }
        $ids = $this->fetchProductsIds();
        if(count($ids) > 0){
            $this->log('Found '.count($ids).' ids');
            $productIdsToParse = [];
            $idsWithNoSku = [];
            $noSkuAllowedForTypes = [ProductType::VARIABLE];
            foreach ($ids as $id){
                if(\in_array($id,$productIdsToParse,true)){
                    continue;
                }
                $idIsValid = true;
                $currentSku = get_post_meta($id,'_sku',true);
                if(!\is_string($currentSku) || $currentSku === ''){
                    $idsWithNoSku[] = $id;
                    if(!\in_array(getProductType($id),$noSkuAllowedForTypes)){
                        $idIsValid = false;
                    }
                }
                $idIsValid = apply_filters('wawoo/cli/genfeeds/populate_products/product_is_valid', $idIsValid, $id, $currentSku);
                if($idIsValid){
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
    protected function populateRecords(): void
    {
        $this->log('Generating records for '.count($this->productIds).' products', !$this->showProgressBar);
        if($this->showProgressBar){
            $progress = $this->makeProgressBar('Generating records', count($this->productIds));
        }else{
            $progress = false;
        }
        $this->records = [];
        foreach ($this->productIds as $productId) {
            try {
                $excludeFromFeeds = get_post_meta($productId,Feeds::EXCLUDE_FROM_FEEDS_META_KEY,true);
                if($excludeFromFeeds === '1'){
                    continue; // safe measure
                }
                $product = wc_get_product($productId);
                if($product->get_sku() !== ''){
                    if(!array_key_exists($product->get_sku(),$this->parsedProductSkus)){
                        $this->parsedProductSkus[$product->get_sku()] = '{Product: '.$productId.'}';
                    }else{
                        $this->skippedDuplicatedSku[] = [
                            'sku' => $product->get_sku(), //The SKU
                            'parsed_record' => $this->parsedProductSkus[$product->get_sku()], //ID of the product previously parsed with the same SKU
                            'duplicated_record' => '{Product: '.$productId.'}' //current product ID
                        ];
                        continue;
                    }
                }
                $newRecords = $this->generateCustomRecords($product);
                if(\is_array($newRecords) && count($newRecords) > 0){
                    // Allowing custom record handling
                    $this->records = array_merge($this->records, $newRecords);
                }else{
                    switch ($product->get_type()){
                        case ProductType::VARIABLE:
                            /**
                             * @var \WC_Product_Variable $product
                             */
                            $newRecords = $this->generateRecordsForVariableProduct($product);
                            $this->records = array_merge($this->records, $newRecords);
                            break;
                        case ProductType::GROUPED:
                            /**
                             * @var \WC_Product_Grouped $product
                             */
                            $newRecords = $this->generateRecordsForGroupedProduct($product);
                            $this->records = array_merge($this->records, $newRecords);
                            break;
                        // WC Product Bundles built-in support
                        case 'bundle':
                            /**
                             * @var \WC_Product_Bundle $product
                             */
                            $newRecords = $this->generateRecordsForBundleProduct($product);
                            $this->records = array_merge($this->records, $newRecords);
                            break;
                        default:
                            try {
                                $newRecord = $this->generateRecord($product);
                                $this->records[] = $newRecord;
                            } catch (\Exception|\Throwable $e) {
                                $this->warning($e->getMessage());
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
     * @return array|null
     */
    protected function generateCustomRecords(\WC_Product $product): ?array
    {
        return null;
    }

    /**
     * @param \WC_Product_Variable $product
     * @return array
     */
    protected function generateRecordsForVariableProduct(\WC_Product_Variable $product): array
    {
        $variationIds = getAllProductVariationIds($product->get_id());
        if(count($variationIds) <= 0){
            return [];
        }
        $records = [];
        foreach ($variationIds as $variationId){
            $variation = wc_get_product($variationId);
            if(!$variation instanceof \WC_Product_Variation){
                continue;
            }
            $variationSku = $variation->get_sku();
            if($variationSku !== ''){
                if(!array_key_exists($variation->get_sku(),$this->parsedVariationSkus)){
                    $this->parsedVariationSkus[$variation->get_sku()] = '{Variation: '.$variationId.', Product: '.$product->get_id().'}';
                }else{
                    $this->skippedDuplicatedSku[] = [
                        'sku' => $variation->get_sku(), //The SKU
                        'parsed_record' => $this->parsedVariationSkus[$variation->get_sku()], //ID of the product previously parsed with the same SKU
                        'duplicated_record' => '{Variation: '.$variationId.', Product: '.$product->get_id().'}' //current product ID
                    ];
                    continue;
                }
            }
            try {
                $records[] = $this->generateRecord($variation, $product);
            } catch (\Exception|\Throwable $e) {
                $this->warning($e->getMessage());
            }
        }
        return $records;
    }

    /**
     * @param \WC_Product_Grouped $product
     * @return array|array[]
     */
    protected function generateRecordsForGroupedProduct(\WC_Product_Grouped $product): array
    {
        try {
            $r = $this->generateRecord($product);
            return [
                $r
            ];
        } catch (\Exception|\Throwable $e) {
            return [];
        }
    }

    /**
     * @param \WC_Product_Bundle $product
     * @return array|array[]
     */
    protected function generateRecordsForBundleProduct(\WC_Product $product): array
    {
        try {
            $r = $this->generateRecord($product);
            return [
                $r
            ];
        } catch (\Exception|\Throwable $e) {
            return [];
        }
    }

    /**
     * @param \WC_Product $product
     * @param \WC_Product|null $parentProduct
     * @return array
     * @throws ProductFactoryException
     */
    protected function generateRecord(\WC_Product $product, \WC_Product $parentProduct = null): array
    {
        $excludeFromFeeds = get_post_meta($product->get_id(),Feeds::EXCLUDE_FROM_FEEDS_META_KEY,true);
        if($excludeFromFeeds === '1'){
            return []; // safe measure
        }
        $wbProduct = ProductFactory::create($product);
        $price = $wbProduct->getRegularPrice();
        $price = apply_filters('wawoo/cli/genfeeds/generate_record/price', $price, $product, $parentProduct);
        $salePrice = $wbProduct->getSalePrice();
        $salePrice = apply_filters('wawoo/cli/genfeeds/generate_record/sale_price', $salePrice, $product, $parentProduct);
        /*
         * BEGIN: Exclude zero priced products
         */
        if($this->excludeZeroPricedProducts){
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
        }
        /*
         * END: Exclude zero priced products
         */

        /*
         * BEGIN: Product Data
         */
        $recordCodes = [
            'id' => $product->get_sku(),
            'mpn' => $product->get_sku(), // Manufacturer Part Number (Obbligatorio per tutti i prodotti privi di un codice GTIN assegnato dal produttore)
            'gtin' => getHierarchicalCustomFieldFromProduct($product,'_ean',''), // Global Trade Item Number
            'item_group_id' => $parentProduct?->get_sku(),
        ];
        $recordCodes = apply_filters('wawoo/cli/genfeeds/generate_record/record_codes', $recordCodes, $product, $parentProduct);
        $title = apply_filters('wawoo/cli/genfeeds/generate_record/title', $product->get_title(), $product, $parentProduct);
        $description = isset($parentProduct) ? getGShoppingDescription($parentProduct) : getGShoppingDescription($product);
        $description = apply_filters('wawoo/cli/genfeeds/generate_record/description', $description, $product, $parentProduct);
        $permalink = $wbProduct->getPermalink();
        $brand = $wbProduct->getBrand();
        if(isset($brand) && $brand instanceof \WP_Term){
            $brand = $brand->name;
        }else{
            $brand = '';
        }
        $brand = apply_filters('wawoo/cli/genfeeds/generate_record/brand',$brand, $product, $parentProduct);
        $categories = $wbProduct->getCategories(false,true,' > ');
        $size = $product->get_attribute('size');
        $availability = $product->is_in_stock() ? 'in_stock' : 'out_of_stock';
        $availability = apply_filters('wawoo/cli/genfeeds/generate_record/availability', $availability, $product, $parentProduct);
        $gProductCat = htmlentities(getHierarchicalCustomFieldFromProduct($product,'_gshopping_product_category',$this->defaultProductCategory));
        $gProductCat = apply_filters('wawoo/cli/genfeeds/generate_record/google_product_cat', $gProductCat, $product, $parentProduct);
        $shippingLabel = getHierarchicalCustomFieldFromProduct($product,'_gshopping_shipping_label',$this->defaultProductShippingLabel);
        $shippingLabel = apply_filters('wawoo/cli/genfeeds/generate_record/shipping_label', $shippingLabel, $product, $parentProduct);
        $customLabels = apply_filters('wawoo/cli/genfeeds/generate_record/custom_labels', [], $product, $parentProduct);
        /*
         * END: Product Data
         */

        //https://support.google.com/merchants/topic/6324338?hl=it&ref_topic=7294998
        //https://support.google.com/merchants/answer/7052112?hl=it&ref_topic=6324338&sjid=15059762771109391205-EU
        //https://developers.facebook.com/docs/commerce-platform/catalog/fields
        $newRecord = [
            'id' => $recordCodes['id'],
            'description' => [
                '_cdata' => $description
            ],
            'condition' => 'new',
            'mpn' => $recordCodes['mpn'],
            'identifier_exists' => 'yes',
            'title' => $title,
            'availability' => $availability,
            'price' => str_replace(',','.',$price).' EUR',
            'link' => $permalink,
            'brand' => $brand,
            'google_product_category' => htmlentities($gProductCat),
            'product_type' => htmlentities($categories),
            'shipping_label' => $shippingLabel,
            'imgs' => getProductImagesSrc($product),
        ];
        if(isset($recordCodes['item_group_id']) && !empty($recordCodes['item_group_id'])){
            $newRecord['item_group_id'] = $recordCodes['item_group_id'];
        }
        if($size !== ''){
            $newRecord['size'] = $size; //It can be used 'one size' as default
        }
        if($recordCodes['gtin'] !== ''){
            $newRecord['gtin'] = $recordCodes['gtin'];
        }
        if($product->is_on_sale()){
            $newRecord['sale_price'] = str_replace(',','.',$salePrice).' EUR';
        }
        if(\is_array($customLabels) && !empty($customLabels)){
            foreach($customLabels as $k => $label){
                $newRecord['custom_label_'.$k] = $label;
            }
        }
        $newRecord = apply_filters('wawoo/cli/genfeeds/generate_record/record', $newRecord, $product);
        if(!\is_array($newRecord)){
            $newRecord = [];
        }
        return $newRecord;
    }

    /**
     * Generate the XML file
     */
    protected function generateXML(): void
    {
        if (!\is_array($this->records) || count($this->records) === 0) {
            throw new \RuntimeException('No records found');
        }
        if(isset($this->customOutputPath)){
            $xmlDirPath = rtrim($this->customOutputPath,'/');
        }else{
            $xmlDirPath = WP_CONTENT_DIR . '/wb-feeds';
        }
        $xmlFileName = $this->customOutputFilename ?? 'google-products-feed';
        if($this->language !== null && \is_string($this->language) && $this->language !== '') {
            $xmlFileName .= '-' . $this->language . '.xml';
        }else{
            $xmlFileName .= '.xml';
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
    protected function setLanguage(): void
    {
        if(Polylang::isPolylang()){
            Polylang::setCurrentLanguage($this->language);
        }
    }
}