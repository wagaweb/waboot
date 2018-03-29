<?php
/**
Component Name: Navbar Vertical
Description: Navbar Vertical Component
Category: Layout
Tags: Navbar
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

if(!class_exists("\\Waboot\\Component")){
	require_once get_template_directory().'/inc/Component.php';
}

class Navbar_Vertical extends \Waboot\Component{
	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();
		Waboot()->add_component_style('navbar_vertical_style', $this->directory_uri . '/assets/dist/css/navbarVertical.css');
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
		WabootLayout()->add_zone_action($display_zone,[$this,"display_tpl"],intval($display_priority));
	}

	public function display_tpl(){
        $header_layout = "navbar_vertical";

        $vWrapper = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/navbar_wrapper.php");
        $vNavbar = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/navbar_content.php");
        $vOffcanvas = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/navbar_offcanvas.php");

        $vWrapper->clean()->display([
			"navbar_class" => $header_layout,

            "navbar_content" => $vNavbar->get([
                "offcanvas" => Waboot\functions\get_option("navbarvertical_mobilestyle") == "offcanvas",
                "display_searchbar" => Waboot\functions\get_option("navbarvertical_searchbar"),
                "navbar_offcanvas" => $vOffcanvas->get([
                    "display_searchbar" => Waboot\functions\get_option("navbarvertical_searchbar"),
                ])
            ])
        ]);
	}

	public function register_options() {
		parent::register_options();
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$imagepath = get_template_directory_uri()."/assets/images/options/";

		$orgzr->set_group($this->name."_component");

		$orgzr->add_section("navigation",_x("Navigation","Theme options section","waboot"));

		try{
			$orgzr->update('navbarvertical_mobilestyle',[
				'name' => __( 'Mobile Nav Style', 'waboot' ),
				'desc' => __( 'Select your mobile nav style' ,'waboot' ),
				'id'   => 'navbarvertical_mobilestyle',
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
			],"navigation");

			$orgzr->update('navbarvertical_searchbar',[
				'name' => __( 'Show search bar in Header?', 'waboot' ),
				'desc' => __( 'Default is enabled. Uncheck this box to turn it off.', 'waboot' ),
				'id'   => 'navbarvertical_searchbar',
				'std'  => '0',
				'type' => 'checkbox'
			],'navigation');
		}catch (\Exception $e){
			trigger_error($e->getMessage());
		}

		$orgzr->reset_group();
		$orgzr->reset_section();
	}
}