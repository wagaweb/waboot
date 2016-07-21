<?php
/**
Component Name: Header Splitted Menu
Description: An Header with centered logo that splits menu in two parts
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

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
        $menu = new \WBF\components\mvc\HTMLView($this->relative_path."/templates/header_splitted.php");
        $menu->clean()->display();
    }



	/**
	 * Register component scripts (called automatically)
	 */
    public function scripts(){
        wp_register_script('component-header_splitted_menu', $this->directory_uri . '/assets/dist/js/headerSplittedMenu.js', ['jquery'], false, true);

	    $position = \Waboot\functions\get_option($this->name.'_item_select');
	    $margin = \Waboot\functions\get_option($this->name.'_margin_select');
	    $theme_locations = get_nav_menu_locations();
	    $menu_obj = get_term( $theme_locations['main']);

	    $count = ($position == 'middle')
		    ? $menu_obj->count/2
		    : $position;



        wp_localize_script('component-header_splitted_menu', 'wabootHeaderSplitted', array(
            'count' => $count,
	        'margin' => $margin
        ) );
        wp_enqueue_script('component-header_splitted_menu');
    }



	/**
	 * Register component styles (called automatically)
	 */
    public function styles(){
        wp_enqueue_style('component-header_splitted-style', $this->directory_uri . '/assets/dist/css/headerSplittedMenu.css');
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
	    $nav_menu_locations = get_nav_menu_locations();
	    $theme_locations = [];
	    foreach ( $nav_menu_locations as $nav_menu_location_key=>$nav_menu_location_value ) {
		    $theme_locations[$nav_menu_location_key] = $nav_menu_location_key;
	    }

	    $options[] = array(
		    'name' => __( 'Splitted Menu Settings', 'waboot' ),
		    'desc' => __( '', 'waboot' ),
		    'type' => 'info'
	    );
	    $options[] = array(
		    'name' => __( 'Splitted Menu Position', 'waboot' ),
		    'desc' => __( 'Select the item of the menu at which you want to apply the margin. By default is "middle" but you can insert any number. ', 'waboot' ),
		    'id'   => $this->name.'_item_select',
		    'std'  => 'middle',
		    'type' => 'text'
	    );
	    $options[] = array(
		    'name' => __( 'Additional Margin', 'waboot' ),
		    'desc' => __( 'An additional margin to increase spacing between logo and menu items. This number is applied to both sides of the logo, therefore consider it will be doubled', 'waboot' ),
		    'id'   => $this->name.'_margin_select',
		    'std'  => '0',
		    'type' => 'text'
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