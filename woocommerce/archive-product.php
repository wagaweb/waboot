<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined('ABSPATH') || exit;

if(!defined('WB_USE_LOCAl_CATALOG') || WB_USE_LOCAl_CATALOG === false){
    defined('WB_CATALOG_BASEURL') || exit;
}

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

$currObj = get_queried_object();

$taxonomies = [
    'product_cat' => [
        'taxonomy' => 'product_cat',
        'title' => __('Categorie prodotto', LANG_TEXTDOMAIN),
        'enableFilter' => true,
        'type' => 'dropdown',
    ],
    /*
    'product_collection' => [
        'taxonomy' => 'product_collection',
        'title' => __('Collezioni', LANG_TEXTDOMAIN),
        'enableFilter' => true,
        'type' => 'checkbox',
    ]
    */
];

if ($currObj instanceof WP_Term) {
    $taxonomies[$currObj->taxonomy]['selectedParent'] = (string)$currObj->term_id;
}

$excludeFromCatalog = get_term_by('slug', 'exclude-from-catalog', 'product_visibility');
if ($excludeFromCatalog !== false) {
    $taxonomies['product_visibility'] = [
        'taxonomy' => 'product_visibility',
        'exclude' => [(string)$excludeFromCatalog->term_id]
    ];
}

$catalog = [
    'apiBaseUrl' => WB_CATALOG_BASEURL,
    'productsPerPage' => 24,
    //'teleportSidebar' => '.aside__wrapper',
    'productPermalink' => 'p',
    'taxonomies' => array_values($taxonomies),
    'language' => str_replace('_', '-', get_locale()),
    'enableOrder' => true,
    'enablePriceFilter' => true,
];

echo \Waboot\inc\renderCatalog($catalog); ?>

<?php
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
