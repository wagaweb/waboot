<?php

if (defined('WB_CATALOG_BASEURL')) {
    $catalog = [
        'searchString' => get_search_query(),
        'layoutMode' => 'sidebar',
        'ga4' => [
            'enabled' => true,
            'listId' => sanitize_title(\Waboot\addons\packages\catalog\getGtagListName()),
            'listName' => \Waboot\addons\packages\catalog\getGtagListName(),
            'brandFallback' => get_bloginfo('name'),
        ],
    ];

    $taxonomies = [];
    $excludeFromSearch = get_term_by('slug', 'exclude-from-search', 'product_visibility');
    $outOfStock = get_term_by('slug', 'outofstock', 'product_visibility');
    if ($excludeFromSearch !== false && $outOfStock !== false) {
        $taxonomies['product_visibility'] = [
            'taxonomy' => 'product_visibility',
            'exclude' => [$excludeFromSearch->term_id, $outOfStock->term_id],
            'enableFilters' => true,
        ];
    }

    $catalog['taxonomies'] = $taxonomies;

    echo \Waboot\addons\packages\catalog\renderCatalog($catalog);
} else {
    if (have_posts()) { ?>
        <ul class="products columns-4">
            <?php while (have_posts()) {
                the_post();
                wc_get_template_part('content', 'product');
            }
            ?>
        </ul>
        <?php
        \Waboot\inc\renderPostNavigation('nav-below'); } else {
        get_template_part('/templates/parts/content', 'none');
    }
}
