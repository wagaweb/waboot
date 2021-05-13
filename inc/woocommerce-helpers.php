<?php

namespace Waboot\inc;

/**
 * Check whether the product is a bundle
 *
 * @param int|\WC_Product $product
 * @return bool
 */
function isBundleProduct($product): bool {
    if(\is_int($product)){
        $product = wc_get_product($product);
        return is_object($product) && $product->is_type('bundle');
    }
    if($product instanceof \WC_Product) {
        return $product->is_type('bundle');
    }
    return false;
}

/**
 * Get all products ids in a bundle
 *
 * @param int $bundleId
 * @return array
 */
function getBundledProductIds(int $bundleId): array {
    $productIds = get_post_meta($bundleId,'_children', true);
    if(!\is_array($productIds)){
        global $wpdb;
        $r = $wpdb->get_results('SELECT product_id FROM '.$wpdb->prefix.'woocommerce_bundled_items WHERE bundle_id = "'.$bundleId.'"');
        if(\is_array($r)){
            $productIds = array_map('intval',wp_list_pluck($r,'product_id'));
        }
    }
    return $productIds;
}

/**
 * Check if a product is in a specific bundle
 *
 * @param int $productId
 * @param int $bundleId
 * @return bool
 */
function isBundledIn(int $productId, int $bundleId): bool {
    $bundledProductIds = getBundledProductIds($bundleId);
    $pType = get_post_type($productId);
    if($pType === 'product_variation'){
        $variation = wc_get_product($productId);
        $parentId = $variation->get_parent_id();
        return \in_array($parentId,$bundledProductIds, true);
    }
    return \in_array($productId,$bundledProductIds, true);
}

/**
 * Get the current regular price of a product associated to the $orderItem
 *
 * @param \WC_Order_Item_Product $orderItem
 * @return float|int|string
 */
function getProductRegularPriceFromOrderItemProduct(\WC_Order_Item_Product $orderItem) {
    $product = $orderItem->get_product();
    $price = 0;
    if($product instanceof \WC_Product){
        $price = $product->get_regular_price();
    }
    return $price;
}

/**
 * Get the percentage value of the sale price in relation with the regular price
 *
 * @param \WC_Product $product
 * @param bool $round whether round the percentage or not
 * @param int $roundPrecision the round precision
 * @param int $additionalRound an additional round precision to apply to the percentage, for example if you want 23% to become 25% or 20%
 * @param bool $returnInteger whether return an integer or a float
 * @return float|int
 */
function getProductSalePercentage(\WC_Product $product, $round = true, $roundPrecision = 0, $additionalRound = 5, $returnInteger = true) {
    $percentage = 0;
    if(!$product->is_on_sale()){
        return 0;
    }
    if ($product->get_type() === 'variable') {
        $variations = $product->get_available_variations();
        $percentage = 0;
        $percentageArr = [];
        foreach ($variations as $variation) {
            $id = $variation['variation_id'];

            /** @var WC_Product_Variation $_product */
            $_product = new \WC_Product_Variation($id);

            if (!is_numeric($_product->is_on_sale()) && $_product->is_on_sale()) {
                $percentage = round((($_product->get_regular_price() - $_product->get_sale_price()) / $_product->get_regular_price()) * 100);
                $percentageArr[] = $percentage;
            }
        }
        if (!empty($percentageArr)) {
            $percentage = max($percentageArr);
        }
    } else {
        $regularPrice = $product->get_regular_price();
        $salePrice = $product->get_sale_price();
        $percentage = (($regularPrice - $salePrice) / $regularPrice) * 100;
    }
    if($percentage && $percentage !== 0){
        if($round){
            $percentage = round($percentage, $roundPrecision);
            if(\is_int($additionalRound) && $additionalRound !== 0){
                $percentage = round($percentage / $additionalRound) * $additionalRound;
            }
        }
        return $returnInteger ? (int) $percentage : (float) $percentage;
    }
    return $returnInteger ? (int) $percentage : (float) $percentage;
}

/**
 * Get a custom field from the provided product. If the product is a variation, the parent will be used as source
 * if the field doesn't exists.
 *
 * @param \WC_Product $product
 * @param string $fieldKey
 * @param string $default
 * @return string
 */
function getHierarchicalCustomFieldFromProduct(\WC_Product $product, string $fieldKey, string $default): string
{
    if(!method_exists($product,'get_id')){
        return $default;
    }
    $productId = $product->get_id();
    $fieldValue = get_post_meta($productId,$fieldKey, true);
    if((!\is_string($fieldValue) || $fieldValue === '') && $product instanceof \WC_Product_Variation){
        $parentId = $product->get_parent_id();
        $fieldValue = get_post_meta($parentId,$fieldKey, true);
    }
    if(!\is_string($fieldValue) || $fieldValue === ''){
        $fieldValue = $default;
    }
    return $fieldValue;
}