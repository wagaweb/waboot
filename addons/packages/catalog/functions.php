<?php

namespace Waboot\addons\packages\catalog;

/**
 * Render Vue Catalog.
 *
 * @param array $config
 * @return string
 */
function renderCatalog(array $config): string
{
    $config = array_merge(getBaseCatalogConfig(), $config);

    $taxRewrites = [];
    $taxQueryFilters = [];
    /** @var \WP_Taxonomy $t */
    foreach (get_taxonomies([], 'objects') as $t) {
        $taxRewrites[$t->name] = $t->rewrite === false ? $t->name : $t->rewrite['slug'];
        $taxQueryFilters[$t->name] = [$t->name];
    }
    $taxRewrites = apply_filters('catalog_addon_tax_rewrites', $taxRewrites);
    $taxQueryFilters = apply_filters('catalog_addon_tax_query_filters', $taxQueryFilters);

    foreach ($config['taxonomies'] as $tax => $options) {
        foreach ($taxQueryFilters[$tax] ?? [] as $queryFilter) {
            $terms = $_GET[$queryFilter] ?? null;
            if (empty($terms)) {
                continue;
            }

            if (!is_array($terms)) {
                $terms = [$terms];
            }

            foreach ($terms as $idOrSlug) {
                if (is_numeric($idOrSlug)) {
                    $t = get_term_by('id', (int)$idOrSlug, $tax);
                } else {
                    $t = get_term_by('slug', $idOrSlug, $tax);
                }
                if (empty($t)) {
                    continue;
                }

                $config['taxonomies'][$tax]['selectedTerms'][] = (string)$t->term_id;
            }
        }

        $config['taxonomies'][$tax]['rewrite'] = $taxRewrites[$tax] ?? '';
    }

    $config = apply_filters('catalog_addon_config', $config);

    $config['taxonomies'] = array_values($config['taxonomies'] ?? []);

    $json = htmlspecialchars(json_encode($config));

    return <<<HTML
<div
    id="vue-catalog"
    catalog-config="$json"
></div>
HTML;
}

function renderSimpleCatalog(array $config): string
{
    $config = apply_filters('catalog_addon_simple_catalog_config', array_merge(getBaseCatalogConfig(), $config));

    $config['taxonomies'] = array_values($config['taxonomies'] ?? []);

    $json = htmlspecialchars(json_encode($config));

    return <<<HTML
<div
    class="vue-simple-catalog"
    catalog-config="$json"
></div>
HTML;
}

function getBaseCatalogConfig(): array
{
    $config = [
        'apiBaseUrl' => WB_CATALOG_BASEURL,
        'baseUrl' => get_site_url(),
        'language' => str_replace('_', '-', get_locale()),
        'pricesIncludeTax' => wc_string_to_bool(get_option('woocommerce_prices_include_tax')),
    ];

    $user = wp_get_current_user();
    if (!empty($user)) {
        $config['userRole'] = $user->roles[array_key_first($user->roles)] ?? null;
    }

    if (function_exists('wcpbc_get_woocommerce_country')) {
        $config['country'] = wcpbc_get_woocommerce_country();
    }

    return apply_filters('catalog_addon_base_config', $config);
}

/**
 * Return the Gtag list name for the current queried object.
 *
 * @return string
 *
 * @todo: handle more queried object types (like pages).
 * @todo: consider to handle translations.
 */
function getGtagListName(): string
{
    if (is_search()) {
        return 'Search Results';
    }

    $queriedObject = get_queried_object();
    if ($queriedObject instanceof \WP_Term) {
        /** @var array<string, \WP_Taxonomy> $taxMap */
        $taxMap = [];
        /** @var \WP_Taxonomy $t */
        foreach (get_taxonomies([], 'objects') as $t) {
            $taxMap[$t->name] = $t;
        }

        $taxonomy = $taxMap[$queriedObject->taxonomy] ?? null;
        if ($taxonomy !== null) {
            return sprintf('%s - %s', $taxonomy->label, $queriedObject->name);
        }
    }

    return 'General Product List';
}

/**
 * Get the term map of the provided taxonomy. It uses an internal static cache.
 * @param string $tax
 * @param bool $clearCache
 * @return array<int, \WP_Term>
 */
function getTermMap(string $tax, bool $clearCache = false): array
{
    static $cache = [];
    if ($clearCache) {
        $cache = [];
    }

    $terms = $cache[$tax] ?? null;
    if ($terms === null) {
        $terms = [];
        $res = get_terms(['taxonomy' => $tax, 'hide_empty' => false]);
        foreach ($res as $t) {
            $terms[$t->term_id] = $t;
        }

        $cache[$tax] = $terms;
    }

    return $terms;
}

