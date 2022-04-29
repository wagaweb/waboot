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


// Save All Variations Prices (Regular and Sale) in Parent Meta
add_action(
    'woocommerce_after_product_object_save',
    function (\WC_Product $product): void {
        if ($product->get_type() !== 'variable') {
            return;
        }

        $product->delete_meta_data('_discounts');
        /** @var \WC_Product_Variable $product */
        $variations = $product->get_available_variations('object');
        $discounts = [];
        $onSale = false;
        foreach ($variations as $v) {
            if ($v->is_on_sale()) {
                $onSale = true;
            }

            $discounts[] = [
                'variation' => $v->get_id(),
                'on_sale' => $v->is_on_sale(),
                'price' => $v->get_price(),
                'base_price' => $v->get_regular_price(),
            ];
        }
        usort($discounts, function (array $a, array $b) {
            $priceA = (float)$a['price'];
            $priceB = (float)$b['price'];

            return $priceA - $priceB;
        });
        if ($onSale) {
            $product->update_meta_data('_discounts', $discounts);
        }
        $product->save_meta_data();
    },
    10,
    1
);
