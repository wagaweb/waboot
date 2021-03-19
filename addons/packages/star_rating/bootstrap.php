<?php

namespace Waboot\addons\packages\star_rating;

/**
 * Remove or Move Star Rating in Archive
 */
add_action('woocommerce_after_shop_loop_item_title', function(){
    remove_action('woocommerce_after_shop_loop_item_title','woocommerce_template_loop_rating', 5 );
    //add_action('woocommerce_after_shop_loop_item_title','woocommerce_template_loop_rating', 15 );
}, 2 );

/**
 *
 */
add_filter('woocommerce_get_star_rating_html', function($html, $rating, $count){
    $rating = (int) $rating;
    $html = '';
    for($i = 1; $i <= 5; $i++){
        if($i <= $rating){
            $html .= '<i class="fas fa-star"></i>';
        }else{
            $html .= '<i class="fal fa-star"></i>';
        }
    }
    return $html;
}, 10, 3);
