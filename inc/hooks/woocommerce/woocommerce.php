<?php

namespace Waboot\inc\woocommerce;

use function Waboot\inc\core\Waboot;

if(!\function_exists('is_woocommerce')){
    return; //Do not load any of the following if WooCommerce is not enabled
}

/**
 * Setup the wrapper
 */
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
add_action('woocommerce_before_main_content', __NAMESPACE__."\\wrapper_start", 10);
add_action('woocommerce_after_main_content', __NAMESPACE__."\\wrapper_end", 10);

/**
 * Set WooCommerce wrapper start tags
 * @hooked 'woocommerce_before_main_content'
 */
function wrapper_start() {
    \get_template_part("templates/wrapper","start");
}

/**
 * Set WooCommerce wrapper end tags
 * @hooked 'woocommerce_after_main_content'
 */
function wrapper_end() {
    \get_template_part("templates/wrapper","end");
}

// Remove WooCommerce default styles
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

// Remove WooCommerce Gutenberg Style
add_action( 'init', function() {
    wp_deregister_style( 'wc-block-style' );
}, 100 );

// Remove WooCommerce Bundle Products Style
add_action( 'wp_enqueue_scripts', function() {
    wp_deregister_style( 'wc-bundle-css' );
    wp_deregister_style( 'wc-bundle-style' );
    //wp_dequeue_style( 'wc-bundle-style' );
}, 101 );

//Page titles altering for WooCommerce:
add_action('waboot/layout/title', function(){
    if(is_shop()){
        Waboot()->renderView('templates/view-parts/main-title.php',[
            'title' => woocommerce_page_title(false),
            'classes' => ''
        ]);
    }
});
add_filter('waboot/main/title/display_flag', function($can_display_title,$post,$currentPageType){
    if(is_product() || is_shop()){
        return false;
    }
    return $can_display_title;
},5,3);
add_filter( 'woocommerce_show_page_title', function(){
    return false;
});

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

