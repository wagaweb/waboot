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
    if (empty($config['apiBaseUrl'])) {
        $config['apiBaseUrl'] = WB_CATALOG_BASEURL;
    }

    if (empty($config['baseUrl'])) {
        $config['baseUrl'] = get_site_url();
    }

    if (empty($config['language'])) {
        $config['language'] = str_replace('_', '-', get_locale());
    }

    if (empty($config['pricesIncludeTax'])) {
        $config['pricesIncludeTax'] = wc_string_to_bool(get_option('woocommerce_prices_include_tax'));
    }

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
    if (empty($config['apiBaseUrl'])) {
        $config['apiBaseUrl'] = WB_CATALOG_BASEURL;
    }

    if (empty($config['baseUrl'])) {
        $config['baseUrl'] = get_site_url();
    }

    if (empty($config['language'])) {
        $config['language'] = str_replace('_', '-', get_locale());
    }

    $config = apply_filters('catalog_addon_simple_catalog_config', $config);

    $config['taxonomies'] = array_values($config['taxonomies'] ?? []);

    $json = htmlspecialchars(json_encode($config));

    return <<<HTML
<div
    class="vue-simple-catalog"
    catalog-config="$json"
></div>
HTML;
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

    /** @var array<int, \WC_Product_Variation> $variations */
    $variations = [];
    foreach ($p->get_children() as $cId) {
        $child = wc_get_product($cId);
        if (empty($child)) {
            continue;
        }

        $variations[$child->get_id()] = $child;
    }
    if (count($variations) === 0) {
        $p->delete_meta_data('_catalog_data');
        $p->save_meta_data();
        return;
    }

    $variationsClone = $variations;
    uasort($variationsClone, fn($a, $b) => (float)$a->get_price('edit') - (float)$b->get_price('edit'));
    $first = $variationsClone[array_key_first($variationsClone)];
    $last = $variationsClone[array_key_last($variationsClone)];

    $catalogData = [
        'minPrice' => (float)$first->get_price('edit'),
        'minBasePrice' => (float)$first->get_regular_price('edit'),
        'maxPrice' => (float)$last->get_price('edit'),
        'maxBasePrice' => (float)$last->get_price('edit'),
    ];
    $p->update_meta_data('_catalog_data', $catalogData);

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
        $p->save_meta_data();
        return;
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
            $p->save_meta_data();
            return;
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
        $catalogData['variations']['products'][] = [
            'id' => $v->get_id(),
            'sku' => $v->get_sku(),
            'name' => $v->get_name(),
            'attributeTerm' => $attrTerm,
            'price' => (float)$v->get_price('edit'),
            'basePrice' => (float)$v->get_regular_price('edit'),
            'taxClass' => $v->get_tax_class(),
            'stockStatus' => $v->get_stock_status(),
            'data' => $termValues[$attrTerm] ?? null,
        ];
    }

    $p->update_meta_data('_catalog_data', wp_json_encode($catalogData));
    $p->save_meta_data();
}
