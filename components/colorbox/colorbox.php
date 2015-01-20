<?php
/**
Component Name: Colorbox
Description: Colorbox component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class ColorboxComponent extends Waboot_Component{

    public function scripts(){
        wp_enqueue_script('component-colorbox',$this->directory_uri . '/jquery.colorbox-min.js',array('jquery'),false,true);
        wp_register_script('component-colorbox-custom',$this->directory_uri . '/colorbox-custom.js',array('jquery','component-colorbox'),false,true);

	    $cbox_elements = of_get_option($this->name.'_element');
	    if($cbox_elements == "") $cbox_elements = false;
	    $cbox_custom_elements = of_get_option($this->name.'_custom_element');
	    if($cbox_custom_elements == "") $cbox_custom_elements = false;

	    wp_localize_script('component-colorbox-custom', 'wabootCbox', array(
		    'elements' => $cbox_elements,
		    'custom_elements' => isset($cbox_custom_elements) ? $cbox_custom_elements : false,
		    'current' => __("image {current} of {total}","waboot")
	    ) );
	    wp_enqueue_script('component-colorbox-custom');
    }
    
    public function styles(){
        wp_enqueue_style('component-colorbox-style',$this->directory_uri . '/colorbox.css');
    }

	public function theme_options($options){
        $options = parent::theme_options($options);
		$options[] = array(
			'name' => __('Colorbox elements', 'waboot'),
			'id' => $this->name.'_element',
			'desc' => __('Select the type of elements on which to apply colorbox', 'waboot'),
			'type' => 'radio',
			'options' => array(
				'all-images' => __("All images","waboot"),
				'galleries' => __("Galleries","waboot"),
				'custom' => __("Custom only","waboot"),
			),
			'std' => 'all-images'
		);
		$options[] = array(
			'name' => __( 'Custom element', 'waboot' ),
			'id'   => $this->name.'_custom_element',
			'desc' => __( 'Enter a custom selector for colobox', 'waboot' ),
			'type' => 'text'
		);
        return $options;
    }
}