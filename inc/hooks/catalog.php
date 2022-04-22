<?php

namespace Waboot\inc\hooks;

/**
 * Prevent default WooCommerce Catalog Query
 */
add_filter(
    'posts_pre_query',
    function ($posts, \WP_Query $query) {
        if (!is_admin() && $query->is_main_query() && is_woocommerce()) {
            return [];
        }

        return $posts;
    },
    9999,
    2
);
