<?php
/**
Component Name: Shuffle
Description: Shuffle component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class ShuffleComponent extends Waboot_Component{

    public function scripts(){
        wp_enqueue_script('isotope-script',$this->directory_uri . '/isotope.pkgd.js',array('jquery'),false,false);
        wp_enqueue_script('isotope-custom-script',$this->directory_uri . '/isotope-custom.js',array('jquery'),false,false);
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