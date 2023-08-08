<?php

namespace Waboot\addons\packages\attributes;

use Waboot\inc\core\mvc\HTMLView;
use function Waboot\addons\getAddonDirectory;

add_filter('woocommerce_dropdown_variation_attribute_options_html', function ($html, $args) {
    $attribute_options = [];
    $options = $args['options'];
    $product = $args['product'];
    $attribute = $args['attribute'];
    $attributes = $product->get_variation_attributes();
    $variations = $product->get_available_variations();

    if ($attribute != 'pa_size') { return $html; }

    if (empty($options) && !empty($product) && !empty($attribute)) {
        $attributes = $product->get_variation_attributes();
        $options = $attributes[$attribute];
    }

    if (!empty($options)) {
        if ($product && taxonomy_exists($attribute)) {
            // Get terms if this is a taxonomy - ordered. We need the names too.
            $terms = wc_get_product_terms($product->get_id(), $attribute, array(
                'fields' => 'all',
            ));

            foreach ($terms as $term) {
                if (in_array($term->slug, $options, true)) {
                    $attribute_options[esc_attr($term->slug)]['value'] = esc_html(apply_filters('woocommerce_variation_option_name',
                        $term->name));
                }
            }
        } else {
            foreach ($options as $option) {
                $attribute_options[esc_attr($option)] = esc_html(apply_filters('woocommerce_variation_option_name',
                    $option));
            }
        }
    }

    $attribute_name = 'attribute_' . $attribute;

    foreach ($variations as $variation) {
        if(count($attributes) < 2){
            $attribute_options[$variation['attributes'][$attribute_name]]['in-stock'] = $variation['is_in_stock'];
        }
    }

    if ($attribute === 'pa_size') {
        $v = new HTMLView(getAddonDirectory('attributes').'/templates/attributes-size.php',false);
        $v->clean()->display([
            'options' => $attribute_options,
            'html' => $html,
        ]);
    }

    return $html;
}, 10, 2);
