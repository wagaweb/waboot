<?php

namespace Waboot\inc\hooks;

add_action('init', function () {
    if (defined('GTM4WP_WPFILTER_EEC_PRODUCT_ARRAY')) {
        add_filter(
            GTM4WP_WPFILTER_EEC_PRODUCT_ARRAY,
            function ($data, $event) {
                $product = wc_get_product_id_by_sku($data['sku']);
                if (empty($product)) {
                    $product = $data['id'];
                }

                $brands = wp_get_post_terms($product, 'product_brand', ['parent' => 0]);
                if (empty($brands)) {
                    return $data;
                }

                /** @var \WP_Term $b */
                foreach ($brands as $i => $b) {
                    $k = 'brand';
                    if ($i > 0) {
                        $k .= '_' . $i;
                    }

                    $data[$k] = $b->name;
                }

                return $data;
            },
            10,
            2
        );
    }

    if (defined('GTM4WP_WPFILTER_COMPILE_DATALAYER')) {
        add_filter(
            GTM4WP_WPFILTER_COMPILE_DATALAYER,
            function ($data) {
                if (isset($_POST['add-to-cart'])) {
                    return [];
                }

                return $data;
            }
        );
    }
});
