<?php

use function Waboot\addons\packages\catalog\renderSimpleCatalog;

defined('ABSPATH') || exit;

/** @var WC_Product[] $related_products */
if ($related_products) : ?>
    <section class="related products">
        <?php
        $heading = apply_filters('woocommerce_product_related_products_heading', __('Related products', 'woocommerce'));
        if ($heading) : ?>
            <h2><?php echo esc_html($heading); ?></h2>
        <?php endif;
        if (defined('WB_CATALOG_BASEURL')) {
            echo renderSimpleCatalog(
                [
                    'productIds' => array_map(function (WC_Product $p): int {
                        return $p->get_id();
                    }, $related_products),
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
            foreach ($related_products as $related_product) {
                $post_object = get_post($related_product->get_id());
                setup_postdata($GLOBALS['post'] =& $post_object);
                wc_get_template_part('content', 'product');
            }
            woocommerce_product_loop_end();
            wp_reset_postdata();
        }
        ?>
    </section>
<?php endif;
