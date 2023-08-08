<?php
namespace Waboot\inc\core\utils;

trait Paths {
    /**
     * Completely erase a directory
     * @param string $dir the directory path
     */
    static function deltree($dir){
        if(!preg_match("|[A-Za-z0-9]+/$|",$dir)) $dir .= "/"; // ensure $dir ends with a slash

        $files = glob( $dir . '*', GLOB_MARK );
        foreach($files as $file){
            if( substr( $file, -1 ) == '/' )
                deltree( $file );
            else
                unlink( $file );
        }
        if(is_dir($dir)){
            rmdir( $dir );
        }
    }

    /**
     * List all files in a folder
     *
     * @param $dir
     * @param string $extension
     * @return array
     */
    static function listFolderFiles($dir,$extension = "php"){
        $files_in_root = glob($dir."/*.{$extension}");
        $files = glob($dir."/*/*.{$extension}");

        if(!$files_in_root) $files_in_root = array();
        if(!$files) $files = array();

        return array_merge($files_in_root,$files);
    }

    /**
     * Create a directory
     *
     * @param $path
     * @param int $chmod
     * @return bool
     * @throws \Exception
     */
    static function mkdir($path,$chmod = 0777){
        if(!is_dir($path)){
            if(!mkdir($path,$chmod)){
                throw new \Exception(_("Unable to create folder {$path}"));
            }else{
                return true;
            }
        }
        return false;
    }

    /**
     * Recursively create directories
     *
     * @param $path
     *
     * @return bool
     */
    static function mkpath($path) {
        if(@mkdir($path) or file_exists($path)) return true;
        return (self::mkpath(dirname($path)) and mkdir($path));
    }

    /**
     * Convert an url to the absolute path of that url in wordpress
     *
     * @param $url
     * @return mixed
     */
    static function urlToPath($url){
        if(defined('WABOOT_REAL_SITE_URL')){
            $siteurl = WABOOT_REAL_SITE_URL;
        }else{
            //$blogurl = get_bloginfo("url");
            if(defined('WABOOT_WP_AS_APP') && WABOOT_WP_AS_APP){
                $siteurl = get_home_url();
            }else{
                $siteurl = site_url();
            }
        }
        $blogurl = preg_replace("(https?://)", "", $siteurl );
        //$result = preg_match("/^https?:\/\/$blogurl\/([[:space:]a-zA-Z0-9\/_.-]+)/", $url, $matches);
        $result = preg_replace("|^https?://$blogurl|", self::getAbspath(), $url);
        //$blogpath = ABSPATH;

        //$filepath = $blogpath."/".$matches[1];
        //return $filepath;
        return $result;
    }

    /**
     * Convert a path to the uri relative to wordpress installation
     *
     * @param $path
     * @return mixed
     */
    static function pathToUrl($path){
        if(defined('WABOOT_REAL_SITE_URL')){
            $siteurl = WABOOT_REAL_SITE_URL;
        }else{
            //$blogurl = trailingslashit(get_bloginfo("url"));
            if(defined('WABOOT_WP_AS_APP') && WABOOT_WP_AS_APP){
                $siteurl = trailingslashit(get_home_url());
            }else{
                $siteurl = trailingslashit(site_url());
            }
        }
        $blogpath = self::getAbspath();
        $result = preg_replace("|^$blogpath|", $siteurl, $path);
        return $result;
    }

    /**
     * Get the absolute filesystem path to the root of the WordPress installation.
     * Wrapper for \get_home_path() and ABSPATH
     */
    static function getAbspath(){
        if(function_exists('get_home_path')){
            $path = \get_home_path();
        }else{
            $path = ABSPATH;
        }
        return $path;
    }

    /**
     * Get the current url via vanilla function
     *
     * @return string
     */
    static function getCurrentUrl() {
        $pageURL = 'http';
        if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    /**
     * Get the current url using wp functions
     *
     * @return string
     */
    static function wpGetCurrentUrl(){
        global $wp;
        $current_url = add_query_arg( $wp->query_string, '', home_url( $wp->request ) );
        return $current_url;
    }

    /**
     * Convert full URL paths to path relative to wp-content.
     *
     * Removes the http or https protocols the domain and wp-content.
     *
     * @param string $link Full URL path.
     * @return string path.
     */
    static function makeLinkRelativeToContentDir( $link ) {
        return preg_replace( '|^(https?:)?\/\/[^/]+(\/?wp-content)(\/?.*)|i', '$3', $link );
    }
}