function updateCatalogProductMetadata(\WC_Product $p): void
{
    if ($p->get_type() !== 'variable') {
        return;
    }
    /** @var \WC_Product_Variable $p */

    /** @var array<int, \WC_Product_Variation> $variations */
    $variations = [];
    foreach ($p->get_available_variations('objects') as $child) {
        $variations[$child->get_id()] = $child;
    }
    if (count($variations) === 0) {
        $p->delete_meta_data('_catalog_data');
        $p->save_meta_data();
        return;
    }

    try {
        /** @var array{
         *     name: string,
         *     value: string,
         *     position: int,
         *     is_visible: int,
         *     is_variation: int,
         *     is_taxonomy: int
         * }[] $varAttr
         */
        $varAttrs = get_post_meta($p->get_id(), '_product_attributes', true);
        if (empty($varAttrs)) {
            $varAttrs = [];
        }

        $varAttrs = array_filter($varAttrs, fn($a) => $a['is_variation'] ?? 0);
        $attrCount = count($varAttrs);
        if ($attrCount !== 1) {
            throw new \Exception('Invalid attribute count');
        }

        $varAttr = array_values($varAttrs)[0];
        $attrName = $varAttr['name'];

        $wvsMeta = $p->get_meta('_woo_variation_swatches_product_settings');
        if (empty($wvsMeta)) {
            $wvsMeta = $p->get_meta('_wvs_product_attributes');
        }

        $termValues = [];
        $attrType = 'select';
        if (empty($wvsMeta)) {
            if ($varAttr['is_taxonomy']) {
                foreach (getTermMap($attrName) as $t) {
                    $termValues[$t->slug] = $t->name;
                }
            } else {
                foreach (explode(' | ', $varAttr['value']) as $v) {
                    $termValues[$v] = $v;
                }
            }
        } else {
            $wvsAttr = $wvsMeta[$attrName] ?? null;
            if ($wvsAttr === null) {
                throw new \Exception('Cannot find attribute wvs data');
            }

            $attrType = $wvsAttr['type'];
            $taxTerms = getTermMap($attrName);
            foreach ($wvsAttr['terms'] as $tId => $d) {
                $t = $taxTerms[$tId] ?? null;
                if ($t === null) {
                    continue;
                }

                switch ($attrType) {
                    case 'color':
                        $termValues[$t->slug] = get_term_meta($t->term_id, 'product_attribute_color', true);
                        break;
                    case 'image':
                        $attachmentId = get_term_meta($t->term_id, 'product_attribute_image', true);
                        if (!empty($attachmentId) && function_exists('acf_get_attachment')) {
                            $termValues[$t->slug] = acf_get_attachment($attachmentId);
                        }
                        break;
                    case 'select':
                        $termValues[$t->slug] = $t->name;
                        break;
                }
            }
        }

        $catalogData['variations'] = [
            'attribute' => $attrName,
            'type' => $attrType,
            'products' => [],
        ];
        foreach ($variations as $v) {
            $attrTerm = get_post_meta($v->get_id(), 'attribute_' . $attrName, true);
            $data = $termValues[$attrTerm] ?? null;
            if (!$data) {
                continue;
            }

            $catalogData['variations']['products'][] = [
                'id' => $v->get_id(),
                'sku' => $v->get_sku(),
                'name' => $v->get_name(),
                'attributeTerm' => $attrTerm,
                'price' => (float)$v->get_price('edit'),
                'basePrice' => (float)$v->get_regular_price('edit'),
                'userRolePrices' => getAllUserRolePriceMeta($v->get_id()),
                'zonePrices' => getAllZonePriceMeta($v->get_id()),
                'taxClass' => $v->get_tax_class(),
                'stockStatus' => $v->get_stock_status(),
                'data' => $data,
            ];
        }

        $p->update_meta_data('_catalog_data', wp_json_encode($catalogData));
        $p->save_meta_data();
    } catch (\Throwable $e) {
        $p->delete_meta_data('_catalog_data');
        $p->save_meta_data();
    }
}

function getAllUserRolePriceMeta(int $productId): array
{
    global $wpdb;

    $sql = <<<SQL
select `meta_key` as `key`, `meta_value` as `value`
from $wpdb->postmeta
where `post_id` = %d and `meta_key` like '_role_base_price_%'
SQL;
    $res = $wpdb->get_results($wpdb->prepare($sql, $productId));

    $prices = [];
    foreach ($res as $r) {
        $value = unserialize(unserialize($r->value));
        preg_match('/_role_base_price_(\w+)/', $r->key, $matches);
        if (empty($matches[1])) {
            continue;
        }

        $prices[$matches[1]] = [
            'type' => $value['discount_type'],
            'value' => (float)$value['discount_value'],
        ];
    }

    return $prices;
}

function getAllZonePriceMeta(int $productId): array
{
    $priceZoneOption = get_option('wc_price_based_country_regions');
    if (empty($priceZoneOption)) {
        return [];
    }

    $zoneKeys = array_keys($priceZoneOption);
    $prices = [];
    foreach ($zoneKeys as $k) {
        $price = get_post_meta($productId, "_{$k}_price", true);
        $regularPrice = get_post_meta($productId, "_{$k}_regular_price", true);
        $prices[$k] = [
            'price' => (float)$price,
            'basePrice' => (float)$regularPrice,
        ];
    }

    return $prices;
}
