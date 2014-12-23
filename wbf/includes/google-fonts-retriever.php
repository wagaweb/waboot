<?php

class WBF_GoogleFontsRetriever{
    const api_url = "https://www.googleapis.com/webfonts/v1/webfonts";
    var $api_key = "AIzaSyDXgT0NYjLhDmUzdcxC5RITeEDimRmpq3s";
    var $last_error = "";
    var $cache_file_name = "wbf_font_cache.php";

    public function __construct($api_key = null){
        if(isset($api_key)){
            $this->api_key = $api_key;
        }
        $this->write_font_cache_file();
    }

    function get_webfonts(){
        $fonts_json = false;
        $cache_file = WBF_DIRECTORY."/cache/".$this->cache_file_name;

        if(is_file($cache_file) && is_readable($cache_file)){
            require_once $cache_file;
            if(isset($fonts))
                $fonts_json = $fonts;
        }
        if(!$fonts_json){
            $fonts_json = $this->download_webfonts(self::api_url);
        }

        if($fonts_json != false) $fonts_json = json_decode($fonts_json);

        return $fonts_json;
    }

    function write_font_cache_file(){
        $fonts_json = $this->download_webfonts(self::api_url);
        if(!$fonts_json){
            $fonts_json = "{}";
        }

        $fonts_json = '<?php $fonts = \''.$fonts_json.'\'; ?>';

        $cache_file = WBF_DIRECTORY."/cache/".$this->cache_file_name;
        $fhandle = fopen($cache_file,'w');
        if(fwrite($fhandle, $fonts_json) === FALSE) {
            $this->last_error = new WBF_GoogleFontsRetrieverException("Unable to write the font cache file, located at: $cache_file","file_write_failed");
        }
        fclose($fhandle);
    }

    function download_webfonts(){
        $fonts_json = $this->do_download_webfonts(self::api_url);
        if(!$fonts_json){
            $fonts_json = $this->do_download_webfonts(self::api_url."?key=".$this->api_key);
        }
        return $fonts_json;
    }

    function do_download_webfonts($url){
        $fonts_json = false;
        if(function_exists('wp_remote_get')){
            $response = wp_remote_get($url, array('sslverify' => false));
            if(is_wp_error($response)){
                $this->last_error = new WBF_GoogleFontsRetrieverException(__("Unable to connect to Google API"), "connection_failed");
            }else{
                if(isset($response['body']) && $response['body']){
                    if(strpos($response['body'], 'error') === false){
                        $fonts_json = $response['body'];
                    }else{
                        $error = json_decode($response['body']);
                        $this->last_error = new WBF_GoogleFontsRetrieverException(sprintf(__('Google API Notice: %s. %s', "wbf"), $error->error->code, $error->error->message), "limit_reached");
                    }
                }
            }
        }
        return $fonts_json;
    }
}

class WBF_GoogleFontsRetrieverException extends Exception{
    var $type;

    public function __construct($message, $type, $code = 0){
        parent::__construct($message, $code);
        $this->type = $type;
    }
}