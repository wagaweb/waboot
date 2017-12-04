<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive.
 *
 * Override this template by copying it to yourtheme/woocommerce/archive-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

get_header( 'shop' ); ?>

    <?php
        /**
         * woocommerce_before_main_content hook
         *
         * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
         * @hooked Waboot\woocommerce\woocommerce_output_content_wrapper - 10
         * @hooked woocommerce_breadcrumb - 20
         */
        do_action( 'woocommerce_before_main_content' );
    ?>

    <?php $wb_wc_title_position_opt = is_shop() ? 'woocommerce_shop_title_position' : 'woocommerce_archives_title_position'; ?>

    <?php if ( apply_filters( 'woocommerce_show_page_title', true ) && \Waboot\functions\get_option($wb_wc_title_position_opt) == "bottom") : ?>
        <?php //NOTE: The "top" case is covered by "waboot/singular/title/display_flag" hook. ?>

        <?php $wb_wc_title_display_opt = is_shop() ? 'woocommerce_shop_display_title' : 'woocommerce_archives_display_title'; ?>

	    <?php if (\Waboot\functions\get_option($wb_wc_title_display_opt) == "1") : ?>
            <div class="title-wrapper">
                <h1 class="page-title entry-title"><?php woocommerce_page_title(); ?></h1>
            </div>
	    <?php endif; ?>

	    <?php do_action( 'woocommerce_archive_description' ); ?>

    <?php endif; ?>

    <?php do_action('waboot/woocommerce/loop'); ?>

    <?php
    /**
     * woocommerce_after_main_content hook
     *
     * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
     * @hooked Waboot\woocommerce\woocommerce_output_content_wrapper_end - 10
     */
    do_action( 'woocommerce_after_main_content' );
    ?>

    <?php
    /**
     * woocommerce_sidebar hook
     *
     * @hooked woocommerce_get_sidebar - 10
     */
    do_action( 'woocommerce_sidebar' );
    ?>

<?php get_footer( 'shop' ); ?>
