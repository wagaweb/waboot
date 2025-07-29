<?php

namespace Waboot\inc\cli\feeds;

use Waboot\inc\core\woocommerce\ProductFactory;
use Waboot\inc\core\woocommerce\ProductFactoryException;
use Waboot\inc\enums\Feeds;
use function Waboot\inc\getAllProductVariationIds;
use function Waboot\inc\getHierarchicalCustomFieldFromProduct;

require_once __DIR__.'/feed-utils.php';

class GenerateGShoppingFeed extends GenerateFeeds
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
}