<?php

namespace Waboot\inc\order_stats;

use Illuminate\Database\Schema\Blueprint;
use Waboot\inc\core\utils\Utilities;
use function Waboot\inc\core\helpers\logException;
use function Waboot\inc\getImportedProductThumbnailImageSrc;

add_action('woocommerce_order_status_changed', static function (int $orderId, string $statusFrom, string $statusTo, \WC_Order $order) {
    try {
        if(strpos($statusTo,'wc-') === false){
            $statusTo = 'wc-'.$statusTo;
        }
        if(!\in_array($statusTo,getOrderStatusForStats())){
            return;
        }
        $rows = generateOrderStatRows($orderId);
        if(empty($rows)){
            return;
        }
        foreach ($rows as $row) {
            insertStatRow($row);
        }
    } catch (OrderStatsException $e) {
        logException($e,'Waboot\inc\order_stats\woocommerce_order_status_changed()');
    }
},99,4);

/*
 * Hiphop: Filter out unwanted taxonomies
 */
add_filter('wawoo/order_stats/selected_taxonomies', static function (array $taxonomies) {
    $taxonomies = array_diff($taxonomies,['post_translations','product_brand']);
    return $taxonomies;
},10,1);

/*
 * Hiphop: Sort product_cat terms
 */
add_filter('wawoo/order_stats/row/parse_terms', static function ($terms, int $productId, string $taxonomy, \WC_Order_Item $item) {
    if($taxonomy === 'product_cat'){
        $terms = Utilities::getPostTermsHierarchical($productId,$taxonomy,[],true,true);
    }
    return $terms;
},10,4);

/*
 * Hiphop: Translate terms
 */
add_filter('wawoo/order_stats/row/parse_terms', static function ($terms, int $productId, string $taxonomy, \WC_Order_Item $item) {
    if($taxonomy === 'language'){
        return $terms;
    }
    if(!\is_array($terms) || empty($terms)){
        return $terms;
    }
    $translatedTerms = [];
    foreach ($terms as $term){
        $tLang = pll_get_term_language($term->term_id);
        if($tLang === 'it'){
            $translatedTerms[] = $term;
        }else{
            $termTranslations = \pll_get_term_translations($term->term_id);
            if(!\is_array($termTranslations) || empty($termTranslations)){
                $translatedTerms[] = $term;
            }
            if(!array_key_exists('it',$termTranslations)){
                $translatedTerms[] = $term;
            }else{
                $translatedTerm = get_term($termTranslations['it'],$taxonomy);
                if($translatedTerm instanceof \WP_Term){
                    $translatedTerms[] = $translatedTerm;
                }else{
                    $translatedTerms[] = $term;
                }
            }
        }
    }
    return $translatedTerms;
},10,4);

/*
 * Hiphop: Put parent and child term of product_cat in different columns
 */
add_filter('wawoo/order_stats/row/parse_taxonomy', static function (array $row, string $taxonomy, $terms, int $productId, \WC_Order_Item $item) {
    if($taxonomy !== 'product_cat'){
        return $row;
    }
    if(!\is_array($terms) || empty($terms)){
        return $row;
    }
    if(count($terms) > 1){
        $row[$taxonomy] = htmlspecialchars_decode($terms[1]->name);
        $row[$taxonomy.'_parent'] = htmlspecialchars_decode($terms[0]->name);
    }else{
        $row[$taxonomy] = htmlspecialchars_decode($terms[0]->name);
    }
    return $row;
},10,5);

/*
 * Hiphop
 */
add_filter('wawoo/order_stats/row', static function (array $row, int $productId, \WC_Order_Item $item) {
    $imgSrc = getImportedProductThumbnailImageSrc($productId);
    if(\is_string($imgSrc) && !empty($imgSrc)){
        $row['main_image'] = $imgSrc;
    }
    return $row;
},10,3);

/*
 * Hiphop: Create the product_cat_parent column
 */
add_action('wawoo/order_stats/table/taxonomy_cols/tax', static function (Blueprint $table, string $taxonomy){
    if($taxonomy === 'product_cat'){
        $table->string('product_cat_parent')->default('');
    }
},10,2);

/*
 * Hiphop: Add main image col
 */
add_action('wawoo/order_stats/table', static function (Blueprint $table){
    $table->string('main_image')->default('');
},10,1);