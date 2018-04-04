<?php
/**
Component Name: Header Flex
Description: Header Flex Component
Category: Layout
Tags: Header, Flexbox
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

if(!class_exists("\\Waboot\\Component")){
	require_once '../../inc/Component.php';
};

class Header_Flex extends \Waboot\Component{
	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();
        //add_filter("waboot/navigation/main/class",[$this,"set_main_navigation_classes"]);
    }

    public function styles(){
        parent::styles();
        wp_enqueue_style('dashicons');
        Waboot()->add_inline_style('header_flex_style', $this->directory_uri . '/assets/dist/css/headerFlex.css');

    }

    public function scripts() {
	    parent::scripts();
	    wp_enqueue_script('header_flex_scripts', $this->directory_uri . '/assets/dist/js/headerFlex.js', ['jquery'], false, true);

    }

	public function run(){
		parent::run();
		$display_zone = $this->get_display_zone();
		$display_priority = $this->get_display_priority();
		WabootLayout()->add_zone_action($display_zone,[$this,"display_tpl"],intval($display_priority));
	}

    public function widgets() {
        add_filter("waboot/widget_areas/available",function($areas){
            $areas['header-left'] = [
                'name' => __('Header Left (Component)', 'waboot'),
                'description' => __( 'The widget areas registered by Header Flex', 'waboot' ),
            ];
            $areas['header-right'] = [
                'name' => __('Header Right (Component)', 'waboot'),
                'description' => __( 'The widget areas registered by Header Flex', 'waboot' ),
            ];
            return $areas;
        });
    }
	
	public function display_tpl(){
        $vWrapper = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/header-wrapper.php");
        $vHeader = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/header-content.php");
        $vNavbar = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/navbar-content.php");
        $vNavbarToggler = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/navbar-toggler.php");

        $vWrapper->clean()->display([
            "header_width" => Waboot\functions\get_option("headerflex_header_width"),
            "logo_position" => Waboot\functions\get_option("headerflex_logo_position"),
            "navbar_width" => Waboot\functions\get_option("headerflex_nav_width"),
            "navbar_position" => Waboot\functions\get_option("headerflex_nav_position"),
            "navbar_position_below" => Waboot\functions\get_option("headerflex_nav_position") == "below",
            "header_content" => $vHeader->get([]),
            "navbar_toggler" => $vNavbarToggler->get([]),
			"navbar_content" => $vNavbar->get([
                "nav_align" => Waboot\functions\get_option("headerflex_nav_align"),
                "display_searchbar" => Waboot\functions\get_option("headerflex_nav_searchbar"),
            ])
		]);
	}
	
	public function register_options() {
		parent::register_options();
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$imagepath = get_template_directory_uri()."/assets/images/options/";

		$orgzr->set_group($this->name."_component");

        $orgzr->add_section("header",_x("Header","Theme options section","waboot"));
        $orgzr->add_section("navigation",_x("Navigation","Theme options section","waboot"));

		try{
			$orgzr->update('headerflex_header_width',[
				'name' => __( 'Header Width', 'waboot' ),
				'desc' => __( 'Select header width. Fluid or Boxed?', 'waboot' ),
				'id' => 'headerflex_header_width',
				'std' => 'container',
				'type' => 'images',
				'options' => [
					'container-fluid' => [
						'label' => 'Fluid',
						'value' => $imagepath . 'layout/header-fluid.png'
					],
					'container' => [
						'label' => 'Boxed',
						'value' => $imagepath . 'layout/header-boxed.png'
					]
				]
			],"header");

			$orgzr->update('headerflex_logo_position',[
				'name' => __( 'Logo Position', 'waboot' ),
				'desc' => __( 'Select logo align position', 'waboot' ),
				'id' => 'headerflex_logo_position',
				'std' => 'center',
				'type' => 'select',
				'options' => [
					'center' => 'Center',
					'left' => 'Left',
					'right' => 'Right'
				]
			],"header");

			$orgzr->update("headerflex_nav_position", [
				'name' => __( 'Navbar Position', 'waboot' ),
				'desc' => __( 'Select navbar position. Aligned or Below Logo?', 'waboot' ),
				'id' => 'headerflex_nav_position',
				'std' => 'aligned',
				'type' => 'select',
				'options' => [
					'aligned' => 'Align to Logo',
					'below' => 'Below Logo'
				]
			],'navigation');

			$orgzr->update('headerflex_nav_align',[
				'name' => __( 'Navbar Align', 'waboot' ),
				'desc' => __( 'Select navbar align position', 'waboot' ),
				'id' => 'headerflex_nav_align',
				'std' => 'center',
				'type' => 'select',
				'options' => [
					'center' => 'Center',
					'left' => 'Left',
					'right' => 'Right'
				]
			],"navigation");

			$orgzr->update("headerflex_nav_width", [
				'name' => __( 'Navbar Width', 'waboot' ),
				'desc' => __( 'Select navbar width. Fluid or Boxed?', 'waboot' ),
				'id' => 'headerflex_nav_width',
				'std' => 'container',
				'type' => 'images',
				'options' => array(
					'container-fluid' => array (
						'label' => 'Fluid',
						'value' => $imagepath . 'layout/header-fluid.png'
					),
					'container' => array (
						'label' => 'Boxed',
						'value' => $imagepath . 'layout/header-boxed.png'
					)
				)
			],'navigation');

			$orgzr->update('headerflex_nav_searchbar',[
				'name' => __( 'Show search bar in Navbar?', 'waboot' ),
				'desc' => __( 'Default is enabled. Uncheck this box to turn it off.', 'waboot' ),
				'id'   => 'headerclassic_nav_searchbar',
				'std'  => '0',
				'type' => 'checkbox'
			],'navigation');
		}catch (\Exception $e){
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
    /*
    public function set_main_navigation_classes($class){
        //$classes = [$class,"navbar"];
        $options = \Waboot\functions\get_option('headerflex_nav_align');
        if(isset($options) && !empty($options)){
            $options = 'navbar-'.$options;
            $classes = [$class,$options];
        }
        return implode(' ', $classes);
    }
    */
}