<?php
/**
Component Name: Cookielaw
Description: Cookielaw component by WAGA
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
 */

class CookielawComponent extends \WBF\modules\components\Component{

	public function scripts(){
		//Enqueue scripts
		//wp_register_script("component-cookielaw", $this->directory_uri . '/js/cookielaw.min.js', array('jquery'), false, true);
		wp_register_script("component-cookielaw", $this->directory_uri . '/js/bundle.js', array('jquery'), false, true);
		wp_localize_script("component-cookielaw", "cookielawData", array(
			"str" => of_get_option($this->name."_str"),
			"close_str" => of_get_option($this->name."_close_str"),
			"learnmore_str" => of_get_option($this->name."_learnmore_str"),
			"learnmore_url" => of_get_option($this->name."_learnmore_url"),
			"saveonscroll" => (int) of_get_option($this->name."_saveonscroll"),
			"scroll_limit" => (int) of_get_option($this->name."_scroll_limit"),
		));
		wp_enqueue_script("component-cookielaw");
	}

	public function styles(){
		wp_enqueue_style('component-cookielaw-style',$this->directory_uri . '/css/cookielaw.min.css');
	}

	public function theme_options($options){
		$options = parent::theme_options($options);
		$options[] = array(
			'name' => __('Message', 'waboot'),
			'id' => $this->name.'_str',
			'desc' => __('The message to display to the users', 'waboot'),
			'type' => 'textarea',
			'std' => __("Cookies help us deliver our services. By continuing to use our website, you agree to our use of cookies","waboot")
		);
		$options[] = array(
			'name' => __('Close string', 'waboot'),
			'id' => $this->name.'_close_str',
			'desc' => __('The close button string', 'waboot'),
			'type' => 'text',
			'std' => __("OK","waboot")
		);
		$options[] = array(
			'name' => __('Learnmore string', 'waboot'),
			'id' => $this->name.'_learnmore_str',
			'desc' => __('The learn more string', 'waboot'),
			'type' => 'text',
			'std' => __("Learn more","waboot")
		);
		$options[] = array(
			'name' => __('Learn more website', 'waboot'),
			'id' => $this->name.'_learnmore_url',
			'desc' => __('The learn more website', 'waboot'),
			'type' => 'text',
			'std' => 'http://example.com'
		);
		$options[] = array(
			'name' => __('Save cookie on scroll', 'waboot'),
			'id' => $this->name.'_saveonscroll',
			'desc' => __('The user preference will be saved on page scroll also', 'waboot'),
			'std'  => '0',
			'type' => 'checkbox'
		);
		$options[] = array(
			'name' => __('Scroll pixel limit', 'waboot'),
			'id' => $this->name.'_scroll_limit',
			'desc' => __('If the \'save on scroll\' option is active, the cookie will be saved after a page scroll at least of the pixels specified here', 'waboot'),
			'type' => 'text',
			'std' => '60'
		);
		return $options;
	}
}