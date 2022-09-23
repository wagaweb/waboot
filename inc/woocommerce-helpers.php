<?php

namespace Waboot\inc;

/**
 * @param string $type
 * @param array $args
 * @return false|int
 * @throws \WC_Data_Exception
 * @throws \RuntimeException
 * @see: https://stackoverflow.com/questions/52937409/create-programmatically-a-product-using-crud-methods-in-woocommerce-3
 */
function createProduct(string $type, array $args){
    if(!\function_exists('wc_get_product_object')){
        return false;
    }
    $product = wc_get_product_object($type);
    if(!$product instanceof \WC_Product){
        throw new \RuntimeException('Invalid product type');
    }
    if(!isset($args['name']) || !\is_string($args['name']) || $args['name'] === ''){
        throw new \RuntimeException('Invalid product name');
    }
    if(!isset($args['sku']) || !\is_string($args['sku']) || $args['sku'] === ''){
        throw new \RuntimeException('Invalid product sku');
    }
    $product->set_sku($args['sku']);
    $product->set_name($args['name']);
    if(isset($args['slug']) && \is_string($args['slug']) && $args['slug'] !== ''){
        $product->set_slug($args['slug']);
    }
    $description = $args['description'] ?? '';
    $shortDescription = $args['short_description'] ?? wp_trim_words($description,50,null);
    $status = $args['status'] ?? 'publish';
    $product->set_description($description);
    $product->set_short_description($shortDescription);
    $product->set_status($status);
    if(isset($args['regular_price'])){
        $regularPrice = str_replace(',','.',trim($args['regular_price']));
    }else{
        $regularPrice = '0';
    }
    $product->set_regular_price($regularPrice);
    if(isset($args['sale_price'])){
        $salePrice = str_replace(',','.',trim($args['sale_price']));
        $product->set_sale_price($salePrice);
    }
    $productId = $product->save();
    if(!\is_int($productId) || $productId === 0){
        throw new \RuntimeException('Unable to save product');
    }
    return $productId;
}

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
 * Get the sum of all prices of bundled items
 * @param int $bundleId
 * @param array $quantities an array with products id as keys and their quantity as values
 * @return array
 */
