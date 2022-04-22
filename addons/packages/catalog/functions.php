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
    if (empty($config['baseUrl'])) {
        $config['baseUrl'] = get_site_url();
    }

    if(empty($config['productPermalink'])){
        $config['productPermalink'] = 'prodotto';
    }

    foreach (TAX_MAP as $slug => $tax) {
        $terms = $_GET[$slug] ?? null;
        if (empty($terms)) {
            continue;
        }

        if (!is_array($terms)) {
            $terms = [$terms];
        }

        foreach ($terms as $slug) {
            $t = get_term_by('slug', $slug, $tax);
            if (empty($t)) {
                continue;
            }

            $config['taxonomies'][$tax]['selectedTerms'][] = (string)$t->term_id;
        }
    }

    $config['taxonomies'] = array_values($config['taxonomies']);

    $json = json_encode($config);

    return <<<HTML
<div
    id="vue-catalog"
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
