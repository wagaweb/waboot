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

    /** @var \WP_Taxonomy $t */
    foreach (get_taxonomies([], 'objects') as $t) {
        if (!isset($config['taxonomies'][$t->name])) {
            continue;
        }

        $config['taxonomies'][$t->name]['rewrite'] = $t->rewrite === false ? $t->name : $t->rewrite['slug'];
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
