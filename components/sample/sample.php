<?php
/**
Component Name: Sample 01
Description: Sample component
Category: Layout
Tags: Tag Sample
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

if(!class_exists("\\WBF\\modules\\components\\Component")) return;

class SampleComponent extends \WBF\modules\components\Component{

    /**
     * This method will be executed at Wordpress startup (every page load).
     *
     * This is called during "init", hooked at "wbf_init", which has a priority of 11. So if you want to hook at "init" you must begin with a priority of 12+.
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

	/**
	 * Register component scripts (called automatically)
	 */
    public function scripts(){
        //Enqueue scripts
        /**
         * wp_enqueue_script('sample-script',$this->directory_uri . '/sample.min.js',array('jquery'),false,false);
         * ...
         * ....
         */
    }

	/**
	 * Register component styles (called automatically)
	 */
    public function styles(){
        //Enqueue styles
        /**
         * wp_enqueue_style('sample-style',$this->directory_uri . '/sample.css');
         * ...
         * ....
         */
    }

	/**
	 * Register component widgets (called automatically).
	 *
	 * @hooked 'widgets_init'
	 */
	public function widgets(){
		//register_widget("sampleWidget");
	}

	/**
	 * This is an action callback.
	 *
	 * Here you can use WBF Organizer to set component options
	 */
	public function register_options(){
		parent::register_options();
		//$orgzr = \WBF\modules\options\Organizer::getInstance();
		//Do stuff...
	}

	/**
	 * This is a filter callback. You can't use WBF Organizer.
	 *
	 * @param $options
	 *
	 * @return array|mixed
	 */
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