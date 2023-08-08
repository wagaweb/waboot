<?php

namespace Waboot\inc\core\multilanguage\wpml;

define('WPML_DEFAULT_LANG_CODE','en');

class WPML
{
    /**
     * @return string
     */
    public static function getCurrentLanguage(): string {
        if(function_exists('wpml_get_current_language')){
            return wpml_get_current_language();
        }
        return WPML_DEFAULT_LANG_CODE;
    }

    /**
     * @param string $sku
     * @param string $language
     * @throws \RuntimeException
     * @return int
     */
    public static function getTranslatedProduct(string $sku, string $language): int {
        global $wpdb;
        $postTable = $wpdb->prefix.'posts';
        $postMetaTable = $wpdb->prefix.'postmeta';
        $translationTable = $wpdb->prefix.'icl_translations';
        //
        // SELECT * FROM `zxevp_posts` as pt
        //       JOIN `zxevp_postmeta` as pm ON pt.ID = pm.post_id
        //       JOIN `zxevp_icl_translations` tt ON pt.ID = tt.element_id
        //   WHERE pm.meta_key = '_sku' AND pm.meta_value = 'CAMP333039' AND (pt.post_status = 'publish' OR pt.post_status = 'draft') AND (pt.post_type = 'product' OR pt.post_type = 'product_variation') AND tt.language_code = 'it' AND pt.post_author != 0
        //
        //$q = "SELECT ID FROM `{$postTable}` as pt JOIN `{$postMetaTable}` as pm ON pt.ID = pm.post_id JOIN `${translationTable}` tt ON pt.ID = tt.element_id WHERE pm.meta_key = '_sku' AND pm.meta_value = %s AND (pt.post_status = 'publish' OR pt.post_status = 'draft') AND (pt.post_type = 'product' OR pt.post_type = 'product_variation') AND tt.language_code = %s AND pt.post_author != 0";
        //$q = "SELECT ID FROM `{$postTable}` as pt JOIN `{$postMetaTable}` as pm ON pt.ID = pm.post_id JOIN `${translationTable}` tt ON pt.ID = tt.element_id WHERE pm.meta_key = '_sku' AND pm.meta_value = %s AND (pt.post_status = 'publish' OR pt.post_status = 'draft') AND (pt.post_type = 'product' OR pt.post_type = 'product_variation') AND tt.language_code = %s";
        $q = "SELECT ID FROM `{$postTable}` as pt JOIN `{$postMetaTable}` as pm ON pt.ID = pm.post_id JOIN `${translationTable}` tt ON pt.ID = tt.element_id WHERE pm.meta_key = '_sku' AND pm.meta_value = %s AND (pt.post_type = 'product' OR pt.post_type = 'product_variation') AND tt.language_code = %s";
        $q = $wpdb->prepare($q,$sku,$language);
        $results = $wpdb->get_results($q);
        if(!\is_array($results) || count($results) === 0){
            throw new \RunTimeException('SKU '.$sku.' does not have '.$language);
        }
        $postIds = wp_list_pluck($results,'ID');
        if(!\is_array($postIds) || count($postIds) === 0){
            throw new \RunTimeException('SKU '.$sku.' does not have '.$language);
        }
        $postId = (int) $postIds[0];
        return $postId;
    }

    /**
     * @param int $id
     * @throws \RuntimeException
     * @return string
     */
    public static function getProductLanguage(int $id): string {
        global $wpdb;
        $postTable = $wpdb->prefix.'posts';
        $translationTable = $wpdb->prefix.'icl_translations';
        $q = "SELECT * FROM `{$postTable}` as pt JOIN `${translationTable}` tt ON pt.ID = tt.element_id WHERE (pt.post_status = 'publish' OR pt.post_status = 'draft') AND (pt.post_type = 'product' OR pt.post_type = 'product_variation') AND pt.ID = %d AND pt.post_author != 0";
        $q = $wpdb->prepare($q,$id);
        $results = $wpdb->get_results($q);
        if(!\is_array($results) || count($results) === 0){
            throw new \RunTimeException('ID '.$id.' does not have languages');
        }
        $langCode = wp_list_pluck($results,'language_code');
        if(!\is_array($langCode) || count($langCode) === 0){
            throw new \RunTimeException('ID '.$id.' does not have languages');
        }
        $langCode = $langCode[0];
        return $langCode;
    }
}