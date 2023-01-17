<?php

use function Waboot\addons\packages\catalog\renderSimpleCatalog;

defined('ABSPATH') || exit;

/** @var $cross_sells WC_Product[] */
if ($cross_sells) : ?>
    <div class="cross-sells">
        <?php
        $heading = apply_filters(
            'woocommerce_product_cross_sells_products_heading',
            __('You may be interested in&hellip;', 'woocommerce')
        );
        if ($heading) : ?>
            <h2><?php echo esc_html($heading); ?></h2>
        <?php endif;
        if (defined('WB_CATALOG_BASEURL')) {
            echo renderSimpleCatalog(
                [
                    'productIds' => array_map(function (WC_Product $p): int {
                        return $p->get_id();
                    }, $cross_sells),
                    'columns' => 4,
//                    'ga4' => [
//                        'enabled' => !isset($_POST['add-to-cart']),
//                        'listId' => sanitize_title(\Waboot\addons\packages\catalog\getGtagListName()),
//                        'listName' => \Waboot\addons\packages\catalog\getGtagListName(),
//                        'brandFallback' => get_bloginfo('name'),
//                    ],
                ]
            );
        } else {
            woocommerce_product_loop_start();
            foreach ($cross_sells as $cross_sell) {
                $post_object = get_post($cross_sell->get_id());
                setup_postdata($GLOBALS['post'] =& $post_object);
                wc_get_template_part('content', 'product');
            }
            woocommerce_product_loop_end();
        } ?>
    </div>
<?php endif;
wp_reset_postdata();
