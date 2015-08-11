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
		},10,5);
	}

	public function scripts(){
		//Enqueue scripts
		wp_register_script('layzr',$this->directory_uri . '/js/vendor/layzr.min.js');
		wp_enqueue_script('lazyload-component-js',$this->directory_uri . '/js/main.js',['layzr']);
	}
}