<?php
/**
 * The template for displaying product content within loops.
 *
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.6.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

// Ensure visibility
if ( ! $product || ! $product->is_visible() ) {
    return;
}

// Extra post classes
$classes = array();
$classes[] = of_get_option('woocommerce_cat_items', 'col-sm-3');
?>
<div <?php post_class( $classes ); ?>>

    <div class="wb-product-wrapper">

        <?php
        /**
         * woocommerce_before_shop_loop_item hook.
         *
         * @hooked woocommerce_template_loop_product_link_open - 10
         */
        do_action( 'woocommerce_before_shop_loop_item' );
        ?>

        <a href="<?php the_permalink(); ?>">

            <?php
                /**
                 * woocommerce_before_shop_loop_item_title hook
                 *
                 * @hooked woocommerce_show_product_loop_sale_flash - 10
                 * @hooked woocommerce_template_loop_product_thumbnail - 10
                 */
                do_action( 'woocommerce_before_shop_loop_item_title' );
            ?>

        </a>

        <div class="wb-product-details">

            <p class="wb-product-cat">
                <?php
                $products_cats = $product->get_categories();
                list($firstpart) = explode(',', $products_cats);
                echo $firstpart;
                ?>
            </p>

            <h4>
                <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
                    <?php the_title(); ?>
                </a>
            </h4>

            <?php
                /**
                 * woocommerce_after_shop_loop_item_title hook
                 *
                 * @hooked woocommerce_template_loop_rating - 5
                 * @hooked woocommerce_template_loop_price - 10
                 */
                do_action( 'woocommerce_after_shop_loop_item_title' );
            ?>

            <?php
                /**
                 * woocommerce_after_shop_loop_item hook
                 *
                 * @hooked woocommerce_template_loop_add_to_cart - 10
                 */
                do_action( 'woocommerce_after_shop_loop_item' );
            ?>

        </div>

    </div>

</div>
