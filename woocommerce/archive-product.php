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
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );

?>

<?php $wb_wc_title_position_opt = is_shop() ? 'woocommerce_shop_title_position' : 'woocommerce_archives_title_position'; ?>

<?php if ( apply_filters( 'woocommerce_show_page_title', true ) && \Waboot\functions\get_option($wb_wc_title_position_opt) == "bottom") : ?>
    <?php //NOTE: The "top" case is covered by "waboot/singular/title/display_flag" hook. ?>

    <?php $wb_wc_title_display_opt = is_shop() ? 'woocommerce_shop_display_title' : 'woocommerce_archives_display_title'; ?>

    <?php if (\Waboot\functions\get_option($wb_wc_title_display_opt) == "1") : ?>
        <div class="entry__header">
            <h1 class="entry__title archive__title"><?php woocommerce_page_title(); ?></h1>
        </div>
    <?php endif; ?>

    <?php
    /**
     * Hook: woocommerce_archive_description.
     *
     * @hooked woocommerce_taxonomy_archive_description - 10
     * @hooked woocommerce_product_archive_description - 10
     */
    do_action( 'woocommerce_archive_description' );
    ?>

<?php endif; ?>

<?php do_action('waboot/woocommerce/loop'); ?>

<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
