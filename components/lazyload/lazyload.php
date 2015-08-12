<?php
/**
Component Name: Lazyload
Description: Lazyload component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
 */

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
	}

	public function scripts(){
		//Enqueue scripts
		wp_register_script('layzr',$this->directory_uri . '/js/vendor/layzr.min.js');
		wp_enqueue_script('lazyload-component-js',$this->directory_uri . '/js/main.js',['layzr']);
	}
}