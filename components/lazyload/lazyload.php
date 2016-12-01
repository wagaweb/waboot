<?php
/**
Component Name: Lazyload
Description: Enable Lazyloading on images
Category: Effects
Tags: jQuery, Lazyload
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
 */

if(!class_exists("\\WBF\\modules\\components\\Component")) return;

class LazyloadComponent extends \WBF\modules\components\Component{
	public function setup(){
		parent::setup();

		add_filter("post_thumbnail_html",function($html, $post_id, $post_thumbnail_id, $size, $attr){
			$html = preg_replace("/src/","data-layzr",$html);
			return $html;
		},99,5);

		add_filter("wp_get_attachment_image_attributes",function($attr, $attachment, $size){
			if(isset($attr['src'])){
				$attr['data-layzr'] = $attr['src'];
				unset($attr['src']);
			}
			return $attr;
		},99,3);

		add_filter("the_content",function($content){
			$pattern = "/<img([^>]+)src=([\w0-9\"'-_%.\/:]+)/";
			$r = preg_replace($pattern,"<img$1 data-layzr=$2",$content);
			if($r){
				$content = $r;
			}
			return $content;
		},999);
	}

	public function scripts(){
		//Enqueue scripts
		wp_register_script('layzr',$this->directory_uri . '/assets/vendor/layzr.min.js');
		wp_enqueue_script('lazyload-component-js',$this->directory_uri . '/assets/dist/js/lazyload.js',['layzr'], false, true);
	}
}