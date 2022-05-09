<?php

namespace Waboot\addons\packages\catalog;

/**
 * Prevent default WooCommerce Catalog Query
 */
add_filter(
    'posts_pre_query',
    function ($posts, \WP_Query $query) {
        if (defined('WB_CATALOG_BASEURL') && !is_admin() && $query->is_main_query() && is_woocommerce()) {
            return [];
        }

        return $posts;
    },
    100,
    2
);

add_filter(
    'woocommerce_blocks_product_grid_item_html',
    function (string $html, object $data, \WC_Product $product): string {
        if (!defined('WB_CATALOG_BASEURL')) {
            return $html;
        }

        return sprintf(
            '<li>%s</li>',
            renderSimpleCatalog(
                [
                    'productIds' => [$product->get_id()],
                    'columns' => 1,
                    'showAddToCartBtn' => false,
                    'gtag' => [
                        'enabled' => false,
                        'listName' => \Waboot\addons\packages\catalog\getGtagListName(),
                        'brandFallback' => get_bloginfo('name'),
                    ],
                ]
            )
        );
    },
    20,
    3
);
