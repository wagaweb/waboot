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
        wp_enqueue_script('component-colorbox',$this->directory_uri . '/jquery.colorbox-min.js',array('jquery'),false,false);
        wp_enqueue_script('component-colorbox-custom',$this->directory_uri . '/colorbox-custom.js',array('jquery','component-colorbox'),false,false);
    }  
    
    public function styles(){
        wp_enqueue_style('component-colorbox-style',$this->directory_uri . '/colorbox.css');
    }

	public function theme_options($options){
        $options = parent::theme_options($options);
        $options[] = array(
            'name' => __( 'Width Column', 'waboot' ),
            'id'   => $this->name.'_column_width',
            'std' => 'col-sm-4',
			'type' => 'select',
	        'options' => array(
	            'col-sm-4' => 'col-sm-4',
	            'col-sm-3' => 'col-sm-3'
	        )
        );
        return $options;
    }
}