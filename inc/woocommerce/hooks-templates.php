<?php

namespace Waboot\woocommerce;

// Loop Template altering:
add_action("woocommerce_before_shop_loop_item", __NAMESPACE__."\\loop_product_inner_start", 1);
add_action("woocommerce_shop_loop_item_title", __NAMESPACE__."\\loop_product_details_start", 1);
add_action("woocommerce_shop_loop_item_title", __NAMESPACE__."\\loop_product_cat", 5);
add_action("woocommerce_after_shop_loop_item", __NAMESPACE__."\\loop_product_details_end", 50);
add_action("woocommerce_after_shop_loop_item", __NAMESPACE__."\\loop_product_inner_end", 60);

function loop_product_inner_start(){
    echo '<div class="woocommerce-loop-product__inner">';
}
function loop_product_inner_end(){
    echo '</div>';
}
function loop_product_details_start(){
    echo '<div class="woocommerce-loop-product__details">';
}
function loop_product_details_end(){
    echo '</div>';
}
function loop_product_cat(){
    global $product;
    echo '<p class="woocommerce-loop-product__cat">';
    $products_cats = wc_get_product_category_list($product->get_id());
    list($firstpart) = explode(',', $products_cats);
    echo $firstpart;
    echo '</p>';
}

// Single Product Template altering:
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
add_action( 'woocommerce_single_product_summary', __NAMESPACE__."\\single_product_title", 5 );
add_action( 'woocommerce_single_product_summary', __NAMESPACE__."\\single_product_cat", 3 );

function single_product_cat(){
    global $post;
    echo get_the_term_list( $post->ID, 'product_cat', '<p class="woocommerce-single-product__cat">', ' - ', '</p>' );
}
function single_product_title(){
   do_action( 'waboot/entry/header' );
}