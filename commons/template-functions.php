<?php

if(!function_exists("images_url")) :
	function images_url(){
		echo get_images_url();
	}
endif;

if(!function_exists("get_images_url")) :
	function get_images_url(){
		$base_dir = get_template_directory_uri();
		if(is_child_theme()){
			$base_dir = get_stylesheet_directory_uri();
		}
		return apply_filters("wbft_images_url",$base_dir."/assets/images");
	}
endif;

if(!function_exists("wbft_current_page_type")):
	function wbft_current_page_type(){
		if ( is_front_page() && is_home() ) {
			// Default homepage
			return "default_home";
		} elseif ( is_front_page() ) {
			// static homepage
			return "static_home";
		} elseif ( is_home() ) {
			// blog page
			return "blog_page";
		} else {
			//everything else
			return "common";
		}
	}
endif;

if(!function_exists("wbft_is_blog_page")):
	function wbft_is_blog_page(){
		return wbft_current_page_type() == "blog_page";
	}
endif;

if(!function_exists("wbft_get_uri_path_after")):
	/**
	 * Get the uri parts after specified tag. Eg: if the uri is "/foo/bar/zor/", calling wbft_get_uri_path_after(foo) will return: array("bar","zor")
	 * @param $tag
	 * @return array
	 */
	function wbft_get_uri_path_after($tag){
		$url_parts = parse_url("http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
		$path_parts = explode("/",$url_parts['path']);
		$key = 0;
		foreach($path_parts as $k => $p){
			if($p == $tag){
				$key = $k;
			}
		}
		$path_parts_sliced = array_slice($path_parts,(int)$key+1);
		return $path_parts_sliced;
	}
endif;