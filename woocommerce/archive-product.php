<?php

use function Waboot\addons\packages\catalog\renderCatalog;

defined('ABSPATH') || exit;

get_header('shop');

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action('woocommerce_before_main_content');

?>

    <header class="woocommerce-products-header">
        <?php
        if (apply_filters('woocommerce_show_page_title', true)) : ?>
            <h1 class="woocommerce-products-header__title page-title"><?php
                woocommerce_page_title(); ?></h1>
        <?php
        endif; ?>

        <?php
        /**
         * Hook: woocommerce_archive_description.
         *
         * @hooked woocommerce_taxonomy_archive_description - 10
         * @hooked woocommerce_product_archive_description - 10
         */
        do_action('woocommerce_archive_description');
        ?>
    </header>

<?php

if (defined('WB_CATALOG_BASEURL')) {
    $catalog = [
//        'productsPerPage' => 24,
//        'columns' => 3,
//        'enableFilters' => false,
//        'enableOrder' => false,
//        'enablePriceFilter' => false,
//        'showAddToCartBtn' => false,
        'layoutMode' => 'block', // 'block', header' or 'sidebar'
//        'teleportSidebar' => '.aside__wrapper',
//        'gtag' => [
//            'enabled' => true,
//            'listName' => \Waboot\addons\packages\catalog\getGtagListName(),
//            'brandFallback' => get_bloginfo('name'),
//        ],
//        'ga4' => [
//            'enabled' => true,
//            'listId' => sanitize_title(\Waboot\addons\packages\catalog\getGtagListName()),
//            'listName' => \Waboot\addons\packages\catalog\getGtagListName(),
//            'brandFallback' => get_bloginfo('name'),
//        ],
    ];

    $taxonomies = [
        'product_cat' => [
            'taxonomy' => 'product_cat',
            'title' => __('Categorie prodotto', LANG_TEXTDOMAIN),
//            'type' => 'permalink',
//            'enableFilter' => false,
//            'selectedParent' => 12345,
//            'exclude' => [12345,12346],
//            'maxDepth' => 2,
//            'fullOpen' => true,
        ],
    ];

    $excludeFromCatalog = get_term_by('slug', 'exclude-from-catalog', 'product_visibility');
    $outOfStock = get_term_by('slug', 'outofstock', 'product_visibility');
    if ($excludeFromCatalog !== false && $outOfStock !== false) {
        $taxonomies['product_visibility'] = [
            'taxonomy' => 'product_visibility',
            'exclude' => [$excludeFromCatalog->term_id, $outOfStock->term_id],
            'enableFilter' => false,
        ];
    }

    $currObj = get_queried_object();
    if ($currObj instanceof WP_Term) {
        $taxonomies[$currObj->taxonomy]['selectedParent'] = $currObj->term_id;
    }

    $catalog['taxonomies'] = $taxonomies;

    echo renderCatalog($catalog);
} else {
    if (woocommerce_product_loop()) {
        /**
         * Hook: woocommerce_before_shop_loop.
         *
         * @hooked woocommerce_output_all_notices - 10
         * @hooked woocommerce_result_count - 20
         * @hooked woocommerce_catalog_ordering - 30
         */
        do_action('woocommerce_before_shop_loop');

        woocommerce_product_loop_start();

        if (wc_get_loop_prop('total')) {
            while (have_posts()) {
                the_post();

                /**
                 * Hook: woocommerce_shop_loop.
                 */
                do_action('woocommerce_shop_loop');

                wc_get_template_part('content', 'product');
            }
        }

        woocommerce_product_loop_end();

        /**
         * Hook: woocommerce_after_shop_loop.
         *
         * @hooked woocommerce_pagination - 10
         */
        do_action('woocommerce_after_shop_loop');
    } else {
        /**
         * Hook: woocommerce_no_products_found.
         *
         * @hooked wc_no_products_found - 10
         */
        do_action('woocommerce_no_products_found');
    }
}

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action('woocommerce_sidebar');

get_footer('shop');
