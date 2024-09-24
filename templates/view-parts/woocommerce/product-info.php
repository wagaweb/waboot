<section id="productTabs">
    <?php
    global $product;
    $productDetails = wpautop($product->get_description());
    $productShipping = get_field('product_shipping');
    $productRefunds = get_field('product_refunds');
    ?>

    <div class="accordion product__details">
        <?php if( $productDetails ) : ?>
            <div class="accordion__item">
                <h3 class="accordion__header">
                    <?php _e('Dettagli prodotto', LANG_TEXTDOMAIN); ?>
                </h3>
                <div class="accordion__body">
                    <?php echo $productDetails; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if( $productShipping ) : ?>
            <div class="accordion__item">
                <h3 class="accordion__header">
                    <?php _e('Spedizioni', LANG_TEXTDOMAIN); ?>
                </h3>
                <div class="accordion__body">
                    <?php echo $productShipping; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if( $productRefunds ) : ?>
            <div class="accordion__item">
                <h3 class="accordion__header">
                    <?php _e('Cambi e resi', LANG_TEXTDOMAIN); ?>
                </h3>
                <div class="accordion__body">
                    <?php echo $productRefunds; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>