<?php
/** @var WC_Product $product */
global $product;
$images = array_merge([$product->get_image_id()], $product->get_gallery_image_ids());
?>

<div class="woocommerce-product-gallery">

    <div class="product-images">

        <?php if ($images): ?>

            <?php if (sizeof($images) > 1) : ?>

                <div class="product-images__main">
                    <div class="product-images__carousel <?php if (sizeof($images) > 1) { echo ' owl-carousel'; } ?> show-nav-hover">
                        <?php foreach ($images as $image): ?>
                            <div class="product-images__item">
                                <a href="<?php echo wp_get_attachment_image_src($image, 'full')[0]; ?>"
                                   rel="product-images__carousel" data-gall="product-images__carousel">
                                    <img src="<?php echo wp_get_attachment_image_src($image, 'large')[0]; ?>"
                                         class="product-images__image"/>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="product-images__sidebar">

                    <div class="product-images__dots">
                        <?php
                        $i = 0;
                        foreach ($images as $image) : ?>
                            <div data-index="<?php echo $i; ?>"
                                 class="product-images__dot <?php echo $i === 0 ? 'active' : ''; ?>">
                                <img class="product-images__dot-image"
                                     src="<?php echo wp_get_attachment_image_src($image, 'woocommerce_thumbnail')[0]; ?>"
                                />
                            </div>
                            <?php $i++; endforeach; ?>
                    </div>
                </div>

            <?php else : ?>

                <div class="product-images__main">
                    <?php foreach ($images as $image): ?>
                        <div class="product-images__item">
                            <a href="<?php echo wp_get_attachment_image_src($image, 'full')[0]; ?>"
                               rel="product-images__carousel" data-gall="product-images__carousel">
                                <img src="<?php echo wp_get_attachment_image_src($image, 'large')[0]; ?>"
                                     class="product-images__image"/>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

        <?php endif; ?>

    </div>
</div>

<?php do_action('woocommerce_single_product_after_closing'); ?>
