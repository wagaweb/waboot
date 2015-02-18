<?php
/**
Component Name: Masonry
Description: Masonry component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class MasonryComponent extends \WBF\modules\components\Component{

    public function scripts(){
        wp_enqueue_script('masonry-script',$this->directory_uri . '/masonry.pkgd.min.js',array('jquery','imagesLoaded-js'),false,true);
        wp_enqueue_script('masonry-custom-script',$this->directory_uri . '/masonry-custom.js',array('jquery','masonry-script'),false,true);
    }
    
    public function theme_options($options){
        $options = parent::theme_options($options);
        $options[] = array(
            'name' => __( 'Width Column', 'waboot' ),
            'desc' => __( 'This is a sample checkbox.', 'waboot' ),
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