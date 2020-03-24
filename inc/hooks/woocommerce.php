<?php

namespace Waboot\inc\woocommerce;

use function Waboot\inc\core\Waboot;

if(!\function_exists('is_woocommerce')){
    return; //Do not load any of the following if WooCommerce is not enabled
}

add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

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


add_action( 'woocommerce_single_product_summary', function(){
    global $post;
    echo get_the_term_list( $post->ID, 'product_cat', '<p class="woocommerce-single-product__cat">', ' - ', '</p>' );
}, 3 );

//Change location on Product Description and Short Description
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
add_action( 'woocommerce_after_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
add_action( 'woocommerce_single_product_summary', 'the_content', 20 );
