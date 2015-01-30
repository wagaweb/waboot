<?php
/**
Component Name: Sample 01
Description: Sample component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class SampleComponent extends Waboot_Component{

    /**
     * This method will be executed at Wordpress startup (every page load)
     */
    public function setup(){
        parent::setup();
        //Do stuff...
    }

    /**
     * This method will be executed on the "wp" action in pages where the component must be loaded
     */
    public function run(){
        parent::run();
        //Do stuff...
    }

    public function scripts(){
        //Enqueue scripts
        /**
         * wp_enqueue_script('sample-script',$this->directory_uri . '/sample.min.js',array('jquery'),false,false);
         * ...
         * ....
         */
    }

    public function styles(){
        //Enqueue styles
        /**
         * wp_enqueue_style('sample-style',$this->directory_uri . '/sample.css');
         * ...
         * ....
         */
    }

	public function widgets(){
		//register_widget("sampleWidget");
	}

    public function theme_options($options){
        $options = parent::theme_options($options);
        //Do stuff...
        $options[] = array(
            'name' => __( 'Sample Info Box', 'waboot' ),
            'desc' => __( 'This is a sample infobox', 'waboot' ),
            'type' => 'info'
        );
        $options[] = array(
            'name' => __( 'Sample check box', 'waboot' ),
            'desc' => __( 'This is a sample checkbox.', 'waboot' ),
            'id'   => $this->name.'_sample_checkbox',
            'std'  => '0', //not enabled by default
            'type' => 'checkbox'
        );
        return $options;
    }

    public function onActivate(){
        parent::onActivate();
        //Do stuff...
    }

    public function onDeactivate(){
        parent::onDeactivate();
        //Do stuff...
    }
}

/*
class sampleWidget extends WP_Widget{
	...
}
*/