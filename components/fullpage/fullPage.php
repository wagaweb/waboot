<?php

/**
Plugin Name: FullPage Addon
Description: Enable full page layouts
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
 */

class FullpageComponent extends Waboot_Component{
    function scripts(){
        wp_enqueue_script('fullpage-script',$this->directory_uri . '/jquery.fullPage.min.js',array('jquery'),false,false);
        wp_enqueue_script('easings-script',$this->directory_uri . '/jquery.easings.min.js',array('jquery'),false,false);
        wp_enqueue_script('slimscroll-script',$this->directory_uri . '/jquery.slimscroll.min.js',array('jquery'),false,false);
    }
	function styles(){
		wp_enqueue_style('fullpage-style',$this->directory_uri . '/jquery.fullPage.css');
	}
}