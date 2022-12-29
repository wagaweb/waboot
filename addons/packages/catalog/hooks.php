<?php

namespace Waboot\addons\packages\catalog;

add_filter('init', function(){
    if(!defined('WB_CATALOG_BASEURL') || strpos($_SERVER[ 'REQUEST_URI' ], '/wp-json/') !== false){
        return;
    }
    add_filter(
        'woocommerce_blocks_product_grid_item_html',
        function (string $html, object $data, \WC_Product $product): string {
            return sprintf(
                '<li>%s</li>',
                renderSimpleCatalog(
                    [
                        'productIds' => [$product->get_id()],
                        'columns' => 1,
                        'showAddToCartBtn' => false,
                        'ga4' => [
                            'enabled' => false,
                            'listId' => sanitize_title(\Waboot\addons\packages\catalog\getGtagListName()),
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
});