function getBundleRealTotals(int $bundleId, array $quantities): array {
    $prices = [
        'subtotal' => 0,
        'total' => 0
    ];
    $productIds = getBundledProductIds($bundleId);
    if(!\is_array($productIds) || count($productIds) === 0){
        return $prices;
    }
    foreach ($productIds as $productId){
        $product = wc_get_product($productId);
        if(!$product instanceof \WC_Product){
            continue;
        }
        $quantity = $quantities[$productId] ?? 1;
        $productRegularPrice = (float) $product->get_regular_price() * $quantity;
        $productSalePrice = (float) $product->get_price() * $quantity;
        $prices['subtotal'] += $productRegularPrice;
        $prices['total'] += $productSalePrice;
    }
    return $prices;
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
 * Get a custom field from the provided product. If the product is a variation and doesn't have the field,
 * the parent will be used as source.
 *
 * @param \WC_Product $product
 * @param string $fieldKey
 * @param string $default
 * @return string
 */
function getHierarchicalCustomFieldFromProduct(\WC_Product $product, string $fieldKey, string $default): string {
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

/**
 * Get the product image data (url, with, height)
 * @see: \WC_Product::get_image()
 *
 * @param \WC_Product $product
 * @param string $size
 * @param array $attr
 * @param bool $placeholder
 * @return array
 */
function getWCProductImageData( \WC_Product $product, $size = 'woocommerce_thumbnail', $attr = array(), $placeholder = true): array {
    $image = [];
    if($product->get_image_id()){
        $image = wp_get_attachment_image_src($product->get_image_id(), $size, false);
    }elseif($product->get_parent_id()){
        $parent_product = wc_get_product($product->get_parent_id());
        if ( $parent_product ) {
            $image = getWCProductImageData($parent_product, $size, $attr, $placeholder);
        }
    }

    if(!$image && $placeholder){
        $image = wc_placeholder_img_src($size);
    }

    if(!\is_array($image)){
        $image = [];
    }

    return $image;
}

/**
 * Get the data to render the mini cart. Useful to use in ajax calls.
 * @see: /woocommerce/templates/cart/mini-cart.php
 *
 * @return array
 */
function getMiniCartData(): array {
    if(!function_exists('WC')){
        return [];
    }
    if(!property_exists(WC(),'cart') || !WC()->cart instanceof \WC_Cart){
        return [];
    }
    if(WC()->cart->is_empty()){
        return [
            'no_items_message' => esc_html__('No products in the cart.', 'woocommerce')
        ];
    }
    $resultData = [
        'total_items_qty' => 0,
        'total_different_items_qty' => 0
    ];
    foreach ( WC()->cart->get_cart() as $cartItemKey => $cartItem ){
        $product = apply_filters( 'woocommerce_cart_item_product', $cartItem['data'], $cartItem, $cartItemKey );
        $productID = apply_filters( 'woocommerce_cart_item_product_id', $cartItem['product_id'], $cartItem, $cartItemKey );
        if ( $product && $product->exists() && $cartItem['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cartItem, $cartItemKey ) ) {
            $productName = apply_filters( 'woocommerce_cart_item_name', $product->get_name(), $cartItem, $cartItemKey );
            //$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $product->get_image(), $cartItem, $cartItemKey );
            $thumbnail = getWCProductImageData($product);
            $productPrice = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $product ), $cartItem, $cartItemKey );
            $productPrice = strip_tags($productPrice);
            //$productPriceXQuantity = apply_filters( 'woocommerce_cart_product_price', wc_price( $cartItem['line_total'] ), $product );
            //$productPermalink = apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink( $cartItem ) : '', $cartItem, $cartItemKey );
            $productPermalink = $product->is_visible() ? $product->get_permalink( $cartItem ) : '';
            $itemListClass = esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cartItem, $cartItemKey ) );
            /*$removeLink = apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                'woocommerce_cart_item_remove_link',
                sprintf(
                    '<a href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">&times;</a>',
                    esc_url( wc_get_cart_remove_url( $cartItemKey ) ),
                    esc_attr__( 'Remove this item', 'woocommerce' ),
                    esc_attr( $productID ),
                    esc_attr( $cartItemKey ),
					esc_attr( $_product->get_sku() )
                ),
                $cartItemKey
            );*/
            $removeLink = esc_url(wc_get_cart_remove_url($cartItemKey));
            $removeLinkLabel = esc_attr__('Remove this item', 'woocommerce');
            $sku = $product->get_sku();
            $itemData = [];
            /*
             * Getting item cart additional data
             * @see: wc_get_formatted_cart_item_data()
             */
            if ( $cartItem['data']->is_type( 'variation' ) && is_array( $cartItem['variation'] ) ) {
                foreach ( $cartItem['variation'] as $name => $value ) {
                    $taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );
                    if ( taxonomy_exists( $taxonomy ) ) {
                        $term = get_term_by( 'slug', $value, $taxonomy );
                        if ( ! is_wp_error( $term ) && $term && $term->name ) {
                            $value = $term->name;
                        }
                        $label = wc_attribute_label( $taxonomy );
                    }else {
                        // If this is a custom option slug, get the options name.
                        $value = apply_filters( 'woocommerce_variation_option_name', $value, null, $taxonomy, $cartItem['data'] );
                        $label = wc_attribute_label( str_replace( 'attribute_', '', $name ), $cartItem['data'] );
                    }
                    if ( '' === $value || wc_is_attribute_in_product_name( $value, $cartItem['data']->get_name() ) ) {
                        continue;
                    }
                    $itemData[] = [
                        'key' => $label,
                        'value' => $value,
                    ];
                }
            }
            $itemData = apply_filters( 'woocommerce_get_item_data', $itemData, $cartItem );
            foreach ( $itemData as $key => $data ) {
                // Set hidden to true to not display meta on cart.
                if (!empty( $data['hidden'])){
                    unset($itemData[$key]);
                    continue;
                }
                $itemData[$key]['key'] = ! empty($data['key']) ? $data['key'] : $data['name'];
                $itemData[$key]['display'] = ! empty($data['display']) ? $data['display'] : $data['value'];
            }
            /*
             * Finalize the item
             */
            $resultData['items'][] = [
                'key' => $cartItemKey,
                'product_name' => $productName,
                'sku' => $sku,
                'thumbnail' => $thumbnail,
                'price' => $productPrice,
                'permalink' => $productPermalink,
                'qty' => $cartItem['quantity'],
                'list_element_class' => $itemListClass,
                'remove_url' => $removeLink,
                'remove_url_label' => $removeLinkLabel,
                'data' => $itemData
            ];
            $resultData['total_items_qty'] += $cartItem['quantity'];
            $resultData['total_different_items_qty'] += 1;
        }
    }
    $subtotal = WC()->cart->get_cart_subtotal();
    $subtotal = strip_tags($subtotal);
    $resultData['subtotal'] = $subtotal;
    $resultData['view_cart_url'] = esc_url(wc_get_cart_url());
    $resultData['view_cart_label'] = esc_html__('View cart', 'woocommerce');
    $resultData['goto_checkout_url'] = esc_url(wc_get_checkout_url());
    $resultData['goto_checkout_label'] = esc_html__('Checkout', 'woocommerce');
    $resultData = apply_filters('waboot/woocommerce/cart_data', $resultData);
    return $resultData;
}

