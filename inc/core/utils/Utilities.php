<?php

namespace Waboot\inc\core\utils;

/**
 * Class Utilities
 *
 * @package WBF\components\utils
 */
class Utilities{
    use Arrays,Paths,Query,Terms,WordPress;

    const PAGE_TYPE_DEFAULT_HOME = "default_home";
    const PAGE_TYPE_STATIC_HOME = "static_home";
    const PAGE_TYPE_BLOG_PAGE = "blog_page";
    const PAGE_TYPE_COMMON = "common";

    /**
     * Check if a string is a JSON array
     *
     * @param $string
     *
     * @return bool
     */
    static function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Returns TRUE if $url is https
     *
     * @param $url
     *
     * @return bool
     */
    static function is_ssl($url){
        return substr( $url, 0, 5 ) === 'https';
    }

    /**
     * @param $url
     *
     * @return bool
     */
    static function validate_url($url){
        $url = \filter_var($url,FILTER_SANITIZE_URL);
        $r = \filter_var($url, FILTER_VALIDATE_URL);
        return (bool) $r;
    }

    static function predump($var){
        echo "<pre>";
        var_dump($var);
        echo "</pre>";
    }

    /**
     * Secure dump. var_dump only in presence of an admin or when $_GET['wbf_debug'] is active.
     */
    static function sdump($var,$format = true){
        if(current_user_can("manage_options") || isset($_GET['wbf_debug'])){
            if($format){
                self::predump($var);
            }else{
                var_dump($var);
            }
        }
    }

    /**
     * Generate a random string of $length characters
     *
     * @param int $length
     * @param string|null $characters the base characters to choose from
     *
     * @return string
     */
    static function getRandomString($length = 10, $characters = null){
        if(!isset($characters)){
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @see: https://stackoverflow.com/questions/1363925/check-whether-image-exists-on-remote-url
     * @param $url
     * @return bool
     */
    public static function remoteFileExists($url): bool {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        // don't download content
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        if($result !== FALSE) {
            return true;
        }
        return false;
    }
}