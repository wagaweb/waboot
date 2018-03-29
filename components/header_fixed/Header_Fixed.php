<?php
/**
Component Name: Header Fixed
Description: A fixed Header
Category: Layout
Tags: jQuery, Header
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

if(!class_exists("\\Waboot\\Component")){
	require_once get_template_directory().'/inc/Component.php';
}

class Header_Fixed extends \Waboot\Component{

    /**
     * This method will be executed at Wordpress startup (every page load)
     */
    public function setup(){
        parent::setup();
	    Waboot()->add_component_style('component-header_fixed-style', $this->directory_uri . '/assets/dist/css/headerFixed.css');
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

	    $fixed_class = \Waboot\functions\get_option($this->name.'_fixed_class');
	    $mode = \Waboot\functions\get_option($this->name.'_mode');
	    $color_before = \Waboot\functions\get_option($this->name.'_color_before');
	    $padding_before = \Waboot\functions\get_option($this->name.'_padding_before');
	    $color_after = \Waboot\functions\get_option($this->name.'_color_after');
	    $padding_after = \Waboot\functions\get_option($this->name.'_padding_after');
	    $breakpoint = \Waboot\functions\get_option($this->name.'_breakpoint');


	    wp_localize_script('component-header_fixed', 'wbHeaderFixed', array(
		    'fixed_class' => $fixed_class,
		    'modality' => $mode,
		    'color_before' => $color_before,
		    'padding_before' => $padding_before,
		    'color_after' => $color_after,
		    'padding_after' => $padding_after,
		    'breakpoint' => $breakpoint
	    ) );
        wp_enqueue_script('component-header_fixed');
    }

	/**
	 * Register component styles (called automatically)
	 */
    public function styles(){
        //wp_enqueue_style('component-header_fixed-style', $this->directory_uri . '/assets/dist/css/headerFixed.css');
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

	    $orgzr->add([
		    'name' => 'Header Fixed',
		    'desc' => __( 'Edit default options for Header Fixed post type', 'waboot' ),
		    'type' => 'info'
	    ], "header");

	    try{
		    $orgzr->update($this->name.'_fixed_class',[
			    'name' => __( 'Class to fix', 'waboot' ),
			    'desc' => __( 'Select the class you want to fix. ', 'waboot' ),
			    'id'   => $this->name.'_fixed_class',
			    'std'  => 'header#masthead',
			    'type' => 'text'
		    ],"header");

		    $orgzr->update($this->name.'_mode',[
			    'name' => __( 'Mode', 'waboot' ),
			    'desc' => __( 'Choose if you want the class to be fixed from the beginning, after a breakpoint or on scroll up', 'waboot' ),
			    'id'   => $this->name.'_mode',
			    'std'  => '0',
			    'type' => 'select',
			    'options' => [
				    'beginning' => __("From the Beginning","waboot"),
				    'breakpoint' => __("After Breakpoint","waboot"),
				    'scrollUp' => __("On Scroll Up","waboot")
			    ]
		    ],"header");

		    $orgzr->update($this->name.'_color_before',[
			    'name' => __( 'Style Before - Color', 'waboot' ),
			    'desc' => __( ' ', 'waboot' ),
			    'id'   => $this->name.'_color_before',
			    'std'  => '',
			    'type' => 'advanced_color'
		    ],"header");

		    $orgzr->update($this->name.'_padding_before',[
			    'name' => __( 'Style Before - Padding', 'waboot' ),
			    'desc' => __( ' ', 'waboot' ),
			    'id'   => $this->name.'_padding_before',
			    'std'  => '50',
			    'type' => 'text'
		    ],"header");

		    $orgzr->update($this->name.'_color_after',[
			    'name' => __( 'Style After - Color', 'waboot' ),
			    'desc' => __( ' ', 'waboot' ),
			    'id'   => $this->name.'_color_after',
			    'std'  => '',
			    'type' => 'advanced_color'
		    ],"header");

		    $orgzr->update($this->name.'_padding_after',[
			    'name' => __( 'Style After - Padding', 'waboot' ),
			    'desc' => __( ' ', 'waboot' ),
			    'id'   => $this->name.'_padding_after',
			    'std'  => '50',
			    'type' => 'text'
		    ],"header");

		    $orgzr->update($this->name.'_breakpoint',[
			    'name' => __( 'Breakpoint', 'waboot' ),
			    'desc' => __( 'The header enter after the specified number of pixels or after a DOM element (e.g. section#second). Only valid for "Beginning" and "Breakpoint" modes.', 'waboot' ),
			    'id'   => $this->name.'_breakpoint',
			    'std'  => '50',
			    'type' => 'text'
		    ],"header");
	    }catch (\Exception $e){
	    	trigger_error($e->getMessage());
	    }

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