<?php

namespace Waboot\inc\order_stats;

use Illuminate\Database\Schema\Blueprint;
use Waboot\inc\core\multilanguage\helpers\Polylang;
use Waboot\inc\core\utils\Utilities;
use function Waboot\inc\core\helpers\logException;

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
 * Standard: Filter out unwanted taxonomies
 */
add_filter('wawoo/order_stats/selected_taxonomies', static function (array $taxonomies) {
    //$taxonomies = array_diff($taxonomies,['post_translations','product_brand']);
    $taxonomies = array_diff($taxonomies,['post_translations','product_brand','product_visibility','product_shipping_class','pa_color','pa_size']);
    return $taxonomies;
},10,1);

/*
 * Standard: Sort product_cat terms
 */
add_filter('wawoo/order_stats/row/parse_terms', static function ($terms, int $productId, string $taxonomy, \WC_Order_Item $item) {
    if($taxonomy === 'product_cat'){
        $terms = Utilities::getPostTermsHierarchical($productId,$taxonomy,[],true,true);
    }
    return $terms;
},10,4);

/*
 * Standard: Translate terms
 */
add_filter('wawoo/order_stats/row/parse_terms', static function ($terms, int $productId, string $taxonomy, \WC_Order_Item $item) {
    if(!Polylang::isPolylang()){
        return $terms;
    }
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
 * Standard candidate: add recurring\new customer col
 */
add_action('wawoo/order_stats/table', static function (Blueprint $table){
    $table->boolean('new_customer')->nullable();
    //$table->integer('customer_orders')->nullable();
},10,1);

/*
 * Standard candidate: populate recurring\new customer col
 */
add_filter('wawoo/order_stats/row', static function (array $row, int $productId, \WC_Order_Item $item) {
    $orderId = $item->get_order_id();
    if(!$orderId){
        return $row;
    }
    $order = wc_get_order($orderId);
    if(!$order instanceof \WC_Order){
        return $row;
    }
    $customerId = $order->get_customer_id();
    if(!\is_int($customerId) || $customerId <= 0){
        return $row;
    }
    $c = wc_get_customer_order_count($customerId);
    $row['new_customer'] = $c === 1;
    //$row['customer_orders'] = $c;
    return $row;
},10,3);

