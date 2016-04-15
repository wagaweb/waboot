<?php
/**
Component Name: Navbar Classic
Description: Navbar Classic Component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class Navbar_Classic extends \Waboot\Component{
	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();
	}

	public function run(){
		parent::run();
		$display_zone = $this->get_display_zone();
		$display_priority = $this->get_display_priority();
		Waboot()->layout->add_zone_action($display_zone,[$this,"display_tpl"],intval($display_priority));
	}

	public function display_tpl(){
		$wrapper = new \WBF\includes\mvc\HTMLView($this->relative_path."/templates/navbar_wrapper.php");
		$content = (new \WBF\includes\mvc\HTMLView($this->relative_path."/templates/navbar_content.php"))->clean()->get([
			'show_mobile_nav' => Waboot\functions\get_option('mobilenav_style') == "offcavas",
			'display_socials' => Waboot\functions\get_option("social_position_none") == 1 || Waboot\functions\get_option('social_position') != "navigation" ? false : true,
		]);
		$wrapper->clean()->display([
			"navbar_width" => Waboot\functions\get_option("navbar_width"),
			'content' => $content
		]);
	}

	public function register_options() {
		parent::register_options();
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$imagepath = WBF()->resources->get_admin_assets_uri()."/images/";

		$orgzr->set_group($this->name."_component");

		$orgzr->add_section("header",_x("Header","Theme options section","waboot"));
		$orgzr->add_section("layout",_x("Layout","Theme options section","waboot"));
		$orgzr->add_section("social",_x("Socials","Theme options section","waboot"));

		$orgzr->update('mobilenav_style',[
			'name' => __( 'Mobile Nav Style', 'waboot' ),
			'desc' => __( 'Select your mobile nav style' ,'waboot' ),
			'id'   => 'mobilenav_style',
			'std' => 'offcanvas',
			'type' => 'images',
			'options' => array(
				'bootstrap' => array(
					'label' => 'Bootstrap',
					'value' => $imagepath . 'mobile/nav-bootstrap.png'
				),
				'offcanvas' => array(
					'label' => 'OffCanvas',
					'value' => $imagepath . 'mobile/nav-offcanvas.png'
				)
			)
		],"header");

		$orgzr->update("navbar_width", [
			'name' => __( 'Navbar', 'waboot' ),
			'desc' => __( 'Select navbar width. Fluid or Boxed?', 'waboot' ),
			'id' => 'navbar_width',
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
		],'layout');

		$orgzr->update("social_position",[
			'name' => __( 'Social Position', 'waboot' ),
			'desc' => __( 'Select one of the following positions for the social links', 'waboot' ),
			'id' => 'social_position',
			'type' => 'images',
			'std'  => 'navigation',
			'options' => [
				'navigation' =>  [
					'label' => 'Navigation',
					'value' => $imagepath . 'social/nav.png'
				]
			]
		],"social");

		$orgzr->reset_group();
		$orgzr->reset_section();
	}
}