<?php

namespace Waboot\addons\packages\catalog;

/**
 * @param string $html
 * @param object $data
 * @param \WC_Product $product
 * @return string
 * @hooked 'woocommerce_blocks_product_grid_item_html' - 20
 */
function renderSimpleCatalogInWoocommerceBlock(string $html, object $data, \WC_Product $product): string
{
    return sprintf(
        '<li>%s</li>',
        renderSimpleCatalog(
            [
                'productIds' => [$product->get_id()],
                'columns' => 1,
                'showAddToCartBtn' => false,
                'showQuantityInput' => true,
                'ga4' => [
                    'enabled' => true,
                    'listId' => sanitize_title(getGtagListName()),
                    'listName' => getGtagListName(),
                ],
            ]
        )
    );
}

/**
 * @return void
 * @hooked 'init'
 */
function maybeHookSimpleCatalogInWoocommerceBlock(): void
{
    if (!defined('WB_CATALOG_BASEURL') || strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) {
        return;
    }
    add_filter(
        'woocommerce_blocks_product_grid_item_html',
        __NAMESPACE__ . '\\renderSimpleCatalogInWoocommerceBlock',
        20,
        3
    );
}

add_action('init', __NAMESPACE__ . '\\maybeHookSimpleCatalogInWoocommerceBlock');

/**
 * @param int $productId
 * @return void
 * @hooked 'woocommerce_update_product'
 * @hooked 'woocommerce_update_product_variation'
 */
function updateProductAttributeListMetadataHook(int $productId): void
{
    $product = wc_get_product($productId);
    if (empty($product)) {
        return;
    }

    if ($product->get_type() === 'variation') {
        $product = wc_get_product($product->get_parent_id());
        if (empty($product)) {
            return;
        }
    }

    updateCatalogProductMetadata($product);
}

add_action('woocommerce_update_product', __NAMESPACE__ . '\\updateProductAttributeListMetadataHook');
add_action('woocommerce_update_product_variation', __NAMESPACE__ . '\\updateProductAttributeListMetadataHook');
