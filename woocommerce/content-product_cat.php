<?php
/**
 * The template for displaying product category thumbnails within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product_cat.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.6.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Extra post classes
$classes = array();
$classes[] = of_get_option('waboot_woocommerce_cat_items', 'col-sm-3');
?>
<div <?php wc_product_cat_class( $classes, $category ); ?>>

    <div class="wb-product-wrapper">

        <?php
        /**
         * woocommerce_before_subcategory hook.
         *
         * @hooked woocommerce_template_loop_category_link_open - 10
         */
        do_action( 'woocommerce_before_subcategory', $category );
        ?>

        <a href="<?php echo get_term_link( $category->slug, 'product_cat' ); ?>">

            <?php
                /**
                 * woocommerce_before_subcategory_title hook
                 *
                 * @hooked woocommerce_subcategory_thumbnail - 10
                 */
                do_action( 'woocommerce_before_subcategory_title', $category );
            ?>

        </a>

        <div class="wb-product-details">

            <h4>
                <?php
                    echo $category->name;

                    if ( $category->count > 0 )
                        echo apply_filters( 'woocommerce_subcategory_count_html', ' <mark class="count">(' . $category->count . ')</mark>', $category );
                ?>
            </h4>

            <?php
                /**
                 * woocommerce_after_subcategory_title hook
                 */
                do_action( 'woocommerce_after_subcategory_title', $category );
            ?>

            <?php do_action( 'woocommerce_after_subcategory', $category ); ?>

        </div>

    </div>

</div>
