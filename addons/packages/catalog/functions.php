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

    $wcPermalinks = get_option('woocommerce_permalinks');
    if (empty($config['productPermalink'])) {
        $config['productPermalink'] = trim($wcPermalinks['product_base'] ?? 'p', '/');
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
        foreach ($taxQueryFilters[$tax] as $queryFilter) {
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

    $json = json_encode($config);

    return <<<HTML
<div
    class="vue-catalog"
    catalog-config='$json'
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

    $wcPermalinks = get_option('woocommerce_permalinks');
    if (empty($config['productPermalink'])) {
        $config['productPermalink'] = trim($wcPermalinks['product_base'] ?? 'p', '/');
    }

    $config = apply_filters('catalog_addon_simple_catalog_config', $config);

    $config['taxonomies'] = array_values($config['taxonomies'] ?? []);

    $json = json_encode($config);

    return <<<HTML
<div
    class="vue-simple-catalog"
    catalog-config='$json'
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
