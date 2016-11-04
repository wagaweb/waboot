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

class Navbar_Vertical extends \Waboot\Component{
	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();
		add_filter("waboot/navigation/main/class",[$this,"set_main_navigation_classes"]);
	}

    public function styles(){
        parent::styles();
        Waboot()->add_inline_style('navbar_vertical_style', $this->directory_uri . '/assets/css/navbarVertical.css');
        Waboot()->add_inline_style('offcanvas_style', $this->directory_uri . '/assets/dist/css/offcanvas.css');
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
		$wrapper = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/navbar_wrapper.php");
		$content = (new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/navbar_content.php"))->clean()->get([
			'show_mobile_nav' => Waboot\functions\get_option('mobilenav_style') == "offcavas",
			'display_searchbar' => Waboot\functions\get_option("display_search_bar_in_header") == 1
		]);
        $header_layout = "navbar_vertical";
		$wrapper->clean()->display([
			"navbar_class" => $header_layout ? "nav-".$header_layout : "nav",
			'content' => $content,
		]);
	}

	public function register_options() {
		parent::register_options();
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$imagepath = get_template_directory_uri()."/assets/images/options/";

		$orgzr->set_group($this->name."_component");

		$orgzr->add_section("navigation",_x("Navigation","Theme options section","waboot"));

		$orgzr->update('mobilenav_style',[
			'name' => __( 'Mobile Nav Style', 'waboot' ),
			'desc' => __( 'Select your mobile nav style' ,'waboot' ),
			'id'   => 'mobilenav_style',
			'std' => 'offcanvas',
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

		$orgzr->update('search_bar',[
			'name' => __( 'Show search bar in Header?', 'waboot' ),
			'desc' => __( 'Default is enabled. Uncheck this box to turn it off.', 'waboot' ),
			'id'   => 'display_search_bar_in_header',
			'std'  => '0',
			'type' => 'checkbox'
		],'navigation');

		$orgzr->reset_group();
		$orgzr->reset_section();
	}
}