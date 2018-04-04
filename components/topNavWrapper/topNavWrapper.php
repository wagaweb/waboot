<?php
/**
Component Name: Top Nav Wrapper
Description: Top Nav Wrapper Component
Category: Layout
Tags: Navigation
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
 */

if(!class_exists("\\Waboot\\Component")){
	require_once get_template_directory().'/inc/Component.php';
}

class TopNavWrapperComponent extends \Waboot\Component{

    var $default_zone = "header";
    var $default_priority = 1;

    /**
     * This method will be executed at Wordpress startup (every page load)
     */
    public function setup(){
        parent::setup();
	    Waboot()->add_component_style('topnav_style', $this->directory_uri . '/assets/dist/css/topNavWrapper.css');
    }

    public function styles(){
        parent::styles();
    }

    public function run(){
        parent::run();
        $display_zone = $this->get_display_zone();
        $display_priority = $this->get_display_priority();
	    WabootLayout()->add_zone_action($display_zone,[$this,"display_tpl"],intval($display_priority));
    }

    public function widgets() {
        add_filter("waboot/widget_areas/available",function($areas){
            $areas['topnav'] = [
                'name' => __('Top Nav {{ n }} (Component)', 'waboot'),
                'description' => __( 'The widget areas registered by Top Nav', 'waboot' ),
                'type' => 'multiple',
                'subareas' => 2
            ];
            return $areas;
        });
    }

    public function display_tpl(){
        $v = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/topnav.php");

        $args = [
            'topnav_width' => of_get_option( 'topnav_width',WabootLayout()->get_grid_class('container') ),
        ];
        $v->clean()->display($args);

    }

    public function register_options(){
        parent::register_options();
        $orgzr = \WBF\modules\options\Organizer::getInstance();

        $imagepath = get_template_directory_uri()."/assets/images/options/";

        $orgzr->set_group($this->name."_component");

        $orgzr->add_section("layout",_x("Layout","Theme options section","waboot"));
        $orgzr->add_section("header",_x("Header","Theme options section","waboot"));

        $orgzr->add([
            'name' => __('Top Nav Wrapper Width', 'waboot'),
            'desc' => __('Select Top Nav Wrapper width. Fluid or Boxed?', 'waboot'),
            'id' => 'topnav_width',
            'std' => 'container',
            'type' => 'images',
            'options' => [
	            'container-fluid' => [
                    'label' => 'Fluid',
                    'value' => $imagepath . 'layout/top-nav-fluid.png'
                ],
	            'container' => [
                    'label' => 'Boxed',
                    'value' => $imagepath . 'layout/top-nav-boxed.png'
                ]
            ]
        ],"layout");

        $orgzr->add([
            'name' => _x('Top Nav Wrapper Background', 'Theme options', 'waboot'),
            'desc' => _x('Change the Top Nav Wrapper background color.', 'Theme options', 'waboot'),
            'id' => 'topnav_bgcolor',
            'type' => 'color',
            'std' => '',
            'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
        ],"header");

        $orgzr->reset_group();
        $orgzr->reset_section();
    }
}