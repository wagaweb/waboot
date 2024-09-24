<?php
/** @var WC_Product $product */
global $product;
$images = array_merge([$product->get_image_id()], $product->get_gallery_image_ids());
?>

<div class="woocommerce-product-gallery">

    <div class="product-images">

        <?php if ($images): ?>
            <div class="product-images__item">
                <a href="<?php echo wp_get_attachment_image_src($product->get_image_id(), 'full')[0]; ?>"
                   rel="product-images__carousel" data-gall="product-images__carousel">
                    <img src="<?php echo wp_get_attachment_image_src($product->get_image_id(), 'full')[0]; ?>"
                         class="product-images__image"/>
                </a>
            </div>
        <?php endif; ?>

        <?php
        if (have_rows('product_video_repeater')):
            while (have_rows('product_video_repeater')): the_row();
                $video = get_sub_field('product_video_oembed');
                if ($video):
                    ?>
                    <div class="product-images__item">
                        <?php echo $video; ?>
                    </div>
                <?php
                endif;
            endwhile;
        endif;
        ?>

        <?php if ($images): ?>
            <?php foreach ($product->get_gallery_image_ids() as $image): ?>
                <div class="product-images__item">
                    <a href="<?php echo wp_get_attachment_image_src($image, 'full')[0]; ?>"
                       rel="product-images__carousel" data-gall="product-images__carousel">
                        <img src="<?php echo wp_get_attachment_image_src($image, 'full')[0]; ?>"
                             class="product-images__image"/>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="product__accordion product__accordion--desktop">
        <?php
        require get_stylesheet_directory().'/templates/view-parts/woocommerce/product-info.php';
        woocommerce_upsell_display(2);
        ?>
    </div>
</div>

<?php do_action('woocommerce_single_product_after_closing'); ?>
