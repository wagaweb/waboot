<?php

namespace Waboot\inc\woocommerce;

use function Waboot\inc\getProductSalePercentage;

if(!\function_exists('is_woocommerce')){
    return; //Do not load any of the following if WooCommerce is not enabled
}

// Catalog Template altering

add_action( 'woocommerce_before_shop_loop', function(){
    echo '<div class="woocommerce-results">';
},10);

add_action( 'woocommerce_before_shop_loop', function(){
    echo '</div>';
},90);

remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');


/**
 * Sales Percentage Label (blocks)
 */
add_filter('woocommerce_blocks_product_grid_item_html', function ($html, $data, $product) {
    if ($product instanceof \WC_Product && $product->is_on_sale() && getProductSalePercentage($product) != 0) {
        $percentage = getProductSalePercentage($product);
        if ($percentage <= 10) {
            $class = "small";
        } elseif ($percentage <= 30) {
            $class = "medium";
        } else {
            $class = "big";
        }
        $data->badge = '<span class="woocommerce-loop-product__sale onsale ' . $class . '">-' . $percentage . '%</span>';
        $html = "<li class=\"wc-block-grid__product\">
            <a href=\"{$data->permalink}\" class=\"wc-block-grid__product-link\">
                {$data->image}
                {$data->title}
            </a>
            {$data->badge}
            {$data->price}
            {$data->rating}
            {$data->button}
		</li>";
        return $html;
    }
    return $html;
}, 11, 3);
