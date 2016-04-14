<?php
/**
Component Name: Header Classic
Description: Header Classic Component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
 */

class HeaderClassicComponent extends \Waboot\Component{
	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();
	}

	public function run(){
		parent::run();
		$can_display = Waboot\functions\get_option("header_layout") == "classic";
		if($can_display){
			$display_zone = $this->get_display_zone();
			$display_priority = $this->get_display_priority();
			Waboot()->layout->add_zone_action($display_zone,[$this,"display_tpl"],intval($display_priority));
		}
	}
	
	public function display_tpl(){
		$v = new \WBF\includes\mvc\HTMLView($this->relative_path."/templates/logo-top-center.php");
		$v->clean()->display([
			"header_layout" => 'classic',
			"header_width" => Waboot\functions\get_option("header_width")
		]);
	}
	
	public function register_options() {
		parent::register_options();
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$imagepath = WBF()->resources->get_admin_assets_uri()."/images/";

		$orgzr->set_group($this->name."_component");

		$orgzr->add_section("header",_x("Header","Theme options section","waboot"));
		$orgzr->add_section("layout",_x("Layout","Theme options section","waboot"));

		$orgzr->update("header_layout",[
			'name' => __( 'Header', 'waboot' ),
			'desc' => __( 'Select your header layout' ,'waboot' ),
			'id'   => 'header_layout',
			'std' => 'classic',
			'type' => 'images',
			'options' => [
				'classic' => array(
					'label' => 'classic',
					'value' => $imagepath . 'header/header-1.png'
				)
			]
		],"header");

		$orgzr->update('header_width',[
			'name' => __( 'Header', 'waboot' ),
			'desc' => __( 'Select header width. Fluid or Boxed?', 'waboot' ),
			'id' => 'header_width',
			'std' => 'container-fluid',
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
		],"layout");

		$orgzr->reset_group();
		$orgzr->reset_section();
	}
}