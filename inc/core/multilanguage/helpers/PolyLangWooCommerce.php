<?php

namespace Waboot\inc\core\multilanguage\helpers;

trait PolyLangWooCommerce
{
    /**
     * @return void
     */
    public static function detachProductUpdateAndCreateActions(): void
    {
        remove_action( 'woocommerce_update_product', array( PLLWC()->products, 'save_product' ) );
        remove_action( 'pll_created_sync_post', array( PLLWC()->products, 'copy_variations' ), 5);
        self::detachVariationUpdateAndCreateActions();
    }

    /**
     * @return void
     */
    public static function detachVariationUpdateAndCreateActions(): void
    {
        /*
         * @see: wp-content/plugins/polylang-wc/include/products.php @ line 143 (// Avoid reverse sync.)
         */
        remove_action( 'woocommerce_new_product_variation', array( PLLWC()->products, 'save_variation' ) );
        remove_action( 'woocommerce_update_product_variation', array( PLLWC()->products, 'save_variation' ) );
    }

    /*public static function isSkuInLanguage(string $sku, string $lang): bool {
        $postIds = getProductIdsBySku($sku);
        return false;
    }*/

    /**
     * @param string $sku
     * @param string $lang
     * @return int|null
     */
    public static function getLocalizedProductIdBySku(string $sku, string $lang): ?int
    {
        $productId = wc_get_product_id_by_sku($sku);
        if(!\is_int($productId) || $productId === 0){
            return null;
        }
        if(!function_exists('\pll_get_post_language')){
            return null;
        }
        $productLang = \pll_get_post_language($productId);
        if($productLang === $lang){
            return $productId;
        }
        return self::getLocalizedPostId((int) $productId, $lang);
    }

    /**
     * @param string $sku
     * @param string $lang
     * @return int|null
     */
    public static function getLocalizedProductIdBySkuUsingStore(string $sku, string $lang): ?int
    {
        if(!class_exists('\PLLWC_Product_Language_CPT')){
            return null;
        }
        $store = new \PLLWC_Product_Language_CPT();
        $productId = (int) $store->get_product_id_by_sku($sku,$lang);
        if($productId > 0){
            return $productId;
        }
        return null;
    }
}