<?php

namespace Waboot\inc\woocommerce;

if(!\function_exists('is_woocommerce')){
    return; //Do not load any of the following if WooCommerce is not enabled
}

/**
 * Limit search results to products only
 */
add_filter('pre_get_posts', function ($query) {
    if ($query->is_search && !is_admin() ) {
        $query->set('post_type',array('product'));
    }
    return $query;
});
