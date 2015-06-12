<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * Override this template by copying it to yourtheme/woocommerce/content-single-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $post, $product;

?>

<?php
/**
 * woocommerce_before_single_product hook
 *
 * @hooked wc_print_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
    echo get_the_password_form();
    return;
}
?>

<article itemscope itemtype="<?php echo woocommerce_get_product_schema(); ?>" id="product-<?php the_ID(); ?>" <?php post_class(); ?>>

<div class="row">
    <div class="col-sm-6 product-gallery">
        <?php
        /**
         * woocommerce_before_single_product_summary hook
         *
         * @hooked woocommerce_show_product_sale_flash - 10
         * @hooked woocommerce_show_product_images - 20
         */
        do_action( 'woocommerce_before_single_product_summary' );
        ?>
    </div><!-- end .product-gallery -->

    <div class="col-sm-6 product-info">

        <?php echo get_the_term_list( $post->ID, 'product_cat', '<p class="prod-category">', ' - ', '</p>' ); ?>

        <?php do_action( 'waboot_entry_header' ); ?>

        <?php
        /**
         * woocommerce_single_product_summary hook
         *
         * @hooked woocommerce_template_single_title - 5 - Remove
         * @hooked woocommerce_template_single_rating - 10
         * @hooked woocommerce_template_single_price - 10
         * @hooked woocommerce_template_single_excerpt - 20
         * @hooked woocommerce_template_single_add_to_cart - 30
         * @hooked woocommerce_template_single_meta - 40 - Remove
         * @hooked woocommerce_template_single_sharing - 50
         */
        do_action( 'woocommerce_single_product_summary' );
        ?>

        <?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>

            <span class="sku_wrapper"><?php _e( 'SKU:', 'woocommerce' ); ?> <span class="sku" itemprop="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : __( 'N/A', 'woocommerce' ); ?></span>.</span>

        <?php endif; ?>

    </div><!-- end .product-info -->

</div>

<div class="row product-details">
    <div class="col-sm-12 columns">

        <?php
        /**
         * woocommerce_after_single_product_summary hook
         *
         * @hooked woocommerce_output_product_data_tabs - 10
         * @hooked woocommerce_upsell_display - 15
         * @hooked woocommerce_output_related_products - 20
         */
        do_action( 'woocommerce_after_single_product_summary' );
        ?>

        <meta itemprop="url" content="<?php the_permalink(); ?>" />

    </div>
</div>

</article><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>
