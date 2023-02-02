<?php

namespace Waboot\inc\core\multilanguage\helpers;

class Polylang
{
    /**
     * @return bool
     */
    public static function isPolylang(): bool
    {
        return function_exists('PLL');
    }

    /**
     * @param string $lang
     * @return void
     */
    public static function isActiveLanguage(string $lang): bool
    {
        $activeLanguages = pll_languages_list(['fields' => '']);
        foreach ($activeLanguages as $activeLanguage){
            if(!$activeLanguage instanceof \PLL_Language){
                continue;
            }
            if($activeLanguage->slug === $lang){
                return true;
            }
        }
        return false;
    }

    /**
     * @return void
     */
    public static function getCurrentLanguage(): string
    {
        $lang = PLL()->curlang;
        if($lang instanceof \PLL_Language){
            return $lang->slug;
        }
        return '';
    }

    /**
     * @param string $language
     * @return void
     */
    public static function setCurrentLanguage(string $language)
    {
        PLL()->curlang = PLL()->model->get_language($language);
    }

    /**
     * @param int $postId
     * @param string $lang
     * @return void
     */
    public static function setPostLanguage(int $postId, string $lang): void
    {
        pll_set_post_language($postId,$lang);
    }

    /**
     * @param int $postId
     * @param array $translations
     * @return void
     */
    public static function setPostTranslations(int $postId, array $translations): void
    {
        PLL()->model->post->save_translations($postId, $translations);
    }

    /**
     * @param int $postId
     * @param int $translationPostId
     * @param string $lang
     * @return void
     */
    public static function setSinglePostTranslation(int $postId, int $translationPostId, string $lang): void
    {
        $currentPostLanguage = pll_get_post_language($postId);
        if($lang === $currentPostLanguage){
            return;
        }
        $availableTranslations = self::getPostTranslationsFromDB($postId);
        if(isset($availableTranslations[$currentPostLanguage])){
            unset($availableTranslations[$currentPostLanguage]);
        }
        $availableTranslations[$lang] = $translationPostId;
        PLL()->model->post->save_translations($postId, $availableTranslations);
    }

    /**
     * @param $postId
     * @return array
     */
    public static function getPostTranslationsFromDB($postId): array
    {
        global $wpdb;
        /*
         * SELECT tt.description
         * FROM wbp_term_relationships AS tr JOIN wbp_term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
         * WHERE tr.object_id = 6609 AND tt.taxonomy = 'post_translations'
         */
        $q = 'SELECT tt.description FROM '.$wpdb->prefix.'term_relationships AS tr JOIN '.$wpdb->prefix.'term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tr.object_id = %d AND tt.taxonomy = %s';
        $q = $wpdb->prepare($q,$postId,'post_translations');
        $r = $wpdb->get_results($q);
        if(!\is_array($r) || count($r) === 0){
            return [];
        }
        $translationsEncoded = $r[0]->description;
        $translations = unserialize($translationsEncoded);
        if(!\is_array($translations) || count($translations) === 0){
            return [];
        }
        return $translations;
    }

    /**
     * @param int $postId
     * @param string $lang
     * @return int|null
     */
    /*function getPostIdInLanguage(int $postId, string $lang): ?int {
        $activeLanguages = pll_languages_list(['fields' => '']);
        return 0;
    }*/

    /**
     * @param int $productId
     * @param string $lang
     * @return int|null
     */
    public static function getLocalizedPostId(int $productId, string $lang): ?int
    {
        $currentPostLang = pll_get_post_language($productId);
        if($currentPostLang === $lang){
            return $productId;
        }
        $translation = pll_get_post($productId,$lang);
        if(!\is_int($translation) || $translation === 0){
            $translations = self::getPostTranslationsFromDB($productId);
            if(empty($translations) || !isset($translations[$lang])){
                return null;
            }
            $translation = $translations[$lang];
        }
        return $translation;
    }

    /**
     * @param mixed $by
     * @param string $taxonomy
     * @param string $byFieldName
     * @param string|null $lang
     * @return \WP_Term|null
     */
    public static function getTerm($by, string $taxonomy, string $byFieldName = 'slug', string $lang = null): ?\WP_Term
    {
        $currentLanguage = self::getCurrentLanguage();
        if(is_string($lang) && $lang !== $currentLanguage){
            self::setCurrentLanguage($lang);
        }
        if(\is_string($by)){
            $term = \get_term_by($byFieldName,$by,$taxonomy);
        }else{
            $term = get_term($by,$taxonomy);
        }
        if(is_string($lang) && $lang !== $currentLanguage){
            self::setCurrentLanguage($currentLanguage);
        }
        if(!$term instanceof \WP_Term){
            return null;
        }
        $currentTermLanguage = pll_get_term_language($term->term_id);
        if($currentTermLanguage === $lang){
            return $term;
        }
        $termTranslations = \pll_get_term_translations($term->term_id);
        if(\is_array($termTranslations) && isset($termTranslations[$lang])){
            return \WP_Term::get_instance($termTranslations[$lang],$taxonomy);
        }
        return null;
    }
}