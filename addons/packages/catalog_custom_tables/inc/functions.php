<?php

namespace Waboot\addons\packages\catalog_custom_tables\inc;

/**
 * Parse a string like this "Cat A | Cat B | Cat C" into an array of categories
 *
 * @param string $categories
 * @param string $delimiter
 * @return array
 */
function extractCategoriesStringTree(string $categories, string $delimiter = '|'): array {
    $categoriesArray = explode($delimiter,$categories);
    if(!\is_array($categoriesArray) || count($categoriesArray) === 0){
        return [];
    }
    $categoriesArray = array_map('trim', $categoriesArray);
    return $categoriesArray;
}

/**
 * @param string $string
 * @return string
 */
function createSlugForDB(string $string): string {
    return sanitize_title($string);
}

/**
 * @param array $termList
 * @param string $taxonomy
 * @return array
 */
function generateTermListTree(array $termList, string $taxonomy): array {
    $termList = array_filter($termList, static function ($item){ return $item instanceof \WP_Term; });
    if(empty($termList)){
        return [];
    }

    $hierarchicalTermList = [];
    foreach ($termList as $term){
        if(!$term instanceof \WP_Term){
            continue;
        }
    }
    return $hierarchicalTermList;
}