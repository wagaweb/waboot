<?php

namespace Waboot\inc\woocommerce;

if(!\function_exists('is_woocommerce')){
    return; //Do not load any of the following if WooCommerce is not enabled
}

// JVM WooCommerce Wishlist
add_action('init', function () {
    if (function_exists('jvm_woocommerce_add_to_wishlist')) {
        remove_action('woocommerce_after_shop_loop_item', 'jvm_woocommerce_add_to_wishlist', 15);
        add_action('woocommerce_before_shop_loop_item_title', 'jvm_woocommerce_add_to_wishlist', 13);
        add_filter('jvm_add_to_wishlist_class', function () {
            if (is_singular('product')) {
                return  ' jvm_add_to_wishlist single-add-to-wishlist';
            }
            return ' jvm_add_to_wishlist add-to-wishlist';
        });
    }
});


// Add to cart in wishlist page for JVM WooCommerce Wishlist
add_action('wp_footer', function() {
    if (function_exists('jvm_woocommerce_add_to_wishlist')) {
        $queriedObj = get_queried_object();
        if (!$queriedObj instanceof \WP_Post || $queriedObj->post_name !== 'wishlist') {
            return;
        }

        $pIds = jvm_woocommerce_wishlist_get_wishlist_product_ids();
        $prodData = [];
        foreach ($pIds as $id) {
            $product = wc_get_product($id);
            if (empty($product)) {
                continue;
            }

            $prodData[$product->get_id()] = [
                'permalink' => $product->get_permalink(),
                'type' => $product->get_type(),
                'inStock' => $product->is_in_stock(),
            ];
        }

        $jsonData = json_encode($prodData);
        ?>
        <script type="text/javascript">
            (function() {
                const wishlistData = <?php echo $jsonData; ?>;

                for (const [pId, pData] of Object.entries(wishlistData)) {
                    const $removeBtn = jQuery(`.jvm-woocommerce-wishlist-product .remove[data-product-id="${pId}"]`);
                    if ($removeBtn.length === 0) {
                        continue;
                    }

                    const $row = $removeBtn.parent().parent();
                    if (pData.inStock === true) {
                        let link = `${window.location.href}?add-to-cart=${pId}`;
                        if (pData.type === 'variable') {
                            link = pData.permalink;
                        }

                        $row.find('.product-stock-status').html(`<a href="${link}" class="btn">Aggiungi al carrello</a>`);
                    }
                }
            })();
        </script>
        <?php
    }
});
