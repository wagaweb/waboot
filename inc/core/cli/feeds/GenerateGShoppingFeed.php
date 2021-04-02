<?php

namespace Waboot\inc\core\cli\feed;

use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\woocommerce\WabootProduct;
use Waboot\inc\core\woocommerce\WabootProductVariation;
use function Waboot\inc\core\woocommerce\getGShoppingDescription;
use function Waboot\inc\core\woocommerce\getProductImagesSrc;

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
     * @var array
     */
    protected $records;

    public function __invoke($args, $assoc_args): int
    {
        try{
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

    public function populateProducts(): void
    {
        $this->log('Retrieving products...');
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

    public function populateRecords(): void
    {
        $this->log('Generating records for '.count($this->productIds).' products');
        $progress = $this->makeProgressBar('Generating records', count($this->productIds));
        foreach ($this->productIds as $productId) {
            try {
                $balleriParent = new WabootProduct($productId);
                $parentProduct = $balleriParent->getWcProduct();
                if (!$parentProduct instanceof \WC_Product_Variable) {
                    continue;
                }
                $parentProductId = $parentProduct->get_id();
                $variationsData = $parentProduct->get_available_variations();
                if(count($variationsData) === 0){
                    continue;
                }
                foreach ($variationsData as $variationData){
                    $variationId = $variationData['variation_id'];
                    try{
                        $balleriVariation = new WabootProductVariation($variationId);
                    }catch (\RuntimeException $e){
                        continue;
                    }
                    $variation = $balleriVariation->getWcProduct();
                    if(!$variation instanceof \WC_Product_Variation){
                        continue;
                    }
                    $brand = $balleriVariation->getBrand();
                    $price = $variation->get_regular_price();
                    $salePrice = $variation->get_price();
                    $size = $variation->get_attribute('numero');
                    $newRecord = [
                        'id' => $variation->get_sku(),
                        'description' => [
                            '_cdata' => getGShoppingDescription($parentProduct)
                        ],
                        'condition' => 'new',
                        'mpn' => $variation->get_sku(),
                        'identifier_exists' => 'yes',
                        'title' => $variation->get_title(),
                        'availability' => 'in stock',
                        'price' => str_replace(',','.',$price).' EUR',
                        'link' => $balleriVariation->getPermalink(),
                        'brand' => $brand,
                        'item_group_id' => $parentProduct->get_sku(),
                        'google_product_category' => htmlentities('Apparel & Accessories > Shoes'),
                        'product_type' => htmlentities($balleriParent->getCategories(false,true,' > ')),
                        'shipping_label' => 'italia',
                        'imgs' => getProductImagesSrc($parentProduct),
                        'size' => $size
                    ];
                    if($variation->is_on_sale()){
                        $newRecord['sale_price'] = str_replace(',','.',$salePrice).' EUR';
                    }
                    $this->records[] = $newRecord;
                }
                if($progress){
                    $progress->tick();
                }
            } catch (\RuntimeException $e) {
                $this->error($e, false);
                continue;
            }
        }
        if($progress){
            $progress->finish();
        }
    }

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