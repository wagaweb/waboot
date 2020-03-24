<?php

namespace Waboot\addons\packages\star_rating;

/**
 *
 */
add_filter('woocommerce_get_star_rating_html', function($html, $rating, $count){
    $rating = (int) $rating;
    $html = '';
    for($i = 1; $i <= 5; $i++){
        if($i <= $rating){
            $html .= '<img src="/wp-content/themes/paneliquido/assets/images/drop-full.png" />';
        }else{
            $html .= '<img src="/wp-content/themes/paneliquido/assets/images/drop-empty.png" />';
        }
    }
    return $html;
}, 10, 3);