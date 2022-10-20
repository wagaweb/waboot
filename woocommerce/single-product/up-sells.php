<?php

use function Waboot\addons\packages\catalog\renderSimpleCatalog;

defined('ABSPATH') || exit;

/** @var $upsells WC_Product[] */
if ($upsells) : ?>
    <section class="up-sells upsells products">
        <?php
        $heading = apply_filters(
            'woocommerce_product_upsells_products_heading',
            __('You may also like&hellip;', 'woocommerce')
        );
        if ($heading) : ?>
            <h2><?php echo esc_html($heading); ?></h2>
        <?php endif;
        if (defined('WB_CATALOG_BASEURL')) {
            echo renderSimpleCatalog(
                [
                    'productIds' => array_map(function (WC_Product $p): int {
                        return $p->get_id();
                    }, $upsells),
                    'columns' => 4,
//                    'gtag' => [
//                        'enabled' => true,
//                        'listName' => \Waboot\addons\packages\catalog\getGtagListName(),
//                        'brandFallback' => get_bloginfo('name'),
//                    ],
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
            foreach ($upsells as $upsell) {
                $post_object = get_post($upsell->get_id());
                setup_postdata($GLOBALS['post'] =& $post_object);
                wc_get_template_part('content', 'product');
            }
            woocommerce_product_loop_end();
            wp_reset_postdata();
        }
        ?>
    </section>
<?php endif;
