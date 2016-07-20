<?php
/**
Component Name: Image Modal
Description: Enable modal visualization on images.
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class Image_Modal extends \WBF\modules\components\Component{
	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();
	}

	/**
	 * This method will be executed in nodes where component is active
	 */
	public function run(){
		parent::run();
	}

	/**
	 * Enqueue component scripts
	 */
	public function scripts(){
		wp_register_script('component-image_modal-colorbox',$this->directory_uri . '/assets/vendor/jquery.colorbox-min.js',['jquery'],false,true);
		wp_register_script('component-image_modal-custom',$this->directory_uri . '/assets/dist/js/imagemodal.js',['jquery','component-image_modal-colorbox'],false,true);

		$cbox_elements = of_get_option($this->name.'_element');
		if($cbox_elements == "") $cbox_elements = false;
		$cbox_custom_elements = of_get_option($this->name.'_custom_element');
		if($cbox_custom_elements == "") $cbox_custom_elements = false;

		wp_localize_script('component-image_modal-custom', 'wabootCbox', array(
			'elements' => $cbox_elements,
			'custom_elements' => isset($cbox_custom_elements) ? $cbox_custom_elements : false,
			'current' => __("image {current} of {total}","waboot")
		) );
		wp_enqueue_script('component-image_modal-custom');
	}

	/**
	 * Enqueue component styles
	 */
	public function styles(){
		wp_enqueue_style('component-colorbox-style',$this->directory_uri . '/assets/dist/css/imagemodal.css');
	}

	/**
	 * Register components options
	 */
	public function register_options(){
		parent::register_options();
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$orgzr->add_section( "colorbox", _x( "Image Modal", "Image Modal options tab label", "waboot" ));

		$orgzr->add([
			'name' => __('Colorbox elements', 'waboot'),
			'id' => $this->name.'_element',
			'desc' => __('Select the type of elements on which to apply colorbox', 'waboot'),
			'type' => 'radio',
			'options' => [
				'all-images' => _x("All images","Image Modal Component Option","waboot"),
				'galleries' => _x("Galleries","Image Modal Component Option","waboot"),
				'custom' => _x("Custom only","Image Modal Component Option","waboot"),
			],
			'std' => 'all-images'
		],"colorbox");

		$orgzr->add([
			'name' => _x( 'Custom element',"Image Modal Component Option", 'waboot' ),
			'id'   => $this->name.'_custom_element',
			'desc' => _x( 'Enter a custom selector for colobox',"Image Modal Component Option", 'waboot' ),
			'type' => 'text'
		],"colorbox");

		$orgzr->reset_group();
		$orgzr->reset_section();
	}
}