/**
 * @param int $orderNumber
 * @return int
 */
function getOrderIdByOrderNumber(int $orderNumber): ?int {
    global $wpdb;
    $r = $wpdb->get_results('SELECT post_id FROM '.$wpdb->postmeta.' WHERE meta_key = "_order_number" AND meta_value = "'.$orderNumber.'"');
    if(\is_array($r) && count($r) > 0){
        $rr = $r[0];
        return (int) $rr->post_id;
    }
    return null;
}

/**
 * @return int[]
 */
function getAllVariableProductIds(): array {
    static $variableProductsIndex;
    if(\is_array($variableProductsIndex)){
        return $variableProductsIndex;
    }
    global $wpdb;

    $sql = <<<SQL
select p.ID
from $wpdb->posts p
inner join $wpdb->term_relationships tr on tr.object_id = p.ID
inner join $wpdb->term_taxonomy tt on tt.term_taxonomy_id = tr.term_taxonomy_id
inner join $wpdb->terms t on t.term_id = tt.term_id
where p.post_type = 'product' and tt.taxonomy = 'product_type' and t.name = 'variable'
SQL;

    $variableProductsIndex = array_map('intval', $wpdb->get_col($sql));
    return $variableProductsIndex;
}

/**
 * @param $parentProductId
 * @return array
 */
function getAllProductVariationIds($parentProductId): array {
    global $wpdb;

    $q = <<<SQL
select p.ID
from $wpdb->posts p
where p.post_parent = %d and p.post_type = 'product_variation'
SQL;
    $q = $wpdb->prepare($q,$parentProductId);
    $r = $wpdb->get_results($q);
    if(!\is_array($r) || count($r) === 0){
        return [];
    }
    return wp_list_pluck($r,'ID');
}

/**
 * Set _price meta accordingly to _regular_price and _sale_price metas (only if _price is 0 or empty)
 * @param int $productId
 * @return void
 */
function adjustPriceMeta(int $productId): void {
    $priceMeta = get_post_meta($productId,'_price', true);
    $regularPriceMeta = get_post_meta($productId,'_regular_price', true);
    $salePriceMeta = get_post_meta($productId,'_sale_price', true);
    if($priceMeta === '' || $priceMeta === '0' || $priceMeta === 0){
        if($salePriceMeta !== ''){
            update_post_meta($productId,'_price',$salePriceMeta);
        }elseif($regularPriceMeta !== ''){
            update_post_meta($productId,'_price',$regularPriceMeta);
        }
    }
}

/**
 * Sync a variable product with it's children.
 * @param int $variableProductId
 * @param bool $save If true, the product object will be saved to the DB before returning it.
 * @return \WC_Product Synced product object.
 */
function syncVariableProductData(int $variableProductId, bool $save = true) {
    delete_transient("wc_product_children_$variableProductId");
    return \WC_Product_Variable::sync($variableProductId, $save);
}

/**
 * @param int $variableProductId
 * @return string
 */
function syncVariableProductStockStatus(int $variableProductId){
    $variationsIds = getAllProductVariationIds($variableProductId);
    if(empty($variationsIds)){
        throw new \RuntimeException('Product #'.$variableProductId.' has no variations');
    }
    $stockStatus = 'outofstock';
    foreach ($variationsIds as $variationsId){
        $qty = (int) get_post_meta($variationsId,'_stock', true);
        if($qty > 0){
            $stockStatus = 'instock';
            break;
        }
    }
    update_post_meta($variableProductId,'_stock_status',$stockStatus);
    return $stockStatus;
}