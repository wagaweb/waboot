<?php

namespace Waboot\inc\core\utils;

trait Query {
    /**
     * Get the current page type. Can be "default_home" | "static_home" | "blog_page" | "common"
     *
     * @return string
     */
    static function getCurrentPageType(){
        if ( is_front_page() && is_home() ) {
            // Default homepage
            return self::PAGE_TYPE_DEFAULT_HOME;
        } elseif ( is_front_page() ) {
            // static homepage
            return self::PAGE_TYPE_STATIC_HOME;
        } elseif ( is_home() ) {
            // blog page
            return self::PAGE_TYPE_BLOG_PAGE;
        } else {
            //everything else
            return self::PAGE_TYPE_COMMON;
        }
    }

    /**
     * Return TRUE when the default home page in displayed
     *
     * @return bool
     */
    static function isDefaultHome(){
        return self::getCurrentPageType() === self::PAGE_TYPE_DEFAULT_HOME;
    }

    /**
     * Return TRUE when the static home page in displayed
     *
     * @return bool
     */
    static function isStaticHome(){
        return self::getCurrentPageType() === self::PAGE_TYPE_STATIC_HOME;
    }

    /**
     * Return TRUE when the user defined blog page in displayed
     *
     * @return bool
     */
    static function isBlogPage(){
        return self::getCurrentPageType() === self::PAGE_TYPE_BLOG_PAGE;
    }

    /**
     * Return TRUE when a common page page in displayed
     *
     * @return bool
     */
    static function isCommonPage(){
        return self::getCurrentPageType() === self::PAGE_TYPE_COMMON;
    }

    /**
     * Get the post type of the current queried object
     *
     * @return string|false
     */
    static function getQueriedObjectPostType(){
        $o = get_queried_object();
        return self::getObjectPostType($o);
    }
}