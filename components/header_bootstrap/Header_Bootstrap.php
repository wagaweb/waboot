<?php
/**
Component Name: Header Bootstrap
Description: Header Bootstrap Component
Category: Layout
Tags: Header
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

if(!class_exists("\\Waboot\\Component")){
	require_once get_template_directory().'/inc/Component.php';
}

class Header_Bootstrap extends \Waboot\Component{
	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();
		add_filter("waboot/navigation/main/class",[$this,"set_main_navigation_classes"]);
		Waboot()->add_component_style('header_bootstrap_style', $this->directory_uri . '/assets/dist/css/headerBootstrap.css');
		Waboot()->add_component_style('offcanvas_style', $this->directory_uri . '/assets/dist/css/offcanvas.css');
	}

    public function styles(){
        parent::styles();
    }

    public function scripts() {
        parent::scripts();
        wp_enqueue_script('offcanvas_js', $this->directory_uri . '/assets/dist/js/offcanvas.js', ['jquery'], false, true);
    }

	public function run(){
		parent::run();
		$display_zone = $this->get_display_zone();
		$display_priority = $this->get_display_priority();
		Waboot()->layout->add_zone_action($display_zone,[$this,"display_tpl"],intval($display_priority));
	}

	public function display_tpl(){
        $header_layout = "header_bootstrap";

        $vWrapper = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/header_wrapper.php");
        $vNavbar = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/navbar_content.php");
        $vOffcanvas = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/navbar_offcanvas.php");

        $vWrapper->clean()->display([
            "header_class" => $header_layout,
            "header_width" => Waboot\functions\get_option("headerbotstrap_width"),

            "navbar_content" => $vNavbar->get([
                "offcanvas" => Waboot\functions\get_option("headerbotstrap_nav_mobilestyle") == "offcanvas",
                "display_searchbar" => Waboot\functions\get_option("headerbotstrap_nav_searchbar"),
                "navbar_offcanvas" => $vOffcanvas->get([
                    "display_searchbar" => Waboot\functions\get_option("headerbotstrap_nav_searchbar"),
                    "logo_offcanvas" => Waboot\functions\get_option("headerbotstrap_offcanvas_logo"),
                    "logo_offcanvas_show" => Waboot\functions\get_option("headerbotstrap_show_offcanvas_logo") && Waboot\functions\get_option("headerbotstrap_offcanvas_logo") != '',
                ])
            ])
        ]);
	}

	public function register_options() {
		parent::register_options();
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$imagepath = get_template_directory_uri()."/assets/images/options/";

		$orgzr->set_group($this->name."_component");

		$orgzr->add_section("header",_x("Header","Theme options section","waboot"));

		try{
			$orgzr->update("headerbotstrap_width", [
				'name' => __( 'Header', 'waboot' ),
				'desc' => __( 'Select header width. Fluid or Boxed?', 'waboot' ),
				'id' => 'headerbotstrap_width',
				'std' => WabootLayout()->get_grid_class('container'),
				'type' => 'images',
				'options' => array(
					WabootLayout()->get_grid_class('container-fluid') => array (
						'label' => 'Fluid',
						'value' => $imagepath . 'layout/header-fluid.png'
					),
					WabootLayout()->get_grid_class('container') => array (
						'label' => 'Boxed',
						'value' => $imagepath . 'layout/header-boxed.png'
					)
				)
			],'header');

			$orgzr->update("headerbotstrap_nav_align", [
				'name' => __( 'Navbar Align', 'waboot' ),
				'desc' => __( 'Select navbar align. Left or Right?', 'waboot' ),
				'id' => 'headerbotstrap_nav_align',
				'std' => 'left',
				'type' => 'select',
				'options' => [
					'left' => 'Left',
					'right' => 'Right'
				]
			],'header');

			$orgzr->update('headerbotstrap_nav_searchbar',[
				'name' => __( 'Show search bar in Header?', 'waboot' ),
				'desc' => __( 'Default is enabled. Uncheck this box to turn it off.', 'waboot' ),
				'id'   => 'headerbotstrap_nav_searchbar',
				'std'  => '0',
				'type' => 'checkbox'
			],'header');

			$orgzr->update('headerbotstrap_nav_mobilestyle',[
				'name' => __( 'Mobile Nav Style', 'waboot' ),
				'desc' => __( 'Select your mobile nav style' ,'waboot' ),
				'id'   => 'headerbotstrap_nav_mobilestyle',
				'std' => 'inline',
				'type' => 'images',
				'options' => array(
					'inline' => array(
						'label' => _x('Inline',"mobilenav_style","waboot"),
						'value' => $imagepath . 'mobile/nav-bootstrap.png'
					),
					'offcanvas' => array(
						'label' => _x('OffCanvas',"mobilenav_style","waboot"),
						'value' => $imagepath . 'mobile/nav-offcanvas.png'
					)
				)
			],"header");

			$orgzr->update('headerbotstrap_show_offcanvas_logo',[
				'name' => __( 'Show Logo in Offcanvas Mobile Nav?', 'waboot' ),
				'desc' => __( 'Choose the visibility of site logo in mobile navigation.', 'waboot' ),
				'id'   => 'headerbotstrap_show_offcanvas_logo',
				'std'  => '1',
				'type' => 'checkbox'
			],"navigation");

			$orgzr->update('headerbotstrap_offcanvas_logo',[
				'name' => __( 'Mobile Offcanvas logo', 'waboot' ),
				'desc' => __( 'Choose the logo to display in mobile offcanvas navigation bar', 'waboot' ),
				'id'   => 'headerbotstrap_offcanvas_logo',
				'std'  => '',
				'type' => 'upload'
			],"navigation");
		}catch(\Exception $e){
			trigger_error($e->getMessage());
		}

		$orgzr->reset_group();
		$orgzr->reset_section();
	}

    /*
     *
     * CUSTOM HOOKS
     *
     */

    /**
     * Set the classes to the main navigation
     * @param $class
     * @return mixed
     */
	public function set_main_navigation_classes($class){
		$options = \Waboot\functions\get_option('headerbotstrap_nav_align');
		if(isset($options) && !empty($options)){
			$options = 'navbar-'.$options;
			$classes = [$class,$options];
		}
		return implode(' ', $classes);
	}
}