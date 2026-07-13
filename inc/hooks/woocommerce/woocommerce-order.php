<?php

namespace Waboot\inc\woocommerce;

if(!\function_exists('is_woocommerce')){
    return; //Do not load any of the following if WooCommerce is not enabled
}

/**
 * Saving all the prices the product has at the moment of the order creation.
 */
add_action('woocommerce_new_order_item', function (int $itemId, \WC_Order_Item $item): void {
    if ($item->get_type() !== 'line_item') {
        return;
    }

    /** @var \WC_Order_Item_Product $item */
    $product = $item->get_product();
    if (empty($product)) {
        return;
    }

    $regularPrice = $product->get_regular_price('edit');
    $item->update_meta_data('_saved_regular_price', $regularPrice);

    $salePrice = $product->get_sale_price('edit');
    $item->update_meta_data('_saved_sale_price', $salePrice);

    $price = $product->get_price('edit');
    $item->update_meta_data('_saved_price', $price);

    $item->save_meta_data();
}, 10, 2);

