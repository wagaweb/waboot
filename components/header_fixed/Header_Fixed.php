<?php
/**
Component Name: Header Fixed
Description: A fixed Header
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class Header_Fixed extends \Waboot\Component{

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
    }


	/**
	 * Register component scripts (called automatically)
	 */
    public function scripts(){
        wp_register_script('component-header_fixed', $this->directory_uri . '/assets/dist/js/headerFixed.js', ['jquery'], false, true);
        wp_enqueue_script('component-header_fixed');
    }



	/**
	 * Register component styles (called automatically)
	 */
    public function styles(){
        wp_enqueue_style('component-header_fixed-style', $this->directory_uri . '/assets/dist/css/headerFixed.css');
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
    public function register_options() {
        parent::register_options();
        $orgzr = \WBF\modules\options\Organizer::getInstance();

        $orgzr->set_group($this->name."_component");

        $orgzr->add_section("header",_x("Header","Theme options section","waboot"));

        $orgzr->reset_group();
        $orgzr->reset_section();
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