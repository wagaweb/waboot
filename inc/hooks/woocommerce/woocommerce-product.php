<?php

namespace Waboot\inc\woocommerce;

use function Waboot\inc\getProductSalePercentage;

if(!\function_exists('is_woocommerce')){
    return; //Do not load any of the following if WooCommerce is not enabled
}

// Single Product Template altering:

add_action('woocommerce_before_single_product_summary',function(){
    echo '<div class="product__main">';
},1);

add_action('woocommerce_before_single_product_summary',function(){
    echo '<div class="product__summary">';
},25);

add_action('woocommerce_after_single_product_summary',function(){
    echo '</div><!-- closed product__main -->';
},1);

add_action('woocommerce_after_single_product_summary',function(){
    echo '</div><!-- closed product__summary -->';
},13);

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

add_action( 'woocommerce_single_product_summary', function(){
    global $post;
    echo get_the_term_list( $post->ID, 'product_cat', '<p class="woocommerce-single-product__cat">', ' - ', '</p>' );
}, 3 );


//Change location on Product Description and Short Description
//remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
//add_action( 'woocommerce_after_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
//remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
//add_action( 'woocommerce_single_product_summary', 'the_content', 20 );


// Shows templates after the add to cart form
add_action('woocommerce_after_add_to_cart_form', function(){
    require_once get_stylesheet_directory().'/templates/view-parts/woocommerce/shipping-conditions.php';
    //require_once get_stylesheet_directory().'/templates/view-parts/woocommerce/product-share.php';
},50);


// Removes some product data tabs
add_filter( 'woocommerce_product_tabs', function( $tabs ) {
    unset( $tabs['additional_information'] );  	// Remove the additional information tab
    return $tabs;
}, 98 );


// Removes the "Clear" button for product variations
add_filter('woocommerce_reset_variations_link', function () {
    return null;
});


// Hide Apple Pay on Product page
add_filter('wc_stripe_hide_payment_request_on_product_page', '__return_true');


/**
 * Sales Percentage Label (blocks)
 */
add_filter('woocommerce_blocks_product_grid_item_html', function ($html, $data, $product) {
    if ($product instanceof \WC_Product && $product->is_on_sale()) {
        $percentage = getProductSalePercentage($product);
        if ($percentage <= 10) {
            $class = "small";
        } elseif ($percentage <= 30) {
            $class = "medium";
        } else {
            $class = "big";
        }
        $data->badge = '<span class="woocommerce-loop-product__sale onsale ' . $class . '"> ' . $percentage . '% off</span>';
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

/**
 * Sales Percentage Label
 */
add_filter('woocommerce_blocks_product_grid_item_html', function ($html, $post, $product) {
    if ($product instanceof \WC_Product && $product->is_on_sale()) {
        $percentage = getProductSalePercentage($product);
        if ($percentage <= 10) {
            $class = "small";
        } elseif ($percentage <= 30) {
            $class = "medium";
        } else {
            $class = "big";
        }
        $html = '<span class="woocommerce-loop-product__sale onsale ' . $class . '"> ' . $percentage . '% off</span>';
    }
    return $html;
}, 10, 3);


/**
 * Add wrapper tag before product price + product sku
 */
add_action( 'woocommerce_single_product_summary', function() { ?>
    <div class="product__meta">
<?php }, 5 );

add_action( 'woocommerce_single_product_summary', function() {
    global $product;
?>
        <span class="product__sku">SKU: <?php echo $product->get_sku(); ?></span>
    </div>
<?php }, 10 );
