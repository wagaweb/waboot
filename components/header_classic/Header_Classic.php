<?php
/**
Component Name: Header Classic
Description: Header Classic Component
Category: Layout
Tags: Header
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class Header_Classic extends \Waboot\Component{
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
		$v = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/header-classic.php");
		$social_position = Waboot\functions\get_option('social_position');
		$v->clean()->display([
			"header_width" => Waboot\functions\get_option("header_classic_width"),
			"social_position" => $social_position,
			'display_socials' => Waboot\functions\get_option("social_position_none") == 1 || !in_array($social_position,['header-right','header-left']) ? false : true,
		]);
	}
	
	public function register_options() {
		parent::register_options();
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$imagepath = get_template_directory_uri()."/assets/images/options/";

		$orgzr->set_group($this->name."_component");

		$orgzr->add_section("layout",_x("Layout","Theme options section","waboot"));
		$orgzr->add_section("social",_x("Socials","Theme options section","waboot"));

		$orgzr->update('header_classic_width',[
			'name' => __( 'Header Classic Width', 'waboot' ),
			'desc' => __( 'Select header width. Fluid or Boxed?', 'waboot' ),
			'id' => 'header_classic_width',
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

		$socials = \Waboot\functions\get_available_socials();

		foreach($socials as $k => $s){
			$opt_id = 'social_'.$k;
			$orgzr->update($opt_id,[
				'name' => $s['name'],
				'desc' => $s['theme_options_desc'],
				'id'   => $opt_id,
				'type' => 'text',
				'std'  => ''
			],"social");
		}

		$orgzr->update("social_position",[
			'name' => __( 'Social Position', 'waboot' ),
			'desc' => __( 'Select one of the following positions for the social links', 'waboot' ),
			'id' => 'social_position',
			'type' => 'images',
			'std'  => 'header-right',
			'options' => [
				'header-right' =>  [
					'label' => 'Header Right',
					'value' => $imagepath . 'social/header-right.png'
				],
				'header-left' =>  [
					'label' => 'Header Left',
					'value' => $imagepath . 'social/header-left.png'
				]
			]
		],"social");

		$orgzr->update("social_position_none",[
			'name' => __( 'Do not use any of the previous positions', 'waboot' ),
			'desc' => __( 'You can manually place the social links with the <strong>waboot social widget</strong> (even if one of the previous positions is selected)', 'waboot' ),
			'id'   => 'social_position_none',
			'std'  => '0',
			'type' => 'checkbox'
		],"social");

		$orgzr->reset_group();
		$orgzr->reset_section();
	}
}