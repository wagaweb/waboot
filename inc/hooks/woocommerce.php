<?php

namespace Waboot\inc\woocommerce;

use function Waboot\inc\core\Waboot;
use function Waboot\inc\getProductSalePercentage;

if(!\function_exists('is_woocommerce')){
    return; //Do not load any of the following if WooCommerce is not enabled
}

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

/*
 * Setup the wrapper
 */
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
add_action('woocommerce_before_main_content', __NAMESPACE__."\\wrapper_start", 10);
add_action('woocommerce_after_main_content', __NAMESPACE__."\\wrapper_end", 10);


/**
 * Set WooCommerce wrapper start tags
 *
 * @hooked 'woocommerce_before_main_content'
 */
function wrapper_start() {
    \get_template_part("templates/wrapper","start");
}

/**
 * Set WooCommerce wrapper end tags
 *
 * @hooked 'woocommerce_after_main_content'
 */
function wrapper_end() {
    \get_template_part("templates/wrapper","end");
}

/*
 * WooCommerce Titles Alter
 */

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

add_filter( 'woocommerce_show_page_title', function(){
    return false;
});


/*
 * Catalog Template altering:
 */

add_action( 'woocommerce_before_shop_loop', function(){
    echo '<div class="woocommerce-results">';
},10);
add_action( 'woocommerce_before_shop_loop', function(){
    echo '</div>';
},90);
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');


/*
 * Single Product Template altering:
 */
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
add_action('woocommerce_after_add_to_cart_form', function(){
    require_once get_stylesheet_directory().'/templates/view-parts/woocommerce/shipping-conditions.php';
},50);

/**
 * Remove product data tabs
 */
add_filter( 'woocommerce_product_tabs', function( $tabs ) {
    unset( $tabs['additional_information'] );  	// Remove the additional information tab
    return $tabs;
}, 98 );

add_filter('woocommerce_reset_variations_link', function () {
    return null;
});

/**
 * Limit Search Results For Specific Post Types
 */
add_filter('pre_get_posts', function ($query) {
    if ($query->is_search && !is_admin() ) {
        $query->set('post_type',array('product'));
    }
    return $query;
});

/**
 * Hide ALL shipping rates in ALL zones when Free Shipping is available
 */

add_filter( 'woocommerce_package_rates', function( $rates ) {

    $free = array();
    foreach ( $rates as $rate_id => $rate ) {
        if ( 'free_shipping' === $rate->method_id ) {
            $free[ $rate_id ] = $rate;
            break;
        }
    }
    return ! empty( $free ) ? $free : $rates;

}, 100 );

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


/**
 * Sales Percentage Label
 */
add_filter('woocommerce_sale_flash', function ($html, $post, $product) {
    if ($product instanceof \WC_Product && $product->is_on_sale() && getProductSalePercentage($product) != 0) {
        $percentage = getProductSalePercentage($product);
        if ($percentage <= 10) {
            $class = "small";
        } elseif ($percentage <= 30) {
            $class = "medium";
        } else {
            $class = "big";
        }
        $html = '<span class="woocommerce-loop-product__sale onsale ' . $class . '">-' . $percentage . '%</span>';
    }
    return $html;
}, 10, 3);

/**
 * Enable Gutenberg for WooCommerce Products
 */
/*
add_filter('use_block_editor_for_post_type', function($can_edit, $post_type){
    if($post_type == 'product'){
        $can_edit = true;
    }

    return $can_edit;
}, 10, 2);
*/

/**
 * Restores product visibility options for WooCommerce Product with Gutenberg enabled
 */
/*
add_action('woocommerce_product_options_general_product_data', function(){
    global $post, $thepostid, $product_object;

    $isGutenbergActive = (bool) apply_filters('use_block_editor_for_post_type', false, $post->post_type);
    if(!$isGutenbergActive){
        return;
    }

    if ( 'product' !== $post->post_type ) {
        return;
    }

    $thepostid          = $post->ID;
    $product_object     = $thepostid ? wc_get_product( $thepostid ) : new WC_Product();
    $current_visibility = $product_object->get_catalog_visibility();
    $current_featured   = wc_bool_to_string( $product_object->get_featured() );
    $visibility_options = wc_get_product_visibility_options();
    ?>
    <div class="options_group" id="catalog-visibility">
        <p><strong><?php esc_html_e( 'Catalog visibility:', 'woocommerce' ); ?></strong></p>

        <input type="hidden" name="current_visibility" id="current_visibility" value="<?php echo esc_attr( $current_visibility ); ?>" />
        <input type="hidden" name="current_featured" id="current_featured" value="<?php echo esc_attr( $current_featured ); ?>" />

        <?php
        echo '<p>' . esc_html__( 'This setting determines which shop pages products will be listed on.', 'woocommerce' ) . '</p>';

        foreach ( $visibility_options as $name => $label ) {
            echo '<p class="form-field">';
            echo '<input type="radio" name="_visibility" id="_visibility_' . esc_attr( $name ) . '" value="' . esc_attr( $name ) . '" ' . checked( $current_visibility, $name, false ) . ' data-label="' . esc_attr( $label ) . '" /> <label for="_visibility_' . esc_attr( $name ) . '" class="selectit" style="width: 200px !important;">' . esc_html( $label ) . '</label><br />';
            echo '</p>';
        }

        echo '<p class="form-field"><input type="checkbox" name="_featured" id="_featured" ' . checked( $current_featured, 'yes', false ) . ' /> <label for="_featured" style="width: 200px !important;">' . esc_html__( 'This is a featured product', 'woocommerce' ) . '</label></p>';
        ?>
    </div>
    <?php
});
*/

/**
 * Adds image to WooCommerce order emails
 */
/*
add_filter( 'woocommerce_email_order_items_args', function( $args ) {
    $args['show_image'] = true;
    $args['image_size'] = array( 50, 75 );
    return $args;
} );
*/


// Add Quantity Badge in Cart Icon
add_filter( 'woocommerce_widget_cart_item_quantity', function($html, $cart_item, $cart_item_key) {
    //var_dump($cart_item);
    $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
    $product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
    return '<span class="quantity" data-cart-item-quantity="' . $cart_item['quantity'] . '">' . sprintf( '%s &times; %s', $cart_item['quantity'], $product_price ) . '</span>';
}, 10, 3 );


// Hide Apple Pay on Product page
add_filter('wc_stripe_hide_payment_request_on_product_page', '__return_true');
// Adds Payment Request button (Apple Pay) on the Checkout page
add_filter('wc_stripe_show_payment_request_on_checkout', '__return_true');

/* JVM WooCommerce Wishlist */

add_action('init', function () {
    if (function_exists('jvm_woocommerce_add_to_wishlist')) {

        remove_action('woocommerce_after_shop_loop_item', 'jvm_woocommerce_add_to_wishlist', 15);

        add_action('woocommerce_before_shop_loop_item_title', 'jvm_woocommerce_add_to_wishlist', 13);

        add_filter('jvm_add_to_wishlist_class', function () {

            if (is_singular('product')) {
                return  ' jvm_add_to_wishlist single-add-to-wishlist';
            }

            return ' jvm_add_to_wishlist add-to-wishlist';
        });
    }
});
