<?php
/**
Component Name: Header Splitted Menu
Description: An Header with centered logo that splits menu in two parts
Category: Layout
Tags: Header
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

require_once( dirname(__FILE__).'/WabootSplittedNavMenuWalker.php');

class Header_Splitted_Menu extends \Waboot\Component{

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
        $display_zone = $this->get_display_zone();
        $display_priority = $this->get_display_priority();
        Waboot()->layout->add_zone_action($display_zone,[$this,"display_tpl"],intval($display_priority));
    }

    public function display_tpl(){

        // retrieve the user options
	    $menu_position = (\Waboot\functions\get_option('header_splitted_menu'))
		    ? \Waboot\functions\get_option('header_splitted_menu')
		    : 'main';
	    $split_position = (\Waboot\functions\get_option('header_splitted_position'))
		    ? \Waboot\functions\get_option('header_splitted_position')
		    : '';

	    $walker = new WabootSplittedNavMenuWalker( $split_position, $menu_position);

    	$menu = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/header_splitted.php");
        $menu->clean()->display([
	        'walker'    => $walker
        ]);
    }


	/**
	 * Register component scripts (called automatically)
	 */
    public function scripts(){
	    $additional_margin = (\Waboot\functions\get_option('header_splitted_margin'))
		    ? \Waboot\functions\get_option('header_splitted_margin')
		    : 10;

        $scripts = [
            'component-header_splitted_menu' => [
                'uri' => $this->directory_uri . '/assets/dist/js/headerSplittedMenu.js',
                'path' => $this->directory . '/assets/dist/js/headerSplittedMenu.js',
                'type' => 'js',
                'deps' => ['jquery'],
                'i10n' => [
                    'name' => "wabootHeaderSplitted",
                    'params' => [
                        'margin' => $additional_margin
                    ]
                ]
            ]
        ];

        $am = new \WBF\components\assets\AssetsManager($scripts);
        $am->enqueue();
    }


	/**
	 * Register component styles (called automatically)
	 */
    public function styles(){
        //wp_enqueue_style('component-header_splitted-style', $this->directory_uri . '/assets/dist/css/headerSplittedMenu.css');
	    Waboot()->add_inline_style('component-header_splitted-style', $this->directory_uri . '/assets/dist/css/headerSplittedMenu.css');
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
		    'name' => 'Header Splitted',
		    'desc' => __( 'Edit default options for Header Fixed post type', 'waboot' ),
		    'type' => 'info'
	    ], "header");
	    $orgzr->update('header_splitted_position',[
			    'name' => __( 'Menu Position', 'waboot' ),
                'desc' => __( 'Select the item of the menu at which you want to apply the margin. By default split at the middle.', 'waboot' ),
			    'id'   => $this->name.'_item_select',
			    'type' => 'text',
                'std' => ''
		    ],"header");
	    $orgzr->update('header_splitted_margin',[
			    'name' => __( 'Additional Margin', 'waboot' ),
			    'desc' => __( 'An additional margin to increase spacing between logo and menu items. This number is applied to both sides of the logo, therefore consider it will be doubled. Default is 10px', 'waboot' ),
			    'id'   => $this->name.'_margin_select',
			    'type' => 'text'
		    ],"header");
	    $orgzr->update('header_splitted_menu',[
			    'name' => __( 'Menu Theme Position', 'waboot' ),
			    'desc' => __( 'The Theme position of the menu that will be splitted, Default is "main"', 'waboot' ),
			    'id'   => $this->name.'_menu_select',
			    'type' => 'text'
		    ],"header");

	    $orgzr->reset_group();
	    $orgzr->reset_section();
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