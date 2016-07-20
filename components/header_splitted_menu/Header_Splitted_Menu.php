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

        $menu_items = wp_get_nav_menu_items('splitted');
        $count = count($menu_items);
        wp_localize_script('component-header_splitted_menu', 'wabootHeaderSplitted', array(
            'count' => $count
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
        $orgzr = \WBF\modules\options\Organizer::getInstance();

        $imagepath = get_template_directory_uri()."/assets/images/options/";

        $orgzr->set_group($this->name."_component");

        $orgzr->add_section("header",_x("Header","Theme options section","waboot"));

        $orgzr->update('header_splitted_logo',[
            'name' => __( 'Header Splitted Logo,', 'waboot' ),
            'desc' => __( 'Select header logo.', 'waboot' ),
            'id' => 'header_splitted_logo',
            'std'  => get_template_directory_uri()."/assets/images/default/waboot-color.png",
            'type' => 'upload'
            ],"header");

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