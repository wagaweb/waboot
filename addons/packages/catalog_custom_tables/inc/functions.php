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